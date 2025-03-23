<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
require_once '../controllers/PoController.php';
require_once '../controllers/ClienteController.php';

$poController = new PoController();
$clienteController = new ClienteController();

$clientes = $clienteController->getClientes();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Purchase Order - MinLex El Salvador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
    <?php include '../components/menu_lateral.php'; ?>
    
    <main>
        <div class="container-fluid px-4">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="mb-0">Nueva Purchase Order</h4>
                        </div>
                        <div class="card-body">
                            <form id="poForm" method="POST">
                                <input type="hidden" name="action" value="create">
                                
                                <div class="row">
                                    <!-- Información General -->
                                    <div class="col-md-6">
                                        <h5 class="border-bottom pb-2 mb-3">Información General</h5>
                                        <div class="mb-3">
                                            <label class="form-label">Número PO:</label>
                                            <input type="text" class="form-control" id="poNumero" name="po_numero" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Cliente:</label>
                                            <select class="form-control select2" id="poCliente" name="po_id_cliente" required>
                                                <option value="">Seleccione un cliente...</option>
                                                <?php foreach ($clientes as $cliente): ?>
                                                    <option value="<?php echo $cliente['id']; ?>">
                                                        <?php echo htmlspecialchars($cliente['cliente_empresa']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Tipo de Envío:</label>
                                            <select class="form-control select2" id="poTipoEnvio" name="po_tipo_envio">
                                                <option value="Tipo 1">Tipo 1</option>
                                                <option value="Tipo 2">Tipo 2</option>
                                                <option value="Tipo 3">Tipo 3</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Comentario:</label>
                                            <textarea class="form-control" id="poComentario" name="po_comentario" rows="2"></textarea>
                                        </div>
                                    </div>

                                    <!-- Fechas -->
                                    <div class="col-md-6">
                                        <h5 class="border-bottom pb-2 mb-3">Fechas</h5>
                                        <div class="mb-3">
                                            <label class="form-label">Inicio Producción:</label>
                                            <input type="date" class="form-control" id="poFechaInicio" name="po_fecha_inicio_produccion">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Fin Producción:</label>
                                            <input type="date" class="form-control" id="poFechaFin" name="po_fecha_fin_produccion">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Envío Programado:</label>
                                            <input type="date" class="form-control" id="poFechaEnvio" name="po_fecha_envio_programada" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Detalles de PO -->
                                <div class="card mt-4">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">Detalles de Items</h5>
                                        <button type="button" class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#itemSearchModal">
                                            <i class="fas fa-search"></i> Buscar Item
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive mt-3">
                                            <table class="table table-bordered table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Número</th>
                                                        <th>Nombre</th>
                                                        <th>Cantidad Total</th>
                                                        <th>Pzs x Cartón</th>
                                                        <th>Pzs x Poly</th>
                                                        <th>Precio Unit.</th>
                                                        <th>Subtotal</th>
                                                        <th>Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="itemsTableBody">
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="6" class="text-end"><strong>Total:</strong></td>
                                                        <td id="totalGeneral">$0.00</td>
                                                        <td></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-12 text-end">
                                        <a href="po.php" class="btn btn-secondary">Cancelar</a>
                                        <button type="submit" class="btn btn-dark">Guardar PO</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de búsqueda de items -->
        <div class="modal fade" id="itemSearchModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Buscar Items</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-5">
                                <input type="text" id="searchItemNumero" class="form-control" 
                                       placeholder="Buscar por número de item">
                            </div>
                            <div class="col-md-5">
                                <input type="text" id="searchItemNombre" class="form-control" 
                                       placeholder="Buscar por nombre de item">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-dark w-100" id="searchItemBtn">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Número</th>
                                        <th>Nombre</th>
                                        <th>Descripción</th>
                                        <th>Talla</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="searchResults"></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../components/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="../js/crear_po.js"></script>
</body>
</html>
