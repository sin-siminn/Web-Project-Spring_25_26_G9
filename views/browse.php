<?php
$listings = Listing::active($_GET['category_id'] ?? null, $_GET['q'] ?? null);
?>
<h1>Active Auctions</h1>
<div id="listings-container">
<?php foreach($listings as $l): ?>
    <div class="listing-card" id="listing-<?= $l['id'] ?>">
        <img src="/uploads/listings/<?= $l['image_path'] ?>" width="150" />
        <h3><?= htmlspecialchars($l['title']) ?></h3>
        <p>Current Bid: $<span id="current-bid-<?= $l['id'] ?>"><?= $l['current_bid'] ?></span></p>
        <p>Bids Count: <span id="bid-count-<?= $l['id'] ?>"><?= Bid::countByListing($l['id']) ?></span></p>

        <form class="place-bid-form" data-listing-id="<?= $l['id'] ?>">
            <input type="number" name="amount" step="0.01" min="<?= $l['current_bid']+0.01 ?>" required />
            <button type="submit">Place Bid</button>
            <div class="bid-message"></div>
        </form>

        <div class="countdown" data-end="<?= $l['end_datetime'] ?>"></div>
    </div>
<?php endforeach; ?>
<script src="/js/bids.js"></script>
<script src="/js/countdown.js"></script>