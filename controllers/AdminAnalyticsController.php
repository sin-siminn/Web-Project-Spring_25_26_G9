<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/AuctionCloser.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../views/login.php');
    exit;
}

// Student 4 requirement: closing service runs before admin stats are displayed.
close_expired_auctions($conn);

include __DIR__ . '/../views/admin_analytics.php';
