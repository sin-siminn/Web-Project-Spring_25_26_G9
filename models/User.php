<?php
require_once __DIR__ . '/../config/db.php';

class User {
    public static function find($id) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function all() {
        global $pdo;
        $stmt = $pdo->query("SELECT * FROM users");
        return $stmt->fetchAll();
    }
}
?>