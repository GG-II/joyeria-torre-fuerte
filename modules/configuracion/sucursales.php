<?php
/**
 * ================================================
 * MÓDULO CONFIGURACIÓN - GESTIÓN DE SUCURSALES
 * ================================================
 * 
 * TODO FASE 5: Conectar con API
 * GET /api/configuracion/sucursales.php - Listar sucursales
 * POST /api/configuracion/sucursales.php - Crear sucursal
 * PUT /api/configuracion/sucursales.php - Actualizar sucursal
 * 
 * Tabla BD: sucursales
 * Campos: id, nombre, direccion, telefono, email, responsable_id, activo, fecha_creacion
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();
requiere_rol(['administrador', 'dueño']);

$titulo_pagina = 'Gestión de Sucursales';
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="container-fluid main-content">
    <div class="page-header mb-4">
        <div class="row align-items-center g-3">
            <div class="col-md-6">
                <h1 class="mb-2"><i class="bi bi-building"></i> Gestión de Sucursales</h1>
                <p class="text-muted mb-0">Administración de sucursales</p>
            </div>
            <div class="col-md-6 text-md-end">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalSucursal" onclick="prepararNuevaSucursal()">
                    <i class="bi bi-plus-circle"></i> Nueva Sucursal
                </button>
            </div>
        </div>
    </div>

    <div id="loadingState" class="text-center py-5">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
        <p class="mt-3 text-muted">Cargando sucursales...</p>
    </div>

    <div id="mainContent" style="display: none;">
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="stat-card azul">
                    <div class="stat-icon"><i class="bi bi-building"></i></div>
                    <div class="stat-value" id="statTotal">0</div>
                    <div class="stat-label">Total Sucursales</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card verde">
                    <div class="stat-icon"><i class="bi bi-people"></i></div>
                    <div class="stat-value" id="statUsuarios">0</div>
                    <div class="stat-label">Total Usuarios</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card dorado">
                    <div class="stat-icon"><i class="bi bi-box-seam"></i></div>
                    <div class="stat-value" id="statProductos">0</div>
                    <div class="stat-label">Productos en Inventario</div>
                </div>
            </div>
        </div>

        <div class="row g-3" id="sucursalesContainer">
            <div class="col-12 text-center py-5 text-muted">
                <i class="bi bi-building" style="font-size: 48px;"></i>
                <p class="mt-2">Sin sucursales registradas</p>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalSucursal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #1e3a8a; color: white;">
                <h5 class="modal-title" id="tituloModal">
                    <i class="bi bi-building"></i> Nueva Sucursal
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formSucursal">
                    <input type="hidden" name="id" id="sucursalId">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre de la Sucursal *</label>
                            <input type="text" class="form-control" name="nombre" id="nombre" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Responsable</label>
                            <select class="form-select" name="responsable_id" id="responsable_id">
                                <option value="">Sin responsable</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3 mt-3">
                        <label class="form-label">Dirección *</label>
                        <textarea class="form-control" name="direccion" id="direccion" rows="2" required></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" name="telefono" id="telefono">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="emailSucursal">
                        </div>
                    </div>
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" name="activo" id="activo" checked>
                        <label class="form-check-label">Sucursal activa</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardar" onclick="guardarSucursal()">
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
.stat-card.dorado { --card-color-start: #d4af37; --card-color-end: #b8941f; }
.stat-icon { width: 50px; height: 50px; border-radius: 10px; background: rgba(255, 255, 255, 0.2); display: flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 15px; color: white; }
.stat-value { font-size: 1.75rem; font-weight: 700; margin: 10px 0; color: white !important; }
.stat-label { font-size: 0.85rem; opacity: 0.95; font-weight: 500; color: white !important; }
.sucursal-card { border-left: 4px solid #1e3a8a; transition: transform 0.2s ease; }
.sucursal-card:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12); }
.card-body { padding: 25px; }
@media (max-width: 575.98px) {
    .main-content { padding: 15px 10px; }
    .page-header h1 { font-size: 1.5rem; }
    .stat-card { padding: 15px; }
    .stat-value { font-size: 1.5rem; }
    .card-body { padding: 15px; }
}
@media (min-width: 576px) and (max-width: 767.98px) { .main-content { padding: 18px 15px; } }
@media (min-width: 992px) { .main-content { padding: 25px 30px; } }
@media (max-width: 767.98px) { .btn, .form-control, .form-select { min-height: 44px; } }
</style>

<script>
let sucursalesData = [];

document.addEventListener('DOMContentLoaded', function() {
    cargarSucursales();
    cargarEmpleados();
});

function cargarSucursales() {
    /* TODO FASE 5: Descomentar
    fetch('<?php echo BASE_URL; ?>api/configuracion/sucursales.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                sucursalesData = data.data;
                renderizarSucursales();
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

function cargarEmpleados() {
    /* TODO FASE 5: Descomentar
    fetch('<?php echo BASE_URL; ?>api/empleados/lista.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('responsable_id');
                data.data.forEach(e => {
                    const option = document.createElement('option');
                    option.value = e.id;
                    option.textContent = e.nombre + ' - ' + e.cargo;
                    select.appendChild(option);
                });
            }
        });
    */
}

function renderizarSucursales() {
    const container = document.getElementById('sucursalesContainer');
    
    if (sucursalesData.length === 0) {
        container.innerHTML = '<div class="col-12 text-center py-5 text-muted"><i class="bi bi-building" style="font-size: 48px;"></i><p class="mt-2">Sin sucursales registradas</p></div>';
        return;
    }
    
    let html = '';
    sucursalesData.forEach(s => {
        const badgeEstado = s.activo ? '<span class="badge bg-success">Activa</span>' : '<span class="badge bg-secondary">Inactiva</span>';
        html += `
            <div class="col-lg-6">
                <div class="card shadow-sm sucursal-card">
                    <div class="card-header" style="background-color: #1e3a8a; color: white;">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-building"></i> ${s.nombre}</h5>
                            ${badgeEstado}
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted d-block"><i class="bi bi-geo-alt"></i> Dirección:</small>
                            <strong>${s.direccion}</strong>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <small class="text-muted d-block"><i class="bi bi-phone"></i> Teléfono:</small>
                                <strong>${s.telefono || 'No especificado'}</strong>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block"><i class="bi bi-envelope"></i> Email:</small>
                                <strong>${s.email || 'No especificado'}</strong>
                            </div>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block"><i class="bi bi-person-badge"></i> Responsable:</small>
                            <strong>${s.responsable_nombre || 'Sin responsable'}</strong>
                        </div>
                        <hr>
                        <div class="row text-center">
                            <div class="col-6">
                                <h4 class="text-primary mb-0">${s.total_usuarios || 0}</h4>
                                <small class="text-muted">Usuarios</small>
                            </div>
                            <div class="col-6">
                                <h4 class="text-success mb-0">${s.total_inventario || 0}</h4>
                                <small class="text-muted">Productos</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="btn-group w-100" role="group">
                            <button class="btn btn-warning btn-sm" onclick="editarSucursal(${s.id})">
                                <i class="bi bi-pencil"></i> <span class="d-none d-md-inline">Editar</span>
                            </button>
                            <button class="btn btn-info btn-sm" onclick="verDetalles(${s.id})">
                                <i class="bi bi-eye"></i> <span class="d-none d-md-inline">Detalles</span>
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="desactivarSucursal(${s.id}, '${s.nombre}')">
                                <i class="bi bi-x-circle"></i> <span class="d-none d-md-inline">Desactivar</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

function actualizarEstadisticas() {
    document.getElementById('statTotal').textContent = sucursalesData.length;
    document.getElementById('statUsuarios').textContent = sucursalesData.reduce((sum, s) => sum + (s.total_usuarios || 0), 0);
    document.getElementById('statProductos').textContent = sucursalesData.reduce((sum, s) => sum + (s.total_inventario || 0), 0);
}

function prepararNuevaSucursal() {
    document.getElementById('tituloModal').innerHTML = '<i class="bi bi-building"></i> Nueva Sucursal';
    document.getElementById('formSucursal').reset();
    document.getElementById('sucursalId').value = '';
    document.getElementById('activo').checked = true;
}

function editarSucursal(id) {
    const sucursal = sucursalesData.find(s => s.id === id);
    if (!sucursal) return;
    
    document.getElementById('tituloModal').innerHTML = '<i class="bi bi-pencil"></i> Editar Sucursal';
    document.getElementById('sucursalId').value = sucursal.id;
    document.getElementById('nombre').value = sucursal.nombre;
    document.getElementById('direccion').value = sucursal.direccion;
    document.getElementById('telefono').value = sucursal.telefono || '';
    document.getElementById('emailSucursal').value = sucursal.email || '';
    document.getElementById('responsable_id').value = sucursal.responsable_id || '';
    document.getElementById('activo').checked = sucursal.activo;
    
    new bootstrap.Modal(document.getElementById('modalSucursal')).show();
}

function guardarSucursal() {
    const form = document.getElementById('formSucursal');
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
    const url = '<?php echo BASE_URL; ?>api/configuracion/sucursales.php';
    const method = esNuevo ? 'POST' : 'PUT';
    
    fetch(url, {
        method: method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(datos)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(esNuevo ? 'Sucursal creada exitosamente' : 'Sucursal actualizada exitosamente');
            bootstrap.Modal.getInstance(document.getElementById('modalSucursal')).hide();
            cargarSucursales();
        } else {
            alert(data.message);
        }
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = '<i class="bi bi-save"></i> Guardar';
    });
    */
    
    setTimeout(() => {
        alert('MODO DESARROLLO: Sucursal ' + (esNuevo ? 'creada' : 'actualizada') + '\n\n' + JSON.stringify(datos, null, 2));
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = '<i class="bi bi-save"></i> Guardar';
        bootstrap.Modal.getInstance(document.getElementById('modalSucursal')).hide();
    }, 1000);
}

function verDetalles(id) { alert('MODO DESARROLLO: Ver detalles de sucursal #' + id); }

function desactivarSucursal(id, nombre) {
    if (confirm('¿Está seguro de desactivar la sucursal "' + nombre + '"?')) {
        alert('MODO DESARROLLO: Desactivar sucursal #' + id);
    }
}

function mostrarMensajeDesarrollo() {
    document.getElementById('sucursalesContainer').innerHTML = '<div class="col-12 text-center py-5 text-muted"><i class="bi bi-building" style="font-size: 48px;"></i><p class="mt-2">MODO DESARROLLO: Esperando API de sucursales</p></div>';
}
</script>

<?php include '../../includes/footer.php'; ?>