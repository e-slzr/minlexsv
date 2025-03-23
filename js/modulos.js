document.addEventListener('DOMContentLoaded', function() {
    // Función para actualizar el prefijo según el tipo seleccionado
    function actualizarPrefijo(tipoSelect, prefijoSpan) {
        const tipo = tipoSelect.value;
        const prefijo = tipo === 'Costura' ? 'C-' : tipo === 'Corte' ? 'T-' : '-';
        prefijoSpan.textContent = prefijo;
    }

    // Manejar cambio de tipo en el formulario nuevo
    const tipoSelect = document.getElementById('tipo');
    const prefijoSpan = document.getElementById('prefijo-modulo');
    tipoSelect.addEventListener('change', () => actualizarPrefijo(tipoSelect, prefijoSpan));

    // Manejar cambio de tipo en el formulario editar
    const editarTipoSelect = document.getElementById('editar_tipo');
    const editarPrefijoSpan = document.getElementById('editar-prefijo-modulo');
    editarTipoSelect.addEventListener('change', () => actualizarPrefijo(editarTipoSelect, editarPrefijoSpan));

    // Guardar nuevo módulo
    document.getElementById('guardarModulo').addEventListener('click', function() {
        const form = document.getElementById('nuevoModuloForm');
        const tipo = document.getElementById('tipo').value;
        const numero = document.getElementById('codigo').value;
        const descripcion = document.getElementById('descripcion').value;

        if (!tipo || !numero.match(/^\d{2}$/)) {
            alert('Por favor, complete todos los campos correctamente');
            return;
        }

        const moduloCodigo = (tipo === 'Costura' ? 'C-' : 'T-') + numero;

        const datos = {
            action: 'crear',
            modulo_codigo: moduloCodigo,
            modulo_tipo: tipo,
            modulo_descripcion: descripcion
        };

        // Enviar petición al servidor
        fetch('../controllers/ModuloController.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(datos)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('Módulo guardado exitosamente');
                location.reload();
            } else {
                alert(data.message || 'Error al guardar el módulo');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al guardar el módulo');
        });
    });

    // Editar módulo
    document.querySelectorAll('.editar-modulo').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            const codigo = this.dataset.codigo;
            const tipo = this.dataset.tipo;
            const descripcion = this.dataset.descripcion;

            document.getElementById('editar_id').value = id;
            document.getElementById('editar_tipo').value = tipo;
            
            // Extraer el número del código y actualizar el prefijo
            const numero = codigo.split('-')[1];
            document.getElementById('editar_codigo').value = numero;
            const editarPrefijoSpan = document.getElementById('editar-prefijo-modulo');
            editarPrefijoSpan.textContent = tipo === 'Costura' ? 'C-' : 'T-';
            
            document.getElementById('editar_descripcion').value = descripcion || '';

            const modal = new bootstrap.Modal(document.getElementById('editarModuloModal'));
            modal.show();
        });
    });

    // Actualizar módulo
    document.getElementById('actualizarModulo').addEventListener('click', function() {
        const id = document.getElementById('editar_id').value;
        const tipo = document.getElementById('editar_tipo').value;
        const numero = document.getElementById('editar_codigo').value;
        const descripcion = document.getElementById('editar_descripcion').value;

        if (!tipo || !numero.match(/^\d{2}$/)) {
            alert('Por favor, complete todos los campos correctamente');
            return;
        }

        const moduloCodigo = (tipo === 'Costura' ? 'C-' : 'T-') + numero;

        const datos = {
            action: 'actualizar',
            id: id,
            modulo_codigo: moduloCodigo,
            modulo_tipo: tipo,
            modulo_descripcion: descripcion
        };

        fetch('../controllers/ModuloController.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(datos)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Módulo actualizado exitosamente');
                location.reload();
            } else {
                alert(data.message || 'Error al actualizar el módulo');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al actualizar el módulo');
        });
    });

    // Toggle estado
    document.querySelectorAll('.toggle-estado').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            const estado = this.dataset.estado;
            
            if (confirm(`¿Está seguro que desea ${estado === 'Activo' ? 'desactivar' : 'activar'} este módulo?`)) {
                const datos = {
                    action: 'toggle_status',
                    id: id
                };

                fetch('../controllers/ModuloController.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(datos)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Error al cambiar el estado del módulo');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al cambiar el estado del módulo');
                });
            }
        });
    });

    // Inicializar prefijos al cargar la página
    if (tipoSelect.value) {
        actualizarPrefijo(tipoSelect, prefijoSpan);
    }
    if (editarTipoSelect.value) {
        actualizarPrefijo(editarTipoSelect, editarPrefijoSpan);
    }

    // Implementar filtros
    const filtros = document.querySelectorAll('.filtro');
    filtros.forEach(filtro => {
        filtro.addEventListener('input', aplicarFiltros);
    });

    function aplicarFiltros() {
        const codigo = document.getElementById('filtro-codigo').value.toLowerCase();
        const nombre = document.getElementById('filtro-nombre').value.toLowerCase();
        const tipo = document.getElementById('filtro-tipo').value;
        const estado = document.getElementById('filtro-estado').value;

        const filas = document.querySelectorAll('tbody tr');
        
        filas.forEach(fila => {
            const textoFila = fila.textContent.toLowerCase();
            const tipoFila = fila.children[2].textContent;
            const estadoFila = fila.querySelector('.badge').textContent;

            const coincideCodigo = !codigo || textoFila.includes(codigo);
            const coincideNombre = !nombre || textoFila.includes(nombre);
            const coincideTipo = !tipo || tipoFila === tipo;
            const coincideEstado = !estado || estadoFila === estado;

            if (coincideCodigo && coincideNombre && coincideTipo && coincideEstado) {
                fila.style.display = '';
            } else {
                fila.style.display = 'none';
            }
        });
    }
}); 