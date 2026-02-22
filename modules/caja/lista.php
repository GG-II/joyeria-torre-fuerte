<?php
/**
 * ================================================
 * MÓDULO CAJA - CONTROL DE CAJA
 * ================================================
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();

require_once '../../includes/header.php';
require_once '../../includes/navbar.php';
?>

<div class="container-fluid px-4 py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-1"><i class="bi bi-cash-coin"></i> Control de Caja</h2>
            <p class="text-muted mb-0">Gestión de apertura, cierre y movimientos de caja</p>
        </div>
        <div>
            <a href="registrar_movimiento.php" class="btn btn-info me-2">
                <i class="bi bi-plus-circle"></i> Registrar Movimiento
            </a>
            <button type="button" class="btn btn-success" id="btnAbrirCaja" data-bs-toggle="modal" data-bs-target="#modalAbrirCaja">
                <i class="bi bi-box-arrow-in-down"></i> Abrir Caja
            </button>
        </div>
    </div>

    <hr class="border-warning border-2 opacity-75 mb-4">

    <!-- Cajas Abiertas -->
    <div id="cajasAbiertasContainer"></div>

    <!-- Cards Estadísticas -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-start border-success border-4 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Cajas Cuadradas</p>
                            <h3 id="totalCuadradas" class="mb-0">0</h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="bi bi-check-circle fs-2 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-start border-danger border-4 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Con Faltante</p>
                            <h3 id="totalFaltante" class="mb-0">0</h3>
                        </div>
                        <div class="bg-danger bg-opacity-10 p-3 rounded">
                            <i class="bi bi-exclamation-triangle fs-2 text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-start border-warning border-4 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Con Sobrante</p>
                            <h3 id="totalSobrante" class="mb-0">0</h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="bi bi-plus-circle fs-2 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-start border-primary border-4 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Total Recaudado</p>
                            <h3 id="totalRecaudado" class="mb-0">Q 0</h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="bi bi-cash-stack fs-2 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow-sm mb-4">
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
                    <select class="form-select" id="filtroSucursal">
                        <option value="">Todas</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Usuario</label>
                    <select class="form-select" id="filtroUsuario">
                        <option value="">Todos</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100" onclick="aplicarFiltros()">
                        <i class="bi bi-funnel"></i> Filtrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla Historial -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-clock-history"></i> Historial de Cajas</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Sucursal</th>
                            <th>Usuario</th>
                            <th>Apertura</th>
                            <th>Cierre</th>
                            <th>Monto Inicial</th>
                            <th>Esperado</th>
                            <th>Real</th>
                            <th>Diferencia</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaCajas">
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                <div class="spinner-border text-primary"></div>
                                <p class="mt-2">Cargando historial...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Modal Abrir Caja -->
<div class="modal fade" id="modalAbrirCaja" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-box-arrow-in-down"></i> Abrir Caja</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formAbrirCaja">
                    <div class="mb-3">
                        <label for="sucursalAbrir" class="form-label">
                            Sucursal <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="sucursalAbrir" required>
                            <option value="">Seleccione...</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="montoInicial" class="form-label">
                            Monto Inicial (Q) <span class="text-danger">*</span>
                        </label>
                        <input type="number" class="form-control form-control-lg" id="montoInicial" 
                               step="0.01" min="0" required placeholder="Ej: 500.00">
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <small>Este monto es el efectivo con el que inicia la caja.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="abrirCaja()">
                    <i class="bi bi-check-circle"></i> Abrir Caja
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cerrar Caja -->
<div class="modal fade" id="modalCerrarCaja" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="tituloModalCerrar"><i class="bi bi-box-arrow-up"></i> Cerrar Caja</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
<div class="modal-body">
                <!-- Resumen de Caja -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="alert alert-primary mb-0">
                            <small class="text-muted d-block">Monto Inicial:</small>
                            <strong id="cierreInicial">Q 0.00</strong>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="alert alert-success mb-0">
                            <small class="text-muted d-block">Total Ingresos:</small>
                            <strong id="cierreIngresos">Q 0.00</strong>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="alert alert-warning mb-0">
                            <small class="text-muted d-block">Monto Esperado:</small>
                            <strong id="cierreEsperado">Q 0.00</strong>
                        </div>
                    </div>
                </div>

                <!-- Historial de Movimientos -->
                <div class="card mb-3">
                    <div class="card-header bg-info text-white py-2">
                        <h6 class="mb-0"><i class="bi bi-list-ul"></i> Movimientos (<span id="cantidadMovimientos">0</span>)</h6>
                    </div>
                    <div class="card-body p-0" style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="width: 35%;">Concepto</th>
                                    <th style="width: 25%;">Fecha/Hora</th>
                                    <th style="width: 20%;">Tipo</th>
                                    <th class="text-end" style="width: 20%;">Monto</th>
                                </tr>
                            </thead>
                            <tbody id="tablaMovimientosCierre">
                                <tr><td colspan="4" class="text-center text-muted">Sin movimientos</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <form id="formCerrarCaja">
                    <div class="mb-3">
                        <label for="montoReal" class="form-label">
                            Monto Real Contado (Q) <span class="text-danger">*</span>
                        </label>
                        <input type="number" class="form-control form-control-lg" id="montoReal" 
                               step="0.01" min="0" required>
                    </div>

                    <div class="mb-3">
                        <label for="observacionesCierre" class="form-label">Observaciones</label>
                        <textarea class="form-control" id="observacionesCierre" rows="2" 
                                  placeholder="Opcional: notas sobre el cierre..."></textarea>
                    </div>

                    <div id="previewDiferencia" style="display: none;" class="alert">
                        <strong>Diferencia:</strong> <span id="textoDiferencia"></span>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" onclick="cerrarCaja()">
                    <i class="bi bi-check-circle"></i> Cerrar Caja
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>

<script src="../../assets/js/vendors/sweetalert2/sweetalert2.all.min.js"></script>
<script src="../../assets/js/common.js"></script>
<script src="../../assets/js/api-client.js"></script>

<script>
// Forzar recarga al volver
window.addEventListener('pageshow', function(event) {
    if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
        window.location.reload();
    }
});

let cajasData = [];
let cajasAbiertas = [];
let cajaACerrar = null;

async function init() {
    await cargarCajasAbiertas();
    await cargarHistorial();
    await cargarSucursales();
    await cargarUsuarios();
    
    const hoy = new Date();
    const hace7dias = new Date();
    hace7dias.setDate(hoy.getDate() - 7);
    
    document.getElementById('fechaDesde').valueAsDate = hace7dias;
    document.getElementById('fechaHasta').valueAsDate = hoy;
    
    configurarEventos();
}

async function cargarCajasAbiertas() {
    try {
        const res = await fetch('/api/caja/listar.php?estado=abierta');
        const data = await res.json();
        
        if (!data.success) {
            cajasAbiertas = [];
            return;
        }
        
        cajasAbiertas = data.data || [];
        
        if (cajasAbiertas.length === 0) {
            document.getElementById('cajasAbiertasContainer').innerHTML = '';
            return;
        }
        
        for (let caja of cajasAbiertas) {
            await cargarTotalesCaja(caja);
        }
        
        mostrarCajasAbiertas();
        
    } catch (error) {
        console.error('Error:', error);
        cajasAbiertas = [];
    }
}

async function cargarTotalesCaja(caja) {
    try {
        const res = await fetch('/api/caja/movimientos.php?caja_id=' + caja.id);
        const data = await res.json();
        
        if (!data.success) {
            caja.total_ingresos = 0;
            caja.total_egresos = 0;
            caja.total_final = parseFloat(caja.monto_inicial) || 0;
            return;
        }
        
        const movimientos = data.data || [];
        let ingresos = 0;
        let egresos = 0;
        
        movimientos.forEach(m => {
            const monto = parseFloat(m.monto) || 0;
            if (m.categoria === 'ingreso') {
                ingresos += monto;
            } else if (m.categoria === 'egreso') {
                egresos += monto;
            }
        });
        
        caja.total_ingresos = ingresos;
        caja.total_egresos = egresos;
        caja.total_final = (parseFloat(caja.monto_inicial) || 0) + ingresos - egresos;
        
    } catch (error) {
        console.error('Error al cargar totales:', error);
        caja.total_ingresos = 0;
        caja.total_egresos = 0;
        caja.total_final = parseFloat(caja.monto_inicial) || 0;
    }
}

function mostrarCajasAbiertas() {
    const container = document.getElementById('cajasAbiertasContainer');
    
    if (cajasAbiertas.length === 0) {
        container.innerHTML = '';
        return;
    }
    
    let html = '';
    
    cajasAbiertas.forEach(caja => {
        const monto_inicial = parseFloat(caja.monto_inicial) || 0;
        const total_ingresos = parseFloat(caja.total_ingresos) || 0;
        const total_final = parseFloat(caja.total_final) || monto_inicial;
        
        html += '<div class="card shadow-sm mb-3 border-success border-3">';
        html += '<div class="card-header bg-success text-white d-flex justify-content-between align-items-center">';
        html += '<h5 class="mb-0"><i class="bi bi-check-circle"></i> Caja Abierta - ' + escaparHTML(caja.sucursal_nombre || 'Sin nombre') + '</h5>';
        html += '<button type="button" class="btn btn-light btn-sm" onclick="prepararCerrarCaja(' + caja.id + ')">';
        html += '<i class="bi bi-box-arrow-up"></i> Cerrar Esta Caja';
        html += '</button>';
        html += '</div>';
        html += '<div class="card-body">';
        html += '<div class="row">';
        html += '<div class="col-md-3"><small class="text-muted">Sucursal:</small><p class="fw-bold mb-0">' + escaparHTML(caja.sucursal_nombre || '-') + '</p></div>';
        html += '<div class="col-md-3"><small class="text-muted">Fecha Apertura:</small><p class="mb-0">' + formatearFechaHora(caja.fecha_apertura) + '</p></div>';
        html += '<div class="col-md-2"><small class="text-muted">Monto Inicial:</small><p class="fw-bold text-primary mb-0">' + formatearMoneda(monto_inicial) + '</p></div>';
        html += '<div class="col-md-2"><small class="text-muted">Total Ingresos:</small><p class="fw-bold text-success mb-0">' + formatearMoneda(total_ingresos) + '</p></div>';
        html += '<div class="col-md-2"><small class="text-muted">Total Final:</small><p class="fw-bold text-dark mb-0 fs-5">' + formatearMoneda(total_final) + '</p></div>';
        html += '</div></div></div>';
    });
    
    container.innerHTML = html;
    
    document.getElementById('btnAbrirCaja').innerHTML = '<i class="bi bi-check-circle"></i> Caja Ya Abierta';
}

async function prepararCerrarCaja(cajaId) {
    const caja = cajasAbiertas.find(c => c.id === cajaId);
    
    if (!caja) {
        mostrarError('No se encontró la caja');
        return;
    }
    
    cajaACerrar = cajaId;
    
    const montoInicial = parseFloat(caja.monto_inicial) || 0;
    const totalIngresos = parseFloat(caja.total_ingresos) || 0;
    const montoEsperado = parseFloat(caja.total_final) || 0;
    
    // Actualizar información del modal
    document.getElementById('tituloModalCerrar').innerHTML = '<i class="bi bi-box-arrow-up"></i> Cerrar Caja - ' + escaparHTML(caja.sucursal_nombre);
    document.getElementById('cierreInicial').textContent = formatearMoneda(montoInicial);
    document.getElementById('cierreIngresos').textContent = formatearMoneda(totalIngresos);
    document.getElementById('cierreEsperado').textContent = formatearMoneda(montoEsperado);
    document.getElementById('montoReal').value = montoEsperado.toFixed(2);
    document.getElementById('observacionesCierre').value = '';
    document.getElementById('previewDiferencia').style.display = 'none';
    
    // Cargar movimientos
    await cargarMovimientosCierre(cajaId);
    
    const modal = new bootstrap.Modal(document.getElementById('modalCerrarCaja'));
    modal.show();
}

async function cargarMovimientosCierre(cajaId) {
    try {
        const res = await fetch('/api/caja/movimientos.php?caja_id=' + cajaId);
        const data = await res.json();
        
        if (!data.success) {
            document.getElementById('tablaMovimientosCierre').innerHTML = '<tr><td colspan="4" class="text-center text-muted">Error al cargar movimientos</td></tr>';
            document.getElementById('cantidadMovimientos').textContent = '0';
            return;
        }
        
        const movimientos = data.data || [];
        document.getElementById('cantidadMovimientos').textContent = movimientos.length;
        
        if (movimientos.length === 0) {
            document.getElementById('tablaMovimientosCierre').innerHTML = '<tr><td colspan="4" class="text-center text-muted">Sin movimientos</td></tr>';
            return;
        }
        
        let html = '';
        
        movimientos.forEach(m => {
            const esIngreso = m.categoria === 'ingreso';
            const clase = esIngreso ? 'text-success' : 'text-danger';
            const signo = esIngreso ? '+' : '-';
            
            html += '<tr>';
            html += '<td><small>' + escaparHTML(m.concepto) + '</small></td>';
            html += '<td><small>' + formatearFechaHora(m.fecha_hora) + '</small></td>';
            html += '<td><span class="badge ' + (esIngreso ? 'bg-success' : 'bg-danger') + '">' + escaparHTML(m.tipo_movimiento) + '</span></td>';
            html += '<td class="text-end ' + clase + '"><strong>' + signo + formatearMoneda(m.monto) + '</strong></td>';
            html += '</tr>';
        });
        
        document.getElementById('tablaMovimientosCierre').innerHTML = html;
        
    } catch (error) {
        console.error('Error al cargar movimientos:', error);
        document.getElementById('tablaMovimientosCierre').innerHTML = '<tr><td colspan="4" class="text-center text-danger">Error al cargar movimientos</td></tr>';
        document.getElementById('cantidadMovimientos').textContent = '0';
    }
}

async function cargarHistorial() {
    try {
        const res = await fetch('/api/caja/listar.php');
        const data = await res.json();
        
        if (!data.success) {
            mostrarError('Error al cargar historial');
            return;
        }
        
        cajasData = data.data || [];
        aplicarFiltros();
        actualizarEstadisticas();
        
    } catch (error) {
        console.error('Error:', error);
        mostrarError('Error al cargar historial');
    }
}

function actualizarEstadisticas() {
    const stats = {
        cuadradas: 0,
        faltante: 0,
        sobrante: 0,
        totalRecaudado: 0
    };
    
    cajasData.forEach(c => {
        if (c.estado !== 'cerrada') return;
        
        const dif = parseFloat(c.diferencia) || 0;
        
        if (Math.abs(dif) < 0.01) {
            stats.cuadradas++;
        } else if (dif < 0) {
            stats.faltante++;
        } else {
            stats.sobrante++;
        }
        
        stats.totalRecaudado += parseFloat(c.monto_real) || 0;
    });
    
    document.getElementById('totalCuadradas').textContent = stats.cuadradas;
    document.getElementById('totalFaltante').textContent = stats.faltante;
    document.getElementById('totalSobrante').textContent = stats.sobrante;
    document.getElementById('totalRecaudado').textContent = formatearMoneda(stats.totalRecaudado);
}

function aplicarFiltros() {
    const desde = document.getElementById('fechaDesde').value;
    const hasta = document.getElementById('fechaHasta').value;
    const sucursal = document.getElementById('filtroSucursal').value;
    const usuario = document.getElementById('filtroUsuario').value;
    
    let filtradas = cajasData.filter(c => {
        if (desde && c.fecha_apertura < desde) return false;
        if (hasta && c.fecha_apertura > hasta + ' 23:59:59') return false;
        if (sucursal && c.sucursal_id != sucursal) return false;
        if (usuario && c.usuario_id != usuario) return false;
        return true;
    });
    
    renderizarTabla(filtradas);
}

function renderizarTabla(cajas) {
    const tbody = document.getElementById('tablaCajas');
    
    if (!cajas || cajas.length === 0) {
        tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted py-4">No hay cajas para mostrar</td></tr>';
        return;
    }
    
    let html = '';
    
    cajas.forEach(c => {
        const dif = parseFloat(c.diferencia) || 0;
        let difClass = 'text-success';
        let difIcon = 'check-circle';
        
        if (Math.abs(dif) > 0.01) {
            if (dif < 0) {
                difClass = 'text-danger';
                difIcon = 'exclamation-triangle';
            } else {
                difClass = 'text-warning';
                difIcon = 'plus-circle';
            }
        }
        
        html += '<tr>';
        html += '<td>' + escaparHTML(c.sucursal_nombre || '-') + '</td>';
        html += '<td>' + escaparHTML(c.usuario_nombre || '-') + '</td>';
        html += '<td><small>' + formatearFechaHora(c.fecha_apertura) + '</small></td>';
        html += '<td><small>' + (c.fecha_cierre ? formatearFechaHora(c.fecha_cierre) : '-') + '</small></td>';
        html += '<td>' + formatearMoneda(c.monto_inicial) + '</td>';
        html += '<td>' + formatearMoneda(c.monto_esperado) + '</td>';
        html += '<td>' + formatearMoneda(c.monto_real) + '</td>';
        html += '<td class="' + difClass + '"><i class="bi bi-' + difIcon + '"></i> ' + formatearMoneda(Math.abs(dif)) + '</td>';
        html += '<td><span class="badge ' + (c.estado === 'abierta' ? 'bg-success' : 'bg-secondary') + '">' + (c.estado === 'abierta' ? 'Abierta' : 'Cerrada') + '</span></td>';
        html += '<td class="text-center">' + (c.estado === 'cerrada' ? '<a href="ver.php?id=' + c.id + '" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>' : '-') + '</td>';
        html += '</tr>';
    });
    
    tbody.innerHTML = html;
}

async function cargarSucursales() {
    try {
        const res = await fetch('/api/sucursales/listar.php?activo=1');
        const data = await res.json();
        
        if (!data.success) return;
        
        const sucursales = data.data || [];
        const selects = [document.getElementById('sucursalAbrir'), document.getElementById('filtroSucursal')];
        
        selects.forEach(select => {
            if (!select) return;
            sucursales.forEach(s => {
                const option = document.createElement('option');
                option.value = s.id;
                option.textContent = s.nombre;
                select.appendChild(option);
            });
        });
    } catch (error) {
        console.error('Error:', error);
    }
}

async function cargarUsuarios() {
    try {
        const res = await fetch('/api/usuarios/listar.php');
        const data = await res.json();
        
        if (!data.success) return;
        
        const usuarios = data.data.usuarios || data.data || [];
        const select = document.getElementById('filtroUsuario');
        
        if (!select) return;
        
        usuarios.forEach(u => {
            const option = document.createElement('option');
            option.value = u.id;
            option.textContent = u.nombre;
            select.appendChild(option);
        });
    } catch (error) {
        console.error('Error:', error);
    }
}

async function abrirCaja() {
    const sucursal = document.getElementById('sucursalAbrir').value;
    const monto = document.getElementById('montoInicial').value;
    
    if (!sucursal || !monto) {
        mostrarError('Complete todos los campos');
        return;
    }
    
    try {
        mostrarCargando();
        
        const res = await fetch('/api/caja/abrir_caja.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                sucursal_id: parseInt(sucursal),
                monto_inicial: parseFloat(monto)
            })
        });
        
        const resultado = await res.json();
        
        ocultarCargando();
        
        if (resultado.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalAbrirCaja'));
            modal.hide();
            
            await mostrarExito(resultado.message);
            window.location.reload();
        } else {
            mostrarError(resultado.error || resultado.message || 'Error al abrir caja');
        }
    } catch (error) {
        ocultarCargando();
        mostrarError('Error: ' + error.message);
    }
}

async function cerrarCaja() {
    const montoReal = document.getElementById('montoReal').value;
    const observaciones = document.getElementById('observacionesCierre').value;
    
    if (!montoReal) {
        mostrarError('Ingrese el monto real contado');
        return;
    }
    
    if (!cajaACerrar) {
        mostrarError('No se ha seleccionado una caja para cerrar');
        return;
    }
    
    const confirmacion = await confirmarAccion('¿Cerrar la caja?', 'Esta acción no se puede deshacer');
    if (!confirmacion) return;
    
    try {
        mostrarCargando();
        
        const res = await fetch('/api/caja/cerrar_caja.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                caja_id: cajaACerrar,
                monto_real: parseFloat(montoReal),
                observaciones: observaciones || null
            })
        });
        
        const resultado = await res.json();
        
        ocultarCargando();
        
        if (resultado.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalCerrarCaja'));
            if (modal) modal.hide();
            
            await mostrarExito(resultado.message);
            window.location.reload();
        } else {
            mostrarError(resultado.error || resultado.message || 'Error');
        }
    } catch (error) {
        ocultarCargando();
        mostrarError('Error: ' + error.message);
    }
}

function configurarEventos() {
    const inputMontoReal = document.getElementById('montoReal');
    if (inputMontoReal) {
        inputMontoReal.addEventListener('input', function() {
            const caja = cajasAbiertas.find(c => c.id === cajaACerrar);
            if (!caja) return;
            
            const esperado = parseFloat(caja.total_final) || 0;
            const real = parseFloat(this.value) || 0;
            const dif = real - esperado;
            
            const preview = document.getElementById('previewDiferencia');
            const texto = document.getElementById('textoDiferencia');
            
            if (!preview || !texto) return;
            
            if (Math.abs(dif) < 0.01) {
                preview.className = 'alert alert-success';
                texto.innerHTML = '<i class="bi bi-check-circle"></i> Cuadrado (sin diferencias)';
            } else if (dif < 0) {
                preview.className = 'alert alert-danger';
                texto.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Faltante de ' + formatearMoneda(Math.abs(dif));
            } else {
                preview.className = 'alert alert-warning';
                texto.innerHTML = '<i class="bi bi-plus-circle"></i> Sobrante de ' + formatearMoneda(dif);
            }
            
            preview.style.display = 'block';
        });
    }
}

document.addEventListener('DOMContentLoaded', init);
</script>