$(document).ready(function() {
    // Variables globales
    let currentPage = 1;
    const defaultRecordsPerPage = 10;
    let recordsPerPage = defaultRecordsPerPage;
    let sortColumn = 'id';
    let sortDirection = 'desc';

    // Función para cargar las órdenes
    function loadOrdenes(page = 1) {
        const filters = {
            item_numero: $('#item_numero').val(),
            operador: $('#operador').val(),
            estado: $('#estado').val(),
            fecha_inicio: $('#fecha_inicio').val(),
            fecha_fin: $('#fecha_fin').val()
        };

        $.ajax({
            url: '../controllers/OrdenProduccionController.php',
            type: 'GET',
            data: {
                action: 'search',
                page: page,
                ...filters
            },
            success: function(response) {
                if (response.success) {
                    updateTable(response.ordenes);
                    updatePagination(response.total, page);
                } else {
                    alert('Error al cargar las órdenes: ' + response.message);
                }
            },
            error: function() {
                alert('Error al conectar con el servidor');
            }
        });
    }

    // Función para actualizar la tabla
    function updateTable(ordenes) {
        const tbody = $('#tabla-ordenes tbody');
        tbody.empty();

        if (ordenes.length === 0) {
            tbody.append('<tr><td colspan="10" class="text-center">No se encontraron órdenes de producción</td></tr>');
            return;
        }

        ordenes.forEach(orden => {
            const row = $('<tr>');
            
            // Formatear fechas
            const fechaInicio = new Date(orden.op_fecha_inicio).toLocaleDateString('es-ES');
            const fechaFin = orden.op_fecha_fin ? new Date(orden.op_fecha_fin).toLocaleDateString('es-ES') : 'No definida';
            
            // Determinar clase de badge para el estado
            let badgeClass = '';
            switch (orden.op_estado) {
                case 'Pendiente':
                    badgeClass = 'bg-warning';
                    break;
                case 'En proceso':
                    badgeClass = 'bg-primary';
                    break;
                case 'Completado':
                    badgeClass = 'bg-success';
                    break;
            }

            row.html(`
                <td>${orden.id}</td>
                <td>${orden.item_numero} - ${orden.item_nombre}</td>
                <td>${orden.proceso_nombre}</td>
                <td>${orden.usuario_nombre} ${orden.usuario_apellido}</td>
                <td>${orden.op_cantidad_asignada}</td>
                <td>${orden.op_cantidad_completada}</td>
                <td>${fechaInicio}</td>
                <td>${fechaFin}</td>
                <td><span class="badge ${badgeClass}">${orden.op_estado}</span></td>
                <td>
                    <button type="button" class="btn btn-light view-orden me-1" data-id="${orden.id}" data-bs-toggle="modal" data-bs-target="#ordenDetailModal">
                        <svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M2 12C2 12 5.63636 5 12 5C18.3636 5 22 12 22 12C22 12 18.3636 19 12 19C5.63636 19 2 12 2 12Z" stroke="black" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                            <path d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z" stroke="black" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                        </svg>
                    </button>
                    <button type="button" class="btn btn-light edit-orden me-1" data-id="${orden.id}" data-bs-toggle="modal" data-bs-target="#ordenModal">
                        <svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M14 6L16.2929 3.70711C16.6834 3.31658 17.3166 3.31658 17.7071 3.70711L20.2929 6.29289C20.6834 6.68342 20.6834 7.31658 20.2929 7.70711L18 10M14 6L4.29289 15.7071C4.10536 15.8946 4 16.149 4 16.4142V19C4 19.5523 4.44772 20 5 20H7.58579C7.851 20 8.10536 19.8946 8.29289 19.7071L18 10M14 6L18 10" stroke="black" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                        </svg>
                    </button>
                    <button type="button" class="btn btn-light delete-orden" data-id="${orden.id}" data-bs-toggle="modal" data-bs-target="#deleteOrdenModal">
                        <svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M19 7L18.1327 19.1425C18.0579 20.1891 17.187 21 16.1378 21H7.86224C6.81296 21 5.94208 20.1891 5.86732 19.1425L5 7M10 11V17M14 11V17M15 7V4C15 3.44772 14.5523 3 14 3H10C9.44772 3 9 3.44772 9 4V7M4 7H20" stroke="#FF0000" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                        </svg>
                    </button>
                </td>
            `);
            tbody.append(row);
        });
    }

    // Función para actualizar la paginación
    function updatePagination(total, currentPage) {
        const totalPages = Math.ceil(total / recordsPerPage);
        const pagination = $('#paginacion');
        pagination.empty();

        // Actualizar información de registros
        $('#registros-mostrados').text(Math.min(recordsPerPage, total));
        $('#registros-totales').text(total);

        // Botón anterior
        pagination.append(`
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage - 1}">Anterior</a>
            </li>
        `);

        // Páginas
        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
                pagination.append(`
                    <li class="page-item ${i === currentPage ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                    </li>
                `);
            } else if (i === currentPage - 3 || i === currentPage + 3) {
                pagination.append('<li class="page-item disabled"><a class="page-link">...</a></li>');
            }
        }

        // Botón siguiente
        pagination.append(`
            <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage + 1}">Siguiente</a>
            </li>
        `);
    }

    // Event Listeners
    
    // Cambio en los filtros
    $('.filtro').on('change', function() {
        currentPage = 1;
        loadOrdenes(currentPage);
    });

    // Limpiar filtros
    $('#limpiar-filtros').on('click', function() {
        $('#item_numero').val('');
        $('#operador').val('');
        $('#estado').val('');
        $('#fecha_inicio').val('');
        $('#fecha_fin').val('');
        currentPage = 1;
        loadOrdenes(currentPage);
    });

    // Cambio en registros por página
    $('#registros-por-pagina').on('change', function() {
        recordsPerPage = parseInt($(this).val());
        currentPage = 1;
        loadOrdenes(currentPage);
    });

    // Paginación
    $(document).on('click', '.page-link', function(e) {
        e.preventDefault();
        const page = $(this).data('page');
        if (page) {
            currentPage = page;
            loadOrdenes(currentPage);
        }
    });

    // Ordenamiento de columnas
    $('.sortable').on('click', function() {
        const column = $(this).data('column');
        if (column === sortColumn) {
            sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            sortColumn = column;
            sortDirection = 'asc';
        }
        loadOrdenes(currentPage);
    });

    // Modal de Nueva/Editar Orden
    $('#ordenModal').on('show.bs.modal', function(e) {
        const button = $(e.relatedTarget);
        const id = button.data('id');
        const modal = $(this);
        
        // Limpiar el formulario
        $('#ordenForm')[0].reset();
        
        if (id) {
            // Modo edición
            modal.find('.modal-title').text('Editar Orden de Producción');
            
            // Cargar datos de la orden
            $.ajax({
                url: '../controllers/OrdenProduccionController.php',
                type: 'GET',
                data: {
                    action: 'getOrdenInfo',
                    id: id
                },
                success: function(response) {
                    if (response.success) {
                        const orden = response.orden;
                        $('#ordenId').val(orden.id);
                        $('#poDetalle').val(orden.op_id_pd);
                        $('#proceso').val(orden.op_id_proceso);
                        $('#operadorAsignado').val(orden.op_operador_asignado);
                        $('#ordenEstado').val(orden.op_estado);
                        $('#cantidadAsignada').val(orden.op_cantidad_asignada);
                        $('#cantidadCompletada').val(orden.op_cantidad_completada);
                        $('#fechaInicio').val(orden.op_fecha_inicio);
                        $('#fechaFin').val(orden.op_fecha_fin);
                        $('#comentario').val(orden.op_comentario);
                    } else {
                        alert('Error al cargar la información de la orden: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error al conectar con el servidor');
                }
            });
        } else {
            // Modo creación
            modal.find('.modal-title').text('Nueva Orden de Producción');
            $('#ordenId').val('');
        }
    });

    // Guardar Orden
    $('#saveOrden').on('click', function() {
        const formData = new FormData($('#ordenForm')[0]);
        const id = $('#ordenId').val();
        formData.append('action', id ? 'update' : 'create');

        $.ajax({
            url: '../controllers/OrdenProduccionController.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#ordenModal').modal('hide');
                    loadOrdenes(currentPage);
                } else {
                    alert('Error al guardar la orden: ' + response.message);
                }
            },
            error: function() {
                alert('Error al conectar con el servidor');
            }
        });
    });

    // Modal de Ver Detalles
    $('#ordenDetailModal').on('show.bs.modal', function(e) {
        const button = $(e.relatedTarget);
        const id = button.data('id');
        
        $.ajax({
            url: '../controllers/OrdenProduccionController.php',
            type: 'GET',
            data: {
                action: 'getOrdenInfo',
                id: id
            },
            success: function(response) {
                if (response.success) {
                    const orden = response.orden;
                    $('#detailItem').text(`${orden.item_numero} - ${orden.item_nombre}`);
                    $('#detailProceso').text(orden.proceso_nombre);
                    $('#detailOperador').text(`${orden.usuario_nombre} ${orden.usuario_apellido}`);
                    $('#detailEstado').text(orden.op_estado);
                    $('#detailCantidadAsignada').text(orden.op_cantidad_asignada);
                    $('#detailCantidadCompletada').text(orden.op_cantidad_completada);
                    $('#detailFechaInicio').text(new Date(orden.op_fecha_inicio).toLocaleDateString('es-ES'));
                    $('#detailFechaFin').text(orden.op_fecha_fin ? new Date(orden.op_fecha_fin).toLocaleDateString('es-ES') : 'No definida');
                    $('#detailComentario').text(orden.op_comentario || 'Sin comentarios');
                } else {
                    alert('Error al cargar los detalles de la orden: ' + response.message);
                }
            },
            error: function() {
                alert('Error al conectar con el servidor');
            }
        });
    });

    // Modal de Eliminar
    $('#deleteOrdenModal').on('show.bs.modal', function(e) {
        const button = $(e.relatedTarget);
        const id = button.data('id');
        $('#deleteOrdenId').val(id);
    });

    // Confirmar Eliminación
    $('#confirmDelete').on('click', function() {
        const formData = new FormData($('#deleteOrdenForm')[0]);
        formData.append('action', 'delete');

        $.ajax({
            url: '../controllers/OrdenProduccionController.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#deleteOrdenModal').modal('hide');
                    loadOrdenes(currentPage);
                } else {
                    alert('Error al eliminar la orden: ' + response.message);
                }
            },
            error: function() {
                alert('Error al conectar con el servidor');
            }
        });
    });

    // Cargar órdenes inicialmente
    loadOrdenes();
});
