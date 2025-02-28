<div class="barra_superior">
    <div>
    MPC System | Minlex El Salvador S.A. de C.V.
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

<style>
/* ... estilos anteriores de barra_superior ... */

.menu-toggle-btn {
    position: fixed;
    bottom: 20px;
    left: 20px;
    z-index: 1001;
    background: #212529;
    border: none;
    border-radius: 50%;
    width: 45px;
    height: 45px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.menu-toggle-btn svg {
    color: white;
}

.menu-lateral {
    position: fixed;
    top: 36px;
    left: 0;
    height: calc(100vh - 36px);
    width: 250px;
    background: #f8f9fa;
    transition: width 0.3s ease-in-out;
    z-index: 1000;
    overflow-x: hidden;
}

.list-group li {
    display: flex;
    align-items: center;
    padding: 0.5rem;
    padding-left: 1rem;
    cursor: pointer;
}

.list-group li img {
    width: 24px;
    height: 24px;
    min-width: 24px;
    pointer-events: none;
}

.list-group li a {
    margin-left: 1rem;
    white-space: nowrap;
    transition: opacity 0.2s ease-in-out;
    pointer-events: none;
}

/* Estilos para el menú reducido */
.menu-lateral.hidden {
    width: 85px;
}

.menu-lateral.hidden h2,
.menu-lateral.hidden .list-group li a,
.menu-lateral.hidden .collapse {
    display: none;
}

.menu-lateral.hidden .list-group li {
    justify-content: center;
    padding: 0.5rem 0;
}

.menu-lateral.expanded {
    width: 250px;
}

.menu-lateral.expanded h2,
.menu-lateral.expanded .list-group li a {
    display: block;
}

.menu-lateral.expanded .list-group li {
    justify-content: flex-start;
    padding: 0.5rem;
    padding-left: 1rem;
}

.menu-lateral.expanded .collapse {
    display: none; /* Inicialmente oculto */
}

.menu-lateral.expanded .collapse.show {
    display: block; /* Se muestra cuando tiene la clase show */
}

/* Ajuste para el contenido principal */
main {
    margin-top: 36px;
    margin-left: 250px;
    transition: margin-left 0.3s ease-in-out;
}

main.reduced {
    margin-left: 85px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('menuToggle');
    const menuLateral = document.getElementById('menuLateral');
    const mainContent = document.querySelector('main');
    let expandTimeout;
    
    // Toggle del botón de menú
    menuToggle.addEventListener('click', function() {
        menuLateral.classList.toggle('hidden');
        mainContent.classList.toggle('reduced');
        
        if (!menuLateral.classList.contains('hidden')) {
            menuLateral.classList.remove('expanded');
        }
    });

    // Manejador para los items del menú (hover)
    document.querySelectorAll('.list-group > li').forEach(item => {
        item.addEventListener('mouseenter', function() {
            if (menuLateral.classList.contains('hidden')) {
                clearTimeout(expandTimeout);
                menuLateral.classList.add('expanded');
            }
        });

        // Click handler
        item.addEventListener('click', function(e) {
            const link = this.querySelector('a');
            if (!link) return;

            if (link.classList.contains('dropdown-toggle')) {
                // Es un ítem con submenú
                e.preventDefault();
                const targetId = link.getAttribute('data-target');
                const targetCollapse = document.querySelector(targetId);
                
                if (targetCollapse) {
                    // Toggle del submenú
                    const wasActive = targetCollapse.classList.contains('show');
                    
                    // Cerrar todos los submenús
                    document.querySelectorAll('.collapse.show').forEach(collapse => {
                        collapse.classList.remove('show');
                    });

                    // Abrir el submenú si estaba cerrado
                    if (!wasActive) {
                        targetCollapse.classList.add('show');
                    }
                }
            } else {
                // Es un enlace normal, navegar a la URL
                window.location.href = link.getAttribute('href');
            }
        });
    });

    // Manejador para los items de submenús
    document.querySelectorAll('.collapse .list-group li').forEach(item => {
        item.addEventListener('click', function(e) {
            e.stopPropagation();
            const link = this.querySelector('a');
            if (link && link.getAttribute('href')) {
                window.location.href = link.getAttribute('href');
            }
        });
    });

    // Manejador para el menú lateral
    menuLateral.addEventListener('mouseleave', function() {
        if (menuLateral.classList.contains('hidden')) {
            expandTimeout = setTimeout(() => {
                menuLateral.classList.remove('expanded');
                // Cerrar todos los submenús al contraer
                document.querySelectorAll('.collapse.show').forEach(collapse => {
                    collapse.classList.remove('show');
                });
            }, 300);
        }
    });
});
</script>