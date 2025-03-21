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

    // Manejo de submenús en modo colapsado
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            if (menuLateral.classList.contains('collapsed')) {
                e.preventDefault();
                expandMenu();
                
                // Esperar un poco y luego abrir el submenú
                setTimeout(() => {
                    const target = this.getAttribute('data-bs-target');
                    const subMenu = document.querySelector(target);
                    if (subMenu && !subMenu.classList.contains('show')) {
                        const bsCollapse = new bootstrap.Collapse(subMenu);
                        bsCollapse.show();
                    }
                }, 300);
            }
        });
    });

    // Si el menú está colapsado, añadir eventos de hover
    if (window.innerWidth > 768) { // Solo para pantallas más grandes que móviles
        // No necesitamos agregar eventos hover ya que lo manejamos con CSS
        // pero podemos agregar lógica adicional si es necesario
    }
});
