<?php
class Category {
    private $conn;
    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAll() {
        $stmt = $this->conn->query("SELECT * FROM categories ORDER BY name ASC");
        return $stmt->fetch_all(MYSQLI_ASSOC);
    }

    public function create($name) {
        $stmt = $this->conn->prepare("INSERT INTO categories(name) VALUES(?)");
        $stmt->bind_param("s", $name);
        return $stmt->execute();
    }

    public function update($id, $name) {
        $stmt = $this->conn->prepare("UPDATE categories SET name=? WHERE id=?");
        $stmt->bind_param("si", $name, $id);
        return $stmt->execute();
    }

    public function delete($id) {
        // Prevent deletion if auctions exist
        $stmt = $this->conn->prepare("SELECT COUNT(*) as cnt FROM auctions WHERE category_id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $cnt = $stmt->get_result()->fetch_assoc()['cnt'];
        if($cnt>0) return false;

        $stmt = $this->conn->prepare("DELETE FROM categories WHERE id=?");
        $stmt->bind_param("i",$id);
        return $stmt->execute();
    }
}