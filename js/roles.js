$(document).ready(function() {
    let currentSort = { column: 'id', direction: 'asc' };
    let filters = {
        nombre: '',
        estado: ''
    };

    // Event listeners para filtros
    $('.filtro').on('input change', function() {
        const id = $(this).attr('id');
        if (id === 'filtro-nombre') filters.nombre = $(this).val().toLowerCase();
        if (id === 'filtro-estado') filters.estado = $(this).val();
        aplicarFiltros();
    });

    // Limpiar filtros
    $('#limpiar-filtros').click(function() {
        $('.filtro').val('');
        filters = {
            nombre: '',
            estado: ''
        };
        aplicarFiltros();
    });

    // Ordenamiento de columnas
    $('.sortable').click(function() {
        const column = $(this).data('column');
        if (currentSort.column === column) {
            currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
        } else {
            currentSort.column = column;
            currentSort.direction = 'asc';
        }
        
        // Actualizar iconos de ordenamiento
        $('.sortable i').removeClass('fa-sort-up fa-sort-down').addClass('fa-sort');
        $(this).find('i').removeClass('fa-sort')
            .addClass(currentSort.direction === 'asc' ? 'fa-sort-up' : 'fa-sort-down');
        
        aplicarFiltros();
    });

    // Manejo del modal de edición
    $('#editarRolModal').on('show.bs.modal', function(event) {
        const button = $(event.relatedTarget);
        const id = button.data('id');
        const nombre = button.data('nombre');
        const descripcion = button.data('descripcion');

        $('#editar_id').val(id);
        $('#editar_nombre').val(nombre);
        $('#editar_descripcion').val(descripcion);
    });

    // Validación y envío del formulario nuevo
    $('#guardarNuevoRol').click(function() {
        const form = $('#nuevoRolForm');
        const formData = new FormData(form[0]);
        
        // Validación básica
        const nombre = $('#nuevo_nombre').val().trim();
        const descripcion = $('#nuevo_descripcion').val().trim();

        if (!nombre || !descripcion) {
            mostrarError('Por favor complete todos los campos obligatorios');
            return;
        }
        
        $.ajax({
            url: '../controllers/RolController.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    mostrarExito('Rol creado exitosamente');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    mostrarError(response.message || 'Error al crear el rol');
                }
            },
            error: function() {
                mostrarError('Error al procesar la solicitud');
            }
        });
    });

    // Validación y envío del formulario editar
    $('#guardarEditarRol').click(function() {
        const form = $('#editarRolForm');
        const formData = new FormData(form[0]);
        
        // Validación básica
        const nombre = $('#editar_nombre').val().trim();
        const descripcion = $('#editar_descripcion').val().trim();

        if (!nombre || !descripcion) {
            mostrarError('Por favor complete todos los campos obligatorios');
            return;
        }
        
        $.ajax({
            url: '../controllers/RolController.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    mostrarExito('Rol actualizado exitosamente');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    mostrarError(response.message || 'Error al actualizar el rol');
                }
            },
            error: function() {
                mostrarError('Error al procesar la solicitud');
            }
        });
    });

    // Manejo de cambio de estado
    let rolToToggle = null;
    $('.toggle-status').click(function() {
        rolToToggle = {
            id: $(this).data('id'),
            estado: $(this).data('estado')
        };
    });

    $('#confirmStatusBtn').click(function() {
        if (!rolToToggle) return;

        $.ajax({
            url: '../controllers/RolController.php',
            type: 'POST',
            data: {
                action: 'toggleStatus',
                id: rolToToggle.id,
                estado: rolToToggle.estado
            },
            success: function(response) {
                if (response.success) {
                    mostrarExito('Estado del rol actualizado exitosamente');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    mostrarError(response.message || 'Error al cambiar el estado del rol');
                }
            },
            error: function() {
                mostrarError('Error al cambiar el estado');
            }
        });

        $('#confirmStatusModal').modal('hide');
    });

    // Funciones auxiliares para mostrar mensajes
    function mostrarExito(mensaje) {
        $('#successMessage').text(mensaje);
        $('#successModal').modal('show');
    }

    function mostrarError(mensaje) {
        $('#errorMessage').text(mensaje);
        $('#errorModal').modal('show');
    }

    function aplicarFiltros() {
        const rows = $('table tbody tr');
        
        rows.each(function() {
            const row = $(this);
            const nombre = row.find('td:eq(1)').text().toLowerCase();
            const estado = row.find('td:eq(3) span').text().trim();
            
            const matchNombre = !filters.nombre || nombre.includes(filters.nombre);
            const matchEstado = !filters.estado || estado === filters.estado;
            
            if (matchNombre && matchEstado) {
                row.show();
            } else {
                row.hide();
            }
        });

        // Ordenar filas
        const rows_array = rows.get();
        rows_array.sort(function(a, b) {
            let aValue, bValue;
            const columnIndex = {
                'id': 0,
                'nombre': 1,
                'descripcion': 2,
                'estado': 3
            }[currentSort.column];

            if (currentSort.column === 'id') {
                aValue = parseInt($(a).find('td').eq(columnIndex).text());
                bValue = parseInt($(b).find('td').eq(columnIndex).text());
            } else if (currentSort.column === 'estado') {
                aValue = $(a).find('td').eq(columnIndex).find('span').text();
                bValue = $(b).find('td').eq(columnIndex).find('span').text();
            } else {
                aValue = $(a).find('td').eq(columnIndex).text();
                bValue = $(b).find('td').eq(columnIndex).text();
            }

            if (currentSort.direction === 'asc') {
                return aValue > bValue ? 1 : -1;
            } else {
                return aValue < bValue ? 1 : -1;
            }
        });

        $('table tbody').empty().append(rows_array);
    }
});
