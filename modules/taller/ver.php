<?php
/**
 * ================================================
 * MÓDULO TALLER - VER TRABAJO
 * ================================================
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();

$trabajo_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$trabajo_id) {
    header('Location: lista.php');
    exit;
}

require_once '../../includes/header.php';
require_once '../../includes/navbar.php';
?>

<div class="container-fluid px-4 py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-1"><i class="bi bi-eye"></i> Detalles del Trabajo</h2>
            <p class="text-muted mb-0" id="codigoTrabajo">Cargando...</p>
        </div>
        <div>
            <?php if (tiene_permiso('taller', 'editar')): ?>
            <a href="editar.php?id=<?php echo $trabajo_id; ?>" class="btn btn-warning me-2" id="btnEditar" style="display: none;">
                <i class="bi bi-pencil"></i> Editar
            </a>
            <?php endif; ?>
            <a href="lista.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <hr class="border-warning border-2 opacity-75 mb-4">

    <div class="row">
        <!-- Columna Izquierda -->
        <div class="col-lg-8">
            
            <!-- Información del Trabajo -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Información del Trabajo</h5>
                    <span id="estadoBadge" class="badge">-</span>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Cliente</label>
                            <h5 id="clienteNombre">-</h5>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Teléfono</label>
                            <h5 id="clienteTelefono">-</h5>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label text-muted">Material</label>
                            <p id="material">-</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted">Peso</label>
                            <p id="peso">-</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted">Largo</label>
                            <p id="largo">-</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Estilo</label>
                            <p id="estilo">-</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">¿Con Piedra?</label>
                            <p id="conPiedra">-</p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Descripción de la Pieza</label>
                        <p id="descripcionPieza" class="border-start border-3 border-warning ps-3">-</p>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Tipo de Trabajo</label>
                            <p id="tipoTrabajo">-</p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Descripción del Trabajo</label>
                        <p id="descripcionTrabajo" class="border-start border-3 border-info ps-3">-</p>
                    </div>

                    <div class="mb-3" id="observacionesContainer" style="display: none;">
                        <label class="form-label text-muted">Observaciones</label>
                        <p id="observaciones" class="border-start border-3 border-secondary ps-3">-</p>
                    </div>
                </div>
            </div>

            <!-- Historial de Transferencias -->
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Historial de Transferencias</h5>
                </div>
                <div class="card-body">
                    <div id="historialContainer">
                        <p class="text-center text-muted">Cargando historial...</p>
                    </div>
                </div>
            </div>

        </div>

        <!-- Columna Derecha -->
        <div class="col-lg-4">
            
            <!-- Fechas -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-calendar"></i> Fechas</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted">Fecha Recepción</label>
                        <p id="fechaRecepcion">-</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Entrega Prometida</label>
                        <p id="fechaEntregaPrometida" class="fw-bold">-</p>
                    </div>
                    <div class="mb-3" id="fechaEntregaRealContainer" style="display: none;">
                        <label class="form-label text-muted">Entrega Real</label>
                        <p id="fechaEntregaReal">-</p>
                    </div>
                </div>
            </div>

            <!-- Empleados -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-people"></i> Empleados</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted">Empleado que Recibió</label>
                        <p id="empleadoRecibe">-</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Empleado Actual</label>
                        <p id="empleadoActual" class="fw-bold text-primary">-</p>
                    </div>
                    <div class="mb-3" id="empleadoEntregaContainer" style="display: none;">
                        <label class="form-label text-muted">Empleado que Entregó</label>
                        <p id="empleadoEntrega">-</p>
                    </div>
                </div>
            </div>

            <!-- Precios -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="bi bi-cash-coin"></i> Precios</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Precio Total:</span>
                        <strong id="precioTotal">-</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Anticipo:</span>
                        <span id="anticipo">-</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>Saldo:</strong>
                        <h4 id="saldo" class="mb-0">-</h4>
                    </div>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="card shadow-sm" id="accionesCard">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-lightning"></i> Acciones Rápidas</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2" id="botonesAccion">
                        <!-- Se llenarán dinámicamente según el estado -->
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
const trabajoId = <?php echo $trabajo_id; ?>;
let trabajo = null;

async function cargarTrabajo() {
    try {
        mostrarCargando();
        
        const res = await fetch(`/joyeria-torre-fuerte/api/taller/detalle.php?id=${trabajoId}`);
        const data = await res.json();
        
        if (!data.success) {
            ocultarCargando();
            await mostrarError(data.message || 'Trabajo no encontrado');
            window.location.href = 'lista.php';
            return;
        }
        
        trabajo = data.data.trabajo;
        const historial = data.data.historial_transferencias || [];
        
        mostrarDatos(trabajo);
        mostrarHistorial(historial);
        mostrarAcciones(trabajo);
        
        ocultarCargando();
        
    } catch (error) {
        ocultarCargando();
        console.error('Error:', error);
        mostrarError('Error al cargar trabajo');
    }
}

function mostrarDatos(t) {
    // Código y estado
    document.getElementById('codigoTrabajo').textContent = t.codigo;
    
    const estadoBadges = {
        'recibido': 'bg-warning text-dark',
        'en_proceso': 'bg-primary',
        'completado': 'bg-success',
        'entregado': 'bg-info',
        'cancelado': 'bg-secondary'
    };
    
    const estadoTextos = {
        'recibido': 'Recibido',
        'en_proceso': 'En Proceso',
        'completado': 'Completado',
        'entregado': 'Entregado',
        'cancelado': 'Cancelado'
    };
    
    const badge = document.getElementById('estadoBadge');
    badge.className = 'badge ' + (estadoBadges[t.estado] || 'bg-secondary');
    badge.textContent = estadoTextos[t.estado] || t.estado;
    
    // Mostrar botón editar solo si no está entregado/cancelado
    if (t.estado !== 'entregado' && t.estado !== 'cancelado') {
        document.getElementById('btnEditar').style.display = 'inline-block';
    }
    
    // Cliente
    document.getElementById('clienteNombre').textContent = t.cliente_nombre;
    document.getElementById('clienteTelefono').textContent = t.cliente_telefono;
    
    // Pieza
    const materialBadge = {
        'oro': '<span class="badge bg-warning text-dark">Oro</span>',
        'plata': '<span class="badge bg-secondary">Plata</span>',
        'otro': '<span class="badge bg-info">Otro</span>'
    };
    document.getElementById('material').innerHTML = materialBadge[t.material] || t.material;
    document.getElementById('peso').textContent = t.peso_gramos ? t.peso_gramos + ' g' : '-';
    document.getElementById('largo').textContent = t.largo_cm ? t.largo_cm + ' cm' : '-';
    document.getElementById('estilo').textContent = t.estilo || '-';
    document.getElementById('conPiedra').textContent = t.con_piedra == 1 ? 'Sí' : 'No';
    document.getElementById('descripcionPieza').textContent = t.descripcion_pieza;
    
    // Trabajo
    document.getElementById('tipoTrabajo').textContent = t.tipo_trabajo;
    document.getElementById('descripcionTrabajo').textContent = t.descripcion_trabajo;
    
    if (t.observaciones) {
        document.getElementById('observacionesContainer').style.display = 'block';
        document.getElementById('observaciones').textContent = t.observaciones;
    }
    
    // Fechas
    document.getElementById('fechaRecepcion').textContent = formatearFechaHora(t.fecha_recepcion);
    document.getElementById('fechaEntregaPrometida').textContent = formatearFecha(t.fecha_entrega_prometida);
    
    // Verificar si está atrasado
    const hoy = new Date();
    const fechaPromesa = new Date(t.fecha_entrega_prometida);
    if (fechaPromesa < hoy && t.estado !== 'entregado' && t.estado !== 'cancelado') {
        document.getElementById('fechaEntregaPrometida').innerHTML += 
            '<br><small class="text-danger"><i class="bi bi-exclamation-triangle"></i> Atrasado</small>';
    }
    
    if (t.fecha_entrega_real) {
        document.getElementById('fechaEntregaRealContainer').style.display = 'block';
        document.getElementById('fechaEntregaReal').textContent = formatearFechaHora(t.fecha_entrega_real);
    }
    
    // Empleados
    document.getElementById('empleadoRecibe').textContent = t.empleado_recibe_nombre || '-';
    document.getElementById('empleadoActual').textContent = t.empleado_actual_nombre || '-';
    
    if (t.empleado_entrega_nombre) {
        document.getElementById('empleadoEntregaContainer').style.display = 'block';
        document.getElementById('empleadoEntrega').textContent = t.empleado_entrega_nombre;
    }
    
    // Precios
    document.getElementById('precioTotal').textContent = formatearMoneda(t.precio_total);
    document.getElementById('anticipo').textContent = formatearMoneda(t.anticipo);
    
    const saldo = parseFloat(t.saldo) || 0;
    const saldoEl = document.getElementById('saldo');
    saldoEl.textContent = formatearMoneda(saldo);
    saldoEl.className = saldo > 0 ? 'mb-0 text-danger' : 'mb-0 text-success';
}

function mostrarHistorial(historial) {
    const container = document.getElementById('historialContainer');
    
    if (!historial || historial.length === 0) {
        container.innerHTML = '<p class="text-center text-muted">No hay transferencias registradas</p>';
        return;
    }
    
    let html = '<div class="timeline">';
    
    historial.forEach((h, index) => {
        html += `
            <div class="mb-3 pb-3 ${index < historial.length - 1 ? 'border-bottom' : ''}">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <small class="text-muted">
                        <i class="bi bi-clock"></i> ${formatearFechaHora(h.fecha_transferencia)}
                    </small>
                    <span class="badge bg-secondary">${escaparHTML(h.estado_trabajo_momento)}</span>
                </div>
                <p class="mb-1">
                    <i class="bi bi-arrow-right-circle text-primary"></i>
                    <strong>${escaparHTML(h.empleado_origen_nombre)}</strong>
                    <i class="bi bi-arrow-right"></i>
                    <strong>${escaparHTML(h.empleado_destino_nombre)}</strong>
                </p>
                ${h.nota ? `<small class="text-muted"><i class="bi bi-chat-left-text"></i> ${escaparHTML(h.nota)}</small>` : ''}
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
}

function mostrarAcciones(t) {
    const container = document.getElementById('botonesAccion');
    let html = '';
    
    // Botones según estado
    if (t.estado === 'recibido' || t.estado === 'en_proceso') {
        html += `
            <button class="btn btn-primary" onclick="cambiarEstado('en_proceso')">
                <i class="bi bi-play-circle"></i> Iniciar Proceso
            </button>
            <button class="btn btn-success" onclick="completarTrabajo()">
                <i class="bi bi-check-circle"></i> Marcar Completado
            </button>
            <button class="btn btn-info" onclick="transferirTrabajo()">
                <i class="bi bi-arrow-left-right"></i> Transferir
            </button>
            <button class="btn btn-danger" onclick="cancelarTrabajo()">
                <i class="bi bi-x-circle"></i> Cancelar
            </button>
        `;
    } else if (t.estado === 'completado') {
        html += `
            <button class="btn btn-success btn-lg" onclick="entregarTrabajo()">
                <i class="bi bi-box-arrow-right"></i> Entregar al Cliente
            </button>
            <button class="btn btn-info" onclick="transferirTrabajo()">
                <i class="bi bi-arrow-left-right"></i> Transferir
            </button>
        `;
    } else if (t.estado === 'entregado') {
        html = '<p class="text-center text-success"><i class="bi bi-check-circle"></i> Trabajo entregado</p>';
    } else if (t.estado === 'cancelado') {
        html = '<p class="text-center text-muted"><i class="bi bi-x-circle"></i> Trabajo cancelado</p>';
    }
    
    container.innerHTML = html;
    
    // Ocultar card si no hay acciones
    if (t.estado === 'entregado' || t.estado === 'cancelado') {
        document.getElementById('accionesCard').style.display = 'none';
    }
}

async function cambiarEstado(nuevoEstado) {
    const confirmacion = await confirmarAccion(`¿Cambiar estado a "${nuevoEstado}"?`);
    if (!confirmacion) return;
    
    try {
        mostrarCargando();
        
        const res = await fetch('/joyeria-torre-fuerte/api/taller/cambiar_estado.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id: trabajoId,
                nuevo_estado: nuevoEstado
            })
        });
        
        const data = await res.json();
        
        ocultarCargando();
        
        if (data.success) {
            await mostrarExito('Estado cambiado exitosamente');
            window.location.reload();
        } else {
            mostrarError(data.message || 'Error al cambiar estado');
        }
    } catch (error) {
        ocultarCargando();
        console.error('Error:', error);
        mostrarError('Error: ' + error.message);
    }
}

async function completarTrabajo() {
    const confirmacion = await confirmarAccion('¿Marcar trabajo como completado?');
    if (!confirmacion) return;
    
    try {
        mostrarCargando();
        
        const res = await fetch('/joyeria-torre-fuerte/api/taller/completar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: trabajoId })
        });
        
        const data = await res.json();
        
        ocultarCargando();
        
        if (data.success) {
            await mostrarExito('Trabajo completado');
            window.location.reload();
        } else {
            mostrarError(data.message || 'Error');
        }
    } catch (error) {
        ocultarCargando();
        mostrarError('Error: ' + error.message);
    }
}

async function registrarPagoSaldo(montoAPagar) {
    console.log('=== INICIO registrarPagoSaldo ===');
    console.log('Monto a pagar:', montoAPagar);
    
    if (!montoAPagar || montoAPagar <= 0) {
        mostrarError('Monto inválido');
        return false;
    }
    
    try {
        mostrarCargando();
        
        const anticipoActual = parseFloat(trabajo.anticipo) || 0;
        const nuevoAnticipo = anticipoActual + montoAPagar;
        
        // USAR EL NUEVO ENDPOINT
        const res = await fetch('/joyeria-torre-fuerte/api/taller/actualizar_anticipo.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id: trabajoId,
                anticipo: nuevoAnticipo
            })
        });
        
        const resultado = await res.json();
        
        console.log('Resultado de API:', resultado);
        
        ocultarCargando();
        
        if (resultado.success) {
            // Actualizar el objeto trabajo en memoria
            trabajo.anticipo = nuevoAnticipo;
            trabajo.saldo = trabajo.precio_total - nuevoAnticipo;
            
            console.log('✅ Pago registrado - Nuevo saldo:', trabajo.saldo);
            
            return true;
        } else {
            console.error('API retornó error:', resultado);
            mostrarError(resultado.error || 'Error al registrar pago');
            return false;
        }
        
    } catch (error) {
        ocultarCargando();
        console.error('ERROR COMPLETO:', error);
        mostrarError('Error al registrar pago: ' + error.message);
        return false;
    }
}

async function entregarTrabajo() {
    const saldo = parseFloat(trabajo.saldo) || 0;
    
    if (saldo > 0) {
        // Preguntar si va a pagar el saldo
        const { value: pagarSaldo } = await Swal.fire({
            title: 'Saldo Pendiente',
            html: `
                <p class="mb-3">El cliente debe: <strong class="text-danger fs-4">${formatearMoneda(saldo)}</strong></p>
                <p class="text-muted">¿Va a pagar el saldo completo ahora?</p>
            `,
            icon: 'warning',
            showDenyButton: true,
            showCancelButton: true,
            confirmButtonText: 'Sí, pagar ahora',
            denyButtonText: 'No, entregar con deuda',
            cancelButtonText: 'Cancelar entrega',
            confirmButtonColor: '#28a745',
            denyButtonColor: '#ffc107'
        });
        
        if (pagarSaldo === undefined) return; // Canceló
        
        if (pagarSaldo) {
            // Pagar el saldo completo antes de entregar
            const pagoExitoso = await registrarPagoSaldo(saldo); // ← CAMBIO: capturar resultado
            
            if (!pagoExitoso) {
                return; // ← CAMBIO: si falla, detener
            }
            
            // ← CAMBIO: Mostrar confirmación de pago
            await Swal.fire({
                icon: 'success',
                title: 'Pago Registrado',
                text: `Se registró el pago de ${formatearMoneda(saldo)}`,
                timer: 2000,
                showConfirmButton: false
            });
        }
    }
    
    // Continuar con la entrega
    const usuarioActual = <?php echo $_SESSION['usuario_id'] ?? 'null'; ?>;
    
    try {
        mostrarCargando();
        
        const res = await fetch('/joyeria-torre-fuerte/api/taller/entregar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id: trabajoId,
                empleado_entrega_id: usuarioActual
            })
        });
        
        const data = await res.json();
        
        ocultarCargando();
        
        if (data.success) {
            await mostrarExito(data.message);
            window.location.reload();
        } else {
            mostrarError(data.message || 'Error');
        }
    } catch (error) {
        ocultarCargando();
        mostrarError('Error: ' + error.message);
    }
}

function transferirTrabajo() {
    window.location.href = `transferir.php?id=${trabajoId}`;
}

async function cancelarTrabajo() {
    const { value: motivo } = await Swal.fire({
        title: 'Cancelar Trabajo',
        input: 'textarea',
        inputLabel: 'Motivo de cancelación',
        inputPlaceholder: 'Explique por qué se cancela...',
        showCancelButton: true,
        confirmButtonText: 'Cancelar Trabajo',
        cancelButtonText: 'Cerrar',
        confirmButtonColor: '#dc3545'
    });
    
    if (!motivo) return;
    
    try {
        mostrarCargando();
        
        const res = await fetch('/joyeria-torre-fuerte/api/taller/cancelar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id: trabajoId,
                motivo: motivo
            })
        });
        
        const data = await res.json();
        
        ocultarCargando();
        
        if (data.success) {
            await mostrarExito('Trabajo cancelado');
            window.location.reload();
        } else {
            mostrarError(data.message || 'Error');
        }
    } catch (error) {
        ocultarCargando();
        mostrarError('Error: ' + error.message);
    }
}

document.addEventListener('DOMContentLoaded', cargarTrabajo);
</script>