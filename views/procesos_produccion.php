<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

require_once '../controllers/ProcesoController.php';

// Inicializar controlador
$procesoController = new ProcesoController();

// Definir título de la página
$pageTitle = "Procesos de Producción";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MINLEX | <?php echo $pageTitle; ?></title>
    <!-- Usar los mismos CDNs que en items.php para consistencia -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/style_main.css">
    <!-- Agregar SweetAlert2 que es usado por el JavaScript -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        /* Estilos adicionales para el loader */
        #loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <?php include '../components/menu_lateral.php'; ?>

    <main>
        <div class="titulo-vista">
            <h1><strong><?php echo $pageTitle; ?></strong></h1>
            <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#procesoModal">
                <i class="fas fa-plus"></i> Nuevo Proceso
            </button>
        </div>

        <div class="container-fluid">
            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-filter"></i> Filtros
                </div>
                <div class="card-body">
                    <form id="filtro-form" class="row">
                        <div class="col-md-4 mb-3">
                            <label for="filtro-nombre" class="form-label">Nombre del Proceso</label>
                            <input type="text" class="form-control" id="filtro-nombre" name="nombre" placeholder="Buscar por nombre...">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="filtro-costo-min" class="form-label">Costo Mínimo</label>
                            <input type="number" step="0.01" class="form-control" id="filtro-costo-min" name="costo_min" placeholder="Costo mínimo...">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="filtro-costo-max" class="form-label">Costo Máximo</label>
                            <input type="number" step="0.01" class="form-control" id="filtro-costo-max" name="costo_max" placeholder="Costo máximo...">
                        </div>
                        <div class="col-12 text-end">
                            <button type="button" id="btn-limpiar-filtros" class="btn btn-secondary">
                                <i class="fas fa-eraser"></i> Limpiar
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabla de Procesos -->
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="tabla-procesos">
                    <thead>
                        <tr>
                            <th class="sortable" data-column="id"># <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-column="pp_nombre">Nombre <i class="fas fa-sort"></i></th>
                            <th>Descripción</th>
                            <th class="sortable" data-column="pp_costo">Costo <i class="fas fa-sort"></i></th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="procesos-body">
                        <!-- Los datos se cargarán mediante JavaScript -->
                    </tbody>
                </table>
                <div id="sin-resultados" class="alert alert-info text-center" style="display: none;">
                    No se encontraron procesos que coincidan con los criterios de búsqueda.
                </div>
            </div>

            <!-- Paginación -->
            <div class="row mt-3">
                <div class="col-md-6">
                    <div class="datatable-info">
                        Mostrando <span id="registros-mostrados">0</span> de <span id="registros-totales">0</span> procesos
                    </div>
                </div>
                <div class="col-md-6">
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-end" id="paginacion">
                            <!-- La paginación se generará con JavaScript -->
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </main>

    <!-- Loader para operaciones asíncronas -->
    <div id="loader" style="display: none;">
        <div class="spinner"></div>
    </div>

    <?php include '../components/footer.php'; ?>

    <!-- Modal para Crear/Editar Proceso -->
    <div class="modal fade" id="procesoModal" tabindex="-1" role="dialog" aria-labelledby="procesoModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="procesoModalLabel">Nuevo Proceso de Producción</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="procesoForm">
                        <input type="hidden" id="procesoId" name="id">
                        <div class="mb-3">
                            <label for="pp_nombre" class="form-label">Nombre del Proceso*</label>
                            <input type="text" class="form-control" id="pp_nombre" name="pp_nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="pp_descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="pp_descripcion" name="pp_descripcion" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="pp_costo" class="form-label">Costo*</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" min="0" class="form-control" id="pp_costo" name="pp_costo" required>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="saveProceso">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Eliminar Proceso -->
    <div class="modal fade" id="deleteProcesoModal" tabindex="-1" aria-labelledby="deleteProcesoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteProcesoModalLabel">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro de que desea eliminar este proceso?</p>
                    <p class="text-danger">Esta acción no se puede deshacer. Si el proceso está asociado a órdenes de producción, no podrá ser eliminado.</p>
                    <form id="deleteProcesoForm">
                        <input type="hidden" id="deleteProcesoId" name="id">
                        <div class="mb-3">
                            <label for="deletePassword" class="form-label">Ingrese su contraseña para confirmar</label>
                            <input type="password" class="form-control" id="deletePassword" name="password" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteProceso">Eliminar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/procesos_produccion.js"></script>
</body>
</html>
