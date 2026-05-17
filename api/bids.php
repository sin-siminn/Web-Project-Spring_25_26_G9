<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/AuctionCloser.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'msg' => 'Please log in first.']);
    exit;
}

// Student 4: close expired auctions before processing auction data.
close_expired_auctions($conn);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'msg' => 'Invalid request method.']);
    exit;
}

$input = $_POST;
if (empty($input)) {
    $raw = file_get_contents('php://input');
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        $input = $decoded;
    }
}

$buyer_id = (int)$_SESSION['user_id'];
$auction_id = (int)($input['auction_id'] ?? $input['listing_id'] ?? 0);
$amount_raw = trim((string)($input['amount'] ?? ''));
$amount = is_numeric($amount_raw) ? (float)$amount_raw : 0;

if ($auction_id <= 0) {
    echo json_encode(['ok' => false, 'msg' => 'Invalid auction ID.']);
    exit;
}

if ($amount <= 0) {
    echo json_encode(['ok' => false, 'msg' => 'Bid amount must be a positive number.']);
    exit;
}

try {
    $conn->begin_transaction();

    $stmt = $conn->prepare("\n        SELECT auction_id, seller_id, title, current_price, end_time, status\n        FROM auctions\n        WHERE auction_id = ?\n        LIMIT 1\n        FOR UPDATE\n    ");
    $stmt->bind_param('i', $auction_id);
    $stmt->execute();
    $auction = $stmt->get_result()->fetch_assoc();

    if (!$auction) {
        $conn->rollback();
        echo json_encode(['ok' => false, 'msg' => 'Auction not found.']);
        exit;
    }

    if ($auction['status'] !== 'active') {
        $conn->rollback();
        echo json_encode(['ok' => false, 'msg' => 'This auction is no longer active.']);
        exit;
    }

    if (strtotime($auction['end_time']) <= time()) {
        $conn->rollback();
        echo json_encode(['ok' => false, 'msg' => 'This auction has already expired.']);
        exit;
    }

    if ((int)$auction['seller_id'] === $buyer_id) {
        $conn->rollback();
        echo json_encode(['ok' => false, 'msg' => 'You cannot bid on your own auction.']);
        exit;
    }

    if ($amount <= (float)$auction['current_price']) {
        $conn->rollback();
        echo json_encode([
            'ok' => false,
            'msg' => 'Your bid must be higher than the current bid of ' . number_format((float)$auction['current_price'], 2) . '.'
        ]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO bids (auction_id, buyer_id, amount, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param('iid', $auction_id, $buyer_id, $amount);
    $stmt->execute();
    $bid_id = (int)$conn->insert_id;

    $stmt = $conn->prepare("UPDATE auctions SET current_price = ? WHERE auction_id = ?");
    $stmt->bind_param('di', $amount, $auction_id);
    $stmt->execute();

    $stmt = $conn->prepare("SELECT COUNT(*) AS bid_count FROM bids WHERE auction_id = ?");
    $stmt->bind_param('i', $auction_id);
    $stmt->execute();
    $bid_count = (int)$stmt->get_result()->fetch_assoc()['bid_count'];

    $stmt = $conn->prepare("SELECT name FROM users WHERE user_id = ? LIMIT 1");
    $stmt->bind_param('i', $buyer_id);
    $stmt->execute();
    $buyer = $stmt->get_result()->fetch_assoc();

    $conn->commit();

    echo json_encode([
        'ok' => true,
        'msg' => 'Bid placed successfully.',
        'bid_id' => $bid_id,
        'new_bid' => $amount,
        'bid_count' => $bid_count,
        'bid' => [
            'bidder_name' => $buyer['name'] ?? 'You',
            'amount' => $amount,
            'created_at' => date('Y-m-d H:i:s')
        ]
    ]);
    exit;
} catch (Throwable $e) {
    if ($conn->errno === 0) {
        // no-op; rollback below is still safe if transaction is open
    }
    @$conn->rollback();
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'msg' => 'Something went wrong while placing the bid.'
    ]);
    exit;
}
