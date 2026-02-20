<?php
/**
 * ================================================
 * MÓDULO VENTAS - LISTA
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
            <h2 class="mb-1"><i class="bi bi-cart-check"></i> Ventas</h2>
            <p class="text-muted mb-0">Gestión de ventas realizadas</p>
        </div>
        <a href="nueva.php" class="btn btn-success btn-lg">
            <i class="bi bi-plus-circle"></i> Nueva Venta
        </a>
    </div>

    <hr class="border-warning border-2 opacity-75 mb-4">

    <!-- KPIs -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-start border-success border-4 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Ventas (últimos 7 días)</h6>
                    <h3 class="mb-0 text-success" id="ventasHoy">0</h3>
                    <small class="text-muted" id="montoHoy">Q 0.00</small>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-start border-primary border-4 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Ticket Promedio</h6>
                    <h3 class="mb-0 text-primary" id="ticketPromedio">Q 0.00</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Fecha Inicio</label>
                    <input type="date" class="form-control" id="fechaInicio">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Fecha Fin</label>
                    <input type="date" class="form-control" id="fechaFin">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sucursal</label>
                    <select class="form-select" id="filtroSucursal">
                        <option value="">Todas</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tipo Venta</label>
                    <select class="form-select" id="filtroTipoVenta">
                        <option value="">Todas</option>
                        <option value="normal">Normal</option>
                        <option value="credito">Crédito</option>
                        <option value="apartado">Apartado</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Estado</label>
                    <select class="form-select" id="filtroEstado">
                        <option value="">Todos</option>
                        <option value="completada">Completada</option>
                        <option value="anulada">Anulada</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100" onclick="aplicarFiltros()">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-list"></i> Listado de Ventas</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha/Hora</th>
                            <th>Número</th>
                            <th>Cliente</th>
                            <th>Vendedor</th>
                            <th>Tipo</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaVentas">
                        <tr><td colspan="8" class="text-center text-muted">Cargando...</td></tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div id="paginacion" class="d-flex justify-content-between align-items-center mt-3">
                <div id="infoPaginacion"></div>
                <div id="botonesPaginacion"></div>
            </div>
        </div>
    </div>

</div>

<!-- Modal Anular Venta -->
<div class="modal fade" id="modalAnular" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-x-circle"></i> Anular Venta</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                
                <!-- Info de la Venta -->
                <div class="alert alert-warning">
                    <strong>Venta:</strong> <span id="anularNumero"></span><br>
                    <strong>Cliente:</strong> <span id="anularCliente"></span><br>
                    <strong>Fecha:</strong> <span id="anularFecha"></span><br>
                    <strong>Total:</strong> <span id="anularTotal" class="fs-5"></span>
                </div>

                <!-- Productos que se devolverán -->
                <h6 class="mb-2"><i class="bi bi-box-arrow-in-down"></i> Productos que se devolverán al inventario:</h6>
                <div class="table-responsive mb-3">
                    <table class="table table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Precio Unit.</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="anularProductos"></tbody>
                    </table>
                </div>

                <!-- Motivo -->
                <div class="mb-3">
                    <label for="motivoAnulacion" class="form-label">
                        Motivo de Anulación <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="motivoAnulacion" rows="3" required
                              placeholder="Describa el motivo de la anulación..."></textarea>
                </div>

                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Advertencia:</strong> Esta acción NO se puede deshacer. 
                    Los productos volverán al inventario y se registrará un egreso en caja (si aplica).
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="confirmarAnulacion()">
                    <i class="bi bi-check-circle"></i> Confirmar Anulación
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>

<script src="../../assets/js/vendors/sweetalert2/sweetalert2.all.min.js"></script>
<script src="../../assets/js/common.js"></script>
<script src="../../assets/js/api-client.js"></script>

<script>
let ventasData = [];
let paginaActual = 1;
let totalPaginas = 1;
let ventaAAnular = null;

// Inicializar fechas
function inicializarFechas() {
    const hoy = new Date();
    const hace7dias = new Date();
    hace7dias.setDate(hoy.getDate() - 7);
    
    document.getElementById('fechaInicio').valueAsDate = hace7dias;
    document.getElementById('fechaFin').valueAsDate = hoy;
}

async function cargarVentas() {
    try {
        console.log('📋 Cargando lista de ventas...');
        mostrarCargando();
        
        const fechaInicio = document.getElementById('fechaInicio').value;
        const fechaFin = document.getElementById('fechaFin').value;
        const sucursal = document.getElementById('filtroSucursal').value;
        const tipoVenta = document.getElementById('filtroTipoVenta').value;
        const estado = document.getElementById('filtroEstado').value;
        
        let url = '/joyeria-torre-fuerte/api/ventas/listar.php?pagina=' + paginaActual + '&por_pagina=20';
        
        if (fechaInicio) url += '&fecha_inicio=' + fechaInicio;
        if (fechaFin) url += '&fecha_fin=' + fechaFin;
        if (sucursal) url += '&sucursal_id=' + sucursal;
        if (tipoVenta) url += '&tipo_venta=' + tipoVenta;
        if (estado) url += '&estado=' + estado;
        
        console.log('🌐 URL lista:', url);
        
        const res = await fetch(url);
        const data = await res.json();
        
        console.log('📦 Respuesta lista:', data);
        
        ocultarCargando();
        
        if (!data.success) {
            mostrarError(data.message || 'Error al cargar ventas');
            return;
        }
        
        ventasData = data.data.ventas || [];
        totalPaginas = data.data.total_paginas || 1;
        
        console.log('✅ Ventas cargadas:', ventasData.length);
        
        mostrarVentas();
        mostrarPaginacion(data.data);
        
    } catch (error) {
        ocultarCargando();
        console.error('Error:', error);
        mostrarError('Error: ' + error.message);
    }
}

function mostrarVentas() {
    const tbody = document.getElementById('tablaVentas');
    
    if (ventasData.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No hay ventas</td></tr>';
        return;
    }
    
    let html = '';
    
    ventasData.forEach(venta => {
        const fechaHora = formatearFechaHora(venta.fecha);
        
        let tipoBadge = '';
        if (venta.tipo_venta === 'normal') tipoBadge = '<span class="badge bg-success">Normal</span>';
        else if (venta.tipo_venta === 'credito') tipoBadge = '<span class="badge bg-warning">Crédito</span>';
        else if (venta.tipo_venta === 'apartado') tipoBadge = '<span class="badge bg-info">Apartado</span>';
        
        let estadoBadge = '';
        if (venta.estado === 'completada') estadoBadge = '<span class="badge bg-success">Completada</span>';
        else if (venta.estado === 'anulada') estadoBadge = '<span class="badge bg-danger">Anulada</span>';
        else estadoBadge = '<span class="badge bg-secondary">' + escaparHTML(venta.estado) + '</span>';
        
        const btnVer = '<a href="ver.php?id=' + venta.id + '" class="btn btn-sm btn-outline-primary" title="Ver"><i class="bi bi-eye"></i></a>';
        
        let btnAnular = '';
        if (venta.estado === 'completada') {
            btnAnular = '<button class="btn btn-sm btn-outline-danger" onclick="prepararAnulacion(' + venta.id + ')" title="Anular"><i class="bi bi-x-circle"></i></button>';
        }
        
        html += '<tr>';
        html += '<td><small>' + fechaHora + '</small></td>';
        html += '<td><code>' + escaparHTML(venta.numero_venta) + '</code></td>';
        html += '<td>' + escaparHTML(venta.cliente_nombre || 'Público General') + '</td>';
        html += '<td>' + escaparHTML(venta.vendedor_nombre || '-') + '</td>';
        html += '<td>' + tipoBadge + '</td>';
        html += '<td><strong>' + formatearMoneda(venta.total) + '</strong></td>';
        html += '<td>' + estadoBadge + '</td>';
        html += '<td class="text-center"><div class="btn-group btn-group-sm">' + btnVer + btnAnular + '</div></td>';
        html += '</tr>';
    });
    
    tbody.innerHTML = html;
}

function mostrarPaginacion(data) {
    const info = document.getElementById('infoPaginacion');
    const botones = document.getElementById('botonesPaginacion');
    
    info.innerHTML = 'Mostrando ' + data.ventas.length + ' de ' + data.total + ' ventas';
    
    if (data.total_paginas <= 1) {
        botones.innerHTML = '';
        return;
    }
    
    let html = '<nav><ul class="pagination pagination-sm mb-0">';
    
    // Botón anterior
    if (paginaActual > 1) {
        html += '<li class="page-item"><a class="page-link" href="#" onclick="cambiarPagina(' + (paginaActual - 1) + ')">Anterior</a></li>';
    }
    
    // Números de página
    for (let i = 1; i <= data.total_paginas; i++) {
        if (i === paginaActual) {
            html += '<li class="page-item active"><span class="page-link">' + i + '</span></li>';
        } else if (i === 1 || i === data.total_paginas || (i >= paginaActual - 2 && i <= paginaActual + 2)) {
            html += '<li class="page-item"><a class="page-link" href="#" onclick="cambiarPagina(' + i + ')">' + i + '</a></li>';
        } else if (i === paginaActual - 3 || i === paginaActual + 3) {
            html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    // Botón siguiente
    if (paginaActual < data.total_paginas) {
        html += '<li class="page-item"><a class="page-link" href="#" onclick="cambiarPagina(' + (paginaActual + 1) + ')">Siguiente</a></li>';
    }
    
    html += '</ul></nav>';
    botones.innerHTML = html;
}

function cambiarPagina(pagina) {
    paginaActual = pagina;
    cargarVentas();
}

function aplicarFiltros() {
    paginaActual = 1;
    cargarVentas();
}

async function cargarEstadisticasDelDia() {
    try {
        console.log('📊 Cargando estadísticas de últimos 7 días...');
        
        // Calcular fecha hace 7 días
        const hoy = new Date();
        const hace7dias = new Date();
        hace7dias.setDate(hoy.getDate() - 7);
        
        const fechaInicio = hace7dias.toISOString().split('T')[0];
        const fechaFin = hoy.toISOString().split('T')[0];
        
        console.log('📅 Rango de fechas:', fechaInicio, 'a', fechaFin);
        
        const url = '/joyeria-torre-fuerte/api/ventas/reportes.php?tipo=rango&fecha_inicio=' + fechaInicio + '&fecha_fin=' + fechaFin;
        console.log('🌐 URL:', url);
        
        const res = await fetch(url);
        
        // Capturar texto de respuesta primero
        const textoRespuesta = await res.text();
        console.log('📝 Texto crudo del servidor:', textoRespuesta);
        
        // Intentar parsear JSON
        let data;
        try {
            data = JSON.parse(textoRespuesta);
        } catch (parseError) {
            console.error('❌ Error parseando JSON:', parseError);
            console.error('❌ Respuesta recibida:', textoRespuesta.substring(0, 500));
            throw new Error('El servidor devolvió HTML en vez de JSON. Ver consola para detalles.');
        }
        
        console.log('📦 Respuesta API reportes:', data);
        
        if (!data.success) {
            console.error('❌ Error en API:', data.message);
            // Si no hay datos, dejar en 0
            return;
        }
        
        const stats = data.data || {};
        console.log('📊 Estadísticas:', stats);
        
        // Extraer datos del reporte
        const ventasHoy = stats.total_ventas || 0;
        const montoHoy = stats.total_monto || stats.monto_total || 0;
        const ventasCredito = stats.ventas_credito || 0;
        const montoCredito = stats.monto_credito || 0;
        const ventasAnuladas = stats.ventas_anuladas || 0;
        const ticketPromedio = stats.ticket_promedio || (ventasHoy > 0 ? montoHoy / ventasHoy : 0);
        
        console.log('✅ Actualizando KPIs:', {
            ventasHoy,
            montoHoy,
            ticketPromedio,
            ventasCredito,
            montoCredito,
            ventasAnuladas
        });
        
        // Actualizar KPIs
        document.getElementById('ventasHoy').textContent = ventasHoy;
        document.getElementById('montoHoy').textContent = formatearMoneda(montoHoy);
        document.getElementById('ticketPromedio').textContent = formatearMoneda(ticketPromedio);
        
    } catch (error) {
        console.error('💥 Error al cargar estadísticas:', error);
        // Dejar los KPIs en 0 si hay error
    }
}

async function prepararAnulacion(ventaId) {
    console.log('🎯 prepararAnulacion() llamado con ID:', ventaId);
    
    try {
        mostrarCargando();
        
        const res = await fetch('/joyeria-torre-fuerte/api/ventas/detalle.php?id=' + ventaId);
        const data = await res.json();
        
        console.log('📦 Detalle de venta para anular:', data);
        
        ocultarCargando();
        
        if (!data.success) {
            console.error('❌ Error al cargar detalle:', data.message);
            mostrarError(data.message || 'Error al cargar detalle');
            return;
        }
        
        ventaAAnular = data.data;
        console.log('✅ ventaAAnular asignada:', ventaAAnular);
        
        // Pequeño delay para asegurar que el DOM esté listo
        setTimeout(() => {
            mostrarModalAnulacion();
        }, 100);
        
    } catch (error) {
        ocultarCargando();
        console.error('Error:', error);
        mostrarError('Error: ' + error.message);
    }
}

function mostrarModalAnulacion() {
    console.log('🎭 mostrarModalAnulacion() llamado');
    console.log('🎭 ventaAAnular:', ventaAAnular);
    
    if (!ventaAAnular) {
        console.error('❌ ventaAAnular es null/undefined');
        return;
    }
    
    // Función helper para obtener elemento con fallback
    const obtenerElemento = (id) => {
        let elem = document.getElementById(id);
        if (!elem) {
            // Intentar con querySelector
            elem = document.querySelector('#' + id);
        }
        return elem;
    };
    
    // Verificar que todos los elementos existan
    const elementos = {
        numero: obtenerElemento('anularNumero'),
        cliente: obtenerElemento('anularCliente'),
        fecha: obtenerElemento('anularFecha'),
        total: obtenerElemento('anularTotal'),
        productos: obtenerElemento('anularProductos'),
        motivo: obtenerElemento('motivoAnulacion'),
        modal: obtenerElemento('modalAnular')
    };
    
    console.log('🔍 Elementos del modal:', elementos);
    console.log('🔍 DOM readyState:', document.readyState);
    
    // Verificar que ninguno sea null
    for (const [key, elemento] of Object.entries(elementos)) {
        if (!elemento) {
            console.error(`❌ Elemento '${key}' (id: ${key === 'numero' ? 'anularNumero' : key}) no encontrado en el DOM`);
            console.error('🔍 Intentando buscar en todo el documento...');
            
            // Debug: Listar todos los IDs en el documento
            const todosLosIds = Array.from(document.querySelectorAll('[id]')).map(el => el.id);
            console.log('📋 Todos los IDs en el documento:', todosLosIds);
            
            mostrarError(`Error: Falta elemento en el modal. Recargue la página.`);
            return;
        }
    }
    
    elementos.numero.textContent = ventaAAnular.numero_venta || '-';
    elementos.cliente.textContent = ventaAAnular.cliente_nombre || 'Público General';
    
    // La fecha viene como "fecha" y "hora" separados
    const fechaHora = ventaAAnular.fecha && ventaAAnular.hora 
        ? ventaAAnular.fecha + ' ' + ventaAAnular.hora 
        : (ventaAAnular.fecha || '-');
    elementos.fecha.textContent = formatearFechaHora(fechaHora);
    elementos.total.textContent = formatearMoneda(ventaAAnular.total || 0);
    
    let html = '';
    
    // El modelo devuelve 'detalles' no 'productos'
    const productos = ventaAAnular.detalles || ventaAAnular.productos || [];
    
    productos.forEach(d => {
        html += '<tr>';
        html += '<td>' + escaparHTML(d.producto_nombre || d.nombre || '-') + '</td>';
        html += '<td>' + (d.cantidad || 0) + '</td>';
        html += '<td>' + formatearMoneda(d.precio_unitario || 0) + '</td>';
        html += '<td><strong>' + formatearMoneda(d.subtotal || 0) + '</strong></td>';
        html += '</tr>';
    });
    
    elementos.productos.innerHTML = html;
    elementos.motivo.value = '';
    
    console.log('✅ Modal listo, mostrando...');
    const modal = new bootstrap.Modal(elementos.modal);
    modal.show();
}

async function confirmarAnulacion() {
    console.log('✅ confirmarAnulacion() llamado');
    
    const motivo = document.getElementById('motivoAnulacion').value.trim();
    
    console.log('📝 Motivo ingresado:', motivo);
    console.log('📦 ventaAAnular actual:', ventaAAnular);
    
    if (!motivo) {
        mostrarError('Debe ingresar el motivo de anulación');
        return;
    }
    
    if (!ventaAAnular) {
        mostrarError('No hay venta seleccionada');
        return;
    }
    
    const confirmacion = await confirmarAccion(
        '¿Anular venta ' + ventaAAnular.numero_venta + '?',
        'Esta acción NO se puede deshacer'
    );
    
    if (!confirmacion) return;
    
    try {
        mostrarCargando();
        
        const datos = {
            id: ventaAAnular.id,
            motivo: motivo
        };
        
        console.log('📤 Datos a enviar:', datos);
        console.log('📤 Venta completa:', ventaAAnular);
        console.log('📤 JSON stringified:', JSON.stringify(datos));
        
        const res = await fetch('/joyeria-torre-fuerte/api/ventas/anular.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datos)
        });
        
        console.log('📡 Response status:', res.status);
        
        const textoRespuesta = await res.text();
        console.log('📝 Texto crudo respuesta:', textoRespuesta);
        
        let resultado;
        try {
            resultado = JSON.parse(textoRespuesta);
        } catch (e) {
            console.error('❌ Error parseando JSON:', e);
            throw new Error('Respuesta inválida del servidor');
        }
        
        console.log('📦 Respuesta anular:', resultado);
        
        ocultarCargando();
        
        if (resultado.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalAnular'));
            if (modal) modal.hide();
            
            await mostrarExito(resultado.message || 'Venta anulada exitosamente');
            ventaAAnular = null;
            cargarVentas();
            cargarEstadisticasDelDia(); // Recargar estadísticas
        } else {
            mostrarError(resultado.message || 'Error al anular venta');
        }
        
    } catch (error) {
        ocultarCargando();
        console.error('Error:', error);
        mostrarError('Error: ' + error.message);
    }
}

async function cargarSucursales() {
    try {
        const res = await fetch('/joyeria-torre-fuerte/api/sucursales/listar.php?activo=1');
        const data = await res.json();
        
        if (!data.success) return;
        
        const select = document.getElementById('filtroSucursal');
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
    console.log('🚀 Inicializando lista de ventas...');
    inicializarFechas();
    cargarSucursales();
    cargarEstadisticasDelDia(); // Cargar KPIs del día
    cargarVentas();
});

console.log('✅ Lista de Ventas cargada correctamente');
console.log('Stats del día');
</script>