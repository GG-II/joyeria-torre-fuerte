<?php
/**
 * ================================================
 * MÓDULO MATERIA PRIMA - EDITAR
 * ================================================
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();
requiere_rol(['administrador', 'dueño']);

$materia_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$materia_id) {
    header('Location: lista.php');
    exit;
}

require_once '../../includes/header.php';
require_once '../../includes/navbar.php';
?>

<div class="container-fluid px-4 py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-1"><i class="bi bi-pencil"></i> Editar Materia Prima</h2>
            <p class="text-muted mb-0" id="nombreMateria">Cargando...</p>
        </div>
        <a href="ver.php?id=<?php echo $materia_id; ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    <hr class="border-warning border-2 opacity-75 mb-4">

    <form id="formMateria">
        <div class="row">
            <!-- Columna Izquierda -->
            <div class="col-lg-8">
                
                <!-- Datos Básicos -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-info-circle"></i> Datos Básicos</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">
                                Nombre de la Materia Prima <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control form-control-lg" id="nombre" 
                                   placeholder="Ej: Oro 18K, Plata 925, Esmeralda..." required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tipo" class="form-label">
                                    Tipo <span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-lg" id="tipo" required>
                                    <option value="">Seleccione...</option>
                                    <option value="oro">Oro</option>
                                    <option value="plata">Plata</option>
                                    <option value="piedra">Piedra</option>
                                    <option value="otro">Otro</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="unidad_medida" class="form-label">
                                    Unidad de Medida <span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-lg" id="unidad_medida" required>
                                    <option value="">Seleccione...</option>
                                    <option value="gramos">Gramos</option>
                                    <option value="piezas">Piezas</option>
                                    <option value="quilates">Quilates</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stock y Precio -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="bi bi-box-seam"></i> Stock y Precio</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Nota:</strong> La cantidad disponible NO se edita aquí. 
                            Use el botón "Ajustar Stock" desde la vista de detalles.
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Cantidad Actual (Solo Lectura)</label>
                                <input type="text" class="form-control" id="cantidad_display" readonly>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="stock_minimo" class="form-label">
                                    Stock Mínimo (Alerta)
                                </label>
                                <input type="number" class="form-control" id="stock_minimo" 
                                       step="0.001" min="0" required>
                                <small class="text-muted">Se alertará cuando esté por debajo</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="precio_por_unidad" class="form-label">
                                Precio por Unidad (Q)
                            </label>
                            <input type="number" class="form-control form-control-lg" id="precio_por_unidad" 
                                   step="0.01" min="0" placeholder="Ej: 350.00">
                            <small class="text-muted">Opcional - para cálculo de valor total</small>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Columna Derecha -->
            <div class="col-lg-4">
                
                <!-- Estado Actual -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="bi bi-info-circle"></i> Estado</h5>
                    </div>
                    <div class="card-body text-center">
                        <h3 id="estadoActual" class="mb-0">-</h3>
                    </div>
                </div>

                <!-- Preview -->
                <div class="card shadow-sm mb-4 border-info">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-eye"></i> Vista Previa</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <small class="text-muted">Nombre:</small>
                            <p id="previewNombre" class="fw-bold mb-0">-</p>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted">Tipo:</small>
                            <p id="previewTipo" class="mb-0">-</p>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted">Unidad:</small>
                            <p id="previewUnidad" class="mb-0">-</p>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted">Stock Mínimo:</small>
                            <p id="previewMinimo" class="mb-0">-</p>
                        </div>
                        <div>
                            <small class="text-muted">Precio/Unidad:</small>
                            <p id="previewPrecio" class="mb-0">-</p>
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <button type="submit" class="btn btn-warning btn-lg w-100 mb-2">
                    <i class="bi bi-check-circle"></i> Guardar Cambios
                </button>

                <a href="ver.php?id=<?php echo $materia_id; ?>" class="btn btn-secondary w-100">
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
const materiaId = <?php echo $materia_id; ?>;
let materiaOriginal = null;

async function cargarMateria() {
    try {
        mostrarCargando();
        
        const res = await fetch('/api/materia_prima/listar.php');
        const data = await res.json();
        
        if (!data.success) {
            ocultarCargando();
            await mostrarError('Error al cargar materia prima');
            window.location.href = 'lista.php';
            return;
        }
        
        // Buscar la materia por ID
        materiaOriginal = (data.data || []).find(m => m.id == materiaId);
        
        if (!materiaOriginal) {
            ocultarCargando();
            await mostrarError('Materia prima no encontrada');
            window.location.href = 'lista.php';
            return;
        }
        
        // Verificar que esté activa
        if (materiaOriginal.activo != 1) {
            ocultarCargando();
            await mostrarError('No se puede editar una materia prima inactiva');
            window.location.href = 'ver.php?id=' + materiaId;
            return;
        }
        
        cargarDatos(materiaOriginal);
        
        ocultarCargando();
        
    } catch (error) {
        ocultarCargando();
        console.error('Error:', error);
        mostrarError('Error al cargar materia prima');
    }
}

function cargarDatos(m) {
    document.getElementById('nombreMateria').textContent = m.nombre;
    
    // Datos básicos
    document.getElementById('nombre').value = m.nombre;
    document.getElementById('tipo').value = m.tipo;
    document.getElementById('unidad_medida').value = m.unidad_medida;
    
    // Stock y precio
    document.getElementById('cantidad_display').value = parseFloat(m.cantidad_disponible).toFixed(3) + ' ' + m.unidad_medida;
    document.getElementById('stock_minimo').value = m.stock_minimo;
    document.getElementById('precio_por_unidad').value = m.precio_por_unidad || '';
    
    // Estado
    const estadoEl = document.getElementById('estadoActual');
    estadoEl.textContent = 'Activo';
    estadoEl.className = 'mb-0 badge bg-success fs-5';
    
    actualizarPreview();
}

// Actualizar preview en tiempo real
function actualizarPreview() {
    const nombre = document.getElementById('nombre').value || '-';
    const tipo = document.getElementById('tipo').value || '-';
    const unidad = document.getElementById('unidad_medida').value || '-';
    const minimo = document.getElementById('stock_minimo').value || '0';
    const precio = document.getElementById('precio_por_unidad').value || '0';
    
    document.getElementById('previewNombre').textContent = nombre;
    document.getElementById('previewTipo').textContent = tipo.charAt(0).toUpperCase() + tipo.slice(1);
    document.getElementById('previewUnidad').textContent = unidad;
    document.getElementById('previewMinimo').textContent = parseFloat(minimo).toFixed(3);
    document.getElementById('previewPrecio').textContent = parseFloat(precio) > 0 ? formatearMoneda(precio) : '-';
}

document.getElementById('nombre').addEventListener('input', actualizarPreview);
document.getElementById('tipo').addEventListener('change', actualizarPreview);
document.getElementById('unidad_medida').addEventListener('change', actualizarPreview);
document.getElementById('stock_minimo').addEventListener('input', actualizarPreview);
document.getElementById('precio_por_unidad').addEventListener('input', actualizarPreview);

// Submit
document.getElementById('formMateria').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const datos = {
        id: materiaId,
        nombre: document.getElementById('nombre').value.trim(),
        tipo: document.getElementById('tipo').value,
        unidad_medida: document.getElementById('unidad_medida').value,
        stock_minimo: parseFloat(document.getElementById('stock_minimo').value) || 5,
        precio_por_unidad: parseFloat(document.getElementById('precio_por_unidad').value) || null,
        activo: materiaOriginal.activo
    };
    
    // Validaciones
    if (!datos.nombre) {
        mostrarError('Ingrese el nombre de la materia prima');
        return;
    }
    
    if (!datos.tipo) {
        mostrarError('Seleccione el tipo');
        return;
    }
    
    if (!datos.unidad_medida) {
        mostrarError('Seleccione la unidad de medida');
        return;
    }
    
    const confirmacion = await confirmarAccion('¿Guardar los cambios?');
    if (!confirmacion) return;
    
    try {
        mostrarCargando();
        
        const res = await fetch('/api/materia_prima/editar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datos)
        });
        
        const resultado = await res.json();
        
        ocultarCargando();
        
        if (resultado.success) {
            await mostrarExito('Materia prima actualizada exitosamente');
            window.location.href = 'ver.php?id=' + materiaId;
        } else {
            mostrarError(resultado.message || 'Error al actualizar materia prima');
        }
        
    } catch (error) {
        ocultarCargando();
        console.error('Error:', error);
        mostrarError('Error: ' + error.message);
    }
});

document.addEventListener('DOMContentLoaded', cargarMateria);
</script>
