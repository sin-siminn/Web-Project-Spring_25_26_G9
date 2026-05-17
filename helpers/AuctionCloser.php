<?php
/**
 * Student 4 auction-closing helper.
 * Uses the current project schema:
 * auctions.auction_id, auctions.current_price, auctions.end_time, bids.auction_id.
 */

if (!function_exists('auction_column_exists')) {
    function auction_column_exists(mysqli $conn, string $table, string $column): bool
    {
        $stmt = $conn->prepare("\n            SELECT COUNT(*) AS total\n            FROM INFORMATION_SCHEMA.COLUMNS\n            WHERE TABLE_SCHEMA = DATABASE()\n              AND TABLE_NAME = ?\n              AND COLUMN_NAME = ?\n        ");
        $stmt->bind_param('ss', $table, $column);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return ((int)($row['total'] ?? 0)) > 0;
    }
}

if (!function_exists('auction_status_allows')) {
    function auction_status_allows(mysqli $conn, string $value): bool
    {
        $stmt = $conn->prepare("\n            SELECT COLUMN_TYPE\n            FROM INFORMATION_SCHEMA.COLUMNS\n            WHERE TABLE_SCHEMA = DATABASE()\n              AND TABLE_NAME = 'auctions'\n              AND COLUMN_NAME = 'status'\n            LIMIT 1\n        ");
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $columnType = (string)($row['COLUMN_TYPE'] ?? '');
        return strpos($columnType, "'" . $conn->real_escape_string($value) . "'") !== false;
    }
}

if (!function_exists('auction_ended_status_value')) {
    function auction_ended_status_value(mysqli $conn): string
    {
        // PDF uses 'ended'. The original SQL dump used 'closed'. This keeps the code compatible with both.
        return auction_status_allows($conn, 'ended') ? 'ended' : 'closed';
    }
}

if (!function_exists('auction_is_ended_status')) {
    function auction_is_ended_status(?string $status): bool
    {
        return in_array((string)$status, ['ended', 'closed'], true);
    }
}

if (!function_exists('auction_ended_status_sql')) {
    function auction_ended_status_sql(): string
    {
        return "'ended','closed'";
    }
}

if (!function_exists('close_expired_auctions')) {
    function close_expired_auctions(mysqli $conn): int
    {
        $closedCount = 0;
        $endedStatus = auction_ended_status_value($conn);
        $hasWinnerBidId = auction_column_exists($conn, 'auctions', 'winner_bid_id');

        try {
            $conn->begin_transaction();

            $stmt = $conn->prepare("\n                SELECT auction_id\n                FROM auctions\n                WHERE status = 'active'\n                  AND end_time IS NOT NULL\n                  AND end_time <= NOW()\n                FOR UPDATE\n            ");
            $stmt->execute();
            $expiredAuctions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            foreach ($expiredAuctions as $auctionRow) {
                $auctionId = (int)$auctionRow['auction_id'];

                $winnerStmt = $conn->prepare("\n                    SELECT bid_id\n                    FROM bids\n                    WHERE auction_id = ?\n                    ORDER BY amount DESC, created_at ASC, bid_id ASC\n                    LIMIT 1\n                ");
                $winnerStmt->bind_param('i', $auctionId);
                $winnerStmt->execute();
                $winner = $winnerStmt->get_result()->fetch_assoc();
                $winnerBidId = $winner ? (int)$winner['bid_id'] : null;

                if ($hasWinnerBidId) {
                    $updateStmt = $conn->prepare("\n                        UPDATE auctions\n                        SET status = ?, winner_bid_id = ?\n                        WHERE auction_id = ? AND status = 'active'\n                    ");
                    $updateStmt->bind_param('sii', $endedStatus, $winnerBidId, $auctionId);
                } else {
                    $updateStmt = $conn->prepare("\n                        UPDATE auctions\n                        SET status = ?\n                        WHERE auction_id = ? AND status = 'active'\n                    ");
                    $updateStmt->bind_param('si', $endedStatus, $auctionId);
                }

                $updateStmt->execute();
                if ($updateStmt->affected_rows > 0) {
                    $closedCount++;
                }
            }

            $conn->commit();
        } catch (Throwable $e) {
            @mysqli_rollback($conn);
        }

        return $closedCount;
    }
}
