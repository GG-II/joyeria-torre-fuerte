<?php
// ================================================
// MÓDULO VENTAS - NUEVA VENTA (PUNTO DE VENTA)
// ================================================

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
    <nav aria-label="breadcrumb">
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
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1>
                    <i class="bi bi-cart-plus"></i>
                    Punto de Venta
                </h1>
                <p class="text-muted">
                    Sucursal: <strong><?php echo $usuario_actual['sucursal_nombre']; ?></strong> | 
                    Vendedor: <strong><?php echo $usuario_actual['nombre']; ?></strong>
                </p>
            </div>
            <div class="col-md-6 text-end">
                <a href="lista.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i>
                    Cancelar Venta
                </a>
            </div>
        </div>
    </div>

    <form id="formVenta" method="POST" action="">
        <input type="hidden" name="usuario_id" value="<?php echo $usuario_actual['id']; ?>">
        <input type="hidden" name="sucursal_id" value="<?php echo $usuario_actual['sucursal_id']; ?>">
        
        <div class="row">
            <!-- Productos y Carrito -->
            <div class="col-lg-8">
                <!-- Búsqueda de Productos -->
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-search"></i>
                        Buscar Productos
                    </div>
                    <div class="card-body">
                        <div class="input-group input-group-lg">
                            <span class="input-group-text">
                                <i class="bi bi-upc-scan"></i>
                            </span>
                            <input type="text" 
                                   class="form-control" 
                                   id="buscarProducto" 
                                   placeholder="Buscar por código o nombre..."
                                   autofocus>
                        </div>
                        
                        <!-- Resultados de búsqueda (dummy) -->
                        <div id="resultadosBusqueda" class="mt-3" style="display: none;">
                            <div class="list-group">
                                <button type="button" class="list-group-item list-group-item-action" onclick="agregarProducto(1, 'AN-001', 'Anillo de Oro 18K', 8500.00, 5)">
                                    <div class="d-flex justify-content-between">
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
                                    <div class="d-flex justify-content-between">
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
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <i class="bi bi-cart"></i>
                        Productos en el Carrito
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="tablaCarrito">
                            <thead>
                                <tr>
                                    <th width="100">Código</th>
                                    <th>Producto</th>
                                    <th width="120">Precio Unit.</th>
                                    <th width="100">Cantidad</th>
                                    <th width="120">Subtotal</th>
                                    <th width="50"></th>
                                </tr>
                            </thead>
                            <tbody id="carritoBody">
                                <tr id="carritoVacio">
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="bi bi-cart-x" style="font-size: 48px;"></i>
                                        <p class="mt-2">No hay productos en el carrito</p>
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
                <div class="card mb-3">
                    <div class="card-header">
                        <i class="bi bi-person"></i>
                        Cliente
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="cliente_id" class="form-label">Buscar Cliente</label>
                            <select class="form-select" id="cliente_id" name="cliente_id">
                                <option value="">Consumidor Final</option>
                                <option value="1">María García López</option>
                                <option value="2">Carlos Méndez</option>
                                <option value="3">Ana Ramírez</option>
                            </select>
                        </div>
                        <div id="infoCliente" style="display: none;">
                            <small class="text-muted">Límite de crédito:</small>
                            <div class="fw-bold" id="limiteCredito">Q 0.00</div>
                        </div>
                    </div>
                </div>

                <!-- Tipo de Venta -->
                <div class="card mb-3">
                    <div class="card-header">
                        <i class="bi bi-tag"></i>
                        Tipo de Venta
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="tipo_venta" id="tipo_normal" value="normal" checked>
                            <label class="form-check-label" for="tipo_normal">
                                <i class="bi bi-cash"></i> Venta Normal (Contado)
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="tipo_venta" id="tipo_credito" value="credito">
                            <label class="form-check-label" for="tipo_credito">
                                <i class="bi bi-credit-card"></i> Venta a Crédito
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
                <div class="card mb-3">
                    <div class="card-header bg-info text-white">
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
                        <div class="d-flex justify-content-between">
                            <h4>TOTAL:</h4>
                            <h4 class="text-success" id="displayTotal">Q 0.00</h4>
                        </div>
                        <input type="hidden" id="subtotal" name="subtotal" value="0">
                    </div>
                </div>

                <!-- Formas de Pago (tabla formas_pago_venta) -->
                <div class="card mb-3" id="cardFormasPago">
                    <div class="card-header bg-success text-white">
                        <i class="bi bi-wallet2"></i>
                        Formas de Pago
                    </div>
                    <div class="card-body">
                        <div id="formasPagoContainer">
                            <div class="forma-pago-item mb-3">
                                <div class="row">
                                    <div class="col-7">
                                        <select class="form-select" name="forma_pago[]" required>
                                            <option value="efectivo">Efectivo</option>
                                            <option value="tarjeta_debito">Tarjeta Débito</option>
                                            <option value="tarjeta_credito">Tarjeta Crédito</option>
                                            <option value="transferencia">Transferencia</option>
                                            <option value="cheque">Cheque</option>
                                        </select>
                                    </div>
                                    <div class="col-5">
                                        <input type="number" 
                                               class="form-control monto-pago" 
                                               name="monto_pago[]" 
                                               placeholder="Monto" 
                                               min="0" 
                                               step="0.01"
                                               required>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-12">
                                        <input type="text" 
                                               class="form-control" 
                                               name="referencia_pago[]" 
                                               placeholder="Referencia (opcional)">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="agregarFormaPago()">
                            <i class="bi bi-plus-circle"></i>
                            Agregar otra forma de pago
                        </button>
                    </div>
                </div>

                <!-- Botón Finalizar -->
                <button type="submit" class="btn btn-primary btn-lg w-100" id="btnFinalizar" disabled>
                    <i class="bi bi-check-circle"></i>
                    Finalizar Venta
                </button>
            </div>
        </div>
    </form>
</div>

<script>
// Variables globales
let carritoProductos = [];
let contadorFormasPago = 1;

// Buscar productos
document.getElementById('buscarProducto').addEventListener('input', function(e) {
    const resultados = document.getElementById('resultadosBusqueda');
    if (e.target.value.length >= 2) {
        // Aquí se conectará con la API
        resultados.style.display = 'block';
    } else {
        resultados.style.display = 'none';
    }
});

// Agregar producto al carrito
function agregarProducto(id, codigo, nombre, precio, stockDisponible) {
    // Verificar si ya está en el carrito
    const existe = carritoProductos.find(p => p.id === id);
    if (existe) {
        if (existe.cantidad < stockDisponible) {
            existe.cantidad++;
        } else {
            alert('No hay más stock disponible');
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
}

// Actualizar carrito
function actualizarCarrito() {
    const tbody = document.getElementById('carritoBody');
    
    if (carritoProductos.length === 0) {
        tbody.innerHTML = '<tr id="carritoVacio"><td colspan="6" class="text-center text-muted py-4"><i class="bi bi-cart-x" style="font-size: 48px;"></i><p class="mt-2">No hay productos en el carrito</p></td></tr>';
        document.getElementById('btnFinalizar').disabled = true;
    } else {
        let html = '';
        carritoProductos.forEach((prod, index) => {
            const subtotal = prod.precio * prod.cantidad;
            html += `
                <tr>
                    <td class="fw-bold">${prod.codigo}</td>
                    <td>${prod.nombre}</td>
                    <td>Q ${prod.precio.toFixed(2)}</td>
                    <td>
                        <div class="input-group input-group-sm">
                            <button class="btn btn-outline-secondary" type="button" onclick="cambiarCantidad(${index}, -1)">-</button>
                            <input type="number" class="form-control text-center" value="${prod.cantidad}" readonly style="max-width: 60px;">
                            <button class="btn btn-outline-secondary" type="button" onclick="cambiarCantidad(${index}, 1)">+</button>
                        </div>
                    </td>
                    <td class="fw-bold">Q ${subtotal.toFixed(2)}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger" onclick="eliminarProducto(${index})">
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

// Cambiar cantidad
function cambiarCantidad(index, cambio) {
    const producto = carritoProductos[index];
    const nuevaCantidad = producto.cantidad + cambio;
    
    if (nuevaCantidad <= 0) {
        eliminarProducto(index);
    } else if (nuevaCantidad <= producto.stockDisponible) {
        producto.cantidad = nuevaCantidad;
        actualizarCarrito();
    } else {
        alert('No hay suficiente stock disponible');
    }
}

// Eliminar producto
function eliminarProducto(index) {
    carritoProductos.splice(index, 1);
    actualizarCarrito();
}

// Calcular totales
function calcularTotales() {
    const subtotal = carritoProductos.reduce((sum, prod) => sum + (prod.precio * prod.cantidad), 0);
    const descuento = parseFloat(document.getElementById('descuento').value) || 0;
    const total = subtotal - descuento;
    
    document.getElementById('displaySubtotal').textContent = 'Q ' + subtotal.toFixed(2);
    document.getElementById('displayTotal').textContent = 'Q ' + total.toFixed(2);
    document.getElementById('subtotal').value = subtotal.toFixed(2);
    
    // Actualizar monto de pago si solo hay una forma
    const montosPago = document.querySelectorAll('.monto-pago');
    if (montosPago.length === 1) {
        montosPago[0].value = total.toFixed(2);
    }
}

document.getElementById('descuento').addEventListener('input', calcularTotales);

// Agregar forma de pago
function agregarFormaPago() {
    const container = document.getElementById('formasPagoContainer');
    const html = `
        <div class="forma-pago-item mb-3">
            <div class="row">
                <div class="col-7">
                    <select class="form-select" name="forma_pago[]" required>
                        <option value="efectivo">Efectivo</option>
                        <option value="tarjeta_debito">Tarjeta Débito</option>
                        <option value="tarjeta_credito">Tarjeta Crédito</option>
                        <option value="transferencia">Transferencia</option>
                        <option value="cheque">Cheque</option>
                    </select>
                </div>
                <div class="col-5">
                    <input type="number" class="form-control monto-pago" name="monto_pago[]" placeholder="Monto" min="0" step="0.01" required>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-12">
                    <input type="text" class="form-control" name="referencia_pago[]" placeholder="Referencia (opcional)">
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
}

// Validación del formulario
document.getElementById('formVenta').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (carritoProductos.length === 0) {
        alert('Debe agregar al menos un producto');
        return;
    }
    
    // API insertará en:
    // 1. ventas (numero_venta, fecha, hora, cliente_id, usuario_id, sucursal_id, subtotal, descuento, tipo_venta, estado='completada')
    // 2. detalle_ventas (venta_id, producto_id, cantidad, precio_unitario, tipo_precio_aplicado)
    // 3. formas_pago_venta (venta_id, forma_pago, monto, referencia)
    // 4. inventario (actualizar cantidades)
    // 5. movimientos_inventario (registrar salidas)
    
    alert('Venta lista para procesar con la API');
    console.log('Productos:', carritoProductos);
    console.log('Formulario:', Object.fromEntries(new FormData(this)));
});
</script>

<?php
// Incluir footer
include '../../includes/footer.php';
?>