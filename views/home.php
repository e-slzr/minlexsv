<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
require_once '../models/Dashboard.php';
$dashboard = new Dashboard();
$stats = $dashboard->getStats();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MinlexSV</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/style_main.css">
</head>
<body>
    <?php include '../components/menu_lateral.php'; ?>
    
    <main>
        <div class="dashboard-container">
            <!-- Título -->
            <div class="titulo-vista">
            <h1><strong>Dashboard</strong></h1><br>
            </div>

            <!-- Estadísticas Principales -->
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div>
                        <div class="stat-title">Total POs</div>
                        <div class="stat-value"><?php echo number_format($stats['pos']['total_pos']); ?></div>
                        <div class="stat-subtitle">
                            <?php echo $stats['pos']['pos_en_proceso']; ?> en proceso
                        </div>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-primary" style="width: <?php echo ($stats['pos']['pos_en_proceso'] / $stats['pos']['total_pos']) * 100; ?>%"></div>
                    </div>
                </div>

                <div class="stat-card">
                    <div>
                        <div class="stat-title">Clientes Activos</div>
                        <div class="stat-value"><?php echo number_format($stats['clientes']['clientes_activos']); ?></div>
                        <div class="stat-subtitle">
                            de <?php echo number_format($stats['clientes']['total_clientes']); ?> clientes totales
                        </div>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-success" style="width: <?php echo ($stats['clientes']['clientes_activos'] / $stats['clientes']['total_clientes']) * 100; ?>%"></div>
                    </div>
                </div>

                <div class="stat-card">
                    <div>
                        <div class="stat-title">Progreso Promedio</div>
                        <div class="stat-value"><?php echo number_format($stats['progreso']['progreso_promedio']); ?>%</div>
                        <div class="stat-subtitle">
                            POs en proceso
                        </div>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-warning" style="width: <?php echo $stats['progreso']['progreso_promedio']; ?>%"></div>
                    </div>
                </div>

                <div class="stat-card">
                    <div>
                        <div class="stat-title">Usuarios Activos</div>
                        <div class="stat-value"><?php echo number_format($stats['usuarios']['usuarios_activos']); ?></div>
                        <div class="stat-subtitle">
                            de <?php echo number_format($stats['usuarios']['total_usuarios']); ?> usuarios totales
                        </div>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-info" style="width: <?php echo ($stats['usuarios']['usuarios_activos'] / $stats['usuarios']['total_usuarios']) * 100; ?>%"></div>
                    </div>
                </div>
            </div>

            <!-- Gráficos y Tablas -->
            <div class="dashboard-charts">
                <div class="chart-container">
                    <h5 class="chart-title">POs por Mes</h5>
                    <div class="chart-area">
                        <canvas id="posChart"></canvas>
                    </div>
                </div>

                <div class="chart-container">
                    <h5 class="chart-title">Estado de POs</h5>
                    <div class="chart-area">
                        <canvas id="estadosChart"></canvas>
                    </div>
                </div>

                <div class="top-clients">
                    <h5 class="chart-title">Top Clientes (últimos 6 meses)</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th class="text-end">POs</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats['top_clientes'] as $cliente): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($cliente['cliente_empresa']); ?></td>
                                    <td class="text-end"><?php echo number_format($cliente['total_pos']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <?php include '../components/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../js/main.js"></script>
</body>
</html>