<?php
/**
 * ================================================
 * MÓDULO INVENTARIO - AGREGAR PRODUCTO
 * ================================================
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();
requiere_rol(['administrador', 'dueño', 'vendedor']);

require_once '../../includes/header.php';
require_once '../../includes/navbar.php';
?>

<div class="container-fluid px-4 py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-1"><i class="bi bi-plus-circle"></i> Nuevo Producto</h2>
            <p class="text-muted mb-0">Crear producto y agregar stock inicial</p>
        </div>
        <a href="lista.php" class="btn btn-secondary">
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
                                <input type="text" class="form-control" id="codigo" 
                                       placeholder="Ej: AN001" required>
                                <small class="text-muted">Código interno único</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Código de Barras</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="codigo_barras" 
                                           placeholder="Se generará automáticamente" readonly>
                                    <button type="button" class="btn btn-outline-primary" id="btnGenerarBarcode">
                                        <i class="bi bi-arrow-clockwise"></i> Generar
                                    </button>
                                </div>
                                <small class="text-muted">EAN-13 único</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="nombre" class="form-label">
                                Nombre del Producto <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="nombre" 
                                   placeholder="Ej: Anillo de Oro 18K con Diamante" required>
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" rows="2" 
                                      placeholder="Descripción detallada del producto"></textarea>
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
                                    placeholder="0.00" step="0.01" min="0" required>
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
                                       placeholder="0.00" step="0.01" min="0">
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
                                <input type="text" class="form-control" id="largo_cm" 
                                       placeholder="Ej: 18cm">
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="activo" checked>
                                <label class="form-check-label" for="activo">Producto Activo</label>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-box-seam"></i> Stock Inicial</h5>
                </div>
                <div class="card-body">
                    
                    <div class="mb-3">
                        <label for="sucursal_id" class="form-label">
                            Sucursal <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="sucursal_id" required>
                            <option value="">Seleccione...</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="cantidad_inicial" class="form-label">
                            Cantidad Inicial <span class="text-danger">*</span>
                        </label>
                        <input type="number" class="form-control form-control-lg" 
                               id="cantidad_inicial" placeholder="0" min="0" required>
                    </div>

                    <div class="mb-4">
                        <label for="stock_minimo" class="form-label">Stock Mínimo</label>
                        <input type="number" class="form-control" id="stock_minimo" 
                               placeholder="5" min="0" value="5">
                        <small class="text-muted">Alerta cuando llegue a este número</small>
                    </div>

                    <button type="submit" form="formProducto" class="btn btn-success btn-lg w-100">
                        <i class="bi bi-check-circle"></i> Crear Producto
                    </button>

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
// Generar código de barras EAN-13 único
function generarCodigoBarras() {
    // Generar 12 dígitos aleatorios
    let codigo = '';
    for (let i = 0; i < 12; i++) {
        codigo += Math.floor(Math.random() * 10);
    }
    
    // Calcular dígito verificador EAN-13
    let sum = 0;
    for (let i = 0; i < 12; i++) {
        const digit = parseInt(codigo[i]);
        sum += (i % 2 === 0) ? digit : digit * 3;
    }
    const checkDigit = (10 - (sum % 10)) % 10;
    
    return codigo + checkDigit;
}

document.getElementById('btnGenerarBarcode').addEventListener('click', function() {
    const codigoBarras = generarCodigoBarras();
    document.getElementById('codigo_barras').value = codigoBarras;
    mostrarExito('Código de barras generado: ' + codigoBarras);
});

async function cargarDatos() {
    try {
        // Cargar categorías
        const resCat = await api.listarCategorias(); // Sin filtro
        if (resCat.success) {
            // Extraer categorías del formato paginado
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
            
            console.log('Proveedores cargados:', proveedores); // DEBUG
            
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
            const select = document.getElementById('sucursal_id');
            sucursales.forEach(suc => {
                const option = document.createElement('option');
                option.value = suc.id;
                option.textContent = suc.nombre;
                select.appendChild(option);
            });
        }
        
        // Auto-generar código de barras
        document.getElementById('btnGenerarBarcode').click();
        
    } catch (error) {
        console.error('Error al cargar datos:', error);
    }
}

document.getElementById('formProducto').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const proveedorVal = document.getElementById('proveedor_id').value;

    const datosProducto = {
        codigo: document.getElementById('codigo').value.trim(),
        codigo_barras: document.getElementById('codigo_barras').value.trim(),
        nombre: document.getElementById('nombre').value.trim(),
        descripcion: document.getElementById('descripcion').value.trim() || null,
        categoria_id: document.getElementById('categoria_id').value,
        precio_publico: document.getElementById('precio_publico').value,
        proveedor_id: proveedorVal && proveedorVal !== '' ? parseInt(proveedorVal) : null, // ← FIX
        peso_gramos: document.getElementById('peso_gramos').value || null,
        estilo: document.getElementById('estilo').value || null,
        largo_cm: document.getElementById('largo_cm').value || null,
        activo: document.getElementById('activo').checked ? 1 : 0
    };

    console.log('DATOS A ENVIAR:', datosProducto); // DEBUG
    
    const sucursalId = document.getElementById('sucursal_id').value;
    const cantidadInicial = parseInt(document.getElementById('cantidad_inicial').value) || 0;
    const stockMinimo = parseInt(document.getElementById('stock_minimo').value) || 5;
    
    if (!datosProducto.codigo || !datosProducto.nombre || !datosProducto.categoria_id || !datosProducto.precio_publico) {
        mostrarError('Complete los campos requeridos del producto');
        return;
    }
    
    if (!sucursalId) {
        mostrarError('Seleccione una sucursal para el stock inicial');
        return;
    }
    
    if (!datosProducto.codigo_barras) {
        mostrarError('Genere un código de barras antes de continuar');
        return;
    }
    
    const confirmacion = await confirmarAccion('¿Crear este producto?');
    if (!confirmacion) return;
    
    try {
        mostrarCargando();
        
        console.log('=== DATOS PRODUCTO ===');
        console.log('Objeto completo:', datosProducto);
        console.log('Proveedor_id:', datosProducto.proveedor_id);
        console.log('Tipo de proveedor_id:', typeof datosProducto.proveedor_id);
        // 1. Crear producto (FormData)
        const formDataProducto = new URLSearchParams();
        Object.keys(datosProducto).forEach(key => {
            if (datosProducto[key] !== null && datosProducto[key] !== undefined) {
                formDataProducto.append(key, datosProducto[key]);
            }
        });

        formDataProducto.append('stock_los_arcos', sucursalId == 1 ? cantidadInicial : 0);
        formDataProducto.append('stock_chinaca', sucursalId == 2 ? cantidadInicial : 0);
        formDataProducto.append('stock_minimo', stockMinimo);
        
        const resProducto = await fetch('/api/productos/crear.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formDataProducto.toString()
        });
        
        const resultadoProducto = await resProducto.json();
        
        if (!resultadoProducto.success) {
            ocultarCargando();
            mostrarError(resultadoProducto.message || 'Error al crear producto');
            return;
        }
        
        const productoId = resultadoProducto.data.id;
        
        // 2. Stock ya se agregó con el producto, solo redirigir
ocultarCargando();
        
if (resultadoProducto.success) {
    await mostrarExito('Producto creado exitosamente');
    window.location.href = 'lista.php';
} else {
    mostrarError(resultadoProducto.message || 'Error al crear producto');
}
        
    } catch (error) {
        ocultarCargando();
        console.error('Error:', error);
        mostrarError('Error: ' + error.message);
    }
});

document.addEventListener('DOMContentLoaded', cargarDatos);
</script>