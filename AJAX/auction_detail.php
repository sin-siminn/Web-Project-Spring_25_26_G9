<?php
require '../config/db.php';
require '../controllers/AuthController.php';
session_start();
requireLogin();

$listing_id = intval($_GET['id']);
$listing = $conn->query("SELECT l.*, u.username AS seller_name 
                         FROM listings l 
                         JOIN users u ON l.seller_id = u.id
                         WHERE l.id=$listing_id")->fetch_assoc();

$bids = $conn->query("SELECT b.amount, b.bid_time, u.username
                      FROM bids b
                      JOIN users u ON b.buyer_id = u.id
                      WHERE listing_id=$listing_id
                      ORDER BY b.bid_time DESC
                      LIMIT 10")->fetch_all(MYSQLI_ASSOC);
?>

<h1><?= $listing['title'] ?></h1>
<p><?= $listing['description'] ?></p>
<p>Seller: <?= $listing['seller_name'] ?></p>
<p>Current Bid: $<span id="currentBid"><?= $listing['current_bid'] ?></span></p>

<input type="number" id="bidAmount" placeholder="Your bid">
<button id="placeBidBtn">Place Bid</button>
<p id="bidError" style="color:red;"></p>

<table>
<tr><th>Bidder</th><th>Amount</th><th>Time</th></tr>
<?php foreach($bids as $b): ?>
<tr><td><?= $b['username'] ?></td><td><?= $b['amount'] ?></td><td><?= $b['bid_time'] ?></td></tr>
<?php endforeach; ?>
</table>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$('#placeBidBtn').click(function(){
    const amount = $('#bidAmount').val();
    $.post('api/place_bid.php', {listing_id: <?= $listing_id ?>, amount}, function(res){
        if(res.ok){
            $('#currentBid').text(res.new_bid);
            $('table tr:first').after(`<tr><td>You</td><td>${res.new_bid}</td><td>Just now</td></tr>`);
            $('#bidError').text('');
        } else {
            $('#bidError').text(res.error);
        }
    }, 'json');
});
</script>