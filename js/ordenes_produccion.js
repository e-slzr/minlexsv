$(document).ready(function() {
    // Variables globales
    let currentPage = 1;
    const defaultRecordsPerPage = 10;
    let recordsPerPage = defaultRecordsPerPage;
    let sortColumn = 'id';
    let sortDirection = 'desc';

    // Solucionar problema de modal backdrop
    $(document).on('hidden.bs.modal', '.modal', function () {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
        $('body').css('padding-right', '');
    });

    // Inicializar Select2 para los selectores
    $('.select2').select2({
        theme: 'bootstrap-5',
        width: '100%',
        dropdownParent: $('#ordenModal')
    });

    // Inicializar Select2 para los selectores del modal de edición
    $('.select2').each(function() {
        let parentModal = $(this).closest('.modal');
        if (parentModal.length) {
            $(this).select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: parentModal
            });
        }
    });

    // Variables para control de cantidades
    let cantidadTotal = 0;
    let cantidadAsignada = 0;
    let cantidadRestante = 0;

    // Evento para cuando cambia el detalle de PO seleccionado
    $('#poDetalle').on('change', function() {
        const poDetalleId = $(this).val();
        if (poDetalleId) {
            // Desbloquear campo de cantidad cuando se selecciona un item
            $('#cantidadAsignada').prop('disabled', false);
            $('#asignarCompleto').prop('disabled', false);
            
            // Obtener información del detalle de PO
            $.ajax({
                url: '../controllers/OrdenProduccionController.php',
                type: 'GET',
                data: {
                    action: 'getPoDetalleInfo',
                    id: poDetalleId,
                    proceso: $('#proceso').val()
                },
                success: function(response) {
                    if (response.success) {
                        cantidadTotal = parseInt(response.cantidadTotal) || 0;
                        cantidadAsignada = parseInt(response.cantidadAsignada) || 0;
                        cantidadRestante = cantidadTotal - cantidadAsignada;
                        
                        // Actualizar la información en la interfaz
                        $('#cantidadInfo').text(cantidadAsignada + '/' + cantidadTotal);
                        $('#cantidadRestante').text('Pendiente por asignar: ' + cantidadRestante);
                        
                        // Establecer el valor máximo para el campo de cantidad
                        $('#cantidadAsignada').attr('max', cantidadRestante);
                        
                        // Si no hay cantidad restante, desactivar campos
                        if (cantidadRestante <= 0) {
                            $('#cantidadAsignada').prop('disabled', true);
                            $('#asignarCompleto').prop('disabled', true);
                            alert('No hay cantidad disponible para asignar a este proceso');
                        }
                    } else {
                        alert('Error al obtener información del detalle de PO: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error al conectar con el servidor');
                }
            });
        } else {
            // Restablecer valores si no hay detalle seleccionado
            cantidadTotal = 0;
            cantidadAsignada = 0;
            cantidadRestante = 0;
            $('#cantidadInfo').text('0/0');
            $('#cantidadRestante').text('Pendiente por asignar: 0');
            $('#cantidadAsignada').val('').prop('disabled', true);
            $('#asignarCompleto').prop('checked', false).prop('disabled', true);
        }
    });

    // Evento para cuando cambia el proceso seleccionado
    $('#proceso').on('change', function() {
        // Si ya hay un detalle de PO seleccionado, actualizar la información
        if ($('#poDetalle').val()) {
            $('#poDetalle').trigger('change');
        }
    });

    // Evento para cuando cambia la cantidad asignada
    $('#cantidadAsignada').on('input', function() {
        const valor = parseInt($(this).val()) || 0;
        
        // Validar que no exceda la cantidad restante
        if (valor > cantidadRestante) {
            $(this).val(cantidadRestante);
        }
        
        // Actualizar la información en tiempo real
        const nuevaAsignada = cantidadAsignada + parseInt($(this).val() || 0);
        $('#cantidadInfo').text(nuevaAsignada + '/' + cantidadTotal);
    });

    // Evento para el checkbox de asignar cantidad completa
    $('#asignarCompleto').on('change', function() {
        if ($(this).is(':checked')) {
            // Asignar la cantidad restante y hacerlo readonly (no disabled)
            $('#cantidadAsignada').val(cantidadRestante);
            $('#cantidadAsignada').prop('readonly', true);
            
            // Actualizar la información en tiempo real
            const nuevaAsignada = cantidadAsignada + cantidadRestante;
            $('#cantidadInfo').text(nuevaAsignada + '/' + cantidadTotal);
        } else {
            // Quitar readonly y limpiar valor
            $('#cantidadAsignada').prop('readonly', false).val('');
            
            // Restablecer la información
            $('#cantidadInfo').text(cantidadAsignada + '/' + cantidadTotal);
        }
    });

    // Evento para el checkbox de asignar cantidad completa en el modal de edición
    $('#editAsignarCompleto').on('change', function() {
        if ($(this).is(':checked')) {
            // Obtener la cantidad total y la cantidad ya asignada
            const cantidadTotal = parseInt($('#editCantidadInfo').text().split('/')[1]) || 0;
            const cantidadAsignada = parseInt($('#editCantidadCompletada').val()) || 0;
            const cantidadRestante = cantidadTotal - cantidadAsignada;
            
            // Asignar la cantidad restante y hacerlo readonly (no disabled)
            $('#editCantidadAsignada').val(cantidadRestante);
            $('#editCantidadAsignada').prop('readonly', true);
            
            // Actualizar el mensaje de cantidad restante
            $('#editCantidadRestante').text('Pendiente por asignar: 0');
        } else {
            // Quitar readonly y limpiar valor
            $('#editCantidadAsignada').prop('readonly', false).val('');
            
            // Restablecer el mensaje de cantidad restante
            const cantidadTotal = parseInt($('#editCantidadInfo').text().split('/')[1]) || 0;
            const cantidadAsignada = parseInt($('#editCantidadCompletada').val()) || 0;
            const cantidadRestante = cantidadTotal - cantidadAsignada;
            $('#editCantidadRestante').text('Pendiente por asignar: ' + cantidadRestante);
        }
    });

    // Validación del formulario antes de enviar
    $('#ordenForm').on('submit', function(e) {
        // Verificar que todos los campos requeridos estén llenos
        const poDetalleSelected = $('#poDetalle').val() !== '' && $('#poDetalle').val() !== null;
        const procesoSelected = $('#proceso').val() !== '' && $('#proceso').val() !== null;
        const operadorSelected = $('#operador').val() !== '' && $('#operador').val() !== null;
        const cantidadValid = $('#cantidadAsignada').val() > 0;
        
        if (!poDetalleSelected || !procesoSelected || !operadorSelected || !cantidadValid) {
            e.preventDefault();
            alert('Por favor complete todos los campos requeridos: Item, Proceso, Operador y Cantidad Asignada.');
            return false;
        }
        
        return true;
    });

    // Función para cargar las órdenes
    function loadOrdenes(page = 1) {
        const filters = {
            item_numero: $('#item_numero').val(),
            operador: $('#operador').val(),
            estado: $('#estado').val(),
            fecha_inicio: $('#fecha_inicio').val(),
            fecha_fin: $('#fecha_fin').val()
        };

        $.ajax({
            url: '../controllers/OrdenProduccionController.php',
            type: 'GET',
            data: {
                action: 'search',
                page: page,
                ...filters
            },
            success: function(response) {
                if (response.success) {
                    updateTable(response.ordenes);
                    updatePagination(response.total, page);
                } else {
                    alert('Error al cargar las órdenes: ' + response.message);
                }
            },
            error: function() {
                alert('Error al conectar con el servidor');
            }
        });
    }

    // Función para actualizar la tabla
    function updateTable(ordenes) {
        const tbody = $('#tabla-ordenes tbody');
        tbody.empty();

        if (ordenes.length === 0) {
            tbody.append('<tr><td colspan="12" class="text-center">No se encontraron órdenes de producción</td></tr>');
            return;
        }

        ordenes.forEach(orden => {
            const row = $('<tr>');
            
            // Formatear fechas
            const fechaInicio = orden.op_fecha_inicio ? new Date(orden.op_fecha_inicio).toLocaleDateString('es-ES') : 'No iniciada';
            const fechaFin = orden.op_fecha_fin ? new Date(orden.op_fecha_fin).toLocaleDateString('es-ES') : 'No finalizada';
            
            // Calcular porcentaje completado
            let completado = 0;
            if (orden.op_cantidad_asignada > 0) {
                completado = Math.round((orden.op_cantidad_completada / orden.op_cantidad_asignada) * 100);
            }
            
            // Determinar clases para badges
            let estadoClass = '';
            switch (orden.op_estado) {
                case 'Pendiente': estadoClass = 'bg-warning'; break;
                case 'En proceso': estadoClass = 'bg-primary'; break;
                case 'Completado': estadoClass = 'bg-success'; break;
            }
            
            let aprobacionClass = '';
            switch (orden.op_estado_aprobacion) {
                case 'Pendiente': aprobacionClass = 'bg-warning'; break;
                case 'Aprobado': aprobacionClass = 'bg-success'; break;
                case 'Rechazado': aprobacionClass = 'bg-danger'; break;
            }
            
            // Construir la fila
            row.append(`<td>${orden.id}</td>`);
            row.append(`<td>${orden.po_numero}</td>`);
            row.append(`<td>${orden.item_numero} - ${orden.item_nombre}</td>`);
            row.append(`<td>${orden.pp_nombre}</td>`);
            row.append(`<td>${orden.usuario_nombre} ${orden.usuario_apellido}</td>`);
            row.append(`<td>${orden.modulo_codigo || 'No asignado'}</td>`);
            row.append(`<td><span class="badge ${estadoClass}">${orden.op_estado}</span></td>`);
            row.append(`<td>${fechaInicio}</td>`);
            row.append(`<td>${fechaFin}</td>`);
            row.append(`<td><span class="badge ${aprobacionClass}">${orden.op_estado_aprobacion}</span></td>`);
            
            // Barra de progreso para completado
            row.append(`
                <td>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-success" role="progressbar" 
                            style="width: ${completado}%;" 
                            aria-valuenow="${completado}" 
                            aria-valuemin="0" 
                            aria-valuemax="100">
                            ${completado}%
                        </div>
                    </div>
                </td>
            `);
            
            // Botones de acción
            let botonesAccion = '<td class="text-end">';
            
            // Botones Aprobar/Rechazar (solo si está pendiente)
            if (orden.op_estado_aprobacion === 'Pendiente' || orden.op_estado_aprobacion === 'Rechazado' || orden.op_estado_aprobacion === 'Aprobado') {
                botonesAccion += `
                    <button type="button" class="btn btn-sm btn-light me-1 gestionar-aprobacion" 
                        data-id="${orden.id}" title="Gestionar Aprobación">
                        <i class="fas fa-check"></i>
                    </button>
                `;
            }
            
            // Botón Ver detalles
            botonesAccion += `
                <button type="button" class="btn btn-sm btn-light me-1 ver-orden" 
                    data-id="${orden.id}" data-bs-toggle="modal" data-bs-target="#ordenDetailModal" title="Ver Detalles">
                    <i class="fas fa-eye"></i>
                </button>
            `;
            
            // Botón Editar
            botonesAccion += `
                <button type="button" class="btn btn-sm btn-light me-1 editar-orden" 
                    data-id="${orden.id}" data-bs-toggle="modal" data-bs-target="#editOrdenModal" title="Editar Orden">
                    <i class="fas fa-edit"></i>
                </button>
            `;
            
            // Botón Eliminar
            botonesAccion += `
                <button type="button" class="btn btn-sm btn-danger eliminar-orden" 
                    data-id="${orden.id}" data-bs-toggle="modal" data-bs-target="#deleteOrdenModal" title="Eliminar Orden">
                    <i class="fas fa-trash"></i>
                </button>
            `;
            
            botonesAccion += '</td>';
            row.append(botonesAccion);
            
            tbody.append(row);
        });
    }

    // Función para actualizar la paginación
    function updatePagination(total, currentPage) {
        const totalPages = Math.ceil(total / recordsPerPage);
        const pagination = $('#pagination');
        pagination.empty();
        
        if (totalPages <= 1) {
            return;
        }
        
        // Botón Anterior
        const prevBtn = $('<li class="page-item">').append(
            $('<a class="page-link" href="#" aria-label="Previous">').append(
                $('<span aria-hidden="true">').html('&laquo;')
            )
        );
        
        if (currentPage === 1) {
            prevBtn.addClass('disabled');
        } else {
            prevBtn.on('click', function(e) {
                e.preventDefault();
                loadOrdenes(currentPage - 1);
            });
        }
        
        pagination.append(prevBtn);
        
        // Páginas
        for (let i = 1; i <= totalPages; i++) {
            const pageItem = $('<li class="page-item">').append(
                $('<a class="page-link" href="#">').text(i)
            );
            
            if (i === currentPage) {
                pageItem.addClass('active');
            }
            
            pageItem.on('click', function(e) {
                e.preventDefault();
                loadOrdenes(i);
            });
            
            pagination.append(pageItem);
        }
        
        // Botón Siguiente
        const nextBtn = $('<li class="page-item">').append(
            $('<a class="page-link" href="#" aria-label="Next">').append(
                $('<span aria-hidden="true">').html('&raquo;')
            )
        );
        
        if (currentPage === totalPages) {
            nextBtn.addClass('disabled');
        } else {
            nextBtn.on('click', function(e) {
                e.preventDefault();
                loadOrdenes(currentPage + 1);
            });
        }
        
        pagination.append(nextBtn);
    }

    // Event Listeners
    
    // Cambio en los filtros
    $('.filtro').on('change', function() {
        loadOrdenes(1);
    });
    
    // Limpiar filtros
    $('#limpiar-filtros').on('click', function() {
        $('.filtro').val('');
        loadOrdenes(1);
    });
    
    // Ordenamiento de columnas
    $('.sortable').on('click', function() {
        const column = $(this).data('column');
        
        if (sortColumn === column) {
            sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            sortColumn = column;
            sortDirection = 'asc';
        }
        
        // Actualizar indicadores visuales
        $('.sortable i').removeClass('fa-sort-up fa-sort-down').addClass('fa-sort');
        $(this).find('i').removeClass('fa-sort').addClass(sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down');
        
        loadOrdenes(1);
    });
    
    // Ver detalles de orden
    $(document).on('click', '.ver-orden', function() {
        const id = $(this).data('id');
        
        $.ajax({
            url: '../controllers/OrdenProduccionController.php',
            type: 'GET',
            data: {
                action: 'readOne',
                id: id
            },
            success: function(response) {
                if (response.success) {
                    const orden = response.orden;
                    
                    // Información General
                    $('#detailPO').text(orden.po_numero || 'No asignado');
                    $('#detailItem').text(orden.item_numero + ' - ' + orden.item_nombre);
                    
                    // Proceso con badge
                    const procesoBadge = '<span class="badge bg-info">' + orden.pp_nombre + '</span>';
                    $('#detailProcesoContainer').html(procesoBadge);
                    
                    $('#detailOperador').text(orden.usuario_nombre + ' ' + orden.usuario_apellido);
                    $('#detailModulo').text(orden.modulo_codigo || 'No asignado');
                    
                    // Estado con badge
                    const estadoClass = getEstadoClass(orden.op_estado);
                    const estadoBadge = '<span class="badge ' + estadoClass + '">' + orden.op_estado + '</span>';
                    $('#detailEstadoContainer').html(estadoBadge);
                    
                    // Estado de aprobación con badge
                    const aprobacionClass = getAprobacionClass(orden.op_estado_aprobacion);
                    const aprobacionBadge = '<span class="badge ' + aprobacionClass + '">' + orden.op_estado_aprobacion + '</span>';
                    $('#detailEstadoAprobacionContainer').html(aprobacionBadge);
                    
                    // Aprobación
                    $('#detailAprobadoPor').text(orden.aprobador_nombre ? (orden.aprobador_nombre + ' ' + orden.aprobador_apellido) : 'Pendiente');
                    $('#detailFechaAprobacion').text(orden.op_fecha_aprobacion ? formatDate(orden.op_fecha_aprobacion) : 'Pendiente');
                    
                    // Motivo de rechazo (mostrar/ocultar según corresponda)
                    if (orden.op_estado_aprobacion === 'Rechazado' && orden.op_motivo_rechazo) {
                        $('#detailMotivoRechazoRow').show();
                        $('#detailMotivoRechazo').text(orden.op_motivo_rechazo);
                    } else {
                        $('#detailMotivoRechazoRow').hide();
                    }
                    
                    // Cantidades
                    $('#detailCantidadAsignada').text(orden.op_cantidad_asignada);
                    $('#detailCantidadCompletada').text(orden.op_cantidad_completada);
                    $('#detailComentario').text(orden.op_comentario || 'Sin comentarios');
                    
                    // Fechas
                    $('#detailFechaInicio').text(orden.op_fecha_inicio ? formatDate(orden.op_fecha_inicio) : 'No iniciada');
                    $('#detailFechaFin').text(orden.op_fecha_fin ? formatDate(orden.op_fecha_fin) : 'No finalizada');
                    
                    // Fechas de creación y modificación
                    $('#detailFechaCreacion').text(formatDate(orden.op_fecha_creacion));
                    $('#detailFechaModificacion').text(formatDate(orden.op_fecha_modificacion));
                    
                    // Mostrar el modal
                    $('#ordenDetailModal').modal('show');
                } else {
                    alert('Error al cargar los detalles de la orden: ' + response.message);
                }
            },
            error: function() {
                alert('Error al conectar con el servidor');
            }
        });
    });
    
    // Exportar a PDF
    $('#exportarPDF').on('click', function() {
        alert('La funcionalidad de exportar a PDF será implementada próximamente.');
        // Aquí se implementará la funcionalidad de exportar a PDF en el futuro
    });
    
    // Modal de Editar Orden
    $(document).on('click', '.editar-orden', function() {
        const id = $(this).data('id');
        
        // Cargar detalles de la orden para editar
        $.ajax({
            url: '../controllers/OrdenProduccionController.php',
            type: 'GET',
            data: {
                action: 'getOrdenInfo',
                id: id
            },
            success: function(response) {
                if (response.success) {
                    const orden = response.orden;
                    
                    // Llenar el formulario con los datos
                    $('#editOrdenId').val(orden.id);
                    $('#editPoDetalle').val(orden.op_id_pd).trigger('change');
                    $('#editProceso').val(orden.op_id_proceso).trigger('change');
                    $('#editOperador').val(orden.op_operador_asignado).trigger('change');
                    $('#editFechaInicio').val(orden.op_fecha_inicio || '');
                    $('#editFechaFin').val(orden.op_fecha_fin || '');
                    
                    // Llenar los campos ocultos para los campos deshabilitados
                    $('#editPoDetalleHidden').val(orden.op_id_pd);
                    $('#editProcesoHidden').val(orden.op_id_proceso);
                    
                    // Actualizar el estado con badge
                    $('#editEstadoSelect').val(orden.op_estado);
                    updateEditEstadoBadge(orden.op_estado);
                    
                    // Actualizar campos de cantidad
                    $('#editCantidadAsignada').val(orden.op_cantidad_asignada);
                    $('#editCantidadCompletada').val(orden.op_cantidad_completada);
                    
                    // Actualizar información de cantidad
                    const cantidadTotal = parseInt(orden.pd_cant_piezas_total) || 0;
                    const cantidadAsignada = parseInt(orden.op_cantidad_asignada) || 0;
                    $('#editCantidadInfo').text(cantidadAsignada + '/' + cantidadTotal);
                    
                    $('#editComentario').val(orden.op_comentario || '');
                    
                    // Mostrar fechas de creación y modificación
                    $('#editFechaCreacion').text(orden.op_fecha_creacion ? formatDate(orden.op_fecha_creacion) : '-');
                    $('#editFechaModificacion').text(orden.op_fecha_modificacion ? formatDate(orden.op_fecha_modificacion) : '-');
                    
                    // Mostrar el modal
                    $('#editOrdenModal').modal('show');
                } else {
                    alert('Error al cargar los detalles de la orden: ' + response.message);
                }
            },
            error: function() {
                alert('Error al conectar con el servidor');
            }
        });
    });
    
    // Función para actualizar el badge de estado en el modal de edición
    function updateEditEstadoBadge(estado) {
        let badgeClass = '';
        switch (estado) {
            case 'Pendiente':
                badgeClass = 'bg-warning';
                break;
            case 'En proceso':
                badgeClass = 'bg-primary';
                break;
            case 'Completado':
                badgeClass = 'bg-success';
                break;
        }
        
        $('#editEstadoBadge').html(`<span class="badge ${badgeClass}">${estado}</span>`);
    }
    
    // Función para formatear fechas
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('es-ES') + ' ' + date.toLocaleTimeString('es-ES', {hour: '2-digit', minute:'2-digit'});
    }
    
    // Evento para cuando cambia el estado en el modal de edición
    $('#editEstadoSelect').on('change', function() {
        updateEditEstadoBadge($(this).val());
    });
    
    // Modal de Gestionar Aprobación
    $(document).on('click', '.gestionar-aprobacion', function() {
        const id = $(this).data('id');
        $('#aprobacionOrdenId').val(id);
        
        // Obtener el estado actual de la orden
        $.ajax({
            url: '../controllers/OrdenProduccionController.php',
            type: 'GET',
            data: {
                action: 'readOne',
                id: id
            },
            success: function(response) {
                if (response.success) {
                    const orden = response.orden;
                    
                    // Si la orden ya está en proceso, deshabilitar la opción de rechazo
                    if (orden.op_estado === 'En proceso') {
                        $('#confirmarRechazo').prop('disabled', true);
                        $('.card-header h6').html('Rechazar Orden <small class="text-danger">(No disponible para órdenes en proceso)</small>');
                    } else {
                        $('#confirmarRechazo').prop('disabled', false);
                        $('.card-header h6').html('Rechazar Orden');
                    }
                    
                    $('#aprobacionOrdenModal').modal('show');
                } else {
                    alert('Error al cargar los detalles de la orden: ' + response.message);
                }
            },
            error: function() {
                alert('Error al conectar con el servidor');
            }
        });
    });
    
    // Confirmar Aprobación
    $('#confirmarAprobacion').on('click', function() {
        const id = $('#aprobacionOrdenId').val();
        
        // Mostrar indicador de carga
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Procesando...');
        
        $.ajax({
            url: '../controllers/OrdenProduccionController.php',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'aprobarOrden',
                id: id
            },
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    $('#aprobacionOrdenModal').modal('hide');
                    loadOrdenes(currentPage);
                } else {
                    alert('Error al aprobar la orden: ' + (response.message || 'Error desconocido'));
                    console.error('Error al aprobar orden:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX:', xhr, status, error);
                let errorMsg = 'Error al conectar con el servidor';
                
                try {
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = 'Error al aprobar la orden: ' + xhr.responseJSON.message;
                    } else if (xhr.responseText) {
                        const response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            errorMsg = 'Error al aprobar la orden: ' + response.message;
                        }
                    }
                } catch (e) {
                    console.error('Error al procesar respuesta:', e);
                }
                
                alert(errorMsg);
            },
            complete: function() {
                // Restaurar el botón
                $('#confirmarAprobacion').prop('disabled', false).html('<i class="fas fa-check-circle me-2"></i> Aprobar Orden');
            }
        });
    });
    
    // Confirmar Rechazo
    $('#confirmarRechazo').on('click', function() {
        const id = $('#aprobacionOrdenId').val();
        const motivo = $('#motivoRechazo').val();
        
        if (!motivo) {
            alert('Por favor, ingrese un motivo para el rechazo');
            return;
        }
        
        // Mostrar indicador de carga
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Procesando...');
        
        $.ajax({
            url: '../controllers/OrdenProduccionController.php',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'rechazarOrden',
                id: id,
                motivo: motivo
            },
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    $('#aprobacionOrdenModal').modal('hide');
                    $('#motivoRechazo').val('');
                    loadOrdenes(currentPage);
                } else {
                    alert('Error al rechazar la orden: ' + (response.message || 'Error desconocido'));
                    console.error('Error al rechazar orden:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX:', xhr, status, error);
                let errorMsg = 'Error al conectar con el servidor';
                
                try {
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = 'Error al rechazar la orden: ' + xhr.responseJSON.message;
                    } else if (xhr.responseText) {
                        const response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            errorMsg = 'Error al rechazar la orden: ' + response.message;
                        }
                    }
                } catch (e) {
                    console.error('Error al procesar respuesta:', e);
                }
                
                alert(errorMsg);
            },
            complete: function() {
                // Restaurar el botón
                $('#confirmarRechazo').prop('disabled', false).html('<i class="fas fa-times-circle me-2"></i> Rechazar Orden');
            }
        });
    });
    
    // Guardar Orden (Nueva)
    $('#saveOrden').on('click', function() {
        if ($('#ordenForm')[0].checkValidity()) {
            const formData = new FormData($('#ordenForm')[0]);
            formData.append('action', 'create');
            
            $.ajax({
                url: '../controllers/OrdenProduccionController.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        alert('Orden de producción creada correctamente');
                        $('#ordenModal').modal('hide');
                        $('#ordenForm')[0].reset();
                        loadOrdenes(1);
                    } else {
                        alert('Error al crear la orden: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error al conectar con el servidor');
                }
            });
        } else {
            $('#ordenForm')[0].reportValidity();
        }
    });
    
    // Actualizar Orden (Editar)
    $('#updateOrden').on('click', function() {
        if ($('#editOrdenForm')[0].checkValidity()) {
            const formData = new FormData($('#editOrdenForm')[0]);
            formData.append('action', 'update');
            
            // Asegurarse de que el ID se envíe correctamente
            const ordenId = $('#editOrdenId').val();
            if (!formData.has('id') || formData.get('id') === '') {
                formData.set('id', ordenId);
            }
            
            $.ajax({
                url: '../controllers/OrdenProduccionController.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        alert('Orden de producción actualizada correctamente');
                        $('#editOrdenModal').modal('hide');
                        loadOrdenes(currentPage);
                    } else {
                        alert('Error al actualizar la orden: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error en la solicitud AJAX:', xhr.responseText);
                    alert('Error al conectar con el servidor: ' + error);
                }
            });
        } else {
            $('#editOrdenForm')[0].reportValidity();
        }
    });

    // Modal de Eliminar
    $(document).on('click', '.eliminar-orden', function() {
        const id = $(this).data('id');
        $('#deleteOrdenId').val(id);
        $('#deleteOrdenModal').modal('show');
    });
    
    // Confirmar Eliminación
    $('#confirmDelete').on('click', function() {
        const id = $('#deleteOrdenId').val();
        const password = $('#deletePassword').val();
        
        if (!password) {
            alert('Por favor ingrese su contraseña para confirmar la eliminación');
            return;
        }
        
        $.ajax({
            url: '../controllers/OrdenProduccionController.php',
            type: 'POST',
            data: {
                action: 'delete',
                id: id,
                password: password
            },
            success: function(response) {
                if (response.success) {
                    alert('Orden de producción eliminada correctamente');
                    $('#deleteOrdenModal').modal('hide');
                    $('#deletePassword').val('');
                    loadOrdenes(currentPage);
                } else {
                    alert('Error al eliminar la orden: ' + response.message);
                }
            },
            error: function() {
                alert('Error al conectar con el servidor');
            }
        });
    });

    // Funciones auxiliares
    function getEstadoClass(estado) {
        switch (estado) {
            case 'Pendiente': return 'bg-warning';
            case 'En proceso': return 'bg-primary';
            case 'Completado': return 'bg-success';
            default: return 'bg-secondary';
        }
    }
    
    function getAprobacionClass(estado) {
        switch (estado) {
            case 'Pendiente': return 'bg-warning';
            case 'Aprobado': return 'bg-success';
            case 'Rechazado': return 'bg-danger';
            default: return 'bg-secondary';
        }
    }

    // Cargar órdenes inicialmente
    loadOrdenes();
});
