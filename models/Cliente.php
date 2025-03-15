<?php
require_once __DIR__ . '/../config/database.php';

class Cliente {
    // Conexión a la base de datos
    private $conn;
    // Nombre de la tabla
    private $table_name = "clientes";

    // Propiedades del objeto (igual que los campos de la tabla)
    public $id;
    public $cliente_empresa;
    public $cliente_nombre;
    public $cliente_apellido;
    public $cliente_direccion;
    public $cliente_telefono;
    public $cliente_correo;

    // Constructor: inicializa la conexión a la base de datos
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Crear un nuevo cliente
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    cliente_empresa = :empresa,
                    cliente_nombre = :nombre,
                    cliente_apellido = :apellido,
                    cliente_direccion = :direccion,
                    cliente_telefono = :telefono,
                    cliente_correo = :correo";

        $stmt = $this->conn->prepare($query);

        // Limpiamos los datos para evitar inyección SQL
        $this->cliente_empresa = htmlspecialchars(strip_tags($this->cliente_empresa));
        $this->cliente_nombre = htmlspecialchars(strip_tags($this->cliente_nombre));
        $this->cliente_apellido = htmlspecialchars(strip_tags($this->cliente_apellido));
        $this->cliente_direccion = htmlspecialchars(strip_tags($this->cliente_direccion));
        $this->cliente_telefono = htmlspecialchars(strip_tags($this->cliente_telefono));
        $this->cliente_correo = htmlspecialchars(strip_tags($this->cliente_correo));

        // Vinculamos los valores
        $stmt->bindParam(":empresa", $this->cliente_empresa);
        $stmt->bindParam(":nombre", $this->cliente_nombre);
        $stmt->bindParam(":apellido", $this->cliente_apellido);
        $stmt->bindParam(":direccion", $this->cliente_direccion);
        $stmt->bindParam(":telefono", $this->cliente_telefono);
        $stmt->bindParam(":correo", $this->cliente_correo);

        // Ejecutamos la consulta
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Leer todos los clientes
    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY cliente_empresa ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Leer un solo cliente
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->cliente_empresa = $row['cliente_empresa'];
            $this->cliente_nombre = $row['cliente_nombre'];
            $this->cliente_apellido = $row['cliente_apellido'];
            $this->cliente_direccion = $row['cliente_direccion'];
            $this->cliente_telefono = $row['cliente_telefono'];
            $this->cliente_correo = $row['cliente_correo'];
            return true;
        }
        return false;
    }

    // Actualizar un cliente
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET
                    cliente_empresa = :empresa,
                    cliente_nombre = :nombre,
                    cliente_apellido = :apellido,
                    cliente_direccion = :direccion,
                    cliente_telefono = :telefono,
                    cliente_correo = :correo
                WHERE
                    id = :id";

        $stmt = $this->conn->prepare($query);

        // Limpiamos los datos
        $this->cliente_empresa = htmlspecialchars(strip_tags($this->cliente_empresa));
        $this->cliente_nombre = htmlspecialchars(strip_tags($this->cliente_nombre));
        $this->cliente_apellido = htmlspecialchars(strip_tags($this->cliente_apellido));
        $this->cliente_direccion = htmlspecialchars(strip_tags($this->cliente_direccion));
        $this->cliente_telefono = htmlspecialchars(strip_tags($this->cliente_telefono));
        $this->cliente_correo = htmlspecialchars(strip_tags($this->cliente_correo));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Vinculamos los valores
        $stmt->bindParam(":empresa", $this->cliente_empresa);
        $stmt->bindParam(":nombre", $this->cliente_nombre);
        $stmt->bindParam(":apellido", $this->cliente_apellido);
        $stmt->bindParam(":direccion", $this->cliente_direccion);
        $stmt->bindParam(":telefono", $this->cliente_telefono);
        $stmt->bindParam(":correo", $this->cliente_correo);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Eliminar un cliente
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

    // Buscar clientes
    public function search($keyword) {
        $query = "SELECT * FROM " . $this->table_name . "
                WHERE
                    cliente_empresa LIKE ? OR
                    cliente_nombre LIKE ? OR
                    cliente_apellido LIKE ? OR
                    cliente_correo LIKE ?
                ORDER BY
                    cliente_empresa ASC";

        $stmt = $this->conn->prepare($query);

        $keyword = "%{$keyword}%";
        
        $stmt->bindParam(1, $keyword);
        $stmt->bindParam(2, $keyword);
        $stmt->bindParam(3, $keyword);
        $stmt->bindParam(4, $keyword);

        $stmt->execute();
        return $stmt;
    }
}
?>
