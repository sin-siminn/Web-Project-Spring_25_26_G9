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

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'msg' => 'Invalid request method.']);
    exit;
}

$category_id = isset($_GET['category_id']) && $_GET['category_id'] !== '' ? (int)$_GET['category_id'] : 0;
$q = trim($_GET['q'] ?? '');

$sql = "
    SELECT
        a.auction_id,
        a.seller_id,
        a.title,
        a.description,
        a.starting_price,
        a.current_price,
        a.end_time,
        a.status,
        a.category_id,
        c.name AS category_name,
        u.name AS seller_name,
        NULL AS image_path,
        (SELECT COUNT(*) FROM bids b WHERE b.auction_id = a.auction_id) AS bid_count
    FROM auctions a
    LEFT JOIN categories c ON c.id = a.category_id
    LEFT JOIN users u ON u.user_id = a.seller_id
    WHERE a.status = 'active'
      AND a.end_time > NOW()
";

$params = [];
$types = '';

if ($category_id > 0) {
    $sql .= " AND a.category_id = ?";
    $params[] = $category_id;
    $types .= 'i';
}

if ($q !== '') {
    $sql .= " AND a.title LIKE ?";
    $params[] = '%' . $q . '%';
    $types .= 's';
}

$sql .= " ORDER BY a.end_time ASC, a.created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$auctions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

foreach ($auctions as &$auction) {
    $auction['auction_id'] = (int)$auction['auction_id'];
    $auction['seller_id'] = (int)$auction['seller_id'];
    $auction['category_id'] = $auction['category_id'] !== null ? (int)$auction['category_id'] : null;
    $auction['starting_price'] = (float)$auction['starting_price'];
    $auction['current_price'] = (float)$auction['current_price'];
    $auction['bid_count'] = (int)$auction['bid_count'];
}
unset($auction);

echo json_encode([
    'ok' => true,
    'auctions' => $auctions
]);
exit;
