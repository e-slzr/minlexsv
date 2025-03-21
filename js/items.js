$(document).ready(function() {
    // Variables globales
    let currentItemId = null;
    let currentSortColumn = '';
    let currentSortDirection = 'asc';
    let currentPage = 1;
    let rowsPerPage = 10;
    let filteredRows = [];

    // Inicializar paginación
    initPagination();

    // Evento para crear/editar Item
    $('#saveItem').click(function() {
        const formData = new FormData($('#itemForm')[0]);
        const action = currentItemId ? 'update' : 'create';
        formData.append('action', action);

        $.ajax({
            url: '../controllers/ItemController.php',
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
    $('.edit-item').click(function() {
        const itemId = $(this).data('id');
        currentItemId = itemId;
        
        $.get(`../controllers/ItemController.php?action=getItemInfo&id=${itemId}`, function(response) {
            if (response.success) {
                const item = response.item;
                $('#itemModalLabel').text('Editar Item');
                $('#itemId').val(item.id);
                $('#itemNumero').val(item.item_numero);
                $('#itemNombre').val(item.item_nombre);
                $('#itemTalla').val(item.item_talla);
                $('#itemDescripcion').val(item.item_descripcion);
                
                // Mostrar imagen actual si existe
                if (item.item_img) {
                    $('#imagenPreview').html(`<img src="${item.item_img}" alt="Vista previa" style="max-width: 100px; max-height: 100px;">`);
                } else {
                    $('#imagenPreview').empty();
                }
                
                // Mostrar info de especificaciones si existe
                if (item.item_dir_specs) {
                    $('#specsInfo').html(`<a href="${item.item_dir_specs}" target="_blank">Ver especificaciones actuales</a>`);
                } else {
                    $('#specsInfo').empty();
                }
            }
        });
    });

    // Evento para mostrar modal de creación
    $('[data-bs-target="#itemModal"]').not('.edit-item').click(function() {
        currentItemId = null;
        $('#itemModalLabel').text('Nuevo Item');
        $('#itemForm')[0].reset();
        $('#imagenPreview').empty();
        $('#specsInfo').empty();
    });

    // Evento para ver detalles de Item
    $('.view-item').click(function() {
        const itemId = $(this).data('id');
        
        $.get(`../controllers/ItemController.php?action=getItemInfo&id=${itemId}`, function(response) {
            if (response.success) {
                const item = response.item;
                
                // Información general
                $('#detailItemNumero').text(item.item_numero);
                $('#detailItemNombre').text(item.item_nombre);
                $('#detailItemTalla').text(item.item_talla || 'No especificada');
                $('#detailItemDescripcion').text(item.item_descripcion || 'Sin descripción');
                
                // Imagen
                if (item.item_img) {
                    $('#detailItemImagen').html(`<img src="${item.item_img}" alt="Imagen del item" style="max-width: 100%; max-height: 300px;">`);
                } else {
                    $('#detailItemImagen').html('<p class="text-muted">No hay imagen disponible</p>');
                }
                
                // Especificaciones
                if (item.item_dir_specs) {
                    $('#detailItemSpecs').html(`<a href="${item.item_dir_specs}" target="_blank" class="btn btn-sm btn-primary"><i class="fas fa-file-pdf"></i> Ver especificaciones</a>`);
                } else {
                    $('#detailItemSpecs').html('<p class="text-muted">No hay especificaciones disponibles</p>');
                }
            }
        });
    });

    // Evento para mostrar modal de eliminación
    $('.delete-item').click(function() {
        const itemId = $(this).data('id');
        const itemNumero = $(this).data('numero');
        
        $('#deleteItemId').val(itemId);
        $('#deleteItemNumero').text(itemNumero);
    });

    // Evento para confirmar eliminación
    $('#confirmDelete').click(function() {
        const formData = new FormData($('#deleteItemForm')[0]);
        formData.append('action', 'delete');
        formData.append('password', $('#deletePassword').val());

        $.ajax({
            url: '../controllers/ItemController.php',
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

    // Vista previa de imagen al seleccionarla
    $('#itemImagen').change(function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#imagenPreview').html(`<img src="${e.target.result}" alt="Vista previa" style="max-width: 100px; max-height: 100px;">`);
            }
            reader.readAsDataURL(file);
        } else {
            $('#imagenPreview').empty();
        }
    });

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
        
        // Actualizar indicadores visuales
        $('.sortable').find('i').remove();
        $(this).append(` <i class="fas fa-sort-${currentSortDirection === 'asc' ? 'up' : 'down'}"></i>`);
        
        // Ordenar tabla
        sortTable(column, currentSortDirection);
        
        // Actualizar paginación después de ordenar
        currentPage = 1;
        updatePagination();
    });
    
    // Función para ordenar la tabla
    function sortTable(column, direction) {
        const table = $('#tabla-items');
        const tbody = table.find('tbody');
        const rows = tbody.find('tr').toArray();
        
        // Mapeo de columnas a índices
        const columnMap = {
            'id': 0,
            'numero': 1,
            'nombre': 2,
            'talla': 3,
            'descripcion': 4
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
            window.location.href = 'items.php';
        }
    });
    
    // Función para filtrar la tabla
    function filterTable() {
        const itemNumero = $('#item_numero').val().toLowerCase();
        const itemNombre = $('#item_nombre').val().toLowerCase();
        
        // Reiniciar array de filas filtradas
        filteredRows = [];
        
        $('#tabla-items tbody tr').each(function() {
            const $row = $(this);
            
            // Obtener valores de las celdas
            const rowItemNumero = $row.find('td').eq(1).text().toLowerCase();
            const rowItemNombre = $row.find('td').eq(2).text().toLowerCase();
            
            // Verificar si la fila cumple con todos los filtros
            const matchItemNumero = itemNumero === '' || rowItemNumero.includes(itemNumero);
            const matchItemNombre = itemNombre === '' || rowItemNombre.includes(itemNombre);
            
            // Mostrar u ocultar fila según los filtros
            if (matchItemNumero && matchItemNombre) {
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
