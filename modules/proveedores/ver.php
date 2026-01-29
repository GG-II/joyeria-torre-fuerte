<?php
/**
 * ================================================
 * MÓDULO PROVEEDORES - VER DETALLES
 * ================================================
 * 
 * TODO FASE 5: Conectar con API
 * GET /api/proveedores/ver.php?id={proveedor_id}
 * 
 * Respuesta: { proveedor, compras, estadisticas }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();

$proveedor_id = $_GET['id'] ?? null;
if (!$proveedor_id) {
    header('Location: lista.php');
    exit;
}

$proveedor = null;
$titulo_pagina = 'Detalles del Proveedor';
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="container-fluid main-content">
    <div id="loadingState" class="text-center py-5">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
        <p class="mt-3 text-muted">Cargando detalles del proveedor...</p>
    </div>

    <div id="mainContent" style="display: none;">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>dashboard.php"><i class="bi bi-house"></i> Dashboard</a></li>
                <li class="breadcrumb-item"><a href="lista.php"><i class="bi bi-truck"></i> Proveedores</a></li>
                <li class="breadcrumb-item active" id="breadcrumbNombre">-</li>
            </ol>
        </nav>

        <div class="card mb-4 shadow-sm" style="border-left: 5px solid #d4af37;">
            <div class="card-body">
                <div class="row align-items-center g-3">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-primary text-white me-3 d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; border-radius: 12px;">
                                <i class="bi bi-truck" style="font-size: 28px;"></i>
                            </div>
                            <div>
                                <h2 class="mb-1" id="proveedorNombre">-</h2>
                                <h5 class="text-muted mb-2" id="proveedorEmpresa" style="display: none;"></h5>
                                <div class="d-flex flex-wrap gap-3 text-muted" id="proveedorInfo"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                            <?php if (tiene_permiso('proveedores', 'editar')): ?>
                            <a href="editar.php?id=<?php echo $proveedor_id; ?>" class="btn btn-warning">
                                <i class="bi bi-pencil"></i> <span class="d-none d-sm-inline">Editar</span>
                            </a>
                            <?php endif; ?>
                            <a href="lista.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> <span class="d-none d-sm-inline">Volver</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card mb-3 shadow-sm">
                    <div class="card-header" style="background-color: #1e3a8a; color: white;">
                        <i class="bi bi-info-circle"></i> Información del Proveedor
                    </div>
                    <div class="card-body" id="infoProveedor">
                        <div class="text-center py-3"><div class="spinner-border spinner-border-sm"></div></div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <i class="bi bi-box-seam"></i> Productos que Suministra
                    </div>
                    <div class="card-body" id="infoProductos">
                        <div class="text-center py-3"><div class="spinner-border spinner-border-sm"></div></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="row g-3 mb-4" id="estadisticas">
                    <div class="col-md-4">
                        <div class="stat-card azul">
                            <div class="stat-icon"><i class="bi bi-cart"></i></div>
                            <div class="stat-value" id="statCompras">0</div>
                            <div class="stat-label">Compras Realizadas</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card dorado">
                            <div class="stat-icon"><i class="bi bi-cash-stack"></i></div>
                            <div class="stat-value" id="statTotal">Q 0</div>
                            <div class="stat-label">Total Comprado</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card verde">
                            <div class="stat-icon"><i class="bi bi-calculator"></i></div>
                            <div class="stat-value" id="statPromedio">Q 0</div>
                            <div class="stat-label">Promedio por Compra</div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header" style="background-color: #1e3a8a; color: white;">
                        <i class="bi bi-clock-history"></i> <span id="tituloHistorial">Historial de Compras</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background-color: #1e3a8a; color: white;">
                                <tr>
                                    <th>#</th>
                                    <th>Fecha</th>
                                    <th class="d-none d-lg-table-cell">Productos</th>
                                    <th>Total</th>
                                    <th class="d-none d-md-table-cell">Registrado por</th>
                                </tr>
                            </thead>
                            <tbody id="comprasBody">
                                <tr><td colspan="5" class="text-center py-4"><div class="spinner-border spinner-border-sm"></div></td></tr>
                            </tbody>
                            <tfoot class="table-light" id="tableFoot" style="display: none;">
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">TOTAL:</td>
                                    <td class="fw-bold text-success" id="totalCompras">Q 0.00</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="card-footer" id="tableFooter" style="display: none;">
                        <small class="text-muted" id="contadorCompras">Mostrando 0 compras</small>
                    </div>
                </div>

                <?php if (tiene_permiso('proveedores', 'editar')): ?>
                <div class="card mt-3 shadow-sm">
                    <div class="card-header"><i class="bi bi-gear"></i> Acciones Disponibles</div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <a href="editar.php?id=<?php echo $proveedor_id; ?>" class="btn btn-warning w-100">
                                    <i class="bi bi-pencil"></i> Editar Información
                                </a>
                            </div>
                            <div class="col-md-4">
                                <button class="btn btn-info w-100" onclick="imprimirDetalles()">
                                    <i class="bi bi-printer"></i> Imprimir
                                </button>
                            </div>
                            <div class="col-md-4">
                                <button class="btn btn-danger w-100" onclick="desactivarProveedor()">
                                    <i class="bi bi-x-circle"></i> Desactivar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div id="errorState" style="display: none;" class="text-center py-5">
        <i class="bi bi-exclamation-triangle text-danger" style="font-size: 48px;"></i>
        <h4 class="mt-3">Error al cargar el proveedor</h4>
        <p class="text-muted" id="errorMessage">No se pudo cargar la información.</p>
        <a href="lista.php" class="btn btn-primary mt-3"><i class="bi bi-arrow-left"></i> Volver al listado</a>
    </div>
</div>

<style>
.main-content { padding: 20px; min-height: calc(100vh - 120px); }
.shadow-sm { box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08) !important; }
.card-body > div:not(:last-child) { padding-bottom: 12px; margin-bottom: 12px; border-bottom: 1px solid #e5e7eb; }
.stat-card { background: white; border-radius: 12px; padding: 15px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08); transition: transform 0.2s ease; border-left: 4px solid; height: 100%; }
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12); }
.stat-card.azul { border-left-color: #1e3a8a; }
.stat-card.verde { border-left-color: #22c55e; }
.stat-card.dorado { border-left-color: #d4af37; }
.stat-icon { width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 22px; margin-bottom: 12px; }
.stat-card.azul .stat-icon { background: rgba(30, 58, 138, 0.1); color: #1e3a8a; }
.stat-card.verde .stat-icon { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
.stat-card.dorado .stat-icon { background: rgba(212, 175, 55, 0.1); color: #d4af37; }
.stat-value { font-size: 1.5rem; font-weight: 700; color: #1a1a1a; margin: 8px 0; }
.stat-label { font-size: 0.8rem; color: #6b7280; font-weight: 500; }
table thead th { font-weight: 600; font-size: 0.85rem; text-transform: uppercase; padding: 12px; }
table tbody td { padding: 12px; vertical-align: middle; }
@media (max-width: 575.98px) {
    .main-content { padding: 15px 10px; }
    h2 { font-size: 1.5rem; }
    .stat-card { padding: 12px; }
    .stat-icon { width: 38px; height: 38px; font-size: 18px; margin-bottom: 8px; }
    .stat-value { font-size: 1.25rem; }
    table { font-size: 0.85rem; }
    table thead th, table tbody td { padding: 8px 6px; }
}
@media (min-width: 576px) and (max-width: 767.98px) { .main-content { padding: 18px 15px; } }
@media (min-width: 992px) { .main-content { padding: 25px 30px; } }
@media (max-width: 767.98px) { .btn { min-height: 44px; } }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    cargarDetallesProveedor();
});

function cargarDetallesProveedor() {
    const proveedorId = <?php echo $proveedor_id; ?>;
    
    /* TODO FASE 5: Descomentar
    fetch('<?php echo BASE_URL; ?>api/proveedores/ver.php?id=' + proveedorId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderizarProveedor(data.data.proveedor);
                renderizarCompras(data.data.compras);
                actualizarEstadisticas(data.data.estadisticas);
            } else {
                mostrarError(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarError('Error al cargar los datos');
        });
    */
    
    setTimeout(() => mostrarError('MODO DESARROLLO: Esperando API'), 1500);
}

function renderizarProveedor(proveedor) {
    document.getElementById('loadingState').style.display = 'none';
    document.getElementById('mainContent').style.display = 'block';
    
    document.getElementById('breadcrumbNombre').textContent = proveedor.nombre;
    document.getElementById('proveedorNombre').textContent = proveedor.nombre;
    
    if (proveedor.empresa) {
        document.getElementById('proveedorEmpresa').textContent = proveedor.empresa;
        document.getElementById('proveedorEmpresa').style.display = 'block';
    }
    
    const badgeEstado = proveedor.activo 
        ? '<span class="badge bg-success">Activo</span>'
        : '<span class="badge bg-secondary">Inactivo</span>';
    
    let info = '';
    if (proveedor.telefono) info += `<span><i class="bi bi-phone"></i> ${proveedor.telefono}</span>`;
    if (proveedor.email) info += `<span><i class="bi bi-envelope"></i> ${proveedor.email}</span>`;
    info += `<span>${badgeEstado}</span>`;
    
    document.getElementById('proveedorInfo').innerHTML = info;
    
    let infoHTML = `<div><small class="text-muted d-block">Nombre:</small><strong>${proveedor.nombre}</strong></div>`;
    if (proveedor.empresa) infoHTML += `<div><small class="text-muted d-block">Empresa:</small><strong>${proveedor.empresa}</strong></div>`;
    if (proveedor.contacto) infoHTML += `<div><small class="text-muted d-block">Contacto:</small><strong>${proveedor.contacto}</strong></div>`;
    infoHTML += '<hr>';
    if (proveedor.telefono) infoHTML += `<div><small class="text-muted d-block">Teléfono:</small><strong>${proveedor.telefono}</strong></div>`;
    if (proveedor.email) infoHTML += `<div><small class="text-muted d-block">Email:</small><strong>${proveedor.email}</strong></div>`;
    if (proveedor.direccion) infoHTML += `<div><small class="text-muted d-block">Dirección:</small><strong>${proveedor.direccion}</strong></div>`;
    infoHTML += '<hr>';
    infoHTML += `<div><small class="text-muted d-block">Fecha de Registro:</small><strong>${formatearFechaHora(proveedor.fecha_creacion)}</strong></div>`;
    
    document.getElementById('infoProveedor').innerHTML = infoHTML;
    
    document.getElementById('infoProductos').innerHTML = proveedor.productos_suministra 
        ? `<p class="mb-0">${proveedor.productos_suministra.replace(/\n/g, '<br>')}</p>`
        : '<p class="text-muted mb-0">No especificado</p>';
}

function renderizarCompras(compras) {
    const tbody = document.getElementById('comprasBody');
    
    if (compras.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-5"><i class="bi bi-inbox" style="font-size: 48px;"></i><p class="mt-2">No hay compras registradas</p></td></tr>';
        document.getElementById('tableFooter').style.display = 'block';
        document.getElementById('contadorCompras').textContent = 'Sin compras registradas';
        return;
    }
    
    let html = '';
    let total = 0;
    compras.forEach(c => {
        total += parseFloat(c.total);
        html += `
            <tr>
                <td class="fw-bold">${c.id}</td>
                <td>${formatearFecha(c.fecha)}</td>
                <td class="d-none d-lg-table-cell">${c.productos}</td>
                <td class="fw-bold text-success">Q ${formatearMoneda(c.total)}</td>
                <td class="d-none d-md-table-cell"><small class="text-muted">${c.usuario_nombre}</small></td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    document.getElementById('totalCompras').textContent = 'Q ' + formatearMoneda(total);
    document.getElementById('tableFoot').style.display = 'table-footer-group';
    document.getElementById('tableFooter').style.display = 'block';
    document.getElementById('contadorCompras').textContent = `Mostrando últimas ${compras.length} compras`;
    document.getElementById('tituloHistorial').textContent = `Historial de Compras (${compras.length})`;
}

function actualizarEstadisticas(stats) {
    document.getElementById('statCompras').textContent = stats.total_compras || 0;
    document.getElementById('statTotal').textContent = 'Q ' + formatearMoneda(stats.total_monto || 0).split('.')[0];
    document.getElementById('statPromedio').textContent = 'Q ' + formatearMoneda(stats.promedio || 0).split('.')[0];
}

function formatearMoneda(monto) {
    return parseFloat(monto).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function formatearFecha(fecha) {
    const d = new Date(fecha);
    return d.toLocaleDateString('es-GT', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function formatearFechaHora(fecha) {
    const d = new Date(fecha);
    return d.toLocaleDateString('es-GT', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function mostrarError(mensaje) {
    document.getElementById('loadingState').style.display = 'none';
    document.getElementById('errorState').style.display = 'block';
    document.getElementById('errorMessage').textContent = mensaje;
}

function imprimirDetalles() { alert('MODO DESARROLLO: Imprimir detalles - Pendiente implementar'); }
function desactivarProveedor() {
    if (confirm('¿Está seguro de desactivar este proveedor?')) {
        alert('MODO DESARROLLO: Desactivar proveedor - Pendiente implementar');
    }
}
</script>

<?php include '../../includes/footer.php'; ?>