<?php
// ================================================
// MÓDULO VENTAS - VER DETALLES
// ================================================

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

// Verificar autenticación
requiere_autenticacion();

// Obtener ID de la venta
$venta_id = $_GET['id'] ?? 1;

// Datos dummy de la venta (CAMPOS REALES)
$venta = [
    'id' => $venta_id,
    'numero_venta' => 'V-2025-0105',
    'fecha' => '2025-01-23',
    'hora' => '14:30:00',
    'cliente_id' => 1,
    'cliente_nombre' => 'María García López',
    'cliente_nit' => '12345678-9',
    'cliente_telefono' => '5512-3456',
    'usuario_id' => 2,
    'usuario_nombre' => 'María Vendedor',
    'sucursal_id' => 1,
    'sucursal_nombre' => 'Los Arcos',
    'subtotal' => 3500.00,
    'descuento' => 0.00,
    'total' => 3500.00,
    'tipo_venta' => 'normal',
    'estado' => 'completada',
    'motivo_anulacion' => null,
    'fecha_creacion' => '2025-01-23 14:30:00'
];

// Detalle de productos (tabla detalle_ventas)
$detalle = [
    [
        'id' => 1,
        'producto_id' => 1,
        'producto_codigo' => 'AN-001',
        'producto_nombre' => 'Anillo de Oro 18K con Diamante',
        'cantidad' => 1,
        'precio_unitario' => 8500.00,
        'tipo_precio_aplicado' => 'publico',
        'subtotal' => 8500.00
    ],
    [
        'id' => 2,
        'producto_id' => 2,
        'producto_codigo' => 'AR-102',
        'producto_nombre' => 'Aretes de Plata con Perla',
        'cantidad' => 2,
        'precio_unitario' => 1200.00,
        'tipo_precio_aplicado' => 'publico',
        'subtotal' => 2400.00
    ]
];

// Formas de pago (tabla formas_pago_venta)
$formas_pago = [
    [
        'id' => 1,
        'forma_pago' => 'efectivo',
        'monto' => 3500.00,
        'referencia' => null
    ]
];

// Título de página
$titulo_pagina = 'Detalles de Venta';

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
                    <i class="bi bi-cart-check"></i> Ventas
                </a>
            </li>
            <li class="breadcrumb-item active"><?php echo $venta['numero_venta']; ?></li>
        </ol>
    </nav>

    <!-- Encabezado de la Venta -->
    <div class="card mb-4" style="border-left: 5px solid var(--color-dorado);">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-success text-white me-3" style="width: 80px; height: 80px; font-size: 32px;">
                            <i class="bi bi-receipt"></i>
                        </div>
                        <div>
                            <h2 class="mb-1"><?php echo $venta['numero_venta']; ?></h2>
                            <div class="d-flex gap-3 text-muted">
                                <span>
                                    <i class="bi bi-calendar"></i>
                                    <?php echo date('d/m/Y H:i', strtotime($venta['fecha'] . ' ' . $venta['hora'])); ?>
                                </span>
                                <span>
                                    <i class="bi bi-building"></i>
                                    <?php echo $venta['sucursal_nombre']; ?>
                                </span>
                                <span>
                                    <?php
                                    $badges_estado = [
                                        'completada' => 'bg-success',
                                        'apartada' => 'bg-warning',
                                        'anulada' => 'bg-danger'
                                    ];
                                    ?>
                                    <span class="badge <?php echo $badges_estado[$venta['estado']]; ?>">
                                        <?php echo ucfirst($venta['estado']); ?>
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <button class="btn btn-secondary">
                        <i class="bi bi-printer"></i>
                        Imprimir
                    </button>
                    <?php if (tiene_permiso('ventas', 'eliminar') && $venta['estado'] == 'completada'): ?>
                    <a href="anular.php?id=<?php echo $venta['id']; ?>" class="btn btn-danger">
                        <i class="bi bi-x-circle"></i>
                        Anular
                    </a>
                    <?php endif; ?>
                    <a href="lista.php" class="btn btn-primary">
                        <i class="bi bi-arrow-left"></i>
                        Volver
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php if ($venta['estado'] == 'anulada'): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle"></i>
        <strong>Venta Anulada:</strong> <?php echo $venta['motivo_anulacion']; ?>
    </div>
    <?php endif; ?>

    <div class="row">
        <!-- Detalles de la Venta -->
        <div class="col-lg-8">
            <!-- Productos (tabla detalle_ventas) -->
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-box-seam"></i>
                    Productos Vendidos
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Precio Unit.</th>
                                <th>Tipo Precio</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detalle as $item): ?>
                            <tr>
                                <td class="fw-bold"><?php echo $item['producto_codigo']; ?></td>
                                <td><?php echo $item['producto_nombre']; ?></td>
                                <td><?php echo $item['cantidad']; ?></td>
                                <td>Q <?php echo number_format($item['precio_unitario'], 2); ?></td>
                                <td>
                                    <span class="badge bg-info">
                                        <?php echo ucfirst($item['tipo_precio_aplicado']); ?>
                                    </span>
                                </td>
                                <td class="fw-bold">Q <?php echo number_format($item['subtotal'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="5" class="text-end fw-bold">Subtotal:</td>
                                <td class="fw-bold">Q <?php echo number_format($venta['subtotal'], 2); ?></td>
                            </tr>
                            <?php if ($venta['descuento'] > 0): ?>
                            <tr>
                                <td colspan="5" class="text-end fw-bold">Descuento:</td>
                                <td class="fw-bold text-danger">-Q <?php echo number_format($venta['descuento'], 2); ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr class="table-success">
                                <td colspan="5" class="text-end fw-bold">TOTAL:</td>
                                <td class="fw-bold text-success" style="font-size: 1.2em;">
                                    Q <?php echo number_format($venta['total'], 2); ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Formas de Pago (tabla formas_pago_venta) -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-wallet2"></i>
                    Formas de Pago
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Forma de Pago</th>
                                <th>Monto</th>
                                <th>Referencia</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($formas_pago as $pago): ?>
                            <tr>
                                <td>
                                    <?php
                                    $iconos_pago = [
                                        'efectivo' => 'bi-cash',
                                        'tarjeta_debito' => 'bi-credit-card',
                                        'tarjeta_credito' => 'bi-credit-card-2-front',
                                        'transferencia' => 'bi-bank',
                                        'cheque' => 'bi-file-earmark-check'
                                    ];
                                    ?>
                                    <i class="bi <?php echo $iconos_pago[$pago['forma_pago']]; ?>"></i>
                                    <?php echo ucfirst(str_replace('_', ' ', $pago['forma_pago'])); ?>
                                </td>
                                <td class="fw-bold">Q <?php echo number_format($pago['monto'], 2); ?></td>
                                <td>
                                    <?php echo $pago['referencia'] ? htmlspecialchars($pago['referencia']) : '-'; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td class="fw-bold">TOTAL PAGADO:</td>
                                <td class="fw-bold text-success">
                                    Q <?php echo number_format(array_sum(array_column($formas_pago, 'monto')), 2); ?>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Información Adicional -->
        <div class="col-lg-4">
            <!-- Datos del Cliente -->
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <i class="bi bi-person"></i>
                    Información del Cliente
                </div>
                <div class="card-body">
                    <?php if ($venta['cliente_id']): ?>
                        <div class="mb-2">
                            <small class="text-muted d-block">Nombre:</small>
                            <strong><?php echo $venta['cliente_nombre']; ?></strong>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block">NIT:</small>
                            <strong><?php echo $venta['cliente_nit']; ?></strong>
                        </div>
                        <div class="mb-0">
                            <small class="text-muted d-block">Teléfono:</small>
                            <strong><?php echo $venta['cliente_telefono']; ?></strong>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">Consumidor Final</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Información de la Venta -->
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-info-circle"></i>
                    Detalles de la Venta
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted d-block">Tipo de Venta:</small>
                        <?php
                        $badges_tipo = [
                            'normal' => 'bg-info',
                            'credito' => 'bg-warning',
                            'apartado' => 'bg-secondary'
                        ];
                        ?>
                        <span class="badge <?php echo $badges_tipo[$venta['tipo_venta']]; ?>">
                            <?php echo ucfirst($venta['tipo_venta']); ?>
                        </span>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block">Vendedor:</small>
                        <strong><?php echo $venta['usuario_nombre']; ?></strong>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block">Sucursal:</small>
                        <strong><?php echo $venta['sucursal_nombre']; ?></strong>
                    </div>
                    <div class="mb-0">
                        <small class="text-muted d-block">Fecha de Registro:</small>
                        <strong><?php echo date('d/m/Y H:i:s', strtotime($venta['fecha_creacion'])); ?></strong>
                    </div>
                </div>
            </div>

            <!-- Resumen Rápido -->
            <div class="card">
                <div class="card-header bg-dorado text-white">
                    <i class="bi bi-calculator"></i>
                    Resumen
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted d-block">Productos Vendidos:</small>
                        <h5 class="mb-0"><?php echo count($detalle); ?> productos</h5>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block">Unidades Totales:</small>
                        <h5 class="mb-0">
                            <?php echo array_sum(array_column($detalle, 'cantidad')); ?> unidades
                        </h5>
                    </div>
                    <div>
                        <small class="text-muted d-block">Total de la Venta:</small>
                        <h3 class="mb-0 text-success">
                            Q <?php echo number_format($venta['total'], 2); ?>
                        </h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Incluir footer
include '../../includes/footer.php';
?>