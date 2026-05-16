<?php
session_start();
require_once('../config/db.php');

// Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// FETCH LATEST DATA FROM DB - This fixes the "Admin approved but I don't see it" issue
$uid = $_SESSION['user_id'];
$query = mysqli_query($conn, "SELECT role, seller_verified, seller_motivation FROM users WHERE user_id = $uid");
$latest = mysqli_fetch_assoc($query);

// Update the session with the latest values from the database
$_SESSION['role'] = $latest['role'];
$_SESSION['seller_verified'] = $latest['seller_verified'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Home - Auction Browse</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="main-content">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h1>
        <p>You are logged in as a <strong><?php echo $_SESSION['role']; ?></strong>.</p>
        
        <hr>

        <?php if ($_SESSION['seller_verified'] == 1): ?>
            <div style="background-color: #dff0d8; color: #3c763d; padding: 15px; border: 1px solid #d6e9c6; border-radius: 4px; margin-bottom: 20px;">
                <strong>Success!</strong> Your seller account is verified. 
                <a href="post_auction.php" style="color: #2b542c; font-weight: bold;">Post a New Auction</a>
            </div>

        <?php elseif (!empty($latest['seller_motivation']) && $_SESSION['seller_verified'] == 0): ?>
            <div style="background: #fcf8e3; padding: 15px; border: 1px solid #faebcc; color: #8a6d3b; border-radius: 4px; margin-bottom: 20px;">
                <strong>Notice:</strong> Your seller request is <strong>Pending Approval</strong> by the Admin.
            </div>

        <?php elseif ($_SESSION['role'] != 'admin'): ?>
            <div style="background: #d9edf7; padding: 15px; border: 1px solid #bce8f1; color: #31708f; border-radius: 4px; margin-bottom: 20px;">
                <strong>Notice:</strong> You are currently a Buyer. 
                <a href="become_seller.php" style="font-weight: bold;">Click here to apply to become a Seller.</a>
            </div>
        <?php endif; ?>

        <h3>Available Auctions</h3>
        <p>Auction items will appear here (Task 2).</p>
        
        <br>
        <a href="../controllers/logout.php">Logout</a>
    </div>
</body>
</html>