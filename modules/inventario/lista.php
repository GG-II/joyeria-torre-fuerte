<?php
// ================================================
// MÓDULO INVENTARIO - LISTA (CORREGIDO)
// ================================================

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

// Verificar autenticación y permisos
requiere_autenticacion();

// Título de página
$titulo_pagina = 'Inventario';

// Incluir header
include '../../includes/header.php';

// Incluir navbar
include '../../includes/navbar.php';

// Datos dummy de productos (ESTRUCTURA REAL: productos + inventario + precios_producto)
// En producción esto vendrá de un JOIN:
// SELECT p.*, c.nombre as categoria_nombre, 
//        pp.precio as precio_publico,
//        i1.cantidad as stock_sucursal_1, i2.cantidad as stock_sucursal_2,
//        (COALESCE(i1.cantidad,0) + COALESCE(i2.cantidad,0)) as stock_total
// FROM productos p
// LEFT JOIN categorias c ON p.categoria_id = c.id
// LEFT JOIN precios_producto pp ON p.id = pp.producto_id AND pp.tipo_precio = 'publico'
// LEFT JOIN inventario i1 ON p.id = i1.producto_id AND i1.sucursal_id = 1
// LEFT JOIN inventario i2 ON p.id = i2.producto_id AND i2.sucursal_id = 2

$productos = [
    [
        'id' => 1,
        'codigo' => 'AN-001',
        'codigo_barras' => '7501234567890',
        'nombre' => 'Anillo de Oro con Diamante',
        'categoria_id' => 1,
        'categoria_nombre' => 'Anillos',
        'es_por_peso' => 0,
        'peso_gramos' => 5.5,
        'estilo' => 'Clásico',
        'largo_cm' => null,
        'precio_publico' => 8500.00,  // Viene de precios_producto
        'stock_los_arcos' => 3,        // Viene de inventario donde sucursal_id = 1
        'stock_chinaca' => 2,          // Viene de inventario donde sucursal_id = 2
        'stock_total' => 5,
        'stock_minimo' => 2,
        'activo' => 1
    ],
    [
        'id' => 2,
        'codigo' => 'AR-102',
        'codigo_barras' => null,
        'nombre' => 'Aretes de Plata con Perla',
        'categoria_id' => 2,
        'categoria_nombre' => 'Aretes',
        'es_por_peso' => 0,
        'peso_gramos' => 3.2,
        'estilo' => 'Elegante',
        'largo_cm' => null,
        'precio_publico' => 1200.00,
        'stock_los_arcos' => 8,
        'stock_chinaca' => 5,
        'stock_total' => 13,
        'stock_minimo' => 4,
        'activo' => 1
    ],
    [
        'id' => 3,
        'codigo' => 'CO-045',
        'codigo_barras' => '7501234567891',
        'nombre' => 'Collar de Cadena Fina',
        'categoria_id' => 3,
        'categoria_nombre' => 'Collares',
        'es_por_peso' => 1,
        'peso_gramos' => 8.0,
        'estilo' => 'Moderno',
        'largo_cm' => 45.00,
        'precio_publico' => 4500.00,
        'stock_los_arcos' => 1,
        'stock_chinaca' => 0,
        'stock_total' => 1,
        'stock_minimo' => 3,
        'activo' => 1
    ],
    [
        'id' => 4,
        'codigo' => 'PU-089',
        'codigo_barras' => null,
        'nombre' => 'Pulsera de Plata con Circonitas',
        'categoria_id' => 4,
        'categoria_nombre' => 'Pulseras',
        'es_por_peso' => 0,
        'peso_gramos' => 4.5,
        'estilo' => 'Juvenil',
        'largo_cm' => 18.00,
        'precio_publico' => 950.00,
        'stock_los_arcos' => 0,
        'stock_chinaca' => 0,
        'stock_total' => 0,
        'stock_minimo' => 2,
        'activo' => 1
    ],
    [
        'id' => 5,
        'codigo' => 'RE-234',
        'codigo_barras' => '7501234567892',
        'nombre' => 'Reloj de Lujo con Diamantes',
        'categoria_id' => 7,
        'categoria_nombre' => 'Relojes',
        'es_por_peso' => 0,
        'peso_gramos' => 45.0,
        'estilo' => 'Lujo',
        'largo_cm' => null,
        'precio_publico' => 15000.00,
        'stock_los_arcos' => 1,
        'stock_chinaca' => 1,
        'stock_total' => 2,
        'stock_minimo' => 1,
        'activo' => 1
    ]
];

// Determinar estado de stock
foreach ($productos as &$producto) {
    if ($producto['stock_total'] == 0) {
        $producto['estado_stock'] = 'agotado';
    } elseif ($producto['stock_total'] <= $producto['stock_minimo']) {
        $producto['estado_stock'] = 'bajo_stock';
    } else {
        $producto['estado_stock'] = 'disponible';
    }
}
?>

<!-- Contenido Principal -->
<div class="container-fluid main-content">
    <!-- Encabezado de Página -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1>
                    <i class="bi bi-box-seam"></i>
                    Inventario de Productos
                </h1>
                <p class="text-muted">Control de stock por sucursal</p>
            </div>
            <div class="col-md-6 text-end">
                <?php if (tiene_permiso('inventario', 'crear')): ?>
                <a href="agregar.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i>
                    Nuevo Producto
                </a>
                <a href="transferencias.php" class="btn btn-warning">
                    <i class="bi bi-arrow-left-right"></i>
                    Transferencias
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Resumen de Inventario -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card azul">
                <div class="stat-icon">
                    <i class="bi bi-boxes"></i>
                </div>
                <div class="stat-value"><?php echo count($productos); ?></div>
                <div class="stat-label">Productos Totales</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card verde">
                <div class="stat-icon">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-value">
                    <?php echo count(array_filter($productos, fn($p) => $p['estado_stock'] == 'disponible')); ?>
                </div>
                <div class="stat-label">Disponibles</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card amarillo">
                <div class="stat-icon">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div class="stat-value">
                    <?php echo count(array_filter($productos, fn($p) => $p['estado_stock'] == 'bajo_stock')); ?>
                </div>
                <div class="stat-label">Stock Bajo</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card rojo">
                <div class="stat-icon">
                    <i class="bi bi-x-circle"></i>
                </div>
                <div class="stat-value">
                    <?php echo count(array_filter($productos, fn($p) => $p['estado_stock'] == 'agotado')); ?>
                </div>
                <div class="stat-label">Agotados</div>
            </div>
        </div>
    </div>

    <!-- Filtros y Búsqueda -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Buscar Producto</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control" id="searchInput" 
                               placeholder="Código, nombre, categoría...">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Categoría</label>
                    <select class="form-select" id="filterCategoria">
                        <option value="">Todas</option>
                        <option value="Anillos">Anillos</option>
                        <option value="Aretes">Aretes</option>
                        <option value="Collares">Collares</option>
                        <option value="Pulseras">Pulseras</option>
                        <option value="Relojes">Relojes</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Estado</label>
                    <select class="form-select" id="filterEstado">
                        <option value="">Todos</option>
                        <option value="disponible">Disponible</option>
                        <option value="bajo_stock">Stock Bajo</option>
                        <option value="agotado">Agotado</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sucursal</label>
                    <select class="form-select" id="filterSucursal">
                        <option value="">Todas</option>
                        <option value="los_arcos">Los Arcos</option>
                        <option value="chinaca">Chinaca Central</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button class="btn btn-secondary w-100" onclick="limpiarFiltros()">
                        <i class="bi bi-x-circle"></i>
                        Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Productos -->
    <div class="card">
        <div class="card-header">
            <i class="bi bi-table"></i>
            Listado de Productos
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="tablaInventario">
                    <thead>
                        <tr>
                            <th width="100">Código</th>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Precio</th>
                            <th class="text-center">Los Arcos</th>
                            <th class="text-center">Chinaca</th>
                            <th class="text-center">Total</th>
                            <th>Estado</th>
                            <th width="180" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $producto): ?>
                        <tr>
                            <td class="fw-bold text-primary"><?php echo $producto['codigo']; ?></td>
                            <td>
                                <div class="fw-bold"><?php echo $producto['nombre']; ?></div>
                                <small class="text-muted">
                                    <i class="bi bi-weight"></i>
                                    <?php echo $producto['peso_gramos']; ?>g
                                    <?php if ($producto['es_por_peso']): ?>
                                        <span class="badge bg-warning text-dark ms-1">Por peso</span>
                                    <?php endif; ?>
                                </small>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    <?php echo $producto['categoria_nombre']; ?>
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-success">
                                    Q <?php echo number_format($producto['precio_publico'], 2); ?>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge <?php echo $producto['stock_los_arcos'] > 0 ? 'bg-info' : 'bg-secondary'; ?>">
                                    <?php echo $producto['stock_los_arcos']; ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge <?php echo $producto['stock_chinaca'] > 0 ? 'bg-info' : 'bg-secondary'; ?>">
                                    <?php echo $producto['stock_chinaca']; ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold">
                                    <?php echo $producto['stock_total']; ?>
                                </span>
                                <small class="text-muted d-block">
                                    Min: <?php echo $producto['stock_minimo']; ?>
                                </small>
                            </td>
                            <td>
                                <?php
                                $badges_estado = [
                                    'disponible' => 'bg-success',
                                    'bajo_stock' => 'bg-warning',
                                    'agotado' => 'bg-danger'
                                ];
                                $textos_estado = [
                                    'disponible' => 'Disponible',
                                    'bajo_stock' => 'Stock Bajo',
                                    'agotado' => 'Agotado'
                                ];
                                ?>
                                <span class="badge <?php echo $badges_estado[$producto['estado_stock']]; ?>">
                                    <?php echo $textos_estado[$producto['estado_stock']]; ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <a href="ver.php?id=<?php echo $producto['id']; ?>" 
                                       class="btn btn-sm btn-info"
                                       data-bs-toggle="tooltip" 
                                       title="Ver detalles">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if (tiene_permiso('inventario', 'editar')): ?>
                                    <a href="editar.php?id=<?php echo $producto['id']; ?>" 
                                       class="btn btn-sm btn-warning"
                                       data-bs-toggle="tooltip" 
                                       title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (tiene_permiso('inventario', 'eliminar')): ?>
                                    <button class="btn btn-sm btn-danger" 
                                            data-action="delete"
                                            data-bs-toggle="tooltip" 
                                            title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <small class="text-muted">
                        Mostrando <?php echo count($productos); ?> productos
                    </small>
                </div>
                <div class="col-md-6 text-end">
                    <a href="<?php echo BASE_URL; ?>api/reportes/inventario.php" 
                       class="btn btn-sm btn-secondary" target="_blank">
                        <i class="bi bi-file-earmark-excel"></i>
                        Exportar Excel
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Funciones de filtrado
function limpiarFiltros() {
    document.getElementById('searchInput').value = '';
    document.getElementById('filterCategoria').value = '';
    document.getElementById('filterEstado').value = '';
    document.getElementById('filterSucursal').value = '';
}

// Búsqueda en tiempo real
document.getElementById('searchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#tablaInventario tbody tr');
    
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