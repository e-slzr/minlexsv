<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MINLEX | Clientes</title>
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
            <h1><strong>Gestión de Clientes</strong></h1><br>
            <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#clienteModal">
                Nuevo Cliente
            </button>
        </div>

        <!-- Filtros -->
        <div class="filtrar">
            <input type="text" id="searchInput" class="form-control" placeholder="Buscar cliente...">
        </div>

        <!-- Tabla de Clientes -->
        <div class="table table-responsive">
            <table class="table table-striped table-hover" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Empresa</th>
                        <th>Nombre</th>
                        <th>Teléfono</th>
                        <th>Correo</th>
                        <th>Estado</th>
                        <th>Opciones</th>
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
                            <span class="<?php echo $cliente['estado'] === 'Activo' ? 'color-activo' : 'color-inactivo'; ?>">
                                <?php echo htmlspecialchars($cliente['estado']); ?>
                            </span>
                        </td>
                        <td>
                            <button type="button" class="btn btn-dark edit-cliente" 
                                    data-id="<?php echo $cliente['id']; ?>"
                                    data-bs-toggle="modal" data-bs-target="#clienteModal">
                                <svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M14 6L16.2929 3.70711C16.6834 3.31658 17.3166 3.31658 17.7071 3.70711L20.2929 6.29289C20.6834 6.68342 20.6834 7.31658 20.2929 7.70711L18 10M14 6L4.29289 15.7071C4.10536 15.8946 4 16.149 4 16.4142V19C4 19.5523 4.44772 20 5 20H7.58579C7.851 20 8.10536 19.8946 8.29289 19.7071L18 10M14 6L18 10" stroke="white" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                                </svg>
                            </button>
                            <button type="button" class="btn btn-warning toggle-status" 
                                    data-id="<?php echo $cliente['id']; ?>"
                                    data-nombre="<?php echo htmlspecialchars($cliente['cliente_nombre'] . ' ' . $cliente['cliente_apellido']); ?>"
                                    data-estado="<?php echo htmlspecialchars($cliente['estado']); ?>">
                                <?php if ($cliente['estado'] === 'Activo'): ?>
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

        <!-- Modal para Crear/Editar Cliente -->
        <div class="modal fade" id="clienteModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Nuevo Cliente</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="clienteForm">
                        <div class="modal-body">
                            <input type="hidden" name="action" id="formAction" value="create">
                            <input type="hidden" name="id" id="clienteId">
                            
                            <div class="form-group mb-3">
                                <label for="empresa">Empresa</label>
                                <input type="text" class="form-control" id="empresa" name="empresa" required>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col">
                                    <label for="nombre">Nombre</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                                </div>
                                <div class="col">
                                    <label for="apellido">Apellido</label>
                                    <input type="text" class="form-control" id="apellido" name="apellido" required>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="direccion">Dirección</label>
                                <textarea class="form-control" id="direccion" name="direccion" rows="2"></textarea>
                            </div>

                            <div class="form-group mb-3">
                                <label for="telefono">Teléfono</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono">
                            </div>

                            <div class="form-group mb-3">
                                <label for="correo">Correo Electrónico</label>
                                <input type="email" class="form-control" id="correo" name="correo">
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
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-dark">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal para Cambiar Estado -->
        <div class="modal fade" id="toggleStatusModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmar Cambio de Estado</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>¿Está seguro que desea <span id="actionType"></span> el cliente <strong><span id="clienteName"></span></strong>?</p>
                        <div class="alert alert-warning">
                            <strong>Nota:</strong> Si deshabilita el cliente, este no estará disponible para nuevas operaciones.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-dark" id="confirmToggleStatus">Confirmar</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Búsqueda en tiempo real
            $("#searchInput").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("table tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });

            // Manejar el envío del formulario
            $('#clienteForm').on('submit', function(e) {
                e.preventDefault();
                
                var formData = $(this).serialize();
                
                $.ajax({
                    url: '../controllers/ClienteController.php',
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        var res = JSON.parse(response);
                        if (res.success) {
                            location.reload();
                        } else {
                            alert('Error: ' + res.message);
                        }
                    },
                    error: function() {
                        alert('Error al procesar la solicitud');
                    }
                });
            });

            // Editar cliente
            $('.edit-cliente').on('click', function() {
                var id = $(this).data('id');
                $('#formAction').val('update');
                $('#clienteId').val(id);
                
                $.ajax({
                    url: '../controllers/ClienteController.php',
                    type: 'GET',
                    data: { action: 'get', id: id },
                    success: function(response) {
                        var cliente = JSON.parse(response);
                        $('#empresa').val(cliente.empresa);
                        $('#nombre').val(cliente.nombre);
                        $('#apellido').val(cliente.apellido);
                        $('#direccion').val(cliente.direccion);
                        $('#telefono').val(cliente.telefono);
                        $('#correo').val(cliente.correo);
                        $('#estado').val(cliente.estado);
                        $('#modalTitle').text('Editar Cliente');
                    }
                });
            });

            // Toggle estado
            $('.toggle-status').on('click', function() {
                var id = $(this).data('id');
                var nombre = $(this).data('nombre');
                var estado = $(this).data('estado');
                var nuevoEstado = estado === 'Activo' ? 'Inactivo' : 'Activo';
                
                $('#actionType').text(estado === 'Activo' ? 'desactivar' : 'activar');
                $('#clienteName').text(nombre);
                
                var modal = new bootstrap.Modal(document.getElementById('toggleStatusModal'));
                modal.show();
                
                $('#confirmToggleStatus').off('click').on('click', function() {
                    $.ajax({
                        url: '../controllers/ClienteController.php',
                        type: 'POST',
                        data: {
                            action: 'toggleStatus',
                            id: id,
                            estado: nuevoEstado
                        },
                        success: function(response) {
                            var res = JSON.parse(response);
                            if (res.success) {
                                location.reload();
                            } else {
                                alert('Error: ' + res.message);
                            }
                        }
                    });
                });
            });

            // Limpiar modal al cerrarlo
            $('#clienteModal').on('hidden.bs.modal', function() {
                $('#clienteForm')[0].reset();
                $('#formAction').val('create');
                $('#clienteId').val('');
                $('#modalTitle').text('Nuevo Cliente');
            });
        });
    </script>
</body>
<?php include '../components/footer.php'; ?>
</html>
