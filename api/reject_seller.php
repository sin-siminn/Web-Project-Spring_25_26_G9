<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Only admins should be able to reject seller requests.
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo 'unauthorized';
    exit;
}

if (isset($_POST['user_id'])) {
    $uid = (int)$_POST['user_id'];
    $stmt = $conn->prepare("UPDATE users SET seller_verified = 0, seller_motivation = NULL WHERE user_id = ?");
    $stmt->bind_param('i', $uid);
    echo $stmt->execute() ? 'success' : 'error';
}
?>
