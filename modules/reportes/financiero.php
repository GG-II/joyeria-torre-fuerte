<?php
/**
 * ================================================
 * MÓDULO REPORTES - FINANCIERO
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
            <h2 class="mb-1"><i class="bi bi-cash-stack"></i> Reporte Financiero</h2>
            <p class="text-muted mb-0">Ganancias y cuentas por cobrar</p>
        </div>
        <a href="dashboard.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Dashboard
        </a>
    </div>

    <hr class="border-warning border-2 opacity-75 mb-4">

    

<hr class="border-warning border-2 opacity-75 mb-4">

<style>
        .nav-tabs .nav-link {
            color: #495057 !important;
            background-color: transparent !important;
            font-weight: 500;
            border: 1px solid transparent;
        }
        .nav-tabs .nav-link:hover {
            color: #1e3a8a !important;
            background-color: #f8f9fa !important;
            border-color: #dee2e6 #dee2e6 #fff;
        }
        .nav-tabs .nav-link.active {
            color: #1e3a8a !important;
            background-color: #fff !important;
            font-weight: 600;
            border-color: #dee2e6 #dee2e6 #fff;
        }
    </style>

    <!-- Pestañas -->
    <ul class="nav nav-tabs mb-4" id="tabsFinanciero" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-ganancias" data-bs-toggle="tab" 
                    data-bs-target="#panelGanancias" type="button">
                <i class="bi bi-graph-up"></i> Ganancias
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-cuentas" data-bs-toggle="tab" 
                    data-bs-target="#panelCuentas" type="button">
                <i class="bi bi-wallet2"></i> Cuentas por Cobrar
            </button>
        </li>
    </ul>

    <div class="tab-content">
        
        <!-- PANEL GANANCIAS -->
        <div class="tab-pane fade show active" id="panelGanancias">
            
            <!-- Filtros -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Fecha Inicio</label>
                            <input type="date" class="form-control" id="fechaInicioGanancias">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fecha Fin</label>
                            <input type="date" class="form-control" id="fechaFinGanancias">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button class="btn btn-primary w-100" onclick="cargarGanancias()">
                                <i class="bi bi-search"></i> Generar Reporte
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPIs Ganancias -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-start border-success border-4 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Total Ingresos</h6>
                            <h3 class="mb-0 text-success" id="totalIngresos">Q 0.00</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-start border-danger border-4 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Total Egresos</h6>
                            <h3 class="mb-0 text-danger" id="totalEgresos">Q 0.00</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-start border-primary border-4 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Ganancia Neta</h6>
                            <h3 class="mb-0 text-primary" id="gananciaNeta">Q 0.00</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-start border-info border-4 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Margen</h6>
                            <h3 class="mb-0 text-info" id="margenGanancia">0%</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detalles -->
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bi bi-arrow-down-circle"></i> Ingresos</h5>
                        </div>
                        <div class="card-body">
                            <div id="detalleIngresos">
                                <p class="text-muted text-center py-4">Seleccione un período</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0"><i class="bi bi-arrow-up-circle"></i> Egresos</h5>
                        </div>
                        <div class="card-body">
                            <div id="detalleEgresos">
                                <p class="text-muted text-center py-4">Seleccione un período</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- PANEL CUENTAS POR COBRAR -->
        <div class="tab-pane fade" id="panelCuentas">
            
            <!-- KPIs Cuentas -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-start border-info border-4 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Total por Cobrar</h6>
                            <h3 class="mb-0 text-info" id="totalCuentasPorCobrar">Q 0.00</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-start border-warning border-4 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Clientes con Crédito</h6>
                            <h3 class="mb-0 text-warning" id="totalClientesCredito">0</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-start border-danger border-4 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Créditos Vencidos</h6>
                            <h3 class="mb-0 text-danger" id="creditosVencidos">0</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla Cuentas -->
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-table"></i> Listado de Cuentas por Cobrar</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Cliente</th>
                                    <th>Total Crédito</th>
                                    <th>Abonado</th>
                                    <th>Saldo</th>
                                    <th>Última Compra</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody id="tablaCuentasPorCobrar">
                                <tr><td colspan="6" class="text-center text-muted">Cargando...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Créditos Vencidos -->
            <div class="card shadow-sm mt-4" id="cardCreditosVencidos" style="display: none;">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Créditos Vencidos</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Cliente</th>
                                    <th>Monto</th>
                                    <th>Días Vencido</th>
                                    <th>Última Compra</th>
                                </tr>
                            </thead>
                            <tbody id="tablaCreditosVencidos"></tbody>
                        </table>
                    </div>
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
let datosGanancias = null;
let datosCuentas = null;

// Inicializar fechas (último mes)
function inicializarFechas() {
    const hoy = new Date();
    const haceUnMes = new Date();
    haceUnMes.setMonth(hoy.getMonth() - 1);
    
    document.getElementById('fechaInicioGanancias').valueAsDate = haceUnMes;
    document.getElementById('fechaFinGanancias').valueAsDate = hoy;
}

async function cargarGanancias() {
    try {
        mostrarCargando();
        
        const fechaInicio = document.getElementById('fechaInicioGanancias').value;
        const fechaFin = document.getElementById('fechaFinGanancias').value;
        
        if (!fechaInicio || !fechaFin) {
            mostrarError('Seleccione las fechas');
            ocultarCargando();
            return;
        }
        
        const url = '/joyeria-torre-fuerte/api/reportes/financiero.php?tipo=ganancias&fecha_inicio=' + fechaInicio + '&fecha_fin=' + fechaFin;
        const res = await fetch(url);
        const data = await res.json();
        
        ocultarCargando();
        
        if (!data.success) {
            mostrarError(data.message || 'Error al cargar ganancias');
            return;
        }
        
        datosGanancias = data.data;
        console.log('📊 Datos de Ganancias:', datosGanancias);
        console.log('💰 Ingresos completos:', datosGanancias.ingresos);
        console.log('   - Ventas:', datosGanancias.ingresos?.ventas);
        console.log('   - Taller:', datosGanancias.ingresos?.taller);
        console.log('   - Abonos:', datosGanancias.ingresos?.abonos);
        console.log('💸 Egresos completos:', datosGanancias.egresos);
        console.log('   - Detalle:', datosGanancias.egresos?.detalle);
        mostrarGanancias();
        
    } catch (error) {
        ocultarCargando();
        console.error('Error:', error);
        mostrarError('Error: ' + error.message);
    }
}

function mostrarGanancias() {
    if (!datosGanancias) return;
    
    const ingresos = datosGanancias.ingresos || {};
    const egresos = datosGanancias.egresos || {};
    const resumen = datosGanancias.resumen || {};
    
    const totalIngresos = parseFloat(ingresos.total) || 0;
    const totalEgresos = parseFloat(egresos.total) || 0;
    
    document.getElementById('totalIngresos').textContent = formatearMoneda(totalIngresos);
    document.getElementById('totalEgresos').textContent = formatearMoneda(totalEgresos);
    document.getElementById('gananciaNeta').textContent = formatearMoneda(resumen.ganancia_neta || 0);
    document.getElementById('margenGanancia').textContent = (resumen.margen_porcentaje || 0).toFixed(1) + '%';
    
    mostrarDetalleIngresos();
    mostrarDetalleEgresos();
}

function mostrarDetalleIngresos() {
    const ingresos = datosGanancias.ingresos || {};
    const container = document.getElementById('detalleIngresos');
    
    let items = [];
    
    // Ventas
    if (ingresos.ventas) {
        const ventas = ingresos.ventas;
        const total = parseFloat(ventas.total_ventas || 0);
        if (total > 0) {
            items.push({
                concepto: 'Ventas (' + (ventas.numero_ventas || 0) + ')',
                monto: total
            });
        }
    }
    
    // Taller
    if (ingresos.taller) {
        const taller = ingresos.taller;
        const total = parseFloat(taller.total_reparaciones || 0);
        if (total > 0) {
            items.push({
                concepto: 'Trabajos de Taller (' + (taller.numero_trabajos || 0) + ')',
                monto: total
            });
        }
    }
    
    // Abonos
    if (ingresos.abonos) {
        const abonos = ingresos.abonos;
        const total = parseFloat(abonos.total_abonos || 0);
        if (total > 0) {
            items.push({
                concepto: 'Abonos a Crédito (' + (abonos.numero_abonos || 0) + ')',
                monto: total
            });
        }
    }
    
    if (items.length === 0) {
        container.innerHTML = '<p class="text-muted text-center">Sin ingresos en este período</p>';
        return;
    }
    
    let html = '';
    items.forEach(item => {
        html += '<div class="d-flex justify-content-between align-items-center mb-2">';
        html += '<span>' + escaparHTML(item.concepto) + '</span>';
        html += '<strong class="text-success">' + formatearMoneda(item.monto) + '</strong>';
        html += '</div>';
    });
    
    container.innerHTML = html;
}

function mostrarDetalleEgresos() {
    const egresos = datosGanancias.egresos || {};
    const container = document.getElementById('detalleEgresos');
    
    const detalle = egresos.detalle || [];
    
    if (detalle.length === 0) {
        container.innerHTML = '<p class="text-muted text-center">Sin egresos en este período</p>';
        return;
    }
    
    let html = '';
    detalle.forEach(item => {
        const concepto = item.tipo_movimiento || 'Egreso';
        const cantidad = item.cantidad || 0;
        const monto = parseFloat(item.monto_total || 0);
        
        html += '<div class="d-flex justify-content-between align-items-center mb-2">';
        html += '<span>' + escaparHTML(concepto) + ' (' + cantidad + ')</span>';
        html += '<strong class="text-danger">' + formatearMoneda(monto) + '</strong>';
        html += '</div>';
    });
    
    container.innerHTML = html;
}

async function cargarCuentasPorCobrar() {
    try {
        mostrarCargando();
        
        const res = await fetch('/joyeria-torre-fuerte/api/reportes/financiero.php?tipo=cuentas_por_cobrar');
        const data = await res.json();
        
        ocultarCargando();
        
        if (!data.success) {
            mostrarError(data.message || 'Error al cargar cuentas');
            return;
        }
        
        datosCuentas = data.data;
        mostrarCuentasPorCobrar();
        
    } catch (error) {
        ocultarCargando();
        console.error('Error:', error);
        mostrarError('Error: ' + error.message);
    }
}

function mostrarCuentasPorCobrar() {
    if (!datosCuentas) return;
    
    const resumen = datosCuentas.resumen || {};
    const cuentas = datosCuentas.cuentas || [];
    const vencidos = datosCuentas.creditos_vencidos || [];
    
    document.getElementById('totalCuentasPorCobrar').textContent = formatearMoneda(resumen.total_por_cobrar || 0);
    document.getElementById('totalClientesCredito').textContent = resumen.total_clientes || 0;
    document.getElementById('creditosVencidos').textContent = vencidos.length;
    
    mostrarTablaCuentas(cuentas);
    
    if (vencidos.length > 0) {
        mostrarCreditosVencidos(vencidos);
    }
}

function mostrarTablaCuentas(cuentas) {
    const tbody = document.getElementById('tablaCuentasPorCobrar');
    
    if (cuentas.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No hay cuentas por cobrar</td></tr>';
        return;
    }
    
    let html = '';
    cuentas.forEach(cuenta => {
        const saldo = parseFloat(cuenta.saldo) || 0;
        const badge = saldo > 0 ? '<span class="badge bg-warning">Pendiente</span>' : '<span class="badge bg-success">Pagado</span>';
        
        html += '<tr>';
        html += '<td>' + escaparHTML(cuenta.cliente_nombre) + '</td>';
        html += '<td>' + formatearMoneda(cuenta.total_credito) + '</td>';
        html += '<td>' + formatearMoneda(cuenta.abonado) + '</td>';
        html += '<td><strong>' + formatearMoneda(saldo) + '</strong></td>';
        html += '<td>' + formatearFecha(cuenta.ultima_compra) + '</td>';
        html += '<td>' + badge + '</td>';
        html += '</tr>';
    });
    
    tbody.innerHTML = html;
}

function mostrarCreditosVencidos(vencidos) {
    document.getElementById('cardCreditosVencidos').style.display = 'block';
    
    const tbody = document.getElementById('tablaCreditosVencidos');
    let html = '';
    
    vencidos.forEach(credito => {
        html += '<tr>';
        html += '<td>' + escaparHTML(credito.cliente_nombre) + '</td>';
        html += '<td><strong class="text-danger">' + formatearMoneda(credito.saldo) + '</strong></td>';
        html += '<td><span class="badge bg-danger">' + credito.dias_vencido + ' días</span></td>';
        html += '<td>' + formatearFecha(credito.fecha_vencimiento) + '</td>';
        html += '</tr>';
    });
    
    tbody.innerHTML = html;
}

// Event listener para pestañas
document.getElementById('tab-cuentas').addEventListener('click', function() {
    if (!datosCuentas) {
        cargarCuentasPorCobrar();
    }
});

document.addEventListener('DOMContentLoaded', function() {
    inicializarFechas();
    cargarGanancias();
});

console.log('✅ Reporte Financiero cargado correctamente');
</script>