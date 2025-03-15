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
        // Log the table structure
        try {
            $describeQuery = "DESCRIBE " . $this->table_name;
            $describeStmt = $this->conn->prepare($describeQuery);
            $describeStmt->execute();
            $tableStructure = $describeStmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Table structure: " . print_r($tableStructure, true));
        } catch (Exception $e) {
            error_log("Error getting table structure: " . $e->getMessage());
        }

        // First get the current state
        $queryGet = "SELECT id, estado FROM " . $this->table_name . " WHERE id = :id";
        $stmtGet = $this->conn->prepare($queryGet);
        $stmtGet->bindParam(":id", $this->id);
        $stmtGet->execute();
        $currentState = $stmtGet->fetch(PDO::FETCH_ASSOC);
        
        error_log("Current state for role ID " . $this->id . ": " . print_r($currentState, true));
        
        // Determine the new state
        $newState = $currentState['estado'] === 'Activo' ? 'Inactivo' : 'Activo';
        error_log("New state will be: " . $newState);
        
        $query = "UPDATE " . $this->table_name . " SET estado = :estado WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":estado", $newState);
        $stmt->bindParam(":id", $this->id);
        $result = $stmt->execute();
        
        error_log("Toggle status result: " . ($result ? "true" : "false"));
        if (!$result) {
            error_log("SQL Error: " . print_r($stmt->errorInfo(), true));
        } else {
            // Verify the update
            $verifyQuery = "SELECT id, estado FROM " . $this->table_name . " WHERE id = :id";
            $verifyStmt = $this->conn->prepare($verifyQuery);
            $verifyStmt->bindParam(":id", $this->id);
            $verifyStmt->execute();
            $verifyState = $verifyStmt->fetch(PDO::FETCH_ASSOC);
            error_log("Verified state after update: " . print_r($verifyState, true));
        }
        
        return $result;
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
