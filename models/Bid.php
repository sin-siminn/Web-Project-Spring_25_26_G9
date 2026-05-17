<?php
require_once __DIR__ . '/../config/db.php';

class Bid {
    public static function create($listing_id, $buyer_id, $amount) {
        global $pdo;
        $stmt = $pdo->prepare("INSERT INTO bids (listing_id, buyer_id, amount, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$listing_id, $buyer_id, $amount]);
        return $pdo->lastInsertId();
    }

    public static function countByListing($listing_id) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM bids WHERE listing_id=?");
        $stmt->execute([$listing_id]);
        return $stmt->fetch()['cnt'];
    }

    public static function getRecentByListing($listing_id, $limit = 10) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT b.*, u.name as buyer_name FROM bids b JOIN users u ON b.buyer_id = u.id WHERE listing_id=? ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$listing_id, $limit]);
        return $stmt->fetchAll();
    }

    public static function getMyBids($buyer_id) {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT l.title, l.current_bid, l.end_datetime, l.status,
                   MAX(b.amount) as my_bid,
                   CASE
                       WHEN l.status='active' AND MAX(b.amount)=l.current_bid THEN 'Leading'
                       WHEN l.status='active' AND MAX(b.amount)<l.current_bid THEN 'Outbid'
                       WHEN l.status='ended' AND MAX(b.amount)=l.current_bid THEN 'Won'
                       WHEN l.status='ended' AND MAX(b.amount)<l.current_bid THEN 'Lost'
                   END as status_badge
            FROM bids b
            JOIN listings l ON b.listing_id=l.id
            WHERE b.buyer_id=?
            GROUP BY b.listing_id
            ORDER BY l.end_datetime DESC
        ");
        $stmt->execute([$buyer_id]);
        return $stmt->fetchAll();
    }
}
?>