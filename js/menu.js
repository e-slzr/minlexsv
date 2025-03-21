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

    menuToggle.addEventListener('click', function() {
        menuLateral.classList.toggle('collapsed');
        mainContent.classList.toggle('menu-collapsed');
        
        // Guardar el estado del menú en localStorage
        const isCollapsed = menuLateral.classList.contains('collapsed');
        localStorage.setItem('menuState', isCollapsed ? 'collapsed' : 'expanded');
    });

    // Manejo de submenús en modo colapsado
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            if (menuLateral.classList.contains('collapsed')) {
                e.preventDefault();
                menuLateral.classList.remove('collapsed');
                mainContent.classList.remove('menu-collapsed');
                localStorage.setItem('menuState', 'expanded');
            }
        });
    });
});
