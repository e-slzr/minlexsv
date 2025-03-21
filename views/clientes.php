<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

require_once '../controllers/ClienteController.php';
$clienteController = new ClienteController();
$clientes = $clienteController->getClientes() ?? [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MINLEX | Clientes</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/style_main.css">
    <style>
        .sortable {
            cursor: pointer;
        }
        .page-item.active .page-link {
            background-color: #212529;
            border-color: #212529;
        }
        .page-link {
            color: #212529;
        }
        .page-link:hover {
            color: #000;
        }
        .filtered-row {
            display: table-row;
        }
    </style>
</head>
<body>
    <?php include '../components/menu_lateral.php'; ?>
    
    <main>
        <div class="titulo-vista">
            <h1><strong>Gestión de Clientes</strong></h1>
            <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#nuevoClienteModal">
                <i class="fas fa-plus"></i> Nuevo Cliente
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
                        <div class="col-md-3 mb-2">
                            <label for="filtro-empresa" class="form-label">Empresa</label>
                            <input type="text" class="form-control filtro" id="filtro-empresa" placeholder="Buscar empresa...">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label for="filtro-nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control filtro" id="filtro-nombre" placeholder="Buscar nombre...">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label for="filtro-apellido" class="form-label">Apellido</label>
                            <input type="text" class="form-control filtro" id="filtro-apellido" placeholder="Buscar apellido...">
                        </div>
                        <div class="col-md-3 mb-2">
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
                <table class="table table-striped table-hover" id="tabla-clientes">
                    <thead>
                        <tr>
                            <th class="sortable" data-column="id">ID <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-column="empresa">Empresa <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-column="nombre">Nombre <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-column="apellido">Apellido <i class="fas fa-sort"></i></th>
                            <th>Dirección</th>
                            <th>Teléfono</th>
                            <th>Correo</th>
                            <th class="sortable" data-column="estado">Estado <i class="fas fa-sort"></i></th>
                            <th>Opciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clientes as $cliente): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($cliente['id']); ?></td>
                                <td><?php echo htmlspecialchars($cliente['cliente_empresa']); ?></td>
                                <td><?php echo htmlspecialchars($cliente['cliente_nombre']); ?></td>
                                <td><?php echo htmlspecialchars($cliente['cliente_apellido']); ?></td>
                                <td><?php echo htmlspecialchars($cliente['cliente_direccion']); ?></td>
                                <td><?php echo htmlspecialchars($cliente['cliente_telefono']); ?></td>
                                <td><?php echo htmlspecialchars($cliente['cliente_correo']); ?></td>
                                <td>
                                    <span class="badge <?php echo $cliente['estado'] == 'Activo' ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo htmlspecialchars($cliente['estado']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-dark btn-sm editar-cliente" data-bs-toggle="modal" 
                                            data-bs-target="#editarClienteModal"
                                            data-id="<?php echo htmlspecialchars($cliente['id']); ?>"
                                            data-empresa="<?php echo htmlspecialchars($cliente['cliente_empresa']); ?>"
                                            data-nombre="<?php echo htmlspecialchars($cliente['cliente_nombre']); ?>"
                                            data-apellido="<?php echo htmlspecialchars($cliente['cliente_apellido']); ?>"
                                            data-direccion="<?php echo htmlspecialchars($cliente['cliente_direccion']); ?>"
                                            data-telefono="<?php echo htmlspecialchars($cliente['cliente_telefono']); ?>"
                                            data-correo="<?php echo htmlspecialchars($cliente['cliente_correo']); ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn <?php echo $cliente['estado'] == 'Activo' ? 'btn-danger' : 'btn-success'; ?> btn-sm toggle-status"
                                            data-bs-toggle="modal"
                                            data-bs-target="#confirmStatusModal"
                                            data-id="<?php echo htmlspecialchars($cliente['id']); ?>"
                                            data-estado="<?php echo $cliente['estado'] == 'Activo' ? 'Inactivo' : 'Activo'; ?>">
                                        <i class="fas <?php echo $cliente['estado'] == 'Activo' ? 'fa-ban' : 'fa-check'; ?>"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <!-- Controles de paginación -->
                <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <span>Mostrar</span>
                        <select class="form-select form-select-sm" id="registros-por-pagina" style="width: auto;">
                            <option value="5">5</option>
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span>registros</span>
                    </div>
                    <nav aria-label="Navegación de páginas">
                        <ul class="pagination mb-0">
                            <li class="page-item" id="anterior-pagina">
                                <a class="page-link" href="#" aria-label="Anterior">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            <li class="page-item disabled">
                                <span class="page-link" id="info-pagina">Página 1 de 1</span>
                            </li>
                            <li class="page-item" id="siguiente-pagina">
                                <a class="page-link" href="#" aria-label="Siguiente">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Modal Nuevo Cliente -->
        <div class="modal fade" id="nuevoClienteModal" tabindex="-1" aria-labelledby="nuevoClienteModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="nuevoClienteModalLabel">Nuevo Cliente</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="nuevoClienteForm">
                            <input type="hidden" name="action" value="create">

                            <div class="mb-3">
                                <label for="nuevo_empresa" class="form-label">Empresa* <small>(máx. 50 caracteres)</small></label>
                                <input type="text" class="form-control" id="nuevo_empresa" name="cliente_empresa" maxlength="50" required>
                            </div>

                            <div class="mb-3">
                                <label for="nuevo_nombre" class="form-label">Nombre* <small>(máx. 50 caracteres)</small></label>
                                <input type="text" class="form-control" id="nuevo_nombre" name="cliente_nombre" maxlength="50" required>
                            </div>

                            <div class="mb-3">
                                <label for="nuevo_apellido" class="form-label">Apellido* <small>(máx. 50 caracteres)</small></label>
                                <input type="text" class="form-control" id="nuevo_apellido" name="cliente_apellido" maxlength="50" required>
                            </div>

                            <div class="mb-3">
                                <label for="nuevo_direccion" class="form-label">Dirección</label>
                                <textarea class="form-control" id="nuevo_direccion" name="cliente_direccion" rows="2"></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="nuevo_telefono" class="form-label">Teléfono <small>(máx. 20 caracteres)</small></label>
                                <input type="tel" class="form-control" id="nuevo_telefono" name="cliente_telefono" maxlength="20">
                            </div>

                            <div class="mb-3">
                                <label for="nuevo_correo" class="form-label">Correo <small>(máx. 100 caracteres)</small></label>
                                <input type="email" class="form-control" id="nuevo_correo" name="cliente_correo" maxlength="100">
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-dark" id="guardarNuevoCliente">Guardar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Editar Cliente -->
        <div class="modal fade" id="editarClienteModal" tabindex="-1" aria-labelledby="editarClienteModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editarClienteModalLabel">Editar Cliente</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editarClienteForm">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" id="editar_id">

                            <div class="mb-3">
                                <label for="editar_empresa" class="form-label">Empresa* <small>(máx. 50 caracteres)</small></label>
                                <input type="text" class="form-control" id="editar_empresa" name="cliente_empresa" maxlength="50" required>
                            </div>

                            <div class="mb-3">
                                <label for="editar_nombre" class="form-label">Nombre* <small>(máx. 50 caracteres)</small></label>
                                <input type="text" class="form-control" id="editar_nombre" name="cliente_nombre" maxlength="50" required>
                            </div>

                            <div class="mb-3">
                                <label for="editar_apellido" class="form-label">Apellido* <small>(máx. 50 caracteres)</small></label>
                                <input type="text" class="form-control" id="editar_apellido" name="cliente_apellido" maxlength="50" required>
                            </div>

                            <div class="mb-3">
                                <label for="editar_direccion" class="form-label">Dirección</label>
                                <textarea class="form-control" id="editar_direccion" name="cliente_direccion" rows="2"></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="editar_telefono" class="form-label">Teléfono <small>(máx. 20 caracteres)</small></label>
                                <input type="tel" class="form-control" id="editar_telefono" name="cliente_telefono" maxlength="20">
                            </div>

                            <div class="mb-3">
                                <label for="editar_correo" class="form-label">Correo <small>(máx. 100 caracteres)</small></label>
                                <input type="email" class="form-control" id="editar_correo" name="cliente_correo" maxlength="100">
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-dark" id="guardarEditarCliente">Guardar Cambios</button>
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
                        <p>¿Está seguro que desea cambiar el estado de este cliente?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-warning" id="confirmToggleStatus">Confirmar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modales de éxito y error -->
        <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Éxito</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p id="successMessage"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Error</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p id="errorMessage"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../components/footer.php'; ?>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="../js/clientes.js"></script>
</body>
</html>
