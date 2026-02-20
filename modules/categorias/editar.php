<?php
/**
 * ================================================
 * MÓDULO CATEGORÍAS - EDITAR
 * ================================================
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();
requiere_rol(['administrador', 'dueño']);

$categoria_id = $_GET['id'] ?? null;

if (!$categoria_id) {
    header('Location: lista.php');
    exit;
}

require_once '../../includes/header.php';
require_once '../../includes/navbar.php';
?>

<div class="container-fluid px-4 py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-1"><i class="bi bi-pencil"></i> Editar Categoría</h2>
            <p class="text-muted mb-0">Modificar datos de la categoría</p>
        </div>
        <a href="lista.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    <hr class="border-warning border-2 opacity-75 mb-4">

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Datos de la Categoría</h5>
                </div>
                <div class="card-body">
                    <form id="formCategoria">
                        
                        <div class="mb-3">
                            <label for="nombre" class="form-label">
                                Nombre <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="nombre" required>
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
                            <textarea class="form-control" id="descripcion" rows="4"></textarea>
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="activo">
                                <label class="form-check-label" for="activo">Categoría Activa</label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="lista.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-warning btn-lg">
                                <i class="bi bi-check-circle"></i> Guardar Cambios
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
const categoriaId = <?php echo $categoria_id; ?>;

async function cargarCategoria() {
    try {
        mostrarCargando();
        
        const resultado = await api.listarCategorias();
        
        ocultarCargando();
        
        if (resultado.success) {
            const categoria = resultado.data.find(c => c.id == categoriaId);
            
            if (!categoria) {
                mostrarError('Categoría no encontrada');
                setTimeout(() => window.location.href = 'lista.php', 2000);
                return;
            }
            
            llenarFormulario(categoria);
        }
    } catch (error) {
        ocultarCargando();
        mostrarError('Error: ' + error.message);
    }
}

function llenarFormulario(categoria) {
    document.getElementById('nombre').value = categoria.nombre || '';
    document.getElementById('tipo_clasificacion').value = categoria.tipo_clasificacion || '';
    document.getElementById('descripcion').value = categoria.descripcion || '';
    document.getElementById('activo').checked = categoria.activo == 1;
}

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
    
    const confirmacion = await confirmarAccion('¿Guardar los cambios?');
    if (!confirmacion) return;
    
    try {
        mostrarCargando();
        const resultado = await api.editarCategoria(categoriaId, datos);
        ocultarCargando();
        
        if (resultado.success) {
            await mostrarExito('Categoría actualizada exitosamente');
            window.location.href = 'lista.php';
        } else {
            mostrarError(resultado.message || 'Error al actualizar');
        }
    } catch (error) {
        ocultarCargando();
        mostrarError('Error: ' + error.message);
    }
});

document.addEventListener('DOMContentLoaded', cargarCategoria);
</script>