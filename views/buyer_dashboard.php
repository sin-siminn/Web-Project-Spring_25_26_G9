<?php
if (!isset($auctions) || !isset($categories)) {
    header('Location: ../controllers/BuyerDashboardController.php?action=browse');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Buyer Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="dashboard-container buyer-dashboard">
    <div class="dashboard-header">
        <div>
            <h2>Buyer Dashboard</h2>
            <p>Browse active auctions, search listings, and place bids without page reloads.</p>
        </div>
        <div class="dashboard-actions">
            <a href="../views/home.php" class="btn-secondary">Home</a>
            <a href="BuyerDashboardController.php?action=my-bids" class="btn-primary">My Bids</a>
        </div>
    </div>

    <div class="summary-grid">
        <div class="summary-card">
            <span>Active Auctions</span>
            <strong><?= (int)$active_total ?></strong>
        </div>
        <div class="summary-card">
            <span>Auctions I Bid On</span>
            <strong><?= (int)$my_bid_total ?></strong>
        </div>
    </div>

    <div class="buyer-tabs">
        <a class="active" href="BuyerDashboardController.php?action=browse">Auction Browse</a>
        <a href="BuyerDashboardController.php?action=my-bids">My Bids</a>
    </div>

    <div class="filter-panel">
        <div class="filter-group">
            <label for="categoryFilter">Category</label>
            <select id="categoryFilter">
                <option value="">All categories</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= (int)$category['id'] ?>">
                        <?= htmlspecialchars($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group search-group">
            <label for="auctionSearch">Keyword Search</label>
            <input type="text" id="auctionSearch" placeholder="Search auction title...">
        </div>
    </div>

    <div id="ajaxMessage" class="ajax-message" style="display:none;"></div>

    <div
        id="auctionGrid"
        class="auction-grid"
        data-api-url="../api/listings.php"
        data-detail-url="BuyerDashboardController.php?action=detail&id="
    >
        <?php if (empty($auctions)): ?>
            <div class="empty-state">No active auctions are available right now.</div>
        <?php endif; ?>

        <?php foreach ($auctions as $auction): ?>
            <div class="auction-card">
                <div class="auction-thumb placeholder-thumb">No Image</div>
                <div class="auction-card-body">
                    <span class="category-chip"><?= htmlspecialchars($auction['category_name'] ?? 'Uncategorised') ?></span>
                    <h3><?= htmlspecialchars($auction['title']) ?></h3>
                    <p class="seller-line">Seller: <?= htmlspecialchars($auction['seller_name'] ?? 'Unknown') ?></p>
                    <div class="card-meta">
                        <span>Current Bid</span>
                        <strong><?= number_format((float)$auction['current_price'], 2) ?></strong>
                    </div>
                    <div class="card-meta small-meta">
                        <span>Bid Count</span>
                        <strong><?= (int)$auction['bid_count'] ?></strong>
                    </div>
                    <div class="countdown" data-end="<?= htmlspecialchars($auction['end_time']) ?>">Loading...</div>
                    <a class="btn-primary full-width" href="BuyerDashboardController.php?action=detail&id=<?= (int)$auction['auction_id'] ?>">View &amp; Bid</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script src="../assets/js/buyer_dashboard.js"></script>
</body>
</html>
