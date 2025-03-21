<?php
require_once __DIR__ . '/../models/Item.php';

class ItemController {
    private $item;

    public function __construct() {
        $this->item = new Item();
    }

    public function getItems() {
        try {
            return $this->item->getAll();
        } catch (Exception $e) {
            error_log("Error en ItemController::getItems: " . $e->getMessage());
            return [];
        }
    }

    public function getItemById($id) {
        try {
            return $this->item->getById($id);
        } catch (Exception $e) {
            error_log("Error en ItemController::getItemById: " . $e->getMessage());
            return null;
        }
    }

    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (isset($_GET['action'])) {
                switch ($_GET['action']) {
                    case 'getAll':
                        header('Content-Type: application/json');
                        echo json_encode(['success' => true, 'data' => $this->getItems()]);
                        break;
                    
                    case 'getById':
                        if (!isset($_GET['id'])) {
                            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
                            break;
                        }
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => true, 
                            'data' => $this->getItemById($_GET['id'])
                        ]);
                        break;
                }
                exit;
            }
        }
    }
}

// Si se hace una llamada directa al controlador
if(basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    $controller = new ItemController();
    $controller->handleRequest();
}
