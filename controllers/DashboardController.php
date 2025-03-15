<?php
require_once '../config/database.php';

class DashboardController {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getCounts() {
        try {
            $counts = [
                'usuarios' => 0,
                'clientes' => 0,
                'pos' => 0
            ];

            // Contar usuarios
            $query = "SELECT COUNT(*) as total FROM usuarios";
            $stmt = $this->conn->query($query);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $counts['usuarios'] = $row['total'];

            // Contar clientes
            $query = "SELECT COUNT(*) as total FROM clientes";
            $stmt = $this->conn->query($query);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $counts['clientes'] = $row['total'];

            // Contar POs
            $query = "SELECT COUNT(*) as total FROM pos";
            $stmt = $this->conn->query($query);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $counts['pos'] = $row['total'];

            return $counts;
        } catch (PDOException $e) {
            error_log("Error al obtener conteos: " . $e->getMessage());
            return false;
        }
    }
}
