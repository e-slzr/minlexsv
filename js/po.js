$(document).ready(function() {
    // Variables globales
    let currentPoId = null;
    let items = [];

    // Cargar items al inicio
    $.get('../controllers/ItemController.php?action=getItems', function(response) {
        if (response.success) {
            items = response.data;
            const select = $('#detailItem');
            items.forEach(item => {
                select.append(`<option value="${item.id}">${item.item_numero} - ${item.item_nombre}</option>`);
            });
        }
    });

    // Evento para crear/editar PO
    $('#savePo').click(function() {
        const formData = new FormData($('#poForm')[0]);
        const action = currentPoId ? 'update' : 'create';
        formData.append('action', action);

        $.ajax({
            url: '../controllers/PoController.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    window.location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Error al procesar la solicitud');
            }
        });
    });

    // Evento para mostrar modal de edición
    $('.edit-po').click(function() {
        const poId = $(this).data('id');
        currentPoId = poId;
        
        $.get(`../controllers/PoController.php?action=getPoInfo&id=${poId}`, function(response) {
            if (response.success) {
                const po = response.po;
                $('#poModalLabel').text('Editar PO');
                $('#poId').val(po.id);
                $('#poNumero').val(po.po_numero).prop('readonly', true);
                $('#poCliente').val(po.po_id_cliente);
                $('#poFechaInicio').val(po.po_fecha_inicio_produccion);
                $('#poFechaFin').val(po.po_fecha_fin_produccion);
                $('#poFechaEnvio').val(po.po_fecha_envio_programada);
                $('#poEstado').val(po.po_estado);
                $('#poTipoEnvio').val(po.po_tipo_envio);
                $('#poComentario').val(po.po_comentario);
                $('#poNotas').val(po.po_notas);
            }
        });
    });

    // Evento para mostrar modal de creación
    $('[data-target="#poModal"]').not('.edit-po').click(function() {
        currentPoId = null;
        $('#poModalLabel').text('Nueva PO');
        $('#poForm')[0].reset();
        $('#poNumero').prop('readonly', false);
    });

    // Evento para ver detalles de PO
    $('.view-po').click(function() {
        const poId = $(this).data('id');
        currentPoId = poId;
        
        $.get(`../controllers/PoController.php?action=getPoInfo&id=${poId}`, function(response) {
            if (response.success) {
                const po = response.po;
                const detalles = response.detalles;
                
                // Información general
                $('#detailPoNumero').text(po.po_numero);
                $('#detailCliente').text(po.cliente_empresa);
                $('#detailEstado').html(`<span class="badge ${getBadgeClass(po.po_estado)}">${po.po_estado}</span>`);
                $('#detailFechaCreacion').text(po.po_fecha_creacion);
                $('#detailFechaInicio').text(po.po_fecha_inicio_produccion || 'No definida');
                $('#detailFechaFin').text(po.po_fecha_fin_produccion || 'No definida');
                $('#detailFechaEnvio').text(po.po_fecha_envio_programada || 'No definida');
                $('#detailTipoEnvio').text(po.po_tipo_envio);
                $('#detailComentario').text(po.po_comentario || 'Sin comentarios');
                $('#detailNotas').text(po.po_notas || 'Sin notas');
                
                // Detalles de items
                const tbody = $('#detailsTableBody');
                tbody.empty();
                
                detalles.forEach(detalle => {
                    const subtotal = (detalle.pd_cant_piezas_total * detalle.pd_precio_unitario).toFixed(2);
                    tbody.append(`
                        <tr>
                            <td>${detalle.item_numero} - ${detalle.item_nombre}</td>
                            <td>${detalle.pd_cant_piezas_total}</td>
                            <td>${detalle.pd_pcs_carton || '-'}</td>
                            <td>${detalle.pd_pcs_poly || '-'}</td>
                            <td>$${detalle.pd_precio_unitario}</td>
                            <td>$${subtotal}</td>
                            <td><span class="badge ${getBadgeClass(detalle.pd_estado)}">${detalle.pd_estado}</span></td>
                            <td>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" 
                                         style="width: ${detalle.progreso}%"
                                         aria-valuenow="${detalle.progreso}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        ${detalle.progreso}%
                                    </div>
                                </div>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-success edit-detail" 
                                        data-id="${detalle.id}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger delete-detail"
                                        data-id="${detalle.id}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `);
                });
                
                $('#detailTotal').text('$' + response.total.toFixed(2));
            }
        });
    });

    // Evento para agregar detalle
    $('#addDetailBtn').click(function() {
        $('#detailModalLabel').text('Agregar Item');
        $('#detailForm')[0].reset();
        $('#detailId').val('');
        $('#detailPoId').val(currentPoId);
        $('#detailModal').modal('show');
    });

    // Evento para editar detalle
    $(document).on('click', '.edit-detail', function() {
        const detalleId = $(this).data('id');
        $('#detailModalLabel').text('Editar Item');
        
        $.get(`../controllers/PoController.php?action=getPoDetails&po_id=${currentPoId}`, function(response) {
            if (response.success) {
                const detalle = response.data.find(d => d.id == detalleId);
                if (detalle) {
                    $('#detailId').val(detalle.id);
                    $('#detailPoId').val(detalle.pd_id_po);
                    $('#detailItem').val(detalle.pd_item);
                    $('#detailCantidad').val(detalle.pd_cant_piezas_total);
                    $('#detailPcsCarton').val(detalle.pd_pcs_carton);
                    $('#detailPcsPoly').val(detalle.pd_pcs_poly);
                    $('#detailPrecio').val(detalle.pd_precio_unitario);
                    $('#detailEstado').val(detalle.pd_estado);
                    $('#detailModal').modal('show');
                }
            }
        });
    });

    // Evento para guardar detalle
    $('#saveDetail').click(function() {
        const formData = new FormData($('#detailForm')[0]);
        const action = $('#detailId').val() ? 'updateDetail' : 'addDetail';
        formData.append('action', action);

        $.ajax({
            url: '../controllers/PoController.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#detailModal').modal('hide');
                    // Recargar detalles
                    $('.view-po[data-id="' + currentPoId + '"]').click();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Error al procesar la solicitud');
            }
        });
    });

    // Evento para mostrar modal de eliminación de PO
    $('.delete-po').click(function() {
        const poId = $(this).data('id');
        const poNumero = $(this).data('po-numero');
        $('#deletePoId').val(poId);
        $('#deletePoNumero').text(poNumero);
    });

    // Evento para confirmar eliminación de PO
    $('#confirmDelete').click(function() {
        const formData = new FormData($('#deletePoForm')[0]);
        formData.append('action', 'delete');

        $.ajax({
            url: '../controllers/PoController.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    window.location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Error al procesar la solicitud');
            }
        });
    });

    // Evento para mostrar modal de eliminación de detalle
    $(document).on('click', '.delete-detail', function() {
        const detalleId = $(this).data('id');
        $('#deleteDetailId').val(detalleId);
        $('#deleteDetailModal').modal('show');
    });

    // Evento para confirmar eliminación de detalle
    $('#confirmDeleteDetail').click(function() {
        const formData = new FormData($('#deleteDetailForm')[0]);
        formData.append('action', 'deleteDetail');

        $.ajax({
            url: '../controllers/PoController.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#deleteDetailModal').modal('hide');
                    // Recargar detalles
                    $('.view-po[data-id="' + currentPoId + '"]').click();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Error al procesar la solicitud');
            }
        });
    });

    // Función auxiliar para obtener la clase de badge según el estado
    function getBadgeClass(estado) {
        switch(estado) {
            case 'Pendiente': return 'bg-warning';
            case 'En proceso': return 'bg-primary';
            case 'Completada': case 'Completado': return 'bg-success';
            case 'Cancelada': return 'bg-danger';
            default: return 'bg-secondary';
        }
    }
});
