<?php
require_once __DIR__ . '/../config/database.php'; // $conn
require_once __DIR__ . '/../models/Listing.php';
require_once __DIR__ . '/../models/Bid.php';
require_once __DIR__ . '/../models/User.php';
session_start();

class ListingsController {
    private $listingModel;
    private $bidModel;
    private $userModel;

    public function __construct($conn) {
        $this->listingModel = new Listing($conn);
        $this->bidModel = new Bid($conn);
        $this->userModel = new User($conn);
        $this->conn = $conn;
    }

    // Browse all active auctions
    public function browse() {
        $listings = $this->listingModel->getActive($_GET['category_id'] ?? null, $_GET['q'] ?? null);
        $bidModel = $this->bidModel; // needed for bid counts in view
        include __DIR__ . '/../views/browse.php';
    }

    // Show single auction detail
    public function detail($auction_id) {
        $listing = $this->listingModel->find($auction_id);
        if (!$listing) {
            echo "Listing not found";
            return;
        }

        $bid_history = $this->bidModel->getRecentByListing($auction_id);
        $seller = $this->userModel->find($listing['seller_id']);

        include __DIR__ . '/../views/listing_detail.php';
    }
}


$controller = new ListingsController($conn);
$action = $_GET['action'] ?? 'browse';

if ($action === 'browse') {
    $controller->browse();
} elseif ($action === 'detail' && isset($_GET['id'])) {
    $controller->detail(intval($_GET['id']));
} else {
    echo "Invalid action";
}
?>