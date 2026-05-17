<h1>My Bids</h1>

<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <th>Auction Title</th>
        <th>My Bid</th>
        <th>Current Bid</th>
        <th>Status</th>
    </tr>

<?php if (!empty($bids) && is_array($bids)): ?>
    <?php foreach ($bids as $b): ?>
        <tr>
            <td><?= htmlspecialchars($b['title']) ?></td>
            <td>$<?= $b['my_bid'] ?></td>
            <td>$<?= $b['current_price'] ?></td>
            <td><?= $b['status_badge'] ?></td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="4">You have not placed any bids yet.</td>
    </tr>
<?php endif; ?>
</table>