<?php
require_once 'controllers/PoController.php';
require_once 'models/Po.php';
require_once 'config/database.php';

// Inicializar el controlador
$poController = new PoController();

// Obtener la PO con ID 1
$filtros = ['id' => 1];
$pos = $poController->getPos($filtros);

// Mostrar los resultados
echo "<pre>";
echo "=== Información de la PO ===\n";
if (!empty($pos)) {
    $po = $pos[0]; // Obtener la primera PO (debería ser la única)
    echo "ID: " . htmlspecialchars($po['id']) . "\n";
    echo "Número de PO: " . htmlspecialchars($po['po_numero']) . "\n";
    echo "Fecha de Creación: " . htmlspecialchars($po['po_fecha_creacion']) . "\n";
    echo "Fecha de Inicio Producción: " . htmlspecialchars($po['po_fecha_inicio_produccion'] ?? 'No definida') . "\n";
    echo "Fecha Fin Producción: " . htmlspecialchars($po['po_fecha_fin_produccion'] ?? 'No definida') . "\n";
    echo "Fecha Envío Programada: " . htmlspecialchars($po['po_fecha_envio_programada'] ?? 'No definida') . "\n";
    echo "Estado: " . htmlspecialchars($po['po_estado']) . "\n";
    echo "Cliente: " . htmlspecialchars($po['cliente_empresa']) . "\n";
    echo "Usuario de Creación: " . htmlspecialchars($po['usuario_creacion']) . "\n";
    echo "Tipo de Envío: " . htmlspecialchars($po['po_tipo_envio'] ?? 'No definido') . "\n";
    echo "Comentario: " . htmlspecialchars($po['po_comentario'] ?? 'Sin comentarios') . "\n";
    echo "Progreso: " . $po['progreso'] . "%\n";
} else {
    echo "No se encontró la PO con ID 1";
}
echo "</pre>";
?>