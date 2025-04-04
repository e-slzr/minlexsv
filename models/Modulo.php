<?php
require_once '../config/Database.php';

class Modulo {
    private $conn;
    private $table = 'modulos';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function read() {
        $query = "SELECT * FROM {$this->table} ORDER BY modulo_codigo";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readByType($tipo) {
        $query = "SELECT * FROM {$this->table} WHERE modulo_tipo = ? AND modulo_estado = 'Activo' ORDER BY modulo_codigo";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$tipo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        try {
            $sql = "INSERT INTO modulos (modulo_codigo, modulo_tipo, modulo_descripcion, modulo_estado) 
                    VALUES (:codigo, :tipo, :descripcion, 'Activo')";
            
            $stmt = $this->conn->prepare($sql);
            
            $stmt->bindParam(':codigo', $data['modulo_codigo']);
            $stmt->bindParam(':tipo', $data['modulo_tipo']);
            $stmt->bindParam(':descripcion', $data['modulo_descripcion']);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en create: " . $e->getMessage());
            return false;
        }
    }

    public function update($data) {
        try {
            $query = "UPDATE {$this->table} 
                     SET modulo_codigo = :codigo,
                         modulo_tipo = :tipo,
                         modulo_descripcion = :descripcion
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(':id', $data['id']);
            $stmt->bindParam(':codigo', $data['modulo_codigo']);
            $stmt->bindParam(':tipo', $data['modulo_tipo']);
            $stmt->bindParam(':descripcion', $data['modulo_descripcion']);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en update: " . $e->getMessage());
            return false;
        }
    }

    public function toggleStatus($id) {
        $query = "UPDATE {$this->table} 
                 SET modulo_estado = CASE 
                     WHEN modulo_estado = 'Activo' THEN 'Inactivo' 
                     ELSE 'Activo' 
                 END 
                 WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    /**
     * Busca módulos por estado.
     *
     * @param string $estado El estado por el cual filtrar ('Activo', 'Inactivo', etc.)
     * @return array Devuelve un array con los módulos encontrados o array vacío en caso de error.
     */
    public function findByEstado($estado) {
        $query = "SELECT id, modulo_codigo, modulo_tipo, modulo_descripcion, modulo_estado 
                  FROM " . $this->table . " 
                  WHERE modulo_estado = :estado 
                  ORDER BY modulo_codigo ASC"; // Cambiado table_name a table

        try {
            $stmt = $this->conn->prepare($query);

            // Sanitizar (aunque PDO previene SQL injection, es buena práctica)
            $estado = htmlspecialchars(strip_tags($estado));

            // Bindear valor
            $stmt->bindParam(':estado', $estado);

            // Ejecutar query
            if ($stmt->execute()) {
                return $stmt->fetchAll(PDO::FETCH_ASSOC); // Devolver directamente el array de resultados
            }
            
            // Registrar error si la ejecución falla
            error_log("Error al ejecutar findByEstado: " . implode(":", $stmt->errorInfo()));
            return [];
        } catch (PDOException $e) {
            error_log("Excepción en findByEstado: " . $e->getMessage());
            return [];
        }
    }
}