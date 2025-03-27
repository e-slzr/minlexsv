<?php
require_once '../controllers/PoController.php';

$poController = new PoController();
$filtros = []; // Puedes agregar filtros si es necesario
$pos = $poController->getPos($filtros);

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=pos.xls");
header("Pragma: no-cache");
header("Expires: 0");

echo "ID\tPO\tFecha Creación\tFecha Envío\tEstado\tCliente\tUsuario de ingreso\tProgreso\n";

foreach ($pos as $po) {
    echo htmlspecialchars($po['id']) . "\t" .
         htmlspecialchars($po['po_numero']) . "\t" .
         htmlspecialchars($po['po_fecha_creacion']) . "\t" .
         htmlspecialchars($po['po_fecha_envio_programada'] ?? 'No definida') . "\t" .
         htmlspecialchars($po['po_estado']) . "\t" .
         htmlspecialchars($po['cliente_empresa']) . "\t" .
         htmlspecialchars($po['usuario_creacion']) . "\t" .
         $po['progreso'] . "%\n";
}
?>
