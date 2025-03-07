<?php
session_start();
if (isset($_SESSION['user'])) {
    header("Location: home.php"); // Redirigir si ya está logueado
    exit();
}

$error = isset($_GET['error']) ? "Invalid credentials" : "";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MPC System | Login</title> <!--MINLEX Production Control -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card p-4 shadow-sm">
            <h2 class="text-center fw-bold">MPC System | by ECODE</h2>
            <hr> 
            <h4 class="text-center">Inicio de sesion</h4>
            <!-- <p class="text-center text-muted">Ingresa tus credenciales para acceder al sistema</p> -->
            <?php if ($error): ?>
                <p class="text-danger text-center"><?php echo $error; ?></p>
            <?php endif; ?>
            <form action="home.php" method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Usuario</label>
                    <input type="text" class="form-control" name="email" placeholder="">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" name="password" placeholder="">
                </div>
                <button type="submit" class="btn btn-dark w-100">Iniciar Sesión</button>
            </form>
        </div>
    </div>
</body>
</html>
