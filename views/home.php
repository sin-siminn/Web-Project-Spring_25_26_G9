<?php
session_start();
require_once('../config/db.php');

// Security check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch latest session info
$uid = $_SESSION['user_id'];
$query = mysqli_query($conn, "SELECT role, seller_verified, seller_motivation FROM users WHERE user_id = $uid");
$latest = mysqli_fetch_assoc($query);

$_SESSION['role'] = $latest['role'];
$_SESSION['seller_verified'] = $latest['seller_verified'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .banner {
            background-color: #dff0d8;
            color: #3c763d;
            padding: 15px;
            border: 1px solid #d6e9c6;
            border-radius: 4px;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .banner a {
            margin-left: 20px;
            text-decoration: none;
            font-weight: bold;
            color: #2b542c;
        }
        .banner a:hover { text-decoration: underline; }
        .logout-btn {
            display:inline-block;
            background:#e74c3c;
            color:#fff;
            padding:10px 20px;
            border-radius:5px;
            text-decoration:none;
            font-weight:bold;
        }
        .logout-btn:hover { background:#c0392b; }
    </style>
</head>
<body>
<div class="main-content">
    <h1>Welcome, <?= htmlspecialchars($_SESSION['name']); ?>!</h1>
    <p>You are logged in as a <strong><?= $_SESSION['role']; ?></strong>.</p>
    
    <hr>

    <?php if ($_SESSION['seller_verified'] == 1): ?>
        <div class="banner">
            Success! Your seller account is verified.
            <a href="../controllers/ListingController.php?action=create">Post a New Auction</a>
            <a href="../controllers/SellerDashboardController.php">Go to Seller Dashboard</a>
            <a href="../controllers/BuyerDashboardController.php?action=start">Start Bidding</a>
        </div>

    <?php elseif (!empty($latest['seller_motivation']) && $_SESSION['seller_verified'] == 0): ?>
        <div class="banner" style="background:#fcf8e3;color:#8a6d3b;border:1px solid #faebcc;">
            Notice: Your seller request is <strong>Pending Approval</strong> by the Admin.
            <a href="../controllers/BuyerDashboardController.php?action=start">Start Bidding</a>
        </div>

    <?php elseif ($_SESSION['role'] != 'admin'): ?>
        <div class="banner" style="background:#d9edf7;color:#31708f;border:1px solid #bce8f1;">
            Info: You are currently a Buyer.
            <a href="become_seller.php">Request to Become Seller</a>
            <a href="../controllers/BuyerDashboardController.php?action=start">Start Bidding</a>
        </div>
    <?php endif; ?>

    <br>
    <a href="../controllers/logout.php" class="logout-btn">Logout</a>
</div>
</body>
</html>