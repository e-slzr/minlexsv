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

// Inicializar controladores
$ordenProduccionController = new OrdenProduccionController();
$usuarioController = new UsuarioController();
$procesoController = new ProcesoController();
$poDetalleController = new PODetalleController();

// Obtener datos necesarios
$operadores = $usuarioController->getUsuarios() ?? [];
$procesos = $procesoController->getProcesos() ?? [];
$poDetalles = $poDetalleController->getAllPODetalles() ?? [];

// Obtener parámetros de filtro de la URL
$itemNumero = isset($_GET['item_numero']) ? $_GET['item_numero'] : '';
$operador = isset($_GET['operador']) ? $_GET['operador'] : '';
$estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$fechaInicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$fechaFin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';

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
                        <div class="col-md-3 mb-2">
                            <label for="item_numero" class="form-label">Número de Item</label>
                            <input type="text" class="form-control filtro" id="item_numero" name="item_numero" value="<?php echo htmlspecialchars($itemNumero); ?>" placeholder="Buscar por número...">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label for="operador" class="form-label">Operador</label>
                            <input type="text" class="form-control filtro" id="operador" name="operador" value="<?php echo htmlspecialchars($operador); ?>" placeholder="Buscar por nombre...">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label for="estado" class="form-label">Estado</label>
                            <select class="form-select filtro" id="estado" name="estado">
                                <option value="">Todos</option>
                                <option value="Pendiente" <?php echo $estado === 'Pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                <option value="En proceso" <?php echo $estado === 'En proceso' ? 'selected' : ''; ?>>En proceso</option>
                                <option value="Completado" <?php echo $estado === 'Completado' ? 'selected' : ''; ?>>Completado</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                            <input type="date" class="form-control filtro" id="fecha_inicio" name="fecha_inicio" value="<?php echo htmlspecialchars($fechaInicio); ?>">
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-12">
                            <button class="btn btn-secondary" id="limpiar-filtros">
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
                            <th class="sortable" data-column="id"># <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-column="item">Item <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-column="proceso">Proceso <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-column="operador">Operador <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-column="cantidad">Cantidad <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-column="completado">Completado <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-column="fecha_inicio">Fecha Inicio <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-column="fecha_fin">Fecha Fin <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-column="estado">Estado <i class="fas fa-sort"></i></th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $filters = [
                            'item_numero' => $itemNumero,
                            'operador' => $operador,
                            'estado' => $estado,
                            'fecha_inicio' => $fechaInicio,
                            'fecha_fin' => $fechaFin
                        ];

                        $ordenes = $ordenProduccionController->searchOrdenes($filters);

                        if (empty($ordenes)) {
                            echo '<tr><td colspan="10" class="text-center">No se encontraron órdenes de producción</td></tr>';
                        } else {
                            foreach ($ordenes as $orden) {
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($orden['id']) . '</td>';
                                echo '<td>' . htmlspecialchars($orden['item_numero'] . ' - ' . $orden['item_nombre']) . '</td>';
                                echo '<td>' . htmlspecialchars($orden['pp_nombre']) . '</td>';
                                echo '<td>' . htmlspecialchars($orden['usuario_nombre'] . ' ' . $orden['usuario_apellido']) . '</td>';
                                echo '<td>' . htmlspecialchars($orden['op_cantidad_asignada']) . '</td>';
                                echo '<td>' . htmlspecialchars($orden['op_cantidad_completada']) . '</td>';
                                echo '<td>' . htmlspecialchars(date('d/m/Y', strtotime($orden['op_fecha_inicio']))) . '</td>';
                                echo '<td>' . ($orden['op_fecha_fin'] ? htmlspecialchars(date('d/m/Y', strtotime($orden['op_fecha_fin']))) : 'No definida') . '</td>';
                                echo '<td>';
                                $badgeClass = '';
                                switch ($orden['op_estado']) {
                                    case 'Pendiente':
                                        $badgeClass = 'bg-warning';
                                        break;
                                    case 'En proceso':
                                        $badgeClass = 'bg-primary';
                                        break;
                                    case 'Completado':
                                        $badgeClass = 'bg-success';
                                        break;
                                }
                                echo '<span class="badge ' . $badgeClass . '">' . htmlspecialchars($orden['op_estado']) . '</span>';
                                echo '</td>';
                                echo '<td>';
                                echo '<button type="button" class="btn btn-light view-orden me-1" data-id="' . $orden['id'] . '" data-bs-toggle="modal" data-bs-target="#ordenDetailModal">';
                                echo '<svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">';
                                echo '<path d="M2 12C2 12 5.63636 5 12 5C18.3636 5 22 12 22 12C22 12 18.3636 19 12 19C5.63636 19 2 12 2 12Z" stroke="black" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>';
                                echo '<path d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z" stroke="black" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>';
                                echo '</svg>';
                                echo '</button>';
                                echo '<button type="button" class="btn btn-light edit-orden me-1" data-id="' . $orden['id'] . '" data-bs-toggle="modal" data-bs-target="#ordenModal">';
                                echo '<svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">';
                                echo '<path d="M14 6L16.2929 3.70711C16.6834 3.31658 17.3166 3.31658 17.7071 3.70711L20.2929 6.29289C20.6834 6.68342 20.6834 7.31658 20.2929 7.70711L18 10M14 6L4.29289 15.7071C4.10536 15.8946 4 16.149 4 16.4142V19C4 19.5523 4.44772 20 5 20H7.58579C7.851 20 8.10536 19.8946 8.29289 19.7071L18 10M14 6L18 10" stroke="black" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>';
                                echo '</svg>';
                                echo '</button>';
                                echo '<button type="button" class="btn btn-light delete-orden" data-id="' . $orden['id'] . '" data-bs-toggle="modal" data-bs-target="#deleteOrdenModal">';
                                echo '<svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">';
                                echo '<path d="M19 7L18.1327 19.1425C18.0579 20.1891 17.187 21 16.1378 21H7.86224C6.81296 21 5.94208 20.1891 5.86732 19.1425L5 7M10 11V17M14 11V17M15 7V4C15 3.44772 14.5523 3 14 3H10C9.44772 3 9 3.44772 9 4V7M4 7H20" stroke="#FF0000" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>';
                                echo '</svg>';
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

    <!-- Modal para Crear/Editar Orden -->
    <div class="modal fade" id="ordenModal" tabindex="-1" role="dialog" aria-labelledby="ordenModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ordenModalLabel">Nueva Orden de Producción</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="ordenForm">
                        <input type="hidden" id="ordenId" name="id">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="poDetalle" class="form-label">PO Detalle*</label>
                                <select class="form-select" id="poDetalle" name="op_id_pd" required>
                                    <option value="">Seleccione un detalle de PO</option>
                                    <?php foreach ($poDetalles as $detalle): ?>
                                        <option value="<?php echo $detalle['id']; ?>">
                                            PO: <?php echo $detalle['po_numero']; ?> - Item: <?php echo $detalle['item_numero']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="proceso" class="form-label">Proceso*</label>
                                <select class="form-select" id="proceso" name="op_id_proceso" required>
                                    <option value="">Seleccione un proceso</option>
                                    <?php foreach ($procesos as $proceso): ?>
                                        <option value="<?php echo $proceso['id']; ?>">
                                            <?php echo htmlspecialchars($proceso['pp_nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="operadorAsignado" class="form-label">Operador Asignado*</label>
                                <select class="form-select" id="operadorAsignado" name="op_operador_asignado" required>
                                    <option value="">Seleccione un operador</option>
                                    <?php foreach ($operadores as $operador): ?>
                                        <option value="<?php echo $operador['id']; ?>">
                                            <?php echo htmlspecialchars($operador['usuario_nombre'] . ' ' . $operador['usuario_apellido']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="estado" class="form-label">Estado*</label>
                                <select class="form-select" id="ordenEstado" name="op_estado" required>
                                    <option value="Pendiente">Pendiente</option>
                                    <option value="En proceso">En proceso</option>
                                    <option value="Completado">Completado</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="cantidadAsignada" class="form-label">Cantidad Asignada*</label>
                                <input type="number" class="form-control" id="cantidadAsignada" name="op_cantidad_asignada" required min="1">
                            </div>
                            <div class="col-md-6">
                                <label for="cantidadCompletada" class="form-label">Cantidad Completada</label>
                                <input type="number" class="form-control" id="cantidadCompletada" name="op_cantidad_completada" min="0">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="fechaInicio" class="form-label">Fecha Inicio*</label>
                                <input type="date" class="form-control" id="fechaInicio" name="op_fecha_inicio" required>
                            </div>
                            <div class="col-md-6">
                                <label for="fechaFin" class="form-label">Fecha Fin</label>
                                <input type="date" class="form-control" id="fechaFin" name="op_fecha_fin">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="comentario" class="form-label">Comentario</label>
                                <textarea class="form-control" id="comentario" name="op_comentario" rows="3"></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="saveOrden">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Ver Detalles de Orden -->
    <div class="modal fade" id="ordenDetailModal" tabindex="-1" aria-labelledby="ordenDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ordenDetailModalLabel">Detalles de la Orden de Producción</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Información General</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th>Item:</th>
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
                                    <th>Estado:</th>
                                    <td id="detailEstado"></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Cantidades y Fechas</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th>Cantidad Asignada:</th>
                                    <td id="detailCantidadAsignada"></td>
                                </tr>
                                <tr>
                                    <th>Cantidad Completada:</th>
                                    <td id="detailCantidadCompletada"></td>
                                </tr>
                                <tr>
                                    <th>Fecha Inicio:</th>
                                    <td id="detailFechaInicio"></td>
                                </tr>
                                <tr>
                                    <th>Fecha Fin:</th>
                                    <td id="detailFechaFin"></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <h6>Comentario</h6>
                            <p id="detailComentario"></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Eliminar Orden -->
    <div class="modal fade" id="deleteOrdenModal" tabindex="-1" aria-labelledby="deleteOrdenModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteOrdenModalLabel">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro que desea eliminar esta orden de producción?</p>
                    <p>Esta acción no se puede deshacer.</p>
                    <form id="deleteOrdenForm">
                        <input type="hidden" id="deleteOrdenId" name="id">
                        <div class="mb-3">
                            <label for="deletePassword" class="form-label">Ingrese su contraseña para confirmar:</label>
                            <input type="password" class="form-control" id="deletePassword" name="password" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Eliminar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/ordenes_produccion.js"></script>
</body>
</html>
