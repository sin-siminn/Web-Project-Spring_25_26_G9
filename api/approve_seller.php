<?php
require_once('../config/db.php');

if (isset($_POST['user_id'])) {
    $uid = $_POST['user_id'];
    // Requirement 4: Set seller_verified = 1
    $sql = "UPDATE users SET seller_verified = 1 WHERE user_id = $uid";
    mysqli_query($conn, $sql);
    echo "success";
}
?>