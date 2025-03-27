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

    public function __construct($db = null) {
        if ($db === null) {
            $database = new Database();
            $this->conn = $database->getConnection();
        } else {
            $this->conn = $db;
        }
    }

    // Leer todas las POs con información relacionada
    public function read($filtros = []) {
        $sql = "SELECT p.*, 
                c.cliente_empresa,
                CONCAT(u.usuario_nombre, ' ', u.usuario_apellido) as usuario_creacion,
                COALESCE(
                    (SELECT ROUND(
                        (SUM(COALESCE(op.op_cantidad_completada, 0)) / NULLIF(SUM(COALESCE(op.op_cantidad_asignada, 0)), 0)) * 100
                    ) 
                    FROM po_detalle pd
                    LEFT JOIN ordenes_produccion op ON op.op_id_pd = pd.id
                    WHERE pd.pd_id_po = p.id), 0
                ) as progreso
                FROM " . $this->table_name . " p
                LEFT JOIN clientes c ON p.po_id_cliente = c.id
                LEFT JOIN usuarios u ON p.po_id_usuario_creacion = u.id";
        
        // Agregar cualquier filtro necesario
        if (!empty($filtros)) {
            if (isset($filtros['po_numero'])) {
                $sql .= " WHERE p.po_numero LIKE '%" . $filtros['po_numero'] . "%'";
            }
            if (isset($filtros['estado'])) {
                $sql .= isset($filtros['po_numero']) ? " AND" : " WHERE";
                $sql .= " p.po_estado = '" . $filtros['estado'] . "'";
            }
            if (isset($filtros['cliente'])) {
                $sql .= (isset($filtros['po_numero']) || isset($filtros['estado'])) ? " AND" : " WHERE";
                $sql .= " p.po_id_cliente = " . $filtros['cliente'];
            }
            if (isset($filtros['fecha_inicio']) && isset($filtros['fecha_fin'])) {
                $sql .= (isset($filtros['po_numero']) || isset($filtros['estado']) || isset($filtros['cliente'])) ? " AND" : " WHERE";
                $sql .= " p.po_fecha_creacion BETWEEN '" . $filtros['fecha_inicio'] . "' AND '" . $filtros['fecha_fin'] . "'";
            }
            if (isset($filtros['estados']) && is_array($filtros['estados'])) {
                $sql .= (isset($filtros['po_numero']) || isset($filtros['estado']) || isset($filtros['cliente']) || (isset($filtros['fecha_inicio']) && isset($filtros['fecha_fin']))) ? " AND" : " WHERE";
                $sql .= " p.po_estado IN ('" . implode("', '", $filtros['estados']) . "')";
            }
        }
        
        return $this->conn->query($sql);
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

    // Obtener una PO por su ID
    public function getById($id) {
        try {
            $query = "SELECT 
                    po.*,
                    c.cliente_nombre, c.cliente_empresa,
                    u.usuario_nombre, u.usuario_apellido,
                    (SELECT COUNT(*) FROM po_detalle pd WHERE pd.pd_id_po = po.id) as total_items,
                    (SELECT COUNT(*) FROM po_detalle pd 
                     JOIN ordenes_produccion op ON pd.id = op.op_id_pd 
                     WHERE pd.pd_id_po = po.id AND op.op_estado = 'Completado') as items_completados,
                    (SELECT SUM(pd.pd_cant_piezas_total * pd.pd_precio_unitario) 
                     FROM po_detalle pd 
                     WHERE pd.pd_id_po = po.id) as total_valor,
                    (SELECT GROUP_CONCAT(DISTINCT pc.pc_estado) 
                     FROM pruebas_calidad pc 
                     WHERE pc.pc_id_po = po.id) as estados_calidad
                    FROM " . $this->table_name . " po
                    LEFT JOIN clientes c ON po.po_id_cliente = c.id
                    LEFT JOIN usuarios u ON po.po_id_usuario_creacion = u.id
                    WHERE po.id = :id";


            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Po::getById: " . $e->getMessage());
            throw new Exception("Error al obtener la PO", 0, $e);
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

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $this->id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC)['progreso'];
        } catch(PDOException $e) {
            error_log("Error en Po::getProgress(): " . $e->getMessage());
            return 0;
        }
    }

    // Obtener detalles completos de la PO incluyendo items y pruebas
    public function getDetallesCompletos($id) {
        try {
            // Obtener información básica de la PO
            $po = $this->getById($id);
            if (!$po) return null;

            // Obtener detalles de items con información adicional
            $query = "SELECT 
                pd.*,
                i.item_numero,
                i.item_nombre,
                i.item_descripcion,
                i.item_talla,
                i.item_color,
                i.item_diseno,
                i.item_ubicacion,
                COALESCE(
                    ROUND(
                        (SUM(op.op_cantidad_completada) / NULLIF(SUM(op.op_cantidad_asignada), 0)) * 100
                    ),
                    0
                ) as progreso,
                (SELECT GROUP_CONCAT(DISTINCT rp.rp_resultado)
                 FROM resultados_pruebas rp
                 WHERE rp.rp_po_detalle_id = pd.id) as resultados_pruebas,
                (SELECT COUNT(*) FROM ordenes_produccion op WHERE op.op_id_pd = pd.id) as total_ordenes,
                (SELECT COUNT(*) FROM ordenes_produccion op WHERE op.op_id_pd = pd.id AND op.op_estado = 'Completado') as ordenes_completadas,
                (SELECT GROUP_CONCAT(DISTINCT p.proceso_nombre)
                 FROM ordenes_produccion op
                 LEFT JOIN procesos p ON op.op_id_proceso = p.id
                 WHERE op.op_id_pd = pd.id) as procesos_asignados
            FROM po_detalle pd
            LEFT JOIN items i ON pd.pd_item = i.id
            LEFT JOIN ordenes_produccion op ON op.op_id_pd = pd.id
            WHERE pd.pd_id_po = :id
            GROUP BY pd.id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $po['detalles'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Obtener pruebas de calidad
            $query = "SELECT 
                pc.*,
                tp.tp_nombre,
                tp.tp_descripcion
            FROM pruebas_calidad pc
            LEFT JOIN tipos_pruebas tp ON pc.pc_id_tipo_prueba = tp.id
            WHERE pc.pc_id_po = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $po['pruebas_calidad'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $po;
        } catch (PDOException $e) {
            error_log("Error en Po::getDetallesCompletos: " . $e->getMessage());
            throw new Exception("Error al obtener los detalles completos de la PO", 0, $e);
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
    public function create($data) {
        try {
            $query = "INSERT INTO " . $this->table_name . "
                    (po_numero, po_fecha_creacion, po_fecha_inicio_produccion, 
                     po_fecha_fin_produccion, po_fecha_envio_programada, 
                     po_estado, po_id_cliente, po_id_usuario_creacion,
                     po_tipo_envio, po_comentario)
                    VALUES
                    (:po_numero, NOW(), :po_fecha_inicio_produccion,
                     :po_fecha_fin_produccion, :po_fecha_envio_programada,
                     :po_estado, :po_id_cliente, :po_id_usuario_creacion,
                     :po_tipo_envio, :po_comentario)";

            $stmt = $this->conn->prepare($query);

            // Bind values
            $stmt->bindParam(":po_numero", $data['po_numero']);
            $stmt->bindParam(":po_fecha_inicio_produccion", $data['po_fecha_inicio_produccion']);
            $stmt->bindParam(":po_fecha_fin_produccion", $data['po_fecha_fin_produccion']);
            $stmt->bindParam(":po_fecha_envio_programada", $data['po_fecha_envio_programada']);
            $stmt->bindParam(":po_estado", $data['po_estado']);
            $stmt->bindParam(":po_id_cliente", $data['po_id_cliente']);
            $stmt->bindParam(":po_id_usuario_creacion", $data['po_id_usuario_creacion']);
            $stmt->bindParam(":po_tipo_envio", $data['po_tipo_envio']);
            $stmt->bindParam(":po_comentario", $data['po_comentario']);

            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            throw new Exception("Error al crear PO: " . $e->getMessage());
        }
    }

    // Obtener la conexión PDO
    public function getConnection() {
        return $this->conn;
    }
}
?>
