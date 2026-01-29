<?php
/**
 * ================================================
 * MÓDULO INVENTARIO - EDITAR PRODUCTO
 * ================================================
 * 
 * TODO FASE 5: Conectar con APIs
 * GET /api/inventario/ver.php?id={producto_id} - Cargar datos
 * GET /api/categorias/lista.php - Cargar categorías
 * POST /api/inventario/actualizar.php - Guardar cambios
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();
requiere_rol(['administrador', 'dueño']);

$producto_id = $_GET['id'] ?? null;
if (!$producto_id) {
    header('Location: lista.php');
    exit;
}

$producto = null;

$titulo_pagina = 'Editar Producto';
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="container-fluid main-content">
    <div id="loadingState" class="text-center py-5">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
        <p class="mt-3 text-muted">Cargando datos del producto...</p>
    </div>

    <div id="mainContent" style="display: none;">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>dashboard.php"><i class="bi bi-house"></i> Dashboard</a></li>
                <li class="breadcrumb-item"><a href="lista.php"><i class="bi bi-box-seam"></i> Inventario</a></li>
                <li class="breadcrumb-item active">Editar Producto</li>
            </ol>
        </nav>

        <div class="page-header mb-4">
            <div class="row align-items-center g-3">
                <div class="col-md-6">
                    <h1 class="mb-2"><i class="bi bi-pencil-square"></i> Editar Producto</h1>
                    <p class="text-muted mb-0" id="productoCodigo">Código: -</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="ver.php?id=<?php echo $producto_id; ?>" class="btn btn-info">
                        <i class="bi bi-eye"></i> <span class="d-none d-sm-inline">Ver Detalles</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header" style="background-color: #1e3a8a; color: white;">
                        <i class="bi bi-pencil-square"></i> Información del Producto
                    </div>
                    <div class="card-body">
                        <form id="formProducto" method="POST">
                            <input type="hidden" name="id" value="<?php echo $producto_id; ?>">

                            <h5 class="mb-3 text-primary"><i class="bi bi-info-circle"></i> Información Básica</h5>

                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label for="codigo" class="form-label"><i class="bi bi-upc-scan"></i> Código *</label>
                                    <input type="text" class="form-control" id="codigo" name="codigo" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="codigo_barras" class="form-label"><i class="bi bi-upc"></i> Código de Barras</label>
                                    <input type="text" class="form-control" id="codigo_barras" name="codigo_barras">
                                </div>
                                <div class="col-md-4">
                                    <label for="categoria_id" class="form-label"><i class="bi bi-folder"></i> Categoría *</label>
                                    <select class="form-select" id="categoria_id" name="categoria_id" required>
                                        <option value="">Seleccione...</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="nombre" class="form-label"><i class="bi bi-tag"></i> Nombre del Producto *</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                            </div>

                            <div class="mb-4">
                                <label for="descripcion" class="form-label"><i class="bi bi-file-text"></i> Descripción</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                            </div>

                            <hr class="my-4">

                            <h5 class="mb-3 text-primary"><i class="bi bi-gem"></i> Características Físicas</h5>

                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label for="peso_gramos" class="form-label"><i class="bi bi-weight"></i> Peso (gramos)</label>
                                    <input type="number" class="form-control" id="peso_gramos" name="peso_gramos" min="0" step="0.001">
                                </div>
                                <div class="col-md-4">
                                    <label for="largo_cm" class="form-label"><i class="bi bi-rulers"></i> Largo (cm)</label>
                                    <input type="number" class="form-control" id="largo_cm" name="largo_cm" min="0" step="0.01">
                                </div>
                                <div class="col-md-4">
                                    <label for="estilo" class="form-label"><i class="bi bi-palette"></i> Estilo</label>
                                    <input type="text" class="form-control" id="estilo" name="estilo">
                                </div>
                            </div>

                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="es_por_peso" name="es_por_peso">
                                <label class="form-check-label" for="es_por_peso"><i class="bi bi-scale"></i> Este producto se vende por peso</label>
                            </div>

                            <hr class="my-4">

                            <h5 class="mb-3 text-primary"><i class="bi bi-currency-dollar"></i> Precios</h5>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="precio_publico" class="form-label"><i class="bi bi-tag"></i> Precio Público (Q) *</label>
                                    <input type="number" class="form-control" id="precio_publico" name="precio_publico" min="0" step="0.01" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="precio_mayorista" class="form-label"><i class="bi bi-tag-fill"></i> Precio Mayorista (Q)</label>
                                    <input type="number" class="form-control" id="precio_mayorista" name="precio_mayorista" min="0" step="0.01">
                                </div>
                            </div>

                            <hr class="my-4">

                            <h5 class="mb-3 text-primary"><i class="bi bi-boxes"></i> Control de Stock</h5>

                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                <strong>Nota:</strong> Para modificar el stock actual, use el módulo de Transferencias o registre movimientos de inventario.
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label"><i class="bi bi-building"></i> Stock Los Arcos</label>
                                    <input type="text" class="form-control" id="stock_los_arcos_display" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label"><i class="bi bi-building"></i> Stock Chinaca</label>
                                    <input type="text" class="form-control" id="stock_chinaca_display" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label for="stock_minimo" class="form-label"><i class="bi bi-exclamation-triangle"></i> Stock Mínimo</label>
                                    <input type="number" class="form-control" id="stock_minimo" name="stock_minimo" min="0">
                                </div>
                            </div>

                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="activo" name="activo">
                                <label class="form-check-label" for="activo">Producto activo</label>
                            </div>

                            <div class="d-flex flex-column flex-sm-row justify-content-between gap-2">
                                <a href="lista.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
                                <div class="d-flex flex-column flex-sm-row gap-2">
                                    <a href="ver.php?id=<?php echo $producto_id; ?>" class="btn btn-info"><i class="bi bi-eye"></i> Ver Detalles</a>
                                    <button type="submit" class="btn btn-primary" id="btnGuardar"><i class="bi bi-save"></i> Guardar Cambios</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card mb-3 shadow-sm">
                    <div class="card-header" style="background-color: #1e3a8a; color: white;"><i class="bi bi-info-circle"></i> Información Actual</div>
                    <div class="card-body" id="infoActual">
                        <div class="text-center text-muted py-3">
                            <div class="spinner-border spinner-border-sm"></div>
                            <p class="mt-2 mb-0 small">Cargando...</p>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header"><i class="bi bi-lightning"></i> Acciones Rápidas</div>
                    <div class="list-group list-group-flush">
                        <a href="ver.php?id=<?php echo $producto_id; ?>" class="list-group-item list-group-item-action">
                            <i class="bi bi-clock-history"></i> Ver historial de movimientos
                        </a>
                        <a href="transferencias.php?producto_id=<?php echo $producto_id; ?>" class="list-group-item list-group-item-action">
                            <i class="bi bi-arrow-left-right"></i> Transferir entre sucursales
                        </a>
                        <?php if (tiene_permiso('inventario', 'eliminar')): ?>
                        <a href="#" onclick="eliminarProducto(); return false;" class="list-group-item list-group-item-action text-danger">
                            <i class="bi bi-trash"></i> Eliminar producto
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="errorState" style="display: none;" class="text-center py-5">
        <i class="bi bi-exclamation-triangle text-danger" style="font-size: 48px;"></i>
        <h4 class="mt-3">Error al cargar el producto</h4>
        <p class="text-muted" id="errorMessage">No se pudo cargar la información del producto.</p>
        <a href="lista.php" class="btn btn-primary mt-3"><i class="bi bi-arrow-left"></i> Volver al listado</a>
    </div>
</div>

<style>
.main-content { padding: 20px; min-height: calc(100vh - 120px); }
.page-header h1 { font-size: 1.75rem; font-weight: 600; color: #1a1a1a; }
.shadow-sm { box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08) !important; }
.card-body { padding: 25px; }
.form-label { font-weight: 500; margin-bottom: 0.5rem; color: #374151; }
.form-control, .form-select { border: 1px solid #d1d5db; border-radius: 6px; padding: 0.625rem 0.75rem; transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out; }
.form-control:focus, .form-select:focus { border-color: #1e3a8a; box-shadow: 0 0 0 0.2rem rgba(30, 58, 138, 0.15); }
textarea.form-control { resize: vertical; }
.form-check-input { width: 1.2em; height: 1.2em; margin-top: 0.15em; }
.form-check-input:checked { background-color: #1e3a8a; border-color: #1e3a8a; }
h5.text-primary { color: #1e3a8a !important; font-weight: 600; }
hr { opacity: 0.1; }
.card-body > div:not(:last-child) { padding-bottom: 12px; margin-bottom: 12px; border-bottom: 1px solid #e5e7eb; }
.list-group-item { transition: background-color 0.15s ease; }
.list-group-item:hover { background-color: #f3f4f6; }
@media (max-width: 575.98px) {
    .main-content { padding: 15px 10px; }
    .page-header h1 { font-size: 1.5rem; }
    .card-body { padding: 15px; }
    h5 { font-size: 1.1rem; }
    .btn { width: 100%; }
}
@media (min-width: 576px) and (max-width: 767.98px) { .main-content { padding: 18px 15px; } }
@media (min-width: 992px) { .main-content { padding: 25px 30px; } }
@media (max-width: 767.98px) {
    .btn, .form-control, .form-select, textarea { min-height: 44px; }
    .form-check-input { width: 1.35em; height: 1.35em; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    cargarDatosProducto();
});

function cargarDatosProducto() {
    const productoId = <?php echo $producto_id; ?>;
    
    /* TODO FASE 5: Descomentar
    Promise.all([
        fetch('<?php echo BASE_URL; ?>api/inventario/ver.php?id=' + productoId),
        fetch('<?php echo BASE_URL; ?>api/categorias/lista.php')
    ])
    .then(responses => Promise.all(responses.map(r => r.json())))
    .then(([productoData, categoriasData]) => {
        if (productoData.success && categoriasData.success) {
            llenarCategorias(categoriasData.data);
            llenarFormulario(productoData.data);
            mostrarInfoActual(productoData.data);
        } else {
            mostrarError('No se pudo cargar el producto');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarError('Error al cargar los datos');
    });
    */
    
    setTimeout(() => mostrarError('MODO DESARROLLO: Esperando conexión con API'), 1500);
}

function llenarCategorias(categorias) {
    const select = document.getElementById('categoria_id');
    categorias.forEach(cat => {
        const option = document.createElement('option');
        option.value = cat.id;
        option.textContent = cat.nombre;
        select.appendChild(option);
    });
}

function llenarFormulario(producto) {
    document.getElementById('loadingState').style.display = 'none';
    document.getElementById('mainContent').style.display = 'block';
    
    document.getElementById('productoCodigo').textContent = 'Código: ' + producto.codigo;
    document.getElementById('codigo').value = producto.codigo;
    document.getElementById('codigo_barras').value = producto.codigo_barras || '';
    document.getElementById('categoria_id').value = producto.categoria_id;
    document.getElementById('nombre').value = producto.nombre;
    document.getElementById('descripcion').value = producto.descripcion || '';
    document.getElementById('peso_gramos').value = producto.peso_gramos || '';
    document.getElementById('largo_cm').value = producto.largo_cm || '';
    document.getElementById('estilo').value = producto.estilo || '';
    document.getElementById('es_por_peso').checked = producto.es_por_peso == 1;
    document.getElementById('precio_publico').value = producto.precio_publico;
    document.getElementById('precio_mayorista').value = producto.precio_mayorista || '';
    document.getElementById('stock_los_arcos_display').value = producto.stock_los_arcos + ' unidades';
    document.getElementById('stock_chinaca_display').value = producto.stock_chinaca + ' unidades';
    document.getElementById('stock_minimo').value = producto.stock_minimo;
    document.getElementById('activo').checked = producto.activo == 1;
}

function mostrarInfoActual(producto) {
    const stockTotal = parseInt(producto.stock_los_arcos) + parseInt(producto.stock_chinaca);
    let badge = '';
    
    if (stockTotal == 0) badge = '<span class="badge bg-danger">Agotado</span>';
    else if (stockTotal <= producto.stock_minimo) badge = '<span class="badge bg-warning">Stock Bajo</span>';
    else badge = '<span class="badge bg-success">Disponible</span>';
    
    document.getElementById('infoActual').innerHTML = `
        <div><small class="text-muted d-block">Stock Total:</small><h4 class="mb-0 text-primary">${stockTotal} unidades</h4></div>
        <div><small class="text-muted d-block">Estado:</small>${badge}</div>
    `;
}

function mostrarError(mensaje) {
    document.getElementById('loadingState').style.display = 'none';
    document.getElementById('errorState').style.display = 'block';
    document.getElementById('errorMessage').textContent = mensaje;
}

document.getElementById('formProducto').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const precioPublico = parseFloat(document.getElementById('precio_publico').value);
    const precioMayorista = parseFloat(document.getElementById('precio_mayorista').value) || 0;
    
    if (precioMayorista > 0 && precioMayorista >= precioPublico) {
        alert('El precio mayorista debe ser menor que el precio público');
        return;
    }
    
    const formData = new FormData(this);
    const datos = {
        id: formData.get('id'),
        codigo: formData.get('codigo'),
        codigo_barras: formData.get('codigo_barras') || null,
        categoria_id: formData.get('categoria_id'),
        nombre: formData.get('nombre'),
        descripcion: formData.get('descripcion') || null,
        peso_gramos: parseFloat(formData.get('peso_gramos')) || null,
        largo_cm: parseFloat(formData.get('largo_cm')) || null,
        estilo: formData.get('estilo') || null,
        es_por_peso: formData.get('es_por_peso') ? 1 : 0,
        stock_minimo: parseInt(formData.get('stock_minimo')),
        activo: formData.get('activo') ? 1 : 0,
        precio_publico: precioPublico,
        precio_mayorista: precioMayorista
    };
    
    const btnGuardar = document.getElementById('btnGuardar');
    btnGuardar.disabled = true;
    btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
    
    /* TODO FASE 5: Descomentar
    fetch('<?php echo BASE_URL; ?>api/inventario/actualizar.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(datos)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Producto actualizado exitosamente');
            setTimeout(() => window.location.href = 'ver.php?id=' + datos.id, 1500);
        } else {
            alert(data.message);
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = '<i class="bi bi-save"></i> Guardar Cambios';
        }
    });
    */
    
    console.log('Datos:', datos);
    setTimeout(() => {
        alert('MODO DESARROLLO: Cambios listos.\n\n' + JSON.stringify(datos, null, 2));
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = '<i class="bi bi-save"></i> Guardar Cambios';
    }, 1000);
});

document.getElementById('codigo').addEventListener('blur', function(e) {
    e.target.value = e.target.value.toUpperCase();
});

function eliminarProducto() {
    if (confirm('¿Está seguro de eliminar este producto?\n\nEsta acción no se puede deshacer.')) {
        alert('MODO DESARROLLO: Eliminar producto - Pendiente API');
    }
}
</script>

<?php include '../../includes/footer.php'; ?>