<?php
require '../config/db.php';
require '../controllers/AuthController.php';
session_start();
requireLogin();

$user_id = $_SESSION['user_id'];

$bids = $conn->query("
SELECT l.id, l.title, l.current_bid, l.end_datetime, MAX(b.amount) AS my_bid
FROM bids b
JOIN listings l ON b.listing_id=l.id
WHERE b.buyer_id=$user_id
GROUP BY l.id
")->fetch_all(MYSQLI_ASSOC);
?>

<table>
<tr><th>Auction</th><th>My Bid</th><th>Current Bid</th><th>Status</th></tr>
<?php foreach($bids as $b): 
    $status = '';
    if(strtotime($b['end_datetime']) > time()){
        $status = ($b['my_bid'] == $b['current_bid']) ? 'Leading' : 'Outbid';
    } else {
        $status = ($b['my_bid'] == $b['current_bid']) ? 'Won' : 'Lost';
    }
?>
<tr>
<td><?= $b['title'] ?></td>
<td><?= $b['my_bid'] ?></td>
<td><?= $b['current_bid'] ?></td>
<td><?= $status ?></td>
</tr>
<?php endforeach; ?>
</table>