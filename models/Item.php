<?php
require_once __DIR__ . '/../config/Database.php';

class Item {
    private $conn;
    private $table_name = "items";

    public function __construct($db) {
        if (!$db instanceof PDO) {
            throw new Exception("Se requiere una conexión a la base de datos válida");
        }
        $this->conn = $db;
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function search($itemNumero, $itemNombre) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE 1=1";
            $params = [];

            if (!empty($itemNumero)) {
                $query .= " AND item_numero LIKE :item_numero";
                $params[':item_numero'] = "%{$itemNumero}%";
            }

            if (!empty($itemNombre)) {
                $query .= " AND item_nombre LIKE :item_nombre";
                $params[':item_nombre'] = "%{$itemNombre}%";
            }

            $query .= " ORDER BY item_numero LIMIT 50";

            error_log("Query de búsqueda: " . $query);
            error_log("Parámetros: " . print_r($params, true));

            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Resultados encontrados: " . count($results));
            
            return $results;
        } catch (PDOException $e) {
            error_log("Error en Item::search: " . $e->getMessage());
            throw new Exception("Error al buscar items en la base de datos", 0, $e);
        }
    }

    public function getAll() {
        try {
            $query = "SELECT * FROM " . $this->table_name . " ORDER BY item_numero ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Item::getAll: " . $e->getMessage());
            throw new Exception("Error al obtener los items", 0, $e);
        }
    }

    public function getById($id) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Item::getById: " . $e->getMessage());
            throw new Exception("Error al obtener el item", 0, $e);
        }
    }
}
