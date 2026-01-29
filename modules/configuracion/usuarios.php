<?php
/**
 * ================================================
 * MÓDULO CONFIGURACIÓN - GESTIÓN DE USUARIOS
 * ================================================
 * 
 * TODO FASE 5: Conectar con API
 * GET /api/configuracion/usuarios.php - Listar usuarios
 * POST /api/configuracion/usuarios.php - Crear usuario
 * PUT /api/configuracion/usuarios.php - Actualizar usuario
 * POST /api/configuracion/usuarios/reset-password.php - Reset contraseña
 * 
 * Tabla BD: usuarios
 * Campos: id, nombre, email, password, rol, sucursal_id, foto_perfil, activo, 
 *         fecha_creacion, ultimo_acceso
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();
requiere_rol(['administrador', 'dueño']);

$titulo_pagina = 'Gestión de Usuarios';
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="container-fluid main-content">
    <div class="page-header mb-4">
        <div class="row align-items-center g-3">
            <div class="col-md-6">
                <h1 class="mb-2"><i class="bi bi-people"></i> Gestión de Usuarios</h1>
                <p class="text-muted mb-0">Administración de usuarios del sistema</p>
            </div>
            <div class="col-md-6 text-md-end">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalUsuario" onclick="prepararNuevoUsuario()">
                    <i class="bi bi-person-plus"></i> Nuevo Usuario
                </button>
            </div>
        </div>
    </div>

    <div id="loadingState" class="text-center py-5">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
        <p class="mt-3 text-muted">Cargando usuarios...</p>
    </div>

    <div id="mainContent" style="display: none;">
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stat-card azul">
                    <div class="stat-icon"><i class="bi bi-people"></i></div>
                    <div class="stat-value" id="statTotal">0</div>
                    <div class="stat-label">Total Usuarios</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card verde">
                    <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
                    <div class="stat-value" id="statActivos">0</div>
                    <div class="stat-label">Activos</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card rojo">
                    <div class="stat-icon"><i class="bi bi-x-circle"></i></div>
                    <div class="stat-value" id="statInactivos">0</div>
                    <div class="stat-label">Inactivos</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card dorado">
                    <div class="stat-icon"><i class="bi bi-shield-check"></i></div>
                    <div class="stat-value" id="statAdmins">0</div>
                    <div class="stat-label">Administradores</div>
                </div>
            </div>
        </div>

        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Buscar</label>
                        <input type="text" class="form-control" id="searchInput" placeholder="Nombre o email...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Rol</label>
                        <select class="form-select" id="filterRol">
                            <option value="">Todos</option>
                            <option value="administrador">Administrador</option>
                            <option value="dueño">Dueño</option>
                            <option value="vendedor">Vendedor</option>
                            <option value="cajero">Cajero</option>
                            <option value="orfebre">Orfebre</option>
                            <option value="publicidad">Publicidad</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Estado</label>
                        <select class="form-select" id="filterEstado">
                            <option value="">Todos</option>
                            <option value="1">Activos</option>
                            <option value="0">Inactivos</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label d-none d-md-block">&nbsp;</label>
                        <button class="btn btn-secondary w-100" onclick="limpiarFiltros()">
                            <i class="bi bi-x-circle"></i> Limpiar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header">
                <i class="bi bi-table"></i> Listado de Usuarios (<span id="contadorUsuarios">0</span>)
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead style="background-color: #f3f4f6;">
                            <tr>
                                <th>#</th>
                                <th>Usuario</th>
                                <th class="d-none d-md-table-cell">Email</th>
                                <th>Rol</th>
                                <th class="d-none d-lg-table-cell">Sucursal</th>
                                <th class="d-none d-lg-table-cell">Último Acceso</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="usuariosBody">
                            <tr><td colspan="8" class="text-center py-4"><div class="spinner-border spinner-border-sm"></div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalUsuario" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #1e3a8a; color: white;">
                <h5 class="modal-title" id="tituloModal">
                    <i class="bi bi-person-plus"></i> Nuevo Usuario
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formUsuario">
                    <input type="hidden" name="id" id="usuarioId">
                    <div class="mb-3">
                        <label class="form-label">Nombre Completo *</label>
                        <input type="text" class="form-control" name="nombre" id="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control" name="email" id="emailUsuario" required>
                    </div>
                    <div class="mb-3" id="passwordGroup">
                        <label class="form-label">Contraseña *</label>
                        <input type="password" class="form-control" name="password" id="password">
                        <small class="text-muted">Mínimo 8 caracteres</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rol *</label>
                        <select class="form-select" name="rol" id="rol" required>
                            <option value="">Seleccione...</option>
                            <option value="administrador">Administrador</option>
                            <option value="dueño">Dueño</option>
                            <option value="vendedor">Vendedor</option>
                            <option value="cajero">Cajero</option>
                            <option value="orfebre">Orfebre</option>
                            <option value="publicidad">Publicidad</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sucursal</label>
                        <select class="form-select" name="sucursal_id" id="sucursal_id">
                            <option value="">Sin sucursal específica</option>
                        </select>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="activo" id="activo" checked>
                        <label class="form-check-label">Usuario activo</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardar" onclick="guardarUsuario()">
                    <i class="bi bi-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.main-content { padding: 20px; min-height: calc(100vh - 120px); }
.page-header h1 { font-size: 1.75rem; font-weight: 600; color: #1a1a1a; }
.shadow-sm { box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08) !important; }
.stat-card { background: linear-gradient(135deg, var(--card-color-start) 0%, var(--card-color-end) 100%); border-radius: 12px; padding: 20px; color: white; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15); transition: transform 0.2s ease; height: 100%; }
.stat-card:hover { transform: translateY(-3px); box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2); }
.stat-card.azul { --card-color-start: #1e3a8a; --card-color-end: #1e40af; }
.stat-card.verde { --card-color-start: #22c55e; --card-color-end: #16a34a; }
.stat-card.rojo { --card-color-start: #ef4444; --card-color-end: #dc2626; }
.stat-card.dorado { --card-color-start: #d4af37; --card-color-end: #b8941f; }
.stat-icon { width: 50px; height: 50px; border-radius: 10px; background: rgba(255, 255, 255, 0.2); display: flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 15px; color: white; }
.stat-value { font-size: 1.75rem; font-weight: 700; margin: 10px 0; color: white !important; }
.stat-label { font-size: 0.85rem; opacity: 0.95; font-weight: 500; color: white !important; }
table thead th { font-weight: 600; font-size: 0.85rem; text-transform: uppercase; padding: 12px; }
table tbody td { padding: 12px; vertical-align: middle; }
.user-avatar { width: 35px; height: 35px; border-radius: 50%; background: #1e3a8a; color: white; display: flex; align-items: center; justify-content: center; margin-right: 10px; }
@media (max-width: 575.98px) {
    .main-content { padding: 15px 10px; }
    .page-header h1 { font-size: 1.5rem; }
    .stat-card { padding: 15px; }
    .stat-value { font-size: 1.5rem; }
    table { font-size: 0.85rem; }
    table thead th, table tbody td { padding: 8px 6px; }
}
@media (min-width: 576px) and (max-width: 767.98px) { .main-content { padding: 18px 15px; } }
@media (min-width: 992px) { .main-content { padding: 25px 30px; } }
@media (max-width: 767.98px) { .btn, .form-control, .form-select { min-height: 44px; } }
</style>

<script>
let usuariosData = [];
const badgeRoles = {
    'administrador': 'bg-danger',
    'dueño': 'bg-warning text-dark',
    'vendedor': 'bg-primary',
    'cajero': 'bg-info',
    'orfebre': 'bg-success',
    'publicidad': 'bg-secondary'
};

document.addEventListener('DOMContentLoaded', function() {
    cargarUsuarios();
    cargarSucursales();
    
    document.getElementById('searchInput').addEventListener('input', aplicarFiltros);
    document.getElementById('filterRol').addEventListener('change', aplicarFiltros);
    document.getElementById('filterEstado').addEventListener('change', aplicarFiltros);
});

function cargarUsuarios() {
    /* TODO FASE 5: Descomentar
    fetch('<?php echo BASE_URL; ?>api/configuracion/usuarios.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                usuariosData = data.data;
                renderizarUsuarios();
                actualizarEstadisticas();
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

function cargarSucursales() {
    /* TODO FASE 5: Descomentar
    fetch('<?php echo BASE_URL; ?>api/configuracion/sucursales.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('sucursal_id');
                data.data.forEach(s => {
                    const option = document.createElement('option');
                    option.value = s.id;
                    option.textContent = s.nombre;
                    select.appendChild(option);
                });
            }
        });
    */
}

function renderizarUsuarios() {
    const tbody = document.getElementById('usuariosBody');
    const usuarios = filtrarUsuarios();
    
    if (usuarios.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted">Sin usuarios que mostrar</td></tr>';
        document.getElementById('contadorUsuarios').textContent = '0';
        return;
    }
    
    let html = '';
    usuarios.forEach(u => {
        const badgeRol = badgeRoles[u.rol] || 'bg-secondary';
        const badgeEstado = u.activo ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>';
        const iniciales = u.nombre.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
        
        html += `
            <tr>
                <td class="fw-bold">${u.id}</td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="user-avatar">${iniciales}</div>
                        <strong>${u.nombre}</strong>
                    </div>
                </td>
                <td class="d-none d-md-table-cell">${u.email}</td>
                <td><span class="badge ${badgeRol}">${u.rol.charAt(0).toUpperCase() + u.rol.slice(1)}</span></td>
                <td class="d-none d-lg-table-cell"><small class="text-muted">${u.sucursal_nombre || 'Todas'}</small></td>
                <td class="d-none d-lg-table-cell"><small class="text-muted">${formatearFechaHora(u.ultimo_acceso)}</small></td>
                <td>${badgeEstado}</td>
                <td class="text-center">
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-info" onclick="verUsuario(${u.id})" title="Ver"><i class="bi bi-eye"></i></button>
                        <button class="btn btn-warning" onclick="editarUsuario(${u.id})" title="Editar"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-secondary" onclick="resetPassword(${u.id})" title="Reset contraseña"><i class="bi bi-key"></i></button>
                        ${u.id !== <?php echo $_SESSION['usuario_id'] ?? 0; ?> ? `<button class="btn btn-danger" onclick="desactivarUsuario(${u.id}, '${u.nombre}')" title="Desactivar"><i class="bi bi-trash"></i></button>` : ''}
                    </div>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    document.getElementById('contadorUsuarios').textContent = usuarios.length;
}

function actualizarEstadisticas() {
    document.getElementById('statTotal').textContent = usuariosData.length;
    document.getElementById('statActivos').textContent = usuariosData.filter(u => u.activo).length;
    document.getElementById('statInactivos').textContent = usuariosData.filter(u => !u.activo).length;
    document.getElementById('statAdmins').textContent = usuariosData.filter(u => ['administrador', 'dueño'].includes(u.rol)).length;
}

function filtrarUsuarios() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const rol = document.getElementById('filterRol').value;
    const estado = document.getElementById('filterEstado').value;
    
    return usuariosData.filter(u => {
        const matchSearch = !search || u.nombre.toLowerCase().includes(search) || u.email.toLowerCase().includes(search);
        const matchRol = !rol || u.rol === rol;
        const matchEstado = estado === '' || u.activo == estado;
        return matchSearch && matchRol && matchEstado;
    });
}

function aplicarFiltros() { renderizarUsuarios(); }

function limpiarFiltros() {
    document.getElementById('searchInput').value = '';
    document.getElementById('filterRol').value = '';
    document.getElementById('filterEstado').value = '';
    renderizarUsuarios();
}

function prepararNuevoUsuario() {
    document.getElementById('tituloModal').innerHTML = '<i class="bi bi-person-plus"></i> Nuevo Usuario';
    document.getElementById('formUsuario').reset();
    document.getElementById('usuarioId').value = '';
    document.getElementById('password').required = true;
    document.getElementById('passwordGroup').style.display = 'block';
    document.getElementById('activo').checked = true;
}

function editarUsuario(id) {
    const usuario = usuariosData.find(u => u.id === id);
    if (!usuario) return;
    
    document.getElementById('tituloModal').innerHTML = '<i class="bi bi-pencil"></i> Editar Usuario';
    document.getElementById('usuarioId').value = usuario.id;
    document.getElementById('nombre').value = usuario.nombre;
    document.getElementById('emailUsuario').value = usuario.email;
    document.getElementById('rol').value = usuario.rol;
    document.getElementById('sucursal_id').value = usuario.sucursal_id || '';
    document.getElementById('activo').checked = usuario.activo;
    document.getElementById('password').required = false;
    document.getElementById('password').value = '';
    document.getElementById('passwordGroup').querySelector('small').textContent = 'Dejar en blanco para mantener contraseña actual';
    
    new bootstrap.Modal(document.getElementById('modalUsuario')).show();
}

function guardarUsuario() {
    const form = document.getElementById('formUsuario');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);
    const datos = Object.fromEntries(formData);
    datos.activo = document.getElementById('activo').checked ? 1 : 0;
    
    const btnGuardar = document.getElementById('btnGuardar');
    btnGuardar.disabled = true;
    btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
    
    const esNuevo = !datos.id;
    
    /* TODO FASE 5: Descomentar
    const url = '<?php echo BASE_URL; ?>api/configuracion/usuarios.php';
    const method = esNuevo ? 'POST' : 'PUT';
    
    fetch(url, {
        method: method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(datos)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(esNuevo ? 'Usuario creado exitosamente' : 'Usuario actualizado exitosamente');
            bootstrap.Modal.getInstance(document.getElementById('modalUsuario')).hide();
            cargarUsuarios();
        } else {
            alert(data.message);
        }
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = '<i class="bi bi-save"></i> Guardar';
    });
    */
    
    setTimeout(() => {
        alert('MODO DESARROLLO: Usuario ' + (esNuevo ? 'creado' : 'actualizado') + '\n\n' + JSON.stringify(datos, null, 2));
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = '<i class="bi bi-save"></i> Guardar';
        bootstrap.Modal.getInstance(document.getElementById('modalUsuario')).hide();
    }, 1000);
}

function verUsuario(id) { alert('MODO DESARROLLO: Ver usuario #' + id); }

function resetPassword(id) {
    if (confirm('¿Está seguro de resetear la contraseña de este usuario?\n\nSe enviará una nueva contraseña al email del usuario.')) {
        alert('MODO DESARROLLO: Reset contraseña usuario #' + id);
    }
}

function desactivarUsuario(id, nombre) {
    if (confirm('¿Está seguro de desactivar al usuario "' + nombre + '"?')) {
        alert('MODO DESARROLLO: Desactivar usuario #' + id);
    }
}

function mostrarMensajeDesarrollo() {
    document.getElementById('usuariosBody').innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted">MODO DESARROLLO: Esperando API de usuarios</td></tr>';
}

function formatearFechaHora(fecha) {
    if (!fecha) return '-';
    const d = new Date(fecha);
    return d.toLocaleDateString('es-GT', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}
</script>

<?php include '../../includes/footer.php'; ?>