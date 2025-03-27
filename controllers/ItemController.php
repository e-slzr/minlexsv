<?php
require_once __DIR__ . '/../models/Item.php';
require_once __DIR__ . '/../config/Database.php';

class ItemController {
    private $conn;
    private $item;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->item = new Item($this->conn);
    }

    public function handleRequest() {
        header('Content-Type: application/json');

        // Manejar solicitudes GET
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $action = isset($_GET['action']) ? $_GET['action'] : '';
            
            switch ($action) {
                case 'getItems':
                    $this->getItems();
                    break;
                case 'getItemInfo':
                    $this->getItemInfo();
                    break;
                default:
                    echo json_encode(['success' => false, 'message' => 'Acción GET no válida']);
                    break;
            }
            return;
        }

        // Manejar solicitudes POST
        if (!isset($_POST['action'])) {
            echo json_encode(['success' => false, 'message' => 'Acción no especificada']);
            return;
        }

        $action = $_POST['action'];

        switch ($action) {
            case 'search':
                $this->buscarItems();
                break;
            case 'create':
                $this->createItem();
                break;
            case 'update':
                $this->updateItem();
                break;
            case 'delete':
                $this->deleteItem();
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

            $items = $this->item->search($itemNumero, $itemNombre);

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

    public function getItems() {
        try {
            $items = $this->item->getAll();
            echo json_encode(['success' => true, 'data' => $items]);
        } catch (Exception $e) {
            error_log("Error en getItems: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error al obtener items', 'error' => $e->getMessage()]);
        }
    }

    public function getItemInfo() {
        try {
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID de item no válido']);
                return;
            }
            
            $item = $this->item->getById($id);
            
            if (!$item) {
                echo json_encode(['success' => false, 'message' => 'Item no encontrado']);
                return;
            }
            
            echo json_encode(['success' => true, 'item' => $item]);
        } catch (Exception $e) {
            error_log("Error en getItemInfo: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error al obtener información del item', 'error' => $e->getMessage()]);
        }
    }

    private function createItem() {
        try {
            $itemNumero = isset($_POST['item_numero']) ? trim($_POST['item_numero']) : '';
            $itemNombre = isset($_POST['item_nombre']) ? trim($_POST['item_nombre']) : '';
            $itemDescripcion = isset($_POST['item_descripcion']) ? trim($_POST['item_descripcion']) : null;
            $itemTalla = isset($_POST['item_talla']) ? trim($_POST['item_talla']) : null;
            
            // Validar datos obligatorios
            if (empty($itemNumero) || empty($itemNombre)) {
                echo json_encode(['success' => false, 'message' => 'El número y nombre del item son obligatorios']);
                return;
            }
            
            // Procesar imagen si existe
            $itemImg = null;
            if (isset($_FILES['item_img']) && $_FILES['item_img']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../uploads/imagenes/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $fileName = basename($_FILES['item_img']['name']);
                $targetFilePath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['item_img']['tmp_name'], $targetFilePath)) {
                    $itemImg = '/uploads/imagenes/' . $fileName;
                }
            }
            
            // Procesar especificaciones si existe
            $itemSpecs = null;
            if (isset($_FILES['item_specs']) && $_FILES['item_specs']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../uploads/specs/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $fileName = basename($_FILES['item_specs']['name']);
                $targetFilePath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['item_specs']['tmp_name'], $targetFilePath)) {
                    $itemSpecs = '/uploads/specs/' . $fileName;
                }
            }
            
            $result = $this->item->create([
                'item_numero' => $itemNumero,
                'item_nombre' => $itemNombre,
                'item_descripcion' => $itemDescripcion,
                'item_talla' => $itemTalla,
                'item_img' => $itemImg,
                'item_dir_specs' => $itemSpecs
            ]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Item creado exitosamente', 'id' => $result]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al crear el item']);
            }
        } catch (Exception $e) {
            error_log("Error en createItem: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error al crear el item', 'error' => $e->getMessage()]);
        }
    }

    private function updateItem() {
        try {
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            $itemNumero = isset($_POST['item_numero']) ? trim($_POST['item_numero']) : '';
            $itemNombre = isset($_POST['item_nombre']) ? trim($_POST['item_nombre']) : '';
            $itemDescripcion = isset($_POST['item_descripcion']) ? trim($_POST['item_descripcion']) : null;
            $itemTalla = isset($_POST['item_talla']) ? trim($_POST['item_talla']) : null;
            
            // Validar datos obligatorios
            if ($id <= 0 || empty($itemNumero) || empty($itemNombre)) {
                echo json_encode(['success' => false, 'message' => 'ID, número y nombre del item son obligatorios']);
                return;
            }
            
            // Obtener item actual para verificar existencia y mantener datos que no se actualizan
            $currentItem = $this->item->getById($id);
            if (!$currentItem) {
                echo json_encode(['success' => false, 'message' => 'Item no encontrado']);
                return;
            }
            
            // Procesar imagen si existe
            $itemImg = $currentItem['item_img'];
            if (isset($_FILES['item_img']) && $_FILES['item_img']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../uploads/imagenes/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $fileName = basename($_FILES['item_img']['name']);
                $targetFilePath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['item_img']['tmp_name'], $targetFilePath)) {
                    $itemImg = '/uploads/imagenes/' . $fileName;
                }
            }
            
            // Procesar especificaciones si existe
            $itemSpecs = $currentItem['item_dir_specs'];
            if (isset($_FILES['item_specs']) && $_FILES['item_specs']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../uploads/specs/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $fileName = basename($_FILES['item_specs']['name']);
                $targetFilePath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['item_specs']['tmp_name'], $targetFilePath)) {
                    $itemSpecs = '/uploads/specs/' . $fileName;
                }
            }
            
            $result = $this->item->update([
                'id' => $id,
                'item_numero' => $itemNumero,
                'item_nombre' => $itemNombre,
                'item_descripcion' => $itemDescripcion,
                'item_talla' => $itemTalla,
                'item_img' => $itemImg,
                'item_dir_specs' => $itemSpecs
            ]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Item actualizado exitosamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar el item']);
            }
        } catch (Exception $e) {
            error_log("Error en updateItem: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el item', 'error' => $e->getMessage()]);
        }
    }

    private function deleteItem() {
        try {
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            $password = isset($_POST['password']) ? trim($_POST['password']) : '';
            
            // Validar datos obligatorios
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID de item no válido']);
                return;
            }
            
            if (empty($password)) {
                echo json_encode(['success' => false, 'message' => 'Contraseña requerida para confirmar la eliminación']);
                return;
            }
            
            // Verificar la contraseña del usuario (esto debería implementarse según la lógica de autenticación del sistema)
            // Por ahora, asumimos que la verificación de contraseña se realiza correctamente
            
            $result = $this->item->delete($id);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Item eliminado exitosamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar el item']);
            }
        } catch (Exception $e) {
            error_log("Error en deleteItem: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error al eliminar el item', 'error' => $e->getMessage()]);
        }
    }

    // Métodos para uso desde la vista
    public function searchItems($itemNumero, $itemNombre) {
        try {
            return $this->item->search($itemNumero, $itemNombre);
        } catch (Exception $e) {
            error_log("Error en searchItems: " . $e->getMessage());
            return [];
        }
    }

    public function getAllItems() {
        try {
            return $this->item->getAll();
        } catch (Exception $e) {
            error_log("Error en getAllItems: " . $e->getMessage());
            return [];
        }
    }
}

// Iniciar el controlador si se accede directamente
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    $controller = new ItemController();
    $controller->handleRequest();
}
