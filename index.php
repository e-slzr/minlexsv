<?php
session_start();
if (isset($_SESSION['user'])) {
    header("Location: views/home.php");
    exit();
}

header("Location: views/login.php");
exit();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MPC System | Loading...</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style_main.css">
</head>
<body>
    <div class="loader-container">
        <div class="loader"></div>
        <div class="loader-text">Cargando MPC System...</div>
    </div>
    <script src="js/loader.js"></script>
</body>
</html>
