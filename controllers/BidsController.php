<?php
require_once __DIR__ . '/../models/Bid.php';
require_once __DIR__ . '/../models/Listing.php';
require_once __DIR__ . '/../models/User.php';
session_start();

class BidsController {

    public function placeBid() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['error' => 'Login required']);
            return;
        }

        $user_id = $_SESSION['user_id'];
        $listing_id = intval($_POST['listing_id']);
        $amount = floatval($_POST['amount']);

        $listing = Listing::find($listing_id);
        if (!$listing || $listing['status'] != 'active') {
            echo json_encode(['error' => 'Auction not active']);
            return;
        }

        if ($listing['seller_id'] == $user_id) {
            echo json_encode(['error' => 'You cannot bid on your own auction']);
            return;
        }

        if ($amount <= $listing['current_bid']) {
            echo json_encode(['error' => 'Bid must be higher than current bid']);
            return;
        }

        // Insert bid
        $bid_id = Bid::create($listing_id, $user_id, $amount);
        Listing::updateCurrentBid($listing_id, $amount);

        $bid_count = Bid::countByListing($listing_id);
        echo json_encode(['ok' => true, 'new_bid' => $amount, 'bid_count' => $bid_count]);
    }

    public function myBids() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login.php');
            exit;
        }

        $bids = Bid::getMyBids($_SESSION['user_id']);
        include __DIR__ . '/../views/my_bids.php';
    }
}
?>