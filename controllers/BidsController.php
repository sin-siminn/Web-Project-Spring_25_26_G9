<?php
require_once __DIR__ . '/../config/db.php'; // $conn
require_once __DIR__ . '/../models/Bid.php';
session_start();

class BidsController {
    private $bidModel;

    public function __construct($conn) {
        $this->bidModel = new Bid($conn);
    }

    // AJAX: Place a bid
    public function placeBid() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['error' => 'Login required']);
            return;
        }

        $user_id = $_SESSION['user_id'];
        $auction_id = intval($_POST['listing_id']);
        $amount = floatval($_POST['amount']);

        // Include Listing model to check current price & seller
        require_once __DIR__ . '/../models/Listing.php';
        $listingModel = new Listing($GLOBALS['conn']);
        $listing = $listingModel->find($auction_id);

        if (!$listing || $listing['status'] != 'active') {
            echo json_encode(['error' => 'Auction not active']);
            return;
        }

        if ($listing['seller_id'] == $user_id) {
            echo json_encode(['error' => 'You cannot bid on your own auction']);
            return;
        }

        if ($amount <= $listing['current_price']) {
            echo json_encode(['error' => 'Bid must be higher than current bid']);
            return;
        }

        // Save bid
        $bid_id = $this->bidModel->create($auction_id, $user_id, $amount);
        $listingModel->updateCurrentPrice($auction_id, $amount);

        $bid_count = $this->bidModel->countByListing($auction_id);
        echo json_encode(['ok' => true, 'new_bid' => $amount, 'bid_count' => $bid_count]);
    }

    // Show My Bids page
    public function myBids() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login.php');
            exit;
        }

        $bids = $this->bidModel->getMyBids($_SESSION['user_id']);
        if (!is_array($bids)) $bids = []; // fallback safety

        include __DIR__ . '/../views/my_bids.php';
    }
}

// ------------------------
// Simple routing example
// ------------------------
$controller = new BidsController($conn);
$action = $_GET['action'] ?? '';

if ($action === 'placeBid') {
    $controller->placeBid();
} elseif ($action === 'myBids') {
    $controller->myBids();
} else {
    echo "Invalid action";
}
?>