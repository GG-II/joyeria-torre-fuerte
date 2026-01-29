<?php
/**
 * ================================================
 * MÓDULO CAJA - LISTA DE CAJAS
 * ================================================
 * 
 * TODO FASE 5: Conectar con APIs
 * GET /api/caja/actual.php - Verificar si hay caja abierta
 * GET /api/caja/lista.php - Cargar historial de cajas
 * 
 * Parámetros: fecha_desde, fecha_hasta, sucursal_id, usuario_id
 * Respuesta: { success, data: [...cajas], resumen: {...stats}, caja_actual: {...} }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();
requiere_rol(['administrador', 'dueño', 'cajero']);

$usuario_actual = [
    'id' => $_SESSION['usuario_id'],
    'nombre' => $_SESSION['usuario_nombre'],
    'sucursal_id' => $_SESSION['usuario_sucursal_id'],
    'sucursal_nombre' => $_SESSION['usuario_sucursal_nombre']
];

$titulo_pagina = 'Control de Caja';
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="container-fluid main-content">
    <div class="page-header mb-4">
        <div class="row align-items-center g-3">
            <div class="col-md-6">
                <h1 class="mb-2"><i class="bi bi-cash-stack"></i> Control de Caja</h1>
                <p class="text-muted mb-0">Gestión de apertura, cierre y movimientos de caja</p>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="d-flex flex-wrap gap-2 justify-content-md-end" id="botonesAccion">
                    <a href="abrir.php" class="btn btn-success btn-lg" id="btnAbrir">
                        <i class="bi bi-box-arrow-in-down"></i> <span class="d-none d-sm-inline">Abrir Caja</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div id="alertCajaActual" class="alert alert-success border-start border-success border-5 shadow-sm" style="display: none;">
        <div class="row align-items-center g-3">
            <div class="col-md-8">
                <h4 class="alert-heading mb-2"><i class="bi bi-check-circle"></i> Caja Abierta - Turno Actual</h4>
                <p class="mb-0" id="infoCajaActual"></p>
            </div>
            <div class="col-md-4 text-md-end">
                <h3 class="mb-0 text-success" id="efectivoActual">Q 0.00</h3>
                <small class="text-muted" id="detalleEfectivo"></small>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4" id="estadisticas">
        <div class="col-6 col-md-3">
            <div class="stat-card verde">
                <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
                <div class="stat-value" id="statCuadradas">0</div>
                <div class="stat-label">Cajas Cuadradas</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card rojo">
                <div class="stat-icon"><i class="bi bi-exclamation-triangle"></i></div>
                <div class="stat-value" id="statFaltante">0</div>
                <div class="stat-label">Con Faltante</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card amarillo">
                <div class="stat-icon"><i class="bi bi-plus-circle"></i></div>
                <div class="stat-value" id="statSobrante">0</div>
                <div class="stat-label">Con Sobrante</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card dorado">
                <div class="stat-icon"><i class="bi bi-cash-stack"></i></div>
                <div class="stat-value" id="statTotal">Q 0</div>
                <div class="stat-label">Total Recaudado</div>
            </div>
        </div>
    </div>

    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Fecha Desde</label>
                    <input type="date" class="form-control" id="fechaDesde">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha Hasta</label>
                    <input type="date" class="form-control" id="fechaHasta">
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
                    <label class="form-label">Usuario</label>
                    <select class="form-select" id="filterUsuario">
                        <option value="">Todos</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label d-none d-md-block">&nbsp;</label>
                    <button class="btn btn-secondary w-100" onclick="aplicarFiltros()">
                        <i class="bi bi-funnel"></i> Filtrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header" style="background-color: #1e3a8a; color: white;">
            <i class="bi bi-table"></i> <span id="tituloTabla">Historial de Cajas</span>
        </div>
        
        <div id="loadingTable" class="text-center py-5">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
            <p class="mt-3 text-muted">Cargando historial...</p>
        </div>

        <div id="tableContainer" class="table-responsive" style="display: none;">
            <table class="table table-hover mb-0">
                <thead style="background-color: #1e3a8a; color: white;">
                    <tr>
                        <th>#</th>
                        <th>Apertura</th>
                        <th class="d-none d-md-table-cell">Cierre</th>
                        <th>Usuario</th>
                        <th class="d-none d-lg-table-cell">Sucursal</th>
                        <th class="d-none d-xl-table-cell">Inicial</th>
                        <th class="d-none d-xl-table-cell">Esperado</th>
                        <th>Real</th>
                        <th>Diferencia</th>
                        <th class="d-none d-lg-table-cell">Ventas</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="cajasBody"></tbody>
                <tfoot class="table-light" id="tableFoot" style="display: none;">
                    <tr>
                        <td colspan="5" class="text-end fw-bold">TOTALES:</td>
                        <td class="fw-bold d-none d-xl-table-cell" id="totalInicial">Q 0.00</td>
                        <td class="fw-bold d-none d-xl-table-cell" id="totalEsperado">Q 0.00</td>
                        <td class="fw-bold text-success" id="totalReal">Q 0.00</td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div id="noResults" class="text-center py-5" style="display: none;">
            <i class="bi bi-inbox" style="font-size: 48px; opacity: 0.3;"></i>
            <p class="mt-3 text-muted">No se encontraron cajas</p>
        </div>

        <div class="card-footer" id="tableFooter" style="display: none;">
            <small class="text-muted" id="contadorCajas">Mostrando 0 cajas</small>
        </div>
    </div>
</div>

<style>
.main-content { padding: 20px; min-height: calc(100vh - 120px); }
.page-header h1 { font-size: 1.75rem; font-weight: 600; color: #1a1a1a; }
.shadow-sm { box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08) !important; }
.stat-card { background: white; border-radius: 12px; padding: 15px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08); transition: transform 0.2s ease; border-left: 4px solid; height: 100%; }
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12); }
.stat-card.verde { border-left-color: #22c55e; }
.stat-card.rojo { border-left-color: #ef4444; }
.stat-card.amarillo { border-left-color: #eab308; }
.stat-card.dorado { border-left-color: #d4af37; }
.stat-icon { width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 22px; margin-bottom: 12px; }
.stat-card.verde .stat-icon { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
.stat-card.rojo .stat-icon { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
.stat-card.amarillo .stat-icon { background: rgba(234, 179, 8, 0.1); color: #eab308; }
.stat-card.dorado .stat-icon { background: rgba(212, 175, 55, 0.1); color: #d4af37; }
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
let cajaActual = null;

document.addEventListener('DOMContentLoaded', function() {
    cargarCajaActual();
    cargarCajas();
    cargarUsuarios();
});

function cargarCajaActual() {
    /* TODO FASE 5: Descomentar
    fetch('<?php echo BASE_URL; ?>api/caja/actual.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                cajaActual = data.data;
                mostrarCajaActual(data.data);
            }
        });
    */
}

function mostrarCajaActual(caja) {
    document.getElementById('btnAbrir').style.display = 'none';
    
    const botonesAccion = document.getElementById('botonesAccion');
    botonesAccion.innerHTML = `
        <a href="ver.php?id=${caja.id}" class="btn btn-info btn-lg">
            <i class="bi bi-eye"></i> <span class="d-none d-sm-inline">Ver Caja Actual</span>
        </a>
        <a href="cerrar.php?id=${caja.id}" class="btn btn-danger btn-lg">
            <i class="bi bi-box-arrow-up"></i> <span class="d-none d-sm-inline">Cerrar Caja</span>
        </a>
    `;
    
    const efectivoEsperado = parseFloat(caja.monto_inicial) + parseFloat(caja.ingresos_total || 0) - parseFloat(caja.egresos_total || 0);
    
    document.getElementById('infoCajaActual').innerHTML = `
        <strong>Apertura:</strong> ${formatearFechaHora(caja.fecha_apertura)} | 
        <strong>Monto Inicial:</strong> Q ${formatearMoneda(caja.monto_inicial)} | 
        <strong>Usuario:</strong> <?php echo $usuario_actual['nombre']; ?> | 
        <strong>Sucursal:</strong> <?php echo $usuario_actual['sucursal_nombre']; ?>
    `;
    
    document.getElementById('efectivoActual').textContent = 'Efectivo: Q ' + formatearMoneda(efectivoEsperado);
    document.getElementById('detalleEfectivo').textContent = `(${formatearMoneda(caja.ingresos_total || 0)} ingresos - ${formatearMoneda(caja.egresos_total || 0)} egresos)`;
    document.getElementById('alertCajaActual').style.display = 'block';
}

function cargarCajas() {
    /* TODO FASE 5: Descomentar
    const params = new URLSearchParams({
        fecha_desde: document.getElementById('fechaDesde').value,
        fecha_hasta: document.getElementById('fechaHasta').value,
        sucursal_id: document.getElementById('filterSucursal').value,
        usuario_id: document.getElementById('filterUsuario').value
    });
    
    fetch('<?php echo BASE_URL; ?>api/caja/lista.php?' + params)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderizarCajas(data.data);
                actualizarEstadisticas(data.resumen);
            } else {
                mostrarError(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarError('Error al cargar las cajas');
        });
    */
    
    setTimeout(() => {
        document.getElementById('loadingTable').style.display = 'none';
        document.getElementById('noResults').style.display = 'block';
        document.getElementById('noResults').innerHTML = '<i class="bi bi-database" style="font-size: 48px; opacity: 0.3;"></i><p class="mt-3 text-muted">MODO DESARROLLO: Esperando API</p>';
    }, 1500);
}

function cargarUsuarios() {
    /* TODO FASE 5: Descomentar
    fetch('<?php echo BASE_URL; ?>api/empleados/lista.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('filterUsuario');
                data.data.forEach(u => {
                    const option = document.createElement('option');
                    option.value = u.id;
                    option.textContent = u.nombre;
                    select.appendChild(option);
                });
            }
        });
    */
}

function renderizarCajas(cajas) {
    const tbody = document.getElementById('cajasBody');
    
    if (cajas.length === 0) {
        document.getElementById('loadingTable').style.display = 'none';
        document.getElementById('noResults').style.display = 'block';
        return;
    }
    
    let html = '';
    let totalInicial = 0, totalEsperado = 0, totalReal = 0;
    
    cajas.forEach(c => {
        totalInicial += parseFloat(c.monto_inicial);
        totalEsperado += parseFloat(c.monto_esperado || 0);
        totalReal += parseFloat(c.monto_real || 0);
        
        html += `
            <tr>
                <td class="fw-bold">${c.id}</td>
                <td>
                    <div>${formatearFecha(c.fecha_apertura)}</div>
                    <small class="text-muted">${formatearHora(c.fecha_apertura)}</small>
                </td>
                <td class="d-none d-md-table-cell">
                    ${c.fecha_cierre ? `<div>${formatearFecha(c.fecha_cierre)}</div><small class="text-muted">${formatearHora(c.fecha_cierre)}</small>` : '<span class="badge bg-warning">Abierta</span>'}
                </td>
                <td>${c.usuario_nombre}</td>
                <td class="d-none d-lg-table-cell">${c.sucursal_nombre}</td>
                <td class="d-none d-xl-table-cell">Q ${formatearMoneda(c.monto_inicial)}</td>
                <td class="d-none d-xl-table-cell">Q ${formatearMoneda(c.monto_esperado || 0)}</td>
                <td class="fw-bold">Q ${formatearMoneda(c.monto_real || 0)}</td>
                <td>${getBadgeDiferencia(c.diferencia)}</td>
                <td class="d-none d-lg-table-cell"><span class="badge bg-info">${c.total_ventas || 0}</span></td>
                <td class="text-center">
                    <div class="btn-group">
                        <a href="ver.php?id=${c.id}" class="btn btn-sm btn-info" title="Ver"><i class="bi bi-eye"></i></a>
                        <button class="btn btn-sm btn-secondary" onclick="imprimirArqueo(${c.id})" title="Imprimir"><i class="bi bi-printer"></i></button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    document.getElementById('totalInicial').textContent = 'Q ' + formatearMoneda(totalInicial);
    document.getElementById('totalEsperado').textContent = 'Q ' + formatearMoneda(totalEsperado);
    document.getElementById('totalReal').textContent = 'Q ' + formatearMoneda(totalReal);
    
    document.getElementById('loadingTable').style.display = 'none';
    document.getElementById('tableContainer').style.display = 'block';
    document.getElementById('tableFoot').style.display = 'table-footer-group';
    document.getElementById('tableFooter').style.display = 'block';
    document.getElementById('contadorCajas').textContent = `Mostrando ${cajas.length} cajas`;
    document.getElementById('tituloTabla').textContent = `Historial de Cajas (${cajas.length})`;
}

function actualizarEstadisticas(resumen) {
    document.getElementById('statCuadradas').textContent = resumen.cuadradas || 0;
    document.getElementById('statFaltante').textContent = resumen.faltante || 0;
    document.getElementById('statSobrante').textContent = resumen.sobrante || 0;
    document.getElementById('statTotal').textContent = 'Q ' + formatearMoneda(resumen.total_recaudado || 0).split('.')[0];
}

function getBadgeDiferencia(diferencia) {
    const diff = parseFloat(diferencia);
    if (diff === 0) return '<span class="badge bg-success">Q 0.00</span>';
    if (diff < 0) return `<span class="badge bg-danger">-Q ${formatearMoneda(Math.abs(diff))}</span>`;
    return `<span class="badge bg-warning text-dark">+Q ${formatearMoneda(diff)}</span>`;
}

function aplicarFiltros() { cargarCajas(); }

function imprimirArqueo(id) { alert('MODO DESARROLLO: Imprimir arqueo #' + id); }

function formatearMoneda(monto) {
    return parseFloat(monto).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function formatearFecha(fecha) {
    const d = new Date(fecha);
    return d.toLocaleDateString('es-GT', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function formatearHora(fecha) {
    const d = new Date(fecha);
    return d.toLocaleTimeString('es-GT', { hour: '2-digit', minute: '2-digit' });
}

function formatearFechaHora(fecha) {
    return formatearFecha(fecha) + ' ' + formatearHora(fecha);
}

function mostrarError(mensaje) {
    document.getElementById('loadingTable').style.display = 'none';
    document.getElementById('noResults').style.display = 'block';
    document.getElementById('noResults').innerHTML = `<i class="bi bi-exclamation-triangle text-danger" style="font-size: 48px;"></i><p class="mt-3 text-danger">${mensaje}</p>`;
}
</script>

<?php include '../../includes/footer.php'; ?>