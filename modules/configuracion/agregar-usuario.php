<?php
/**
 * ================================================
 * MÓDULO USUARIOS - AGREGAR
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
            <h2 class="mb-1"><i class="bi bi-plus-circle"></i> Nuevo Usuario</h2>
            <p class="text-muted mb-0">Registrar nuevo usuario del sistema</p>
        </div>
        <a href="usuarios.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    <hr class="border-warning border-2 opacity-75 mb-4">

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Datos del Usuario</h5>
                </div>
                <div class="card-body">
                    <form id="formUsuario">
                        
                        <div class="mb-3">
                            <label for="nombre" class="form-label">
                                Nombre Completo <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="nombre" 
                                   placeholder="Ej: Carlos Méndez" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">
                                Email <span class="text-danger">*</span>
                            </label>
                            <input type="email" class="form-control" id="email" 
                                   placeholder="usuario@ejemplo.com" required>
                            <small class="text-muted">Se usará como nombre de usuario</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">
                                    Contraseña <span class="text-danger">*</span>
                                </label>
                                <input type="password" class="form-control" id="password" 
                                       placeholder="Mínimo 6 caracteres" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="password_confirm" class="form-label">
                                    Confirmar Contraseña <span class="text-danger">*</span>
                                </label>
                                <input type="password" class="form-control" id="password_confirm" 
                                       placeholder="Repita la contraseña" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="rol" class="form-label">
                                    Rol <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="rol" required>
                                    <option value="">Seleccione...</option>
                                    <option value="administrador">Administrador</option>
                                    <option value="dueño">Dueño</option>
                                    <option value="vendedor">Vendedor</option>
                                    <option value="cajero">Cajero</option>
                                    <option value="orfebre">Orfebre</option>
                                    <option value="publicidad">Publicidad</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="sucursal_id" class="form-label">Sucursal</label>
                                <select class="form-select" id="sucursal_id">
                                    <option value="">Sin sucursal asignada</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="activo" checked>
                                <label class="form-check-label" for="activo">Usuario Activo</label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="usuarios.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-check-circle"></i> Crear Usuario
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
async function cargarSucursales() {
    try {
        const resultado = await api.listarSucursales({ activo: 1 });
        
        if (resultado.success && resultado.data) {
            const select = document.getElementById('sucursal_id');
            
            resultado.data.forEach(sucursal => {
                const option = document.createElement('option');
                option.value = sucursal.id;
                option.textContent = sucursal.nombre;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error al cargar sucursales:', error);
    }
}

document.getElementById('formUsuario').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const password = document.getElementById('password').value;
    const passwordConfirm = document.getElementById('password_confirm').value;
    
    if (password !== passwordConfirm) {
        mostrarError('Las contraseñas no coinciden');
        return;
    }
    
    if (password.length < 6) {
        mostrarError('La contraseña debe tener mínimo 6 caracteres');
        return;
    }
    
    const datos = {
        nombre: document.getElementById('nombre').value.trim(),
        email: document.getElementById('email').value.trim(),
        password: password,
        rol: document.getElementById('rol').value,
        sucursal_id: document.getElementById('sucursal_id').value || null,
        activo: document.getElementById('activo').checked ? 1 : 0
    };
    
    if (!datos.nombre || !datos.email || !datos.rol) {
        mostrarError('Complete los campos requeridos');
        return;
    }
    
    if (!validarEmail(datos.email)) {
        mostrarError('Email no válido');
        return;
    }
    
    const confirmacion = await confirmarAccion('¿Crear este usuario?');
    if (!confirmacion) return;
    
    try {
        mostrarCargando();
        const resultado = await api.crearUsuario(datos);
        ocultarCargando();
        
        if (resultado.success) {
            await mostrarExito('Usuario creado exitosamente');
            window.location.href = 'usuarios.php';
        } else {
            mostrarError(resultado.message || 'Error al crear');
        }
    } catch (error) {
        ocultarCargando();
        mostrarError('Error: ' + error.message);
    }
});

document.addEventListener('DOMContentLoaded', cargarSucursales);
</script>