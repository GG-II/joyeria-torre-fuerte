<?php
// ================================================
// MÓDULO PROVEEDORES - VER DETALLES
// ================================================

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

// Verificar autenticación
requiere_autenticacion();

// Obtener ID del proveedor
$proveedor_id = $_GET['id'] ?? 1;

// Datos dummy del proveedor (CAMPOS REALES)
$proveedor = [
    'id' => $proveedor_id,
    'nombre' => 'Juan Pérez',
    'empresa' => 'Oro Fino Guatemala',
    'contacto' => 'Juan Pérez - Gerente de Ventas',
    'telefono' => '2234-5678',
    'email' => 'ventas@orofino.gt',
    'direccion' => 'Zona 10, Ciudad de Guatemala',
    'productos_suministra' => 'Oro 18K, Oro 14K, Cadenas de oro, Aretes de oro',
    'activo' => 1,
    'fecha_creacion' => '2024-01-15 10:00:00'
];

// Historial de compras (dummy - relación con productos)
$compras = [
    [
        'id' => 1,
        'fecha' => '2025-01-15',
        'productos' => 'Oro 18K - 500g',
        'total' => 15000.00,
        'usuario_nombre' => 'Carlos Admin'
    ],
    [
        'id' => 2,
        'fecha' => '2024-12-20',
        'productos' => 'Cadenas de oro - 10 unidades',
        'total' => 8500.00,
        'usuario_nombre' => 'María Gerente'
    ],
    [
        'id' => 3,
        'fecha' => '2024-11-10',
        'productos' => 'Oro 14K - 300g',
        'total' => 7200.00,
        'usuario_nombre' => 'Carlos Admin'
    ]
];

// Título de página
$titulo_pagina = 'Detalles del Proveedor';

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
                    <i class="bi bi-truck"></i> Proveedores
                </a>
            </li>
            <li class="breadcrumb-item active"><?php echo $proveedor['nombre']; ?></li>
        </ol>
    </nav>

    <!-- Encabezado del Proveedor -->
    <div class="card mb-4" style="border-left: 5px solid var(--color-dorado);">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-primary text-white me-3" style="width: 80px; height: 80px; font-size: 32px;">
                            <i class="bi bi-truck"></i>
                        </div>
                        <div>
                            <h2 class="mb-1"><?php echo $proveedor['nombre']; ?></h2>
                            <?php if ($proveedor['empresa']): ?>
                                <h5 class="text-muted mb-2"><?php echo $proveedor['empresa']; ?></h5>
                            <?php endif; ?>
                            <div class="d-flex gap-3 text-muted">
                                <?php if ($proveedor['telefono']): ?>
                                <span>
                                    <i class="bi bi-phone"></i>
                                    <?php echo $proveedor['telefono']; ?>
                                </span>
                                <?php endif; ?>
                                <?php if ($proveedor['email']): ?>
                                <span>
                                    <i class="bi bi-envelope"></i>
                                    <?php echo $proveedor['email']; ?>
                                </span>
                                <?php endif; ?>
                                <span>
                                    <?php if ($proveedor['activo']): ?>
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
                    <?php if (tiene_permiso('proveedores', 'editar')): ?>
                    <a href="editar.php?id=<?php echo $proveedor['id']; ?>" class="btn btn-warning">
                        <i class="bi bi-pencil"></i>
                        Editar
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
        <!-- Información del Proveedor -->
        <div class="col-lg-4">
            <!-- Datos del Proveedor -->
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-info-circle"></i>
                    Información del Proveedor
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Nombre:</small>
                        <strong><?php echo $proveedor['nombre']; ?></strong>
                    </div>
                    <?php if ($proveedor['empresa']): ?>
                    <div class="mb-3">
                        <small class="text-muted d-block">Empresa:</small>
                        <strong><?php echo $proveedor['empresa']; ?></strong>
                    </div>
                    <?php endif; ?>
                    <?php if ($proveedor['contacto']): ?>
                    <div class="mb-3">
                        <small class="text-muted d-block">Contacto:</small>
                        <strong><?php echo $proveedor['contacto']; ?></strong>
                    </div>
                    <?php endif; ?>
                    <hr>
                    <?php if ($proveedor['telefono']): ?>
                    <div class="mb-2">
                        <small class="text-muted d-block">Teléfono:</small>
                        <strong><?php echo $proveedor['telefono']; ?></strong>
                    </div>
                    <?php endif; ?>
                    <?php if ($proveedor['email']): ?>
                    <div class="mb-2">
                        <small class="text-muted d-block">Email:</small>
                        <strong><?php echo $proveedor['email']; ?></strong>
                    </div>
                    <?php endif; ?>
                    <?php if ($proveedor['direccion']): ?>
                    <div class="mb-2">
                        <small class="text-muted d-block">Dirección:</small>
                        <strong><?php echo $proveedor['direccion']; ?></strong>
                    </div>
                    <?php endif; ?>
                    <hr>
                    <div class="mb-0">
                        <small class="text-muted d-block">Fecha de Registro:</small>
                        <strong><?php echo date('d/m/Y H:i', strtotime($proveedor['fecha_creacion'])); ?></strong>
                    </div>
                </div>
            </div>

            <!-- Productos que Suministra -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-box-seam"></i>
                    Productos que Suministra
                </div>
                <div class="card-body">
                    <?php if ($proveedor['productos_suministra']): ?>
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($proveedor['productos_suministra'])); ?></p>
                    <?php else: ?>
                        <p class="text-muted mb-0">No especificado</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Historial y Estadísticas -->
        <div class="col-lg-8">
            <!-- Estadísticas Rápidas -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="stat-card azul">
                        <div class="stat-icon">
                            <i class="bi bi-cart"></i>
                        </div>
                        <div class="stat-value"><?php echo count($compras); ?></div>
                        <div class="stat-label">Compras Realizadas</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card dorado">
                        <div class="stat-icon">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                        <div class="stat-value">
                            Q <?php echo number_format(array_sum(array_column($compras, 'total')), 0); ?>
                        </div>
                        <div class="stat-label">Total Comprado</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card verde">
                        <div class="stat-icon">
                            <i class="bi bi-calculator"></i>
                        </div>
                        <div class="stat-value">
                            Q <?php echo count($compras) > 0 ? number_format(array_sum(array_column($compras, 'total')) / count($compras), 0) : 0; ?>
                        </div>
                        <div class="stat-label">Promedio por Compra</div>
                    </div>
                </div>
            </div>

            <!-- Historial de Compras -->
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-clock-history"></i>
                    Historial de Compras
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Fecha</th>
                                <th>Productos</th>
                                <th>Total</th>
                                <th>Registrado por</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($compras) > 0): ?>
                                <?php foreach ($compras as $compra): ?>
                                <tr>
                                    <td class="fw-bold"><?php echo $compra['id']; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($compra['fecha'])); ?></td>
                                    <td><?php echo $compra['productos']; ?></td>
                                    <td class="fw-bold text-success">
                                        Q <?php echo number_format($compra['total'], 2); ?>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?php echo $compra['usuario_nombre']; ?></small>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox" style="font-size: 48px;"></i>
                                        <p class="mt-2">No hay compras registradas</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <?php if (count($compras) > 0): ?>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="3" class="text-end fw-bold">TOTAL:</td>
                                <td class="fw-bold text-success">
                                    Q <?php echo number_format(array_sum(array_column($compras, 'total')), 2); ?>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
                <div class="card-footer">
                    <small class="text-muted">
                        Mostrando últimas <?php echo count($compras); ?> compras
                    </small>
                </div>
            </div>

            <!-- Acciones -->
            <?php if (tiene_permiso('proveedores', 'editar')): ?>
            <div class="card mt-3">
                <div class="card-header">
                    <i class="bi bi-gear"></i>
                    Acciones Disponibles
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <a href="editar.php?id=<?php echo $proveedor['id']; ?>" class="btn btn-warning w-100">
                                <i class="bi bi-pencil"></i>
                                Editar Información
                            </a>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-info w-100">
                                <i class="bi bi-printer"></i>
                                Imprimir Detalles
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-danger w-100">
                                <i class="bi bi-x-circle"></i>
                                Desactivar Proveedor
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