<?php
require_once __DIR__ . '/../config/database.php';

class Cliente {
    private $conn;
    private $table_name = "clientes";

    public $id;
    public $cliente_empresa;
    public $cliente_nombre;
    public $cliente_apellido;
    public $cliente_direccion;
    public $cliente_telefono;
    public $cliente_correo;
    public $estado;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getAll() {
        $query = "SELECT 
                    id,
                    cliente_empresa,
                    cliente_nombre,
                    cliente_apellido,
                    cliente_direccion,
                    cliente_telefono,
                    cliente_correo,
                    COALESCE(estado, 'Activo') as estado
                FROM " . $this->table_name;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    cliente_empresa = :empresa,
                    cliente_nombre = :nombre,
                    cliente_apellido = :apellido,
                    cliente_telefono = :telefono,
                    cliente_correo = :correo,
                    cliente_direccion = :direccion,
                    estado = :estado";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":empresa", $this->cliente_empresa);
        $stmt->bindParam(":nombre", $this->cliente_nombre);
        $stmt->bindParam(":apellido", $this->cliente_apellido);
        $stmt->bindParam(":telefono", $this->cliente_telefono);
        $stmt->bindParam(":correo", $this->cliente_correo);
        $stmt->bindParam(":direccion", $this->cliente_direccion);
        $stmt->bindParam(":estado", $this->estado);

        return $stmt->execute();
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET
                    cliente_empresa = :empresa,
                    cliente_nombre = :nombre,
                    cliente_apellido = :apellido,
                    cliente_telefono = :telefono,
                    cliente_correo = :correo,
                    cliente_direccion = :direccion
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":empresa", $this->cliente_empresa);
        $stmt->bindParam(":nombre", $this->cliente_nombre);
        $stmt->bindParam(":apellido", $this->cliente_apellido);
        $stmt->bindParam(":telefono", $this->cliente_telefono);
        $stmt->bindParam(":correo", $this->cliente_correo);
        $stmt->bindParam(":direccion", $this->cliente_direccion);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
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
}
