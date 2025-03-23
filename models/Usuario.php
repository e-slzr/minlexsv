<?php
class Usuario {
    private $conn;
    private $table_name = "usuarios";

    public $id;
    public $usuario_usuario;
    public $usuario_nombre;
    public $usuario_apellido;
    public $usuario_password;
    public $usuario_rol_id;
    public $usuario_departamento;
    public $estado;
    public $usuario_creacion;
    public $usuario_modificacion;
    public $usuario_modulo_id;

    private $validation_rules = [
        'usuario_usuario' => ['required' => true, 'max_length' => 25],
        'usuario_nombre' => ['required' => true, 'max_length' => 25],
        'usuario_apellido' => ['required' => true, 'max_length' => 25],
        'usuario_password' => ['required' => true, 'max_length' => 255],
        'usuario_rol_id' => ['required' => true, 'type' => 'integer'],
        'usuario_departamento' => ['required' => false, 'max_length' => 25],
        'estado' => ['required' => false, 'values' => ['Activo', 'Inactivo']],
        'usuario_modulo_id' => ['required' => false, 'type' => 'integer']
    ];

    public function __construct() {
        require_once __DIR__ . '/../config/Database.php';
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function validate() {
        $errors = [];
        
        foreach ($this->validation_rules as $field => $rules) {
            $value = $this->$field ?? null;
            
            if ($rules['required'] && empty($value)) {
                $errors[] = "El campo {$field} es requerido";
                continue;
            }
            
            if (!empty($value)) {
                if (isset($rules['max_length']) && strlen($value) > $rules['max_length']) {
                    $errors[] = "El campo {$field} no puede exceder {$rules['max_length']} caracteres";
                }
                
                if (isset($rules['type']) && $rules['type'] === 'integer' && !filter_var($value, FILTER_VALIDATE_INT)) {
                    $errors[] = "El campo {$field} debe ser un número entero";
                }
                
                if (isset($rules['values']) && !in_array($value, $rules['values'])) {
                    $errors[] = "El valor del campo {$field} no es válido";
                }
            }
        }
        
        if (empty($this->id) && !empty($this->usuario_usuario)) {
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM {$this->table_name} WHERE usuario_alias = ?");
            $stmt->execute([$this->usuario_usuario]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = "El nombre de usuario ya existe";
            }
        }
        
        return empty($errors) ? true : $errors;
    }

    public function create($data) {
        // Validar antes de crear
        $validation = $this->validate();
        if ($validation !== true) {
            throw new Exception(implode(", ", $validation));
        }

        $query = "INSERT INTO " . $this->table_name . "
                SET
                    usuario_alias = :usuario_usuario,
                    usuario_nombre = :usuario_nombre,
                    usuario_apellido = :usuario_apellido,
                    usuario_password = :usuario_password,
                    usuario_rol_id = :usuario_rol_id,
                    usuario_departamento = :usuario_departamento,
                    estado = :estado,
                    usuario_creacion = NOW(),
                    usuario_modulo_id = :modulo_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":usuario_usuario", $this->usuario_usuario);
        $stmt->bindParam(":usuario_nombre", $this->usuario_nombre);
        $stmt->bindParam(":usuario_apellido", $this->usuario_apellido);
        $stmt->bindParam(":usuario_password", $this->usuario_password);
        $stmt->bindParam(":usuario_rol_id", $this->usuario_rol_id);
        $stmt->bindParam(":usuario_departamento", $this->usuario_departamento);
        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":modulo_id", $this->usuario_modulo_id);

        return $stmt->execute();
    }

    public function update($data = null) {
        $query = "UPDATE " . $this->table_name . "
                SET
                    usuario_alias = :usuario_usuario,
                    usuario_nombre = :usuario_nombre,
                    usuario_apellido = :usuario_apellido,
                    usuario_rol_id = :usuario_rol_id,
                    usuario_departamento = :usuario_departamento,
                    usuario_modulo_id = :modulo_id,
                    usuario_modificacion = NOW()";

        // Agregar la actualización de contraseña solo si se proporciona una nueva
        if (!empty($this->usuario_password)) {
            $query .= ", usuario_password = :usuario_password";
        }

        $query .= " WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":usuario_usuario", $this->usuario_usuario);
        $stmt->bindParam(":usuario_nombre", $this->usuario_nombre);
        $stmt->bindParam(":usuario_apellido", $this->usuario_apellido);
        $stmt->bindParam(":usuario_rol_id", $this->usuario_rol_id);
        $stmt->bindParam(":usuario_departamento", $this->usuario_departamento);
        $stmt->bindParam(":modulo_id", $this->usuario_modulo_id);
        $stmt->bindParam(":id", $this->id);

        if (!empty($this->usuario_password)) {
            $stmt->bindParam(":usuario_password", $this->usuario_password);
        }

        return $stmt->execute();
    }

    public function getByUsername($username) {
        $query = "SELECT 
                    id,
                    usuario_alias as usuario_usuario,
                    usuario_nombre,
                    usuario_apellido,
                    usuario_password,
                    usuario_rol_id,
                    usuario_departamento,
                    estado,
                    usuario_creacion,
                    usuario_modificacion
                FROM " . $this->table_name . "
                WHERE usuario_alias = :username AND estado = 'Activo'
                LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAll() {
        $query = "SELECT 
                    u.id,
                    u.usuario_alias as usuario_usuario,
                    u.usuario_nombre,
                    u.usuario_apellido,
                    u.usuario_departamento,
                    u.usuario_rol_id,
                    u.usuario_modulo_id,
                    r.rol_nombre,
                    m.modulo_codigo,
                    u.estado,
                    u.usuario_creacion,
                    u.usuario_modificacion
                FROM " . $this->table_name . " u
                LEFT JOIN roles r ON u.usuario_rol_id = r.id
                LEFT JOIN modulos m ON u.usuario_modulo_id = m.id
                ORDER BY u.id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function toggleStatus() {
        $query = "UPDATE " . $this->table_name . "
                SET estado = :estado
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    public function authenticate($username, $password) {
        $query = "SELECT u.*, r.rol_nombre 
                FROM " . $this->table_name . " u 
                LEFT JOIN roles r ON u.usuario_rol_id = r.id 
                WHERE u.usuario_alias = :username AND u.estado = 'Activo'";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['usuario_password'])) {
            return [
                'success' => true,
                'user_id' => $user['id'],
                'user_nombre' => $user['usuario_nombre'],
                'rol_nombre' => $user['rol_nombre']
            ];
        }

        return ['success' => false, 'message' => 'Usuario o contraseña incorrectos'];
    }

    public function read() {
        $query = "SELECT u.*, r.rol_nombre 
                FROM " . $this->table_name . " u 
                LEFT JOIN roles r ON u.usuario_rol_id = r.id
                ORDER BY u.id DESC";

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

    public function getRolById($rolId) {
        try {
            $query = "SELECT * FROM roles WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $rolId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener rol: " . $e->getMessage());
            return null;
        }
    }

    public function validatePassword($userId, $password) {
        try {
            $query = "SELECT usuario_password FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $userId);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                return ['success' => false, 'message' => 'Usuario no encontrado'];
            }

            if (password_verify($password, $row['usuario_password'])) {
                return ['success' => true];
            }

            return ['success' => false, 'message' => 'Contraseña incorrecta'];
        } catch (PDOException $e) {
            throw new Exception("Error al validar contraseña: " . $e->getMessage());
        }
    }
}
?>
