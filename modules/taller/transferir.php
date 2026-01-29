<?php
/**
 * ================================================
 * MÓDULO TALLER - TRANSFERIR TRABAJO
 * ================================================
 * 
 * TODO FASE 5: Conectar con APIs
 * GET /api/taller/ver.php?id={trabajo_id} - Cargar datos trabajo
 * GET /api/taller/lista.php?estado=en_proceso - Lista trabajos (si no viene ID)
 * GET /api/empleados/lista.php?rol=orfebre - Cargar orfebres
 * POST /api/taller/transferir.php - Procesar transferencia
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();
requiere_rol(['administrador', 'dueño', 'gerente']);

$trabajo_id = $_GET['id'] ?? null;
$trabajo = null;

$titulo_pagina = 'Transferir Trabajo';
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="container-fluid main-content">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>dashboard.php"><i class="bi bi-house"></i> Dashboard</a></li>
            <li class="breadcrumb-item"><a href="lista.php"><i class="bi bi-tools"></i> Taller</a></li>
            <li class="breadcrumb-item active">Transferir Trabajo</li>
        </ol>
    </nav>

    <div class="page-header mb-4">
        <h1 class="mb-2"><i class="bi bi-arrow-left-right"></i> Transferir Trabajo a Otro Orfebre</h1>
        <p class="text-muted mb-0">Registro inmutable de transferencia entre empleados</p>
    </div>

    <div id="loadingState" class="text-center py-5" style="<?php echo $trabajo_id ? '' : 'display: none;'; ?>">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
        <p class="mt-3 text-muted">Cargando datos del trabajo...</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div id="alertTrabajo" class="alert alert-info" style="display: none;">
                <h5 class="alert-heading"><i class="bi bi-info-circle"></i> Trabajo a Transferir</h5>
                <p class="mb-0" id="infoTrabajo"></p>
            </div>

            <div class="card shadow-sm">
                <div class="card-header" style="background-color: #1e3a8a; color: white;">
                    <i class="bi bi-pencil-square"></i> Datos de la Transferencia
                </div>
                <div class="card-body">
                    <form id="formTransferencia" method="POST">
                        <input type="hidden" id="trabajo_id" name="trabajo_id" value="<?php echo $trabajo_id ?? ''; ?>">
                        <input type="hidden" id="empleado_origen_id" name="empleado_origen_id">

                        <?php if (!$trabajo_id): ?>
                        <div class="mb-4">
                            <label for="trabajo_select" class="form-label"><i class="bi bi-search"></i> Seleccionar Trabajo *</label>
                            <select class="form-select" id="trabajo_select" required>
                                <option value="">Busque por código o cliente...</option>
                            </select>
                        </div>
                        <hr class="my-4">
                        <?php endif; ?>

                        <h5 class="mb-3 text-primary"><i class="bi bi-arrow-left-right"></i> Información de Transferencia</h5>

                        <div class="mb-3">
                            <label for="empleado_destino_id" class="form-label"><i class="bi bi-person-workspace"></i> Transferir a Orfebre *</label>
                            <select class="form-select" id="empleado_destino_id" name="empleado_destino_id" required>
                                <option value="">Seleccione...</option>
                            </select>
                        </div>

                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>Importante:</strong> La transferencia se registrará en la tabla <code>transferencias_trabajo</code> que es INMUTABLE y sirve como auditoría completa.
                        </div>

                        <div class="mb-4">
                            <label for="nota" class="form-label"><i class="bi bi-chat-left-text"></i> Nota / Motivo de la Transferencia</label>
                            <textarea class="form-control" id="nota" name="nota" rows="3" placeholder="Ej: Transferido por carga de trabajo, especialización requerida, etc."></textarea>
                        </div>

                        <div class="d-flex flex-column flex-sm-row justify-content-end gap-2">
                            <a href="<?php echo $trabajo_id ? 'ver.php?id=' . $trabajo_id : 'lista.php'; ?>" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary" id="btnTransferir">
                                <i class="bi bi-arrow-left-right"></i> Realizar Transferencia
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mt-3 shadow-sm">
                <div class="card-header" style="background-color: #1e3a8a; color: white;">
                    <i class="bi bi-info-circle"></i> Cómo Funciona la Transferencia
                </div>
                <div class="card-body">
                    <h6 class="fw-bold">Proceso automático al transferir:</h6>
                    <ol class="mb-3">
                        <li>Se actualiza el campo <code>empleado_actual_id</code> en <code>trabajos_taller</code></li>
                        <li>Se crea un registro INMUTABLE en <code>transferencias_trabajo</code> con:
                            <ul class="mt-2">
                                <li><code>trabajo_id</code></li>
                                <li><code>empleado_origen_id</code></li>
                                <li><code>empleado_destino_id</code></li>
                                <li><code>fecha_transferencia</code> (automática)</li>
                                <li><code>estado_trabajo_momento</code> (estado actual del trabajo)</li>
                                <li><code>nota</code> (motivo)</li>
                                <li><code>usuario_registra_id</code> (quien hace la transferencia)</li>
                            </ul>
                        </li>
                    </ol>

                    <div class="alert alert-success mb-0">
                        <i class="bi bi-shield-check"></i>
                        <strong>Auditoría Completa:</strong> Cada transferencia queda registrada permanentemente, permitiendo rastrear la trazabilidad completa del trabajo.
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
.card-body { padding: 25px; }
.form-label { font-weight: 500; margin-bottom: 0.5rem; color: #374151; }
.form-control, .form-select { border: 1px solid #d1d5db; border-radius: 6px; padding: 0.625rem 0.75rem; }
.form-control:focus, .form-select:focus { border-color: #1e3a8a; box-shadow: 0 0 0 0.2rem rgba(30, 58, 138, 0.15); }
textarea.form-control { resize: vertical; }
h5.text-primary { color: #1e3a8a !important; font-weight: 600; }
h6.fw-bold { color: #1a1a1a; font-size: 0.95rem; margin-bottom: 0.75rem; }
code { background-color: #f3f4f6; padding: 2px 6px; border-radius: 4px; font-size: 0.9em; color: #1e3a8a; }
hr { opacity: 0.1; }
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
let trabajoActual = null;

document.addEventListener('DOMContentLoaded', function() {
    cargarOrfebres();
    
    <?php if ($trabajo_id): ?>
    cargarDatosTrabajo(<?php echo $trabajo_id; ?>);
    <?php else: ?>
    cargarTrabajos();
    <?php endif; ?>
});

function cargarDatosTrabajo(trabajoId) {
    /* TODO FASE 5: Descomentar
    fetch('<?php echo BASE_URL; ?>api/taller/ver.php?id=' + trabajoId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                trabajoActual = data.data;
                mostrarInfoTrabajo(data.data);
                document.getElementById('empleado_origen_id').value = data.data.empleado_actual_id;
                document.getElementById('loadingState').style.display = 'none';
            }
        })
        .catch(error => console.error('Error:', error));
    */
    
    setTimeout(() => {
        document.getElementById('loadingState').style.display = 'none';
        alert('MODO DESARROLLO: Cargar datos del trabajo - Pendiente API');
    }, 1000);
}

function cargarTrabajos() {
    /* TODO FASE 5: Descomentar
    fetch('<?php echo BASE_URL; ?>api/taller/lista.php?estado=en_proceso')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('trabajo_select');
                data.data.forEach(t => {
                    const option = document.createElement('option');
                    option.value = t.id;
                    option.textContent = `${t.codigo} - ${t.cliente_nombre} - ${t.descripcion_pieza}`;
                    option.dataset.empleadoId = t.empleado_actual_id;
                    select.appendChild(option);
                });
            }
        });
    */
}

function cargarOrfebres() {
    /* TODO FASE 5: Descomentar
    fetch('<?php echo BASE_URL; ?>api/empleados/lista.php?rol=orfebre')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('empleado_destino_id');
                data.data.forEach(emp => {
                    const option = document.createElement('option');
                    option.value = emp.id;
                    option.textContent = emp.nombre;
                    select.appendChild(option);
                });
            }
        });
    */
    
    const orfebres = ['Roberto Orfebre', 'Juan Artesano', 'Pedro Maestro'];
    const select = document.getElementById('empleado_destino_id');
    orfebres.forEach((nombre, i) => {
        const option = document.createElement('option');
        option.value = i + 3;
        option.textContent = nombre;
        select.appendChild(option);
    });
}

function mostrarInfoTrabajo(trabajo) {
    document.getElementById('infoTrabajo').innerHTML = `
        <strong>Código:</strong> ${trabajo.codigo}<br>
        <strong>Cliente:</strong> ${trabajo.cliente_nombre}<br>
        <strong>Pieza:</strong> ${trabajo.descripcion_pieza}<br>
        <strong>Orfebre actual:</strong> ${trabajo.empleado_actual_nombre}
    `;
    document.getElementById('alertTrabajo').style.display = 'block';
}

<?php if (!$trabajo_id): ?>
document.getElementById('trabajo_select').addEventListener('change', function() {
    const trabajoId = this.value;
    if (trabajoId) {
        document.getElementById('trabajo_id').value = trabajoId;
        document.getElementById('empleado_origen_id').value = this.options[this.selectedIndex].dataset.empleadoId;
        cargarDatosTrabajo(trabajoId);
    }
});
<?php endif; ?>

document.getElementById('empleado_destino_id').addEventListener('change', function() {
    const origen = parseInt(document.getElementById('empleado_origen_id').value);
    const destino = parseInt(this.value);
    
    if (origen && destino && origen === destino) {
        alert('No puede transferir el trabajo al mismo orfebre que lo tiene actualmente');
        this.value = '';
    }
});

document.getElementById('formTransferencia').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!confirm('¿Confirma la transferencia de este trabajo? Esta acción quedará registrada permanentemente.')) {
        return;
    }
    
    const formData = new FormData(this);
    const datos = {
        trabajo_id: formData.get('trabajo_id'),
        empleado_origen_id: formData.get('empleado_origen_id'),
        empleado_destino_id: formData.get('empleado_destino_id'),
        nota: formData.get('nota') || null
    };
    
    const btnTransferir = document.getElementById('btnTransferir');
    btnTransferir.disabled = true;
    btnTransferir.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...';
    
    /* TODO FASE 5: Descomentar
    fetch('<?php echo BASE_URL; ?>api/taller/transferir.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(datos)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Transferencia realizada exitosamente');
            setTimeout(() => window.location.href = 'ver.php?id=' + datos.trabajo_id, 1500);
        } else {
            alert(data.message);
            btnTransferir.disabled = false;
            btnTransferir.innerHTML = '<i class="bi bi-arrow-left-right"></i> Realizar Transferencia';
        }
    });
    */
    
    console.log('Datos transferencia:', datos);
    setTimeout(() => {
        alert('MODO DESARROLLO: Transferencia lista.\n\n' + JSON.stringify(datos, null, 2));
        btnTransferir.disabled = false;
        btnTransferir.innerHTML = '<i class="bi bi-arrow-left-right"></i> Realizar Transferencia';
    }, 1000);
});
</script>

<?php include '../../includes/footer.php'; ?>