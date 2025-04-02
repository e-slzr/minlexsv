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

// Inicializar controladores
$ordenProduccionController = new OrdenProduccionController();
$usuarioController = new UsuarioController();
$procesoController = new ProcesoController();
$poDetalleController = new PODetalleController();
$moduloController = new ModuloController();

// Obtener datos necesarios
$operadores = $usuarioController->getUsuariosActivos() ?? [];
$procesos = $procesoController->getProcesos() ?? [];
$poDetalles = $poDetalleController->getAllPODetalles() ?? [];
$modulos = $moduloController->getModulos() ?? [];

// Obtener parámetros de filtro de la URL
$itemNumero = isset($_GET['item_numero']) ? $_GET['item_numero'] : '';
$operador = isset($_GET['operador']) ? $_GET['operador'] : '';
$estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$fechaInicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$fechaFin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';
$poNumero = isset($_GET['po_numero']) ? $_GET['po_numero'] : '';

// Título de la página
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
</head>
<body>
    <?php include '../components/menu_lateral.php'; ?>

    <main>
        <div class="titulo-vista">
            <h1><strong><?php echo $pageTitle; ?></strong></h1>
            <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#ordenModal">
                <i class="fas fa-plus"></i> Nueva Orden
            </button>
        </div>

        <div class="container-fluid">
            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-filter"></i> Filtros
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2 mb-2">
                            <label for="po_numero" class="form-label">Número de PO</label>
                            <input type="text" class="form-control filtro" id="po_numero" name="po_numero" value="<?php echo htmlspecialchars($poNumero); ?>" placeholder="Buscar por PO...">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label for="item_numero" class="form-label">Número de Item</label>
                            <input type="text" class="form-control filtro" id="item_numero" name="item_numero" value="<?php echo htmlspecialchars($itemNumero); ?>" placeholder="Buscar por número...">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label for="operador" class="form-label">Operador</label>
                            <input type="text" class="form-control filtro" id="operador" name="operador" value="<?php echo htmlspecialchars($operador); ?>" placeholder="Buscar por nombre...">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label for="estado" class="form-label">Estado</label>
                            <select class="form-select filtro" id="estado" name="estado">
                                <option value="">Todos</option>
                                <option value="Pendiente" <?php echo $estado === 'Pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                <option value="En proceso" <?php echo $estado === 'En proceso' ? 'selected' : ''; ?>>En proceso</option>
                                <option value="Completado" <?php echo $estado === 'Completado' ? 'selected' : ''; ?>>Completado</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                            <input type="date" class="form-control filtro" id="fecha_inicio" name="fecha_inicio" value="<?php echo htmlspecialchars($fechaInicio); ?>">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label for="fecha_fin" class="form-label">Fecha Fin</label>
                            <input type="date" class="form-control filtro" id="fecha_fin" name="fecha_fin" value="<?php echo htmlspecialchars($fechaFin); ?>">
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-10 mb-2">
                            <!-- Espacio para más filtros si se necesitan en el futuro -->
                        </div>
                        <div class="col-md-2 mb-2 d-flex align-items-end">
                            <button class="btn btn-secondary w-100" id="limpiar-filtros">
                                <i class="fas fa-eraser"></i> Limpiar filtros
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de Órdenes -->
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
                            <th class="sortable" data-column="fecha_modificacion">Últ. Modificación <i class="fas fa-sort"></i></th>
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
                            'fecha_fin' => $fechaFin
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
                                
                                // Estado con badge
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
                                
                                // Fechas
                                echo '<td>' . ($orden['op_fecha_inicio'] ? htmlspecialchars(date('d/m/Y', strtotime($orden['op_fecha_inicio']))) : 'No iniciada') . '</td>';
                                echo '<td>' . ($orden['op_fecha_fin'] ? htmlspecialchars(date('d/m/Y', strtotime($orden['op_fecha_fin']))) : 'No finalizada') . '</td>';
                                
                                // Estado de aprobación con badge
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
                                
                                // Completado (porcentaje)
                                $completado = 0;
                                if ($orden['op_cantidad_asignada'] > 0) {
                                    $completado = round(($orden['op_cantidad_completada'] / $orden['op_cantidad_asignada']) * 100);
                                }
                                echo '<td>';
                                echo '<div class="progress" style="height: 20px;">';
                                echo '<div class="progress-bar bg-success" role="progressbar" style="width: ' . $completado . '%;" aria-valuenow="' . $completado . '" aria-valuemin="0" aria-valuemax="100">' . $completado . '%</div>';
                                echo '</div>';
                                echo '</td>';
                                
                                // Fecha de modificación
                                echo '<td>' . ($orden['op_fecha_modificacion'] ? htmlspecialchars(date('d/m/Y H:i', strtotime($orden['op_fecha_modificacion']))) : '-') . '</td>';
                                
                                // Botones de acción
                                echo '<td class="text-center">';
                                
                                // Botón para aprobar/rechazar orden (solo si está pendiente)
                                if ($orden['op_estado_aprobacion'] == 'Pendiente') {
                                    echo '<button type="button" class="btn btn-sm btn-outline-primary me-1 gestionar-aprobacion" data-id="' . $orden['id'] . '" title="Gestionar Aprobación">';
                                    echo '<i class="fas fa-check-circle"></i>';
                                    echo '</button>';
                                }
                                
                                // Botón Ver detalles
                                echo '<button type="button" class="btn btn-sm btn-info me-1 ver-orden" data-id="' . $orden['id'] . '" title="Ver Detalles">';
                                echo '<i class="fas fa-eye"></i>';
                                echo '</button>';
                                
                                // Botón Editar
                                echo '<button type="button" class="btn btn-sm btn-primary me-1 editar-orden" data-id="' . $orden['id'] . '" title="Editar Orden">';
                                echo '<i class="fas fa-edit"></i>';
                                echo '</button>';
                                
                                // Botón Eliminar
                                echo '<button type="button" class="btn btn-sm btn-danger eliminar-orden" data-id="' . $orden['id'] . '" title="Eliminar Orden">';
                                echo '<i class="fas fa-trash"></i>';
                                echo '</button>';
                                
                                echo '</td>';
                                echo '</tr>';
                            }
                        }
                        ?>
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

    <!-- Modal para Crear Nueva Orden -->
    <div class="modal fade" id="ordenModal" tabindex="-1" role="dialog" aria-labelledby="ordenModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg"  style="width: 60%;" role="document">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="ordenModalLabel">Nueva Orden de Producción</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="ordenForm">
                        <input type="hidden" id="ordenId" name="id" value="">
                        
                        <div class="row">
                            <!-- Información General - Lado Izquierdo -->
                            <div class="col-md-6">
                                <h5 class="border-bottom pb-2 mb-3">Información General</h5>
                                
                                <div class="mb-3">
                                    <label for="poDetalle" class="form-label">PO Detalle*</label>
                                    <select class="form-select select2" id="poDetalle" name="op_id_pd" required>
                                        <option value="">Seleccione un PO y un item</option>
                                        <?php foreach ($poDetalles as $poDetalle): ?>
                                            <option value="<?php echo htmlspecialchars($poDetalle['id']); ?>">
                                                PO: <?php echo htmlspecialchars($poDetalle['po_numero']); ?> - Item: <?php echo htmlspecialchars($poDetalle['item_numero'] . '-' . $poDetalle['item_nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted">Seleccione la PO y el item para el que se creará esta orden</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="proceso" class="form-label">Proceso*</label>
                                    <select class="form-select select2" id="proceso" name="op_id_proceso" required>
                                        <option value="">Seleccione un proceso</option>
                                        <?php foreach ($procesos as $proceso): ?>
                                            <option value="<?php echo htmlspecialchars($proceso['id']); ?>">
                                                <?php echo htmlspecialchars($proceso['pp_nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted">Proceso de producción que se realizará</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="operador" class="form-label">Operador Asignado*</label>
                                    <select class="form-select select2" id="operador" name="op_operador_asignado" required>
                                        <option value="">Seleccione un operador</option>
                                        <?php foreach ($operadores as $usuario): ?>
                                            <option value="<?php echo htmlspecialchars($usuario['id']); ?>">
                                                <?php echo htmlspecialchars($usuario['usuario_nombre'] . ' ' . $usuario['usuario_apellido']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted">Persona responsable de ejecutar el proceso</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Estado</label>
                                    <div>
                                        <span class="badge bg-warning">Pendiente</span>
                                        <input type="hidden" id="ordenEstado" name="op_estado" value="Pendiente">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Información Adicional - Lado Derecho -->
                            <div class="col-md-6">
                                <h5 class="border-bottom pb-2 mb-3">Información Adicional</h5>
                                
                                <div class="mb-3">
                                    <label for="cantidadAsignada" class="form-label">Cantidad Asignada*</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="cantidadAsignada" name="op_cantidad_asignada" required min="1" disabled>
                                        <span class="input-group-text" id="cantidadInfo">0/0</span>
                                    </div>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" id="asignarCompleto">
                                        <label class="form-check-label" for="asignarCompleto">
                                            Asignar cantidad completa
                                        </label>
                                    </div>
                                    <small class="text-muted d-block" id="cantidadRestante">Pendiente por asignar: 0</small>
                                </div>
                                
                                
                                <div class="mb-3">
                                    <label for="comentario" class="form-label">Comentario</label>
                                    <textarea class="form-control" id="comentario" name="op_comentario" rows="3"></textarea>
                                    <small class="text-muted">Instrucciones especiales o notas para esta orden</small>
                                </div>
                                
                                <!-- Campos ocultos para fechas automáticas -->
                                <input type="hidden" id="cantidadCompletada" name="op_cantidad_completada" value="0">
                            </div>
                        </div>
                        
                        <div class="mt-3 border-top pt-3">
                            <div class="text-muted small">
                                <i class="fas fa-info-circle"></i> Las fechas de inicio y fin se registrarán automáticamente cuando la orden inicie y se complete.
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-dark" id="saveOrden">Guardar Orden</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Editar Orden -->
    <div class="modal fade" id="editOrdenModal" tabindex="-1" role="dialog" aria-labelledby="editOrdenModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" style="width: 60%;" role="document">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="editOrdenModalLabel">Editar Orden de Producción</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editOrdenForm">
                        <input type="hidden" id="editOrdenId" name="id">
                        
                        <div class="row">
                            <!-- Información General - Lado Izquierdo -->
                            <div class="col-md-6">
                                <h5 class="border-bottom pb-2 mb-3">Información General</h5>
                                
                                <div class="mb-3">
                                    <label for="editPoDetalle" class="form-label">PO Detalle*</label>
                                    <select class="form-select select2" id="editPoDetalle" name="op_id_pd" required disabled>
                                        <?php foreach ($poDetalles as $poDetalle): ?>
                                            <option value="<?php echo htmlspecialchars($poDetalle['id']); ?>">
                                                PO: <?php echo htmlspecialchars($poDetalle['po_numero']); ?> - Item: <?php echo htmlspecialchars($poDetalle['item_numero'] . '-' . $poDetalle['item_nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted">El PO Detalle no se puede cambiar después de crear la orden</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="editProceso" class="form-label">Proceso*</label>
                                    <select class="form-select select2" id="editProceso" name="op_id_proceso" required disabled>
                                        <?php foreach ($procesos as $proceso): ?>
                                            <option value="<?php echo htmlspecialchars($proceso['id']); ?>">
                                                <?php echo htmlspecialchars($proceso['pp_nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted">El proceso no se puede cambiar después de crear la orden</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="editOperador" class="form-label">Operador Asignado*</label>
                                    <select class="form-select select2" id="editOperador" name="op_operador_asignado" required>
                                        <option value="">Seleccione un operador</option>
                                        <?php foreach ($operadores as $usuario): ?>
                                            <option value="<?php echo htmlspecialchars($usuario['id']); ?>">
                                                <?php echo htmlspecialchars($usuario['usuario_nombre'] . ' ' . $usuario['usuario_apellido']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted">Persona responsable de ejecutar el proceso</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="editEstado" class="form-label">Estado*</label>
                                    <div>
                                        <select class="form-select d-none" id="editEstadoSelect" name="op_estado" required>
                                            <option value="Pendiente">Pendiente</option>
                                            <option value="En proceso">En proceso</option>
                                            <option value="Completado">Completado</option>
                                        </select>
                                        <div id="editEstadoBadge">
                                            <span class="badge bg-warning">Pendiente</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Información Adicional - Lado Derecho -->
                            <div class="col-md-6">
                                <h5 class="border-bottom pb-2 mb-3">Información Adicional</h5>
                                
                                <div class="mb-3">
                                    <label for="editCantidadAsignada" class="form-label">Cantidad Asignada*</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="editCantidadAsignada" name="op_cantidad_asignada" required min="1">
                                        <span class="input-group-text" id="editCantidadInfo">0/0</span>
                                    </div>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" id="editAsignarCompleto">
                                        <label class="form-check-label" for="editAsignarCompleto">
                                            Asignar cantidad completa
                                        </label>
                                    </div>
                                    <small class="text-muted d-block" id="editCantidadRestante">Pendiente por asignar: 0</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="editCantidadCompletada" class="form-label">Cantidad Completada</label>
                                    <input type="number" class="form-control" id="editCantidadCompletada" name="op_cantidad_completada" min="0">
                                    <small class="text-muted">Cantidad de piezas ya procesadas</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="editComentario" class="form-label">Comentario</label>
                                    <textarea class="form-control" id="editComentario" name="op_comentario" rows="3"></textarea>
                                    <small class="text-muted">Instrucciones especiales o notas para esta orden</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3 border-top pt-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="text-muted small">
                                        <i class="fas fa-calendar-alt"></i> Fecha de creación: <span id="editFechaCreacion">-</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-muted small">
                                        <i class="fas fa-edit"></i> Última modificación: <span id="editFechaModificacion">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-dark" id="updateOrden">Actualizar Orden</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Ver Detalles de Orden -->
    <div class="modal fade" id="ordenDetailModal" tabindex="-1" aria-labelledby="ordenDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog" style="max-width: 1000px;">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="ordenDetailModalLabel">Detalles de la Orden de Producción</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h5 class="border-bottom pb-2 mb-3">Información General</h5>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th style="width: 40%">PO:</th>
                                    <td id="detailPO"></td>
                                </tr>
                                <tr>
                                    <th style="width: 40%">Item:</th>
                                    <td id="detailItem"></td>
                                </tr>
                                <tr>
                                    <th>Proceso:</th>
                                    <td id="detailProceso"></td>
                                </tr>
                                <tr>
                                    <th>Operador:</th>
                                    <td id="detailOperador"></td>
                                </tr>
                                <tr>
                                    <th>Módulo:</th>
                                    <td id="detailModulo"></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th style="width: 40%">Estado:</th>
                                    <td id="detailEstadoContainer"></td>
                                </tr>
                                <tr>
                                    <th>Aprobada por:</th>
                                    <td id="detailAprobadoPor"></td>
                                </tr>
                                <tr>
                                    <th>Fecha Aprobación:</th>
                                    <td id="detailFechaAprobacion"></td>
                                </tr>
                                <tr>
                                    <th>Comentario:</th>
                                    <td id="detailComentario"></td>
                                </tr>
                                <tr id="detailMotivoRechazoRow" style="display: none;">
                                    <th>Motivo de Rechazo:</th>
                                    <td id="detailMotivoRechazo"></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <h5 class="border-bottom pb-2 mb-3">Cantidades y Fechas</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th style="width: 50%">Cantidad Asignada:</th>
                                    <td id="detailCantidadAsignada"></td>
                                </tr>
                                <tr>
                                    <th>Cantidad Completada:</th>
                                    <td id="detailCantidadCompletada"></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th style="width: 40%">Fecha Inicio:</th>
                                    <td id="detailFechaInicio"></td>
                                </tr>
                                <tr>
                                    <th>Fecha Fin:</th>
                                    <td id="detailFechaFin"></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Eliminar Orden -->
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
    
    <!-- Modal para Gestionar Aprobación -->
    <div class="modal fade" id="aprobacionOrdenModal" tabindex="-1" aria-labelledby="aprobacionOrdenModalLabel" aria-hidden="true">
        <div class="modal-dialog" style="width: 700px;">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="aprobacionOrdenModalLabel">Gestionar Aprobación de Orden</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="aprobacionOrdenId">
                    
                    <div class="mb-3">
                        <h6>¿Qué acción desea realizar con esta orden de producción?</h6>
                        
                        <div class="mt-4">
                            <button type="button" class="btn btn-success w-100 mb-3" id="confirmarAprobacion">
                                <i class="fas fa-check-circle me-2"></i> Aprobar Orden
                            </button>
                            
                            <div class="text-center my-2">- o -</div>
                            
                            <div class="card mt-2">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Rechazar Orden</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="motivoRechazo" class="form-label">Motivo del rechazo:</label>
                                        <textarea class="form-control" id="motivoRechazo" rows="3" placeholder="Indique el motivo por el cual rechaza esta orden..."></textarea>
                                    </div>
                                    <button type="button" class="btn btn-danger w-100" id="confirmarRechazo">
                                        <i class="fas fa-times-circle me-2"></i> Rechazar Orden
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
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
