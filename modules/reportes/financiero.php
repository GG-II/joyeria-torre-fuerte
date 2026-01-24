<?php
// ================================================
// MÓDULO REPORTES - REPORTE FINANCIERO
// ================================================

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();
requiere_rol(['administrador', 'dueño']);

$titulo_pagina = 'Reporte Financiero';
include '../../includes/header.php';
include '../../includes/navbar.php';

$resumen = [
    'ingresos_totales' => 174300.00,
    'egresos_totales' => 45800.00,
    'utilidad_neta' => 128500.00,
    'margen_utilidad' => 73.7,
    'efectivo_disponible' => 11500.00,
    'cuentas_por_cobrar' => 25800.00,
    'cuentas_por_pagar' => 8500.00
];

$ingresos_detalle = [
    ['concepto' => 'Ventas de Productos', 'monto' => 145800.00],
    ['concepto' => 'Trabajos de Taller', 'monto' => 28500.00]
];

$egresos_detalle = [
    ['concepto' => 'Compra de Materiales', 'monto' => 25000.00],
    ['concepto' => 'Salarios', 'monto' => 12000.00],
    ['concepto' => 'Alquiler', 'monto' => 5000.00],
    ['concepto' => 'Servicios', 'monto' => 2800.00],
    ['concepto' => 'Otros Gastos', 'monto' => 1000.00]
];

$flujo_mensual = [
    ['mes' => 'Ene', 'ingresos' => 174300.00, 'egresos' => 45800.00],
    ['mes' => 'Dic', 'ingresos' => 165200.00, 'egresos' => 42100.00],
    ['mes' => 'Nov', 'ingresos' => 152800.00, 'egresos' => 39500.00]
];
?>

<div class="container-fluid main-content">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="dashboard.php"><i class="bi bi-graph-up"></i> Reportes</a></li>
            <li class="breadcrumb-item active">Financiero</li>
        </ol>
    </nav>

    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1><i class="bi bi-cash-stack"></i> Reporte Financiero</h1>
                <p class="text-muted">Estado financiero del negocio</p>
            </div>
            <div class="col-md-6 text-end">
                <button class="btn btn-success">
                    <i class="bi bi-file-earmark-excel"></i>
                    Exportar
                </button>
            </div>
        </div>
    </div>

    <!-- Indicadores Financieros Principales -->
    <div class="row mb-4">
        <div class="col-lg-3">
            <div class="stat-card verde">
                <div class="stat-icon"><i class="bi bi-arrow-up-circle"></i></div>
                <div class="stat-value">Q <?php echo number_format($resumen['ingresos_totales'], 0); ?></div>
                <div class="stat-label">Ingresos Totales</div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="stat-card rojo">
                <div class="stat-icon"><i class="bi bi-arrow-down-circle"></i></div>
                <div class="stat-value">Q <?php echo number_format($resumen['egresos_totales'], 0); ?></div>
                <div class="stat-label">Egresos Totales</div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="stat-card dorado">
                <div class="stat-icon"><i class="bi bi-trophy"></i></div>
                <div class="stat-value">Q <?php echo number_format($resumen['utilidad_neta'], 0); ?></div>
                <div class="stat-label">Utilidad Neta</div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="stat-card azul">
                <div class="stat-icon"><i class="bi bi-percent"></i></div>
                <div class="stat-value"><?php echo number_format($resumen['margen_utilidad'], 1); ?>%</div>
                <div class="stat-label">Margen de Utilidad</div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Ingresos -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-arrow-up-circle"></i>
                    Detalle de Ingresos
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Concepto</th>
                                <th>Monto</th>
                                <th>%</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ingresos_detalle as $ing): ?>
                            <tr>
                                <td><?php echo $ing['concepto']; ?></td>
                                <td class="fw-bold text-success">
                                    Q <?php echo number_format($ing['monto'], 2); ?>
                                </td>
                                <td>
                                    <?php echo number_format(($ing['monto'] / $resumen['ingresos_totales']) * 100, 1); ?>%
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td class="fw-bold">TOTAL</td>
                                <td class="fw-bold text-success">
                                    Q <?php echo number_format($resumen['ingresos_totales'], 2); ?>
                                </td>
                                <td class="fw-bold">100%</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Egresos -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <i class="bi bi-arrow-down-circle"></i>
                    Detalle de Egresos
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Concepto</th>
                                <th>Monto</th>
                                <th>%</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($egresos_detalle as $egr): ?>
                            <tr>
                                <td><?php echo $egr['concepto']; ?></td>
                                <td class="fw-bold text-danger">
                                    Q <?php echo number_format($egr['monto'], 2); ?>
                                </td>
                                <td>
                                    <?php echo number_format(($egr['monto'] / $resumen['egresos_totales']) * 100, 1); ?>%
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td class="fw-bold">TOTAL</td>
                                <td class="fw-bold text-danger">
                                    Q <?php echo number_format($resumen['egresos_totales'], 2); ?>
                                </td>
                                <td class="fw-bold">100%</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Estado de Cuentas -->
    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-cash"></i>
                    Efectivo Disponible
                </div>
                <div class="card-body text-center">
                    <h2 class="text-success mb-0">
                        Q <?php echo number_format($resumen['efectivo_disponible'], 2); ?>
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <i class="bi bi-clock-history"></i>
                    Cuentas por Cobrar
                </div>
                <div class="card-body text-center">
                    <h2 class="text-warning mb-0">
                        Q <?php echo number_format($resumen['cuentas_por_cobrar'], 2); ?>
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <i class="bi bi-exclamation-triangle"></i>
                    Cuentas por Pagar
                </div>
                <div class="card-body text-center">
                    <h2 class="text-danger mb-0">
                        Q <?php echo number_format($resumen['cuentas_por_pagar'], 2); ?>
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Flujo de Efectivo -->
    <div class="card">
        <div class="card-header">
            <i class="bi bi-graph-up-arrow"></i>
            Flujo de Efectivo (Últimos 3 Meses)
        </div>
        <div class="card-body">
            <canvas id="chartFlujo" height="80"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
const ctx = document.getElementById('chartFlujo').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: [
            <?php foreach (array_reverse($flujo_mensual) as $mes): ?>
                '<?php echo $mes['mes']; ?>',
            <?php endforeach; ?>
        ],
        datasets: [{
            label: 'Ingresos',
            data: [
                <?php foreach (array_reverse($flujo_mensual) as $mes): ?>
                    <?php echo $mes['ingresos']; ?>,
                <?php endforeach; ?>
            ],
            borderColor: 'rgba(34, 197, 94, 1)',
            backgroundColor: 'rgba(34, 197, 94, 0.1)',
            tension: 0.4
        }, {
            label: 'Egresos',
            data: [
                <?php foreach (array_reverse($flujo_mensual) as $mes): ?>
                    <?php echo $mes['egresos']; ?>,
                <?php endforeach; ?>
            ],
            borderColor: 'rgba(239, 68, 68, 1)',
            backgroundColor: 'rgba(239, 68, 68, 0.1)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top'
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

<?php include '../../includes/footer.php'; ?>