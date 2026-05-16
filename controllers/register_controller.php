<?php
require_once('../config/db.php'); // Make sure your db connection file exists

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $bio = $_POST['bio'];
    
    // Requirement 1: Hash password (at least 8 chars)
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Requirement 1: role='buyer' and seller_verified=0 are handled by DB defaults, 
    // but we can be explicit here:
    $sql = "INSERT INTO users (name, email, phone, bio, password, role, seller_verified) 
            VALUES ('$name', '$email', '$phone', '$bio', '$password', 'buyer', 0)";

    if (mysqli_query($conn, $sql)) {
        // Requirement 1: Redirect to login on success
        header("Location: ../views/login.php?success=registered");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>