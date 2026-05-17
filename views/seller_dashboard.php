<?php
if (!isset($listings)) {
    header('Location: ../controllers/SellerDashboardController.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Seller Dashboard</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
body { background: #f5f5f5; padding: 20px; }
.container { max-width: 1250px; margin: 0 auto; background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
h2 { text-align: center; margin-bottom: 20px; color:#31708f; }
a.post-new { display: inline-block; margin-bottom: 15px; color: #2b542c; font-weight: bold; text-decoration: none; }
a.post-new:hover { text-decoration: underline; }
.seller-links { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:15px; }
.status-active { color: green; font-weight: bold; }
.status-cancelled { color: red; font-weight: bold; }
.status-ended, .status-closed { color: #555; font-weight: bold; }
button { padding: 6px 12px; margin: 2px; border: none; border-radius: 4px; cursor: pointer; }
button.cancel { background: #dc3545; color: #fff; }
button.edit { background: #007BFF; color: #fff; }
button:disabled { background: #aaa; cursor: not-allowed; }
.countdown { font-weight: bold; }
.notice { color: #777; font-size: 13px; }
.result-note { font-size: 13px; line-height:1.4; }
.result-success { color:#2b542c; font-weight:700; }
.result-warning { color:#8a6d3b; font-weight:700; }
.result-danger { color:#a94442; font-weight:700; }
</style>
</head>
<body>

<div class="container">
    <h2>Seller Dashboard</h2>

    <div class="seller-links">
        <a href="ListingController.php?action=create" class="post-new">Post a New Auction</a>
        <a href="../views/home.php" class="btn-secondary small-btn">Home</a>
    </div>

    <table class="dashboard-table">
        <thead>
        <tr>
            <th>Title</th>
            <th>Category</th>
            <th>Start Price</th>
            <th>Current Price</th>
            <th>Bid Count</th>
            <th>Status</th>
            <th>Time Remaining</th>
            <th>Winner / Result</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($listings)): ?>
            <tr>
                <td colspan="9" style="text-align:center;">No auctions found.</td>
            </tr>
        <?php endif; ?>

        <?php foreach ($listings as $l): ?>
            <tr id="row-<?= (int)$l['auction_id'] ?>">
                <td><?= htmlspecialchars($l['title']) ?></td>
                <td><?= htmlspecialchars($l['category_name'] ?? 'Uncategorised') ?></td>
                <td><?= number_format((float)$l['starting_price'], 2) ?></td>
                <td><?= number_format((float)$l['current_price'], 2) ?></td>
                <td><?= (int)$l['bid_count'] ?></td>

                <td id="status-<?= (int)$l['auction_id'] ?>" class="<?= htmlspecialchars($l['status_class']) ?>">
                    <?= htmlspecialchars($l['status_label']) ?>
                </td>

                <td>
                    <?php if ($l['is_ended']): ?>
                        Ended
                    <?php elseif ($l['status'] === 'cancelled'): ?>
                        Cancelled
                    <?php else: ?>
                        <span class="countdown" data-end="<?= htmlspecialchars($l['end_time']) ?>"></span>
                    <?php endif; ?>
                </td>

                <td>
                    <?php if ($l['is_ended']): ?>
                        <?php if ($l['winning_bid_amount'] === null): ?>
                            <div class="result-note result-danger">No bids placed.</div>
                        <?php elseif (!empty($l['reserve_not_met'])): ?>
                            <div class="result-note result-warning">Reserve Not Met</div>
                            <small>Highest bid: <?= number_format((float)$l['winning_bid_amount'], 2) ?></small>
                        <?php else: ?>
                            <div class="result-note result-success"><?= htmlspecialchars($l['reserve_result']) ?></div>
                            <small>Winning bid: <?= number_format((float)$l['winning_bid_amount'], 2) ?></small><br>
                            <small>Winner: <?= htmlspecialchars($l['winner_name'] ?? 'Unknown') ?></small><br>
                            <small>Email: <?= htmlspecialchars($l['winner_email'] ?? 'Not available') ?></small>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="notice">Result available after auction ends.</span>
                    <?php endif; ?>
                </td>

                <td>
                    <?php if ($l['can_modify']): ?>
                        <a href="<?= htmlspecialchars($l['edit_url']) ?>">
                            <button type="button" class="edit">Edit</button>
                        </a>

                        <button type="button" class="cancel" data-auction-id="<?= (int)$l['auction_id'] ?>">
                            Cancel
                        </button>
                    <?php else: ?>
                        <span class="notice">Not editable/cancellable</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
// Student 2 requirement: client-side countdown timer
function startSellerCountdowns() {
    document.querySelectorAll('.countdown').forEach(function(el) {
        const endTime = new Date(String(el.dataset.end).replace(' ', 'T')).getTime();

        const tick = function() {
            const now = new Date().getTime();
            const diff = endTime - now;

            if (!endTime || diff <= 0) {
                el.innerHTML = 'Ended';
                return;
            }

            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hrs = Math.floor((diff / (1000 * 60 * 60)) % 24);
            const mins = Math.floor((diff / (1000 * 60)) % 60);
            const secs = Math.floor((diff / 1000) % 60);

            el.innerHTML = `${days}d ${hrs}h ${mins}m ${secs}s`;
        };

        tick();
        setInterval(tick, 1000);
    });
}
startSellerCountdowns();

// Student 2 requirement: cancel using AJAX
window.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.cancel').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const auctionId = this.dataset.auctionId;

            if (!confirm('Are you sure you want to cancel this auction?')) {
                return;
            }

            fetch('../api/listings_cancel.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'auction_id=' + encodeURIComponent(auctionId)
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.ok) {
                    const statusCell = document.getElementById('status-' + auctionId);
                    statusCell.textContent = 'Cancelled';
                    statusCell.className = 'status-cancelled';
                    btn.parentElement.innerHTML = '<span class="notice">Not editable/cancellable</span>';
                } else {
                    alert(data.msg);
                }
            })
            .catch(function() {
                alert('Something went wrong while cancelling the auction.');
            });
        });
    });
});
</script>

</body>
</html>
