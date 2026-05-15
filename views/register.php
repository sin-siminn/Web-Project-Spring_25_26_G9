<!DOCTYPE html>
<html>
<head>
    <title>Register - Auction System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="main-content">
        <h1>Register</h1>
        <p class="subtitle">Create a new account to start bidding.</p>
        <hr>

        <form action="../controllers/register_controller.php" method="POST" class="login-form">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>

            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" required>
            </div>

            <div class="form-group">
                <label>Short Bio</label>
                <textarea name="bio" style="width: 300px; padding: 8px; border-radius: 4px; border: 1px solid #ccc;"></textarea>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" minlength="8" required>
            </div>

            <button type="submit" class="btn-login">Register</button>
        </form>
        <p style="margin-left: 170px; margin-top: 15px;">
            Already have an account? <a href="login.php">Login here</a>
        </p>
    </div>
</body>
</html>