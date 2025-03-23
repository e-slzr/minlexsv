<?php
class Dashboard {
    private $conn;

    public function __construct() {
        require_once __DIR__ . '/../config/Database.php';
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getStats() {
        try {
            $stats = [];

            // Total POs
            $query = "SELECT 
                        COUNT(*) as total_pos,
                        SUM(CASE WHEN po_estado = 'En proceso' THEN 1 ELSE 0 END) as pos_en_proceso,
                        SUM(CASE WHEN po_estado = 'Completada' THEN 1 ELSE 0 END) as pos_completadas,
                        SUM(CASE WHEN po_estado = 'Pendiente' THEN 1 ELSE 0 END) as pos_pendientes
                     FROM po";
            $stmt = $this->conn->query($query);
            $stats['pos'] = $stmt->fetch(PDO::FETCH_ASSOC);

            // Clientes activos (con POs en los últimos 6 meses)
            $query = "SELECT COUNT(DISTINCT c.id) as clientes_activos,
                            (SELECT COUNT(*) FROM clientes) as total_clientes
                     FROM clientes c
                     JOIN po p ON p.po_id_cliente = c.id
                     WHERE p.po_fecha_creacion >= DATE_SUB(NOW(), INTERVAL 6 MONTH)";
            $stmt = $this->conn->query($query);
            $stats['clientes'] = $stmt->fetch(PDO::FETCH_ASSOC);

            // Top 5 clientes por número de POs
            $query = "SELECT c.cliente_empresa, COUNT(p.id) as total_pos
                     FROM clientes c
                     JOIN po p ON p.po_id_cliente = c.id
                     WHERE p.po_fecha_creacion >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                     GROUP BY c.id
                     ORDER BY total_pos DESC
                     LIMIT 5";
            $stmt = $this->conn->query($query);
            $stats['top_clientes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Progreso promedio de POs activas
            $query = "SELECT 
                        COALESCE(
                            ROUND(
                                AVG(
                                    CASE 
                                        WHEN op.op_cantidad_asignada > 0 
                                        THEN (op.op_cantidad_completada * 100.0 / op.op_cantidad_asignada)
                                        ELSE 0 
                                    END
                                )
                            ),
                            0
                        ) as progreso_promedio
                     FROM po p
                     JOIN po_detalle pd ON pd.pd_id_po = p.id
                     LEFT JOIN ordenes_produccion op ON op.op_id_pd = pd.id
                     WHERE p.po_estado = 'En proceso'";
            $stmt = $this->conn->query($query);
            $stats['progreso'] = $stmt->fetch(PDO::FETCH_ASSOC);

            // POs por mes (últimos 6 meses)
            $query = "SELECT 
                        DATE_FORMAT(po_fecha_creacion, '%Y-%m') as mes,
                        COUNT(*) as total
                     FROM po
                     WHERE po_fecha_creacion >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                     GROUP BY mes
                     ORDER BY mes DESC";
            $stmt = $this->conn->query($query);
            $stats['pos_por_mes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Usuarios activos
            $query = "SELECT 
                        COUNT(*) as total_usuarios,
                        SUM(CASE WHEN estado = 1 THEN 1 ELSE 0 END) as usuarios_activos
                     FROM usuarios";
            $stmt = $this->conn->query($query);
            $stats['usuarios'] = $stmt->fetch(PDO::FETCH_ASSOC);

            // Obtener estados de POs
            $query = "SELECT 
                SUM(CASE WHEN po_estado = 'En Proceso' THEN 1 ELSE 0 END) as en_proceso,
                SUM(CASE WHEN po_estado = 'Completada' THEN 1 ELSE 0 END) as completadas,
                SUM(CASE WHEN po_estado = 'Retrasada' THEN 1 ELSE 0 END) as retrasadas,
                SUM(CASE WHEN po_estado = 'Cancelada' THEN 1 ELSE 0 END) as canceladas,
                SUM(CASE WHEN po_estado = 'Pendiente' THEN 1 ELSE 0 END) as pendientes
            FROM po";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $estados_result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $stats['estados_pos'] = [
                ['estado' => 'Pendiente', 'total' => (int)$estados_result['pendientes'], 'color' => '#ffc107'],
                ['estado' => 'En Proceso', 'total' => (int)$estados_result['en_proceso'], 'color' => '#0d6efd'],
                ['estado' => 'Completada', 'total' => (int)$estados_result['completadas'], 'color' => '#198754'],
                ['estado' => 'Retrasada', 'total' => (int)$estados_result['retrasadas'], 'color' => '#dc3545'],
                ['estado' => 'Cancelada', 'total' => (int)$estados_result['canceladas'], 'color' => '#6c757d']
            ];

            return $stats;
        } catch(PDOException $e) {
            error_log("Error en Dashboard::getStats(): " . $e->getMessage());
            throw $e;
        }
    }
}
?>
