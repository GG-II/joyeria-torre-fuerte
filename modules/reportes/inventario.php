<?php
/**
 * ================================================
 * MÓDULO REPORTES - INVENTARIO
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
            <h2 class="mb-1"><i class="bi bi-box-seam"></i> Reporte de Inventario</h2>
            <p class="text-muted mb-0">Estado actual del stock</p>
        </div>
        <div>
            <select class="form-select" id="filtroSucursal">
                <option value="">Todas las sucursales</option>
            </select>
        </div>
    </div>

    <hr class="border-warning border-2 opacity-75 mb-4">

    <!-- KPIs -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-start border-primary border-4 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="text-muted mb-0">Total Productos</h6>
                        <i class="bi bi-box fs-3 text-primary"></i>
                    </div>
                    <h3 class="mb-0" id="totalProductos">0</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-start border-success border-4 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="text-muted mb-0">Total Unidades</h6>
                        <i class="bi bi-stack fs-3 text-success"></i>
                    </div>
                    <h3 class="mb-0" id="totalUnidades">0</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-start border-warning border-4 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="text-muted mb-0">Stock Bajo</h6>
                        <i class="bi bi-exclamation-triangle fs-3 text-warning"></i>
                    </div>
                    <h3 class="mb-0" id="productosBajoStock">0</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-start border-danger border-4 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="text-muted mb-0">Sin Stock</h6>
                        <i class="bi bi-x-circle fs-3 text-danger"></i>
                    </div>
                    <h3 class="mb-0" id="productosSinStock">0</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Productos Sin Stock -->
    <div class="card shadow-sm mb-4" id="cardSinStock" style="display: none;">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0"><i class="bi bi-exclamation-octagon"></i> Productos Sin Stock</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>SKU</th>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Precio</th>
                            <th>Stock Mínimo</th>
                            <th>Última Venta</th>
                        </tr>
                    </thead>
                    <tbody id="tablaSinStock"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Productos Bajo Stock -->
    <div class="card shadow-sm mb-4" id="cardBajoStock" style="display: none;">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Productos con Stock Bajo</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>SKU</th>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Stock Actual</th>
                            <th>Stock Mínimo</th>
                            <th>Precio</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody id="tablaBajoStock"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Inventario General -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-list-ul"></i> Inventario General</h5>
            <div class="input-group" style="max-width: 300px;">
                <span class="input-group-text bg-white">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" class="form-control" id="buscarProducto" 
                       placeholder="Buscar producto...">
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>SKU</th>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Stock Actual</th>
                            <th>Stock Mínimo</th>
                            <th>Precio Venta</th>
                            <th>Valor Total</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody id="tablaInventario">
                        <tr><td colspan="8" class="text-center text-muted">Cargando...</td></tr>
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
let datosInventario = null;
let inventarioFiltrado = [];

async function cargarInventario() {
    try {
        mostrarCargando();
        
        const sucursalId = document.getElementById('filtroSucursal').value;
        let url = '/joyeria-torre-fuerte/api/reportes/inventario.php';
        
        if (sucursalId) {
            url += '?sucursal_id=' + sucursalId;
        }
        
        const res = await fetch(url);
        const data = await res.json();
        
        ocultarCargando();
        
        if (!data.success) {
            mostrarError(data.message || 'Error al cargar inventario');
            return;
        }
        
        datosInventario = data.data;
        inventarioFiltrado = datosInventario.inventario_detalle || [];
        
        mostrarInventario();
        
    } catch (error) {
        ocultarCargando();
        console.error('Error:', error);
        mostrarError('Error: ' + error.message);
    }
}

function mostrarInventario() {
    if (!datosInventario) return;
    
    mostrarKPIs();
    mostrarProductosSinStock();
    mostrarProductosBajoStock();
    mostrarInventarioGeneral();
}

function mostrarKPIs() {
    const resumen = datosInventario.resumen || {};
    
    document.getElementById('totalProductos').textContent = resumen.total_productos || 0;
    document.getElementById('totalUnidades').textContent = formatearNumero(resumen.total_unidades || 0);
    document.getElementById('productosBajoStock').textContent = resumen.productos_bajo_stock || 0;
    document.getElementById('productosSinStock').textContent = resumen.productos_sin_stock || 0;
}

function mostrarProductosSinStock() {
    const sinStock = datosInventario.productos_sin_stock || [];
    
    if (sinStock.length === 0) {
        document.getElementById('cardSinStock').style.display = 'none';
        return;
    }
    
    document.getElementById('cardSinStock').style.display = 'block';
    
    const tbody = document.getElementById('tablaSinStock');
    let html = '';
    
    sinStock.forEach(producto => {
        html += '<tr>';
        html += '<td><code>' + escaparHTML(producto.sku) + '</code></td>';
        html += '<td><strong>' + escaparHTML(producto.nombre) + '</strong></td>';
        html += '<td>' + escaparHTML(producto.categoria_nombre || '-') + '</td>';
        html += '<td>' + formatearMoneda(producto.precio_venta) + '</td>';
        html += '<td><span class="badge bg-warning">' + producto.stock_minimo + '</span></td>';
        html += '<td>' + (producto.ultima_venta ? formatearFecha(producto.ultima_venta) : 'Sin registro') + '</td>';
        html += '</tr>';
    });
    
    tbody.innerHTML = html;
}

function mostrarProductosBajoStock() {
    const bajoStock = datosInventario.productos_bajo_stock || [];
    
    if (bajoStock.length === 0) {
        document.getElementById('cardBajoStock').style.display = 'none';
        return;
    }
    
    document.getElementById('cardBajoStock').style.display = 'block';
    
    const tbody = document.getElementById('tablaBajoStock');
    let html = '';
    
    bajoStock.forEach(producto => {
        const stockActual = parseInt(producto.stock_actual) || 0;
        const stockMinimo = parseInt(producto.stock_minimo) || 0;
        const porcentaje = stockMinimo > 0 ? (stockActual / stockMinimo * 100) : 0;
        
        let badgeClass = 'bg-warning';
        if (porcentaje < 50) badgeClass = 'bg-danger';
        
        html += '<tr>';
        html += '<td><code>' + escaparHTML(producto.sku) + '</code></td>';
        html += '<td><strong>' + escaparHTML(producto.nombre) + '</strong></td>';
        html += '<td>' + escaparHTML(producto.categoria_nombre || '-') + '</td>';
        html += '<td><span class="badge ' + badgeClass + '">' + stockActual + '</span></td>';
        html += '<td>' + stockMinimo + '</td>';
        html += '<td>' + formatearMoneda(producto.precio_venta) + '</td>';
        html += '<td><span class="badge bg-warning">' + Math.round(porcentaje) + '%</span></td>';
        html += '</tr>';
    });
    
    tbody.innerHTML = html;
}

function mostrarInventarioGeneral() {
    const tbody = document.getElementById('tablaInventario');
    
    if (inventarioFiltrado.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No hay productos</td></tr>';
        return;
    }
    
    let html = '';
    
    inventarioFiltrado.forEach(producto => {
        const stockActual = parseInt(producto.stock_actual) || 0;
        const stockMinimo = parseInt(producto.stock_minimo) || 0;
        const precioVenta = parseFloat(producto.precio_venta) || 0;
        const valorTotal = stockActual * precioVenta;
        
        let estadoBadge = '<span class="badge bg-success">OK</span>';
        if (stockActual === 0) {
            estadoBadge = '<span class="badge bg-danger">Sin stock</span>';
        } else if (stockActual <= stockMinimo) {
            estadoBadge = '<span class="badge bg-warning">Bajo</span>';
        }
        
        html += '<tr>';
        html += '<td><code>' + escaparHTML(producto.sku) + '</code></td>';
        html += '<td><strong>' + escaparHTML(producto.nombre) + '</strong></td>';
        html += '<td>' + escaparHTML(producto.categoria_nombre || '-') + '</td>';
        html += '<td>' + formatearNumero(stockActual) + '</td>';
        html += '<td>' + stockMinimo + '</td>';
        html += '<td>' + formatearMoneda(precioVenta) + '</td>';
        html += '<td><strong>' + formatearMoneda(valorTotal) + '</strong></td>';
        html += '<td>' + estadoBadge + '</td>';
        html += '</tr>';
    });
    
    tbody.innerHTML = html;
}

function filtrarInventario() {
    const busqueda = document.getElementById('buscarProducto').value.toLowerCase().trim();
    
    if (!busqueda) {
        inventarioFiltrado = datosInventario.inventario_detalle || [];
    } else {
        inventarioFiltrado = (datosInventario.inventario_detalle || []).filter(p => {
            const nombre = (p.nombre || '').toLowerCase();
            const sku = (p.sku || '').toLowerCase();
            const categoria = (p.categoria_nombre || '').toLowerCase();
            
            return nombre.includes(busqueda) || sku.includes(busqueda) || categoria.includes(busqueda);
        });
    }
    
    mostrarInventarioGeneral();
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
        console.error('Error al cargar sucursales:', error);
    }
}

// Event Listeners
document.getElementById('filtroSucursal').addEventListener('change', cargarInventario);
document.getElementById('buscarProducto').addEventListener('input', filtrarInventario);

document.addEventListener('DOMContentLoaded', function() {
    cargarSucursales();
    cargarInventario();
});

console.log('✅ Reporte de Inventario cargado correctamente');
</script>