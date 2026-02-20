<?php
/**
 * ================================================
 * MÓDULO USUARIOS - EDITAR
 * ================================================
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();
requiere_rol(['administrador', 'dueño']);

$usuario_id = $_GET['id'] ?? null;

if (!$usuario_id) {
    header('Location: usuarios.php');
    exit;
}

require_once '../../includes/header.php';
require_once '../../includes/navbar.php';
?>

<div class="container-fluid px-4 py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-1"><i class="bi bi-pencil"></i> Editar Usuario</h2>
            <p class="text-muted mb-0">Modificar datos del usuario</p>
        </div>
        <a href="usuarios.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    <hr class="border-warning border-2 opacity-75 mb-4">

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Datos del Usuario</h5>
                </div>
                <div class="card-body">
                    <form id="formUsuario">
                        
                        <div class="mb-3">
                            <label for="nombre" class="form-label">
                                Nombre Completo <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="nombre" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">
                                Email <span class="text-danger">*</span>
                            </label>
                            <input type="email" class="form-control" id="email" required>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> 
                            Deja los campos de contraseña vacíos si no deseas cambiarla
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Nueva Contraseña</label>
                                <input type="password" class="form-control" id="password" 
                                       placeholder="Dejar vacío para no cambiar">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="password_confirm" class="form-label">Confirmar Contraseña</label>
                                <input type="password" class="form-control" id="password_confirm">
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
                                <input class="form-check-input" type="checkbox" id="activo">
                                <label class="form-check-label" for="activo">Usuario Activo</label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="usuarios.php" class="btn btn-secondary">
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
const usuarioId = <?php echo $usuario_id; ?>;

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

async function cargarUsuario() {
    try {
        mostrarCargando();
        
        await cargarSucursales();
        
        const resultadoLista = await api.listarUsuarios();
        
        ocultarCargando();
        
        if (resultadoLista.success && resultadoLista.data) {
            const usuario = resultadoLista.data.find(u => u.id == usuarioId);
            
            if (usuario) {
                llenarFormulario(usuario);
            } else {
                mostrarError('Usuario no encontrado');
                setTimeout(() => window.location.href = 'usuarios.php', 2000);
            }
        }
    } catch (error) {
        ocultarCargando();
        mostrarError('Error: ' + error.message);
    }
}

function llenarFormulario(usuario) {
    document.getElementById('nombre').value = usuario.nombre || '';
    document.getElementById('email').value = usuario.email || '';
    document.getElementById('rol').value = usuario.rol || '';
    document.getElementById('sucursal_id').value = usuario.sucursal_id || '';
    document.getElementById('activo').checked = usuario.activo == 1;
}

document.getElementById('formUsuario').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const password = document.getElementById('password').value;
    const passwordConfirm = document.getElementById('password_confirm').value;
    
    if (password && password !== passwordConfirm) {
        mostrarError('Las contraseñas no coinciden');
        return;
    }
    
    if (password && password.length < 6) {
        mostrarError('La contraseña debe tener mínimo 6 caracteres');
        return;
    }
    
    const datos = {
        nombre: document.getElementById('nombre').value.trim(),
        email: document.getElementById('email').value.trim(),
        rol: document.getElementById('rol').value,
        sucursal_id: document.getElementById('sucursal_id').value || null,
        activo: document.getElementById('activo').checked ? 1 : 0
    };
    
    // Solo enviar password si se llenó
    if (password) {
        datos.password = password;
    }
    
    if (!datos.nombre || !datos.email || !datos.rol) {
        mostrarError('Complete los campos requeridos');
        return;
    }
    
    if (!validarEmail(datos.email)) {
        mostrarError('Email no válido');
        return;
    }
    
    const confirmacion = await confirmarAccion('¿Guardar los cambios?');
    if (!confirmacion) return;
    
    try {
        mostrarCargando();
        const resultado = await api.editarUsuario(usuarioId, datos);
        ocultarCargando();
        
        if (resultado.success) {
            await mostrarExito('Usuario actualizado exitosamente');
            window.location.href = 'usuarios.php';
        } else {
            mostrarError(resultado.message || 'Error al actualizar');
        }
    } catch (error) {
        ocultarCargando();
        mostrarError('Error: ' + error.message);
    }
});

document.addEventListener('DOMContentLoaded', cargarUsuario);
</script>