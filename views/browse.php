<h1>Active Auctions</h1>

<div id="listings-container">
<?php if (!empty($listings)): ?>
    <?php foreach ($listings as $l): ?>
        <div class="listing-card" id="listing-<?= $l['auction_id'] ?>" style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
            <img src="/uploads/listings/<?= htmlspecialchars($l['image_path'] ?? '') ?>" width="150" alt="Listing Image"/>
            <h3><?= htmlspecialchars($l['title']) ?></h3>
            <p>Current Bid: $<span id="current-bid-<?= $l['auction_id'] ?>"><?= $l['current_price'] ?></span></p>
            <p>Bids Count: <span id="bid-count-<?= $l['auction_id'] ?>"><?= $bidModel->countByListing($l['auction_id']) ?></span></p>

            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $l['seller_id'] && $l['status'] === 'active'): ?>
            <form class="place-bid-form" data-listing-id="<?= $l['auction_id'] ?>">
                <input type="number" name="amount" step="0.01" min="<?= $l['current_price'] + 0.01 ?>" required />
                <button type="submit">Place Bid</button>
                <div class="bid-message"></div>
            </form>
            <?php endif; ?>

            <div class="countdown" data-end="<?= $l['end_time'] ?>"></div>
            <a href="/controllers/ListingsController.php?action=detail&id=<?= $l['auction_id'] ?>">View Details</a>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>No active auctions found.</p>
<?php endif; ?>
</div>

<script src="/js/bids.js"></script>
<script src="/js/countdown.js"></script>