<?php
/**
 * ================================================
 * MÓDULO TALLER - EDITAR TRABAJO
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
            <h2 class="mb-1"><i class="bi bi-pencil"></i> Editar Trabajo</h2>
            <p class="text-muted mb-0" id="codigoTrabajo">Cargando...</p>
        </div>
        <a href="ver.php?id=<?php echo $trabajo_id; ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    <hr class="border-warning border-2 opacity-75 mb-4">

    <form id="formTrabajo">
        <div class="row">
            <!-- Columna Izquierda -->
            <div class="col-lg-8">
                
                <!-- Datos del Cliente -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-person"></i> Datos del Cliente</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="cliente_nombre" class="form-label">
                                    Nombre del Cliente <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="cliente_nombre" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="cliente_telefono" class="form-label">
                                    Teléfono <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="cliente_telefono" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Descripción de la Pieza -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="bi bi-gem"></i> Descripción de la Pieza</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="material" class="form-label">
                                    Material <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="material" required>
                                    <option value="">Seleccione...</option>
                                    <option value="oro">Oro</option>
                                    <option value="plata">Plata</option>
                                    <option value="otro">Otro</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="peso_gramos" class="form-label">Peso (gramos)</label>
                                <input type="number" class="form-control" id="peso_gramos" 
                                       step="0.001" min="0">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="largo_cm" class="form-label">Largo (cm)</label>
                                <input type="number" class="form-control" id="largo_cm" 
                                       step="0.01" min="0">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="estilo" class="form-label">Estilo</label>
                                <input type="text" class="form-control" id="estilo">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label d-block">¿Con Piedra?</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="con_piedra" 
                                           id="con_piedra_si" value="1">
                                    <label class="form-check-label" for="con_piedra_si">Sí</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="con_piedra" 
                                           id="con_piedra_no" value="0">
                                    <label class="form-check-label" for="con_piedra_no">No</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="descripcion_pieza" class="form-label">
                                Descripción de la Pieza <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" id="descripcion_pieza" rows="2" required></textarea>
                        </div>
                    </div>
                </div>

                <!-- Descripción del Trabajo -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-tools"></i> Descripción del Trabajo</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="tipo_trabajo" class="form-label">
                                Tipo de Trabajo <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="tipo_trabajo" required>
                                <option value="">Seleccione...</option>
                                <option value="reparacion">Reparación</option>
                                <option value="ajuste">Ajuste</option>
                                <option value="grabado">Grabado</option>
                                <option value="diseño">Diseño</option>
                                <option value="limpieza">Limpieza</option>
                                <option value="engaste">Engaste</option>
                                <option value="repuesto">Repuesto</option>
                                <option value="fabricacion">Fabricación</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="descripcion_trabajo" class="form-label">
                                Descripción del Trabajo <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" id="descripcion_trabajo" rows="3" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea class="form-control" id="observaciones" rows="2"></textarea>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Columna Derecha -->
            <div class="col-lg-4">
                
                <!-- Estado Actual -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="bi bi-info-circle"></i> Estado Actual</h5>
                    </div>
                    <div class="card-body text-center">
                        <h3 id="estadoActual" class="mb-0">-</h3>
                    </div>
                </div>

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
                            <label for="fecha_entrega_prometida" class="form-label">
                                Fecha Entrega Prometida <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control" id="fecha_entrega_prometida" required>
                        </div>
                    </div>
                </div>

                <!-- Precios -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="bi bi-cash"></i> Precios</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="precio_total" class="form-label">
                                Precio Total (Q) <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control form-control-lg" id="precio_total" 
                                   step="0.01" min="0" required>
                        </div>

                        <div class="mb-3">
                            <label for="anticipo" class="form-label">Anticipo (Q)</label>
                            <input type="number" class="form-control" id="anticipo" 
                                   step="0.01" min="0">
                        </div>

                        <div class="alert alert-info">
                            <strong>Saldo:</strong> <span id="saldoCalculado">Q 0.00</span>
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <button type="submit" class="btn btn-warning btn-lg w-100 mb-2">
                    <i class="bi bi-check-circle"></i> Guardar Cambios
                </button>

                <a href="ver.php?id=<?php echo $trabajo_id; ?>" class="btn btn-secondary w-100">
                    <i class="bi bi-x-circle"></i> Cancelar
                </a>

            </div>
        </div>
    </form>

</div>

<?php require_once '../../includes/footer.php'; ?>

<script src="../../assets/js/vendors/sweetalert2/sweetalert2.all.min.js"></script>
<script src="../../assets/js/common.js"></script>
<script src="../../assets/js/api-client.js"></script>

<script>
const trabajoId = <?php echo $trabajo_id; ?>;
let trabajoOriginal = null;

async function cargarTrabajo() {
    try {
        mostrarCargando();
        
        const res = await fetch(`/api/taller/detalle.php?id=${trabajoId}`);
        const data = await res.json();
        
        if (!data.success) {
            ocultarCargando();
            await mostrarError(data.message || 'Trabajo no encontrado');
            window.location.href = 'lista.php';
            return;
        }
        
        trabajoOriginal = data.data.trabajo;
        
        // Verificar si se puede editar
        if (trabajoOriginal.estado === 'entregado' || trabajoOriginal.estado === 'cancelado') {
            ocultarCargando();
            await mostrarError('No se puede editar un trabajo entregado o cancelado');
            window.location.href = 'ver.php?id=' + trabajoId;
            return;
        }
        
        cargarDatos(trabajoOriginal);
        
        ocultarCargando();
        
    } catch (error) {
        ocultarCargando();
        console.error('Error:', error);
        mostrarError('Error al cargar trabajo');
    }
}

function cargarDatos(t) {
    document.getElementById('codigoTrabajo').textContent = t.codigo;
    
    // Cliente
    document.getElementById('cliente_nombre').value = t.cliente_nombre;
    document.getElementById('cliente_telefono').value = t.cliente_telefono;
    
    // Pieza
    document.getElementById('material').value = t.material;
    document.getElementById('peso_gramos').value = t.peso_gramos || '';
    document.getElementById('largo_cm').value = t.largo_cm || '';
    document.getElementById('estilo').value = t.estilo || '';
    
    if (t.con_piedra == 1) {
        document.getElementById('con_piedra_si').checked = true;
    } else {
        document.getElementById('con_piedra_no').checked = true;
    }
    
    document.getElementById('descripcion_pieza').value = t.descripcion_pieza;
    
    // Trabajo
    document.getElementById('tipo_trabajo').value = t.tipo_trabajo;
    document.getElementById('descripcion_trabajo').value = t.descripcion_trabajo;
    document.getElementById('observaciones').value = t.observaciones || '';
    
    // Fechas
    document.getElementById('fechaRecepcion').textContent = formatearFechaHora(t.fecha_recepcion);
    document.getElementById('fecha_entrega_prometida').value = t.fecha_entrega_prometida;
    
    // Precios
    document.getElementById('precio_total').value = t.precio_total;
    document.getElementById('anticipo').value = t.anticipo || 0;
    
    // Estado
    const estadoTextos = {
        'recibido': 'Recibido',
        'en_proceso': 'En Proceso',
        'completado': 'Completado'
    };
    
    const estadoBadges = {
        'recibido': 'badge bg-warning text-dark',
        'en_proceso': 'badge bg-primary',
        'completado': 'badge bg-success'
    };
    
    const estadoEl = document.getElementById('estadoActual');
    estadoEl.textContent = estadoTextos[t.estado] || t.estado;
    estadoEl.className = estadoBadges[t.estado] || 'badge bg-secondary';
    
    calcularSaldo();
}

function calcularSaldo() {
    const total = parseFloat(document.getElementById('precio_total').value) || 0;
    const anticipo = parseFloat(document.getElementById('anticipo').value) || 0;
    const saldo = total - anticipo;
    
    document.getElementById('saldoCalculado').textContent = formatearMoneda(saldo);
}

document.getElementById('precio_total').addEventListener('input', calcularSaldo);
document.getElementById('anticipo').addEventListener('input', calcularSaldo);

// Validar teléfono
document.getElementById('cliente_telefono').addEventListener('input', function() {
    this.value = this.value.replace(/[^0-9-]/g, '');
});

// Submit
document.getElementById('formTrabajo').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const conPiedra = document.querySelector('input[name="con_piedra"]:checked')?.value;
    
    if (!conPiedra) {
        mostrarError('Seleccione si tiene piedra o no');
        return;
    }
    
    const datos = {
        id: trabajoId,
        cliente_nombre: document.getElementById('cliente_nombre').value.trim(),
        cliente_telefono: document.getElementById('cliente_telefono').value.trim(),
        material: document.getElementById('material').value,
        peso_gramos: document.getElementById('peso_gramos').value || null,
        largo_cm: document.getElementById('largo_cm').value || null,
        con_piedra: parseInt(conPiedra),
        estilo: document.getElementById('estilo').value.trim() || null,
        descripcion_pieza: document.getElementById('descripcion_pieza').value.trim(),
        tipo_trabajo: document.getElementById('tipo_trabajo').value,
        descripcion_trabajo: document.getElementById('descripcion_trabajo').value.trim(),
        precio_total: parseFloat(document.getElementById('precio_total').value),
        anticipo: parseFloat(document.getElementById('anticipo').value) || 0,
        fecha_entrega_prometida: document.getElementById('fecha_entrega_prometida').value,
        observaciones: document.getElementById('observaciones').value.trim() || null
    };
    
    // Validaciones
    if (!datos.cliente_nombre || !datos.cliente_telefono) {
        mostrarError('Complete los datos del cliente');
        return;
    }
    
    if (!datos.material || !datos.descripcion_pieza) {
        mostrarError('Complete la descripción de la pieza');
        return;
    }
    
    if (!datos.tipo_trabajo || !datos.descripcion_trabajo) {
        mostrarError('Complete la descripción del trabajo');
        return;
    }
    
    if (!datos.precio_total || datos.precio_total <= 0) {
        mostrarError('Ingrese un precio total válido');
        return;
    }
    
    if (!datos.fecha_entrega_prometida) {
        mostrarError('Ingrese la fecha de entrega');
        return;
    }
    
    const confirmacion = await confirmarAccion('¿Guardar los cambios?');
    if (!confirmacion) return;
    
    try {
        mostrarCargando();
        
        const res = await fetch('/api/taller/actualizar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datos)
        });
        
        const resultado = await res.json();
        
        ocultarCargando();
        
        if (resultado.success) {
            await mostrarExito('Trabajo actualizado exitosamente');
            window.location.href = 'ver.php?id=' + trabajoId;
        } else {
            mostrarError(resultado.message || 'Error al actualizar trabajo');
        }
        
    } catch (error) {
        ocultarCargando();
        console.error('Error:', error);
        mostrarError('Error: ' + error.message);
    }
});

document.addEventListener('DOMContentLoaded', cargarTrabajo);
</script>