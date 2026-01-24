<?php
// ================================================
// MÓDULO CAJA - ABRIR CAJA
// ================================================

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

// Verificar autenticación y permisos
requiere_autenticacion();
requiere_rol(['administrador', 'dueño', 'cajero']);

// Datos del usuario actual
$usuario_actual = [
    'id' => $_SESSION['usuario_id'],
    'nombre' => $_SESSION['usuario_nombre'],
    'sucursal_id' => $_SESSION['usuario_sucursal_id'],
    'sucursal_nombre' => $_SESSION['usuario_sucursal_nombre']
];

// Título de página
$titulo_pagina = 'Abrir Caja';

// Incluir header
include '../../includes/header.php';

// Incluir navbar
include '../../includes/navbar.php';
?>

<!-- Contenido Principal -->
<div class="container-fluid main-content">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="<?php echo BASE_URL; ?>dashboard.php">
                    <i class="bi bi-house"></i> Dashboard
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="lista.php">
                    <i class="bi bi-cash-stack"></i> Caja
                </a>
            </li>
            <li class="breadcrumb-item active">Abrir Caja</li>
        </ol>
    </nav>

    <!-- Encabezado -->
    <div class="page-header">
        <h1>
            <i class="bi bi-box-arrow-in-down"></i>
            Apertura de Caja
        </h1>
        <p class="text-muted">Iniciar turno de caja</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <!-- Información del Turno -->
            <div class="alert alert-info">
                <h5 class="alert-heading">
                    <i class="bi bi-info-circle"></i>
                    Información del Turno
                </h5>
                <p class="mb-0">
                    <strong>Usuario:</strong> <?php echo $usuario_actual['nombre']; ?><br>
                    <strong>Sucursal:</strong> <?php echo $usuario_actual['sucursal_nombre']; ?><br>
                    <strong>Fecha y Hora:</strong> <?php echo date('d/m/Y H:i:s'); ?>
                </p>
            </div>

            <!-- Formulario de Apertura -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-pencil-square"></i>
                    Datos de Apertura
                </div>
                <div class="card-body">
                    <form id="formAbrirCaja" method="POST" action="">
                        <input type="hidden" name="usuario_id" value="<?php echo $usuario_actual['id']; ?>">
                        <input type="hidden" name="sucursal_id" value="<?php echo $usuario_actual['sucursal_id']; ?>">

                        <!-- Monto Inicial -->
                        <div class="mb-4">
                            <label for="monto_inicial" class="form-label">
                                <i class="bi bi-cash-coin"></i>
                                Monto Inicial en Efectivo (Q) *
                            </label>
                            <input type="number" 
                                   class="form-control form-control-lg" 
                                   id="monto_inicial" 
                                   name="monto_inicial" 
                                   min="0" 
                                   step="0.01"
                                   placeholder="0.00"
                                   required
                                   autofocus>
                            <small class="text-muted">
                                Efectivo con el que se inicia el turno (fondo de cambio)
                            </small>
                        </div>

                        <!-- Desglose de Billetes y Monedas (Opcional) -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <i class="bi bi-list-check"></i>
                                Desglose de Efectivo (Opcional)
                            </div>
                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="col-6">
                                        <label class="form-label small">Q 200.00 x</label>
                                        <input type="number" class="form-control form-control-sm desglose" 
                                               data-valor="200" min="0" value="0">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small">Q 100.00 x</label>
                                        <input type="number" class="form-control form-control-sm desglose" 
                                               data-valor="100" min="0" value="0">
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-6">
                                        <label class="form-label small">Q 50.00 x</label>
                                        <input type="number" class="form-control form-control-sm desglose" 
                                               data-valor="50" min="0" value="0">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small">Q 20.00 x</label>
                                        <input type="number" class="form-control form-control-sm desglose" 
                                               data-valor="20" min="0" value="0">
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-6">
                                        <label class="form-label small">Q 10.00 x</label>
                                        <input type="number" class="form-control form-control-sm desglose" 
                                               data-valor="10" min="0" value="0">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small">Q 5.00 x</label>
                                        <input type="number" class="form-control form-control-sm desglose" 
                                               data-valor="5" min="0" value="0">
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-6">
                                        <label class="form-label small">Q 1.00 x</label>
                                        <input type="number" class="form-control form-control-sm desglose" 
                                               data-valor="1" min="0" value="0">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small">Monedas</label>
                                        <input type="number" class="form-control form-control-sm" 
                                               id="monedas" min="0" step="0.01" value="0">
                                    </div>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <strong>Total Desglose:</strong>
                                    <strong id="totalDesglose">Q 0.00</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Información Técnica -->
                        <div class="alert alert-success">
                            <h6 class="fw-bold">
                                <i class="bi bi-database"></i>
                                Registro en Base de Datos:
                            </h6>
                            <p class="mb-0 small">
                                Se creará un registro en la tabla <code>cajas</code> con:<br>
                                - <code>usuario_id</code>: <?php echo $usuario_actual['id']; ?><br>
                                - <code>sucursal_id</code>: <?php echo $usuario_actual['sucursal_id']; ?><br>
                                - <code>fecha_apertura</code>: Automática<br>
                                - <code>monto_inicial</code>: Valor ingresado<br>
                                - <code>estado</code>: 'abierta'
                            </p>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-between">
                            <a href="lista.php" class="btn btn-secondary btn-lg">
                                <i class="bi bi-x-circle"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-box-arrow-in-down"></i>
                                Abrir Caja
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Calcular desglose automáticamente
document.querySelectorAll('.desglose').forEach(input => {
    input.addEventListener('input', calcularDesglose);
});
document.getElementById('monedas').addEventListener('input', calcularDesglose);

function calcularDesglose() {
    let total = 0;
    
    // Sumar billetes
    document.querySelectorAll('.desglose').forEach(input => {
        const valor = parseFloat(input.dataset.valor) || 0;
        const cantidad = parseInt(input.value) || 0;
        total += valor * cantidad;
    });
    
    // Sumar monedas
    total += parseFloat(document.getElementById('monedas').value) || 0;
    
    document.getElementById('totalDesglose').textContent = 'Q ' + total.toFixed(2);
    
    // Actualizar monto inicial si el desglose está completo
    if (total > 0) {
        document.getElementById('monto_inicial').value = total.toFixed(2);
    }
}

// Validación del formulario
document.getElementById('formAbrirCaja').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const montoInicial = parseFloat(document.getElementById('monto_inicial').value);
    
    if (montoInicial <= 0) {
        alert('El monto inicial debe ser mayor a 0');
        return;
    }
    
    if (confirm('¿Confirma la apertura de caja con Q ' + montoInicial.toFixed(2) + '?')) {
        // API insertará en:
        // INSERT INTO cajas (usuario_id, sucursal_id, fecha_apertura, monto_inicial, estado)
        // VALUES (?, ?, NOW(), ?, 'abierta')
        
        alert('Caja abierta exitosamente. Redirigiendo...');
        
        const formData = new FormData(this);
        console.log('Datos de apertura:', Object.fromEntries(formData));
        
        // window.location.href = 'ver.php?id=' + nuevaCajaId;
    }
});
</script>

<?php
// Incluir footer
include '../../includes/footer.php';
?>