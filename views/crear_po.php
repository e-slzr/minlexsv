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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MINLEX | Crear Purchase Order</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style_main.css">
</head>
<body>
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
                            <form id="poForm" method="POST" action="../controllers/PoController.php">
                                <input type="hidden" name="action" value="create">
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="poNumero" class="form-label">Número de PO*</label>
                                        <input type="text" class="form-control" id="poNumero" name="po_numero" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="poCliente" class="form-label">Cliente*</label>
                                        <select class="form-control" id="poCliente" name="po_id_cliente" required>
                                            <option value="">Seleccione un cliente...</option>
                                            <?php
                                            $clientes = $clienteController->getClientes();
                                            foreach ($clientes as $cliente) {
                                                echo "<option value='" . $cliente['id'] . "'>" . htmlspecialchars($cliente['cliente_empresa']) . "</option>";
                                            }
                                            ?>
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
                                        <label for="poFechaEnvio" class="form-label">Fecha Envío Programada</label>
                                        <input type="date" class="form-control" id="poFechaEnvio" name="po_fecha_envio_programada">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="poEstado" class="form-label">Estado</label>
                                        <select class="form-control" id="poEstado" name="po_estado">
                                            <option value="Pendiente">Pendiente</option>
                                            <option value="En proceso">En proceso</option>
                                            <option value="Completada">Completada</option>
                                            <option value="Cancelada">Cancelada</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="poTipoEnvio" class="form-label">Tipo de Envío</label>
                                        <select class="form-control" id="poTipoEnvio" name="po_tipo_envio">
                                            <option value="Tipo 1">Tipo 1</option>
                                            <option value="Tipo 2">Tipo 2</option>
                                            <option value="Tipo 3">Tipo 3</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="poComentario" class="form-label">Comentario</label>
                                        <textarea class="form-control" id="poComentario" name="po_comentario" rows="2"></textarea>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="poNotas" class="form-label">Notas Internas</label>
                                        <textarea class="form-control" id="poNotas" name="po_notas" rows="2"></textarea>
                                    </div>
                                </div>

                                <div class="row">
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
    </main>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#poForm').on('submit', function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        response = JSON.parse(response);
                        if (response.success) {
                            alert(response.message);
                            window.location.href = 'po.php';
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Error al procesar la solicitud');
                    }
                });
            });
        });
    </script>
</body>
</html>
