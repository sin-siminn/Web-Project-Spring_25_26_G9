<!DOCTYPE html>
<html>
<head>
    <title>Place Bid</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f8f9fa; color: #333; margin: 0; padding: 0; }
        .container { width: 90%; max-width: 1000px; margin: 20px auto; padding: 20px; background-color: #fff; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1, h2, h3 { color: #007bff; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table th, table td { border: 1px solid #dee2e6; padding: 10px; text-align: left; }
        table th { background-color: #007bff; color: #fff; }
        form { display: flex; flex-direction: column; }
        input[type="text"], input[type="number"], input[type="email"], input[type="password"], select, textarea { padding: 10px; margin-bottom: 15px; border: 1px solid #ced4da; border-radius: 4px; }
        input[type="submit"], button { padding: 10px 15px; background-color: #007bff; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
        input[type="submit"]:hover, button:hover { background-color: #0056b3; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .alert { padding: 10px 15px; margin-bottom: 15px; border-radius: 4px; }
        .alert-success { background-color: #d4edda; color: #155724; }
        .alert-error { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
<div class="container">
    <?php
require '../../config/db.php';
require '../../controllers/AuthController.php';
session_start();

$user_id = $_SESSION['user_id'];
$listing_id = intval($_POST['listing_id']);
$amount = floatval($_POST['amount']);

$listing = $conn->query("SELECT * FROM listings WHERE id=$listing_id")->fetch_assoc();

if(!$listing || $listing['status']!='active' || strtotime($listing['end_datetime'])<time()){
    echo json_encode(['ok'=>false,'error'=>'Auction ended']); exit;
}
if($amount <= $listing['current_bid']){
    echo json_encode(['ok'=>false,'error'=>'Bid must be higher']); exit;
}
if($listing['seller_id']==$user_id){
    echo json_encode(['ok'=>false,'error'=>'You cannot bid on your own auction']); exit;
}

$conn->query("INSERT INTO bids(listing_id,buyer_id,amount) VALUES ($listing_id,$user_id,$amount)");
$conn->query("UPDATE listings SET current_bid=$amount, bid_count=bid_count+1 WHERE id=$listing_id");

echo json_encode(['ok'=>true,'new_bid'=>$amount,'bid_count'=>$listing['bid_count']+1]);
?>
</div>
</body>
</html>