<?php
/**
 * ================================================
 * MÓDULO TALLER - TRANSFERIR TRABAJO
 * ================================================
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();
requiere_rol(['administrador', 'dueño', 'vendedor']);

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
            <h2 class="mb-1"><i class="bi bi-arrow-left-right"></i> Transferir Trabajo</h2>
            <p class="text-muted mb-0">Asignar trabajo a otro empleado</p>
        </div>
        <a href="ver.php?id=<?php echo $trabajo_id; ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    <hr class="border-warning border-2 opacity-75 mb-4">

    <div class="row">
        <!-- Información del Trabajo -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Información del Trabajo</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Código</label>
                            <h4 id="codigo" class="text-primary">-</h4>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Estado</label>
                            <h4><span id="estado" class="badge">-</span></h4>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Cliente</label>
                            <p id="cliente" class="mb-0">-</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Teléfono</label>
                            <p id="telefono" class="mb-0">-</p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Descripción de la Pieza</label>
                        <p id="descripcionPieza" class="border-start border-3 border-warning ps-3">-</p>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Tipo de Trabajo</label>
                            <p id="tipoTrabajo">-</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Material</label>
                            <p id="material">-</p>
                        </div>
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
                        <p class="text-center text-muted">Cargando...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulario de Transferencia -->
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-people"></i> Empleados</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted">Empleado Actual</label>
                        <div class="alert alert-primary" id="empleadoActualContainer">
                            <i class="bi bi-person-fill"></i>
                            <strong id="empleadoActual">-</strong>
                        </div>
                    </div>

                    <form id="formTransferencia">
                        <div class="mb-3">
                            <label for="empleado_destino_id" class="form-label">
                                Transferir a: <span class="text-danger">*</span>
                            </label>
                            <select class="form-select form-select-lg" id="empleado_destino_id" required>
                                <option value="">Seleccione empleado...</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="nota" class="form-label">Nota (opcional)</label>
                            <textarea class="form-control" id="nota" rows="3" 
                                      placeholder="Ej: Transferido para grabado personalizado"></textarea>
                            <small class="text-muted">Motivo o instrucciones de la transferencia</small>
                        </div>

                        <div class="alert alert-warning">
                            <i class="bi bi-info-circle"></i>
                            <small>
                                <strong>Importante:</strong> Esta acción quedará registrada en el historial.
                            </small>
                        </div>

                        <button type="submit" class="btn btn-success btn-lg w-100">
                            <i class="bi bi-arrow-left-right"></i> Transferir Trabajo
                        </button>
                    </form>
                </div>
            </div>

            <!-- Info Adicional -->
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="bi bi-calendar"></i> Fechas</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted">Fecha Recepción:</small>
                        <p id="fechaRecepcion" class="mb-0"><strong>-</strong></p>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Entrega Prometida:</small>
                        <p id="fechaEntrega" class="mb-0"><strong>-</strong></p>
                    </div>
                    <div>
                        <small class="text-muted">Días restantes:</small>
                        <p id="diasRestantes" class="mb-0"><strong>-</strong></p>
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
let empleadoActualId = null;

async function cargarDatos() {
    try {
        mostrarCargando();
        
        // Cargar trabajo
        const res = await fetch(`/api/taller/detalle.php?id=${trabajoId}`);
        const data = await res.json();
        
        if (!data.success) {
            ocultarCargando();
            await mostrarError(data.message || 'Trabajo no encontrado');
            window.location.href = 'lista.php';
            return;
        }
        
        trabajo = data.data.trabajo;
        const historial = data.data.historial_transferencias || [];
        
        // Verificar que se puede transferir
        if (trabajo.estado === 'entregado' || trabajo.estado === 'cancelado') {
            ocultarCargando();
            await mostrarError('No se puede transferir un trabajo entregado o cancelado');
            window.location.href = 'ver.php?id=' + trabajoId;
            return;
        }
        
        empleadoActualId = trabajo.empleado_actual_id;
        
        mostrarTrabajo(trabajo);
        mostrarHistorial(historial);
        await cargarEmpleados();
        
        ocultarCargando();
        
    } catch (error) {
        ocultarCargando();
        console.error('Error:', error);
        mostrarError('Error al cargar datos');
    }
}

function mostrarTrabajo(t) {
    document.getElementById('codigo').textContent = t.codigo;
    
    const estadoBadges = {
        'recibido': 'bg-warning text-dark',
        'en_proceso': 'bg-primary',
        'completado': 'bg-success'
    };
    
    const estadoTextos = {
        'recibido': 'Recibido',
        'en_proceso': 'En Proceso',
        'completado': 'Completado'
    };
    
    const estadoEl = document.getElementById('estado');
    estadoEl.className = 'badge ' + (estadoBadges[t.estado] || 'bg-secondary');
    estadoEl.textContent = estadoTextos[t.estado] || t.estado;
    
    document.getElementById('cliente').textContent = t.cliente_nombre;
    document.getElementById('telefono').textContent = t.cliente_telefono;
    document.getElementById('descripcionPieza').textContent = t.descripcion_pieza;
    document.getElementById('tipoTrabajo').textContent = t.tipo_trabajo;
    
    const materialBadge = {
        'oro': '<span class="badge bg-warning text-dark">Oro</span>',
        'plata': '<span class="badge bg-secondary">Plata</span>',
        'otro': '<span class="badge bg-info">Otro</span>'
    };
    document.getElementById('material').innerHTML = materialBadge[t.material] || t.material;
    
    document.getElementById('empleadoActual').textContent = t.empleado_actual_nombre;
    
    // Fechas
    document.getElementById('fechaRecepcion').textContent = formatearFechaHora(t.fecha_recepcion);
    document.getElementById('fechaEntrega').textContent = formatearFecha(t.fecha_entrega_prometida);
    
    // Calcular días restantes
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);
    const fechaPromesa = new Date(t.fecha_entrega_prometida);
    const diffTime = fechaPromesa - hoy;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    const diasEl = document.getElementById('diasRestantes');
    if (diffDays < 0) {
        diasEl.innerHTML = `<span class="text-danger">${Math.abs(diffDays)} días de atraso</span>`;
    } else if (diffDays === 0) {
        diasEl.innerHTML = '<span class="text-warning">Vence hoy</span>';
    } else {
        diasEl.innerHTML = `<span class="text-success">${diffDays} días</span>`;
    }
}

function mostrarHistorial(historial) {
    const container = document.getElementById('historialContainer');
    
    if (!historial || historial.length === 0) {
        container.innerHTML = '<p class="text-center text-muted">No hay transferencias previas</p>';
        return;
    }
    
    let html = '';
    
    historial.forEach((h, index) => {
        html += `
            <div class="mb-3 pb-3 ${index < historial.length - 1 ? 'border-bottom' : ''}">
                <div class="d-flex justify-content-between align-items-start mb-1">
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
    
    container.innerHTML = html;
}

async function cargarEmpleados() {
    try {
        const res = await fetch('/api/usuarios/listar.php?activo=1');
        const data = await res.json();
        
        if (!data.success) return;
        
        const empleados = data.data.usuarios || data.data || [];
        const select = document.getElementById('empleado_destino_id');
        
        empleados.forEach(emp => {
            // No incluir al empleado actual
            if (emp.id == empleadoActualId) return;
            
            const option = document.createElement('option');
            option.value = emp.id;
            option.textContent = emp.nombre + ' (' + emp.rol + ')';
            select.appendChild(option);
        });
        
    } catch (error) {
        console.error('Error al cargar empleados:', error);
    }
}

document.getElementById('formTransferencia').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const empleadoDestinoId = parseInt(document.getElementById('empleado_destino_id').value);
    const nota = document.getElementById('nota').value.trim();
    
    if (!empleadoDestinoId) {
        mostrarError('Seleccione el empleado destino');
        return;
    }
    
    // Obtener nombre del empleado destino
    const selectDestino = document.getElementById('empleado_destino_id');
    const nombreDestino = selectDestino.options[selectDestino.selectedIndex].text;
    
    const confirmacion = await confirmarAccion(
        '¿Transferir este trabajo?',
        `El trabajo será asignado a: <strong>${nombreDestino}</strong>`
    );
    
    if (!confirmacion) return;
    
    try {
        mostrarCargando();
        
        const res = await fetch('/api/taller/transferir.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                trabajo_id: trabajoId,
                empleado_destino_id: empleadoDestinoId,
                nota: nota || null
            })
        });
        
        const resultado = await res.json();
        
        ocultarCargando();
        
        if (resultado.success) {
            await mostrarExito(resultado.message || 'Trabajo transferido exitosamente');
            window.location.href = 'ver.php?id=' + trabajoId;
        } else {
            mostrarError(resultado.message || 'Error al transferir trabajo');
        }
        
    } catch (error) {
        ocultarCargando();
        console.error('Error:', error);
        mostrarError('Error: ' + error.message);
    }
});

document.addEventListener('DOMContentLoaded', cargarDatos);
</script>