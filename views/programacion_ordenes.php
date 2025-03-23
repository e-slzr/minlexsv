<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once '../controllers/OrdenProduccionController.php';
require_once '../controllers/ModuloController.php';
require_once '../controllers/ProcesoController.php';

$ordenesController = new OrdenProduccionController();
$moduloController = new ModuloController();
$procesoController = new ProcesoController();

$modulos = $moduloController->getModulos(['modulo_estado' => 'Activo']);
$procesos = $procesoController->getProcesos();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MINLEX | Programación de Órdenes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/style_main.css">
</head>
<body>
    <?php include '../components/menu_lateral.php'; ?>
    
    <main>
        <div class="titulo-vista">
            <h1><strong>Programación de Órdenes de Producción</strong></h1>
        </div>

        <div class="container-fluid">
            <!-- Filtros de búsqueda -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-filter"></i> Filtros
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="filtro-modulo" class="form-label">Módulo</label>
                            <select class="form-select" id="filtro-modulo">
                                <option value="">Todos los módulos</option>
                                <?php foreach ($modulos as $modulo): ?>
                                    <option value="<?php echo $modulo['id']; ?>">
                                        <?php echo htmlspecialchars($modulo['modulo_codigo'] . ' - ' . $modulo['modulo_tipo']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="filtro-proceso" class="form-label">Proceso</label>
                            <select class="form-select" id="filtro-proceso">
                                <option value="">Todos los procesos</option>
                                <?php foreach ($procesos as $proceso): ?>
                                    <option value="<?php echo $proceso['id']; ?>">
                                        <?php echo htmlspecialchars($proceso['pp_nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filtro-estado" class="form-label">Estado</label>
                            <select class="form-select" id="filtro-estado">
                                <option value="">Todos los estados</option>
                                <option value="Pendiente">Pendiente</option>
                                <option value="En Proceso">En Proceso</option>
                                <option value="Completado">Completado</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label d-block">&nbsp;</label>
                            <button class="btn btn-secondary w-100" id="limpiar-filtros">
                                <i class="fas fa-eraser"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de órdenes -->
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="tabla-ordenes">
                    <thead>
                        <tr>
                            <th>PO</th>
                            <th>Item</th>
                            <th>Proceso</th>
                            <th>Módulo Asignado</th>
                            <th>Cantidad</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Se llenará dinámicamente -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal para asignar/editar orden -->
        <div class="modal fade" id="modalOrden" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Asignar Orden de Producción</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formOrden">
                            <input type="hidden" id="orden-id">
                            <div class="mb-3">
                                <label for="orden-modulo" class="form-label">Módulo</label>
                                <select class="form-select" id="orden-modulo" required>
                                    <option value="">Seleccione un módulo</option>
                                    <?php foreach ($modulos as $modulo): ?>
                                        <option value="<?php echo $modulo['id']; ?>">
                                            <?php echo htmlspecialchars($modulo['modulo_codigo'] . ' - ' . $modulo['modulo_tipo']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="orden-proceso" class="form-label">Proceso</label>
                                <select class="form-select" id="orden-proceso" required>
                                    <option value="">Seleccione un proceso</option>
                                    <?php foreach ($procesos as $proceso): ?>
                                        <option value="<?php echo $proceso['id']; ?>">
                                            <?php echo htmlspecialchars($proceso['pp_nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="orden-cantidad" class="form-label">Cantidad</label>
                                <input type="number" class="form-control" id="orden-cantidad" required>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="btn-guardar-orden">Guardar</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/programacion_ordenes.js"></script>
</body>
</html> 