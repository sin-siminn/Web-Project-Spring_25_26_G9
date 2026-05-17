<?php
class Listing {
    public $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Existing Methods
    public function create($seller_id, $category_id, $title, $desc, $start_price, $reserve_price, $image_path, $end_datetime) {
        $stmt = $this->conn->prepare(
            "INSERT INTO auctions 
            (seller_id, category_id, title, description, starting_price, current_price, end_time, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW())"
        );

        $current_price = $start_price;

        $stmt->bind_param(
            "iisddds",
            $seller_id,
            $category_id,
            $title,
            $desc,
            $start_price,
            $current_price,
            $end_datetime
        );

        return $stmt->execute();
    }

    public function getBySeller($seller_id) {
        $stmt = $this->conn->prepare("SELECT * FROM auctions WHERE seller_id=? ORDER BY created_at DESC");
        $stmt->bind_param("i", $seller_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function cancel($auction_id) {
        $stmt = $this->conn->prepare("UPDATE auctions SET status='cancelled' WHERE auction_id=?");
        $stmt->bind_param("i", $auction_id);
        return $stmt->execute();
    }

    // ------------------------------
    // New Methods for Task 3 (Buyer)
    // ------------------------------

    // Get all active auctions (optionally filter by category or search keyword)
    public function getActive($category_id = null, $search = null) {
        $query = "SELECT * FROM auctions WHERE status='active' AND end_time > NOW()";
        $params = [];
        $types = '';

        if ($category_id) {
            $query .= " AND category_id=?";
            $params[] = $category_id;
            $types .= 'i';
        }

        if ($search) {
            $query .= " AND title LIKE ?";
            $params[] = "%$search%";
            $types .= 's';
        }

        $stmt = $this->conn->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Find a single auction by ID
    public function find($auction_id) {
        $stmt = $this->conn->prepare("SELECT * FROM auctions WHERE auction_id=?");
        $stmt->bind_param("i", $auction_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Update current price of an auction
    public function updateCurrentPrice($auction_id, $amount) {
        $stmt = $this->conn->prepare("UPDATE auctions SET current_price=? WHERE auction_id=?");
        $stmt->bind_param("di", $amount, $auction_id);
        return $stmt->execute();
    }
}
?>