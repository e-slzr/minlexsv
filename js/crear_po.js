$(document).ready(function() {
    // Inicializar Select2 con búsqueda habilitada
    $('.select2').select2({
        theme: 'bootstrap-5',
        width: '100%',
        language: {
            noResults: function() {
                return "No se encontraron resultados";
            },
            searching: function() {
                return "Buscando...";
            },
            inputTooShort: function() {
                return "Ingrese al menos un carácter para buscar";
            }
        },
        placeholder: "Seleccione una opción",
        allowClear: true,
        minimumInputLength: 0,
        minimumResultsForSearch: 5 // Mostrar búsqueda solo si hay más de 5 opciones
    });
    
    // Variable para almacenar las notas
    let poNotas = '';
    
    // Evento para expandir notas detalladas
    $('#expandNotasBtn').on('click', function() {
        const notasCompletas = $('#poNotas').val();
        
        Swal.fire({
            title: 'Notas Detalladas',
            html: `<div style="text-align: left; white-space: pre-wrap;">${notasCompletas}</div>`,
            width: '600px',
            confirmButtonText: 'Cerrar'
        });
    });
    
    // Guardar notas cuando se haga clic en el botón
    $('#saveNotas').on('click', function() {
        poNotas = $('#notasDetalladas').val();
        $('#poNotas').val(poNotas);
        
        // Actualizar la vista previa de las notas
        if (poNotas.trim() !== '') {
            // Mostrar vista previa con texto truncado si es necesario
            if (poNotas.length > 100) {
                const notasPreview = poNotas.substring(0, 100) + '...';
                $('#notasPreview').text(notasPreview);
                $('#expandNotasBtn').show();
            } else {
                $('#notasPreview').text(poNotas);
                $('#expandNotasBtn').hide();
            }
        } else {
            $('#notasPreview').html('<em class="text-muted">No hay notas detalladas</em>');
            $('#expandNotasBtn').hide();
        }
        
        // Cerrar el modal
        $('#notasModal').modal('hide');
    });
    
    // Función para realizar la búsqueda
    function searchItems() {
        const itemNumero = $('#searchItemNumero').val().trim();
        const itemNombre = $('#searchItemNombre').val().trim();

        if (!itemNumero && !itemNombre) {
            $('#searchResults').html(`
                <tr>
                    <td colspan="5" class="text-center">
                        Ingrese al menos un criterio de búsqueda
                    </td>
                </tr>
            `);
            return;
        }

        // Mostrar indicador de carga
        $('#searchResults').html(`
            <tr>
                <td colspan="5" class="text-center">
                    <div class="spinner-border text-dark" role="status">
                        <span class="visually-hidden">Buscando...</span>
                    </div>
                </td>
            </tr>
        `);

        $.ajax({
            url: '../controllers/ItemController.php',
            method: 'POST',
            data: {
                action: 'search',
                item_numero: itemNumero,
                item_nombre: itemNombre
            },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.items && response.items.length > 0) {
                    let html = '';
                    response.items.forEach(function(item) {
                        html += `
                            <tr>
                                <td>${item.item_numero || ''}</td>
                                <td>${item.item_nombre || ''}</td>
                                <td>${item.item_descripcion || ''}</td>
                                <td>${item.item_talla || ''}</td>
                                <td>
                                    <button type="button" class="btn btn-success btn-sm add-item-btn"
                                            data-id="${item.id}"
                                            data-numero="${item.item_numero}"
                                            data-nombre="${item.item_nombre}">
                                        <i class="fas fa-plus"></i> Agregar
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                    $('#searchResults').html(html);
                } else {
                    $('#searchResults').html(`
                        <tr>
                            <td colspan="5" class="text-center">
                                No se encontraron resultados
                            </td>
                        </tr>
                    `);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en la búsqueda:', error);
                $('#searchResults').html(`
                    <tr>
                        <td colspan="5" class="text-center text-danger">
                            Error al buscar items
                        </td>
                    </tr>
                `);
            }
        });
    }

    // Limpiar campos y resultados al abrir el modal
    $('#itemSearchModal').on('show.bs.modal', function() {
        $('#searchItemNumero').val('');
        $('#searchItemNombre').val('');
        $('#searchResults').html('');
    });

    // Manejar clic en el botón de búsqueda
    $('#searchItemBtn').on('click', function() {
        searchItems();
    });

    // Manejar tecla Enter en los campos de búsqueda
    $('#searchItemNumero, #searchItemNombre').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            searchItems();
        }
    });

    // Manejar clic en el botón de agregar item
    $(document).on('click', '.add-item-btn', function() {
        const itemId = $(this).data('id');
        const itemNumero = $(this).data('numero');
        const itemNombre = $(this).data('nombre');

        // Verificar si el item ya está en la tabla
        if ($(`#itemsTableBody tr[data-item-id="${itemId}"]`).length > 0) {
            alert('Este item ya ha sido agregado a la PO');
            return;
        }

        // Agregar nueva fila a la tabla de items
        const newRow = `
            <tr data-item-id="${itemId}">
                <td>${itemNumero}</td>
                <td>${itemNombre}</td>
                <td>
                    <input type="number" class="form-control form-control-sm cant-total" 
                           min="1" step="1" value="1" required>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm pcs-carton" 
                           min="0" step="1" value="0">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm pcs-poly" 
                           min="0" step="1" value="0">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm precio-unitario" 
                           min="0.01" step="0.01" value="0.00" required>
                </td>
                <td class="subtotal">$0.00</td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-item">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        $('#itemsTableBody').append(newRow);
        
        // Trigger input event para calcular el subtotal
        $(`#itemsTableBody tr[data-item-id="${itemId}"] .cant-total`).trigger('input');
        
        // Mostrar mensaje de éxito
        // alert('Item agregado correctamente. Puede seguir agregando más items o cerrar el modal cuando termine.');
    });

    // Calcular subtotal cuando cambie la cantidad o precio
    $(document).on('input', '.cant-total, .precio-unitario', function() {
        const $row = $(this).closest('tr');
        const cantidad = parseFloat($row.find('.cant-total').val()) || 0;
        const precio = parseFloat($row.find('.precio-unitario').val()) || 0;
        const subtotal = cantidad * precio;
        $row.find('.subtotal').text('$' + subtotal.toFixed(2));
        actualizarTotal();
    });

    // Función para actualizar el total general
    function actualizarTotal() {
        let total = 0;
        $('.subtotal').each(function() {
            total += parseFloat($(this).text().replace('$', '')) || 0;
        });
        $('#totalGeneral').text('$' + total.toFixed(2));
    }

    // Eliminar item de la tabla
    $(document).on('click', '.remove-item', function() {
        $(this).closest('tr').remove();
        actualizarTotal();
    });

    // Manejar el envío del formulario principal
    $('#poForm').on('submit', function(e) {
        e.preventDefault();

        // Validar que haya al menos un item
        if ($('#itemsTableBody tr').length === 0) {
            alert('Debe agregar al menos un item a la PO');
            return;
        }

        // Crear objeto con los datos del formulario
        const formData = new FormData(this);
        
        // Agregar las notas al formulario
        formData.append('po_notas', poNotas);
        
        // Agregar los items de la tabla
        const items = [];
        $('#itemsTableBody tr').each(function() {
            const $row = $(this);
            items.push({
                id: $row.data('item-id'),
                cant_piezas_total: $row.find('.cant-total').val(),
                pcs_carton: $row.find('.pcs-carton').val(),
                pcs_poly: $row.find('.pcs-poly').val(),
                precio_unitario: $row.find('.precio-unitario').val()
            });
        });
        
        formData.append('items', JSON.stringify(items));

        // Mostrar indicador de carga
        const $submitBtn = $(this).find('button[type="submit"]');
        const originalBtnText = $submitBtn.html();
        $submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...').prop('disabled', true);

        // Enviar el formulario
        $.ajax({
            url: '../controllers/PoController.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                try {
                    if (response && response.success) {
                        window.location.href = 'po.php';
                    } else {
                        alert('Error: ' + (response && response.message ? response.message : 'No se pudo crear la PO'));
                        $submitBtn.html(originalBtnText).prop('disabled', false);
                    }
                } catch (e) {
                    console.error('Error al procesar la respuesta:', e);
                    alert('La PO se ha creado correctamente, pero hubo un error al procesar la respuesta.');
                    window.location.href = 'po.php';
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al guardar:', error);
                
                // Verificar si la PO se guardó a pesar del error
                try {
                    const response = xhr.responseText;
                    if (response && response.includes('"success":true')) {
                        alert('La PO se ha creado correctamente, pero hubo un error en la respuesta del servidor.');
                        window.location.href = 'po.php';
                        return;
                    }
                } catch (e) {
                    console.error('Error al analizar la respuesta:', e);
                }
                
                alert('Error al guardar la PO. Por favor, intente nuevamente.');
                $submitBtn.html(originalBtnText).prop('disabled', false);
            }
        });
    });
});
