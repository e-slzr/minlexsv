<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/ProduccionAvance.php';

class ProduccionAvanceController {
    private $produccionAvance;

    public function __construct() {
        $database = new Database();
        $db = $database->getConnection();
        $this->produccionAvance = new ProduccionAvance($db);
    }

    public function registrarAvance() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'orden_produccion_id' => $_POST['orden_produccion_id'],
                'proceso_id' => $_POST['proceso_id'],
                'cantidad_completada' => $_POST['cantidad_completada'],
                'fecha_registro' => date('Y-m-d H:i:s'),
                'operario_id' => $_SESSION['user']['id'],
                'observaciones' => $_POST['observaciones'] ?? '',
                'estado' => $_POST['estado']
            ];

            if ($this->produccionAvance->registrarAvance($data)) {
                echo json_encode(['status' => 'success', 'message' => 'Avance registrado correctamente']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error al registrar el avance']);
            }
        }
    }

    public function getAvances() {
        if (isset($_GET['orden_produccion_id'])) {
            $avances = $this->produccionAvance->getAvancesByOrdenProduccion($_GET['orden_produccion_id']);
            echo json_encode($avances);
        }
    }

    public function getTotalCompletado() {
        if (isset($_GET['orden_produccion_id']) && isset($_GET['proceso_id'])) {
            $total = $this->produccionAvance->getTotalCompletadoByOrden(
                $_GET['orden_produccion_id'],
                $_GET['proceso_id']
            );
            echo json_encode(['total' => $total]);
        }
    }
}
