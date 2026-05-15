<?php
// controllers/AuthController.php
require_once '../config/db.php'; // Include the database connection [cite: 12]

if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $bio = $_POST['bio'];
    
    // Requirement 1.2.7: Hash password for security [cite: 13, 27]
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    
    // Requirement 1.2.6: Default registration as buyer (seller_verified = 0) [cite: 26]
    $role = 'buyer';
    $seller_verified = 0;

    try {
        // Requirement 1.1.4: Use PDO with prepared statements [cite: 14]
        $sql = "INSERT INTO users (name, email, password_hash, role, seller_verified, bio, phone) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$name, $email, $password, $role, $seller_verified, $bio, $phone])) {
            // Requirement 1.2.7: Redirect to login on success [cite: 27]
            header("Location: ../views/login.php?success=registered");
            exit();
        }
    } catch (PDOException $e) {
        // Requirement 1.1.5: Server-side validation/error handling [cite: 15]
        echo "Error: " . $e->getMessage();
    }
}
?>