<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
require_once '../controllers/PoController.php';
require_once '../controllers/ClienteController.php';

$poController = new PoController();
$clienteController = new ClienteController();

// Obtener las POs
$filtros = [];
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!empty($_GET['po_numero'])) $filtros['po_numero'] = $_GET['po_numero'];
    if (!empty($_GET['estado'])) $filtros['estado'] = $_GET['estado'];
    if (!empty($_GET['cliente'])) $filtros['cliente'] = $_GET['cliente'];
    if (!empty($_GET['fecha_inicio']) && !empty($_GET['fecha_fin'])) {
        $filtros['fecha_inicio'] = $_GET['fecha_inicio'];
        $filtros['fecha_fin'] = $_GET['fecha_fin'];
    }
}
$pos = $poController->getPos($filtros);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MINLEX | Purchase Orders</title>
    <?php include '../components/meta.php'; ?>
</head>
<body>
    <?php include '../components/menu_lateral.php'; ?>
    
    <main>
        <div class="border-bottom border-secondary titulo-vista">
            <h1><strong>PO (Purchase Orders)</strong></h1><br>
            <a href="crear_po.php" class="btn btn-dark">
                Crear nueva PO
            </a>
            <a href="../components/descargar_excel.php" class="btn btn-success" style="margin-left: 10px;">
                Descargar Excel
            </a>
        </div>
        
        <!-- Filtros -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-filter"></i> Filtros
            </div>
            <div class="card-body">
                <form id="filtroForm" method="GET">
                    <div class="row">
                        <div class="col-md-2 mb-2">
                            <label for="po_numero" class="form-label">Número PO</label>
                            <input type="text" name="po_numero" id="po_numero" class="form-control filtro" placeholder="# PO" value="<?php echo $_GET['po_numero'] ?? ''; ?>">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label for="estado" class="form-label">Estado</label>
                            <select name="estado" id="estado" class="form-select filtro">
                                <option value="">Todos</option>
                                <option value="Pendiente" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'Pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                                <option value="En proceso" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'En proceso') ? 'selected' : ''; ?>>En proceso</option>
                                <option value="Completada" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'Completada') ? 'selected' : ''; ?>>Completada</option>
                                <option value="Cancelada" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'Cancelada') ? 'selected' : ''; ?>>Cancelada</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label for="cliente" class="form-label">Cliente</label>
                            <input type="text" name="cliente" id="cliente" class="form-control filtro" placeholder="Cliente" value="<?php echo $_GET['cliente'] ?? ''; ?>">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label for="fecha_inicio" class="form-label">Fecha Desde</label>
                            <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control filtro" value="<?php echo $_GET['fecha_inicio'] ?? ''; ?>">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label for="fecha_fin" class="form-label">Fecha Hasta</label>
                            <input type="date" name="fecha_fin" id="fecha_fin" class="form-control filtro" value="<?php echo $_GET['fecha_fin'] ?? ''; ?>">
                        </div>
                        <div class="col-md-1 mb-2 d-flex align-items-end">
                            <button type="button" id="limpiar-filtros" class="btn btn-secondary">
                                <i class="fas fa-eraser"></i> Limpiar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de POs -->
        <div class="table-responsive">
            <table class="table table-striped table-hover" id="tabla-pos">
                <thead>
                    <tr>
                        <th class="sortable" data-column="id">ID <i class="fas fa-sort"></i></th>
                        <th class="sortable" data-column="po">PO <i class="fas fa-sort"></i></th>
                        <th class="sortable" data-column="creacion">Fecha Creación <i class="fas fa-sort"></i></th>
                        <th class="sortable" data-column="envio">Fecha Envío <i class="fas fa-sort"></i></th>
                        <th class="sortable" data-column="estado">Estado <i class="fas fa-sort"></i></th>
                        <th class="sortable" data-column="cliente">Cliente <i class="fas fa-sort"></i></th>
                        <th class="sortable" data-column="usuario">Usuario de ingreso <i class="fas fa-sort"></i></th>
                        <th class="sortable" data-column="total_piezas">Total Piezas <i class="fas fa-sort"></i></th>
                        <th class="sortable" data-column="progreso">Progreso <i class="fas fa-sort"></i></th>
                        <th>Opciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pos as $po): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($po['id']); ?></td>
                        <td><?php echo htmlspecialchars($po['po_numero']); ?></td>
                        <td><?php echo htmlspecialchars($po['po_fecha_creacion']); ?></td>
                        <td><?php echo htmlspecialchars($po['po_fecha_envio_programada'] ?? 'No definida'); ?></td>
                        <td>
                            <span class="badge <?php 
                                echo match($po['po_estado']) {
                                    'Pendiente' => 'bg-warning',
                                    'En proceso' => 'bg-primary',
                                    'Completada' => 'bg-success',
                                    'Cancelada' => 'bg-danger',
                                    default => 'bg-secondary'
                                };
                            ?>">
                                <?php echo htmlspecialchars($po['po_estado']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($po['cliente_empresa']); ?></td>
                        <td><?php echo htmlspecialchars($po['usuario_creacion']); ?></td>
                        <td><?php echo htmlspecialchars($po['total_piezas']); ?></td>
                        <td>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: <?php echo $po['progreso']; ?>%" aria-valuenow="<?php echo $po['progreso']; ?>" aria-valuemin="0" aria-valuemax="100">
                                    <?php echo $po['progreso']; ?>%
                                </div>
                            </div>
                        </td>
                        <td>
                            <button type="button" class="btn btn-light view-po" 
                                    data-id="<?php echo $po['id']; ?>"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#poDetailModal">
                                <svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M10 4H6C4.89543 4 4 4.89543 4 6V18C4 19.1046 4.89543 20 6 20H18C19.1046 20 20 19.1046 20 18V14M12 12L20 4M20 4V9M20 4H15" 
                                          stroke="black" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                                </svg>
                            </button>
                            <a href="editar_po.php?id=<?php echo $po['id']; ?>" class="btn btn-warning">
                                <svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M14 6L16.2929 3.70711C16.6834 3.31658 17.3166 3.31658 17.7071 3.70711L20.2929 6.29289C20.6834 6.68342 20.6834 7.31658 20.2929 7.70711L18 10M14 6L4.29289 15.7071C4.10536 15.8946 4 16.149 4 16.4142V19C4 19.5523 4.44772 20 5 20H7.58579C7.851 20 8.10536 19.8946 8.29289 19.7071L18 10M14 6L18 10" 
                                          stroke="white" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                                </svg>
                            </a>
                            <button type="button" class="btn btn-danger delete-po"
                                    data-id="<?php echo $po['id']; ?>"
                                    data-po-numero="<?php echo htmlspecialchars($po['po_numero']); ?>"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#deletePoModal">
                                <svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M9 7V7C9 5.34315 10.3431 4 12 4V4C13.6569 4 15 5.34315 15 7V7M9 7H15M9 7H6M15 7H18M20 7H18M4 7H6M6 7V18C6 19.1046 6.89543 20 8 20H16C17.1046 20 18 19.1046 18 18V7" 
                                          stroke="white" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                                </svg>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        <div class="row mt-3">
            <div class="col-md-6">
                <div class="registros-info">
                    Mostrando <span id="registros-mostrados">0</span> de <span id="registros-totales">0</span> registros
                </div>
            </div>
            <div class="col-md-6">
                <nav aria-label="Paginación de POs">
                    <ul class="pagination justify-content-end" id="paginacion">
                        <!-- Los botones de paginación se generarán dinámicamente -->
                    </ul>
                </nav>
            </div>
            <div class="col-md-6 mt-2">
                <div class="form-group">
                    <label for="registros-por-pagina" class="form-label">Registros por página:</label>
                    <select class="form-select form-select-sm w-auto d-inline-block ms-2" id="registros-por-pagina">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
        </div>
    </main>

    <?php include '../components/footer.php'; ?>

    <!-- Modal para Ver Detalles de PO -->
    <div class="modal fade" id="poDetailModal" tabindex="-1" aria-labelledby="poDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="poDetailModalLabel">Detalles de PO</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-end mb-3">
                        <button type="button" class="btn btn-primary" id="generatePdfBtn">
                            <i class="fas fa-file-pdf"></i> Generar PDF
                        </button>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Información General</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th style="width: 40%;">Número PO:</th>
                                    <td id="detailPoNumero"></td>
                                </tr>
                                <tr>
                                    <th style="width: 40%;">Cliente:</th>
                                    <td id="detailCliente"></td>
                                </tr>
                                <tr>
                                    <th style="width: 40%;">Estado:</th>
                                    <td id="detailEstado"></td>
                                </tr>
                                <tr>
                                    <th style="width: 40%;">Fecha Creación:</th>
                                    <td id="detailFechaCreacion"></td>
                                </tr>
                                <tr>
                                    <th style="width: 40%;">Total Items:</th>
                                    <td id="detailTotalItems"></td>
                                </tr>
                                <tr>
                                    <th style="width: 40%;">Items Completados:</th>
                                    <td id="detailItemsCompletados"></td>
                                </tr>
                                <tr>
                                    <th style="width: 40%;">Valor Total:</th>
                                    <td id="detailValorTotal"></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Fechas y Envío</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th style="width: 40%;">Inicio Producción:</th>
                                    <td id="detailFechaInicio"></td>
                                </tr>
                                <tr>
                                    <th style="width: 40%;">Fin Producción:</th>
                                    <td id="detailFechaFin"></td>
                                </tr>
                                <tr>
                                    <th style="width: 40%;">Envío Programado:</th>
                                    <td id="detailFechaEnvio"></td>
                                </tr>
                                <tr>
                                    <th style="width: 40%;">Tipo Envío:</th>
                                    <td id="detailTipoEnvio"></td>
                                </tr>
                                <tr>
                                    <th style="width: 40%;">Usuario Creación:</th>
                                    <td id="detailUsuarioCreacion"></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h6>Comentario</h6>
                            <p id="detailComentario"></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Notas Internas</h6>
                            <div class="card">
                                <div class="card-body p-2">
                                    <div id="notasPreviewContainer">
                                        <div id="notasPreview" class="mb-2"></div>
                                        <button type="button" class="btn btn-sm btn-link" id="expandNotasBtn">
                                            <i class="fas fa-expand-alt"></i> Ver completo
                                        </button>
                                    </div>
                                    <div id="notasCompletasContainer" style="display:none;">
                                        <div id="notasCompletas" class="mb-2"></div>
                                        <button type="button" class="btn btn-sm btn-link" id="collapseNotasBtn">
                                            <i class="fas fa-compress-alt"></i> Ver menos
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <h6>Detalles de Items</h6>
                            
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered" id="detailsTable">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th>Descripción</th>
                                            <th>Talla</th>
                                            <th>Color</th>
                                            <th>Diseño</th>
                                            <th>Ubicación</th>
                                            <th>Cantidad Total</th>
                                            <th>Pcs/Cartón</th>
                                            <th>Pcs/Poly</th>
                                            <th>Precio Unit.</th>
                                            <th>Subtotal</th>
                                            <th>Estado</th>
                                            <th>Progreso</th>
                                            <th>Procesos</th>
                                        </tr>
                                    </thead>
                                    <tbody id="detailsTableBody"></tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="10" class="text-end">Total:</th>
                                            <th id="detailTotal"></th>
                                            <th colspan="3"></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Eliminar PO -->
    <div class="modal fade" id="deletePoModal" tabindex="-1" aria-labelledby="deletePoModalLabel" aria-hidden="true">
        <div class="modal-dialog"  style="width: 700px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deletePoModalLabel">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro que desea eliminar la PO <strong><span id="deletePoNumero"></span></strong>?</p>
                    <p>Esta acción no se puede deshacer.</p>
                    <form id="deletePoForm">
                        <input type="hidden" id="deletePoId" name="id">
                        <div class="mb-3">
                            <label for="deletePassword" class="form-label">Ingrese su contraseña para confirmar:</label>
                            <input type="password" class="form-control" id="deletePassword" name="password" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Eliminar</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="../js/po.js"></script>
</body>
</html>