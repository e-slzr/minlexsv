document.addEventListener('DOMContentLoaded', function() {
    // Función para actualizar fecha y hora
    function actualizarFechaHora() {
        const ahora = new Date();
        
        // Actualizar fecha
        const fechaActual = document.getElementById('fecha-actual');
        fechaActual.textContent = ahora.toLocaleDateString('es-ES');
        
        // Actualizar hora
        const horaActual = document.getElementById('hora-actual');
        horaActual.textContent = ahora.toLocaleTimeString('es-ES', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
    }

    // Actualizar inmediatamente
    actualizarFechaHora();
    
    // Actualizar cada segundo
    setInterval(actualizarFechaHora, 1000);

    // Actualizar fecha actual
    const fechaActual = document.getElementById('fecha-actual');
    const fecha = new Date();
    fechaActual.textContent = fecha.toLocaleDateString('es-ES');
    
    // Referencias a elementos del DOM
    const selectorPo = document.getElementById('selector-po');
    const selectorProceso = document.getElementById('selector-proceso');
    const infoTrabajo = document.getElementById('info-trabajo');
    const contadores = document.getElementById('contadores');
    const panelIngreso = document.getElementById('panel-ingreso');
    const botonesAccion = document.getElementById('botones-accion');
    const panelCalidades = document.getElementById('panel-calidades');
    const cantidadIngresar = document.getElementById('cantidad-ingresar');
    
    // Elementos para mostrar información del trabajo
    const poNumero = document.getElementById('po-numero');
    const itemDescripcion = document.getElementById('item-descripcion');
    const itemTalla = document.getElementById('item-talla');
    const itemColor = document.getElementById('item-color');
    const itemDiseno = document.getElementById('item-diseno');
    const itemUbicacion = document.getElementById('item-ubicacion');
    
    // Contadores
    const totalPiezas = document.getElementById('total-piezas');
    const pendientes = document.getElementById('pendientes');
    const producidas = document.getElementById('producidas');
    
    // Contadores de calidades
    const countPrimeras = document.getElementById('count-primeras');
    const countIrregulares = document.getElementById('count-irregulares');
    const countPerdidas = document.getElementById('count-perdidas');
    
    // Botones
    const btnBorrar = document.getElementById('btn-borrar');
    const btnRegistrar = document.getElementById('btn-registrar');
    const btnFinalizarTalla = document.getElementById('btn-finalizar-talla');
    const btnPoEspera = document.getElementById('btn-po-espera');
    const btnGuardarCalidad = document.getElementById('btn-guardar-calidad');
    const btnConfirmarAccion = document.getElementById('btn-confirmar-accion');
    
    // Modales
    const modalCalidad = new bootstrap.Modal(document.getElementById('modalCalidad'));
    const modalConfirmacion = new bootstrap.Modal(document.getElementById('modalConfirmacion'));
    
    // Variables de estado
    let poActual = null;
    let procesoActual = null;
    let ordenActual = null;
    let calidadesRegistradas = {
        primeras: 0,
        irregulares: 0,
        perdidas: 0
    };
    let ordenesPendientes = [];

    // Inicializar selectores con Bootstrap Select
    $(selectorPo).selectpicker({
        liveSearch: true,
        size: 10,
        noneResultsText: 'No se encontraron resultados para {0}',
        liveSearchPlaceholder: 'Buscar PO...',
        width: '100%'
    });

    // Inicializar selector de proceso
    $(selectorProceso).selectpicker({
        width: '100%'
    });

    // Inicializar Bootstrap Select
    $('.selectpicker').selectpicker({
        liveSearch: true,
        size: 10
    });

    // Cargar órdenes de producción del módulo
    cargarOrdenesProduccion();
    
    // Cargar lista de POs disponibles
    function cargarPOs() {
        const moduloId = <?php echo $_SESSION['user']['usuario_modulo_id']; ?>;
        
        fetch(`../controllers/PoController.php?action=getPOsPorModulo&modulo_id=${moduloId}`)
            .then(response => response.json())
            .then(data => {
                const selector = document.getElementById('selector-po');
                selector.innerHTML = '<option value="" data-tokens="">Seleccione una PO</option>';
                
                data.forEach(po => {
                    const option = document.createElement('option');
                    option.value = po.id;
                    option.setAttribute('data-tokens', po.po_numero);
                    option.textContent = `${po.po_numero} - ${po.item_descripcion} (${po.item_talla})`;
                    selector.appendChild(option);
                });

                // Reinicializar el select con Bootstrap Select
                $(selector).selectpicker('refresh');
            })
            .catch(error => {
                console.error('Error al cargar POs:', error);
                alert('Error al cargar las órdenes de producción');
            });
    }
    
    // Cargar detalles de PO y mostrar información
    function cargarDetallesPO(poId, procesoId) {
        fetch(`../controllers/PoController.php?action=getPoInfo&id=${poId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    poActual = data.po;
                    
                    // Mostrar información de la PO
                    poNumero.textContent = poActual.po_numero;
                    
                    // Buscar órdenes de producción para esta PO y proceso
                    return fetch(`../controllers/OrdenProduccionController.php?action=getOrdenesPendientes&po_id=${poId}&proceso_id=${procesoId}`);
                } else {
                    throw new Error('No se pudo obtener información de la PO');
                }
            })
            .then(response => response.json())
            .then(ordenes => {
                if (ordenes.length > 0) {
                    ordenActual = ordenes[0];
                    ordenesPendientes = ordenes;
                    
                    // Mostrar información del item
                    itemDescripcion.textContent = ordenActual.item_nombre || 'N/A';
                    itemTalla.textContent = ordenActual.item_talla || 'N/A';
                    itemColor.textContent = ordenActual.item_color || 'N/A';
                    itemDiseno.textContent = ordenActual.item_diseno || 'N/A';
                    itemUbicacion.textContent = ordenActual.item_ubicacion || 'N/A';
                    
                    // Actualizar contadores
                    totalPiezas.textContent = ordenActual.cantidad_total || 0;
                    producidas.textContent = ordenActual.cantidad_completada || 0;
                    pendientes.textContent = ordenActual.cantidad_total - (ordenActual.cantidad_completada || 0);
                    
                    // Reiniciar calidades
                    calidadesRegistradas = {
                        primeras: 0,
                        irregulares: 0,
                        perdidas: 0
                    };
                    actualizarContadoresCalidad();
                    
                    // Mostrar paneles
                    infoTrabajo.classList.remove('d-none');
                    contadores.classList.remove('d-none');
                    panelIngreso.classList.remove('d-none');
                    botonesAccion.classList.remove('d-none');
                    panelCalidades.classList.remove('d-none');
                    
                    // Limpiar pantalla de entrada
                    cantidadIngresar.value = '';
                } else {
                    Swal.fire({
                        icon: 'info',
                        title: 'Sin órdenes',
                        text: 'No hay órdenes pendientes para este proceso y PO.'
                    });
                    
                    // Ocultar paneles
                    ocultarPaneles();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Ocurrió un error al cargar la información.'
                });
                
                // Ocultar paneles
                ocultarPaneles();
            });
    }
    
    // Ocultar paneles cuando no hay orden activa
    function ocultarPaneles() {
        infoTrabajo.classList.add('d-none');
        contadores.classList.add('d-none');
        panelIngreso.classList.add('d-none');
        botonesAccion.classList.add('d-none');
        panelCalidades.classList.add('d-none');
    }
    
    // Actualizar contadores de calidad
    function actualizarContadoresCalidad() {
        countPrimeras.textContent = calidadesRegistradas.primeras;
        countIrregulares.textContent = calidadesRegistradas.irregulares;
        countPerdidas.textContent = calidadesRegistradas.perdidas;
    }
    
    // Registrar avance de producción
    function registrarAvance(cantidad) {
        if (!ordenActual || !cantidad) return;
        
        const formData = new FormData();
        formData.append('orden_produccion_id', ordenActual.id);
        formData.append('proceso_id', procesoActual);
        formData.append('cantidad_completada', cantidad);
        formData.append('estado', 'en_proceso');
        
        fetch('../controllers/ProduccionAvanceController.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: 'Avance registrado correctamente',
                    timer: 1500,
                    showConfirmButton: false
                });
                
                // Actualizar contadores
                const cantidadAnterior = parseInt(producidas.textContent) || 0;
                const cantidadNueva = cantidadAnterior + parseInt(cantidad);
                producidas.textContent = cantidadNueva;
                pendientes.textContent = parseInt(totalPiezas.textContent) - cantidadNueva;
                
                // Limpiar entrada
                cantidadIngresar.value = '';
            } else {
                throw new Error(data.message || 'Error al registrar avance');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'Ocurrió un error al registrar el avance.'
            });
        });
    }
    
    // Registrar calidad
    function registrarCalidad(tipo, cantidad) {
        if (!ordenActual || !cantidad) return;
        
        const formData = new FormData();
        formData.append('orden_produccion_id', ordenActual.id);
        formData.append('tipo_calidad', tipo);
        formData.append('cantidad', cantidad);
        
        fetch('../controllers/ProduccionAvanceController.php?action=registrarCalidad', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Actualizar contador local
                calidadesRegistradas[tipo] += parseInt(cantidad);
                actualizarContadoresCalidad();
                
                modalCalidad.hide();
            } else {
                throw new Error(data.message || 'Error al registrar calidad');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'Ocurrió un error al registrar la calidad.'
            });
        });
    }
    
    // Finalizar talla
    function finalizarTalla() {
        if (!ordenActual) return;
        
        const formData = new FormData();
        formData.append('id', ordenActual.id);
        formData.append('op_estado', 'Completado');
        
        fetch('../controllers/OrdenProduccionController.php?action=update', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: 'Talla finalizada correctamente'
                });
                
                // Reiniciar interfaz
                $(selectorPo).val('');
                ocultarPaneles();
                ordenActual = null;
            } else {
                throw new Error(data.message || 'Error al finalizar talla');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'Ocurrió un error al finalizar la talla.'
            });
        });
    }
    
    // Poner PO en espera
    function ponerEnEspera() {
        if (!ordenActual) return;
        
        const formData = new FormData();
        formData.append('id', ordenActual.id);
        formData.append('op_estado', 'En Espera');
        
        fetch('../controllers/OrdenProduccionController.php?action=update', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'info',
                    title: 'En Espera',
                    text: 'La orden ha sido puesta en espera'
                });
                
                // Reiniciar interfaz
                $(selectorPo).val('');
                ocultarPaneles();
                ordenActual = null;
            } else {
                throw new Error(data.message || 'Error al poner en espera');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'Ocurrió un error al poner la orden en espera.'
            });
        });
    }
    
    // Event Listeners
    
    // Selección de PO
    $(selectorPo).on('changed.bs.select', function() {
        const poId = $(this).val();
        const procesoId = $(selectorProceso).val();
        
        if (poId && procesoId) {
            cargarDetallesPO(poId, procesoId);
        } else {
            ocultarPaneles();
        }
    });
    
    // Selección de proceso
    $(selectorProceso).on('changed.bs.select', function() {
        const procesoId = $(this).val();
        procesoActual = procesoId;
        
        // Si ya hay una PO seleccionada, cargar sus detalles
        const poId = $(selectorPo).val();
        if (poId && procesoId) {
            cargarDetallesPO(poId, procesoId);
        }
    });
    
    // Teclado numérico
    document.querySelectorAll('.numpad-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const valor = this.getAttribute('data-value');
            cantidadIngresar.value = (cantidadIngresar.value || '') + valor;
        });
    });
    
    // Botón borrar
    btnBorrar.addEventListener('click', function() {
        cantidadIngresar.value = '';
    });
    
    // Botón registrar
    btnRegistrar.addEventListener('click', function() {
        const cantidad = parseInt(cantidadIngresar.value);
        if (isNaN(cantidad) || cantidad <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Error',
                text: 'Ingrese una cantidad válida'
            });
            return;
        }
        
        registrarAvance(cantidad);
    });
    
    // Botones de calidad
    document.querySelectorAll('.calidad-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const tipo = this.getAttribute('data-type');
            document.getElementById('tipo-calidad').value = tipo;
            document.getElementById('cantidad-calidad').value = '';
            
            // Título del modal
            let titulo = 'Registrar ';
            switch (tipo) {
                case 'primeras': titulo += 'Primeras'; break;
                case 'irregulares': titulo += 'Irregulares'; break;
                case 'perdidas': titulo += 'Piezas Perdidas'; break;
            }
            document.getElementById('modalCalidadTitle').textContent = titulo;
            
            modalCalidad.show();
        });
    });
    
    // Guardar calidad
    btnGuardarCalidad.addEventListener('click', function() {
        const tipo = document.getElementById('tipo-calidad').value;
        const cantidad = parseInt(document.getElementById('cantidad-calidad').value);
        
        if (isNaN(cantidad) || cantidad <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Error',
                text: 'Ingrese una cantidad válida'
            });
            return;
        }
        
        registrarCalidad(tipo, cantidad);
    });
    
    // Finalizar talla
    btnFinalizarTalla.addEventListener('click', function() {
        document.getElementById('modalConfirmacionTitle').textContent = 'Finalizar Talla';
        document.getElementById('modalConfirmacionBody').textContent = 
            '¿Está seguro que desea finalizar esta talla? Esta acción no se puede deshacer.';
        
        document.getElementById('btn-confirmar-accion').onclick = function() {
            finalizarTalla();
            modalConfirmacion.hide();
        };
        
        modalConfirmacion.show();
    });
    
    // PO en espera
    btnPoEspera.addEventListener('click', function() {
        document.getElementById('modalConfirmacionTitle').textContent = 'Poner en Espera';
        document.getElementById('modalConfirmacionBody').textContent = 
            '¿Está seguro que desea poner esta orden en espera?';
        
        document.getElementById('btn-confirmar-accion').onclick = function() {
            ponerEnEspera();
            modalConfirmacion.hide();
        };
        
        modalConfirmacion.show();
    });
    
    // Funcionalidad de pantalla completa
    const btnFullscreen = document.getElementById('btn-fullscreen');
    const iconFullscreen = btnFullscreen.querySelector('i');

    function toggleFullScreen() {
        if (!document.fullscreenElement && 
            !document.mozFullScreenElement && 
            !document.webkitFullscreenElement && 
            !document.msFullscreenElement) {
            // Entrar a pantalla completa
            if (document.documentElement.requestFullscreen) {
                document.documentElement.requestFullscreen();
            } else if (document.documentElement.msRequestFullscreen) {
                document.documentElement.msRequestFullscreen();
            } else if (document.documentElement.mozRequestFullScreen) {
                document.documentElement.mozRequestFullScreen();
            } else if (document.documentElement.webkitRequestFullscreen) {
                document.documentElement.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
            }
            iconFullscreen.classList.remove('fa-expand');
            iconFullscreen.classList.add('fa-compress');
        } else {
            // Salir de pantalla completa
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            } else if (document.mozCancelFullScreen) {
                document.mozCancelFullScreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            }
            iconFullscreen.classList.remove('fa-compress');
            iconFullscreen.classList.add('fa-expand');
        }
    }

    // Evento para el botón de pantalla completa
    btnFullscreen.addEventListener('click', toggleFullScreen);

    // Detectar cambios en el estado de pantalla completa
    document.addEventListener('fullscreenchange', updateFullscreenButton);
    document.addEventListener('webkitfullscreenchange', updateFullscreenButton);
    document.addEventListener('mozfullscreenchange', updateFullscreenButton);
    document.addEventListener('MSFullscreenChange', updateFullscreenButton);

    function updateFullscreenButton() {
        if (document.fullscreenElement || 
            document.webkitFullscreenElement || 
            document.mozFullScreenElement || 
            document.msFullscreenElement) {
            iconFullscreen.classList.remove('fa-expand');
            iconFullscreen.classList.add('fa-compress');
        } else {
            iconFullscreen.classList.remove('fa-compress');
            iconFullscreen.classList.add('fa-expand');
        }
    }

    // Detectar orientación y solicitar pantalla completa en modo landscape
    window.addEventListener('orientationchange', function() {
        if (window.orientation === 90 || window.orientation === -90) {
            // Landscape
            setTimeout(function() {
                if (!document.fullscreenElement) {
                    toggleFullScreen();
                }
            }, 100);
        }
    });
});

function cargarOrdenesProduccion() {
    const moduloId = document.querySelector('[data-modulo-id]').dataset.moduloId;
    
    fetch(`../controllers/OrdenProduccionController.php?action=getOrdenesPorModulo&modulo_id=${moduloId}`)
        .then(response => response.json())
        .then(ordenes => {
            const selectorPo = document.getElementById('selector-po');
            selectorPo.innerHTML = '<option value="">Seleccione una orden</option>';
            
            ordenes.forEach(orden => {
                const option = document.createElement('option');
                option.value = orden.id;
                option.textContent = `PO: ${orden.po_numero} - ${orden.item_numero} - ${orden.proceso_nombre}`;
                option.dataset.itemInfo = JSON.stringify({
                    descripcion: orden.item_nombre,
                    talla: orden.item_talla,
                    total: orden.op_cantidad_asignada,
                    completadas: orden.op_cantidad_completada
                });
                selectorPo.appendChild(option);
            });

            // Inicializar el select con búsqueda
            $(selectorPo).selectpicker('refresh');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar las órdenes de producción');
        });
}
