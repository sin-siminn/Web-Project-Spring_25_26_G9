<!DOCTYPE html>
<html>
<head>
<<<<<<< HEAD
    <title>Login - Online Auction </title>
=======
    <title>Log In - Auction System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
>>>>>>> 97f10b6b9e2aa698dcfac697e85740b2f4457ce8
</head>
<body>
    <div class="main-content">
        <h1>Log in.</h1>
        <p>Use a local account to log in.</p>
        <hr>

        <form action="../controllers/login_controller.php" method="POST" class="login-form">
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <div class="form-group checkbox-group">
                <input type="checkbox" name="remember" id="remember">
                <label for="remember">Remember me?</label>
            </div>

            <button type="submit" class="btn-login">Log in</button>
        </form>

        <p style="margin-left: 170px; margin-top: 15px;">
            New user? <a href="register.php">Register here</a>
        </p>
    </div>
</body>
</html>