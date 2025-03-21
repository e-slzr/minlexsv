<?php
require_once __DIR__ . '/../models/PoDetalle.php';
require_once __DIR__ . '/../config/Database.php';

class PODetalleController {
    private $poDetalle;
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->poDetalle = new PoDetalle($this->db);
    }

    public function handleRequest() {
        $action = $_REQUEST['action'] ?? '';
        $response = ['success' => false, 'message' => 'Acción no válida'];

        try {
            switch ($action) {
                case 'getAll':
                    $response = [
                        'success' => true,
                        'detalles' => $this->poDetalle->getAll()
                    ];
                    break;

                case 'getPODetalleInfo':
                    if (!isset($_GET['id'])) {
                        throw new Exception('ID de detalle no proporcionado');
                    }
                    $detalle = $this->poDetalle->getById($_GET['id']);
                    if ($detalle) {
                        $response = [
                            'success' => true,
                            'detalle' => $detalle
                        ];
                    } else {
                        throw new Exception('Detalle no encontrado');
                    }
                    break;

                case 'getByPoId':
                    if (!isset($_GET['po_id'])) {
                        throw new Exception('ID de PO no proporcionado');
                    }
                    $detalles = $this->poDetalle->getByPoId($_GET['po_id']);
                    $response = [
                        'success' => true,
                        'detalles' => $detalles
                    ];
                    break;

                case 'create':
                case 'update':
                    // Validar datos requeridos
                    $requiredFields = ['pd_id_po', 'pd_item', 'pd_cant_piezas_total', 'pd_pcs_carton', 'pd_pcs_poly'];
                    foreach ($requiredFields as $field) {
                        if (!isset($_POST[$field]) || empty($_POST[$field])) {
                            throw new Exception("El campo {$field} es requerido");
                        }
                    }

                    $data = [
                        'pd_id_po' => $_POST['pd_id_po'],
                        'pd_item' => $_POST['pd_item'],
                        'pd_cant_piezas_total' => $_POST['pd_cant_piezas_total'],
                        'pd_pcs_carton' => $_POST['pd_pcs_carton'],
                        'pd_pcs_poly' => $_POST['pd_pcs_poly'],
                        'pd_estado' => $_POST['pd_estado'] ?? 'Pendiente',
                        'pd_precio_unitario' => $_POST['pd_precio_unitario'] ?? null
                    ];

                    if ($action === 'update') {
                        if (!isset($_POST['id'])) {
                            throw new Exception('ID de detalle no proporcionado');
                        }
                        $data['id'] = $_POST['id'];
                        $result = $this->poDetalle->update($data);
                        $response = [
                            'success' => true,
                            'message' => 'Detalle de PO actualizado correctamente'
                        ];
                    } else {
                        $id = $this->poDetalle->create($data);
                        $response = [
                            'success' => true,
                            'message' => 'Detalle de PO creado correctamente',
                            'id' => $id
                        ];
                    }
                    break;

                case 'delete':
                    if (!isset($_POST['id'])) {
                        throw new Exception('ID de detalle no proporcionado');
                    }
                    
                    // Verificar contraseña antes de eliminar
                    if (!isset($_POST['password']) || empty($_POST['password'])) {
                        throw new Exception('Se requiere contraseña para eliminar');
                    }
                    
                    // Aquí deberías verificar la contraseña contra la almacenada del usuario
                    // Por ahora solo verificamos que se haya proporcionado
                    
                    $this->poDetalle->delete($_POST['id']);
                    $response = [
                        'success' => true,
                        'message' => 'Detalle de PO eliminado correctamente'
                    ];
                    break;

                default:
                    throw new Exception('Acción no válida');
            }
        } catch (Exception $e) {
            error_log("Error en PODetalleController: " . $e->getMessage());
            $response = [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    // Métodos auxiliares para uso desde PHP
    public function getAllPODetalles() {
        try {
            return $this->poDetalle->getAll();
        } catch (Exception $e) {
            error_log("Error al obtener detalles de PO: " . $e->getMessage());
            return null;
        }
    }

    public function getPODetallesByPoId($poId) {
        try {
            return $this->poDetalle->getByPoId($poId);
        } catch (Exception $e) {
            error_log("Error al obtener detalles de PO: " . $e->getMessage());
            return null;
        }
    }
}

// Manejar la solicitud si se accede directamente al controlador
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    $controller = new PODetalleController();
    $controller->handleRequest();
}
