<?php
/**
 * ================================================
 * MÓDULO INVENTARIO - EDITAR PRODUCTO
 * ================================================
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();
requiere_rol(['administrador', 'dueño', 'vendedor']);

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
            <h2 class="mb-1"><i class="bi bi-pencil"></i> Editar Producto</h2>
            <p class="text-muted mb-0">Modificar datos y ajustar stock</p>
        </div>
        <a href="ver.php?id=<?php echo $producto_id; ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    <hr class="border-warning border-2 opacity-75 mb-4">

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Datos del Producto</h5>
                </div>
                <div class="card-body">
                    <form id="formProducto">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="codigo" class="form-label">
                                    Código del Producto <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="codigo" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Código de Barras</label>
                                <input type="text" class="form-control" id="codigo_barras" readonly>
                                <small class="text-muted">No se puede modificar</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="nombre" class="form-label">
                                Nombre del Producto <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="nombre" required>
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" rows="2"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="categoria_id" class="form-label">
                                    Categoría <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="categoria_id" required>
                                    <option value="">Seleccione...</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="precio_publico" class="form-label">
                                    Precio Público (Q) <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control" id="precio_publico" 
                                       step="0.01" min="0" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="proveedor_id" class="form-label">Proveedor</label>
                            <select class="form-select" id="proveedor_id">
                                <option value="">Sin proveedor</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="peso_gramos" class="form-label">Peso (gramos)</label>
                                <input type="number" class="form-control" id="peso_gramos" 
                                       step="0.01" min="0">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="estilo" class="form-label">Estilo</label>
                                <select class="form-select" id="estilo">
                                    <option value="">Sin estilo</option>
                                    <option value="Clásico">Clásico</option>
                                    <option value="Moderno">Moderno</option>
                                    <option value="Elegante">Elegante</option>
                                    <option value="Casual">Casual</option>
                                    <option value="Vintage">Vintage</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="largo_cm" class="form-label">Largo (cm)</label>
                                <input type="text" class="form-control" id="largo_cm">
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="activo">
                                <label class="form-check-label" for="activo">Producto Activo</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle"></i> Guardar Cambios
                        </button>

                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-box-seam"></i> Ajustar Stock</h5>
                </div>
                <div class="card-body">
                    
                    <div class="mb-3">
                        <label for="sucursal_ajuste" class="form-label">
                            Sucursal <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="sucursal_ajuste">
                            <option value="">Seleccione...</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Stock Actual</label>
                        <h3 id="stockActual" class="text-primary">-</h3>
                    </div>

                    <div class="mb-3">
                        <label for="tipo_ajuste" class="form-label">Tipo de Ajuste</label>
                        <select class="form-select" id="tipo_ajuste">
                            <option value="manual">Establecer cantidad exacta</option>
                            <option value="entrada">Incrementar (entrada)</option>
                            <option value="salida">Decrementar (salida)</option>
                        </select>
                    </div>

                    <div class="mb-3" id="campoManual">
                        <label for="cantidad_nueva" class="form-label">
                            Cantidad Nueva <span class="text-danger">*</span>
                        </label>
                        <input type="number" class="form-control form-control-lg" 
                               id="cantidad_nueva" min="0">
                    </div>

                    <div class="mb-3 d-none" id="campoIncremento">
                        <label for="cantidad_ajuste" class="form-label">
                            Cantidad <span class="text-danger">*</span>
                        </label>
                        <input type="number" class="form-control form-control-lg" 
                               id="cantidad_ajuste" min="1">
                    </div>

                    <div class="mb-3">
                        <label for="motivo_ajuste" class="form-label">
                            Motivo <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="motivo_ajuste" rows="2" 
                                  placeholder="Ej: Inventario físico, devolución, corrección"></textarea>
                    </div>

                    <button type="button" class="btn btn-success btn-lg w-100" id="btnAjustarStock">
                        <i class="bi bi-arrow-repeat"></i> Ajustar Stock
                    </button>

                </div>
            </div>

            <!-- Stock por Sucursal -->
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-boxes"></i> Stock Actual</h5>
                </div>
                <div class="card-body" id="stockPorSucursal">
                    <p class="text-center text-muted">Cargando...</p>
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
const productoId = <?php echo $producto_id; ?>;
let producto = null;
let inventarioActual = [];

async function cargarDatos() {
    try {
        mostrarCargando();
        
        // Cargar categorías
        const resCat = await api.listarCategorias();
        if (resCat.success) {
            let categorias = resCat.data;
            if (!Array.isArray(categorias)) {
                categorias = categorias.categorias || [];
            }
            
            const select = document.getElementById('categoria_id');
            categorias.forEach(cat => {
                const option = document.createElement('option');
                option.value = cat.id;
                option.textContent = cat.nombre;
                select.appendChild(option);
            });
        }
        
        // Cargar proveedores
        const resProv = await api.listarProveedores();
        if (resProv.success) {
            let proveedores = resProv.data;
            if (!Array.isArray(proveedores)) {
                proveedores = proveedores.proveedores || [];
            }
            
            const select = document.getElementById('proveedor_id');
            proveedores.forEach(prov => {
                const option = document.createElement('option');
                option.value = prov.id;
                option.textContent = prov.nombre;
                select.appendChild(option);
            });
        }
        
        // Cargar sucursales
        const resSuc = await api.listarSucursales({ activo: 1 });
        if (resSuc.success) {
            const sucursales = Array.isArray(resSuc.data) ? resSuc.data : [];
            const select = document.getElementById('sucursal_ajuste');
            sucursales.forEach(suc => {
                const option = document.createElement('option');
                option.value = suc.id;
                option.textContent = suc.nombre;
                select.appendChild(option);
            });
        }
        
        // Cargar producto
        await cargarProducto();
        await cargarStock();
        
        ocultarCargando();
        
    } catch (error) {
        ocultarCargando();
        console.error('Error:', error);
        mostrarError('Error al cargar datos');
    }
}

async function cargarProducto() {
    const res = await api.listarProductos();
    const productos = res.data.productos || [];
    producto = productos.find(p => p.id == productoId);
    
    if (!producto) {
        await mostrarError('Producto no encontrado');
        window.location.href = 'lista.php';
        return;
    }
    
    document.getElementById('codigo').value = producto.codigo || '';
    document.getElementById('codigo_barras').value = producto.codigo_barras || '';
    document.getElementById('nombre').value = producto.nombre || '';
    document.getElementById('descripcion').value = producto.descripcion || '';
    document.getElementById('categoria_id').value = producto.categoria_id || '';
    document.getElementById('precio_publico').value = producto.precio_publico || '';
    document.getElementById('proveedor_id').value = producto.proveedor_id || '';
    document.getElementById('peso_gramos').value = producto.peso_gramos || '';
    document.getElementById('estilo').value = producto.estilo || '';
    document.getElementById('largo_cm').value = producto.largo_cm || '';
    document.getElementById('activo').checked = producto.activo == 1;
}

async function cargarStock() {
    try {
        const params = new URLSearchParams({ producto_id: productoId });
        const res = await fetch(`/joyeria-torre-fuerte/api/inventario/listar.php?${params}`);
        const data = await res.json();
        
        const container = document.getElementById('stockPorSucursal');
        
        if (!data.success || !data.data.inventario || data.data.inventario.length === 0) {
            container.innerHTML = '<p class="text-center text-muted">Sin stock</p>';
            inventarioActual = [];
            return;
        }
        
        inventarioActual = data.data.inventario;
        
        let html = '';
        inventarioActual.forEach(inv => {
            const cantidad = parseInt(inv.cantidad) || 0;
            const minimo = inv.stock_minimo || 5;
            
            let badgeClass = 'bg-success';
            if (cantidad === 0) badgeClass = 'bg-danger';
            else if (cantidad <= minimo) badgeClass = 'bg-warning text-dark';
            
            html += `
                <div class="mb-2 pb-2 border-bottom">
                    <div class="d-flex justify-content-between">
                        <small>${escaparHTML(inv.sucursal_nombre)}</small>
                        <span class="badge ${badgeClass}">${cantidad}</span>
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
        
    } catch (error) {
        console.error('Error:', error);
    }
}

// Actualizar stock actual al cambiar sucursal
document.getElementById('sucursal_ajuste').addEventListener('change', function() {
    const sucId = parseInt(this.value);
    if (!sucId) {
        document.getElementById('stockActual').textContent = '-';
        return;
    }
    
    const inv = inventarioActual.find(i => i.sucursal_id == sucId);
    const cantidad = inv ? (parseInt(inv.cantidad) || 0) : 0;
    document.getElementById('stockActual').textContent = cantidad;
});

// Cambiar campos según tipo de ajuste
document.getElementById('tipo_ajuste').addEventListener('change', function() {
    const tipo = this.value;
    
    if (tipo === 'manual') {
        document.getElementById('campoManual').classList.remove('d-none');
        document.getElementById('campoIncremento').classList.add('d-none');
    } else {
        document.getElementById('campoManual').classList.add('d-none');
        document.getElementById('campoIncremento').classList.remove('d-none');
    }
});

// Guardar cambios del producto
document.getElementById('formProducto').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const proveedorVal = document.getElementById('proveedor_id').value;
    
    const datos = {
        id: productoId,
        codigo: document.getElementById('codigo').value.trim(),
        nombre: document.getElementById('nombre').value.trim(),
        descripcion: document.getElementById('descripcion').value.trim() || null,
        categoria_id: document.getElementById('categoria_id').value,
        precio_publico: document.getElementById('precio_publico').value,
        proveedor_id: proveedorVal && proveedorVal !== '' ? parseInt(proveedorVal) : null,
        peso_gramos: document.getElementById('peso_gramos').value || null,
        estilo: document.getElementById('estilo').value || null,
        largo_cm: document.getElementById('largo_cm').value || null,
        activo: document.getElementById('activo').checked ? 1 : 0
    };
    
    const confirmacion = await confirmarAccion('¿Guardar los cambios?');
    if (!confirmacion) return;
    
    try {
        mostrarCargando();
        
        const formData = new URLSearchParams();
        Object.keys(datos).forEach(key => {
            if (datos[key] !== null && datos[key] !== undefined) {
                formData.append(key, datos[key]);
            }
        });
        
        const res = await fetch('/joyeria-torre-fuerte/api/productos/actualizar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData.toString()
        });
        
        const resultado = await res.json();
        
        ocultarCargando();
        
        if (resultado.success) {
            await mostrarExito('Producto actualizado exitosamente');
            window.location.reload();
        } else {
            mostrarError(resultado.message || 'Error al actualizar');
        }
        
    } catch (error) {
        ocultarCargando();
        console.error('Error:', error);
        mostrarError('Error: ' + error.message);
    }
});

// Ajustar stock
document.getElementById('btnAjustarStock').addEventListener('click', async function() {
    const sucId = document.getElementById('sucursal_ajuste').value;
    const tipoAjuste = document.getElementById('tipo_ajuste').value;
    const motivo = document.getElementById('motivo_ajuste').value.trim();
    
    if (!sucId) {
        mostrarError('Seleccione una sucursal');
        return;
    }
    
    if (!motivo) {
        mostrarError('Ingrese el motivo del ajuste');
        return;
    }
    
    const formData = new URLSearchParams();
    formData.append('producto_id', productoId);
    formData.append('sucursal_id', sucId);
    formData.append('tipo_ajuste', tipoAjuste);
    formData.append('motivo', motivo);
    
    if (tipoAjuste === 'manual') {
        const cantidadNueva = document.getElementById('cantidad_nueva').value;
        if (!cantidadNueva || cantidadNueva === '') {
            mostrarError('Ingrese la cantidad nueva');
            return;
        }
        formData.append('cantidad_nueva', cantidadNueva);
    } else {
        const cantidad = document.getElementById('cantidad_ajuste').value;
        if (!cantidad || cantidad === '' || cantidad <= 0) {
            mostrarError('Ingrese una cantidad válida');
            return;
        }
        formData.append('cantidad', cantidad);
    }
    
    const confirmacion = await confirmarAccion('¿Confirmar ajuste de stock?');
    if (!confirmacion) return;
    
    try {
        mostrarCargando();
        
        const res = await fetch('/joyeria-torre-fuerte/api/inventario/ajustar_stock.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData.toString()
        });
        
        const resultado = await res.json();
        
        ocultarCargando();
        
        if (resultado.success) {
            await mostrarExito(`Stock ajustado: ${resultado.data.cantidad_anterior} → ${resultado.data.cantidad_nueva}`);
            
            // Limpiar formulario
            document.getElementById('motivo_ajuste').value = '';
            document.getElementById('cantidad_nueva').value = '';
            document.getElementById('cantidad_ajuste').value = '';
            
            // Recargar stock
            await cargarStock();
            
            // Actualizar stock actual
            document.getElementById('sucursal_ajuste').dispatchEvent(new Event('change'));
        } else {
            mostrarError(resultado.message || resultado.error || 'Error al ajustar stock');
        }
        
    } catch (error) {
        ocultarCargando();
        console.error('Error:', error);
        mostrarError('Error: ' + error.message);
    }
});

document.addEventListener('DOMContentLoaded', cargarDatos);
</script>