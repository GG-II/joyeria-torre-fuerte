<?php
// ================================================
// MÓDULO INVENTARIO - VER DETALLES
// ================================================

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

// Verificar autenticación
requiere_autenticacion();

// Obtener ID del producto (dummy)
$producto_id = $_GET['id'] ?? 1;

// Datos dummy del producto
$producto = [
    'id' => $producto_id,
    'codigo' => 'AN-001',
    'nombre' => 'Anillo de Oro 18K con Diamante',
    'descripcion' => 'Elegante anillo de oro 18K con diamante central de 0.5 quilates, diseño clásico',
    'categoria' => 'Anillos',
    'tipo' => 'Producto Terminado',
    'quilates' => 18,
    'peso_gramos' => 5.5,
    'material' => 'Oro',
    'costo' => 6000.00,
    'precio_venta' => 8500.00,
    'stock_los_arcos' => 3,
    'stock_chinaca' => 2,
    'stock_total' => 5,
    'stock_minimo' => 2,
    'activo' => 1,
    'fecha_creacion' => '2024-01-15'
];

// Historial de movimientos (dummy)
$movimientos = [
    [
        'id' => 45,
        'tipo' => 'entrada',
        'cantidad' => 5,
        'sucursal' => 'Los Arcos',
        'motivo' => 'Compra a proveedor',
        'usuario' => 'Carlos Admin',
        'fecha' => '2024-01-15 10:30:00'
    ],
    [
        'id' => 52,
        'tipo' => 'transferencia',
        'cantidad' => 2,
        'sucursal' => 'Chinaca Central',
        'motivo' => 'Transferencia Los Arcos → Chinaca',
        'usuario' => 'Carlos Admin',
        'fecha' => '2024-02-10 14:15:00'
    ],
    [
        'id' => 78,
        'tipo' => 'salida',
        'cantidad' => 1,
        'sucursal' => 'Los Arcos',
        'motivo' => 'Venta #105',
        'usuario' => 'María Vendedor',
        'fecha' => '2025-01-20 11:45:00'
    ]
];

// Título de página
$titulo_pagina = 'Detalles del Producto';

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
                    <i class="bi bi-box-seam"></i> Inventario
                </a>
            </li>
            <li class="breadcrumb-item active"><?php echo $producto['codigo']; ?></li>
        </ol>
    </nav>

    <!-- Encabezado del Producto -->
    <div class="card mb-4" style="border-left: 5px solid var(--color-dorado);">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-primary text-white me-3" style="width: 80px; height: 80px; font-size: 32px;">
                            <i class="bi bi-gem"></i>
                        </div>
                        <div>
                            <h2 class="mb-1"><?php echo $producto['nombre']; ?></h2>
                            <div class="d-flex gap-3 text-muted">
                                <span>
                                    <i class="bi bi-upc-scan"></i>
                                    <?php echo $producto['codigo']; ?>
                                </span>
                                <span>
                                    <i class="bi bi-folder"></i>
                                    <?php echo $producto['categoria']; ?>
                                </span>
                                <span>
                                    <?php if ($producto['activo']): ?>
                                        <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactivo</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <?php if (tiene_permiso('inventario', 'editar')): ?>
                    <a href="editar.php?id=<?php echo $producto['id']; ?>" class="btn btn-warning">
                        <i class="bi bi-pencil"></i>
                        Editar Producto
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
        <!-- Información del Producto -->
        <div class="col-lg-4">
            <!-- Datos del Producto -->
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-info-circle"></i>
                    Información del Producto
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Descripción</small>
                        <p class="mb-0"><?php echo $producto['descripcion']; ?></p>
                    </div>
                    <hr>
                    <div class="mb-2">
                        <small class="text-muted d-block">Material</small>
                        <strong><?php echo $producto['material']; ?></strong>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block">Quilates</small>
                        <strong><?php echo $producto['quilates']; ?>K</strong>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block">Peso</small>
                        <strong><?php echo $producto['peso_gramos']; ?> gramos</strong>
                    </div>
                    <div class="mb-0">
                        <small class="text-muted d-block">Fecha de Creación</small>
                        <strong><?php echo date('d/m/Y', strtotime($producto['fecha_creacion'])); ?></strong>
                    </div>
                </div>
            </div>

            <!-- Precios -->
            <div class="card mb-3">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-cash-coin"></i>
                    Precios y Costos
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Costo</small>
                        <h4 class="mb-0 text-danger">
                            Q <?php echo number_format($producto['costo'], 2); ?>
                        </h4>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Precio de Venta</small>
                        <h4 class="mb-0 text-success">
                            Q <?php echo number_format($producto['precio_venta'], 2); ?>
                        </h4>
                    </div>
                    <div>
                        <small class="text-muted d-block">Margen de Ganancia</small>
                        <h4 class="mb-0 text-primary">
                            <?php 
                            $margen = (($producto['precio_venta'] - $producto['costo']) / $producto['costo'] * 100);
                            echo number_format($margen, 2); 
                            ?>%
                        </h4>
                    </div>
                </div>
            </div>

            <!-- Stock por Sucursal -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <i class="bi bi-building"></i>
                    Stock por Sucursal
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span>Los Arcos</span>
                            <span class="badge bg-info"><?php echo $producto['stock_los_arcos']; ?></span>
                        </div>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar" role="progressbar" 
                                 style="width: <?php echo ($producto['stock_los_arcos'] / $producto['stock_total'] * 100); ?>%">
                                <?php echo number_format($producto['stock_los_arcos'] / $producto['stock_total'] * 100, 0); ?>%
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span>Chinaca Central</span>
                            <span class="badge bg-info"><?php echo $producto['stock_chinaca']; ?></span>
                        </div>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-warning" role="progressbar" 
                                 style="width: <?php echo ($producto['stock_chinaca'] / $producto['stock_total'] * 100); ?>%">
                                <?php echo number_format($producto['stock_chinaca'] / $producto['stock_total'] * 100, 0); ?>%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas e Historial -->
        <div class="col-lg-8">
            <!-- Estadísticas Rápidas -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stat-card azul">
                        <div class="stat-icon">
                            <i class="bi bi-boxes"></i>
                        </div>
                        <div class="stat-value"><?php echo $producto['stock_total']; ?></div>
                        <div class="stat-label">Stock Total</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card verde">
                        <div class="stat-icon">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                        <div class="stat-value">
                            Q <?php echo number_format($producto['costo'] * $producto['stock_total'], 0); ?>
                        </div>
                        <div class="stat-label">Valor Total</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card amarillo">
                        <div class="stat-icon">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <div class="stat-value"><?php echo $producto['stock_minimo']; ?></div>
                        <div class="stat-label">Stock Mínimo</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card dorado">
                        <div class="stat-icon">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <div class="stat-value">
                            Q <?php echo number_format($producto['precio_venta'] - $producto['costo'], 0); ?>
                        </div>
                        <div class="stat-label">Ganancia/Unidad</div>
                    </div>
                </div>
            </div>

            <!-- Historial de Movimientos -->
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-clock-history"></i>
                    Historial de Movimientos
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Cantidad</th>
                                <th>Sucursal</th>
                                <th>Motivo</th>
                                <th>Usuario</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($movimientos as $mov): ?>
                            <tr>
                                <td>
                                    <small><?php echo date('d/m/Y H:i', strtotime($mov['fecha'])); ?></small>
                                </td>
                                <td>
                                    <?php
                                    $badges_tipo = [
                                        'entrada' => 'bg-success',
                                        'salida' => 'bg-danger',
                                        'transferencia' => 'bg-warning',
                                        'ajuste' => 'bg-info'
                                    ];
                                    ?>
                                    <span class="badge <?php echo $badges_tipo[$mov['tipo']]; ?>">
                                        <?php echo ucfirst($mov['tipo']); ?>
                                    </span>
                                </td>
                                <td class="fw-bold">
                                    <?php if ($mov['tipo'] == 'entrada'): ?>
                                        <span class="text-success">+<?php echo $mov['cantidad']; ?></span>
                                    <?php elseif ($mov['tipo'] == 'salida'): ?>
                                        <span class="text-danger">-<?php echo $mov['cantidad']; ?></span>
                                    <?php else: ?>
                                        <?php echo $mov['cantidad']; ?>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $mov['sucursal']; ?></td>
                                <td><?php echo $mov['motivo']; ?></td>
                                <td>
                                    <small class="text-muted"><?php echo $mov['usuario']; ?></small>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <small class="text-muted">
                        Mostrando últimos <?php echo count($movimientos); ?> movimientos
                    </small>
                </div>
            </div>

            <!-- Acciones -->
            <?php if (tiene_permiso('inventario', 'editar')): ?>
            <div class="card mt-3">
                <div class="card-header">
                    <i class="bi bi-gear"></i>
                    Acciones Disponibles
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <a href="transferencias.php?producto_id=<?php echo $producto['id']; ?>" 
                               class="btn btn-warning w-100">
                                <i class="bi bi-arrow-left-right"></i>
                                Transferir Stock
                            </a>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-success w-100">
                                <i class="bi bi-plus-circle"></i>
                                Entrada de Stock
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-info w-100">
                                <i class="bi bi-arrow-repeat"></i>
                                Ajuste de Inventario
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Incluir footer
include '../../includes/footer.php';
?>