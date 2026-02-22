<?php
/**
 * ================================================
 * MÓDULO INVENTARIO - VER PRODUCTO
 * ================================================
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();

$producto_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$producto_id) {
    header('Location: lista.php');
    exit;
}

require_once '../../includes/header.php';
require_once '../../includes/navbar.php';
?>

<div class="container-fluid px-4 py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-1"><i class="bi bi-eye"></i> Detalles del Producto</h2>
            <p class="text-muted mb-0">Información completa y movimientos</p>
        </div>
        <div>
            <?php if (tiene_permiso('inventario', 'editar')): ?>
            <a href="editar.php?id=<?php echo $producto_id; ?>" class="btn btn-warning me-2">
                <i class="bi bi-pencil"></i> Editar
            </a>
            <?php endif; ?>
            <a href="lista.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <hr class="border-warning border-2 opacity-75 mb-4">

    <div class="row">
        <!-- Información del Producto -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Información del Producto</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Código</label>
                            <h5 id="codigo" class="text-primary">-</h5>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Código de Barras</label>
                            <div class="d-flex align-items-center">
                                <code id="codigo_barras" class="fs-5">-</code>
                                <button type="button" class="btn btn-sm btn-outline-primary ms-2" id="btnVerBarcode">
                                    <i class="bi bi-upc-scan"></i> Ver
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Nombre</label>
                        <h4 id="nombre">-</h4>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Descripción</label>
                        <p id="descripcion" class="text-secondary">-</p>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label text-muted">Categoría</label>
                            <p id="categoria">-</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted">Proveedor</label>
                            <p id="proveedor">-</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted">Precio Público</label>
                            <h5 id="precio" class="text-success">-</h5>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label text-muted">Peso</label>
                            <p id="peso">-</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted">Estilo</label>
                            <p id="estilo">-</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted">Largo</label>
                            <p id="largo">-</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Estado</label>
                            <p><span id="estado" class="badge">-</span></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Fecha Creación</label>
                            <p id="fecha_creacion">-</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Movimientos de Inventario -->
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Últimos Movimientos</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Sucursal</th>
                                    <th>Cantidad</th>
                                    <th>Motivo</th>
                                    <th>Usuario</th>
                                </tr>
                            </thead>
                            <tbody id="tablaMovimientos">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock por Sucursal -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-boxes"></i> Stock por Sucursal</h5>
                </div>
                <div class="card-body" id="stockPorSucursal">
                    <p class="text-center text-muted">Cargando...</p>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Modal Código de Barras -->
<div class="modal fade" id="modalBarcode" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-upc-scan"></i> Código de Barras</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <h5 id="modalProductoNombre" class="mb-2">-</h5>
                <p id="modalCategoria" class="text-muted mb-4">-</p>
                
                <div class="bg-light p-4 rounded mb-3">
                    <svg id="barcodeSvg"></svg>
                </div>
                
                <h3 id="modalCodigoBarras" class="font-monospace text-primary">-</h3>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btnCopiarCodigo">
                    <i class="bi bi-clipboard"></i> Copiar Código
                </button>
                <button type="button" class="btn btn-success" id="btnDescargarBarcode">
                    <i class="bi bi-download"></i> Descargar
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>

<script src="../../assets/js/vendors/sweetalert2/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script src="../../assets/js/common.js"></script>
<script src="../../assets/js/api-client.js"></script>

<script>
const productoId = <?php echo $producto_id; ?>;
let producto = null;
let modalBarcode = null;

async function cargarProducto() {
    try {
        mostrarCargando();
        
        const res = await api.listarProductos();
        const productos = res.data.productos || [];
        producto = productos.find(p => p.id == productoId);
        
        if (!producto) {
            ocultarCargando();
            await mostrarError('Producto no encontrado');
            window.location.href = 'lista.php';
            return;
        }
        
        mostrarDatos(producto);
        await cargarStock();
        await cargarMovimientos();
        
        ocultarCargando();
        
    } catch (error) {
        ocultarCargando();
        console.error('Error:', error);
        mostrarError('Error al cargar producto');
    }
}

function mostrarDatos(prod) {
    document.getElementById('codigo').textContent = prod.codigo || '-';
    document.getElementById('codigo_barras').textContent = prod.codigo_barras || 'No generado';
    document.getElementById('nombre').textContent = prod.nombre || '-';
    document.getElementById('descripcion').textContent = prod.descripcion || 'Sin descripción';
    document.getElementById('categoria').textContent = prod.categoria_nombre || '-';
    document.getElementById('proveedor').textContent = prod.proveedor_nombre || 'Sin proveedor';
    document.getElementById('precio').textContent = prod.precio_publico ? formatearMoneda(prod.precio_publico) : '-';
    document.getElementById('peso').textContent = prod.peso_gramos ? prod.peso_gramos + 'g' : '-';
    document.getElementById('estilo').textContent = prod.estilo || '-';
    document.getElementById('largo').textContent = prod.largo_cm || '-';
    
    const badge = document.getElementById('estado');
    badge.textContent = prod.activo == 1 ? 'Activo' : 'Inactivo';
    badge.className = prod.activo == 1 ? 'badge bg-success' : 'badge bg-danger';
    
    document.getElementById('fecha_creacion').textContent = formatearFechaHora(prod.fecha_creacion);
    
    // Habilitar botón de barcode solo si existe
    document.getElementById('btnVerBarcode').disabled = !prod.codigo_barras;
}

async function cargarStock() {
    try {
        const params = new URLSearchParams({ producto_id: productoId });
        const res = await fetch(`/api/inventario/listar.php?${params}`);
        const data = await res.json();
        
        const container = document.getElementById('stockPorSucursal');
        
        if (!data.success || !data.data.inventario || data.data.inventario.length === 0) {
            container.innerHTML = '<p class="text-center text-muted">Sin stock registrado</p>';
            return;
        }
        
        let html = '';
        data.data.inventario.forEach(inv => {
            const cantidad = parseInt(inv.cantidad) || 0;
            const minimo = inv.stock_minimo || 5;
            
            let badgeClass = 'bg-success';
            let estado = 'Disponible';
            
            if (cantidad === 0) {
                badgeClass = 'bg-danger';
                estado = 'Agotado';
            } else if (cantidad <= minimo) {
                badgeClass = 'bg-warning text-dark';
                estado = 'Stock Bajo';
            }
            
            html += `
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1">${escaparHTML(inv.sucursal_nombre)}</h6>
                            <small class="text-muted">Mínimo: ${minimo}</small>
                        </div>
                        <div class="text-end">
                            <h4 class="mb-0">${cantidad}</h4>
                            <span class="badge ${badgeClass}">${estado}</span>
                        </div>
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
        
    } catch (error) {
        console.error('Error al cargar stock:', error);
    }
}

async function cargarMovimientos() {
    try {
        const params = new URLSearchParams({ 
            producto_id: productoId,
            limit: 20
        });
        
        const res = await fetch(`/api/movimientos_inventario/listar.php?${params}`);
        const data = await res.json();
        
        const tbody = document.getElementById('tablaMovimientos');
        
        if (!data.success || !data.data || data.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No hay movimientos registrados</td></tr>';
            return;
        }
        
        let html = '';
        data.data.forEach(mov => {
            const tipoClass = {
                'ingreso': 'text-success',
                'entrada': 'text-success',
                'salida': 'text-danger',
                'ajuste': 'text-warning',
                'transferencia': 'text-info',
                'venta': 'text-primary'
            };
            
            const clase = tipoClass[mov.tipo_movimiento] || '';
            
            html += `
                <tr>
                    <td><small>${formatearFechaHora(mov.fecha_hora)}</small></td>
                    <td><span class="${clase}">${escaparHTML(mov.tipo_movimiento)}</span></td>
                    <td>${escaparHTML(mov.sucursal_nombre || '-')}</td>
                    <td><strong>${mov.cantidad}</strong></td>
                    <td><small>${escaparHTML(mov.motivo || '-')}</small></td>
                    <td><small>${escaparHTML(mov.usuario_nombre || '-')}</small></td>
                </tr>
            `;
        });
        
        tbody.innerHTML = html;
        
    } catch (error) {
        console.error('Error al cargar movimientos:', error);
    }
}

// Modal de código de barras
document.getElementById('btnVerBarcode').addEventListener('click', function() {
    if (!producto || !producto.codigo_barras) {
        mostrarError('No hay código de barras generado');
        return;
    }
    
    document.getElementById('modalProductoNombre').textContent = producto.nombre;
    document.getElementById('modalCategoria').textContent = producto.categoria_nombre;
    document.getElementById('modalCodigoBarras').textContent = producto.codigo_barras;
    
    // Generar barcode SOLO con el código numérico
    JsBarcode("#barcodeSvg", producto.codigo_barras, {
        format: "EAN13",
        width: 2,
        height: 80,
        displayValue: true,  // ← Mostrar el código de barras
        fontSize: 14,
        margin: 10
    });
    
    // Agregar nombre del producto debajo en el HTML
    const container = document.querySelector('#barcodeSvg').parentElement;
    let nombreElement = container.querySelector('.producto-nombre');
    if (!nombreElement) {
        nombreElement = document.createElement('div');
        nombreElement.className = 'producto-nombre text-center mt-2';
        container.appendChild(nombreElement);
    }
    nombreElement.innerHTML = `<strong>${escaparHTML(producto.nombre)}</strong>`;
    
    const modal = new bootstrap.Modal(document.getElementById('modalBarcode'));
    modal.show();
    modalBarcode = modal;
});

document.getElementById('btnCopiarCodigo').addEventListener('click', function() {
    if (!producto || !producto.codigo_barras) return;
    
    navigator.clipboard.writeText(producto.codigo_barras).then(() => {
        mostrarExito('Código copiado al portapapeles');
    }).catch(() => {
        mostrarError('No se pudo copiar');
    });
});

document.getElementById('btnDescargarBarcode').addEventListener('click', function() {
    const svg = document.getElementById('barcodeSvg');
    const nombreProducto = producto.nombre;
    
    // Crear canvas
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    
    // Tamaño del canvas
    canvas.width = 400;
    canvas.height = 200;
    
    // Fondo blanco
    ctx.fillStyle = 'white';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    
    // Convertir SVG a imagen
    const svgData = new XMLSerializer().serializeToString(svg);
    const img = new Image();
    
    img.onload = function() {
        // Centrar el barcode
        const x = (canvas.width - img.width) / 2;
        const y = 20;
        ctx.drawImage(img, x, y);
        
        // Agregar nombre del producto debajo
        ctx.fillStyle = 'black';
        ctx.font = 'bold 16px Arial';
        ctx.textAlign = 'center';
        ctx.fillText(nombreProducto, canvas.width / 2, y + img.height + 30);
        
        // Descargar como JPG
        canvas.toBlob(function(blob) {
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `barcode_${producto.codigo}.jpg`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
            
            mostrarExito('Código de barras descargado');
        }, 'image/jpeg', 1.0);
    };
    
    img.src = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svgData)));
});

document.addEventListener('DOMContentLoaded', cargarProducto);
</script>