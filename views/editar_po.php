<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once '../controllers/PoController.php';
require_once '../controllers/ClienteController.php';
require_once '../controllers/ItemController.php';
require_once '../models/Po.php';
require_once '../config/Database.php';

$poController = new PoController();
$clienteController = new ClienteController();
$itemController = new ItemController();

// Verificar si se proporcionó un ID de PO
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: po.php?error=no_id");
    exit();
}

$poId = $_GET['id'];

// Obtener la información de la PO directamente del modelo
$database = new Database();
$conn = $database->getConnection();
$poModel = new Po($conn);
$poInfo = $poModel->readOne($poId);

// Si no se encuentra la PO, redirigir
if (!$poInfo) {
    header("Location: po.php?error=not_found");
    exit();
}

// Obtener los detalles de la PO
$poDetails = [];
try {
    // Obtener los detalles directamente de la base de datos
    $query = "SELECT pd.*, i.item_numero, i.item_nombre 
              FROM po_detalle pd 
              LEFT JOIN items i ON pd.pd_item = i.id 
              WHERE pd.pd_id_po = :id";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $poId);
    $stmt->execute();
    $poDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Error al obtener detalles de PO: " . $e->getMessage());
}

// Obtener la lista de clientes activos
$clientes = $clienteController->getClientesActivos();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Purchase Order - MinLex El Salvador</title>
    <?php include '../components/meta.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../css/po.css">
</head>
<body>
    <?php include '../components/menu_lateral.php'; ?>
    
    <main>
        <div class="container-fluid px-4">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">Editar Purchase Order: <?php echo htmlspecialchars($poInfo['po_numero']); ?></h4>
                            <a href="po.php" class="btn btn-light btn-sm">
                                <i class="fas fa-arrow-left"></i> Volver a lista
                            </a>
                        </div>
                        <div class="card-body">
                            <form id="poForm" method="POST">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="id" id="poId" value="<?php echo $poId; ?>">
                                <input type="hidden" name="items" id="items" value="">
                                
                                <div class="row">
                                    <!-- Información General - Lado Izquierdo -->
                                    <div class="col-md-6">
                                        <h5 class="border-bottom pb-2 mb-3">Información General</h5>
                                        <div class="mb-3">
                                            <label class="form-label">Número PO</label>
                                            <input type="text" class="form-control" id="poNumero" name="po_numero" value="<?php echo htmlspecialchars($poInfo['po_numero']); ?>" readonly disabled>
                                            <small class="form-text text-muted">El número de PO no puede modificarse</small>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Cliente*</label>
                                                <select class="form-select select2" id="poCliente" name="po_id_cliente" required>
                                                    <option value="">Seleccione un cliente</option>
                                                    <?php foreach ($clientes as $cliente): ?>
                                                        <option value="<?php echo $cliente['id']; ?>" <?php echo ($cliente['id'] == $poInfo['po_id_cliente']) ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($cliente['cliente_empresa']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Tipo de Envío</label>
                                                <select class="form-select select2" id="poTipoEnvio" name="po_tipo_envio">
                                                    <option value="Tipo 1" <?php echo ($poInfo['po_tipo_envio'] == 'Tipo 1') ? 'selected' : ''; ?>>Tipo 1</option>
                                                    <option value="Tipo 2" <?php echo ($poInfo['po_tipo_envio'] == 'Tipo 2') ? 'selected' : ''; ?>>Tipo 2</option>
                                                    <option value="Tipo 3" <?php echo ($poInfo['po_tipo_envio'] == 'Tipo 3') ? 'selected' : ''; ?>>Tipo 3</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Comentario (breve)</label>
                                            <textarea class="form-control" id="poComentario" name="po_comentario" rows="2" maxlength="255"><?php echo htmlspecialchars($poInfo['po_comentario']); ?></textarea>
                                        </div>
                                    </div>

                                    <!-- Fechas - Lado Derecho -->
                                    <div class="col-md-6">
                                        <h5 class="border-bottom pb-2 mb-3">Fechas y Notas</h5>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Inicio Producción <small class="text-muted">(Auto)</small></label>
                                                <input type="date" class="form-control" id="poFechaInicio" name="po_fecha_inicio_produccion" value="<?php echo $poInfo['po_fecha_inicio_produccion']; ?>" disabled>
                                                <small class="form-text text-muted">Las fechas se generan automáticamente.</small>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Fin Producción <small class="text-muted">(Auto)</small></label>
                                                <input type="date" class="form-control" id="poFechaFin" name="po_fecha_fin_produccion" value="<?php echo $poInfo['po_fecha_fin_produccion']; ?>" disabled>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Envío Programado*</label>
                                            <input type="date" class="form-control" id="poFechaEnvio" name="po_fecha_envio_programada" value="<?php echo $poInfo['po_fecha_envio_programada']; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Estado de la PO</label>
                                            <select class="form-select" id="poEstado" name="po_estado">
                                                <option value="Pendiente" <?php echo ($poInfo['po_estado'] == 'Pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                                                <option value="En proceso" <?php echo ($poInfo['po_estado'] == 'En proceso') ? 'selected' : ''; ?>>En proceso</option>
                                                <option value="Completada" <?php echo ($poInfo['po_estado'] == 'Completada') ? 'selected' : ''; ?>>Completada</option>
                                                <option value="Cancelada" <?php echo ($poInfo['po_estado'] == 'Cancelada') ? 'selected' : ''; ?>>Cancelada</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Notas Detalladas</label>
                                            <div class="card">
                                                <div class="card-body p-2">
                                                    <div id="notasPreview" class="mb-2">
                                                        <?php 
                                                        if (!empty($poInfo['po_notas'])) {
                                                            $notasPreview = mb_substr(htmlspecialchars($poInfo['po_notas']), 0, 100);
                                                            echo $notasPreview . (strlen($poInfo['po_notas']) > 100 ? '...' : '');
                                                        } else {
                                                            echo '<em class="text-muted">No hay notas detalladas</em>';
                                                        }
                                                        ?>
                                                    </div>
                                                    <div class="d-flex justify-content-between">
                                                        <button type="button" class="btn btn-sm btn-link" id="expandNotasBtn" <?php echo empty($poInfo['po_notas']) ? 'style="display:none;"' : ''; ?>>
                                                            <i class="fas fa-expand-alt"></i> Ver completo
                                                        </button>
                                                        <button type="button" class="btn btn-outline-dark btn-sm" data-bs-toggle="modal" data-bs-target="#notasModal">
                                                            <i class="fas fa-edit"></i> Editar Notas
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <input type="hidden" id="poNotas" name="po_notas" value="<?php echo htmlspecialchars($poInfo['po_notas']); ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Detalles de PO -->
                                <div class="card mt-4">
                                    <div class="card-header d-flex justify-content-between align-items-center bg-light">
                                        <h5 class="mb-0">Detalles de Items</h5>
                                        <button type="button" class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#itemSearchModal">
                                            <i class="fas fa-plus-circle"></i> Agregar Items
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive mt-3">
                                            <table class="table table-bordered table-hover table-details">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Número</th>
                                                        <th>Nombre</th>
                                                        <th>Cantidad Total</th>
                                                        <th>Pzs x Cartón</th>
                                                        <th>Pzs x Poly</th>
                                                        <th>Precio Unit.</th>
                                                        <th>Subtotal</th>
                                                        <th>Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="itemsTableBody">
                                                    <!-- Los items se cargarán dinámicamente -->
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="6" class="text-end"><strong>Total:</strong></td>
                                                        <td id="totalGeneral">$0.00</td>
                                                        <td></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn btn-dark" style="width: 250px;">
                                            <i class="fas fa-save"></i> Guardar Cambios
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de búsqueda de items -->
        <div class="modal fade" id="itemSearchModal" tabindex="-1" aria-labelledby="itemSearchModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="itemSearchModalLabel">Buscar Items</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-5">
                                <input type="text" id="searchItemNumero" class="form-control" 
                                       placeholder="Buscar por número de item">
                            </div>
                            <div class="col-md-5">
                                <input type="text" id="searchItemNombre" class="form-control" 
                                       placeholder="Buscar por nombre de item">
                            </div>
                            <div class="col-md-2">
                                <button type="button" id="searchItemBtn" class="btn btn-dark w-100">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Número</th>
                                        <th>Nombre</th>
                                        <th>Descripción</th>
                                        <th>Talla</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="searchResults">
                                    <!-- Resultados de búsqueda aquí -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Modal para notas detalladas -->
        <div class="modal fade" id="notasModal" tabindex="-1" aria-labelledby="notasModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="notasModalLabel">Notas Detalladas</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <textarea class="form-control" id="notasDetalladas" rows="10"><?php echo htmlspecialchars($poInfo['po_notas']); ?></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-dark" id="saveNotas">Guardar Notas</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../components/footer.php'; ?>

    <script>
        // Pasar los detalles de la PO al JavaScript
        var poDetails = <?php echo json_encode($poDetails); ?>;
        
        // Agregar un manejador para errores de carga
        window.addEventListener('error', function(e) {
            console.error('Error de carga:', e.message);
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="../js/editar_po.js"></script>
</body>
</html>
