<?php
// ================================================
// MÓDULO CAJA - VER DETALLES DE CAJA
// ================================================

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

// Verificar autenticación
requiere_autenticacion();

// Obtener ID de la caja
$caja_id = $_GET['id'] ?? 1;

// Datos dummy de la caja (CAMPOS REALES)
$caja = [
    'id' => $caja_id,
    'usuario_id' => 2,
    'usuario_nombre' => 'María Vendedor',
    'sucursal_id' => 1,
    'sucursal_nombre' => 'Los Arcos',
    'fecha_apertura' => '2025-01-24 08:00:00',
    'fecha_cierre' => null,
    'monto_inicial' => 500.00,
    'monto_esperado' => null,
    'monto_real' => null,
    'diferencia' => null,
    'observaciones_cierre' => null,
    'estado' => 'abierta'
];

// Movimientos de caja (tabla movimientos_caja)
$movimientos = [
    [
        'id' => 1,
        'tipo_movimiento' => 'venta',
        'categoria' => 'ingreso',
        'concepto' => 'Venta V-2025-0105',
        'monto' => 3500.00,
        'usuario_nombre' => 'María Vendedor',
        'referencia_tipo' => 'venta',
        'referencia_id' => 105,
        'fecha_hora' => '2025-01-24 09:15:00'
    ],
    [
        'id' => 2,
        'tipo_movimiento' => 'venta',
        'categoria' => 'ingreso',
        'concepto' => 'Venta V-2025-0106',
        'monto' => 1200.00,
        'usuario_nombre' => 'María Vendedor',
        'referencia_tipo' => 'venta',
        'referencia_id' => 106,
        'fecha_hora' => '2025-01-24 10:30:00'
    ],
    [
        'id' => 3,
        'tipo_movimiento' => 'anticipo_trabajo',
        'categoria' => 'ingreso',
        'concepto' => 'Anticipo trabajo T-2025-006',
        'monto' => 200.00,
        'usuario_nombre' => 'María Vendedor',
        'referencia_tipo' => 'trabajo_taller',
        'referencia_id' => 6,
        'fecha_hora' => '2025-01-24 11:00:00'
    ],
    [
        'id' => 4,
        'tipo_movimiento' => 'abono_credito',
        'categoria' => 'ingreso',
        'concepto' => 'Abono a crédito #5',
        'monto' => 350.00,
        'usuario_nombre' => 'María Vendedor',
        'referencia_tipo' => 'credito',
        'referencia_id' => 5,
        'fecha_hora' => '2025-01-24 12:00:00'
    ],
    [
        'id' => 5,
        'tipo_movimiento' => 'gasto',
        'categoria' => 'egreso',
        'concepto' => 'Pago de luz',
        'monto' => 450.00,
        'usuario_nombre' => 'María Vendedor',
        'referencia_tipo' => null,
        'referencia_id' => null,
        'fecha_hora' => '2025-01-24 13:00:00'
    ],
    [
        'id' => 6,
        'tipo_movimiento' => 'venta',
        'categoria' => 'ingreso',
        'concepto' => 'Venta V-2025-0107',
        'monto' => 8500.00,
        'usuario_nombre' => 'María Vendedor',
        'referencia_tipo' => 'venta',
        'referencia_id' => 107,
        'fecha_hora' => '2025-01-24 14:30:00'
    ]
];

// Calcular totales
$total_ingresos = array_sum(array_map(fn($m) => $m['categoria'] == 'ingreso' ? $m['monto'] : 0, $movimientos));
$total_egresos = array_sum(array_map(fn($m) => $m['categoria'] == 'egreso' ? $m['monto'] : 0, $movimientos));
$efectivo_esperado = $caja['monto_inicial'] + $total_ingresos - $total_egresos;

// Título de página
$titulo_pagina = 'Detalles de Caja';

// Incluir header
include '../../includes/header.php';

// Incluir navbar
include '../../includes/navbar.php';
?>

<!-- Contenido Principal -->
<div class="container-fluid main-content">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="<?php echo BASE_URL; ?>dashboard.php">
                    <i class="bi bi-house"></i> Dashboard
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="lista.php">
                    <i class="bi bi-cash-stack"></i> Caja
                </a>
            </li>
            <li class="breadcrumb-item active">Caja #<?php echo $caja['id']; ?></li>
        </ol>
    </nav>

    <!-- Encabezado de la Caja -->
    <div class="card mb-4" style="border-left: 5px solid var(--color-dorado);">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-success text-white me-3" style="width: 80px; height: 80px; font-size: 32px;">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                        <div>
                            <h2 class="mb-1">Caja #<?php echo $caja['id']; ?></h2>
                            <div class="d-flex gap-3 text-muted">
                                <span>
                                    <i class="bi bi-person"></i>
                                    <?php echo $caja['usuario_nombre']; ?>
                                </span>
                                <span>
                                    <i class="bi bi-building"></i>
                                    <?php echo $caja['sucursal_nombre']; ?>
                                </span>
                                <span>
                                    <span class="badge <?php echo $caja['estado'] == 'abierta' ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?php echo ucfirst($caja['estado']); ?>
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <?php if ($caja['estado'] == 'abierta'): ?>
                    <a href="cerrar.php?id=<?php echo $caja['id']; ?>" class="btn btn-danger btn-lg">
                        <i class="bi bi-box-arrow-up"></i>
                        Cerrar Caja
                    </a>
                    <?php else: ?>
                    <button class="btn btn-secondary">
                        <i class="bi bi-printer"></i>
                        Imprimir Arqueo
                    </button>
                    <?php endif; ?>
                    <a href="lista.php" class="btn btn-primary">
                        <i class="bi bi-arrow-left"></i>
                        Volver
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Información de la Caja -->
        <div class="col-lg-4">
            <!-- Datos Generales -->
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-info-circle"></i>
                    Información General
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted d-block">Apertura:</small>
                        <strong><?php echo date('d/m/Y H:i', strtotime($caja['fecha_apertura'])); ?></strong>
                    </div>
                    <?php if ($caja['fecha_cierre']): ?>
                    <div class="mb-2">
                        <small class="text-muted d-block">Cierre:</small>
                        <strong><?php echo date('d/m/Y H:i', strtotime($caja['fecha_cierre'])); ?></strong>
                    </div>
                    <?php endif; ?>
                    <div class="mb-2">
                        <small class="text-muted d-block">Monto Inicial:</small>
                        <strong>Q <?php echo number_format($caja['monto_inicial'], 2); ?></strong>
                    </div>
                    <div class="mb-0">
                        <small class="text-muted d-block">Movimientos:</small>
                        <strong><?php echo count($movimientos); ?> registros</strong>
                    </div>
                </div>
            </div>

            <!-- Resumen de Montos -->
            <div class="card mb-3">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-calculator"></i>
                    Resumen de Montos
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Monto Inicial:</span>
                            <span class="fw-bold">Q <?php echo number_format($caja['monto_inicial'], 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-1 text-success">
                            <span>+ Ingresos:</span>
                            <span class="fw-bold">Q <?php echo number_format($total_ingresos, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-1 text-danger">
                            <span>- Egresos:</span>
                            <span class="fw-bold">Q <?php echo number_format($total_egresos, 2); ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <h5 class="mb-0">Efectivo Esperado:</h5>
                            <h5 class="mb-0 text-success">Q <?php echo number_format($efectivo_esperado, 2); ?></h5>
                        </div>
                    </div>

                    <?php if ($caja['estado'] == 'cerrada'): ?>
                    <hr>
                    <div class="mb-2">
                        <small class="text-muted d-block">Efectivo Real:</small>
                        <strong>Q <?php echo number_format($caja['monto_real'], 2); ?></strong>
                    </div>
                    <div>
                        <small class="text-muted d-block">Diferencia:</small>
                        <?php if ($caja['diferencia'] == 0): ?>
                            <span class="badge bg-success">Q 0.00 - Cuadrada</span>
                        <?php elseif ($caja['diferencia'] < 0): ?>
                            <span class="badge bg-danger">-Q <?php echo number_format(abs($caja['diferencia']), 2); ?> - Faltante</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">+Q <?php echo number_format($caja['diferencia'], 2); ?> - Sobrante</span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Desglose por Tipo -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <i class="bi bi-pie-chart"></i>
                    Desglose por Tipo
                </div>
                <div class="card-body">
                    <?php
                    $tipos_ingresos = [];
                    foreach ($movimientos as $mov) {
                        if ($mov['categoria'] == 'ingreso') {
                            $tipo = $mov['tipo_movimiento'];
                            if (!isset($tipos_ingresos[$tipo])) {
                                $tipos_ingresos[$tipo] = 0;
                            }
                            $tipos_ingresos[$tipo] += $mov['monto'];
                        }
                    }
                    
                    foreach ($tipos_ingresos as $tipo => $monto):
                    ?>
                    <div class="d-flex justify-content-between mb-2">
                        <small><?php echo ucfirst(str_replace('_', ' ', $tipo)); ?>:</small>
                        <strong class="text-success">Q <?php echo number_format($monto, 2); ?></strong>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Movimientos de Caja -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-list-ul"></i>
                    Movimientos de Caja (<?php echo count($movimientos); ?>)
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Fecha/Hora</th>
                                <th>Tipo</th>
                                <th>Concepto</th>
                                <th>Usuario</th>
                                <th class="text-end">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($movimientos as $mov): ?>
                            <tr>
                                <td>
                                    <small><?php echo date('d/m/Y H:i', strtotime($mov['fecha_hora'])); ?></small>
                                </td>
                                <td>
                                    <?php
                                    $badge_class = $mov['categoria'] == 'ingreso' ? 'bg-success' : 'bg-danger';
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $mov['tipo_movimiento'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo $mov['concepto']; ?>
                                    <?php if ($mov['referencia_tipo']): ?>
                                        <br><small class="text-muted">Ref: <?php echo $mov['referencia_tipo']; ?> #<?php echo $mov['referencia_id']; ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small class="text-muted"><?php echo $mov['usuario_nombre']; ?></small>
                                </td>
                                <td class="text-end">
                                    <?php if ($mov['categoria'] == 'ingreso'): ?>
                                        <span class="text-success fw-bold">+Q <?php echo number_format($mov['monto'], 2); ?></span>
                                    <?php else: ?>
                                        <span class="text-danger fw-bold">-Q <?php echo number_format($mov['monto'], 2); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="4" class="text-end fw-bold">Total Ingresos:</td>
                                <td class="text-end fw-bold text-success">
                                    +Q <?php echo number_format($total_ingresos, 2); ?>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end fw-bold">Total Egresos:</td>
                                <td class="text-end fw-bold text-danger">
                                    -Q <?php echo number_format($total_egresos, 2); ?>
                                </td>
                            </tr>
                            <tr class="table-success">
                                <td colspan="4" class="text-end fw-bold">SALDO:</td>
                                <td class="text-end fw-bold text-success" style="font-size: 1.2em;">
                                    Q <?php echo number_format($total_ingresos - $total_egresos, 2); ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Incluir footer
include '../../includes/footer.php';
?>