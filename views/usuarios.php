<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
require_once '../controllers/UsuarioController.php';
$usuarioController = new UsuarioController();
$usuarios = $usuarioController->getUsuarios();

$departamentos = [
    'Corte',
    'Calidad',
    'Produccion',
    'Compras',
    'Almacen',
    'Costura',
    'Administracion'
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MINLEX | Usuarios</title>
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
    <?php include 'menu_lateral.php'; ?>
    
    <main>
        <div style="width: 100%;" class="border-bottom border-secondary titulo-vista">
            <h1><strong>Gestión de Usuarios</strong></h1><br>
            <button type="button" class="btn btn-dark" data-toggle="modal" data-target="#userModal">
                Nuevo Usuario
            </button>
        </div>

        <!-- Filtros -->
        <div class="filtrar">
            <input type="text" id="searchInput" class="form-control" placeholder="Buscar usuario...">
        </div>

        <!-- Tabla de Usuarios -->
        <div class="table table-responsive">
            <table class="table table-striped table-hover" style="width: 100%;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Alias</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Rol</th>
                        <th>Departamento</th>
                        <th>Opciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($usuario['id']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['usuario_alias']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['usuario_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['usuario_apellido']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['rol_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['usuario_departamento']); ?></td>
                        <td>
                            <button type="button" class="btn btn-success edit-user" 
                                    data-id="<?php echo $usuario['id']; ?>"
                                    data-alias="<?php echo htmlspecialchars($usuario['usuario_alias']); ?>"
                                    data-nombre="<?php echo htmlspecialchars($usuario['usuario_nombre']); ?>"
                                    data-apellido="<?php echo htmlspecialchars($usuario['usuario_apellido']); ?>"
                                    data-rol="<?php echo $usuario['usuario_rol_id']; ?>"
                                    data-departamento="<?php echo htmlspecialchars($usuario['usuario_departamento']); ?>"
                                    data-toggle="modal" data-target="#userModal">
                                <svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M14 6L16.2929 3.70711C16.6834 3.31658 17.3166 3.31658 17.7071 3.70711L20.2929 6.29289C20.6834 6.68342 20.6834 7.31658 20.2929 7.70711L18 10M14 6L4.29289 15.7071C4.10536 15.8946 4 16.149 4 16.4142V19C4 19.5523 4.44772 20 5 20H7.58579C7.851 20 8.10536 19.8946 8.29289 19.7071L18 10M14 6L18 10" stroke="white" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                                </svg>
                            </button>
                            <button type="button" class="btn btn-danger delete-user" 
                                    data-id="<?php echo $usuario['id']; ?>"
                                    data-nombre="<?php echo htmlspecialchars($usuario['usuario_nombre']); ?>"
                                    data-apellido="<?php echo htmlspecialchars($usuario['usuario_apellido']); ?>"
                                    data-toggle="modal" data-target="#deleteModal">
                                <svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M9 7V7C9 5.34315 10.3431 4 12 4V4C13.6569 4 15 5.34315 15 7V7M9 7H15M9 7H6M15 7H18M20 7H18M4 7H6M6 7V18C6 19.1046 6.89543 20 8 20H16C17.1046 20 18 19.1046 18 18V7" stroke="white" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                                </svg>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Modal para Crear/Editar Usuario -->
        <div class="modal fade" id="userModal" tabindex="-1" role="dialog" aria-labelledby="userModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="userModalLabel">Nuevo Usuario</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="userForm">
                        <div class="modal-body">
                            <input type="hidden" name="id" id="userId">
                            <input type="hidden" name="action" id="formAction" value="create">
                            
                            <div class="form-group mb-3">
                                <label for="alias">Alias</label>
                                <input type="text" class="form-control" id="alias" name="alias" required>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="nombre">Nombre</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="apellido">Apellido</label>
                                <input type="text" class="form-control" id="apellido" name="apellido" required>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="password">Contraseña</label>
                                <input type="password" class="form-control" id="password" name="password">
                                <small class="form-text text-muted">Dejar en blanco para mantener la contraseña actual (en caso de edición)</small>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="rol_id">Rol</label>
                                <select class="form-control" id="rol_id" name="rol_id" required>
                                    <option value="1">Administrador</option>
                                    <option value="2">Operador</option>
                                    <option value="3">Calidad</option>
                                </select>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="departamento">Departamento</label>
                                <select class="form-control" id="departamento" name="departamento" required>
                                    <option value="">Seleccione un departamento</option>
                                    <?php foreach ($departamentos as $depto): ?>
                                    <option value="<?php echo $depto; ?>"><?php echo $depto; ?></option>
                                    <?php endforeach; ?>
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

        <!-- Modal para Confirmar Eliminación -->
        <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>¿Está seguro que desea eliminar al usuario <span id="deleteUserName"></span>?</p>
                        <p class="text-danger">Esta acción no se puede deshacer.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-danger" id="confirmDelete">Eliminar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Respuesta -->
        <div class="modal fade" id="responseModal" tabindex="-1" role="dialog" aria-labelledby="responseModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="responseModalLabel">Mensaje del Sistema</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p id="responseMessage"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-dark" data-dismiss="modal" onclick="if(responseSuccess) window.location.reload();">Aceptar</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        let responseSuccess = false;

        function showResponseModal(message, success) {
            responseSuccess = success;
            $('#responseMessage').text(message);
            $('#responseModal').modal('show');
        }

        $(document).ready(function() {
            // Búsqueda en tiempo real
            $('#searchInput').on('keyup', function() {
                var value = $(this).val().toLowerCase();
                $('table tbody tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });

            // Editar usuario
            $('.edit-user').click(function() {
                $('#userModalLabel').text('Editar Usuario');
                $('#formAction').val('update');
                $('#userId').val($(this).data('id'));
                $('#alias').val($(this).data('alias'));
                $('#nombre').val($(this).data('nombre'));
                $('#apellido').val($(this).data('apellido'));
                $('#rol_id').val($(this).data('rol'));
                $('#departamento').val($(this).data('departamento'));
                $('#password').val('').prop('required', false);
            });

            // Nuevo usuario
            $('[data-target="#userModal"]').not('.edit-user').click(function() {
                $('#userModalLabel').text('Nuevo Usuario');
                $('#userForm')[0].reset();
                $('#formAction').val('create');
                $('#userId').val('');
                $('#password').prop('required', true);
            });

            // Manejar envío del formulario
            $('#userForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: '../controllers/UsuarioController.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        $('#userModal').modal('hide');
                        showResponseModal(response.message, response.success);
                    },
                    error: function() {
                        showResponseModal('Error al procesar la solicitud', false);
                    }
                });
            });

            // Eliminar usuario
            $('.delete-user').click(function() {
                var userId = $(this).data('id');
                var nombre = $(this).data('nombre');
                var apellido = $(this).data('apellido');
                $('#deleteUserName').text(nombre + ' ' + apellido);
                $('#confirmDelete').data('id', userId);
            });

            // Confirmar eliminación
            $('#confirmDelete').click(function() {
                var userId = $(this).data('id');
                $.ajax({
                    url: '../controllers/UsuarioController.php',
                    type: 'GET',
                    data: {
                        action: 'delete',
                        id: userId
                    },
                    dataType: 'json',
                    success: function(response) {
                        $('#deleteModal').modal('hide');
                        showResponseModal(response.message, response.success);
                    },
                    error: function() {
                        showResponseModal('Error al procesar la solicitud', false);
                    }
                });
            });
        });
    </script>
</body>
<?php include 'footer.php'; ?>
</html>