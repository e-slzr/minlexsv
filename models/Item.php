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
    
    public function create($data) {
        try {
            $query = "INSERT INTO " . $this->table_name . " 
                    (item_numero, item_nombre, item_descripcion, item_talla, item_img, item_dir_specs) 
                    VALUES 
                    (:item_numero, :item_nombre, :item_descripcion, :item_talla, :item_img, :item_dir_specs)";
            
            $stmt = $this->conn->prepare($query);
            
            // Bind de los valores
            $stmt->bindValue(':item_numero', $data['item_numero']);
            $stmt->bindValue(':item_nombre', $data['item_nombre']);
            $stmt->bindValue(':item_descripcion', $data['item_descripcion']);
            $stmt->bindValue(':item_talla', $data['item_talla']);
            $stmt->bindValue(':item_img', $data['item_img']);
            $stmt->bindValue(':item_dir_specs', $data['item_dir_specs']);
            
            $stmt->execute();
            
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error en Item::create: " . $e->getMessage());
            throw new Exception("Error al crear el item", 0, $e);
        }
    }
    
    public function update($data) {
        try {
            $query = "UPDATE " . $this->table_name . " 
                    SET item_numero = :item_numero, 
                        item_nombre = :item_nombre, 
                        item_descripcion = :item_descripcion, 
                        item_talla = :item_talla";
            
            // Agregar campos de imagen y specs solo si están presentes
            if ($data['item_img'] !== null) {
                $query .= ", item_img = :item_img";
            }
            
            if ($data['item_dir_specs'] !== null) {
                $query .= ", item_dir_specs = :item_dir_specs";
            }
            
            $query .= " WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            
            // Bind de los valores
            $stmt->bindValue(':id', $data['id']);
            $stmt->bindValue(':item_numero', $data['item_numero']);
            $stmt->bindValue(':item_nombre', $data['item_nombre']);
            $stmt->bindValue(':item_descripcion', $data['item_descripcion']);
            $stmt->bindValue(':item_talla', $data['item_talla']);
            
            // Bind condicional para imagen y specs
            if ($data['item_img'] !== null) {
                $stmt->bindValue(':item_img', $data['item_img']);
            }
            
            if ($data['item_dir_specs'] !== null) {
                $stmt->bindValue(':item_dir_specs', $data['item_dir_specs']);
            }
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Item::update: " . $e->getMessage());
            throw new Exception("Error al actualizar el item", 0, $e);
        }
    }
    
    public function delete($id) {
        try {
            // Verificar si el item está siendo utilizado en otras tablas
            // Por ejemplo, en detalles de PO
            $checkQuery = "SELECT COUNT(*) FROM po_detalle WHERE pd_item = :id";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindValue(':id', $id);
            $checkStmt->execute();
            
            if ($checkStmt->fetchColumn() > 0) {
                throw new Exception("No se puede eliminar el item porque está siendo utilizado en órdenes de compra");
            }
            
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':id', $id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Item::delete: " . $e->getMessage());
            throw new Exception("Error al eliminar el item", 0, $e);
        }
    }
}
