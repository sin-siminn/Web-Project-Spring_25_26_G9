<?php
class Listing {
    public $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

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
}