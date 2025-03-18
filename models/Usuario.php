<?php
class Usuario {
    private $conn;
    private $table_name = "usuarios";

    public $id;
    public $usuario_alias;
    public $usuario_nombre;
    public $usuario_apellido;
    public $usuario_password;
    public $usuario_rol_id;
    public $usuario_departamento;
    public $estado;

    public function __construct() {
        require_once __DIR__ . '/../config/Database.php';
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    usuario_alias = :usuario_alias,
                    usuario_nombre = :usuario_nombre,
                    usuario_apellido = :usuario_apellido,
                    usuario_password = :usuario_password,
                    usuario_rol_id = :usuario_rol_id,
                    usuario_departamento = :usuario_departamento";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":usuario_alias", $this->usuario_alias);
        $stmt->bindParam(":usuario_nombre", $this->usuario_nombre);
        $stmt->bindParam(":usuario_apellido", $this->usuario_apellido);
        $stmt->bindParam(":usuario_password", $this->usuario_password);
        $stmt->bindParam(":usuario_rol_id", $this->usuario_rol_id);
        $stmt->bindParam(":usuario_departamento", $this->usuario_departamento);

        return $stmt->execute();
    }

    public function read() {
        $query = "SELECT u.*, r.rol_nombre 
                FROM " . $this->table_name . " u 
                LEFT JOIN roles r ON u.usuario_rol_id = r.id";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    public function countUsers() {
        $query = "SELECT COUNT(*) FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchColumn(); // Retorna el número total de usuarios
    }    

    public function update($password = null) {
        // If only updating password
        if ($password !== null) {
            $query = "UPDATE " . $this->table_name . "
                    SET usuario_password = :password
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":password", $password);
            $stmt->bindParam(":id", $this->id);
            
            return $stmt->execute();
        }
        
        // Regular update with all fields
        $query = "UPDATE " . $this->table_name . "
                SET
                    usuario_alias = :usuario_alias,
                    usuario_nombre = :usuario_nombre,
                    usuario_apellido = :usuario_apellido,
                    usuario_rol_id = :usuario_rol_id,
                    usuario_departamento = :usuario_departamento,
                    estado = :estado" . 
                    ($password ? ", usuario_password = :usuario_password" : "") . "
                WHERE 
                    id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":usuario_alias", $this->usuario_alias);
        $stmt->bindParam(":usuario_nombre", $this->usuario_nombre);
        $stmt->bindParam(":usuario_apellido", $this->usuario_apellido);
        $stmt->bindParam(":usuario_rol_id", $this->usuario_rol_id);
        $stmt->bindParam(":usuario_departamento", $this->usuario_departamento);
        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":id", $this->id);

        if ($password) {
            $stmt->bindParam(":usuario_password", $password);
        }

        return $stmt->execute();
    }

    public function toggleStatus() {
        $query = "UPDATE " . $this->table_name . "
                SET estado = CASE 
                    WHEN estado = 'Activo' THEN 'Inactivo'
                    ELSE 'Activo'
                END
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    /* public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        
        return $stmt->execute();
    } */

    public function authenticate($username, $password) {
        $query = "SELECT * FROM " . $this->table_name . " 
                WHERE usuario_alias = :username";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();
    
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($row['estado'] === 'Inactivo') {
                error_log("Intento de login fallido: Usuario deshabilitado - " . $username);
                return ['success' => false, 'message' => 'Usuario deshabilitado. Contacte al administrador.'];
            }
    
            if (password_verify($password, $row['usuario_password'])) {
                error_log("Login exitoso: " . $username);
                return [
                    'success' => true, 
                    'user_id' => $row['id'], 
                    'user_nombre' => $row['usuario_nombre'], 
                    'user_apellido' => $row['usuario_apellido']
                ];
            }
        }
    
        error_log("Intento de login fallido: Credenciales inválidas - " . $username);
        return ['success' => false, 'message' => 'Usuario o contraseña incorrectos'];
    }

    public function verifyPassword($userId, $password) {
        $query = "SELECT usuario_password FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $userId);
        
        try {
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($row) {
                return password_verify($password, $row['usuario_password']);
            }
            return false;
        } catch(PDOException $e) {
            error_log("Error en Usuario::verifyPassword(): " . $e->getMessage());
            throw $e;
        }
    }

    /* public function authenticate($username, $password) {
        $query = "SELECT id, alias, password, estado FROM " . $this->table_name . " 
                WHERE alias = :username";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($row['estado'] === 'Inactivo') {
                error_log("Intento de login fallido: Usuario deshabilitado - " . $username);
                return ['success' => false, 'message' => 'Usuario deshabilitado. Contacte al administrador.'];
            }

            if (password_verify($password, $row['password'])) {
                error_log("Login exitoso: " . $username);
                return ['success' => true, 'user_id' => $row['id']];
            }
        }

        error_log("Intento de login fallido: Credenciales inválidas - " . $username);
        return ['success' => false, 'message' => 'Usuario o contraseña incorrectos'];
    } */
}
?>
