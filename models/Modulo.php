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
        $query = "UPDATE {$this->table} 
                 SET modulo_codigo = :codigo,
                     modulo_nombre = :nombre,
                     modulo_tipo = :tipo,
                     modulo_descripcion = :descripcion
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([
            ':id' => $data['id'],
            ':codigo' => $data['modulo_codigo'],
            ':nombre' => $data['modulo_nombre'],
            ':tipo' => $data['modulo_tipo'],
            ':descripcion' => $data['modulo_descripcion']
        ]);
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
} 