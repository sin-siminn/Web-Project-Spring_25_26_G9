<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/AuctionCloser.php';

// Only verified sellers can access seller dashboard
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['seller_verified']) ||
    (int)$_SESSION['seller_verified'] !== 1
) {
    header('Location: ../views/login.php');
    exit;
}

// Student 4 requirement: close expired auctions before showing seller results.
close_expired_auctions($conn);

$seller_id = (int)$_SESSION['user_id'];
$has_reserve = auction_column_exists($conn, 'auctions', 'reserve_price');
$has_winner_bid_id = auction_column_exists($conn, 'auctions', 'winner_bid_id');
$reserve_select = $has_reserve ? 'a.reserve_price' : 'NULL AS reserve_price';
$stored_winner_select = $has_winner_bid_id ? 'a.winner_bid_id AS stored_winner_bid_id' : 'NULL AS stored_winner_bid_id';
$highest_bid_subquery = "(\n    SELECT b2.bid_id\n    FROM bids b2\n    WHERE b2.auction_id = a.auction_id\n    ORDER BY b2.amount DESC, b2.created_at ASC, b2.bid_id ASC\n    LIMIT 1\n)";
$winner_join_expr = $has_winner_bid_id ? "COALESCE(a.winner_bid_id, $highest_bid_subquery)" : $highest_bid_subquery;

$stmt = $conn->prepare("\n    SELECT\n        a.auction_id,\n        a.seller_id,\n        a.title,\n        a.description,\n        a.starting_price,\n        a.current_price,\n        a.end_time,\n        a.status,\n        a.created_at,\n        a.category_id,\n        $reserve_select,\n        $stored_winner_select,\n        c.name AS category_name,\n        (SELECT COUNT(*) FROM bids b WHERE b.auction_id = a.auction_id) AS bid_count,\n        hb.bid_id AS resolved_winner_bid_id,\n        hb.amount AS winning_bid_amount,\n        winner.name AS winner_name,\n        winner.email AS winner_email\n    FROM auctions a\n    LEFT JOIN categories c ON a.category_id = c.id\n    LEFT JOIN bids hb ON hb.bid_id = $winner_join_expr\n    LEFT JOIN users winner ON winner.user_id = hb.buyer_id\n    WHERE a.seller_id = ?\n    ORDER BY a.created_at DESC\n");

$stmt->bind_param('i', $seller_id);
$stmt->execute();
$result = $stmt->get_result();

$listings = [];

while ($row = $result->fetch_assoc()) {
    $row['bid_count'] = (int)$row['bid_count'];
    $row['can_modify'] = ($row['status'] === 'active' && $row['bid_count'] === 0);
    $row['is_ended'] = auction_is_ended_status($row['status']);
    $row['status_label'] = auction_is_ended_status($row['status']) ? 'Ended' : ucfirst((string)$row['status']);
    $row['status_class'] = 'status-' . htmlspecialchars((string)$row['status']);
    $row['edit_url'] = 'ListingController.php?action=edit&id=' . (int)$row['auction_id'];

    $row['reserve_result'] = 'N/A';
    $row['reserve_not_met'] = false;

    if ($row['is_ended']) {
        if ($row['winning_bid_amount'] === null) {
            $row['reserve_result'] = 'No Bids';
        } elseif ($has_reserve && $row['reserve_price'] !== null && $row['reserve_price'] !== '') {
            if ((float)$row['winning_bid_amount'] >= (float)$row['reserve_price']) {
                $row['reserve_result'] = 'Reserve Met';
            } else {
                $row['reserve_result'] = 'Reserve Not Met';
                $row['reserve_not_met'] = true;
            }
        } else {
            $row['reserve_result'] = 'Reserve Not Set';
        }
    }

    $listings[] = $row;
}

include __DIR__ . '/../views/seller_dashboard.php';
