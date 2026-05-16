<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Listing.php';

header('Content-Type: application/json');

if(!isset($_SESSION['user_id']) || $_SESSION['seller_verified'] != 1) {
    echo json_encode(['ok'=>false,'msg'=>'Unauthorized']); exit;
}

$listing_id = $_POST['listing_id'] ?? 0;
$listing = new Listing($conn);

$success = $listing->cancel($listing_id);
if($success) echo json_encode(['ok'=>true]);
else echo json_encode(['ok'=>false,'msg'=>'Cannot cancel: bids exist']);