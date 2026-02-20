<?php
/**
 * ================================================
 * MÓDULO VENTAS - NUEVA VENTA (POS)
 * ================================================
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();

// Obtener datos del usuario actual
$usuario_sesion = $_SESSION['usuario'] ?? null;
$sucursal_usuario = $usuario_sesion['sucursal_id'] ?? null;
$rol_usuario = $usuario_sesion['rol'] ?? '';
$es_admin = in_array($rol_usuario, ['administrador', 'dueño']);

require_once '../../includes/header.php';
require_once '../../includes/navbar.php';
?>

<style>
/* Estilos específicos para POS */
.producto-card {
    cursor: pointer;
    transition: all 0.2s;
    border: 2px solid transparent;
}
.producto-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border-color: #1e3a8a;
}
.producto-card.sin-stock {
    opacity: 0.5;
    cursor: not-allowed;
}
.carrito-item {
    border-bottom: 1px solid #dee2e6;
    padding: 12px 0;
}
.carrito-item:last-child {
    border-bottom: none;
}
.input-cantidad {
    width: 70px;
    text-align: center;
}
#buscarProducto {
    font-size: 1.1rem;
    height: 50px;
}
.total-venta {
    font-size: 2.5rem;
    font-weight: bold;
    color: #1e3a8a;
}
.disabled-overlay {
    opacity: 0.5;
    pointer-events: none;
}
</style>

<div class="container-fluid px-4 py-4">
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-1"><i class="bi bi-cart-plus"></i> Nueva Venta (POS)</h2>
            <p class="text-muted mb-0">Sucursal: <strong id="nombreSucursalActual">Seleccione una sucursal</strong></p>
        </div>
        <div>
            <a href="lista.php" class="btn btn-secondary me-2">
                <i class="bi bi-list"></i> Ver Ventas
            </a>
            <button type="button" class="btn btn-danger" onclick="limpiarVenta()">
                <i class="bi bi-x-circle"></i> Limpiar Todo
            </button>
        </div>
    </div>

    <hr class="border-warning border-2 opacity-75 mb-4">

    <div class="row">
        
        <!-- PANEL IZQUIERDO: Búsqueda y Carrito -->
        <div class="col-lg-7">
            
            <!-- Selección de Sucursal -->
            <div class="card shadow-sm mb-3 border-primary">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="bi bi-building"></i> Selección de Sucursal</h6>
                </div>
                <div class="card-body">
                    <select class="form-select form-select-lg" id="sucursalVenta" onchange="cambiarSucursal()" required>
                        <option value="">Seleccione una sucursal...</option>
                    </select>
                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i> 
                        <?php if ($es_admin): ?>
                            Seleccione la sucursal donde realizará la venta
                        <?php else: ?>
                            Sucursal asignada según su usuario
                        <?php endif; ?>
                    </small>
                </div>
            </div>
            
            <!-- Búsqueda con Escáner -->
            <div class="card shadow-sm mb-3" id="panelBusqueda">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="bi bi-upc-scan"></i> Búsqueda de Productos</h6>
                </div>
                <div class="card-body">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-success text-white">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control" id="buscarProducto" 
                               placeholder="Primero seleccione una sucursal..." 
                               autocomplete="off" disabled>
                        <button type="button" class="btn btn-success" id="btnBuscar" onclick="buscarProductos()" disabled>
                            Buscar
                        </button>
                    </div>
                    <small class="text-muted">
                        <i class="bi bi-lightning-charge"></i> Escanee código de barras o busque por nombre/SKU y presione ENTER
                    </small>
                </div>
            </div>

            <!-- Resultados de Búsqueda -->
            <div id="resultadosBusqueda" class="mb-3" style="display: none;">
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="bi bi-list-ul"></i> Resultados (<span id="cantidadResultados">0</span>)</h6>
                        <button type="button" class="btn btn-sm btn-light" onclick="cerrarResultados()">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                    <div class="card-body p-2" style="max-height: 400px; overflow-y: auto;">
                        <div id="listaResultados" class="row g-2"></div>
                    </div>
                </div>
            </div>

            <!-- Carrito -->
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="bi bi-cart-fill"></i> Carrito de Compras 
                        (<span id="cantidadItems">0</span> items - <span id="totalUnidades">0</span> unidades)
                    </h5>
                </div>
                <div class="card-body" style="min-height: 300px; max-height: 500px; overflow-y: auto;">
                    <div id="carritoVacio" class="text-center text-muted py-5">
                        <i class="bi bi-cart-x" style="font-size: 4rem; opacity: 0.3;"></i>
                        <p class="mt-3 mb-1 fs-5">El carrito está vacío</p>
                        <small>Seleccione una sucursal y busque productos para agregar</small>
                    </div>
                    <div id="carritoItems" style="display: none;"></div>
                </div>
            </div>

        </div>

        <!-- PANEL DERECHO: Información y Pago -->
        <div class="col-lg-5">
            
            <!-- Información de Venta -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="bi bi-info-circle"></i> Información de la Venta</h6>
                </div>
                <div class="card-body">
                    
                    <!-- Cliente -->
                    <div class="mb-3">
                        <label class="form-label">Cliente</label>
                        
                        <!-- Cliente seleccionado -->
                        <div id="clienteSeleccionadoPos" style="display: none;">
                            <div class="card border-primary shadow-sm">
                                <div class="card-body p-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong id="clienteNombrePos"></strong><br>
                                            <small class="text-muted" id="clienteTelefonoPos"></small>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="limpiarClientePos()">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Botones de búsqueda -->
                        <div id="botonesClientePos">
                            <button type="button" class="btn btn-outline-primary w-100 mb-2" 
                                    data-bs-toggle="modal" data-bs-target="#modalBuscarClientePos">
                                <i class="bi bi-search"></i> Buscar Cliente
                            </button>
                            <button type="button" class="btn btn-outline-success w-100" onclick="irANuevoCliente()">
                                <i class="bi bi-person-plus"></i> Crear Nuevo Cliente
                            </button>
                        </div>
                        
                        <input type="hidden" id="clienteVenta" value="">
                        <small class="text-muted">Dejar vacío para "Público General"</small>
                    </div>

                    <!-- Tipo de Venta -->
                    <div class="mb-3">
                        <label class="form-label">Tipo de Venta <span class="text-danger">*</span></label>
                        <select class="form-select" id="tipoVenta" onchange="cambiarTipoVenta()">
                            <option value="normal">🟢 Normal - Pago completo ahora</option>
                            <option value="credito">🟡 A Crédito - Pago en cuotas</option>
                            <option value="apartado">🔵 Apartado - Reserva con anticipo</option>
                        </select>
                    </div>

                    <!-- Cuotas (solo crédito) -->
                    <div class="mb-3" id="divCuotas" style="display: none;">
                        <label class="form-label">Número de Cuotas Semanales</label>
                        <input type="number" class="form-control" id="numeroCuotas" value="4" min="1" max="52">
                        <small class="text-muted">Las cuotas se cobrarán cada 7 días</small>
                    </div>

                    <!-- Descuento -->
                    <div class="mb-3">
                        <label class="form-label">Descuento</label>
                        <div class="input-group">
                            <span class="input-group-text">Q</span>
                            <input type="number" class="form-control" id="descuentoVenta" 
                                   value="0" min="0" step="0.01" onchange="calcularTotales()">
                        </div>
                    </div>

                </div>
            </div>

            <!-- Resumen Totales -->
            <div class="card shadow-sm mb-3 border-primary border-2">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal:</span>
                        <strong id="subtotalVenta" class="fs-5">Q 0.00</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Descuento:</span>
                        <strong id="descuentoTotal" class="text-danger">- Q 0.00</strong>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">TOTAL:</h4>
                        <h2 class="total-venta mb-0" id="totalVenta">Q 0.00</h2>
                    </div>
                </div>
            </div>

            <!-- Formas de Pago (solo venta normal) -->
            <div class="card shadow-sm mb-3" id="divFormasPago">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="bi bi-credit-card"></i> Formas de Pago</h6>
                </div>
                <div class="card-body">
                    <div id="formasPagoContainer">
                        <p class="text-muted text-center">No hay formas de pago agregadas</p>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary w-100 mt-2" onclick="agregarFormaPago()">
                        <i class="bi bi-plus-circle"></i> Agregar Forma de Pago
                    </button>
                    
                    <div class="mt-3 p-3 bg-light rounded">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Total a pagar:</span>
                            <strong id="totalAPagar" class="text-primary">Q 0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Total pagado:</span>
                            <strong id="totalPagado">Q 0.00</strong>
                        </div>
                        <hr class="my-1">
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold" id="labelFaltaPagar">Falta por pagar:</span>
                            <strong id="faltaPagar" class="text-danger fs-5">Q 0.00</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botón Procesar Venta -->
            <button type="button" class="btn btn-success btn-lg w-100 py-3" onclick="procesarVenta(event)" id="btnProcesar" disabled>
                <i class="bi bi-check-circle-fill"></i> PROCESAR VENTA
            </button>
            <small class="text-muted d-block text-center mt-2" id="mensajeBotonProcesar">
                Agregue productos al carrito para continuar
            </small>

        </div>

    </div>

</div>

<!-- Modal Buscar Cliente -->
<div class="modal fade" id="modalBuscarClientePos" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-search"></i> Buscar Cliente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control form-control-lg" 
                           id="buscarClienteInputPos" 
                           placeholder="Escriba nombre o teléfono..." autocomplete="off">
                </div>
                
                <div id="resultadosClientesPos" style="max-height: 400px; overflow-y: auto;">
                    <p class="text-center text-muted">Escriba al menos 2 caracteres para buscar</p>
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
// ============================================
// VARIABLES GLOBALES
// ============================================
let carrito = [];
let formasPago = [];
let contadorFormasPago = 0;
let sucursalActual = null;
let productosEncontrados = []; // Para guardar productos de búsqueda

// ============================================
// INICIALIZACIÓN
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 POS Inicializado');
    cargarSucursales();
    configurarEventos();
    cambiarTipoVenta();
});

function configurarEventos() {
    // ENTER en búsqueda de productos
    document.getElementById('buscarProducto').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            buscarProductos();
        }
    });
    
    // Búsqueda de clientes en modal
    document.getElementById('buscarClienteInputPos').addEventListener('input', buscarClientesConDelay);
}

// ============================================
// CARGA DE SUCURSALES
// ============================================
async function cargarSucursales() {
    try {
        const res = await fetch('/joyeria-torre-fuerte/api/sucursales/listar.php?activo=1');
        const data = await res.json();
        
        if (!data.success) {
            mostrarError('Error al cargar sucursales');
            return;
        }
        
        const select = document.getElementById('sucursalVenta');
        let sucursales = data.data || [];
        
        if (!Array.isArray(sucursales)) {
            sucursales = Object.values(sucursales);
        }
        
        sucursales.forEach(s => {
            const option = document.createElement('option');
            option.value = s.id;
            option.textContent = s.nombre;
            option.dataset.nombre = s.nombre;
            select.appendChild(option);
        });
        
        // Pre-seleccionar y bloquear para usuarios no admin
        <?php if ($sucursal_usuario): ?>
        select.value = <?php echo $sucursal_usuario; ?>;
        <?php if (!$es_admin): ?>
        select.disabled = true;
        <?php endif; ?>
        cambiarSucursal();
        <?php endif; ?>
        
    } catch (error) {
        console.error('Error:', error);
        mostrarError('Error al cargar sucursales');
    }
}

// ============================================
// GESTIÓN DE SUCURSAL
// ============================================
function cambiarSucursal() {
    const select = document.getElementById('sucursalVenta');
    const sucursalId = select.value;
    
    if (!sucursalId) {
        deshabilitarBusqueda();
        return;
    }
    
    sucursalActual = {
        id: parseInt(sucursalId),
        nombre: select.options[select.selectedIndex].dataset.nombre || select.options[select.selectedIndex].text
    };
    
    document.getElementById('nombreSucursalActual').textContent = sucursalActual.nombre;
    habilitarBusqueda();
    
    if (carrito.length > 0) {
        Swal.fire({
            title: '¿Limpiar carrito?',
            text: 'Al cambiar de sucursal se limpiará el carrito actual',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, limpiar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                limpiarCarrito();
            }
        });
    }
}

function habilitarBusqueda() {
    const inputBuscar = document.getElementById('buscarProducto');
    const btnBuscar = document.getElementById('btnBuscar');
    
    inputBuscar.disabled = false;
    inputBuscar.placeholder = 'Escanear código de barras o buscar producto...';
    btnBuscar.disabled = false;
    
    document.getElementById('panelBusqueda').classList.remove('disabled-overlay');
}

function deshabilitarBusqueda() {
    const inputBuscar = document.getElementById('buscarProducto');
    const btnBuscar = document.getElementById('btnBuscar');
    
    inputBuscar.disabled = true;
    inputBuscar.placeholder = 'Primero seleccione una sucursal...';
    inputBuscar.value = '';
    btnBuscar.disabled = true;
    
    document.getElementById('panelBusqueda').classList.add('disabled-overlay');
    cerrarResultados();
}

// ============================================
// BÚSQUEDA DE PRODUCTOS
// ============================================
async function buscarProductos() {
    const termino = document.getElementById('buscarProducto').value.trim();
    
    if (!termino) {
        mostrarError('Ingrese un término de búsqueda');
        return;
    }
    
    if (!sucursalActual) {
        mostrarError('Seleccione una sucursal primero');
        return;
    }
    
    try {
        mostrarCargando();
        
        const url = '/joyeria-torre-fuerte/api/productos/buscar-con-stock.php?termino=' + 
                    encodeURIComponent(termino) + 
                    '&sucursal_id=' + sucursalActual.id +
                    '&limite=20';
        
        const res = await fetch(url);
        const data = await res.json();
        
        ocultarCargando();
        
        if (!data.success) {
            mostrarError(data.message || 'Error en búsqueda');
            return;
        }
        
        let productos = data.data || [];
        
        if (!Array.isArray(productos)) {
            productos = Object.values(productos);
        }
        
        if (productos.length === 0) {
            mostrarError('No se encontraron productos');
            return;
        }
        
        // Si es código de barras exacto y solo hay 1 resultado, agregar automáticamente
        if (productos.length === 1 && productos[0].codigo_barras === termino) {
            agregarAlCarrito(productos[0]);
            document.getElementById('buscarProducto').value = '';
            cerrarResultados();
        } else {
            mostrarResultados(productos);
        }
        
    } catch (error) {
        ocultarCargando();
        console.error('Error:', error);
        mostrarError('Error: ' + error.message);
    }
}

function mostrarResultados(productos) {
    console.log('📋 Mostrando resultados:', productos);
    
    // Guardar productos para usar después
    productosEncontrados = productos;
    
    const container = document.getElementById('listaResultados');
    const divResultados = document.getElementById('resultadosBusqueda');
    
    document.getElementById('cantidadResultados').textContent = productos.length;
    
    let html = '';
    
    productos.forEach(p => {
        const precio = parseFloat(p.precio_publico || 0);
        const stock = parseInt(p.stock_disponible || 0);
        
        let badgeStock = '';
        let cardClass = '';
        
        if (stock === 0) {
            badgeStock = '<span class="badge bg-danger">Sin stock</span>';
            cardClass = 'sin-stock';
        } else if (stock <= (p.stock_minimo || 0)) {
            badgeStock = '<span class="badge bg-warning text-dark">Stock: ' + stock + '</span>';
        } else {
            badgeStock = '<span class="badge bg-success">Stock: ' + stock + '</span>';
        }
        
        html += '<div class="col-md-6">';
        html += '<div class="producto-card card h-100 ' + cardClass + '">';
        html += '<div class="card-body p-2">';
        html += '<h6 class="mb-1">' + escaparHTML(p.nombre) + '</h6>';
        html += '<div class="d-flex justify-content-between align-items-center mb-1">';
        html += '<small class="text-muted">SKU: ' + escaparHTML(p.codigo) + '</small>';
        html += badgeStock;
        html += '</div>';
        html += '<div class="d-flex justify-content-between align-items-center">';
        html += '<strong class="text-primary fs-5">' + formatearMoneda(precio) + '</strong>';
        if (stock > 0) {
            html += '<button type="button" class="btn btn-sm btn-success" onclick="agregarProductoPorId(' + p.id + ')">';
            html += '<i class="bi bi-plus-circle"></i> Agregar';
            html += '</button>';
        } else {
            html += '<span class="text-muted"><small>Sin stock</small></span>';
        }
        html += '</div>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
    });
    
    container.innerHTML = html;
    divResultados.style.display = 'block';
}

function agregarProductoPorId(productoId) {
    console.log('🎯 Agregando producto ID:', productoId);
    
    const producto = productosEncontrados.find(p => p.id === productoId);
    
    if (!producto) {
        console.error('❌ Producto no encontrado');
        mostrarError('Error: Producto no encontrado');
        return;
    }
    
    console.log('✅ Producto encontrado:', producto);
    agregarAlCarrito(producto);
}

function cerrarResultados() {
    document.getElementById('resultadosBusqueda').style.display = 'none';
    document.getElementById('buscarProducto').value = '';
}

// ============================================
// GESTIÓN DEL CARRITO
// ============================================
function agregarAlCarrito(producto) {
    console.log('➕ Agregando al carrito:', producto);
    
    if (!sucursalActual) {
        mostrarError('Seleccione una sucursal primero');
        return;
    }
    
    const stock = parseInt(producto.stock_disponible || 0);
    
    if (stock === 0) {
        mostrarError('Producto sin stock disponible');
        return;
    }
    
    const precio = parseFloat(producto.precio_publico || 0);
    
    console.log('💰 Precio:', precio, '| Stock:', stock);
    
    const indice = carrito.findIndex(item => item.producto_id === producto.id);
    
    if (indice >= 0) {
        if (carrito[indice].cantidad < stock) {
            carrito[indice].cantidad++;
            console.log('📈 Cantidad actualizada:', carrito[indice].cantidad);
            mostrarExito('Cantidad actualizada', 1000);
        } else {
            mostrarError('No hay más stock disponible');
            return;
        }
    } else {
        carrito.push({
            producto_id: producto.id,
            nombre: producto.nombre,
            codigo: producto.codigo,
            precio_unitario: precio,
            cantidad: 1,
            stock_disponible: stock
        });
        console.log('🆕 Producto agregado al carrito');
        mostrarExito('Producto agregado', 1000);
    }
    
    console.log('🛒 Carrito actualizado:', carrito);
    
    actualizarCarrito();
    cerrarResultados();
}

function actualizarCarrito() {
    console.log('🔄 Actualizando carrito. Items:', carrito.length);
    
    const carritoVacio = document.getElementById('carritoVacio');
    const carritoItems = document.getElementById('carritoItems');
    
    if (carrito.length === 0) {
        carritoVacio.style.display = 'block';
        carritoItems.style.display = 'none';
        document.getElementById('cantidadItems').textContent = '0';
        document.getElementById('totalUnidades').textContent = '0';
        calcularTotales();
        actualizarBotonProcesar();
        return;
    }
    
    carritoVacio.style.display = 'none';
    carritoItems.style.display = 'block';
    
    let html = '';
    let totalItems = 0;
    let totalUnidades = 0;
    
    carrito.forEach((item, index) => {
        const subtotal = item.cantidad * item.precio_unitario;
        totalItems++;
        totalUnidades += item.cantidad;
        
        html += '<div class="carrito-item">';
        html += '<div class="d-flex justify-content-between align-items-start mb-2">';
        html += '<div class="flex-grow-1">';
        html += '<strong class="text-dark">' + escaparHTML(item.nombre) + '</strong><br>';
        html += '<small class="text-muted">SKU: ' + escaparHTML(item.codigo) + ' | ';
        html += 'Precio: ' + formatearMoneda(item.precio_unitario) + '</small>';
        html += '</div>';
        html += '<button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarDelCarrito(' + index + ')" title="Eliminar">';
        html += '<i class="bi bi-trash"></i>';
        html += '</button>';
        html += '</div>';
        
        html += '<div class="d-flex justify-content-between align-items-center">';
        html += '<div class="btn-group btn-group-sm">';
        html += '<button type="button" class="btn btn-outline-secondary" onclick="cambiarCantidad(' + index + ', -1)">-</button>';
        html += '<input type="number" class="form-control input-cantidad" value="' + item.cantidad + '" ';
        html += 'min="1" max="' + item.stock_disponible + '" ';
        html += 'onchange="cambiarCantidadDirecta(' + index + ', this.value)">';
        html += '<button type="button" class="btn btn-outline-secondary" onclick="cambiarCantidad(' + index + ', 1)">+</button>';
        html += '</div>';
        html += '<div class="text-end">';
        html += '<small class="text-muted d-block">Subtotal</small>';
        html += '<strong class="text-primary fs-5">' + formatearMoneda(subtotal) + '</strong>';
        html += '</div>';
        html += '</div>';
        
        html += '</div>';
    });
    
    carritoItems.innerHTML = html;
    document.getElementById('cantidadItems').textContent = totalItems;
    document.getElementById('totalUnidades').textContent = totalUnidades;
    
    calcularTotales();
    actualizarBotonProcesar();
}

function cambiarCantidad(index, cambio) {
    const item = carrito[index];
    const nuevaCantidad = item.cantidad + cambio;
    
    if (nuevaCantidad < 1) {
        eliminarDelCarrito(index);
        return;
    }
    
    if (nuevaCantidad > item.stock_disponible) {
        mostrarError('Stock máximo: ' + item.stock_disponible);
        return;
    }
    
    item.cantidad = nuevaCantidad;
    actualizarCarrito();
}

function cambiarCantidadDirecta(index, valor) {
    const cantidad = parseInt(valor) || 1;
    const item = carrito[index];
    
    if (cantidad < 1) {
        eliminarDelCarrito(index);
        return;
    }
    
    if (cantidad > item.stock_disponible) {
        mostrarError('Stock máximo: ' + item.stock_disponible);
        item.cantidad = item.stock_disponible;
        actualizarCarrito();
        return;
    }
    
    item.cantidad = cantidad;
    actualizarCarrito();
}

function eliminarDelCarrito(index) {
    const producto = carrito[index].nombre;
    carrito.splice(index, 1);
    actualizarCarrito();
    mostrarExito('Producto eliminado: ' + producto, 1500);
}

function limpiarCarrito() {
    carrito = [];
    actualizarCarrito();
}

// ============================================
// CÁLCULOS Y TOTALES
// ============================================
function calcularTotales() {
    console.log('💰 Calculando totales...');
    console.log('🛒 Carrito actual:', carrito);
    
    let subtotal = 0;
    
    carrito.forEach(item => {
        const itemSubtotal = item.cantidad * item.precio_unitario;
        console.log(`  ${item.nombre}: ${item.cantidad} x ${item.precio_unitario} = ${itemSubtotal}`);
        subtotal += itemSubtotal;
    });
    
    const descuento = parseFloat(document.getElementById('descuentoVenta').value) || 0;
    const total = Math.max(0, subtotal - descuento);
    
    console.log('📊 Subtotal:', subtotal);
    console.log('📊 Descuento:', descuento);
    console.log('📊 TOTAL:', total);
    
    document.getElementById('subtotalVenta').textContent = formatearMoneda(subtotal);
    document.getElementById('descuentoTotal').textContent = '- ' + formatearMoneda(descuento);
    document.getElementById('totalVenta').textContent = formatearMoneda(total);
    document.getElementById('totalAPagar').textContent = formatearMoneda(total);
    
    calcularTotalPagado();
}

// ============================================
// TIPO DE VENTA
// ============================================
function cambiarTipoVenta() {
    const tipo = document.getElementById('tipoVenta').value;
    const divCuotas = document.getElementById('divCuotas');
    const divFormasPago = document.getElementById('divFormasPago');
    
    if (tipo === 'credito') {
        divCuotas.style.display = 'block';
        divFormasPago.style.display = 'none';
        formasPago = [];
    } else if (tipo === 'normal') {
        divCuotas.style.display = 'none';
        divFormasPago.style.display = 'block';
        if (formasPago.length === 0) {
            agregarFormaPago();
        }
    } else if (tipo === 'apartado') {
        divCuotas.style.display = 'none';
        divFormasPago.style.display = 'none';
        formasPago = [];
    }
    
    actualizarBotonProcesar();
}

// ============================================
// FORMAS DE PAGO
// ============================================
function agregarFormaPago() {
    formasPago.push({
        id: contadorFormasPago++,
        forma_pago: 'efectivo',
        monto: 0,
        referencia: ''
    });
    
    actualizarFormasPago();
}

function actualizarFormasPago() {
    const container = document.getElementById('formasPagoContainer');
    
    if (formasPago.length === 0) {
        container.innerHTML = '<p class="text-muted text-center py-2">No hay formas de pago agregadas</p>';
        calcularTotalPagado();
        return;
    }
    
    let html = '';
    
    formasPago.forEach((fp, index) => {
        html += '<div class="border rounded p-2 mb-2 bg-light">';
        html += '<div class="row g-2">';
        
        html += '<div class="col-6">';
        html += '<select class="form-select form-select-sm" onchange="actualizarFormaPago(' + index + ', \'forma_pago\', this.value)">';
        html += '<option value="efectivo"' + (fp.forma_pago === 'efectivo' ? ' selected' : '') + '>💵 Efectivo</option>';
        html += '<option value="tarjeta_debito"' + (fp.forma_pago === 'tarjeta_debito' ? ' selected' : '') + '>💳 Débito</option>';
        html += '<option value="tarjeta_credito"' + (fp.forma_pago === 'tarjeta_credito' ? ' selected' : '') + '>💳 Crédito</option>';
        html += '<option value="transferencia"' + (fp.forma_pago === 'transferencia' ? ' selected' : '') + '>🏦 Transferencia</option>';
        html += '<option value="cheque"' + (fp.forma_pago === 'cheque' ? ' selected' : '') + '>📝 Cheque</option>';
        html += '</select>';
        html += '</div>';
        
        html += '<div class="col-4">';
        html += '<input type="number" class="form-control form-control-sm" placeholder="Monto" ';
        html += 'value="' + fp.monto + '" step="0.01" min="0" ';
        html += 'onchange="actualizarFormaPago(' + index + ', \'monto\', this.value)">';
        html += '</div>';
        
        html += '<div class="col-2">';
        html += '<button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="eliminarFormaPago(' + index + ')" title="Eliminar">';
        html += '<i class="bi bi-trash"></i>';
        html += '</button>';
        html += '</div>';
        
        if (fp.forma_pago !== 'efectivo') {
            html += '<div class="col-12">';
            html += '<input type="text" class="form-control form-control-sm" placeholder="Referencia/Autorización" ';
            html += 'value="' + (fp.referencia || '') + '" ';
            html += 'onchange="actualizarFormaPago(' + index + ', \'referencia\', this.value)">';
            html += '</div>';
        }
        
        html += '</div>';
        html += '</div>';
    });
    
    container.innerHTML = html;
    calcularTotalPagado();
}

function actualizarFormaPago(index, campo, valor) {
    if (campo === 'monto') {
        formasPago[index][campo] = parseFloat(valor) || 0;
        calcularTotalPagado();
    } else {
        formasPago[index][campo] = valor;
        if (campo === 'forma_pago') {
            actualizarFormasPago();
        }
    }
}

function eliminarFormaPago(index) {
    formasPago.splice(index, 1);
    actualizarFormasPago();
}

function calcularTotalPagado() {
    let totalPagado = 0;
    
    formasPago.forEach(fp => {
        totalPagado += fp.monto;
    });
    
    const totalVenta = obtenerTotalVenta();
    const diferencia = totalVenta - totalPagado;
    
    document.getElementById('totalPagado').textContent = formatearMoneda(totalPagado);
    
    const elementoFalta = document.getElementById('faltaPagar');
    const elementoLabel = document.getElementById('labelFaltaPagar');
    
    if (diferencia < -0.01) {
        // Paga de más - mostrar cambio
        const cambio = Math.abs(diferencia);
        elementoLabel.textContent = 'Cambio a devolver:';
        elementoFalta.textContent = formatearMoneda(cambio);
        elementoFalta.className = 'text-success fs-5 fw-bold';
    } else if (diferencia > 0.01) {
        // Falta por pagar
        elementoLabel.textContent = 'Falta por pagar:';
        elementoFalta.textContent = formatearMoneda(diferencia);
        elementoFalta.className = 'text-danger fs-5 fw-bold';
    } else {
        // Pago exacto
        elementoLabel.textContent = 'Falta por pagar:';
        elementoFalta.textContent = formatearMoneda(0);
        elementoFalta.className = 'text-success fs-5 fw-bold';
    }
    
    actualizarBotonProcesar();
}

function obtenerTotalVenta() {
    let subtotal = 0;
    carrito.forEach(item => {
        subtotal += item.cantidad * item.precio_unitario;
    });
    const descuento = parseFloat(document.getElementById('descuentoVenta').value) || 0;
    return Math.max(0, subtotal - descuento);
}

// ============================================
// GESTIÓN DE CLIENTES
// ============================================
let timeoutBusquedaCliente;

function buscarClientesConDelay() {
    clearTimeout(timeoutBusquedaCliente);
    const busqueda = document.getElementById('buscarClienteInputPos').value.trim();
    
    const container = document.getElementById('resultadosClientesPos');
    
    if (busqueda.length < 2) {
        container.innerHTML = '<p class="text-center text-muted">Escriba al menos 2 caracteres para buscar</p>';
        return;
    }
    
    container.innerHTML = '<div class="text-center"><div class="spinner-border text-primary"></div><p class="mt-2">Buscando...</p></div>';
    
    timeoutBusquedaCliente = setTimeout(() => buscarClientesPos(busqueda), 300);
}

async function buscarClientesPos(busqueda) {
    try {
        const res = await fetch('/joyeria-torre-fuerte/api/clientes/listar.php?buscar=' + encodeURIComponent(busqueda) + '&limite=15');
        const data = await res.json();
        
        const container = document.getElementById('resultadosClientesPos');
        
        if (!data.success) {
            container.innerHTML = '<p class="text-center text-muted">No se encontraron clientes</p>';
            return;
        }
        
        let clientes = data.data || [];
        
        if (clientes.clientes && Array.isArray(clientes.clientes)) {
            clientes = clientes.clientes;
        }
        
        if (!Array.isArray(clientes)) {
            clientes = Object.values(clientes);
        }
        
        if (clientes.length === 0) {
            container.innerHTML = '<p class="text-center text-muted">No se encontraron clientes</p>';
            return;
        }
        
        let html = '<div class="list-group">';
        clientes.forEach(c => {
            const nombre = c.nombre_completo || c.nombre;
            const telefono = c.telefono || 'Sin teléfono';
            
            html += '<a href="#" class="list-group-item list-group-item-action" ';
            html += 'onclick="event.preventDefault(); seleccionarClientePos(' + c.id + ', \'' + 
                    escaparHTML(nombre).replace(/'/g, "\\'") + '\', \'' + 
                    escaparHTML(telefono).replace(/'/g, "\\'") + '\')">';
            html += '<div class="d-flex justify-content-between align-items-start">';
            html += '<div>';
            html += '<h6 class="mb-1">' + escaparHTML(nombre) + '</h6>';
            html += '<small class="text-muted"><i class="bi bi-telephone"></i> ' + escaparHTML(telefono) + '</small>';
            html += '</div>';
            if (c.nit && c.nit !== 'C/F') {
                html += '<span class="badge bg-secondary">NIT: ' + escaparHTML(c.nit) + '</span>';
            }
            html += '</div>';
            html += '</a>';
        });
        html += '</div>';
        
        container.innerHTML = html;
        
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('resultadosClientesPos').innerHTML = 
            '<p class="text-center text-danger">Error al buscar clientes</p>';
    }
}

function seleccionarClientePos(id, nombre, telefono) {
    document.getElementById('clienteVenta').value = id;
    document.getElementById('clienteNombrePos').textContent = nombre;
    document.getElementById('clienteTelefonoPos').textContent = telefono;
    
    document.getElementById('clienteSeleccionadoPos').style.display = 'block';
    document.getElementById('botonesClientePos').style.display = 'none';
    
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalBuscarClientePos'));
    if (modal) modal.hide();
    
    document.getElementById('buscarClienteInputPos').value = '';
    document.getElementById('resultadosClientesPos').innerHTML = 
        '<p class="text-center text-muted">Escriba al menos 2 caracteres para buscar</p>';
}

function limpiarClientePos() {
    document.getElementById('clienteVenta').value = '';
    document.getElementById('clienteSeleccionadoPos').style.display = 'none';
    document.getElementById('botonesClientePos').style.display = 'block';
}

function irANuevoCliente() {
    if (carrito.length > 0) {
        Swal.fire({
            title: '¿Ir a crear cliente?',
            text: 'Se perderá el carrito actual',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, continuar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '/joyeria-torre-fuerte/modules/clientes/agregar.php?origen=pos';
            }
        });
    } else {
        window.location.href = '/joyeria-torre-fuerte/modules/clientes/agregar.php?origen=pos';
    }
}

// ============================================
// BOTÓN PROCESAR
// ============================================
function actualizarBotonProcesar() {
    const btnProcesar = document.getElementById('btnProcesar');
    const mensaje = document.getElementById('mensajeBotonProcesar');
    const tipoVenta = document.getElementById('tipoVenta').value;
    
    // Verificar carrito
    if (carrito.length === 0) {
        btnProcesar.disabled = true;
        mensaje.textContent = 'Agregue productos al carrito para continuar';
        mensaje.className = 'text-muted d-block text-center mt-2';
        return;
    }
    
    // Validar formas de pago para venta normal
    if (tipoVenta === 'normal') {
        if (formasPago.length === 0) {
            btnProcesar.disabled = true;
            mensaje.textContent = 'Agregue al menos una forma de pago';
            mensaje.className = 'text-warning d-block text-center mt-2';
            return;
        }
        
        let totalPagado = 0;
        formasPago.forEach(fp => {
            totalPagado += fp.monto;
        });
        
        const totalVenta = obtenerTotalVenta();
        
        // Permitir si el pago es mayor o igual (con cambio permitido)
        if (totalPagado < totalVenta - 0.01) {
            btnProcesar.disabled = true;
            mensaje.textContent = 'El monto pagado es insuficiente';
            mensaje.className = 'text-danger d-block text-center mt-2';
            return;
        }
    }
    
    // Todo OK
    btnProcesar.disabled = false;
    mensaje.textContent = '✓ Todo listo para procesar';
    mensaje.className = 'text-success d-block text-center mt-2';
}

// ============================================
// PROCESAR VENTA
// ============================================
async function procesarVenta(event) {
    // Prevenir scroll y comportamiento por defecto
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    
    console.log('🚀 Iniciando procesarVenta()');
    
    if (carrito.length === 0) {
        console.error('❌ Carrito vacío');
        mostrarError('El carrito está vacío');
        return;
    }
    
    if (!sucursalActual) {
        console.error('❌ Sin sucursal');
        mostrarError('Seleccione una sucursal');
        return;
    }
    
    const tipoVenta = document.getElementById('tipoVenta').value;
    const total = obtenerTotalVenta();
    
    console.log('💰 Tipo venta:', tipoVenta, '| Total:', total);
    
    if (tipoVenta === 'normal') {
        if (formasPago.length === 0) {
            console.error('❌ Sin formas de pago');
            mostrarError('Debe agregar al menos una forma de pago');
            return;
        }
        
        let totalPagado = 0;
        formasPago.forEach(fp => {
            totalPagado += fp.monto;
        });
        
        console.log('💳 Total pagado:', totalPagado);
        
        if (totalPagado < total - 0.01) {
            console.error('❌ Pago insuficiente:', totalPagado, '<', total);
            mostrarError('El monto pagado es insuficiente');
            return;
        }
    }
    
    const datos = {
        sucursal_id: sucursalActual.id,
        cliente_id: document.getElementById('clienteVenta').value ? parseInt(document.getElementById('clienteVenta').value) : null,
        tipo_venta: tipoVenta,
        descuento: parseFloat(document.getElementById('descuentoVenta').value) || 0,
        productos: carrito.map(item => ({
            producto_id: item.producto_id,
            cantidad: item.cantidad,
            precio_unitario: item.precio_unitario
        }))
    };
    
    if (tipoVenta === 'normal') {
        // Calcular total pagado
        let totalPagado = 0;
        formasPago.forEach(fp => {
            totalPagado += fp.monto;
        });
        
        // Si paga de más, ajustar el monto al total exacto
        if (totalPagado > total) {
            console.log('💰 Ajustando pago de', totalPagado, 'a', total, '(cambio:', (totalPagado - total).toFixed(2) + ')');
            
            // Copiar formas de pago y ajustar la última al monto exacto
            let formasPagoAjustadas = formasPago.map((fp, index) => {
                if (index === formasPago.length - 1) {
                    // Última forma de pago: ajustar al total restante
                    let montoAjustado = total;
                    for (let i = 0; i < formasPago.length - 1; i++) {
                        montoAjustado -= formasPago[i].monto;
                    }
                    return {
                        forma_pago: fp.forma_pago,
                        monto: Math.max(0, montoAjustado),
                        referencia: fp.referencia || null
                    };
                } else {
                    return {
                        forma_pago: fp.forma_pago,
                        monto: fp.monto,
                        referencia: fp.referencia || null
                    };
                }
            });
            
            datos.formas_pago = formasPagoAjustadas;
            console.log('📦 Formas de pago ajustadas:', formasPagoAjustadas);
        } else {
            datos.formas_pago = formasPago.map(fp => ({
                forma_pago: fp.forma_pago,
                monto: fp.monto,
                referencia: fp.referencia || null
            }));
        }
    }
    
    if (tipoVenta === 'credito') {
        datos.numero_cuotas = parseInt(document.getElementById('numeroCuotas').value) || 4;
    }
    
    console.log('📦 Datos a enviar:', datos);
    
    const result = await Swal.fire({
        title: '¿Procesar esta venta?',
        html: '<strong>Total: ' + formatearMoneda(total) + '</strong><br>' +
              'Tipo: ' + tipoVenta.toUpperCase() + '<br>' +
              'Productos: ' + carrito.length + ' items',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, procesar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#198754'
    });
    
    if (!result.isConfirmed) {
        console.log('❌ Usuario canceló');
        return;
    }
    
    try {
        console.log('🌐 Enviando request a API...');
        mostrarCargando();
        
        const res = await fetch('/joyeria-torre-fuerte/api/ventas/crear.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datos)
        });
        
        console.log('📡 Status:', res.status, res.statusText);
        
        const resultado = await res.json();
        
        console.log('📦 Respuesta completa del servidor:', resultado);
        
        ocultarCargando();
        
        if (resultado.success) {
            console.log('✅ Venta exitosa:', resultado.data);
            
            await Swal.fire({
                title: '¡Venta exitosa!',
                html: '<strong>Número: ' + resultado.data.numero_venta + '</strong><br>' +
                      'Total: ' + formatearMoneda(resultado.data.total),
                icon: 'success',
                confirmButtonText: 'Ver detalle',
                showCancelButton: true,
                cancelButtonText: 'Nueva venta'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'ver.php?id=' + resultado.data.venta_id;
                } else {
                    limpiarVenta();
                }
            });
        } else {
            console.error('❌ Error del servidor:', resultado);
            console.error('❌ Mensaje:', resultado.message);
            console.error('❌ Código:', resultado.code);
            console.error('❌ Error completo:', resultado.error);
            
            mostrarError(resultado.message || resultado.error || 'Error al procesar venta');
        }
        
    } catch (error) {
        ocultarCargando();
        console.error('💥 ERROR CRÍTICO:', error);
        console.error('💥 Error completo:', error.stack);
        mostrarError('Error de conexión: ' + error.message);
    }
}

// ============================================
// LIMPIAR TODO
// ============================================
function limpiarVenta() {
    limpiarCarrito();
    formasPago = [];
    contadorFormasPago = 0;
    actualizarFormasPago();
    
    limpiarClientePos();
    document.getElementById('tipoVenta').value = 'normal';
    document.getElementById('descuentoVenta').value = '0';
    document.getElementById('numeroCuotas').value = '4';
    document.getElementById('buscarProducto').value = '';
    
    cerrarResultados();
    cambiarTipoVenta();
}

console.log('✅ POS Nueva Venta cargado correctamente');
</script>