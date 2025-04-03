<?php
require_once '../controllers/PoController.php';
require_once '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_GET['id'])) {
    die('ID de PO no proporcionado');
}

$poController = new PoController();
$po = $poController->getPoById($_GET['id']);

if (!$po) {
    die('PO no encontrada');
}

// Configurar DOMPDF
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isPhpEnabled', true);

$dompdf = new Dompdf($options);

// Preparar el HTML
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
        }
        .po-number {
            font-size: 36px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            border: 2px solid #000;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ccc;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .info-item {
            margin-bottom: 5px;
        }
        .label {
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
        }
        .status {
            padding: 5px 10px;
            border-radius: 3px;
            display: inline-block;
        }
        .status-pendiente { background-color: #ffeeba; }
        .status-proceso { background-color: #b8daff; }
        .status-completada { background-color: #c3e6cb; }
        .status-cancelada { background-color: #f5c6cb; }
    </style>
</head>
<body>
    <div class="po-number">PO: ' . htmlspecialchars($po['po_numero']) . '</div>
    
    <div class="section">
        <div class="section-title">Información General</div>
        <div class="info-grid">
            <div class="info-item">
                <span class="label">Cliente:</span> 
                ' . htmlspecialchars($po['cliente_empresa']) . '
            </div>
            <div class="info-item">
                <span class="label">Estado:</span> 
                <span class="status status-' . strtolower($po['po_estado']) . '">
                    ' . htmlspecialchars($po['po_estado']) . '
                </span>
            </div>
            <div class="info-item">
                <span class="label">Fecha Creación:</span> 
                ' . htmlspecialchars($po['po_fecha_creacion']) . '
            </div>
            <div class="info-item">
                <span class="label">Usuario Creación:</span> 
                ' . htmlspecialchars($po['usuario_creacion']) . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Fechas de Producción</div>
        <div class="info-grid">
            <div class="info-item">
                <span class="label">Inicio Producción:</span> 
                ' . htmlspecialchars($po['po_fecha_inicio_produccion'] ?? 'No definida') . '
            </div>
            <div class="info-item">
                <span class="label">Fin Producción:</span> 
                ' . htmlspecialchars($po['po_fecha_fin_produccion'] ?? 'No definida') . '
            </div>
            <div class="info-item">
                <span class="label">Envío Programado:</span> 
                ' . htmlspecialchars($po['po_fecha_envio_programada'] ?? 'No definida') . '
            </div>
            <div class="info-item">
                <span class="label">Tipo Envío:</span> 
                ' . htmlspecialchars($po['po_tipo_envio'] ?? 'No definido') . '
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Items</div>
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Cantidad</th>
                    <th>Precio Unit.</th>
                    <th>Subtotal</th>
                    <th>Estado</th>
                    <th>Progreso</th>
                </tr>
            </thead>
            <tbody>
';

// Agregar detalles de items
foreach ($po['detalles'] as $detalle) {
    $html .= '
        <tr>
            <td>' . htmlspecialchars($detalle['item_numero']) . '</td>
            <td>' . htmlspecialchars($detalle['pd_cant_piezas_total']) . '</td>
            <td>$' . number_format($detalle['pd_precio_unitario'], 2) . '</td>
            <td>$' . number_format($detalle['pd_cant_piezas_total'] * $detalle['pd_precio_unitario'], 2) . '</td>
            <td>
                <span class="status status-' . strtolower($detalle['pd_estado']) . '">
                    ' . htmlspecialchars($detalle['pd_estado']) . '
                </span>
            </td>
            <td>' . htmlspecialchars($detalle['progreso']) . '%</td>
        </tr>
    ';
}

$html .= '
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Notas</div>
        <div class="info-item">
            <span class="label">Comentario:</span><br>
            ' . nl2br(htmlspecialchars($po['po_comentario'] ?? 'Sin comentarios')) . '
        </div>
        <div class="info-item" style="margin-top: 10px;">
            <span class="label">Notas Internas:</span><br>
            ' . nl2br(htmlspecialchars($po['po_notas'] ?? 'Sin notas')) . '
        </div>
    </div>
</body>
</html>
';

// Generar PDF
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Descargar PDF
$dompdf->stream("PO_" . $po['po_numero'] . ".pdf", array("Attachment" => false));