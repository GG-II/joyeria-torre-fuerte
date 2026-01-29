<?php
/**
 * ================================================
 * MÓDULO INVENTARIO - LISTA
 * ================================================
 * 
 * TODO FASE 5: Conectar con API
 * GET /api/inventario/lista.php
 * 
 * Parámetros: buscar, categoria_id, estado_stock, sucursal_id
 * Respuesta: { success, data: [...productos], resumen: {...stats} }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();

$titulo_pagina = 'Inventario';
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="container-fluid main-content">
    <div class="page-header mb-4">
        <div class="row align-items-center g-3">
            <div class="col-md-6">
                <h1 class="mb-2"><i class="bi bi-box-seam"></i> Inventario de Productos</h1>
                <p class="text-muted mb-0">Control de stock por sucursal</p>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                    <?php if (tiene_permiso('inventario', 'crear')): ?>
                    <a href="agregar.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> <span class="d-none d-sm-inline">Nuevo</span>
                    </a>
                    <a href="transferencias.php" class="btn btn-warning">
                        <i class="bi bi-arrow-left-right"></i> <span class="d-none d-sm-inline">Transferencias</span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4" id="estadisticas">
        <div class="col-6 col-md-3">
            <div class="stat-card azul">
                <div class="stat-icon"><i class="bi bi-boxes"></i></div>
                <div class="stat-value" id="statTotal">0</div>
                <div class="stat-label">Productos Totales</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card verde">
                <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
                <div class="stat-value" id="statDisponibles">0</div>
                <div class="stat-label">Disponibles</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card amarillo">
                <div class="stat-icon"><i class="bi bi-exclamation-triangle"></i></div>
                <div class="stat-value" id="statBajoStock">0</div>
                <div class="stat-label">Stock Bajo</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card rojo">
                <div class="stat-icon"><i class="bi bi-x-circle"></i></div>
                <div class="stat-value" id="statAgotados">0</div>
                <div class="stat-label">Agotados</div>
            </div>
        </div>
    </div>

    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Buscar Producto</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" id="searchInput" placeholder="Código, nombre...">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Categoría</label>
                    <select class="form-select" id="filterCategoria">
                        <option value="">Todas</option>
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
                        <option value="1">Los Arcos</option>
                        <option value="2">Chinaca Central</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label d-none d-md-block">&nbsp;</label>
                    <button class="btn btn-secondary w-100" onclick="limpiarFiltros()">
                        <i class="bi bi-x-circle"></i> <span class="d-md-none">Limpiar</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header" style="background-color: #1e3a8a; color: white;">
            <i class="bi bi-table"></i> <span id="tituloTabla">Listado de Productos</span>
        </div>
        
        <div id="loadingTable" class="text-center py-5">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
            <p class="mt-3 text-muted">Cargando inventario...</p>
        </div>

        <div id="tableContainer" class="table-responsive" style="display: none;">
            <table class="table table-hover mb-0">
                <thead style="background-color: #1e3a8a; color: white;">
                    <tr>
                        <th>Código</th>
                        <th>Producto</th>
                        <th class="d-none d-lg-table-cell">Categoría</th>
                        <th class="d-none d-md-table-cell">Precio</th>
                        <th class="text-center d-none d-xl-table-cell">Los Arcos</th>
                        <th class="text-center d-none d-xl-table-cell">Chinaca</th>
                        <th class="text-center">Total</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="productosBody"></tbody>
            </table>
        </div>

        <div id="noResults" class="text-center py-5" style="display: none;">
            <i class="bi bi-inbox" style="font-size: 48px; opacity: 0.3;"></i>
            <p class="mt-3 text-muted">No se encontraron productos</p>
        </div>

        <div class="card-footer" id="tableFooter" style="display: none;">
            <div class="row align-items-center g-2">
                <div class="col-md-6">
                    <small class="text-muted" id="contadorProductos">Mostrando 0 productos</small>
                </div>
                <div class="col-md-6 text-md-end">
                    <button class="btn btn-sm btn-secondary" onclick="exportarExcel()">
                        <i class="bi bi-file-earmark-excel"></i> Exportar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.main-content { padding: 20px; min-height: calc(100vh - 120px); }
.page-header h1 { font-size: 1.75rem; font-weight: 600; color: #1a1a1a; }
.shadow-sm { box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08) !important; }
.stat-card { background: white; border-radius: 12px; padding: 15px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08); transition: transform 0.2s ease; border-left: 4px solid; height: 100%; }
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12); }
.stat-card.azul { border-left-color: #1e3a8a; }
.stat-card.verde { border-left-color: #22c55e; }
.stat-card.amarillo { border-left-color: #eab308; }
.stat-card.rojo { border-left-color: #ef4444; }
.stat-icon { width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 22px; margin-bottom: 12px; }
.stat-card.azul .stat-icon { background: rgba(30, 58, 138, 0.1); color: #1e3a8a; }
.stat-card.verde .stat-icon { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
.stat-card.amarillo .stat-icon { background: rgba(234, 179, 8, 0.1); color: #eab308; }
.stat-card.rojo .stat-icon { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
.stat-value { font-size: 1.5rem; font-weight: 700; color: #1a1a1a; margin: 8px 0; }
.stat-label { font-size: 0.8rem; color: #6b7280; font-weight: 500; }
table thead th { font-weight: 600; font-size: 0.85rem; text-transform: uppercase; padding: 12px; }
table tbody td { padding: 12px; vertical-align: middle; }
.badge { padding: 0.35em 0.65em; font-size: 0.85em; }
@media (max-width: 575.98px) {
    .main-content { padding: 15px 10px; }
    .page-header h1 { font-size: 1.5rem; }
    .stat-card { padding: 12px; }
    .stat-icon { width: 38px; height: 38px; font-size: 18px; margin-bottom: 8px; }
    .stat-value { font-size: 1.25rem; }
    table { font-size: 0.85rem; }
    table thead th, table tbody td { padding: 8px 6px; }
}
@media (min-width: 576px) and (max-width: 767.98px) { .main-content { padding: 18px 15px; } }
@media (min-width: 992px) { .main-content { padding: 25px 30px; } }
@media (max-width: 767.98px) { .btn, .form-control, .form-select { min-height: 44px; } }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    cargarInventario();
    cargarCategorias();
    
    document.getElementById('searchInput').addEventListener('input', aplicarFiltros);
    document.getElementById('filterCategoria').addEventListener('change', aplicarFiltros);
    document.getElementById('filterEstado').addEventListener('change', aplicarFiltros);
    document.getElementById('filterSucursal').addEventListener('change', aplicarFiltros);
});

function cargarInventario() {
    /* TODO FASE 5: Descomentar
    const params = new URLSearchParams({
        buscar: document.getElementById('searchInput').value,
        categoria_id: document.getElementById('filterCategoria').value,
        estado_stock: document.getElementById('filterEstado').value,
        sucursal_id: document.getElementById('filterSucursal').value
    });
    
    fetch('<?php echo BASE_URL; ?>api/inventario/lista.php?' + params)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderizarProductos(data.data);
                actualizarEstadisticas(data.resumen);
            } else {
                mostrarError(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarError('Error al cargar el inventario');
        });
    */
    
    setTimeout(() => {
        document.getElementById('loadingTable').style.display = 'none';
        document.getElementById('noResults').style.display = 'block';
        document.getElementById('noResults').innerHTML = '<i class="bi bi-database" style="font-size: 48px; opacity: 0.3;"></i><p class="mt-3 text-muted">MODO DESARROLLO: Esperando API</p>';
    }, 1500);
}

function cargarCategorias() {
    /* TODO FASE 5: Descomentar
    fetch('<?php echo BASE_URL; ?>api/categorias/lista.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('filterCategoria');
                data.data.forEach(cat => {
                    const option = document.createElement('option');
                    option.value = cat.id;
                    option.textContent = cat.nombre;
                    select.appendChild(option);
                });
            }
        });
    */
}

function renderizarProductos(productos) {
    const tbody = document.getElementById('productosBody');
    
    if (productos.length === 0) {
        document.getElementById('loadingTable').style.display = 'none';
        document.getElementById('noResults').style.display = 'block';
        return;
    }
    
    let html = '';
    productos.forEach(p => {
        const estadoStock = calcularEstadoStock(p.stock_total, p.stock_minimo);
        
        html += `
            <tr>
                <td class="fw-bold text-primary">${p.codigo}</td>
                <td>
                    <div class="fw-bold">${p.nombre}</div>
                    <small class="text-muted">
                        <i class="bi bi-weight"></i> ${p.peso_gramos}g
                        ${p.es_por_peso ? '<span class="badge bg-warning text-dark ms-1">Por peso</span>' : ''}
                    </small>
                </td>
                <td class="d-none d-lg-table-cell"><span class="badge bg-secondary">${p.categoria_nombre}</span></td>
                <td class="d-none d-md-table-cell"><div class="fw-bold text-success">Q ${formatearMoneda(p.precio_publico)}</div></td>
                <td class="text-center d-none d-xl-table-cell">
                    <span class="badge ${p.stock_los_arcos > 0 ? 'bg-info' : 'bg-secondary'}">${p.stock_los_arcos}</span>
                </td>
                <td class="text-center d-none d-xl-table-cell">
                    <span class="badge ${p.stock_chinaca > 0 ? 'bg-info' : 'bg-secondary'}">${p.stock_chinaca}</span>
                </td>
                <td class="text-center">
                    <span class="fw-bold">${p.stock_total}</span>
                    <small class="text-muted d-block">Min: ${p.stock_minimo}</small>
                </td>
                <td>${getBadgeEstadoStock(estadoStock)}</td>
                <td class="text-center">
                    <div class="btn-group">
                        <a href="ver.php?id=${p.id}" class="btn btn-sm btn-info" title="Ver"><i class="bi bi-eye"></i></a>
                        <?php if (tiene_permiso('inventario', 'editar')): ?>
                        <a href="editar.php?id=${p.id}" class="btn btn-sm btn-warning" title="Editar"><i class="bi bi-pencil"></i></a>
                        <?php endif; ?>
                        <?php if (tiene_permiso('inventario', 'eliminar')): ?>
                        <button class="btn btn-sm btn-danger" onclick="eliminarProducto(${p.id})" title="Eliminar"><i class="bi bi-trash"></i></button>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    document.getElementById('loadingTable').style.display = 'none';
    document.getElementById('tableContainer').style.display = 'block';
    document.getElementById('tableFooter').style.display = 'block';
    document.getElementById('contadorProductos').textContent = `Mostrando ${productos.length} productos`;
    document.getElementById('tituloTabla').textContent = `Listado de Productos (${productos.length})`;
}

function actualizarEstadisticas(resumen) {
    document.getElementById('statTotal').textContent = resumen.total || 0;
    document.getElementById('statDisponibles').textContent = resumen.disponibles || 0;
    document.getElementById('statBajoStock').textContent = resumen.bajo_stock || 0;
    document.getElementById('statAgotados').textContent = resumen.agotados || 0;
}

function calcularEstadoStock(stockTotal, stockMinimo) {
    if (stockTotal === 0) return 'agotado';
    if (stockTotal <= stockMinimo) return 'bajo_stock';
    return 'disponible';
}

function getBadgeEstadoStock(estado) {
    const badges = {
        'disponible': '<span class="badge bg-success">Disponible</span>',
        'bajo_stock': '<span class="badge bg-warning">Stock Bajo</span>',
        'agotado': '<span class="badge bg-danger">Agotado</span>'
    };
    return badges[estado] || '';
}

function aplicarFiltros() { cargarInventario(); }

function limpiarFiltros() {
    document.getElementById('searchInput').value = '';
    document.getElementById('filterCategoria').value = '';
    document.getElementById('filterEstado').value = '';
    document.getElementById('filterSucursal').value = '';
    cargarInventario();
}

function eliminarProducto(id) {
    if (confirm('¿Está seguro de eliminar este producto?\n\nEsta acción no se puede deshacer.')) {
        alert('MODO DESARROLLO: Eliminar producto #' + id);
    }
}

function exportarExcel() {
    alert('MODO DESARROLLO: Exportar a Excel - Pendiente implementar');
}

function formatearMoneda(monto) {
    return parseFloat(monto).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function mostrarError(mensaje) {
    document.getElementById('loadingTable').style.display = 'none';
    document.getElementById('noResults').style.display = 'block';
    document.getElementById('noResults').innerHTML = `<i class="bi bi-exclamation-triangle text-danger" style="font-size: 48px;"></i><p class="mt-3 text-danger">${mensaje}</p>`;
}
</script>

<?php include '../../includes/footer.php'; ?>