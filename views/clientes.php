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

// Añadir verificación para debugging
if (empty($clientes)) {
    error_log("No se encontraron clientes en la base de datos o hubo un error al recuperarlos.");
}
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
                        <?php if (!empty($clientes)): ?>
                            <?php foreach ($clientes as $cliente): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($cliente['id']); ?></td>
                                    <td><?php echo htmlspecialchars($cliente['cliente_empresa']); ?></td>
                                    <td><?php echo htmlspecialchars($cliente['cliente_nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($cliente['cliente_apellido']); ?></td>
                                    <td><?php echo htmlspecialchars($cliente['cliente_direccion'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($cliente['cliente_telefono'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($cliente['cliente_correo'] ?? ''); ?></td>
                                    <td>
                                        <span class="badge <?php echo (isset($cliente['estado']) && $cliente['estado'] == 'Inactivo') ? 'bg-danger' : 'bg-success'; ?>">
                                            <?php echo htmlspecialchars(isset($cliente['estado']) ? $cliente['estado'] : 'Activo'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-dark btn-sm editar-cliente" data-bs-toggle="modal" 
                                                data-bs-target="#editarClienteModal"
                                                data-id="<?php echo htmlspecialchars($cliente['id']); ?>"
                                                data-empresa="<?php echo htmlspecialchars($cliente['cliente_empresa']); ?>"
                                                data-nombre="<?php echo htmlspecialchars($cliente['cliente_nombre']); ?>"
                                                data-apellido="<?php echo htmlspecialchars($cliente['cliente_apellido']); ?>"
                                                data-direccion="<?php echo htmlspecialchars($cliente['cliente_direccion'] ?? ''); ?>"
                                                data-telefono="<?php echo htmlspecialchars($cliente['cliente_telefono'] ?? ''); ?>"
                                                data-correo="<?php echo htmlspecialchars($cliente['cliente_correo'] ?? ''); ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn <?php echo (isset($cliente['estado']) && $cliente['estado'] == 'Inactivo') ? 'btn-success' : 'btn-danger'; ?> btn-sm toggle-status"
                                                data-bs-toggle="modal"
                                                data-bs-target="#confirmStatusModal"
                                                data-id="<?php echo htmlspecialchars($cliente['id']); ?>"
                                                data-estado="<?php echo (isset($cliente['estado']) && $cliente['estado'] == 'Inactivo') ? 'Activo' : 'Inactivo'; ?>">
                                            <i class="fas <?php echo (isset($cliente['estado']) && $cliente['estado'] == 'Inactivo') ? 'fa-check' : 'fa-ban'; ?>"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center">No se encontraron clientes</td>
                            </tr>
                        <?php endif; ?>
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

        <!-- Modal para Nuevo Cliente -->
        <div class="modal fade" id="nuevoClienteModal" tabindex="-1" aria-labelledby="nuevoClienteModalLabel" aria-hidden="true">
            <div class="modal-dialog" style="width: 700px;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="nuevoClienteModalLabel">Nuevo Cliente</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="nuevo-cliente-form">
                            <div class="mb-3">
                                <label for="empresa" class="form-label">Empresa*</label>
                                <input type="text" class="form-control" id="empresa" name="cliente_empresa" required>
                            </div>
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre*</label>
                                <input type="text" class="form-control" id="nombre" name="cliente_nombre" required>
                            </div>
                            <div class="mb-3">
                                <label for="apellido" class="form-label">Apellido*</label>
                                <input type="text" class="form-control" id="apellido" name="cliente_apellido" required>
                            </div>
                            <div class="mb-3">
                                <label for="direccion" class="form-label">Dirección</label>
                                <textarea class="form-control" id="direccion" name="cliente_direccion" rows="2"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="text" class="form-control" id="telefono" name="cliente_telefono">
                            </div>
                            <div class="mb-3">
                                <label for="correo" class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control" id="correo" name="cliente_correo">
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-dark" id="guardar-nuevo">Guardar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para Editar Cliente -->
        <div class="modal fade" id="editarClienteModal" tabindex="-1" aria-labelledby="editarClienteModalLabel" aria-hidden="true">
            <div class="modal-dialog" style="width: 700px;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editarClienteModalLabel">Editar Cliente</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editar-cliente-form">
                            <input type="hidden" id="editar-id" name="id">
                            <div class="mb-3">
                                <label for="editar-empresa" class="form-label">Empresa*</label>
                                <input type="text" class="form-control" id="editar-empresa" name="cliente_empresa" required>
                            </div>
                            <div class="mb-3">
                                <label for="editar-nombre" class="form-label">Nombre*</label>
                                <input type="text" class="form-control" id="editar-nombre" name="cliente_nombre" required>
                            </div>
                            <div class="mb-3">
                                <label for="editar-apellido" class="form-label">Apellido*</label>
                                <input type="text" class="form-control" id="editar-apellido" name="cliente_apellido" required>
                            </div>
                            <div class="mb-3">
                                <label for="editar-direccion" class="form-label">Dirección</label>
                                <textarea class="form-control" id="editar-direccion" name="cliente_direccion" rows="2"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="editar-telefono" class="form-label">Teléfono</label>
                                <input type="text" class="form-control" id="editar-telefono" name="cliente_telefono">
                            </div>
                            <div class="mb-3">
                                <label for="editar-correo" class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control" id="editar-correo" name="cliente_correo">
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-dark" id="guardar-edicion">Guardar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para Confirmar Cambio de Estado -->
        <div class="modal fade" id="confirmStatusModal" tabindex="-1" aria-labelledby="confirmStatusModalLabel" aria-hidden="true">
            <div class="modal-dialog" style="width: 700px;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmStatusModalLabel">Confirmar Cambio de Estado</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>¿Está seguro de que desea cambiar el estado de este cliente?</p>
                        <form id="status-form">
                            <input type="hidden" id="status-id" name="id">
                            <input type="hidden" id="status-estado" name="estado">
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-dark" id="confirmar-estado">Confirmar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Éxito -->
        <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Éxito</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p id="success-message">Operación completada con éxito.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../components/footer.php'; ?>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script>
        $(document).ready(function() {
            
            // Variables para paginación
            let registrosPorPagina = parseInt($('#registros-por-pagina').val());
            let paginaActual = 1;
            let totalPaginas = 1;
            
            // Filtrar la tabla cuando se ingresan datos en los filtros
            $('.filtro').on('input change', function() {
                actualizarFilasVisibles();
            });
            
            // Limpiar filtros
            $('#limpiar-filtros').click(function() {
                $('.filtro').val('');
                actualizarFilasVisibles();
            });
            
            // Función para actualizar las filas visibles según los filtros
            function actualizarFilasVisibles() {
                let empresaFiltro = $('#filtro-empresa').val().toLowerCase();
                let nombreFiltro = $('#filtro-nombre').val().toLowerCase();
                let apellidoFiltro = $('#filtro-apellido').val().toLowerCase();
                let estadoFiltro = $('#filtro-estado').val();
                
                let filas = $('#tabla-clientes tbody tr');
                let contadorVisible = 0;
                
                filas.each(function(index, fila) {
                    let empresa = $(fila).find('td:eq(1)').text().toLowerCase();
                    let nombre = $(fila).find('td:eq(2)').text().toLowerCase();
                    let apellido = $(fila).find('td:eq(3)').text().toLowerCase();
                    let estado = $(fila).find('td:eq(7)').text().trim();
                    
                    let coincideEmpresa = empresa.includes(empresaFiltro);
                    let coincideNombre = nombre.includes(nombreFiltro);
                    let coincideApellido = apellido.includes(apellidoFiltro);
                    let coincideEstado = estadoFiltro === "" || estado === estadoFiltro;
                    
                    if (coincideEmpresa && coincideNombre && coincideApellido && coincideEstado) {
                        contadorVisible++;
                        let paginaRow = Math.ceil(contadorVisible / registrosPorPagina);
                        $(fila).addClass('filtered-row');
                        
                        if (paginaRow === paginaActual) {
                            $(fila).show();
                        } else {
                            $(fila).hide();
                        }
                    } else {
                        $(fila).removeClass('filtered-row');
                        $(fila).hide();
                    }
                });
                
                // Actualizar paginación
                totalPaginas = Math.max(1, Math.ceil($('.filtered-row').length / registrosPorPagina));
                actualizarPaginacion();
                
                // Si no hay registros que coincidan con el filtro
                if ($('.filtered-row').length === 0) {
                    if ($('#tabla-clientes tbody tr.no-results').length === 0) {
                        $('#tabla-clientes tbody').append('<tr class="no-results"><td colspan="9" class="text-center">No se encontraron registros que coincidan con los filtros</td></tr>');
                    }
                } else {
                    $('#tabla-clientes tbody tr.no-results').remove();
                }
            }
            
            // Función para actualizar la información de paginación
            function actualizarPaginacion() {
                $('#info-pagina').text('Página ' + paginaActual + ' de ' + totalPaginas);
                
                if (paginaActual <= 1) {
                    $('#anterior-pagina').addClass('disabled');
                } else {
                    $('#anterior-pagina').removeClass('disabled');
                }
                
                if (paginaActual >= totalPaginas) {
                    $('#siguiente-pagina').addClass('disabled');
                } else {
                    $('#siguiente-pagina').removeClass('disabled');
                }
            }
            
            // Evento para cambiar registros por página
            $('#registros-por-pagina').change(function() {
                registrosPorPagina = parseInt($(this).val());
                paginaActual = 1;
                actualizarFilasVisibles();
            });
            
            // Eventos de paginación
            $('#anterior-pagina').click(function(e) {
                e.preventDefault();
                if (paginaActual > 1) {
                    paginaActual--;
                    actualizarFilasVisibles();
                }
            });
            
            $('#siguiente-pagina').click(function(e) {
                e.preventDefault();
                if (paginaActual < totalPaginas) {
                    paginaActual++;
                    actualizarFilasVisibles();
                }
            });
            
            // Ordenar por columnas
            $('.sortable').click(function() {
                let column = $(this).data('column');
                let currentDir = $(this).hasClass('asc') ? 'desc' : 'asc';
                
                // Restablecer otras columnas
                $('.sortable').not(this).removeClass('asc desc');
                $(this).removeClass('asc desc').addClass(currentDir);
                
                // Ordenar la tabla
                let rows = $('#tabla-clientes tbody tr').toArray();
                rows.sort(function(a, b) {
                    let aVal, bVal;
                    
                    if (column === 'id') {
                        aVal = parseInt($(a).find('td:eq(0)').text());
                        bVal = parseInt($(b).find('td:eq(0)').text());
                    } else if (column === 'empresa') {
                        aVal = $(a).find('td:eq(1)').text().toLowerCase();
                        bVal = $(b).find('td:eq(1)').text().toLowerCase();
                    } else if (column === 'nombre') {
                        aVal = $(a).find('td:eq(2)').text().toLowerCase();
                        bVal = $(b).find('td:eq(2)').text().toLowerCase();
                    } else if (column === 'apellido') {
                        aVal = $(a).find('td:eq(3)').text().toLowerCase();
                        bVal = $(b).find('td:eq(3)').text().toLowerCase();
                    } else if (column === 'estado') {
                        aVal = $(a).find('td:eq(7)').text().trim();
                        bVal = $(b).find('td:eq(7)').text().trim();
                    }
                    
                    if (currentDir === 'asc') {
                        return aVal > bVal ? 1 : -1;
                    } else {
                        return aVal < bVal ? 1 : -1;
                    }
                });
                
                // Volver a añadir filas ordenadas
                $.each(rows, function(index, row) {
                    $('#tabla-clientes tbody').append(row);
                });
                
                // Restablecer paginación y filtros
                paginaActual = 1;
                actualizarFilasVisibles();
            });
            
            // Inicialización
            actualizarFilasVisibles();
            
            // Guardar nuevo cliente
            $('#guardar-nuevo').click(function() {
                let formData = $('#nuevo-cliente-form').serializeArray();
                formData.push({name: 'action', value: 'create'});
                
                $.ajax({
                    url: '../controllers/ClienteController.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#nuevoClienteModal').modal('hide');
                            $('#success-message').text('Cliente creado exitosamente.');
                            $('#successModal').modal('show');
                            
                            // Recargar la página después de cerrar el modal de éxito
                            $('#successModal').on('hidden.bs.modal', function () {
                                location.reload();
                            });
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Error al procesar la solicitud.');
                    }
                });
            });
            
            // Cargar datos para editar
            $('.editar-cliente').click(function() {
                $('#editar-id').val($(this).data('id'));
                $('#editar-empresa').val($(this).data('empresa'));
                $('#editar-nombre').val($(this).data('nombre'));
                $('#editar-apellido').val($(this).data('apellido'));
                $('#editar-direccion').val($(this).data('direccion'));
                $('#editar-telefono').val($(this).data('telefono'));
                $('#editar-correo').val($(this).data('correo'));
            });
            
            // Guardar edición de cliente
            $('#guardar-edicion').click(function() {
                let formData = $('#editar-cliente-form').serializeArray();
                formData.push({name: 'action', value: 'update'});
                
                $.ajax({
                    url: '../controllers/ClienteController.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#editarClienteModal').modal('hide');
                            $('#success-message').text('Cliente actualizado exitosamente.');
                            $('#successModal').modal('show');
                            
                            // Recargar la página después de cerrar el modal de éxito
                            $('#successModal').on('hidden.bs.modal', function () {
                                location.reload();
                            });
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Error al procesar la solicitud.');
                    }
                });
            });
            
            // Cargar datos para cambiar estado
            $('.toggle-status').click(function() {
                $('#status-id').val($(this).data('id'));
                $('#status-estado').val($(this).data('estado'));
            });
            
            // Confirmar cambio de estado
            $('#confirmar-estado').click(function() {
                let formData = $('#status-form').serializeArray();
                formData.push({name: 'action', value: 'toggleStatus'});
                
                $.ajax({
                    url: '../controllers/ClienteController.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#confirmStatusModal').modal('hide');
                            $('#success-message').text('Estado de cliente actualizado exitosamente.');
                            $('#successModal').modal('show');
                            
                            // Recargar la página después de cerrar el modal de éxito
                            $('#successModal').on('hidden.bs.modal', function () {
                                location.reload();
                            });
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Error al procesar la solicitud.');
                    }
                });
            });
        });
    </script>
</body>
</html>
