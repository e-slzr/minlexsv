<?php
class Po {
    private $conn;
    private $table_name = "po";

    // Propiedades del objeto
    public $id;
    public $po_numero;
    public $po_fecha_creacion;
    public $po_fecha_inicio_produccion;
    public $po_fecha_fin_produccion;
    public $po_fecha_envio_programada;
    public $po_estado;
    public $po_id_cliente;
    public $po_id_usuario_creacion;
    public $po_tipo_envio;
    public $po_comentario;
    public $po_notas;

    public function __construct() {
        require_once __DIR__ . '/../config/Database.php';
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Leer todas las POs con información relacionada
    public function read($filtros = []) {
        $query = "SELECT 
                    p.*,
                    c.cliente_empresa,
                    u.usuario_nombre as usuario_creacion,
                    COALESCE(
                        (
                            SELECT 
                                ROUND(
                                    (SUM(COALESCE(op.op_cantidad_completada, 0)) * 100.0) / 
                                    NULLIF(SUM(COALESCE(op.op_cantidad_asignada, 0)), 0)
                                )
                            FROM po_detalle pd
                            LEFT JOIN ordenes_produccion op ON op.op_id_pd = pd.id
                            WHERE pd.pd_id_po = p.id
                        ),
                        0
                    ) as progreso
                FROM " . $this->table_name . " p
                LEFT JOIN clientes c ON p.po_id_cliente = c.id
                LEFT JOIN usuarios u ON p.po_id_usuario_creacion = u.id
                WHERE 1=1";

        if (!empty($filtros['po_numero'])) {
            $query .= " AND p.po_numero LIKE :po_numero";
        }
        if (!empty($filtros['estado'])) {
            $query .= " AND p.po_estado = :estado";
        }
        if (!empty($filtros['cliente'])) {
            $query .= " AND c.cliente_empresa LIKE :cliente";
        }
        if (!empty($filtros['fecha_inicio']) && !empty($filtros['fecha_fin'])) {
            $query .= " AND p.po_fecha_creacion BETWEEN :fecha_inicio AND :fecha_fin";
        }

        $query .= " ORDER BY p.id DESC";

        $stmt = $this->conn->prepare($query);

        if (!empty($filtros['po_numero'])) {
            $stmt->bindValue(':po_numero', '%' . $filtros['po_numero'] . '%');
        }
        if (!empty($filtros['estado'])) {
            $stmt->bindValue(':estado', $filtros['estado']);
        }
        if (!empty($filtros['cliente'])) {
            $stmt->bindValue(':cliente', '%' . $filtros['cliente'] . '%');
        }
        if (!empty($filtros['fecha_inicio']) && !empty($filtros['fecha_fin'])) {
            $stmt->bindValue(':fecha_inicio', $filtros['fecha_inicio']);
            $stmt->bindValue(':fecha_fin', $filtros['fecha_fin']);
        }

        try {
            $stmt->execute();
            return $stmt;
        } catch(PDOException $e) {
            error_log("Error en Po::read(): " . $e->getMessage());
            throw $e;
        }
    }

    // Obtener una PO específica con todos sus detalles
    public function readOne() {
        $query = "SELECT 
                    p.*, 
                    c.cliente_empresa,
                    CONCAT(u.usuario_nombre, ' ', u.usuario_apellido) as usuario_creacion
                FROM " . $this->table_name . " p
                LEFT JOIN clientes c ON p.po_id_cliente = c.id
                LEFT JOIN usuarios u ON p.po_id_usuario_creacion = u.id
                WHERE p.id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);

        try {
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error en Po::readOne(): " . $e->getMessage());
            throw $e;
        }
    }

    // Actualizar PO
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET
                    po_fecha_inicio_produccion = :po_fecha_inicio_produccion,
                    po_fecha_fin_produccion = :po_fecha_fin_produccion,
                    po_fecha_envio_programada = :po_fecha_envio_programada,
                    po_estado = :po_estado,
                    po_tipo_envio = :po_tipo_envio,
                    po_comentario = :po_comentario,
                    po_notas = :po_notas
                WHERE 
                    id = :id";

        $stmt = $this->conn->prepare($query);

        // Bindear valores
        $stmt->bindParam(":po_fecha_inicio_produccion", $this->po_fecha_inicio_produccion);
        $stmt->bindParam(":po_fecha_fin_produccion", $this->po_fecha_fin_produccion);
        $stmt->bindParam(":po_fecha_envio_programada", $this->po_fecha_envio_programada);
        $stmt->bindParam(":po_estado", $this->po_estado);
        $stmt->bindParam(":po_tipo_envio", $this->po_tipo_envio);
        $stmt->bindParam(":po_comentario", $this->po_comentario);
        $stmt->bindParam(":po_notas", $this->po_notas);
        $stmt->bindParam(":id", $this->id);

        try {
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Error en Po::update(): " . $e->getMessage());
            throw $e;
        }
    }

    // Eliminar PO
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        
        try {
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Error en Po::delete(): " . $e->getMessage());
            throw $e;
        }
    }

    // Verificar si existe una PO con el mismo número
    public function existsPoNumero($poNumero) {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE po_numero = :po_numero";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':po_numero', $poNumero);
            $stmt->execute();
            
            return $stmt->fetchColumn() > 0;
        } catch(PDOException $e) {
            error_log("Error en Po::existsPoNumero(): " . $e->getMessage());
            throw $e;
        }
    }

    // Obtener el progreso general de la PO
    public function getProgress() {
        $query = "SELECT 
                    COALESCE(
                        ROUND(
                            (SUM(op.op_cantidad_completada) / NULLIF(SUM(op.op_cantidad_asignada), 0)) * 100
                        ), 
                        0
                    ) as progreso
                FROM po_detalle pd
                LEFT JOIN ordenes_produccion op ON op.op_id_pd = pd.id
                WHERE pd.pd_id_po = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        
        try {
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['progreso'];
        } catch(PDOException $e) {
            error_log("Error en Po::getProgress(): " . $e->getMessage());
            throw $e;
        }
    }

    // Iniciar transacción
    public function beginTransaction() {
        return $this->conn->beginTransaction();
    }

    // Confirmar transacción
    public function commit() {
        return $this->conn->commit();
    }

    // Revertir transacción
    public function rollBack() {
        return $this->conn->rollBack();
    }

    // Crear PO
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                (po_numero, po_fecha_creacion, po_fecha_inicio_produccion, 
                 po_fecha_fin_produccion, po_fecha_envio_programada, po_estado,
                 po_id_cliente, po_id_usuario_creacion, po_tipo_envio, po_comentario)
                VALUES
                (:po_numero, :po_fecha_creacion, :po_fecha_inicio_produccion,
                 :po_fecha_fin_produccion, :po_fecha_envio_programada, :po_estado,
                 :po_id_cliente, :po_id_usuario_creacion, :po_tipo_envio, :po_comentario)";

        try {
            $stmt = $this->conn->prepare($query);

            // Sanitizar y asignar valores
            $stmt->bindParam(':po_numero', $this->po_numero);
            $stmt->bindParam(':po_fecha_creacion', $this->po_fecha_creacion);
            $stmt->bindParam(':po_fecha_inicio_produccion', $this->po_fecha_inicio_produccion);
            $stmt->bindParam(':po_fecha_fin_produccion', $this->po_fecha_fin_produccion);
            $stmt->bindParam(':po_fecha_envio_programada', $this->po_fecha_envio_programada);
            $stmt->bindParam(':po_estado', $this->po_estado);
            $stmt->bindParam(':po_id_cliente', $this->po_id_cliente);
            $stmt->bindParam(':po_id_usuario_creacion', $this->po_id_usuario_creacion);
            $stmt->bindParam(':po_tipo_envio', $this->po_tipo_envio);
            $stmt->bindParam(':po_comentario', $this->po_comentario);

            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            return false;

        } catch(PDOException $e) {
            error_log("Error en Po::create(): " . $e->getMessage());
            throw $e;
        }
    }

    // Obtener la conexión PDO
    public function getConnection() {
        return $this->conn;
    }
}
?>
