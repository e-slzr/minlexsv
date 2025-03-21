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
            <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#rolModal">
                <i class="fas fa-plus"></i> Nuevo Rol
            </button>
        </div>

        <div class="container-fluid">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
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
                                            data-bs-target="#rolModal">
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

        <!-- Modal de Rol -->
        <div class="modal fade" id="rolModal" tabindex="-1" aria-labelledby="rolModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="rolModalLabel">Nuevo Rol</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="rolForm">
                            <input type="hidden" id="rolId" name="id">
                            <input type="hidden" id="formAction" name="action" value="create">

                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre*</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                            </div>

                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripción*</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" required></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" form="rolForm" class="btn btn-dark">Guardar</button>
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

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        // Función para mostrar modal de éxito
        function showSuccessModal(message) {
            $('#successMessage').text(message);
            var modal = new bootstrap.Modal(document.getElementById('successModal'));
            modal.show();
            // Limpiar eventos anteriores
            $('#successModal').off('hidden.bs.modal');
            // Agregar nuevo evento
            $('#successModal').on('hidden.bs.modal', function () {
                location.reload();
            });
        }

        // Función para mostrar modal de error
        function showErrorModal(message) {
            $('#errorMessage').text(message);
            var modal = new bootstrap.Modal(document.getElementById('errorModal'));
            modal.show();
        }

        // Editar rol
        $('.edit-rol').click(function() {
            var id = $(this).data('id');
            var nombre = $(this).data('nombre');
            var descripcion = $(this).data('descripcion');
            
            $('#formAction').val('update');
            $('#rolId').val(id);
            $('#nombre').val(nombre);
            $('#descripcion').val(descripcion);
            $('#rolModalLabel').text('Editar Rol');
        });

        // Manejar cambio de estado
        $('.toggle-status').click(function(e) {
            e.preventDefault();
            var rolId = $(this).data('id');
            var estadoActual = $(this).data('estado');
            
            $('#confirmStatusModal').modal('show');
            
            // Limpiar eventos anteriores
            $('#confirmStatusBtn').off('click');
            
            // Agregar nuevo evento
            $('#confirmStatusBtn').on('click', function() {
                $.ajax({
                    url: '../controllers/RolController.php',
                    type: 'POST',
                    data: {
                        action: 'toggleStatus',
                        id: rolId,
                        estado: estadoActual === 'Activo' ? 'Inactivo' : 'Activo'
                    },
                    dataType: 'json',
                    success: function(response) {
                        $('#confirmStatusModal').modal('hide');
                        if (response.success) {
                            showSuccessModal(response.message);
                        } else {
                            showErrorModal(response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#confirmStatusModal').modal('hide');
                        showErrorModal('Error al procesar la solicitud: ' + error);
                    }
                });
            });
        });

        // Manejar envío del formulario
        $('#rolForm').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: '../controllers/RolController.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#rolModal').modal('hide');
                        showSuccessModal(response.message);
                    } else {
                        showErrorModal(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    showErrorModal('Error al procesar la solicitud: ' + error);
                }
            });
        });

        // Limpiar modal al cerrarlo
        $('#rolModal').on('hidden.bs.modal', function() {
            $('#rolForm')[0].reset();
            $('#formAction').val('create');
            $('#rolId').val('');
            $('#rolModalLabel').text('Nuevo Rol');
        });

        // Búsqueda en tiempo real
        $('#searchInput').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('table tbody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });
    });
    </script>
</body>
</html>