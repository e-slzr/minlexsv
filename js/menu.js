document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('menuToggle');
    const menuLateral = document.getElementById('menuLateral');
    const mainContent = document.querySelector('main');
    
    // Recuperar el estado del menú del localStorage
    const menuState = localStorage.getItem('menuState');
    if (menuState === 'collapsed') {
        menuLateral.classList.add('collapsed');
        mainContent.classList.add('menu-collapsed');
    }

    // Función para colapsar el menú
    function collapseMenu() {
        menuLateral.classList.add('collapsed');
        mainContent.classList.add('menu-collapsed');
        localStorage.setItem('menuState', 'collapsed');
    }

    // Función para expandir el menú
    function expandMenu() {
        menuLateral.classList.remove('collapsed');
        mainContent.classList.remove('menu-collapsed');
        localStorage.setItem('menuState', 'expanded');
    }

    // Toggle del menú al hacer clic en el botón
    menuToggle.addEventListener('click', function() {
        if (menuLateral.classList.contains('collapsed')) {
            expandMenu();
        } else {
            collapseMenu();
        }
    });

    // Manejo de submenús en modo colapsado y toggle de submenús
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            // Obtener el target del submenú
            const targetId = this.getAttribute('data-bs-target');
            const subMenu = document.querySelector(targetId);
            
            // Si el menú está colapsado, primero expandir el menú
            if (menuLateral.classList.contains('collapsed')) {
                e.preventDefault();
                expandMenu();
                
                // Esperar un poco y luego abrir el submenú
                setTimeout(() => {
                    if (subMenu && !subMenu.classList.contains('show')) {
                        const bsCollapse = new bootstrap.Collapse(subMenu);
                        bsCollapse.show();
                    }
                }, 300);
            } else {
                // Si el menú ya está expandido, toggle el submenú manualmente
                // Esto permite colapsar el submenú al hacer clic nuevamente
                if (subMenu) {
                    e.preventDefault(); // Prevenir el comportamiento predeterminado
                    if (subMenu.classList.contains('show')) {
                        const bsCollapse = new bootstrap.Collapse(subMenu);
                        bsCollapse.hide();
                    } else {
                        const bsCollapse = new bootstrap.Collapse(subMenu);
                        bsCollapse.show();
                    }
                }
            }
        });
    });

    // Si el menú está colapsado, añadir eventos de hover
    if (window.innerWidth > 768) { // Solo para pantallas más grandes que móviles
        // No necesitamos agregar eventos hover ya que lo manejamos con CSS
        // pero podemos agregar lógica adicional si es necesario
    }
});
