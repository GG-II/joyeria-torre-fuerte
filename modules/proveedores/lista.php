<?php
/**
 * ================================================
 * MÓDULO PROVEEDORES - LISTA
 * ================================================
 * 
 * TODO FASE 5: Conectar con API
 * GET /api/proveedores/lista.php
 * 
 * Parámetros: buscar, estado, productos
 * Respuesta: { success, data: [...proveedores], resumen: {...stats} }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();

$titulo_pagina = 'Proveedores';
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="container-fluid main-content">
    <div class="page-header mb-4">
        <div class="row align-items-center g-3">
            <div class="col-md-6">
                <h1 class="mb-2"><i class="bi bi-truck"></i> Proveedores</h1>
                <p class="text-muted mb-0">Gestión de proveedores y suministros</p>
            </div>
            <div class="col-md-6 text-md-end">
                <?php if (tiene_permiso('proveedores', 'crear')): ?>
                <a href="agregar.php" class="btn btn-primary btn-lg">
                    <i class="bi bi-plus-circle"></i> <span class="d-none d-sm-inline">Nuevo Proveedor</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4" id="estadisticas">
        <div class="col-6 col-md-3">
            <div class="stat-card azul">
                <div class="stat-icon"><i class="bi bi-building"></i></div>
                <div class="stat-value" id="statTotal">0</div>
                <div class="stat-label">Total Proveedores</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card verde">
                <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
                <div class="stat-value" id="statActivos">0</div>
                <div class="stat-label">Activos</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card rojo">
                <div class="stat-icon"><i class="bi bi-x-circle"></i></div>
                <div class="stat-value" id="statInactivos">0</div>
                <div class="stat-label">Inactivos</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card dorado">
                <div class="stat-icon"><i class="bi bi-cart"></i></div>
                <div class="stat-value" id="statCompras">0</div>
                <div class="stat-label">Compras Totales</div>
            </div>
        </div>
    </div>

    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Buscar</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" id="searchInput" placeholder="Nombre, empresa...">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Estado</label>
                    <select class="form-select" id="filterEstado">
                        <option value="">Todos</option>
                        <option value="1">Activos</option>
                        <option value="0">Inactivos</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Productos</label>
                    <input type="text" class="form-control" id="filterProductos" placeholder="Ej: oro, plata...">
                </div>
                <div class="col-md-2">
                    <label class="form-label d-none d-md-block">&nbsp;</label>
                    <button class="btn btn-secondary w-100" onclick="limpiarFiltros()">
                        <i class="bi bi-x-circle"></i> <span class="d-md-none">Limpiar</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header" style="background-color: #1e3a8a; color: white;">
            <i class="bi bi-table"></i> <span id="tituloTabla">Listado de Proveedores</span>
        </div>
        
        <div id="loadingTable" class="text-center py-5">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
            <p class="mt-3 text-muted">Cargando proveedores...</p>
        </div>

        <div id="tableContainer" class="table-responsive" style="display: none;">
            <table class="table table-hover mb-0">
                <thead style="background-color: #1e3a8a; color: white;">
                    <tr>
                        <th>ID</th>
                        <th>Nombre / Empresa</th>
                        <th class="d-none d-md-table-cell">Contacto</th>
                        <th class="d-none d-lg-table-cell">Productos Suministra</th>
                        <th class="d-none d-xl-table-cell">Compras</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="proveedoresBody"></tbody>
            </table>
        </div>

        <div id="noResults" class="text-center py-5" style="display: none;">
            <i class="bi bi-inbox" style="font-size: 48px; opacity: 0.3;"></i>
            <p class="mt-3 text-muted">No se encontraron proveedores</p>
        </div>

        <div class="card-footer" id="tableFooter" style="display: none;">
            <small class="text-muted" id="contadorProveedores">Mostrando 0 proveedores</small>
        </div>
    </div>
</div>

<style>
.main-content { padding: 20px; min-height: calc(100vh - 120px); }
.page-header h1 { font-size: 1.75rem; font-weight: 600; color: #1a1a1a; }
.shadow-sm { box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08) !important; }
.stat-card { background: white; border-radius: 12px; padding: 15px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08); transition: transform 0.2s ease; border-left: 4px solid; height: 100%; }
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12); }
.stat-card.azul { border-left-color: #1e3a8a; }
.stat-card.verde { border-left-color: #22c55e; }
.stat-card.rojo { border-left-color: #ef4444; }
.stat-card.dorado { border-left-color: #d4af37; }
.stat-icon { width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 22px; margin-bottom: 12px; }
.stat-card.azul .stat-icon { background: rgba(30, 58, 138, 0.1); color: #1e3a8a; }
.stat-card.verde .stat-icon { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
.stat-card.rojo .stat-icon { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
.stat-card.dorado .stat-icon { background: rgba(212, 175, 55, 0.1); color: #d4af37; }
.stat-value { font-size: 1.5rem; font-weight: 700; color: #1a1a1a; margin: 8px 0; }
.stat-label { font-size: 0.8rem; color: #6b7280; font-weight: 500; }
table thead th { font-weight: 600; font-size: 0.85rem; text-transform: uppercase; padding: 12px; }
table tbody td { padding: 12px; vertical-align: middle; }
.badge { padding: 0.35em 0.65em; font-size: 0.85em; }
@media (max-width: 575.98px) {
    .main-content { padding: 15px 10px; }
    .page-header h1 { font-size: 1.5rem; }
    .stat-card { padding: 12px; }
    .stat-icon { width: 38px; height: 38px; font-size: 18px; margin-bottom: 8px; }
    .stat-value { font-size: 1.25rem; }
    table { font-size: 0.85rem; }
    table thead th, table tbody td { padding: 8px 6px; }
}
@media (min-width: 576px) and (max-width: 767.98px) { .main-content { padding: 18px 15px; } }
@media (min-width: 992px) { .main-content { padding: 25px 30px; } }
@media (max-width: 767.98px) { .btn, .form-control, .form-select { min-height: 44px; } }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    cargarProveedores();
    
    document.getElementById('searchInput').addEventListener('input', aplicarFiltros);
    document.getElementById('filterEstado').addEventListener('change', aplicarFiltros);
    document.getElementById('filterProductos').addEventListener('input', aplicarFiltros);
});

function cargarProveedores() {
    /* TODO FASE 5: Descomentar
    const params = new URLSearchParams({
        buscar: document.getElementById('searchInput').value,
        estado: document.getElementById('filterEstado').value,
        productos: document.getElementById('filterProductos').value
    });
    
    fetch('<?php echo BASE_URL; ?>api/proveedores/lista.php?' + params)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderizarProveedores(data.data);
                actualizarEstadisticas(data.resumen);
            } else {
                mostrarError(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarError('Error al cargar los proveedores');
        });
    */
    
    setTimeout(() => {
        document.getElementById('loadingTable').style.display = 'none';
        document.getElementById('noResults').style.display = 'block';
        document.getElementById('noResults').innerHTML = '<i class="bi bi-database" style="font-size: 48px; opacity: 0.3;"></i><p class="mt-3 text-muted">MODO DESARROLLO: Esperando API</p>';
    }, 1500);
}

function renderizarProveedores(proveedores) {
    const tbody = document.getElementById('proveedoresBody');
    
    if (proveedores.length === 0) {
        document.getElementById('loadingTable').style.display = 'none';
        document.getElementById('noResults').style.display = 'block';
        return;
    }
    
    let html = '';
    proveedores.forEach(p => {
        const badgeEstado = p.activo ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>';
        const productos = p.productos_suministra || '-';
        const productosCorto = productos.length > 50 ? productos.substring(0, 50) + '...' : productos;
        
        html += `
            <tr>
                <td class="fw-bold">${p.id}</td>
                <td>
                    <div class="fw-bold">${p.nombre}</div>
                    ${p.empresa ? `<small class="text-muted">${p.empresa}</small>` : ''}
                </td>
                <td class="d-none d-md-table-cell">
                    ${p.telefono ? `<div><i class="bi bi-phone"></i> ${p.telefono}</div>` : ''}
                    ${p.email ? `<div><i class="bi bi-envelope"></i> <small>${p.email}</small></div>` : ''}
                    ${!p.telefono && !p.email ? '<small class="text-muted">Sin contacto</small>' : ''}
                </td>
                <td class="d-none d-lg-table-cell"><small class="text-muted">${productosCorto}</small></td>
                <td class="d-none d-xl-table-cell"><span class="badge bg-info">${p.total_compras || 0}</span></td>
                <td>${badgeEstado}</td>
                <td class="text-center">
                    <div class="btn-group">
                        <a href="ver.php?id=${p.id}" class="btn btn-sm btn-info" title="Ver"><i class="bi bi-eye"></i></a>
                        <?php if (tiene_permiso('proveedores', 'editar')): ?>
                        <a href="editar.php?id=${p.id}" class="btn btn-sm btn-warning" title="Editar"><i class="bi bi-pencil"></i></a>
                        <?php endif; ?>
                        <?php if (tiene_permiso('proveedores', 'eliminar')): ?>
                        <button class="btn btn-sm btn-danger" onclick="desactivarProveedor(${p.id})" title="Desactivar"><i class="bi bi-trash"></i></button>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    document.getElementById('loadingTable').style.display = 'none';
    document.getElementById('tableContainer').style.display = 'block';
    document.getElementById('tableFooter').style.display = 'block';
    document.getElementById('contadorProveedores').textContent = `Mostrando ${proveedores.length} proveedores`;
    document.getElementById('tituloTabla').textContent = `Listado de Proveedores (${proveedores.length})`;
}

function actualizarEstadisticas(resumen) {
    document.getElementById('statTotal').textContent = resumen.total || 0;
    document.getElementById('statActivos').textContent = resumen.activos || 0;
    document.getElementById('statInactivos').textContent = resumen.inactivos || 0;
    document.getElementById('statCompras').textContent = resumen.total_compras || 0;
}

function aplicarFiltros() { cargarProveedores(); }

function limpiarFiltros() {
    document.getElementById('searchInput').value = '';
    document.getElementById('filterEstado').value = '';
    document.getElementById('filterProductos').value = '';
    cargarProveedores();
}

function desactivarProveedor(id) {
    if (confirm('¿Está seguro de desactivar este proveedor?')) {
        alert('MODO DESARROLLO: Desactivar proveedor #' + id);
    }
}

function mostrarError(mensaje) {
    document.getElementById('loadingTable').style.display = 'none';
    document.getElementById('noResults').style.display = 'block';
    document.getElementById('noResults').innerHTML = `<i class="bi bi-exclamation-triangle text-danger" style="font-size: 48px;"></i><p class="mt-3 text-danger">${mensaje}</p>`;
}
</script>

<?php include '../../includes/footer.php'; ?>