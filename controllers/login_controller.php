<?php
session_start();
require_once('../config/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);

    if ($user = mysqli_fetch_assoc($result)) {
        // Verify the hashed password
        if (password_verify($password, $user['password'])) {
            
            // REQUIREMENT: Set these specific session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['seller_verified'] = $user['seller_verified'];
            $_SESSION['role'] = $user['role'];

            // REQUIREMENT: Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: ../views/admin_panel.php");
            } else {
                header("Location: ../views/home.php");
            }
            exit();
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "No user found with that email.";
    }
}
?>