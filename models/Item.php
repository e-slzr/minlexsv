<?php
require_once __DIR__ . '/../config/Database.php';

class Item {
    private $conn;
    private $table = 'items';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAll() {
        try {
            $query = "SELECT * FROM {$this->table} ORDER BY item_numero ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Item::getAll: " . $e->getMessage());
            throw new Exception("Error al obtener los items");
        }
    }

    public function getById($id) {
        try {
            $query = "SELECT * FROM {$this->table} WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Item::getById: " . $e->getMessage());
            throw new Exception("Error al obtener el item");
        }
    }
}
