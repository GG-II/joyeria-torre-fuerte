<?php
/**
 * ================================================
 * MÓDULO CONFIGURACIÓN - ROLES Y PERMISOS
 * ================================================
 * 
 * TODO FASE 5: Conectar con API
 * GET /api/configuracion/permisos.php - Cargar roles y permisos actuales
 * POST /api/configuracion/permisos.php - Guardar cambios
 * 
 * Estructura BD:
 * - tabla roles: id, nombre, descripcion
 * - tabla permisos: id, modulo, accion
 * - tabla roles_permisos: rol_id, permiso_id
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();
requiere_rol(['administrador', 'dueño']);

$titulo_pagina = 'Roles y Permisos';
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="container-fluid main-content">
    <div class="page-header mb-4">
        <h1 class="mb-2"><i class="bi bi-shield-check"></i> Roles y Permisos</h1>
        <p class="text-muted mb-0">Configuración de permisos por rol</p>
    </div>

    <div id="loadingState" class="text-center py-5">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
        <p class="mt-3 text-muted">Cargando configuración de permisos...</p>
    </div>

    <div id="mainContent" style="display: none;">
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <div class="row align-items-center g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Seleccione un Rol para Configurar:</label>
                        <select class="form-select form-select-lg" id="rolSelect" onchange="cargarPermisos()">
                            <option value="">Cargando roles...</option>
                        </select>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <button class="btn btn-primary btn-lg" onclick="guardarPermisos()" id="btnGuardar">
                            <i class="bi bi-save"></i> <span class="d-none d-sm-inline">Guardar Cambios</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header" style="background-color: #1e3a8a; color: white;">
                <i class="bi bi-grid"></i> Matriz de Permisos para Rol: <span id="rolNombre" class="fw-bold">-</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background-color: #f3f4f6;">
                        <tr>
                            <th style="width: 200px;">Módulo</th>
                            <th class="text-center">Ver</th>
                            <th class="text-center">Crear</th>
                            <th class="text-center">Editar</th>
                            <th class="text-center">Eliminar</th>
                            <th class="text-center">Exportar</th>
                        </tr>
                    </thead>
                    <tbody id="tablaPermisos">
                        <tr><td colspan="6" class="text-center py-4"><div class="spinner-border spinner-border-sm"></div></td></tr>
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <div class="row g-3 align-items-center">
                    <div class="col-md-6">
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i>
                            Los cambios se aplicarán inmediatamente para todos los usuarios con este rol
                        </small>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-success" onclick="seleccionarTodos()">
                                <i class="bi bi-check-all"></i> <span class="d-none d-sm-inline">Todos</span>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deseleccionarTodos()">
                                <i class="bi bi-x"></i> <span class="d-none d-sm-inline">Ninguno</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mt-3">
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header" style="background-color: #1e3a8a; color: white;">
                        <i class="bi bi-info-circle"></i> Descripción de Roles
                    </div>
                    <div class="card-body">
                        <ul class="mb-0">
                            <li><strong>Administrador:</strong> Acceso total al sistema</li>
                            <li><strong>Dueño:</strong> Acceso a reportes financieros y configuración</li>
                            <li><strong>Vendedor:</strong> Gestión de ventas y clientes</li>
                            <li><strong>Cajero:</strong> Manejo de caja y pagos</li>
                            <li><strong>Orfebre:</strong> Gestión de trabajos del taller</li>
                            <li><strong>Publicidad:</strong> Acceso a clientes para marketing</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card border-warning shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <i class="bi bi-exclamation-triangle"></i> Advertencias
                    </div>
                    <div class="card-body">
                        <ul class="mb-0">
                            <li>Los cambios en permisos son permanentes</li>
                            <li>Los usuarios en sesión deben cerrar y volver a iniciar sesión</li>
                            <li>No se puede quitar acceso total al rol Administrador</li>
                            <li>Se recomienda probar en un usuario de prueba antes</li>
                        </ul>
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
table thead th { font-weight: 600; font-size: 0.85rem; text-transform: uppercase; padding: 12px; }
table tbody td { padding: 12px; vertical-align: middle; }
.form-check-input { width: 1.3em; height: 1.3em; cursor: pointer; }
.form-check-input:checked { background-color: #1e3a8a; border-color: #1e3a8a; }
@media (max-width: 575.98px) {
    .main-content { padding: 15px 10px; }
    .page-header h1 { font-size: 1.5rem; }
    table { font-size: 0.85rem; }
    table thead th, table tbody td { padding: 8px 6px; }
}
@media (min-width: 576px) and (max-width: 767.98px) { .main-content { padding: 18px 15px; } }
@media (min-width: 992px) { .main-content { padding: 25px 30px; } }
@media (max-width: 767.98px) { .btn, .form-select { min-height: 44px; } .form-check-input { width: 1.5em; height: 1.5em; } }
</style>

<script>
let rolesData = [];
let modulosData = [];
let permisosActuales = {};

document.addEventListener('DOMContentLoaded', function() {
    cargarDatos();
});

function cargarDatos() {
    /* TODO FASE 5: Descomentar
    fetch('<?php echo BASE_URL; ?>api/configuracion/permisos.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                rolesData = data.data.roles;
                modulosData = data.data.modulos;
                permisosActuales = data.data.permisos_actuales;
                
                renderizarRoles();
                if (rolesData.length > 0) {
                    document.getElementById('rolSelect').value = rolesData[0].id;
                    cargarPermisos();
                }
                
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

function renderizarRoles() {
    const select = document.getElementById('rolSelect');
    select.innerHTML = '';
    
    rolesData.forEach(rol => {
        const option = document.createElement('option');
        option.value = rol.id;
        option.textContent = rol.nombre;
        select.appendChild(option);
    });
}

function cargarPermisos() {
    const rol = document.getElementById('rolSelect').value;
    if (!rol) return;
    
    const rolNombre = document.getElementById('rolSelect').options[document.getElementById('rolSelect').selectedIndex].text;
    document.getElementById('rolNombre').textContent = rolNombre;
    
    if (modulosData.length === 0) {
        mostrarMensajeDesarrollo();
        return;
    }
    
    const tbody = document.getElementById('tablaPermisos');
    let html = '';
    
    modulosData.forEach(modulo => {
        html += `<tr>
            <td class="fw-bold"><i class="bi bi-folder"></i> ${modulo.nombre}</td>`;
        
        const permisosPosibles = ['ver', 'crear', 'editar', 'eliminar', 'exportar'];
        permisosPosibles.forEach(permiso => {
            const tienePermiso = modulo.permisos.includes(permiso);
            const checked = permisosActuales[rol] && 
                           permisosActuales[rol][modulo.key] && 
                           permisosActuales[rol][modulo.key].includes(permiso);
            
            html += '<td class="text-center">';
            if (tienePermiso) {
                html += `<input type="checkbox" class="form-check-input permiso-check" 
                               data-modulo="${modulo.key}" 
                               data-permiso="${permiso}" 
                               ${checked ? 'checked' : ''}>`;
            } else {
                html += '<span class="text-muted">-</span>';
            }
            html += '</td>';
        });
        
        html += '</tr>';
    });
    
    tbody.innerHTML = html;
}

function guardarPermisos() {
    const rol = document.getElementById('rolSelect').value;
    const permisos = {};
    
    document.querySelectorAll('.permiso-check:checked').forEach(checkbox => {
        const modulo = checkbox.dataset.modulo;
        const permiso = checkbox.dataset.permiso;
        
        if (!permisos[modulo]) permisos[modulo] = [];
        permisos[modulo].push(permiso);
    });
    
    const btnGuardar = document.getElementById('btnGuardar');
    btnGuardar.disabled = true;
    btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
    
    /* TODO FASE 5: Descomentar
    fetch('<?php echo BASE_URL; ?>api/configuracion/permisos.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ rol: rol, permisos: permisos })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const rolNombre = document.getElementById('rolSelect').options[document.getElementById('rolSelect').selectedIndex].text;
            alert('Permisos actualizados exitosamente para el rol: ' + rolNombre);
            permisosActuales[rol] = permisos;
        } else {
            alert(data.message);
        }
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = '<i class="bi bi-save"></i> <span class="d-none d-sm-inline">Guardar Cambios</span>';
    });
    */
    
    setTimeout(() => {
        const rolNombre = document.getElementById('rolSelect').options[document.getElementById('rolSelect').selectedIndex].text;
        alert('MODO DESARROLLO: Permisos guardados para ' + rolNombre + '\n\n' + JSON.stringify(permisos, null, 2));
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = '<i class="bi bi-save"></i> <span class="d-none d-sm-inline">Guardar Cambios</span>';
    }, 1000);
}

function seleccionarTodos() {
    document.querySelectorAll('.permiso-check').forEach(cb => cb.checked = true);
}

function deseleccionarTodos() {
    document.querySelectorAll('.permiso-check').forEach(cb => cb.checked = false);
}

function mostrarMensajeDesarrollo() {
    document.getElementById('rolSelect').innerHTML = '<option value="">MODO DESARROLLO: Esperando API</option>';
    document.getElementById('tablaPermisos').innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">MODO DESARROLLO: Esperando API de permisos</td></tr>';
}
</script>

<?php include '../../includes/footer.php'; ?>