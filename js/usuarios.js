$(document).ready(function() {
    // Variables globales para ordenamiento y paginación
    let currentSortColumn = '';
    let currentSortDirection = 'asc';
    let currentPage = 1;
    let rowsPerPage = 10;
    let filteredRows = [];
    
    // Inicializar paginación
    initPagination();
    
    // Manejo del modal de nuevo usuario
    $('#guardarNuevoUsuario').click(function() {
        const form = $('#nuevoUsuarioForm');
        
        // Validación básica del formulario
        if (!form[0].checkValidity()) {
            form[0].reportValidity();
            return;
        }
        
        // Enviar datos al servidor
        $.ajax({
            url: '../controllers/UsuarioController.php',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Mostrar mensaje de éxito
                    alert(response.message);
                    // Cerrar modal
                    $('#nuevoUsuarioModal').modal('hide');
                    // Recargar página para ver los cambios
                    location.reload();
                } else {
                    // Mostrar mensaje de error
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Error al procesar la solicitud');
            }
        });
    });
    
    // Manejo del modal de editar usuario
    $('.edit-usuario').click(function() {
        // Obtener datos del botón
        const id = $(this).data('id');
        const nombre = $(this).data('nombre');
        const apellido = $(this).data('apellido');
        const usuario = $(this).data('usuario');
        const departamento = $(this).data('departamento');
        const rolId = $(this).data('rol-id');
        
        // Llenar el formulario con los datos
        $('#editar_id').val(id);
        $('#editar_nombre').val(nombre);
        $('#editar_apellido').val(apellido);
        $('#editar_usuario').val(usuario);
        $('#editar_departamento').val(departamento);
        $('#editar_rol_id').val(rolId);
        $('#editar_password').val(''); // Limpiar campo de contraseña
    });
    
    // Guardar cambios de usuario editado
    $('#guardarEditarUsuario').click(function() {
        const form = $('#editarUsuarioForm');
        
        // Validación básica del formulario
        if (!form[0].checkValidity()) {
            form[0].reportValidity();
            return;
        }
        
        // Enviar datos al servidor
        $.ajax({
            url: '../controllers/UsuarioController.php',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Mostrar mensaje de éxito
                    alert(response.message);
                    // Cerrar modal
                    $('#editarUsuarioModal').modal('hide');
                    // Recargar página para ver los cambios
                    location.reload();
                } else {
                    // Mostrar mensaje de error
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Error al procesar la solicitud');
            }
        });
    });
    
    // Manejo del cambio de estado
    $('.toggle-status').click(function() {
        const id = $(this).data('id');
        const estado = $(this).data('estado');
        
        // Guardar referencia al botón de confirmación
        $('#confirmStatus').data('id', id);
        $('#confirmStatus').data('estado', estado);
    });
    
    // Confirmar cambio de estado
    $('#confirmStatus').click(function() {
        const id = $(this).data('id');
        const estado = $(this).data('estado');
        
        // Enviar solicitud al servidor
        $.ajax({
            url: '../controllers/UsuarioController.php',
            type: 'POST',
            data: {
                action: 'toggleStatus',
                id: id,
                estado: estado
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Mostrar mensaje de éxito
                    alert(response.message);
                    // Cerrar modal
                    $('#confirmStatusModal').modal('hide');
                    // Recargar página para ver los cambios
                    location.reload();
                } else {
                    // Mostrar mensaje de error
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Error al procesar la solicitud');
            }
        });
    });
    
    // Mostrar/ocultar contraseña
    $('.toggle-password').click(function() {
        const targetId = $(this).data('target');
        const passwordInput = $('#' + targetId);
        
        if (passwordInput.attr('type') === 'password') {
            passwordInput.attr('type', 'text');
            $(this).find('i').removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            passwordInput.attr('type', 'password');
            $(this).find('i').removeClass('fa-eye-slash').addClass('fa-eye');
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
        const table = $('#tabla-usuarios');
        const tbody = table.find('tbody');
        const rows = tbody.find('tr').toArray();
        
        // Mapeo de columnas a índices
        const columnMap = {
            'id': 0,
            'usuario': 1,
            'nombre': 2,
            'apellido': 3,
            'departamento': 4,
            'rol': 5,
            'estado': 6,
            'creacion': 7,
            'modificacion': 8
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
            if (column === 'creacion' || column === 'modificacion') {
                const aDate = new Date(aValue.split(' ')[0].split('/').reverse().join('-'));
                const bDate = new Date(bValue.split(' ')[0].split('/').reverse().join('-'));
                return direction === 'asc' ? aDate - bDate : bDate - aDate;
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
    });
    
    // Función para filtrar la tabla
    function filterTable() {
        const usuario = $('#filtro-usuario').val().toLowerCase();
        const nombre = $('#filtro-nombre').val().toLowerCase();
        const apellido = $('#filtro-apellido').val().toLowerCase();
        const departamento = $('#filtro-departamento').val();
        const rol = $('#filtro-rol').val();
        const estado = $('#filtro-estado').val();
        
        // Reiniciar array de filas filtradas
        filteredRows = [];
        
        $('#tabla-usuarios tbody tr').each(function() {
            const $row = $(this);
            
            // Obtener valores de las celdas
            const rowUsuario = $row.find('td').eq(1).text().toLowerCase();
            const rowNombre = $row.find('td').eq(2).text().toLowerCase();
            const rowApellido = $row.find('td').eq(3).text().toLowerCase();
            const rowDepartamento = $row.find('td').eq(4).text();
            const rowRol = $row.find('td').eq(5).text();
            const rowEstado = $row.find('td').eq(6).text();
            
            // Verificar si la fila cumple con todos los filtros
            const matchUsuario = usuario === '' || rowUsuario.includes(usuario);
            const matchNombre = nombre === '' || rowNombre.includes(nombre);
            const matchApellido = apellido === '' || rowApellido.includes(apellido);
            const matchDepartamento = departamento === '' || rowDepartamento === departamento;
            const matchRol = rol === '' || rowRol === rol;
            const matchEstado = estado === '' || rowEstado.includes(estado);
            
            // Mostrar u ocultar fila según los filtros
            if (matchUsuario && matchNombre && matchApellido && 
                matchDepartamento && matchRol && matchEstado) {
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
