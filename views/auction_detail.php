<?php
if (!isset($auction)) {
    header('Location: ../controllers/BuyerDashboardController.php?action=browse');
    exit;
}
$can_bid = !$auction_ended && (int)$auction['seller_id'] !== (int)$_SESSION['user_id'];
$is_own_auction = (int)$auction['seller_id'] === (int)$_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Auction Details</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="dashboard-container buyer-dashboard">
    <div class="dashboard-header">
        <div>
            <h2>Auction Details</h2>
            <p>Review the auction, check recent bids, and place a valid higher bid.</p>
        </div>
        <div class="dashboard-actions">
            <a href="BuyerDashboardController.php?action=browse" class="btn-secondary">Back to Browse</a>
            <a href="BuyerDashboardController.php?action=my-bids" class="btn-primary">My Bids</a>
        </div>
    </div>

    <div class="detail-layout">
        <div class="detail-main">
            <div class="detail-thumb placeholder-thumb large-thumb">No Image</div>
            <span class="category-chip"><?= htmlspecialchars($auction['category_name'] ?? 'Uncategorised') ?></span>
            <h1><?= htmlspecialchars($auction['title']) ?></h1>
            <p class="detail-description"><?= nl2br(htmlspecialchars($auction['description'] ?? 'No description available.')) ?></p>
        </div>

        <aside class="bid-panel">
            <h3>Bid Summary</h3>
            <div class="summary-line">
                <span>Seller</span>
                <strong><?= htmlspecialchars($auction['seller_name'] ?? 'Unknown') ?></strong>
            </div>
            <div class="summary-line">
                <span>Starting Price</span>
                <strong><?= number_format((float)$auction['starting_price'], 2) ?></strong>
            </div>
            <div class="summary-line highlight">
                <span>Current Highest Bid</span>
                <strong id="currentBid"><?= number_format((float)$auction['current_price'], 2) ?></strong>
            </div>
            <div class="summary-line">
                <span>Bid Count</span>
                <strong id="bidCount"><?= (int)$auction['bid_count'] ?></strong>
            </div>
            <div class="countdown detail-countdown" data-end="<?= htmlspecialchars($auction['end_time']) ?>">Loading...</div>

            <?php if ($auction_ended): ?>
                <div class="result-box">
                    <?php if ($reserve_not_met): ?>
                        <strong>Reserve Not Met</strong>
                        <p>The highest bid did not reach the reserve price.</p>
                    <?php elseif ($winner_bid): ?>
                        <strong>Winner: <?= htmlspecialchars($winner_bid['winner_name']) ?></strong>
                        <p>Winning bid: <?= number_format((float)$winner_bid['amount'], 2) ?></p>
                    <?php else: ?>
                        <strong>Auction Ended</strong>
                        <p>No bids were placed for this auction.</p>
                    <?php endif; ?>
                </div>
            <?php elseif ($is_own_auction): ?>
                <div class="error-message">You cannot bid on your own auction.</div>
            <?php else: ?>
                <form id="bidForm" class="bid-form" data-auction-id="<?= (int)$auction['auction_id'] ?>">
                    <label for="bidAmount">Your Bid Amount</label>
                    <input
                        type="number"
                        step="0.01"
                        min="<?= htmlspecialchars((string)((float)$auction['current_price'] + 0.01)) ?>"
                        id="bidAmount"
                        name="amount"
                        placeholder="Enter amount higher than current bid"
                        required
                    >
                    <button type="submit" class="btn-primary full-width">Place Bid</button>
                    <div id="bidMessage" class="ajax-message" style="display:none;"></div>
                </form>
            <?php endif; ?>
        </aside>
    </div>

    <div class="history-card">
        <h3>Last 10 Bids</h3>
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>Bidder</th>
                    <th>Amount</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody id="bidHistoryBody">
                <?php if (empty($recent_bids)): ?>
                    <tr class="empty-row">
                        <td colspan="3">No bids yet.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($recent_bids as $bid): ?>
                    <tr>
                        <td><?= htmlspecialchars($bid['bidder_name']) ?></td>
                        <td><?= number_format((float)$bid['amount'], 2) ?></td>
                        <td><?= htmlspecialchars($bid['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="../assets/js/buyer_dashboard.js"></script>
</body>
</html>
