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
    <?php include '../components/menu_lateral.php'; ?>
    
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
                        <th>Usuario</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Rol</th>
                        <th>Departamento</th>
                        <th>Estado</th>
                        <th>Opciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($usuario['id']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['usuario_usuario']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['usuario_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['usuario_apellido']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['rol_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['usuario_departamento']); ?></td>
                        <td>
                            <span class="badge <?php echo $usuario['estado'] === 'Activo' ? 'bg-success' : 'bg-danger'; ?>">
                                <?php echo htmlspecialchars($usuario['estado']); ?>
                            </span>
                        </td>
                        <td>
                            <button type="button" class="btn btn-success edit-user" 
                                    data-id="<?php echo $usuario['id']; ?>"
                                    data-alias="<?php echo htmlspecialchars($usuario['usuario_usuario']); ?>"
                                    data-nombre="<?php echo htmlspecialchars($usuario['usuario_nombre']); ?>"
                                    data-apellido="<?php echo htmlspecialchars($usuario['usuario_apellido']); ?>"
                                    data-rol="<?php echo htmlspecialchars($usuario['usuario_rol_id']); ?>"
                                    data-estado="<?php echo htmlspecialchars($usuario['estado']); ?>"
                                    data-departamento="<?php echo htmlspecialchars($usuario['usuario_departamento']); ?>"
                                    data-toggle="modal" data-target="#userModal">
                                <svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M14 6L16.2929 3.70711C16.6834 3.31658 17.3166 3.31658 17.7071 3.70711L20.2929 6.29289C20.6834 6.68342 20.6834 7.31658 20.2929 7.70711L18 10M14 6L4.29289 15.7071C4.10536 15.8946 4 16.149 4 16.4142V19C4 19.5523 4.44772 20 5 20H7.58579C7.851 20 8.10536 19.8946 8.29289 19.7071L18 10M14 6L18 10" stroke="white" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                                </svg>
                            </button>
                            <button type="button" class="btn <?php echo $usuario['estado'] === 'Activo' ? 'btn-warning' : 'btn-info'; ?> toggle-status" 
                                    data-id="<?php echo $usuario['id']; ?>"
                                    data-nombre="<?php echo htmlspecialchars($usuario['usuario_nombre'] . ' ' . $usuario['usuario_apellido']); ?>"
                                    data-estado="<?php echo htmlspecialchars($usuario['estado']); ?>"
                                    data-departamento="<?php echo htmlspecialchars($usuario['usuario_departamento']); ?>">
                                <?php if ($usuario['estado'] === 'Activo'): ?>
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
                                <label for="alias">Usuario</label>
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
                                <label for="rol">Rol</label>
                                <select class="form-control" id="rol" name="rol_id" required>
                                    <?php 
                                    require_once '../controllers/RolController.php';
                                    $rolController = new RolController();
                                    $roles = $rolController->getActiveRoles();
                                    foreach ($roles as $rol): 
                                    ?>
                                        <option value="<?php echo $rol['id']; ?>"><?php echo htmlspecialchars($rol['rol_nombre']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group mb-3" id="passwordGroup">
                                <label for="password">Contraseña</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-dark" id="generatePassword">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-key" viewBox="0 0 16 16">
                                                <path d="M0 8a4 4 0 0 1 7.465-2H14a.5.5 0 0 1 .354.146l1.5 1.5a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0L13 9.207l-.646.647a.5.5 0 0 1-.708 0L11 9.207l-.646.647a.5.5 0 0 1-.708 0L9 9.207l-.646.647A.5.5 0 0 1 8 10h-.535A4 4 0 0 1 0 8zm4-3a3 3 0 1 0 2.712 4.285A.5.5 0 0 1 7.163 9h.63l.853-.854a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.793-.793-1-1h-6.63a.5.5 0 0 1-.451-.285A3 3 0 0 0 4 5z"/>
                                                <path d="M4 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                                            </svg>
                                        </button>
                                        <button type="button" class="btn btn-light" id="togglePassword">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                                                <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755-.165.165-.337.328-.517.486l.708.709z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <small class="form-text text-muted">Dejar el campo vacio para conservar la contraseña actual*</small>
                            </div>

                            <div class="form-group mb-3">
                                <label for="departamento">Departamento</label>
                                <select class="form-control" id="departamento" name="departamento" required>
                                    <?php foreach ($departamentos as $departamento): ?>
                                        <option value="<?php echo htmlspecialchars($departamento); ?>"><?php echo htmlspecialchars($departamento); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group mb-3" id="estadoGroup">
                                <label for="estado">Estado</label>
                                <select class="form-control" id="estado" name="estado">
                                    <option value="Activo">Activo</option>
                                    <option value="Deshabilitado">Deshabilitado</option>
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
                        <p>¿Está seguro que desea <span id="actionType"></span> al usuario <strong><span id="userName"></span></strong>?</p>
                        <div class="alert alert-warning">
                            <strong>Nota:</strong> Si deshabilita el usuario, este no podrá iniciar sesión en el sistema.
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
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            function generatePassword() {
                const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                let password = chars.charAt(Math.floor(Math.random() * 26)).toUpperCase();
                const length = Math.floor(Math.random() * 5) + 4;
                for (let i = 1; i < length; i++) {
                    password += chars.charAt(Math.floor(Math.random() * chars.length));
                }
                return password;
            }

            $('#generatePassword, #generateNewPassword').click(function() {
                const password = generatePassword();
                const inputId = $(this).attr('id') === 'generatePassword' ? '#password' : '#newPassword';
                $(inputId).val(password);
                if (inputId === '#newPassword') {
                    $('#confirmPassword').val(password);
                }
            });

            $('#togglePassword, #toggleNewPassword, #toggleConfirmPassword').click(function() {
                const inputId = $(this).closest('.input-group').find('input[type="password"], input[type="text"]');
                const icon = $(this).find('svg');
                
                if (inputId.attr('type') === 'password') {
                    inputId.attr('type', 'text');
                    icon.html('<path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755-.165.165-.337.328-.517.486l.708.709z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>');
                } else {
                    inputId.attr('type', 'password');
                    icon.html('<path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7.028 7.028 0 0 0-2.79.588l.77.771A5.944 5.944 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.134 13.134 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755-.165.165-.337.328-.517.486l.708.709z"/><path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829l.822.822zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829z"/><path d="M3.35 5.47c-.18.16-.353.322-.518.487A13.134 13.134 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7.029 7.029 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12-.708.708z"/>');
                }
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            // Incluir el modal de reseteo de contraseña
            $.get('../components/reset_password_modal.php', function(data) {
                $('body').append(data);
            });

            // Búsqueda en tiempo real
            $("#searchInput").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("table tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });

            // Generar contraseña aleatoria
            $('#generatePassword').click(function() {
                var length = Math.floor(Math.random() * 5) + 4; // Random length between 4 and 8
                var firstChar = String.fromCharCode(65 + Math.floor(Math.random() * 26)); // A-Z
                var charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
                var password = firstChar;
                for (var i = 1; i < length; i++) {
                    password += charset.charAt(Math.floor(Math.random() * charset.length));
                }
                $('#password').val(password);
            });

            // Toggle de visibilidad de contraseña
            $('#togglePassword').click(function() {
                var passwordInput = $('#password');
                var icon = $(this).find('svg');
                
                if (passwordInput.attr('type') === 'password') {
                    passwordInput.attr('type', 'text');
                    icon.removeClass('bi-eye-fill').addClass('bi-eye-slash-fill');
                } else {
                    passwordInput.attr('type', 'password');
                    icon.removeClass('bi-eye-slash-fill').addClass('bi-eye-fill');
                }
            });

            // Manejo del modal de edición
            $('.edit-user').click(function() {
                $('#userModalLabel').text('Editar Usuario');
                $('#userId').val($(this).data('id'));
                $('#alias').val($(this).data('alias'));
                $('#nombre').val($(this).data('nombre'));
                $('#apellido').val($(this).data('apellido'));
                $('#rol').val($(this).data('rol'));
                $('#estado').val($(this).data('estado'));
                $('#formAction').val('update');
                $('#password').val('').attr('required', false);
                $('#passwordGroup').show();
                $('#estadoGroup').show();
            });

            // Resetear el modal al crear nuevo usuario
            $('[data-target="#userModal"]').click(function() {
                if (!$(this).hasClass('edit-user')) {
                    $('#userModalLabel').text('Nuevo Usuario');
                    $('#userForm')[0].reset();
                    $('#userId').val('');
                    $('#formAction').val('create');
                    $('#password').attr('required', true);
                    $('#passwordGroup').show();
                    $('#estadoGroup').hide();
                }
            });

            // Manejo del modal de eliminación
            $('.delete-user').click(function() {
                var id = $(this).data('id');
                var nombre = $(this).data('nombre');
                $('#userToDelete').text(nombre);
                $('#confirmDelete').attr('href', '../controllers/UsuarioController.php?action=delete&id=' + id);
            });

            // Manejo del formulario con AJAX
            $('#userForm').on('submit', function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: '../controllers/UsuarioController.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        $('#userModal').modal('hide');
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
                        $('#userModal').modal('hide');
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

            // Manejo del cambio de estado
            $('.toggle-status').click(function() {
                var id = $(this).data('id');
                var nombre = $(this).data('nombre');
                var estadoActual = $(this).data('estado');
                var nuevoEstado = estadoActual === 'Activo' ? 'deshabilitar' : 'activar';
                
                $('#actionType').text(nuevoEstado);
                $('#userName').text(nombre);
                $('#confirmToggleStatus').data('id', id);
                $('#toggleStatusModal').modal('show');
            });

            // Confirmar cambio de estado
            $('#confirmToggleStatus').click(function() {
                var id = $(this).data('id');
                
                $.ajax({
                    url: '../controllers/UsuarioController.php',
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
            $('[data-target="#userModal"]').click(function() {
                if (!$(this).hasClass('edit-user')) {
                    $('#estadoGroup').hide();
                } else {
                    $('#estadoGroup').show();
                }
            });

            // Manejo del reseteo de contraseña
            $('.reset-password').click(function() {
                var id = $(this).data('id');
                var nombre = $(this).data('nombre');
                $('#resetUserId').val(id);
                $('#resetPasswordForm')[0].reset();
            });

            // Generar nueva contraseña aleatoria
            $('#generateNewPassword').click(function() {
                var length = 8;
                var charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
                var password = "";
                for (var i = 0; i < length; i++) {
                    password += charset.charAt(Math.floor(Math.random() * charset.length));
                }
                $('#newPassword').val(password);
                $('#confirmPassword').val(password);
            });

            // Toggle visibilidad de nueva contraseña
            $('#toggleNewPassword, #toggleConfirmPassword').click(function() {
                var inputId = $(this).attr('id') === 'toggleNewPassword' ? 'newPassword' : 'confirmPassword';
                var passwordInput = $('#' + inputId);
                var icon = $(this).find('svg');
                
                if (passwordInput.attr('type') === 'password') {
                    passwordInput.attr('type', 'text');
                    icon.removeClass('bi-eye').addClass('bi-eye-slash');
                } else {
                    passwordInput.attr('type', 'password');
                    icon.removeClass('bi-eye-slash').addClass('bi-eye');
                }
            });

            // Validación y envío del formulario de reseteo de contraseña
            $('#resetPasswordForm').on('submit', function(e) {
                e.preventDefault();
                
                var newPassword = $('#newPassword').val();
                var confirmPassword = $('#confirmPassword').val();

                if (newPassword.length < 8) {
                    $('#errorMessage').text('La contraseña debe tener al menos 8 caracteres');
                    $('#errorModal').modal('show');
                    return;
                }

                if (newPassword !== confirmPassword) {
                    $('#errorMessage').text('Las contraseñas no coinciden');
                    $('#errorModal').modal('show');
                    return;
                }

                $.ajax({
                    url: '../controllers/UsuarioController.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        $('#resetPasswordModal').modal('hide');
                        if (response.success) {
                            $('#confirmationMessage').text(response.message);
                            $('#confirmationModal').modal('show');
                        } else {
                            $('#errorMessage').text(response.message);
                            $('#errorModal').modal('show');
                        }
                    },
                    error: function() {
                        $('#resetPasswordModal').modal('hide');
                        $('#errorMessage').text('Error al procesar la solicitud');
                        $('#errorModal').modal('show');
                    }
                });
            });
        });
    </script>
</body>
<?php include '../components/footer.php'; ?>
</html>