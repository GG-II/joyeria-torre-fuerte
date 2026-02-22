<?php
/**
 * ================================================
 * MÓDULO REPORTES - TALLER
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
            <h2 class="mb-1"><i class="bi bi-tools"></i> Reporte de Taller</h2>
            <p class="text-muted mb-0">Trabajos completados y análisis de rendimiento</p>
        </div>
        <a href="dashboard.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Dashboard
        </a>
    </div>

    <hr class="border-warning border-2 opacity-75 mb-4">

    <!-- Filtros -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Fecha Inicio</label>
                    <input type="date" class="form-control" id="fechaInicio">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha Fin</label>
                    <input type="date" class="form-control" id="fechaFin">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Estado</label>
                    <select class="form-select" id="filtroEstado">
                        <option value="">Todos</option>
                        <option value="entregado" selected>Entregados</option>
                        <option value="en_proceso">En Proceso</option>
                        <option value="pendiente">Pendientes</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button class="btn btn-primary w-100" onclick="cargarReporteTaller()">
                        <i class="bi bi-search"></i> Generar Reporte
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- KPIs -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-start border-success border-4 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="text-muted mb-0">Trabajos Completados</h6>
                        <i class="bi bi-check-circle fs-3 text-success"></i>
                    </div>
                    <h3 class="mb-0" id="trabajosCompletados">0</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-start border-primary border-4 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="text-muted mb-0">Ingresos Totales</h6>
                        <i class="bi bi-cash-coin fs-3 text-primary"></i>
                    </div>
                    <h3 class="mb-0" id="ingresosTotales">Q 0.00</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-start border-info border-4 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="text-muted mb-0">Ticket Promedio</h6>
                        <i class="bi bi-calculator fs-3 text-info"></i>
                    </div>
                    <h3 class="mb-0" id="ticketPromedio">Q 0.00</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-start border-warning border-4 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="text-muted mb-0">Tiempo Promedio</h6>
                        <i class="bi bi-clock fs-3 text-warning"></i>
                    </div>
                    <h3 class="mb-0" id="tiempoPromedio">0 días</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficas -->
    <div class="row g-4 mb-4">
        
        <!-- Trabajos por Tipo -->
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Trabajos por Tipo</h5>
                </div>
                <div class="card-body">
                    <div id="contenedorTipos"></div>
                </div>
            </div>
        </div>

        <!-- Trabajos por Orfebres -->
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-people"></i> Trabajos por Orfebre</h5>
                </div>
                <div class="card-body">
                    <div id="contenedorOrfebres"></div>
                </div>
            </div>
        </div>

    </div>

    <!-- Tabla de Trabajos -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-list"></i> Detalle de Trabajos</h5>
            <div class="input-group" style="max-width: 300px;">
                <span class="input-group-text bg-white">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" class="form-control" id="buscarTrabajo" 
                       placeholder="Buscar...">
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Código</th>
                            <th>Cliente</th>
                            <th>Tipo Trabajo</th>
                            <th>Orfebre</th>
                            <th>Fecha Ingreso</th>
                            <th>Fecha Entrega</th>
                            <th>Días</th>
                            <th>Precio</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody id="tablaTrabajos">
                        <tr><td colspan="9" class="text-center text-muted">Seleccione un período</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php require_once '../../includes/footer.php'; ?>

<script src="../../assets/js/vendors/sweetalert2/sweetalert2.all.min.js"></script>
<script src="../../assets/js/common.js"></script>
<script src="../../assets/js/api-client.js"></script>

<script>
let datosTaller = null;
let trabajosFiltrados = [];

// Inicializar fechas (último mes)
function inicializarFechas() {
    const hoy = new Date();
    const haceUnMes = new Date();
    haceUnMes.setMonth(hoy.getMonth() - 1);
    
    document.getElementById('fechaInicio').valueAsDate = haceUnMes;
    document.getElementById('fechaFin').valueAsDate = hoy;
}

async function cargarReporteTaller() {
    try {
        mostrarCargando();
        
        const fechaInicio = document.getElementById('fechaInicio').value;
        const fechaFin = document.getElementById('fechaFin').value;
        const estado = document.getElementById('filtroEstado').value;
        
        if (!fechaInicio || !fechaFin) {
            mostrarError('Seleccione las fechas');
            ocultarCargando();
            return;
        }
        
        // Construir URL con filtros
        let url = '/api/taller/listar.php?fecha_inicio=' + fechaInicio + '&fecha_fin=' + fechaFin;
        if (estado) {
            url += '&estado=' + estado;
        }
        
        const res = await fetch(url);
        const data = await res.json();
        
        ocultarCargando();
        
        if (!data.success) {
            mostrarError(data.message || 'Error al cargar reporte');
            return;
        }
        
        datosTaller = data.data || [];
        console.log('📊 Datos de Taller:', datosTaller.length > 0 ? datosTaller[0] : 'Sin datos');
        trabajosFiltrados = [...datosTaller];
        
        mostrarReporteTaller();
        
    } catch (error) {
        ocultarCargando();
        console.error('Error:', error);
        mostrarError('Error: ' + error.message);
    }
}

function mostrarReporteTaller() {
    if (!datosTaller || datosTaller.length === 0) {
        mostrarMensajeVacio();
        return;
    }
    
    calcularKPIs();
    mostrarTiposTrabajo();
    mostrarOrfebres();
    mostrarTablaTrabajos();
}

function calcularKPIs() {
    const trabajos = datosTaller;
    
    const completados = trabajos.filter(t => t.estado === 'entregado').length;
    
    let totalIngresos = 0;
    let totalDias = 0;
    let countDias = 0;
    
    trabajos.forEach(t => {
        const precio = parseFloat(t.precio_total) || 0;
        totalIngresos += precio;
        
        if (t.fecha_recepcion && t.fecha_entrega_real) {
            const inicio = new Date(t.fecha_recepcion);
            const fin = new Date(t.fecha_entrega_real);
            const dias = Math.ceil((fin - inicio) / (1000 * 60 * 60 * 24));
            if (dias >= 0) {
                totalDias += dias;
                countDias++;
            }
        }
    });
    
    const ticketPromedio = trabajos.length > 0 ? totalIngresos / trabajos.length : 0;
    const tiempoPromedio = countDias > 0 ? totalDias / countDias : 0;
    
    document.getElementById('trabajosCompletados').textContent = formatearNumero(completados);
    document.getElementById('ingresosTotales').textContent = formatearMoneda(totalIngresos);
    document.getElementById('ticketPromedio').textContent = formatearMoneda(ticketPromedio);
    document.getElementById('tiempoPromedio').textContent = Math.round(tiempoPromedio) + ' días';
}

function mostrarTiposTrabajo() {
    const tipos = {};
    
    datosTaller.forEach(t => {
        const tipoRaw = t.tipo_trabajo || 'Sin especificar';
        const tipo = tipoRaw.charAt(0).toUpperCase() + tipoRaw.slice(1).toLowerCase();
        
        if (!tipos[tipo]) {
            tipos[tipo] = { cantidad: 0, total: 0 };
        }
        tipos[tipo].cantidad++;
        tipos[tipo].total += parseFloat(t.precio_total) || 0;
    });
    
    const container = document.getElementById('contenedorTipos');
    
    if (Object.keys(tipos).length === 0) {
        container.innerHTML = '<p class="text-muted text-center">Sin datos</p>';
        return;
    }
    
    let html = '';
    Object.keys(tipos).sort((a, b) => tipos[b].cantidad - tipos[a].cantidad).forEach(tipo => {
        const data = tipos[tipo];
        const porcentaje = (data.cantidad / datosTaller.length * 100).toFixed(1);
        
        html += '<div class="mb-3">';
        html += '<div class="d-flex justify-content-between mb-1">';
        html += '<span><strong>' + escaparHTML(tipo) + '</strong> (' + data.cantidad + ')</span>';
        html += '<span>' + formatearMoneda(data.total) + '</span>';
        html += '</div>';
        html += '<div class="progress">';
        html += '<div class="progress-bar bg-primary" style="width: ' + porcentaje + '%">' + porcentaje + '%</div>';
        html += '</div>';
        html += '</div>';
    });
    
    container.innerHTML = html;
}

function mostrarOrfebres() {
    const orfebres = {};
    
    datosTaller.forEach(t => {
        const orfebreRaw = t.empleado_actual_nombre || 'Sin asignar';
        const orfebre = orfebreRaw.split(' ').map(palabra => 
            palabra.charAt(0).toUpperCase() + palabra.slice(1).toLowerCase()
        ).join(' ');
        
        if (!orfebres[orfebre]) {
            orfebres[orfebre] = { cantidad: 0, total: 0 };
        }
        orfebres[orfebre].cantidad++;
        orfebres[orfebre].total += parseFloat(t.precio_total) || 0;
    });
    
    const container = document.getElementById('contenedorOrfebres');
    
    if (Object.keys(orfebres).length === 0) {
        container.innerHTML = '<p class="text-muted text-center">Sin datos</p>';
        return;
    }
    
    let html = '';
    Object.keys(orfebres).sort((a, b) => orfebres[b].cantidad - orfebres[a].cantidad).forEach(orfebre => {
        const data = orfebres[orfebre];
        const porcentaje = (data.cantidad / datosTaller.length * 100).toFixed(1);
        
        html += '<div class="mb-3">';
        html += '<div class="d-flex justify-content-between mb-1">';
        html += '<span><strong>' + escaparHTML(orfebre) + '</strong> (' + data.cantidad + ')</span>';
        html += '<span>' + formatearMoneda(data.total) + '</span>';
        html += '</div>';
        html += '<div class="progress">';
        html += '<div class="progress-bar bg-success" style="width: ' + porcentaje + '%">' + porcentaje + '%</div>';
        html += '</div>';
        html += '</div>';
    });
    
    container.innerHTML = html;
}
function mostrarTablaTrabajos() {
    const tbody = document.getElementById('tablaTrabajos');
    
    if (trabajosFiltrados.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted">No hay trabajos en este período</td></tr>';
        return;
    }
    
    let html = '';
    
    trabajosFiltrados.forEach(t => {
        let estadoBadge = '';
        if (t.estado === 'entregado') {
            estadoBadge = '<span class="badge bg-success">Entregado</span>';
        } else if (t.estado === 'en_proceso') {
            estadoBadge = '<span class="badge bg-info">En Proceso</span>';
        } else {
            estadoBadge = '<span class="badge bg-warning">Pendiente</span>';
        }
        
        let dias = '-';
        if (t.fecha_recepcion && t.fecha_entrega_real) {
            const inicio = new Date(t.fecha_recepcion);
            const fin = new Date(t.fecha_entrega_real);
            dias = Math.ceil((fin - inicio) / (1000 * 60 * 60 * 24));
        }
        
        const tipoCapitalizado = t.tipo_trabajo ? t.tipo_trabajo.charAt(0).toUpperCase() + t.tipo_trabajo.slice(1) : '-';
        
        html += '<tr>';
        html += '<td><code>' + escaparHTML(t.codigo) + '</code></td>';
        html += '<td>' + escaparHTML(t.cliente_nombre) + '</td>';
        html += '<td>' + escaparHTML(tipoCapitalizado) + '</td>';
        html += '<td>' + escaparHTML(t.empleado_actual_nombre || '-') + '</td>';
        html += '<td>' + formatearFecha(t.fecha_recepcion) + '</td>';
        html += '<td>' + (t.fecha_entrega_real ? formatearFecha(t.fecha_entrega_real) : '-') + '</td>';
        html += '<td>' + dias + '</td>';
        html += '<td><strong>' + formatearMoneda(t.precio_total) + '</strong></td>';
        html += '<td>' + estadoBadge + '</td>';
        html += '</tr>';
    });
    
    tbody.innerHTML = html;
}

function filtrarTrabajos() {
    const busqueda = document.getElementById('buscarTrabajo').value.toLowerCase().trim();
    
    if (!busqueda) {
        trabajosFiltrados = [...datosTaller];
    } else {
        trabajosFiltrados = datosTaller.filter(t => {
            const codigo = (t.codigo || '').toLowerCase();
            const cliente = (t.cliente_nombre || '').toLowerCase();
            const tipo = (t.tipo_trabajo || '').toLowerCase();
            const orfebre = (t.empleado_actual_nombre || '').toLowerCase();
            
            return codigo.includes(busqueda) || 
                   cliente.includes(busqueda) || 
                   tipo.includes(busqueda) || 
                   orfebre.includes(busqueda);
        });
    }
    
    mostrarTablaTrabajos();
}

function mostrarMensajeVacio() {
    document.getElementById('trabajosCompletados').textContent = '0';
    document.getElementById('ingresosTotales').textContent = 'Q 0.00';
    document.getElementById('ticketPromedio').textContent = 'Q 0.00';
    document.getElementById('tiempoPromedio').textContent = '0 días';
    
    document.getElementById('contenedorTipos').innerHTML = '<p class="text-muted text-center">Sin datos</p>';
    document.getElementById('contenedorOrfebres').innerHTML = '<p class="text-muted text-center">Sin datos</p>';
    
    document.getElementById('tablaTrabajos').innerHTML = '<tr><td colspan="9" class="text-center text-muted">No hay trabajos en este período</td></tr>';
}

// Event Listeners
document.getElementById('buscarTrabajo').addEventListener('input', filtrarTrabajos);

document.addEventListener('DOMContentLoaded', function() {
    inicializarFechas();
    cargarReporteTaller();
});

console.log('✅ Reporte de Taller cargado correctamente');
</script>