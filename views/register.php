<!DOCTYPE html>
<html>
<head>
    <title>Registration</title>
</head>
<body>
    <h2>Sign Up</h2>
    <form action="../controllers/AuthController.php" method="POST">
        <input type="text" name="name" placeholder="Full Name" required><br><br>
        <input type="email" name="email" placeholder="Email" required><br><br>
        <input type="text" name="phone" placeholder="Phone Number" required><br><br>
        <textarea name="bio" placeholder="Tell us about yourself"></textarea><br><br>
        
        <input type="password" name="password" placeholder="Password (min 8 chars)" minlength="8" required><br><br>
        
        <button type="submit" name="register">Register</button>
    </form>
</body>
</html>