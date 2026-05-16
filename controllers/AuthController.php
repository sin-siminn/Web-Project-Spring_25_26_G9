<?php
// controllers/AuthController.php
require_once '../config/db.php'; // Database connection

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
    }
}
?>