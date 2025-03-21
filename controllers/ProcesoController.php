<?php
require_once __DIR__ . '/../models/Proceso.php';
require_once __DIR__ . '/../config/Database.php';

class ProcesoController {
    private $proceso;
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->proceso = new Proceso($this->db);
    }

    public function handleRequest() {
        $action = $_REQUEST['action'] ?? '';
        $response = ['success' => false, 'message' => 'Acción no válida'];

        try {
            switch ($action) {
                case 'getAll':
                    $response = [
                        'success' => true,
                        'procesos' => $this->proceso->getAll()
                    ];
                    break;

                case 'search':
                    $filters = [
                        'nombre' => $_GET['nombre'] ?? '',
                        'costo_min' => $_GET['costo_min'] ?? '',
                        'costo_max' => $_GET['costo_max'] ?? '',
                        'order_column' => $_GET['order_column'] ?? 'pp_nombre',
                        'order_dir' => $_GET['order_dir'] ?? 'ASC'
                    ];
                    $procesos = $this->proceso->search($filters);
                    $response = [
                        'success' => true,
                        'procesos' => $procesos,
                        'total' => count($procesos)
                    ];
                    break;

                case 'getProcesoInfo':
                    if (!isset($_GET['id'])) {
                        throw new Exception('ID de proceso no proporcionado');
                    }
                    $proceso = $this->proceso->getById($_GET['id']);
                    if ($proceso) {
                        $response = [
                            'success' => true,
                            'proceso' => $proceso
                        ];
                    } else {
                        throw new Exception('Proceso no encontrado');
                    }
                    break;

                case 'create':
                case 'update':
                    // Validar datos requeridos
                    if (!isset($_POST['pp_nombre']) || empty($_POST['pp_nombre'])) {
                        throw new Exception("El nombre del proceso es requerido");
                    }

                    $data = [
                        'pp_nombre' => $_POST['pp_nombre'],
                        'pp_descripcion' => $_POST['pp_descripcion'] ?? null,
                        'pp_costo' => $_POST['pp_costo'] ?? null
                    ];

                    if ($action === 'update') {
                        if (!isset($_POST['id'])) {
                            throw new Exception('ID de proceso no proporcionado');
                        }
                        $data['id'] = $_POST['id'];
                        $result = $this->proceso->update($data);
                        $response = [
                            'success' => true,
                            'message' => 'Proceso actualizado correctamente'
                        ];
                    } else {
                        $id = $this->proceso->create($data);
                        $response = [
                            'success' => true,
                            'message' => 'Proceso creado correctamente',
                            'id' => $id
                        ];
                    }
                    break;

                case 'delete':
                    if (!isset($_POST['id'])) {
                        throw new Exception('ID de proceso no proporcionado');
                    }
                    
                    // Verificar contraseña antes de eliminar
                    if (!isset($_POST['password']) || empty($_POST['password'])) {
                        throw new Exception('Se requiere contraseña para eliminar');
                    }
                    
                    // Verificar si el proceso está siendo utilizado
                    if ($this->proceso->isUsedInProduction($_POST['id'])) {
                        throw new Exception('No se puede eliminar el proceso porque está asociado a órdenes de producción');
                    }
                    
                    $this->proceso->delete($_POST['id']);
                    $response = [
                        'success' => true,
                        'message' => 'Proceso eliminado correctamente'
                    ];
                    break;

                default:
                    throw new Exception('Acción no válida');
            }
        } catch (Exception $e) {
            error_log("Error en ProcesoController: " . $e->getMessage());
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
    public function getProcesos() {
        try {
            return $this->proceso->getAll();
        } catch (Exception $e) {
            error_log("Error al obtener procesos: " . $e->getMessage());
            return null;
        }
    }
    
    public function searchProcesos($filters = []) {
        try {
            return $this->proceso->search($filters);
        } catch (Exception $e) {
            error_log("Error al buscar procesos: " . $e->getMessage());
            return null;
        }
    }
    
    public function getProcesoById($id) {
        try {
            return $this->proceso->getById($id);
        } catch (Exception $e) {
            error_log("Error al obtener proceso: " . $e->getMessage());
            return null;
        }
    }
}

// Manejar la solicitud si se accede directamente al controlador
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    $controller = new ProcesoController();
    $controller->handleRequest();
}
