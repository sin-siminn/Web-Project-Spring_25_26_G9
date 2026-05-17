<?php
<<<<<<< HEAD
// controllers/AuthController.php
require_once '../config/db.php'; // Database connection
=======
// Legacy registration controller. The register page currently posts to register_controller.php,
// but this file is kept working in case it is used directly.
require_once __DIR__ . '/../config/db.php';
>>>>>>> e79faca14239cbb5e665b3275454d0b2b75a3f0d

session_start();

/**
 * Check if a user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Require a user to be logged in; redirect to login if not
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: ../views/login.php");
        exit();
    }
}

/**
 * Handle registration form submission
 */
if (isset($_POST['register'])) {
<<<<<<< HEAD
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $bio = $_POST['bio'];
    
    // Hash password for security
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    
    // Default registration as buyer (seller_verified = 0)
    $role = 'buyer';
    $seller_verified = 0;

    try {
        // Use PDO with prepared statements
        $sql = "INSERT INTO users (name, email, password_hash, role, seller_verified, bio, phone) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$name, $email, $password, $role, $seller_verified, $bio, $phone])) {
            header("Location: ../views/login.php?success=registered");
            exit();
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
=======
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
>>>>>>> e79faca14239cbb5e665b3275454d0b2b75a3f0d
    }

    echo 'Error: ' . $conn->error;
}
?>
