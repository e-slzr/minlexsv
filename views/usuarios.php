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
    'Administración',
    'Almacen',
    'Calidad',
    'Compras',
    'Corte',
    'Costura',
    'IT',
    'Produccion'
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
</head>
<body>
    <?php include '../components/menu_lateral.php'; ?>
    
    <main>
        <div style="width: 100%;" class="border-bottom border-secondary titulo-vista">
            <h1><strong>Gestión de Usuarios</strong></h1><br>
            <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#newUserModal">
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
                        <th>Módulo</th>
                        <th>Estado</th>
                        <th>Fecha Creación</th>
                        <th>Última Modificación</th>
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
                        <td><?php echo htmlspecialchars($usuario['modulo_codigo'] ?? 'No asignado'); ?></td>
                        <td>
                            <span class="badge <?php echo $usuario['estado'] === 'Activo' ? 'bg-success' : 'bg-danger'; ?>">
                                <?php echo htmlspecialchars($usuario['estado']); ?>
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y H:i', strtotime($usuario['usuario_creacion'])); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($usuario['usuario_modificacion'])); ?></td>
                        <td>
                            <button type="button" class="btn btn-success edit-user" 
                                    data-id="<?php echo $usuario['id']; ?>"
                                    data-alias="<?php echo htmlspecialchars($usuario['usuario_alias']); ?>"
                                    data-nombre="<?php echo htmlspecialchars($usuario['usuario_nombre']); ?>"
                                    data-apellido="<?php echo htmlspecialchars($usuario['usuario_apellido']); ?>"
                                    data-rol="<?php echo htmlspecialchars($usuario['usuario_rol_id']); ?>"
                                    data-estado="<?php echo htmlspecialchars($usuario['estado']); ?>"
                                    data-departamento="<?php echo htmlspecialchars($usuario['usuario_departamento']); ?>"
                                    data-ultima-modificacion="<?php echo date('d/m/Y H:i', strtotime($usuario['usuario_modificacion'])); ?>"
                                    data-bs-toggle="modal" data-bs-target="#editUserModal">
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
                                        <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 1-1.06 1.06L6.97 11.03a.75.75 0 0 1-1.079-.02l-3.992-4.99a.75.75 0 0 1 .708-.708l.646.647.646-.647a.75.75 0 0 1 1.079.02l2.97 3.992a.75.75 0 0 1-.01 1.042z"/>
                                        <path d="M8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"/>
                                    </svg>
                                <?php endif; ?>
                            </button>
                            <button type="button" class="btn btn-primary change-password" 
                                    data-id="<?php echo $usuario['id']; ?>"
                                    data-nombre="<?php echo htmlspecialchars($usuario['usuario_nombre'] . ' ' . $usuario['usuario_apellido']); ?>"
                                    data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-key" viewBox="0 0 16 16">
                                    <path d="M0 8a4 4 0 0 1 7.465-2H14a.5.5 0 0 1 .354.146l1.5 1.5a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0L13 9.207l-.646.647a.5.5 0 0 1-.708 0L11 9.207l-.646.647a.5.5 0 0 1-.708 0L9 9.207l-.646.647A.5.5 0 0 1 8 10h-.535A4 4 0 0 1 0 8zm4-3a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                    <path d="M4 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                                </svg>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Modal para Nuevo Usuario -->
        <div class="modal fade" id="newUserModal" tabindex="-1" role="dialog" aria-labelledby="newUserModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="newUserModalLabel">Nuevo Usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="newUserForm">
                        <div class="modal-body">
                            <input type="hidden" name="action" value="create">
                            
                            <div class="form-group mb-3">
                                <label for="new_alias">Usuario</label>
                                <input type="text" class="form-control" id="new_alias" name="alias" required>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="new_nombre">Nombre</label>
                                    <input type="text" class="form-control" id="new_nombre" name="nombre" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="new_apellido">Apellido</label>
                                    <input type="text" class="form-control" id="new_apellido" name="apellido" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="new_rol">Rol</label>
                                    <select class="form-control" id="new_rol" name="rol_id" required>
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
                                <div class="col-md-6">
                                    <label for="new_departamento">Departamento</label>
                                    <select class="form-control" id="new_departamento" name="departamento" required>
                                        <?php foreach ($departamentos as $departamento): ?>
                                            <option value="<?php echo htmlspecialchars($departamento); ?>"><?php echo htmlspecialchars($departamento); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="new_modulo">Módulo Asignado</label>
                                <select class="form-control" id="new_modulo" name="modulo_id">
                                    <option value="">Sin módulo asignado</option>
                                    <?php 
                                    require_once '../controllers/ModuloController.php';
                                    $moduloController = new ModuloController();
                                    $modulos = $moduloController->getModulos();
                                    foreach ($modulos as $modulo): 
                                    ?>
                                        <option value="<?php echo $modulo['id']; ?>"><?php echo htmlspecialchars($modulo['modulo_codigo'] . ' - ' . $modulo['modulo_tipo']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="new_password">Contraseña</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="new_password" name="password" required>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-dark" id="generatePasswordNew">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-key" viewBox="0 0 16 16">
                                                    <path d="M0 8a4 4 0 0 1 7.465-2H14a.5.5 0 0 1 .354.146l1.5 1.5a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0L13 9.207l-.646.647a.5.5 0 0 1-.708 0L11 9.207l-.646.647a.5.5 0 0 1-.708 0L9 9.207l-.646.647A.5.5 0 0 1 8 10h-.535A4 4 0 0 1 0 8zm4-3a3 3 0 1 0 2.712 4.285A.5.5 0 0 1 7.163 9h.63l.853-.854a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.793-.793-1-1h-6.63a.5.5 0 0 1-.451-.285A3 3 0 0 0 4 5z"/>
                                                    <path d="M4 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                                                </svg>
                                            </button>
                                            <button type="button" class="btn btn-light" id="togglePasswordNew">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                                                    <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755-.165.165-.337.328-.517.486l.708.709z"/>
                                                    <path d="M8 5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="new_password_confirm">Confirmar Contraseña</label>
                                    <input type="password" class="form-control" id="new_password_confirm" name="password_confirm" required>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-dark">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal para Editar Usuario -->
        <div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editUserModalLabel">Editar Usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="editUserForm">
                        <div class="modal-body">
                            <input type="hidden" name="id" id="edit_userId">
                            <input type="hidden" name="action" value="update">
                            
                            <div class="form-group mb-3">
                                <label for="edit_alias">Usuario</label>
                                <input type="text" class="form-control" id="edit_alias" name="alias" required>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="edit_nombre">Nombre</label>
                                    <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="edit_apellido">Apellido</label>
                                    <input type="text" class="form-control" id="edit_apellido" name="apellido" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="edit_rol">Rol</label>
                                    <select class="form-control" id="edit_rol" name="rol_id" required>
                                        <?php foreach ($roles as $rol): ?>
                                            <option value="<?php echo $rol['id']; ?>"><?php echo htmlspecialchars($rol['rol_nombre']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="edit_departamento">Departamento</label>
                                    <select class="form-control" id="edit_departamento" name="departamento" required>
                                        <?php foreach ($departamentos as $departamento): ?>
                                            <option value="<?php echo htmlspecialchars($departamento); ?>"><?php echo htmlspecialchars($departamento); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="edit_modulo">Módulo Asignado</label>
                                    <select class="form-control" id="edit_modulo" name="modulo_id">
                                        <option value="">Sin módulo asignado</option>
                                        <?php foreach ($modulos as $modulo): ?>
                                            <option value="<?php echo $modulo['id']; ?>"><?php echo htmlspecialchars($modulo['modulo_codigo'] . ' - ' . $modulo['modulo_tipo']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="edit_estado">Estado</label>
                                    <div class="input-group">
                                        <select class="form-control" id="edit_estado" name="estado" disabled>
                                            <option value="Activo">Activo</option>
                                            <option value="Inactivo">Inactivo</option>
                                        </select>
                                        <div class="input-group-append">
                                            <span class="input-group-text" id="estado_indicador"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-muted mt-3">
                                <small>Última modificación: <span id="ultima_modificacion"></span></small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-dark">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal para Cambiar Contraseña -->
        <div class="modal fade" id="changePasswordModal" tabindex="-1" role="dialog" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="changePasswordModalLabel">Cambiar Contraseña</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="changePasswordForm">
                        <div class="modal-body">
                            <input type="hidden" name="id" id="change_password_id">
                            <input type="hidden" name="action" value="change_password">
                            
                            <div class="form-group mb-3">
                                <label for="change_password">Nueva Contraseña</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="change_password" name="password" required>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-dark" id="generatePasswordChange">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-key" viewBox="0 0 16 16">
                                                <path d="M0 8a4 4 0 0 1 7.465-2H14a.5.5 0 0 1 .354.146l1.5 1.5a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0L13 9.207l-.646.647a.5.5 0 0 1-.708 0L11 9.207l-.646.647a.5.5 0 0 1-.708 0L9 9.207l-.646.647A.5.5 0 0 1 8 10h-.535A4 4 0 0 1 0 8zm4-3a3 3 0 1 0 2.712 4.285A.5.5 0 0 1 7.163 9h.63l.853-.854a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.793-.793-1-1h-6.63a.5.5 0 0 1-.451-.285A3 3 0 0 0 4 5z"/>
                                                <path d="M4 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                                            </svg>
                                        </button>
                                        <button type="button" class="btn btn-light" id="togglePasswordChange">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                                                <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755-.165.165-.337.328-.517.486l.708.709z"/>
                                                <path d="M8 5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="change_password_confirm">Confirmar Nueva Contraseña</label>
                                <input type="password" class="form-control" id="change_password_confirm" name="password_confirm" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
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
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>¿Está seguro que desea <span id="actionType"></span> al usuario <strong><span id="userName"></span></strong>?</p>
                        <div class="alert alert-warning">
                            <strong>Nota:</strong> Si deshabilita el usuario, este no podrá iniciar sesión en el sistema.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
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
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-check-circle text-success" viewBox="0 0 16 16">
                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 1-1.06 1.06L6.97 11.03a.75.75 0 0 1-1.079-.02l-3.992-4.99a.75.75 0 0 1 .708-.708l.646.647.646-.647a.75.75 0 0 1 1.079.02l2.97 3.992a.75.75 0 0 1-.01 1.042z"/>
                            </svg>
                            <p class="mt-3" id="confirmationMessage"></p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Aceptar</button>
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
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                        <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Aceptar</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/usuarios.js"></script>
</body>
<?php include '../components/footer.php'; ?>
</html>