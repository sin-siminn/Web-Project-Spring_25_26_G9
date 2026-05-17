<?php
require_once __DIR__ . '/../models/Listing.php';
require_once __DIR__ . '/../models/Bid.php';
require_once __DIR__ . '/../models/User.php';
session_start();

class ListingsController {

    // Browse active listings (with optional category or search filter)
    public function browse() {
        $category_id = $_GET['category_id'] ?? null;
        $search = $_GET['q'] ?? null;

        $listings = Listing::active($category_id, $search);
        include __DIR__ . '/../views/browse.php';
    }

    // Show detail of a single listing with bid history
    public function detail($id) {
        $listing = Listing::find($id);
        if (!$listing) {
            echo "Listing not found";
            return;
        }

        // Fetch last 10 bids
        $bid_history = Bid::getRecentByListing($id);

        // Fetch seller info
        $seller = User::find($listing['seller_id']);

        include __DIR__ . '/../views/listing_detail.php';
    }
}

// Router-like handling
$action = $_GET['action'] ?? 'browse';
$controller = new ListingsController();

if ($action === 'browse') {
    $controller->browse();
} elseif ($action === 'detail' && isset($_GET['id'])) {
    $controller->detail(intval($_GET['id']));
} else {
    echo "Invalid action";
}
?>