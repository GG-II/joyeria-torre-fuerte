<?php
/**
 * ================================================
 * MÓDULO VENTAS - LISTA
 * ================================================
 * 
 * Vista de listado de ventas con filtros y búsqueda.
 * Muestra resumen de ventas del día y tabla completa.
 * 
 * TODO FASE 5: Conectar con API
 * GET /api/ventas/lista.php
 * 
 * Parámetros opcionales:
 * - fecha_desde: YYYY-MM-DD
 * - fecha_hasta: YYYY-MM-DD
 * - estado: completada|apartada|anulada
 * - tipo: normal|credito|apartado
 * - buscar: texto de búsqueda
 * 
 * Respuesta esperada:
 * {
 *   "success": true,
 *   "data": [...],
 *   "resumen": {
 *     "completadas": 0,
 *     "total_ventas": 0,
 *     "apartadas": 0,
 *     "anuladas": 0
 *   },
 *   "total": 0
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

// Verificar autenticación y permisos
requiere_autenticacion();

// Título de página
$titulo_pagina = 'Ventas';

// Incluir header
include '../../includes/header.php';

// Incluir navbar
include '../../includes/navbar.php';

// TODO FASE 5: Los datos se cargarán vía API
$ventas = [];
?>

<!-- Contenido Principal -->
<div class="container-fluid main-content">
    <!-- Encabezado de Página -->
    <div class="page-header mb-4">
        <div class="row align-items-center g-3">
            <div class="col-md-6">
                <h1 class="mb-2">
                    <i class="bi bi-cart-check"></i>
                    Ventas
                </h1>
                <p class="text-muted mb-0">Historial de ventas y punto de venta</p>
            </div>
            <div class="col-md-6 text-md-end">
                <?php if (tiene_permiso('ventas', 'crear')): ?>
                <a href="nueva.php" class="btn btn-primary btn-lg">
                    <i class="bi bi-plus-circle"></i>
                    <span class="d-none d-sm-inline">Nueva Venta</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Resumen de Ventas -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card verde">
                <div class="stat-icon">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-value" id="statCompletadas">0</div>
                <div class="stat-label">Completadas Hoy</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card dorado">
                <div class="stat-icon">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div class="stat-value" id="statTotal">Q 0</div>
                <div class="stat-label">Total en Ventas</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card amarillo">
                <div class="stat-icon">
                    <i class="bi bi-bookmark"></i>
                </div>
                <div class="stat-value" id="statApartadas">0</div>
                <div class="stat-label">Apartados</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card rojo">
                <div class="stat-icon">
                    <i class="bi bi-x-circle"></i>
                </div>
                <div class="stat-value" id="statAnuladas">0</div>
                <div class="stat-label">Anuladas</div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-3">
                    <label class="form-label">Buscar</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control" id="searchInput" 
                               placeholder="Número, cliente...">
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">Fecha Desde</label>
                    <input type="date" class="form-control" id="fechaDesde">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">Fecha Hasta</label>
                    <input type="date" class="form-control" id="fechaHasta">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">Estado</label>
                    <select class="form-select" id="filterEstado">
                        <option value="">Todos</option>
                        <option value="completada">Completada</option>
                        <option value="apartada">Apartada</option>
                        <option value="anulada">Anulada</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">Tipo</label>
                    <select class="form-select" id="filterTipo">
                        <option value="">Todos</option>
                        <option value="normal">Normal</option>
                        <option value="credito">Crédito</option>
                        <option value="apartado">Apartado</option>
                    </select>
                </div>
                <div class="col-12 col-md-1">
                    <label class="form-label d-none d-md-block">&nbsp;</label>
                    <button class="btn btn-secondary w-100" onclick="limpiarFiltros()">
                        <i class="bi bi-x-circle"></i>
                        <span class="d-md-none ms-2">Limpiar</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Ventas -->
    <div class="card shadow-sm">
        <div class="card-header" style="background-color: #1e3a8a; color: white;">
            <i class="bi bi-table"></i>
            <span id="tituloTabla">Historial de Ventas</span>
        </div>
        <div class="card-body p-0">
            <!-- Estado de carga -->
            <div id="loadingTable" class="text-center py-5">
                <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
                <p class="mt-3 text-muted">Cargando ventas...</p>
            </div>

            <!-- Tabla -->
            <div id="tableContainer" class="table-responsive" style="display: none;">
                <table class="table table-hover mb-0" id="tablaVentas">
                    <thead style="background-color: #1e3a8a; color: white;">
                        <tr>
                            <th class="d-none d-lg-table-cell">Número</th>
                            <th>Fecha/Hora</th>
                            <th class="d-none d-md-table-cell">Cliente</th>
                            <th class="d-none d-xl-table-cell">Prods.</th>
                            <th class="d-none d-sm-table-cell">Subtotal</th>
                            <th class="d-none d-lg-table-cell">Desc.</th>
                            <th>Total</th>
                            <th class="d-none d-md-table-cell">Tipo</th>
                            <th>Estado</th>
                            <th class="d-none d-xl-table-cell">Vendedor</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="ventasBody">
                        <!-- Se llenará dinámicamente -->
                    </tbody>
                    <tfoot class="table-light" id="ventasFooter" style="display: none;">
                        <tr>
                            <td colspan="6" class="text-end fw-bold">TOTALES:</td>
                            <td class="fw-bold text-success" id="totalGeneral">Q 0.00</td>
                            <td colspan="4"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Sin resultados -->
            <div id="noResults" class="text-center py-5" style="display: none;">
                <i class="bi bi-inbox" style="font-size: 48px; opacity: 0.3;"></i>
                <p class="mt-3 text-muted">No se encontraron ventas</p>
            </div>
        </div>
        <div class="card-footer" id="tableFooter" style="display: none;">
            <div class="row align-items-center g-2">
                <div class="col-md-6">
                    <small class="text-muted" id="contadorVentas">
                        Mostrando 0 ventas
                    </small>
                </div>
                <div class="col-md-6 text-md-end">
                    <button class="btn btn-sm btn-secondary" onclick="exportarExcel()">
                        <i class="bi bi-file-earmark-excel"></i>
                        <span class="d-none d-sm-inline">Exportar Excel</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* ============================================
   ESTILOS ESPECÍFICOS LISTA DE VENTAS
   ============================================ */

/* Contenedor principal */
.main-content {
    padding: 20px;
    min-height: calc(100vh - 120px);
}

/* Page header */
.page-header h1 {
    font-size: 1.75rem;
    font-weight: 600;
    color: #1a1a1a;
}

/* Stats cards */
.stat-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border-left: 4px solid;
    height: 100%;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12);
}

.stat-card.verde { border-left-color: #22c55e; }
.stat-card.dorado { border-left-color: #d4af37; }
.stat-card.amarillo { border-left-color: #eab308; }
.stat-card.rojo { border-left-color: #ef4444; }

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin-bottom: 15px;
}

.stat-card.verde .stat-icon {
    background: rgba(34, 197, 94, 0.1);
    color: #22c55e;
}

.stat-card.dorado .stat-icon {
    background: rgba(212, 175, 55, 0.1);
    color: #d4af37;
}

.stat-card.amarillo .stat-icon {
    background: rgba(234, 179, 8, 0.1);
    color: #eab308;
}

.stat-card.rojo .stat-icon {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

.stat-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1a1a1a;
    margin: 10px 0;
}

.stat-label {
    font-size: 0.875rem;
    color: #6b7280;
    font-weight: 500;
}

/* Cards */
.shadow-sm {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08) !important;
}

/* Tabla */
table thead th {
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    padding: 12px;
}

table tbody td {
    padding: 12px;
    vertical-align: middle;
}

/* Badges */
.badge {
    padding: 0.35em 0.65em;
    font-size: 0.85em;
}

/* Botones de acción */
.btn-group .btn {
    margin: 0;
}

/* ============================================
   RESPONSIVE - MOBILE FIRST
   ============================================ */

/* Móvil (< 576px) */
@media (max-width: 575.98px) {
    .main-content {
        padding: 15px 10px;
    }
    
    .page-header h1 {
        font-size: 1.5rem;
    }
    
    .stat-card {
        padding: 15px;
    }
    
    .stat-icon {
        width: 40px;
        height: 40px;
        font-size: 20px;
        margin-bottom: 10px;
    }
    
    .stat-value {
        font-size: 1.5rem;
    }
    
    .stat-label {
        font-size: 0.8rem;
    }
    
    /* Tabla más compacta */
    table {
        font-size: 0.85rem;
    }
    
    table thead th,
    table tbody td {
        padding: 8px 6px;
    }
    
    /* Botones solo iconos */
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
    }
    
    /* Card body sin padding */
    .card-body {
        padding: 15px;
    }
}

/* Tablet (576px - 767.98px) */
@media (min-width: 576px) and (max-width: 767.98px) {
    .main-content {
        padding: 18px 15px;
    }
    
    .stat-card {
        padding: 18px;
    }
}

/* Desktop (992px+) */
@media (min-width: 992px) {
    .main-content {
        padding: 25px 30px;
    }
}

/* Touch targets */
@media (max-width: 767.98px) {
    .btn,
    .form-control,
    .form-select {
        min-height: 44px;
    }
}

/* Card header */
.card-header {
    font-weight: 600;
    padding: 12px 20px;
}

/* Animaciones */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

#ventasBody tr {
    animation: fadeIn 0.3s ease;
}
</style>

<script>
/**
 * ================================================
 * JAVASCRIPT - CARGA Y FILTRADO DE VENTAS
 * ================================================
 */

// Cargar datos al iniciar
document.addEventListener('DOMContentLoaded', function() {
    cargarVentas();
    
    // Event listeners para filtros
    document.getElementById('searchInput').addEventListener('input', aplicarFiltros);
    document.getElementById('fechaDesde').addEventListener('change', aplicarFiltros);
    document.getElementById('fechaHasta').addEventListener('change', aplicarFiltros);
    document.getElementById('filterEstado').addEventListener('change', aplicarFiltros);
    document.getElementById('filterTipo').addEventListener('change', aplicarFiltros);
});

/**
 * Cargar ventas desde API
 * TODO FASE 5: Conectar con API real
 */
function cargarVentas() {
    // TODO FASE 5: Descomentar y conectar
    /*
    const params = new URLSearchParams({
        fecha_desde: document.getElementById('fechaDesde').value,
        fecha_hasta: document.getElementById('fechaHasta').value,
        estado: document.getElementById('filterEstado').value,
        tipo: document.getElementById('filterTipo').value,
        buscar: document.getElementById('searchInput').value
    });
    
    fetch(`<?php echo BASE_URL; ?>api/ventas/lista.php?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderizarVentas(data.data);
                actualizarResumen(data.resumen);
            } else {
                mostrarError(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarError('Error al cargar las ventas');
        });
    */
    
    // Simular carga
    setTimeout(() => {
        document.getElementById('loadingTable').style.display = 'none';
        document.getElementById('noResults').style.display = 'block';
        document.getElementById('noResults').innerHTML = `
            <i class="bi bi-database" style="font-size: 48px; opacity: 0.3;"></i>
            <p class="mt-3 text-muted">MODO DESARROLLO: Esperando conexión con API</p>
        `;
    }, 1500);
}

/**
 * Renderizar ventas en la tabla
 */
function renderizarVentas(ventas) {
    const tbody = document.getElementById('ventasBody');
    
    if (ventas.length === 0) {
        document.getElementById('loadingTable').style.display = 'none';
        document.getElementById('noResults').style.display = 'block';
        return;
    }
    
    let html = '';
    let totalGeneral = 0;
    
    ventas.forEach(venta => {
        if (venta.estado !== 'anulada') {
            totalGeneral += parseFloat(venta.total);
        }
        
        html += `
            <tr data-estado="${venta.estado}" data-tipo="${venta.tipo_venta}">
                <td class="fw-bold text-primary d-none d-lg-table-cell">${venta.numero_venta}</td>
                <td>
                    <div class="fw-bold d-lg-none">${venta.numero_venta}</div>
                    <div>${formatearFecha(venta.fecha)}</div>
                    <small class="text-muted">${venta.hora.substring(0, 5)}</small>
                </td>
                <td class="d-none d-md-table-cell">
                    ${venta.cliente_id ? 
                        `<div class="fw-bold">${venta.cliente_nombre}</div>
                         <small class="text-muted">ID: ${venta.cliente_id}</small>` :
                        '<span class="text-muted">Consumidor Final</span>'
                    }
                </td>
                <td class="d-none d-xl-table-cell">
                    <span class="badge" style="background-color: #1e3a8a;">${venta.cantidad_productos}</span>
                </td>
                <td class="d-none d-sm-table-cell">Q ${formatearMoneda(venta.subtotal)}</td>
                <td class="d-none d-lg-table-cell">
                    ${venta.descuento > 0 ? 
                        `<span class="text-danger">-Q ${formatearMoneda(venta.descuento)}</span>` :
                        '<span class="text-muted">-</span>'
                    }
                </td>
                <td class="fw-bold text-success">Q ${formatearMoneda(venta.total)}</td>
                <td class="d-none d-md-table-cell">
                    ${getBadgeTipo(venta.tipo_venta)}
                </td>
                <td>${getBadgeEstado(venta.estado)}</td>
                <td class="d-none d-xl-table-cell">
                    <small class="text-muted">${venta.usuario_nombre}</small>
                </td>
                <td class="text-center">
                    <div class="btn-group" role="group">
                        <a href="ver.php?id=${venta.id}" 
                           class="btn btn-sm btn-info"
                           title="Ver detalles">
                            <i class="bi bi-eye"></i>
                        </a>
                        <button class="btn btn-sm btn-secondary" 
                                onclick="imprimir(${venta.id})"
                                title="Imprimir">
                            <i class="bi bi-printer"></i>
                        </button>
                        ${venta.estado === 'completada' ? 
                            `<a href="anular.php?id=${venta.id}" 
                                class="btn btn-sm btn-danger"
                                title="Anular">
                                <i class="bi bi-x-circle"></i>
                            </a>` : ''
                        }
                    </div>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    
    // Mostrar tabla y footer
    document.getElementById('loadingTable').style.display = 'none';
    document.getElementById('tableContainer').style.display = 'block';
    document.getElementById('ventasFooter').style.display = 'table-footer-group';
    document.getElementById('tableFooter').style.display = 'block';
    
    // Actualizar totales
    document.getElementById('totalGeneral').textContent = 'Q ' + formatearMoneda(totalGeneral);
    document.getElementById('contadorVentas').textContent = `Mostrando ${ventas.length} ventas`;
    document.getElementById('tituloTabla').textContent = `Historial de Ventas (${ventas.length})`;
}

/**
 * Actualizar cards de resumen
 */
function actualizarResumen(resumen) {
    document.getElementById('statCompletadas').textContent = resumen.completadas;
    document.getElementById('statTotal').textContent = 'Q ' + formatearMoneda(resumen.total_ventas);
    document.getElementById('statApartadas').textContent = resumen.apartadas;
    document.getElementById('statAnuladas').textContent = resumen.anuladas;
}

/**
 * Aplicar filtros
 */
function aplicarFiltros() {
    // TODO FASE 5: Llamar a cargarVentas() con los nuevos filtros
    cargarVentas();
}

/**
 * Limpiar filtros
 */
function limpiarFiltros() {
    document.getElementById('searchInput').value = '';
    document.getElementById('fechaDesde').value = '';
    document.getElementById('fechaHasta').value = '';
    document.getElementById('filterEstado').value = '';
    document.getElementById('filterTipo').value = '';
    cargarVentas();
}

/**
 * Exportar a Excel
 * TODO FASE 5: Conectar con API
 */
function exportarExcel() {
    alert('MODO DESARROLLO: Función de exportar pendiente de API');
    // window.location.href = '<?php echo BASE_URL; ?>api/ventas/exportar.php';
}

/**
 * Imprimir venta
 */
function imprimir(ventaId) {
    window.open(`ver.php?id=${ventaId}&imprimir=1`, '_blank');
}

/**
 * Utilidades
 */
function formatearMoneda(monto) {
    return parseFloat(monto).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function formatearFecha(fecha) {
    const d = new Date(fecha);
    return d.toLocaleDateString('es-GT', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}

function getBadgeTipo(tipo) {
    const badges = {
        'normal': '<span class="badge" style="background-color: #1e3a8a;">Normal</span>',
        'credito': '<span class="badge bg-warning">Crédito</span>',
        'apartado': '<span class="badge bg-secondary">Apartado</span>'
    };
    return badges[tipo] || '';
}

function getBadgeEstado(estado) {
    const badges = {
        'completada': '<span class="badge bg-success">Completada</span>',
        'apartada': '<span class="badge bg-warning">Apartada</span>',
        'anulada': '<span class="badge bg-danger">Anulada</span>'
    };
    return badges[estado] || '';
}

function mostrarError(mensaje) {
    document.getElementById('loadingTable').style.display = 'none';
    document.getElementById('noResults').style.display = 'block';
    document.getElementById('noResults').innerHTML = `
        <i class="bi bi-exclamation-triangle text-danger" style="font-size: 48px;"></i>
        <p class="mt-3 text-danger">${mensaje}</p>
    `;
}
</script>

<?php
// Incluir footer
include '../../includes/footer.php';
?>