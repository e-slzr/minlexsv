<?php
require_once __DIR__ . '/../config/database.php';

class Rol {
    private $conn;
    private $table_name = "roles";

    public $id;
    public $rol_nombre;
    public $rol_descripcion;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    rol_nombre = :nombre,
                    rol_descripcion = :descripcion";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->rol_nombre = htmlspecialchars(strip_tags($this->rol_nombre));
        $this->rol_descripcion = htmlspecialchars(strip_tags($this->rol_descripcion));

        // Bind
        $stmt->bindParam(":nombre", $this->rol_nombre);
        $stmt->bindParam(":descripcion", $this->rol_descripcion);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY rol_nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET
                    rol_nombre = :nombre,
                    rol_descripcion = :descripcion
                WHERE 
                    id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->rol_nombre = htmlspecialchars(strip_tags($this->rol_nombre));
        $this->rol_descripcion = htmlspecialchars(strip_tags($this->rol_descripcion));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind
        $stmt->bindParam(":nombre", $this->rol_nombre);
        $stmt->bindParam(":descripcion", $this->rol_descripcion);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        // Primero verificamos si hay usuarios usando este rol
        $query = "SELECT COUNT(*) as count FROM usuarios WHERE usuario_rol_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row['count'] > 0) {
            return false; // No podemos eliminar un rol que está en uso
        }

        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
