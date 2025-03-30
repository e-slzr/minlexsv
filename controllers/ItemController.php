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
        
        // Asegurar que no haya salida previa antes de enviar JSON
        ob_start();
    }

    private function processRequest() {
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
                case 'search':
                    $this->buscarItems();
                    break;
                default:
                    echo json_encode(['success' => false, 'message' => 'Acción GET no válida']);
                    break;
            }
            return;
        }

        // Manejar solicitudes POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
                    echo json_encode(['success' => false, 'message' => 'Acción POST no válida']);
                    break;
            }
            return;
        }

        echo json_encode(['success' => false, 'message' => 'Método de solicitud no soportado']);
    }

    public function handleRequest() {
        try {
            // Limpiar cualquier salida previa
            while (ob_get_level()) ob_end_clean();
            
            // Establecer encabezados para JSON
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-cache, must-revalidate');
            
            // Verificar si es una solicitud de búsqueda
            if (isset($_POST['action']) && $_POST['action'] === 'search') {
                $this->buscarItems();
                return;
            }
            
            // Verificar si es una solicitud GET de búsqueda
            if (isset($_GET['action']) && $_GET['action'] === 'search') {
                $this->buscarItems();
                return;
            }
            
            // Procesar la solicitud
            $this->processRequest();
        } catch (Exception $e) {
            error_log("Error en handleRequest: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
        } finally {
            // Asegurar que solo se envíe la respuesta JSON
            $output = ob_get_clean();
            if (!empty($output)) {
                if (strpos($output, '{') !== 0 || strpos($output, '}') !== strlen($output) - 1) {
                    error_log("Respuesta no válida: " . $output);
                    echo json_encode(['success' => false, 'message' => 'Error en la respuesta del servidor']);
                } else {
                    echo $output;
                }
            }
        }
    }

    private function buscarItems() {
        try {
            // Obtener parámetros de búsqueda (soporta tanto GET como POST)
            $itemNumero = '';
            $itemNombre = '';
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $itemNumero = isset($_POST['item_numero']) ? trim($_POST['item_numero']) : '';
                $itemNombre = isset($_POST['item_nombre']) ? trim($_POST['item_nombre']) : '';
            } else {
                $itemNumero = isset($_GET['item_numero']) ? trim($_GET['item_numero']) : '';
                $itemNombre = isset($_GET['item_nombre']) ? trim($_GET['item_nombre']) : '';
            }

            // Log para depuración
            error_log("Búsqueda de items - Número: '$itemNumero', Nombre: '$itemNombre'");

            if (empty($itemNumero) && empty($itemNombre)) {
                echo json_encode(['success' => false, 'message' => 'Ingrese al menos un criterio de búsqueda']);
                return;
            }

            $items = $this->item->search($itemNumero, $itemNombre);
            error_log("Items encontrados: " . count($items));

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
            
            // Procesar especificaciones si existen
            $itemSpecs = null;
            if (isset($_FILES['item_specs'])) {
                $baseSpecsDir = '../uploads/specs/';
                if (!is_dir($baseSpecsDir)) {
                    mkdir($baseSpecsDir, 0777, true);
                }

                // Crear directorio específico para el item usando timestamp y número de item
                $itemSpecsDir = $baseSpecsDir . time() . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $itemNumero) . '/';
                mkdir($itemSpecsDir, 0777, true);

                // Procesar múltiples archivos
                $uploadedFiles = [];
                foreach ($_FILES['item_specs']['tmp_name'] as $key => $tmpName) {
                    if ($_FILES['item_specs']['error'][$key] === UPLOAD_ERR_OK) {
                        $fileName = basename($_FILES['item_specs']['name'][$key]);
                        $targetFilePath = $itemSpecsDir . $fileName;
                        
                        if (move_uploaded_file($tmpName, $targetFilePath)) {
                            $uploadedFiles[] = $fileName;
                        }
                    }
                }

                if (!empty($uploadedFiles)) {
                    $itemSpecs = str_replace('../', '/', $itemSpecsDir);
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
                echo json_encode(['success' => false, 'message' => 'Datos incompletos o inválidos']);
                return;
            }
            
            // Obtener item actual
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
                
                // Validar tipo de archivo
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                $fileType = $_FILES['item_img']['type'];
                if (!in_array($fileType, $allowedTypes)) {
                    echo json_encode(['success' => false, 'message' => 'Tipo de archivo no permitido. Use JPG, PNG o GIF']);
                    return;
                }
                
                // Validar tamaño (2MB máximo)
                if ($_FILES['item_img']['size'] > 2 * 1024 * 1024) {
                    echo json_encode(['success' => false, 'message' => 'La imagen no debe exceder 2MB']);
                    return;
                }
                
                // Generar nombre único para la imagen
                $extension = pathinfo($_FILES['item_img']['name'], PATHINFO_EXTENSION);
                $fileName = $itemNumero . '_' . date('Ymd_His') . '.' . $extension;
                $targetFilePath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['item_img']['tmp_name'], $targetFilePath)) {
                    // Eliminar imagen anterior si existe
                    if ($currentItem['item_img'] && file_exists('../' . $currentItem['item_img'])) {
                        unlink('../' . $currentItem['item_img']);
                    }
                    $itemImg = '/uploads/imagenes/' . $fileName;
                }
            }
            
            // Procesar especificaciones si existen
            $itemSpecs = $currentItem['item_dir_specs'];
            if (isset($_FILES['item_specs'])) {
                $baseSpecsDir = '../uploads/specs/';
                if (!is_dir($baseSpecsDir)) {
                    mkdir($baseSpecsDir, 0777, true);
                }

                // Crear o usar directorio específico para el item
                $itemSpecsDir = $itemSpecs ? '../' . $itemSpecs : $baseSpecsDir . time() . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $itemNumero) . '/';
                if (!is_dir($itemSpecsDir)) {
                    mkdir($itemSpecsDir, 0777, true);
                }

                // Validar y procesar cada archivo
                $allowedTypes = [
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'image/jpeg',
                    'image/png'
                ];

                foreach ($_FILES['item_specs']['tmp_name'] as $key => $tmpName) {
                    if ($_FILES['item_specs']['error'][$key] === UPLOAD_ERR_OK) {
                        // Validar tipo de archivo
                        if (!in_array($_FILES['item_specs']['type'][$key], $allowedTypes)) {
                            continue; // Saltar archivos no permitidos
                        }

                        // Validar tamaño (5MB máximo)
                        if ($_FILES['item_specs']['size'][$key] > 5 * 1024 * 1024) {
                            continue; // Saltar archivos muy grandes
                        }

                        $fileName = basename($_FILES['item_specs']['name'][$key]);
                        $targetFilePath = $itemSpecsDir . $fileName;
                        move_uploaded_file($tmpName, $targetFilePath);
                    }
                }

                if (!$itemSpecs) {
                    $itemSpecs = str_replace('../', '/', $itemSpecsDir);
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
