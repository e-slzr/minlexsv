<?php
// Incluir controladores necesarios
require_once '../controllers/ItemController.php';

// Inicializar controladores
$itemController = new ItemController();

// Obtener parámetros de filtro de la URL
$itemNumero = isset($_GET['item_numero']) ? $_GET['item_numero'] : '';
$itemNombre = isset($_GET['item_nombre']) ? $_GET['item_nombre'] : '';

// Título de la página
$pageTitle = "Gestión de Items";
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
</head>
<body>
    <?php include '../components/menu_lateral.php'; ?>

    <main>
        <div class="titulo-vista">
            <h1><strong><?php echo $pageTitle; ?></strong></h1>
            <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#itemModal">
                <i class="fas fa-plus"></i> Nuevo Item
            </button>
        </div>

        <div class="container-fluid">
            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-filter"></i> Filtros
                </div>
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <label for="item_numero" class="form-label">Número de Item</label>
                            <input type="text" class="form-control filtro" id="item_numero" name="item_numero" value="<?php echo htmlspecialchars($itemNumero); ?>" placeholder="Buscar por número...">
                        </div>
                        <div class="col-md-4">
                            <label for="item_nombre" class="form-label">Nombre de Item</label>
                            <input type="text" class="form-control filtro" id="item_nombre" name="item_nombre" value="<?php echo htmlspecialchars($itemNombre); ?>" placeholder="Buscar por nombre...">
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-secondary" id="limpiar-filtros">
                                <i class="fas fa-eraser"></i> Limpiar filtros
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de Items -->
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="tabla-items">
                    <thead>
                        <tr>
                            <th class="sortable" data-column="id"># <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-column="numero">Número <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-column="nombre">Nombre <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-column="talla">Talla <i class="fas fa-sort"></i></th>
                            <th class="sortable" data-column="descripcion">Descripción <i class="fas fa-sort"></i></th>
                            <th>Imagen</th>
                            <th>Especificaciones</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Si hay parámetros de búsqueda, realizar la búsqueda
                        $items = [];
                        if (!empty($itemNumero) || !empty($itemNombre)) {
                            $items = $itemController->searchItems($itemNumero, $itemNombre);
                        } else {
                            $items = $itemController->getAllItems();
                        }

                        if (empty($items)) {
                            echo '<tr><td colspan="8" class="text-center">No se encontraron items</td></tr>';
                        } else {
                            foreach ($items as $item) {
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($item['id']) . '</td>';
                                echo '<td>' . htmlspecialchars($item['item_numero']) . '</td>';
                                echo '<td>' . htmlspecialchars($item['item_nombre']) . '</td>';
                                echo '<td>' . htmlspecialchars($item['item_talla'] ?? 'N/A') . '</td>';
                                echo '<td>' . htmlspecialchars(substr($item['item_descripcion'] ?? 'Sin descripción', 0, 50)) . (strlen($item['item_descripcion'] ?? '') > 50 ? '...' : '') . '</td>';
                                echo '<td>' . (empty($item['item_img']) ? 'No disponible' : '<a href="..' . htmlspecialchars($item['item_img']) . '" target="_blank"><i class="fas fa-image"></i> Ver</a>') . '</td>';
                                echo '<td>' . (empty($item['item_dir_specs']) ? 'No disponible' : '<a href="specs_viewer.php?dir=' . urlencode(str_replace('/uploads/specs/', '', $item['item_dir_specs'])) . '" target="_blank"><i class="fas fa-folder-open"></i> Ver</a>') . '</td>';
                                echo '<td>';
                                echo '<button type="button" class="btn btn-light view-item me-1" data-id="' . $item['id'] . '" data-bs-toggle="modal" data-bs-target="#itemDetailModal">';
                                echo '<svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">';
                                echo '<path d="M2 12C2 12 5.63636 5 12 5C18.3636 5 22 12 22 12C22 12 18.3636 19 12 19C5.63636 19 2 12 2 12Z" stroke="black" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>';
                                echo '<path d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z" stroke="black" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>';
                                echo '</svg>';
                                echo '</button>';
                                echo '<button type="button" class="btn btn-light edit-item me-1" data-id="' . $item['id'] . '" data-bs-toggle="modal" data-bs-target="#itemModal">';
                                echo '<svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">';
                                echo '<path d="M14 6L16.2929 3.70711C16.6834 3.31658 17.3166 3.31658 17.7071 3.70711L20.2929 6.29289C20.6834 6.68342 20.6834 7.31658 20.2929 7.70711L18 10M14 6L4.29289 15.7071C4.10536 15.8946 4 16.149 4 16.4142V19C4 19.5523 4.44772 20 5 20H7.58579C7.851 20 8.10536 19.8946 8.29289 19.7071L18 10M14 6L18 10" stroke="black" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>';
                                echo '</svg>';
                                echo '</button>';
                                echo '<button type="button" class="btn btn-light delete-item" data-id="' . $item['id'] . '" data-numero="' . htmlspecialchars($item['item_numero']) . '" data-bs-toggle="modal" data-bs-target="#deleteItemModal">';
                                echo '<svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">';
                                echo '<path d="M19 7L18.1327 19.1425C18.0579 20.1891 17.187 21 16.1378 21H7.86224C6.81296 21 5.94208 20.1891 5.86732 19.1425L5 7M10 11V17M14 11V17M15 7V4C15 3.44772 14.5523 3 14 3H10C9.44772 3 9 3.44772 9 4V7M4 7H20" stroke="#FF0000" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>';
                                echo '</svg>';
                                echo '</button>';
                                echo '</td>';
                                echo '</tr>';
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Paginación -->
            <div class="row mt-3">
                <div class="col-md-6">
                    <div class="registros-info">
                        Mostrando <span id="registros-mostrados">0</span> de <span id="registros-totales">0</span> registros
                    </div>
                </div>
                <div class="col-md-6">
                    <nav aria-label="Paginación de Items">
                        <ul class="pagination justify-content-end" id="paginacion">
                            <!-- Los botones de paginación se generarán dinámicamente -->
                        </ul>
                    </nav>
                </div>
                <div class="col-md-6 mt-2">
                    <div class="form-group">
                        <label for="registros-por-pagina" class="form-label">Registros por página:</label>
                        <select class="form-select form-select-sm w-auto d-inline-block ms-2" id="registros-por-pagina">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../components/footer.php'; ?>

    <!-- Modal para Crear/Editar Item -->
    <div class="modal fade" id="itemModal" tabindex="-1" role="dialog" aria-labelledby="itemModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="itemModalLabel">Nuevo Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="itemForm" enctype="multipart/form-data">
                        <input type="hidden" id="itemId" name="id">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="itemNumero" class="form-label">Número de Item*</label>
                                <input type="text" class="form-control" id="itemNumero" name="item_numero" required>
                            </div>
                            <div class="col-md-6">
                                <label for="itemTalla" class="form-label">Talla</label>
                                <input type="text" class="form-control" id="itemTalla" name="item_talla">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="itemNombre" class="form-label">Nombre*</label>
                                <input type="text" class="form-control" id="itemNombre" name="item_nombre" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="itemDescripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="itemDescripcion" name="item_descripcion" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="itemImagen" class="form-label">Imagen del Item</label>
                                <input type="file" class="form-control" id="itemImagen" name="item_img" accept="image/*">
                                <small class="form-text text-muted">Formatos aceptados: JPG, PNG, GIF. Tamaño máximo: 2MB</small>
                                <div id="imagenPreview" class="mt-2 border rounded p-2 text-center" style="min-height: 150px; display: flex; align-items: center; justify-content: center;">
                                    <span class="text-muted">Vista previa de la imagen</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="itemSpecs" class="form-label">Archivos de Especificaciones</label>
                                <input type="file" class="form-control" id="itemSpecs" name="item_specs[]" multiple>
                                <small class="form-text text-muted">Formatos aceptados: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG. Tamaño máximo por archivo: 5MB</small>
                                <div id="specsInfo" class="mt-2 border rounded p-2">
                                    <div id="specsList"></div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="saveItem">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Ver Detalles de Item -->
    <div class="modal fade" id="itemDetailModal" tabindex="-1" aria-labelledby="itemDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="itemDetailModalLabel">Detalles del Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Información General</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th>Número Item:</th>
                                    <td id="detailItemNumero"></td>
                                </tr>
                                <tr>
                                    <th>Nombre:</th>
                                    <td id="detailItemNombre"></td>
                                </tr>
                                <tr>
                                    <th>Talla:</th>
                                    <td id="detailItemTalla"></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Imagen</h6>
                            <div id="detailItemImagen" class="text-center"></div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <h6>Descripción</h6>
                            <p id="detailItemDescripcion"></p>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <h6>Especificaciones</h6>
                            <p id="detailItemSpecs"></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Eliminar Item -->
    <div class="modal fade" id="deleteItemModal" tabindex="-1" aria-labelledby="deleteItemModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteItemModalLabel">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro que desea eliminar el Item <strong><span id="deleteItemNumero"></span></strong>?</p>
                    <p>Esta acción no se puede deshacer.</p>
                    <form id="deleteItemForm">
                        <input type="hidden" id="deleteItemId" name="id">
                        <div class="mb-3">
                            <label for="deletePassword" class="form-label">Ingrese su contraseña para confirmar:</label>
                            <input type="password" class="form-control" id="deletePassword" name="password" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Eliminar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/items.js"></script>
</body>
</html>
