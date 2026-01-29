<?php
/**
 * ================================================
 * MÓDULO REPORTES - REPORTE DE VENTAS
 * ================================================
 * 
 * TODO FASE 5: Conectar con API
 * GET /api/reportes/ventas.php
 * 
 * Parámetros: fecha_inicio, fecha_fin, sucursal_id, vendedor_id
 * Respuesta: { resumen, por_vendedor, por_sucursal, por_forma_pago, por_categoria }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();

$titulo_pagina = 'Reporte de Ventas';
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="container-fluid main-content">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="dashboard.php"><i class="bi bi-graph-up"></i> Reportes</a></li>
            <li class="breadcrumb-item active">Ventas</li>
        </ol>
    </nav>

    <div class="page-header mb-4">
        <div class="row align-items-center g-3">
            <div class="col-md-6">
                <h1 class="mb-2"><i class="bi bi-cart-check"></i> Reporte de Ventas</h1>
                <p class="text-muted mb-0">Análisis detallado de ventas</p>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                    <button class="btn btn-success" onclick="exportarExcel()">
                        <i class="bi bi-file-earmark-excel"></i> <span class="d-none d-sm-inline">Excel</span>
                    </button>
                    <button class="btn btn-secondary" onclick="imprimir()">
                        <i class="bi bi-printer"></i> <span class="d-none d-sm-inline">Imprimir</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Fecha Desde</label>
                    <input type="date" class="form-control" id="fechaInicio">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha Hasta</label>
                    <input type="date" class="form-control" id="fechaFin">
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
                    <label class="form-label">Vendedor</label>
                    <select class="form-select" id="filterVendedor">
                        <option value="">Todos</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label d-none d-md-block">&nbsp;</label>
                    <button class="btn btn-primary w-100" onclick="aplicarFiltros()">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="loadingState" class="text-center py-5">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
        <p class="mt-3 text-muted">Cargando reporte de ventas...</p>
    </div>

    <div id="mainContent" style="display: none;">
        <div class="row g-3 mb-4">
            <div class="col-lg-4">
                <div class="stat-card dorado">
                    <div class="stat-icon"><i class="bi bi-cash-stack"></i></div>
                    <div class="stat-value" id="statTotal">Q 0.00</div>
                    <div class="stat-label">Total en Ventas</div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="stat-card azul">
                    <div class="stat-icon"><i class="bi bi-receipt"></i></div>
                    <div class="stat-value" id="statTransacciones">0</div>
                    <div class="stat-label">Total de Transacciones</div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="stat-card verde">
                    <div class="stat-icon"><i class="bi bi-calculator"></i></div>
                    <div class="stat-value" id="statTicket">Q 0.00</div>
                    <div class="stat-label">Ticket Promedio</div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-4">
                <div class="card border-warning shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <i class="bi bi-credit-card"></i> Ventas a Crédito
                    </div>
                    <div class="card-body text-center">
                        <h2 class="text-warning mb-0" id="statCredito">Q 0.00</h2>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-info shadow-sm">
                    <div class="card-header bg-info text-white">
                        <i class="bi bi-clock-history"></i> Ventas Apartado
                    </div>
                    <div class="card-body text-center">
                        <h2 class="text-info mb-0" id="statApartado">Q 0.00</h2>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-danger shadow-sm">
                    <div class="card-header bg-danger text-white">
                        <i class="bi bi-percent"></i> Descuentos Aplicados
                    </div>
                    <div class="card-body text-center">
                        <h2 class="text-danger mb-0" id="statDescuentos">Q 0.00</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header" style="background-color: #1e3a8a; color: white;">
                        <i class="bi bi-person-badge"></i> Ventas por Vendedor
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background-color: #1e3a8a; color: white;">
                                <tr>
                                    <th>Vendedor</th>
                                    <th>Ventas</th>
                                    <th>Total</th>
                                    <th>Promedio</th>
                                </tr>
                            </thead>
                            <tbody id="vendedoresBody">
                                <tr><td colspan="4" class="text-center py-4"><div class="spinner-border spinner-border-sm"></div></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header" style="background-color: #1e3a8a; color: white;">
                        <i class="bi bi-building"></i> Ventas por Sucursal
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background-color: #1e3a8a; color: white;">
                                <tr>
                                    <th>Sucursal</th>
                                    <th>Ventas</th>
                                    <th>Total</th>
                                    <th>%</th>
                                </tr>
                            </thead>
                            <tbody id="sucursalesBody">
                                <tr><td colspan="4" class="text-center py-4"><div class="spinner-border spinner-border-sm"></div></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mt-4">
            <div class="card-header" style="background-color: #1e3a8a; color: white;">
                <i class="bi bi-wallet2"></i> Ventas por Forma de Pago
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-lg-6">
                        <canvas id="chartFormasPago"></canvas>
                    </div>
                    <div class="col-lg-6">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Forma de Pago</th>
                                        <th>Cantidad</th>
                                        <th>Total</th>
                                        <th>%</th>
                                    </tr>
                                </thead>
                                <tbody id="formasPagoBody">
                                    <tr><td colspan="4" class="text-center py-4"><div class="spinner-border spinner-border-sm"></div></td></tr>
                                </tbody>
                            </table>
                        </div>
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
.stat-icon { width: 50px; height: 50px; border-radius: 10px; background: rgba(255, 255, 255, 0.2); display: flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 15px; color: white; }
.stat-value { font-size: 1.75rem; font-weight: 700; margin: 10px 0; color: white !important; }
.stat-label { font-size: 0.85rem; opacity: 0.95; font-weight: 500; color: white !important; }
table thead th { font-weight: 600; font-size: 0.85rem; text-transform: uppercase; padding: 12px; }
table tbody td { padding: 12px; vertical-align: middle; }
@media (max-width: 575.98px) {
    .main-content { padding: 15px 10px; }
    .page-header h1 { font-size: 1.5rem; }
    .stat-card { padding: 15px; }
    .stat-value { font-size: 1.5rem; }
    h2 { font-size: 1.5rem; }
}
@media (min-width: 576px) and (max-width: 767.98px) { .main-content { padding: 18px 15px; } }
@media (min-width: 992px) { .main-content { padding: 25px 30px; } }
@media (max-width: 767.98px) { .btn, .form-control, .form-select { min-height: 44px; } }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
let chartFormasPago = null;

document.addEventListener('DOMContentLoaded', function() {
    cargarReporteVentas();
    cargarVendedores();
});

function cargarReporteVentas() {
    /* TODO FASE 5: Descomentar
    const params = new URLSearchParams({
        fecha_inicio: document.getElementById('fechaInicio').value,
        fecha_fin: document.getElementById('fechaFin').value,
        sucursal_id: document.getElementById('filterSucursal').value,
        vendedor_id: document.getElementById('filterVendedor').value
    });
    
    fetch('<?php echo BASE_URL; ?>api/reportes/ventas.php?' + params)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                actualizarResumen(data.data.resumen);
                renderizarVendedores(data.data.por_vendedor);
                renderizarSucursales(data.data.por_sucursal);
                renderizarFormasPago(data.data.por_forma_pago);
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

function cargarVendedores() {
    /* TODO FASE 5: Descomentar
    fetch('<?php echo BASE_URL; ?>api/empleados/lista.php?cargo=vendedor')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('filterVendedor');
                data.data.forEach(v => {
                    const option = document.createElement('option');
                    option.value = v.id;
                    option.textContent = v.nombre;
                    select.appendChild(option);
                });
            }
        });
    */
}

function actualizarResumen(resumen) {
    document.getElementById('statTotal').textContent = 'Q ' + formatearMoneda(resumen.total_ventas || 0);
    document.getElementById('statTransacciones').textContent = resumen.total_transacciones || 0;
    document.getElementById('statTicket').textContent = 'Q ' + formatearMoneda(resumen.ticket_promedio || 0);
    document.getElementById('statCredito').textContent = 'Q ' + formatearMoneda(resumen.ventas_credito || 0);
    document.getElementById('statApartado').textContent = 'Q ' + formatearMoneda(resumen.ventas_apartado || 0);
    document.getElementById('statDescuentos').textContent = 'Q ' + formatearMoneda(resumen.descuentos_aplicados || 0);
}

function renderizarVendedores(vendedores) {
    const tbody = document.getElementById('vendedoresBody');
    
    if (vendedores.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted">Sin datos</td></tr>';
        return;
    }
    
    let html = '';
    vendedores.forEach(v => {
        const promedio = v.ventas > 0 ? parseFloat(v.total) / parseInt(v.ventas) : 0;
        html += `
            <tr>
                <td class="fw-bold">${v.nombre}</td>
                <td>${v.ventas}</td>
                <td class="fw-bold text-success">Q ${formatearMoneda(v.total)}</td>
                <td class="text-muted">Q ${formatearMoneda(promedio)}</td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

function renderizarSucursales(sucursales) {
    const tbody = document.getElementById('sucursalesBody');
    
    if (sucursales.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted">Sin datos</td></tr>';
        return;
    }
    
    const totalGeneral = sucursales.reduce((sum, s) => sum + parseFloat(s.total), 0);
    let html = '';
    
    sucursales.forEach(s => {
        const porcentaje = totalGeneral > 0 ? ((parseFloat(s.total) / totalGeneral) * 100).toFixed(1) : 0;
        html += `
            <tr>
                <td class="fw-bold">${s.nombre}</td>
                <td>${s.ventas}</td>
                <td class="fw-bold text-success">Q ${formatearMoneda(s.total)}</td>
                <td>${porcentaje}%</td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

function renderizarFormasPago(formas) {
    const tbody = document.getElementById('formasPagoBody');
    
    if (formas.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted">Sin datos</td></tr>';
        return;
    }
    
    const totalGeneral = formas.reduce((sum, f) => sum + parseFloat(f.total), 0);
    let html = '';
    
    formas.forEach(f => {
        const porcentaje = totalGeneral > 0 ? ((parseFloat(f.total) / totalGeneral) * 100).toFixed(1) : 0;
        html += `
            <tr>
                <td>${f.forma}</td>
                <td>${f.cantidad}</td>
                <td class="fw-bold">Q ${formatearMoneda(f.total)}</td>
                <td>${porcentaje}%</td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    
    actualizarGrafica(formas);
}

function actualizarGrafica(formas) {
    const labels = formas.map(f => f.forma);
    const data = formas.map(f => parseFloat(f.total));
    
    if (chartFormasPago) chartFormasPago.destroy();
    
    const ctx = document.getElementById('chartFormasPago').getContext('2d');
    chartFormasPago = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: [
                    'rgba(212, 175, 55, 0.8)',
                    'rgba(30, 58, 138, 0.8)',
                    'rgba(34, 197, 94, 0.8)',
                    'rgba(239, 68, 68, 0.8)',
                    'rgba(234, 179, 8, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } }
        }
    });
}

function inicializarGrafica() {
    const ctx = document.getElementById('chartFormasPago').getContext('2d');
    chartFormasPago = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: [],
            datasets: [{ data: [], backgroundColor: [] }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } }
        }
    });
}

function mostrarMensajeDesarrollo() {
    document.getElementById('vendedoresBody').innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted">MODO DESARROLLO: Esperando API</td></tr>';
    document.getElementById('sucursalesBody').innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted">MODO DESARROLLO: Esperando API</td></tr>';
    document.getElementById('formasPagoBody').innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted">MODO DESARROLLO: Esperando API</td></tr>';
}

function aplicarFiltros() { cargarReporteVentas(); }

function exportarExcel() { alert('MODO DESARROLLO: Exportar a Excel - Pendiente implementar'); }

function imprimir() { window.print(); }

function formatearMoneda(monto) {
    return parseFloat(monto).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}
</script>

<?php include '../../includes/footer.php'; ?>