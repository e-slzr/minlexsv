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
    <?php include '../components/meta.php'; ?>
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
        <div style="width: 100%;" class="border-bottom border-secondary titulo-vista">
            <h1><strong>Gestión de Roles</strong></h1><br>
            <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#nuevoRolModal">
                <i class="fas fa-plus"></i> Nuevo Rol
            </button>
        </div>

        <div class="container-fluid">
            <!-- Filtros de búsqueda -->
            <div class="filtrar">
                <input type="text" id="filtro-nombre" class="form-control" placeholder="Buscar rol...">
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
                                    <button class="btn btn-sm btn-primary editar-rol" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editarRolModal"
                                            data-id="<?php echo $rol['id']; ?>"
                                            data-nombre="<?php echo htmlspecialchars($rol['rol_nombre']); ?>"
                                            data-descripcion="<?php echo htmlspecialchars($rol['rol_descripcion']); ?>"
                                            title="Editar rol">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm <?php echo $rol['estado'] === 'Activo' ? 'btn-danger' : 'btn-success'; ?> toggle-estado"
                                            data-id="<?php echo $rol['id']; ?>"
                                            data-estado="<?php echo $rol['estado']; ?>"
                                            title="Cambiar estado">
                                        <i class="fas <?php echo $rol['estado'] === 'Activo' ? 'fa-times' : 'fa-check'; ?>"></i>
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
                    <form id="nuevoRolForm">
                        <div class="modal-body">
                            <input type="hidden" name="action" value="create">
                            
                            <div class="form-group mb-3">
                                <label for="nuevo_nombre">Nombre*</label>
                                <input type="text" class="form-control" id="nuevo_nombre" name="nombre" required>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="nuevo_descripcion">Descripción*</label>
                                <textarea class="form-control" id="nuevo_descripcion" name="descripcion" rows="3" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-dark" id="guardarNuevoRol">Guardar</button>
                        </div>
                    </form>
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
                    <form id="editarRolForm">
                        <div class="modal-body">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" id="editar_id" name="id">
                            
                            <div class="form-group mb-3">
                                <label for="editar_nombre">Nombre*</label>
                                <input type="text" class="form-control" id="editar_nombre" name="nombre" required>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="editar_descripcion">Descripción*</label>
                                <textarea class="form-control" id="editar_descripcion" name="descripcion" rows="3" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-dark" id="guardarEditarRol">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Confirmación de Cambio de Estado -->
        <div class="modal fade" id="confirmStatusModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmar Cambio de Estado</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p id="confirmStatusMessage"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-dark" id="confirmStatusChange">Confirmar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Confirmación -->
        <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmación</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p id="confirmationMessage"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Aceptar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Error -->
        <div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Error</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p id="errorMessage"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Aceptar</button>
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