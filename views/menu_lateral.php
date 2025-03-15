<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}
?>
<link rel="stylesheet" href="../css/style_menu.css">
<div class="barra_superior">
    <div>
     MPC System | by ECODE
    </div>
    <div class="link_login">
        <?php echo htmlspecialchars($_SESSION['user']['usuario_nombre'] . ' ' . $_SESSION['user']['usuario_apellido']); ?>
        <span> | </span>
        <a href="#" data-toggle="modal" data-target="#logoutModal">Salir</a>
    </div>
</div>

<!-- Botón hamburguesa flotante -->
<button id="menuToggle" class="menu-toggle-btn">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M3 12H21M3 6H21M3 18H21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
    </svg>
</button>

<nav id="menuLateral" class="menu-lateral">
        <ul class="list-group">
            <li>
                <img src="../resources/img/menu/10132075_home_line_icon.svg" alt="">
                <a href="home.php" class="">Dashboard</a></li>
            <li>
                <img src="../resources/img/menu//10132197_box_package_icon.svg" alt="">
                <a href="" class="dropdown-toggle" data-toggle="collapse" data-target="#subMenuProduccion">Producción</a></li>
                <ul id="subMenuProduccion" class="collapse list-group">
                    <li><a href="po.php" class="">PO (Purchase Orders)</a></li>
                    <li><a href="#" class="">Ordenes de producción</a></li>
                </ul>
            <li>
                <img src="../resources/img/menu/10132108_clipboard_check_line_icon.svg" alt="">
                <a href="" class="dropdown-toggle" data-toggle="collapse" data-target="#subMenuAlmacen">Almacén</a></li>
                <ul id="subMenuAlmacen" class="collapse list-group">
                    <li><a href="" class="">Bodegas</a></li>
                </ul>
            <li>
                <img src="../resources/img/menu/10132156_presentation_chart_line_icon.svg" alt="">
                <a href="" class="dropdown-toggle" data-toggle="collapse" data-target="#subMenuReportes">Reportes</a></li>
                <ul id="subMenuReportes" class="collapse list-group">
                    <li><a href="" class="">Reporte de Calidad</a></li>
                    <li><a href="" class="">Reporte de Corte</a></li>
                    <li><a href="" class="">Reporte de Costura</a></li>
                    <li><a href="" class="">Reporte de Produccion</a></li>
                </ul>                
            <li>
                <img src="../resources/img/menu/10131851_settings_cog_line_icon.svg" alt="">
                <a href="" class="dropdown-toggle" data-toggle="collapse" data-target="#subMenuAdmin">Administración</a></li>
                <ul id="subMenuAdmin" class="collapse list-group">
                    <li><a href="./usuarios.php" class="">Usuarios</a></li>
                    <li><a href="./roles.php" class="">Roles</a></li>
                </ul>                
            <li>
                <img src="../resources/img/menu/10131911_megaphone_line_icon.svg" alt="">
                <a href="" class="dropdown-toggle" data-toggle="collapse" data-target="#subMenuAyuda">Ayuda</a></li>
                <ul id="subMenuAyuda" class="collapse list-group">
                    <li><a href="" class="">Acerca de</a></li>
                    <li><a href="" class="">Manual de usuario</a></li>
                    <li><a href="" class="">Recursos</a></li>
                </ul>
        </ul>
    </nav>

<div id="loader" style="display: none;">
    <div class="spinner"></div>
</div>

<!-- Modal de confirmación de cierre de sesión -->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logoutModalLabel">Cerrar sesión</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                ¿Estás seguro de que deseas cerrar sesión?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="logout()">Cerrar sesión</button>
            </div>
        </div>
    </div>
</div>

<script>
function logout() {
    $('#loader').show();
    $.ajax({
        url: '../controllers/UsuarioController.php',
        type: 'GET',
        data: { action: 'logout' },
        success: function() {
            window.location.href = '../views/login.php';
        },
        error: function() {
            alert('Error al cerrar sesión');
            $('#loader').hide();
        }
    });
}
</script>
<script src="../js/menu_lateral.js"></script>