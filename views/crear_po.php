<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MINLEX | Crear Purchase Order</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../css/style_main.css">
    <style>
        .select2-container--bootstrap-5 {
            width: 100% !important;
        }
        .select2-container .select2-selection--single {
            height: 38px;
        }
        .select2-container--bootstrap-5 .select2-selection {
            border-radius: 0.375rem;
            border-color: #dee2e6;
        }
    </style>
</head>
<body>
    <?php
    session_start();
    if (!isset($_SESSION['user'])) {
        header("Location: login.php");
        exit();
    }
    require_once '../controllers/PoController.php';
    require_once '../controllers/ClienteController.php';
    require_once '../controllers/ItemController.php';

    $poController = new PoController();
    $clienteController = new ClienteController();
    $itemController = new ItemController();

    $items = $itemController->getItems();
    $clientes = $clienteController->getClientes();
    ?>

    <?php include '../components/menu_lateral.php'; ?>
    
    <main>
        <div class="container-fluid px-4">
            <div class="row">
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4 class="mb-0">Crear Nueva Purchase Order</h4>
                        </div>
                        <div class="card-body">
                            <form id="poForm" method="POST">
                                <input type="hidden" name="action" value="create">
                                
                                <!-- Información básica de PO -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="poNumero" class="form-label">Número de PO*</label>
                                        <input type="text" class="form-control" id="poNumero" name="po_numero" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="poCliente" class="form-label">Cliente*</label>
                                        <select class="form-control select2" id="poCliente" name="po_id_cliente" required>
                                            <option value="">Seleccione un cliente...</option>
                                            <?php foreach ($clientes as $cliente): ?>
                                                <option value="<?php echo $cliente['id']; ?>">
                                                    <?php echo htmlspecialchars($cliente['cliente_empresa']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="poFechaInicio" class="form-label">Fecha Inicio Producción</label>
                                        <input type="date" class="form-control" id="poFechaInicio" name="po_fecha_inicio_produccion">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="poFechaFin" class="form-label">Fecha Fin Producción</label>
                                        <input type="date" class="form-control" id="poFechaFin" name="po_fecha_fin_produccion">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="poFechaEnvio" class="form-label">Fecha Envío Programada*</label>
                                        <input type="date" class="form-control" id="poFechaEnvio" name="po_fecha_envio_programada" required>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="poTipoEnvio" class="form-label">Tipo de Envío</label>
                                        <select class="form-control select2" id="poTipoEnvio" name="po_tipo_envio">
                                            <option value="Tipo 1">Tipo 1</option>
                                            <option value="Tipo 2">Tipo 2</option>
                                            <option value="Tipo 3">Tipo 3</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="poComentario" class="form-label">Comentario</label>
                                        <textarea class="form-control" id="poComentario" name="po_comentario" rows="2"></textarea>
                                    </div>
                                </div>

                                <!-- Detalles de PO -->
                                <div class="card mt-4">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">Detalles de la PO</h5>
                                        <button type="button" class="btn btn-dark btn-sm" id="addDetailBtn">
                                            <i class="fas fa-plus"></i> Agregar Item
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <div id="poDetails">
                                            <!-- Aquí se agregarán dinámicamente los detalles -->
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-dark">Guardar PO</button>
                                        <a href="po.php" class="btn btn-light">Cancelar</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Template para nuevos detalles -->
        <template id="detailTemplate">
            <div class="po-detail-row border rounded p-3 mb-3">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Item*</label>
                        <select class="form-control select2-items" name="items[]" required>
                            <option value="">Seleccione un item...</option>
                            <?php foreach ($items as $item): ?>
                                <option value="<?php echo $item['id']; ?>" 
                                        data-numero="<?php echo htmlspecialchars($item['item_numero']); ?>"
                                        data-nombre="<?php echo htmlspecialchars($item['item_nombre']); ?>">
                                    <?php echo htmlspecialchars($item['item_numero'] . ' - ' . $item['item_nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Cantidad Total*</label>
                        <input type="number" class="form-control cant-total" name="cant_piezas_total[]" required min="1">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Pzs x Cartón*</label>
                        <input type="number" class="form-control pcs-carton" name="pcs_carton[]" required min="1">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Pzs x Poly*</label>
                        <input type="number" class="form-control pcs-poly" name="pcs_poly[]" required min="1">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Precio Unit.*</label>
                        <input type="number" class="form-control precio-unit" name="precio_unitario[]" required step="0.01" min="0">
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="button" class="btn btn-danger btn-sm remove-detail">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </main>

    <!-- Modal de Éxito -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="successModalLabel">¡Éxito!</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="successMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Error -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="errorModalLabel">Error</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="errorMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://kit.fontawesome.com/your-kit-code.js"></script>
    
    <script>
    $(document).ready(function() {
        // Inicializar Select2 en selectores existentes
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Buscar...',
            allowClear: true
        });

        // Agregar nuevo detalle
        $('#addDetailBtn').click(function() {
            const template = document.getElementById('detailTemplate');
            const clone = template.content.cloneNode(true);
            $('#poDetails').append(clone);

            // Inicializar Select2 en el nuevo selector de items
            $('.select2-items').last().select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Buscar item...',
                allowClear: true
            });
        });

        // Eliminar detalle
        $(document).on('click', '.remove-detail', function() {
            $(this).closest('.po-detail-row').remove();
        });

        // Función para mostrar modal de éxito
        function showSuccessModal(message) {
            $('#successMessage').text(message);
            var successModal = new bootstrap.Modal(document.getElementById('successModal'));
            successModal.show();
            // Redirigir después de cerrar el modal
            $('#successModal').on('hidden.bs.modal', function () {
                window.location.href = 'po.php';
            });
        }

        // Función para mostrar modal de error
        function showErrorModal(message) {
            $('#errorMessage').text(message);
            var errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
            errorModal.show();
        }

        // Validación y envío del formulario
        $('#poForm').submit(function(e) {
            e.preventDefault();
            
            if ($('.po-detail-row').length === 0) {
                showErrorModal('Debe agregar al menos un detalle a la PO');
                return false;
            }

            const formData = new FormData(this);
            
            $.ajax({
                url: '../controllers/PoController.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        showSuccessModal(response.message);
                    } else {
                        showErrorModal(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    showErrorModal('Error al procesar la solicitud: ' + error);
                }
            });
        });
    });
    </script>
</body>
</html>
