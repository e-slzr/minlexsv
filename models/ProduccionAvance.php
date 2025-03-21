<?php
class ProduccionAvance {
    private $conn;
    private $table_name = "produccion_avance";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function registrarAvance($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                 (orden_produccion_id, proceso_id, cantidad_completada, 
                  fecha_registro, operario_id, observaciones, estado) 
                 VALUES 
                 (:orden_produccion_id, :proceso_id, :cantidad_completada,
                  :fecha_registro, :operario_id, :observaciones, :estado)";

        try {
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(":orden_produccion_id", $data['orden_produccion_id']);
            $stmt->bindParam(":proceso_id", $data['proceso_id']);
            $stmt->bindParam(":cantidad_completada", $data['cantidad_completada']);
            $stmt->bindParam(":fecha_registro", $data['fecha_registro']);
            $stmt->bindParam(":operario_id", $data['operario_id']);
            $stmt->bindParam(":observaciones", $data['observaciones']);
            $stmt->bindParam(":estado", $data['estado']);

            if($stmt->execute()) {
                return true;
            }
            return false;
        } catch(PDOException $e) {
            error_log("Error al registrar avance: " . $e->getMessage());
            return false;
        }
    }

    public function getAvancesByOrdenProduccion($orden_produccion_id) {
        $query = "SELECT pa.*, pp.pp_nombre as proceso_nombre, u.nombre_completo as operario_nombre 
                 FROM " . $this->table_name . " pa
                 LEFT JOIN procesos_produccion pp ON pa.proceso_id = pp.id
                 LEFT JOIN usuarios u ON pa.operario_id = u.id
                 WHERE pa.orden_produccion_id = :orden_produccion_id
                 ORDER BY pa.fecha_registro DESC";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":orden_produccion_id", $orden_produccion_id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error al obtener avances: " . $e->getMessage());
            return false;
        }
    }

    public function getTotalCompletadoByOrden($orden_produccion_id, $proceso_id) {
        $query = "SELECT SUM(cantidad_completada) as total 
                 FROM " . $this->table_name . "
                 WHERE orden_produccion_id = :orden_produccion_id 
                 AND proceso_id = :proceso_id";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":orden_produccion_id", $orden_produccion_id);
            $stmt->bindParam(":proceso_id", $proceso_id);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['total'] ?? 0;
        } catch(PDOException $e) {
            error_log("Error al obtener total completado: " . $e->getMessage());
            return 0;
        }
    }
}
