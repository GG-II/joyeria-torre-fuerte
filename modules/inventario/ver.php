<?php
/**
 * ================================================
 * MÓDULO INVENTARIO - VER DETALLES PRODUCTO
 * ================================================
 * 
 * TODO FASE 5: Conectar con API
 * GET /api/inventario/ver.php?id={producto_id}
 * 
 * Respuesta: { producto, precios, stock, movimientos }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();

$producto_id = $_GET['id'] ?? null;
if (!$producto_id) {
    header('Location: lista.php');
    exit;
}

$producto = null;
$titulo_pagina = 'Detalles del Producto';
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="container-fluid main-content">
    <div id="loadingState" class="text-center py-5">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
        <p class="mt-3 text-muted">Cargando detalles del producto...</p>
    </div>

    <div id="mainContent" style="display: none;">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>dashboard.php"><i class="bi bi-house"></i> Dashboard</a></li>
                <li class="breadcrumb-item"><a href="lista.php"><i class="bi bi-box-seam"></i> Inventario</a></li>
                <li class="breadcrumb-item active" id="breadcrumbCodigo">-</li>
            </ol>
        </nav>

        <div class="card mb-4 shadow-sm" style="border-left: 5px solid #d4af37;">
            <div class="card-body">
                <div class="row align-items-center g-3">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-primary text-white me-3 d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; border-radius: 12px;">
                                <i class="bi bi-gem" style="font-size: 28px;"></i>
                            </div>
                            <div>
                                <h2 class="mb-2" id="productoNombre">-</h2>
                                <div class="d-flex flex-wrap gap-3 text-muted">
                                    <span id="productoCodigo"><i class="bi bi-upc-scan"></i> -</span>
                                    <span id="productoCategoria"><i class="bi bi-folder"></i> -</span>
                                    <span id="productoEstado"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                            <?php if (tiene_permiso('inventario', 'editar')): ?>
                            <a href="editar.php?id=<?php echo $producto_id; ?>" class="btn btn-warning">
                                <i class="bi bi-pencil"></i> <span class="d-none d-sm-inline">Editar</span>
                            </a>
                            <?php endif; ?>
                            <a href="lista.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> <span class="d-none d-sm-inline">Volver</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card mb-3 shadow-sm">
                    <div class="card-header" style="background-color: #1e3a8a; color: white;">
                        <i class="bi bi-info-circle"></i> Información del Producto
                    </div>
                    <div class="card-body" id="infoProducto">
                        <div class="text-center py-3"><div class="spinner-border spinner-border-sm"></div></div>
                    </div>
                </div>

                <div class="card mb-3 shadow-sm">
                    <div class="card-header bg-success text-white">
                        <i class="bi bi-cash-coin"></i> Precios y Costos
                    </div>
                    <div class="card-body" id="infoPrecio">
                        <div class="text-center py-3"><div class="spinner-border spinner-border-sm"></div></div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header" style="background-color: #1e3a8a; color: white;">
                        <i class="bi bi-building"></i> Stock por Sucursal
                    </div>
                    <div class="card-body" id="infoStock">
                        <div class="text-center py-3"><div class="spinner-border spinner-border-sm"></div></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="row g-3 mb-4" id="estadisticas">
                    <div class="col-6 col-md-3">
                        <div class="stat-card azul">
                            <div class="stat-icon"><i class="bi bi-boxes"></i></div>
                            <div class="stat-value" id="statStockTotal">0</div>
                            <div class="stat-label">Stock Total</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-card verde">
                            <div class="stat-icon"><i class="bi bi-cash-stack"></i></div>
                            <div class="stat-value" id="statValorTotal">Q 0</div>
                            <div class="stat-label">Valor Total</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-card amarillo">
                            <div class="stat-icon"><i class="bi bi-exclamation-triangle"></i></div>
                            <div class="stat-value" id="statStockMin">0</div>
                            <div class="stat-label">Stock Mínimo</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-card dorado">
                            <div class="stat-icon"><i class="bi bi-graph-up"></i></div>
                            <div class="stat-value" id="statGanancia">Q 0</div>
                            <div class="stat-label">Ganancia/Unidad</div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header">
                        <i class="bi bi-clock-history"></i> <span id="tituloMovimientos">Historial de Movimientos</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background-color: #1e3a8a; color: white;">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Cantidad</th>
                                    <th class="d-none d-md-table-cell">Sucursal</th>
                                    <th class="d-none d-lg-table-cell">Motivo</th>
                                    <th class="d-none d-xl-table-cell">Usuario</th>
                                </tr>
                            </thead>
                            <tbody id="movimientosBody">
                                <tr><td colspan="6" class="text-center py-4"><div class="spinner-border spinner-border-sm"></div></td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer" id="movimientosFooter" style="display: none;">
                        <small class="text-muted" id="contadorMovimientos">Mostrando 0 movimientos</small>
                    </div>
                </div>

                <?php if (tiene_permiso('inventario', 'editar')): ?>
                <div class="card mt-3 shadow-sm">
                    <div class="card-header"><i class="bi bi-gear"></i> Acciones Disponibles</div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <a href="transferencias.php?producto_id=<?php echo $producto_id; ?>" class="btn btn-warning w-100">
                                    <i class="bi bi-arrow-left-right"></i> Transferir Stock
                                </a>
                            </div>
                            <div class="col-md-4">
                                <button class="btn btn-success w-100" onclick="entradaStock()">
                                    <i class="bi bi-plus-circle"></i> Entrada de Stock
                                </button>
                            </div>
                            <div class="col-md-4">
                                <button class="btn btn-info w-100" onclick="ajusteInventario()">
                                    <i class="bi bi-arrow-repeat"></i> Ajuste
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div id="errorState" style="display: none;" class="text-center py-5">
        <i class="bi bi-exclamation-triangle text-danger" style="font-size: 48px;"></i>
        <h4 class="mt-3">Error al cargar el producto</h4>
        <p class="text-muted" id="errorMessage">No se pudo cargar la información.</p>
        <a href="lista.php" class="btn btn-primary mt-3"><i class="bi bi-arrow-left"></i> Volver al listado</a>
    </div>
</div>

<style>
.main-content { padding: 20px; min-height: calc(100vh - 120px); }
.shadow-sm { box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08) !important; }
.stat-card { background: white; border-radius: 12px; padding: 15px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08); transition: transform 0.2s ease; border-left: 4px solid; height: 100%; }
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12); }
.stat-card.azul { border-left-color: #1e3a8a; }
.stat-card.verde { border-left-color: #22c55e; }
.stat-card.amarillo { border-left-color: #eab308; }
.stat-card.dorado { border-left-color: #d4af37; }
.stat-icon { width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 22px; margin-bottom: 12px; }
.stat-card.azul .stat-icon { background: rgba(30, 58, 138, 0.1); color: #1e3a8a; }
.stat-card.verde .stat-icon { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
.stat-card.amarillo .stat-icon { background: rgba(234, 179, 8, 0.1); color: #eab308; }
.stat-card.dorado .stat-icon { background: rgba(212, 175, 55, 0.1); color: #d4af37; }
.stat-value { font-size: 1.5rem; font-weight: 700; color: #1a1a1a; margin: 8px 0; }
.stat-label { font-size: 0.8rem; color: #6b7280; font-weight: 500; }
.card-body > div:not(:last-child) { padding-bottom: 12px; margin-bottom: 12px; border-bottom: 1px solid #e5e7eb; }
table thead th { font-weight: 600; font-size: 0.85rem; text-transform: uppercase; padding: 12px; }
table tbody td { padding: 12px; vertical-align: middle; }
@media (max-width: 575.98px) {
    .main-content { padding: 15px 10px; }
    h2 { font-size: 1.5rem; }
    .stat-card { padding: 12px; }
    .stat-icon { width: 38px; height: 38px; font-size: 18px; margin-bottom: 8px; }
    .stat-value { font-size: 1.25rem; }
    .stat-label { font-size: 0.75rem; }
    table { font-size: 0.85rem; }
    table thead th, table tbody td { padding: 8px 6px; }
}
@media (min-width: 576px) and (max-width: 767.98px) { .main-content { padding: 18px 15px; } }
@media (min-width: 992px) { .main-content { padding: 25px 30px; } }
@media (max-width: 767.98px) { .btn { min-height: 44px; } }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    cargarDetallesProducto();
});

function cargarDetallesProducto() {
    const productoId = <?php echo $producto_id; ?>;
    
    /* TODO FASE 5: Descomentar
    fetch('<?php echo BASE_URL; ?>api/inventario/ver.php?id=' + productoId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const { producto, precios, stock, movimientos } = data.data;
                renderizarProducto(producto, precios, stock);
                renderizarEstadisticas(producto, precios, stock);
                renderizarMovimientos(movimientos);
            } else {
                mostrarError(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarError('Error al cargar los datos');
        });
    */
    
    setTimeout(() => mostrarError('MODO DESARROLLO: Esperando API'), 1500);
}

function renderizarProducto(producto, precios, stock) {
    document.getElementById('loadingState').style.display = 'none';
    document.getElementById('mainContent').style.display = 'block';
    
    document.getElementById('breadcrumbCodigo').textContent = producto.codigo;
    document.getElementById('productoNombre').textContent = producto.nombre;
    document.getElementById('productoCodigo').innerHTML = '<i class="bi bi-upc-scan"></i> ' + producto.codigo;
    document.getElementById('productoCategoria').innerHTML = '<i class="bi bi-folder"></i> ' + producto.categoria;
    document.getElementById('productoEstado').innerHTML = producto.activo == 1 
        ? '<span class="badge bg-success">Activo</span>' 
        : '<span class="badge bg-secondary">Inactivo</span>';
    
    document.getElementById('infoProducto').innerHTML = `
        <div><small class="text-muted d-block">Descripción</small><p class="mb-0">${producto.descripcion || 'Sin descripción'}</p></div>
        <div><small class="text-muted d-block">Peso</small><strong>${producto.peso_gramos || 0} gramos</strong></div>
        <div><small class="text-muted d-block">Estilo</small><strong>${producto.estilo || '-'}</strong></div>
        <div><small class="text-muted d-block">Fecha de Creación</small><strong>${formatearFecha(producto.fecha_creacion)}</strong></div>
    `;
    
    const margen = ((precios.precio_publico - precios.costo) / precios.costo * 100);
    document.getElementById('infoPrecio').innerHTML = `
        <div><small class="text-muted d-block">Precio Público</small><h4 class="mb-0 text-success">Q ${formatearMoneda(precios.precio_publico)}</h4></div>
        <div><small class="text-muted d-block">Precio Mayorista</small><h4 class="mb-0 text-primary">Q ${formatearMoneda(precios.precio_mayorista)}</h4></div>
        <div><small class="text-muted d-block">Margen</small><h4 class="mb-0 text-info">${margen.toFixed(2)}%</h4></div>
    `;
    
    const stockTotal = parseInt(stock.los_arcos) + parseInt(stock.chinaca);
    const porcentajeLosArcos = (stock.los_arcos / stockTotal * 100);
    const porcentajeChinaca = (stock.chinaca / stockTotal * 100);
    
    document.getElementById('infoStock').innerHTML = `
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span>Los Arcos</span>
                <span class="badge" style="background-color: #1e3a8a;">${stock.los_arcos}</span>
            </div>
            <div class="progress" style="height: 20px;">
                <div class="progress-bar" style="width: ${porcentajeLosArcos}%; background-color: #1e3a8a;">${porcentajeLosArcos.toFixed(0)}%</div>
            </div>
        </div>
        <div>
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span>Chinaca Central</span>
                <span class="badge bg-warning">${stock.chinaca}</span>
            </div>
            <div class="progress" style="height: 20px;">
                <div class="progress-bar bg-warning" style="width: ${porcentajeChinaca}%">${porcentajeChinaca.toFixed(0)}%</div>
            </div>
        </div>
    `;
}

function renderizarEstadisticas(producto, precios, stock) {
    const stockTotal = parseInt(stock.los_arcos) + parseInt(stock.chinaca);
    const valorTotal = precios.precio_publico * stockTotal;
    const gananciaUnidad = precios.precio_publico - precios.costo;
    
    document.getElementById('statStockTotal').textContent = stockTotal;
    document.getElementById('statValorTotal').textContent = 'Q ' + formatearMoneda(valorTotal);
    document.getElementById('statStockMin').textContent = producto.stock_minimo;
    document.getElementById('statGanancia').textContent = 'Q ' + formatearMoneda(gananciaUnidad);
}

function renderizarMovimientos(movimientos) {
    const tbody = document.getElementById('movimientosBody');
    
    if (movimientos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-5"><i class="bi bi-inbox" style="font-size: 48px; opacity: 0.3;"></i><p class="mt-3 text-muted">No hay movimientos registrados</p></td></tr>';
        return;
    }
    
    let html = '';
    movimientos.forEach(mov => {
        html += `
            <tr>
                <td><small>${formatearFecha(mov.fecha)}</small></td>
                <td>${getBadgeTipo(mov.tipo)}</td>
                <td class="fw-bold">${getCantidad(mov.tipo, mov.cantidad)}</td>
                <td class="d-none d-md-table-cell">${mov.sucursal}</td>
                <td class="d-none d-lg-table-cell">${mov.motivo}</td>
                <td class="d-none d-xl-table-cell"><small class="text-muted">${mov.usuario}</small></td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    document.getElementById('movimientosFooter').style.display = 'block';
    document.getElementById('contadorMovimientos').textContent = `Mostrando ${movimientos.length} movimientos`;
    document.getElementById('tituloMovimientos').textContent = `Historial de Movimientos (${movimientos.length})`;
}

function mostrarError(mensaje) {
    document.getElementById('loadingState').style.display = 'none';
    document.getElementById('errorState').style.display = 'block';
    document.getElementById('errorMessage').textContent = mensaje;
}

function formatearMoneda(monto) {
    return parseFloat(monto).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function formatearFecha(fecha) {
    const d = new Date(fecha);
    return d.toLocaleDateString('es-GT', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function getBadgeTipo(tipo) {
    const badges = {
        'entrada': '<span class="badge bg-success">Entrada</span>',
        'salida': '<span class="badge bg-danger">Salida</span>',
        'transferencia': '<span class="badge bg-warning">Transferencia</span>',
        'ajuste': '<span class="badge" style="background-color: #1e3a8a;">Ajuste</span>'
    };
    return badges[tipo] || '';
}

function getCantidad(tipo, cantidad) {
    if (tipo === 'entrada') return '<span class="text-success">+' + cantidad + '</span>';
    if (tipo === 'salida') return '<span class="text-danger">-' + cantidad + '</span>';
    return cantidad;
}

function entradaStock() { alert('MODO DESARROLLO: Entrada de stock - Pendiente implementar'); }
function ajusteInventario() { alert('MODO DESARROLLO: Ajuste de inventario - Pendiente implementar'); }
</script>

<?php include '../../includes/footer.php'; ?>