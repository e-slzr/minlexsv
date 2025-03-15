<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
require_once '../models/Usuario.php';
$usuarios = new Usuario();
$counts = $usuarios->countUsers();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MINLEX | Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style_main.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
    <?php include '../components/loader.php'; ?>
    <?php include '../components/menu_lateral.php'; ?>
    
    <main>
        <div style="width: 100%;" class="border-bottom border-secondary">
            <h1><strong>Dashboard</strong></h1><br>
        </div>
        <div class="tarjeta_dashboard">Total de PO's <br><span style="font-size: 26pt;">100</span></div>
        <div class="tarjeta_dashboard">PO's en producción <br><span style="font-size: 26pt;">17</span></div>
        <div class="tarjeta_dashboard">Ordenes completadas <br><span style="font-size: 26pt;">780</span></div>
        <div class="tarjeta_dashboard">Totales <br><span style="font-size: 26pt;">1,557</span></div>
        <div style="width: 100%;" class="border-bottom border-secondary">
            <br>
        </div>
        <div class="tarjeta_dashboard">Clientes <br><span style="font-size: 26pt;">100</span></div>
        <div class="tarjeta_dashboard">Proveedores <br><span style="font-size: 26pt;">45</span></div>
        <div class="tarjeta_dashboard">Usuarios del sistema <br><span style="font-size: 26pt;"><?php echo $counts; ?></span></div>
    </main>

    <!-- Modal de Confirmación de Cierre de Sesión -->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logoutModalLabel">Confirmar Cierre de Sesión</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    ¿Está seguro que desea cerrar sesión?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
                    <a href="../controllers/UsuarioController.php?action=logout" class="btn btn-dark">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(window).on('load', function() {
            $('#loader').fadeOut('slow');
        });
    </script>
<?php include '../components/footer.php'; ?>
</body>
</html>