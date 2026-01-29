<?php
/**
 * ================================================
 * MÓDULO CAJA - ABRIR CAJA
 * ================================================
 * 
 * TODO FASE 5: Conectar con API
 * POST /api/caja/abrir.php - Crear apertura de caja
 * 
 * Inserta en tabla: cajas
 * Campos: usuario_id, sucursal_id, fecha_apertura, monto_inicial, estado='abierta'
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

$titulo_pagina = 'Abrir Caja';
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="container-fluid main-content">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>dashboard.php"><i class="bi bi-house"></i> Dashboard</a></li>
            <li class="breadcrumb-item"><a href="lista.php"><i class="bi bi-cash-stack"></i> Caja</a></li>
            <li class="breadcrumb-item active">Abrir Caja</li>
        </ol>
    </nav>

    <div class="page-header mb-4">
        <h1 class="mb-2"><i class="bi bi-box-arrow-in-down"></i> Apertura de Caja</h1>
        <p class="text-muted mb-0">Iniciar turno de caja</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="alert alert-info shadow-sm">
                <h5 class="alert-heading"><i class="bi bi-info-circle"></i> Información del Turno</h5>
                <p class="mb-0">
                    <strong>Usuario:</strong> <?php echo $usuario_actual['nombre']; ?><br>
                    <strong>Sucursal:</strong> <?php echo $usuario_actual['sucursal_nombre']; ?><br>
                    <strong>Fecha y Hora:</strong> <span id="fechaHora"></span>
                </p>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-pencil-square"></i> Datos de Apertura
                </div>
                <div class="card-body">
                    <form id="formAbrirCaja" method="POST">
                        <input type="hidden" name="usuario_id" value="<?php echo $usuario_actual['id']; ?>">
                        <input type="hidden" name="sucursal_id" value="<?php echo $usuario_actual['sucursal_id']; ?>">

                        <div class="mb-4">
                            <label for="monto_inicial" class="form-label">
                                <i class="bi bi-cash-coin"></i> Monto Inicial en Efectivo (Q) *
                            </label>
                            <input type="number" class="form-control form-control-lg" id="monto_inicial" name="monto_inicial" min="0" step="0.01" placeholder="0.00" required autofocus>
                            <small class="text-muted">Efectivo con el que se inicia el turno (fondo de cambio)</small>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header" style="background-color: #1e3a8a; color: white;">
                                <i class="bi bi-list-check"></i> Desglose de Efectivo (Opcional)
                            </div>
                            <div class="card-body">
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <label class="form-label small">Q 200.00 x</label>
                                        <input type="number" class="form-control form-control-sm desglose" data-valor="200" min="0" value="0">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small">Q 100.00 x</label>
                                        <input type="number" class="form-control form-control-sm desglose" data-valor="100" min="0" value="0">
                                    </div>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <label class="form-label small">Q 50.00 x</label>
                                        <input type="number" class="form-control form-control-sm desglose" data-valor="50" min="0" value="0">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small">Q 20.00 x</label>
                                        <input type="number" class="form-control form-control-sm desglose" data-valor="20" min="0" value="0">
                                    </div>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <label class="form-label small">Q 10.00 x</label>
                                        <input type="number" class="form-control form-control-sm desglose" data-valor="10" min="0" value="0">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small">Q 5.00 x</label>
                                        <input type="number" class="form-control form-control-sm desglose" data-valor="5" min="0" value="0">
                                    </div>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label small">Q 1.00 x</label>
                                        <input type="number" class="form-control form-control-sm desglose" data-valor="1" min="0" value="0">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small">Monedas</label>
                                        <input type="number" class="form-control form-control-sm" id="monedas" min="0" step="0.01" value="0">
                                    </div>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <strong>Total Desglose:</strong>
                                    <strong id="totalDesglose" class="text-primary">Q 0.00</strong>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-success">
                            <h6 class="fw-bold"><i class="bi bi-database"></i> Registro en Base de Datos:</h6>
                            <p class="mb-0 small">
                                Se creará un registro en la tabla <code>cajas</code> con:<br>
                                - <code>usuario_id</code>: <?php echo $usuario_actual['id']; ?><br>
                                - <code>sucursal_id</code>: <?php echo $usuario_actual['sucursal_id']; ?><br>
                                - <code>fecha_apertura</code>: Automática<br>
                                - <code>monto_inicial</code>: Valor ingresado<br>
                                - <code>estado</code>: 'abierta'
                            </p>
                        </div>

                        <div class="d-flex flex-column flex-sm-row justify-content-between gap-2">
                            <a href="lista.php" class="btn btn-secondary btn-lg">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-success btn-lg" id="btnAbrir">
                                <i class="bi bi-box-arrow-in-down"></i> Abrir Caja
                            </button>
                        </div>
                    </form>
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
.form-control, .form-select { border: 1px solid #d1d5db; border-radius: 6px; }
.form-control:focus, .form-select:focus { border-color: #1e3a8a; box-shadow: 0 0 0 0.2rem rgba(30, 58, 138, 0.15); }
.form-control-lg { font-size: 1.25rem; padding: 0.75rem 1rem; }
code { background-color: #f3f4f6; padding: 2px 6px; border-radius: 4px; font-size: 0.9em; color: #1e3a8a; }
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
document.addEventListener('DOMContentLoaded', function() {
    actualizarFechaHora();
    setInterval(actualizarFechaHora, 1000);
    
    document.querySelectorAll('.desglose').forEach(input => {
        input.addEventListener('input', calcularDesglose);
    });
    document.getElementById('monedas').addEventListener('input', calcularDesglose);
});

function actualizarFechaHora() {
    const ahora = new Date();
    document.getElementById('fechaHora').textContent = ahora.toLocaleDateString('es-GT', { 
        day: '2-digit', 
        month: '2-digit', 
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
}

function calcularDesglose() {
    let total = 0;
    
    document.querySelectorAll('.desglose').forEach(input => {
        const valor = parseFloat(input.dataset.valor) || 0;
        const cantidad = parseInt(input.value) || 0;
        total += valor * cantidad;
    });
    
    total += parseFloat(document.getElementById('monedas').value) || 0;
    
    document.getElementById('totalDesglose').textContent = 'Q ' + total.toFixed(2);
    
    if (total > 0) {
        document.getElementById('monto_inicial').value = total.toFixed(2);
    }
}

document.getElementById('formAbrirCaja').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const montoInicial = parseFloat(document.getElementById('monto_inicial').value);
    
    if (montoInicial <= 0) {
        alert('El monto inicial debe ser mayor a 0');
        return;
    }
    
    if (!confirm('¿Confirma la apertura de caja con Q ' + montoInicial.toFixed(2) + '?')) {
        return;
    }
    
    const formData = new FormData(this);
    const datos = {
        usuario_id: formData.get('usuario_id'),
        sucursal_id: formData.get('sucursal_id'),
        monto_inicial: montoInicial
    };
    
    const btnAbrir = document.getElementById('btnAbrir');
    btnAbrir.disabled = true;
    btnAbrir.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Abriendo caja...';
    
    /* TODO FASE 5: Descomentar
    fetch('<?php echo BASE_URL; ?>api/caja/abrir.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(datos)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Caja abierta exitosamente');
            setTimeout(() => window.location.href = 'ver.php?id=' + data.caja_id, 1500);
        } else {
            alert(data.message);
            btnAbrir.disabled = false;
            btnAbrir.innerHTML = '<i class="bi bi-box-arrow-in-down"></i> Abrir Caja';
        }
    });
    */
    
    console.log('Datos apertura:', datos);
    setTimeout(() => {
        alert('MODO DESARROLLO: Caja abierta.\n\n' + JSON.stringify(datos, null, 2));
        btnAbrir.disabled = false;
        btnAbrir.innerHTML = '<i class="bi bi-box-arrow-in-down"></i> Abrir Caja';
    }, 1000);
});
</script>

<?php include '../../includes/footer.php'; ?>