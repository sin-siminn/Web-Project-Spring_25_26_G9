<?php
// This view needs $listings from a controller. If opened directly, go through the controller first.
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
<style>
body {
    font-family: Arial, sans-serif;
    background: #f5f5f5;
    padding: 20px;
}
.container {
    max-width: 1000px;
    margin: 0 auto;
    background: #fff;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
h2 {
    text-align: center;
    margin-bottom: 20px;
}
a.post-new {
    display: inline-block;
    margin-bottom: 15px;
    color: #2b542c;
    font-weight: bold;
    text-decoration: none;
}
a.post-new:hover { text-decoration: underline; }
table {
    width: 100%;
    border-collapse: collapse;
}
th, td {
    padding: 10px;
    border: 1px solid #ccc;
    text-align: left;
}
th {
    background: #007BFF;
    color: #fff;
}
.status-active {
    color: green;
    font-weight: bold;
}
.status-cancelled {
    color: red;
    font-weight: bold;
}
button {
    padding: 5px 10px;
    margin: 2px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}
button.cancel {
    background: #dc3545;
    color: #fff;
}
button.edit {
    background: #007BFF;
    color: #fff;
}
.countdown {
    font-weight: bold;
}
</style>
</head>
<body>
<div class="container">
<h2>Seller Dashboard</h2>

<a href="ListingController.php?action=create" class="post-new">Post a New Auction</a>

<table>
<tr>
<th>Title</th>
<th>Category</th>
<th>Start Price</th>
<th>Current Price</th>
<th>Bid Count</th>
<th>Status</th>
<th>Time Remaining</th>
<th>Actions</th>
</tr>

<?php foreach($listings as $l): ?>
<tr>
<td><?= htmlspecialchars($l['title']) ?></td>
<td><?= htmlspecialchars($l['category_name']) ?></td>
<td><?= number_format($l['starting_price'], 2) ?></td>
<td><?= number_format($l['current_price'], 2) ?></td>
<td><?= $l['bid_count'] ?></td>
<td class="status-<?= $l['status'] ?>"><?= ucfirst($l['status']) ?></td>
<td>
<span class="countdown" data-end="<?= $l['end_time'] ?>"></span>
</td>
<td>
<?php if($l['bid_count']==0 && $l['status']=='active'): ?>
    <a href="ListingController.php?action=cancel&id=<?= $l['auction_id'] ?>"><button class="cancel">Cancel</button></a>
    <a href="ListingController.php?action=edit&id=<?= $l['auction_id'] ?>"><button class="edit">Edit</button></a>
<?php else: ?>
    -
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
</table>
</div>

<script>
// Countdown timer for each listing
document.querySelectorAll('.countdown').forEach(function(el){
  const endTime = new Date(el.dataset.end).getTime();
  const interval = setInterval(function(){
    const now = new Date().getTime();
    const diff = endTime - now;
    if(diff <= 0){
        el.innerHTML = "Ended";
        clearInterval(interval);
        return;
    }
    const hrs = Math.floor(diff / 1000 / 60 / 60);
    const mins = Math.floor((diff / 1000 / 60) % 60);
    const secs = Math.floor((diff / 1000) % 60);
    el.innerHTML = `${hrs}h ${mins}m ${secs}s`;
  }, 1000);
});
</script>

</body>
</html>