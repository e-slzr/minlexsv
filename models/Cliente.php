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
        $this->conn = $database->getConnection();
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
                    CASE WHEN estado IS NULL THEN 'Activo' ELSE estado END as estado
                FROM " . $this->table_name;
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Si no hay columna estado, la agregamos manualmente
            if ($result && !array_key_exists('estado', $result[0])) {
                foreach ($result as &$row) {
                    $row['estado'] = 'Activo'; // Por defecto todos los clientes están activos
                }
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error en getAll: " . $e->getMessage());
            // Verifica si el error es por la columna estado
            if (strpos($e->getMessage(), "Unknown column 'estado'") !== false) {
                // Si la columna estado no existe, obtener datos sin ella
                $query = "SELECT 
                            id,
                            cliente_empresa,
                            cliente_nombre,
                            cliente_apellido,
                            cliente_direccion,
                            cliente_telefono,
                            cliente_correo
                        FROM " . $this->table_name;
                
                $stmt = $this->conn->prepare($query);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Agregamos manualmente el estado
                foreach ($result as &$row) {
                    $row['estado'] = 'Activo'; // Por defecto todos los clientes están activos
                }
                
                return $result;
            }
            throw $e;
        }
    }

    public function create() {
        try {
            // Verificar si existe la columna estado
            $checkQuery = "SHOW COLUMNS FROM " . $this->table_name . " LIKE 'estado'";
            $stmt = $this->conn->prepare($checkQuery);
            $stmt->execute();
            $columnExists = $stmt->rowCount() > 0;
            
            if ($columnExists) {
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
                $stmt->bindParam(":estado", $this->estado);
            } else {
                $query = "INSERT INTO " . $this->table_name . "
                        SET
                            cliente_empresa = :empresa,
                            cliente_nombre = :nombre,
                            cliente_apellido = :apellido,
                            cliente_telefono = :telefono,
                            cliente_correo = :correo,
                            cliente_direccion = :direccion";
                
                $stmt = $this->conn->prepare($query);
            }

            $stmt->bindParam(":empresa", $this->cliente_empresa);
            $stmt->bindParam(":nombre", $this->cliente_nombre);
            $stmt->bindParam(":apellido", $this->cliente_apellido);
            $stmt->bindParam(":telefono", $this->cliente_telefono);
            $stmt->bindParam(":correo", $this->cliente_correo);
            $stmt->bindParam(":direccion", $this->cliente_direccion);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en create: " . $e->getMessage());
            return false;
        }
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
        try {
            // Verificar si existe la columna estado
            $checkQuery = "SHOW COLUMNS FROM " . $this->table_name . " LIKE 'estado'";
            $stmt = $this->conn->prepare($checkQuery);
            $stmt->execute();
            $columnExists = $stmt->rowCount() > 0;
            
            if (!$columnExists) {
                // Si la columna no existe, la creamos
                $alterQuery = "ALTER TABLE " . $this->table_name . " ADD COLUMN estado VARCHAR(10) DEFAULT 'Activo'";
                $stmt = $this->conn->prepare($alterQuery);
                $stmt->execute();
            }
            
            // Ahora actualizamos el estado
            $query = "UPDATE " . $this->table_name . "
                    SET estado = :estado
                    WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":estado", $this->estado);
            $stmt->bindParam(":id", $this->id);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en toggleStatus: " . $e->getMessage());
            return false;
        }
    }
}
