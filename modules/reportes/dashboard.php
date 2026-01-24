<?php
// ================================================
// MÓDULO REPORTES - DASHBOARD GENERAL
// ================================================

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

// Verificar autenticación
requiere_autenticacion();

// Título de página
$titulo_pagina = 'Dashboard de Reportes';

// Incluir header
include '../../includes/header.php';

// Incluir navbar
include '../../includes/navbar.php';

// Datos dummy para el dashboard
$estadisticas = [
    'ventas_hoy' => 12350.00,
    'ventas_mes' => 145800.00,
    'ventas_año' => 892500.00,
    'productos_vendidos_hoy' => 28,
    'clientes_atendidos_hoy' => 15,
    'trabajos_pendientes' => 8,
    'trabajos_completados_mes' => 45,
    'inventario_valor' => 285000.00,
    'productos_bajo_stock' => 12,
    'efectivo_caja' => 11500.00,
    'creditos_pendientes' => 25800.00,
    'apartados_activos' => 8
];

// Ventas por día (últimos 7 días)
$ventas_por_dia = [
    ['fecha' => '2025-01-18', 'total' => 8500.00, 'cantidad' => 12],
    ['fecha' => '2025-01-19', 'total' => 12200.00, 'cantidad' => 18],
    ['fecha' => '2025-01-20', 'total' => 9800.00, 'cantidad' => 14],
    ['fecha' => '2025-01-21', 'total' => 15400.00, 'cantidad' => 22],
    ['fecha' => '2025-01-22', 'total' => 11200.00, 'cantidad' => 16],
    ['fecha' => '2025-01-23', 'total' => 13500.00, 'cantidad' => 19],
    ['fecha' => '2025-01-24', 'total' => 12350.00, 'cantidad' => 15]
];

// Productos más vendidos
$productos_top = [
    ['nombre' => 'Anillo de Oro 18K', 'cantidad' => 15, 'total' => 127500.00],
    ['nombre' => 'Aretes de Plata', 'cantidad' => 28, 'total' => 33600.00],
    ['nombre' => 'Cadena de Oro', 'cantidad' => 12, 'total' => 54000.00],
    ['nombre' => 'Pulsera de Plata', 'cantidad' => 22, 'total' => 20900.00],
    ['nombre' => 'Collar con Diamante', 'cantidad' => 8, 'total' => 48000.00]
];
?>

<!-- Contenido Principal -->
<div class="container-fluid main-content">
    <!-- Encabezado -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1>
                    <i class="bi bi-graph-up"></i>
                    Dashboard de Reportes
                </h1>
                <p class="text-muted">Vista general del negocio</p>
            </div>
            <div class="col-md-6 text-end">
                <div class="btn-group" role="group">
                    <button class="btn btn-outline-primary active">Hoy</button>
                    <button class="btn btn-outline-primary">Semana</button>
                    <button class="btn btn-outline-primary">Mes</button>
                    <button class="btn btn-outline-primary">Año</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas Principales -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card dorado">
                <div class="stat-icon">
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <div class="stat-value">Q <?php echo number_format($estadisticas['ventas_hoy'], 0); ?></div>
                <div class="stat-label">Ventas Hoy</div>
                <small class="text-white-50">+15% vs ayer</small>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card azul">
                <div class="stat-icon">
                    <i class="bi bi-cart-check"></i>
                </div>
                <div class="stat-value"><?php echo $estadisticas['clientes_atendidos_hoy']; ?></div>
                <div class="stat-label">Clientes Hoy</div>
                <small class="text-white-50"><?php echo $estadisticas['productos_vendidos_hoy']; ?> productos</small>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card verde">
                <div class="stat-icon">
                    <i class="bi bi-tools"></i>
                </div>
                <div class="stat-value"><?php echo $estadisticas['trabajos_completados_mes']; ?></div>
                <div class="stat-label">Trabajos Completados (Mes)</div>
                <small class="text-white-50"><?php echo $estadisticas['trabajos_pendientes']; ?> pendientes</small>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card rojo">
                <div class="stat-icon">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div class="stat-value"><?php echo $estadisticas['productos_bajo_stock']; ?></div>
                <div class="stat-label">Productos Bajo Stock</div>
                <small class="text-white-50">Requieren atención</small>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Gráfica de Ventas -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-bar-chart"></i>
                    Ventas de los Últimos 7 Días
                </div>
                <div class="card-body">
                    <canvas id="chartVentas" height="80"></canvas>
                </div>
            </div>
        </div>

        <!-- Estado de Caja -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-cash-stack"></i>
                    Estado de Caja
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Efectivo en Caja:</small>
                        <h3 class="text-success mb-0">
                            Q <?php echo number_format($estadisticas['efectivo_caja'], 2); ?>
                        </h3>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <small class="text-muted d-block">Créditos Pendientes:</small>
                        <h4 class="text-warning mb-0">
                            Q <?php echo number_format($estadisticas['creditos_pendientes'], 2); ?>
                        </h4>
                    </div>
                    <hr>
                    <div class="mb-0">
                        <small class="text-muted d-block">Apartados Activos:</small>
                        <h4 class="text-info mb-0">
                            <?php echo $estadisticas['apartados_activos']; ?> apartados
                        </h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Productos Más Vendidos -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-trophy"></i>
                    Top 5 Productos Más Vendidos (Mes)
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos_top as $index => $prod): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-primary me-2"><?php echo $index + 1; ?></span>
                                    <?php echo $prod['nombre']; ?>
                                </td>
                                <td><?php echo $prod['cantidad']; ?></td>
                                <td class="fw-bold text-success">
                                    Q <?php echo number_format($prod['total'], 2); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Resumen Mensual -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-calendar-month"></i>
                    Resumen del Mes
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                        <div>
                            <small class="text-muted d-block">Total Ventas</small>
                            <h4 class="mb-0 text-success">
                                Q <?php echo number_format($estadisticas['ventas_mes'], 2); ?>
                            </h4>
                        </div>
                        <div class="text-end">
                            <small class="text-muted d-block">vs Mes Anterior</small>
                            <span class="badge bg-success">+12%</span>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                        <div>
                            <small class="text-muted d-block">Trabajos Completados</small>
                            <h4 class="mb-0 text-info">
                                <?php echo $estadisticas['trabajos_completados_mes']; ?>
                            </h4>
                        </div>
                        <div class="text-end">
                            <small class="text-muted d-block">Pendientes</small>
                            <span class="badge bg-warning"><?php echo $estadisticas['trabajos_pendientes']; ?></span>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <div>
                            <small class="text-muted d-block">Valor de Inventario</small>
                            <h4 class="mb-0 text-primary">
                                Q <?php echo number_format($estadisticas['inventario_valor'], 0); ?>
                            </h4>
                        </div>
                        <div class="text-end">
                            <small class="text-muted d-block">Productos Bajos</small>
                            <span class="badge bg-danger"><?php echo $estadisticas['productos_bajo_stock']; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Accesos Rápidos a Reportes -->
    <div class="card">
        <div class="card-header">
            <i class="bi bi-speedometer2"></i>
            Acceso Rápido a Reportes
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <a href="ventas.php" class="btn btn-outline-primary w-100 py-3">
                        <i class="bi bi-cart-check d-block mb-2" style="font-size: 2em;"></i>
                        Reporte de Ventas
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="inventario.php" class="btn btn-outline-success w-100 py-3">
                        <i class="bi bi-box-seam d-block mb-2" style="font-size: 2em;"></i>
                        Reporte de Inventario
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="taller.php" class="btn btn-outline-warning w-100 py-3">
                        <i class="bi bi-tools d-block mb-2" style="font-size: 2em;"></i>
                        Reporte de Taller
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="financiero.php" class="btn btn-outline-danger w-100 py-3">
                        <i class="bi bi-cash-stack d-block mb-2" style="font-size: 2em;"></i>
                        Reporte Financiero
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
// Gráfica de Ventas
const ctx = document.getElementById('chartVentas').getContext('2d');
const chartVentas = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: [
            <?php foreach ($ventas_por_dia as $venta): ?>
                '<?php echo date('d/m', strtotime($venta['fecha'])); ?>',
            <?php endforeach; ?>
        ],
        datasets: [{
            label: 'Ventas (Q)',
            data: [
                <?php foreach ($ventas_por_dia as $venta): ?>
                    <?php echo $venta['total']; ?>,
                <?php endforeach; ?>
            ],
            backgroundColor: 'rgba(212, 175, 55, 0.7)',
            borderColor: 'rgba(212, 175, 55, 1)',
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'Q ' + value.toLocaleString();
                    }
                }
            }
        }
    }
});
</script>

<?php
// Incluir footer
include '../../includes/footer.php';
?>