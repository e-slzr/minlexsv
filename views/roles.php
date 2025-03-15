<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
require_once '../controllers/RolController.php';
$rolController = new RolController();
$roles = $rolController->getRoles();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MINLEX | Roles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style_main.css">
    <style>
        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 150vw !important;
            height: 150vh !important;
            background-color: rgba(0, 0, 0, 0.5) !important;
            z-index: 1040;
        }
        .modal {
            z-index: 1050;
        }
        body {
            zoom: 0.8;
        }
    </style>
</head>
<body>
    <?php include '../components/menu_lateral.php'; ?>
    
    <main>
        <div style="width: 100%;" class="border-bottom border-secondary titulo-vista">
            <h1><strong>Gestión de Roles</strong></h1><br>
            <button type="button" class="btn btn-dark" data-toggle="modal" data-target="#roleModal">
                Nuevo Rol
            </button>
        </div>

        <!-- Filtros -->
        <div class="filtrar">
            <input type="text" id="searchInput" class="form-control" placeholder="Buscar rol...">
        </div>

        <!-- Tabla de Roles -->
        <div class="table table-responsive">
            <table class="table table-striped table-hover" style="width: 100%;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Estado</th>
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
                            <span class="<?php echo $rol['estado'] === 'Activo' ? 'color-activo' : 'color-inactivo'; ?>">
                                <?php echo htmlspecialchars($rol['estado']); ?>
                            </span>
                        </td>
                        <td>
                            <button type="button" class="btn btn-dark edit-role" 
                                    data-id="<?php echo $rol['id']; ?>"
                                    data-nombre="<?php echo htmlspecialchars($rol['rol_nombre']); ?>"
                                    data-descripcion="<?php echo htmlspecialchars($rol['rol_descripcion']); ?>"
                                    data-estado="<?php echo htmlspecialchars($rol['estado']); ?>"
                                    data-toggle="modal" data-target="#roleModal">
                                <svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M14 6L16.2929 3.70711C16.6834 3.31658 17.3166 3.31658 17.7071 3.70711L20.2929 6.29289C20.6834 6.68342 20.6834 7.31658 20.2929 7.70711L18 10M14 6L4.29289 15.7071C4.10536 15.8946 4 16.149 4 16.4142V19C4 19.5523 4.44772 20 5 20H7.58579C7.851 20 8.10536 19.8946 8.29289 19.7071L18 10M14 6L18 10" stroke="white" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                                </svg>
                            </button>
                            <button type="button" class="btn btn-warning toggle-status" 
                                    data-id="<?php echo $rol['id']; ?>"
                                    data-nombre="<?php echo htmlspecialchars($rol['rol_nombre']); ?>"
                                    data-estado="<?php echo htmlspecialchars($rol['estado']); ?>">
                                <?php if ($rol['estado'] === 'Activo'): ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-person-slash" viewBox="0 0 16 16">
                                        <path d="M13.879 10.414a2.501 2.501 0 0 0-3.465 3.465l3.465-3.465Zm.707.707-3.465 3.465a2.501 2.501 0 0 0 3.465-3.465Zm-4.56-1.096a3.5 3.5 0 1 1 4.949 4.95 3.5 3.5 0 0 1-4.95-4.95ZM11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm-9 8c0 1 1 1 1 1h5.256A4.493 4.493 0 0 1 8 12.5a4.49 4.49 0 0 1 1.544-3.393C9.077 9.038 8.564 9 8 9c-5 0-6 3-6 4Z"/>
                                    </svg>
                                <?php else: ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-person-check" viewBox="0 0 16 16">
                                        <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm1.679-4.493-1.335 2.226a.75.75 0 0 1-1.174.144l-.774-.773a.5.5 0 0 1 .708-.708l.547.547 1.17-1.951a.5.5 0 1 1 .858.514ZM11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"/>
                                        <path d="M8.256 14a4.474 4.474 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10c.26 0 .507.009.74.025.226-.341.496-.65.804-.918C9.077 9.038 8.564 9 8 9c-5 0-6 3-6 4s1 1 1 1h5.256Z"/>
                                    </svg>
                                <?php endif; ?>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Modal para Crear/Editar Rol -->
        <div class="modal fade" id="roleModal" tabindex="-1" role="dialog" aria-labelledby="roleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="roleModalLabel">Nuevo Rol</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="roleForm">
                        <div class="modal-body">
                            <input type="hidden" name="id" id="roleId">
                            <input type="hidden" name="action" id="formAction" value="create">
                            
                            <div class="form-group mb-3">
                                <label for="nombre">Nombre</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="descripcion">Descripción</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required></textarea>
                            </div>

                            <div class="form-group mb-3" id="estadoGroup">
                                <label for="estado">Estado</label>
                                <select class="form-control" id="estado" name="estado">
                                    <option value="Activo">Activo</option>
                                    <option value="Inactivo">Inactivo</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-dark">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal para Cambiar Estado -->
        <div class="modal fade" id="toggleStatusModal" tabindex="-1" role="dialog" aria-labelledby="toggleStatusModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="toggleStatusModalLabel">Confirmar Cambio de Estado</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>¿Está seguro que desea <span id="actionType"></span> el rol <strong><span id="roleName"></span></strong>?</p>
                        <div class="alert alert-warning">
                            <strong>Nota:</strong> Si deshabilita el rol, este no estará disponible para asignar a nuevos usuarios.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-dark" id="confirmToggleStatus">Confirmar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Confirmación -->
        <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmationModalLabel">Operación Exitosa</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-check-circle text-success" viewBox="0 0 16 16">
                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/>
                            </svg>
                            <p class="mt-3" id="confirmationMessage"></p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-dark" data-dismiss="modal">Aceptar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Error -->
        <div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="errorModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="errorModalLabel">Error</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-x-circle text-danger" viewBox="0 0 16 16">
                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                            </svg>
                            <p class="mt-3" id="errorMessage"></p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-dark" data-dismiss="modal">Aceptar</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            // Búsqueda en tiempo real
            $("#searchInput").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("table tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });

            // Manejo del cambio de estado
            $('.toggle-status').click(function() {
                var id = $(this).data('id');
                var nombre = $(this).data('nombre');
                var estadoActual = $(this).data('estado');
                var nuevoEstado = estadoActual === 'Activo' ? 'Inactivo' : 'Activo';
                
                $('#actionType').text(nuevoEstado);
                $('#roleName').text(nombre);
                $('#confirmToggleStatus').data('id', id);
                $('#toggleStatusModal').modal('show');
            });

            // Confirmar cambio de estado
            $('#confirmToggleStatus').click(function() {
                var id = $(this).data('id');
                
                $.ajax({
                    url: '../controllers/RolController.php',
                    type: 'POST',
                    data: {
                        action: 'toggleStatus',
                        id: id
                    },
                    dataType: 'json',
                    success: function(response) {
                        $('#toggleStatusModal').modal('hide');
                        if (response.success) {
                            $('#confirmationMessage').text(response.message);
                            $('#confirmationModal').modal('show');
                            $('#confirmationModal').on('hidden.bs.modal', function () {
                                location.reload();
                            });
                        } else {
                            $('#errorMessage').text(response.message);
                            $('#errorModal').modal('show');
                        }
                    },
                    error: function() {
                        $('#toggleStatusModal').modal('hide');
                        $('#errorMessage').text('Error al procesar la solicitud');
                        $('#errorModal').modal('show');
                    }
                });
            });

            // Mostrar/ocultar campo de estado según la acción
            $('[data-target="#roleModal"]').click(function() {
                if (!$(this).hasClass('edit-role')) {
                    $('#estadoGroup').hide();
                } else {
                    $('#estadoGroup').show();
                }
            });

            // Editar rol
            $('.edit-role').click(function() {
                $('#roleId').val($(this).data('id'));
                $('#nombre').val($(this).data('nombre'));
                $('#descripcion').val($(this).data('descripcion'));
                $('#estado').val($(this).data('estado'));
                $('#formAction').val('update');
                $('#roleModalLabel').text('Editar Rol');
                $('#estadoGroup').show();
            });

            // Resetear el modal al crear nuevo rol
            $('[data-target="#roleModal"]').click(function() {
                if (!$(this).hasClass('edit-role')) {
                    $('#roleForm')[0].reset();
                    $('#roleId').val('');
                    $('#formAction').val('create');
                    $('#roleModalLabel').text('Nuevo Rol');
                    $('#estadoGroup').hide();
                }
            });

            // Manejo del formulario con AJAX
            $('#roleForm').on('submit', function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: '../controllers/RolController.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        $('#roleModal').modal('hide');
                        if (response.success) {
                            $('#confirmationMessage').text(response.message);
                            $('#confirmationModal').modal('show');
                            $('#confirmationModal').on('hidden.bs.modal', function () {
                                location.reload();
                            });
                        } else {
                            $('#errorMessage').text(response.message);
                            $('#errorModal').modal('show');
                        }
                    },
                    error: function() {
                        $('#roleModal').modal('hide');
                        $('#errorMessage').text('Error al procesar la solicitud');
                        $('#errorModal').modal('show');
                    }
                });
            });

            // Cerrar modal de confirmación
            $('#confirmationModal, #errorModal').on('hidden.bs.modal', function () {
                if ($(this).attr('id') === 'confirmationModal') {
                    location.reload();
                }
            });
        });
    </script>
</body>
<?php include '../components/footer.php'; ?>
</html>