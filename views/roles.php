<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

require_once '../controllers/RolController.php';
$rolController = new RolController();
$roles = $rolController->getRoles() ?? [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MINLEX | Roles</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/style_main.css">
    <style>
        .color-activo {
            color: green;
            font-weight: bold;
        }
        .color-inactivo {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php include '../components/menu_lateral.php'; ?>
    
    <main>
        <div class="titulo-vista">
            <h1><strong>Gestión de Roles</strong></h1>
            <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#nuevoRolModal">
                <i class="fas fa-plus"></i> Nuevo Rol
            </button>
        </div>

        <div class="container-fluid">
            <!-- Filtros de búsqueda -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-filter"></i> Filtros
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label for="filtro-nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control filtro" id="filtro-nombre" placeholder="Buscar por nombre...">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="filtro-estado" class="form-label">Estado</label>
                            <select class="form-select filtro" id="filtro-estado">
                                <option value="">Todos</option>
                                <option value="Activo">Activo</option>
                                <option value="Inactivo">Inactivo</option>
                            </select>
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

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th class="sortable" data-column="id">ID <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-column="nombre">Nombre <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-column="descripcion">Descripción <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-column="estado">Estado <i class="fas fa-sort"></i></th>
                            <th>Opciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($roles as $rol): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($rol['id']); ?></td>
                                <td><?php echo htmlspecialchars($rol['rol_nombre']); ?></td>
                                <td><?php echo htmlspecialchars($rol['rol_descripcion']); ?></td>
                                <td>
                                    <span class="badge <?php echo $rol['estado'] === 'Activo' ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo htmlspecialchars($rol['estado']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-light edit-rol" 
                                            data-id="<?php echo $rol['id']; ?>"
                                            data-nombre="<?php echo htmlspecialchars($rol['rol_nombre']); ?>"
                                            data-descripcion="<?php echo htmlspecialchars($rol['rol_descripcion']); ?>"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editarRolModal">
                                        <svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M14 6L16.2929 3.70711C16.6834 3.31658 17.3166 3.31658 17.7071 3.70711L20.2929 6.29289C20.6834 6.68342 20.6834 7.31658 20.2929 7.70711L18 10M14 6L4.29289 15.7071C4.10536 15.8946 4 16.149 4 16.4142V19C4 19.5523 4.44772 20 5 20H7.58579C7.851 20 8.10536 19.8946 8.29289 19.7071L18 10M14 6L18 10" 
                                                  stroke="black" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                                        </svg>
                                    </button>
                                    <button class="btn btn-light toggle-status"
                                            data-id="<?php echo $rol['id']; ?>"
                                            data-estado="<?php echo $rol['estado'] === 'Activo' ? 'Inactivo' : 'Activo'; ?>"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#confirmStatusModal">
                                        <svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M12 4V20M4 12H20" stroke="black" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Nuevo Rol -->
        <div class="modal fade" id="nuevoRolModal" tabindex="-1" aria-labelledby="nuevoRolModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="nuevoRolModalLabel">Nuevo Rol</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="nuevoRolForm">
                            <input type="hidden" name="action" value="create">

                            <div class="mb-3">
                                <label for="nuevo_nombre" class="form-label">Nombre* <small>(máx. 50 caracteres)</small></label>
                                <input type="text" class="form-control" id="nuevo_nombre" name="nombre" maxlength="50" required>
                            </div>

                            <div class="mb-3">
                                <label for="nuevo_descripcion" class="form-label">Descripción* <small>(máx. 255 caracteres)</small></label>
                                <textarea class="form-control" id="nuevo_descripcion" name="descripcion" maxlength="255" rows="3" required></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-dark" id="guardarNuevoRol">Guardar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Editar Rol -->
        <div class="modal fade" id="editarRolModal" tabindex="-1" aria-labelledby="editarRolModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editarRolModalLabel">Editar Rol</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editarRolForm">
                            <input type="hidden" id="editar_id" name="id">
                            <input type="hidden" name="action" value="update">

                            <div class="mb-3">
                                <label for="editar_nombre" class="form-label">Nombre* <small>(máx. 50 caracteres)</small></label>
                                <input type="text" class="form-control" id="editar_nombre" name="nombre" maxlength="50" required>
                            </div>

                            <div class="mb-3">
                                <label for="editar_descripcion" class="form-label">Descripción* <small>(máx. 255 caracteres)</small></label>
                                <textarea class="form-control" id="editar_descripcion" name="descripcion" maxlength="255" rows="3" required></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-dark" id="guardarEditarRol">Guardar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Confirmación de Estado -->
        <div class="modal fade" id="confirmStatusModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmar Cambio de Estado</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>¿Está seguro que desea cambiar el estado de este rol?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-warning" id="confirmStatusBtn">Confirmar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Éxito -->
        <div class="modal fade" id="successModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">¡Éxito!</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p id="successMessage"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Error -->
        <div class="modal fade" id="errorModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Error</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p id="errorMessage"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../components/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/roles.js"></script>
</body>
</html>