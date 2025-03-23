<?php
session_start();
if (isset($_SESSION['user'])) {
    header("Location: home.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MPC System | Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style_login.css">
    <style>
        .login-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 1rem;
            text-align: center;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.875rem;
            background: rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .login-footer i {
            color: #0d6efd;
            margin-right: 0.5rem;
        }

        .login-footer span {
            opacity: 0.8;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    
    <div class="login-container">
        <div class="login-card">
            <div class="login-brand">
                <i class="fas fa-layer-group brand-icon"></i>
                <h1 class="brand-name">MPC System</h1>
                <div class="brand-subtitle">by ECODE</div>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    Usuario o contraseña incorrectos. Por favor intente nuevamente.
                </div>
            <?php endif; ?>

            <form id="loginForm" class="login-form">
                <input type="hidden" name="action" value="login">
                
                <div class="mb-3">
                    <label for="username" class="form-label">Usuario</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-user"></i>
                        </span>
                        <input type="text" 
                               class="form-control" 
                               id="username" 
                               name="username" 
                               required 
                               placeholder="Ingrese su nombre de usuario"
                               autocomplete="username">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" 
                               class="form-control" 
                               id="password" 
                               name="password" 
                               required
                               placeholder="Ingrese su contraseña"
                               autocomplete="current-password">
                        <button type="button" 
                                class="btn btn-outline-secondary" 
                                id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    Iniciar Sesión
                </button>
            </form>

            <div class="login-help">
                <i class="fas fa-info-circle me-1"></i>
                Utilice su nombre de usuario (alias) para ingresar
            </div>
        </div>
    </div>

    <footer class="login-footer">
        <i class="fas fa-code"></i>
        <span>Developed by ECODE | Software Development</span>
    </footer>

    <script>
    $(document).ready(function() {
        // Mostrar/ocultar contraseña
        $('#togglePassword').on('click', function() {
            const passwordInput = $('#password');
            const icon = $(this).find('i');
            
            if (passwordInput.attr('type') === 'password') {
                passwordInput.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                passwordInput.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });

        // Manejar el envío del formulario
        $('#loginForm').on('submit', function(e) {
            e.preventDefault();
            
            const username = $('#username').val().trim();
            const password = $('#password').val().trim();
            
            if (!username || !password) {
                alert('Por favor complete todos los campos');
                return;
            }
            
            const data = {
                action: 'login',
                username: username,
                password: password
            };

            // Deshabilitar el botón de envío
            const submitBtn = $(this).find('button[type="submit"]');
            submitBtn.prop('disabled', true);

            fetch('../controllers/UsuarioController.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    console.log('Redirigiendo a:', data.redirect);
                    window.location.replace(data.redirect);
                } else {
                    alert(data.message || 'Error al iniciar sesión');
                    submitBtn.prop('disabled', false);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al iniciar sesión');
                submitBtn.prop('disabled', false);
            });
        });
    });
    </script>
</body>
</html>
