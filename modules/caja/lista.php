<?php
// ================================================
// MÓDULO CAJA - LISTA DE CAJAS
// ================================================

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

// Verificar autenticación y permisos
requiere_autenticacion();
requiere_rol(['administrador', 'dueño', 'cajero']);

// Título de página
$titulo_pagina = 'Control de Caja';

// Incluir header
include '../../includes/header.php';

// Incluir navbar
include '../../includes/navbar.php';

// Datos del usuario actual
$usuario_actual = [
    'id' => $_SESSION['usuario_id'],
    'nombre' => $_SESSION['usuario_nombre'],
    'sucursal_id' => $_SESSION['usuario_sucursal_id'],
    'sucursal_nombre' => $_SESSION['usuario_sucursal_nombre']
];

// Verificar si hay caja abierta
$caja_abierta = [
    'id' => 25,
    'fecha_apertura' => '2025-01-24 08:00:00',
    'monto_inicial' => 500.00,
    'ingresos_total' => 12350.00,
    'egresos_total' => 850.00,
    'efectivo_esperado' => 11500.00
];

// Historial de cajas (CAMPOS REALES)
$cajas = [
    [
        'id' => 24,
        'usuario_id' => 2,
        'usuario_nombre' => 'María Vendedor',
        'sucursal_id' => 1,
        'sucursal_nombre' => 'Los Arcos',
        'fecha_apertura' => '2025-01-23 08:00:00',
        'fecha_cierre' => '2025-01-23 18:30:00',
        'monto_inicial' => 500.00,
        'monto_esperado' => 10850.00,
        'monto_real' => 10820.00,
        'diferencia' => -30.00,
        'observaciones_cierre' => 'Faltante menor en billetes de Q5',
        'estado' => 'cerrada',
        'total_ventas' => 15
    ],
    [
        'id' => 23,
        'usuario_id' => 1,
        'usuario_nombre' => 'Carlos Admin',
        'sucursal_id' => 2,
        'sucursal_nombre' => 'Chinaca Central',
        'fecha_apertura' => '2025-01-22 08:00:00',
        'fecha_cierre' => '2025-01-22 19:00:00',
        'monto_inicial' => 500.00,
        'monto_esperado' => 8520.00,
        'monto_real' => 8520.00,
        'diferencia' => 0.00,
        'observaciones_cierre' => null,
        'estado' => 'cerrada',
        'total_ventas' => 12
    ],
    [
        'id' => 22,
        'usuario_id' => 2,
        'usuario_nombre' => 'María Vendedor',
        'sucursal_id' => 1,
        'sucursal_nombre' => 'Los Arcos',
        'fecha_apertura' => '2025-01-22 08:00:00',
        'fecha_cierre' => '2025-01-22 18:15:00',
        'monto_inicial' => 500.00,
        'monto_esperado' => 12100.00,
        'monto_real' => 12150.00,
        'diferencia' => 50.00,
        'observaciones_cierre' => 'Sobrante - cliente dejó propina',
        'estado' => 'cerrada',
        'total_ventas' => 18
    ]
];
?>

<!-- Contenido Principal -->
<div class="container-fluid main-content">
    <!-- Encabezado de Página -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1>
                    <i class="bi bi-cash-stack"></i>
                    Control de Caja
                </h1>
                <p class="text-muted">Gestión de apertura, cierre y movimientos de caja</p>
            </div>
            <div class="col-md-6 text-end">
                <?php if (!$caja_abierta): ?>
                    <a href="abrir.php" class="btn btn-success btn-lg">
                        <i class="bi bi-box-arrow-in-down"></i>
                        Abrir Caja
                    </a>
                <?php else: ?>
                    <a href="ver.php?id=<?php echo $caja_abierta['id']; ?>" class="btn btn-info btn-lg">
                        <i class="bi bi-eye"></i>
                        Ver Caja Actual
                    </a>
                    <a href="cerrar.php?id=<?php echo $caja_abierta['id']; ?>" class="btn btn-danger btn-lg">
                        <i class="bi bi-box-arrow-up"></i>
                        Cerrar Caja
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Caja Actual (si está abierta) -->
    <?php if ($caja_abierta): ?>
    <div class="alert alert-success border-start border-success border-5">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4 class="alert-heading mb-2">
                    <i class="bi bi-check-circle"></i>
                    Caja Abierta - Turno Actual
                </h4>
                <p class="mb-0">
                    <strong>Apertura:</strong> <?php echo date('d/m/Y H:i', strtotime($caja_abierta['fecha_apertura'])); ?> | 
                    <strong>Monto Inicial:</strong> Q <?php echo number_format($caja_abierta['monto_inicial'], 2); ?> | 
                    <strong>Usuario:</strong> <?php echo $usuario_actual['nombre']; ?> | 
                    <strong>Sucursal:</strong> <?php echo $usuario_actual['sucursal_nombre']; ?>
                </p>
            </div>
            <div class="col-md-4 text-end">
                <h3 class="mb-0 text-success">
                    Efectivo: Q <?php echo number_format($caja_abierta['efectivo_esperado'], 2); ?>
                </h3>
                <small class="text-muted">
                    (<?php echo number_format($caja_abierta['ingresos_total'], 2); ?> ingresos - 
                    <?php echo number_format($caja_abierta['egresos_total'], 2); ?> egresos)
                </small>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Resumen de Cajas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card verde">
                <div class="stat-icon">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-value">
                    <?php echo count(array_filter($cajas, fn($c) => $c['estado'] == 'cerrada' && $c['diferencia'] == 0)); ?>
                </div>
                <div class="stat-label">Cajas Cuadradas</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card rojo">
                <div class="stat-icon">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div class="stat-value">
                    <?php echo count(array_filter($cajas, fn($c) => $c['diferencia'] < 0)); ?>
                </div>
                <div class="stat-label">Con Faltante</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card amarillo">
                <div class="stat-icon">
                    <i class="bi bi-plus-circle"></i>
                </div>
                <div class="stat-value">
                    <?php echo count(array_filter($cajas, fn($c) => $c['diferencia'] > 0)); ?>
                </div>
                <div class="stat-label">Con Sobrante</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card dorado">
                <div class="stat-icon">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div class="stat-value">
                    Q <?php echo number_format(array_sum(array_column($cajas, 'monto_real')), 0); ?>
                </div>
                <div class="stat-label">Total Recaudado</div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Fecha Desde</label>
                    <input type="date" class="form-control" id="fechaDesde">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha Hasta</label>
                    <input type="date" class="form-control" id="fechaHasta">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sucursal</label>
                    <select class="form-select" id="filterSucursal">
                        <option value="">Todas</option>
                        <option value="1">Los Arcos</option>
                        <option value="2">Chinaca Central</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Usuario</label>
                    <select class="form-select" id="filterUsuario">
                        <option value="">Todos</option>
                        <option value="1">Carlos Admin</option>
                        <option value="2">María Vendedor</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button class="btn btn-secondary w-100">
                        <i class="bi bi-funnel"></i>
                        Filtrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Historial de Cajas -->
    <div class="card">
        <div class="card-header">
            <i class="bi bi-table"></i>
            Historial de Cajas (<?php echo count($cajas); ?>)
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Apertura</th>
                            <th>Cierre</th>
                            <th>Usuario</th>
                            <th>Sucursal</th>
                            <th>Inicial</th>
                            <th>Esperado</th>
                            <th>Real</th>
                            <th>Diferencia</th>
                            <th>Ventas</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cajas as $caja): ?>
                        <tr>
                            <td class="fw-bold"><?php echo $caja['id']; ?></td>
                            <td>
                                <div><?php echo date('d/m/Y', strtotime($caja['fecha_apertura'])); ?></div>
                                <small class="text-muted"><?php echo date('H:i', strtotime($caja['fecha_apertura'])); ?></small>
                            </td>
                            <td>
                                <?php if ($caja['fecha_cierre']): ?>
                                    <div><?php echo date('d/m/Y', strtotime($caja['fecha_cierre'])); ?></div>
                                    <small class="text-muted"><?php echo date('H:i', strtotime($caja['fecha_cierre'])); ?></small>
                                <?php else: ?>
                                    <span class="badge bg-warning">Abierta</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $caja['usuario_nombre']; ?></td>
                            <td><?php echo $caja['sucursal_nombre']; ?></td>
                            <td>Q <?php echo number_format($caja['monto_inicial'], 2); ?></td>
                            <td>Q <?php echo number_format($caja['monto_esperado'], 2); ?></td>
                            <td class="fw-bold">Q <?php echo number_format($caja['monto_real'], 2); ?></td>
                            <td>
                                <?php if ($caja['diferencia'] == 0): ?>
                                    <span class="badge bg-success">Q 0.00</span>
                                <?php elseif ($caja['diferencia'] < 0): ?>
                                    <span class="badge bg-danger">-Q <?php echo number_format(abs($caja['diferencia']), 2); ?></span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">+Q <?php echo number_format($caja['diferencia'], 2); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-info"><?php echo $caja['total_ventas']; ?></span>
                            </td>
                            <td class="text-center">
                                <a href="ver.php?id=<?php echo $caja['id']; ?>" 
                                   class="btn btn-sm btn-info"
                                   data-bs-toggle="tooltip" 
                                   title="Ver detalles">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <button class="btn btn-sm btn-secondary" 
                                        data-bs-toggle="tooltip" 
                                        title="Imprimir arqueo">
                                    <i class="bi bi-printer"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="5" class="text-end fw-bold">TOTALES:</td>
                            <td class="fw-bold">
                                Q <?php echo number_format(array_sum(array_column($cajas, 'monto_inicial')), 2); ?>
                            </td>
                            <td class="fw-bold">
                                Q <?php echo number_format(array_sum(array_column($cajas, 'monto_esperado')), 2); ?>
                            </td>
                            <td class="fw-bold text-success">
                                Q <?php echo number_format(array_sum(array_column($cajas, 'monto_real')), 2); ?>
                            </td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <small class="text-muted">
                Mostrando <?php echo count($cajas); ?> cajas
            </small>
        </div>
    </div>
</div>

<?php
// Incluir footer
include '../../includes/footer.php';
?>