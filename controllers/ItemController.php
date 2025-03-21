<?php
require_once __DIR__ . '/../models/Item.php';
require_once __DIR__ . '/../config/Database.php';

class ItemController {
    private $conn;
    private $item;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
        $this->item = new Item($this->conn);
    }

    public function handleRequest() {
        header('Content-Type: application/json');

        if (!isset($_POST['action'])) {
            echo json_encode(['success' => false, 'message' => 'Acción no especificada']);
            return;
        }

        $action = $_POST['action'];

        switch ($action) {
            case 'search':
                $this->buscarItems();
                break;
            default:
                echo json_encode(['success' => false, 'message' => 'Acción no válida']);
                break;
        }
    }

    private function buscarItems() {
        try {
            $itemNumero = isset($_POST['item_numero']) ? trim($_POST['item_numero']) : '';
            $itemNombre = isset($_POST['item_nombre']) ? trim($_POST['item_nombre']) : '';

            if (empty($itemNumero) && empty($itemNombre)) {
                echo json_encode(['success' => false, 'message' => 'Ingrese al menos un criterio de búsqueda']);
                return;
            }

            $query = "SELECT * FROM items WHERE 1=1";
            $params = [];

            if (!empty($itemNumero)) {
                $query .= " AND item_numero LIKE :numero";
                $params[':numero'] = "%$itemNumero%";
            }

            if (!empty($itemNombre)) {
                $query .= " AND item_nombre LIKE :nombre";
                $params[':nombre'] = "%$itemNombre%";
            }

            $query .= " ORDER BY item_numero LIMIT 50";

            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();

            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'items' => $items
            ]);
        } catch (Exception $e) {
            error_log("Error en buscarItems: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error al buscar items',
                'debug' => $e->getMessage()
            ]);
        }
    }
}

// Iniciar el controlador si se accede directamente
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    $controller = new ItemController();
    $controller->handleRequest();
}
