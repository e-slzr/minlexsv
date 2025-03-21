<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

require_once '../controllers/UsuarioController.php';
require_once '../controllers/RolController.php';

$usuarioController = new UsuarioController();
$rolController = new RolController();

$usuarios = $usuarioController->getUsuarios() ?? [];
$roles = $rolController->getRoles() ?? [];

$departamentos = [
    'Corte',
    'Calidad',
    'Produccion',
    'Compras',
    'Almacen',
    'Costura',
    'Administracion',
    'TI',
    'Recursos Humanos',
    'Ventas',
    'Soporte',
    'Logística'
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MINLEX | Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/style_main.css">
</head>
<body>
    <?php include '../components/menu_lateral.php'; ?>
    
    <main>
        <div class="titulo-vista">
            <h1><strong>Gestión de Usuarios</strong></h1>
            <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#nuevoUsuarioModal">
                <i class="fas fa-plus"></i> Nuevo Usuario
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
                        <div class="col-md-2 mb-2">
                            <label for="filtro-usuario" class="form-label">Usuario</label>
                            <input type="text" class="form-control filtro" id="filtro-usuario" placeholder="Buscar usuario...">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label for="filtro-nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control filtro" id="filtro-nombre" placeholder="Buscar nombre...">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label for="filtro-apellido" class="form-label">Apellido</label>
                            <input type="text" class="form-control filtro" id="filtro-apellido" placeholder="Buscar apellido...">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label for="filtro-departamento" class="form-label">Departamento</label>
                            <select class="form-select filtro" id="filtro-departamento">
                                <option value="">Todos</option>
                                <?php foreach ($departamentos as $departamento): ?>
                                    <option value="<?php echo $departamento; ?>">
                                        <?php echo htmlspecialchars($departamento); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label for="filtro-rol" class="form-label">Rol</label>
                            <select class="form-select filtro" id="filtro-rol">
                                <option value="">Todos</option>
                                <?php foreach ($roles as $rol): ?>
                                    <option value="<?php echo htmlspecialchars($rol['rol_nombre']); ?>">
                                        <?php echo htmlspecialchars($rol['rol_nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
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
                <table class="table table-striped table-hover" id="tabla-usuarios">
                    <thead>
                        <tr>
                            <th class="sortable" data-column="id">ID <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-column="usuario">Usuario <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-column="nombre">Nombre <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-column="apellido">Apellido <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-column="departamento">Departamento <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-column="rol">Rol <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-column="estado">Estado <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-column="creacion">Creación <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-column="modificacion">Última Modificación <i class="fas fa-sort"></i></th>
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
                                <td><?php echo htmlspecialchars($usuario['usuario_departamento']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['rol_nombre']); ?></td>
                                <td>
                                    <span class="badge <?php echo $usuario['estado'] === 'Activo' ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo htmlspecialchars($usuario['estado']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($usuario['usuario_creacion'])); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($usuario['usuario_modificacion'])); ?></td>
                                <td>
                                    <button type="button" class="btn btn-light edit-usuario" 
                                            data-id="<?php echo $usuario['id']; ?>"
                                            data-nombre="<?php echo htmlspecialchars($usuario['usuario_nombre']); ?>"
                                            data-apellido="<?php echo htmlspecialchars($usuario['usuario_apellido']); ?>"
                                            data-usuario="<?php echo htmlspecialchars($usuario['usuario_usuario']); ?>"
                                            data-departamento="<?php echo htmlspecialchars($usuario['usuario_departamento']); ?>"
                                            data-rol-id="<?php echo htmlspecialchars($usuario['usuario_rol_id']); ?>"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editarUsuarioModal">
                                        <svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M14 6L16.2929 3.70711C16.6834 3.31658 17.3166 3.31658 17.7071 3.70711L20.2929 6.29289C20.6834 6.68342 20.6834 7.31658 20.2929 7.70711L18 10M14 6L4.29289 15.7071C4.10536 15.8946 4 16.149 4 16.4142V19C4 19.5523 4.44772 20 5 20H7.58579C7.851 20 8.10536 19.8946 8.29289 19.7071L18 10M14 6L18 10" 
                                                  stroke="black" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                                        </svg>
                                    </button>
                                    <button class="btn btn-light toggle-status"
                                            data-id="<?php echo $usuario['id']; ?>"
                                            data-estado="<?php echo $usuario['estado']; ?>"
                                            data-bs-toggle="modal"
                                            data-bs-target="#confirmStatusModal">
                                        <?php if ($usuario['estado'] === 'Activo'): ?>
                                            <svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M18 6L6 18M6 6L18 18" stroke="black" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                                            </svg>
                                        <?php else: ?>
                                            <svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M5 13L9 17L19 7" stroke="black" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                                            </svg>
                                        <?php endif; ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
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
                    <nav aria-label="Paginación de usuarios">
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

        <!-- Modal Nuevo Usuario -->
        <div class="modal fade" id="nuevoUsuarioModal" tabindex="-1" aria-labelledby="nuevoUsuarioModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="nuevoUsuarioModalLabel">Nuevo Usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="nuevoUsuarioForm">
                            <input type="hidden" name="action" value="create">

                            <div class="mb-3">
                                <label for="nuevo_usuario" class="form-label">Usuario* <small>(máx. 25 caracteres)</small></label>
                                <input type="text" class="form-control" id="nuevo_usuario" name="usuario" maxlength="25" required>
                            </div>

                            <div class="mb-3">
                                <label for="nuevo_nombre" class="form-label">Nombre* <small>(máx. 25 caracteres)</small></label>
                                <input type="text" class="form-control" id="nuevo_nombre" name="nombre" maxlength="25" required>
                            </div>

                            <div class="mb-3">
                                <label for="nuevo_apellido" class="form-label">Apellido* <small>(máx. 25 caracteres)</small></label>
                                <input type="text" class="form-control" id="nuevo_apellido" name="apellido" maxlength="25" required>
                            </div>

                            <div class="mb-3">
                                <label for="nuevo_rol_id" class="form-label">Rol*</label>
                                <select class="form-select" id="nuevo_rol_id" name="rol_id" required>
                                    <option value="">Seleccione un rol</option>
                                    <?php foreach ($roles as $rol): ?>
                                        <option value="<?php echo $rol['id']; ?>">
                                            <?php echo htmlspecialchars($rol['rol_nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="nuevo_departamento" class="form-label">Departamento <small>(máx. 25 caracteres)</small></label>
                                <select class="form-select" id="nuevo_departamento" name="departamento">
                                    <option value="">Seleccione un departamento</option>
                                    <?php foreach ($departamentos as $departamento): ?>
                                        <option value="<?php echo $departamento; ?>">
                                            <?php echo htmlspecialchars($departamento); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="nuevo_password" class="form-label">Contraseña* <small>(máx. 255 caracteres)</small></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="nuevo_password" name="password" maxlength="255" required>
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="nuevo_password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-dark" id="guardarNuevoUsuario">Guardar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Editar Usuario -->
        <div class="modal fade" id="editarUsuarioModal" tabindex="-1" aria-labelledby="editarUsuarioModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editarUsuarioModalLabel">Editar Usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editarUsuarioForm">
                            <input type="hidden" id="editar_id" name="id">
                            <input type="hidden" name="action" value="update">

                            <div class="mb-3">
                                <label for="editar_usuario" class="form-label">Usuario* <small>(máx. 25 caracteres)</small></label>
                                <input type="text" class="form-control" id="editar_usuario" name="usuario" maxlength="25" required>
                            </div>

                            <div class="mb-3">
                                <label for="editar_nombre" class="form-label">Nombre* <small>(máx. 25 caracteres)</small></label>
                                <input type="text" class="form-control" id="editar_nombre" name="nombre" maxlength="25" required>
                            </div>

                            <div class="mb-3">
                                <label for="editar_apellido" class="form-label">Apellido* <small>(máx. 25 caracteres)</small></label>
                                <input type="text" class="form-control" id="editar_apellido" name="apellido" maxlength="25" required>
                            </div>

                            <div class="mb-3">
                                <label for="editar_rol_id" class="form-label">Rol*</label>
                                <select class="form-select" id="editar_rol_id" name="rol_id" required>
                                    <option value="">Seleccione un rol</option>
                                    <?php foreach ($roles as $rol): ?>
                                        <option value="<?php echo $rol['id']; ?>">
                                            <?php echo htmlspecialchars($rol['rol_nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="editar_departamento" class="form-label">Departamento <small>(máx. 25 caracteres)</small></label>
                                <select class="form-select" id="editar_departamento" name="departamento">
                                    <option value="">Seleccione un departamento</option>
                                    <?php foreach ($departamentos as $departamento): ?>
                                        <option value="<?php echo $departamento; ?>">
                                            <?php echo htmlspecialchars($departamento); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="editar_password" class="form-label">Contraseña <small>(Dejar en blanco para mantener la actual)</small></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="editar_password" name="password" maxlength="255">
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="editar_password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <small class="form-text text-muted">Solo complete este campo si desea cambiar la contraseña</small>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-dark" id="guardarEditarUsuario">Actualizar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Confirmar Estado -->
        <div class="modal fade" id="confirmStatusModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmar Cambio de Estado</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>¿Está seguro de que desea cambiar el estado de este usuario?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-dark" id="confirmStatus">Confirmar</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/usuarios.js"></script>
</body>
</html>