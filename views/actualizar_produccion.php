<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

require_once '../controllers/OrdenProduccionController.php';
require_once '../controllers/ProcesoController.php';

// Inicializar controladores
$ordenProduccionController = new OrdenProduccionController();
$procesoController = new ProcesoController();

// Obtener procesos para filtro
$procesos = $procesoController->getProcesos() ?? [];

// Por defecto filtrar por el ID del operario actual
$operadorId = $_SESSION['user']['id'] ?? 0;

// Título de la página
$pageTitle = "Actualizar Producción";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MINLEX | <?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/style_main.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
</head>
<body>
    <?php include '../components/menu_lateral.php'; ?>

    <main>
        <div class="titulo-vista">
            <h1><strong><?php echo $pageTitle; ?></strong></h1>
        </div>

        <div class="container-fluid">
            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-filter"></i> Filtros
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label for="filtro_proceso" class="form-label">Proceso</label>
                            <select class="form-select filtro" id="filtro_proceso" name="proceso">
                                <option value="">Todos los procesos</option>
                                <?php foreach ($procesos as $proceso): ?>
                                <option value="<?php echo $proceso['id']; ?>"><?php echo htmlspecialchars($proceso['pp_nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label for="filtro_estado" class="form-label">Estado</label>
                            <select class="form-select filtro" id="filtro_estado" name="estado">
                                <option value="">Todos</option>
                                <option value="Pendiente" selected>Pendiente</option>
                                <option value="En proceso">En proceso</option>
                                <option value="Completado">Completado</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label for="filtro_item" class="form-label">Item</label>
                            <input type="text" class="form-control filtro" id="filtro_item" name="item" placeholder="Buscar por número o nombre...">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de Órdenes de Producción -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-tasks"></i> Mis Órdenes de Producción
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="tabla-produccion">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>PO</th>
                                    <th>Item</th>
                                    <th>Proceso</th>
                                    <th>Asignado</th>
                                    <th>Completado</th>
                                    <th>Progreso</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Se carga dinámicamente con JavaScript -->
                            </tbody>
                        </table>
                        <div id="sin-resultados" class="alert alert-info d-none">
                            No hay órdenes de producción asignadas con los filtros seleccionados.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal para actualizar producción -->
    <div class="modal fade" id="actualizarModal" tabindex="-1" aria-labelledby="actualizarModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="actualizarModalLabel">Actualizar Producción</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formActualizar">
                        <input type="hidden" id="orden_id" name="id">
                        
                        <div class="mb-3">
                            <label for="item_info" class="form-label">Item:</label>
                            <div id="item_info" class="form-control-plaintext fw-bold"></div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="proceso_info" class="form-label">Proceso:</label>
                            <div id="proceso_info" class="form-control-plaintext"></div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="cantidad_asignada" class="form-label">Cantidad Asignada:</label>
                                <div id="cantidad_asignada" class="form-control-plaintext"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="cantidad_completada_actual" class="form-label">Completado Actual:</label>
                                <div id="cantidad_completada_actual" class="form-control-plaintext"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="cantidad_nueva" class="form-label">Cantidad a Registrar Hoy:</label>
                            <input type="number" class="form-control" id="cantidad_nueva" name="cantidad_nueva" min="1" required>
                            <div class="form-text">Ingrese la cantidad adicional producida (no el total acumulado).</div>
                        </div>

                        <div class="mb-3">
                            <label for="estado" class="form-label">Estado:</label>
                            <select class="form-select" id="estado" name="op_estado" required>
                                <option value="Pendiente">Pendiente</option>
                                <option value="En proceso">En proceso</option>
                                <option value="Completado">Completado</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="comentario" class="form-label">Comentario:</label>
                            <textarea class="form-control" id="comentario" name="op_comentario" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="guardarActualizacion">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <?php include '../components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tablaProduccion = document.getElementById('tabla-produccion').getElementsByTagName('tbody')[0];
            const sinResultados = document.getElementById('sin-resultados');
            const actualizarModal = new bootstrap.Modal(document.getElementById('actualizarModal'));
            
            // Operador actual (del usuario logueado)
            const operadorId = <?php echo $operadorId; ?>;
            
            // Cargar órdenes de producción
            function cargarOrdenes() {
                const proceso = document.getElementById('filtro_proceso').value;
                const estado = document.getElementById('filtro_estado').value;
                const item = document.getElementById('filtro_item').value;
                
                // Construir URL con los filtros
                let url = `../controllers/OrdenProduccionController.php?action=getOrdenesFiltered&operador=${operadorId}`;
                if (proceso) url += `&proceso=${proceso}`;
                if (estado) url += `&estado=${estado}`;
                if (item) url += `&item=${item}`;
                
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        tablaProduccion.innerHTML = '';
                        
                        if (data.length === 0) {
                            sinResultados.classList.remove('d-none');
                        } else {
                            sinResultados.classList.add('d-none');
                            
                            data.forEach(orden => {
                                const progreso = orden.op_cantidad_asignada > 0 
                                    ? Math.round((orden.op_cantidad_completada / orden.op_cantidad_asignada) * 100) 
                                    : 0;
                                
                                const row = tablaProduccion.insertRow();
                                row.innerHTML = `
                                    <td>${orden.id}</td>
                                    <td>${orden.po_numero || 'N/A'}</td>
                                    <td>${orden.item_numero ? orden.item_numero + ' - ' + orden.item_nombre : 'N/A'}</td>
                                    <td>${orden.pp_nombre || 'N/A'}</td>
                                    <td>${orden.op_cantidad_asignada}</td>
                                    <td>${orden.op_cantidad_completada}</td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar ${getProgressClass(progreso)}" role="progressbar" 
                                                 style="width: ${progreso}%" aria-valuenow="${progreso}" 
                                                 aria-valuemin="0" aria-valuemax="100">${progreso}%</div>
                                        </div>
                                    </td>
                                    <td><span class="badge ${getEstadoClass(orden.op_estado)}">${orden.op_estado}</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary actualizar-btn" 
                                                data-id="${orden.id}" 
                                                data-item="${orden.item_numero ? orden.item_numero + ' - ' + orden.item_nombre : 'N/A'}"
                                                data-proceso="${orden.pp_nombre || 'N/A'}"
                                                data-asignada="${orden.op_cantidad_asignada}"
                                                data-completada="${orden.op_cantidad_completada}"
                                                data-estado="${orden.op_estado}"
                                                data-comentario="${orden.op_comentario || ''}">
                                            <i class="fas fa-edit"></i> Actualizar
                                        </button>
                                    </td>
                                `;
                            });
                            
                            // Agregar eventos a los botones de actualizar
                            document.querySelectorAll('.actualizar-btn').forEach(btn => {
                                btn.addEventListener('click', function() {
                                    abrirModalActualizar(this.dataset);
                                });
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudieron cargar las órdenes de producción'
                        });
                    });
            }
            
            // Función para abrir el modal de actualización
            function abrirModalActualizar(data) {
                document.getElementById('orden_id').value = data.id;
                document.getElementById('item_info').textContent = data.item;
                document.getElementById('proceso_info').textContent = data.proceso;
                document.getElementById('cantidad_asignada').textContent = data.asignada;
                document.getElementById('cantidad_completada_actual').textContent = data.completada;
                document.getElementById('estado').value = data.estado;
                document.getElementById('comentario').value = data.comentario;
                
                // Calcular cantidad pendiente para validación
                const pendiente = parseInt(data.asignada) - parseInt(data.completada);
                document.getElementById('cantidad_nueva').max = pendiente;
                document.getElementById('cantidad_nueva').value = '';
                
                actualizarModal.show();
            }
            
            // Guardar actualización
            document.getElementById('guardarActualizacion').addEventListener('click', function() {
                const form = document.getElementById('formActualizar');
                
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }
                
                const ordenId = document.getElementById('orden_id').value;
                const cantidadNueva = parseInt(document.getElementById('cantidad_nueva').value);
                const cantidadActual = parseInt(document.getElementById('cantidad_completada_actual').textContent);
                const estado = document.getElementById('estado').value;
                const comentario = document.getElementById('comentario').value;
                
                // Crear FormData para enviar
                const formData = new FormData();
                formData.append('action', 'updateProgress');
                formData.append('id', ordenId);
                formData.append('op_cantidad_completada', cantidadActual + cantidadNueva);
                formData.append('op_estado', estado);
                formData.append('op_comentario', comentario);
                
                fetch('../controllers/OrdenProduccionController.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: 'Producción actualizada correctamente'
                        }).then(() => {
                            actualizarModal.hide();
                            cargarOrdenes();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'No se pudo actualizar la producción'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error al procesar la solicitud'
                    });
                });
            });
            
            // Funciones de utilidad para las clases CSS
            function getProgressClass(progreso) {
                if (progreso >= 100) return 'bg-success';
                if (progreso >= 66) return 'bg-info';
                if (progreso >= 33) return 'bg-primary';
                return 'bg-warning';
            }
            
            function getEstadoClass(estado) {
                switch (estado) {
                    case 'Completado': return 'bg-success';
                    case 'En proceso': return 'bg-primary';
                    default: return 'bg-secondary';
                }
            }
            
            // Manejar eventos de filtro
            document.querySelectorAll('.filtro').forEach(filtro => {
                filtro.addEventListener('change', cargarOrdenes);
            });
            
            document.getElementById('filtro_item').addEventListener('keyup', function(event) {
                if (event.key === 'Enter') {
                    cargarOrdenes();
                }
            });
            
            // Cargar órdenes inicialmente
            cargarOrdenes();
        });
    </script>
</body>
</html>
