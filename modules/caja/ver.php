<?php
/**
 * ================================================
 * MÓDULO CAJA - VER DETALLES DE CAJA
 * ================================================
 * 
 * TODO FASE 5: Conectar con API
 * GET /api/caja/ver.php?id={caja_id}
 * 
 * Respuesta: { caja, movimientos, totales }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();

$caja_id = $_GET['id'] ?? null;
if (!$caja_id) {
    header('Location: lista.php');
    exit;
}

$caja = null;
$titulo_pagina = 'Detalles de Caja';
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="container-fluid main-content">
    <div id="loadingState" class="text-center py-5">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
        <p class="mt-3 text-muted">Cargando detalles de la caja...</p>
    </div>

    <div id="mainContent" style="display: none;">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>dashboard.php"><i class="bi bi-house"></i> Dashboard</a></li>
                <li class="breadcrumb-item"><a href="lista.php"><i class="bi bi-cash-stack"></i> Caja</a></li>
                <li class="breadcrumb-item active" id="breadcrumbCaja">Caja #<?php echo $caja_id; ?></li>
            </ol>
        </nav>

        <div class="card mb-4 shadow-sm" style="border-left: 5px solid #d4af37;">
            <div class="card-body">
                <div class="row align-items-center g-3">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-success text-white me-3 d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; border-radius: 12px;">
                                <i class="bi bi-cash-stack" style="font-size: 28px;"></i>
                            </div>
                            <div>
                                <h2 class="mb-2" id="cajaTitulo">Caja #<?php echo $caja_id; ?></h2>
                                <div class="d-flex flex-wrap gap-3 text-muted" id="cajaInfo"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div class="d-flex flex-wrap gap-2 justify-content-md-end" id="botonesAccion">
                            <a href="lista.php" class="btn btn-primary">
                                <i class="bi bi-arrow-left"></i> <span class="d-none d-sm-inline">Volver</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card mb-3 shadow-sm">
                    <div class="card-header" style="background-color: #1e3a8a; color: white;">
                        <i class="bi bi-info-circle"></i> Información General
                    </div>
                    <div class="card-body" id="infoGeneral">
                        <div class="text-center py-3"><div class="spinner-border spinner-border-sm"></div></div>
                    </div>
                </div>

                <div class="card mb-3 shadow-sm">
                    <div class="card-header bg-success text-white">
                        <i class="bi bi-calculator"></i> Resumen de Montos
                    </div>
                    <div class="card-body" id="resumenMontos">
                        <div class="text-center py-3"><div class="spinner-border spinner-border-sm"></div></div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header" style="background-color: #1e3a8a; color: white;">
                        <i class="bi bi-pie-chart"></i> Desglose por Tipo
                    </div>
                    <div class="card-body" id="desgloseTipo">
                        <div class="text-center py-3"><div class="spinner-border spinner-border-sm"></div></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header" style="background-color: #1e3a8a; color: white;">
                        <i class="bi bi-list-ul"></i> <span id="tituloMovimientos">Movimientos de Caja</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background-color: #1e3a8a; color: white;">
                                <tr>
                                    <th>Fecha/Hora</th>
                                    <th class="d-none d-md-table-cell">Tipo</th>
                                    <th>Concepto</th>
                                    <th class="d-none d-lg-table-cell">Usuario</th>
                                    <th class="text-end">Monto</th>
                                </tr>
                            </thead>
                            <tbody id="movimientosBody">
                                <tr><td colspan="5" class="text-center py-4"><div class="spinner-border spinner-border-sm"></div></td></tr>
                            </tbody>
                            <tfoot class="table-light" id="tableFoot" style="display: none;">
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">Total Ingresos:</td>
                                    <td class="text-end fw-bold text-success" id="totalIngresos">+Q 0.00</td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">Total Egresos:</td>
                                    <td class="text-end fw-bold text-danger" id="totalEgresos">-Q 0.00</td>
                                </tr>
                                <tr class="table-success">
                                    <td colspan="4" class="text-end fw-bold">SALDO:</td>
                                    <td class="text-end fw-bold text-success" id="saldoTotal" style="font-size: 1.2em;">Q 0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="errorState" style="display: none;" class="text-center py-5">
        <i class="bi bi-exclamation-triangle text-danger" style="font-size: 48px;"></i>
        <h4 class="mt-3">Error al cargar la caja</h4>
        <p class="text-muted" id="errorMessage">No se pudo cargar la información.</p>
        <a href="lista.php" class="btn btn-primary mt-3"><i class="bi bi-arrow-left"></i> Volver al listado</a>
    </div>
</div>

<style>
.main-content { padding: 20px; min-height: calc(100vh - 120px); }
.shadow-sm { box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08) !important; }
.card-body > div:not(:last-child) { padding-bottom: 12px; margin-bottom: 12px; border-bottom: 1px solid #e5e7eb; }
table thead th { font-weight: 600; font-size: 0.85rem; text-transform: uppercase; padding: 12px; }
table tbody td { padding: 12px; vertical-align: middle; }
.badge { padding: 0.35em 0.65em; font-size: 0.85em; }
@media (max-width: 575.98px) {
    .main-content { padding: 15px 10px; }
    h2 { font-size: 1.5rem; }
    table { font-size: 0.85rem; }
    table thead th, table tbody td { padding: 8px 6px; }
}
@media (min-width: 576px) and (max-width: 767.98px) { .main-content { padding: 18px 15px; } }
@media (min-width: 992px) { .main-content { padding: 25px 30px; } }
@media (max-width: 767.98px) { .btn { min-height: 44px; } }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    cargarDetallesCaja();
});

function cargarDetallesCaja() {
    const cajaId = <?php echo $caja_id; ?>;
    
    /* TODO FASE 5: Descomentar
    fetch('<?php echo BASE_URL; ?>api/caja/ver.php?id=' + cajaId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderizarCaja(data.data.caja, data.data.movimientos, data.data.totales);
            } else {
                mostrarError(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarError('Error al cargar los datos');
        });
    */
    
    setTimeout(() => mostrarError('MODO DESARROLLO: Esperando API'), 1500);
}

function renderizarCaja(caja, movimientos, totales) {
    document.getElementById('loadingState').style.display = 'none';
    document.getElementById('mainContent').style.display = 'block';
    
    document.getElementById('breadcrumbCaja').textContent = 'Caja #' + caja.id;
    document.getElementById('cajaTitulo').textContent = 'Caja #' + caja.id;
    
    const badgeEstado = caja.estado === 'abierta' 
        ? '<span class="badge bg-success">Abierta</span>'
        : '<span class="badge bg-secondary">Cerrada</span>';
    
    document.getElementById('cajaInfo').innerHTML = `
        <span><i class="bi bi-person"></i> ${caja.usuario_nombre}</span>
        <span><i class="bi bi-building"></i> ${caja.sucursal_nombre}</span>
        <span>${badgeEstado}</span>
    `;
    
    const botonesAccion = document.getElementById('botonesAccion');
    if (caja.estado === 'abierta') {
        botonesAccion.innerHTML = `
            <a href="cerrar.php?id=${caja.id}" class="btn btn-danger btn-lg">
                <i class="bi bi-box-arrow-up"></i> <span class="d-none d-sm-inline">Cerrar Caja</span>
            </a>
        ` + botonesAccion.innerHTML;
    } else {
        botonesAccion.innerHTML = `
            <button class="btn btn-secondary" onclick="imprimirArqueo()">
                <i class="bi bi-printer"></i> <span class="d-none d-sm-inline">Imprimir</span>
            </button>
        ` + botonesAccion.innerHTML;
    }
    
    document.getElementById('infoGeneral').innerHTML = `
        <div><small class="text-muted d-block">Apertura:</small><strong>${formatearFechaHora(caja.fecha_apertura)}</strong></div>
        ${caja.fecha_cierre ? `<div><small class="text-muted d-block">Cierre:</small><strong>${formatearFechaHora(caja.fecha_cierre)}</strong></div>` : ''}
        <div><small class="text-muted d-block">Monto Inicial:</small><strong>Q ${formatearMoneda(caja.monto_inicial)}</strong></div>
        <div><small class="text-muted d-block">Movimientos:</small><strong>${movimientos.length} registros</strong></div>
    `;
    
    const efectivoEsperado = parseFloat(caja.monto_inicial) + parseFloat(totales.ingresos) - parseFloat(totales.egresos);
    
    let resumenHTML = `
        <div class="mb-3">
            <div class="d-flex justify-content-between mb-1"><span>Monto Inicial:</span><span class="fw-bold">Q ${formatearMoneda(caja.monto_inicial)}</span></div>
            <div class="d-flex justify-content-between mb-1 text-success"><span>+ Ingresos:</span><span class="fw-bold">Q ${formatearMoneda(totales.ingresos)}</span></div>
            <div class="d-flex justify-content-between mb-1 text-danger"><span>- Egresos:</span><span class="fw-bold">Q ${formatearMoneda(totales.egresos)}</span></div>
            <hr>
            <div class="d-flex justify-content-between"><h5 class="mb-0">Efectivo Esperado:</h5><h5 class="mb-0 text-success">Q ${formatearMoneda(efectivoEsperado)}</h5></div>
        </div>
    `;
    
    if (caja.estado === 'cerrada') {
        const diferencia = parseFloat(caja.diferencia);
        let badgeDif = '';
        if (diferencia === 0) badgeDif = '<span class="badge bg-success">Q 0.00 - Cuadrada</span>';
        else if (diferencia < 0) badgeDif = `<span class="badge bg-danger">-Q ${formatearMoneda(Math.abs(diferencia))} - Faltante</span>`;
        else badgeDif = `<span class="badge bg-warning text-dark">+Q ${formatearMoneda(diferencia)} - Sobrante</span>`;
        
        resumenHTML += `
            <hr>
            <div class="mb-2"><small class="text-muted d-block">Efectivo Real:</small><strong>Q ${formatearMoneda(caja.monto_real)}</strong></div>
            <div><small class="text-muted d-block">Diferencia:</small>${badgeDif}</div>
        `;
    }
    
    document.getElementById('resumenMontos').innerHTML = resumenHTML;
    
    const desglose = {};
    movimientos.forEach(m => {
        if (m.categoria === 'ingreso') {
            if (!desglose[m.tipo_movimiento]) desglose[m.tipo_movimiento] = 0;
            desglose[m.tipo_movimiento] += parseFloat(m.monto);
        }
    });
    
    let desgloseHTML = '';
    for (const [tipo, monto] of Object.entries(desglose)) {
        desgloseHTML += `<div class="d-flex justify-content-between mb-2"><small>${tipo.replace(/_/g, ' ').charAt(0).toUpperCase() + tipo.replace(/_/g, ' ').slice(1)}:</small><strong class="text-success">Q ${formatearMoneda(monto)}</strong></div>`;
    }
    document.getElementById('desgloseTipo').innerHTML = desgloseHTML || '<p class="text-muted mb-0">Sin movimientos</p>';
    
    renderizarMovimientos(movimientos, totales);
}

function renderizarMovimientos(movimientos, totales) {
    const tbody = document.getElementById('movimientosBody');
    
    if (movimientos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-5 text-muted">No hay movimientos registrados</td></tr>';
        return;
    }
    
    let html = '';
    movimientos.forEach(m => {
        const badgeClass = m.categoria === 'ingreso' ? 'bg-success' : 'bg-danger';
        const signo = m.categoria === 'ingreso' ? '+' : '-';
        const colorClass = m.categoria === 'ingreso' ? 'text-success' : 'text-danger';
        
        html += `
            <tr>
                <td><small>${formatearFechaHora(m.fecha_hora)}</small></td>
                <td class="d-none d-md-table-cell"><span class="badge ${badgeClass}">${m.tipo_movimiento.replace(/_/g, ' ').charAt(0).toUpperCase() + m.tipo_movimiento.replace(/_/g, ' ').slice(1)}</span></td>
                <td>
                    ${m.concepto}
                    ${m.referencia_tipo ? `<br><small class="text-muted">Ref: ${m.referencia_tipo} #${m.referencia_id}</small>` : ''}
                </td>
                <td class="d-none d-lg-table-cell"><small class="text-muted">${m.usuario_nombre}</small></td>
                <td class="text-end"><span class="${colorClass} fw-bold">${signo}Q ${formatearMoneda(m.monto)}</span></td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    document.getElementById('totalIngresos').textContent = '+Q ' + formatearMoneda(totales.ingresos);
    document.getElementById('totalEgresos').textContent = '-Q ' + formatearMoneda(totales.egresos);
    document.getElementById('saldoTotal').textContent = 'Q ' + formatearMoneda(totales.ingresos - totales.egresos);
    document.getElementById('tableFoot').style.display = 'table-footer-group';
    document.getElementById('tituloMovimientos').textContent = `Movimientos de Caja (${movimientos.length})`;
}

function formatearMoneda(monto) {
    return parseFloat(monto).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function formatearFechaHora(fecha) {
    const d = new Date(fecha);
    return d.toLocaleDateString('es-GT', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function mostrarError(mensaje) {
    document.getElementById('loadingState').style.display = 'none';
    document.getElementById('errorState').style.display = 'block';
    document.getElementById('errorMessage').textContent = mensaje;
}

function imprimirArqueo() { alert('MODO DESARROLLO: Imprimir arqueo - Pendiente implementar'); }
</script>

<?php include '../../includes/footer.php'; ?>