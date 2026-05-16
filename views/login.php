<!DOCTYPE html>
<html>
<head>
    <title>Login - Online Auction </title>
</head>
<body>
    <h2>Login</h2>
    <?php if(isset($_GET['success'])) echo "<p style='color:green;'>Registration successful! Please login.</p>"; ?>
    
    <form action="../controllers/AuthController.php" method="POST">
        <input type="email" name="email" placeholder="Email" required><br><br>
        <input type="password" name="password" placeholder="Password" required><br><br>
        <button type="submit" name="login">Login</button>
    </form>
</body>
</html>