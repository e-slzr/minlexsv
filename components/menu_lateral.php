<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user'])) {
    header("Location: ../views/login.php");
    exit();
}

// Obtener la inicial del nombre del usuario para el avatar
$nombreCompleto = $_SESSION['user']['nombre_completo'] ?? 'Usuario';
$inicial = strtoupper(substr($nombreCompleto, 0, 1));
$rol = $_SESSION['user']['rol_nombre'] ?? 'Usuario';
?>

<link rel="stylesheet" href="../css/style_menu.css">
<script src="../js/menu.js"></script>

<div class="barra_superior">
    <div class="brand">
        <i class="fas fa-layer-group"></i>
        <span>MPC System | by ECODE</span>
    </div>
    <div class="link_login">
        <div class="notifications">
            <i class="fas fa-bell"></i>
            <div class="notifications-badge">3</div>
        </div>
        <div class="user-profile">
            <div class="user-avatar">
                <?php echo htmlspecialchars($inicial); ?>
            </div>
            <div class="user-info">
                <div class="user-name"><?php echo htmlspecialchars($nombreCompleto); ?></div>
                <div class="user-role"><?php echo htmlspecialchars($rol); ?></div>
            </div>
        </div>
        <a href="#" class="logout-btn" data-bs-toggle="modal" data-bs-target="#logoutModal">
            <i class="fas fa-sign-out-alt"></i>
            <span>Cerrar Sesión</span>
        </a>
    </div>
</div>

<!-- Modal de Cierre de Sesión -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logoutModalLabel">Confirmar Cierre de Sesión</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea cerrar la sesión?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form action="../controllers/UsuarioController.php" method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="logout">
                    <button type="submit" class="btn btn-dark">Cerrar Sesión</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Botón hamburguesa -->
<button id="menuToggle" class="menu-toggle-btn">
    <i class="fas fa-bars"></i>
</button>

<nav id="menuLateral" class="menu-lateral">
    <ul class="list-group">
        <a href="../views/home.php">    
            <li>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 9L12 2L21 9V20C21 20.5304 20.7893 21.0391 20.4142 21.4142C20.0391 21.7893 19.5304 22 19 22H5C4.46957 22 3.96086 21.7893 3.58579 21.4142C3.21071 21.0391 3 20.5304 3 20V9Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M9 22V12H15V22" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Dashboard</span>
            </li>
        </a>
        <a href="#" class="dropdown-toggle" data-bs-toggle="collapse" data-bs-target="#subMenuProduccion">
            <li>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2L20 6V18L12 22L4 18V6L12 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M12 22V12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M20 6L12 12L4 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Producción</span>
            </li>
        </a>
        <ul id="subMenuProduccion" class="collapse list-group">
            <a href="../views/po.php"><li><span>PO (Purchase Orders)</span></li></a>
            <a href="../views/ordenes_produccion.php"><li><span>Órdenes de Producción</span></li></a>
            <a href="../views/programacion_ordenes.php"><li><span>Programación de Órdenes</span></li></a>
            <a href="../views/actualizar_produccion.php"><li><span>Actualizar Producción</span></li></a>
            <a href="../views/registro_produccion.php"><li><span>Registro de Producción</span></li></a>
            <a href="../views/items.php"><li><span>Items</span></li></a>
            <a href="../views/procesos_produccion.php"><li><span>Procesos de producción</span></li></a>
        </ul>
        <a href="#" class="dropdown-toggle" data-bs-toggle="collapse" data-bs-target="#subMenuAlmacen">
            <li>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 5H7C6.46957 5 5.96086 5.21071 5.58579 5.58579C5.21071 5.96086 5 6.46957 5 7V19C5 19.5304 5.21071 20.0391 5.58579 20.4142C5.96086 20.7893 6.46957 21 7 21H17C17.5304 21 18.0391 20.7893 18.4142 20.4142C18.7893 20.0391 19 19.5304 19 19V7C19 6.46957 18.7893 5.96086 18.4142 5.58579C18.0391 5.21071 17.5304 5 17 5H15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M12 12H15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M12 16H15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M9 8H15V12H9V8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Almacén</span>
            </li>
        </a>
        <ul id="subMenuAlmacen" class="collapse list-group">
            <a href="../views/bodegas.php"><li><span>Bodegas</span></li></a>
        </ul>
        <a href="#" class="dropdown-toggle" data-bs-toggle="collapse" data-bs-target="#subMenuCalidad">
            <li>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 12L11 14L15 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Control de Calidad</span>
            </li>
        </a>
        <ul id="subMenuCalidad" class="collapse list-group">
            <a href="../views/pruebas_calidad.php"><li><span>Pruebas de Calidad</span></li></a>
            <a href="../views/tipos_pruebas.php"><li><span>Tipos de Pruebas</span></li></a>
            <a href="../views/resultados_pruebas.php"><li><span>Resultados de Pruebas</span></li></a>
        </ul>
        <a href="#" class="dropdown-toggle" data-bs-toggle="collapse" data-bs-target="#subMenuReportes">
            <li>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M19 3H5C3.89543 3 3 3.89543 3 5V19C3 20.1046 3.89543 21 5 21H19C20.1046 21 21 20.1046 21 19V5C21 3.89543 20.1046 3 19 3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M8 15V17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M12 11V17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M16 7V17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Reportes</span>
            </li>
        </a>
        <ul id="subMenuReportes" class="collapse list-group">
            <a href="../views/reporte_calidad.php"><li><span>Reporte de Calidad</span></li></a>
            <a href="../views/reporte_corte.php"><li><span>Reporte de Corte</span></li></a>
            <a href="../views/reporte_costura.php"><li><span>Reporte de Costura</span></li></a>
            <a href="../views/reporte_produccion.php"><li><span>Reporte de Produccion</span></li></a>
            <a href="../views/reporte_aprobaciones.php"><li><span>Reporte de Aprobaciones</span></li></a>
        </ul>
        <a href="#" class="dropdown-toggle" data-bs-toggle="collapse" data-bs-target="#subMenuAdmin">
            <li>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M19.4 15C19.7 14.1 19.9 13.1 19.9 12C19.9 10.9 19.7 9.9 19.4 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M4.6 15C4.3 14.1 4.1 13.1 4.1 12C4.1 10.9 4.3 9.9 4.6 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M17.7 17.7C16.9 18.4 15.9 19 14.8 19.4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M6.3 17.7C7.1 18.4 8.1 19 9.2 19.4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M9.2 4.6C8.1 5 7.1 5.6 6.3 6.3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M14.8 4.6C15.9 5 16.9 5.6 17.7 6.3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Administración</span>
            </li>
        </a>
        <ul id="subMenuAdmin" class="collapse list-group">
            <a href="../views/clientes.php"><li><span>Clientes</span></li></a>
            <a href="../views/usuarios.php"><li><span>Usuarios</span></li></a>
            <a href="../views/roles.php"><li><span>Roles</span></li></a>
            <a href="../views/modulos.php"><li><span>Módulos</span></li></a>
            <a href="../views/aprobaciones.php"><li><span>Aprobaciones</span></li></a>
            <a href="../views/modificaciones.php"><li><span>Modificaciones</span></li></a>
        </ul>
        <a href="#" class="dropdown-toggle" data-bs-toggle="collapse" data-bs-target="#subMenuAyuda">
            <li>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M18 8C19.1046 8 20 7.10457 20 6C20 4.89543 19.1046 4 18 4C16.8954 4 16 4.89543 16 6C16 7.10457 16.8954 8 18 8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M18 8V12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M18 16C19.1046 16 20 15.1046 20 14C20 12.8954 19.1046 12 18 12C16.8954 12 16 12.8954 16 14C16 15.1046 16.8954 16 18 16Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M9 15V17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M9 21V19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M4 17H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M12 17H14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M9 17C10.1046 17 11 16.1046 11 15C11 13.8954 10.1046 13 9 13C7.89543 13 7 13.8954 7 15C7 16.1046 7.89543 17 9 17Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M9 17C10.1046 17 11 17.8954 11 19C11 20.1046 10.1046 21 9 21C7.89543 21 7 20.1046 7 19C7 17.8954 7.89543 17 9 17Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Ayuda</span>
            </li>
        </a>
        <ul id="subMenuAyuda" class="collapse list-group">
            <a href="../views/manual_usuario.php"><li><span>Manual de usuario</span></li></a>
            <a href="../views/recursos.php"><li><span>Recursos</span></li></a>
        </ul>
    </ul>
</nav>

<div id="loader" style="display: none;">
    <div class="spinner"></div>
</div>

<!-- Asegurarnos de que jQuery esté cargado primero -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap 5 Bundle (incluye Popper.js) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Inicializar los dropdowns de Bootstrap 5
document.addEventListener('DOMContentLoaded', function() {
    // Manejar clics en los enlaces del menú
    document.querySelectorAll('.menu-lateral a').forEach(function(link) {
        link.addEventListener('click', function(e) {
            // Si el enlace tiene href y no es un toggle de dropdown
            if (this.getAttribute('href') && !this.classList.contains('dropdown-toggle')) {
                window.location.href = this.getAttribute('href');
            }
        });
    });

    // Manejar el comportamiento de colapso de los submenús
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('data-bs-target');
            const targetCollapse = document.querySelector(targetId);
            
            // Cerrar todos los otros submenús
            document.querySelectorAll('.collapse.show').forEach(collapse => {
                if (collapse !== targetCollapse) {
                    collapse.classList.remove('show');
                }
            });

            // Toggle el submenú actual
            if (targetCollapse) {
                targetCollapse.classList.toggle('show');
            }
        });
    });
});
</script>
