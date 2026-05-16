<?php
session_start();
require_once('../config/db.php');

// Security: Only admins can see this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: home.php");
    exit();
}

// Fetch all users who want to be sellers
$sql = "SELECT user_id, name, email, seller_motivation FROM users WHERE seller_verified = 0 AND seller_motivation IS NOT NULL";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel - Seller Requests</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="main-content">
        <h1>Admin Panel</h1>
        <p class="subtitle">Manage pending seller verification requests.</p>
        <hr>

        <table border="1" style="width: 100%; border-collapse: collapse; margin-top: 20px;">
            <tr style="background: #f2f2f2;">
                <th>Name</th>
                <th>Email</th>
                <th>Motivation</th>
                <th>Action</th>
            </tr>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
            <tr id="row-<?php echo $row['user_id']; ?>">
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['email']; ?></td>
                <td><?php echo $row['seller_motivation']; ?></td>
                <td>
                    <button onclick="approveSeller(<?php echo $row['user_id']; ?>)" style="color: green;">Approve</button>
                    <button onclick="rejectSeller(<?php echo $row['user_id']; ?>)" style="color: red;">Reject</button>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <script>
    function approveSeller(userId) {
        fetch('../api/approve_seller.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'user_id=' + userId
        })
        .then(response => response.text())
        .then(data => {
            document.getElementById('row-' + userId).innerHTML = "<td colspan='4' style='text-align:center; color:green;'>Approved ✅</td>";
        });
    }

    function rejectSeller(userId) {
        if(confirm('Are you sure you want to reject this seller?')) {
            fetch('../api/reject_seller.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'user_id=' + userId
            })
            .then(response => response.text())
            .then(data => {
                document.getElementById('row-' + userId).remove();
            });
        }
    }
    </script>
</body>
</html>