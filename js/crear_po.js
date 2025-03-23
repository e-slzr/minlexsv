$(document).ready(function() {
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
        const $row = $(this).closest('tr');
        const itemId = $(this).data('id');
        const itemNumero = $(this).data('numero');
        const itemNombre = $(this).data('nombre');

        // Agregar nueva fila a la tabla de items
        const newRow = `
            <tr data-item-id="${itemId}">
                <td>${itemNumero}</td>
                <td>${itemNombre}</td>
                <td>
                    <input type="number" class="form-control form-control-sm cant-total" 
                           min="1" step="1" value="0" required>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm pcs-carton" 
                           min="1" step="1" value="0" required>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm pcs-poly" 
                           min="1" step="1" value="0" required>
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
        const formData = {
            action: 'create',
            proveedor_id: $('#proveedorId').val(),
            fecha_entrega: $('#fechaEntrega').val(),
            items: []
        };

        // Agregar los items de la tabla
        $('#itemsTableBody tr').each(function() {
            const $row = $(this);
            formData.items.push({
                id: $row.data('item-id'),
                cant_piezas_total: $row.find('.cant-total').val(),
                pcs_carton: $row.find('.pcs-carton').val(),
                pcs_poly: $row.find('.pcs-poly').val(),
                precio_unitario: $row.find('.precio-unitario').val()
            });
        });

        // Enviar el formulario
        $.ajax({
            url: '../controllers/PoController.php',
            type: 'POST',
            dataType: 'json',
            data: formData,
            success: function(response) {
                try {
                    if (response.success) {
                        alert('PO creada exitosamente');
                        window.location.href = 'po.php';
                    } else {
                        alert('Error: ' + response.message);
                    }
                } catch (e) {
                    alert('Error al procesar la respuesta del servidor');
                    console.error('Error en la respuesta:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al procesar la solicitud:', error);
                console.error('Respuesta del servidor:', xhr.responseText);
                alert('Error al procesar la solicitud');
            }
        });
    });
});
