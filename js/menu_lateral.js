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

        // Click handler para dropdowns
        item.addEventListener('click', function(e) {
            const link = this.querySelector('a');
            if (!link) return;

            if (link.classList.contains('dropdown-toggle')) {
                e.preventDefault();
                const targetId = link.getAttribute('data-bs-target');
                const targetCollapse = document.querySelector(targetId);
                
                if (targetCollapse) {
                    const bsCollapse = new bootstrap.Collapse(targetCollapse, {
                        toggle: true
                    });
                }
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
                    const bsCollapse = bootstrap.Collapse.getInstance(collapse);
                    if (bsCollapse) {
                        bsCollapse.hide();
                    }
                });
            }, 300);
        }
    });
}); 