<?php
class Bid
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function create(int $auction_id, int $buyer_id, float $amount): int
    {
        $stmt = $this->conn->prepare("INSERT INTO bids (auction_id, buyer_id, amount, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param('iid', $auction_id, $buyer_id, $amount);
        $stmt->execute();
        return (int)$this->conn->insert_id;
    }

    public function countByAuction(int $auction_id): int
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS cnt FROM bids WHERE auction_id = ?");
        $stmt->bind_param('i', $auction_id);
        $stmt->execute();
        return (int)($stmt->get_result()->fetch_assoc()['cnt'] ?? 0);
    }

    public function getRecentByAuction(int $auction_id, int $limit = 10): array
    {
        $limit = max(1, min($limit, 50));
        $stmt = $this->conn->prepare("\n            SELECT b.bid_id, b.amount, b.created_at, u.name AS bidder_name\n            FROM bids b\n            INNER JOIN users u ON u.user_id = b.buyer_id\n            WHERE b.auction_id = ?\n            ORDER BY b.created_at DESC, b.bid_id DESC\n            LIMIT ?\n        ");
        $stmt->bind_param('ii', $auction_id, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getMyBids(int $buyer_id): array
    {
        $stmt = $this->conn->prepare("\n            SELECT\n                a.auction_id,\n                a.title,\n                a.current_price,\n                a.end_time,\n                a.status,\n                MAX(b.amount) AS my_highest_bid\n            FROM bids b\n            INNER JOIN auctions a ON a.auction_id = b.auction_id\n            WHERE b.buyer_id = ?\n            GROUP BY a.auction_id, a.title, a.current_price, a.end_time, a.status\n            ORDER BY a.end_time DESC\n        ");
        $stmt->bind_param('i', $buyer_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>
