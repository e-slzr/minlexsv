$(document).ready(function() {
    // Variables globales
    let currentItemId = null;
    let currentPage = 1;
    let rowsPerPage = parseInt($('#registros-por-pagina').val()) || 10;
    let filteredRows = [];
    let currentSortColumn = 'numero';
    let currentSortDirection = 'asc';
    });
    
    // O mejor aún, mover este código dentro del $(document).ready()
    // y eliminar esta sección duplicada que está fuera
    $('#itemForm').submit(function(e) {
        e.preventDefault();
        
        // Validar campos requeridos
        if (!$('#itemNumero').val().trim() || !$('#itemNombre').val().trim()) {
            alert('Por favor, complete los campos obligatorios (Número y Nombre del Item)');
            return;
        }

        const formData = new FormData(this);
        const action = currentItemId ? 'update' : 'create';
        formData.append('action', action);
        if (currentItemId) {
            formData.append('id', currentItemId);
        }

        // Mostrar indicador de carga
        const submitButton = $('#saveItem');
        submitButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: '../controllers/ItemController.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                try {
                    if (typeof response === 'string') {
                        response = JSON.parse(response);
                    }
                    if (response.success) {
                        alert(response.message);
                        window.location.reload();
                    } else {
                        alert('Error: ' + response.message);
                        submitButton.prop('disabled', false).html('Guardar');
                    }
                } catch (e) {
                    console.error('Error al procesar la respuesta:', e);
                    alert('Error al procesar la respuesta del servidor');
                    submitButton.prop('disabled', false).html('Guardar');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                alert('Error al procesar la solicitud');
                submitButton.prop('disabled', false).html('Guardar');
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
                    $('#imagenPreview').html(`<img src="..${item.item_img}" alt="Vista previa" style="max-width: 100px; max-height: 100px;">`);
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
                    $('#detailItemImagen').html(`<img src="..${item.item_img}" alt="Imagen del item" style="max-width: 100%; max-height: 300px;">`);
                } else {
                    $('#detailItemImagen').html('<p class="text-muted">No hay imagen disponible</p>');
                }
                
                // Especificaciones
                if (item.item_dir_specs) {
                    const specsPath = item.item_dir_specs.replace('/uploads/specs/', '');
                    $('#detailItemSpecs').html(`<a href="specs_viewer.php?dir=${encodeURIComponent(specsPath)}" target="_blank" class="btn btn-sm btn-primary"><i class="fas fa-folder-open"></i> Ver especificaciones</a>`);
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

    // Vista previa de archivos de especificaciones
    $('#itemSpecs').change(function() {
        const files = this.files;
        let fileList = '';
        
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const fileSize = (file.size / 1024).toFixed(2) + ' KB';
            const fileType = file.name.split('.').pop().toUpperCase();
            const fileIcon = getFileIcon(fileType);
            
            fileList += `
                <div class="file-preview-item d-flex align-items-center p-2 border-bottom">
                    <i class="fas ${fileIcon} me-2"></i>
                    <div class="flex-grow-1">
                        <div class="fw-bold">${file.name}</div>
                        <small class="text-muted">${fileType} - ${fileSize}</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-file" data-index="${i}">
                        <i class="fas fa-times"></i>
                    </button>
                </div>`;
        }
        
        // Mostrar la lista de archivos
        if (fileList) {
            if (!$('#specsPreview').length) {
                $('#itemSpecs').after('<div id="specsPreview" class="mt-2 border rounded"></div>');
            }
            $('#specsPreview').html(fileList);
            
            // Evento para eliminar archivos
            $('.remove-file').click(function() {
                const index = $(this).data('index');
                const dt = new DataTransfer();
                const input = document.getElementById('itemSpecs');
                const { files } = input;
                
                for (let i = 0; i < files.length; i++) {
                    if (i !== index) {
                        dt.items.add(files[i]);
                    }
                }
                
                input.files = dt.files;
                $(this).trigger('change');
            });
        } else {
            $('#specsPreview').remove();
        }
    });
    
    // Función para obtener el ícono según el tipo de archivo
    function getFileIcon(type) {
        const icons = {
            'PDF': 'fa-file-pdf text-danger',
            'DOC': 'fa-file-word text-primary',
            'DOCX': 'fa-file-word text-primary',
            'XLS': 'fa-file-excel text-success',
            'XLSX': 'fa-file-excel text-success',
            'JPG': 'fa-file-image text-info',
            'JPEG': 'fa-file-image text-info',
            'PNG': 'fa-file-image text-info',
            'GIF': 'fa-file-image text-info'
        };
        
        return icons[type] || 'fa-file text-secondary';
    }
    
    // Vista previa de imagen al seleccionarla
    $('#itemImagen').change(function() {
        const file = this.files[0];
        if (file) {
            // Validar tipo y tamaño de archivo
            const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
            const maxSize = 2 * 1024 * 1024; // 2MB

            if (!validTypes.includes(file.type)) {
                alert('Por favor, seleccione un archivo de imagen válido (JPG, PNG o GIF)');
                this.value = '';
                $('#imagenPreview').html('<span class="text-muted">Vista previa de la imagen</span>');
                return;
            }

            if (file.size > maxSize) {
                alert('El archivo es demasiado grande. El tamaño máximo permitido es 2MB');
                this.value = '';
                $('#imagenPreview').html('<span class="text-muted">Vista previa de la imagen</span>');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                $('#imagenPreview').html(`
                    <div class="position-relative">
                        <img src="${e.target.result}" alt="Vista previa" class="img-fluid" style="max-height: 150px;">
                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" id="removeImage">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `);

                // Evento para remover imagen
                $('#removeImage').click(function(e) {
                    e.preventDefault();
                    $('#itemImagen').val('');
                    $('#imagenPreview').html('<span class="text-muted">Vista previa de la imagen</span>');
                });
            }
            reader.readAsDataURL(file);
        } else {
            $('#imagenPreview').html('<span class="text-muted">Vista previa de la imagen</span>');
        }
    });

    // Manejar archivos de especificaciones
    $('#itemSpecs').change(function() {
        const files = this.files;
        const maxSize = 5 * 1024 * 1024; // 5MB por archivo
        const validTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'image/jpeg',
            'image/png'
        ];

        let fileList = '';
        let validFiles = true;

        Array.from(files).forEach(file => {
            if (!validTypes.includes(file.type)) {
                alert(`El archivo ${file.name} no es de un tipo válido`);
                validFiles = false;
                return;
            }

            if (file.size > maxSize) {
                alert(`El archivo ${file.name} excede el tamaño máximo permitido de 5MB`);
                validFiles = false;
                return;
            }

            fileList += `
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-truncate" style="max-width: 200px;">${file.name}</span>
                    <span class="badge bg-primary">${(file.size / 1024 / 1024).toFixed(2)} MB</span>
                </div>
            `;
        });

        if (!validFiles) {
            this.value = '';
            $('#specsList').html('<span class="text-muted">No hay archivos seleccionados</span>');
            return;
        }

        if (fileList) {
            $('#specsList').html(fileList);
        } else {
            $('#specsList').html('<span class="text-muted">No hay archivos seleccionados</span>');
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
        
        // Ocultar todas las filas primero
        $('#tabla-items tbody tr').hide();
        
        // Mostrar solo las filas de la página actual
        filteredRows.slice(startIndex, endIndex).forEach(row => row.show());
        
        // Generar botones de paginación
        const $pagination = $('#paginacion');
        $pagination.empty();
        
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


    // Asignar el evento submit del formulario
    $('#itemForm').on('submit', function(e) {
    e.preventDefault();
    
    // Validar campos requeridos
    if (!$('#itemNumero').val().trim() || !$('#itemNombre').val().trim()) {
        alert('Por favor, complete los campos obligatorios (Número y Nombre del Item)');
        return;
    }

    const formData = new FormData(this);
    const action = currentItemId ? 'update' : 'create';
    formData.append('action', action);
    if (currentItemId) {
        formData.append('id', currentItemId);
    }

    // Mostrar indicador de carga
    const submitButton = $('#saveItem');
    submitButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

    $.ajax({
        url: '../controllers/ItemController.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            try {
                if (typeof response === 'string') {
                    response = JSON.parse(response);
                }
                if (response.success) {
                    alert(response.message);
                    window.location.reload();
                } else {
                    alert('Error: ' + (response.message || 'Error desconocido'));
                    submitButton.prop('disabled', false).html('Guardar');
                }
            } catch (e) {
                console.error('Error al procesar la respuesta:', e);
                alert('Error al procesar la respuesta del servidor');
                submitButton.prop('disabled', false).html('Guardar');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            alert('Error al procesar la solicitud: ' + error);
            submitButton.prop('disabled', false).html('Guardar');
        }
    });
    });

    // Asignar el evento click al botón guardar
    $('#saveItem').click(function(e) {
        e.preventDefault();
        $('#itemForm').submit();
    });

