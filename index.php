<?php
session_start();
if (isset($_SESSION['user'])) {
    header("Location: dashboard.php"); // Redirigir si ya está logueado
    exit();
}

$error = isset($_GET['error']) ? "Invalid credentials" : "";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MINLEX | Loading...</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .loader-container {
            text-align: center;
        }
        .loader {
            width: 100px;
            height: 100px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #212529;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .loader-text {
            color: #212529;
            font-size: 1.2rem;
            font-weight: 500;
        } 
    </style>
</head>
<body>
    <div class="loader-container">
        <div class="loader"></div>
        <div class="loader-text">Cargando MINLEX...</div>
    </div>

    <script>
        setTimeout(function() {
            window.location.href = 'views/login.php';
        }, 2000);
    </script>
</body>
</html>
