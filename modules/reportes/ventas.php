<?php
/**
 * ================================================
 * MÓDULO REPORTES - VENTAS
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
            <h2 class="mb-1"><i class="bi bi-graph-up-arrow"></i> Reporte de Ventas</h2>
            <p class="text-muted mb-0">Análisis de ventas por período</p>
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
    <ul class="nav nav-tabs mb-4" id="tabsVentas" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-diario" data-bs-toggle="tab" 
                    data-bs-target="#panelDiario" type="button">
                <i class="bi bi-calendar-day"></i> Ventas Diarias
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-mensual" data-bs-toggle="tab" 
                    data-bs-target="#panelMensual" type="button">
                <i class="bi bi-calendar-month"></i> Ventas Mensuales
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-vendedor" data-bs-toggle="tab" 
                    data-bs-target="#panelVendedor" type="button">
                <i class="bi bi-person-badge"></i> Por Vendedor
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-sucursal" data-bs-toggle="tab" 
                    data-bs-target="#panelSucursal" type="button">
                <i class="bi bi-building"></i> Por Sucursal
            </button>
        </li>
    </ul>

    <div class="tab-content">
        
        <!-- PANEL VENTAS DIARIAS -->
        <div class="tab-pane fade show active" id="panelDiario">
            
            <!-- Filtros -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Fecha</label>
                            <input type="date" class="form-control" id="fechaDiario" 
                                   value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Sucursal</label>
                            <select class="form-select" id="sucursalDiario">
                                <option value="">Todas</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button class="btn btn-primary w-100" onclick="cargarVentasDiarias()">
                                <i class="bi bi-search"></i> Generar Reporte
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPIs Diario -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-start border-success border-4 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Total Ventas</h6>
                            <h3 class="mb-0 text-success" id="totalVentasDiario">Q 0.00</h3>
                            <small class="text-muted" id="cantidadVentasDiario">0 ventas</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-start border-info border-4 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Ticket Promedio</h6>
                            <h3 class="mb-0 text-info" id="ticketPromedioDiario">Q 0.00</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-start border-primary border-4 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Ventas Contado</h6>
                            <h3 class="mb-0 text-primary" id="ventasContadoDiario">Q 0.00</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-start border-warning border-4 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Ventas Crédito</h6>
                            <h3 class="mb-0 text-warning" id="ventasCreditoDiario">Q 0.00</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla Ventas Diarias -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-list"></i> Detalle de Ventas</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Hora</th>
                                    <th>Ticket</th>
                                    <th>Cliente</th>
                                    <th>Vendedor</th>
                                    <th>Tipo Pago</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody id="tablaVentasDiarias">
                                <tr><td colspan="6" class="text-center text-muted">Seleccione una fecha</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        <!-- PANEL VENTAS MENSUALES -->
        <div class="tab-pane fade" id="panelMensual">
            
            <!-- Filtros -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Mes</label>
                            <select class="form-select" id="mesMensual">
                                <option value="1">Enero</option>
                                <option value="2">Febrero</option>
                                <option value="3">Marzo</option>
                                <option value="4">Abril</option>
                                <option value="5">Mayo</option>
                                <option value="6">Junio</option>
                                <option value="7">Julio</option>
                                <option value="8">Agosto</option>
                                <option value="9">Septiembre</option>
                                <option value="10">Octubre</option>
                                <option value="11">Noviembre</option>
                                <option value="12">Diciembre</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Año</label>
                            <input type="number" class="form-control" id="anioMensual" 
                                   value="<?php echo date('Y'); ?>" min="2020" max="2030">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button class="btn btn-primary w-100" onclick="cargarVentasMensuales()">
                                <i class="bi bi-search"></i> Generar Reporte
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPIs Mensual -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-start border-success border-4 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Total del Mes</h6>
                            <h3 class="mb-0 text-success" id="totalVentasMensual">Q 0.00</h3>
                            <small class="text-muted" id="cantidadVentasMensual">0 ventas</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-start border-info border-4 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Promedio Diario</h6>
                            <h3 class="mb-0 text-info" id="promedioDiarioMensual">Q 0.00</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-start border-primary border-4 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Mejor Día</h6>
                            <h3 class="mb-0 text-primary" id="mejorDiaMensual">-</h3>
                            <small class="text-muted" id="mejorDiaMontoMensual">Q 0.00</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráfica Mensual -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Ventas por Día</h5>
                </div>
                <div class="card-body">
                    <div id="grafica MensualPlaceholder" class="text-center text-muted py-5">
                        Seleccione un mes para ver la gráfica
                    </div>
                </div>
            </div>

        </div>

        <!-- PANEL POR VENDEDOR -->
        <div class="tab-pane fade" id="panelVendedor">
            
            <!-- Filtros -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Fecha Inicio</label>
                            <input type="date" class="form-control" id="fechaInicioVendedor">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fecha Fin</label>
                            <input type="date" class="form-control" id="fechaFinVendedor">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button class="btn btn-primary w-100" onclick="cargarVentasPorVendedor()">
                                <i class="bi bi-search"></i> Generar Reporte
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla Vendedores -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-people"></i> Ventas por Vendedor</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Vendedor</th>
                                    <th>Cantidad Ventas</th>
                                    <th>Total Vendido</th>
                                    <th>Ticket Promedio</th>
                                    <th>Participación %</th>
                                </tr>
                            </thead>
                            <tbody id="tablaVendedores">
                                <tr><td colspan="5" class="text-center text-muted">Seleccione un período</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        <!-- PANEL POR SUCURSAL -->
        <div class="tab-pane fade" id="panelSucursal">
            
            <!-- Filtros -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Fecha Inicio</label>
                            <input type="date" class="form-control" id="fechaInicioSucursal">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fecha Fin</label>
                            <input type="date" class="form-control" id="fechaFinSucursal">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button class="btn btn-primary w-100" onclick="cargarVentasPorSucursal()">
                                <i class="bi bi-search"></i> Generar Reporte
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla Sucursales -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-shop"></i> Ventas por Sucursal</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Sucursal</th>
                                    <th>Cantidad Ventas</th>
                                    <th>Total Vendido</th>
                                    <th>Ticket Promedio</th>
                                    <th>Participación %</th>
                                </tr>
                            </thead>
                            <tbody id="tablaSucursales">
                                <tr><td colspan="5" class="text-center text-muted">Seleccione un período</td></tr>
                            </tbody>
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
// Inicializar fechas
function inicializarFechas() {
    const hoy = new Date();
    const hace7dias = new Date();
    hace7dias.setDate(hoy.getDate() - 7);
    
    const mesActual = hoy.getMonth() + 1;
    document.getElementById('mesMensual').value = mesActual;
    
    document.getElementById('fechaInicioVendedor').valueAsDate = hace7dias;
    document.getElementById('fechaFinVendedor').valueAsDate = hoy;
    
    document.getElementById('fechaInicioSucursal').valueAsDate = hace7dias;
    document.getElementById('fechaFinSucursal').valueAsDate = hoy;
}

async function cargarVentasDiarias() {
    try {
        mostrarCargando();
        
        const fecha = document.getElementById('fechaDiario').value;
        const sucursalId = document.getElementById('sucursalDiario').value;
        
        let url = '/api/reportes/ventas.php?tipo=diario&fecha=' + fecha;
        if (sucursalId) url += '&sucursal_id=' + sucursalId;
        
        const res = await fetch(url);
        const data = await res.json();
        
        ocultarCargando();
        
        if (!data.success) {
            mostrarError(data.message || 'Error al cargar ventas');
            return;
        }
        
        mostrarVentasDiarias(data.data);
        
    } catch (error) {
        ocultarCargando();
        console.error('Error:', error);
        mostrarError('Error: ' + error.message);
    }
}

function mostrarVentasDiarias(datos) {
    const totales = datos.totales || {};
    const ventas = datos.ventas || [];
    
    document.getElementById('totalVentasDiario').textContent = formatearMoneda(totales.monto_total || 0);
    document.getElementById('cantidadVentasDiario').textContent = (totales.total_ventas || 0) + ' ventas';
    document.getElementById('ticketPromedioDiario').textContent = formatearMoneda(totales.ticket_promedio || 0);
    document.getElementById('ventasContadoDiario').textContent = formatearMoneda(totales.ventas_contado || 0);
    document.getElementById('ventasCreditoDiario').textContent = formatearMoneda(totales.ventas_credito || 0);
    
    const tbody = document.getElementById('tablaVentasDiarias');
    
    if (ventas.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No hay ventas en esta fecha</td></tr>';
        return;
    }
    
    let html = '';
    ventas.forEach(venta => {
        const hora = venta.fecha_venta ? new Date(venta.fecha_venta).toLocaleTimeString('es-GT', {hour: '2-digit', minute: '2-digit'}) : '-';
        const tipoPago = venta.tipo_pago === 'contado' ? '<span class="badge bg-success">Contado</span>' : '<span class="badge bg-warning">Crédito</span>';
        
        html += '<tr>';
        html += '<td>' + hora + '</td>';
        html += '<td><code>' + escaparHTML(venta.codigo || venta.id) + '</code></td>';
        html += '<td>' + escaparHTML(venta.cliente_nombre || 'Público General') + '</td>';
        html += '<td>' + escaparHTML(venta.vendedor_nombre || '-') + '</td>';
        html += '<td>' + tipoPago + '</td>';
        html += '<td><strong>' + formatearMoneda(venta.total) + '</strong></td>';
        html += '</tr>';
    });
    
    tbody.innerHTML = html;
}

async function cargarVentasMensuales() {
    try {
        mostrarCargando();
        
        const mes = document.getElementById('mesMensual').value;
        const anio = document.getElementById('anioMensual').value;
        
        const url = '/api/reportes/ventas.php?tipo=mensual&mes=' + mes + '&año=' + anio;
        
        const res = await fetch(url);
        const data = await res.json();
        
        ocultarCargando();
        
        if (!data.success) {
            mostrarError(data.message || 'Error al cargar ventas');
            return;
        }
        
        mostrarVentasMensuales(data.data);
        
    } catch (error) {
        ocultarCargando();
        console.error('Error:', error);
        mostrarError('Error: ' + error.message);
    }
}

function mostrarVentasMensuales(datos) {
    const totales = datos.totales || {};
    
    document.getElementById('totalVentasMensual').textContent = formatearMoneda(totales.monto_total || 0);
    document.getElementById('cantidadVentasMensual').textContent = (totales.total_ventas || 0) + ' ventas';
    document.getElementById('promedioDiarioMensual').textContent = formatearMoneda(totales.promedio_diario || 0);
    
    if (datos.mejor_dia) {
        document.getElementById('mejorDiaMensual').textContent = formatearFecha(datos.mejor_dia.fecha);
        document.getElementById('mejorDiaMontoMensual').textContent = formatearMoneda(datos.mejor_dia.monto);
    }
}

async function cargarVentasPorVendedor() {
    try {
        mostrarCargando();
        
        const fechaInicio = document.getElementById('fechaInicioVendedor').value;
        const fechaFin = document.getElementById('fechaFinVendedor').value;
        
        const url = '/api/reportes/ventas.php?tipo=vendedor&fecha_inicio=' + fechaInicio + '&fecha_fin=' + fechaFin;
        
        const res = await fetch(url);
        const data = await res.json();
        
        ocultarCargando();
        
        if (!data.success) {
            mostrarError(data.message || 'Error al cargar ventas');
            return;
        }
        
        mostrarVentasPorVendedor(data.data);
        
    } catch (error) {
        ocultarCargando();
        console.error('Error:', error);
        mostrarError('Error: ' + error.message);
    }
}

function mostrarVentasPorVendedor(datos) {
    const vendedores = datos.vendedores || [];
    const totalGeneral = datos.total_general || 0;
    
    const tbody = document.getElementById('tablaVendedores');
    
    if (vendedores.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No hay datos</td></tr>';
        return;
    }
    
    let html = '';
    vendedores.forEach(v => {
        const participacion = totalGeneral > 0 ? (v.total_vendido / totalGeneral * 100) : 0;
        
        html += '<tr>';
        html += '<td><strong>' + escaparHTML(v.vendedor_nombre) + '</strong></td>';
        html += '<td>' + formatearNumero(v.cantidad_ventas) + '</td>';
        html += '<td><strong>' + formatearMoneda(v.total_vendido) + '</strong></td>';
        html += '<td>' + formatearMoneda(v.ticket_promedio) + '</td>';
        html += '<td><span class="badge bg-primary">' + participacion.toFixed(1) + '%</span></td>';
        html += '</tr>';
    });
    
    tbody.innerHTML = html;
}

async function cargarVentasPorSucursal() {
    try {
        mostrarCargando();
        
        const fechaInicio = document.getElementById('fechaInicioSucursal').value;
        const fechaFin = document.getElementById('fechaFinSucursal').value;
        
        const url = '/api/reportes/ventas.php?tipo=sucursal&fecha_inicio=' + fechaInicio + '&fecha_fin=' + fechaFin;
        
        const res = await fetch(url);
        const data = await res.json();
        
        ocultarCargando();
        
        if (!data.success) {
            mostrarError(data.message || 'Error al cargar ventas');
            return;
        }
        
        mostrarVentasPorSucursal(data.data);
        
    } catch (error) {
        ocultarCargando();
        console.error('Error:', error);
        mostrarError('Error: ' + error.message);
    }
}

function mostrarVentasPorSucursal(datos) {
    const sucursales = datos.sucursales || [];
    const totalGeneral = datos.total_general || 0;
    
    const tbody = document.getElementById('tablaSucursales');
    
    if (sucursales.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No hay datos</td></tr>';
        return;
    }
    
    let html = '';
    sucursales.forEach(s => {
        const participacion = totalGeneral > 0 ? (s.total_vendido / totalGeneral * 100) : 0;
        
        html += '<tr>';
        html += '<td><strong>' + escaparHTML(s.sucursal_nombre) + '</strong></td>';
        html += '<td>' + formatearNumero(s.cantidad_ventas) + '</td>';
        html += '<td><strong>' + formatearMoneda(s.total_vendido) + '</strong></td>';
        html += '<td>' + formatearMoneda(s.ticket_promedio) + '</td>';
        html += '<td><span class="badge bg-primary">' + participacion.toFixed(1) + '%</span></td>';
        html += '</tr>';
    });
    
    tbody.innerHTML = html;
}

async function cargarSucursales() {
    try {
        const res = await fetch('/api/sucursales/listar.php?activo=1');
        const data = await res.json();
        
        if (!data.success) return;
        
        const select = document.getElementById('sucursalDiario');
        const sucursales = data.data || [];
        
        sucursales.forEach(s => {
            const option = document.createElement('option');
            option.value = s.id;
            option.textContent = s.nombre;
            select.appendChild(option);
        });
    } catch (error) {
        console.error('Error:', error);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    inicializarFechas();
    cargarSucursales();
    cargarVentasDiarias();
});

console.log('✅ Reporte de Ventas cargado correctamente');
</script>