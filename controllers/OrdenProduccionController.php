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
        header('Content-Type: application/json');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                if (isset($_GET['action']) && $_GET['action'] === 'getOrdenesPorModulo') {
                    $moduloId = $_GET['modulo_id'] ?? null;
                    if (!$moduloId) {
                        throw new Exception('ID de módulo no proporcionado');
                    }
                    $ordenes = $this->getOrdenesPorModulo($moduloId);
                    echo json_encode(['success' => true, 'data' => $ordenes]);
                    exit;
                }
            }
            $action = $_REQUEST['action'] ?? '';
            $response = ['success' => false, 'message' => 'Acción no válida'];

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

                case 'getOrdenesFiltered':
                    $filtros = [
                        'modulo_id' => $_GET['modulo_id'] ?? null,
                        'proceso_id' => $_GET['proceso_id'] ?? null,
                        'estado' => $_GET['estado'] ?? null
                    ];
                    try {
                        $ordenes = $this->getOrdenesFiltered($filtros);
                        $response = ['success' => true, 'data' => $ordenes];
                    } catch (Exception $e) {
                        $response = ['success' => false, 'message' => $e->getMessage()];
                    }
                    echo json_encode($response);
                    exit;
                    break;

                case 'updateProgress':
                    $this->updateProgress();
                    return;
                    
                case 'getOrdenesPendientes':
                    $this->getOrdenesPendientes();
                    return;

                case 'getOrdenesPorModulo':
                    if (!isset($_GET['modulo_id'])) {
                        throw new Exception('ID de módulo no proporcionado');
                    }
                    $response = [
                        'success' => true,
                        'ordenes' => $this->getOrdenesPorModulo($_GET['modulo_id'])
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

    public function getOrdenesFiltered($filters = []) {
        try {
            $query = "SELECT 
                        op.*,
                        pd.pd_item,
                        i.item_numero,
                        i.item_nombre,
                        po.po_numero,
                        pp.pp_nombre as proceso_nombre,
                        m.modulo_codigo
                    FROM ordenes_produccion op
                    LEFT JOIN po_detalle pd ON op.op_id_pd = pd.id
                    LEFT JOIN items i ON pd.pd_item = i.id
                    LEFT JOIN po po ON pd.pd_id_po = po.id
                    LEFT JOIN procesos_produccion pp ON op.op_id_proceso = pp.id
                    LEFT JOIN modulos m ON op.op_modulo_id = m.id
                    WHERE 1=1";

            $params = [];
            
            if (!empty($filters['modulo_id'])) {
                $query .= " AND op.op_modulo_id = :modulo_id";
                $params[':modulo_id'] = $filters['modulo_id'];
            }
            
            if (!empty($filters['proceso_id'])) {
                $query .= " AND op.op_id_proceso = :proceso_id";
                $params[':proceso_id'] = $filters['proceso_id'];
            }
            
            if (!empty($filters['estado'])) {
                $query .= " AND op.op_estado = :estado";
                $params[':estado'] = $filters['estado'];
            }
            
            $query .= " ORDER BY op.op_fecha_inicio DESC";
            
            $stmt = $this->db->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en getOrdenesFiltered: " . $e->getMessage());
            throw new Exception("Error al obtener órdenes de producción");
        }
    }

    public function getOrdenesPendientes() {
        try {
            // Obtener parámetros de filtro
            $po = isset($_GET['po']) ? (int)$_GET['po'] : 0;
            $proceso = isset($_GET['proceso']) ? (int)$_GET['proceso'] : 0;
            
            // Construir array de filtros
            $filters = [];
            if ($po > 0) $filters['po'] = $po;
            if ($proceso > 0) $filters['proceso'] = $proceso;
            
            // Obtener órdenes pendientes
            $ordenes = $this->ordenProduccion->getOrdenesPendientes($filters);
            
            header('Content-Type: application/json');
            echo json_encode($ordenes);
        } catch (Exception $e) {
            error_log("Error en getOrdenesPendientes: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function updateProgress() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
            try {
                $id = (int)$_POST['id'];
                $cantidadCompletada = (int)$_POST['op_cantidad_completada'];
                $estado = $_POST['op_estado'];
                $comentario = $_POST['op_comentario'] ?? '';
                
                // Obtener la orden actual para validaciones
                $orden = $this->ordenProduccion->getById($id);
                
                if (!$orden) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Orden de producción no encontrada']);
                    return;
                }
                
                // Validar que la cantidad completada no exceda la asignada
                if ($cantidadCompletada > $orden['op_cantidad_asignada']) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false, 
                        'message' => 'La cantidad completada no puede ser mayor que la asignada'
                    ]);
                    return;
                }
                
                // Preparar datos para la actualización
                $data = [
                    'id' => $id,
                    'op_cantidad_completada' => $cantidadCompletada,
                    'op_estado' => $estado,
                    'op_comentario' => $comentario
                ];
                
                // Si se marca como completado, pero no coincide la cantidad, ajustar el estado
                if ($estado === 'Completado' && $cantidadCompletada < $orden['op_cantidad_asignada']) {
                    $data['op_estado'] = 'En proceso';
                }
                
                // Si ya se completó toda la cantidad, marcar como completado automáticamente
                if ($cantidadCompletada >= $orden['op_cantidad_asignada']) {
                    $data['op_estado'] = 'Completado';
                }
                
                // Actualizar la fecha de fin si se marca como completado
                if ($data['op_estado'] === 'Completado' && $orden['op_estado'] !== 'Completado') {
                    $data['op_fecha_fin'] = date('Y-m-d');
                }
                
                // Actualizar el registro
                $result = $this->ordenProduccion->updateProgress($data);
                
                header('Content-Type: application/json');
                echo json_encode(['success' => $result]);
            } catch (Exception $e) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
        }
    }

    public function getOrdenesPorModulo($moduloId) {
        try {
            $query = "SELECT 
                        op.*,
                        pd.pd_item,
                        i.item_numero,
                        i.item_nombre,
                        i.item_talla,
                        po.po_numero,
                        pp.pp_nombre as proceso_nombre,
                        m.modulo_codigo
                    FROM ordenes_produccion op
                    LEFT JOIN po_detalle pd ON op.op_id_pd = pd.id
                    LEFT JOIN items i ON pd.pd_item = i.id
                    LEFT JOIN po po ON pd.pd_id_po = po.id
                    LEFT JOIN procesos_produccion pp ON op.op_id_proceso = pp.id
                    LEFT JOIN modulos m ON op.op_modulo_id = m.id
                    WHERE op.op_modulo_id = :modulo_id
                    AND op.op_estado != 'Completado'
                    ORDER BY op.op_fecha_inicio ASC";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':modulo_id', $moduloId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en getOrdenesPorModulo: " . $e->getMessage());
            throw new Exception("Error al obtener órdenes de producción");
        }
    }
}

// Manejar la solicitud si se accede directamente al controlador
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    $controller = new OrdenProduccionController();
    $controller->handleRequest();
}
