<?php
/**
 * ================================================
 * MÓDULO PROVEEDORES - AGREGAR
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
            <h2 class="mb-1"><i class="bi bi-plus-circle"></i> Nuevo Proveedor</h2>
            <p class="text-muted mb-0">Registrar nuevo proveedor</p>
        </div>
        <a href="lista.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    <hr class="border-warning border-2 opacity-75 mb-4">

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Datos del Proveedor</h5>
                </div>
                <div class="card-body">
                    <form id="formProveedor">
                        
                        <div class="mb-3">
                            <label for="nombre" class="form-label">
                                Nombre del Proveedor <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="nombre" name="nombre" 
                                   placeholder="Ej: José Ramírez" required>
                        </div>

                        <div class="mb-3">
                            <label for="empresa" class="form-label">Empresa</label>
                            <input type="text" class="form-control" id="empresa" name="empresa" 
                                   placeholder="Ej: Distribuidora El Dorado">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="contacto" class="form-label">Persona de Contacto</label>
                                <input type="text" class="form-control" id="contacto" name="contacto" 
                                       placeholder="Ej: María López">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono" 
                                       placeholder="1234-5678">
                                <small class="text-muted">Formato: 1234-5678</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   placeholder="proveedor@ejemplo.com">
                        </div>

                        <div class="mb-3">
                            <label for="direccion" class="form-label">Dirección</label>
                            <textarea class="form-control" id="direccion" name="direccion" rows="2" 
                                      placeholder="Zona 3, Guatemala"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="productos_suministra" class="form-label">
                                Productos que Suministra <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="productos_suministra" 
                                   name="productos_suministra" 
                                   placeholder="Oro 18K, Plata 925" required>
                            <small class="text-muted">Separados por comas</small>
                        </div>

                        <div class="mb-3">
                            <label for="notas" class="form-label">Notas</label>
                            <textarea class="form-control" id="notas" name="notas" rows="3"></textarea>
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="activo" checked>
                                <label class="form-check-label" for="activo">Proveedor Activo</label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="lista.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-check-circle"></i> Guardar Proveedor
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
    
    const confirmacion = await confirmarAccion('¿Crear este proveedor?');
    if (!confirmacion) return;
    
    try {
        mostrarCargando();
        const resultado = await api.crearProveedor(datos);
        ocultarCargando();
        
        if (resultado.success) {
            await mostrarExito('Proveedor creado exitosamente');
            window.location.href = 'lista.php';
        } else {
            mostrarError(resultado.message || 'Error al crear');
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
</script>