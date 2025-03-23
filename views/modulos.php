<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

require_once '../controllers/ModuloController.php';
$moduloController = new ModuloController();
$modulos = $moduloController->getModulos();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MINLEX | Módulos</title>
    <?php include '../components/meta.php'; ?>
</head>
<body>
    <?php include '../components/menu_lateral.php'; ?>
    
    <main>
        <div class="titulo-vista">
            <h1><strong>Gestión de Módulos</strong></h1>
            <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#nuevoModuloModal">
                <i class="fas fa-plus"></i> Nuevo Módulo
            </button>
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
                            <label for="filtro-codigo" class="form-label">Código</label>
                            <input type="text" class="form-control filtro" id="filtro-codigo" placeholder="Buscar código...">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label for="filtro-tipo" class="form-label">Tipo</label>
                            <select class="form-select filtro" id="filtro-tipo">
                                <option value="">Todos</option>
                                <option value="Costura">Costura</option>
                                <option value="Corte">Corte</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label for="filtro-estado" class="form-label">Estado</label>
                            <select class="form-select filtro" id="filtro-estado">
                                <option value="">Todos</option>
                                <option value="Activo">Activo</option>
                                <option value="Inactivo">Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de módulos -->
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Tipo</th>
                            <th>Descripción</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($modulos as $modulo): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($modulo['modulo_codigo']); ?></td>
                            <td><?php echo htmlspecialchars($modulo['modulo_tipo']); ?></td>
                            <td><?php echo htmlspecialchars($modulo['modulo_descripcion'] ?? ''); ?></td>
                            <td>
                                <span class="badge <?php echo $modulo['modulo_estado'] === 'Activo' ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo htmlspecialchars($modulo['modulo_estado']); ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary editar-modulo" 
                                        data-id="<?php echo $modulo['id']; ?>"
                                        data-codigo="<?php echo htmlspecialchars($modulo['modulo_codigo']); ?>"
                                        data-tipo="<?php echo htmlspecialchars($modulo['modulo_tipo']); ?>"
                                        data-descripcion="<?php echo htmlspecialchars($modulo['modulo_descripcion'] ?? ''); ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm <?php echo $modulo['modulo_estado'] === 'Activo' ? 'btn-danger' : 'btn-success'; ?> toggle-estado"
                                        data-id="<?php echo $modulo['id']; ?>"
                                        data-estado="<?php echo $modulo['modulo_estado']; ?>">
                                    <i class="fas <?php echo $modulo['modulo_estado'] === 'Activo' ? 'fa-times' : 'fa-check'; ?>"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Nuevo Módulo -->
        <div class="modal fade" id="nuevoModuloModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Nuevo Módulo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="nuevoModuloForm">
                            <div class="mb-3">
                                <label for="tipo" class="form-label">Tipo*</label>
                                <select class="form-select" id="tipo" name="tipo" required>
                                    <option value="">Seleccione un tipo</option>
                                    <option value="Costura">Costura</option>
                                    <option value="Corte">Corte</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="codigo" class="form-label">Número de Módulo*</label>
                                <div class="input-group">
                                    <span class="input-group-text" id="prefijo-modulo">-</span>
                                    <input type="text" class="form-control" id="codigo" name="codigo" required 
                                           pattern="\d{2}" maxlength="2" placeholder="01">
                                </div>
                                <div class="form-text">Solo ingrese el número (dos dígitos)</div>
                            </div>
                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="guardarModulo">Guardar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Editar Módulo -->
        <div class="modal fade" id="editarModuloModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Módulo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editarModuloForm">
                            <input type="hidden" id="editar_id" name="id">
                            <div class="mb-3">
                                <label for="editar_tipo" class="form-label">Tipo*</label>
                                <select class="form-select" id="editar_tipo" name="tipo" required>
                                    <option value="Costura">Costura</option>
                                    <option value="Corte">Corte</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="editar_codigo" class="form-label">Número de Módulo*</label>
                                <div class="input-group">
                                    <span class="input-group-text" id="editar-prefijo-modulo">-</span>
                                    <input type="text" class="form-control" id="editar_codigo" name="codigo" required 
                                           pattern="\d{2}" maxlength="2" placeholder="01">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="editar_descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="editar_descripcion" name="descripcion" rows="3"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="actualizarModulo">Actualizar</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../components/footer.php'; ?>
    <script src="../js/modulos.js"></script>
</body>
</html> 