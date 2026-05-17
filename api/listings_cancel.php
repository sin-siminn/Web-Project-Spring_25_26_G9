<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/AuctionCloser.php';

header('Content-Type: application/json');

// Only verified sellers can cancel auctions
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['seller_verified']) ||
    (int)$_SESSION['seller_verified'] !== 1
) {
    http_response_code(403);
    echo json_encode([
        'ok' => false,
        'msg' => 'Unauthorized request.'
    ]);
    exit;
}

// Student 4: close expired auctions before processing auction data.
close_expired_auctions($conn);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'ok' => false,
        'msg' => 'Invalid request method.'
    ]);
    exit;
}

$auction_id = (int)($_POST['auction_id'] ?? 0);
$seller_id = (int)$_SESSION['user_id'];

if ($auction_id <= 0) {
    echo json_encode([
        'ok' => false,
        'msg' => 'Invalid auction ID.'
    ]);
    exit;
}

// Check auction belongs to this seller
$stmt = $conn->prepare("
    SELECT auction_id, seller_id, status
    FROM auctions
    WHERE auction_id = ? AND seller_id = ?
    LIMIT 1
");
$stmt->bind_param("ii", $auction_id, $seller_id);
$stmt->execute();
$auction = $stmt->get_result()->fetch_assoc();

if (!$auction) {
    echo json_encode([
        'ok' => false,
        'msg' => 'Auction not found or you do not own this auction.'
    ]);
    exit;
}

if ($auction['status'] !== 'active') {
    echo json_encode([
        'ok' => false,
        'msg' => 'Only active auctions can be cancelled.'
    ]);
    exit;
}

// Check bid count
$stmt = $conn->prepare("
    SELECT COUNT(*) AS bid_count
    FROM bids
    WHERE auction_id = ?
");
$stmt->bind_param("i", $auction_id);
$stmt->execute();
$bid_count = (int)$stmt->get_result()->fetch_assoc()['bid_count'];

if ($bid_count > 0) {
    echo json_encode([
        'ok' => false,
        'msg' => 'This auction already has bids, so it cannot be cancelled.'
    ]);
    exit;
}

// Cancel auction
$stmt = $conn->prepare("
    UPDATE auctions
    SET status = 'cancelled'
    WHERE auction_id = ? AND seller_id = ? AND status = 'active'
");
$stmt->bind_param("ii", $auction_id, $seller_id);

if ($stmt->execute()) {
    echo json_encode([
        'ok' => true,
        'msg' => 'Auction cancelled successfully.'
    ]);
    exit;
}

echo json_encode([
    'ok' => false,
    'msg' => 'Failed to cancel auction.'
]);
exit;