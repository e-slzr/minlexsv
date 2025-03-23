<?php
require_once '../config/database.php';

class DashboardController {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function handleRequest() {
        header('Content-Type: application/json');

        if (!isset($_POST['action'])) {
            echo json_encode(['success' => false, 'message' => 'Acción no especificada']);
            return;
        }

        $action = $_POST['action'];
        switch ($action) {
            case 'getChartData':
                $this->getChartData();
                break;
            default:
                echo json_encode(['success' => false, 'message' => 'Acción no válida']);
                break;
        }
    }

    private function getChartData() {
        try {
            // Obtener POs por mes
            $posPorMes = $this->getPosPorMes();
            
            // Obtener estados de POs
            $estadosPo = $this->getEstadosPo();

            echo json_encode([
                'success' => true,
                'posPorMes' => $posPorMes,
                'estadosPo' => $estadosPo
            ]);
        } catch (Exception $e) {
            error_log("Error al obtener datos para gráficos: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener datos para gráficos'
            ]);
        }
    }

    private function getPosPorMes() {
        $query = "SELECT 
                    DATE_FORMAT(po_fecha_creacion, '%Y-%m') as mes,
                    COUNT(*) as total
                 FROM po 
                 WHERE po_fecha_creacion >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                 GROUP BY mes 
                 ORDER BY mes";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $labels = [];
        $data = [];
        foreach ($results as $row) {
            $labels[] = date('M Y', strtotime($row['mes'] . '-01'));
            $data[] = (int)$row['total'];
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    private function getEstadosPo() {
        $query = "SELECT 
                    po_estado,
                    COUNT(*) as total
                 FROM po 
                 GROUP BY po_estado";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $labels = [];
        $data = [];
        foreach ($results as $row) {
            $labels[] = $row['po_estado'];
            $data[] = (int)$row['total'];
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    public function getCounts() {
        try {
            $counts = [
                'usuarios' => 0,
                'clientes' => 0,
                'pos' => 0
            ];

            // Contar usuarios
            $query = "SELECT COUNT(*) as total FROM usuarios";
            $stmt = $this->conn->query($query);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $counts['usuarios'] = $row['total'];

            // Contar clientes
            $query = "SELECT COUNT(*) as total FROM clientes";
            $stmt = $this->conn->query($query);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $counts['clientes'] = $row['total'];

            // Contar POs
            $query = "SELECT COUNT(*) as total FROM po";
            $stmt = $this->conn->query($query);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $counts['pos'] = $row['total'];

            return $counts;
        } catch (PDOException $e) {
            error_log("Error al obtener conteos: " . $e->getMessage());
            return false;
        }
    }
}

// Iniciar el controlador si se accede directamente
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    $controller = new DashboardController();
    $controller->handleRequest();
}
