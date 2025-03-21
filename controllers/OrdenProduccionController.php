<?php
require_once __DIR__ . '/../models/OrdenProduccion.php';
require_once __DIR__ . '/../config/Database.php';

class OrdenProduccionController {
    private $ordenProduccion;
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->ordenProduccion = new OrdenProduccion($this->db);
    }

    public function handleRequest() {
        $action = $_REQUEST['action'] ?? '';
        $response = ['success' => false, 'message' => 'Acción no válida'];

        try {
            switch ($action) {
                case 'getAll':
                    $response = [
                        'success' => true,
                        'ordenes' => $this->ordenProduccion->getAll()
                    ];
                    break;

                case 'getOrdenInfo':
                    if (!isset($_GET['id'])) {
                        throw new Exception('ID de orden no proporcionado');
                    }
                    $orden = $this->ordenProduccion->getById($_GET['id']);
                    if ($orden) {
                        $response = [
                            'success' => true,
                            'orden' => $orden
                        ];
                    } else {
                        throw new Exception('Orden no encontrada');
                    }
                    break;

                case 'create':
                case 'update':
                    // Validar datos requeridos
                    $requiredFields = ['op_id_pd', 'op_operador_asignado', 'op_id_proceso', 
                                     'op_fecha_inicio', 'op_cantidad_asignada'];
                    foreach ($requiredFields as $field) {
                        if (!isset($_POST[$field]) || empty($_POST[$field])) {
                            throw new Exception("El campo {$field} es requerido");
                        }
                    }

                    $data = [
                        'op_id_pd' => $_POST['op_id_pd'],
                        'op_operador_asignado' => $_POST['op_operador_asignado'],
                        'op_id_proceso' => $_POST['op_id_proceso'],
                        'op_fecha_inicio' => $_POST['op_fecha_inicio'],
                        'op_fecha_fin' => $_POST['op_fecha_fin'] ?? null,
                        'op_estado' => $_POST['op_estado'] ?? 'Pendiente',
                        'op_cantidad_asignada' => $_POST['op_cantidad_asignada'],
                        'op_cantidad_completada' => $_POST['op_cantidad_completada'] ?? 0,
                        'op_comentario' => $_POST['op_comentario'] ?? null
                    ];

                    if ($action === 'update') {
                        if (!isset($_POST['id'])) {
                            throw new Exception('ID de orden no proporcionado');
                        }
                        $data['id'] = $_POST['id'];
                        $result = $this->ordenProduccion->update($data);
                        $response = [
                            'success' => true,
                            'message' => 'Orden de producción actualizada correctamente'
                        ];
                    } else {
                        $id = $this->ordenProduccion->create($data);
                        $response = [
                            'success' => true,
                            'message' => 'Orden de producción creada correctamente',
                            'id' => $id
                        ];
                    }
                    break;

                case 'delete':
                    if (!isset($_POST['id'])) {
                        throw new Exception('ID de orden no proporcionado');
                    }
                    
                    // Verificar contraseña antes de eliminar
                    if (!isset($_POST['password']) || empty($_POST['password'])) {
                        throw new Exception('Se requiere contraseña para eliminar');
                    }
                    
                    // Aquí deberías verificar la contraseña contra la almacenada del usuario
                    // Por ahora solo verificamos que se haya proporcionado
                    
                    $this->ordenProduccion->delete($_POST['id']);
                    $response = [
                        'success' => true,
                        'message' => 'Orden de producción eliminada correctamente'
                    ];
                    break;

                case 'search':
                    $filters = [
                        'item_numero' => $_GET['item_numero'] ?? '',
                        'operador' => $_GET['operador'] ?? '',
                        'estado' => $_GET['estado'] ?? '',
                        'fecha_inicio' => $_GET['fecha_inicio'] ?? '',
                        'fecha_fin' => $_GET['fecha_fin'] ?? ''
                    ];
                    
                    $ordenes = $this->ordenProduccion->searchOrdenes($filters);
                    $response = [
                        'success' => true,
                        'ordenes' => $ordenes
                    ];
                    break;

                default:
                    throw new Exception('Acción no válida');
            }
        } catch (Exception $e) {
            error_log("Error en OrdenProduccionController: " . $e->getMessage());
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
    public function getAllOrdenes() {
        try {
            return $this->ordenProduccion->getAll();
        } catch (Exception $e) {
            error_log("Error al obtener órdenes: " . $e->getMessage());
            return null;
        }
    }

    public function searchOrdenes($filters) {
        try {
            return $this->ordenProduccion->searchOrdenes($filters);
        } catch (Exception $e) {
            error_log("Error al buscar órdenes: " . $e->getMessage());
            return null;
        }
    }
}

// Manejar la solicitud si se accede directamente al controlador
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    $controller = new OrdenProduccionController();
    $controller->handleRequest();
}
