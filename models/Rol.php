<?php
require_once __DIR__ . '/../config/Database.php';

class Rol {
    private $conn;
    private $table = 'roles';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAll() {
        try {
            $query = "SELECT * FROM {$this->table} ORDER BY rol_nombre ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Rol::getAll: " . $e->getMessage());
            throw new Exception("Error al obtener los roles");
        }
    }

    public function create($data) {
        try {
            $query = "INSERT INTO {$this->table} (rol_nombre, rol_descripcion, estado) 
                     VALUES (:nombre, :descripcion, :estado)";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(':nombre', $data['rol_nombre']);
            $stmt->bindParam(':descripcion', $data['rol_descripcion']);
            $stmt->bindParam(':estado', $data['estado']);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Rol::create: " . $e->getMessage());
            throw new Exception("Error al crear el rol");
        }
    }

    public function update($data) {
        try {
            $query = "UPDATE {$this->table} 
                     SET rol_nombre = :nombre, 
                         rol_descripcion = :descripcion 
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(':id', $data['id']);
            $stmt->bindParam(':nombre', $data['rol_nombre']);
            $stmt->bindParam(':descripcion', $data['rol_descripcion']);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Rol::update: " . $e->getMessage());
            throw new Exception("Error al actualizar el rol");
        }
    }

    public function toggleStatus($id, $nuevoEstado) {
        try {
            // Verificar si el rol está en uso antes de desactivarlo
            if ($nuevoEstado === 'Inactivo' && $this->isInUse($id)) {
                throw new Exception("No se puede desactivar el rol porque está siendo utilizado por uno o más usuarios");
            }

            $query = "UPDATE {$this->table} SET estado = :estado WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':estado', $nuevoEstado);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Rol::toggleStatus: " . $e->getMessage());
            throw new Exception("Error al actualizar el estado del rol");
        }
    }

    public function isInUse($id) {
        try {
            $query = "SELECT COUNT(*) FROM usuarios WHERE usuario_rol_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error en Rol::isInUse: " . $e->getMessage());
            throw new Exception("Error al verificar si el rol está en uso");
        }
    }

    public function getActiveRoles() {
        try {
            $query = "SELECT * FROM {$this->table} WHERE estado = 'Activo' ORDER BY rol_nombre ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Rol::getActiveRoles: " . $e->getMessage());
            throw new Exception("Error al obtener los roles activos");
        }
    }
}
