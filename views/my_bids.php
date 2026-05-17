<?php
if (!isset($my_bids)) {
    header('Location: ../controllers/BuyerDashboardController.php?action=my-bids');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Bids</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="dashboard-container buyer-dashboard">
    <div class="dashboard-header">
        <div>
            <h2>My Bids</h2>
            <p>Track your highest bids, leading bids, and auction outcomes.</p>
        </div>
        <div class="dashboard-actions">
            <a href="BuyerDashboardController.php?action=browse" class="btn-secondary">Auction Browse</a>
            <a href="../views/home.php" class="btn-primary">Home</a>
        </div>
    </div>

    <div class="buyer-tabs">
        <a href="BuyerDashboardController.php?action=browse">Auction Browse</a>
        <a class="active" href="BuyerDashboardController.php?action=my-bids">My Bids</a>
    </div>

    <table class="dashboard-table my-bids-table">
        <thead>
            <tr>
                <th>Auction Title</th>
                <th>Category</th>
                <th>My Highest Bid</th>
                <th>Current Leading Bid</th>
                <th>Bid Count</th>
                <th>Status</th>
                <th>Time / Result</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($my_bids)): ?>
                <tr>
                    <td colspan="8" style="text-align:center;">You have not placed any bids yet.</td>
                </tr>
            <?php endif; ?>

            <?php foreach ($my_bids as $bid): ?>
                <tr>
                    <td><?= htmlspecialchars($bid['title']) ?></td>
                    <td><?= htmlspecialchars($bid['category_name'] ?? 'Uncategorised') ?></td>
                    <td><?= number_format((float)$bid['my_highest_bid'], 2) ?></td>
                    <td><?= number_format((float)$bid['current_price'], 2) ?></td>
                    <td><?= (int)$bid['bid_count'] ?></td>
                    <td><span class="status-badge <?= htmlspecialchars($bid['status_class']) ?>"><?= htmlspecialchars($bid['buyer_status']) ?></span></td>
                    <td>
                        <?php if ($bid['is_active_now']): ?>
                            <span class="countdown" data-end="<?= htmlspecialchars($bid['end_time']) ?>">Loading...</span>
                        <?php elseif (!empty($bid['reserve_not_met'])): ?>
                            <strong>Reserve Not Met</strong><br>
                            <small>The highest bid did not reach the reserve price.</small>
                        <?php elseif ($bid['is_winner']): ?>
                            <strong>🏆 You Won!</strong><br>
                            <small>Seller: <?= htmlspecialchars($bid['seller_name'] ?? 'Unknown') ?></small><br>
                            <small>Email: <?= htmlspecialchars($bid['seller_email'] ?? 'Not available') ?></small>
                        <?php else: ?>
                            Auction ended
                        <?php endif; ?>
                    </td>
                    <td>
                        <a class="btn-secondary small-btn" href="BuyerDashboardController.php?action=detail&id=<?= (int)$bid['auction_id'] ?>">View</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script src="../assets/js/buyer_dashboard.js"></script>
</body>
</html>
