<?php
require_once __DIR__ . '/../config/database.php';

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

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function login($username, $password) {
        $query = "SELECT u.*, r.rol_nombre 
                 FROM " . $this->table_name . " u 
                 JOIN roles r ON u.usuario_rol_id = r.id 
                 WHERE u.usuario_alias = :username AND u.usuario_password = :password";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":password", $password);
        $stmt->execute();

        if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return $row;
        }
        return false;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    usuario_alias = :alias,
                    usuario_nombre = :nombre,
                    usuario_apellido = :apellido,
                    usuario_password = :password,
                    usuario_rol_id = :rol_id,
                    usuario_departamento = :departamento";

        $stmt = $this->conn->prepare($query);

        // Sanitize and hash
        $this->usuario_alias = htmlspecialchars(strip_tags($this->usuario_alias));
        $this->usuario_nombre = htmlspecialchars(strip_tags($this->usuario_nombre));
        $this->usuario_apellido = htmlspecialchars(strip_tags($this->usuario_apellido));
        $this->usuario_password = password_hash($this->usuario_password, PASSWORD_DEFAULT);
        $this->usuario_departamento = htmlspecialchars(strip_tags($this->usuario_departamento));

        // Bind
        $stmt->bindParam(":alias", $this->usuario_alias);
        $stmt->bindParam(":nombre", $this->usuario_nombre);
        $stmt->bindParam(":apellido", $this->usuario_apellido);
        $stmt->bindParam(":password", $this->usuario_password);
        $stmt->bindParam(":rol_id", $this->usuario_rol_id);
        $stmt->bindParam(":departamento", $this->usuario_departamento);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function read() {
        $query = "SELECT 
                    u.*, 
                    r.rol_nombre
                FROM 
                    " . $this->table_name . " u
                    LEFT JOIN roles r ON u.usuario_rol_id = r.id
                ORDER BY 
                    u.usuario_nombre ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function update() {
        $password_set = !empty($this->usuario_password) ? ", usuario_password = :password" : "";
        
        $query = "UPDATE " . $this->table_name . "
                SET
                    usuario_alias = :alias,
                    usuario_nombre = :nombre,
                    usuario_apellido = :apellido,
                    usuario_rol_id = :rol_id,
                    usuario_departamento = :departamento
                    " . $password_set . "
                WHERE 
                    id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->usuario_alias = htmlspecialchars(strip_tags($this->usuario_alias));
        $this->usuario_nombre = htmlspecialchars(strip_tags($this->usuario_nombre));
        $this->usuario_apellido = htmlspecialchars(strip_tags($this->usuario_apellido));
        $this->usuario_departamento = htmlspecialchars(strip_tags($this->usuario_departamento));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind
        $stmt->bindParam(":alias", $this->usuario_alias);
        $stmt->bindParam(":nombre", $this->usuario_nombre);
        $stmt->bindParam(":apellido", $this->usuario_apellido);
        $stmt->bindParam(":rol_id", $this->usuario_rol_id);
        $stmt->bindParam(":departamento", $this->usuario_departamento);
        $stmt->bindParam(":id", $this->id);

        if(!empty($this->usuario_password)){
            $this->usuario_password = password_hash($this->usuario_password, PASSWORD_DEFAULT);
            $stmt->bindParam(":password", $this->usuario_password);
        }

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
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
