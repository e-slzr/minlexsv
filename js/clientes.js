$(document).ready(function() {
    let currentSort = { column: 'id', direction: 'asc' };
    let filters = {
        empresa: '',
        nombre: '',
        apellido: '',
        estado: ''
    };
    let paginacion = {
        registrosPorPagina: 10,
        paginaActual: 1,
        totalPaginas: 1
    };

    // Aplicar filtros
    function aplicarFiltros() {
        const rows = $('#tabla-clientes tbody tr');
        
        rows.each(function() {
            const row = $(this);
            const empresa = row.find('td:eq(1)').text().toLowerCase();
            const nombre = row.find('td:eq(2)').text().toLowerCase();
            const apellido = row.find('td:eq(3)').text().toLowerCase();
            const estado = row.find('td:eq(7) span').text().trim();
            
            const matchEmpresa = !filters.empresa || empresa.includes(filters.empresa);
            const matchNombre = !filters.nombre || nombre.includes(filters.nombre);
            const matchApellido = !filters.apellido || apellido.includes(filters.apellido);
            const matchEstado = !filters.estado || estado === filters.estado;
            
            if (matchEmpresa && matchNombre && matchApellido && matchEstado) {
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
                'empresa': 1,
                'nombre': 2,
                'apellido': 3,
                'estado': 7
            }[currentSort.column];

            if (currentSort.column === 'id') {
                aValue = parseInt($(a).find('td').eq(columnIndex).text());
                bValue = parseInt($(b).find('td').eq(columnIndex).text());
            } else if (currentSort.column === 'estado') {
                aValue = $(a).find('td').eq(columnIndex).find('span').text();
                bValue = $(b).find('td').eq(columnIndex).find('span').text();
            } else {
                aValue = $(a).find('td').eq(columnIndex).text().toLowerCase();
                bValue = $(b).find('td').eq(columnIndex).text().toLowerCase();
            }

            if (aValue < bValue) return currentSort.direction === 'asc' ? -1 : 1;
            if (aValue > bValue) return currentSort.direction === 'asc' ? 1 : -1;
            return 0;
        });

        // Actualizar paginación
        const rowsVisibles = rows_array.filter(row => $(row).is(':visible'));
        paginacion.totalPaginas = Math.ceil(rowsVisibles.length / paginacion.registrosPorPagina);
        if (paginacion.paginaActual > paginacion.totalPaginas) {
            paginacion.paginaActual = paginacion.totalPaginas || 1;
        }

        // Actualizar información de paginación
        $('#info-pagina').text(`Página ${paginacion.paginaActual} de ${paginacion.totalPaginas}`);
        
        // Actualizar estado de botones de navegación
        $('#anterior-pagina').toggleClass('disabled', paginacion.paginaActual <= 1);
        $('#siguiente-pagina').toggleClass('disabled', paginacion.paginaActual >= paginacion.totalPaginas);

        // Mostrar solo las filas de la página actual
        const inicio = (paginacion.paginaActual - 1) * paginacion.registrosPorPagina;
        const fin = inicio + paginacion.registrosPorPagina;
        rowsVisibles.slice(inicio, fin).forEach(row => $(row).show());
        rowsVisibles.slice(0, inicio).forEach(row => $(row).hide());
        rowsVisibles.slice(fin).forEach(row => $(row).hide());

        // Reordenar tabla
        $('#tabla-clientes tbody').empty().append(rows_array);
    }

    // Event listeners para filtros
    $('.filtro').on('input change', function() {
        const id = $(this).attr('id');
        if (id === 'filtro-empresa') filters.empresa = $(this).val().toLowerCase();
        if (id === 'filtro-nombre') filters.nombre = $(this).val().toLowerCase();
        if (id === 'filtro-apellido') filters.apellido = $(this).val().toLowerCase();
        if (id === 'filtro-estado') filters.estado = $(this).val();
        paginacion.paginaActual = 1; // Resetear a primera página al filtrar
        aplicarFiltros();
    });

    // Limpiar filtros
    $('#limpiar-filtros').click(function() {
        $('.filtro').val('');
        filters = {
            empresa: '',
            nombre: '',
            apellido: '',
            estado: ''
        };
        paginacion.paginaActual = 1; // Resetear a primera página al limpiar filtros
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

    // Manejo de paginación
    $('#registros-por-pagina').change(function() {
        paginacion.registrosPorPagina = parseInt($(this).val());
        paginacion.paginaActual = 1; // Resetear a primera página al cambiar registros por página
        aplicarFiltros();
    });

    $('#anterior-pagina').click(function(e) {
        e.preventDefault();
        if (paginacion.paginaActual > 1) {
            paginacion.paginaActual--;
            aplicarFiltros();
        }
    });

    $('#siguiente-pagina').click(function(e) {
        e.preventDefault();
        if (paginacion.paginaActual < paginacion.totalPaginas) {
            paginacion.paginaActual++;
            aplicarFiltros();
        }
    });

    // Manejo del modal de edición
    $('#editarClienteModal').on('show.bs.modal', function(event) {
        const button = $(event.relatedTarget);
        const id = button.data('id');
        const empresa = button.data('empresa');
        const nombre = button.data('nombre');
        const apellido = button.data('apellido');
        const direccion = button.data('direccion');
        const telefono = button.data('telefono');
        const correo = button.data('correo');

        $('#editar_id').val(id);
        $('#editar_empresa').val(empresa);
        $('#editar_nombre').val(nombre);
        $('#editar_apellido').val(apellido);
        $('#editar_direccion').val(direccion);
        $('#editar_telefono').val(telefono);
        $('#editar_correo').val(correo);
    });

    // Validación y envío del formulario nuevo
    $('#guardarNuevoCliente').click(function() {
        const form = $('#nuevoClienteForm');
        const formData = new FormData(form[0]);
        
        // Validación básica
        const empresa = $('#nuevo_empresa').val().trim();
        const nombre = $('#nuevo_nombre').val().trim();
        const apellido = $('#nuevo_apellido').val().trim();

        if (!empresa || !nombre || !apellido) {
            mostrarError('Por favor complete todos los campos obligatorios');
            return;
        }
        
        $.ajax({
            url: '../controllers/ClienteController.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                try {
                    if (response.success) {
                        mostrarExito('Cliente creado exitosamente');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        mostrarError(response.message || 'Error al crear el cliente');
                    }
                } catch (e) {
                    mostrarError('Error al procesar la respuesta del servidor');
                    console.error('Error en la respuesta:', response);
                }
            },
            error: function(xhr, status, error) {
                mostrarError('Error al procesar la solicitud');
                console.error('Error AJAX:', error);
                console.error('Estado:', status);
                console.error('Respuesta:', xhr.responseText);
            }
        });
    });

    // Validación y envío del formulario editar
    $('#guardarEditarCliente').click(function() {
        const form = $('#editarClienteForm');
        const formData = new FormData(form[0]);
        
        // Validación básica
        const empresa = $('#editar_empresa').val().trim();
        const nombre = $('#editar_nombre').val().trim();
        const apellido = $('#editar_apellido').val().trim();

        if (!empresa || !nombre || !apellido) {
            mostrarError('Por favor complete todos los campos obligatorios');
            return;
        }
        
        $.ajax({
            url: '../controllers/ClienteController.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                try {
                    if (response.success) {
                        mostrarExito('Cliente actualizado exitosamente');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        mostrarError(response.message || 'Error al actualizar el cliente');
                    }
                } catch (e) {
                    mostrarError('Error al procesar la respuesta del servidor');
                    console.error('Error en la respuesta:', response);
                }
            },
            error: function(xhr, status, error) {
                mostrarError('Error al procesar la solicitud');
                console.error('Error AJAX:', error);
                console.error('Estado:', status);
                console.error('Respuesta:', xhr.responseText);
            }
        });
    });

    // Manejo de cambio de estado
    let clienteToToggle = null;
    $('.toggle-status').click(function() {
        clienteToToggle = {
            id: $(this).data('id'),
            estado: $(this).data('estado')
        };
    });

    $('#confirmToggleStatus').click(function() {
        if (!clienteToToggle) return;

        $.ajax({
            url: '../controllers/ClienteController.php',
            type: 'POST',
            data: {
                action: 'toggleStatus',
                id: clienteToToggle.id,
                estado: clienteToToggle.estado
            },
            success: function(response) {
                try {
                    if (response.success) {
                        mostrarExito('Estado del cliente actualizado exitosamente');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        mostrarError(response.message || 'Error al cambiar el estado del cliente');
                    }
                } catch (e) {
                    mostrarError('Error al procesar la respuesta del servidor');
                    console.error('Error en la respuesta:', response);
                }
            },
            error: function(xhr, status, error) {
                mostrarError('Error al procesar la solicitud');
                console.error('Error AJAX:', error);
                console.error('Estado:', status);
                console.error('Respuesta:', xhr.responseText);
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
});
