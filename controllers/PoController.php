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

        // Verificar si se está solicitando getPoDetails directamente
        if (isset($_GET['action']) && $_GET['action'] === 'getPoDetails') {
            $this->getPoDetails();
            return;
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

            // Generar automáticamente el número de PO
            $poNumero = $this->po->getNextPoNumber();

            // Validar datos básicos de la PO
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
                'po_numero' => $poNumero,
                'po_id_cliente' => $_POST['po_id_cliente'],
                'po_fecha_inicio_produccion' => $_POST['po_fecha_inicio_produccion'] ?: null,
                'po_fecha_fin_produccion' => $_POST['po_fecha_fin_produccion'] ?: null,
                'po_fecha_envio_programada' => $_POST['po_fecha_envio_programada'],
                'po_tipo_envio' => $_POST['po_tipo_envio'],
                'po_comentario' => $_POST['po_comentario'] ?: '',
                'po_notas' => $_POST['po_notas'] ?: '',
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
            
            // Asegurar que no haya salida antes de la respuesta JSON
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'PO creada exitosamente', 'poId' => $poId]);
            exit;

        } catch (Exception $e) {
            if (isset($conn)) {
                $conn->rollBack();
            }
            
            // Asegurar que no haya salida antes de la respuesta JSON
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }

    private function updatePo() {
        try {
            error_log("Iniciando updatePo con datos: " . json_encode($_POST));
            
            if (empty($_POST['id'])) {
                throw new Exception("ID de PO es requerido");
            }

            // Validar que el cliente sea válido
            if (empty($_POST['po_id_cliente'])) {
                throw new Exception("Cliente es requerido");
            }

            $conn = $this->db->getConnection();
            $conn->beginTransaction();

            // Actualizar la PO
            $query = "UPDATE po SET 
                po_id_cliente = :po_id_cliente,
                po_fecha_inicio_produccion = :po_fecha_inicio_produccion,
                po_fecha_fin_produccion = :po_fecha_fin_produccion,
                po_fecha_envio_programada = :po_fecha_envio_programada,
                po_estado = :po_estado,
                po_tipo_envio = :po_tipo_envio,
                po_comentario = :po_comentario,
                po_notas = :po_notas
                WHERE id = :id";
                
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id', $_POST['id']);
            $stmt->bindParam(':po_id_cliente', $_POST['po_id_cliente']);
            
            // Manejar fechas
            $fechaInicio = !empty($_POST['po_fecha_inicio_produccion']) ? $_POST['po_fecha_inicio_produccion'] : null;
            $fechaFin = !empty($_POST['po_fecha_fin_produccion']) ? $_POST['po_fecha_fin_produccion'] : null;
            $fechaEnvio = !empty($_POST['po_fecha_envio_programada']) ? $_POST['po_fecha_envio_programada'] : null;
            
            $stmt->bindParam(':po_fecha_inicio_produccion', $fechaInicio);
            $stmt->bindParam(':po_fecha_fin_produccion', $fechaFin);
            $stmt->bindParam(':po_fecha_envio_programada', $fechaEnvio);
            $stmt->bindParam(':po_estado', $_POST['po_estado']);
            $stmt->bindParam(':po_tipo_envio', $_POST['po_tipo_envio']);
            $stmt->bindParam(':po_comentario', $_POST['po_comentario']);
            $stmt->bindParam(':po_notas', $_POST['po_notas']);
            
            if (!$stmt->execute()) {
                throw new Exception("Error al actualizar la PO: " . implode(", ", $stmt->errorInfo()));
            }

            error_log("PO actualizada correctamente, procesando items");
            
            // Actualizar los detalles de la PO
            if (isset($_POST['items']) && !empty($_POST['items'])) {
                $items = json_decode($_POST['items'], true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception("Error al decodificar los items: " . json_last_error_msg());
                }
                
                error_log("Items decodificados: " . json_encode($items));
                
                // Obtener los detalles actuales para comparar
                $query = "SELECT * FROM po_detalle WHERE pd_id_po = :po_id";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':po_id', $_POST['id']);
                $stmt->execute();
                $detallesActuales = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                error_log("Detalles actuales: " . json_encode($detallesActuales));
                
                $idsActuales = array_column($detallesActuales, 'id');
                
                // Identificar los IDs de items existentes en la nueva lista
                $idsNuevos = [];
                foreach ($items as $item) {
                    if (!empty($item['id'])) {
                        $idsNuevos[] = $item['id'];
                    }
                }
                
                error_log("IDs actuales: " . json_encode($idsActuales));
                error_log("IDs nuevos: " . json_encode($idsNuevos));
                
                // Eliminar los detalles que ya no existen
                foreach ($detallesActuales as $detalle) {
                    if (!in_array($detalle['id'], $idsNuevos) && $detalle['id'] != null) {
                        error_log("Eliminando detalle con ID: " . $detalle['id']);
                        // Usar directamente la consulta para eliminar
                        $stmt = $conn->prepare("DELETE FROM po_detalle WHERE id = :id");
                        $stmt->bindParam(':id', $detalle['id']);
                        if (!$stmt->execute()) {
                            throw new Exception("Error al eliminar el detalle con ID " . $detalle['id']);
                        }
                    }
                }
                
                // Actualizar o crear los detalles
                foreach ($items as $item) {
                    // Si el item tiene id y existe en la base de datos, actualizar
                    if (!empty($item['id']) && in_array($item['id'], $idsActuales)) {
                        error_log("Actualizando detalle con ID: " . $item['id']);
                        
                        $updateData = [
                            'id' => $item['id'],
                            'pd_cant_piezas_total' => $item['pd_cant_piezas_total'],
                            'pd_pcs_carton' => $item['pd_pcs_carton'],
                            'pd_pcs_poly' => $item['pd_pcs_poly'],
                            'pd_precio_unitario' => $item['pd_precio_unitario'],
                            'pd_estado' => 'Pendiente'
                        ];
                        
                        // Actualizar usando una consulta directa
                        $query = "UPDATE po_detalle SET 
                            pd_cant_piezas_total = :pd_cant_piezas_total,
                            pd_pcs_carton = :pd_pcs_carton,
                            pd_pcs_poly = :pd_pcs_poly,
                            pd_precio_unitario = :pd_precio_unitario,
                            pd_estado = :pd_estado
                            WHERE id = :id";
                            
                        $stmt = $conn->prepare($query);
                        $stmt->bindParam(':pd_cant_piezas_total', $updateData['pd_cant_piezas_total']);
                        $stmt->bindParam(':pd_pcs_carton', $updateData['pd_pcs_carton']);
                        $stmt->bindParam(':pd_pcs_poly', $updateData['pd_pcs_poly']);
                        $stmt->bindParam(':pd_precio_unitario', $updateData['pd_precio_unitario']);
                        $stmt->bindParam(':pd_estado', $updateData['pd_estado']);
                        $stmt->bindParam(':id', $updateData['id']);
                        
                        if (!$stmt->execute()) {
                            throw new Exception("Error al actualizar el detalle de la PO");
                        }
                    } else {
                        // Si el item no tiene id o no existe en la base de datos, crear nuevo
                        error_log("Creando nuevo detalle para item: " . $item['pd_item']);
                        
                        $detalleData = [
                            'pd_id_po' => $_POST['id'],
                            'pd_item' => $item['pd_item'],
                            'pd_cant_piezas_total' => $item['pd_cant_piezas_total'],
                            'pd_pcs_carton' => $item['pd_pcs_carton'],
                            'pd_pcs_poly' => $item['pd_pcs_poly'],
                            'pd_precio_unitario' => $item['pd_precio_unitario'],
                            'pd_estado' => 'Pendiente'
                        ];
                        
                        // Crear usando una consulta directa
                        $query = "INSERT INTO po_detalle (
                            pd_id_po, pd_item, pd_cant_piezas_total,
                            pd_pcs_carton, pd_pcs_poly, pd_estado,
                            pd_precio_unitario)
                            VALUES (
                            :pd_id_po, :pd_item, :pd_cant_piezas_total,
                            :pd_pcs_carton, :pd_pcs_poly, :pd_estado,
                            :pd_precio_unitario)";
                            
                        $stmt = $conn->prepare($query);
                        $stmt->bindParam(':pd_id_po', $detalleData['pd_id_po']);
                        $stmt->bindParam(':pd_item', $detalleData['pd_item']);
                        $stmt->bindParam(':pd_cant_piezas_total', $detalleData['pd_cant_piezas_total']);
                        $stmt->bindParam(':pd_pcs_carton', $detalleData['pd_pcs_carton']);
                        $stmt->bindParam(':pd_pcs_poly', $detalleData['pd_pcs_poly']);
                        $stmt->bindParam(':pd_estado', $detalleData['pd_estado']);
                        $stmt->bindParam(':pd_precio_unitario', $detalleData['pd_precio_unitario']);
                        
                        if (!$stmt->execute()) {
                            throw new Exception("Error al crear el detalle de la PO");
                        }
                    }
                }
            }
            
            error_log("Todos los items procesados correctamente");
            $conn->commit();
            
            // Asegurar que no haya salida antes de la respuesta JSON
            if (ob_get_length()) ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'PO actualizada exitosamente']);
            exit;
            
        } catch (Exception $e) {
            error_log("Error en updatePo: " . $e->getMessage());
            
            if (isset($conn)) {
                $conn->rollBack();
            }
            
            // Asegurar que no haya salida antes de la respuesta JSON
            if (ob_get_length()) ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
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
            if ($this->poDetalle->delete($_POST['id'])) {
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
            
            // Obtener los detalles completos de la PO
            $detallesCompletos = $this->po->getDetallesCompletos($id);
            
            if (!$detallesCompletos) {
                throw new Exception("No se encontraron detalles para la PO");
            }
            
            // Verificar si hay detalles
            if (!isset($detallesCompletos['detalles']) || empty($detallesCompletos['detalles'])) {
                // Intentar obtener los detalles directamente de la tabla po_detalle
                $query = "SELECT pd.*, i.item_numero, i.item_nombre 
                          FROM po_detalle pd 
                          LEFT JOIN items i ON pd.pd_item = i.id 
                          WHERE pd.pd_id_po = :id";
                
                $stmt = $this->db->getConnection()->prepare($query);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (!empty($detalles)) {
                    $detallesCompletos['detalles'] = $detalles;
                } else {
                    // Si no hay detalles, devolver un array vacío pero con éxito
                    echo json_encode([
                        'success' => true,
                        'data' => [
                            'po' => $detallesCompletos,
                            'detalles' => []
                        ]
                    ]);
                    return;
                }
            }
            
            // Calcular totales y otros datos adicionales
            $totalPiezas = 0;
            $totalValor = 0;
            foreach ($detallesCompletos['detalles'] as $detalle) {
                $totalPiezas += intval($detalle['pd_cant_piezas_total']);
                $totalValor += intval($detalle['pd_cant_piezas_total']) * floatval($detalle['pd_precio_unitario']);
            }
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'po' => $detallesCompletos,
                    'detalles' => $detallesCompletos['detalles'],
                    'total_piezas' => $totalPiezas,
                    'total_valor' => $totalValor
                ]
            ]);
            
        } catch (Exception $e) {
            error_log("Error en PoController::getPoDetails: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
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

    public function getNextPoNumber() {
        return $this->po->getNextPoNumber();
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

    public function getPosByModulo($moduloId) {
        $query = "SELECT p.*, c.cliente_empresa, 
                 (SELECT COUNT(*) FROM po_detalle pd WHERE pd.pd_id_po = p.id) as total_items,
                 (SELECT SUM(pd.pd_cant_piezas_total) FROM po_detalle pd WHERE pd.pd_id_po = p.id) as total_piezas
                 FROM po p
                 LEFT JOIN clientes c ON p.po_id_cliente = c.id
                 WHERE p.po_modulo = :modulo_id
                 ORDER BY p.id DESC";
        
        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->bindParam(':modulo_id', $moduloId);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Método para obtener una PO por su ID
    public function getPoById($id) {
        return $this->po->readOne($id);
    }
    
    // Método para obtener los detalles de una PO (para vistas)
    public function getPoDetailsForView($poId) {
        $_GET['po_id'] = $poId;
        $_GET['action'] = 'getPoDetails';
        return $this->getPoDetails();
    }
}

if(basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    $controller = new PoController();
    $controller->handleRequest();
}
?>
