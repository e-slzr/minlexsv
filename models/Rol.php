<?php
class Rol {
    private $conn;
    private $table_name = "roles";

    public $id;
    public $rol_nombre;
    public $rol_descripcion;
    public $estado;

    public function __construct() {
        require_once __DIR__ . '/../config/Database.php';
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    rol_nombre = :nombre,
                    rol_descripcion = :descripcion,
                    estado = 'Activo'";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":nombre", $this->rol_nombre);
        $stmt->bindParam(":descripcion", $this->rol_descripcion);

        return $stmt->execute();
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getActiveRoles() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE estado = 'Activo'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET
                    rol_nombre = :nombre,
                    rol_descripcion = :descripcion,
                    estado = :estado
                WHERE 
                    id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":nombre", $this->rol_nombre);
        $stmt->bindParam(":descripcion", $this->rol_descripcion);
        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    public function toggleStatus() {
        $query = "UPDATE " . $this->table_name . "
                SET estado = CASE 
                    WHEN estado = 'Activo' THEN 'Deshabilitado'
                    ELSE 'Activo'
                END
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    public function isInUse($id) {
        $query = "SELECT COUNT(*) as count FROM usuarios WHERE rol_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }
}
?>
