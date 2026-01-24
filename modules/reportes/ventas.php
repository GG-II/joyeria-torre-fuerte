<?php
// ================================================
// MÓDULO REPORTES - REPORTE DE VENTAS
// ================================================

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

// Verificar autenticación
requiere_autenticacion();

// Título de página
$titulo_pagina = 'Reporte de Ventas';

// Incluir header
include '../../includes/header.php';

// Incluir navbar
include '../../includes/navbar.php';

// Datos dummy del reporte
$resumen = [
    'total_ventas' => 145800.00,
    'total_transacciones' => 125,
    'ticket_promedio' => 1166.40,
    'ventas_credito' => 25800.00,
    'ventas_apartado' => 12400.00,
    'descuentos_aplicados' => 3200.00
];

// Ventas por vendedor
$por_vendedor = [
    ['nombre' => 'María Vendedor', 'ventas' => 85, 'total' => 89500.00],
    ['nombre' => 'Carlos Admin', 'nombre' => 40, 'total' => 56300.00]
];

// Ventas por sucursal
$por_sucursal = [
    ['nombre' => 'Los Arcos', 'ventas' => 78, 'total' => 92400.00],
    ['nombre' => 'Chinaca Central', 'ventas' => 47, 'total' => 53400.00]
];

// Ventas por forma de pago
$por_forma_pago = [
    ['forma' => 'Efectivo', 'cantidad' => 68, 'total' => 78500.00],
    ['forma' => 'Tarjeta Crédito', 'cantidad' => 35, 'total' => 45200.00],
    ['forma' => 'Tarjeta Débito', 'cantidad' => 15, 'total' => 18300.00],
    ['forma' => 'Transferencia', 'cantidad' => 7, 'total' => 3800.00]
];
?>

<!-- Contenido Principal -->
<div class="container-fluid main-content">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="dashboard.php">
                    <i class="bi bi-graph-up"></i> Reportes
                </a>
            </li>
            <li class="breadcrumb-item active">Ventas</li>
        </ol>
    </nav>

    <!-- Encabezado -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1>
                    <i class="bi bi-cart-check"></i>
                    Reporte de Ventas
                </h1>
                <p class="text-muted">Análisis detallado de ventas</p>
            </div>
            <div class="col-md-6 text-end">
                <button class="btn btn-success">
                    <i class="bi bi-file-earmark-excel"></i>
                    Exportar Excel
                </button>
                <button class="btn btn-secondary">
                    <i class="bi bi-printer"></i>
                    Imprimir
                </button>
            </div>
        </div>
    </div>

    <!-- Filtros de Fecha -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Fecha Desde</label>
                    <input type="date" class="form-control" value="2025-01-01">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha Hasta</label>
                    <input type="date" class="form-control" value="2025-01-31">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sucursal</label>
                    <select class="form-select">
                        <option value="">Todas</option>
                        <option value="1">Los Arcos</option>
                        <option value="2">Chinaca Central</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Vendedor</label>
                    <select class="form-select">
                        <option value="">Todos</option>
                        <option value="1">Carlos Admin</option>
                        <option value="2">María Vendedor</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button class="btn btn-primary w-100">
                        <i class="bi bi-search"></i>
                        Filtrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen General -->
    <div class="row mb-4">
        <div class="col-lg-4">
            <div class="stat-card dorado">
                <div class="stat-icon">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div class="stat-value">Q <?php echo number_format($resumen['total_ventas'], 2); ?></div>
                <div class="stat-label">Total en Ventas</div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="stat-card azul">
                <div class="stat-icon">
                    <i class="bi bi-receipt"></i>
                </div>
                <div class="stat-value"><?php echo $resumen['total_transacciones']; ?></div>
                <div class="stat-label">Total de Transacciones</div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="stat-card verde">
                <div class="stat-icon">
                    <i class="bi bi-calculator"></i>
                </div>
                <div class="stat-value">Q <?php echo number_format($resumen['ticket_promedio'], 2); ?></div>
                <div class="stat-label">Ticket Promedio</div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Ventas por Vendedor -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-person-badge"></i>
                    Ventas por Vendedor
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Vendedor</th>
                                <th>Ventas</th>
                                <th>Total</th>
                                <th>Promedio</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($por_vendedor as $vendedor): ?>
                            <tr>
                                <td><?php echo $vendedor['nombre']; ?></td>
                                <td><?php echo $vendedor['ventas']; ?></td>
                                <td class="fw-bold text-success">
                                    Q <?php echo number_format($vendedor['total'], 2); ?>
                                </td>
                                <td>
                                    Q <?php echo number_format($vendedor['total'] / $vendedor['ventas'], 2); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Ventas por Sucursal -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-building"></i>
                    Ventas por Sucursal
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Sucursal</th>
                                <th>Ventas</th>
                                <th>Total</th>
                                <th>%</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($por_sucursal as $sucursal): ?>
                            <tr>
                                <td><?php echo $sucursal['nombre']; ?></td>
                                <td><?php echo $sucursal['ventas']; ?></td>
                                <td class="fw-bold text-success">
                                    Q <?php echo number_format($sucursal['total'], 2); ?>
                                </td>
                                <td>
                                    <?php echo number_format(($sucursal['total'] / $resumen['total_ventas']) * 100, 1); ?>%
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Ventas por Forma de Pago -->
    <div class="card">
        <div class="card-header">
            <i class="bi bi-wallet2"></i>
            Ventas por Forma de Pago
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <canvas id="chartFormasPago"></canvas>
                </div>
                <div class="col-lg-6">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Forma de Pago</th>
                                <th>Cantidad</th>
                                <th>Total</th>
                                <th>%</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($por_forma_pago as $forma): ?>
                            <tr>
                                <td><?php echo $forma['forma']; ?></td>
                                <td><?php echo $forma['cantidad']; ?></td>
                                <td class="fw-bold">
                                    Q <?php echo number_format($forma['total'], 2); ?>
                                </td>
                                <td>
                                    <?php echo number_format(($forma['total'] / $resumen['total_ventas']) * 100, 1); ?>%
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
const ctx = document.getElementById('chartFormasPago').getContext('2d');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: [
            <?php foreach ($por_forma_pago as $forma): ?>
                '<?php echo $forma['forma']; ?>',
            <?php endforeach; ?>
        ],
        datasets: [{
            data: [
                <?php foreach ($por_forma_pago as $forma): ?>
                    <?php echo $forma['total']; ?>,
                <?php endforeach; ?>
            ],
            backgroundColor: [
                'rgba(212, 175, 55, 0.8)',
                'rgba(30, 58, 138, 0.8)',
                'rgba(34, 197, 94, 0.8)',
                'rgba(239, 68, 68, 0.8)'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>

<?php include '../../includes/footer.php'; ?>