<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Only logged-in verified sellers can access the seller dashboard.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['seller_verified']) || (int)$_SESSION['seller_verified'] !== 1) {
    header('Location: ../views/login.php');
    exit;
}

$seller_id = (int)$_SESSION['user_id'];

$stmt = $conn->prepare(
    "SELECT a.*, c.name AS category_name,
            (SELECT COUNT(*) FROM bids b WHERE b.auction_id = a.auction_id) AS bid_count
     FROM auctions a
     LEFT JOIN categories c ON a.category_id = c.id
     WHERE a.seller_id = ?
     ORDER BY a.created_at DESC"
);

$stmt->bind_param('i', $seller_id);
$stmt->execute();
$listings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/../views/seller_dashboard.php';
