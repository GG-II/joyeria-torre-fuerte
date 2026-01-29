<?php
/**
 * ================================================
 * MÓDULO REPORTES - REPORTE DE TALLER
 * ================================================
 * 
 * TODO FASE 5: Conectar con API
 * GET /api/reportes/taller.php
 * 
 * Parámetros: fecha_inicio, fecha_fin, orfebre_id, estado
 * Respuesta: { resumen, por_tipo, por_orfebre, por_estado, trabajos_criticos }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();

$titulo_pagina = 'Reporte de Taller';
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="container-fluid main-content">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="dashboard.php"><i class="bi bi-graph-up"></i> Reportes</a></li>
            <li class="breadcrumb-item active">Taller</li>
        </ol>
    </nav>

    <div class="page-header mb-4">
        <div class="row align-items-center g-3">
            <div class="col-md-6">
                <h1 class="mb-2"><i class="bi bi-tools"></i> Reporte de Taller</h1>
                <p class="text-muted mb-0">Análisis de trabajos y desempeño</p>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                    <input type="date" class="form-control" id="fechaInicio" style="max-width: 160px;">
                    <input type="date" class="form-control" id="fechaFin" style="max-width: 160px;">
                    <select class="form-select" id="filterOrfebre" style="max-width: 180px;">
                        <option value="">Todos los orfebres</option>
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
        <p class="mt-3 text-muted">Cargando reporte de taller...</p>
    </div>

    <div id="mainContent" style="display: none;">
        <div class="row g-3 mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="stat-card azul">
                    <div class="stat-icon"><i class="bi bi-clipboard-check"></i></div>
                    <div class="stat-value" id="statTrabajos">0</div>
                    <div class="stat-label">Trabajos del Mes</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card verde">
                    <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
                    <div class="stat-value" id="statCompletados">0</div>
                    <div class="stat-label">Completados</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card amarillo">
                    <div class="stat-icon"><i class="bi bi-clock-history"></i></div>
                    <div class="stat-value" id="statPendientes">0</div>
                    <div class="stat-label">Pendientes</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card dorado">
                    <div class="stat-icon"><i class="bi bi-cash"></i></div>
                    <div class="stat-value" id="statIngresos">Q 0</div>
                    <div class="stat-label">Ingresos del Mes</div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-4 col-md-6">
                <div class="card border-danger shadow-sm">
                    <div class="card-header bg-danger text-white">
                        <i class="bi bi-exclamation-triangle"></i> Trabajos Atrasados
                    </div>
                    <div class="card-body text-center">
                        <h2 class="text-danger mb-0" id="statAtrasados">0</h2>
                        <small class="text-muted">Requieren atención</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card border-info shadow-sm">
                    <div class="card-header bg-info text-white">
                        <i class="bi bi-calculator"></i> Ticket Promedio
                    </div>
                    <div class="card-body text-center">
                        <h2 class="text-info mb-0" id="statTicket">Q 0</h2>
                        <small class="text-muted">Por trabajo</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card border-success shadow-sm">
                    <div class="card-header bg-success text-white">
                        <i class="bi bi-percent"></i> Tasa de Completación
                    </div>
                    <div class="card-body text-center">
                        <h2 class="text-success mb-0" id="statTasa">0%</h2>
                        <small class="text-muted">Del total</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header" style="background-color: #1e3a8a; color: white;">
                        <i class="bi bi-hammer"></i> Trabajos por Tipo
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background-color: #1e3a8a; color: white;">
                                <tr>
                                    <th>Tipo de Trabajo</th>
                                    <th>Cantidad</th>
                                    <th>Total</th>
                                    <th>Promedio</th>
                                </tr>
                            </thead>
                            <tbody id="tiposBody">
                                <tr><td colspan="4" class="text-center py-4"><div class="spinner-border spinner-border-sm"></div></td></tr>
                            </tbody>
                            <tfoot class="table-light" id="tiposFoot" style="display: none;">
                                <tr>
                                    <td class="fw-bold">TOTAL</td>
                                    <td class="fw-bold" id="totalCantidadTipos">0</td>
                                    <td class="fw-bold text-success" id="totalTipos">Q 0.00</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header" style="background-color: #1e3a8a; color: white;">
                        <i class="bi bi-person-workspace"></i> Desempeño por Orfebre
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background-color: #1e3a8a; color: white;">
                                <tr>
                                    <th>Orfebre</th>
                                    <th>Trabajos</th>
                                    <th>Completados</th>
                                    <th>% Completado</th>
                                </tr>
                            </thead>
                            <tbody id="orfebresBody">
                                <tr><td colspan="4" class="text-center py-4"><div class="spinner-border spinner-border-sm"></div></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mt-3">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <i class="bi bi-exclamation-circle"></i> Trabajos Críticos (Atrasados o Próximos a Vencer)
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background-color: #eab308; color: #1a1a1a;">
                                <tr>
                                    <th>Código</th>
                                    <th>Cliente</th>
                                    <th>Tipo</th>
                                    <th>Orfebre</th>
                                    <th>Entrega Prometida</th>
                                    <th>Días</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody id="criticosBody">
                                <tr><td colspan="7" class="text-center py-4"><div class="spinner-border spinner-border-sm"></div></td></tr>
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
.stat-card.verde { --card-color-start: #22c55e; --card-color-end: #16a34a; }
.stat-card.amarillo { --card-color-start: #eab308; --card-color-end: #ca8a04; }
.stat-card.dorado { --card-color-start: #d4af37; --card-color-end: #b8941f; }
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    cargarReporteTaller();
    cargarOrfebres();
});

function cargarReporteTaller() {
    /* TODO FASE 5: Descomentar
    const params = new URLSearchParams({
        fecha_inicio: document.getElementById('fechaInicio').value,
        fecha_fin: document.getElementById('fechaFin').value,
        orfebre_id: document.getElementById('filterOrfebre').value
    });
    
    fetch('<?php echo BASE_URL; ?>api/reportes/taller.php?' + params)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                actualizarResumen(data.data.resumen);
                renderizarTipos(data.data.por_tipo);
                renderizarOrfebres(data.data.por_orfebre);
                renderizarCriticos(data.data.trabajos_criticos);
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

function cargarOrfebres() {
    /* TODO FASE 5: Descomentar
    fetch('<?php echo BASE_URL; ?>api/empleados/lista.php?cargo=orfebre')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('filterOrfebre');
                data.data.forEach(o => {
                    const option = document.createElement('option');
                    option.value = o.id;
                    option.textContent = o.nombre;
                    select.appendChild(option);
                });
            }
        });
    */
}

function actualizarResumen(resumen) {
    document.getElementById('statTrabajos').textContent = resumen.trabajos_mes || 0;
    document.getElementById('statCompletados').textContent = resumen.completados || 0;
    document.getElementById('statPendientes').textContent = resumen.pendientes || 0;
    document.getElementById('statIngresos').textContent = 'Q ' + formatearMoneda(resumen.ingresos_mes || 0).split('.')[0];
    document.getElementById('statAtrasados').textContent = resumen.trabajos_atrasados || 0;
    document.getElementById('statTicket').textContent = 'Q ' + formatearMoneda(resumen.ticket_promedio || 0).split('.')[0];
    
    const tasa = resumen.trabajos_mes > 0 ? ((resumen.completados / resumen.trabajos_mes) * 100).toFixed(1) : 0;
    document.getElementById('statTasa').textContent = tasa + '%';
}

function renderizarTipos(tipos) {
    const tbody = document.getElementById('tiposBody');
    
    if (tipos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted">Sin datos</td></tr>';
        return;
    }
    
    const totalMonto = tipos.reduce((sum, t) => sum + parseFloat(t.total), 0);
    const totalCantidad = tipos.reduce((sum, t) => sum + parseInt(t.cantidad), 0);
    let html = '';
    
    tipos.forEach(t => {
        const promedio = t.cantidad > 0 ? parseFloat(t.total) / parseInt(t.cantidad) : 0;
        html += `
            <tr>
                <td>${t.tipo}</td>
                <td>${t.cantidad}</td>
                <td class="fw-bold text-success">Q ${formatearMoneda(t.total)}</td>
                <td class="text-muted">Q ${formatearMoneda(promedio)}</td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    document.getElementById('totalCantidadTipos').textContent = totalCantidad;
    document.getElementById('totalTipos').textContent = 'Q ' + formatearMoneda(totalMonto);
    document.getElementById('tiposFoot').style.display = 'table-footer-group';
}

function renderizarOrfebres(orfebres) {
    const tbody = document.getElementById('orfebresBody');
    
    if (orfebres.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted">Sin datos</td></tr>';
        return;
    }
    
    let html = '';
    orfebres.forEach(o => {
        const porcentaje = o.trabajos > 0 ? ((o.completados / o.trabajos) * 100).toFixed(1) : 0;
        const badgeColor = porcentaje >= 80 ? 'bg-success' : (porcentaje >= 60 ? 'bg-warning text-dark' : 'bg-danger');
        html += `
            <tr>
                <td class="fw-bold">${o.nombre}</td>
                <td>${o.trabajos}</td>
                <td>${o.completados}</td>
                <td><span class="badge ${badgeColor}">${porcentaje}%</span></td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

function renderizarCriticos(criticos) {
    const tbody = document.getElementById('criticosBody');
    
    if (criticos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No hay trabajos críticos</td></tr>';
        return;
    }
    
    let html = '';
    criticos.forEach(c => {
        const dias = c.dias_restantes;
        const badgeDias = dias < 0 
            ? `<span class="badge bg-danger">Atrasado ${Math.abs(dias)}d</span>`
            : `<span class="badge bg-warning text-dark">Faltan ${dias}d</span>`;
        
        const badgeEstado = c.estado === 'recibido' ? 'bg-warning text-dark' : 'bg-info';
        
        html += `
            <tr>
                <td class="fw-bold">${c.codigo}</td>
                <td>${c.cliente_nombre}</td>
                <td><small>${c.tipo_trabajo}</small></td>
                <td><small>${c.orfebre_nombre}</small></td>
                <td>${formatearFecha(c.fecha_entrega_prometida)}</td>
                <td>${badgeDias}</td>
                <td><span class="badge ${badgeEstado}">${c.estado}</span></td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

function mostrarMensajeDesarrollo() {
    document.getElementById('tiposBody').innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted">MODO DESARROLLO: Esperando API</td></tr>';
    document.getElementById('orfebresBody').innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted">MODO DESARROLLO: Esperando API</td></tr>';
    document.getElementById('criticosBody').innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">MODO DESARROLLO: Esperando API</td></tr>';
}

function aplicarFiltros() { cargarReporteTaller(); }

function exportarExcel() { alert('MODO DESARROLLO: Exportar a Excel - Pendiente implementar'); }

function formatearMoneda(monto) {
    return parseFloat(monto).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function formatearFecha(fecha) {
    const d = new Date(fecha);
    return d.toLocaleDateString('es-GT', { day: '2-digit', month: '2-digit', year: 'numeric' });
}
</script>

<?php include '../../includes/footer.php'; ?>