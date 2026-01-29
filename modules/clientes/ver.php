<?php
/**
 * ================================================
 * MÓDULO CLIENTES - VER FICHA
 * ================================================
 * 
 * Vista de ficha completa del cliente.
 * Muestra datos personales, crédito, estadísticas, historial de compras y créditos.
 * 
 * TODO FASE 5: Conectar con API
 * GET /api/clientes/ver.php?id={cliente_id}
 * 
 * Respuesta esperada:
 * {
 *   "success": true,
 *   "data": {
 *     "cliente": {...},
 *     "estadisticas": {...},
 *     "historial_compras": [...],
 *     "creditos": [...]
 *   }
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

// Verificar autenticación y permisos
requiere_autenticacion();

// Obtener ID del cliente
$cliente_id = $_GET['id'] ?? null;

if (!$cliente_id) {
    header('Location: lista.php');
    exit;
}

// TODO FASE 5: Cargar datos desde API
$cliente = null;

// Título de página
$titulo_pagina = 'Ficha del Cliente';

// Incluir header
include '../../includes/header.php';

// Incluir navbar
include '../../includes/navbar.php';
?>

<!-- Contenido Principal -->
<div class="container-fluid main-content">
    <!-- Estado de carga -->
    <div id="loadingState" class="text-center py-5">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
        <p class="mt-3 text-muted">Cargando ficha del cliente...</p>
    </div>

    <!-- Contenido principal -->
    <div id="mainContent" style="display: none;">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="<?php echo BASE_URL; ?>dashboard.php">
                        <i class="bi bi-house"></i> Dashboard
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a href="lista.php">
                        <i class="bi bi-people"></i> Clientes
                    </a>
                </li>
                <li class="breadcrumb-item active" id="breadcrumbNombre">-</li>
            </ol>
        </nav>

        <!-- Encabezado con información del cliente -->
        <div class="card mb-4 shadow-sm" style="border-left: 5px solid #d4af37;">
            <div class="card-body">
                <div class="row align-items-center g-3">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center">
                            <!-- Avatar -->
                            <div class="user-avatar bg-primary text-white me-3 d-flex align-items-center justify-content-center" id="clienteAvatar">
                                ?
                            </div>
                            <div>
                                <h2 class="mb-2" id="clienteNombre">-</h2>
                                <div class="d-flex flex-wrap gap-3 text-muted">
                                    <span id="clienteNit">
                                        <i class="bi bi-card-text"></i>
                                        NIT: -
                                    </span>
                                    <span id="clienteTelefono">
                                        <i class="bi bi-telephone"></i>
                                        -
                                    </span>
                                    <span id="clienteTipo"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                            <?php if (tiene_permiso('clientes', 'editar')): ?>
                            <a href="editar.php?id=<?php echo $cliente_id; ?>" class="btn btn-warning">
                                <i class="bi bi-pencil"></i>
                                <span class="d-none d-sm-inline">Editar</span>
                            </a>
                            <?php endif; ?>
                            <a href="lista.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i>
                                <span class="d-none d-sm-inline">Volver</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <!-- Información del Cliente -->
            <div class="col-lg-4">
                <!-- Datos Personales -->
                <div class="card mb-3 shadow-sm">
                    <div class="card-header" style="background-color: #1e3a8a; color: white;">
                        <i class="bi bi-person-badge"></i>
                        Datos Personales
                    </div>
                    <div class="card-body" id="datosPersonales">
                        <div class="text-center text-muted py-3">
                            <div class="spinner-border spinner-border-sm"></div>
                            <p class="mt-2 mb-0 small">Cargando...</p>
                        </div>
                    </div>
                </div>

                <!-- Crédito -->
                <div class="card mb-3 shadow-sm">
                    <div class="card-header bg-success text-white">
                        <i class="bi bi-cash-coin"></i>
                        Información de Crédito
                    </div>
                    <div class="card-body" id="infoCredito">
                        <div class="text-center text-muted py-3">
                            <div class="spinner-border spinner-border-sm"></div>
                            <p class="mt-2 mb-0 small">Cargando...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Historial y Estadísticas -->
            <div class="col-lg-8">
                <!-- Estadísticas Rápidas -->
                <div class="row g-3 mb-4" id="estadisticas">
                    <div class="col-md-4">
                        <div class="stat-card dorado">
                            <div class="stat-icon">
                                <i class="bi bi-cart-check"></i>
                            </div>
                            <div class="stat-value" id="statCompras">0</div>
                            <div class="stat-label">Total Compras</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card verde">
                            <div class="stat-icon">
                                <i class="bi bi-cash-stack"></i>
                            </div>
                            <div class="stat-value" id="statGastado">Q 0</div>
                            <div class="stat-label">Total Gastado</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card azul">
                            <div class="stat-icon">
                                <i class="bi bi-graph-up"></i>
                            </div>
                            <div class="stat-value" id="statPromedio">Q 0</div>
                            <div class="stat-label">Promedio por Compra</div>
                        </div>
                    </div>
                </div>

                <!-- Pestañas -->
                <ul class="nav nav-tabs mb-3" id="clienteTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="compras-tab" data-bs-toggle="tab" 
                                data-bs-target="#compras" type="button">
                            <i class="bi bi-cart"></i>
                            Historial de Compras
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="creditos-tab" data-bs-toggle="tab" 
                                data-bs-target="#creditos" type="button">
                            <i class="bi bi-credit-card"></i>
                            Créditos
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="clienteTabsContent">
                    <!-- Historial de Compras -->
                    <div class="tab-pane fade show active" id="compras">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <i class="bi bi-clock-history"></i>
                                <span id="tituloCompras">Últimas Compras</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead style="background-color: #1e3a8a; color: white;">
                                        <tr>
                                            <th class="d-none d-md-table-cell">Número</th>
                                            <th>Fecha</th>
                                            <th>Total</th>
                                            <th class="d-none d-lg-table-cell">Tipo</th>
                                            <th class="d-none d-xl-table-cell">Pago</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="comprasBody">
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <div class="spinner-border spinner-border-sm"></div>
                                                <span class="ms-2">Cargando compras...</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="table-light" id="comprasFooter" style="display: none;">
                                        <tr class="fw-bold">
                                            <td colspan="2">TOTAL</td>
                                            <td class="text-success" id="totalCompras">Q 0.00</td>
                                            <td colspan="4"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Créditos -->
                    <div class="tab-pane fade" id="creditos">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <i class="bi bi-credit-card"></i>
                                Historial de Créditos
                            </div>
                            <div id="creditosContent">
                                <div class="text-center py-5">
                                    <div class="spinner-border"></div>
                                    <p class="mt-3 text-muted">Cargando créditos...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Error al cargar -->
    <div id="errorState" style="display: none;" class="text-center py-5">
        <i class="bi bi-exclamation-triangle text-danger" style="font-size: 48px;"></i>
        <h4 class="mt-3">Error al cargar el cliente</h4>
        <p class="text-muted" id="errorMessage">No se pudo cargar la información del cliente.</p>
        <a href="lista.php" class="btn btn-primary mt-3">
            <i class="bi bi-arrow-left"></i>
            Volver al listado
        </a>
    </div>
</div>

<style>
/* ============================================
   ESTILOS ESPECÍFICOS VER CLIENTE
   ============================================ */

/* Contenedor principal */
.main-content {
    padding: 20px;
    min-height: calc(100vh - 120px);
}

/* Cards */
.shadow-sm {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08) !important;
}

/* Avatar */
.user-avatar {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    font-size: 28px;
    font-weight: 700;
    flex-shrink: 0;
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

.stat-card.dorado { border-left-color: #d4af37; }
.stat-card.verde { border-left-color: #22c55e; }
.stat-card.azul { border-left-color: #1e3a8a; }

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

.stat-card.dorado .stat-icon {
    background: rgba(212, 175, 55, 0.1);
    color: #d4af37;
}

.stat-card.verde .stat-icon {
    background: rgba(34, 197, 94, 0.1);
    color: #22c55e;
}

.stat-card.azul .stat-icon {
    background: rgba(30, 58, 138, 0.1);
    color: #1e3a8a;
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

/* Información del cliente */
.card-body > div:not(:last-child) {
    padding-bottom: 12px;
    margin-bottom: 12px;
    border-bottom: 1px solid #e5e7eb;
}

/* Pestañas */
.nav-tabs .nav-link {
    color: #6b7280;
    border: none;
    border-bottom: 2px solid transparent;
}

.nav-tabs .nav-link:hover {
    border-bottom-color: #d4af37;
    color: #1a1a1a;
}

.nav-tabs .nav-link.active {
    color: #1e3a8a;
    border-bottom-color: #1e3a8a;
    background: none;
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

/* ============================================
   RESPONSIVE - MOBILE FIRST
   ============================================ */

/* Móvil (< 576px) */
@media (max-width: 575.98px) {
    .main-content {
        padding: 15px 10px;
    }
    
    .user-avatar {
        width: 50px;
        height: 50px;
        font-size: 20px;
    }
    
    h2 {
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
    
    table {
        font-size: 0.85rem;
    }
    
    table thead th,
    table tbody td {
        padding: 8px 6px;
    }
}

/* Tablet (576px - 767.98px) */
@media (min-width: 576px) and (max-width: 767.98px) {
    .main-content {
        padding: 18px 15px;
    }
    
    .user-avatar {
        width: 60px;
        height: 60px;
        font-size: 24px;
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
    .btn {
        min-height: 44px;
    }
    
    .nav-link {
        min-height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
}
</style>

<script>
/**
 * ================================================
 * JAVASCRIPT - VER FICHA CLIENTE
 * ================================================
 */

// Cargar datos al iniciar
document.addEventListener('DOMContentLoaded', function() {
    cargarFichaCliente();
});

/**
 * Cargar ficha completa del cliente
 * TODO FASE 5: Conectar con API
 */
function cargarFichaCliente() {
    const clienteId = <?php echo $cliente_id; ?>;
    
    // TODO FASE 5: Descomentar y conectar
    /*
    fetch(`<?php echo BASE_URL; ?>api/clientes/ver.php?id=${clienteId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const { cliente, estadisticas, historial_compras, creditos } = data.data;
                renderizarCliente(cliente);
                renderizarEstadisticas(estadisticas);
                renderizarHistorialCompras(historial_compras);
                renderizarCreditos(creditos);
            } else {
                mostrarError(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarError('Error al cargar la ficha del cliente');
        });
    */
    
    // Simular carga
    setTimeout(() => {
        mostrarError('MODO DESARROLLO: Esperando conexión con API');
    }, 1500);
}

/**
 * Renderizar datos del cliente
 */
function renderizarCliente(cliente) {
    document.getElementById('loadingState').style.display = 'none';
    document.getElementById('mainContent').style.display = 'block';
    
    // Header
    const inicial = cliente.nombre.charAt(0).toUpperCase();
    document.getElementById('clienteAvatar').textContent = inicial;
    document.getElementById('breadcrumbNombre').textContent = cliente.nombre;
    document.getElementById('clienteNombre').textContent = cliente.nombre;
    document.getElementById('clienteNit').innerHTML = `<i class="bi bi-card-text"></i> NIT: ${cliente.nit}`;
    document.getElementById('clienteTelefono').innerHTML = `<i class="bi bi-telephone"></i> ${cliente.telefono}`;
    document.getElementById('clienteTipo').innerHTML = getBadgeTipo(cliente.tipo_cliente);
    
    // Datos personales
    const iconosMercaderias = {
        'oro': '🟡',
        'plata': '⚪',
        'ambas': '🟡⚪'
    };
    
    document.getElementById('datosPersonales').innerHTML = `
        <div>
            <small class="text-muted d-block">Correo Electrónico</small>
            <strong>${cliente.email || 'No registrado'}</strong>
        </div>
        <div>
            <small class="text-muted d-block">Dirección</small>
            <strong>${cliente.direccion || 'No registrada'}</strong>
        </div>
        <div>
            <small class="text-muted d-block">Tipo de Mercaderías</small>
            <strong>
                ${iconosMercaderias[cliente.tipo_mercaderias]} 
                ${cliente.tipo_mercaderias.charAt(0).toUpperCase() + cliente.tipo_mercaderias.slice(1)}
            </strong>
        </div>
        <div>
            <small class="text-muted d-block">Cliente desde</small>
            <strong>${formatearFecha(cliente.fecha_creacion)}</strong>
        </div>
    `;
    
    // Crédito
    const creditoDisponible = parseFloat(cliente.limite_credito) - parseFloat(cliente.saldo_creditos || 0);
    
    document.getElementById('infoCredito').innerHTML = `
        <div>
            <small class="text-muted d-block">Límite de Crédito</small>
            <h4 class="mb-0 text-primary">Q ${formatearMoneda(cliente.limite_credito)}</h4>
        </div>
        <div>
            <small class="text-muted d-block">Saldo Pendiente</small>
            <h4 class="mb-0 ${cliente.saldo_creditos > 0 ? 'text-danger' : 'text-success'}">
                Q ${formatearMoneda(cliente.saldo_creditos || 0)}
            </h4>
        </div>
        <div>
            <small class="text-muted d-block">Crédito Disponible</small>
            <h4 class="mb-0 text-success">Q ${formatearMoneda(creditoDisponible)}</h4>
        </div>
        <div>
            <small class="text-muted d-block">Plazo de Crédito</small>
            <strong>${cliente.plazo_credito_dias ? cliente.plazo_credito_dias + ' días' : 'No aplica'}</strong>
        </div>
    `;
}

/**
 * Renderizar estadísticas
 */
function renderizarEstadisticas(stats) {
    document.getElementById('statCompras').textContent = stats.total_compras;
    document.getElementById('statGastado').textContent = 'Q ' + formatearMoneda(stats.total_gastado);
    document.getElementById('statPromedio').textContent = 'Q ' + formatearMoneda(stats.promedio_compra);
}

/**
 * Renderizar historial de compras
 */
function renderizarHistorialCompras(compras) {
    const tbody = document.getElementById('comprasBody');
    
    if (compras.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 48px; opacity: 0.3;"></i>
                    <p class="mt-3 text-muted">No hay compras registradas</p>
                </td>
            </tr>
        `;
        return;
    }
    
    let html = '';
    let total = 0;
    
    compras.forEach(venta => {
        total += parseFloat(venta.total);
        
        html += `
            <tr>
                <td class="fw-bold d-none d-md-table-cell">${venta.numero_venta}</td>
                <td>
                    <div class="fw-bold d-md-none">${venta.numero_venta}</div>
                    <div>${formatearFecha(venta.fecha)}</div>
                </td>
                <td class="fw-bold text-success">Q ${formatearMoneda(venta.total)}</td>
                <td class="d-none d-lg-table-cell">${getBadgeTipoVenta(venta.tipo_venta)}</td>
                <td class="d-none d-xl-table-cell">${venta.forma_pago || '-'}</td>
                <td>${getBadgeEstado(venta.estado)}</td>
                <td>
                    <a href="<?php echo BASE_URL; ?>modules/ventas/ver.php?id=${venta.id}" 
                       class="btn btn-sm btn-info" title="Ver venta">
                        <i class="bi bi-eye"></i>
                    </a>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    document.getElementById('comprasFooter').style.display = 'table-footer-group';
    document.getElementById('totalCompras').textContent = 'Q ' + formatearMoneda(total);
    document.getElementById('tituloCompras').textContent = `Últimas Compras (${compras.length})`;
}

/**
 * Renderizar créditos
 */
function renderizarCreditos(creditos) {
    const container = document.getElementById('creditosContent');
    
    if (creditos.length === 0) {
        container.innerHTML = `
            <div class="card-body text-center py-5">
                <i class="bi bi-inbox text-muted" style="font-size: 48px;"></i>
                <p class="text-muted mt-3 mb-0">No hay créditos registrados</p>
            </div>
        `;
        return;
    }
    
    let html = `
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead style="background-color: #1e3a8a; color: white;">
                    <tr>
                        <th>Crédito #</th>
                        <th class="d-none d-md-table-cell">Venta</th>
                        <th>Monto Total</th>
                        <th>Saldo</th>
                        <th class="d-none d-lg-table-cell">Cuotas</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    creditos.forEach(credito => {
        html += `
            <tr>
                <td class="fw-bold">#${credito.id}</td>
                <td class="d-none d-md-table-cell">${credito.numero_venta || 'V-' + credito.venta_id}</td>
                <td>Q ${formatearMoneda(credito.monto_total)}</td>
                <td class="${credito.saldo_pendiente > 0 ? 'text-danger' : 'text-success'}">
                    Q ${formatearMoneda(credito.saldo_pendiente)}
                </td>
                <td class="d-none d-lg-table-cell">
                    ${credito.cuotas_pagadas}/${credito.numero_cuotas}
                </td>
                <td>${getBadgeEstadoCredito(credito.estado)}</td>
            </tr>
        `;
    });
    
    html += `
                </tbody>
            </table>
        </div>
    `;
    
    container.innerHTML = html;
}

/**
 * Mostrar error
 */
function mostrarError(mensaje) {
    document.getElementById('loadingState').style.display = 'none';
    document.getElementById('errorState').style.display = 'block';
    document.getElementById('errorMessage').textContent = mensaje;
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
        'publico': '<span class="badge" style="background-color: #1e3a8a;">Público</span>',
        'mayorista': '<span class="badge" style="background-color: #d4af37;">Mayorista</span>'
    };
    return badges[tipo] || '';
}

function getBadgeTipoVenta(tipo) {
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

function getBadgeEstadoCredito(estado) {
    const badges = {
        'liquidado': '<span class="badge bg-success">Liquidado</span>',
        'activo': '<span class="badge bg-warning">Activo</span>',
        'vencido': '<span class="badge bg-danger">Vencido</span>'
    };
    return badges[estado] || '';
}
</script>

<?php
// Incluir footer
include '../../includes/footer.php';
?>