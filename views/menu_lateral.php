<link rel="stylesheet" href="../css/style_menu.css">
<div class="barra_superior">
    <div>
    <!-- MPC System | Minlex El Salvador S.A. de C.V. -->
     MPC System | by ECODE
    </div>
    <div class="link_login">
        Administrador
        <span> | </span>
        <a href="../index.php">Salir</a>
    </div>
</div>

<!-- Botón hamburguesa flotante -->
<button id="menuToggle" class="menu-toggle-btn">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M3 12H21M3 6H21M3 18H21" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
    </svg>
</button>

<nav id="menuLateral" class="menu-lateral">
        <!-- <h2><strong>Menu</strong></h2> -->
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
                    <li><a href="" class="">Usuarios</a></li>
                    <li><a href="" class="">Roles</a></li>
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

<script src="../js/menu_lateral.js"></script>