<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Verificar si el usuario tiene un módulo asignado
if (empty($_SESSION['user']['usuario_modulo_id'])) {
    die("Error: No tienes un módulo asignado. Contacta al administrador.");
}

require_once '../controllers/ProduccionAvanceController.php';
require_once '../controllers/OrdenProduccionController.php';
require_once '../controllers/ProcesoController.php';
require_once '../controllers/PoController.php';
require_once '../controllers/ModuloController.php';

// Instancias de controladores
$produccionController = new ProduccionAvanceController();
$ordenesController = new OrdenProduccionController();
$procesosController = new ProcesoController();
$poController = new PoController();
$moduloController = new ModuloController();

// Obtener información del módulo
$moduloInfo = $moduloController->getModulos(['id' => $_SESSION['user']['usuario_modulo_id']])[0] ?? null;
if (!$moduloInfo || $moduloInfo['modulo_estado'] !== 'Activo') {
    die("Error: El módulo asignado no está activo o no existe.");
}

// Obtener procesos de producción para el filtro
$procesos = $procesosController->getProcesos();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Ingreso de Producción</title>
    <?php include '../components/meta.php'; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="../css/produccion_tablet.css">
</head>
<body class="tablet-view">
    <header class="tablet-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-8">
                    <h1>Ingreso de Producción</h1>
                    <div class="modulo-info">
                        <span class="badge bg-primary" data-modulo-id="<?php echo htmlspecialchars($moduloInfo['id']); ?>">
                            Módulo: <?php echo htmlspecialchars($moduloInfo['modulo_codigo'] . ' - ' . $moduloInfo['modulo_tipo']); ?>
                        </span>
                    </div>
                </div>
                <div class="col-4 text-end d-flex align-items-center justify-content-end">
                    <div class="header-info me-2">
                        <span class="usuario-info"><?php echo htmlspecialchars($_SESSION['user']['nombre_completo']); ?></span>
                        <span id="fecha-actual" class="badge bg-secondary"></span>
                        <span id="hora-actual" class="badge bg-secondary"></span>
                    </div>
                    <button id="btn-fullscreen" class="btn btn-sm btn-outline-secondary" title="Pantalla Completa">
                        <i class="fas fa-expand"></i>
                    </button>
                </div>
            </div>
        </div>
    </header>
    
    <main class="container-fluid mt-2">
        <!-- Agregar la sección de búsqueda antes del row principal -->
        <div class="card mb-2">
            <div class="card-body p-2">
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label for="selector-po" class="form-label fw-bold">Seleccionar Orden:</label>
                        <select class="form-select" id="selector-po" data-live-search="true" data-size="10">
                            <option value="">Seleccione una orden</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label for="selector-proceso" class="form-label fw-bold">Proceso:</label>
                        <select class="form-select" id="selector-proceso" data-live-search="true" data-size="10">
                            <option value="">Seleccione un proceso</option>
                            <?php foreach ($procesos as $proceso): ?>
                                <option value="<?php echo $proceso['id']; ?>" data-tokens="<?php echo htmlspecialchars($proceso['pp_nombre']); ?>">
                                    <?php echo htmlspecialchars($proceso['pp_nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Columna izquierda: Información -->
            <div class="col-md-4">
                <!-- Información del trabajo actual -->
                <div id="info-trabajo" class="card mb-2">
                    <div class="card-header bg-secondary text-white py-1">
                        <h6 class="m-0">Información del Trabajo</h6>
                    </div>
                    <div class="card-body p-2">
                        <p class="mb-1"><strong>PO:</strong> <span id="po-numero"></span></p>
                        <p class="mb-1"><strong>Item:</strong> <span id="item-descripcion"></span></p>
                        <p class="mb-1"><strong>Talla:</strong> <span id="item-talla"></span></p>
                        <p class="mb-1"><strong>Color:</strong> <span id="item-color"></span></p>
                        <p class="mb-1"><strong>Diseño:</strong> <span id="item-diseno"></span></p>
                        <p class="mb-1"><strong>Ubicación:</strong> <span id="item-ubicacion"></span></p>
                    </div>
                </div>

                <!-- Contadores -->
                <div id="contadores" class="row mb-2">
                    <div class="col-4">
                        <div class="counter-card">
                            <h6 class="small mb-0">Total</h6>
                            <div class="counter-value" id="total-piezas">0</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="counter-card">
                            <h6 class="small mb-0">Pendientes</h6>
                            <div class="counter-value" id="pendientes">0</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="counter-card">
                            <h6 class="small mb-0">Producidas</h6>
                            <div class="counter-value" id="producidas">0</div>
                        </div>
                    </div>
                </div>

                <!-- Registro de calidades -->
                <div id="panel-calidades" class="card">
                    <div class="card-header bg-secondary text-white py-1">
                        <h6 class="m-0">Calidades</h6>
                    </div>
                    <div class="card-body p-2">
                        <div class="row">
                            <div class="col-6">
                                <button class="btn btn-sm btn-success w-100 calidad-btn" data-type="primeras">
                                    Primeras <span id="count-primeras" class="badge bg-white text-success">0</span>
                                </button>
                            </div>
                            <div class="col-6">
                                <button class="btn btn-sm btn-warning w-100 calidad-btn" data-type="irregulares">
                                    Irreg. <span id="count-irregulares" class="badge bg-white text-warning">0</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna derecha: Teclado y entrada -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-success text-white py-2">
                        <h5 class="card-title mb-0">Ingreso de Producción</h5>
                    </div>
                    <div class="card-body">
                        <!-- Display de entrada -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <input type="text" id="cantidad-input" class="form-control form-control-lg text-end" readonly>
                            </div>
                        </div>
                        
                        <!-- Teclado numérico con grid -->
                        <div class="row g-2">
                            <div><button class="btn btn-lg btn-outline-dark w-100" data-num="1">1</button></div>
                            <div><button class="btn btn-lg btn-outline-dark w-100" data-num="2">2</button></div>
                            <div><button class="btn btn-lg btn-outline-dark w-100" data-num="3">3</button></div>
                            <div><button class="btn btn-lg btn-outline-dark w-100" data-num="4">4</button></div>
                            <div><button class="btn btn-lg btn-outline-dark w-100" data-num="5">5</button></div>
                            <div><button class="btn btn-lg btn-outline-dark w-100" data-num="6">6</button></div>
                            <div><button class="btn btn-lg btn-outline-dark w-100" data-num="7">7</button></div>
                            <div><button class="btn btn-lg btn-outline-dark w-100" data-num="8">8</button></div>
                            <div><button class="btn btn-lg btn-outline-dark w-100" data-num="9">9</button></div>
                            <div><button class="btn btn-lg btn-outline-dark w-100" data-num="0">0</button></div>
                            <div><button class="btn btn-lg btn-danger w-100" id="btn-borrar">⌫</button></div>
                            <div><button class="btn btn-lg btn-success w-100" id="btn-registrar">✓</button></div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="row g-2 mt-3"></div>
                            <div class="col-6">
                                <button id="btn-finalizar-talla" class="btn btn-primary w-100">Finalizar Talla</button>
                            </div>
                            <div class="col-6">
                                <button id="btn-po-espera" class="btn btn-secondary w-100">PO en Espera</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal de registro de calidad -->
    <div class="modal fade" id="modalCalidad" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCalidadTitle">Registrar Calidad</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="tipo-calidad">
                    <div class="mb-3">
                        <label for="cantidad-calidad" class="form-label">Cantidad</label>
                        <input type="number" class="form-control form-control-lg" id="cantidad-calidad">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btn-guardar-calidad">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación -->
    <div class="modal fade" id="modalConfirmacion" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalConfirmacionTitle">Confirmación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modalConfirmacionBody">
                    ¿Está seguro que desea realizar esta acción?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btn-confirmar-accion">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    <?php include '../components/scripts.php'; ?>
    <script src="../js/produccion_tablet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>
</body>
</html>
