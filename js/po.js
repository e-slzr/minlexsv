$(document).ready(function() {
    // Variables globales
    let currentPoId = null;
    let items = [];
    let currentSortColumn = '';
    let currentSortDirection = 'asc';
    let currentPage = 1;
    let rowsPerPage = 10;
    let filteredRows = [];

    // Inicializar paginación
    initPagination();

    // Cargar items al inicio
    $.get('../controllers/ItemController.php?action=getItems', function(response) {
        if (response.success) {
            items = response.data;
            const select = $('#detailItem');
            items.forEach(item => {
                select.append(`<option value="${item.id}">${item.item_numero} - ${item.item_nombre}</option>`);
            });
        }
    });

    // Evento para crear/editar PO
    $('#savePo').click(function() {
        const formData = new FormData($('#poForm')[0]);
        const action = currentPoId ? 'update' : 'create';
        formData.append('action', action);

        $.ajax({
            url: '../controllers/PoController.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    window.location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Error al procesar la solicitud');
            }
        });
    });

    // Evento para mostrar modal de edición
    $('.edit-po').click(function() {
        const poId = $(this).data('id');
        currentPoId = poId;
        
        $.get(`../controllers/PoController.php?action=getPoInfo&id=${poId}`, function(response) {
            if (response.success) {
                const po = response.po;
                $('#poModalLabel').text('Editar PO');
                $('#poId').val(po.id);
                $('#poNumero').val(po.po_numero).prop('readonly', true);
                $('#poCliente').val(po.po_id_cliente);
                $('#poFechaInicio').val(po.po_fecha_inicio_produccion);
                $('#poFechaFin').val(po.po_fecha_fin_produccion);
                $('#poFechaEnvio').val(po.po_fecha_envio_programada);
                $('#poEstado').val(po.po_estado);
                $('#poTipoEnvio').val(po.po_tipo_envio);
                $('#poComentario').val(po.po_comentario);
                $('#poNotas').val(po.po_notas);
            }
        });
    });

    // Evento para mostrar modal de creación
    $('[data-target="#poModal"]').not('.edit-po').click(function() {
        currentPoId = null;
        $('#poModalLabel').text('Nueva PO');
        $('#poForm')[0].reset();
        $('#poNumero').prop('readonly', false);
    });

    // Evento para ver detalles de PO
    $('.view-po').click(function() {
        const poId = $(this).data('id');
        currentPoId = poId;
        
        $.get(`../controllers/PoController.php?action=getPoInfo&id=${poId}`, function(response) {
            if (response.success) {
                const po = response.po;
                const detalles = response.detalles;
                
                // Información general
                $('#detailPoNumero').text(po.po_numero);
                $('#detailCliente').text(po.cliente_empresa);
                $('#detailEstado').html(`<span class="badge ${getBadgeClass(po.po_estado)}">${po.po_estado}</span>`);
                $('#detailFechaCreacion').text(po.po_fecha_creacion);
                $('#detailFechaInicio').text(po.po_fecha_inicio_produccion || 'No definida');
                $('#detailFechaFin').text(po.po_fecha_fin_produccion || 'No definida');
                $('#detailFechaEnvio').text(po.po_fecha_envio_programada || 'No definida');
                $('#detailTipoEnvio').text(po.po_tipo_envio);
                $('#detailComentario').text(po.po_comentario || 'Sin comentarios');
                $('#detailNotas').text(po.po_notas || 'Sin notas');
                
                // Detalles de items
                const tbody = $('#detailsTableBody');
                tbody.empty();
                
                detalles.forEach(detalle => {
                    const subtotal = (detalle.pd_cant_piezas_total * detalle.pd_precio_unitario).toFixed(2);
                    tbody.append(`
                        <tr>
                            <td>${detalle.item_numero} - ${detalle.item_nombre}</td>
                            <td>${detalle.pd_cant_piezas_total}</td>
                            <td>${detalle.pd_pcs_carton || '-'}</td>
                            <td>${detalle.pd_pcs_poly || '-'}</td>
                            <td>$${detalle.pd_precio_unitario}</td>
                            <td>$${subtotal}</td>
                            <td><span class="badge ${getBadgeClass(detalle.pd_estado)}">${detalle.pd_estado}</span></td>
                            <td>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" 
                                         style="width: ${detalle.progreso}%"
                                         aria-valuenow="${detalle.progreso}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        ${detalle.progreso}%
                                    </div>
                                </div>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-success edit-detail" 
                                        data-id="${detalle.id}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger delete-detail"
                                        data-id="${detalle.id}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `);
                });
                
                $('#detailTotal').text('$' + response.total.toFixed(2));
            }
        });
    });

    // Evento para agregar detalle
    $('#addDetailBtn').click(function() {
        $('#detailModalLabel').text('Agregar Item');
        $('#detailForm')[0].reset();
        $('#detailId').val('');
        $('#detailPoId').val(currentPoId);
        $('#detailModal').modal('show');
    });

    // Evento para editar detalle
    $(document).on('click', '.edit-detail', function() {
        const detalleId = $(this).data('id');
        $('#detailModalLabel').text('Editar Item');
        
        $.get(`../controllers/PoController.php?action=getPoDetails&po_id=${currentPoId}`, function(response) {
            if (response.success) {
                const detalle = response.data.find(d => d.id == detalleId);
                if (detalle) {
                    $('#detailId').val(detalle.id);
                    $('#detailPoId').val(detalle.pd_id_po);
                    $('#detailItem').val(detalle.pd_item);
                    $('#detailCantidad').val(detalle.pd_cant_piezas_total);
                    $('#detailPcsCarton').val(detalle.pd_pcs_carton);
                    $('#detailPcsPoly').val(detalle.pd_pcs_poly);
                    $('#detailPrecio').val(detalle.pd_precio_unitario);
                    $('#detailEstado').val(detalle.pd_estado);
                    $('#detailModal').modal('show');
                }
            }
        });
    });

    // Evento para guardar detalle
    $('#saveDetail').click(function() {
        const formData = new FormData($('#detailForm')[0]);
        const action = $('#detailId').val() ? 'updateDetail' : 'addDetail';
        formData.append('action', action);

        $.ajax({
            url: '../controllers/PoController.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#detailModal').modal('hide');
                    // Recargar detalles
                    $('.view-po[data-id="' + currentPoId + '"]').click();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Error al procesar la solicitud');
            }
        });
    });

    // Evento para mostrar modal de eliminación de PO
    $('.delete-po').click(function() {
        const poId = $(this).data('id');
        const poNumero = $(this).data('po-numero');
        $('#deletePoId').val(poId);
        $('#deletePoNumero').text(poNumero);
    });

    // Evento para confirmar eliminación de PO
    $('#confirmDelete').click(function() {
        const formData = new FormData($('#deletePoForm')[0]);
        formData.append('action', 'delete');

        $.ajax({
            url: '../controllers/PoController.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    window.location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Error al procesar la solicitud');
            }
        });
    });

    // Evento para mostrar modal de eliminación de detalle
    $(document).on('click', '.delete-detail', function() {
        const detalleId = $(this).data('id');
        $('#deleteDetailId').val(detalleId);
        $('#deleteDetailModal').modal('show');
    });

    // Evento para confirmar eliminación de detalle
    $('#confirmDeleteDetail').click(function() {
        const formData = new FormData($('#deleteDetailForm')[0]);
        formData.append('action', 'deleteDetail');

        $.ajax({
            url: '../controllers/PoController.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#deleteDetailModal').modal('hide');
                    // Recargar detalles
                    $('.view-po[data-id="' + currentPoId + '"]').click();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Error al procesar la solicitud');
            }
        });
    });

    // Función auxiliar para obtener la clase de badge según el estado
    function getBadgeClass(estado) {
        switch(estado) {
            case 'Pendiente': return 'bg-warning';
            case 'En proceso': return 'bg-primary';
            case 'Completada': case 'Completado': return 'bg-success';
            case 'Cancelada': return 'bg-danger';
            default: return 'bg-secondary';
        }
    }
    
    // Ordenamiento de columnas
    $('.sortable').click(function() {
        const column = $(this).data('column');
        
        // Cambiar dirección si es la misma columna
        if (column === currentSortColumn) {
            currentSortDirection = currentSortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            currentSortColumn = column;
            currentSortDirection = 'asc';
        }
        
        // Actualizar iconos de ordenamiento
        $('.sortable i').removeClass('fa-sort-up fa-sort-down').addClass('fa-sort');
        $(this).find('i').removeClass('fa-sort').addClass(currentSortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down');
        
        // Ordenar tabla
        sortTable(column, currentSortDirection);
        
        // Actualizar paginación después de ordenar
        currentPage = 1;
        updatePagination();
    });
    
    // Función para ordenar la tabla
    function sortTable(column, direction) {
        const table = $('#tabla-pos');
        const tbody = table.find('tbody');
        const rows = tbody.find('tr').toArray();
        
        // Mapeo de columnas a índices
        const columnMap = {
            'id': 0,
            'po': 1,
            'creacion': 2,
            'envio': 3,
            'estado': 4,
            'cliente': 5,
            'usuario': 6,
            'progreso': 7
        };
        
        // Ordenar filas
        rows.sort(function(a, b) {
            const aValue = $(a).find('td').eq(columnMap[column]).text().trim();
            const bValue = $(b).find('td').eq(columnMap[column]).text().trim();
            
            // Ordenar como números si es posible
            if (!isNaN(aValue) && !isNaN(bValue)) {
                return direction === 'asc' ? 
                    parseInt(aValue) - parseInt(bValue) : 
                    parseInt(bValue) - parseInt(aValue);
            }
            
            // Ordenar como fechas si es columna de fechas
            if (column === 'creacion' || column === 'envio') {
                // Convertir formato de fecha DD/MM/YYYY a objeto Date
                const aDate = new Date(aValue.split('/').reverse().join('-'));
                const bDate = new Date(bValue.split('/').reverse().join('-'));
                
                // Si alguna fecha es inválida, ordenar como texto
                if (isNaN(aDate) || isNaN(bDate)) {
                    return direction === 'asc' ? 
                        aValue.localeCompare(bValue) : 
                        bValue.localeCompare(aValue);
                }
                
                return direction === 'asc' ? aDate - bDate : bDate - aDate;
            }
            
            // Ordenar como porcentaje si es columna de progreso
            if (column === 'progreso') {
                const aProgress = parseInt(aValue.replace('%', ''));
                const bProgress = parseInt(bValue.replace('%', ''));
                return direction === 'asc' ? aProgress - bProgress : bProgress - aProgress;
            }
            
            // Ordenar como texto
            return direction === 'asc' ? 
                aValue.localeCompare(bValue) : 
                bValue.localeCompare(aValue);
        });
        
        // Reordenar filas en la tabla
        $.each(rows, function(index, row) {
            tbody.append(row);
        });
        
        // Actualizar filas filtradas
        filterTable();
    }
    
    // Filtrado de tabla
    $('.filtro').on('input change', function() {
        filterTable();
    });
    
    // Limpiar filtros
    $('#limpiar-filtros').click(function() {
        $('.filtro').val('');
        filterTable();
        
        // Si estamos en la página con filtros GET, redirigir a la página sin filtros
        if (window.location.search) {
            window.location.href = 'po.php';
        }
    });
    
    // Función para filtrar la tabla
    function filterTable() {
        const poNumero = $('#po_numero').val().toLowerCase();
        const estado = $('#estado').val();
        const cliente = $('#cliente').val().toLowerCase();
        const fechaInicio = $('#fecha_inicio').val();
        const fechaFin = $('#fecha_fin').val();
        
        // Reiniciar array de filas filtradas
        filteredRows = [];
        
        $('#tabla-pos tbody tr').each(function() {
            const $row = $(this);
            
            // Obtener valores de las celdas
            const rowPoNumero = $row.find('td').eq(1).text().toLowerCase();
            const rowEstado = $row.find('td').eq(4).text().trim();
            const rowCliente = $row.find('td').eq(5).text().toLowerCase();
            const rowFechaCreacion = $row.find('td').eq(2).text().trim();
            
            // Convertir fecha de la fila a formato YYYY-MM-DD para comparación
            const dateParts = rowFechaCreacion.split('/');
            const rowDate = dateParts.length === 3 ? 
                new Date(dateParts[2], dateParts[1] - 1, dateParts[0]) : 
                new Date(0);
            
            // Verificar si la fila cumple con todos los filtros
            const matchPoNumero = poNumero === '' || rowPoNumero.includes(poNumero);
            const matchEstado = estado === '' || rowEstado.includes(estado);
            const matchCliente = cliente === '' || rowCliente.includes(cliente);
            
            // Filtro de fechas
            let matchFechas = true;
            if (fechaInicio && fechaFin) {
                const startDate = new Date(fechaInicio);
                const endDate = new Date(fechaFin);
                // Ajustar endDate para incluir todo el día
                endDate.setHours(23, 59, 59, 999);
                matchFechas = rowDate >= startDate && rowDate <= endDate;
            } else if (fechaInicio) {
                const startDate = new Date(fechaInicio);
                matchFechas = rowDate >= startDate;
            } else if (fechaFin) {
                const endDate = new Date(fechaFin);
                // Ajustar endDate para incluir todo el día
                endDate.setHours(23, 59, 59, 999);
                matchFechas = rowDate <= endDate;
            }
            
            // Mostrar u ocultar fila según los filtros
            if (matchPoNumero && matchEstado && matchCliente && matchFechas) {
                // Agregar a filas filtradas
                filteredRows.push($row);
                $row.addClass('filtered-row');
            } else {
                $row.removeClass('filtered-row');
            }
            
            // Ocultar todas las filas inicialmente
            $row.hide();
        });
        
        // Actualizar paginación después de filtrar
        currentPage = 1;
        updatePagination();
    }
    
    // Inicializar paginación
    function initPagination() {
        // Configurar cambio de registros por página
        $('#registros-por-pagina').change(function() {
            rowsPerPage = parseInt($(this).val());
            currentPage = 1;
            updatePagination();
        });
        
        // Inicializar filtrado para obtener filas iniciales
        filterTable();
    }
    
    // Actualizar paginación
    function updatePagination() {
        const totalRows = filteredRows.length;
        const totalPages = Math.ceil(totalRows / rowsPerPage);
        
        // Actualizar contador de registros
        $('#registros-totales').text(totalRows);
        
        // Si no hay registros o la página actual es mayor que el total de páginas
        if (totalRows === 0 || currentPage > totalPages) {
            currentPage = 1;
        }
        
        // Calcular rangos para mostrar
        const startIndex = (currentPage - 1) * rowsPerPage;
        const endIndex = Math.min(startIndex + rowsPerPage, totalRows);
        
        // Actualizar contador de registros mostrados
        $('#registros-mostrados').text(totalRows === 0 ? 0 : `${startIndex + 1}-${endIndex}`);
        
        // Mostrar filas de la página actual
        for (let i = 0; i < filteredRows.length; i++) {
            if (i >= startIndex && i < endIndex) {
                filteredRows[i].show();
            } else {
                filteredRows[i].hide();
            }
        }
        
        // Generar botones de paginación
        generatePaginationButtons(totalPages);
    }
    
    // Generar botones de paginación
    function generatePaginationButtons(totalPages) {
        const $pagination = $('#paginacion');
        $pagination.empty();
        
        // Si no hay páginas, no mostrar paginación
        if (totalPages === 0) {
            return;
        }
        
        // Botón anterior
        $pagination.append(`
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage - 1}" aria-label="Anterior">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
        `);
        
        // Determinar qué botones mostrar
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, startPage + 4);
        
        // Ajustar si estamos cerca del final
        if (endPage - startPage < 4) {
            startPage = Math.max(1, endPage - 4);
        }
        
        // Botones de número de página
        for (let i = startPage; i <= endPage; i++) {
            $pagination.append(`
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `);
        }
        
        // Botón siguiente
        $pagination.append(`
            <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage + 1}" aria-label="Siguiente">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        `);
        
        // Manejar clics en botones de paginación
        $('.page-link').click(function(e) {
            e.preventDefault();
            const page = parseInt($(this).data('page'));
            
            // Solo cambiar si es una página válida
            if (!isNaN(page) && page >= 1 && page <= totalPages) {
                currentPage = page;
                updatePagination();
            }
        });
    }
    
    // Estilos para las columnas ordenables
    $('.sortable').css('cursor', 'pointer');
});
