document.addEventListener('DOMContentLoaded', function() {
    // Función para generar contraseña aleatoria
    function generatePassword() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        let password = chars.charAt(Math.floor(Math.random() * 26)).toUpperCase();
        const length = Math.floor(Math.random() * 5) + 8; // Mínimo 8 caracteres
        for (let i = 1; i < length; i++) {
            password += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return password;
    }

    // Función para actualizar el indicador de estado
    function updateStatusIndicator(estado) {
        const indicador = document.getElementById('estado_indicador');
        if (estado === 'Activo') {
            indicador.innerHTML = '🟢';
        } else {
            indicador.innerHTML = '🔴';
        }
    }

    // Botón para abrir el modal de nuevo usuario
    document.querySelector('button[data-bs-target="#newUserModal"]').addEventListener('click', function() {
        const modal = new bootstrap.Modal(document.getElementById('newUserModal'));
        modal.show();
    });

    // Función para mostrar los datos en el modal de edición
    const editButtons = document.querySelectorAll('.edit-user');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const alias = this.getAttribute('data-alias');
            const nombre = this.getAttribute('data-nombre');
            const apellido = this.getAttribute('data-apellido');
            const departamento = this.getAttribute('data-departamento');
            const rol = this.getAttribute('data-rol');
            const estado = this.getAttribute('data-estado');
            const modulo = this.getAttribute('data-modulo');
            const ultima_mod = this.getAttribute('data-ultima-modificacion');

            // Llenar el formulario de edición
            document.getElementById('edit_userId').value = id;
            document.getElementById('edit_alias').value = alias;
            document.getElementById('edit_nombre').value = nombre;
            document.getElementById('edit_apellido').value = apellido;
            document.getElementById('edit_departamento').value = departamento || '';
            document.getElementById('edit_rol').value = rol;
            document.getElementById('edit_estado').value = estado;
            if (document.getElementById('edit_modulo')) {
                document.getElementById('edit_modulo').value = modulo || '';
            }
            if (document.getElementById('ultima_modificacion')) {
                document.getElementById('ultima_modificacion').textContent = ultima_mod || 'No disponible';
            }

            // Actualizar el indicador de estado
            updateStatusIndicator(estado);

            // Mostrar el modal de edición
            const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
            modal.show();
        });
    });

    // Manejar el botón de generar contraseña para nuevo usuario
    $('#generatePasswordNew').click(function() {
        const password = generatePassword();
        $('#new_password').val(password);
        $('#new_password_confirm').val(password);
    });

    // Manejar la visibilidad de la contraseña para nuevo usuario
    $('#togglePasswordNew').click(function() {
        const passwordInput = $('#new_password');
        const confirmInput = $('#new_password_confirm');
        const icon = $(this).find('svg');
        
        const type = passwordInput.attr('type') === 'password' ? 'text' : 'password';
        passwordInput.attr('type', type);
        confirmInput.attr('type', type);
        
        if (type === 'text') {
            icon.html('<path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755-.165.165-.337.328-.517.486l.708.709z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>');
        } else {
            icon.html('<path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7.028 7.028 0 0 0-2.79.588l.77.771A5.944 5.944 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.134 13.134 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755-.165.165-.337.328-.517.486l.708.709z"/><path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829l.822.822zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829z"/><path d="M3.35 5.47c-.18.16-.353.322-.518.487A13.134 13.134 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7.029 7.029 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12-.708.708z"/>');
        }
    });

    // Manejar el envío del formulario de nuevo usuario
    $('#newUserForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());

        // Validar que las contraseñas coincidan
        if (data.password !== data.password_confirm) {
            $('#errorMessage').text('Las contraseñas no coinciden');
            const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
            errorModal.show();
            return;
        }

        // Eliminar la confirmación de contraseña antes de enviar
        delete data.password_confirm;
        
        // Preparar los datos para enviar al controlador
        const requestData = {
            action: 'create',
            usuario_usuario: data.alias,
            nombre: data.nombre,
            apellido: data.apellido,
            password: data.password,
            rol_id: data.rol_id,
            departamento: data.departamento,
            modulo_id: data.modulo_id || ''
        };

        console.log('Datos a enviar:', requestData);

        fetch('../controllers/UsuarioController.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(requestData)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                const newUserModal = bootstrap.Modal.getInstance(document.getElementById('newUserModal'));
                newUserModal.hide();
                $('#confirmationMessage').text(result.message || 'Usuario creado exitosamente');
                const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
                confirmationModal.show();
                setTimeout(() => window.location.reload(), 1500);
            } else {
                $('#errorMessage').text(result.message || 'Error al crear el usuario');
                const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                errorModal.show();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            $('#errorMessage').text('Error al procesar la solicitud');
            const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
            errorModal.show();
        });
    });

    // Manejar el envío del formulario de edición
    $('#editUserForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());
        
        // Preparar los datos para enviar al controlador
        const requestData = {
            action: 'update',
            id: data.id,
            usuario_usuario: data.alias,
            nombre: data.nombre,
            apellido: data.apellido,
            rol_id: data.rol_id,
            departamento: data.departamento,
            modulo_id: data.modulo_id || ''
        };

        // Agregar contraseña solo si se proporcionó
        if (data.password) {
            requestData.password = data.password;
        }

        console.log('Datos de edición a enviar:', requestData);

        fetch('../controllers/UsuarioController.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(requestData)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                const editUserModal = bootstrap.Modal.getInstance(document.getElementById('editUserModal'));
                editUserModal.hide();
                $('#confirmationMessage').text(result.message || 'Usuario actualizado exitosamente');
                const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
                confirmationModal.show();
                setTimeout(() => window.location.reload(), 1500);
            } else {
                $('#errorMessage').text(result.message || 'Error al actualizar el usuario');
                const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                errorModal.show();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            $('#errorMessage').text('Error al procesar la solicitud');
            const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
            errorModal.show();
        });
    });
    
    // Manejar el cambio de estado
    $('.toggle-status').click(function() {
        const userId = $(this).data('id');
        const userName = $(this).data('nombre');
        const currentStatus = $(this).data('estado');
        const newStatus = currentStatus === 'Activo' ? 'Inactivo' : 'Activo';
        
        $('#actionType').text(currentStatus === 'Activo' ? 'deshabilitar' : 'habilitar');
        $('#userName').text(userName);
        const toggleStatusModal = new bootstrap.Modal(document.getElementById('toggleStatusModal'));
        toggleStatusModal.show();
        
        $('#confirmToggleStatus').off('click').on('click', function() {
            const data = {
                action: 'toggleStatus',
                id: userId,
                estado: currentStatus  // Enviamos el estado actual, no el nuevo
            };

            console.log('Datos de cambio de estado:', data);

            fetch('../controllers/UsuarioController.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                const toggleStatusModalInstance = bootstrap.Modal.getInstance(document.getElementById('toggleStatusModal'));
                toggleStatusModalInstance.hide();
                if (result.success) {
                    $('#confirmationMessage').text('Estado del usuario actualizado exitosamente');
                    const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
                    confirmationModal.show();
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    $('#errorMessage').text(result.message || 'Error al cambiar el estado del usuario');
                    const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                    errorModal.show();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                $('#errorMessage').text('Error al procesar la solicitud');
                const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                errorModal.show();
            });
        });
    });

    // Manejar el cambio de contraseña
    $('.change-password').click(function() {
        const userId = $(this).data('id');
        const userName = $(this).data('nombre');
        
        // Actualizar el título del modal con el nombre del usuario
        $('#changePasswordModalLabel').text(`Cambiar Contraseña - ${userName}`);
        
        // Establecer el ID del usuario en el formulario
        $('#change_password_id').val(userId);
        
        // Limpiar los campos de contraseña
        $('#change_password').val('');
        $('#change_password_confirm').val('');
    });
    
    // Manejar el botón de generar contraseña para cambio de contraseña
    $('#generatePasswordChange').click(function() {
        const password = generatePassword();
        $('#change_password').val(password);
        $('#change_password_confirm').val(password);
    });
    
    // Manejar la visibilidad de la contraseña para cambio de contraseña
    $('#togglePasswordChange').click(function() {
        const passwordInput = $('#change_password');
        const confirmInput = $('#change_password_confirm');
        const icon = $(this).find('svg');
        
        const type = passwordInput.attr('type') === 'password' ? 'text' : 'password';
        passwordInput.attr('type', type);
        confirmInput.attr('type', type);
        
        if (type === 'text') {
            icon.html('<path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755-.165.165-.337.328-.517.486l.708.709z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>');
        } else {
            icon.html('<path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7.028 7.028 0 0 0-2.79.588l.77.771A5.944 5.944 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.134 13.134 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755-.165.165-.337.328-.517.486l.708.709z"/><path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829l.822.822zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829z"/><path d="M3.35 5.47c-.18.16-.353.322-.518.487A13.134 13.134 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7.029 7.029 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12-.708.708z"/>');
        }
    });
    
    // Manejar el envío del formulario de cambio de contraseña
    $('#changePasswordForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());
        
        // Validar que las contraseñas coincidan
        if (data.password !== data.password_confirm) {
            $('#errorMessage').text('Las contraseñas no coinciden');
            const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
            errorModal.show();
            return;
        }
        
        // Eliminar la confirmación de contraseña antes de enviar
        delete data.password_confirm;
        
        // Preparar los datos para enviar al controlador
        const requestData = {
            action: 'change_password',
            id: data.id,
            password: data.password
        };
        
        console.log('Datos de cambio de contraseña:', requestData);
        
        fetch('../controllers/UsuarioController.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(requestData)
        })
        .then(response => response.json())
        .then(result => {
            const changePasswordModal = bootstrap.Modal.getInstance(document.getElementById('changePasswordModal'));
            changePasswordModal.hide();
            if (result.success) {
                $('#confirmationMessage').text(result.message || 'Contraseña actualizada exitosamente');
                const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
                confirmationModal.show();
            } else {
                $('#errorMessage').text(result.message || 'Error al actualizar la contraseña');
                const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                errorModal.show();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            $('#errorMessage').text('Error al procesar la solicitud');
            const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
            errorModal.show();
        });
    });

    // Filtrado de usuarios
    $('#searchInput').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('table tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    // Cuando el documento esté listo
    $(document).ready(function() {
        // Asegurarse de que los modales se limpien correctamente al cerrarse
        $('.modal').on('hidden.bs.modal', function() {
            // Eliminar cualquier backdrop que pueda haber quedado
            $('.modal-backdrop').remove();
            // Eliminar la clase modal-open del body
            $('body').removeClass('modal-open');
            // Eliminar el estilo inline que agrega Bootstrap
            $('body').css('padding-right', '');
            // Asegurarse de que el modal actual esté oculto
            $(this).modal('dispose');
        });
    });
});
