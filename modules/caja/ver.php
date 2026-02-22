<?php
/**
 * ================================================
 * MÓDULO CAJA - VER DETALLES
 * ================================================
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();

$caja_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$caja_id) {
    header('Location: lista.php');
    exit;
}

require_once '../../includes/header.php';
require_once '../../includes/navbar.php';
?>

<div class="container-fluid px-4 py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-1"><i class="bi bi-eye"></i> Detalles de Caja</h2>
            <p class="text-muted mb-0" id="cajaTitulo">Cargando...</p>
        </div>
        <a href="lista.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    <hr class="border-warning border-2 opacity-75 mb-4">

    <div class="row">
        <!-- Columna Izquierda -->
        <div class="col-lg-8">
            
            <!-- Información General -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Información General</h5>
                    <span id="estadoBadge" class="badge">-</span>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Sucursal</label>
                            <h5 id="sucursal">-</h5>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Usuario Responsable</label>
                            <h5 id="usuario">-</h5>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Fecha/Hora Apertura</label>
                            <p id="fechaApertura" class="mb-0">-</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Fecha/Hora Cierre</label>
                            <p id="fechaCierre" class="mb-0">-</p>
                        </div>
                    </div>

                    <div id="observacionesContainer" style="display: none;">
                        <label class="form-label text-muted">Observaciones del Cierre</label>
                        <p id="observaciones" class="border-start border-3 border-info ps-3">-</p>
                    </div>
                </div>
            </div>

            <!-- Movimientos -->
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-list-ul"></i> Movimientos de Caja (<span id="totalMovimientos">0</span>)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha/Hora</th>
                                    <th>Tipo</th>
                                    <th>Categoría</th>
                                    <th>Concepto</th>
                                    <th class="text-end">Monto</th>
                                </tr>
                            </thead>
                            <tbody id="tablaMovimientos">
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <div class="spinner-border text-primary"></div>
                                        <p class="mt-2">Cargando movimientos...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        <!-- Columna Derecha -->
        <div class="col-lg-4">
            
            <!-- Resumen Financiero -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-cash-stack"></i> Resumen Financiero</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Monto Inicial:</small>
                        <h4 id="montoInicial" class="mb-0 text-primary">-</h4>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted">Total Ingresos:</small>
                        <h4 id="totalIngresos" class="mb-0 text-success">-</h4>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted">Total Egresos:</small>
                        <h4 id="totalEgresos" class="mb-0 text-danger">-</h4>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <small class="text-muted">Monto Esperado:</small>
                        <h3 id="montoEsperado" class="mb-0">-</h3>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted">Monto Real Contado:</small>
                        <h3 id="montoReal" class="mb-0">-</h3>
                    </div>

                    <hr>

                    <div>
                        <small class="text-muted">Diferencia:</small>
                        <h2 id="diferencia" class="mb-0">-</h2>
                        <div id="statusDiferencia"></div>
                    </div>
                </div>
            </div>

            <!-- Desglose Ingresos -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="bi bi-arrow-down-circle"></i> Desglose Ingresos</h6>
                </div>
                <div class="card-body">
                    <div id="desgloseIngresos">
                        <p class="text-muted text-center">Sin datos</p>
                    </div>
                </div>
            </div>

            <!-- Desglose Egresos -->
            <div class="card shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0"><i class="bi bi-arrow-up-circle"></i> Desglose Egresos</h6>
                </div>
                <div class="card-body">
                    <div id="desgloseEgresos">
                        <p class="text-muted text-center">Sin datos</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

<?php require_once '../../includes/footer.php'; ?>

<script src="../../assets/js/vendors/sweetalert2/sweetalert2.all.min.js"></script>
<script src="../../assets/js/common.js"></script>
<script src="../../assets/js/api-client.js"></script>

<script>
const cajaId = <?php echo $caja_id; ?>;
let caja = null;
let movimientos = [];

async function cargarCaja() {
    try {
        mostrarCargando();
        
        // Cargar datos de la caja
        const res = await fetch(`/api/caja/listar.php?limite=1000`);
        const data = await res.json();
        
        if (!data.success) {
            ocultarCargando();
            await mostrarError('Error al cargar caja');
            window.location.href = 'lista.php';
            return;
        }
        
        // Buscar la caja por ID
        caja = (data.data || []).find(c => c.id == cajaId);
        
        if (!caja) {
            ocultarCargando();
            await mostrarError('Caja no encontrada');
            window.location.href = 'lista.php';
            return;
        }
        
        mostrarDatos(caja);
        
        // Cargar movimientos
        await cargarMovimientos();
        
        ocultarCargando();
        
    } catch (error) {
        ocultarCargando();
        console.error('Error:', error);
        mostrarError('Error al cargar caja');
    }
}

function mostrarDatos(c) {
    // Título
    document.getElementById('cajaTitulo').textContent = `Caja #${c.id} - ${c.sucursal_nombre || 'Sin sucursal'}`;
    
    // Estado
    const estadoBadge = document.getElementById('estadoBadge');
    estadoBadge.className = c.estado === 'abierta' ? 'badge bg-success' : 'badge bg-secondary';
    estadoBadge.textContent = c.estado === 'abierta' ? 'Abierta' : 'Cerrada';
    
    // Información general
    document.getElementById('sucursal').textContent = c.sucursal_nombre || '-';
    document.getElementById('usuario').textContent = c.usuario_nombre || '-';
    document.getElementById('fechaApertura').textContent = formatearFechaHora(c.fecha_apertura);
    document.getElementById('fechaCierre').textContent = c.fecha_cierre ? formatearFechaHora(c.fecha_cierre) : '-';
    
    if (c.observaciones_cierre) {
        document.getElementById('observacionesContainer').style.display = 'block';
        document.getElementById('observaciones').textContent = c.observaciones_cierre;
    }
    
    // Resumen financiero
    document.getElementById('montoInicial').textContent = formatearMoneda(c.monto_inicial);
    
    // Calcular totales (si no vienen en la respuesta)
    const esperado = parseFloat(c.monto_esperado) || 0;
    const real = parseFloat(c.monto_real) || 0;
    const diferencia = parseFloat(c.diferencia) || 0;
    
    document.getElementById('montoEsperado').textContent = formatearMoneda(esperado);
    document.getElementById('montoReal').textContent = formatearMoneda(real);
    
    // Diferencia con colores
    const difEl = document.getElementById('diferencia');
    const statusEl = document.getElementById('statusDiferencia');
    
    if (Math.abs(diferencia) < 0.01) {
        difEl.textContent = formatearMoneda(0);
        difEl.className = 'mb-0 text-success';
        statusEl.innerHTML = '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Cuadrado</span>';
    } else if (diferencia < 0) {
        difEl.textContent = formatearMoneda(diferencia);
        difEl.className = 'mb-0 text-danger';
        statusEl.innerHTML = `<span class="badge bg-danger"><i class="bi bi-exclamation-triangle"></i> Faltante de ${formatearMoneda(Math.abs(diferencia))}</span>`;
    } else {
        difEl.textContent = formatearMoneda(diferencia);
        difEl.className = 'mb-0 text-warning';
        statusEl.innerHTML = `<span class="badge bg-warning text-dark"><i class="bi bi-plus-circle"></i> Sobrante de ${formatearMoneda(diferencia)}</span>`;
    }
}

async function cargarMovimientos() {
    try {
        const res = await fetch(`/api/caja/movimientos.php?caja_id=${cajaId}`);
        const data = await res.json();
        
        if (!data.success) {
            document.getElementById('tablaMovimientos').innerHTML = 
                '<tr><td colspan="5" class="text-center text-muted py-3">No se pudieron cargar los movimientos</td></tr>';
            return;
        }
        
        movimientos = data.data || [];
        
        document.getElementById('totalMovimientos').textContent = movimientos.length;
        
        if (movimientos.length === 0) {
            document.getElementById('tablaMovimientos').innerHTML = 
                '<tr><td colspan="5" class="text-center text-muted py-3">No hay movimientos registrados</td></tr>';
            return;
        }
        
        renderizarMovimientos(movimientos);
        calcularTotalesYDesgloses(movimientos);
        
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('tablaMovimientos').innerHTML = 
            '<tr><td colspan="5" class="text-center text-danger py-3">Error al cargar movimientos</td></tr>';
    }
}

function renderizarMovimientos(movs) {
    const tbody = document.getElementById('tablaMovimientos');
    
    let html = '';
    
    movs.forEach(m => {
        // CORREGIDO: Usar 'categoria' para determinar si es ingreso o egreso
        const esIngreso = m.categoria === 'ingreso';
        const clase = esIngreso ? 'text-success' : 'text-danger';
        const signo = esIngreso ? '+' : '-';
        
        html += `
            <tr>
                <td><small>${formatearFechaHora(m.fecha_hora)}</small></td>
                <td>
                    <span class="badge ${esIngreso ? 'bg-success' : 'bg-danger'}">
                        ${m.tipo_movimiento}
                    </span>
                </td>
                <td><small>${escaparHTML(m.categoria)}</small></td>
                <td><small>${escaparHTML(m.concepto)}</small></td>
                <td class="text-end ${clase}">
                    <strong>${signo}${formatearMoneda(m.monto)}</strong>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

function calcularTotalesYDesgloses(movs) {
    const totales = {
        ingresos: 0,
        egresos: 0
    };
    
    const desgloseIng = {};
    const desgloseEgr = {};
    
    movs.forEach(m => {
        const monto = parseFloat(m.monto) || 0;
        
        // CORREGIDO: Usar 'categoria' en vez de 'tipo_movimiento'
        if (m.categoria === 'ingreso') {
            totales.ingresos += monto;
            desgloseIng[m.tipo_movimiento] = (desgloseIng[m.tipo_movimiento] || 0) + monto;
        } else {
            totales.egresos += monto;
            desgloseEgr[m.tipo_movimiento] = (desgloseEgr[m.tipo_movimiento] || 0) + monto;
        }
    });
    
    document.getElementById('totalIngresos').textContent = formatearMoneda(totales.ingresos);
    document.getElementById('totalEgresos').textContent = formatearMoneda(totales.egresos);
    
    // Renderizar desgloses
    renderizarDesglose('desgloseIngresos', desgloseIng, 'success');
    renderizarDesglose('desgloseEgresos', desgloseEgr, 'danger');
}

function renderizarDesglose(containerId, desglose, color) {
    const container = document.getElementById(containerId);
    
    const categorias = Object.keys(desglose);
    
    if (categorias.length === 0) {
        container.innerHTML = '<p class="text-muted text-center mb-0">Sin movimientos</p>';
        return;
    }
    
    let html = '';
    
    categorias.forEach(cat => {
        html += `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <small>${escaparHTML(cat)}:</small>
                <strong class="text-${color}">${formatearMoneda(desglose[cat])}</strong>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

document.addEventListener('DOMContentLoaded', cargarCaja);
</script>