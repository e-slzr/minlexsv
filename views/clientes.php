<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../controllers/ClienteController.php';
$controller = new ClienteController();
$clientes = $controller->getClientes();

// Mensajes de éxito/error
$messages = [
    'success' => [
        1 => 'Cliente creado exitosamente',
        2 => 'Cliente actualizado exitosamente',
        3 => 'Cliente eliminado exitosamente'
    ],
    'error' => [
        1 => 'Error al crear el cliente',
        2 => 'Error al actualizar el cliente',
        3 => 'Error al eliminar el cliente'
    ]
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Clientes - MPC System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/style_main.css">
</head>
<body>
    <?php include 'menu_lateral.php'; ?>

    <div class="content-wrapper">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col">
                    <h2><i class="bi bi-people"></i> Gestión de Clientes</h2>
                </div>
                <div class="col text-end">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#clienteModal">
                        <i class="bi bi-plus-circle"></i> Nuevo Cliente
                    </button>
                </div>
            </div>

            <?php
            // Mostrar mensajes de éxito/error
            if (isset($_GET['success']) && isset($messages['success'][$_GET['success']])) {
                echo '<div class="alert alert-success">' . $messages['success'][$_GET['success']] . '</div>';
            }
            if (isset($_GET['error']) && isset($messages['error'][$_GET['error']])) {
                echo '<div class="alert alert-danger">' . $messages['error'][$_GET['error']] . '</div>';
            }
            ?>

            <!-- Barra de búsqueda -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" id="searchInput" class="form-control" placeholder="Buscar clientes...">
                        <button class="btn btn-outline-secondary" type="button" id="searchButton">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tabla de clientes -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Empresa</th>
                            <th>Nombre</th>
                            <th>Teléfono</th>
                            <th>Correo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clientes as $cliente): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($cliente['cliente_empresa']); ?></td>
                            <td><?php echo htmlspecialchars($cliente['cliente_nombre'] . ' ' . $cliente['cliente_apellido']); ?></td>
                            <td><?php echo htmlspecialchars($cliente['cliente_telefono']); ?></td>
                            <td><?php echo htmlspecialchars($cliente['cliente_correo']); ?></td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="verCliente(<?php echo $cliente['id']; ?>)">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-warning" onclick="editarCliente(<?php echo $cliente['id']; ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="confirmarEliminar(<?php echo $cliente['id']; ?>)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para Crear/Editar Cliente -->
    <div class="modal fade" id="clienteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Nuevo Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="clienteForm" action="../controllers/ClienteController.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        <input type="hidden" name="id" id="clienteId">
                        
                        <div class="mb-3">
                            <label for="empresa" class="form-label">Empresa</label>
                            <input type="text" class="form-control" id="empresa" name="empresa" required>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col">
                                <label for="nombre" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                            </div>
                            <div class="col">
                                <label for="apellido" class="form-label">Apellido</label>
                                <input type="text" class="form-control" id="apellido" name="apellido" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="direccion" class="form-label">Dirección</label>
                            <textarea class="form-control" id="direccion" name="direccion" rows="2"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" id="telefono" name="telefono">
                        </div>

                        <div class="mb-3">
                            <label for="correo" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" id="correo" name="correo">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación de Eliminación -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    ¿Está seguro que desea eliminar este cliente?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Eliminar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Variables globales
        let clienteModal = new bootstrap.Modal(document.getElementById('clienteModal'));
        let deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        let clienteIdToDelete = null;

        // Función para ver cliente
        function verCliente(id) {
            fetch(`../controllers/ClienteController.php?action=view&id=${id}`)
                .then(response => response.json())
                .then(cliente => {
                    document.getElementById('modalTitle').textContent = 'Ver Cliente';
                    document.getElementById('clienteId').value = cliente.id;
                    document.getElementById('empresa').value = cliente.empresa;
                    document.getElementById('nombre').value = cliente.nombre;
                    document.getElementById('apellido').value = cliente.apellido;
                    document.getElementById('direccion').value = cliente.direccion;
                    document.getElementById('telefono').value = cliente.telefono;
                    document.getElementById('correo').value = cliente.correo;
                    
                    // Deshabilitar campos para solo lectura
                    const formInputs = document.querySelectorAll('#clienteForm input, #clienteForm textarea');
                    formInputs.forEach(input => input.setAttribute('disabled', 'disabled'));
                    
                    clienteModal.show();
                });
        }

        // Función para editar cliente
        function editarCliente(id) {
            fetch(`../controllers/ClienteController.php?action=view&id=${id}`)
                .then(response => response.json())
                .then(cliente => {
                    document.getElementById('modalTitle').textContent = 'Editar Cliente';
                    document.getElementById('clienteForm').action = '../controllers/ClienteController.php?action=update';
                    document.getElementById('clienteId').value = cliente.id;
                    document.getElementById('empresa').value = cliente.empresa;
                    document.getElementById('nombre').value = cliente.nombre;
                    document.getElementById('apellido').value = cliente.apellido;
                    document.getElementById('direccion').value = cliente.direccion;
                    document.getElementById('telefono').value = cliente.telefono;
                    document.getElementById('correo').value = cliente.correo;
                    
                    // Habilitar campos para edición
                    const formInputs = document.querySelectorAll('#clienteForm input, #clienteForm textarea');
                    formInputs.forEach(input => input.removeAttribute('disabled'));
                    
                    clienteModal.show();
                });
        }

        // Función para confirmar eliminación
        function confirmarEliminar(id) {
            clienteIdToDelete = id;
            deleteModal.show();
        }

        // Evento para eliminar cliente
        document.getElementById('confirmDelete').addEventListener('click', function() {
            if (clienteIdToDelete) {
                window.location.href = `../controllers/ClienteController.php?action=delete&id=${clienteIdToDelete}`;
            }
        });

        // Evento para búsqueda
        document.getElementById('searchButton').addEventListener('click', function() {
            const keyword = document.getElementById('searchInput').value;
            window.location.href = `clientes.php?action=search&keyword=${encodeURIComponent(keyword)}`;
        });

        // Evento para limpiar modal al cerrarlo
        document.getElementById('clienteModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('clienteForm').reset();
            document.getElementById('modalTitle').textContent = 'Nuevo Cliente';
            document.getElementById('clienteForm').action = '../controllers/ClienteController.php?action=create';
            const formInputs = document.querySelectorAll('#clienteForm input, #clienteForm textarea');
            formInputs.forEach(input => input.removeAttribute('disabled'));
        });
    </script>
</body>
</html>
