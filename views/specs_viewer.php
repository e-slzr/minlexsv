<?php
header('Content-Type: text/html; charset=utf-8');

// Validar y obtener la ruta del directorio
$dir = isset($_GET['dir']) ? urldecode($_GET['dir']) : '';
$baseDir = '../uploads/specs/';
$fullPath = realpath($baseDir . $dir);
$basePathReal = realpath($baseDir);

// Validar que la ruta esté dentro del directorio base permitido
if ($fullPath === false || strpos($fullPath, $basePathReal) !== 0) {
    die('Acceso no permitido');
}

// Obtener el contenido del directorio
$files = scandir($fullPath);
$items = [];

foreach ($files as $file) {
    if ($file == '.' || $file == '..') continue;
    
    $path = $fullPath . DIRECTORY_SEPARATOR . $file;
    $relativePath = substr($path, strlen($basePathReal));
    $items[] = [
        'name' => $file,
        'path' => str_replace('\\', '/', $relativePath),
        'is_dir' => is_dir($path),
        'size' => is_file($path) ? filesize($path) : 0,
        'type' => is_file($path) ? pathinfo($file, PATHINFO_EXTENSION) : 'folder'
    ];
}

// Ordenar items: carpetas primero, luego archivos
usort($items, function($a, $b) {
    if ($a['is_dir'] && !$b['is_dir']) return -1;
    if (!$a['is_dir'] && $b['is_dir']) return 1;
    return strcasecmp($a['name'], $b['name']);
});
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Especificaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* Estilos generales */
        .file-icon { font-size: 1.2em; margin-right: 10px; }
        .file-name { color: inherit; text-decoration: none; }
        .file-name:hover { text-decoration: underline; }
        
        /* Vista de lista */
        .list-view .file-row:hover { background-color: #f8f9fa; }
        
        /* Vista de cuadrícula */
        .grid-view { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem; padding: 1rem; }
        .grid-view .file-item { 
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            padding: 1rem;
            text-align: center;
            transition: transform 0.2s;
        }
        .grid-view .file-item:hover { 
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .grid-view .file-icon { 
            font-size: 2.5em;
            margin: 0.5rem 0;
            display: block;
        }
        .grid-view .file-details {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 0.5rem;
        }
        
        /* Botón de cambio de vista */
        .view-toggle {
            margin-left: auto;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-3">
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Especificaciones</h5>
                <div class="btn-group view-toggle">
                    <button type="button" class="btn btn-outline-secondary active" data-view="list">
                        <i class="fas fa-list"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" data-view="grid">
                        <i class="fas fa-th"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="view-container list-view">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Tamaño</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr class="file-row">
                                <td>
                                    <?php if ($item['is_dir']): ?>
                                    <a href="?dir=<?= urlencode(trim($dir . '/' . $item['name'], '/')) ?>" class="file-name">
                                        <i class="fas fa-folder text-warning file-icon"></i>
                                        <?= htmlspecialchars($item['name']) ?>
                                    </a>
                                    <?php else: ?>
                                    <a href="../uploads/specs<?= htmlspecialchars($item['path']) ?>" target="_blank" class="file-name">
                                        <i class="fas <?= getFileIcon($item['type']) ?> file-icon"></i>
                                        <?= htmlspecialchars($item['name']) ?>
                                    </a>
                                    <?php endif; ?>
                                </td>
                                <td><?= $item['is_dir'] ? 'Carpeta' : strtoupper($item['type']) ?></td>
                                <td><?= $item['is_dir'] ? '-' : formatFileSize($item['size']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="3" class="text-center py-3">No hay archivos en este directorio</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                        </table>
                    </div>
                    <div class="grid-view d-none">
                        <?php foreach ($items as $item): ?>
                        <div class="file-item">
                            <?php if ($item['is_dir']): ?>
                            <a href="?dir=<?= urlencode(trim($dir . '/' . $item['name'], '/')) ?>" class="file-name">
                                <i class="fas fa-folder text-warning file-icon"></i>
                                <div><?= htmlspecialchars($item['name']) ?></div>
                            </a>
                            <?php else: ?>
                            <a href="../uploads/specs<?= htmlspecialchars($item['path']) ?>" target="_blank" class="file-name">
                                <i class="fas <?= getFileIcon($item['type']) ?> file-icon"></i>
                                <div><?= htmlspecialchars($item['name']) ?></div>
                            </a>
                            <div class="file-details">
                                <?= strtoupper($item['type']) ?> - <?= formatFileSize($item['size']) ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($items)): ?>
                        <div class="text-center py-3">No hay archivos en este directorio</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const viewContainer = document.querySelector('.view-container');
            const listView = document.querySelector('.table-responsive');
            const gridView = document.querySelector('.grid-view');
            const viewToggleButtons = document.querySelectorAll('.view-toggle button');

            viewToggleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const view = this.dataset.view;
                    
                    // Actualizar botones
                    viewToggleButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Cambiar vista
                    if (view === 'grid') {
                        viewContainer.classList.remove('list-view');
                        listView.classList.add('d-none');
                        gridView.classList.remove('d-none');
                    } else {
                        viewContainer.classList.add('list-view');
                        listView.classList.remove('d-none');
                        gridView.classList.add('d-none');
                    }
                });
            });
        });
    </script>
</body>
</html>

<?php
function getFileIcon($type) {
    $icons = [
        'pdf' => 'fa-file-pdf text-danger',
        'doc' => 'fa-file-word text-primary',
        'docx' => 'fa-file-word text-primary',
        'xls' => 'fa-file-excel text-success',
        'xlsx' => 'fa-file-excel text-success',
        'jpg' => 'fa-file-image text-info',
        'jpeg' => 'fa-file-image text-info',
        'png' => 'fa-file-image text-info',
        'gif' => 'fa-file-image text-info'
    ];
    
    return isset($icons[$type]) ? $icons[$type] : 'fa-file text-secondary';
}

function formatFileSize($size) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    while ($size >= 1024 && $i < count($units) - 1) {
        $size /= 1024;
        $i++;
    }
    return round($size, 1) . ' ' . $units[$i];
}
?>