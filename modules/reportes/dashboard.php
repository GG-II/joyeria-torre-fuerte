<?php
/**
 * ================================================
 * MÓDULO REPORTES - DASHBOARD
 * ================================================
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();

require_once '../../includes/header.php';
require_once '../../includes/navbar.php';
?>

<div class="container-fluid px-4 py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-1"><i class="bi bi-speedometer2"></i> Dashboard</h2>
            <p class="text-muted mb-0">Resumen general del sistema</p>
        </div>
        <div>
            <input type="date" class="form-control" id="fechaDashboard" value="<?php echo date('Y-m-d'); ?>">
        </div>
    </div>

    <hr class="border-warning border-2 opacity-75 mb-4">

    <!-- Alertas -->
    <div id="containerAlertas" class="mb-4"></div>

    <!-- KPIs Principales -->
    <div class="row g-3 mb-4">
        <!-- Ventas del Día -->
        <div class="col-md-3">
            <div class="card border-start border-success border-4 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="text-muted mb-0">Ventas del Día</h6>
                        <i class="bi bi-cart-check fs-3 text-success"></i>
                    </div>
                    <h3 class="mb-0" id="totalVentasDia">Q 0.00</h3>
                    <small class="text-muted" id="cantidadVentas">0 ventas</small>
                </div>
            </div>
        </div>

        <!-- Productos Bajo Stock -->
        <div class="col-md-3">
            <div class="card border-start border-warning border-4 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="text-muted mb-0">Stock Bajo</h6>
                        <i class="bi bi-box-seam fs-3 text-warning"></i>
                    </div>
                    <h3 class="mb-0" id="productosBajoStock">0</h3>
                    <small class="text-muted">productos</small>
                </div>
            </div>
        </div>

        <!-- Cuentas por Cobrar -->
        <div class="col-md-3">
            <div class="card border-start border-info border-4 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="text-muted mb-0">Por Cobrar</h6>
                        <i class="bi bi-cash-coin fs-3 text-info"></i>
                    </div>
                    <h3 class="mb-0" id="totalPorCobrar">Q 0.00</h3>
                    <small class="text-muted" id="clientesCredito">0 clientes</small>
                </div>
            </div>
        </div>

        <!-- Trabajos Pendientes -->
        <div class="col-md-3">
            <div class="card border-start border-primary border-4 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="text-muted mb-0">Trabajos Taller</h6>
                        <i class="bi bi-tools fs-3 text-primary"></i>
                    </div>
                    <h3 class="mb-0" id="trabajosPendientes">0</h3>
                    <small class="text-muted">pendientes</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficas y Detalles -->
    <div class="row g-4">
        
        <!-- Ventas del Día -->
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-graph-up"></i> Ventas de Hoy</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-6">
                            <small class="text-muted d-block">Ticket Promedio</small>
                            <h5 id="ticketPromedio">Q 0.00</h5>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Total Ventas</small>
                            <h5 id="cantidadVentasDetalle">0</h5>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-success me-2">Contado</span>
                                <span id="ventasContado">Q 0.00</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-warning">Crédito</span>
                                <span class="ms-2" id="ventasCredito">Q 0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventario Crítico -->
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Estado de Inventario</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Total Productos</span>
                            <strong id="totalProductos">0</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-danger">Sin Stock</span>
                            <strong class="text-danger" id="productosSinStock">0</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-warning">Stock Bajo</span>
                            <strong class="text-warning" id="productosBajoStockDetalle">0</strong>
                        </div>
                    </div>
                    <a href="inventario.php" class="btn btn-sm btn-warning w-100">
                        <i class="bi bi-arrow-right-circle"></i> Ver Reporte Completo
                    </a>
                </div>
            </div>
        </div>

        <!-- Trabajos de Taller -->
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-wrench"></i> Taller</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Trabajos Pendientes</span>
                            <strong id="trabajosPendientesDetalle">0</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>En Proceso</span>
                            <strong class="text-info" id="trabajosEnProceso">0</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-danger">Atrasados</span>
                            <strong class="text-danger" id="trabajosAtrasados">0</strong>
                        </div>
                    </div>
                    <a href="../taller/lista.php" class="btn btn-sm btn-primary w-100">
                        <i class="bi bi-arrow-right-circle"></i> Ir a Taller
                    </a>
                </div>
            </div>
        </div>

        <!-- Cuentas por Cobrar -->
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-wallet2"></i> Cuentas por Cobrar</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Total por Cobrar</span>
                            <strong id="totalPorCobrarDetalle">Q 0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Clientes con Crédito</span>
                            <strong id="clientesCreditoDetalle">0</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-danger">Créditos Vencidos</span>
                            <strong class="text-danger" id="creditosVencidos">0</strong>
                        </div>
                    </div>
                    <a href="financiero.php" class="btn btn-sm btn-info w-100">
                        <i class="bi bi-arrow-right-circle"></i> Ver Reporte Financiero
                    </a>
                </div>
            </div>
        </div>

    </div>

</div>

<?php require_once '../../includes/footer.php'; ?>

<script src="../../assets/js/vendors/sweetalert2/sweetalert2.all.min.js"></script>
<script src="../../assets/js/common.js"></script>
<script src="../../assets/js/api-client.js"></script>

<script>
let dashboardData = null;

async function cargarDashboard() {
    try {
        mostrarCargando();
        
        const fecha = document.getElementById('fechaDashboard').value;
        
        const res = await fetch('/joyeria-torre-fuerte/api/reportes/dashboard.php?fecha=' + fecha);
        const data = await res.json();
        
        ocultarCargando();
        
        if (!data.success) {
            mostrarError(data.message || 'Error al cargar dashboard');
            return;
        }
        
        dashboardData = data.data;
        mostrarDashboard();
        
    } catch (error) {
        ocultarCargando();
        console.error('Error:', error);
        mostrarError('Error al cargar dashboard: ' + error.message);
    }
}

function mostrarDashboard() {
    if (!dashboardData) return;
    
    mostrarAlertas();
    mostrarKPIs();
    mostrarDetalles();
}

function mostrarAlertas() {
    const container = document.getElementById('containerAlertas');
    
    if (!dashboardData.alertas || dashboardData.alertas.length === 0) {
        container.innerHTML = '';
        return;
    }
    
    let html = '<div class="row g-3">';
    
    dashboardData.alertas.forEach(alerta => {
        let iconClass = 'bi-info-circle';
        let bgClass = 'alert-info';
        
        if (alerta.nivel === 'critico') {
            iconClass = 'bi-exclamation-triangle-fill';
            bgClass = 'alert-danger';
        } else if (alerta.nivel === 'advertencia') {
            iconClass = 'bi-exclamation-circle';
            bgClass = 'alert-warning';
        }
        
        html += '<div class="col-md-6">';
        html += '<div class="alert ' + bgClass + ' d-flex align-items-center" role="alert">';
        html += '<i class="bi ' + iconClass + ' fs-4 me-3"></i>';
        html += '<div class="flex-grow-1">';
        html += '<strong>' + escaparHTML(alerta.mensaje) + '</strong><br>';
        html += '<small>' + escaparHTML(alerta.accion) + '</small>';
        html += '</div></div></div>';
    });
    
    html += '</div>';
    container.innerHTML = html;
}

function mostrarKPIs() {
    const ventas = dashboardData.ventas_hoy || {};
    const inventario = dashboardData.inventario || {};
    const cuentas = dashboardData.cuentas_por_cobrar || {};
    const taller = dashboardData.taller || {};
    
    document.getElementById('totalVentasDia').textContent = formatearMoneda(ventas.monto_total || 0);
    document.getElementById('cantidadVentas').textContent = (ventas.total_ventas || 0) + ' ventas';
    document.getElementById('productosBajoStock').textContent = inventario.productos_bajo_stock || 0;
    document.getElementById('totalPorCobrar').textContent = formatearMoneda(cuentas.total_por_cobrar || 0);
    document.getElementById('clientesCredito').textContent = (cuentas.total_clientes_credito || 0) + ' clientes';
    document.getElementById('trabajosPendientes').textContent = taller.trabajos_pendientes || 0;
}

function mostrarDetalles() {
    const ventas = dashboardData.ventas_hoy || {};
    const inventario = dashboardData.inventario || {};
    const cuentas = dashboardData.cuentas_por_cobrar || {};
    const taller = dashboardData.taller || {};
    
    document.getElementById('ticketPromedio').textContent = formatearMoneda(ventas.ticket_promedio || 0);
    document.getElementById('cantidadVentasDetalle').textContent = ventas.total_ventas || 0;
    document.getElementById('ventasContado').textContent = formatearMoneda(ventas.ventas_contado || 0);
    document.getElementById('ventasCredito').textContent = formatearMoneda(ventas.ventas_credito || 0);
    document.getElementById('totalProductos').textContent = inventario.total_productos || 0;
    document.getElementById('productosSinStock').textContent = inventario.productos_sin_stock || 0;
    document.getElementById('productosBajoStockDetalle').textContent = inventario.productos_bajo_stock || 0;
    document.getElementById('trabajosPendientesDetalle').textContent = taller.trabajos_pendientes || 0;
    document.getElementById('trabajosEnProceso').textContent = taller.en_proceso || 0;
    document.getElementById('trabajosAtrasados').textContent = taller.trabajos_atrasados || 0;
    document.getElementById('totalPorCobrarDetalle').textContent = formatearMoneda(cuentas.total_por_cobrar || 0);
    document.getElementById('clientesCreditoDetalle').textContent = cuentas.total_clientes_credito || 0;
    document.getElementById('creditosVencidos').textContent = cuentas.creditos_vencidos || 0;
}

document.getElementById('fechaDashboard').addEventListener('change', cargarDashboard);
document.addEventListener('DOMContentLoaded', cargarDashboard);

console.log('✅ Dashboard de Reportes cargado correctamente');
</script>