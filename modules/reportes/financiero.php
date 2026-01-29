<?php
/**
 * ================================================
 * MÓDULO REPORTES - DASHBOARD GENERAL
 * ================================================
 * 
 * TODO FASE 5: Conectar con API
 * GET /api/reportes/dashboard.php
 * 
 * Parámetros: periodo (hoy, semana, mes, año)
 * Respuesta: { estadisticas, ventas_por_dia, productos_top, comparativas }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();

$titulo_pagina = 'Dashboard de Reportes';
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="container-fluid main-content">
    <div class="page-header mb-4">
        <div class="row align-items-center g-3">
            <div class="col-md-6">
                <h1 class="mb-2"><i class="bi bi-graph-up"></i> Dashboard de Reportes</h1>
                <p class="text-muted mb-0">Vista general del negocio</p>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="btn-group" role="group">
                    <button class="btn btn-outline-primary active" onclick="cambiarPeriodo('hoy')">Hoy</button>
                    <button class="btn btn-outline-primary" onclick="cambiarPeriodo('semana')">Semana</button>
                    <button class="btn btn-outline-primary" onclick="cambiarPeriodo('mes')">Mes</button>
                    <button class="btn btn-outline-primary" onclick="cambiarPeriodo('año')">Año</button>
                </div>
            </div>
        </div>
    </div>

    <div id="loadingState" class="text-center py-5">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
        <p class="mt-3 text-muted">Cargando dashboard...</p>
    </div>

    <div id="mainContent" style="display: none;">
        <div class="row g-3 mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="stat-card dorado">
                    <div class="stat-icon"><i class="bi bi-currency-dollar"></i></div>
                    <div class="stat-value" id="statVentasHoy">Q 0</div>
                    <div class="stat-label">Ventas Hoy</div>
                    <small class="text-white-50" id="compVentasHoy">-</small>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card azul">
                    <div class="stat-icon"><i class="bi bi-cart-check"></i></div>
                    <div class="stat-value" id="statClientes">0</div>
                    <div class="stat-label">Clientes Hoy</div>
                    <small class="text-white-50" id="compProductos">0 productos</small>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card verde">
                    <div class="stat-icon"><i class="bi bi-tools"></i></div>
                    <div class="stat-value" id="statTrabajos">0</div>
                    <div class="stat-label">Trabajos Completados (Mes)</div>
                    <small class="text-white-50" id="compTrabajos">0 pendientes</small>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card rojo">
                    <div class="stat-icon"><i class="bi bi-exclamation-triangle"></i></div>
                    <div class="stat-value" id="statBajoStock">0</div>
                    <div class="stat-label">Productos Bajo Stock</div>
                    <small class="text-white-50">Requieren atención</small>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header" style="background-color: #1e3a8a; color: white;">
                        <i class="bi bi-bar-chart"></i> Ventas de los Últimos 7 Días
                    </div>
                    <div class="card-body">
                        <canvas id="chartVentas" height="80"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <i class="bi bi-cash-stack"></i> Estado de Caja
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted d-block">Efectivo en Caja:</small>
                            <h3 class="text-success mb-0" id="efectivoCaja">Q 0.00</h3>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <small class="text-muted d-block">Créditos Pendientes:</small>
                            <h4 class="text-warning mb-0" id="creditosPendientes">Q 0.00</h4>
                        </div>
                        <hr>
                        <div class="mb-0">
                            <small class="text-muted d-block">Apartados Activos:</small>
                            <h4 class="text-info mb-0" id="apartadosActivos">0 apartados</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mt-3">
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header" style="background-color: #1e3a8a; color: white;">
                        <i class="bi bi-trophy"></i> Top 5 Productos Más Vendidos (Mes)
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background-color: #1e3a8a; color: white;">
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody id="productosTop">
                                <tr><td colspan="3" class="text-center py-4"><div class="spinner-border spinner-border-sm"></div></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header" style="background-color: #1e3a8a; color: white;">
                        <i class="bi bi-calendar-month"></i> Resumen del Mes
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                            <div>
                                <small class="text-muted d-block">Total Ventas</small>
                                <h4 class="mb-0 text-success" id="totalVentasMes">Q 0.00</h4>
                            </div>
                            <div class="text-end">
                                <small class="text-muted d-block">vs Mes Anterior</small>
                                <span class="badge" id="compVentasMes">-</span>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                            <div>
                                <small class="text-muted d-block">Trabajos Completados</small>
                                <h4 class="mb-0 text-info" id="trabajosCompletados">0</h4>
                            </div>
                            <div class="text-end">
                                <small class="text-muted d-block">Pendientes</small>
                                <span class="badge bg-warning" id="trabajosPendientes">0</span>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <div>
                                <small class="text-muted d-block">Valor de Inventario</small>
                                <h4 class="mb-0 text-primary" id="valorInventario">Q 0</h4>
                            </div>
                            <div class="text-end">
                                <small class="text-muted d-block">Productos Bajos</small>
                                <span class="badge bg-danger" id="productosBajos">0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mt-4">
            <div class="card-header"><i class="bi bi-speedometer2"></i> Acceso Rápido a Reportes</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <a href="ventas.php" class="btn btn-outline-primary w-100 py-3">
                            <i class="bi bi-cart-check d-block mb-2" style="font-size: 2em;"></i>
                            Reporte de Ventas
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="inventario.php" class="btn btn-outline-success w-100 py-3">
                            <i class="bi bi-box-seam d-block mb-2" style="font-size: 2em;"></i>
                            Reporte de Inventario
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="taller.php" class="btn btn-outline-warning w-100 py-3">
                            <i class="bi bi-tools d-block mb-2" style="font-size: 2em;"></i>
                            Reporte de Taller
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="financiero.php" class="btn btn-outline-danger w-100 py-3">
                            <i class="bi bi-cash-stack d-block mb-2" style="font-size: 2em;"></i>
                            Reporte Financiero
                        </a>
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
.stat-card.dorado { --card-color-start: #d4af37; --card-color-end: #b8941f; }
.stat-card.azul { --card-color-start: #1e3a8a; --card-color-end: #1e40af; }
.stat-card.verde { --card-color-start: #22c55e; --card-color-end: #16a34a; }
.stat-card.rojo { --card-color-start: #ef4444; --card-color-end: #dc2626; }
.stat-icon { width: 50px; height: 50px; border-radius: 10px; background: rgba(255, 255, 255, 0.2); display: flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 15px; color: white; }
.stat-value { font-size: 1.75rem; font-weight: 700; margin: 10px 0; color: white !important; }
.stat-label { font-size: 0.85rem; opacity: 0.95; font-weight: 500; color: white !important; }
.stat-card small { color: rgba(255, 255, 255, 0.8) !important; }
.card-body { padding: 25px; }
table thead th { font-weight: 600; font-size: 0.85rem; text-transform: uppercase; padding: 12px; }
table tbody td { padding: 12px; vertical-align: middle; }
@media (max-width: 575.98px) {
    .main-content { padding: 15px 10px; }
    .page-header h1 { font-size: 1.5rem; }
    .stat-card { padding: 15px; }
    .stat-value { font-size: 1.5rem; }
    .card-body { padding: 15px; }
}
@media (min-width: 576px) and (max-width: 767.98px) { .main-content { padding: 18px 15px; } }
@media (min-width: 992px) { .main-content { padding: 25px 30px; } }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
let chartVentas = null;
let periodoActual = 'hoy';

document.addEventListener('DOMContentLoaded', function() {
    cargarDashboard();
});

function cambiarPeriodo(periodo) {
    periodoActual = periodo;
    document.querySelectorAll('.btn-group .btn').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    cargarDashboard();
}

function cargarDashboard() {
    /* TODO FASE 5: Descomentar
    fetch('<?php echo BASE_URL; ?>api/reportes/dashboard.php?periodo=' + periodoActual)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                actualizarEstadisticas(data.data.estadisticas);
                actualizarGrafica(data.data.ventas_por_dia);
                actualizarProductosTop(data.data.productos_top);
                actualizarResumen(data.data);
            }
        })
        .catch(error => console.error('Error:', error));
    */
    
    setTimeout(() => {
        document.getElementById('loadingState').style.display = 'none';
        document.getElementById('mainContent').style.display = 'block';
        inicializarGrafica();
        mostrarMensajeDesarrollo();
    }, 1500);
}

function actualizarEstadisticas(stats) {
    document.getElementById('statVentasHoy').textContent = 'Q ' + formatearMoneda(stats.ventas_hoy || 0).split('.')[0];
    document.getElementById('statClientes').textContent = stats.clientes_atendidos_hoy || 0;
    document.getElementById('statTrabajos').textContent = stats.trabajos_completados_mes || 0;
    document.getElementById('statBajoStock').textContent = stats.productos_bajo_stock || 0;
    
    document.getElementById('compVentasHoy').textContent = stats.comparativa_ventas || '-';
    document.getElementById('compProductos').textContent = (stats.productos_vendidos_hoy || 0) + ' productos';
    document.getElementById('compTrabajos').textContent = (stats.trabajos_pendientes || 0) + ' pendientes';
    
    document.getElementById('efectivoCaja').textContent = 'Q ' + formatearMoneda(stats.efectivo_caja || 0);
    document.getElementById('creditosPendientes').textContent = 'Q ' + formatearMoneda(stats.creditos_pendientes || 0);
    document.getElementById('apartadosActivos').textContent = (stats.apartados_activos || 0) + ' apartados';
}

function actualizarGrafica(ventas) {
    const labels = ventas.map(v => formatearFecha(v.fecha));
    const data = ventas.map(v => parseFloat(v.total));
    
    if (chartVentas) chartVentas.destroy();
    
    const ctx = document.getElementById('chartVentas').getContext('2d');
    chartVentas = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Ventas (Q)',
                data: data,
                backgroundColor: 'rgba(212, 175, 55, 0.7)',
                borderColor: 'rgba(212, 175, 55, 1)',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: function(value) { return 'Q ' + value.toLocaleString(); } }
                }
            }
        }
    });
}

function actualizarProductosTop(productos) {
    const tbody = document.getElementById('productosTop');
    
    if (productos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" class="text-center py-4 text-muted">Sin datos</td></tr>';
        return;
    }
    
    let html = '';
    productos.forEach((p, i) => {
        html += `
            <tr>
                <td><span class="badge bg-primary me-2">${i + 1}</span> ${p.nombre}</td>
                <td>${p.cantidad}</td>
                <td class="fw-bold text-success">Q ${formatearMoneda(p.total)}</td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

function actualizarResumen(data) {
    document.getElementById('totalVentasMes').textContent = 'Q ' + formatearMoneda(data.resumen_mes?.ventas || 0);
    document.getElementById('trabajosCompletados').textContent = data.resumen_mes?.trabajos_completados || 0;
    document.getElementById('trabajosPendientes').textContent = data.resumen_mes?.trabajos_pendientes || 0;
    document.getElementById('valorInventario').textContent = 'Q ' + formatearMoneda(data.resumen_mes?.inventario_valor || 0).split('.')[0];
    document.getElementById('productosBajos').textContent = data.resumen_mes?.productos_bajos || 0;
    
    const comp = data.resumen_mes?.comparativa_mes || 0;
    const badge = document.getElementById('compVentasMes');
    badge.textContent = (comp >= 0 ? '+' : '') + comp + '%';
    badge.className = comp >= 0 ? 'badge bg-success' : 'badge bg-danger';
}

function inicializarGrafica() {
    const ctx = document.getElementById('chartVentas').getContext('2d');
    chartVentas = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['', '', '', '', '', '', ''],
            datasets: [{
                label: 'Ventas (Q)',
                data: [0, 0, 0, 0, 0, 0, 0],
                backgroundColor: 'rgba(212, 175, 55, 0.7)',
                borderColor: 'rgba(212, 175, 55, 1)',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: function(value) { return 'Q ' + value.toLocaleString(); } }
                }
            }
        }
    });
}

function mostrarMensajeDesarrollo() {
    document.getElementById('productosTop').innerHTML = '<tr><td colspan="3" class="text-center py-4 text-muted">MODO DESARROLLO: Esperando API</td></tr>';
}

function formatearMoneda(monto) {
    return parseFloat(monto).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function formatearFecha(fecha) {
    const d = new Date(fecha);
    return d.toLocaleDateString('es-GT', { day: '2-digit', month: '2-digit' });
}
</script>

<?php include '../../includes/footer.php'; ?>