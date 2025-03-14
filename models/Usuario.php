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
        try {
            error_log("=== Inicio de intento de login en modelo ===");
            error_log("Buscando usuario: " . $username);
            
            $query = "SELECT u.*, r.rol_nombre 
                     FROM " . $this->table_name . " u 
                     JOIN roles r ON u.usuario_rol_id = r.id 
                     WHERE u.usuario_alias = :username";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":username", $username);
            $stmt->execute();
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($row) {
                error_log("Usuario encontrado en la base de datos");
                error_log("Hash almacenado (primeros 13 caracteres): " . substr($row['usuario_password'], 0, 13) . "...");
                error_log("Longitud del hash almacenado: " . strlen($row['usuario_password']));
                
                if (password_verify($password, $row['usuario_password'])) {
                    error_log("Contraseña verificada correctamente");
                    return $row;
                } else {
                    error_log("Contraseña incorrecta");
                }
            } else {
                error_log("Usuario no encontrado en la base de datos");
            }
            
            error_log("=== Fin de intento de login en modelo ===");
            return false;
        } catch (PDOException $e) {
            error_log("Error en login: " . $e->getMessage());
            throw new Exception("Error al intentar iniciar sesión");
        }
    }

    public function create($hashedPassword = null) {
        try {
            $query = "INSERT INTO " . $this->table_name . "
                    SET
                        usuario_alias = :alias,
                        usuario_nombre = :nombre,
                        usuario_apellido = :apellido,
                        usuario_password = :password,
                        usuario_rol_id = :rol_id,
                        usuario_departamento = :departamento";

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(":alias", $this->usuario_alias);
            $stmt->bindParam(":nombre", $this->usuario_nombre);
            $stmt->bindParam(":apellido", $this->usuario_apellido);
            $stmt->bindParam(":password", $hashedPassword);
            $stmt->bindParam(":rol_id", $this->usuario_rol_id);
            $stmt->bindParam(":departamento", $this->usuario_departamento);

            if ($stmt->execute()) {
                error_log("Usuario creado exitosamente en la base de datos");
                error_log("Hash almacenado (primeros 13 caracteres): " . substr($hashedPassword, 0, 13) . "...");
                return true;
            }
            
            error_log("Error al ejecutar la consulta de creación");
            return false;
        } catch (PDOException $e) {
            error_log("Error en create: " . $e->getMessage());
            return false;
        }
    }

    public function read() {
        try {
            $query = "SELECT u.*, r.rol_nombre 
                    FROM " . $this->table_name . " u
                    JOIN roles r ON u.usuario_rol_id = r.id
                    ORDER BY u.id DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error en read: " . $e->getMessage());
            throw new Exception("Error al leer usuarios");
        }
    }

    public function update($hashedPassword = null) {
        try {
            $password_set = "";
            $params = array();
            
            if($hashedPassword !== null) {
                $password_set = ", usuario_password = :password";
                $params[":password"] = $hashedPassword;
            }

            $query = "UPDATE " . $this->table_name . "
                    SET
                        usuario_alias = :alias,
                        usuario_nombre = :nombre,
                        usuario_apellido = :apellido,
                        usuario_rol_id = :rol_id,
                        usuario_departamento = :departamento" . $password_set . "
                    WHERE 
                        id = :id";

            $stmt = $this->conn->prepare($query);

            $params[":alias"] = $this->usuario_alias;
            $params[":nombre"] = $this->usuario_nombre;
            $params[":apellido"] = $this->usuario_apellido;
            $params[":rol_id"] = $this->usuario_rol_id;
            $params[":departamento"] = $this->usuario_departamento;
            $params[":id"] = $this->id;

            foreach($params as $param => $value) {
                $stmt->bindValue($param, $value);
            }

            if ($stmt->execute()) {
                error_log("Usuario actualizado exitosamente en la base de datos");
                if ($hashedPassword !== null) {
                    error_log("Hash actualizado (primeros 13 caracteres): " . substr($hashedPassword, 0, 13) . "...");
                }
                return true;
            }
            
            error_log("Error al ejecutar la consulta de actualización");
            return false;
        } catch (PDOException $e) {
            error_log("Error en update: " . $e->getMessage());
            return false;
        }
    }

    public function delete() {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $this->id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en delete: " . $e->getMessage());
            return false;
        }
    }
}
