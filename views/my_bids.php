<h1>My Bids</h1>
<table>
    <tr>
        <th>Auction Title</th>
        <th>My Bid</th>
        <th>Current Bid</th>
        <th>Status</th>
    </tr>
    <?php foreach($bids as $b): ?>
    <tr>
        <td><?= htmlspecialchars($b['title']) ?></td>
        <td>$<?= $b['my_bid'] ?></td>
        <td>$<?= $b['current_bid'] ?></td>
        <td><?= $b['status_badge'] ?></td>
    </tr>
    <?php endforeach; ?>
</table>