<?php
// ================================================
// MÓDULO CAJA - CERRAR CAJA (ARQUEO)
// ================================================

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

// Verificar autenticación y permisos
requiere_autenticacion();
requiere_rol(['administrador', 'dueño', 'cajero']);

// Obtener ID de la caja
$caja_id = $_GET['id'] ?? null;

if (!$caja_id) {
    header('Location: lista.php');
    exit;
}

// Datos dummy de la caja
$caja = [
    'id' => $caja_id,
    'usuario_nombre' => 'María Vendedor',
    'sucursal_nombre' => 'Los Arcos',
    'fecha_apertura' => '2025-01-24 08:00:00',
    'monto_inicial' => 500.00,
    'total_ingresos' => 13750.00,
    'total_egresos' => 450.00,
    'monto_esperado' => 13800.00,
    'estado' => 'abierta'
];

// Verificar que la caja está abierta
if ($caja['estado'] != 'abierta') {
    header('Location: ver.php?id=' . $caja_id);
    exit;
}

// Título de página
$titulo_pagina = 'Cerrar Caja';

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
            <li class="breadcrumb-item">
                <a href="ver.php?id=<?php echo $caja['id']; ?>">
                    Caja #<?php echo $caja['id']; ?>
                </a>
            </li>
            <li class="breadcrumb-item active">Cerrar Caja</li>
        </ol>
    </nav>

    <!-- Encabezado -->
    <div class="page-header">
        <h1>
            <i class="bi bi-box-arrow-up"></i>
            Cierre de Caja (Arqueo)
        </h1>
        <p class="text-muted">Cuente el efectivo y cierre el turno</p>
    </div>

    <form id="formCerrarCaja" method="POST" action="">
        <input type="hidden" name="caja_id" value="<?php echo $caja['id']; ?>">
        
        <div class="row">
            <!-- Resumen de la Caja -->
            <div class="col-lg-4">
                <!-- Información del Turno -->
                <div class="card mb-3">
                    <div class="card-header bg-info text-white">
                        <i class="bi bi-info-circle"></i>
                        Información del Turno
                    </div>
                    <div class="card-body">
                        <p class="mb-2">
                            <strong>Usuario:</strong><br>
                            <?php echo $caja['usuario_nombre']; ?>
                        </p>
                        <p class="mb-2">
                            <strong>Sucursal:</strong><br>
                            <?php echo $caja['sucursal_nombre']; ?>
                        </p>
                        <p class="mb-2">
                            <strong>Apertura:</strong><br>
                            <?php echo date('d/m/Y H:i', strtotime($caja['fecha_apertura'])); ?>
                        </p>
                        <p class="mb-0">
                            <strong>Cierre:</strong><br>
                            <?php echo date('d/m/Y H:i'); ?>
                        </p>
                    </div>
                </div>

                <!-- Monto Esperado -->
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <i class="bi bi-calculator"></i>
                        Efectivo Esperado
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Monto Inicial:</span>
                            <span>Q <?php echo number_format($caja['monto_inicial'], 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 text-success">
                            <span>+ Ingresos:</span>
                            <span>Q <?php echo number_format($caja['total_ingresos'], 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 text-danger">
                            <span>- Egresos:</span>
                            <span>Q <?php echo number_format($caja['total_egresos'], 2); ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <h4 class="mb-0">Total Esperado:</h4>
                            <h3 class="mb-0 text-success" id="montoEsperado">
                                Q <?php echo number_format($caja['monto_esperado'], 2); ?>
                            </h3>
                        </div>
                        <input type="hidden" name="monto_esperado" value="<?php echo $caja['monto_esperado']; ?>">
                    </div>
                </div>
            </div>

            <!-- Arqueo de Efectivo -->
            <div class="col-lg-8">
                <!-- Conteo de Efectivo -->
                <div class="card mb-3">
                    <div class="card-header bg-warning">
                        <i class="bi bi-calculator-fill"></i>
                        Conteo de Efectivo Real
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            Cuente el efectivo en caja e ingrese las cantidades
                        </div>

                        <!-- Desglose de Billetes -->
                        <h6 class="fw-bold mb-3">Billetes:</h6>
                        <div class="row g-2 mb-3">
                            <div class="col-md-3">
                                <label class="form-label small">Q 200.00 x</label>
                                <input type="number" class="form-control desglose" 
                                       data-valor="200" min="0" value="0" name="billetes_200">
                                <small class="text-muted total-parcial">Q 0.00</small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Q 100.00 x</label>
                                <input type="number" class="form-control desglose" 
                                       data-valor="100" min="0" value="0" name="billetes_100">
                                <small class="text-muted total-parcial">Q 0.00</small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Q 50.00 x</label>
                                <input type="number" class="form-control desglose" 
                                       data-valor="50" min="0" value="0" name="billetes_50">
                                <small class="text-muted total-parcial">Q 0.00</small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Q 20.00 x</label>
                                <input type="number" class="form-control desglose" 
                                       data-valor="20" min="0" value="0" name="billetes_20">
                                <small class="text-muted total-parcial">Q 0.00</small>
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-3">
                                <label class="form-label small">Q 10.00 x</label>
                                <input type="number" class="form-control desglose" 
                                       data-valor="10" min="0" value="0" name="billetes_10">
                                <small class="text-muted total-parcial">Q 0.00</small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Q 5.00 x</label>
                                <input type="number" class="form-control desglose" 
                                       data-valor="5" min="0" value="0" name="billetes_5">
                                <small class="text-muted total-parcial">Q 0.00</small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Q 1.00 x</label>
                                <input type="number" class="form-control desglose" 
                                       data-valor="1" min="0" value="0" name="billetes_1">
                                <small class="text-muted total-parcial">Q 0.00</small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Monedas (Q)</label>
                                <input type="number" class="form-control" 
                                       id="monedas" min="0" step="0.01" value="0" name="monedas">
                            </div>
                        </div>

                        <hr>

                        <!-- Total Real -->
                        <div class="row">
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

                <!-- Observaciones -->
                <div class="card mb-3">
                    <div class="card-header">
                        <i class="bi bi-chat-left-text"></i>
                        Observaciones del Cierre
                    </div>
                    <div class="card-body">
                        <textarea class="form-control" 
                                  id="observaciones_cierre" 
                                  name="observaciones_cierre" 
                                  rows="3"
                                  placeholder="Anote cualquier observación sobre el cierre (opcional)"></textarea>
                        <small class="text-muted">
                            En caso de faltante o sobrante, explique el motivo si es conocido
                        </small>
                    </div>
                </div>

                <!-- Información Técnica -->
                <div class="alert alert-success">
                    <h6 class="fw-bold">
                        <i class="bi bi-database"></i>
                        Proceso al cerrar:
                    </h6>
                    <ol class="mb-0 small">
                        <li>Se actualizará la tabla <code>cajas</code>:
                            <ul>
                                <li><code>fecha_cierre</code> = ahora</li>
                                <li><code>monto_esperado</code> = <?php echo $caja['monto_esperado']; ?></li>
                                <li><code>monto_real</code> = valor contado</li>
                                <li><code>diferencia</code> = monto_real - monto_esperado (calculado automáticamente)</li>
                                <li><code>observaciones_cierre</code> = texto ingresado</li>
                                <li><code>estado</code> = 'cerrada'</li>
                            </ul>
                        </li>
                        <li>No se pueden modificar cajas cerradas</li>
                    </ol>
                </div>

                <!-- Botones -->
                <div class="d-flex justify-content-between">
                    <a href="ver.php?id=<?php echo $caja['id']; ?>" class="btn btn-secondary btn-lg">
                        <i class="bi bi-arrow-left"></i>
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-danger btn-lg">
                        <i class="bi bi-box-arrow-up"></i>
                        Cerrar Caja
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
const montoEsperado = <?php echo $caja['monto_esperado']; ?>;

// Calcular desglose
document.querySelectorAll('.desglose').forEach((input, index) => {
    input.addEventListener('input', function() {
        const valor = parseFloat(this.dataset.valor);
        const cantidad = parseInt(this.value) || 0;
        const total = valor * cantidad;
        
        // Actualizar total parcial
        const parcialElement = this.parentElement.querySelector('.total-parcial');
        parcialElement.textContent = 'Q ' + total.toFixed(2);
        
        calcularTotal();
    });
});

document.getElementById('monedas').addEventListener('input', calcularTotal);

function calcularTotal() {
    let total = 0;
    
    // Sumar billetes
    document.querySelectorAll('.desglose').forEach(input => {
        const valor = parseFloat(input.dataset.valor) || 0;
        const cantidad = parseInt(input.value) || 0;
        total += valor * cantidad;
    });
    
    // Sumar monedas
    total += parseFloat(document.getElementById('monedas').value) || 0;
    
    // Actualizar total real
    document.getElementById('montoReal').textContent = 'Q ' + total.toFixed(2);
    document.getElementById('montoRealInput').value = total.toFixed(2);
    
    // Calcular diferencia
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

// Validación del formulario
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
        if (diferencia < 0) {
            mensaje += 'Faltante de Q ' + Math.abs(diferencia).toFixed(2);
        } else {
            mensaje += 'Sobrante de Q ' + diferencia.toFixed(2);
        }
        
        if (!document.getElementById('observaciones_cierre').value) {
            alert('Por favor agregue observaciones explicando la diferencia');
            document.getElementById('observaciones_cierre').focus();
            return;
        }
    }
    
    if (confirm(mensaje)) {
        // API ejecutará:
        // UPDATE cajas 
        // SET fecha_cierre = NOW(),
        //     monto_esperado = ?,
        //     monto_real = ?,
        //     observaciones_cierre = ?,
        //     estado = 'cerrada'
        // WHERE id = ?
        
        alert('Caja cerrada exitosamente. Redirigiendo...');
        
        const formData = new FormData(this);
        console.log('Datos de cierre:', Object.fromEntries(formData));
        
        // window.location.href = 'ver.php?id=' + formData.get('caja_id');
    }
});
</script>

<?php
// Incluir footer
include '../../includes/footer.php';
?>