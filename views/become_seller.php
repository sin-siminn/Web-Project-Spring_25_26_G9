<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
?>

<!DOCTYPE html>
<html>
<head>
    <title>Become a Seller</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="main-content">
        <h1>Become a Seller</h1>
        <p>Tell the admin why you want to sell items on our platform.</p>
        <hr>

        <form action="../controllers/seller_request_controller.php" method="POST" class="login-form">
            <div class="form-group">
                <label>Motivation</label>
                <textarea name="motivation" required placeholder="I want to sell my electronics..." 
                          style="width: 300px; height: 100px; padding: 10px;"></textarea>
            </div>
            
            <button type="submit" class="btn-login">Submit Request</button>
        </form>
        
        <p style="margin-left: 170px; margin-top: 15px;">
            <a href="home.php">Back to Home</a>
        </p>
    </div>
</body>
</html>