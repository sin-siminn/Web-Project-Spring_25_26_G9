<?php
// Legacy helper/controller kept for compatibility. The current register page posts to register_controller.php.
session_start();
require_once __DIR__ . '/../config/db.php';

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: ../views/login.php');
        exit;
    }
}

if (isset($_POST['register'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $raw_password = $_POST['password'] ?? '';

    if ($name === '' || $email === '' || strlen($raw_password) < 8) {
        echo 'Name, valid email, and password of at least 8 characters are required.';
        exit;
    }

    $password = password_hash($raw_password, PASSWORD_BCRYPT);

    $stmt = $conn->prepare(
        "INSERT INTO users (name, email, phone, bio, password, role, seller_verified)
         VALUES (?, ?, ?, ?, ?, 'buyer', 0)"
    );
    $stmt->bind_param('sssss', $name, $email, $phone, $bio, $password);

    if ($stmt->execute()) {
        header('Location: ../views/login.php?success=registered');
        exit;
    }

    echo 'Error: ' . htmlspecialchars($conn->error);
}
?>
