// Aquí irá el código JavaScript general del proyecto
// Por ejemplo, funciones para las tablas, filtros, etc.

// Funciones para manejar clientes
const handleClientes = {
    // Variables para el modal de estado
    clienteIdToToggle: null,
    newEstado: null,

    // Inicializar eventos
    init: function() {
        this.initEditCliente();
        this.initNewCliente();
        this.initToggleStatus();
        this.initFormSubmit();
    },

    // Editar cliente
    initEditCliente: function() {
        $('.edit-cliente').click(function() {
            const id = $(this).data('id');
            const empresa = $(this).data('empresa');
            const contacto = $(this).data('contacto');
            const telefono = $(this).data('telefono');
            const email = $(this).data('email');

            $('#clienteId').val(id);
            $('#empresa').val(empresa);
            $('#contacto').val(contacto);
            $('#telefono').val(telefono);
            $('#email').val(email);
            $('#formAction').val('update');
            $('#clienteModalLabel').text('Editar Cliente');
        });
    },

    // Nuevo cliente
    initNewCliente: function() {
        $('[data-bs-target="#clienteModal"]').click(function() {
            if (!$(this).hasClass('edit-cliente')) {
                $('#clienteForm')[0].reset();
                $('#clienteId').val('');
                $('#formAction').val('create');
                $('#clienteModalLabel').text('Nuevo Cliente');
            }
        });
    },

    // Toggle estado
    initToggleStatus: function() {
        $('.toggle-status').click(function() {
            handleClientes.clienteIdToToggle = $(this).data('id');
            handleClientes.newEstado = $(this).data('estado');
        });

        $('#confirmToggleStatus').click(function() {
            if (handleClientes.clienteIdToToggle && handleClientes.newEstado) {
                $.ajax({
                    url: '../controllers/ClienteController.php',
                    type: 'POST',
                    data: {
                        action: 'toggleStatus',
                        id: handleClientes.clienteIdToToggle,
                        estado: handleClientes.newEstado
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert('Error al cambiar el estado del cliente');
                        }
                    },
                    error: function() {
                        alert('Error al procesar la solicitud');
                    }
                });
            }
        });
    },

    // Submit form
    initFormSubmit: function() {
        $('#clienteForm').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: '../controllers/ClienteController.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error al procesar la solicitud');
                }
            });
        });
    }
};

// Funciones para manejar roles
const handleRoles = {
    // Variables para el modal de estado
    rolIdToToggle: null,
    newEstado: null,

    // Inicializar eventos
    init: function() {
        this.initEditRol();
        this.initNewRol();
        this.initToggleStatus();
        this.initFormSubmit();
    },

    // Editar rol
    initEditRol: function() {
        $('.edit-rol').click(function() {
            const id = $(this).data('id');
            const nombre = $(this).data('nombre');
            const descripcion = $(this).data('descripcion');

            $('#rolId').val(id);
            $('#nombre').val(nombre);
            $('#descripcion').val(descripcion);
            $('#formAction').val('update');
            $('#rolModalLabel').text('Editar Rol');
        });
    },

    // Nuevo rol
    initNewRol: function() {
        $('[data-bs-target="#rolModal"]').click(function() {
            if (!$(this).hasClass('edit-rol')) {
                $('#rolForm')[0].reset();
                $('#rolId').val('');
                $('#formAction').val('create');
                $('#rolModalLabel').text('Nuevo Rol');
            }
        });
    },

    // Toggle estado
    initToggleStatus: function() {
        $('.toggle-status').click(function() {
            handleRoles.rolIdToToggle = $(this).data('id');
            handleRoles.newEstado = $(this).data('estado');
        });

        $('#confirmToggleStatus').click(function() {
            if (handleRoles.rolIdToToggle && handleRoles.newEstado) {
                $.ajax({
                    url: '../controllers/RolController.php',
                    type: 'POST',
                    data: {
                        action: 'toggleStatus',
                        id: handleRoles.rolIdToToggle,
                        estado: handleRoles.newEstado
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert('Error al cambiar el estado del rol');
                        }
                    },
                    error: function() {
                        alert('Error al procesar la solicitud');
                    }
                });
            }
        });
    },

    // Submit form
    initFormSubmit: function() {
        $('#rolForm').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: '../controllers/RolController.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error al procesar la solicitud');
                }
            });
        });
    }
};

// Funciones para manejar usuarios
const handleUsuarios = {
    // Variables para el modal de estado
    usuarioIdToToggle: null,
    newEstado: null,

    // Inicializar eventos
    init: function() {
        this.initEditUsuario();
        this.initNewUsuario();
        this.initToggleStatus();
        this.initFormSubmit();
        this.initGeneratePassword();
        this.initModalClose();
    },

    // Generar contraseña aleatoria
    generateRandomPassword: function() {
        const length = 8;
        const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        let password = "";
        for (let i = 0; i < length; i++) {
            password += charset.charAt(Math.floor(Math.random() * charset.length));
        }
        return password;
    },

    // Inicializar generador de contraseña
    initGeneratePassword: function() {
        $('#generatePassword').click(function() {
            const password = handleUsuarios.generateRandomPassword();
            $('#password').val(password);
        });
    },

    // Editar usuario
    initEditUsuario: function() {
        $('.edit-usuario').click(function() {
            const id = $(this).data('id');
            const nombre = $(this).data('nombre');
            const usuario = $(this).data('usuario');
            const email = $(this).data('email');
            const rolId = $(this).data('rol-id');

            $('#usuarioId').val(id);
            $('#nombre').val(nombre);
            $('#usuario').val(usuario);
            $('#email').val(email);
            $('#rol_id').val(rolId);
            $('#formAction').val('update');
            $('#usuarioModalLabel').text('Editar Usuario');
            $('#password').prop('required', false);
        });
    },

    // Nuevo usuario
    initNewUsuario: function() {
        $('[data-bs-target="#usuarioModal"]').click(function() {
            if (!$(this).hasClass('edit-usuario')) {
                $('#usuarioForm')[0].reset();
                $('#usuarioId').val('');
                $('#formAction').val('create');
                $('#usuarioModalLabel').text('Nuevo Usuario');
                $('#password').prop('required', true);
            }
        });
    },

    // Toggle estado
    initToggleStatus: function() {
        $('.toggle-status').click(function() {
            handleUsuarios.usuarioIdToToggle = $(this).data('id');
            handleUsuarios.newEstado = $(this).data('estado');
        });

        $('#confirmToggleStatus').click(function() {
            if (handleUsuarios.usuarioIdToToggle && handleUsuarios.newEstado) {
                $.ajax({
                    url: '../controllers/UsuarioController.php',
                    type: 'POST',
                    data: {
                        action: 'toggleStatus',
                        id: handleUsuarios.usuarioIdToToggle,
                        estado: handleUsuarios.newEstado
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert('Error al cambiar el estado del usuario');
                        }
                    },
                    error: function() {
                        alert('Error al procesar la solicitud');
                    }
                });
            }
        });
    },

    // Submit form
    initFormSubmit: function() {
        $('#usuarioForm').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: '../controllers/UsuarioController.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#usuarioModal').modal('hide');
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    alert('Error al procesar la solicitud: ' + error);
                }
            });
        });
    },

    // Limpiar modal al cerrarlo
    initModalClose: function() {
        $('#usuarioModal').on('hidden.bs.modal', function() {
            $('#usuarioForm')[0].reset();
            $('#formAction').val('create');
            $('#usuarioId').val('');
            $('#usuarioModalLabel').text('Nuevo Usuario');
            $('#password').attr('required', 'required');
        });
    }
};

// Funciones para el dashboard
const handleDashboard = {
    init: function() {
        if (document.getElementById('posChart') && document.getElementById('estadosChart')) {
            this.loadChartData();
        }
    },

    loadChartData: function() {
        $.ajax({
            url: '../controllers/DashboardController.php',
            method: 'POST',
            data: { action: 'getChartData' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Gráfico de POs por mes
                    const posCtx = document.getElementById('posChart').getContext('2d');
                    new Chart(posCtx, {
                        type: 'line',
                        data: {
                            labels: response.posPorMes.labels,
                            datasets: [{
                                label: 'POs Creadas',
                                data: response.posPorMes.data,
                                borderColor: 'rgba(54, 162, 235, 1)',
                                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1
                                    }
                                }
                            }
                        }
                    });

                    // Gráfico de Estado de POs
                    const estadosCtx = document.getElementById('estadosChart').getContext('2d');
                    new Chart(estadosCtx, {
                        type: 'doughnut',
                        data: {
                            labels: response.estadosPo.labels,
                            datasets: [{
                                data: response.estadosPo.data,
                                backgroundColor: [
                                    'rgba(255, 193, 7, 0.8)',    // Pendiente (warning)
                                    'rgba(13, 110, 253, 0.8)',   // En proceso (primary)
                                    'rgba(25, 135, 84, 0.8)',    // Completada (success)
                                    'rgba(220, 53, 69, 0.8)'     // Cancelada (danger)
                                ],
                                borderColor: [
                                    'rgba(255, 193, 7, 1)',      // Pendiente
                                    'rgba(13, 110, 253, 1)',     // En proceso
                                    'rgba(25, 135, 84, 1)',      // Completada
                                    'rgba(220, 53, 69, 1)'       // Cancelada
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                }
                            }
                        }
                    });
                } else {
                    console.error('Error al cargar datos:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en la petición AJAX:', error);
            }
        });
    }
};

// Inicializar todos los manejadores cuando el documento esté listo
$(document).ready(function() {
    // Inicializar manejadores según la página actual
    if (document.getElementById('clienteForm')) {
        handleClientes.init();
    }
    if (document.getElementById('usuarioForm')) {
        handleUsuarios.init();
    }
    if (document.getElementById('rolForm')) {
        handleRoles.init();
    }
    if (document.getElementById('posChart')) {
        handleDashboard.init();
    }
});
