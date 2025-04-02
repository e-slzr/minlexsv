<?php
require_once __DIR__ . '/../config/Database.php';

class OrdenProduccion {
    private $conn;
    private $table_name = "ordenes_produccion";

    public function __construct($db) {
        if (!$db instanceof PDO) {
            throw new Exception("Se requiere una conexión a la base de datos válida");
        }
        $this->conn = $db;
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getAll() {
        try {
            $query = "SELECT op.*, 
                    u.usuario_nombre, u.usuario_apellido,
                    pp.pp_nombre,
                    pd.pd_item,
                    i.item_numero, i.item_nombre
                    FROM " . $this->table_name . " op
                    LEFT JOIN usuarios u ON op.op_operador_asignado = u.id
                    LEFT JOIN procesos_produccion pp ON op.op_id_proceso = pp.id
                    LEFT JOIN po_detalle pd ON op.op_id_pd = pd.id
                    LEFT JOIN items i ON pd.pd_item = i.id
                    ORDER BY op.id DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en OrdenProduccion::getAll: " . $e->getMessage());
            throw new Exception("Error al obtener las órdenes de producción", 0, $e);
        }
    }

    public function getById($id) {
        try {
            $query = "SELECT op.*, 
                    u.usuario_nombre, u.usuario_apellido,
                    pp.pp_nombre,
                    pd.pd_item, pd.pd_cant_piezas_total,
                    i.item_numero, i.item_nombre, i.item_talla,
                    m.modulo_codigo
                    FROM " . $this->table_name . " op
                    LEFT JOIN usuarios u ON op.op_operador_asignado = u.id
                    LEFT JOIN procesos_produccion pp ON op.op_id_proceso = pp.id
                    LEFT JOIN po_detalle pd ON op.op_id_pd = pd.id
                    LEFT JOIN items i ON pd.pd_item = i.id
                    LEFT JOIN modulos m ON op.op_modulo_id = m.id
                    WHERE op.id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en OrdenProduccion::getById: " . $e->getMessage());
            throw new Exception("Error al obtener la orden de producción: " . $e->getMessage(), 0, $e);
        }
    }

    public function create($data) {
        try {
            $query = "INSERT INTO " . $this->table_name . " 
                    (op_id_pd, op_operador_asignado, op_id_proceso, op_fecha_inicio, 
                     op_fecha_fin, op_estado, op_cantidad_asignada, op_cantidad_completada,
                     op_comentario, op_modulo_id, op_fecha_creacion) 
                    VALUES 
                    (:op_id_pd, :op_operador_asignado, :op_id_proceso, :op_fecha_inicio,
                     :op_fecha_fin, :op_estado, :op_cantidad_asignada, :op_cantidad_completada,
                     :op_comentario, :op_modulo_id, NOW())";
            
            $stmt = $this->conn->prepare($query);
            
            // Bind de los valores
            $stmt->bindValue(':op_id_pd', $data['op_id_pd']);
            $stmt->bindValue(':op_operador_asignado', $data['op_operador_asignado']);
            $stmt->bindValue(':op_id_proceso', $data['op_id_proceso']);
            $stmt->bindValue(':op_fecha_inicio', $data['op_fecha_inicio']);
            $stmt->bindValue(':op_fecha_fin', $data['op_fecha_fin'] ?? null);
            $stmt->bindValue(':op_estado', $data['op_estado'] ?? 'Pendiente');
            $stmt->bindValue(':op_cantidad_asignada', $data['op_cantidad_asignada']);
            $stmt->bindValue(':op_cantidad_completada', $data['op_cantidad_completada'] ?? 0);
            $stmt->bindValue(':op_comentario', $data['op_comentario'] ?? null);
            $stmt->bindValue(':op_modulo_id', $data['op_modulo_id'] ?? null);
            
            $stmt->execute();
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error en OrdenProduccion::create: " . $e->getMessage());
            throw new Exception("Error al crear la orden de producción: " . $e->getMessage(), 0, $e);
        }
    }

    public function update($data) {
        try {
            $query = "UPDATE " . $this->table_name . " 
                    SET op_operador_asignado = :op_operador_asignado,
                        op_id_proceso = :op_id_proceso,
                        op_fecha_inicio = :op_fecha_inicio,
                        op_fecha_fin = :op_fecha_fin,
                        op_estado = :op_estado,
                        op_cantidad_asignada = :op_cantidad_asignada,
                        op_cantidad_completada = :op_cantidad_completada,
                        op_comentario = :op_comentario,
                        op_modulo_id = :op_modulo_id,
                        op_fecha_modificacion = NOW()
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            
            // Bind de los valores
            $stmt->bindValue(':id', $data['id']);
            $stmt->bindValue(':op_operador_asignado', $data['op_operador_asignado']);
            $stmt->bindValue(':op_id_proceso', $data['op_id_proceso']);
            $stmt->bindValue(':op_fecha_inicio', $data['op_fecha_inicio']);
            $stmt->bindValue(':op_fecha_fin', $data['op_fecha_fin'] ?? null);
            $stmt->bindValue(':op_estado', $data['op_estado']);
            $stmt->bindValue(':op_cantidad_asignada', $data['op_cantidad_asignada']);
            $stmt->bindValue(':op_cantidad_completada', $data['op_cantidad_completada'] ?? 0);
            $stmt->bindValue(':op_comentario', $data['op_comentario'] ?? null);
            $stmt->bindValue(':op_modulo_id', $data['op_modulo_id'] ?? null);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en OrdenProduccion::update: " . $e->getMessage());
            throw new Exception("Error al actualizar la orden de producción: " . $e->getMessage(), 0, $e);
        }
    }

    public function delete($id) {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en OrdenProduccion::delete: " . $e->getMessage());
            throw new Exception("Error al eliminar la orden de producción", 0, $e);
        }
    }

    public function searchOrdenes($filters = []) {
        try {
            $query = "SELECT op.*, 
                    u.usuario_nombre, u.usuario_apellido,
                    pp.pp_nombre,
                    pd.pd_item,
                    i.item_numero, i.item_nombre,
                    p.po_numero,
                    m.modulo_codigo
                    FROM " . $this->table_name . " op
                    LEFT JOIN usuarios u ON op.op_operador_asignado = u.id
                    LEFT JOIN procesos_produccion pp ON op.op_id_proceso = pp.id
                    LEFT JOIN po_detalle pd ON op.op_id_pd = pd.id
                    LEFT JOIN items i ON pd.pd_item = i.id
                    LEFT JOIN po p ON pd.pd_id_po = p.id
                    LEFT JOIN modulos m ON op.op_modulo_id = m.id
                    WHERE 1=1";
            
            $params = [];

            if (!empty($filters['item_numero'])) {
                $query .= " AND i.item_numero LIKE :item_numero";
                $params[':item_numero'] = "%" . $filters['item_numero'] . "%";
            }

            if (!empty($filters['operador'])) {
                $query .= " AND (u.usuario_nombre LIKE :operador OR u.usuario_apellido LIKE :operador)";
                $params[':operador'] = "%" . $filters['operador'] . "%";
            }

            if (!empty($filters['estado'])) {
                $query .= " AND op.op_estado = :estado";
                $params[':estado'] = $filters['estado'];
            }

            if (!empty($filters['fecha_inicio'])) {
                $query .= " AND op.op_fecha_inicio >= :fecha_inicio";
                $params[':fecha_inicio'] = $filters['fecha_inicio'];
            }

            if (!empty($filters['fecha_fin'])) {
                $query .= " AND op.op_fecha_fin <= :fecha_fin";
                $params[':fecha_fin'] = $filters['fecha_fin'];
            }
            
            if (!empty($filters['po_numero'])) {
                $query .= " AND p.po_numero LIKE :po_numero";
                $params[':po_numero'] = "%" . $filters['po_numero'] . "%";
            }

            $query .= " ORDER BY op.id DESC";

            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en OrdenProduccion::searchOrdenes: " . $e->getMessage());
            throw new Exception("Error al buscar órdenes de producción", 0, $e);
        }
    }

    // Actualizar solo el progreso y estado de una orden de producción
    public function updateProgress($data) {
        try {
            $query = "UPDATE " . $this->table_name . " 
                    SET op_cantidad_completada = :op_cantidad_completada,
                        op_estado = :op_estado,
                        op_comentario = :op_comentario";
            
            // Añadir fecha de fin si se proporciona
            if (isset($data['op_fecha_fin'])) {
                $query .= ", op_fecha_fin = :op_fecha_fin";
            }
            
            $query .= " WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            
            // Bind de los valores
            $stmt->bindValue(':id', $data['id']);
            $stmt->bindValue(':op_cantidad_completada', $data['op_cantidad_completada']);
            $stmt->bindValue(':op_estado', $data['op_estado']);
            $stmt->bindValue(':op_comentario', $data['op_comentario'] ?? null);
            
            if (isset($data['op_fecha_fin'])) {
                $stmt->bindValue(':op_fecha_fin', $data['op_fecha_fin']);
            }
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en OrdenProduccion::updateProgress: " . $e->getMessage());
            throw new Exception("Error al actualizar el progreso de la orden", 0, $e);
        }
    }

    // Actualizar el estado de aprobación de una orden
    public function updateAprobacion($data) {
        try {
            $query = "UPDATE " . $this->table_name . " 
                    SET op_usuario_aprobacion = :op_usuario_aprobacion,
                        op_fecha_aprobacion = :op_fecha_aprobacion,
                        op_estado_aprobacion = :op_estado_aprobacion";
            
            // Añadir motivo de rechazo si se proporciona
            if (isset($data['op_motivo_rechazo'])) {
                $query .= ", op_motivo_rechazo = :op_motivo_rechazo";
            }
            
            // Añadir comentario si se proporciona
            if (isset($data['op_comentario'])) {
                $query .= ", op_comentario = :op_comentario";
            }
            
            $query .= " WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            
            // Bind de los valores
            $stmt->bindValue(':id', $data['id']);
            $stmt->bindValue(':op_usuario_aprobacion', $data['op_usuario_aprobacion']);
            $stmt->bindValue(':op_fecha_aprobacion', $data['op_fecha_aprobacion']);
            $stmt->bindValue(':op_estado_aprobacion', $data['op_estado_aprobacion']);
            
            if (isset($data['op_motivo_rechazo'])) {
                $stmt->bindValue(':op_motivo_rechazo', $data['op_motivo_rechazo']);
            }
            
            if (isset($data['op_comentario'])) {
                $stmt->bindValue(':op_comentario', $data['op_comentario']);
            }
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en OrdenProduccion::updateAprobacion: " . $e->getMessage());
            throw new Exception("Error al actualizar el estado de aprobación de la orden", 0, $e);
        }
    }

    // Obtener órdenes filtradas
    public function getFiltered($filters = []) {
        try {
            $query = "SELECT op.*, 
                    u.usuario_nombre, u.usuario_apellido,
                    pp.pp_nombre,
                    pd.pd_item,
                    i.item_numero, i.item_nombre,
                    po.po_numero
                    FROM " . $this->table_name . " op
                    LEFT JOIN usuarios u ON op.op_operador_asignado = u.id
                    LEFT JOIN procesos_produccion pp ON op.op_id_proceso = pp.id
                    LEFT JOIN po_detalle pd ON op.op_id_pd = pd.id
                    LEFT JOIN items i ON pd.pd_item = i.id
                    LEFT JOIN po po ON pd.pd_id_po = po.id
                    WHERE 1=1";
            
            $params = [];
            
            // Añadir filtros si existen
            if (isset($filters['operador']) && $filters['operador'] > 0) {
                $query .= " AND op.op_operador_asignado = :operador";
                $params[':operador'] = $filters['operador'];
            }
            
            if (isset($filters['proceso']) && $filters['proceso'] > 0) {
                $query .= " AND op.op_id_proceso = :proceso";
                $params[':proceso'] = $filters['proceso'];
            }
            
            if (isset($filters['estado']) && !empty($filters['estado'])) {
                $query .= " AND op.op_estado = :estado";
                $params[':estado'] = $filters['estado'];
            }
            
            if (isset($filters['item']) && !empty($filters['item'])) {
                $query .= " AND (i.item_numero LIKE :item OR i.item_nombre LIKE :item)";
                $params[':item'] = '%' . $filters['item'] . '%';
            }
            
            $query .= " ORDER BY op.id DESC";
            
            $stmt = $this->conn->prepare($query);
            
            // Bind de parámetros
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en OrdenProduccion::getFiltered: " . $e->getMessage());
            // En lugar de lanzar una excepción, retornamos un array vacío para evitar errores
            return [];
        }
    }

    /**
     * Obtiene las órdenes de producción pendientes filtradas por PO y proceso
     * @param array $filters Filtros a aplicar (po, proceso)
     * @return array Lista de órdenes pendientes
     */
    public function getOrdenesPendientes($filters = []) {
        try {
            $query = "SELECT op.*, 
                    u.usuario_nombre, u.usuario_apellido,
                    pp.pp_nombre as proceso_nombre,
                    pd.pd_cant_piezas_total as cantidad_total,
                    pd.pd_pcs_carton, pd.pd_pcs_poly,
                    COALESCE(SUM(pa.pa_cantidad_completada), 0) as cantidad_completada,
                    i.item_numero, i.item_nombre, i.item_talla, i.item_descripcion,
                    po.po_numero,
                    (SELECT c.color_nombre FROM item_colores ic JOIN colores c ON ic.ic_id_color = c.id WHERE ic.ic_id_item = i.id LIMIT 1) as item_color,
                    (SELECT d.diseno_nombre FROM item_disenos id JOIN disenos d ON id.id_id_diseno = d.id WHERE id.id_id_item = i.id LIMIT 1) as item_diseno,
                    (SELECT ub.ubicacion_nombre FROM item_ubicaciones iu JOIN ubicaciones ub ON iu.iu_id_ubicacion = ub.id WHERE iu.iu_id_item = i.id LIMIT 1) as item_ubicacion
                    FROM " . $this->table_name . " op
                    LEFT JOIN usuarios u ON op.op_operador_asignado = u.id
                    LEFT JOIN procesos_produccion pp ON op.op_id_proceso = pp.id
                    LEFT JOIN po_detalle pd ON op.op_id_pd = pd.id
                    LEFT JOIN items i ON pd.pd_item = i.id
                    LEFT JOIN po ON pd.pd_id_po = po.id
                    LEFT JOIN produccion_avance pa ON pa.pa_id_orden_produccion = op.id
                    WHERE op.op_estado IN ('Pendiente', 'En proceso')";

            $params = [];
            
            // Aplicar filtros
            if (!empty($filters['po'])) {
                $query .= " AND pd.pd_id_po = :po_id";
                $params[':po_id'] = $filters['po'];
            }
            
            if (!empty($filters['proceso'])) {
                $query .= " AND op.op_id_proceso = :proceso_id";
                $params[':proceso_id'] = $filters['proceso'];
            }
            
            $query .= " GROUP BY op.id ORDER BY op.id DESC";
            
            $stmt = $this->conn->prepare($query);
            
            // Bind de parámetros
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en OrdenProduccion::getOrdenesPendientes: " . $e->getMessage());
            throw new Exception("Error al obtener las órdenes de producción pendientes", 0, $e);
        }
    }
}
