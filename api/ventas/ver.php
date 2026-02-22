<?php
/**
 * ================================================
 * MÓDULO VENTAS - VER DETALLE DE VENTA
 * ================================================
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();

$venta_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$venta_id) {
    header('Location: lista.php');
    exit;
}

require_once '../../includes/header.php';
require_once '../../includes/navbar.php';
?>

<style>
/* Estilos para vista en pantalla */
.info-venta {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    border-radius: 10px;
    margin-bottom: 2rem;
}

.detalle-tabla th {
    background-color: #f8f9fa;
    font-weight: 600;
}

/* Estilos para impresión térmica 80mm */
@media print {
    body * {
        visibility: hidden;
    }
    
    #ticket-print, #ticket-print * {
        visibility: visible;
    }
    
    #ticket-print {
        position: absolute;
        left: 0;
        top: 0;
        width: 80mm;
        margin: 0;
        padding: 5mm;
        font-family: 'Courier New', monospace;
        font-size: 10pt;
    }
    
    .ticket-header {
        text-align: center;
        margin-bottom: 5mm;
        border-bottom: 2px dashed #000;
        padding-bottom: 3mm;
    }
    
    .ticket-logo {
        font-size: 14pt;
        font-weight: bold;
        margin-bottom: 2mm;
    }
    
    .ticket-section {
        margin: 3mm 0;
        font-size: 9pt;
    }
    
    .ticket-item {
        margin: 2mm 0;
    }
    
    .ticket-divider {
        border-top: 1px dashed #000;
        margin: 3mm 0;
    }
    
    .ticket-total {
        font-size: 12pt;
        font-weight: bold;
        text-align: right;
        margin-top: 3mm;
    }
    
    .ticket-footer {
        text-align: center;
        margin-top: 5mm;
        border-top: 2px dashed #000;
        padding-top: 3mm;
        font-size: 8pt;
    }
}
</style>

<div class="container-fluid px-4 py-4">
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-1"><i class="bi bi-receipt"></i> Detalle de Venta</h2>
            <p class="text-muted mb-0">Información completa de la transacción</p>
        </div>
        <div>
            <a href="lista.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver a Lista
            </a>
            <button type="button" class="btn btn-primary" onclick="imprimirTicket()">
                <i class="bi bi-printer"></i> Imprimir Ticket
            </button>
            <button type="button" class="btn btn-outline-primary" onclick="descargarPDF()">
                <i class="bi bi-file-pdf"></i> Descargar PDF
            </button>
            <button type="button" class="btn btn-danger" id="btnAnular" onclick="anularVenta()" style="display: none;">
                <i class="bi bi-x-circle"></i> Anular Venta
            </button>
        </div>
    </div>

    <hr class="border-warning border-2 opacity-75 mb-4">

    <!-- Información de la Venta -->
    <div class="info-venta shadow-lg" id="infoVenta">
        <div class="row">
            <div class="col-md-3">
                <h6 class="text-white-50 mb-1">Número de Venta</h6>
                <h3 class="mb-0" id="numeroVenta">-</h3>
            </div>
            <div class="col-md-3">
                <h6 class="text-white-50 mb-1">Fecha y Hora</h6>
                <h5 class="mb-0" id="fechaVenta">-</h5>
            </div>
            <div class="col-md-3">
                <h6 class="text-white-50 mb-1">Estado</h6>
                <h5 class="mb-0" id="estadoVenta">-</h5>
            </div>
            <div class="col-md-3">
                <h6 class="text-white-50 mb-1">Total</h6>
                <h3 class="mb-0" id="totalVenta">-</h3>
            </div>
        </div>
    </div>

    <div class="row">
        
        <!-- Panel Izquierdo -->
        <div class="col-lg-8">
            
            <!-- Detalles de Productos -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-box-seam"></i> Productos</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover detalle-tabla mb-0">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-end">Precio Unit.</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="tablaProductos">
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        <div class="spinner-border text-primary"></div>
                                        <p class="mt-2">Cargando...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Formas de Pago -->
            <div class="card shadow-sm mb-3" id="cardFormasPago" style="display: none;">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-credit-card"></i> Formas de Pago</h5>
                </div>
                <div class="card-body">
                    <div id="listaFormasPago"></div>
                </div>
            </div>

            <!-- Información de Crédito -->
            <div class="card shadow-sm mb-3" id="cardCredito" style="display: none;">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-calendar-week"></i> Información de Crédito</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Monto Total:</strong> <span id="creditoTotal">-</span></p>
                            <p class="mb-2"><strong>Saldo Pendiente:</strong> <span id="creditoSaldo" class="text-danger">-</span></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Cuotas:</strong> <span id="creditoCuotas">-</span></p>
                            <p class="mb-2"><strong>Cuota Semanal:</strong> <span id="creditoCuotaSemanal">-</span></p>
                            <p class="mb-2"><strong>Próximo Pago:</strong> <span id="creditoProximoPago">-</span></p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Panel Derecho -->
        <div class="col-lg-4">
            
            <!-- Información General -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Información General</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Sucursal:</strong><br><span id="sucursalNombre">-</span></p>
                    <p class="mb-2"><strong>Cliente:</strong><br><span id="clienteNombre">-</span></p>
                    <p class="mb-2"><strong>Vendedor:</strong><br><span id="vendedorNombre">-</span></p>
                    <p class="mb-2"><strong>Tipo de Venta:</strong><br><span id="tipoVenta">-</span></p>
                </div>
            </div>

            <!-- Resumen Totales -->
            <div class="card shadow-sm border-primary border-2">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-calculator"></i> Resumen</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <strong id="resumenSubtotal">Q 0.00</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Descuento:</span>
                        <strong id="resumenDescuento" class="text-danger">Q 0.00</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">TOTAL:</h4>
                        <h3 class="mb-0 text-primary" id="resumenTotal">Q 0.00</h3>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>

<!-- Ticket para impresión térmica (oculto en pantalla) -->
<div id="ticket-print" style="display: none;">
    <div class="ticket-header">
        <div class="ticket-logo">JOYERÍA TORRE FUERTE</div>
        <div id="ticket-sucursal" style="font-size: 9pt;"></div>
        <div id="ticket-fecha" style="font-size: 8pt;"></div>
    </div>
    
    <div class="ticket-section">
        <div><strong>VENTA #<span id="ticket-numero"></span></strong></div>
        <div id="ticket-cliente"></div>
        <div id="ticket-vendedor"></div>
    </div>
    
    <div class="ticket-divider"></div>
    
    <div class="ticket-section" id="ticket-productos"></div>
    
    <div class="ticket-divider"></div>
    
    <div class="ticket-section">
        <div style="display: flex; justify-content: space-between;">
            <span>Subtotal:</span>
            <span id="ticket-subtotal"></span>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <span>Descuento:</span>
            <span id="ticket-descuento"></span>
        </div>
        <div class="ticket-total">
            <div style="display: flex; justify-content: space-between;">
                <span>TOTAL:</span>
                <span id="ticket-total"></span>
            </div>
        </div>
    </div>
    
    <div class="ticket-section" id="ticket-formas-pago" style="display: none;">
        <div><strong>Formas de Pago:</strong></div>
        <div id="ticket-formas-pago-lista"></div>
    </div>
    
    <div class="ticket-footer">
        <div>¡Gracias por su compra!</div>
        <div>www.joyeriatf.com</div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>

<script src="../../assets/js/vendors/sweetalert2/sweetalert2.all.min.js"></script>
<script src="../../assets/js/common.js"></script>
<script src="../../assets/js/api-client.js"></script>

<script>
const ventaId = <?php echo $venta_id; ?>;
let ventaData = null;

// Cargar detalle al iniciar
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔍 Cargando venta ID:', ventaId);
    cargarDetalleVenta();
});

async function cargarDetalleVenta() {
    try {
        mostrarCargando();
        
        const res = await fetch('/api/ventas/detalle.php?id=' + ventaId);
        const data = await res.json();
        
        console.log('📦 Detalle venta:', data);
        
        ocultarCargando();
        
        if (!data.success) {
            mostrarError(data.message || 'Error al cargar venta');
            setTimeout(() => {
                window.location.href = 'lista.php';
            }, 2000);
            return;
        }
        
        ventaData = data.data;
        mostrarDetalleVenta(ventaData);
        
    } catch (error) {
        ocultarCargando();
        console.error('Error:', error);
        mostrarError('Error: ' + error.message);
    }
}

function mostrarDetalleVenta(venta) {
    console.log('🖼️ === MOSTRAR DETALLE VENTA ===');
    console.log('📦 Venta completa:', venta);
    console.log('📦 venta.productos:', venta.productos);
    console.log('📦 Tipo de productos:', typeof venta.productos);
    console.log('📦 Es array?:', Array.isArray(venta.productos));
    
    if (venta.productos) {
        console.log('📦 Cantidad:', venta.productos.length);
        if (venta.productos.length > 0) {
            console.log('📦 Primer producto:', venta.productos[0]);
        }
    } else {
        console.error('❌ venta.productos es null/undefined');
    }
    
    console.log('🖼️ Mostrando venta:', venta);
    
    // Header principal
    document.getElementById('numeroVenta').textContent = venta.numero_venta;
    
    // Fecha con múltiples intentos de formato
    const fechaTexto = venta.fecha_venta || (venta.fecha + ' ' + venta.hora) || '-';
    console.log('📅 Fecha para mostrar:', fechaTexto);
    document.getElementById('fechaVenta').textContent = formatearFechaHora(fechaTexto);
    
    // Estado con badge
    const estado = venta.estado;
    let badgeEstado = '';
    if (estado === 'completada') {
        badgeEstado = '<span class="badge bg-success fs-6">Completada</span>';
    } else if (estado === 'anulada') {
        badgeEstado = '<span class="badge bg-danger fs-6">Anulada</span>';
    } else {
        badgeEstado = '<span class="badge bg-warning fs-6">Pendiente</span>';
    }
    document.getElementById('estadoVenta').innerHTML = badgeEstado;
    document.getElementById('totalVenta').textContent = formatearMoneda(venta.total);
    
    // Información general
    document.getElementById('sucursalNombre').textContent = venta.sucursal_nombre || '-';
    document.getElementById('clienteNombre').textContent = venta.cliente_nombre || 'Público General';
    document.getElementById('vendedorNombre').textContent = venta.vendedor_nombre || '-';
    
    // Tipo de venta
    let tipoTexto = venta.tipo_venta.charAt(0).toUpperCase() + venta.tipo_venta.slice(1);
    document.getElementById('tipoVenta').textContent = tipoTexto;
    
    // Productos
    mostrarProductos(venta.productos || []);
    
    // Resumen totales
    document.getElementById('resumenSubtotal').textContent = formatearMoneda(venta.subtotal);
    document.getElementById('resumenDescuento').textContent = formatearMoneda(venta.descuento);
    document.getElementById('resumenTotal').textContent = formatearMoneda(venta.total);
    
    // Formas de pago (solo si es venta normal)
    if (venta.tipo_venta === 'normal' && venta.formas_pago && venta.formas_pago.length > 0) {
        mostrarFormasPago(venta.formas_pago);
    }
    
    // Información de crédito
    if (venta.tipo_venta === 'credito' && venta.credito) {
        mostrarCredito(venta.credito);
    }
    
    // Botón anular (solo si está completada)
    if (venta.estado === 'completada') {
        document.getElementById('btnAnular').style.display = 'inline-block';
    }
    
    // Preparar ticket para impresión
    prepararTicket(venta);
}

function mostrarProductos(productos) {
    console.log('📦 mostrarProductos() llamado con:', productos);
    console.log('📦 Tipo:', typeof productos);
    console.log('📦 Es array?:', Array.isArray(productos));
    
    const tbody = document.getElementById('tablaProductos');
    
    if (!productos) {
        console.error('❌ productos es null/undefined');
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-danger py-3">Error: No se recibieron productos</td></tr>';
        return;
    }
    
    // Convertir a array si no lo es
    let productosArray = productos;
    if (!Array.isArray(productos)) {
        console.log('⚠️ Convirtiendo productos a array...');
        productosArray = Object.values(productos);
    }
    
    console.log('📦 Array final:', productosArray);
    console.log('📦 Cantidad de items:', productosArray.length);
    
    if (productosArray.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">No hay productos</td></tr>';
        return;
    }
    
    let html = '';
    productosArray.forEach((p, index) => {
        console.log(`  Producto ${index}:`, p);
        
        html += '<tr>';
        html += '<td><strong>' + escaparHTML(p.producto_nombre || p.nombre || '-') + '</strong><br>';
        html += '<small class="text-muted">SKU: ' + escaparHTML(p.producto_codigo || p.codigo || '-') + '</small></td>';
        html += '<td class="text-center"><span class="badge bg-primary">' + (p.cantidad || 0) + '</span></td>';
        html += '<td class="text-end">' + formatearMoneda(p.precio_unitario || 0) + '</td>';
        html += '<td class="text-end"><strong>' + formatearMoneda(p.subtotal || 0) + '</strong></td>';
        html += '</tr>';
    });
    
    console.log('✅ HTML generado, actualizando tabla...');
    tbody.innerHTML = html;
}

function mostrarFormasPago(formasPago) {
    const card = document.getElementById('cardFormasPago');
    const lista = document.getElementById('listaFormasPago');
    
    card.style.display = 'block';
    
    let html = '<div class="row g-2">';
    formasPago.forEach(fp => {
        const icono = obtenerIconoFormaPago(fp.forma_pago);
        const nombre = obtenerNombreFormaPago(fp.forma_pago);
        
        html += '<div class="col-md-6">';
        html += '<div class="border rounded p-2 bg-light">';
        html += '<div class="d-flex justify-content-between align-items-center">';
        html += '<span>' + icono + ' ' + nombre + '</span>';
        html += '<strong>' + formatearMoneda(fp.monto) + '</strong>';
        html += '</div>';
        if (fp.referencia) {
            html += '<small class="text-muted">Ref: ' + escaparHTML(fp.referencia) + '</small>';
        }
        html += '</div>';
        html += '</div>';
    });
    html += '</div>';
    
    lista.innerHTML = html;
}

function mostrarCredito(credito) {
    const card = document.getElementById('cardCredito');
    
    card.style.display = 'block';
    
    document.getElementById('creditoTotal').textContent = formatearMoneda(credito.monto_total);
    document.getElementById('creditoSaldo').textContent = formatearMoneda(credito.saldo_pendiente);
    document.getElementById('creditoCuotas').textContent = credito.cuotas_pagadas + ' / ' + credito.numero_cuotas;
    document.getElementById('creditoCuotaSemanal').textContent = formatearMoneda(credito.cuota_semanal);
    document.getElementById('creditoProximoPago').textContent = formatearFecha(credito.fecha_proximo_pago);
}

function prepararTicket(venta) {
    // Información básica
    document.getElementById('ticket-sucursal').textContent = venta.sucursal_nombre || '';
    document.getElementById('ticket-fecha').textContent = formatearFechaHora(venta.fecha_venta);
    document.getElementById('ticket-numero').textContent = venta.numero_venta;
    document.getElementById('ticket-cliente').textContent = 'Cliente: ' + (venta.cliente_nombre || 'Público General');
    document.getElementById('ticket-vendedor').textContent = 'Vendedor: ' + (venta.vendedor_nombre || '-');
    
    // Productos
    let productosHtml = '';
    (venta.productos || []).forEach(p => {
        productosHtml += '<div class="ticket-item">';
        productosHtml += '<div>' + escaparHTML(p.producto_nombre) + '</div>';
        productosHtml += '<div style="display: flex; justify-content: space-between;">';
        productosHtml += '<span>' + p.cantidad + ' x ' + formatearMoneda(p.precio_unitario) + '</span>';
        productosHtml += '<span>' + formatearMoneda(p.subtotal) + '</span>';
        productosHtml += '</div>';
        productosHtml += '</div>';
    });
    document.getElementById('ticket-productos').innerHTML = productosHtml;
    
    // Totales
    document.getElementById('ticket-subtotal').textContent = formatearMoneda(venta.subtotal);
    document.getElementById('ticket-descuento').textContent = formatearMoneda(venta.descuento);
    document.getElementById('ticket-total').textContent = formatearMoneda(venta.total);
    
    // Formas de pago
    if (venta.tipo_venta === 'normal' && venta.formas_pago && venta.formas_pago.length > 0) {
        const divFormasPago = document.getElementById('ticket-formas-pago');
        const listaFormasPago = document.getElementById('ticket-formas-pago-lista');
        
        divFormasPago.style.display = 'block';
        
        let fpHtml = '';
        venta.formas_pago.forEach(fp => {
            fpHtml += '<div style="display: flex; justify-content: space-between;">';
            fpHtml += '<span>' + obtenerNombreFormaPago(fp.forma_pago) + ':</span>';
            fpHtml += '<span>' + formatearMoneda(fp.monto) + '</span>';
            fpHtml += '</div>';
        });
        listaFormasPago.innerHTML = fpHtml;
    }
}

function imprimirTicket() {
    console.log('🖨️ Imprimiendo ticket...');
    
    // Mostrar el ticket temporalmente
    document.getElementById('ticket-print').style.display = 'block';
    
    // Imprimir
    window.print();
    
    // Ocultar el ticket después de imprimir
    setTimeout(() => {
        document.getElementById('ticket-print').style.display = 'none';
    }, 100);
}

function descargarPDF() {
    mostrarError('Función de descarga PDF en desarrollo');
    // TODO: Implementar generación de PDF con una librería como jsPDF
}

async function anularVenta() {
    if (!ventaData || ventaData.estado !== 'completada') {
        mostrarError('Solo se pueden anular ventas completadas');
        return;
    }
    
    const { value: motivo } = await Swal.fire({
        title: '¿Anular esta venta?',
        html: '<strong>' + ventaData.numero_venta + '</strong><br>Total: ' + formatearMoneda(ventaData.total),
        input: 'textarea',
        inputLabel: 'Motivo de anulación',
        inputPlaceholder: 'Escriba el motivo...',
        showCancelButton: true,
        confirmButtonText: 'Sí, anular',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
        inputValidator: (value) => {
            if (!value) {
                return 'Debe ingresar un motivo';
            }
        }
    });
    
    if (!motivo) return;
    
    try {
        mostrarCargando();
        
        const res = await fetch('/api/ventas/anular.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                venta_id: ventaId,
                motivo: motivo
            })
        });
        
        const resultado = await res.json();
        
        ocultarCargando();
        
        if (resultado.success) {
            await mostrarExito('Venta anulada exitosamente');
            cargarDetalleVenta(); // Recargar
        } else {
            mostrarError(resultado.message || 'Error al anular venta');
        }
        
    } catch (error) {
        ocultarCargando();
        console.error('Error:', error);
        mostrarError('Error: ' + error.message);
    }
}

// Funciones auxiliares
function obtenerIconoFormaPago(forma) {
    const iconos = {
        'efectivo': '💵',
        'tarjeta_debito': '💳',
        'tarjeta_credito': '💳',
        'transferencia': '🏦',
        'cheque': '📝'
    };
    return iconos[forma] || '💰';
}

function obtenerNombreFormaPago(forma) {
    const nombres = {
        'efectivo': 'Efectivo',
        'tarjeta_debito': 'Tarjeta Débito',
        'tarjeta_credito': 'Tarjeta Crédito',
        'transferencia': 'Transferencia',
        'cheque': 'Cheque'
    };
    return nombres[forma] || forma;
}

function formatearFechaHora(fecha) {
    if (!fecha) return '-';
    const d = new Date(fecha);
    return d.toLocaleString('es-GT', { 
        year: 'numeric', 
        month: '2-digit', 
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function formatearFecha(fecha) {
    if (!fecha) return '-';
    const d = new Date(fecha);
    return d.toLocaleDateString('es-GT', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric'
    });
}

console.log('✅ Vista detalle venta cargada');
</script>