<?php
require_once __DIR__ . '/../config/Database.php';

class Proceso {
    private $conn;
    private $table_name = "procesos_produccion";

    public function __construct($db) {
        if (!$db instanceof PDO) {
            throw new Exception("Se requiere una conexión a la base de datos válida");
        }
        $this->conn = $db;
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getAll() {
        try {
            $query = "SELECT * FROM " . $this->table_name . " ORDER BY pp_nombre ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Proceso::getAll: " . $e->getMessage());
            return [];
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
            error_log("Error en Proceso::getById: " . $e->getMessage());
            return false;
        }
    }

    public function create($data) {
        try {
            $query = "INSERT INTO " . $this->table_name . " 
                    (pp_nombre, pp_descripcion, pp_costo) 
                    VALUES 
                    (:pp_nombre, :pp_descripcion, :pp_costo)";
            
            $stmt = $this->conn->prepare($query);
            
            // Bind de los valores
            $stmt->bindValue(':pp_nombre', $data['pp_nombre']);
            $stmt->bindValue(':pp_descripcion', $data['pp_descripcion'] ?? null);
            $stmt->bindValue(':pp_costo', $data['pp_costo'] ?? null);
            
            $stmt->execute();
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error en Proceso::create: " . $e->getMessage());
            throw new Exception("Error al crear el proceso", 0, $e);
        }
    }

    public function update($data) {
        try {
            $query = "UPDATE " . $this->table_name . " 
                    SET pp_nombre = :pp_nombre,
                        pp_descripcion = :pp_descripcion,
                        pp_costo = :pp_costo
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            
            // Bind de los valores
            $stmt->bindValue(':id', $data['id']);
            $stmt->bindValue(':pp_nombre', $data['pp_nombre']);
            $stmt->bindValue(':pp_descripcion', $data['pp_descripcion'] ?? null);
            $stmt->bindValue(':pp_costo', $data['pp_costo'] ?? null);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Proceso::update: " . $e->getMessage());
            throw new Exception("Error al actualizar el proceso", 0, $e);
        }
    }

    public function delete($id) {
        try {
            // Primero verificar si hay órdenes de producción asociadas
            $query = "SELECT COUNT(*) as count FROM ordenes_produccion WHERE op_id_proceso = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['count'] > 0) {
                throw new Exception("No se puede eliminar el proceso porque tiene órdenes de producción asociadas");
            }

            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Proceso::delete: " . $e->getMessage());
            throw new Exception("Error al eliminar el proceso", 0, $e);
        }
    }

    public function search($filters = []) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE 1=1";
            
            // Aplicar filtros
            if (!empty($filters['nombre'])) {
                $query .= " AND pp_nombre LIKE :nombre";
            }
            
            if (!empty($filters['costo_min'])) {
                $query .= " AND pp_costo >= :costo_min";
            }
            
            if (!empty($filters['costo_max'])) {
                $query .= " AND pp_costo <= :costo_max";
            }
            
            // Ordenamiento
            $order_column = !empty($filters['order_column']) ? $filters['order_column'] : 'pp_nombre';
            $order_dir = !empty($filters['order_dir']) ? $filters['order_dir'] : 'ASC';
            
            // Validar que la columna de ordenamiento sea válida
            $valid_columns = ['id', 'pp_nombre', 'pp_costo'];
            if (!in_array($order_column, $valid_columns)) {
                $order_column = 'pp_nombre';
            }
            
            // Validar la dirección de ordenamiento
            if ($order_dir != 'ASC' && $order_dir != 'DESC') {
                $order_dir = 'ASC';
            }
            
            $query .= " ORDER BY {$order_column} {$order_dir}";
            
            $stmt = $this->conn->prepare($query);
            
            // Bind de los valores
            if (!empty($filters['nombre'])) {
                $stmt->bindValue(':nombre', '%' . $filters['nombre'] . '%');
            }
            
            if (!empty($filters['costo_min'])) {
                $stmt->bindValue(':costo_min', $filters['costo_min']);
            }
            
            if (!empty($filters['costo_max'])) {
                $stmt->bindValue(':costo_max', $filters['costo_max']);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Proceso::search: " . $e->getMessage());
            return [];
        }
    }

    public function isUsedInProduction($id) {
        try {
            $query = "SELECT COUNT(*) as count FROM ordenes_produccion WHERE op_id_proceso = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
        } catch (PDOException $e) {
            error_log("Error en Proceso::isUsedInProduction: " . $e->getMessage());
            return false;
        }
    }
}
