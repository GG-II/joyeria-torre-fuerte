<?php
/**
 * ================================================
 * MÓDULO REPORTES - REPORTE DE INVENTARIO
 * ================================================
 * 
 * TODO FASE 5: Conectar con API
 * GET /api/reportes/inventario.php
 * 
 * Parámetros: sucursal_id, categoria_id
 * Respuesta: { resumen, por_categoria, por_sucursal, productos_criticos }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();

$titulo_pagina = 'Reporte de Inventario';
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="container-fluid main-content">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="dashboard.php"><i class="bi bi-graph-up"></i> Reportes</a></li>
            <li class="breadcrumb-item active">Inventario</li>
        </ol>
    </nav>

    <div class="page-header mb-4">
        <div class="row align-items-center g-3">
            <div class="col-md-6">
                <h1 class="mb-2"><i class="bi bi-box-seam"></i> Reporte de Inventario</h1>
                <p class="text-muted mb-0">Estado actual del inventario</p>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                    <select class="form-select" id="filterSucursal" style="max-width: 200px;">
                        <option value="">Todas las sucursales</option>
                        <option value="1">Los Arcos</option>
                        <option value="2">Chinaca Central</option>
                    </select>
                    <select class="form-select" id="filterCategoria" style="max-width: 200px;">
                        <option value="">Todas las categorías</option>
                    </select>
                    <button class="btn btn-primary" onclick="aplicarFiltros()">
                        <i class="bi bi-funnel"></i> <span class="d-none d-sm-inline">Filtrar</span>
                    </button>
                    <button class="btn btn-success" onclick="exportarExcel()">
                        <i class="bi bi-file-earmark-excel"></i> <span class="d-none d-sm-inline">Exportar</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="loadingState" class="text-center py-5">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
        <p class="mt-3 text-muted">Cargando reporte de inventario...</p>
    </div>

    <div id="mainContent" style="display: none;">
        <div class="row g-3 mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="stat-card azul">
                    <div class="stat-icon"><i class="bi bi-boxes"></i></div>
                    <div class="stat-value" id="statTotal">0</div>
                    <div class="stat-label">Total de Productos</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card dorado">
                    <div class="stat-icon"><i class="bi bi-currency-dollar"></i></div>
                    <div class="stat-value" id="statValor">Q 0</div>
                    <div class="stat-label">Valor Total</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card amarillo">
                    <div class="stat-icon"><i class="bi bi-exclamation-triangle"></i></div>
                    <div class="stat-value" id="statBajo">0</div>
                    <div class="stat-label">Bajo Stock</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card rojo">
                    <div class="stat-icon"><i class="bi bi-x-circle"></i></div>
                    <div class="stat-value" id="statAgotados">0</div>
                    <div class="stat-label">Agotados</div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header" style="background-color: #1e3a8a; color: white;">
                        <i class="bi bi-pie-chart"></i> Inventario por Categoría
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background-color: #1e3a8a; color: white;">
                                <tr>
                                    <th>Categoría</th>
                                    <th>Cantidad</th>
                                    <th>Valor</th>
                                    <th>% del Total</th>
                                </tr>
                            </thead>
                            <tbody id="categoriasBody">
                                <tr><td colspan="4" class="text-center py-4"><div class="spinner-border spinner-border-sm"></div></td></tr>
                            </tbody>
                            <tfoot class="table-light" id="categoriasFoot" style="display: none;">
                                <tr>
                                    <td class="fw-bold">TOTAL</td>
                                    <td class="fw-bold" id="totalCantidad">0</td>
                                    <td class="fw-bold text-success" id="totalValor">Q 0.00</td>
                                    <td class="fw-bold">100%</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-danger text-white">
                        <i class="bi bi-exclamation-octagon"></i> Productos Críticos
                    </div>
                    <div class="card-body" id="productosCriticos" style="max-height: 400px; overflow-y: auto;">
                        <div class="text-center py-3"><div class="spinner-border spinner-border-sm"></div></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mt-3">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header" style="background-color: #1e3a8a; color: white;">
                        <i class="bi bi-building"></i> Inventario por Sucursal
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background-color: #1e3a8a; color: white;">
                                <tr>
                                    <th>Sucursal</th>
                                    <th>Total Productos</th>
                                    <th>Valor Total</th>
                                    <th>Bajo Stock</th>
                                    <th>Agotados</th>
                                </tr>
                            </thead>
                            <tbody id="sucursalesBody">
                                <tr><td colspan="5" class="text-center py-4"><div class="spinner-border spinner-border-sm"></div></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.main-content { padding: 20px; min-height: calc(100vh - 120px); }
.page-header h1 { font-size: 1.75rem; font-weight: 600; color: #1a1a1a; }
.shadow-sm { box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08) !important; }
.stat-card { background: linear-gradient(135deg, var(--card-color-start) 0%, var(--card-color-end) 100%); border-radius: 12px; padding: 20px; color: white; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15); transition: transform 0.2s ease; height: 100%; }
.stat-card:hover { transform: translateY(-3px); box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2); }
.stat-card.azul { --card-color-start: #1e3a8a; --card-color-end: #1e40af; }
.stat-card.dorado { --card-color-start: #d4af37; --card-color-end: #b8941f; }
.stat-card.amarillo { --card-color-start: #eab308; --card-color-end: #ca8a04; }
.stat-card.rojo { --card-color-start: #ef4444; --card-color-end: #dc2626; }
.stat-icon { width: 50px; height: 50px; border-radius: 10px; background: rgba(255, 255, 255, 0.2); display: flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 15px; color: white; }
.stat-value { font-size: 1.75rem; font-weight: 700; margin: 10px 0; color: white !important; }
.stat-label { font-size: 0.85rem; opacity: 0.95; font-weight: 500; color: white !important; }
table thead th { font-weight: 600; font-size: 0.85rem; text-transform: uppercase; padding: 12px; }
table tbody td { padding: 12px; vertical-align: middle; }
.producto-critico { padding: 10px; margin-bottom: 10px; border-radius: 6px; background: #fee2e2; border-left: 3px solid #dc2626; }
.producto-critico.agotado { background: #fef2f2; }
@media (max-width: 575.98px) {
    .main-content { padding: 15px 10px; }
    .page-header h1 { font-size: 1.5rem; }
    .stat-card { padding: 15px; }
    .stat-value { font-size: 1.5rem; }
}
@media (min-width: 576px) and (max-width: 767.98px) { .main-content { padding: 18px 15px; } }
@media (min-width: 992px) { .main-content { padding: 25px 30px; } }
@media (max-width: 767.98px) { .btn, .form-control, .form-select { min-height: 44px; } }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    cargarReporteInventario();
    cargarCategorias();
});

function cargarReporteInventario() {
    /* TODO FASE 5: Descomentar
    const params = new URLSearchParams({
        sucursal_id: document.getElementById('filterSucursal').value,
        categoria_id: document.getElementById('filterCategoria').value
    });
    
    fetch('<?php echo BASE_URL; ?>api/reportes/inventario.php?' + params)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                actualizarResumen(data.data.resumen);
                renderizarCategorias(data.data.por_categoria);
                renderizarProductosCriticos(data.data.productos_criticos);
                renderizarSucursales(data.data.por_sucursal);
            }
        })
        .catch(error => console.error('Error:', error));
    */
    
    setTimeout(() => {
        document.getElementById('loadingState').style.display = 'none';
        document.getElementById('mainContent').style.display = 'block';
        mostrarMensajeDesarrollo();
    }, 1500);
}

function cargarCategorias() {
    /* TODO FASE 5: Descomentar
    fetch('<?php echo BASE_URL; ?>api/categorias/lista.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('filterCategoria');
                data.data.forEach(c => {
                    const option = document.createElement('option');
                    option.value = c.id;
                    option.textContent = c.nombre;
                    select.appendChild(option);
                });
            }
        });
    */
}

function actualizarResumen(resumen) {
    document.getElementById('statTotal').textContent = resumen.total_productos || 0;
    document.getElementById('statValor').textContent = 'Q ' + formatearMoneda(resumen.valor_total || 0).split('.')[0];
    document.getElementById('statBajo').textContent = resumen.productos_bajo_stock || 0;
    document.getElementById('statAgotados').textContent = resumen.productos_agotados || 0;
}

function renderizarCategorias(categorias) {
    const tbody = document.getElementById('categoriasBody');
    
    if (categorias.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted">Sin datos</td></tr>';
        return;
    }
    
    const totalValor = categorias.reduce((sum, c) => sum + parseFloat(c.valor), 0);
    const totalCantidad = categorias.reduce((sum, c) => sum + parseInt(c.cantidad), 0);
    let html = '';
    
    categorias.forEach(c => {
        const porcentaje = ((parseFloat(c.valor) / totalValor) * 100).toFixed(1);
        html += `
            <tr>
                <td>${c.nombre}</td>
                <td>${c.cantidad}</td>
                <td class="fw-bold">Q ${formatearMoneda(c.valor)}</td>
                <td>${porcentaje}%</td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    document.getElementById('totalCantidad').textContent = totalCantidad;
    document.getElementById('totalValor').textContent = 'Q ' + formatearMoneda(totalValor);
    document.getElementById('categoriasFoot').style.display = 'table-footer-group';
}

function renderizarProductosCriticos(productos) {
    const div = document.getElementById('productosCriticos');
    
    if (productos.length === 0) {
        div.innerHTML = '<p class="text-muted text-center mb-0">No hay productos críticos</p>';
        return;
    }
    
    let html = '';
    productos.forEach(p => {
        const clase = p.stock_actual === 0 ? 'agotado' : '';
        const icono = p.stock_actual === 0 ? 'x-circle' : 'exclamation-triangle';
        html += `
            <div class="producto-critico ${clase}">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <i class="bi bi-${icono} text-danger"></i>
                        <strong>${p.nombre}</strong>
                        <div><small class="text-muted">${p.categoria}</small></div>
                    </div>
                    <span class="badge ${p.stock_actual === 0 ? 'bg-danger' : 'bg-warning text-dark'}">${p.stock_actual} unid.</span>
                </div>
            </div>
        `;
    });
    
    div.innerHTML = html;
}

function renderizarSucursales(sucursales) {
    const tbody = document.getElementById('sucursalesBody');
    
    if (sucursales.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">Sin datos</td></tr>';
        return;
    }
    
    let html = '';
    sucursales.forEach(s => {
        html += `
            <tr>
                <td class="fw-bold">${s.nombre}</td>
                <td>${s.total_productos}</td>
                <td class="fw-bold text-success">Q ${formatearMoneda(s.valor_total)}</td>
                <td><span class="badge bg-warning text-dark">${s.bajo_stock}</span></td>
                <td><span class="badge bg-danger">${s.agotados}</span></td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

function mostrarMensajeDesarrollo() {
    document.getElementById('categoriasBody').innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted">MODO DESARROLLO: Esperando API</td></tr>';
    document.getElementById('productosCriticos').innerHTML = '<p class="text-muted text-center mb-0">MODO DESARROLLO: Esperando API</p>';
    document.getElementById('sucursalesBody').innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">MODO DESARROLLO: Esperando API</td></tr>';
}

function aplicarFiltros() { cargarReporteInventario(); }

function exportarExcel() { alert('MODO DESARROLLO: Exportar a Excel - Pendiente implementar'); }

function formatearMoneda(monto) {
    return parseFloat(monto).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}
</script>

<?php include '../../includes/footer.php'; ?>