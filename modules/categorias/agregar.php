<?php
/**
 * ================================================
 * MÓDULO CATEGORÍAS - AGREGAR
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
            <h2 class="mb-1"><i class="bi bi-plus-circle"></i> Nueva Categoría</h2>
            <p class="text-muted mb-0">Crear nueva categoría de productos</p>
        </div>
        <a href="lista.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    <hr class="border-warning border-2 opacity-75 mb-4">

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Datos de la Categoría</h5>
                </div>
                <div class="card-body">
                    <form id="formCategoria">
                        
                        <div class="mb-3">
                            <label for="nombre" class="form-label">
                                Nombre <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="nombre" 
                                   placeholder="Ej: Anillos, Collares, Pulseras" required>
                            <small class="text-muted">Nombre de la categoría</small>
                        </div>

                        <div class="mb-3">
                            <label for="tipo_clasificacion" class="form-label">
                                Tipo de Clasificación <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="tipo_clasificacion" required>
                                <option value="">Seleccione...</option>
                                <option value="tipo">Tipo de Producto</option>
                                <option value="material">Material</option>
                            </select>
                            <small class="text-muted">Tipo: Anillos, Collares, etc. | Material: Oro 18K, Plata 925, etc.</small>
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" rows="4" 
                                      placeholder="Descripción opcional de la categoría"></textarea>
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="activo" checked>
                                <label class="form-check-label" for="activo">Categoría Activa</label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="lista.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-check-circle"></i> Guardar Categoría
                            </button>
                        </div>

                    </form>
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
document.getElementById('formCategoria').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const datos = {
        nombre: document.getElementById('nombre').value.trim(),
        tipo_clasificacion: document.getElementById('tipo_clasificacion').value,
        descripcion: document.getElementById('descripcion').value.trim() || null,
        activo: document.getElementById('activo').checked ? 1 : 0
    };
    
    if (!datos.nombre) {
        mostrarError('El nombre es requerido');
        return;
    }
    
    if (!datos.tipo_clasificacion) {
        mostrarError('Debe seleccionar el tipo de clasificación');
        return;
    }
    
    const confirmacion = await confirmarAccion('¿Crear esta categoría?');
    if (!confirmacion) return;
    
    try {
        mostrarCargando();
        const resultado = await api.crearCategoria(datos);
        ocultarCargando();
        
        if (resultado.success) {
            await mostrarExito('Categoría creada exitosamente');
            window.location.href = 'lista.php';
        } else {
            mostrarError(resultado.message || 'Error al crear');
        }
    } catch (error) {
        ocultarCargando();
        mostrarError('Error: ' + error.message);
    }
});
</script>