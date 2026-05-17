<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/AuctionCloser.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../views/login.php');
    exit;
}

// Student 4 requirement: close expired auctions before showing auction data.
close_expired_auctions($conn);

$buyer_id = (int)$_SESSION['user_id'];
$action = $_GET['action'] ?? 'browse';

function table_column_exists(mysqli $conn, string $table, string $column): bool
{
    return auction_column_exists($conn, $table, $column);
}

function fetch_categories(mysqli $conn): array
{
    $result = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

switch ($action) {
    case 'browse':
        $categories = fetch_categories($conn);

        $stmt = $conn->prepare("\n            SELECT\n                a.auction_id,\n                a.seller_id,\n                a.title,\n                a.description,\n                a.starting_price,\n                a.current_price,\n                a.end_time,\n                a.status,\n                a.category_id,\n                c.name AS category_name,\n                u.name AS seller_name,\n                NULL AS image_path,\n                (SELECT COUNT(*) FROM bids b WHERE b.auction_id = a.auction_id) AS bid_count\n            FROM auctions a\n            LEFT JOIN categories c ON c.id = a.category_id\n            LEFT JOIN users u ON u.user_id = a.seller_id\n            WHERE a.status = 'active'\n              AND a.end_time > NOW()\n            ORDER BY a.end_time ASC, a.created_at DESC\n        ");
        $stmt->execute();
        $auctions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $stmt = $conn->prepare("SELECT COUNT(DISTINCT auction_id) AS total FROM bids WHERE buyer_id = ?");
        $stmt->bind_param('i', $buyer_id);
        $stmt->execute();
        $my_bid_total = (int)($stmt->get_result()->fetch_assoc()['total'] ?? 0);

        $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM auctions WHERE status = 'active' AND end_time > NOW()");
        $stmt->execute();
        $active_total = (int)($stmt->get_result()->fetch_assoc()['total'] ?? 0);

        include __DIR__ . '/../views/buyer_dashboard.php';
        break;

    case 'detail':
        $auction_id = (int)($_GET['id'] ?? 0);
        if ($auction_id <= 0) {
            header('Location: BuyerDashboardController.php?action=browse');
            exit;
        }

        $has_reserve = auction_column_exists($conn, 'auctions', 'reserve_price');
        $has_winner_bid_id = auction_column_exists($conn, 'auctions', 'winner_bid_id');
        $reserve_select = $has_reserve ? 'a.reserve_price' : 'NULL AS reserve_price';
        $winner_select = $has_winner_bid_id ? 'a.winner_bid_id' : 'NULL AS winner_bid_id';

        $stmt = $conn->prepare("\n            SELECT\n                a.auction_id,\n                a.seller_id,\n                a.title,\n                a.description,\n                a.starting_price,\n                a.current_price,\n                a.end_time,\n                a.status,\n                a.category_id,\n                $reserve_select,\n                $winner_select,\n                c.name AS category_name,\n                u.name AS seller_name,\n                u.email AS seller_email,\n                NULL AS image_path,\n                (SELECT COUNT(*) FROM bids b WHERE b.auction_id = a.auction_id) AS bid_count\n            FROM auctions a\n            LEFT JOIN categories c ON c.id = a.category_id\n            LEFT JOIN users u ON u.user_id = a.seller_id\n            WHERE a.auction_id = ?\n            LIMIT 1\n        ");
        $stmt->bind_param('i', $auction_id);
        $stmt->execute();
        $auction = $stmt->get_result()->fetch_assoc();

        if (!$auction) {
            header('Location: BuyerDashboardController.php?action=browse');
            exit;
        }

        $stmt = $conn->prepare("\n            SELECT\n                b.bid_id,\n                b.amount,\n                b.created_at,\n                u.name AS bidder_name\n            FROM bids b\n            INNER JOIN users u ON u.user_id = b.buyer_id\n            WHERE b.auction_id = ?\n            ORDER BY b.created_at DESC, b.bid_id DESC\n            LIMIT 10\n        ");
        $stmt->bind_param('i', $auction_id);
        $stmt->execute();
        $recent_bids = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        if (!empty($auction['winner_bid_id'])) {
            $stmt = $conn->prepare("\n                SELECT b.bid_id, b.buyer_id, b.amount, u.name AS winner_name\n                FROM bids b\n                INNER JOIN users u ON u.user_id = b.buyer_id\n                WHERE b.bid_id = ? AND b.auction_id = ?\n                LIMIT 1\n            ");
            $winner_id = (int)$auction['winner_bid_id'];
            $stmt->bind_param('ii', $winner_id, $auction_id);
        } else {
            $stmt = $conn->prepare("\n                SELECT b.bid_id, b.buyer_id, b.amount, u.name AS winner_name\n                FROM bids b\n                INNER JOIN users u ON u.user_id = b.buyer_id\n                WHERE b.auction_id = ?\n                ORDER BY b.amount DESC, b.created_at ASC, b.bid_id ASC\n                LIMIT 1\n            ");
            $stmt->bind_param('i', $auction_id);
        }
        $stmt->execute();
        $winner_bid = $stmt->get_result()->fetch_assoc();

        $auction_ended = auction_is_ended_status($auction['status']) || strtotime($auction['end_time']) <= time();
        $reserve_not_met = false;
        if ($auction_ended && $has_reserve && $auction['reserve_price'] !== null && $auction['reserve_price'] !== '' && $winner_bid) {
            $reserve_not_met = ((float)$winner_bid['amount'] < (float)$auction['reserve_price']);
        }

        include __DIR__ . '/../views/auction_detail.php';
        break;

    case 'my-bids':
    case 'my_bids':
        $has_reserve = auction_column_exists($conn, 'auctions', 'reserve_price');
        $has_winner_bid_id = auction_column_exists($conn, 'auctions', 'winner_bid_id');
        $reserve_select = $has_reserve ? 'a.reserve_price' : 'NULL AS reserve_price';
        $stored_winner_select = $has_winner_bid_id ? 'a.winner_bid_id AS stored_winner_bid_id' : 'NULL AS stored_winner_bid_id';
        $highest_bid_subquery = "(\n            SELECT b2.bid_id\n            FROM bids b2\n            WHERE b2.auction_id = a.auction_id\n            ORDER BY b2.amount DESC, b2.created_at ASC, b2.bid_id ASC\n            LIMIT 1\n        )";
        $winner_join_expr = $has_winner_bid_id ? "COALESCE(a.winner_bid_id, $highest_bid_subquery)" : $highest_bid_subquery;

        $stmt = $conn->prepare("\n            SELECT\n                a.auction_id,\n                a.title,\n                a.current_price,\n                a.end_time,\n                a.status,\n                a.seller_id,\n                $reserve_select,\n                $stored_winner_select,\n                c.name AS category_name,\n                seller.name AS seller_name,\n                seller.email AS seller_email,\n                mb.my_highest_bid,\n                hb.bid_id AS highest_bid_id,\n                hb.buyer_id AS highest_bidder_id,\n                hb.amount AS highest_bid_amount,\n                (SELECT COUNT(*) FROM bids bx WHERE bx.auction_id = a.auction_id) AS bid_count\n            FROM (\n                SELECT auction_id, MAX(amount) AS my_highest_bid\n                FROM bids\n                WHERE buyer_id = ?\n                GROUP BY auction_id\n            ) mb\n            INNER JOIN auctions a ON a.auction_id = mb.auction_id\n            LEFT JOIN categories c ON c.id = a.category_id\n            LEFT JOIN users seller ON seller.user_id = a.seller_id\n            LEFT JOIN bids hb ON hb.bid_id = $winner_join_expr\n            ORDER BY a.end_time DESC, a.created_at DESC\n        ");
        $stmt->bind_param('i', $buyer_id);
        $stmt->execute();
        $my_bids = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        foreach ($my_bids as &$row) {
            $is_active = ($row['status'] === 'active' && strtotime($row['end_time']) > time());
            $is_ended = !$is_active || auction_is_ended_status($row['status']);
            $reserve_not_met = false;

            if ($is_ended && $has_reserve && $row['reserve_price'] !== null && $row['reserve_price'] !== '' && $row['highest_bid_amount'] !== null) {
                $reserve_not_met = ((float)$row['highest_bid_amount'] < (float)$row['reserve_price']);
            }

            $is_winner = ($is_ended && !$reserve_not_met && (int)$row['highest_bidder_id'] === $buyer_id);
            $row['is_active_now'] = $is_active;
            $row['is_winner'] = $is_winner;
            $row['reserve_not_met'] = $reserve_not_met;

            if ($is_active) {
                if ((float)$row['my_highest_bid'] >= (float)$row['current_price'] && (int)$row['highest_bidder_id'] === $buyer_id) {
                    $row['buyer_status'] = 'Leading';
                    $row['status_class'] = 'status-leading';
                } else {
                    $row['buyer_status'] = 'Outbid';
                    $row['status_class'] = 'status-outbid';
                }
            } elseif ($reserve_not_met) {
                $row['buyer_status'] = 'Reserve Not Met';
                $row['status_class'] = 'status-reserve';
            } elseif ($is_winner) {
                $row['buyer_status'] = 'Won';
                $row['status_class'] = 'status-won';
            } else {
                $row['buyer_status'] = 'Lost';
                $row['status_class'] = 'status-lost';
            }
        }
        unset($row);

        include __DIR__ . '/../views/my_bids.php';
        break;

    default:
        header('Location: BuyerDashboardController.php?action=browse');
        exit;
}
