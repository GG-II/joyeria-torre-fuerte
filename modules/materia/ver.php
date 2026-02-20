<?php
/**
 * ================================================
 * MÓDULO MATERIA PRIMA - VER
 * ================================================
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();

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
            <h2 class="mb-1"><i class="bi bi-eye"></i> Detalles de Materia Prima</h2>
            <p class="text-muted mb-0" id="nombreMateria">Cargando...</p>
        </div>
        <div>
            <?php if (tiene_permiso('materia_prima', 'editar')): ?>
            <a href="editar.php?id=<?php echo $materia_id; ?>" class="btn btn-warning me-2" id="btnEditar" style="display: none;">
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
            
            <!-- Información -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Información</h5>
                    <span id="estadoBadge" class="badge">-</span>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Nombre</label>
                            <h4 id="nombre">-</h4>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Tipo</label>
                            <h4 id="tipo">-</h4>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label text-muted">Unidad de Medida</label>
                            <p id="unidadMedida" class="fs-5 mb-0">-</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted">Stock Mínimo</label>
                            <p id="stockMinimo" class="fs-5 mb-0">-</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted">Precio por Unidad</label>
                            <p id="precioUnidad" class="fs-5 mb-0">-</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Fecha Creación</label>
                            <p id="fechaCreacion">-</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Última Actualización</label>
                            <p id="fechaActualizacion">-</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Historial (placeholder) -->
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Historial de Movimientos</h5>
                </div>
                <div class="card-body">
                    <p class="text-center text-muted">
                        <i class="bi bi-info-circle"></i> El historial de movimientos estará disponible próximamente
                    </p>
                </div>
            </div>

        </div>

        <!-- Columna Derecha -->
        <div class="col-lg-4">
            
            <!-- Stock Actual -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-box-seam"></i> Stock Actual</h5>
                </div>
                <div class="card-body text-center" id="stockContainer">
                    <h1 id="cantidadActual" class="display-4 mb-0">-</h1>
                    <p id="unidadMedidaStock" class="text-muted mb-3">-</p>
                    <div id="alertaStock"></div>
                </div>
            </div>

            <!-- Valor Total -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="bi bi-cash-coin"></i> Valor Total</h5>
                </div>
                <div class="card-body text-center">
                    <h2 id="valorTotal" class="mb-0">-</h2>
                    <small class="text-muted">Cantidad × Precio</small>
                </div>
            </div>

            <!-- Botón Ajustar Stock -->
            <?php if (tiene_permiso('materia_prima', 'ajustar_stock')): ?>
            <button type="button" class="btn btn-warning btn-lg w-100 mb-2" id="btnAjustarStock">
                <i class="bi bi-arrow-left-right"></i> Ajustar Stock
            </button>
            <?php endif; ?>

            <!-- Botón Cambiar Estado -->
            <?php if (tiene_permiso('materia_prima', 'editar')): ?>
            <button type="button" class="btn btn-outline-secondary w-100" id="btnCambiarEstado">
                <i class="bi bi-toggle-on"></i> <span id="textoEstado">Cambiar Estado</span>
            </button>
            <?php endif; ?>

        </div>
    </div>

</div>

<!-- Modal Ajustar Stock -->
<div class="modal fade" id="modalAjustarStock" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="bi bi-arrow-left-right"></i> Ajustar Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <strong>Stock Actual:</strong> <span id="modalStockActual">-</span>
                </div>

                <div class="mb-3">
                    <label for="cantidadNueva" class="form-label">
                        Nueva Cantidad <span class="text-danger">*</span>
                    </label>
                    <input type="number" class="form-control form-control-lg" id="cantidadNueva" 
                           step="0.001" min="0" required>
                </div>

                <div class="mb-3">
                    <label for="motivoAjuste" class="form-label">
                        Motivo del Ajuste <span class="text-danger">*</span>
                    </label>
                    <select class="form-select" id="motivoAjuste" required>
                        <option value="">Seleccione...</option>
                        <option value="Compra a proveedor">Compra a proveedor</option>
                        <option value="Uso en taller">Uso en taller</option>
                        <option value="Ajuste de inventario">Ajuste de inventario</option>
                        <option value="Devolución">Devolución</option>
                        <option value="Merma">Merma</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>

                <div id="previewAjuste" class="alert alert-secondary" style="display: none;">
                    <strong>Cambio:</strong> <span id="textoCambio"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" id="btnConfirmarAjuste">
                    <i class="bi bi-check-circle"></i> Confirmar Ajuste
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>

<script src="../../assets/js/vendors/sweetalert2/sweetalert2.all.min.js"></script>
<script src="../../assets/js/common.js"></script>
<script src="../../assets/js/api-client.js"></script>

<script>
const materiaId = <?php echo $materia_id; ?>;
let materia = null;

async function cargarMateria() {
    try {
        mostrarCargando();
        
        const res = await fetch(`/joyeria-torre-fuerte/api/materia_prima/listar.php`);
        const data = await res.json();
        
        if (!data.success) {
            ocultarCargando();
            await mostrarError('Error al cargar materia prima');
            window.location.href = 'lista.php';
            return;
        }
        
        // Buscar la materia por ID
        materia = (data.data || []).find(m => m.id == materiaId);
        
        if (!materia) {
            ocultarCargando();
            await mostrarError('Materia prima no encontrada');
            window.location.href = 'lista.php';
            return;
        }
        
        mostrarDatos(materia);
        
        ocultarCargando();
        
    } catch (error) {
        ocultarCargando();
        console.error('Error:', error);
        mostrarError('Error al cargar materia prima');
    }
}

function mostrarDatos(m) {
    // Título
    document.getElementById('nombreMateria').textContent = m.nombre;
    
    // Estado
    const estadoBadge = document.getElementById('estadoBadge');
    estadoBadge.className = m.activo == 1 ? 'badge bg-success' : 'badge bg-secondary';
    estadoBadge.textContent = m.activo == 1 ? 'Activo' : 'Inactivo';
    
    // Mostrar botón editar solo si está activo
    if (m.activo == 1) {
        document.getElementById('btnEditar').style.display = 'inline-block';
    }
    
    // Información
    document.getElementById('nombre').textContent = m.nombre;
    
    const tipoBadge = {
        'oro': '<span class="badge bg-warning text-dark fs-5">Oro</span>',
        'plata': '<span class="badge bg-secondary fs-5">Plata</span>',
        'piedra': '<span class="badge bg-info fs-5">Piedra</span>',
        'otro': '<span class="badge bg-primary fs-5">Otro</span>'
    };
    document.getElementById('tipo').innerHTML = tipoBadge[m.tipo] || m.tipo;
    
    document.getElementById('unidadMedida').textContent = m.unidad_medida;
    document.getElementById('stockMinimo').textContent = parseFloat(m.stock_minimo).toFixed(3);
    document.getElementById('precioUnidad').textContent = m.precio_por_unidad ? formatearMoneda(m.precio_por_unidad) : '-';
    document.getElementById('fechaCreacion').textContent = formatearFechaHora(m.fecha_creacion);
    document.getElementById('fechaActualizacion').textContent = formatearFechaHora(m.fecha_actualizacion);
    
    // Stock
    const cantidad = parseFloat(m.cantidad_disponible);
    const minimo = parseFloat(m.stock_minimo);
    
    document.getElementById('cantidadActual').textContent = cantidad.toFixed(3);
    document.getElementById('unidadMedidaStock').textContent = m.unidad_medida;
    
    // Alerta de stock
    const alertaDiv = document.getElementById('alertaStock');
    if (cantidad === 0) {
        alertaDiv.innerHTML = '<div class="alert alert-danger mb-0"><i class="bi bi-exclamation-triangle"></i> <strong>Agotado</strong></div>';
    } else if (cantidad <= minimo) {
        alertaDiv.innerHTML = '<div class="alert alert-warning mb-0"><i class="bi bi-exclamation-circle"></i> <strong>Stock Bajo</strong></div>';
    } else {
        alertaDiv.innerHTML = '<div class="alert alert-success mb-0"><i class="bi bi-check-circle"></i> <strong>Stock OK</strong></div>';
    }
    
    // Valor total
    const precio = parseFloat(m.precio_por_unidad) || 0;
    const valorTotal = cantidad * precio;
    document.getElementById('valorTotal').textContent = valorTotal > 0 ? formatearMoneda(valorTotal) : '-';
    
    // Botón estado
    const btnEstado = document.getElementById('btnCambiarEstado');
    const textoEstado = document.getElementById('textoEstado');
    if (m.activo == 1) {
        textoEstado.textContent = 'Desactivar';
    } else {
        textoEstado.textContent = 'Activar';
    }
}

// Ajustar Stock
document.getElementById('btnAjustarStock')?.addEventListener('click', function() {
    if (!materia) return;
    
    const modal = new bootstrap.Modal(document.getElementById('modalAjustarStock'));
    document.getElementById('modalStockActual').textContent = parseFloat(materia.cantidad_disponible).toFixed(3) + ' ' + materia.unidad_medida;
    document.getElementById('cantidadNueva').value = parseFloat(materia.cantidad_disponible).toFixed(3);
    document.getElementById('motivoAjuste').value = '';
    document.getElementById('previewAjuste').style.display = 'none';
    
    modal.show();
});

// Preview de cambio
document.getElementById('cantidadNueva')?.addEventListener('input', function() {
    const actual = parseFloat(materia.cantidad_disponible);
    const nueva = parseFloat(this.value) || 0;
    const diferencia = nueva - actual;
    
    const preview = document.getElementById('previewAjuste');
    const texto = document.getElementById('textoCambio');
    
    if (diferencia === 0) {
        preview.style.display = 'none';
    } else {
        preview.style.display = 'block';
        if (diferencia > 0) {
            texto.innerHTML = `<span class="text-success">+${diferencia.toFixed(3)} ${materia.unidad_medida}</span>`;
        } else {
            texto.innerHTML = `<span class="text-danger">${diferencia.toFixed(3)} ${materia.unidad_medida}</span>`;
        }
    }
});

// Confirmar ajuste
document.getElementById('btnConfirmarAjuste')?.addEventListener('click', async function() {
    const cantidadNueva = parseFloat(document.getElementById('cantidadNueva').value);
    const motivo = document.getElementById('motivoAjuste').value;
    
    if (!cantidadNueva && cantidadNueva !== 0) {
        mostrarError('Ingrese la nueva cantidad');
        return;
    }
    
    if (!motivo) {
        mostrarError('Seleccione el motivo del ajuste');
        return;
    }
    
    try {
        mostrarCargando();
        
        const res = await fetch('/joyeria-torre-fuerte/api/materia_prima/ajustar_stock.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id: materiaId,
                cantidad_nueva: cantidadNueva,
                motivo: motivo
            })
        });
        
        const resultado = await res.json();
        
        ocultarCargando();
        
        if (resultado.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalAjustarStock'));
            modal.hide();
            
            await mostrarExito('Stock ajustado exitosamente');
            window.location.reload();
        } else {
            mostrarError(resultado.message || 'Error al ajustar stock');
        }
        
    } catch (error) {
        ocultarCargando();
        mostrarError('Error: ' + error.message);
    }
});

// Cambiar estado
document.getElementById('btnCambiarEstado')?.addEventListener('click', async function() {
    if (!materia) return;
    
    const accion = materia.activo == 1 ? 'desactivar' : 'activar';
    const texto = materia.activo == 1 ? 'desactivar' : 'activar';
    
    const confirmacion = await confirmarAccion(
        `¿${texto.charAt(0).toUpperCase() + texto.slice(1)} esta materia prima?`,
        materia.nombre
    );
    
    if (!confirmacion) return;
    
    try {
        mostrarCargando();
        
        const res = await fetch('/joyeria-torre-fuerte/api/materia_prima/cambiar_estado.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id: materiaId,
                accion: accion
            })
        });
        
        const resultado = await res.json();
        
        ocultarCargando();
        
        if (resultado.success) {
            await mostrarExito(resultado.message);
            window.location.reload();
        } else {
            mostrarError(resultado.message || 'Error');
        }
        
    } catch (error) {
        ocultarCargando();
        mostrarError('Error: ' + error.message);
    }
});

document.addEventListener('DOMContentLoaded', cargarMateria);
</script>
