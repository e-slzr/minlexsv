$(document).ready(function() {
    // Variables globales
    let itemsArray = [];
    let totalGeneral = 0;
    
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
    
    // Cargar los detalles de la PO desde los datos pasados por PHP
    loadPoDetailsFromData();
    
    // Evento para generar PDF
    $('#generatePdfBtn').on('click', function() {
        const poId = $('#poId').val();
        window.open(`../components/generar_pdf_po.php?id=${poId}`, '_blank');
    });
    
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
    
    // Función para cargar los detalles de la PO desde los datos pasados por PHP
    function loadPoDetailsFromData() {
        try {
            console.log('Detalles de la PO recibidos:', poDetails);
            
            // Verificar si hay detalles
            if (!poDetails || poDetails.length === 0) {
                $('#itemsTableBody').html('<tr><td colspan="8" class="text-center">No hay items agregados</td></tr>');
                $('#totalGeneral').text('$0.00');
                return;
            }
            
            // Procesar los detalles
            itemsArray = poDetails.map(item => ({
                id: item.id,
                pd_item: item.pd_item,
                item_numero: item.item_numero || '',
                item_nombre: item.item_nombre || '',
                pd_cant_piezas_total: parseInt(item.pd_cant_piezas_total) || 0,
                pd_pcs_carton: parseInt(item.pd_pcs_carton) || 0,
                pd_pcs_poly: parseInt(item.pd_pcs_poly) || 0,
                pd_precio_unitario: parseFloat(item.pd_precio_unitario) || 0
            }));
            
            // Actualizar la tabla de items
            updateItemsTable();
            
            // Actualizar el campo oculto de items
            $('#items').val(JSON.stringify(itemsArray));
        } catch (e) {
            console.error('Error al procesar los detalles de la PO:', e);
            $('#itemsTableBody').html('<tr><td colspan="8" class="text-center">Error al procesar los detalles</td></tr>');
            $('#totalGeneral').text('$0.00');
        }
    }
    
    // Actualizar la tabla de items
    function updateItemsTable() {
        if (itemsArray.length === 0) {
            $('#itemsTableBody').html('<tr><td colspan="8" class="text-center">No hay items agregados</td></tr>');
            $('#totalGeneral').text('$0.00');
            return;
        }
        
        let html = '';
        let total = 0;
        
        itemsArray.forEach((item, index) => {
            const subtotal = item.pd_cant_piezas_total * item.pd_precio_unitario;
            total += subtotal;
            
            html += `
                <tr data-index="${index}" data-item-id="${item.pd_item}" ${item.id ? 'data-id="' + item.id + '"' : ''}>
                    <td>${item.item_numero}</td>
                    <td>${item.item_nombre}</td>
                    <td>
                        <input type="number" class="form-control form-control-sm cant-total" 
                               value="${item.pd_cant_piezas_total}" min="1" required>
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm pcs-carton" 
                               value="${item.pd_pcs_carton}" min="0">
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm pcs-poly" 
                               value="${item.pd_pcs_poly}" min="0">
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm precio-unitario" 
                               value="${item.pd_precio_unitario}" min="0.01" step="0.01" required>
                    </td>
                    <td class="subtotal">$${subtotal.toFixed(2)}</td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm remove-item">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        
        $('#itemsTableBody').html(html);
        $('#totalGeneral').text('$' + total.toFixed(2));
    }
    
    // Manejar cambios en los campos de cantidad y precio para actualizar subtotales
    $(document).on('input', '.cant-total, .precio-unitario', function() {
        const $row = $(this).closest('tr');
        const index = $row.data('index');
        
        // Actualizar el valor en el array
        const cantidad = parseInt($row.find('.cant-total').val()) || 0;
        const pcsCarton = parseInt($row.find('.pcs-carton').val()) || 0;
        const pcsPoly = parseInt($row.find('.pcs-poly').val()) || 0;
        const precio = parseFloat($row.find('.precio-unitario').val()) || 0;
        
        itemsArray[index].pd_cant_piezas_total = cantidad;
        itemsArray[index].pd_pcs_carton = pcsCarton;
        itemsArray[index].pd_pcs_poly = pcsPoly;
        itemsArray[index].pd_precio_unitario = precio;
        
        // Calcular y mostrar subtotal
        const subtotal = cantidad * precio;
        $row.find('.subtotal').text('$' + subtotal.toFixed(2));
        
        // Actualizar total general
        actualizarTotal();
        
        // Actualizar el campo oculto de items
        $('#items').val(JSON.stringify(itemsArray));
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
        const $row = $(this).closest('tr');
        const index = $row.data('index');
        
        // Eliminar el item del array
        itemsArray.splice(index, 1);
        
        // Actualizar la tabla
        updateItemsTable();
        
        // Actualizar el campo oculto de items
        $('#items').val(JSON.stringify(itemsArray));
    });
    
    // Buscar items
    $('#searchItemBtn').on('click', function() {
        const numero = $('#searchItemNumero').val();
        const nombre = $('#searchItemNombre').val();
        
        $.ajax({
            url: '../controllers/ItemController.php',
            type: 'POST',
            data: {
                action: 'search',
                item_numero: numero,
                item_nombre: nombre
            },
            dataType: 'json',
            success: function(response) {
                $('#searchResults').empty();
                
                if (response.success && response.items && response.items.length > 0) {
                    response.items.forEach(function(item) {
                        const row = `
                            <tr>
                                <td>${item.item_numero || ''}</td>
                                <td>${item.item_nombre || ''}</td>
                                <td>${item.item_descripcion || ''}</td>
                                <td>${item.item_talla || ''}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-success add-item-btn" 
                                            data-id="${item.id}" 
                                            data-numero="${item.item_numero || ''}" 
                                            data-nombre="${item.item_nombre || ''}">
                                        <i class="fas fa-plus"></i> Agregar
                                    </button>
                                </td>
                            </tr>
                        `;
                        
                        $('#searchResults').append(row);
                    });
                } else {
                    $('#searchResults').html('<tr><td colspan="5" class="text-center">No se encontraron resultados</td></tr>');
                    console.log('Respuesta de búsqueda:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al buscar items:', error);
                console.error('Respuesta del servidor:', xhr.responseText);
                $('#searchResults').html('<tr><td colspan="5" class="text-center">Error al buscar items</td></tr>');
            }
        });
    });
    
    // Manejar clic en el botón de agregar item
    $(document).on('click', '.add-item-btn', function() {
        const itemId = $(this).data('id');
        const itemNumero = $(this).data('numero');
        const itemNombre = $(this).data('nombre');
        
        // Verificar si el item ya está en la lista
        const existingItem = itemsArray.find(item => item.pd_item == itemId);
        if (existingItem) {
            alert('Este item ya ha sido agregado a la PO');
            return;
        }
        
        // Agregar el item directamente al array
        const newItem = {
            id: null, // Nuevo item, no tiene id aún
            pd_item: itemId,
            item_numero: itemNumero,
            item_nombre: itemNombre,
            pd_cant_piezas_total: 0,
            pd_pcs_carton: 0,
            pd_pcs_poly: 0,
            pd_precio_unitario: 0
        };
        
        // Agregar al array y actualizar la tabla
        itemsArray.push(newItem);
        updateItemsTable();
        
        // Actualizar el campo oculto de items
        $('#items').val(JSON.stringify(itemsArray));
        
        // Mostrar mensaje de éxito
        const $searchResults = $('#searchResults');
        const $successMsg = $('<div class="alert alert-success alert-dismissible fade show mt-2" role="alert">')
            .text(`Item "${itemNombre}" agregado exitosamente`)
            .append('<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>');
        
        // Agregar mensaje después de los resultados
        $searchResults.after($successMsg);
        
        // Auto-ocultar el mensaje después de 3 segundos
        setTimeout(() => {
            $successMsg.alert('close');
        }, 3000);
    });
    
    // Asegurar que el fondo traslúcido se elimine al cerrar el modal
    $('.modal').on('hidden.bs.modal', function () {
        if ($('.modal:visible').length) {
            $('body').addClass('modal-open');
        } else {
            $('body').removeClass('modal-open');
            $('.modal-backdrop').remove();
        }
    });
    
    // Guardar notas detalladas
    $('#saveNotas').on('click', function() {
        const notas = $('#notasDetalladas').val();
        $('#poNotas').val(notas);
        
        if (notas && notas.trim() !== '') {
            $('#notasIndicator').removeClass('d-none');
        } else {
            $('#notasIndicator').addClass('d-none');
        }
        
        $('#notasModal').modal('hide');
    });
    
    // Manejar el envío del formulario
    $('#poForm').on('submit', function(e) {
        e.preventDefault();
        
        // Validar que haya al menos un item
        if (itemsArray.length === 0) {
            alert('Debe agregar al menos un item a la PO');
            return;
        }
        
        // Actualizar los datos de los items desde los campos editables
        const updatedItemsArray = [];
        
        $('#itemsTableBody tr').each(function() {
            const $row = $(this);
            const index = $row.data('index');
            
            if (index !== undefined && index >= 0 && index < itemsArray.length) {
                // Crear una copia del item con los valores actualizados
                const updatedItem = { ...itemsArray[index] };
                updatedItem.pd_cant_piezas_total = parseInt($row.find('.cant-total').val()) || 0;
                updatedItem.pd_pcs_carton = parseInt($row.find('.pcs-carton').val()) || 0;
                updatedItem.pd_pcs_poly = parseInt($row.find('.pcs-poly').val()) || 0;
                updatedItem.pd_precio_unitario = parseFloat($row.find('.precio-unitario').val()) || 0;
                
                updatedItemsArray.push(updatedItem);
            }
        });
        
        // Reemplazar el array original con el actualizado
        if (updatedItemsArray.length > 0) {
            itemsArray = updatedItemsArray;
        }
        
        // Log para depuración
        console.log('Items a enviar:', itemsArray);
        
        // Crear objeto con los datos del formulario
        const formData = new FormData(this);
        
        // Agregar las notas al formulario si se han editado
        if ($('#notasDetalladas').val()) {
            formData.append('po_notas', $('#notasDetalladas').val());
        }
        
        // Agregar los items de la tabla
        formData.append('items', JSON.stringify(itemsArray));
        
        // Mostrar todos los datos que se enviarán para depuración
        console.log('Datos del formulario:');
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + (pair[0] === 'items' ? '(JSON data)' : pair[1]));
        }
        
        // Mostrar indicador de carga
        Swal.fire({
            title: 'Guardando cambios...',
            text: 'Por favor espere',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Enviar el formulario
        $.ajax({
            url: '../controllers/PoController.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Respuesta del servidor:', response);
                
                try {
                    const data = (typeof response === 'string') ? JSON.parse(response) : response;
                    
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: data.message,
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.href = 'po.php';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Error al guardar la PO',
                            confirmButtonText: 'OK'
                        });
                    }
                } catch (e) {
                    console.error('Error al procesar la respuesta:', e);
                    console.error('Respuesta recibida:', response);
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al procesar la respuesta del servidor',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en la solicitud:', error);
                console.error('Respuesta del servidor:', xhr.responseText);
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al guardar la PO. Intente nuevamente.',
                    confirmButtonText: 'OK'
                });
            }
        });
    });
    
    // Presionar Enter en los campos de búsqueda
    $('#searchItemNumero, #searchItemNombre').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#searchItemBtn').click();
        }
    });
});
