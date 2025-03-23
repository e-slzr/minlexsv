<?php
require_once '../config/session.php';
require_once '../controllers/ProduccionAvanceController.php';
require_once '../controllers/OrdenProduccionController.php';
require_once '../controllers/ProcesosProduccionController.php';

$produccionController = new ProduccionAvanceController();
$ordenesController = new OrdenProduccionController();
$procesosController = new ProcesosProduccionController();

// Obtener procesos de producción para el filtro
$procesos = $procesosController->getProcesos();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Producción</title>
    <?php include '../components/meta.php'; ?>
</head>
<body>
    <?php include '../components/menu_lateral.php'; ?>
    
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4">Registro de Producción</h1>
            
            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="filtro_proceso" class="form-label">Proceso</label>
                            <select class="form-select" id="filtro_proceso">
                                <option value="">Todos los procesos</option>
                                <?php foreach ($procesos as $proceso): ?>
                                    <option value="<?php echo $proceso['id']; ?>">
                                        <?php echo htmlspecialchars($proceso['pp_nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="filtro_fecha" class="form-label">Fecha</label>
                            <input type="date" class="form-control" id="filtro_fecha" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista de Órdenes de Producción -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-list me-1"></i>
                    Órdenes de Producción Pendientes
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="tablaOrdenes">
                            <thead>
                                <tr>
                                    <th>Orden #</th>
                                    <th>PO</th>
                                    <th>Proceso</th>
                                    <th>Item</th>
                                    <th>Cantidad Total</th>
                                    <th>Completado</th>
                                    <th>Pendiente</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Se llena dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal de Registro de Avance -->
    <div class="modal fade" id="modalRegistroAvance" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Registrar Avance de Producción</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formRegistroAvance">
                        <input type="hidden" id="orden_produccion_id" name="orden_produccion_id">
                        <input type="hidden" id="proceso_id" name="proceso_id">
                        
                        <div class="mb-3">
                            <label for="cantidad_completada" class="form-label">Cantidad Completada</label>
                            <input type="number" class="form-control" id="cantidad_completada" name="cantidad_completada" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea class="form-control" id="observaciones" name="observaciones" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="estado" class="form-label">Estado</label>
                            <select class="form-select" id="estado" name="estado" required>
                                <option value="en_proceso">En Proceso</option>
                                <option value="completado">Completado</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarAvance">Guardar Avance</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tablaOrdenes = document.getElementById('tablaOrdenes');
            const formRegistroAvance = document.getElementById('formRegistroAvance');
            const modalRegistroAvance = new bootstrap.Modal(document.getElementById('modalRegistroAvance'));

            // Cargar órdenes de producción
            function cargarOrdenes() {
                const proceso_id = document.getElementById('filtro_proceso').value;
                const fecha = document.getElementById('filtro_fecha').value;

                fetch(`../controllers/OrdenProduccionController.php?action=getOrdenesPendientes&proceso_id=${proceso_id}&fecha=${fecha}`)
                    .then(response => response.json())
                    .then(data => {
                        const tbody = tablaOrdenes.querySelector('tbody');
                        tbody.innerHTML = '';

                        data.forEach(orden => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${orden.id}</td>
                                <td>${orden.po_numero}</td>
                                <td>${orden.proceso_nombre}</td>
                                <td>${orden.item_nombre}</td>
                                <td>${orden.cantidad_total}</td>
                                <td>${orden.cantidad_completada || 0}</td>
                                <td>${orden.cantidad_total - (orden.cantidad_completada || 0)}</td>
                                <td>
                                    <button class="btn btn-primary btn-sm" 
                                            onclick="abrirModalAvance(${orden.id}, ${orden.proceso_id})">
                                        Registrar Avance
                                    </button>
                                </td>
                            `;
                            tbody.appendChild(row);
                        });
                    })
                    .catch(error => console.error('Error:', error));
            }

            // Evento para abrir modal de registro
            window.abrirModalAvance = function(orden_id, proceso_id) {
                document.getElementById('orden_produccion_id').value = orden_id;
                document.getElementById('proceso_id').value = proceso_id;
                modalRegistroAvance.show();
            };

            // Evento para guardar avance
            document.getElementById('btnGuardarAvance').addEventListener('click', function() {
                const formData = new FormData(formRegistroAvance);

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
                            text: data.message
                        });
                        modalRegistroAvance.hide();
                        cargarOrdenes();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message
                        });
                    }
                })
                .catch(error => console.error('Error:', error));
            });

            // Eventos de filtros
            document.getElementById('filtro_proceso').addEventListener('change', cargarOrdenes);
            document.getElementById('filtro_fecha').addEventListener('change', cargarOrdenes);

            // Cargar órdenes inicialmente
            cargarOrdenes();
        });
    </script>
</body>
</html>
