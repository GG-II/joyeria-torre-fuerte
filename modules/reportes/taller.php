<?php
// ================================================
// MÓDULO REPORTES - REPORTE DE TALLER
// ================================================

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();
$titulo_pagina = 'Reporte de Taller';
include '../../includes/header.php';
include '../../includes/navbar.php';

$resumen = [
    'trabajos_mes' => 45,
    'completados' => 37,
    'pendientes' => 8,
    'ingresos_mes' => 28500.00,
    'trabajos_atrasados' => 3,
    'ticket_promedio' => 633.33
];

$por_tipo = [
    ['tipo' => 'Reparación', 'cantidad' => 18, 'total' => 12500.00],
    ['tipo' => 'Fabricación', 'cantidad' => 8, 'total' => 8900.00],
    ['tipo' => 'Engaste', 'cantidad' => 12, 'total' => 5400.00],
    ['tipo' => 'Limpieza', 'cantidad' => 7, 'total' => 1700.00]
];

$por_orfebre = [
    ['nombre' => 'Roberto Orfebre', 'trabajos' => 28, 'completados' => 24],
    ['nombre' => 'Juan Artesano', 'trabajos' => 17, 'completados' => 13]
];
?>

<div class="container-fluid main-content">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="dashboard.php"><i class="bi bi-graph-up"></i> Reportes</a></li>
            <li class="breadcrumb-item active">Taller</li>
        </ol>
    </nav>

    <div class="page-header">
        <h1><i class="bi bi-tools"></i> Reporte de Taller</h1>
    </div>

    <div class="row mb-4">
        <div class="col-lg-4">
            <div class="stat-card azul">
                <div class="stat-icon"><i class="bi bi-clipboard-check"></i></div>
                <div class="stat-value"><?php echo $resumen['trabajos_mes']; ?></div>
                <div class="stat-label">Trabajos del Mes</div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="stat-card verde">
                <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
                <div class="stat-value"><?php echo $resumen['completados']; ?></div>
                <div class="stat-label">Completados</div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="stat-card dorado">
                <div class="stat-icon"><i class="bi bi-cash"></i></div>
                <div class="stat-value">Q <?php echo number_format($resumen['ingresos_mes'], 0); ?></div>
                <div class="stat-label">Ingresos del Mes</div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header"><i class="bi bi-hammer"></i> Trabajos por Tipo</div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Tipo de Trabajo</th>
                                <th>Cantidad</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($por_tipo as $tipo): ?>
                            <tr>
                                <td><?php echo $tipo['tipo']; ?></td>
                                <td><?php echo $tipo['cantidad']; ?></td>
                                <td class="fw-bold text-success">Q <?php echo number_format($tipo['total'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header"><i class="bi bi-person-workspace"></i> Desempeño por Orfebre</div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Orfebre</th>
                                <th>Trabajos</th>
                                <th>Completados</th>
                                <th>% Completado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($por_orfebre as $orfebre): ?>
                            <tr>
                                <td><?php echo $orfebre['nombre']; ?></td>
                                <td><?php echo $orfebre['trabajos']; ?></td>
                                <td><?php echo $orfebre['completados']; ?></td>
                                <td>
                                    <span class="badge bg-success">
                                        <?php echo number_format(($orfebre['completados'] / $orfebre['trabajos']) * 100, 1); ?>%
                                    </span>
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

<?php include '../../includes/footer.php'; ?>