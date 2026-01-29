<?php
/**
 * ================================================
 * MÓDULO TALLER - VER DETALLES DEL TRABAJO
 * ================================================
 * 
 * TODO FASE 5: Conectar con API
 * GET /api/taller/ver.php?id={trabajo_id}
 * 
 * Respuesta: { trabajo, transferencias }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();

$trabajo_id = $_GET['id'] ?? null;
if (!$trabajo_id) {
    header('Location: lista.php');
    exit;
}

$trabajo = null;
$titulo_pagina = 'Detalles del Trabajo';
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="container-fluid main-content">
    <div id="loadingState" class="text-center py-5">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
        <p class="mt-3 text-muted">Cargando detalles del trabajo...</p>
    </div>

    <div id="mainContent" style="display: none;">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>dashboard.php"><i class="bi bi-house"></i> Dashboard</a></li>
                <li class="breadcrumb-item"><a href="lista.php"><i class="bi bi-tools"></i> Taller</a></li>
                <li class="breadcrumb-item active" id="breadcrumbCodigo">-</li>
            </ol>
        </nav>

        <div class="card mb-4 shadow-sm" style="border-left: 5px solid #d4af37;">
            <div class="card-body">
                <div class="row align-items-center g-3">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-primary text-white me-3 d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; border-radius: 12px;">
                                <i class="bi bi-tools" style="font-size: 28px;"></i>
                            </div>
                            <div>
                                <h2 class="mb-2" id="trabajoCodigo">-</h2>
                                <div class="d-flex flex-wrap gap-3 text-muted">
                                    <span id="trabajoCliente"><i class="bi bi-person"></i> -</span>
                                    <span id="trabajoTelefono"><i class="bi bi-phone"></i> -</span>
                                    <span id="trabajoEstado"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                            <?php if (tiene_permiso('taller', 'editar')): ?>
                            <a href="editar.php?id=<?php echo $trabajo_id; ?>" class="btn btn-warning" id="btnEditar">
                                <i class="bi bi-pencil"></i> <span class="d-none d-sm-inline">Editar</span>
                            </a>
                            <?php endif; ?>
                            <a href="lista.php" class="btn btn-secondary">
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
                        <i class="bi bi-gem"></i> Información de la Pieza
                    </div>
                    <div class="card-body" id="infoPieza">
                        <div class="text-center py-3"><div class="spinner-border spinner-border-sm"></div></div>
                    </div>
                </div>

                <div class="card mb-3 shadow-sm">
                    <div class="card-header bg-success text-white">
                        <i class="bi bi-cash-coin"></i> Información de Pago
                    </div>
                    <div class="card-body" id="infoPago">
                        <div class="text-center py-3"><div class="spinner-border spinner-border-sm"></div></div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header" style="background-color: #1e3a8a; color: white;">
                        <i class="bi bi-calendar-event"></i> Fechas Importantes
                    </div>
                    <div class="card-body" id="infoFechas">
                        <div class="text-center py-3"><div class="spinner-border spinner-border-sm"></div></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card mb-3 shadow-sm">
                    <div class="card-header"><i class="bi bi-file-text"></i> Descripción del Trabajo</div>
                    <div class="card-body" id="infoTrabajo">
                        <div class="text-center py-3"><div class="spinner-border spinner-border-sm"></div></div>
                    </div>
                </div>

                <div class="card mb-3 shadow-sm">
                    <div class="card-header"><i class="bi bi-people"></i> Personal Asignado</div>
                    <div class="card-body" id="infoPersonal">
                        <div class="text-center py-3"><div class="spinner-border spinner-border-sm"></div></div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header">
                        <i class="bi bi-clock-history"></i> <span id="tituloTransferencias">Historial de Transferencias (INMUTABLE)</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background-color: #1e3a8a; color: white;">
                                <tr>
                                    <th>Fecha</th>
                                    <th class="d-none d-md-table-cell">De</th>
                                    <th>Para</th>
                                    <th class="d-none d-lg-table-cell">Estado</th>
                                    <th class="d-none d-xl-table-cell">Nota</th>
                                    <th class="d-none d-xl-table-cell">Registrado por</th>
                                </tr>
                            </thead>
                            <tbody id="transferenciasBody">
                                <tr><td colspan="6" class="text-center py-4"><div class="spinner-border spinner-border-sm"></div></td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <small class="text-muted"><i class="bi bi-info-circle"></i> Este historial es inmutable y sirve como auditoría completa</small>
                    </div>
                </div>

                <div class="card mt-3 shadow-sm" id="accionesCard" style="display: none;">
                    <div class="card-header"><i class="bi bi-gear"></i> Acciones Disponibles</div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <a href="transferir.php?id=<?php echo $trabajo_id; ?>" class="btn btn-warning w-100">
                                    <i class="bi bi-arrow-left-right"></i> Transferir
                                </a>
                            </div>
                            <div class="col-md-4">
                                <button class="btn btn-info w-100" onclick="imprimirOrden()">
                                    <i class="bi bi-printer"></i> Imprimir
                                </button>
                            </div>
                            <div class="col-md-4">
                                <a href="editar.php?id=<?php echo $trabajo_id; ?>" class="btn btn-primary w-100">
                                    <i class="bi bi-pencil"></i> Editar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="errorState" style="display: none;" class="text-center py-5">
        <i class="bi bi-exclamation-triangle text-danger" style="font-size: 48px;"></i>
        <h4 class="mt-3">Error al cargar el trabajo</h4>
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
    cargarDetallesTrabajo();
});

function cargarDetallesTrabajo() {
    const trabajoId = <?php echo $trabajo_id; ?>;
    
    /* TODO FASE 5: Descomentar
    fetch('<?php echo BASE_URL; ?>api/taller/ver.php?id=' + trabajoId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderizarTrabajo(data.data.trabajo);
                renderizarTransferencias(data.data.transferencias);
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

function renderizarTrabajo(trabajo) {
    document.getElementById('loadingState').style.display = 'none';
    document.getElementById('mainContent').style.display = 'block';
    
    document.getElementById('breadcrumbCodigo').textContent = trabajo.codigo;
    document.getElementById('trabajoCodigo').textContent = trabajo.codigo;
    document.getElementById('trabajoCliente').innerHTML = '<i class="bi bi-person"></i> ' + trabajo.cliente_nombre;
    document.getElementById('trabajoTelefono').innerHTML = '<i class="bi bi-phone"></i> ' + trabajo.cliente_telefono;
    document.getElementById('trabajoEstado').innerHTML = getBadgeEstado(trabajo.estado);
    
    const iconosMaterial = { 'oro': '🟡', 'plata': '⚪', 'otro': '⚫' };
    document.getElementById('infoPieza').innerHTML = `
        <div><small class="text-muted d-block">Descripción</small><strong>${trabajo.descripcion_pieza}</strong></div>
        <div><small class="text-muted d-block">Material</small><strong>${iconosMaterial[trabajo.material] || ''} ${trabajo.material.charAt(0).toUpperCase() + trabajo.material.slice(1)}</strong></div>
        <div><small class="text-muted d-block">Peso</small><strong>${trabajo.peso_gramos || 0} gramos</strong></div>
        ${trabajo.largo_cm ? '<div><small class="text-muted d-block">Largo</small><strong>' + trabajo.largo_cm + ' cm</strong></div>' : ''}
        ${trabajo.estilo ? '<div><small class="text-muted d-block">Estilo</small><strong>' + trabajo.estilo + '</strong></div>' : ''}
        <div><small class="text-muted d-block">Piedras</small>${trabajo.con_piedra ? '<span class="badge bg-warning text-dark">Con piedras</span>' : '<span class="badge bg-secondary">Sin piedras</span>'}</div>
    `;
    
    const saldo = parseFloat(trabajo.saldo);
    document.getElementById('infoPago').innerHTML = `
        <div><small class="text-muted d-block">Precio Total</small><h4 class="mb-0 text-success">Q ${formatearMoneda(trabajo.precio_total)}</h4></div>
        <div><small class="text-muted d-block">Anticipo Recibido</small><h5 class="mb-0 text-primary">Q ${formatearMoneda(trabajo.anticipo)}</h5></div>
        <div><small class="text-muted d-block">Saldo Pendiente</small><h4 class="mb-0 ${saldo > 0 ? 'text-danger' : 'text-success'}">Q ${formatearMoneda(saldo)}</h4></div>
    `;
    
    const diasRestantes = calcularDiasRestantes(trabajo.fecha_entrega_prometida);
    let badgeDias = '';
    if (!trabajo.fecha_entrega_real) {
        badgeDias = diasRestantes < 0 
            ? `<span class="badge bg-danger">Atrasado ${Math.abs(diasRestantes)} días</span>`
            : `<span class="badge bg-info">Faltan ${diasRestantes} días</span>`;
    }
    
    document.getElementById('infoFechas').innerHTML = `
        <div><small class="text-muted d-block">Fecha de Recepción</small><strong>${formatearFechaHora(trabajo.fecha_recepcion)}</strong></div>
        <div><small class="text-muted d-block">Entrega Prometida</small><strong>${formatearFecha(trabajo.fecha_entrega_prometida)}</strong><br>${badgeDias}</div>
        ${trabajo.fecha_entrega_real ? '<div><small class="text-muted d-block">Entrega Real</small><strong>' + formatearFechaHora(trabajo.fecha_entrega_real) + '</strong></div>' : ''}
    `;
    
    document.getElementById('infoTrabajo').innerHTML = `
        <div class="mb-3"><h6 class="fw-bold">Tipo de Trabajo:</h6><p class="mb-0"><span class="badge bg-primary">${trabajo.tipo_trabajo.charAt(0).toUpperCase() + trabajo.tipo_trabajo.slice(1).replace('_', ' ')}</span></p></div>
        <div class="mb-3"><h6 class="fw-bold">Detalles:</h6><p class="mb-0">${trabajo.descripcion_trabajo.replace(/\n/g, '<br>')}</p></div>
        ${trabajo.observaciones ? '<div><h6 class="fw-bold">Observaciones:</h6><p class="mb-0 text-muted">' + trabajo.observaciones.replace(/\n/g, '<br>') + '</p></div>' : ''}
    `;
    
    document.getElementById('infoPersonal').innerHTML = `
        <div class="row">
            <div class="col-md-4"><small class="text-muted d-block">Recibió el trabajo:</small><strong>${trabajo.empleado_recibe_nombre}</strong></div>
            <div class="col-md-4"><small class="text-muted d-block">Orfebre actual:</small><strong>${trabajo.empleado_actual_nombre}</strong></div>
            <div class="col-md-4"><small class="text-muted d-block">Entregó:</small><strong>${trabajo.empleado_entrega_nombre || 'Pendiente'}</strong></div>
        </div>
    `;
    
    if (trabajo.estado !== 'entregado') {
        document.getElementById('accionesCard').style.display = 'block';
        document.getElementById('btnEditar').style.display = 'inline-block';
    } else {
        document.getElementById('btnEditar').style.display = 'none';
    }
}

function renderizarTransferencias(transferencias) {
    const tbody = document.getElementById('transferenciasBody');
    
    if (transferencias.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-5 text-muted">No hay transferencias registradas</td></tr>';
        return;
    }
    
    let html = '';
    transferencias.forEach(t => {
        html += `
            <tr>
                <td><small>${formatearFechaHora(t.fecha_transferencia)}</small></td>
                <td class="d-none d-md-table-cell">${t.empleado_origen_nombre}</td>
                <td>${t.empleado_destino_nombre}</td>
                <td class="d-none d-lg-table-cell"><span class="badge bg-info">${t.estado_trabajo_momento.charAt(0).toUpperCase() + t.estado_trabajo_momento.slice(1)}</span></td>
                <td class="d-none d-xl-table-cell"><small class="text-muted">${t.nota || '-'}</small></td>
                <td class="d-none d-xl-table-cell"><small class="text-muted">${t.usuario_registra_nombre}</small></td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    document.getElementById('tituloTransferencias').textContent = `Historial de Transferencias (${transferencias.length})`;
}

function getBadgeEstado(estado) {
    const badges = {
        'recibido': '<span class="badge bg-warning">Recibido</span>',
        'en_proceso': '<span class="badge bg-info">En Proceso</span>',
        'completado': '<span class="badge bg-success">Completado</span>',
        'entregado': '<span class="badge bg-secondary">Entregado</span>',
        'cancelado': '<span class="badge bg-danger">Cancelado</span>'
    };
    return badges[estado] || '';
}

function calcularDiasRestantes(fechaEntrega) {
    const hoy = new Date();
    const entrega = new Date(fechaEntrega);
    return Math.floor((entrega - hoy) / (1000 * 60 * 60 * 24));
}

function formatearMoneda(monto) {
    return parseFloat(monto).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function formatearFecha(fecha) {
    const d = new Date(fecha);
    return d.toLocaleDateString('es-GT', { day: '2-digit', month: '2-digit', year: 'numeric' });
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

function imprimirOrden() { alert('MODO DESARROLLO: Imprimir orden - Pendiente implementar'); }
</script>

<?php include '../../includes/footer.php'; ?>