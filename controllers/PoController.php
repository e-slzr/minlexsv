<?php
require_once __DIR__ . '/../models/Po.php';
require_once __DIR__ . '/../models/PoDetalle.php';
require_once __DIR__ . '/../models/Cliente.php';
require_once __DIR__ . '/../config/Database.php';

class PoController {
    private $po;
    private $poDetalle;
    private $cliente;
    private $db;

    public function __construct() {
        $this->db = new Database();
        $conn = $this->db->getConnection();
        $this->po = new Po($conn);
        $this->poDetalle = new PoDetalle($conn);
        $this->cliente = new Cliente($conn);
    }

    public function handleRequest() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user'])) {
            header('Location: ../views/login.php');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            
            switch ($action) {
                case 'create':
                    $this->createPo();
                    break;
                case 'update':
                    $this->updatePo();
                    break;
                case 'delete':
                    $this->deletePo();
                    break;
                case 'validatePassword':
                    $this->validatePassword();
                    break;
                case 'addDetail':
                    $this->addPoDetail();
                    break;
                case 'updateDetail':
                    $this->updatePoDetail();
                    break;
                case 'deleteDetail':
                    $this->deletePoDetail();
                    break;
                case 'getPOs':
                    $pos = $this->getPos();
                    header('Content-Type: application/json');
                    echo json_encode($pos);
                    exit;
                    break;
                default:
                    echo json_encode(['success' => false, 'message' => 'Acción no válida']);
                    break;
            }
        } else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $action = $_GET['action'] ?? '';
            
            switch ($action) {
                case 'getDetails':
                    $this->getPoDetails();
                    break;

                case 'getPoInfo':
                    $this->getPoInfo();
                    break;
                    
                case 'getActivePOs':
                    $activePOs = $this->getActivePOs();
                    echo json_encode($activePOs);
                    break;

                case 'getPOsPorModulo':
                    $moduloId = $_GET['modulo_id'] ?? null;
                    if ($moduloId) {
                        $pos = $this->getPOsPorModulo($moduloId);
                        echo json_encode($pos);
                        return;
                    }
                    echo json_encode(['error' => 'Módulo no especificado']);
                    return;

                default:
                    echo json_encode(['success' => false, 'message' => 'Acción no válida']);
                    break;
            }
        }
    }

    private function createPo() {
        try {
            $conn = $this->db->getConnection();
            $conn->beginTransaction();

            // Validar datos básicos de la PO
            if (!isset($_POST['po_numero']) || empty($_POST['po_numero'])) {
                throw new Exception("El número de PO es requerido");
            }

            if (!isset($_POST['po_id_cliente']) || empty($_POST['po_id_cliente'])) {
                throw new Exception("El cliente es requerido");
            }

            // Validar que existan items
            $items = json_decode($_POST['items'], true);
            if (empty($items)) {
                throw new Exception("Debe agregar al menos un item a la PO");
            }

            // Crear la PO
            $poData = [
                'po_numero' => $_POST['po_numero'],
                'po_id_cliente' => $_POST['po_id_cliente'],
                'po_fecha_inicio_produccion' => $_POST['po_fecha_inicio_produccion'] ?: null,
                'po_fecha_fin_produccion' => $_POST['po_fecha_fin_produccion'] ?: null,
                'po_fecha_envio_programada' => $_POST['po_fecha_envio_programada'],
                'po_tipo_envio' => $_POST['po_tipo_envio'],
                'po_comentario' => $_POST['po_comentario'] ?: '',
                'po_estado' => 'Pendiente',
                'po_id_usuario_creacion' => $_SESSION['user']['id']
            ];

            $poId = $this->po->create($poData);
            if (!$poId) {
                throw new Exception("Error al crear la PO");
            }

            // Crear los detalles
            foreach ($items as $item) {
                $detalleData = [
                    'pd_id_po' => $poId,
                    'pd_item' => $item['id'],
                    'pd_cant_piezas_total' => $item['cant_piezas_total'],
                    'pd_pcs_carton' => $item['pcs_carton'],
                    'pd_pcs_poly' => $item['pcs_poly'],
                    'pd_precio_unitario' => $item['precio_unitario'],
                    'pd_estado' => 'Pendiente'
                ];

                if (!$this->poDetalle->create($detalleData)) {
                    throw new Exception("Error al crear el detalle de la PO");
                }
            }

            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'PO creada exitosamente']);

        } catch (Exception $e) {
            if (isset($conn)) {
                $conn->rollBack();
            }
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function updatePo() {
        try {
            if (empty($_POST['id'])) {
                throw new Exception("ID de PO es requerido");
            }

            $this->po->id = $_POST['id'];
            $this->po->po_fecha_inicio_produccion = !empty($_POST['po_fecha_inicio_produccion']) ? $_POST['po_fecha_inicio_produccion'] : null;
            $this->po->po_fecha_fin_produccion = !empty($_POST['po_fecha_fin_produccion']) ? $_POST['po_fecha_fin_produccion'] : null;
            $this->po->po_fecha_envio_programada = !empty($_POST['po_fecha_envio_programada']) ? $_POST['po_fecha_envio_programada'] : null;
            $this->po->po_estado = $_POST['po_estado'];
            $this->po->po_tipo_envio = $_POST['po_tipo_envio'];
            $this->po->po_comentario = $_POST['po_comentario'] ?? null;
            $this->po->po_notas = $_POST['po_notas'] ?? null;

            if ($this->po->update()) {
                echo json_encode(['success' => true, 'message' => 'PO actualizada exitosamente']);
            } else {
                throw new Exception("Error al actualizar la PO");
            }

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function deletePo() {
        try {
            $poId = $_POST['id'] ?? null;
            if (!$poId) {
                throw new Exception("ID de PO no proporcionado");
            }

            // Validar contraseña
            require_once __DIR__ . '/../models/Usuario.php';
            $usuario = new Usuario($this->db->getConnection());
            $passwordValid = $usuario->validatePassword(
                $_SESSION['user']['id'],
                $_POST['password']
            );

            if (!$passwordValid['success']) {
                throw new Exception("Contraseña incorrecta");
            }

            // Eliminar la PO
            if ($this->po->delete($poId)) {
                echo json_encode(['success' => true, 'message' => 'PO eliminada correctamente']);
            } else {
                throw new Exception("Error al eliminar la PO");
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function validatePassword() {
        try {
            require_once __DIR__ . '/../models/Usuario.php';
            $usuario = new Usuario($this->db->getConnection());
            $result = $usuario->validatePassword(
                $_SESSION['user']['id'],
                $_POST['password']
            );

            echo json_encode([
                'success' => $result['success'],
                'message' => $result['success'] ? 'Contraseña válida' : 'Contraseña incorrecta'
            ]);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function addPoDetail() {
        try {
            if (empty($_POST['pd_id_po']) || empty($_POST['pd_item']) || empty($_POST['pd_cant_piezas_total'])) {
                throw new Exception("Todos los campos son requeridos");
            }

            $this->poDetalle->pd_id_po = $_POST['pd_id_po'];
            $this->poDetalle->pd_item = $_POST['pd_item'];
            $this->poDetalle->pd_cant_piezas_total = $_POST['pd_cant_piezas_total'];
            $this->poDetalle->pd_pcs_carton = $_POST['pd_pcs_carton'];
            $this->poDetalle->pd_pcs_poly = $_POST['pd_pcs_poly'];
            $this->poDetalle->pd_estado = 'Pendiente';
            $this->poDetalle->pd_precio_unitario = $_POST['pd_precio_unitario'];

            $detalleId = $this->poDetalle->create([
                'pd_id_po' => $this->poDetalle->pd_id_po,
                'pd_item' => $this->poDetalle->pd_item,
                'pd_cant_piezas_total' => $this->poDetalle->pd_cant_piezas_total,
                'pd_pcs_carton' => $this->poDetalle->pd_pcs_carton,
                'pd_pcs_poly' => $this->poDetalle->pd_pcs_poly,
                'pd_estado' => $this->poDetalle->pd_estado,
                'pd_precio_unitario' => $this->poDetalle->pd_precio_unitario
            ]);
            if (!$detalleId) {
                throw new Exception("Error al agregar el detalle");
            }

            echo json_encode([
                'success' => true,
                'message' => 'Detalle agregado exitosamente',
                'detalle_id' => $detalleId
            ]);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function updatePoDetail() {
        try {
            if (empty($_POST['id'])) {
                throw new Exception("ID del detalle es requerido");
            }

            $this->poDetalle->id = $_POST['id'];
            $this->poDetalle->pd_cant_piezas_total = $_POST['pd_cant_piezas_total'];
            $this->poDetalle->pd_pcs_carton = $_POST['pd_pcs_carton'];
            $this->poDetalle->pd_pcs_poly = $_POST['pd_pcs_poly'];
            $this->poDetalle->pd_estado = $_POST['pd_estado'];
            $this->poDetalle->pd_precio_unitario = $_POST['pd_precio_unitario'];

            if ($this->poDetalle->update([
                'pd_cant_piezas_total' => $this->poDetalle->pd_cant_piezas_total,
                'pd_pcs_carton' => $this->poDetalle->pd_pcs_carton,
                'pd_pcs_poly' => $this->poDetalle->pd_pcs_poly,
                'pd_estado' => $this->poDetalle->pd_estado,
                'pd_precio_unitario' => $this->poDetalle->pd_precio_unitario
            ])) {
                echo json_encode(['success' => true, 'message' => 'Detalle actualizado exitosamente']);
            } else {
                throw new Exception("Error al actualizar el detalle");
            }

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function deletePoDetail() {
        try {
            if (empty($_POST['id'])) {
                throw new Exception("ID del detalle es requerido");
            }

            $this->poDetalle->id = $_POST['id'];
            if ($this->poDetalle->delete()) {
                echo json_encode(['success' => true, 'message' => 'Detalle eliminado exitosamente']);
            } else {
                throw new Exception("Error al eliminar el detalle");
            }

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function getPoDetails() {
        try {
            if (!isset($_GET['po_id'])) {
                throw new Exception("ID de PO no especificado");
            }
            $id = $_GET['po_id'];
            $detallesCompletos = $this->po->getDetallesCompletos($id);
            if (!$detallesCompletos) {
                throw new Exception("No se encontró la PO especificada");
            }
            
            // Calcular totales y otros datos adicionales
            $totalPiezas = 0;
            $totalValor = 0;
            foreach ($detallesCompletos['detalles'] as $detalle) {
                $totalPiezas += $detalle['pd_cant_piezas_total'];
                $totalValor += $detalle['pd_cant_piezas_total'] * $detalle['pd_precio_unitario'];
            }
            
            $detallesCompletos['totales'] = [
                'piezas' => $totalPiezas,
                'valor' => $totalValor
            ];
            
            echo json_encode(['success' => true, 'data' => $detallesCompletos]);
        } catch (Exception $e) {
            error_log("Error en PoController::getPoDetails: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function getPoInfo() {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $po = $this->po->getById($id);
            echo json_encode($po);
        } else {
            throw new Exception("ID de PO no especificado");
        }
    }

    public function getClientes() {
        try {
            return $this->cliente->getAll([]);
        } catch (Exception $e) {
            error_log("Error al obtener clientes: " . $e->getMessage());
            return [];
        }
    }

    public function getPos($filtros = []) {
        try {
            return $this->po->read($filtros)->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener POs: " . $e->getMessage());
            return [];
        }
    }

    public function getPoById($id) {
        $this->po->id = $id;
        return $this->po->readOne();
    }

    public function getPoDetalles($poId) {
        if (!$poId) {
            throw new Exception("ID de PO no especificado");
        }
        return $this->poDetalle->readByPo($poId)->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene las POs activas (en estado Pendiente o En proceso)
     * @return array Lista de POs activas
     */
    public function getActivePOs() {
        try {
            $filtros = [
                'estados' => ['Pendiente', 'En proceso']
            ];
            $result = $this->po->read($filtros);
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener POs activas: " . $e->getMessage());
            return [];
        }
    }

    public function getPOsPorModulo($moduloId) {
        $query = "SELECT p.*, 
                  i.item_descripcion, i.item_talla, i.item_color, i.item_diseno, i.item_ubicacion
                  FROM po p
                  INNER JOIN items i ON p.po_item_id = i.id
                  WHERE p.po_modulo_id = :modulo_id
                  AND p.po_estado IN ('En Proceso', 'En Espera')
                  ORDER BY p.po_fecha_envio_programada ASC";
        
        $params = [':modulo_id' => $moduloId];
        return $this->po->custom_query($query, $params);
    }
}

if(basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    $controller = new PoController();
    $controller->handleRequest();
}
?>
