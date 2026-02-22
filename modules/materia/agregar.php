<?php
/**
 * ================================================
 * MÓDULO MATERIA PRIMA - AGREGAR
 * ================================================
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();
requiere_rol(['administrador', 'dueño']);

require_once '../../includes/header.php';
require_once '../../includes/navbar.php';
?>

<div class="container-fluid px-4 py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-1"><i class="bi bi-plus-circle"></i> Nueva Materia Prima</h2>
            <p class="text-muted mb-0">Registrar oro, plata, piedras o insumos</p>
        </div>
        <a href="lista.php" class="btn btn-secondary">
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
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="cantidad_disponible" class="form-label">
                                    Cantidad Inicial
                                </label>
                                <input type="number" class="form-control" id="cantidad_disponible" 
                                       step="0.001" min="0" value="0">
                                <small class="text-muted">Puede dejarlo en 0 y ajustarlo después</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="stock_minimo" class="form-label">
                                    Stock Mínimo (Alerta)
                                </label>
                                <input type="number" class="form-control" id="stock_minimo" 
                                       step="0.001" min="0" value="5">
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
                            <small class="text-muted">Cantidad:</small>
                            <p id="previewCantidad" class="mb-0">-</p>
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

                <!-- Botón Guardar -->
                <button type="submit" class="btn btn-warning btn-lg w-100">
                    <i class="bi bi-check-circle"></i> Crear Materia Prima
                </button>

            </div>
        </div>
    </form>

</div>

<?php require_once '../../includes/footer.php'; ?>

<script src="../../assets/js/vendors/sweetalert2/sweetalert2.all.min.js"></script>
<script src="../../assets/js/common.js"></script>
<script src="../../assets/js/api-client.js"></script>

<script>
// Actualizar preview en tiempo real
function actualizarPreview() {
    const nombre = document.getElementById('nombre').value || '-';
    const tipo = document.getElementById('tipo').value || '-';
    const unidad = document.getElementById('unidad_medida').value || '-';
    const cantidad = document.getElementById('cantidad_disponible').value || '0';
    const minimo = document.getElementById('stock_minimo').value || '0';
    const precio = document.getElementById('precio_por_unidad').value || '0';
    
    document.getElementById('previewNombre').textContent = nombre;
    document.getElementById('previewTipo').textContent = tipo.charAt(0).toUpperCase() + tipo.slice(1);
    document.getElementById('previewUnidad').textContent = unidad;
    document.getElementById('previewCantidad').textContent = parseFloat(cantidad).toFixed(3);
    document.getElementById('previewMinimo').textContent = parseFloat(minimo).toFixed(3);
    document.getElementById('previewPrecio').textContent = parseFloat(precio) > 0 ? formatearMoneda(precio) : '-';
}

document.getElementById('nombre').addEventListener('input', actualizarPreview);
document.getElementById('tipo').addEventListener('change', actualizarPreview);
document.getElementById('unidad_medida').addEventListener('change', actualizarPreview);
document.getElementById('cantidad_disponible').addEventListener('input', actualizarPreview);
document.getElementById('stock_minimo').addEventListener('input', actualizarPreview);
document.getElementById('precio_por_unidad').addEventListener('input', actualizarPreview);

// Submit
document.getElementById('formMateria').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const datos = {
        nombre: document.getElementById('nombre').value.trim(),
        tipo: document.getElementById('tipo').value,
        unidad_medida: document.getElementById('unidad_medida').value,
        cantidad_disponible: parseFloat(document.getElementById('cantidad_disponible').value) || 0,
        stock_minimo: parseFloat(document.getElementById('stock_minimo').value) || 5,
        precio_por_unidad: parseFloat(document.getElementById('precio_por_unidad').value) || null,
        activo: 1
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
    
    const confirmacion = await confirmarAccion(
        '¿Crear esta materia prima?',
        `${datos.nombre} - ${datos.tipo}`
    );
    
    if (!confirmacion) return;
    
    try {
        mostrarCargando();
        
        const res = await fetch('/api/materia_prima/crear.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datos)
        });
        
        const resultado = await res.json();
        
        ocultarCargando();
        
        if (resultado.success) {
            await mostrarExito('Materia prima creada exitosamente');
            window.location.href = 'ver.php?id=' + resultado.data.id;
        } else {
            mostrarError(resultado.message || 'Error al crear materia prima');
        }
        
    } catch (error) {
        ocultarCargando();
        console.error('Error:', error);
        mostrarError('Error: ' + error.message);
    }
});

// Actualizar preview inicial
actualizarPreview();
</script>
