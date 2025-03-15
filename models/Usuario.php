<?php
class Usuario {
    private $conn;
    private $table_name = "usuarios";

    public $id;
    public $alias;
    public $nombre;
    public $apellido;
    public $email;
    public $password;
    public $usuario_rol_id;
    public $estado;

    public function __construct() {
        require_once __DIR__ . '/../config/Database.php';
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    alias = :alias,
                    nombre = :nombre,
                    apellido = :apellido,
                    email = :email,
                    password = :password,
                    usuario_rol_id = :usuario_rol_id,
                    estado = 'Activo'";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":alias", $this->alias);
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":apellido", $this->apellido);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":usuario_rol_id", $this->usuario_rol_id);

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

    public function update() {
        $passwordSet = !empty($this->password) ? ", password = :password" : "";
        
        $query = "UPDATE " . $this->table_name . "
                SET
                    alias = :alias,
                    nombre = :nombre,
                    apellido = :apellido,
                    email = :email,
                    usuario_rol_id = :usuario_rol_id,
                    estado = :estado" . 
                    $passwordSet . "
                WHERE 
                    id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":alias", $this->alias);
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":apellido", $this->apellido);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":usuario_rol_id", $this->usuario_rol_id);
        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":id", $this->id);

        if (!empty($this->password)) {
            $stmt->bindParam(":password", $this->password);
        }

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

    public function authenticate($username, $password) {
        $query = "SELECT id, usuario_alias, usuario_password, estado FROM " . $this->table_name . " 
                WHERE usuario_alias = :username";
    
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();
    
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($row['estado'] === 'Deshabilitado') {
                error_log("Intento de login fallido: Usuario deshabilitado - " . $username);
                return ['success' => false, 'message' => 'Usuario deshabilitado. Contacte al administrador.'];
            }
    
            if (password_verify($password, $row['usuario_password'])) {
                error_log("Login exitoso: " . $username);
                return ['success' => true, 'user_id' => $row['id']];
            }
        }
    
        error_log("Intento de login fallido: Credenciales inválidas - " . $username);
        return ['success' => false, 'message' => 'Usuario o contraseña incorrectos'];
    }

    /* public function authenticate($username, $password) {
        $query = "SELECT id, alias, password, estado FROM " . $this->table_name . " 
                WHERE alias = :username";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($row['estado'] === 'Deshabilitado') {
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
