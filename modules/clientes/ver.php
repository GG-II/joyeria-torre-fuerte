<?php
// ================================================
// MÓDULO CLIENTES - VER FICHA (CORREGIDO)
// ================================================

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

// Verificar autenticación y permisos
requiere_autenticacion();

// Obtener ID del cliente (dummy)
$cliente_id = $_GET['id'] ?? 1;

// Datos dummy del cliente (CAMPOS REALES DEL SCHEMA)
$cliente = [
    'id' => $cliente_id,
    'nombre' => 'María García López',
    'nit' => '12345678-9',
    'telefono' => '5512-3456',
    'email' => 'maria.garcia@email.com',
    'direccion' => 'Zona 10, Ciudad de Guatemala',
    'tipo_cliente' => 'publico',
    'tipo_mercaderias' => 'ambas',
    'limite_credito' => 5000.00,
    'plazo_credito_dias' => 30,
    'saldo_creditos' => 0.00,  // Suma de creditos_clientes.saldo_pendiente
    'activo' => 1,
    'fecha_creacion' => '2024-01-15'
];

// Historial de compras (dummy) - tabla ventas
$historial_compras = [
    [
        'id' => 105,
        'numero_venta' => 'V-2025-0105',
        'fecha' => '2025-01-20',
        'total' => 3500.00,
        'tipo_venta' => 'normal',
        'estado' => 'completada',
        'forma_pago' => 'Efectivo'
    ],
    [
        'id' => 98,
        'numero_venta' => 'V-2024-0098',
        'fecha' => '2024-12-15',
        'total' => 1200.00,
        'tipo_venta' => 'normal',
        'estado' => 'completada',
        'forma_pago' => 'Tarjeta'
    ],
    [
        'id' => 87,
        'numero_venta' => 'V-2024-0087',
        'fecha' => '2024-11-03',
        'total' => 2800.00,
        'tipo_venta' => 'credito',
        'estado' => 'completada',
        'forma_pago' => 'Crédito'
    ]
];

// Créditos activos (dummy) - tabla creditos_clientes
$creditos = [
    [
        'id' => 12,
        'venta_id' => 87,
        'monto_total' => 2800.00,
        'saldo_pendiente' => 0.00,
        'cuota_semanal' => 400.00,
        'numero_cuotas' => 7,
        'cuotas_pagadas' => 7,
        'estado' => 'liquidado',
        'fecha_inicio' => '2024-11-03',
        'fecha_liquidacion' => '2024-12-22'
    ]
];

// Título de página
$titulo_pagina = 'Ficha del Cliente';

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
                    <i class="bi bi-people"></i> Clientes
                </a>
            </li>
            <li class="breadcrumb-item active"><?php echo $cliente['nombre']; ?></li>
        </ol>
    </nav>

    <!-- Encabezado con información del cliente -->
    <div class="card mb-4" style="border-left: 5px solid var(--color-dorado);">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center">
                        <div class="user-avatar bg-primary text-white me-3" style="width: 80px; height: 80px; font-size: 32px;">
                            <?php echo strtoupper(substr($cliente['nombre'], 0, 1)); ?>
                        </div>
                        <div>
                            <h2 class="mb-1"><?php echo $cliente['nombre']; ?></h2>
                            <div class="d-flex gap-3 text-muted">
                                <span>
                                    <i class="bi bi-card-text"></i>
                                    NIT: <?php echo $cliente['nit']; ?>
                                </span>
                                <span>
                                    <i class="bi bi-telephone"></i>
                                    <?php echo $cliente['telefono']; ?>
                                </span>
                                <span>
                                    <?php
                                    $badges = [
                                        'publico' => 'bg-info',
                                        'mayorista' => 'bg-dorado'
                                    ];
                                    $badge_class = $badges[$cliente['tipo_cliente']] ?? 'bg-secondary';
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>">
                                        <?php echo ucfirst($cliente['tipo_cliente']); ?>
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <?php if (tiene_permiso('clientes', 'editar')): ?>
                    <a href="editar.php?id=<?php echo $cliente['id']; ?>" class="btn btn-warning">
                        <i class="bi bi-pencil"></i>
                        Editar Cliente
                    </a>
                    <?php endif; ?>
                    <a href="lista.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i>
                        Volver
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Información del Cliente -->
        <div class="col-lg-4">
            <!-- Datos Personales -->
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-person-badge"></i>
                    Datos Personales
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Correo Electrónico</small>
                        <strong><?php echo $cliente['email'] ?: 'No registrado'; ?></strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Dirección</small>
                        <strong><?php echo $cliente['direccion']; ?></strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Tipo de Mercaderías</small>
                        <?php
                        $icons = [
                            'oro' => '🟡',
                            'plata' => '⚪',
                            'ambas' => '🟡⚪'
                        ];
                        ?>
                        <strong>
                            <?php echo $icons[$cliente['tipo_mercaderias']]; ?>
                            <?php echo ucfirst($cliente['tipo_mercaderias']); ?>
                        </strong>
                    </div>
                    <div class="mb-0">
                        <small class="text-muted d-block">Cliente desde</small>
                        <strong><?php echo date('d/m/Y', strtotime($cliente['fecha_creacion'])); ?></strong>
                    </div>
                </div>
            </div>

            <!-- Crédito -->
            <div class="card mb-3">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-cash-coin"></i>
                    Información de Crédito
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Límite de Crédito</small>
                        <h4 class="mb-0 text-primary">
                            Q <?php echo number_format($cliente['limite_credito'], 2); ?>
                        </h4>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Saldo Pendiente</small>
                        <h4 class="mb-0 <?php echo $cliente['saldo_creditos'] > 0 ? 'text-danger' : 'text-success'; ?>">
                            Q <?php echo number_format($cliente['saldo_creditos'], 2); ?>
                        </h4>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Crédito Disponible</small>
                        <h4 class="mb-0 text-success">
                            Q <?php echo number_format($cliente['limite_credito'] - $cliente['saldo_creditos'], 2); ?>
                        </h4>
                    </div>
                    <div>
                        <small class="text-muted d-block">Plazo de Crédito</small>
                        <strong>
                            <?php echo $cliente['plazo_credito_dias'] ? $cliente['plazo_credito_dias'] . ' días' : 'No aplica'; ?>
                        </strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Historial y Estadísticas -->
        <div class="col-lg-8">
            <!-- Estadísticas Rápidas -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="stat-card dorado">
                        <div class="stat-icon">
                            <i class="bi bi-cart-check"></i>
                        </div>
                        <div class="stat-value"><?php echo count($historial_compras); ?></div>
                        <div class="stat-label">Total Compras</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card verde">
                        <div class="stat-icon">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                        <div class="stat-value">
                            Q <?php echo number_format(array_sum(array_column($historial_compras, 'total')), 0); ?>
                        </div>
                        <div class="stat-label">Total Gastado</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card azul">
                        <div class="stat-icon">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <div class="stat-value">
                            Q <?php echo count($historial_compras) > 0 ? number_format(array_sum(array_column($historial_compras, 'total')) / count($historial_compras), 0) : 0; ?>
                        </div>
                        <div class="stat-label">Promedio por Compra</div>
                    </div>
                </div>
            </div>

            <!-- Pestañas -->
            <ul class="nav nav-tabs mb-3" id="clienteTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="compras-tab" data-bs-toggle="tab" 
                            data-bs-target="#compras" type="button">
                        <i class="bi bi-cart"></i>
                        Historial de Compras
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="creditos-tab" data-bs-toggle="tab" 
                            data-bs-target="#creditos" type="button">
                        <i class="bi bi-credit-card"></i>
                        Créditos
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="clienteTabsContent">
                <!-- Historial de Compras -->
                <div class="tab-pane fade show active" id="compras">
                    <div class="card">
                        <div class="card-header">
                            <i class="bi bi-clock-history"></i>
                            Últimas Compras
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Número</th>
                                        <th>Fecha</th>
                                        <th>Total</th>
                                        <th>Tipo</th>
                                        <th>Pago</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($historial_compras as $venta): ?>
                                    <tr>
                                        <td class="fw-bold"><?php echo $venta['numero_venta']; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($venta['fecha'])); ?></td>
                                        <td class="fw-bold text-success">
                                            Q <?php echo number_format($venta['total'], 2); ?>
                                        </td>
                                        <td>
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
                                        </td>
                                        <td><?php echo $venta['forma_pago']; ?></td>
                                        <td>
                                            <span class="badge bg-success">
                                                <?php echo ucfirst($venta['estado']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="../../modules/ventas/detalle.php?id=<?php echo $venta['id']; ?>" 
                                               class="btn btn-sm btn-info">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="table-light fw-bold">
                                        <td colspan="2">TOTAL</td>
                                        <td class="text-success">
                                            Q <?php echo number_format(array_sum(array_column($historial_compras, 'total')), 2); ?>
                                        </td>
                                        <td colspan="4"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Créditos -->
                <div class="tab-pane fade" id="creditos">
                    <div class="card">
                        <div class="card-header">
                            <i class="bi bi-credit-card"></i>
                            Historial de Créditos
                        </div>
                        <?php if (count($creditos) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Crédito #</th>
                                        <th>Venta</th>
                                        <th>Monto Total</th>
                                        <th>Saldo</th>
                                        <th>Cuotas</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($creditos as $credito): ?>
                                    <tr>
                                        <td class="fw-bold">#<?php echo $credito['id']; ?></td>
                                        <td>V-<?php echo $credito['venta_id']; ?></td>
                                        <td>Q <?php echo number_format($credito['monto_total'], 2); ?></td>
                                        <td class="<?php echo $credito['saldo_pendiente'] > 0 ? 'text-danger' : 'text-success'; ?>">
                                            Q <?php echo number_format($credito['saldo_pendiente'], 2); ?>
                                        </td>
                                        <td>
                                            <?php echo $credito['cuotas_pagadas']; ?>/<?php echo $credito['numero_cuotas']; ?>
                                        </td>
                                        <td>
                                            <?php if ($credito['estado'] == 'liquidado'): ?>
                                                <span class="badge bg-success">Liquidado</span>
                                            <?php elseif ($credito['estado'] == 'activo'): ?>
                                                <span class="badge bg-warning">Activo</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Vencido</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="card-body text-center py-5">
                            <i class="bi bi-inbox text-muted" style="font-size: 48px;"></i>
                            <p class="text-muted mt-3">No hay créditos registrados</p>
                        </div>
                        <?php endif; ?>
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