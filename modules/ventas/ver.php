<?php
/**
 * ================================================
 * MÓDULO VENTAS - VER DETALLES
 * ================================================
 * 
 * Vista de detalle de una venta procesada.
 * Muestra productos vendidos, formas de pago, cliente e información general.
 * 
 * TODO FASE 5: Conectar con API
 * GET /api/ventas/ver.php?id={venta_id}
 * 
 * Respuesta esperada:
 * {
 *   "success": true,
 *   "data": {
 *     "venta": {...},
 *     "detalle": [...],
 *     "formas_pago": [...]
 *   }
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

// Verificar autenticación
requiere_autenticacion();

// Obtener ID de la venta
$venta_id = $_GET['id'] ?? null;

if (!$venta_id) {
    header('Location: lista.php');
    exit;
}

// TODO FASE 5: Cargar datos desde API
// Los datos se cargarán dinámicamente vía JavaScript
$venta = null;
$detalle = [];
$formas_pago = [];

// Título de página
$titulo_pagina = 'Detalles de Venta';

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
        <p class="mt-3 text-muted">Cargando detalles de la venta...</p>
    </div>

    <!-- Contenido de la venta (se llenará dinámicamente) -->
    <div id="ventaContent" style="display: none;">
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
                        <i class="bi bi-cart-check"></i> Ventas
                    </a>
                </li>
                <li class="breadcrumb-item active" id="breadcrumbNumero">-</li>
            </ol>
        </nav>

        <!-- Encabezado de la Venta -->
        <div class="card mb-4 shadow-sm" style="border-left: 5px solid #d4af37;">
            <div class="card-body">
                <div class="row align-items-center g-3">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center">
                            <!-- Icono centrado -->
                            <div class="icon-container bg-success text-white me-3 d-flex align-items-center justify-content-center">
                                <i class="bi bi-receipt"></i>
                            </div>
                            <div>
                                <h2 class="mb-2" id="numeroVenta">-</h2>
                                <div class="d-flex flex-wrap gap-3 text-muted">
                                    <span id="fechaVenta">
                                        <i class="bi bi-calendar me-1"></i>
                                        -
                                    </span>
                                    <span id="sucursalVenta">
                                        <i class="bi bi-building me-1"></i>
                                        -
                                    </span>
                                    <span id="estadoVenta"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                            <button class="btn btn-secondary" onclick="imprimirVenta()">
                                <i class="bi bi-printer"></i>
                                <span class="d-none d-sm-inline">Imprimir</span>
                            </button>
                            <button class="btn btn-danger" id="btnAnular" style="display: none;" onclick="anularVenta()">
                                <i class="bi bi-x-circle"></i>
                                <span class="d-none d-sm-inline">Anular</span>
                            </button>
                            <a href="lista.php" class="btn btn-primary">
                                <i class="bi bi-arrow-left"></i>
                                <span class="d-none d-sm-inline">Volver</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerta de venta anulada -->
        <div class="alert alert-danger mb-3" id="alertaAnulada" style="display: none;">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Venta Anulada:</strong> <span id="motivoAnulacion"></span>
        </div>

        <div class="row g-3">
            <!-- Detalles de la Venta -->
            <div class="col-lg-8">
                <!-- Productos -->
                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-white" style="background-color: #1e3a8a;">
                        <i class="bi bi-box-seam"></i>
                        Productos Vendidos
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background-color: #1e3a8a; color: white;">
                                <tr>
                                    <th class="d-none d-md-table-cell">Código</th>
                                    <th>Producto</th>
                                    <th>Cant.</th>
                                    <th class="d-none d-sm-table-cell">Precio Unit.</th>
                                    <th class="d-none d-lg-table-cell">Tipo Precio</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="productosBody">
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="spinner-border spinner-border-sm text-primary"></div>
                                        <span class="ms-2">Cargando productos...</span>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="table-light" id="productosFooter" style="display: none;">
                                <tr>
                                    <td colspan="5" class="text-end fw-bold">Subtotal:</td>
                                    <td class="fw-bold" id="displaySubtotal">Q 0.00</td>
                                </tr>
                                <tr id="filaDescuento" style="display: none;">
                                    <td colspan="5" class="text-end fw-bold">Descuento:</td>
                                    <td class="fw-bold text-danger" id="displayDescuento">-Q 0.00</td>
                                </tr>
                                <tr class="table-success">
                                    <td colspan="5" class="text-end fw-bold">TOTAL:</td>
                                    <td class="fw-bold text-success" style="font-size: 1.2em;" id="displayTotal">Q 0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Formas de Pago -->
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <i class="bi bi-wallet2"></i>
                        Formas de Pago
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background-color: #1e3a8a; color: white;">
                                <tr>
                                    <th>Forma de Pago</th>
                                    <th>Monto</th>
                                    <th class="d-none d-sm-table-cell">Referencia</th>
                                </tr>
                            </thead>
                            <tbody id="formasPagoBody">
                                <tr>
                                    <td colspan="3" class="text-center py-4">
                                        <div class="spinner-border spinner-border-sm text-primary"></div>
                                        <span class="ms-2">Cargando formas de pago...</span>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="table-light" id="formasPagoFooter" style="display: none;">
                                <tr>
                                    <td class="fw-bold">TOTAL PAGADO:</td>
                                    <td class="fw-bold text-success" id="displayTotalPagado">Q 0.00</td>
                                    <td class="d-none d-sm-table-cell"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Información Adicional -->
            <div class="col-lg-4">
                <!-- Datos del Cliente -->
                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-white" style="background-color: #1e3a8a;">
                        <i class="bi bi-person"></i>
                        Información del Cliente
                    </div>
                    <div class="card-body" id="infoCliente">
                        <div class="text-center text-muted py-3">
                            <div class="spinner-border spinner-border-sm"></div>
                            <p class="mt-2 mb-0">Cargando...</p>
                        </div>
                    </div>
                </div>

                <!-- Información de la Venta -->
                <div class="card mb-3 shadow-sm">
                    <div class="card-header" style="background-color: #1e3a8a; color: white;">
                        <i class="bi bi-info-circle"></i>
                        Detalles de la Venta
                    </div>
                    <div class="card-body" id="detallesVenta">
                        <div class="text-center text-muted py-3">
                            <div class="spinner-border spinner-border-sm"></div>
                            <p class="mt-2 mb-0">Cargando...</p>
                        </div>
                    </div>
                </div>

                <!-- Resumen Rápido -->
                <div class="card shadow-sm" style="border-left: 4px solid #d4af37;">
                    <div class="card-header" style="background-color: #d4af37; color: white;">
                        <i class="bi bi-calculator"></i>
                        Resumen
                    </div>
                    <div class="card-body" id="resumenVenta">
                        <div class="text-center text-muted py-3">
                            <div class="spinner-border spinner-border-sm"></div>
                            <p class="mt-2 mb-0">Cargando...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Error al cargar -->
    <div id="errorState" style="display: none;" class="text-center py-5">
        <i class="bi bi-exclamation-triangle text-danger" style="font-size: 48px;"></i>
        <h4 class="mt-3">Error al cargar la venta</h4>
        <p class="text-muted" id="errorMessage">No se pudo cargar la información de la venta.</p>
        <a href="lista.php" class="btn btn-primary mt-3">
            <i class="bi bi-arrow-left"></i>
            Volver al listado
        </a>
    </div>
</div>

<style>
/* ============================================
   ESTILOS ESPECÍFICOS DETALLE DE VENTA
   ============================================ */

/* Contenedor principal */
.main-content {
    padding: 20px;
    min-height: calc(100vh - 120px);
}

/* Cards con mejor sombra */
.shadow-sm {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08) !important;
}

/* Icono del header centrado */
.icon-container {
    width: 70px;
    height: 70px;
    border-radius: 12px;
    font-size: 28px;
    flex-shrink: 0;
}

/* Tablas */
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

/* Cards del sidebar */
.card-body {
    padding: 20px;
}

/* Espaciado de información */
.card-body > div:not(:last-child) {
    padding-bottom: 12px;
    border-bottom: 1px solid #e5e7eb;
    margin-bottom: 12px;
}

/* ============================================
   RESPONSIVE - MOBILE FIRST
   ============================================ */

/* Móvil (< 576px) */
@media (max-width: 575.98px) {
    .main-content {
        padding: 15px 10px;
    }
    
    .icon-container {
        width: 50px;
        height: 50px;
        font-size: 20px;
    }
    
    .card-body h2 {
        font-size: 1.5rem;
    }
    
    table {
        font-size: 0.85rem;
    }
    
    table thead th,
    table tbody td {
        padding: 8px 6px;
    }
    
    .card-body {
        padding: 15px;
    }
    
    .card-body h3 {
        font-size: 1.75rem;
    }
    
    .card-body h5 {
        font-size: 1.1rem;
    }
}

/* Tablet (576px - 767.98px) */
@media (min-width: 576px) and (max-width: 767.98px) {
    .main-content {
        padding: 18px 15px;
    }
    
    .icon-container {
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
        padding: 0.5rem 1rem;
    }
}

/* Animaciones */
.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12) !important;
}
</style>

<script>
/**
 * ================================================
 * JAVASCRIPT - CARGA DE DATOS DE VENTA
 * ================================================
 */

// Cargar datos al iniciar
document.addEventListener('DOMContentLoaded', function() {
    cargarDatosVenta();
});

/**
 * Cargar datos de la venta desde API
 * TODO FASE 5: Conectar con API real
 */
function cargarDatosVenta() {
    const ventaId = <?php echo $venta_id; ?>;
    
    // TODO FASE 5: Descomentar y conectar
    /*
    fetch(`<?php echo BASE_URL; ?>api/ventas/ver.php?id=${ventaId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderizarVenta(data.data);
            } else {
                mostrarError(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarError('Error al cargar los datos de la venta');
        });
    */
    
    // Simular espera
    setTimeout(() => {
        mostrarError('MODO DESARROLLO: Esperando conexión con API');
    }, 1500);
}

/**
 * Renderizar datos de la venta en la interfaz
 */
function renderizarVenta(data) {
    const { venta, detalle, formas_pago } = data;
    
    // Ocultar loading y mostrar contenido
    document.getElementById('loadingState').style.display = 'none';
    document.getElementById('ventaContent').style.display = 'block';
    
    // Renderizar header
    document.getElementById('breadcrumbNumero').textContent = venta.numero_venta;
    document.getElementById('numeroVenta').textContent = venta.numero_venta;
    document.getElementById('fechaVenta').innerHTML = `
        <i class="bi bi-calendar me-1"></i>
        ${formatearFecha(venta.fecha, venta.hora)}
    `;
    document.getElementById('sucursalVenta').innerHTML = `
        <i class="bi bi-building me-1"></i>
        ${venta.sucursal_nombre}
    `;
    document.getElementById('estadoVenta').innerHTML = getBadgeEstado(venta.estado);
    
    // Mostrar botón anular si aplica
    if (venta.estado === 'completada') {
        document.getElementById('btnAnular').style.display = 'inline-block';
    }
    
    // Alerta de anulada
    if (venta.estado === 'anulada') {
        document.getElementById('alertaAnulada').style.display = 'block';
        document.getElementById('motivoAnulacion').textContent = venta.motivo_anulacion;
    }
    
    // Renderizar productos
    renderizarProductos(detalle, venta);
    
    // Renderizar formas de pago
    renderizarFormasPago(formas_pago);
    
    // Renderizar información del cliente
    renderizarCliente(venta);
    
    // Renderizar detalles de la venta
    renderizarDetalles(venta);
    
    // Renderizar resumen
    renderizarResumen(detalle, venta);
}

/**
 * Renderizar tabla de productos
 */
function renderizarProductos(detalle, venta) {
    const tbody = document.getElementById('productosBody');
    let html = '';
    
    detalle.forEach(item => {
        html += `
            <tr>
                <td class="fw-bold d-none d-md-table-cell">${item.producto_codigo}</td>
                <td>
                    <div class="fw-bold">${item.producto_nombre}</div>
                    <small class="text-muted d-md-none">${item.producto_codigo}</small>
                </td>
                <td>${item.cantidad}</td>
                <td class="d-none d-sm-table-cell">Q ${formatearMoneda(item.precio_unitario)}</td>
                <td class="d-none d-lg-table-cell">
                    <span class="badge" style="background-color: #1e3a8a;">
                        ${item.tipo_precio_aplicado}
                    </span>
                </td>
                <td class="fw-bold text-success">Q ${formatearMoneda(item.subtotal)}</td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    
    // Mostrar footer
    document.getElementById('productosFooter').style.display = 'table-footer-group';
    document.getElementById('displaySubtotal').textContent = 'Q ' + formatearMoneda(venta.subtotal);
    document.getElementById('displayTotal').textContent = 'Q ' + formatearMoneda(venta.total);
    
    if (venta.descuento > 0) {
        document.getElementById('filaDescuento').style.display = 'table-row';
        document.getElementById('displayDescuento').textContent = '-Q ' + formatearMoneda(venta.descuento);
    }
}

/**
 * Renderizar formas de pago
 */
function renderizarFormasPago(formas_pago) {
    const tbody = document.getElementById('formasPagoBody');
    let html = '';
    let total = 0;
    
    const iconos = {
        'efectivo': 'bi-cash',
        'tarjeta_debito': 'bi-credit-card',
        'tarjeta_credito': 'bi-credit-card-2-front',
        'transferencia': 'bi-bank',
        'cheque': 'bi-file-earmark-check'
    };
    
    formas_pago.forEach(pago => {
        total += parseFloat(pago.monto);
        const formaPago = pago.forma_pago.replace('_', ' ');
        
        html += `
            <tr>
                <td>
                    <i class="bi ${iconos[pago.forma_pago] || 'bi-cash'} me-1"></i>
                    ${formaPago.charAt(0).toUpperCase() + formaPago.slice(1)}
                </td>
                <td class="fw-bold">Q ${formatearMoneda(pago.monto)}</td>
                <td class="d-none d-sm-table-cell">${pago.referencia || '-'}</td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    document.getElementById('formasPagoFooter').style.display = 'table-footer-group';
    document.getElementById('displayTotalPagado').textContent = 'Q ' + formatearMoneda(total);
}

/**
 * Renderizar información del cliente
 */
function renderizarCliente(venta) {
    const container = document.getElementById('infoCliente');
    
    if (venta.cliente_id) {
        container.innerHTML = `
            <div>
                <small class="text-muted d-block">Nombre:</small>
                <strong>${venta.cliente_nombre}</strong>
            </div>
            <div>
                <small class="text-muted d-block">NIT:</small>
                <strong>${venta.cliente_nit}</strong>
            </div>
            <div>
                <small class="text-muted d-block">Teléfono:</small>
                <strong>${venta.cliente_telefono}</strong>
            </div>
        `;
    } else {
        container.innerHTML = '<p class="text-muted mb-0">Consumidor Final</p>';
    }
}

/**
 * Renderizar detalles de la venta
 */
function renderizarDetalles(venta) {
    const container = document.getElementById('detallesVenta');
    
    container.innerHTML = `
        <div>
            <small class="text-muted d-block">Tipo de Venta:</small>
            ${getBadgeTipo(venta.tipo_venta)}
        </div>
        <div>
            <small class="text-muted d-block">Vendedor:</small>
            <strong>${venta.usuario_nombre}</strong>
        </div>
        <div>
            <small class="text-muted d-block">Sucursal:</small>
            <strong>${venta.sucursal_nombre}</strong>
        </div>
        <div>
            <small class="text-muted d-block">Fecha de Registro:</small>
            <strong>${formatearFechaCompleta(venta.fecha_creacion)}</strong>
        </div>
    `;
}

/**
 * Renderizar resumen
 */
function renderizarResumen(detalle, venta) {
    const container = document.getElementById('resumenVenta');
    const totalProductos = detalle.length;
    const totalUnidades = detalle.reduce((sum, item) => sum + item.cantidad, 0);
    
    container.innerHTML = `
        <div>
            <small class="text-muted d-block">Productos Vendidos:</small>
            <h5 class="mb-0">${totalProductos} productos</h5>
        </div>
        <div>
            <small class="text-muted d-block">Unidades Totales:</small>
            <h5 class="mb-0">${totalUnidades} unidades</h5>
        </div>
        <div>
            <small class="text-muted d-block">Total de la Venta:</small>
            <h3 class="mb-0 text-success">Q ${formatearMoneda(venta.total)}</h3>
        </div>
    `;
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

function formatearFecha(fecha, hora) {
    const d = new Date(fecha + ' ' + hora);
    return d.toLocaleDateString('es-GT', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    }) + ' ' + d.toLocaleTimeString('es-GT', {
        hour: '2-digit',
        minute: '2-digit'
    });
}

function formatearFechaCompleta(fechaHora) {
    const d = new Date(fechaHora);
    return d.toLocaleDateString('es-GT', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
}

function getBadgeEstado(estado) {
    const badges = {
        'completada': '<span class="badge bg-success">Completada</span>',
        'apartada': '<span class="badge bg-warning">Apartada</span>',
        'anulada': '<span class="badge bg-danger">Anulada</span>'
    };
    return badges[estado] || '';
}

function getBadgeTipo(tipo) {
    const badges = {
        'normal': '<span class="badge" style="background-color: #1e3a8a;">Normal</span>',
        'credito': '<span class="badge bg-warning">Crédito</span>',
        'apartado': '<span class="badge bg-secondary">Apartado</span>'
    };
    return badges[tipo] || '';
}

/**
 * Acciones
 */
function imprimirVenta() {
    window.print();
}

function anularVenta() {
    if (confirm('¿Está seguro de anular esta venta?')) {
        window.location.href = 'anular.php?id=<?php echo $venta_id; ?>';
    }
}
</script>

<?php
// Incluir footer
include '../../includes/footer.php';
?>