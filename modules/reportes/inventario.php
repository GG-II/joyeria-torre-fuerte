<?php
// ================================================
// MÓDULO REPORTES - REPORTE DE INVENTARIO
// ================================================

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();
$titulo_pagina = 'Reporte de Inventario';
include '../../includes/header.php';
include '../../includes/navbar.php';

$resumen = [
    'total_productos' => 245,
    'valor_total' => 285000.00,
    'productos_bajo_stock' => 12,
    'productos_agotados' => 5
];

$por_categoria = [
    ['nombre' => 'Anillos', 'cantidad' => 45, 'valor' => 95000.00],
    ['nombre' => 'Aretes', 'cantidad' => 68, 'valor' => 52000.00],
    ['nombre' => 'Collares', 'cantidad' => 32, 'valor' => 78000.00],
    ['nombre' => 'Pulseras', 'cantidad' => 55, 'valor' => 35000.00],
    ['nombre' => 'Cadenas', 'nombre' => 45, 'valor' => 25000.00]
];
?>

<div class="container-fluid main-content">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="dashboard.php"><i class="bi bi-graph-up"></i> Reportes</a></li>
            <li class="breadcrumb-item active">Inventario</li>
        </ol>
    </nav>

    <div class="page-header">
        <h1><i class="bi bi-box-seam"></i> Reporte de Inventario</h1>
    </div>

    <div class="row mb-4">
        <div class="col-lg-3">
            <div class="stat-card azul">
                <div class="stat-icon"><i class="bi bi-boxes"></i></div>
                <div class="stat-value"><?php echo $resumen['total_productos']; ?></div>
                <div class="stat-label">Total de Productos</div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="stat-card dorado">
                <div class="stat-icon"><i class="bi bi-currency-dollar"></i></div>
                <div class="stat-value">Q <?php echo number_format($resumen['valor_total'], 0); ?></div>
                <div class="stat-label">Valor Total</div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="stat-card amarillo">
                <div class="stat-icon"><i class="bi bi-exclamation-triangle"></i></div>
                <div class="stat-value"><?php echo $resumen['productos_bajo_stock']; ?></div>
                <div class="stat-label">Bajo Stock</div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="stat-card rojo">
                <div class="stat-icon"><i class="bi bi-x-circle"></i></div>
                <div class="stat-value"><?php echo $resumen['productos_agotados']; ?></div>
                <div class="stat-label">Agotados</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><i class="bi bi-pie-chart"></i> Inventario por Categoría</div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Categoría</th>
                        <th>Cantidad</th>
                        <th>Valor</th>
                        <th>% del Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($por_categoria as $cat): ?>
                    <tr>
                        <td><?php echo $cat['nombre']; ?></td>
                        <td><?php echo $cat['cantidad']; ?></td>
                        <td class="fw-bold">Q <?php echo number_format($cat['valor'], 2); ?></td>
                        <td><?php echo number_format(($cat['valor'] / $resumen['valor_total']) * 100, 1); ?>%</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>