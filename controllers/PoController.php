<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header('Location: ../views/login.php');
    exit();
}

require_once __DIR__ . '/../models/Po.php';
require_once __DIR__ . '/../models/PoDetalle.php';
require_once __DIR__ . '/../models/Usuario.php';

class PoController {
    private $po;
    private $poDetalle;
    private $usuario;

    public function __construct() {
        $this->po = new Po();
        $this->poDetalle = new PoDetalle();
        $this->usuario = new Usuario();
    }

    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            $response = ['success' => false, 'message' => ''];

            try {
                switch ($action) {
                    case 'create':
                        $response = $this->createPo();
                        break;

                    case 'update':
                        $response = $this->updatePo();
                        break;

                    case 'delete':
                        $response = $this->deletePo();
                        break;

                    case 'validatePassword':
                        $response = $this->validatePassword();
                        break;

                    case 'addDetail':
                        $response = $this->addPoDetail();
                        break;

                    case 'updateDetail':
                        $response = $this->updatePoDetail();
                        break;

                    case 'deleteDetail':
                        $response = $this->deletePoDetail();
                        break;

                    default:
                        $response = ['success' => false, 'message' => 'Acción no válida'];
                }
            } catch (Exception $e) {
                error_log("Error en PoController: " . $e->getMessage());
                $response = ['success' => false, 'message' => $e->getMessage()];
            }

            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        } else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (isset($_GET['action'])) {
                switch ($_GET['action']) {
                    case 'getDetails':
                        $this->getPoDetails();
                        break;

                    case 'getPoInfo':
                        $this->getPoInfo();
                        break;
                }
            }
        }
    }

    private function createPo() {
        try {
            // Validar campos requeridos
            if (empty($_POST['po_numero']) || empty($_POST['po_id_cliente'])) {
                throw new Exception("Número de PO y Cliente son requeridos");
            }

            // Validar que haya al menos un detalle
            if (empty($_POST['items']) || !is_array($_POST['items'])) {
                throw new Exception("Debe agregar al menos un detalle a la PO");
            }

            // Verificar si ya existe una PO con el mismo número
            if ($this->po->existsPoNumero($_POST['po_numero'])) {
                throw new Exception("Ya existe una PO con este número");
            }

            // Obtener la conexión y comenzar transacción
            $conn = $this->po->getConnection();
            $conn->beginTransaction();

            try {
                // Asignar valores al objeto PO
                $this->po->po_numero = $_POST['po_numero'];
                $this->po->po_fecha_creacion = date('Y-m-d');
                $this->po->po_fecha_inicio_produccion = !empty($_POST['po_fecha_inicio_produccion']) ? $_POST['po_fecha_inicio_produccion'] : null;
                $this->po->po_fecha_fin_produccion = !empty($_POST['po_fecha_fin_produccion']) ? $_POST['po_fecha_fin_produccion'] : null;
                $this->po->po_fecha_envio_programada = $_POST['po_fecha_envio_programada'];
                $this->po->po_estado = 'Pendiente';
                $this->po->po_id_cliente = $_POST['po_id_cliente'];
                $this->po->po_id_usuario_creacion = $_SESSION['user']['id'];
                $this->po->po_tipo_envio = $_POST['po_tipo_envio'] ?? 'Tipo 1';
                $this->po->po_comentario = $_POST['po_comentario'] ?? null;

                // Crear la PO
                $poId = $this->po->create();

                if (!$poId) {
                    throw new Exception("Error al crear la PO");
                }

                // Procesar los detalles
                $items = $_POST['items'];
                $cantidades = $_POST['cant_piezas_total'];
                $pcsCarton = $_POST['pcs_carton'];
                $pcsPoly = $_POST['pcs_poly'];
                $precios = $_POST['precio_unitario'];

                foreach ($items as $index => $itemId) {
                    $detalle = [
                        'pd_id_po' => $poId,
                        'pd_item' => $itemId,
                        'pd_cant_piezas_total' => $cantidades[$index],
                        'pd_pcs_carton' => $pcsCarton[$index],
                        'pd_pcs_poly' => $pcsPoly[$index],
                        'pd_estado' => 'Pendiente',
                        'pd_precio_unitario' => $precios[$index]
                    ];

                    if (!$this->poDetalle->create($detalle)) {
                        throw new Exception("Error al crear el detalle de la PO");
                    }
                }

                // Confirmar transacción
                $conn->commit();

                return [
                    'success' => true,
                    'message' => 'PO creada exitosamente',
                    'id' => $poId
                ];

            } catch (Exception $e) {
                $conn->rollBack();
                throw $e;
            }

        } catch (Exception $e) {
            error_log("Error en PoController::createPo: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
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
                return ['success' => true, 'message' => 'PO actualizada exitosamente'];
            }
            throw new Exception("Error al actualizar la PO");

        } catch (Exception $e) {
            error_log("Error al actualizar PO: " . $e->getMessage());
            throw $e;
        }
    }

    private function deletePo() {
        try {
            if (!isset($_POST['id']) || !isset($_POST['password'])) {
                throw new Exception("Datos incompletos");
            }

            $poId = $_POST['id'];
            $password = $_POST['password'];

            // Verificar la contraseña del usuario
            if (!isset($_SESSION['user'])) {
                throw new Exception("Usuario no autenticado");
            }

            if (!$this->usuario->verifyPassword($_SESSION['user']['id'], $password)) {
                throw new Exception("Contraseña incorrecta");
            }

            // Verificar si la PO tiene detalles
            if ($this->poDetalle->hasDetails($poId)) {
                throw new Exception("No se puede eliminar la PO porque tiene detalles asociados");
            }

            // Eliminar la PO
            $po = new Po();
            if ($po->delete($poId)) {
                return ['success' => true, 'message' => 'PO eliminada correctamente'];
            } else {
                throw new Exception("Error al eliminar la PO");
            }
        } catch (Exception $e) {
            error_log("Error al eliminar PO: " . $e->getMessage());
            throw $e;
        }
    }

    private function validatePassword() {
        try {
            if (empty($_POST['password'])) {
                throw new Exception("La contraseña es requerida");
            }

            $result = $this->usuario->authenticate(
                $_SESSION['user']['usuario_alias'],
                $_POST['password']
            );

            return [
                'success' => $result['success'],
                'message' => $result['success'] ? 'Contraseña válida' : 'Contraseña incorrecta'
            ];

        } catch (Exception $e) {
            error_log("Error al validar contraseña: " . $e->getMessage());
            throw $e;
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

            $detalleId = $this->poDetalle->create();
            if (!$detalleId) {
                throw new Exception("Error al agregar el detalle");
            }

            return [
                'success' => true,
                'message' => 'Detalle agregado exitosamente',
                'detalle_id' => $detalleId
            ];

        } catch (Exception $e) {
            error_log("Error al agregar detalle de PO: " . $e->getMessage());
            throw $e;
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

            if ($this->poDetalle->update()) {
                return ['success' => true, 'message' => 'Detalle actualizado exitosamente'];
            }
            throw new Exception("Error al actualizar el detalle");

        } catch (Exception $e) {
            error_log("Error al actualizar detalle de PO: " . $e->getMessage());
            throw $e;
        }
    }

    private function deletePoDetail() {
        try {
            if (empty($_POST['id'])) {
                throw new Exception("ID del detalle es requerido");
            }

            $this->poDetalle->id = $_POST['id'];
            if ($this->poDetalle->delete()) {
                return ['success' => true, 'message' => 'Detalle eliminado exitosamente'];
            }
            throw new Exception("Error al eliminar el detalle");

        } catch (Exception $e) {
            error_log("Error al eliminar detalle de PO: " . $e->getMessage());
            throw $e;
        }
    }

    private function getPoDetails() {
        try {
            if (!isset($_GET['po_id'])) {
                throw new Exception("ID de PO es requerido");
            }

            $result = $this->poDetalle->readByPo($_GET['po_id']);
            $detalles = $result->fetchAll(PDO::FETCH_ASSOC);

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $detalles]);
            exit;

        } catch (Exception $e) {
            error_log("Error al obtener detalles de PO: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }

    private function getPoInfo() {
        try {
            if (!isset($_GET['id'])) {
                throw new Exception("ID de PO es requerido");
            }

            $this->po->id = $_GET['id'];
            $poInfo = $this->po->readOne();
            
            if (!$poInfo) {
                throw new Exception("PO no encontrada");
            }

            // Obtener detalles
            $result = $this->poDetalle->readByPo($_GET['id']);
            $detalles = $result->fetchAll(PDO::FETCH_ASSOC);

            // Calcular total
            $total = $this->poDetalle->calculatePoTotal($_GET['id']);

            $response = [
                'success' => true,
                'po' => $poInfo,
                'detalles' => $detalles,
                'total' => $total,
                'progreso' => $this->po->getProgress()
            ];

            header('Content-Type: application/json');
            echo json_encode($response);
            exit;

        } catch (Exception $e) {
            error_log("Error al obtener información de PO: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }

    // Métodos públicos para obtener datos para las vistas
    public function getPos($filtros = []) {
        return $this->po->read($filtros)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPoById($id) {
        $this->po->id = $id;
        return $this->po->readOne();
    }

    public function getPoDetalles($poId) {
        return $this->poDetalle->readByPo($poId)->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Si se hace una llamada directa al controlador
if(basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    $controller = new PoController();
    $controller->handleRequest();
}
?>
