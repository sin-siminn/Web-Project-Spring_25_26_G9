<?php
// Legacy registration controller. The register page currently posts to register_controller.php,
// but this file is kept working in case it is used directly.
require_once __DIR__ . '/../config/db.php';

if (isset($_POST['register'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name'] ?? '');
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
    $phone = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
    $bio = mysqli_real_escape_string($conn, $_POST['bio'] ?? '');
    $password = password_hash($_POST['password'] ?? '', PASSWORD_BCRYPT);

    $stmt = $conn->prepare(
        "INSERT INTO users (name, email, phone, bio, password, role, seller_verified)
         VALUES (?, ?, ?, ?, ?, 'buyer', 0)"
    );
    $stmt->bind_param('sssss', $name, $email, $phone, $bio, $password);

    if ($stmt->execute()) {
        header('Location: ../views/login.php?success=registered');
        exit;
    }

    echo 'Error: ' . $conn->error;
}
?>
