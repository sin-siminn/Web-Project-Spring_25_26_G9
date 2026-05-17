<?php
class Bid {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Create a new bid
    public function create($auction_id, $buyer_id, $amount) {
        $stmt = $this->conn->prepare(
            "INSERT INTO bids (auction_id, buyer_id, amount, created_at) VALUES (?, ?, ?, NOW())"
        );
        $stmt->bind_param("iid", $auction_id, $buyer_id, $amount);
        $stmt->execute();
        return $this->conn->insert_id;
    }

    // Count total bids for a specific auction
    public function countByListing($auction_id) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS cnt FROM bids WHERE auction_id=?");
        $stmt->bind_param("i", $auction_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result ? $result['cnt'] : 0;
    }

    // Get last 10 bids for an auction
    public function getRecentByListing($auction_id, $limit = 10) {
        $stmt = $this->conn->prepare("
            SELECT b.*, u.name AS buyer_name
            FROM bids b
            JOIN users u ON b.buyer_id = u.id
            WHERE b.auction_id=?
            ORDER BY b.created_at DESC
            LIMIT ?
        ");
        $stmt->bind_param("ii", $auction_id, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // Get all bids placed by a buyer for My Bids page
    public function getMyBids($buyer_id) {
        $stmt = $this->conn->prepare("
            SELECT a.auction_id, a.title, a.current_price,
                   MAX(b.amount) AS my_bid,
                   CASE
                       WHEN a.status='active' AND MAX(b.amount)=a.current_price THEN 'Leading'
                       WHEN a.status='active' AND MAX(b.amount)<a.current_price THEN 'Outbid'
                       WHEN a.status='ended' AND MAX(b.amount)=a.current_price THEN 'Won'
                       WHEN a.status='ended' AND MAX(b.amount)<a.current_price THEN 'Lost'
                   END AS status_badge
            FROM bids b
            JOIN auctions a ON b.auction_id = a.auction_id
            WHERE b.buyer_id = ?
            GROUP BY b.auction_id
            ORDER BY a.end_time DESC
        ");
        $stmt->bind_param("i", $buyer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
}
?>