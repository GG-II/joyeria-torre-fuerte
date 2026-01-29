<?php
/**
 * ================================================
 * MÓDULO VENTAS - NUEVA VENTA (PUNTO DE VENTA)
 * ================================================
 * 
 * Interfaz de punto de venta para procesar transacciones.
 * Permite buscar productos, agregar al carrito, aplicar descuentos
 * y procesar múltiples formas de pago.
 * 
 * TODO FASE 5: Conectar con APIs
 * - GET /api/productos/buscar.php
 * - POST /api/ventas/crear.php
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

// Verificar autenticación y permisos
requiere_autenticacion();
requiere_rol(['administrador', 'dueño', 'vendedor', 'cajero']);

// Título de página
$titulo_pagina = 'Nueva Venta';

// Incluir header
include '../../includes/header.php';

// Incluir navbar
include '../../includes/navbar.php';

// Datos del usuario actual
$usuario_actual = [
    'id' => $_SESSION['usuario_id'],
    'nombre' => $_SESSION['usuario_nombre'],
    'sucursal_id' => $_SESSION['usuario_sucursal_id'],
    'sucursal_nombre' => $_SESSION['usuario_sucursal_nombre']
];
?>

<!-- Contenido Principal -->
<div class="container-fluid main-content">
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
            <li class="breadcrumb-item active">Nueva Venta</li>
        </ol>
    </nav>

    <!-- Encabezado -->
    <div class="page-header mb-4">
        <div class="row align-items-center g-2">
            <div class="col-md-6">
                <h1 class="mb-2">
                    <i class="bi bi-cart-plus"></i>
                    Punto de Venta
                </h1>
                <p class="text-muted mb-0">
                    <i class="bi bi-building me-1"></i>
                    <strong><?php echo $usuario_actual['sucursal_nombre']; ?></strong>
                    <span class="mx-2">|</span>
                    <i class="bi bi-person me-1"></i>
                    <strong><?php echo $usuario_actual['nombre']; ?></strong>
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <a href="lista.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i>
                    <span class="d-none d-sm-inline">Cancelar Venta</span>
                </a>
            </div>
        </div>
    </div>

    <form id="formVenta" method="POST" action="">
        <input type="hidden" name="usuario_id" value="<?php echo $usuario_actual['id']; ?>">
        <input type="hidden" name="sucursal_id" value="<?php echo $usuario_actual['sucursal_id']; ?>">
        
        <div class="row g-3">
            <!-- Productos y Carrito -->
            <div class="col-lg-8">
                <!-- Búsqueda de Productos -->
                <div class="card mb-3 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-search"></i>
                        Buscar Productos
                    </div>
                    <div class="card-body">
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-upc-scan"></i>
                            </span>
                            <input type="text" 
                                   class="form-control form-control-lg" 
                                   id="buscarProducto" 
                                   placeholder="Buscar por código o nombre..."
                                   autofocus>
                        </div>
                        
                        <!-- TODO FASE 5: Resultados dinámicos desde API -->
                        <div id="resultadosBusqueda" class="mt-3" style="display: none;">
                            <div class="list-group">
                                <button type="button" class="list-group-item list-group-item-action" onclick="agregarProducto(1, 'AN-001', 'Anillo de Oro 18K', 8500.00, 5)">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong>AN-001</strong> - Anillo de Oro 18K con Diamante
                                            <br><small class="text-muted">Stock: 5 unidades</small>
                                        </div>
                                        <div class="text-end">
                                            <strong class="text-success">Q 8,500.00</strong>
                                        </div>
                                    </div>
                                </button>
                                <button type="button" class="list-group-item list-group-item-action" onclick="agregarProducto(2, 'AR-102', 'Aretes de Plata', 1200.00, 13)">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong>AR-102</strong> - Aretes de Plata con Perla
                                            <br><small class="text-muted">Stock: 13 unidades</small>
                                        </div>
                                        <div class="text-end">
                                            <strong class="text-success">Q 1,200.00</strong>
                                        </div>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Carrito de Compras -->
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <i class="bi bi-cart"></i>
                        Productos en el Carrito
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="tablaCarrito">
                            <thead class="table-light">
                                <tr>
                                    <th class="d-none d-md-table-cell">Código</th>
                                    <th>Producto</th>
                                    <th class="d-none d-sm-table-cell">Precio</th>
                                    <th>Cant.</th>
                                    <th>Subtotal</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="carritoBody">
                                <tr id="carritoVacio">
                                    <td colspan="6" class="text-center text-muted py-5">
                                        <i class="bi bi-cart-x" style="font-size: 48px; opacity: 0.3;"></i>
                                        <p class="mt-2 mb-0">No hay productos en el carrito</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Resumen y Pago -->
            <div class="col-lg-4">
                <!-- Cliente -->
                <div class="card mb-3 shadow-sm">
                    <div class="card-header">
                        <i class="bi bi-person"></i>
                        Cliente
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <label for="cliente_id" class="form-label">Seleccionar Cliente</label>
                            <select class="form-select" id="cliente_id" name="cliente_id">
                                <option value="">Consumidor Final</option>
                                <!-- TODO FASE 5: Cargar desde API -->
                                <option value="1">María García López</option>
                                <option value="2">Carlos Méndez</option>
                                <option value="3">Ana Ramírez</option>
                            </select>
                        </div>
                        <div id="infoCliente" style="display: none;">
                            <small class="text-muted">Límite de crédito:</small>
                            <div class="fw-bold text-success" id="limiteCredito">Q 0.00</div>
                        </div>
                    </div>
                </div>

                <!-- Tipo de Venta -->
                <div class="card mb-3 shadow-sm">
                    <div class="card-header">
                        <i class="bi bi-tag"></i>
                        Tipo de Venta
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="tipo_venta" id="tipo_normal" value="normal" checked>
                            <label class="form-check-label" for="tipo_normal">
                                <i class="bi bi-cash"></i> Contado
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="tipo_venta" id="tipo_credito" value="credito">
                            <label class="form-check-label" for="tipo_credito">
                                <i class="bi bi-credit-card"></i> Crédito
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="tipo_venta" id="tipo_apartado" value="apartado">
                            <label class="form-check-label" for="tipo_apartado">
                                <i class="bi bi-bookmark"></i> Apartado
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Resumen de Totales -->
                <div class="card mb-3 shadow-sm border-info">
                    <div class="card-header" style="background-color: #1e3a8a; color: white;">
                        <i class="bi bi-calculator"></i>
                        Resumen de Venta
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span class="fw-bold" id="displaySubtotal">Q 0.00</span>
                        </div>
                        <div class="mb-2">
                            <label for="descuento" class="form-label">Descuento (Q):</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="descuento" 
                                   name="descuento" 
                                   min="0" 
                                   step="0.01" 
                                   value="0.00">
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">TOTAL:</h4>
                            <h3 class="mb-0 text-success" id="displayTotal">Q 0.00</h3>
                        </div>
                        <input type="hidden" id="subtotal" name="subtotal" value="0">
                    </div>
                </div>

                <!-- Formas de Pago -->
                <div class="card mb-3 shadow-sm" id="cardFormasPago">
                    <div class="card-header bg-success text-white">
                        <i class="bi bi-wallet2"></i>
                        Formas de Pago
                    </div>
                    <div class="card-body">
                        <div id="formasPagoContainer">
                            <div class="forma-pago-item mb-3 p-3 bg-light rounded">
                                <div class="row g-2">
                                    <div class="col-12">
                                        <label class="form-label small">Forma de Pago</label>
                                        <select class="form-select" name="forma_pago[]" required>
                                            <option value="efectivo">Efectivo</option>
                                            <option value="tarjeta_debito">Tarjeta Débito</option>
                                            <option value="tarjeta_credito">Tarjeta Crédito</option>
                                            <option value="transferencia">Transferencia</option>
                                            <option value="cheque">Cheque</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small">Monto</label>
                                        <input type="number" 
                                               class="form-control monto-pago" 
                                               name="monto_pago[]" 
                                               placeholder="0.00" 
                                               min="0" 
                                               step="0.01"
                                               required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small">Referencia (opcional)</label>
                                        <input type="text" 
                                               class="form-control" 
                                               name="referencia_pago[]" 
                                               placeholder="Ej: N° cheque, autorización">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary w-100" onclick="agregarFormaPago()">
                            <i class="bi bi-plus-circle"></i>
                            Agregar otra forma de pago
                        </button>
                    </div>
                </div>

                <!-- Botón Finalizar -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg" id="btnFinalizar" disabled>
                        <i class="bi bi-check-circle"></i>
                        Finalizar Venta
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
/* ============================================
   ESTILOS ESPECÍFICOS PUNTO DE VENTA
   ============================================ */

/* Contenedor principal */
.main-content {
    padding: 20px;
    min-height: calc(100vh - 120px);
}

/* Cards con mejor sombra */
.card {
    border-radius: 8px;
    margin-bottom: 1rem;
}

.shadow-sm {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08) !important;
}

/* Input de búsqueda destacado */
#buscarProducto {
    font-size: 1.1rem;
    border: 2px solid #e5e7eb;
    transition: border-color 0.3s ease;
}

#buscarProducto:focus {
    border-color: #d4af37;
    box-shadow: 0 0 0 0.2rem rgba(212, 175, 55, 0.25);
}

/* Resultados de búsqueda */
.list-group-item-action {
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.list-group-item-action:hover {
    background-color: rgba(212, 175, 55, 0.1);
}

/* Tabla del carrito */
#tablaCarrito {
    font-size: 0.95rem;
}

#tablaCarrito thead th {
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    color: white;
}

#tablaCarrito tbody td {
    vertical-align: middle;
    padding: 12px 8px;
}

/* Botones de cantidad */
.input-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

/* Formas de pago */
.forma-pago-item {
    border: 1px solid #e5e7eb;
}

.forma-pago-item:last-of-type {
    margin-bottom: 1rem !important;
}

/* Botón finalizar */
#btnFinalizar:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Cards de cliente y tipo de venta */
.form-check-input:checked {
    background-color: #d4af37;
    border-color: #d4af37;
}

.form-check-label {
    cursor: pointer;
}

/* Resumen destacado */
.border-info {
    border-left: 4px solid #0ea5e9 !important;
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
    
    .page-header p {
        font-size: 0.85rem;
    }
    
    .card-body {
        padding: 15px;
    }
    
    /* Tabla responsive - ajustar columnas */
    #tablaCarrito {
        font-size: 0.85rem;
    }
    
    #tablaCarrito tbody td {
        padding: 8px 4px;
    }
    
    /* Ocultar columnas menos importantes en móvil */
    .d-mobile-none {
        display: none !important;
    }
    
    /* Botones de cantidad más grandes */
    .input-group-sm .btn {
        padding: 0.4rem 0.6rem;
        font-size: 1rem;
    }
    
    /* Input de búsqueda */
    #buscarProducto {
        font-size: 1rem;
    }
    
    /* Resultados de búsqueda */
    .list-group-item-action {
        padding: 12px;
        font-size: 0.9rem;
    }
    
    /* Formas de pago en móvil */
    .forma-pago-item {
        padding: 12px !important;
    }
    
    /* Total más grande en móvil */
    #displayTotal {
        font-size: 1.75rem;
    }
    
    /* Botón finalizar más grande */
    #btnFinalizar {
        padding: 1rem;
        font-size: 1.1rem;
    }
}

/* Tablet (576px - 767.98px) */
@media (min-width: 576px) and (max-width: 767.98px) {
    .main-content {
        padding: 18px 15px;
    }
    
    #tablaCarrito {
        font-size: 0.9rem;
    }
}

/* Tablet horizontal (768px - 991.98px) */
@media (min-width: 768px) and (max-width: 991.98px) {
    .main-content {
        padding: 20px;
    }
    
    /* En tablet, el sidebar se pone abajo */
    .col-lg-8, .col-lg-4 {
        width: 100%;
    }
}

/* Desktop (992px+) */
@media (min-width: 992px) {
    .main-content {
        padding: 25px 30px;
    }
    
    /* Cards pegajosos en desktop */
    .col-lg-4 .card {
        position: sticky;
        top: 20px;
    }
}

/* Touch targets para móvil */
@media (max-width: 767.98px) {
    .btn,
    .form-control,
    .form-select,
    .list-group-item-action {
        min-height: 44px;
    }
    
    .form-check-input {
        width: 1.25em;
        height: 1.25em;
    }
}

/* Mejoras visuales adicionales */
.card-header {
    font-weight: 600;
    font-size: 0.95rem;
}

.form-label {
    font-weight: 500;
    margin-bottom: 0.4rem;
    font-size: 0.9rem;
}

/* Animación suave para actualizaciones */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

#carritoBody tr {
    animation: fadeIn 0.3s ease;
}
</style>

<script>
/**
 * ================================================
 * JAVASCRIPT - PUNTO DE VENTA
 * ================================================
 * 
 * TODO FASE 5: Conectar todas las funciones con APIs
 */

// Variables globales
let carritoProductos = [];
let contadorFormasPago = 1;

/**
 * Buscar productos (TODO FASE 5: Conectar con API)
 */
document.getElementById('buscarProducto').addEventListener('input', function(e) {
    const resultados = document.getElementById('resultadosBusqueda');
    const termino = e.target.value.trim();
    
    if (termino.length >= 2) {
        // TODO FASE 5: fetch('/api/productos/buscar.php?q=' + termino)
        resultados.style.display = 'block';
    } else {
        resultados.style.display = 'none';
    }
});

/**
 * Agregar producto al carrito
 */
function agregarProducto(id, codigo, nombre, precio, stockDisponible) {
    // Verificar si ya está en el carrito
    const existe = carritoProductos.find(p => p.id === id);
    
    if (existe) {
        if (existe.cantidad < stockDisponible) {
            existe.cantidad++;
        } else {
            mostrarAlerta('No hay más stock disponible', 'warning');
            return;
        }
    } else {
        carritoProductos.push({
            id: id,
            codigo: codigo,
            nombre: nombre,
            precio: precio,
            cantidad: 1,
            stockDisponible: stockDisponible
        });
    }
    
    actualizarCarrito();
    document.getElementById('buscarProducto').value = '';
    document.getElementById('resultadosBusqueda').style.display = 'none';
    document.getElementById('buscarProducto').focus();
}

/**
 * Actualizar visualización del carrito
 */
function actualizarCarrito() {
    const tbody = document.getElementById('carritoBody');
    
    if (carritoProductos.length === 0) {
        tbody.innerHTML = `
            <tr id="carritoVacio">
                <td colspan="6" class="text-center text-muted py-5">
                    <i class="bi bi-cart-x" style="font-size: 48px; opacity: 0.3;"></i>
                    <p class="mt-2 mb-0">No hay productos en el carrito</p>
                </td>
            </tr>
        `;
        document.getElementById('btnFinalizar').disabled = true;
    } else {
        let html = '';
        carritoProductos.forEach((prod, index) => {
            const subtotal = prod.precio * prod.cantidad;
            html += `
                <tr>
                    <td class="fw-bold d-none d-md-table-cell">${prod.codigo}</td>
                    <td>
                        <div class="fw-bold">${prod.nombre}</div>
                        <small class="text-muted d-md-none">${prod.codigo}</small>
                    </td>
                    <td class="d-none d-sm-table-cell">Q ${formatearNumero(prod.precio)}</td>
                    <td>
                        <div class="input-group input-group-sm" style="max-width: 110px;">
                            <button class="btn btn-outline-secondary" type="button" onclick="cambiarCantidad(${index}, -1)" aria-label="Disminuir cantidad">
                                <i class="bi bi-dash"></i>
                            </button>
                            <input type="text" class="form-control text-center" value="${prod.cantidad}" readonly style="max-width: 50px;">
                            <button class="btn btn-outline-secondary" type="button" onclick="cambiarCantidad(${index}, 1)" aria-label="Aumentar cantidad">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                    </td>
                    <td class="fw-bold text-success">Q ${formatearNumero(subtotal)}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger" onclick="eliminarProducto(${index})" aria-label="Eliminar producto">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
        document.getElementById('btnFinalizar').disabled = false;
    }
    
    calcularTotales();
}

/**
 * Cambiar cantidad de producto
 */
function cambiarCantidad(index, cambio) {
    const producto = carritoProductos[index];
    const nuevaCantidad = producto.cantidad + cambio;
    
    if (nuevaCantidad <= 0) {
        eliminarProducto(index);
    } else if (nuevaCantidad <= producto.stockDisponible) {
        producto.cantidad = nuevaCantidad;
        actualizarCarrito();
    } else {
        mostrarAlerta('No hay suficiente stock disponible', 'warning');
    }
}

/**
 * Eliminar producto del carrito
 */
function eliminarProducto(index) {
    carritoProductos.splice(index, 1);
    actualizarCarrito();
}

/**
 * Calcular totales
 */
function calcularTotales() {
    const subtotal = carritoProductos.reduce((sum, prod) => sum + (prod.precio * prod.cantidad), 0);
    const descuento = parseFloat(document.getElementById('descuento').value) || 0;
    const total = subtotal - descuento;
    
    document.getElementById('displaySubtotal').textContent = 'Q ' + formatearNumero(subtotal);
    document.getElementById('displayTotal').textContent = 'Q ' + formatearNumero(total);
    document.getElementById('subtotal').value = subtotal.toFixed(2);
    
    // Auto-completar monto de pago si solo hay una forma
    const montosPago = document.querySelectorAll('.monto-pago');
    if (montosPago.length === 1) {
        montosPago[0].value = total.toFixed(2);
    }
}

// Recalcular al cambiar descuento
document.getElementById('descuento').addEventListener('input', calcularTotales);

/**
 * Agregar forma de pago adicional
 */
function agregarFormaPago() {
    const container = document.getElementById('formasPagoContainer');
    const html = `
        <div class="forma-pago-item mb-3 p-3 bg-light rounded">
            <div class="row g-2">
                <div class="col-12">
                    <label class="form-label small">Forma de Pago</label>
                    <select class="form-select" name="forma_pago[]" required>
                        <option value="efectivo">Efectivo</option>
                        <option value="tarjeta_debito">Tarjeta Débito</option>
                        <option value="tarjeta_credito">Tarjeta Crédito</option>
                        <option value="transferencia">Transferencia</option>
                        <option value="cheque">Cheque</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label small">Monto</label>
                    <input type="number" class="form-control monto-pago" name="monto_pago[]" placeholder="0.00" min="0" step="0.01" required>
                </div>
                <div class="col-12">
                    <label class="form-label small">Referencia (opcional)</label>
                    <input type="text" class="form-control" name="referencia_pago[]" placeholder="Ej: N° cheque, autorización">
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
}

/**
 * Validación y envío del formulario
 * TODO FASE 5: Conectar con API POST /api/ventas/crear.php
 */
document.getElementById('formVenta').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (carritoProductos.length === 0) {
        mostrarAlerta('Debe agregar al menos un producto', 'warning');
        return;
    }
    
    // Preparar datos para la API
    const datosVenta = {
        usuario_id: <?php echo $usuario_actual['id']; ?>,
        sucursal_id: <?php echo $usuario_actual['sucursal_id']; ?>,
        cliente_id: document.getElementById('cliente_id').value || null,
        tipo_venta: document.querySelector('input[name="tipo_venta"]:checked').value,
        subtotal: document.getElementById('subtotal').value,
        descuento: document.getElementById('descuento').value,
        productos: carritoProductos,
        formas_pago: []
    };
    
    // Recopilar formas de pago
    const formasPago = document.querySelectorAll('select[name="forma_pago[]"]');
    const montosPago = document.querySelectorAll('input[name="monto_pago[]"]');
    const referenciasPago = document.querySelectorAll('input[name="referencia_pago[]"]');
    
    formasPago.forEach((forma, index) => {
        datosVenta.formas_pago.push({
            forma_pago: forma.value,
            monto: montosPago[index].value,
            referencia: referenciasPago[index].value
        });
    });
    
    console.log('Datos de venta:', datosVenta);
    
    // TODO FASE 5: Descomentar y conectar
    /*
    fetch('<?php echo BASE_URL; ?>api/ventas/crear.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(datosVenta)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarAlerta('Venta procesada exitosamente', 'success');
            setTimeout(() => {
                window.location.href = 'ver.php?id=' + data.venta_id;
            }, 1500);
        } else {
            mostrarAlerta(data.message, 'error');
        }
    })
    .catch(error => {
        mostrarAlerta('Error al procesar la venta', 'error');
        console.error('Error:', error);
    });
    */
    
    mostrarAlerta('MODO DESARROLLO: Venta lista para procesar', 'info');
});

/**
 * Utilidades
 */
function formatearNumero(numero) {
    return parseFloat(numero).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function mostrarAlerta(mensaje, tipo) {
    // TODO: Implementar sistema de notificaciones
    alert(mensaje);
}

// Atajos de teclado
document.addEventListener('keydown', function(e) {
    // F2: Enfocar búsqueda
    if (e.key === 'F2') {
        e.preventDefault();
        document.getElementById('buscarProducto').focus();
    }
    
    // F9: Finalizar venta (si está habilitado)
    if (e.key === 'F9') {
        e.preventDefault();
        const btnFinalizar = document.getElementById('btnFinalizar');
        if (!btnFinalizar.disabled) {
            btnFinalizar.click();
        }
    }
});
</script>

<?php
// Incluir footer
include '../../includes/footer.php';
?>