<?php
/**
 * ================================================
 * MÓDULO PROVEEDORES - EDITAR
 * ================================================
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();
requiere_rol(['administrador', 'dueño']);

$proveedor_id = $_GET['id'] ?? null;

if (!$proveedor_id) {
    header('Location: lista.php');
    exit;
}

require_once '../../includes/header.php';
require_once '../../includes/navbar.php';
?>

<div class="container-fluid px-4 py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-1"><i class="bi bi-pencil"></i> Editar Proveedor</h2>
            <p class="text-muted mb-0">Modificar datos del proveedor</p>
        </div>
        <a href="lista.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    <hr class="border-warning border-2 opacity-75 mb-4">

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Datos del Proveedor</h5>
                </div>
                <div class="card-body">
                    <form id="formProveedor">
                        
                        <div class="mb-3">
                            <label for="nombre" class="form-label">
                                Nombre del Proveedor <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="nombre" required>
                        </div>

                        <div class="mb-3">
                            <label for="empresa" class="form-label">Empresa</label>
                            <input type="text" class="form-control" id="empresa">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="contacto" class="form-label">Persona de Contacto</label>
                                <input type="text" class="form-control" id="contacto">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="telefono">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email">
                        </div>

                        <div class="mb-3">
                            <label for="direccion" class="form-label">Dirección</label>
                            <textarea class="form-control" id="direccion" rows="2"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="productos_suministra" class="form-label">
                                Productos que Suministra <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="productos_suministra" required>
                        </div>

                        <div class="mb-3">
                            <label for="notas" class="form-label">Notas</label>
                            <textarea class="form-control" id="notas" rows="3"></textarea>
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="activo">
                                <label class="form-check-label" for="activo">Proveedor Activo</label>
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
const proveedorId = <?php echo $proveedor_id; ?>;

async function cargarProveedor() {
    try {
        mostrarCargando();
        
        const resultado = await api.listarProveedores();
        
        ocultarCargando();
        
        if (resultado.success) {
            const proveedor = resultado.data.find(p => p.id == proveedorId);
            
            if (!proveedor) {
                mostrarError('Proveedor no encontrado');
                setTimeout(() => window.location.href = 'lista.php', 2000);
                return;
            }
            
            llenarFormulario(proveedor);
        }
    } catch (error) {
        ocultarCargando();
        mostrarError('Error al cargar: ' + error.message);
    }
}

function llenarFormulario(proveedor) {
    document.getElementById('nombre').value = proveedor.nombre || '';
    document.getElementById('empresa').value = proveedor.empresa || '';
    document.getElementById('contacto').value = proveedor.contacto || '';
    document.getElementById('telefono').value = proveedor.telefono || '';
    document.getElementById('email').value = proveedor.email || '';
    document.getElementById('direccion').value = proveedor.direccion || '';
    document.getElementById('productos_suministra').value = proveedor.productos_suministra || '';
    document.getElementById('notas').value = proveedor.notas || '';
    document.getElementById('activo').checked = proveedor.activo == 1;
}

document.getElementById('formProveedor').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const datos = {
        nombre: document.getElementById('nombre').value.trim(),
        empresa: document.getElementById('empresa').value.trim() || null,
        contacto: document.getElementById('contacto').value.trim() || null,
        telefono: document.getElementById('telefono').value.trim() || null,
        email: document.getElementById('email').value.trim() || null,
        direccion: document.getElementById('direccion').value.trim() || null,
        productos_suministra: document.getElementById('productos_suministra').value.trim(),
        notas: document.getElementById('notas').value.trim() || null,
        activo: document.getElementById('activo').checked ? 1 : 0
    };
    
    if (!datos.nombre || !datos.productos_suministra) {
        mostrarError('Complete los campos requeridos');
        return;
    }
    
    if (datos.email && !validarEmail(datos.email)) {
        mostrarError('Email no válido');
        return;
    }
    
    const confirmacion = await confirmarAccion('¿Guardar los cambios?');
    if (!confirmacion) return;
    
    try {
        mostrarCargando();
        const resultado = await api.editarProveedor(proveedorId, datos);
        ocultarCargando();
        
        if (resultado.success) {
            await mostrarExito('Proveedor actualizado exitosamente');
            window.location.href = 'lista.php';
        } else {
            mostrarError(resultado.message || 'Error al actualizar');
        }
    } catch (error) {
        ocultarCargando();
        mostrarError('Error: ' + error.message);
    }
});

document.getElementById('telefono').addEventListener('input', function(e) {
    let valor = e.target.value.replace(/\D/g, '');
    if (valor.length > 4) {
        valor = valor.substring(0, 4) + '-' + valor.substring(4, 8);
    }
    e.target.value = valor;
});

document.addEventListener('DOMContentLoaded', cargarProveedor);
</script>