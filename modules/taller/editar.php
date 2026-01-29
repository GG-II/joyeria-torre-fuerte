<?php
/**
 * ================================================
 * MÓDULO TALLER - EDITAR TRABAJO
 * ================================================
 * 
 * TODO FASE 5: Conectar con APIs
 * GET /api/taller/ver.php?id={trabajo_id} - Cargar datos
 * GET /api/empleados/lista.php?rol=orfebre - Cargar orfebres
 * POST /api/taller/actualizar.php - Guardar cambios
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();
requiere_rol(['administrador', 'dueño', 'orfebre']);

$trabajo_id = $_GET['id'] ?? null;
if (!$trabajo_id) {
    header('Location: lista.php');
    exit;
}

$trabajo = null;
$titulo_pagina = 'Editar Trabajo';
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="container-fluid main-content">
    <div id="loadingState" class="text-center py-5">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
        <p class="mt-3 text-muted">Cargando datos del trabajo...</p>
    </div>

    <div id="mainContent" style="display: none;">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>dashboard.php"><i class="bi bi-house"></i> Dashboard</a></li>
                <li class="breadcrumb-item"><a href="lista.php"><i class="bi bi-tools"></i> Taller</a></li>
                <li class="breadcrumb-item active">Editar Trabajo</li>
            </ol>
        </nav>

        <div class="page-header mb-4">
            <div class="row align-items-center g-3">
                <div class="col-md-6">
                    <h1 class="mb-2"><i class="bi bi-pencil-square"></i> Editar Trabajo</h1>
                    <p class="text-muted mb-0" id="trabajoCodigo">Código: -</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="ver.php?id=<?php echo $trabajo_id; ?>" class="btn btn-info">
                        <i class="bi bi-eye"></i> <span class="d-none d-sm-inline">Ver Detalles</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header" style="background-color: #1e3a8a; color: white;">
                        <i class="bi bi-pencil-square"></i> Información del Trabajo
                    </div>
                    <div class="card-body">
                        <form id="formTrabajo" method="POST">
                            <input type="hidden" name="id" value="<?php echo $trabajo_id; ?>">

                            <h5 class="mb-3 text-primary"><i class="bi bi-person"></i> Datos del Cliente</h5>

                            <div class="row g-3 mb-4">
                                <div class="col-md-8">
                                    <label class="form-label">Nombre del Cliente</label>
                                    <input type="text" class="form-control" id="cliente_nombre" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Teléfono</label>
                                    <input type="text" class="form-control" id="cliente_telefono" readonly>
                                </div>
                            </div>

                            <hr class="my-4">

                            <h5 class="mb-3 text-primary"><i class="bi bi-clipboard-check"></i> Estado del Trabajo</h5>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="estado" class="form-label"><i class="bi bi-flag"></i> Estado *</label>
                                    <select class="form-select" id="estado" name="estado" required>
                                        <option value="recibido">Recibido</option>
                                        <option value="en_proceso">En Proceso</option>
                                        <option value="completado">Completado</option>
                                        <option value="entregado">Entregado</option>
                                        <option value="cancelado">Cancelado</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="empleado_actual_id" class="form-label"><i class="bi bi-person-workspace"></i> Orfebre Asignado *</label>
                                    <select class="form-select" id="empleado_actual_id" name="empleado_actual_id" required>
                                        <option value="">Seleccione...</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="fecha_entrega_prometida" class="form-label"><i class="bi bi-calendar-event"></i> Fecha Entrega Prometida *</label>
                                    <input type="date" class="form-control" id="fecha_entrega_prometida" name="fecha_entrega_prometida" required>
                                </div>
                                <div class="col-md-6" id="fechaEntregaRealContainer" style="display: none;">
                                    <label for="fecha_entrega_real" class="form-label"><i class="bi bi-check-circle"></i> Fecha Entrega Real</label>
                                    <input type="datetime-local" class="form-control" id="fecha_entrega_real" name="fecha_entrega_real">
                                </div>
                            </div>

                            <hr class="my-4">

                            <h5 class="mb-3 text-primary"><i class="bi bi-file-text"></i> Descripción del Trabajo</h5>

                            <div class="mb-3">
                                <label for="descripcion_trabajo" class="form-label">Trabajo a Realizar *</label>
                                <textarea class="form-control" id="descripcion_trabajo" name="descripcion_trabajo" rows="3" required></textarea>
                            </div>

                            <div class="mb-4">
                                <label for="observaciones" class="form-label"><i class="bi bi-chat-left-text"></i> Observaciones</label>
                                <textarea class="form-control" id="observaciones" name="observaciones" rows="2"></textarea>
                            </div>

                            <hr class="my-4">

                            <h5 class="mb-3 text-primary"><i class="bi bi-cash"></i> Información de Pago</h5>

                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label for="precio_total" class="form-label">Precio Total (Q) *</label>
                                    <input type="number" class="form-control" id="precio_total" name="precio_total" min="0" step="0.01" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Anticipo Recibido (Q)</label>
                                    <input type="text" class="form-control" id="anticipo_display" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Saldo Pendiente (Q)</label>
                                    <input type="text" class="form-control text-danger fw-bold" id="saldo" readonly>
                                </div>
                            </div>

                            <div class="d-flex flex-column flex-sm-row justify-content-between gap-2">
                                <a href="lista.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
                                <div class="d-flex flex-column flex-sm-row gap-2">
                                    <a href="ver.php?id=<?php echo $trabajo_id; ?>" class="btn btn-info"><i class="bi bi-eye"></i> Ver Detalles</a>
                                    <button type="submit" class="btn btn-primary" id="btnGuardar"><i class="bi bi-save"></i> Guardar Cambios</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card mb-3 shadow-sm">
                    <div class="card-header" style="background-color: #1e3a8a; color: white;">
                        <i class="bi bi-info-circle"></i> Información del Trabajo
                    </div>
                    <div class="card-body" id="infoTrabajo">
                        <div class="text-center py-3"><div class="spinner-border spinner-border-sm"></div></div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header"><i class="bi bi-lightning"></i> Acciones Rápidas</div>
                    <div class="list-group list-group-flush">
                        <a href="ver.php?id=<?php echo $trabajo_id; ?>" class="list-group-item list-group-item-action">
                            <i class="bi bi-clock-history"></i> Ver historial de transferencias
                        </a>
                        <a href="transferir.php?id=<?php echo $trabajo_id; ?>" class="list-group-item list-group-item-action">
                            <i class="bi bi-arrow-left-right"></i> Transferir a otro orfebre
                        </a>
                        <a href="#" onclick="imprimirOrden(); return false;" class="list-group-item list-group-item-action">
                            <i class="bi bi-printer"></i> Imprimir orden de trabajo
                        </a>
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
.page-header h1 { font-size: 1.75rem; font-weight: 600; color: #1a1a1a; }
.shadow-sm { box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08) !important; }
.card-body { padding: 25px; }
.form-label { font-weight: 500; margin-bottom: 0.5rem; color: #374151; }
.form-control, .form-select { border: 1px solid #d1d5db; border-radius: 6px; padding: 0.625rem 0.75rem; }
.form-control:focus, .form-select:focus { border-color: #1e3a8a; box-shadow: 0 0 0 0.2rem rgba(30, 58, 138, 0.15); }
textarea.form-control { resize: vertical; }
h5.text-primary { color: #1e3a8a !important; font-weight: 600; }
hr { opacity: 0.1; }
.card-body > p:not(:last-child) { padding-bottom: 12px; margin-bottom: 12px; border-bottom: 1px solid #e5e7eb; }
.list-group-item { transition: background-color 0.15s ease; }
.list-group-item:hover { background-color: #f3f4f6; }
@media (max-width: 575.98px) {
    .main-content { padding: 15px 10px; }
    .page-header h1 { font-size: 1.5rem; }
    .card-body { padding: 15px; }
    h5 { font-size: 1.1rem; }
    .btn { width: 100%; }
}
@media (min-width: 576px) and (max-width: 767.98px) { .main-content { padding: 18px 15px; } }
@media (min-width: 992px) { .main-content { padding: 25px 30px; } }
@media (max-width: 767.98px) { .btn, .form-control, .form-select, textarea { min-height: 44px; } }
</style>

<script>
let anticipoActual = 0;

document.addEventListener('DOMContentLoaded', function() {
    cargarDatosTrabajo();
    cargarOrfebres();
});

function cargarDatosTrabajo() {
    const trabajoId = <?php echo $trabajo_id; ?>;
    
    /* TODO FASE 5: Descomentar
    fetch('<?php echo BASE_URL; ?>api/taller/ver.php?id=' + trabajoId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                llenarFormulario(data.data);
                mostrarInfoTrabajo(data.data);
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

function cargarOrfebres() {
    /* TODO FASE 5: Descomentar
    fetch('<?php echo BASE_URL; ?>api/empleados/lista.php?rol=orfebre')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('empleado_actual_id');
                data.data.forEach(emp => {
                    const option = document.createElement('option');
                    option.value = emp.id;
                    option.textContent = emp.nombre;
                    select.appendChild(option);
                });
            }
        });
    */
}

function llenarFormulario(trabajo) {
    document.getElementById('loadingState').style.display = 'none';
    document.getElementById('mainContent').style.display = 'block';
    
    document.getElementById('trabajoCodigo').textContent = 'Código: ' + trabajo.codigo;
    document.getElementById('cliente_nombre').value = trabajo.cliente_nombre;
    document.getElementById('cliente_telefono').value = trabajo.cliente_telefono;
    document.getElementById('estado').value = trabajo.estado;
    document.getElementById('empleado_actual_id').value = trabajo.empleado_actual_id;
    document.getElementById('fecha_entrega_prometida').value = trabajo.fecha_entrega_prometida;
    document.getElementById('descripcion_trabajo').value = trabajo.descripcion_trabajo;
    document.getElementById('observaciones').value = trabajo.observaciones || '';
    document.getElementById('precio_total').value = trabajo.precio_total;
    
    anticipoActual = parseFloat(trabajo.anticipo);
    document.getElementById('anticipo_display').value = anticipoActual.toFixed(2);
    
    calcularSaldo();
    
    if (trabajo.estado === 'entregado') {
        document.getElementById('fechaEntregaRealContainer').style.display = 'block';
        document.getElementById('fecha_entrega_real').required = true;
        if (trabajo.fecha_entrega_real) {
            document.getElementById('fecha_entrega_real').value = trabajo.fecha_entrega_real.replace(' ', 'T');
        }
    }
}

function mostrarInfoTrabajo(trabajo) {
    const iconosMaterial = { 'oro': '🟡', 'plata': '⚪', 'otro': '⚫' };
    
    document.getElementById('infoTrabajo').innerHTML = `
        <p><strong>Fecha de Recepción:</strong><br>${formatearFecha(trabajo.fecha_recepcion)}</p>
        <p><strong>Pieza:</strong><br>${trabajo.descripcion_pieza}</p>
        <p><strong>Material:</strong><br>${iconosMaterial[trabajo.material] || ''} ${trabajo.material.charAt(0).toUpperCase() + trabajo.material.slice(1)}${trabajo.peso_gramos ? ' (' + trabajo.peso_gramos + 'g)' : ''}</p>
        <p><strong>Tipo de Trabajo:</strong><br>${trabajo.tipo_trabajo.charAt(0).toUpperCase() + trabajo.tipo_trabajo.slice(1)}</p>
    `;
}

document.getElementById('estado').addEventListener('change', function() {
    const container = document.getElementById('fechaEntregaRealContainer');
    if (this.value === 'entregado') {
        container.style.display = 'block';
        document.getElementById('fecha_entrega_real').required = true;
    } else {
        container.style.display = 'none';
        document.getElementById('fecha_entrega_real').required = false;
    }
});

function calcularSaldo() {
    const precio = parseFloat(document.getElementById('precio_total').value) || 0;
    const saldo = precio - anticipoActual;
    document.getElementById('saldo').value = saldo.toFixed(2);
}

document.getElementById('precio_total').addEventListener('input', calcularSaldo);

document.getElementById('formTrabajo').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const datos = {
        id: formData.get('id'),
        estado: formData.get('estado'),
        empleado_actual_id: formData.get('empleado_actual_id'),
        fecha_entrega_prometida: formData.get('fecha_entrega_prometida'),
        fecha_entrega_real: formData.get('fecha_entrega_real') || null,
        descripcion_trabajo: formData.get('descripcion_trabajo'),
        observaciones: formData.get('observaciones') || null,
        precio_total: parseFloat(formData.get('precio_total'))
    };
    
    const btnGuardar = document.getElementById('btnGuardar');
    btnGuardar.disabled = true;
    btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
    
    /* TODO FASE 5: Descomentar
    fetch('<?php echo BASE_URL; ?>api/taller/actualizar.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(datos)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Trabajo actualizado exitosamente');
            setTimeout(() => window.location.href = 'ver.php?id=' + datos.id, 1500);
        } else {
            alert(data.message);
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = '<i class="bi bi-save"></i> Guardar Cambios';
        }
    });
    */
    
    console.log('Datos:', datos);
    setTimeout(() => {
        alert('MODO DESARROLLO: Cambios listos.\n\n' + JSON.stringify(datos, null, 2));
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = '<i class="bi bi-save"></i> Guardar Cambios';
    }, 1000);
});

function mostrarError(mensaje) {
    document.getElementById('loadingState').style.display = 'none';
    document.getElementById('errorState').style.display = 'block';
    document.getElementById('errorMessage').textContent = mensaje;
}

function formatearFecha(fecha) {
    const d = new Date(fecha);
    return d.toLocaleDateString('es-GT', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function imprimirOrden() { alert('MODO DESARROLLO: Imprimir orden - Pendiente implementar'); }
</script>

<?php include '../../includes/footer.php'; ?>