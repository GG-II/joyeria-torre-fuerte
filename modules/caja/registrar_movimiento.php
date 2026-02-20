<?php
/**
 * ================================================
 * MÓDULO CAJA - REGISTRAR MOVIMIENTO
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
            <h2 class="mb-1"><i class="bi bi-plus-circle"></i> Registrar Movimiento de Caja</h2>
            <p class="text-muted mb-0">Registrar ingresos y egresos manualmente</p>
        </div>
        <a href="lista.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    <hr class="border-warning border-2 opacity-75 mb-4">

    <div class="row">
        <!-- Formulario -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-clipboard"></i> Datos del Movimiento</h5>
                </div>
                <div class="card-body">
                    <form id="formMovimiento">
                        
                        <!-- Tipo de Movimiento -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                Tipo de Movimiento <span class="text-danger">*</span>
                            </label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="categoria" id="radioIngreso" value="ingreso" checked>
                                <label class="btn btn-outline-success" for="radioIngreso">
                                    <i class="bi bi-arrow-down-circle"></i> Ingreso
                                </label>
                                
                                <input type="radio" class="btn-check" name="categoria" id="radioEgreso" value="egreso">
                                <label class="btn btn-outline-danger" for="radioEgreso">
                                    <i class="bi bi-arrow-up-circle"></i> Egreso
                                </label>
                            </div>
                        </div>

                        <!-- Tipo Específico -->
                        <div class="mb-3">
                            <label for="tipoMovimiento" class="form-label">
                                Tipo Específico <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="tipoMovimiento" required>
                                <!-- Se llena dinámicamente según ingreso/egreso -->
                            </select>
                        </div>

                        <!-- Monto -->
                        <div class="mb-3">
                            <label for="monto" class="form-label">
                                Monto (Q) <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control form-control-lg" id="monto" 
                                   step="0.01" min="0.01" required placeholder="Ej: 150.00">
                        </div>

                        <!-- Concepto -->
                        <div class="mb-3">
                            <label for="concepto" class="form-label">
                                Concepto/Descripción <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" id="concepto" rows="3" required
                                      placeholder="Describa el motivo del movimiento..."></textarea>
                            <div class="form-text">Sea específico para facilitar el seguimiento.</div>
                        </div>

                        <!-- Botones -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle"></i> Registrar Movimiento
                            </button>
                            <a href="lista.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            
            <!-- Caja Actual -->
            <div class="card shadow-sm mb-4" id="cardCajaActual">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="bi bi-cash-coin"></i> Tu Caja Actual</h6>
                </div>
                <div class="card-body">
                    <div id="infoCajaActual">
                        <div class="text-center text-muted py-3">
                            <div class="spinner-border text-primary"></div>
                            <p class="mt-2">Verificando caja...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ayuda -->
            <div class="card shadow-sm border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="bi bi-info-circle"></i> Ayuda</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Ingresos:</strong></p>
                    <ul class="small mb-3">
                        <li>Devoluciones</li>
                        <li>Ventas no registradas</li>
                        <li>Otros ingresos extras</li>
                    </ul>

                    <p class="mb-2"><strong>Egresos:</strong></p>
                    <ul class="small mb-0">
                        <li>Gastos operativos</li>
                        <li>Pagos a proveedores</li>
                        <li>Compras de material</li>
                        <li>Servicios (luz, agua, etc.)</li>
                        <li>Otros gastos</li>
                    </ul>
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
let cajaActualId = null;

// Tipos de movimientos según categoría
const tiposMovimiento = {
    ingreso: {
        'venta': 'Venta',
        'ingreso_reparacion': 'Ingreso por Reparación',
        'anticipo_trabajo': 'Anticipo de Trabajo',
        'abono_credito': 'Abono a Crédito',
        'anticipo_apartado': 'Anticipo Mercadería Apartada',
        'otro_ingreso': 'Otro Ingreso'
    },
    egreso: {
        'gasto': 'Gasto Operativo',
        'pago_proveedor': 'Pago a Proveedor',
        'compra_material': 'Compra de Material',
        'alquiler': 'Alquiler',
        'salario': 'Salario',
        'servicio': 'Servicio (luz, agua, etc.)',
        'otro_egreso': 'Otro Egreso'
    }
};

async function init() {
    await verificarCajaAbierta();
    cargarTiposMovimiento('ingreso');
    configurarEventos();
}

async function verificarCajaAbierta() {
    try {
        const res = await fetch('/joyeria-torre-fuerte/api/caja/caja_actual.php');
        const data = await res.json();
        
        const container = document.getElementById('infoCajaActual');
if (!data.success || !data.data.tiene_caja_abierta) {
            container.innerHTML = 
                '<div class="alert alert-warning mb-0">' +
                '<i class="bi bi-exclamation-triangle"></i> ' +
                '<strong>No tienes una caja abierta</strong>' +
                '<p class="mb-0 mt-2">Debes abrir una caja antes de registrar movimientos.</p>' +
                '<a href="lista.php" class="btn btn-sm btn-warning mt-2">' +
                '<i class="bi bi-box-arrow-in-down"></i> Ir a abrir caja' +
                '</a>' +
                '</div>';
            
            // Deshabilitar formulario
            document.getElementById('formMovimiento').querySelectorAll('input, select, textarea, button[type="submit"]').forEach(el => {
                el.disabled = true;
            });
            
            return;
        }
        
        // Caja encontrada
        const caja = data.data.caja;
        cajaActualId = caja.id;
        
        container.innerHTML = 
            '<div class="mb-2">' +
            '<small class="text-muted">Sucursal:</small>' +
            '<p class="fw-bold mb-0">' + escaparHTML(caja.sucursal_nombre) + '</p>' +
            '</div>' +
            '<div class="mb-2">' +
            '<small class="text-muted">Apertura:</small>' +
            '<p class="mb-0">' + formatearFechaHora(caja.fecha_apertura) + '</p>' +
            '</div>' +
            '<hr>' +
            '<div class="mb-2">' +
            '<small class="text-muted">Monto Inicial:</small>' +
            '<p class="fw-bold text-primary mb-0">' + formatearMoneda(caja.totales.monto_inicial) + '</p>' +
            '</div>' +
            '<div class="mb-2">' +
            '<small class="text-muted">Total Ingresos:</small>' +
            '<p class="fw-bold text-success mb-0">' + formatearMoneda(caja.totales.total_ingresos) + '</p>' +
            '</div>' +
            '<div class="mb-2">' +
            '<small class="text-muted">Total Egresos:</small>' +
            '<p class="fw-bold text-danger mb-0">' + formatearMoneda(caja.totales.total_egresos) + '</p>' +
            '</div>' +
            '<hr>' +
            '<div>' +
            '<small class="text-muted">Total en Caja:</small>' +
            '<p class="fw-bold fs-4 mb-0">' + formatearMoneda(caja.totales.total_final) + '</p>' +
            '</div>';
        
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('infoCajaActual').innerHTML = 
            '<div class="alert alert-danger mb-0">' +
            '<i class="bi bi-x-circle"></i> Error al verificar caja' +
            '</div>';
    }
}

function cargarTiposMovimiento(categoria) {
    const select = document.getElementById('tipoMovimiento');
    select.innerHTML = '';
    
    const tipos = tiposMovimiento[categoria];
    
    Object.keys(tipos).forEach(key => {
        const option = document.createElement('option');
        option.value = key;
        option.textContent = tipos[key];
        select.appendChild(option);
    });
}

function configurarEventos() {
    // Cambiar tipos cuando cambia la categoría
    document.querySelectorAll('input[name="categoria"]').forEach(radio => {
        radio.addEventListener('change', function() {
            cargarTiposMovimiento(this.value);
            
            // Cambiar color del formulario según tipo
            const card = document.querySelector('#formMovimiento').closest('.card');
            if (this.value === 'ingreso') {
                card.querySelector('.card-header').className = 'card-header bg-success text-white';
            } else {
                card.querySelector('.card-header').className = 'card-header bg-danger text-white';
            }
        });
    });
    
    // Submit del formulario
    document.getElementById('formMovimiento').addEventListener('submit', registrarMovimiento);
}

async function registrarMovimiento(e) {
    e.preventDefault();
    
    if (!cajaActualId) {
        mostrarError('No tienes una caja abierta');
        return;
    }
    
    const categoria = document.querySelector('input[name="categoria"]:checked').value;
    const tipoMovimiento = document.getElementById('tipoMovimiento').value;
    const monto = parseFloat(document.getElementById('monto').value);
    const concepto = document.getElementById('concepto').value.trim();
    
    if (!concepto) {
        mostrarError('El concepto es requerido');
        return;
    }
    
    if (monto <= 0) {
        mostrarError('El monto debe ser mayor a 0');
        return;
    }
    
    try {
        mostrarCargando();
        
        const res = await fetch('/joyeria-torre-fuerte/api/caja/registrar-movimiento.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                caja_id: cajaActualId,
                tipo_movimiento: tipoMovimiento,
                categoria: categoria,
                concepto: concepto,
                monto: monto
            })
        });
        
        // DEBUG: Ver la respuesta cruda
        const textoRespuesta = await res.text();
        console.log('Respuesta cruda del API:', textoRespuesta);
        
        const resultado = JSON.parse(textoRespuesta);
        
        ocultarCargando();
        
        if (resultado.success) {
            await mostrarExito(resultado.message || 'Movimiento registrado exitosamente');
            
            // Limpiar formulario
            document.getElementById('formMovimiento').reset();
            document.getElementById('radioIngreso').checked = true;
            cargarTiposMovimiento('ingreso');
            
            // Recargar info de caja
            await verificarCajaAbierta();
            
        } else {
            mostrarError(resultado.error || resultado.message || 'Error al registrar movimiento');
        }
        
    } catch (error) {
        ocultarCargando();
        console.error('Error:', error);
        mostrarError('Error: ' + error.message);
    }
}

document.addEventListener('DOMContentLoaded', init);
</script>