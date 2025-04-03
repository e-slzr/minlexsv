document.addEventListener('DOMContentLoaded', function() {
    // Buscar el formulario de cierre de sesión
    const logoutForm = document.querySelector('form[action="../controllers/UsuarioController.php"]');
    
    if (logoutForm) {
        logoutForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Obtener los datos del formulario
            const formData = new FormData(this);
            
            // Enviar la solicitud mediante fetch
            fetch('../controllers/UsuarioController.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Si hay una URL de redirección, redirigir
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        // Si no hay redirección específica, ir a login.php
                        window.location.href = '../views/login.php';
                    }
                } else {
                    // Mostrar mensaje de error
                    alert(data.message || 'Error al cerrar sesión');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // En caso de error, redirigir a login.php
                window.location.href = '../views/login.php';
            });
        });
    }
});