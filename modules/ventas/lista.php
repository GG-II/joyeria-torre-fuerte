<?php
// ================================================
// MÓDULO VENTAS - LISTA
// ================================================

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

// Verificar autenticación y permisos
requiere_autenticacion();

// Título de página
$titulo_pagina = 'Ventas';

// Incluir header
include '../../includes/header.php';

// Incluir navbar
include '../../includes/navbar.php';

// Datos dummy de ventas (CAMPOS REALES DEL SCHEMA)
$ventas = [
    [
        'id' => 105,
        'numero_venta' => 'V-2025-0105',
        'fecha' => '2025-01-23',
        'hora' => '14:30:00',
        'cliente_id' => 1,
        'cliente_nombre' => 'María García López',
        'usuario_id' => 2,
        'usuario_nombre' => 'María Vendedor',
        'sucursal_id' => 1,
        'sucursal_nombre' => 'Los Arcos',
        'subtotal' => 3500.00,
        'descuento' => 0.00,
        'total' => 3500.00,
        'tipo_venta' => 'normal',
        'estado' => 'completada',
        'forma_pago_principal' => 'efectivo',
        'cantidad_productos' => 2
    ],
    [
        'id' => 104,
        'numero_venta' => 'V-2025-0104',
        'fecha' => '2025-01-23',
        'hora' => '11:15:00',
        'cliente_id' => null,
        'cliente_nombre' => 'Consumidor Final',
        'usuario_id' => 2,
        'usuario_nombre' => 'María Vendedor',
        'sucursal_id' => 1,
        'sucursal_nombre' => 'Los Arcos',
        'subtotal' => 950.00,
        'descuento' => 50.00,
        'total' => 900.00,
        'tipo_venta' => 'normal',
        'estado' => 'completada',
        'forma_pago_principal' => 'tarjeta_credito',
        'cantidad_productos' => 1
    ],
    [
        'id' => 103,
        'numero_venta' => 'V-2025-0103',
        'fecha' => '2025-01-22',
        'hora' => '16:45:00',
        'cliente_id' => 2,
        'cliente_nombre' => 'Carlos Méndez',
        'usuario_id' => 2,
        'usuario_nombre' => 'María Vendedor',
        'sucursal_id' => 2,
        'sucursal_nombre' => 'Chinaca Central',
        'subtotal' => 8500.00,
        'descuento' => 500.00,
        'total' => 8000.00,
        'tipo_venta' => 'credito',
        'estado' => 'completada',
        'forma_pago_principal' => 'credito',
        'cantidad_productos' => 1
    ],
    [
        'id' => 102,
        'numero_venta' => 'V-2025-0102',
        'fecha' => '2025-01-22',
        'hora' => '10:20:00',
        'cliente_id' => 3,
        'cliente_nombre' => 'Ana Ramírez',
        'usuario_id' => 1,
        'usuario_nombre' => 'Carlos Admin',
        'sucursal_id' => 1,
        'sucursal_nombre' => 'Los Arcos',
        'subtotal' => 2400.00,
        'descuento' => 0.00,
        'total' => 2400.00,
        'tipo_venta' => 'apartado',
        'estado' => 'apartada',
        'forma_pago_principal' => 'efectivo',
        'cantidad_productos' => 3
    ],
    [
        'id' => 101,
        'numero_venta' => 'V-2025-0101',
        'fecha' => '2025-01-20',
        'hora' => '09:00:00',
        'cliente_id' => null,
        'cliente_nombre' => 'Consumidor Final',
        'usuario_id' => 2,
        'usuario_nombre' => 'María Vendedor',
        'sucursal_id' => 1,
        'sucursal_nombre' => 'Los Arcos',
        'subtotal' => 1200.00,
        'descuento' => 0.00,
        'total' => 1200.00,
        'tipo_venta' => 'normal',
        'estado' => 'anulada',
        'forma_pago_principal' => 'efectivo',
        'cantidad_productos' => 1,
        'motivo_anulacion' => 'Cliente devolvió producto - no le gustó'
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
                    <i class="bi bi-cart-check"></i>
                    Ventas
                </h1>
                <p class="text-muted">Historial de ventas y punto de venta</p>
            </div>
            <div class="col-md-6 text-end">
                <?php if (tiene_permiso('ventas', 'crear')): ?>
                <a href="nueva.php" class="btn btn-primary btn-lg">
                    <i class="bi bi-plus-circle"></i>
                    Nueva Venta
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Resumen de Ventas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card verde">
                <div class="stat-icon">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-value">
                    <?php echo count(array_filter($ventas, fn($v) => $v['estado'] == 'completada')); ?>
                </div>
                <div class="stat-label">Completadas Hoy</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card dorado">
                <div class="stat-icon">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div class="stat-value">
                    Q <?php 
                    $total_ventas = array_sum(array_map(fn($v) => $v['estado'] == 'completada' ? $v['total'] : 0, $ventas));
                    echo number_format($total_ventas, 0); 
                    ?>
                </div>
                <div class="stat-label">Total en Ventas</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card amarillo">
                <div class="stat-icon">
                    <i class="bi bi-bookmark"></i>
                </div>
                <div class="stat-value">
                    <?php echo count(array_filter($ventas, fn($v) => $v['estado'] == 'apartada')); ?>
                </div>
                <div class="stat-label">Apartados</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card rojo">
                <div class="stat-icon">
                    <i class="bi bi-x-circle"></i>
                </div>
                <div class="stat-value">
                    <?php echo count(array_filter($ventas, fn($v) => $v['estado'] == 'anulada')); ?>
                </div>
                <div class="stat-label">Anuladas</div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Buscar</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control" id="searchInput" 
                               placeholder="Número, cliente...">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Fecha Desde</label>
                    <input type="date" class="form-control" id="fechaDesde">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Fecha Hasta</label>
                    <input type="date" class="form-control" id="fechaHasta">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Estado</label>
                    <select class="form-select" id="filterEstado">
                        <option value="">Todos</option>
                        <option value="completada">Completada</option>
                        <option value="apartada">Apartada</option>
                        <option value="anulada">Anulada</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tipo</label>
                    <select class="form-select" id="filterTipo">
                        <option value="">Todos</option>
                        <option value="normal">Normal</option>
                        <option value="credito">Crédito</option>
                        <option value="apartado">Apartado</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button class="btn btn-secondary w-100" onclick="limpiarFiltros()">
                        <i class="bi bi-x-circle"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Ventas -->
    <div class="card">
        <div class="card-header">
            <i class="bi bi-table"></i>
            Historial de Ventas (<?php echo count($ventas); ?>)
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="tablaVentas">
                    <thead>
                        <tr>
                            <th>Número</th>
                            <th>Fecha/Hora</th>
                            <th>Cliente</th>
                            <th>Productos</th>
                            <th>Subtotal</th>
                            <th>Descuento</th>
                            <th>Total</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Vendedor</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ventas as $venta): ?>
                        <tr>
                            <td class="fw-bold text-primary"><?php echo $venta['numero_venta']; ?></td>
                            <td>
                                <div><?php echo date('d/m/Y', strtotime($venta['fecha'])); ?></div>
                                <small class="text-muted"><?php echo date('H:i', strtotime($venta['hora'])); ?></small>
                            </td>
                            <td>
                                <?php if ($venta['cliente_id']): ?>
                                    <div class="fw-bold"><?php echo $venta['cliente_nombre']; ?></div>
                                    <small class="text-muted">ID: <?php echo $venta['cliente_id']; ?></small>
                                <?php else: ?>
                                    <span class="text-muted">Consumidor Final</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-info"><?php echo $venta['cantidad_productos']; ?></span>
                            </td>
                            <td>Q <?php echo number_format($venta['subtotal'], 2); ?></td>
                            <td>
                                <?php if ($venta['descuento'] > 0): ?>
                                    <span class="text-danger">-Q <?php echo number_format($venta['descuento'], 2); ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
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
                            <td>
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
                            </td>
                            <td>
                                <small class="text-muted"><?php echo $venta['usuario_nombre']; ?></small>
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <a href="ver.php?id=<?php echo $venta['id']; ?>" 
                                       class="btn btn-sm btn-info"
                                       data-bs-toggle="tooltip" 
                                       title="Ver detalles">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <button class="btn btn-sm btn-secondary" 
                                            data-bs-toggle="tooltip" 
                                            title="Imprimir">
                                        <i class="bi bi-printer"></i>
                                    </button>
                                    <?php if (tiene_permiso('ventas', 'eliminar') && $venta['estado'] == 'completada'): ?>
                                    <a href="anular.php?id=<?php echo $venta['id']; ?>" 
                                       class="btn btn-sm btn-danger"
                                       data-bs-toggle="tooltip" 
                                       title="Anular venta">
                                        <i class="bi bi-x-circle"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="6" class="text-end fw-bold">TOTALES:</td>
                            <td class="fw-bold text-success">
                                Q <?php 
                                $total_general = array_sum(array_map(fn($v) => $v['estado'] != 'anulada' ? $v['total'] : 0, $ventas));
                                echo number_format($total_general, 2); 
                                ?>
                            </td>
                            <td colspan="4"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <small class="text-muted">
                        Mostrando <?php echo count($ventas); ?> ventas
                    </small>
                </div>
                <div class="col-md-6 text-end">
                    <button class="btn btn-sm btn-secondary">
                        <i class="bi bi-file-earmark-excel"></i>
                        Exportar Excel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function limpiarFiltros() {
    document.getElementById('searchInput').value = '';
    document.getElementById('fechaDesde').value = '';
    document.getElementById('fechaHasta').value = '';
    document.getElementById('filterEstado').value = '';
    document.getElementById('filterTipo').value = '';
}

// Búsqueda en tiempo real
document.getElementById('searchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#tablaVentas tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});
</script>

<?php
// Incluir footer
include '../../includes/footer.php';
?>