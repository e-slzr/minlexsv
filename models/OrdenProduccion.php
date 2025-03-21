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
                    pd.pd_item,
                    i.item_numero, i.item_nombre
                    FROM " . $this->table_name . " op
                    LEFT JOIN usuarios u ON op.op_operador_asignado = u.id
                    LEFT JOIN procesos_produccion pp ON op.op_id_proceso = pp.id
                    LEFT JOIN po_detalle pd ON op.op_id_pd = pd.id
                    LEFT JOIN items i ON pd.pd_item = i.id
                    WHERE op.id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en OrdenProduccion::getById: " . $e->getMessage());
            throw new Exception("Error al obtener la orden de producción", 0, $e);
        }
    }

    public function create($data) {
        try {
            $query = "INSERT INTO " . $this->table_name . " 
                    (op_id_pd, op_operador_asignado, op_id_proceso, op_fecha_inicio, 
                     op_fecha_fin, op_estado, op_cantidad_asignada, op_comentario) 
                    VALUES 
                    (:op_id_pd, :op_operador_asignado, :op_id_proceso, :op_fecha_inicio,
                     :op_fecha_fin, :op_estado, :op_cantidad_asignada, :op_comentario)";
            
            $stmt = $this->conn->prepare($query);
            
            // Bind de los valores
            $stmt->bindValue(':op_id_pd', $data['op_id_pd']);
            $stmt->bindValue(':op_operador_asignado', $data['op_operador_asignado']);
            $stmt->bindValue(':op_id_proceso', $data['op_id_proceso']);
            $stmt->bindValue(':op_fecha_inicio', $data['op_fecha_inicio']);
            $stmt->bindValue(':op_fecha_fin', $data['op_fecha_fin'] ?? null);
            $stmt->bindValue(':op_estado', $data['op_estado'] ?? 'Pendiente');
            $stmt->bindValue(':op_cantidad_asignada', $data['op_cantidad_asignada']);
            $stmt->bindValue(':op_comentario', $data['op_comentario'] ?? null);
            
            $stmt->execute();
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error en OrdenProduccion::create: " . $e->getMessage());
            throw new Exception("Error al crear la orden de producción", 0, $e);
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
                        op_comentario = :op_comentario
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
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en OrdenProduccion::update: " . $e->getMessage());
            throw new Exception("Error al actualizar la orden de producción", 0, $e);
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
                    i.item_numero, i.item_nombre
                    FROM " . $this->table_name . " op
                    LEFT JOIN usuarios u ON op.op_operador_asignado = u.id
                    LEFT JOIN procesos_produccion pp ON op.op_id_proceso = pp.id
                    LEFT JOIN po_detalle pd ON op.op_id_pd = pd.id
                    LEFT JOIN items i ON pd.pd_item = i.id
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
}
