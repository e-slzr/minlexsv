document.addEventListener('DOMContentLoaded', function() {
    // Referencias a elementos del DOM
    const tablaOrdenes = document.getElementById('tabla-ordenes');
    const filtroModulo = document.getElementById('filtro-modulo');
    const filtroProceso = document.getElementById('filtro-proceso');
    const filtroEstado = document.getElementById('filtro-estado');
    const btnLimpiarFiltros = document.getElementById('limpiar-filtros');
    const modalOrden = new bootstrap.Modal(document.getElementById('modalOrden'));
    const formOrden = document.getElementById('formOrden');
    
    // Cargar órdenes iniciales
    cargarOrdenes();
    
    // Event listeners para filtros
    filtroModulo.addEventListener('change', cargarOrdenes);
    filtroProceso.addEventListener('change', cargarOrdenes);
    filtroEstado.addEventListener('change', cargarOrdenes);
    btnLimpiarFiltros.addEventListener('click', limpiarFiltros);
    
    // Event listener para guardar orden
    document.getElementById('btn-guardar-orden').addEventListener('click', guardarOrden);
    
    function cargarOrdenes() {
        const filtros = {
            modulo_id: filtroModulo.value,
            proceso_id: filtroProceso.value,
            estado: filtroEstado.value
        };
        
        fetch(`../controllers/OrdenProduccionController.php?action=getOrdenesFiltered&${new URLSearchParams(filtros)}`)
            .then(response => response.json())
            .then(result => {
                const tbody = tablaOrdenes.querySelector('tbody');
                tbody.innerHTML = '';
                
                if (!result.success) {
                    throw new Error(result.message || 'Error al cargar las órdenes');
                }
                
                const data = result.data;
                if (Array.isArray(data)) {
                    data.forEach(orden => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${orden.po_numero || '-'}</td>
                            <td>${orden.item_numero ? orden.item_numero + ' - ' + orden.item_nombre : '-'}</td>
                            <td>${orden.proceso_nombre || '-'}</td>
                            <td>${orden.modulo_codigo || 'Sin asignar'}</td>
                            <td>${orden.op_cantidad_asignada || '0'}</td>
                            <td>
                                <span class="badge bg-${getEstadoColor(orden.op_estado)}">
                                    ${orden.op_estado || 'Pendiente'}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary btn-editar" data-id="${orden.id}">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });
                } else {
                    console.error('La respuesta no es un array:', data);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al cargar las órdenes');
            });
    }
    
    function limpiarFiltros() {
        filtroModulo.value = '';
        filtroProceso.value = '';
        filtroEstado.value = '';
        cargarOrdenes();
    }
    
    function guardarOrden() {
        const data = {
            id: document.getElementById('orden-id').value,
            op_modulo_id: document.getElementById('orden-modulo').value,
            op_id_proceso: document.getElementById('orden-proceso').value,
            op_cantidad_asignada: document.getElementById('orden-cantidad').value
        };
        
        fetch('../controllers/OrdenProduccionController.php?action=update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                modalOrden.hide();
                cargarOrdenes();
                alert('Orden actualizada correctamente');
            } else {
                throw new Error(result.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al guardar la orden');
        });
    }
    
    function getEstadoColor(estado) {
        switch (estado) {
            case 'Pendiente': return 'warning';
            case 'En Proceso': return 'info';
            case 'Completado': return 'success';
            default: return 'secondary';
        }
    }
});