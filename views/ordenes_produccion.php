<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

require_once '../controllers/OrdenProduccionController.php';
require_once '../controllers/UsuarioController.php';
require_once '../controllers/ProcesoController.php';
require_once '../controllers/PODetalleController.php';
require_once '../controllers/ModuloController.php'; 
require_once '../controllers/ClienteController.php'; 

$ordenProduccionController = new OrdenProduccionController();
$usuarioController = new UsuarioController();
$procesoController = new ProcesoController();
$poDetalleController = new PODetalleController();
$moduloController = new ModuloController();
$clienteController = new ClienteController(); 

$operadores = $usuarioController->getUsuariosActivos() ?? [];
$procesos = $procesoController->getProcesos() ?? [];
$poDetalles = $poDetalleController->getAllPODetalles() ?? [];
$modulos = $moduloController->getModulosActivos() ?? []; 
$clientes = $clienteController->getClientesActivos() ?? []; 

$itemNumero = isset($_GET['item_numero']) ? $_GET['item_numero'] : '';
$operador = isset($_GET['operador']) ? $_GET['operador'] : '';
$estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$fechaInicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$fechaFin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';
$poNumero = isset($_GET['po_numero']) ? $_GET['po_numero'] : '';
$moduloId = isset($_GET['modulo_id']) ? $_GET['modulo_id'] : ''; 

$pageTitle = "Órdenes de Producción";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MINLEX | <?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../css/style_main.css">
    <link rel="stylesheet" href="../css/ordenes_produccion.css"> 
</head>
<body>
    <?php include '../components/menu_lateral.php'; ?>

    <main>
        <div class="titulo-vista d-flex justify-content-between align-items-center mb-4">
            <h1><strong><?php echo $pageTitle; ?></strong></h1>
            <div>
                <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#modalProgramarOrdenes" style="width: 250px;">
                    <i class="fas fa-tasks"></i> Programar Ordenes
                </button>
                <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#ordenModal">
                    <i class="fas fa-plus"></i> Nueva Orden
                </button>
            </div>
        </div>

        <div class="container-fluid">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-filter"></i> Filtros
                </div>
                <div class="card-body">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-2">
                            <label for="po_numero" class="form-label">Número de PO</label>
                            <input type="text" class="form-control filtro" id="po_numero" name="po_numero" value="<?php echo htmlspecialchars($poNumero); ?>" placeholder="Buscar por PO...">
                        </div>
                        <div class="col-md-2">
                            <label for="item_numero" class="form-label">Número de Item</label>
                            <input type="text" class="form-control filtro" id="item_numero" name="item_numero" value="<?php echo htmlspecialchars($itemNumero); ?>" placeholder="Buscar por número...">
                        </div>
                        <div class="col-md-2">
                            <label for="operador" class="form-label">Operador</label>
                            <input type="text" class="form-control filtro" id="operador" name="operador" value="<?php echo htmlspecialchars($operador); ?>" placeholder="Buscar por nombre...">
                        </div>
                        <div class="col-md-2">
                            <label for="modulo_id" class="form-label">Módulo</label>
                            <select class="form-select filtro" id="modulo_id" name="modulo_id">
                                <option value="">Todos</option>
                                <?php foreach ($modulos as $modulo): ?>
                                    <option value="<?php echo htmlspecialchars($modulo['id']); ?>" <?php echo $moduloId == $modulo['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($modulo['modulo_codigo']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <label for="estado" class="form-label">Estado</label>
                            <select class="form-select filtro" id="estado" name="estado">
                                <option value="">Todos</option>
                                <option value="Pendiente" <?php echo $estado === 'Pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                <option value="En proceso" <?php echo $estado === 'En proceso' ? 'selected' : ''; ?>>En proceso</option>
                                <option value="Completado" <?php echo $estado === 'Completado' ? 'selected' : ''; ?>>Completado</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <label for="fecha_inicio" class="form-label">Desde</label>
                            <input type="date" class="form-control filtro" id="fecha_inicio" name="fecha_inicio" value="<?php echo htmlspecialchars($fechaInicio); ?>">
                        </div>
                        <div class="col-md-1">
                            <label for="fecha_fin" class="form-label">Hasta</label>
                            <input type="date" class="form-control filtro" id="fecha_fin" name="fecha_fin" value="<?php echo htmlspecialchars($fechaFin); ?>">
                        </div>
                        <div class="col-md-auto">
                            <button class="btn btn-secondary w-100" id="limpiar-filtros">
                                <i class="fas fa-eraser"></i> Limpiar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover" id="tabla-ordenes">
                    <thead>
                        <tr>
                            <th class="sortable" data-column="id">ID <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-column="po_numero">PO <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-column="item">Item <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-column="proceso">Proceso <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-column="operador">Operador <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-column="modulo">Módulo <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-column="estado">Estado <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-column="fecha_inicio">Fecha Inicio <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-column="fecha_fin">Fecha Fin <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-column="aprobacion">Aprobación <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-column="completado">Completado <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-column="opciones">Opciones <i class="fas fa-sort"></i></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $filters = [
                            'po_numero' => $poNumero,
                            'item_numero' => $itemNumero,
                            'operador' => $operador,
                            'estado' => $estado,
                            'fecha_inicio' => $fechaInicio,
                            'fecha_fin' => $fechaFin,
                            'modulo_id' => $moduloId 
                        ];

                        $ordenes = $ordenProduccionController->searchOrdenes($filters); 

                        if (empty($ordenes)) {
                            echo '<tr><td colspan="13" class="text-center">No se encontraron órdenes de producción</td></tr>';
                        } else {
                            foreach ($ordenes as $orden) {
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($orden['id']) . '</td>';
                                echo '<td>' . htmlspecialchars($orden['po_numero'] ?? 'No asignado') . '</td>';
                                echo '<td>' . htmlspecialchars($orden['item_numero'] . ' - ' . $orden['item_nombre']) . '</td>';
                                echo '<td>' . htmlspecialchars($orden['pp_nombre']) . '</td>';
                                echo '<td>' . htmlspecialchars($orden['usuario_nombre'] . ' ' . $orden['usuario_apellido']) . '</td>';
                                echo '<td>' . htmlspecialchars($orden['modulo_codigo'] ?? 'No asignado') . '</td>';
                                
                                $estadoClass = '';
                                switch ($orden['op_estado']) {
                                    case 'Pendiente':
                                        $estadoClass = 'bg-warning';
                                        break;
                                    case 'En proceso':
                                        $estadoClass = 'bg-primary';
                                        break;
                                    case 'Completado':
                                        $estadoClass = 'bg-success';
                                        break;
                                }
                                echo '<td><span class="badge ' . $estadoClass . '">' . htmlspecialchars($orden['op_estado']) . '</span></td>';
                                
                                echo '<td>' . ($orden['op_fecha_inicio'] ? htmlspecialchars(date('d/m/Y', strtotime($orden['op_fecha_inicio']))) : 'No iniciada') . '</td>';
                                echo '<td>' . ($orden['op_fecha_fin'] ? htmlspecialchars(date('d/m/Y', strtotime($orden['op_fecha_fin']))) : 'No finalizada') . '</td>';
                                
                                $aprobacionClass = '';
                                switch ($orden['op_estado_aprobacion']) {
                                    case 'Pendiente':
                                        $aprobacionClass = 'bg-warning';
                                        break;
                                    case 'Aprobado':
                                        $aprobacionClass = 'bg-success';
                                        break;
                                    case 'Rechazado':
                                        $aprobacionClass = 'bg-danger';
                                        break;
                                }
                                echo '<td><span class="badge ' . $aprobacionClass . '">' . htmlspecialchars($orden['op_estado_aprobacion']) . '</span></td>';
                                
                                $completado = 0;
                                $cantidad_asignada = $orden['op_cantidad_asignada'] ?? 0;
                                $cantidad_completada = $orden['op_cantidad_completada'] ?? 0;
                                
                                if ($cantidad_asignada > 0) {
                                    $completado = round(($cantidad_completada / $cantidad_asignada) * 100);
                                }
                                echo '<td>';
                                echo '<div class="progress" style="height: 20px;">';
                                echo '  <div class="progress-bar" role="progressbar" style="width: ' . $completado . '%;" aria-valuenow="' . $completado . '" aria-valuemin="0" aria-valuemax="100">' . $completado . '%</div>';
                                echo '</div>';
                                echo '</td>';

                                echo '<td>';
                                echo '<button class="btn btn-sm btn-info me-1 view-details" data-id="' . $orden['id'] . '" data-bs-toggle="tooltip" title="Ver Detalles"><i class="fas fa-eye"></i></button>';
                                echo '<button class="btn btn-sm btn-warning me-1 edit-orden" data-id="' . $orden['id'] . '" data-bs-toggle="tooltip" title="Editar"><i class="fas fa-edit"></i></button>';
                                echo '<button class="btn btn-sm btn-danger delete-orden" data-id="' . $orden['id'] . '" data-bs-toggle="tooltip" title="Eliminar"><i class="fas fa-trash"></i></button>';
                                echo '</td>';
                                echo '</tr>';
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <div class="registros-info">
                        Mostrando <span id="registros-mostrados">0</span> de <span id="registros-totales">0</span> registros
                    </div>
                </div>
                <div class="col-md-6">
                    <nav aria-label="Paginación de Órdenes">
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
        </div>
    </main>

    <?php include '../components/footer.php'; ?>

    <div class="modal fade" id="modalProgramarOrdenes" tabindex="-1" aria-labelledby="modalProgramarOrdenesLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" style="width: 1750px;">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="modalProgramarOrdenesLabel">Programar Ordenes de Producción</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formProgramarOrdenes">
                        <!-- Selector de módulo al inicio -->
                        <div class="mb-4">
                            <label for="selectModuloProgramacion" class="form-label fw-bold">1. Seleccione el Módulo a Asignar</label>
                            <select class="form-select form-select-lg" id="selectModuloProgramacion" name="modulo_id" required>
                                <option value="" selected disabled>Seleccione un módulo...</option>
                                <?php foreach ($modulos as $modulo): ?>
                                    <?php if ($modulo['modulo_estado'] === 'Activo'): ?>
                                        <option value="<?php echo htmlspecialchars($modulo['id']); ?>">
                                            <?php echo htmlspecialchars($modulo['modulo_codigo']); ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="row">
                            <!-- Columna izquierda: Órdenes disponibles -->
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">2. Órdenes Disponibles</h6>
                                    </div>
                                    <div class="card-body">
                                        <!-- Campo de búsqueda -->
                                        <div class="mb-3">
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                                <input type="text" class="form-control" id="buscarOrdenPO" placeholder="Buscar por número de PO...">
                                            </div>
                                        </div>
                                        <div id="listaOrdenesProgramar" style="height: 350px; overflow-y: auto;">
                                            <!-- Las órdenes se cargarán aquí mediante JavaScript -->
                                            <p class="text-center">Cargando órdenes...</p>
                                        </div>
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-sm btn-dark" id="btnSeleccionarTodas">Seleccionar Todas</button>
                                            <button type="button" class="btn btn-sm btn-light" id="btnLimpiarSeleccion">Limpiar Selección</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Columna derecha: Órdenes seleccionadas -->
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0">3. Órdenes Seleccionadas (<span id="contadorSeleccionadas">0</span>)</h6>
                                    </div>
                                    <div class="card-body">
                                        <div id="listaOrdenesSeleccionadas" style="height: 350px; overflow-y: auto;">
                                            <p class="text-center text-muted" id="mensajeNoSeleccionadas">No hay órdenes seleccionadas</p>
                                            <!-- Las órdenes seleccionadas se mostrarán aquí -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="btnGuardarProgramacion">Guardar Programación</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalConfirmarProgramacion" tabindex="-1" aria-labelledby="modalConfirmarProgramacionLabel" aria-hidden="true">
        <div class="modal-dialog" style="width: 700px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalConfirmarProgramacionLabel">Confirmar Programación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro de asignar <strong id="confirmOrdenesCount">X</strong> órdenes de producción al módulo <strong id="confirmModuloNombre">Y</strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-dark" id="btnConfirmarGuardarProgramacion">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteOrdenModal" tabindex="-1" aria-labelledby="deleteOrdenModalLabel" aria-hidden="true">
        <div class="modal-dialog" style="width: 700px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteOrdenModalLabel">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro de que desea eliminar esta orden de producción? Esta acción no se puede deshacer.</p>
                    <form id="deleteOrdenForm">
                        <input type="hidden" id="deleteOrdenId" name="id">
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
    <script src="../js/ordenes_produccion.js"></script>
</body>
</html>
