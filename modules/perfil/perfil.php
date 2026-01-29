<?php
/**
 * ================================================
 * PERFIL DE USUARIO
 * ================================================
 * 
 * TODO FASE 5: Conectar con API
 * GET /api/perfil/ver.php - Obtener datos del usuario actual
 * POST /api/perfil/actualizar.php - Actualizar perfil
 * POST /api/perfil/cambiar-password.php - Cambiar contraseña
 * 
 * Tabla BD: usuarios
 * Campos: id, nombre, email, password, rol, sucursal_id, foto_perfil,
 *         telefono, fecha_nacimiento, direccion, activo, fecha_creacion, ultimo_acceso
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();

$titulo_pagina = 'Mi Perfil';
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="container-fluid main-content">
    <div class="page-header mb-4">
        <div class="row align-items-center g-3">
            <div class="col-md-6">
                <h1 class="mb-2"><i class="bi bi-person-circle"></i> Mi Perfil</h1>
                <p class="text-muted mb-0">Información personal y configuración de cuenta</p>
            </div>
            <div class="col-md-6 text-md-end">
                <a href="<?php echo BASE_URL; ?>dashboard.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver al Dashboard
                </a>
            </div>
        </div>
    </div>

    <div id="loadingState" class="text-center py-5">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
        <p class="mt-3 text-muted">Cargando perfil...</p>
    </div>

    <div id="mainContent" style="display: none;">
        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card shadow-sm text-center">
                    <div class="card-body py-4">
                        <div class="mb-3">
                            <div class="profile-avatar mx-auto mb-3">
                                <i class="bi bi-person-circle" style="font-size: 120px; color: #1e3a8a;"></i>
                            </div>
                        </div>
                        <h4 class="mb-1" id="perfilNombre">-</h4>
                        <p class="text-muted mb-2" id="perfilEmail">-</p>
                        <span class="badge" id="perfilRol" style="font-size: 0.9rem;">-</span>
                    </div>
                </div>

                <div class="card shadow-sm mt-3">
                    <div class="card-header" style="background-color: #1e3a8a; color: white;">
                        <i class="bi bi-info-circle"></i> Información de la Cuenta
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted d-block">Sucursal:</small>
                            <strong id="perfilSucursal">-</strong>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Miembro desde:</small>
                            <strong id="perfilFechaCreacion">-</strong>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Último acceso:</small>
                            <strong id="perfilUltimoAcceso">-</strong>
                        </div>
                        <div class="mb-0">
                            <small class="text-muted d-block">Estado:</small>
                            <span class="badge bg-success">Activo</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card shadow-sm mb-3">
                    <div class="card-header" style="background-color: #1e3a8a; color: white;">
                        <i class="bi bi-pencil-square"></i> Editar Información Personal
                    </div>
                    <div class="card-body">
                        <form id="formPerfil">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nombre Completo *</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>
                            <div class="row g-3 mt-2">
                                <div class="col-md-6">
                                    <label class="form-label">Teléfono</label>
                                    <input type="tel" class="form-control" id="telefono" name="telefono">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Fecha de Nacimiento</label>
                                    <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento">
                                </div>
                            </div>
                            <div class="mb-3 mt-3">
                                <label class="form-label">Dirección</label>
                                <textarea class="form-control" id="direccion" name="direccion" rows="2"></textarea>
                            </div>
                            <div class="text-end">
                                <button type="button" class="btn btn-secondary" onclick="cargarPerfil()">
                                    <i class="bi bi-arrow-clockwise"></i> Cancelar
                                </button>
                                <button type="submit" class="btn btn-primary" id="btnGuardarPerfil">
                                    <i class="bi bi-save"></i> Guardar Cambios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <i class="bi bi-shield-lock"></i> Cambiar Contraseña
                    </div>
                    <div class="card-body">
                        <form id="formPassword">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                La contraseña debe tener al menos 8 caracteres y combinar letras y números.
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Contraseña Actual *</label>
                                <input type="password" class="form-control" id="password_actual" name="password_actual" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nueva Contraseña *</label>
                                <input type="password" class="form-control" id="password_nueva" name="password_nueva" required minlength="8">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Confirmar Nueva Contraseña *</label>
                                <input type="password" class="form-control" id="password_confirmar" name="password_confirmar" required minlength="8">
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-warning" id="btnCambiarPassword">
                                    <i class="bi bi-key"></i> Cambiar Contraseña
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.main-content { padding: 20px; min-height: calc(100vh - 120px); }
.page-header h1 { font-size: 1.75rem; font-weight: 600; color: #1a1a1a; }
.shadow-sm { box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08) !important; }
.card-body { padding: 25px; }
.profile-avatar { width: 150px; height: 150px; border-radius: 50%; display: flex; align-items: center; justify-content: center; overflow: hidden; border: 4px solid #e5e7eb; }
.profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
.form-label { font-weight: 500; margin-bottom: 0.5rem; color: #374151; }
.form-control, .form-select { border: 1px solid #d1d5db; border-radius: 6px; }
.form-control:focus, .form-select:focus { border-color: #1e3a8a; box-shadow: 0 0 0 0.2rem rgba(30, 58, 138, 0.15); }
@media (max-width: 575.98px) {
    .main-content { padding: 15px 10px; }
    .page-header h1 { font-size: 1.5rem; }
    .card-body { padding: 15px; }
    .profile-avatar { width: 120px; height: 120px; }
}
@media (min-width: 576px) and (max-width: 767.98px) { .main-content { padding: 18px 15px; } }
@media (min-width: 992px) { .main-content { padding: 25px 30px; } }
@media (max-width: 767.98px) { .btn, .form-control, .form-select { min-height: 44px; } }
</style>

<script>
const badgeRoles = {
    'administrador': 'bg-danger',
    'dueño': 'bg-warning text-dark',
    'vendedor': 'bg-primary',
    'cajero': 'bg-info',
    'orfebre': 'bg-success',
    'publicidad': 'bg-secondary'
};

document.addEventListener('DOMContentLoaded', function() {
    cargarPerfil();
    
    document.getElementById('formPerfil').addEventListener('submit', function(e) {
        e.preventDefault();
        guardarPerfil();
    });
    
    document.getElementById('formPassword').addEventListener('submit', function(e) {
        e.preventDefault();
        cambiarPassword();
    });
});

function cargarPerfil() {
    /* TODO FASE 5: Descomentar
    fetch('<?php echo BASE_URL; ?>api/perfil/ver.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarPerfil(data.data);
                document.getElementById('loadingState').style.display = 'none';
                document.getElementById('mainContent').style.display = 'block';
            }
        })
        .catch(error => console.error('Error:', error));
    */
    
    setTimeout(() => {
        document.getElementById('loadingState').style.display = 'none';
        document.getElementById('mainContent').style.display = 'block';
        mostrarMensajeDesarrollo();
    }, 1500);
}

function mostrarPerfil(usuario) {
    // Header del perfil
    document.getElementById('perfilNombre').textContent = usuario.nombre;
    document.getElementById('perfilEmail').textContent = usuario.email;
    
    const badgeRol = document.getElementById('perfilRol');
    badgeRol.textContent = usuario.rol.charAt(0).toUpperCase() + usuario.rol.slice(1);
    badgeRol.className = 'badge ' + (badgeRoles[usuario.rol] || 'bg-secondary');
    
    // Info de la cuenta
    document.getElementById('perfilSucursal').textContent = usuario.sucursal_nombre || 'Todas las sucursales';
    document.getElementById('perfilFechaCreacion').textContent = formatearFecha(usuario.fecha_creacion);
    document.getElementById('perfilUltimoAcceso').textContent = formatearFechaHora(usuario.ultimo_acceso);
    
    // Formulario
    document.getElementById('nombre').value = usuario.nombre || '';
    document.getElementById('email').value = usuario.email || '';
    document.getElementById('telefono').value = usuario.telefono || '';
    document.getElementById('fecha_nacimiento').value = usuario.fecha_nacimiento || '';
    document.getElementById('direccion').value = usuario.direccion || '';
}

function guardarPerfil() {
    const form = document.getElementById('formPerfil');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);
    const datos = Object.fromEntries(formData);
    
    const btnGuardar = document.getElementById('btnGuardarPerfil');
    btnGuardar.disabled = true;
    btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
    
    /* TODO FASE 5: Descomentar
    fetch('<?php echo BASE_URL; ?>api/perfil/actualizar.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(datos)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Perfil actualizado exitosamente');
            cargarPerfil();
        } else {
            alert(data.message);
        }
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = '<i class="bi bi-save"></i> Guardar Cambios';
    });
    */
    
    setTimeout(() => {
        alert('MODO DESARROLLO: Perfil actualizado\n\n' + JSON.stringify(datos, null, 2));
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = '<i class="bi bi-save"></i> Guardar Cambios';
    }, 1000);
}

function cambiarPassword() {
    const form = document.getElementById('formPassword');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const passwordNueva = document.getElementById('password_nueva').value;
    const passwordConfirmar = document.getElementById('password_confirmar').value;
    
    if (passwordNueva !== passwordConfirmar) {
        alert('Las contraseñas no coinciden');
        return;
    }
    
    const formData = new FormData(form);
    const datos = Object.fromEntries(formData);
    
    const btnCambiar = document.getElementById('btnCambiarPassword');
    btnCambiar.disabled = true;
    btnCambiar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Cambiando...';
    
    /* TODO FASE 5: Descomentar
    fetch('<?php echo BASE_URL; ?>api/perfil/cambiar-password.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(datos)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Contraseña cambiada exitosamente');
            form.reset();
        } else {
            alert(data.message);
        }
        btnCambiar.disabled = false;
        btnCambiar.innerHTML = '<i class="bi bi-key"></i> Cambiar Contraseña';
    });
    */
    
    setTimeout(() => {
        alert('MODO DESARROLLO: Contraseña cambiada exitosamente');
        form.reset();
        btnCambiar.disabled = false;
        btnCambiar.innerHTML = '<i class="bi bi-key"></i> Cambiar Contraseña';
    }, 1000);
}

function mostrarMensajeDesarrollo() {
    document.getElementById('perfilNombre').textContent = 'MODO DESARROLLO';
    document.getElementById('perfilEmail').textContent = 'Esperando API de perfil';
    document.getElementById('perfilRol').textContent = 'Usuario';
    document.getElementById('perfilSucursal').textContent = '-';
    document.getElementById('perfilFechaCreacion').textContent = '-';
    document.getElementById('perfilUltimoAcceso').textContent = '-';
}

function formatearFecha(fecha) {
    if (!fecha) return '-';
    const d = new Date(fecha);
    return d.toLocaleDateString('es-GT', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function formatearFechaHora(fecha) {
    if (!fecha) return '-';
    const d = new Date(fecha);
    return d.toLocaleDateString('es-GT', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}
</script>

<?php include '../../includes/footer.php'; ?>
