<?php
session_start();
require_once('../config/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $motivation = mysqli_real_escape_string($conn, $_POST['motivation']);

    // Requirement 3: PHP sets seller_verified = 0 and stores the request
    $sql = "UPDATE users SET seller_motivation = '$motivation', seller_verified = 0 WHERE user_id = $uid";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Request submitted! Wait for admin approval.'); window.location.href='../views/home.php';</script>";
    } else {
        echo "Error updating record: " . mysqli_error($conn);
    }
}
?>