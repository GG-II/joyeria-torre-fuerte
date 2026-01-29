<?php
/**
 * ================================================
 * MÓDULO CAJA - CERRAR CAJA (ARQUEO)
 * ================================================
 * 
 * TODO FASE 5: Conectar con APIs
 * GET /api/caja/ver.php?id={caja_id} - Cargar datos de la caja
 * POST /api/caja/cerrar.php - Cerrar caja con arqueo
 * 
 * UPDATE cajas SET fecha_cierre, monto_esperado, monto_real, diferencia, observaciones_cierre, estado='cerrada'
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();
requiere_rol(['administrador', 'dueño', 'cajero']);

$caja_id = $_GET['id'] ?? null;
if (!$caja_id) {
    header('Location: lista.php');
    exit;
}

$caja = null;
$titulo_pagina = 'Cerrar Caja';
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="container-fluid main-content">
    <div id="loadingState" class="text-center py-5">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
        <p class="mt-3 text-muted">Cargando datos de la caja...</p>
    </div>

    <div id="mainContent" style="display: none;">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>dashboard.php"><i class="bi bi-house"></i> Dashboard</a></li>
                <li class="breadcrumb-item"><a href="lista.php"><i class="bi bi-cash-stack"></i> Caja</a></li>
                <li class="breadcrumb-item"><a href="ver.php?id=<?php echo $caja_id; ?>">Caja #<?php echo $caja_id; ?></a></li>
                <li class="breadcrumb-item active">Cerrar Caja</li>
            </ol>
        </nav>

        <div class="page-header mb-4">
            <h1 class="mb-2"><i class="bi bi-box-arrow-up"></i> Cierre de Caja (Arqueo)</h1>
            <p class="text-muted mb-0">Cuente el efectivo y cierre el turno</p>
        </div>

        <form id="formCerrarCaja" method="POST">
            <input type="hidden" name="caja_id" value="<?php echo $caja_id; ?>">
            <input type="hidden" id="montoEsperadoInput" name="monto_esperado" value="0">
            
            <div class="row g-3">
                <div class="col-lg-4">
                    <div class="card mb-3 shadow-sm">
                        <div class="card-header" style="background-color: #1e3a8a; color: white;">
                            <i class="bi bi-info-circle"></i> Información del Turno
                        </div>
                        <div class="card-body" id="infoTurno">
                            <div class="text-center py-3"><div class="spinner-border spinner-border-sm"></div></div>
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-header bg-success text-white">
                            <i class="bi bi-calculator"></i> Efectivo Esperado
                        </div>
                        <div class="card-body" id="infoEsperado">
                            <div class="text-center py-3"><div class="spinner-border spinner-border-sm"></div></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card mb-3 shadow-sm">
                        <div class="card-header bg-warning">
                            <i class="bi bi-calculator-fill"></i> Conteo de Efectivo Real
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> Cuente el efectivo en caja e ingrese las cantidades
                            </div>

                            <h6 class="fw-bold mb-3">Billetes:</h6>
                            <div class="row g-2 mb-3">
                                <div class="col-6 col-md-3">
                                    <label class="form-label small">Q 200.00 x</label>
                                    <input type="number" class="form-control desglose" data-valor="200" min="0" value="0" name="billetes_200">
                                    <small class="text-muted total-parcial">Q 0.00</small>
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label small">Q 100.00 x</label>
                                    <input type="number" class="form-control desglose" data-valor="100" min="0" value="0" name="billetes_100">
                                    <small class="text-muted total-parcial">Q 0.00</small>
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label small">Q 50.00 x</label>
                                    <input type="number" class="form-control desglose" data-valor="50" min="0" value="0" name="billetes_50">
                                    <small class="text-muted total-parcial">Q 0.00</small>
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label small">Q 20.00 x</label>
                                    <input type="number" class="form-control desglose" data-valor="20" min="0" value="0" name="billetes_20">
                                    <small class="text-muted total-parcial">Q 0.00</small>
                                </div>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6 col-md-3">
                                    <label class="form-label small">Q 10.00 x</label>
                                    <input type="number" class="form-control desglose" data-valor="10" min="0" value="0" name="billetes_10">
                                    <small class="text-muted total-parcial">Q 0.00</small>
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label small">Q 5.00 x</label>
                                    <input type="number" class="form-control desglose" data-valor="5" min="0" value="0" name="billetes_5">
                                    <small class="text-muted total-parcial">Q 0.00</small>
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label small">Q 1.00 x</label>
                                    <input type="number" class="form-control desglose" data-valor="1" min="0" value="0" name="billetes_1">
                                    <small class="text-muted total-parcial">Q 0.00</small>
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label small">Monedas (Q)</label>
                                    <input type="number" class="form-control" id="monedas" min="0" step="0.01" value="0" name="monedas">
                                </div>
                            </div>

                            <hr>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="mb-2">Total Efectivo Real:</h6>
                                            <h2 class="mb-0 text-primary" id="montoReal">Q 0.00</h2>
                                            <input type="hidden" name="monto_real" id="montoRealInput" value="0">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card" id="cardDiferencia">
                                        <div class="card-body">
                                            <h6 class="mb-2">Diferencia:</h6>
                                            <h2 class="mb-0" id="diferencia">Q 0.00</h2>
                                            <small id="estadoDiferencia"></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3 shadow-sm">
                        <div class="card-header"><i class="bi bi-chat-left-text"></i> Observaciones del Cierre</div>
                        <div class="card-body">
                            <textarea class="form-control" id="observaciones_cierre" name="observaciones_cierre" rows="3" placeholder="Anote cualquier observación sobre el cierre (opcional)"></textarea>
                            <small class="text-muted">En caso de faltante o sobrante, explique el motivo si es conocido</small>
                        </div>
                    </div>

                    <div class="alert alert-success">
                        <h6 class="fw-bold"><i class="bi bi-database"></i> Proceso al cerrar:</h6>
                        <ol class="mb-0 small">
                            <li>Se actualizará la tabla <code>cajas</code>:
                                <ul>
                                    <li><code>fecha_cierre</code> = ahora</li>
                                    <li><code>monto_esperado</code> = calculado automáticamente</li>
                                    <li><code>monto_real</code> = valor contado</li>
                                    <li><code>diferencia</code> = monto_real - monto_esperado</li>
                                    <li><code>observaciones_cierre</code> = texto ingresado</li>
                                    <li><code>estado</code> = 'cerrada'</li>
                                </ul>
                            </li>
                            <li>No se pueden modificar cajas cerradas</li>
                        </ol>
                    </div>

                    <div class="d-flex flex-column flex-sm-row justify-content-between gap-2">
                        <a href="ver.php?id=<?php echo $caja_id; ?>" class="btn btn-secondary btn-lg">
                            <i class="bi bi-arrow-left"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-danger btn-lg" id="btnCerrar">
                            <i class="bi bi-box-arrow-up"></i> Cerrar Caja
                        </button>
                    </div>
                </div>
            </div>
        </form>
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
.page-header h1 { font-size: 1.75rem; font-weight: 600; color: #1a1a1a; }
.shadow-sm { box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08) !important; }
.card-body { padding: 25px; }
.card-body > p:not(:last-child) { padding-bottom: 12px; margin-bottom: 12px; border-bottom: 1px solid #e5e7eb; }
.form-label { font-weight: 500; margin-bottom: 0.5rem; color: #374151; }
.form-control { border: 1px solid #d1d5db; border-radius: 6px; }
.form-control:focus { border-color: #1e3a8a; box-shadow: 0 0 0 0.2rem rgba(30, 58, 138, 0.15); }
code { background-color: #f3f4f6; padding: 2px 6px; border-radius: 4px; font-size: 0.9em; color: #1e3a8a; }
.total-parcial { display: block; margin-top: 4px; }
@media (max-width: 575.98px) {
    .main-content { padding: 15px 10px; }
    .page-header h1 { font-size: 1.5rem; }
    .card-body { padding: 15px; }
    .btn { width: 100%; }
}
@media (min-width: 576px) and (max-width: 767.98px) { .main-content { padding: 18px 15px; } }
@media (min-width: 992px) { .main-content { padding: 25px 30px; } }
@media (max-width: 767.98px) { .btn, .form-control { min-height: 44px; } }
</style>

<script>
let montoEsperado = 0;

document.addEventListener('DOMContentLoaded', function() {
    cargarDatosCaja();
    
    document.querySelectorAll('.desglose').forEach(input => {
        input.addEventListener('input', function() {
            const valor = parseFloat(this.dataset.valor);
            const cantidad = parseInt(this.value) || 0;
            const total = valor * cantidad;
            this.parentElement.querySelector('.total-parcial').textContent = 'Q ' + total.toFixed(2);
            calcularTotal();
        });
    });
    
    document.getElementById('monedas').addEventListener('input', calcularTotal);
});

function cargarDatosCaja() {
    const cajaId = <?php echo $caja_id; ?>;
    
    /* TODO FASE 5: Descomentar
    fetch('<?php echo BASE_URL; ?>api/caja/ver.php?id=' + cajaId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.data.estado !== 'abierta') {
                    alert('Esta caja ya está cerrada');
                    window.location.href = 'ver.php?id=' + cajaId;
                    return;
                }
                renderizarCaja(data.data);
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

function renderizarCaja(caja) {
    document.getElementById('loadingState').style.display = 'none';
    document.getElementById('mainContent').style.display = 'block';
    
    montoEsperado = parseFloat(caja.monto_inicial) + parseFloat(caja.total_ingresos) - parseFloat(caja.total_egresos);
    document.getElementById('montoEsperadoInput').value = montoEsperado.toFixed(2);
    
    document.getElementById('infoTurno').innerHTML = `
        <p><strong>Usuario:</strong><br>${caja.usuario_nombre}</p>
        <p><strong>Sucursal:</strong><br>${caja.sucursal_nombre}</p>
        <p><strong>Apertura:</strong><br>${formatearFechaHora(caja.fecha_apertura)}</p>
        <p class="mb-0"><strong>Cierre:</strong><br>${formatearFechaHora(new Date())}</p>
    `;
    
    document.getElementById('infoEsperado').innerHTML = `
        <div class="d-flex justify-content-between mb-2"><span>Monto Inicial:</span><span>Q ${formatearMoneda(caja.monto_inicial)}</span></div>
        <div class="d-flex justify-content-between mb-2 text-success"><span>+ Ingresos:</span><span>Q ${formatearMoneda(caja.total_ingresos)}</span></div>
        <div class="d-flex justify-content-between mb-2 text-danger"><span>- Egresos:</span><span>Q ${formatearMoneda(caja.total_egresos)}</span></div>
        <hr>
        <div class="d-flex justify-content-between"><h4 class="mb-0">Total Esperado:</h4><h3 class="mb-0 text-success">Q ${formatearMoneda(montoEsperado)}</h3></div>
    `;
}

function calcularTotal() {
    let total = 0;
    
    document.querySelectorAll('.desglose').forEach(input => {
        const valor = parseFloat(input.dataset.valor) || 0;
        const cantidad = parseInt(input.value) || 0;
        total += valor * cantidad;
    });
    
    total += parseFloat(document.getElementById('monedas').value) || 0;
    
    document.getElementById('montoReal').textContent = 'Q ' + total.toFixed(2);
    document.getElementById('montoRealInput').value = total.toFixed(2);
    
    const diferencia = total - montoEsperado;
    const diferenciaElement = document.getElementById('diferencia');
    const estadoElement = document.getElementById('estadoDiferencia');
    const cardDiferencia = document.getElementById('cardDiferencia');
    
    if (diferencia === 0) {
        diferenciaElement.textContent = 'Q 0.00';
        diferenciaElement.className = 'mb-0 text-success';
        estadoElement.textContent = '✓ Caja cuadrada';
        estadoElement.className = 'text-success';
        cardDiferencia.className = 'card border-success';
    } else if (diferencia < 0) {
        diferenciaElement.textContent = '-Q ' + Math.abs(diferencia).toFixed(2);
        diferenciaElement.className = 'mb-0 text-danger';
        estadoElement.textContent = '⚠ Faltante';
        estadoElement.className = 'text-danger';
        cardDiferencia.className = 'card border-danger';
    } else {
        diferenciaElement.textContent = '+Q ' + diferencia.toFixed(2);
        diferenciaElement.className = 'mb-0 text-warning';
        estadoElement.textContent = '⚠ Sobrante';
        estadoElement.className = 'text-warning';
        cardDiferencia.className = 'card border-warning';
    }
}

document.getElementById('formCerrarCaja').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const montoReal = parseFloat(document.getElementById('montoRealInput').value);
    const diferencia = montoReal - montoEsperado;
    
    if (montoReal === 0) {
        alert('Debe contar el efectivo antes de cerrar la caja');
        return;
    }
    
    let mensaje = '¿Confirma el cierre de caja?\n\n';
    mensaje += 'Efectivo esperado: Q ' + montoEsperado.toFixed(2) + '\n';
    mensaje += 'Efectivo real: Q ' + montoReal.toFixed(2) + '\n';
    
    if (diferencia !== 0) {
        mensaje += '\n⚠ ATENCIÓN: ';
        mensaje += diferencia < 0 
            ? 'Faltante de Q ' + Math.abs(diferencia).toFixed(2)
            : 'Sobrante de Q ' + diferencia.toFixed(2);
        
        if (!document.getElementById('observaciones_cierre').value) {
            alert('Por favor agregue observaciones explicando la diferencia');
            document.getElementById('observaciones_cierre').focus();
            return;
        }
    }
    
    if (!confirm(mensaje)) return;
    
    const formData = new FormData(this);
    const datos = {
        caja_id: formData.get('caja_id'),
        monto_esperado: montoEsperado,
        monto_real: montoReal,
        observaciones_cierre: formData.get('observaciones_cierre') || null
    };
    
    const btnCerrar = document.getElementById('btnCerrar');
    btnCerrar.disabled = true;
    btnCerrar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Cerrando...';
    
    /* TODO FASE 5: Descomentar
    fetch('<?php echo BASE_URL; ?>api/caja/cerrar.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(datos)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Caja cerrada exitosamente');
            setTimeout(() => window.location.href = 'ver.php?id=' + datos.caja_id, 1500);
        } else {
            alert(data.message);
            btnCerrar.disabled = false;
            btnCerrar.innerHTML = '<i class="bi bi-box-arrow-up"></i> Cerrar Caja';
        }
    });
    */
    
    console.log('Datos cierre:', datos);
    setTimeout(() => {
        alert('MODO DESARROLLO: Caja cerrada.\n\n' + JSON.stringify(datos, null, 2));
        btnCerrar.disabled = false;
        btnCerrar.innerHTML = '<i class="bi bi-box-arrow-up"></i> Cerrar Caja';
    }, 1000);
});

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
</script>

<?php include '../../includes/footer.php'; ?>