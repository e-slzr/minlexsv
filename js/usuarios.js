document.addEventListener('DOMContentLoaded', function() {
    // Función para mostrar los datos en el modal de edición
    const editButtons = document.querySelectorAll('.edit-usuario');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const usuario = this.getAttribute('data-usuario');
            const nombre = this.getAttribute('data-nombre');
            const apellido = this.getAttribute('data-apellido');
            const departamento = this.getAttribute('data-departamento');
            const rol = this.getAttribute('data-rol');
            const modulo = this.getAttribute('data-modulo');

            document.getElementById('editar_id').value = id;
            document.getElementById('editar_usuario').value = usuario;
            document.getElementById('editar_nombre').value = nombre;
            document.getElementById('editar_apellido').value = apellido;
            document.getElementById('editar_departamento').value = departamento || '';
            document.getElementById('editar_rol_id').value = rol;
            document.getElementById('editar_modulo_id').value = modulo || '';
            document.getElementById('editar_password').value = '';
        });
    });

    // Manejar el envío del formulario de edición
    document.getElementById('guardarEditarUsuario').addEventListener('click', function() {
        const form = document.getElementById('editarUsuarioForm');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        
        // Si la contraseña está vacía, la eliminamos del objeto
        if (!data.password) {
            delete data.password;
        }

        fetch('../controllers/UsuarioController.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'update',
                ...data
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return response.json();
        })
        .then(result => {
            if (result.success) {
                alert('Usuario actualizado exitosamente');
                window.location.reload();
            } else {
                alert(result.message || 'Error al actualizar el usuario');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al actualizar el usuario');
        });
    });

    // Manejar el cambio de estado
    const toggleButtons = document.querySelectorAll('.toggle-status');
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-id');
            const currentStatus = this.getAttribute('data-estado');
            
            document.getElementById('confirmStatus').onclick = function() {
                const data = {
                    action: 'toggleStatus',
                    id: userId,
                    estado: currentStatus
                };

                fetch('../controllers/UsuarioController.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        window.location.reload();
                    } else {
                        alert(result.message || 'Error al cambiar el estado');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al cambiar el estado');
                });
            };
        });
    });

    // Filtros
    const filtros = document.querySelectorAll('.filtro');
    filtros.forEach(filtro => {
        filtro.addEventListener('input', function() {
            const filas = document.querySelectorAll('#tabla-usuarios tbody tr');
            filas.forEach(fila => {
                let mostrar = true;
                filtros.forEach(f => {
                    if (f.value) {
                        const texto = fila.textContent.toLowerCase();
                        if (!texto.includes(f.value.toLowerCase())) {
                            mostrar = false;
                        }
                    }
                });
                fila.style.display = mostrar ? '' : 'none';
            });
        });
    });

    // Limpiar filtros
    document.getElementById('limpiar-filtros')?.addEventListener('click', function() {
        filtros.forEach(filtro => {
            filtro.value = '';
        });
        const filas = document.querySelectorAll('#tabla-usuarios tbody tr');
        filas.forEach(fila => fila.style.display = '');
    });
});
