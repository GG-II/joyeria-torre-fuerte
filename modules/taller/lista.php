<?php
/**
 * ================================================
 * MÓDULO TALLER - LISTA
 * ================================================
 * 
 * TODO FASE 5: Conectar con API
 * GET /api/taller/lista.php
 * 
 * Parámetros: buscar, estado, material, tipo_trabajo
 * Respuesta: { success, data: [...trabajos], resumen: {...stats} }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();

$titulo_pagina = 'Trabajos de Taller';
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="container-fluid main-content">
    <div class="page-header mb-4">
        <div class="row align-items-center g-3">
            <div class="col-md-6">
                <h1 class="mb-2"><i class="bi bi-tools"></i> Trabajos de Taller</h1>
                <p class="text-muted mb-0">Gestión de reparaciones y trabajos de orfebrería</p>
            </div>
            <div class="col-md-6 text-md-end">
                <?php if (tiene_permiso('taller', 'crear')): ?>
                <a href="agregar.php" class="btn btn-primary btn-lg">
                    <i class="bi bi-plus-circle"></i> <span class="d-none d-sm-inline">Nuevo Trabajo</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4" id="estadisticas">
        <div class="col-6 col-md-3">
            <div class="stat-card amarillo">
                <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
                <div class="stat-value" id="statRecibidos">0</div>
                <div class="stat-label">Recibidos</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card azul">
                <div class="stat-icon"><i class="bi bi-gear"></i></div>
                <div class="stat-value" id="statEnProceso">0</div>
                <div class="stat-label">En Proceso</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card verde">
                <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
                <div class="stat-value" id="statCompletados">0</div>
                <div class="stat-label">Completados</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card rojo">
                <div class="stat-icon"><i class="bi bi-exclamation-triangle"></i></div>
                <div class="stat-value" id="statAtrasados">0</div>
                <div class="stat-label">Atrasados</div>
            </div>
        </div>
    </div>

    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Buscar</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" id="searchInput" placeholder="Código, cliente...">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Estado</label>
                    <select class="form-select" id="filterEstado">
                        <option value="">Todos</option>
                        <option value="recibido">Recibido</option>
                        <option value="en_proceso">En Proceso</option>
                        <option value="completado">Completado</option>
                        <option value="entregado">Entregado</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Material</label>
                    <select class="form-select" id="filterMaterial">
                        <option value="">Todos</option>
                        <option value="oro">🟡 Oro</option>
                        <option value="plata">⚪ Plata</option>
                        <option value="otro">⚫ Otro</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tipo Trabajo</label>
                    <select class="form-select" id="filterTipo">
                        <option value="">Todos</option>
                        <option value="reparacion">Reparación</option>
                        <option value="ajuste">Ajuste</option>
                        <option value="grabado">Grabado</option>
                        <option value="diseño">Diseño</option>
                        <option value="limpieza">Limpieza</option>
                        <option value="engaste">Engaste</option>
                        <option value="fabricacion">Fabricación</option>
                    </select>
                </div>
                <div class="col-md-3">
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
            <i class="bi bi-table"></i> <span id="tituloTabla">Listado de Trabajos</span>
        </div>
        
        <div id="loadingTable" class="text-center py-5">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
            <p class="mt-3 text-muted">Cargando trabajos...</p>
        </div>

        <div id="tableContainer" class="table-responsive" style="display: none;">
            <table class="table table-hover mb-0">
                <thead style="background-color: #1e3a8a; color: white;">
                    <tr>
                        <th>Código</th>
                        <th>Cliente</th>
                        <th class="d-none d-lg-table-cell">Pieza / Trabajo</th>
                        <th class="d-none d-md-table-cell">Material</th>
                        <th>Entrega</th>
                        <th class="d-none d-xl-table-cell">Precio</th>
                        <th class="d-none d-lg-table-cell">Saldo</th>
                        <th class="d-none d-xl-table-cell">Orfebre</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="trabajosBody"></tbody>
            </table>
        </div>

        <div id="noResults" class="text-center py-5" style="display: none;">
            <i class="bi bi-inbox" style="font-size: 48px; opacity: 0.3;"></i>
            <p class="mt-3 text-muted">No se encontraron trabajos</p>
        </div>

        <div class="card-footer" id="tableFooter" style="display: none;">
            <small class="text-muted" id="contadorTrabajos">Mostrando 0 trabajos</small>
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
.stat-card.amarillo { border-left-color: #eab308; }
.stat-card.rojo { border-left-color: #ef4444; }
.stat-icon { width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 22px; margin-bottom: 12px; }
.stat-card.azul .stat-icon { background: rgba(30, 58, 138, 0.1); color: #1e3a8a; }
.stat-card.verde .stat-icon { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
.stat-card.amarillo .stat-icon { background: rgba(234, 179, 8, 0.1); color: #eab308; }
.stat-card.rojo .stat-icon { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
.stat-value { font-size: 1.5rem; font-weight: 700; color: #1a1a1a; margin: 8px 0; }
.stat-label { font-size: 0.8rem; color: #6b7280; font-weight: 500; }
table thead th { font-weight: 600; font-size: 0.85rem; text-transform: uppercase; padding: 12px; }
table tbody td { padding: 12px; vertical-align: middle; }
.table-danger { background-color: rgba(239, 68, 68, 0.1) !important; }
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
    cargarTrabajos();
    
    document.getElementById('searchInput').addEventListener('input', aplicarFiltros);
    document.getElementById('filterEstado').addEventListener('change', aplicarFiltros);
    document.getElementById('filterMaterial').addEventListener('change', aplicarFiltros);
    document.getElementById('filterTipo').addEventListener('change', aplicarFiltros);
});

function cargarTrabajos() {
    /* TODO FASE 5: Descomentar
    const params = new URLSearchParams({
        buscar: document.getElementById('searchInput').value,
        estado: document.getElementById('filterEstado').value,
        material: document.getElementById('filterMaterial').value,
        tipo_trabajo: document.getElementById('filterTipo').value
    });
    
    fetch('<?php echo BASE_URL; ?>api/taller/lista.php?' + params)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderizarTrabajos(data.data);
                actualizarEstadisticas(data.resumen);
            } else {
                mostrarError(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarError('Error al cargar los trabajos');
        });
    */
    
    setTimeout(() => {
        document.getElementById('loadingTable').style.display = 'none';
        document.getElementById('noResults').style.display = 'block';
        document.getElementById('noResults').innerHTML = '<i class="bi bi-database" style="font-size: 48px; opacity: 0.3;"></i><p class="mt-3 text-muted">MODO DESARROLLO: Esperando API</p>';
    }, 1500);
}

function renderizarTrabajos(trabajos) {
    const tbody = document.getElementById('trabajosBody');
    
    if (trabajos.length === 0) {
        document.getElementById('loadingTable').style.display = 'none';
        document.getElementById('noResults').style.display = 'block';
        return;
    }
    
    const iconosMaterial = { 'oro': '🟡', 'plata': '⚪', 'otro': '⚫' };
    const tiposTrabajo = {
        'reparacion': 'Reparación', 'ajuste': 'Ajuste', 'grabado': 'Grabado',
        'diseño': 'Diseño', 'limpieza': 'Limpieza', 'engaste': 'Engaste',
        'repuesto': 'Repuesto', 'fabricacion': 'Fabricación'
    };
    
    let html = '';
    trabajos.forEach(t => {
        const diasRestantes = calcularDiasRestantes(t.fecha_entrega_prometida);
        const atrasado = diasRestantes < 0 && t.estado !== 'entregado';
        
        html += `
            <tr class="${atrasado ? 'table-danger' : ''}">
                <td class="fw-bold text-primary">${t.codigo}</td>
                <td>
                    <div class="fw-bold">${t.cliente_nombre}</div>
                    <small class="text-muted"><i class="bi bi-phone"></i> ${t.cliente_telefono}</small>
                </td>
                <td class="d-none d-lg-table-cell">
                    <div class="fw-bold">${t.descripcion_pieza}</div>
                    <small class="text-muted">${tiposTrabajo[t.tipo_trabajo] || t.tipo_trabajo} ${t.con_piedra ? '<span class="badge bg-warning text-dark">Con piedra</span>' : ''}</small>
                </td>
                <td class="d-none d-md-table-cell">
                    <span>${iconosMaterial[t.material]} ${t.material.charAt(0).toUpperCase() + t.material.slice(1)}</span>
                    <br><small class="text-muted">${t.peso_gramos}g</small>
                </td>
                <td>
                    <div>${formatearFecha(t.fecha_entrega_prometida)}</div>
                    ${atrasado ? `<small class="text-danger fw-bold"><i class="bi bi-exclamation-triangle"></i> Atrasado ${Math.abs(diasRestantes)}d</small>` : (diasRestantes > 0 ? `<small class="text-muted">En ${diasRestantes} días</small>` : '')}
                </td>
                <td class="fw-bold text-success d-none d-xl-table-cell">Q ${formatearMoneda(t.precio_total)}</td>
                <td class="d-none d-lg-table-cell">
                    ${t.saldo > 0 ? `<span class="text-danger fw-bold">Q ${formatearMoneda(t.saldo)}</span>` : '<span class="text-success"><i class="bi bi-check-circle"></i> Pagado</span>'}
                </td>
                <td class="d-none d-xl-table-cell"><small class="text-muted">${t.empleado_actual_nombre}</small></td>
                <td>${getBadgeEstado(t.estado)}</td>
                <td class="text-center">
                    <div class="btn-group">
                        <a href="ver.php?id=${t.id}" class="btn btn-sm btn-info" title="Ver"><i class="bi bi-eye"></i></a>
                        <?php if (tiene_permiso('taller', 'editar')): ?>
                        ${t.estado !== 'entregado' ? `<a href="editar.php?id=${t.id}" class="btn btn-sm btn-warning" title="Editar"><i class="bi bi-pencil"></i></a>` : ''}
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
    document.getElementById('contadorTrabajos').textContent = `Mostrando ${trabajos.length} trabajos`;
    document.getElementById('tituloTabla').textContent = `Listado de Trabajos (${trabajos.length})`;
}

function actualizarEstadisticas(resumen) {
    document.getElementById('statRecibidos').textContent = resumen.recibidos || 0;
    document.getElementById('statEnProceso').textContent = resumen.en_proceso || 0;
    document.getElementById('statCompletados').textContent = resumen.completados || 0;
    document.getElementById('statAtrasados').textContent = resumen.atrasados || 0;
}

function getBadgeEstado(estado) {
    const badges = {
        'recibido': '<span class="badge bg-warning">Recibido</span>',
        'en_proceso': '<span class="badge bg-info">En Proceso</span>',
        'completado': '<span class="badge bg-success">Completado</span>',
        'entregado': '<span class="badge bg-secondary">Entregado</span>',
        'cancelado': '<span class="badge bg-danger">Cancelado</span>'
    };
    return badges[estado] || '';
}

function calcularDiasRestantes(fechaEntrega) {
    const hoy = new Date();
    const entrega = new Date(fechaEntrega);
    return Math.floor((entrega - hoy) / (1000 * 60 * 60 * 24));
}

function aplicarFiltros() { cargarTrabajos(); }

function limpiarFiltros() {
    document.getElementById('searchInput').value = '';
    document.getElementById('filterEstado').value = '';
    document.getElementById('filterMaterial').value = '';
    document.getElementById('filterTipo').value = '';
    cargarTrabajos();
}

function formatearMoneda(monto) {
    return parseFloat(monto).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function formatearFecha(fecha) {
    const d = new Date(fecha);
    return d.toLocaleDateString('es-GT', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function mostrarError(mensaje) {
    document.getElementById('loadingTable').style.display = 'none';
    document.getElementById('noResults').style.display = 'block';
    document.getElementById('noResults').innerHTML = `<i class="bi bi-exclamation-triangle text-danger" style="font-size: 48px;"></i><p class="mt-3 text-danger">${mensaje}</p>`;
}
</script>

<?php include '../../includes/footer.php'; ?>