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
    <link rel="stylesheet" href="../css/style_main.css">
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card p-4 shadow-sm">
            <h2 class="text-center fw-bold">MPC System | by ECODE</h2>
            <hr> 
            <h4 class="text-center">Inicio de sesión</h4>
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger text-center">
                    Credenciales inválidas. Por favor intente nuevamente.
                </div>
            <?php endif; ?>
            <form action="../controllers/UsuarioController.php?action=login" method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Usuario</label>
                    <input type="text" class="form-control" id="username" name="username" required 
                           placeholder="Ingrese su nombre de usuario">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password" required
                           placeholder="Ingrese su contraseña">
                </div>
                <button type="submit" class="btn btn-dark w-100">Iniciar Sesión</button>
            </form>
            <div class="mt-3 text-center">
                <small class="text-muted">Utilice su nombre de usuario (alias) para ingresar</small>
            </div>
        </div>
    </div>
</body>
</html>
