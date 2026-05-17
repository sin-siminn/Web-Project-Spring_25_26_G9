<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/AuctionCloser.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'msg' => 'Admin access required.']);
    exit;
}

close_expired_auctions($conn);

$has_reserve = auction_column_exists($conn, 'auctions', 'reserve_price');
$has_winner_bid_id = auction_column_exists($conn, 'auctions', 'winner_bid_id');
$ended_statuses = auction_ended_status_sql();

function scalar_count(mysqli $conn, string $sql): int
{
    $result = $conn->query($sql);
    if (!$result) {
        return 0;
    }
    $row = $result->fetch_assoc();
    return (int)array_values($row)[0];
}

$total_active = scalar_count($conn, "SELECT COUNT(*) FROM auctions WHERE status = 'active' AND end_time > NOW()");
$total_ended = scalar_count($conn, "SELECT COUNT(*) FROM auctions WHERE status IN ($ended_statuses)");
$total_bids = scalar_count($conn, "SELECT COUNT(*) FROM bids");

$highest_sale = [
    'auction_id' => null,
    'title' => null,
    'amount' => 0,
    'winner_name' => null,
    'category_name' => null
];

$highest_bid_expr = "COALESCE(wb.amount, a.current_price)";
$highest_bid_id_subquery = "(
    SELECT b2.bid_id
    FROM bids b2
    WHERE b2.auction_id = a.auction_id
    ORDER BY b2.amount DESC, b2.created_at ASC, b2.bid_id ASC
    LIMIT 1
)";

$reserve_select = $has_reserve ? 'a.reserve_price' : 'NULL AS reserve_price';
$reserve_filter = $has_reserve ? 'AND (sale.reserve_price IS NULL OR sale.sale_amount >= sale.reserve_price)' : '';
$winner_join = $has_winner_bid_id
    ? "LEFT JOIN bids wb ON wb.bid_id = COALESCE(a.winner_bid_id, $highest_bid_id_subquery)"
    : "LEFT JOIN bids wb ON wb.bid_id = $highest_bid_id_subquery";
$winner_user_join = 'LEFT JOIN users winner ON winner.user_id = wb.buyer_id';

$sql = "\n    SELECT *\n    FROM (\n        SELECT\n            a.auction_id,\n            a.title,\n            c.name AS category_name,\n            winner.name AS winner_name,\n            $reserve_select,\n            $highest_bid_expr AS sale_amount\n        FROM auctions a\n        LEFT JOIN categories c ON c.id = a.category_id\n        $winner_join\n        $winner_user_join\n        WHERE a.status IN ($ended_statuses)\n          AND EXISTS (SELECT 1 FROM bids bcheck WHERE bcheck.auction_id = a.auction_id)\n    ) sale\n    WHERE sale.sale_amount IS NOT NULL\n    $reserve_filter\n    ORDER BY sale.sale_amount DESC\n    LIMIT 1\n";
$result = $conn->query($sql);
if ($result && ($row = $result->fetch_assoc())) {
    $highest_sale = [
        'auction_id' => (int)$row['auction_id'],
        'title' => $row['title'],
        'amount' => (float)$row['sale_amount'],
        'winner_name' => $row['winner_name'],
        'category_name' => $row['category_name']
    ];
}

$top_categories = [];
$sql = "\n    SELECT\n        COALESCE(c.name, 'Uncategorised') AS category_name,\n        COUNT(*) AS completed_count\n    FROM auctions a\n    LEFT JOIN categories c ON c.id = a.category_id\n    WHERE a.status IN ($ended_statuses)\n    GROUP BY a.category_id, c.name\n    ORDER BY completed_count DESC, category_name ASC\n    LIMIT 5\n";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $top_categories[] = [
            'category_name' => $row['category_name'],
            'completed_count' => (int)$row['completed_count']
        ];
    }
}

echo json_encode([
    'ok' => true,
    'stats' => [
        'total_active' => $total_active,
        'total_ended' => $total_ended,
        'total_bids' => $total_bids,
        'highest_sale' => $highest_sale,
        'top_categories' => $top_categories
    ]
]);
exit;
