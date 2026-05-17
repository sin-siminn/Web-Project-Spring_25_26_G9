<h1><?= htmlspecialchars($listing['title']) ?></h1>
<p><strong>Description:</strong> <?= nl2br(htmlspecialchars($listing['description'])) ?></p>
<p><strong>Seller:</strong> <?= htmlspecialchars($seller['name']) ?> (<?= htmlspecialchars($seller['email']) ?>)</p>
<p><strong>Current Bid:</strong> $<span id="current-bid-<?= $listing['id'] ?>"><?= $listing['current_bid'] ?></span></p>
<p><strong>Bids Count:</strong> <span id="bid-count-<?= $listing['id'] ?>"><?= count($bid_history) ?></span></p>
<p><strong>Ends In:</strong> <span class="countdown" data-end="<?= $listing['end_datetime'] ?>"></span></p>

<?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $listing['seller_id'] && $listing['status'] === 'active'): ?>
<form class="place-bid-form" data-listing-id="<?= $listing['id'] ?>">
    <input type="number" name="amount" step="0.01" min="<?= $listing['current_bid'] + 0.01 ?>" required />
    <button type="submit">Place Bid</button>
    <div class="bid-message"></div>
</form>
<?php elseif ($listing['status'] !== 'active'): ?>
<p><strong>Auction Ended</strong></p>
<?php endif; ?>

<h2>Bid History (Last 10)</h2>
<table>
    <tr>
        <th>Bidder</th>
        <th>Amount</th>
        <th>Time</th>
    </tr>
    <?php foreach ($bid_history as $b): ?>
    <tr>
        <td><?= htmlspecialchars($b['buyer_name']) ?></td>
        <td>$<?= $b['amount'] ?></td>
        <td><?= $b['created_at'] ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<script src="/js/bids.js"></script>
<script src="/js/countdown.js"></script>