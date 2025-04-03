$(document).ready(function() {
    // Asegurarse de que los modales se limpien correctamente al cerrarse
    $('.modal').on('hidden.bs.modal', function() {
        // Eliminar cualquier backdrop que pueda haber quedado
        $('.modal-backdrop').remove();
        // Eliminar la clase modal-open del body
        $('body').removeClass('modal-open');
        // Eliminar el estilo inline que agrega Bootstrap
        $('body').css('padding-right', '');
    });

    // Manejar el modal de edición
    $('.editar-rol').click(function() {
        const id = $(this).data('id');
        const nombre = $(this).data('nombre');
        const descripcion = $(this).data('descripcion');

        $('#editar_id').val(id);
        $('#editar_nombre').val(nombre);
        $('#editar_descripcion').val(descripcion);
    });

    // Filtrado de roles
    $('#filtro-nombre').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('table tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    // Validación y envío del formulario nuevo
    $('#guardarNuevoRol').click(function() {
        const form = $('#nuevoRolForm')[0];
        const formData = new FormData(form);
        
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
                try {
                    const result = typeof response === 'string' ? JSON.parse(response) : response;
                    if (result.success) {
                        mostrarConfirmacion('Rol creado exitosamente');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        mostrarError(result.message || 'Error al crear el rol');
                    }
                } catch (e) {
                    console.error('Error parsing response:', e, response);
                    mostrarError('Error al procesar la respuesta del servidor');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en la solicitud:', status, error);
                mostrarError('Error al procesar la solicitud');
            }
        });
    });

    // Validación y envío del formulario editar
    $('#guardarEditarRol').click(function() {
        const form = $('#editarRolForm')[0];
        const formData = new FormData(form);
        
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
                try {
                    const result = typeof response === 'string' ? JSON.parse(response) : response;
                    if (result.success) {
                        mostrarConfirmacion('Rol actualizado exitosamente');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        mostrarError(result.message || 'Error al actualizar el rol');
                    }
                } catch (e) {
                    console.error('Error parsing response:', e, response);
                    mostrarError('Error al procesar la respuesta del servidor');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en la solicitud:', status, error);
                mostrarError('Error al procesar la solicitud');
            }
        });
    });

    // Manejo de cambio de estado
    $('.toggle-estado').click(function() {
        const id = $(this).data('id');
        const estado = $(this).data('estado');
        const nuevoEstado = estado === 'Activo' ? 'Inactivo' : 'Activo';
        
        $('#confirmStatusMessage').text(`¿Está seguro que desea cambiar el estado del rol a ${nuevoEstado}?`);
        
        // Guardar los datos para el evento de confirmación
        $('#confirmStatusChange').data('id', id);
        $('#confirmStatusChange').data('estado', nuevoEstado);
        
        // Mostrar el modal de confirmación
        const modal = new bootstrap.Modal(document.getElementById('confirmStatusModal'));
        modal.show();
    });

    $('#confirmStatusChange').click(function() {
        const id = $(this).data('id');
        const nuevoEstado = $(this).data('estado');
        
        if (!id || !nuevoEstado) {
            mostrarError('Error: No se ha seleccionado un rol para cambiar su estado');
            return;
        }
        
        const formData = new FormData();
        formData.append('action', 'toggleStatus');
        formData.append('id', id);
        formData.append('estado', nuevoEstado);
        
        $.ajax({
            url: '../controllers/RolController.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                try {
                    const result = typeof response === 'string' ? JSON.parse(response) : response;
                    if (result.success) {
                        mostrarConfirmacion('Estado del rol actualizado exitosamente');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        mostrarError(result.message || 'Error al actualizar el estado del rol');
                    }
                } catch (e) {
                    console.error('Error parsing response:', e, response);
                    mostrarError('Error al procesar la respuesta del servidor');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en la solicitud:', status, error);
                mostrarError('Error al procesar la solicitud');
            }
        });
        
        // Cerrar el modal de confirmación
        const modal = bootstrap.Modal.getInstance(document.getElementById('confirmStatusModal'));
        if (modal) {
            modal.hide();
        }
    });

    // Funciones auxiliares para mostrar mensajes
    function mostrarConfirmacion(mensaje) {
        $('#confirmationMessage').text(mensaje);
        const modal = new bootstrap.Modal(document.getElementById('confirmationModal'));
        modal.show();
    }

    function mostrarError(mensaje) {
        $('#errorMessage').text(mensaje);
        const modal = new bootstrap.Modal(document.getElementById('errorModal'));
        modal.show();
    }
});
