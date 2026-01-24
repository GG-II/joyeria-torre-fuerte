<?php
// ================================================
// MÓDULO INVENTARIO - AGREGAR (CORREGIDO)
// ================================================

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

// Verificar autenticación y permisos
requiere_autenticacion();
requiere_rol(['administrador', 'dueño']);

// Título de página
$titulo_pagina = 'Nuevo Producto';

// Incluir header
include '../../includes/header.php';

// Incluir navbar
include '../../includes/navbar.php';
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
                    <i class="bi bi-box-seam"></i> Inventario
                </a>
            </li>
            <li class="breadcrumb-item active">Nuevo Producto</li>
        </ol>
    </nav>

    <!-- Encabezado -->
    <div class="page-header">
        <h1>
            <i class="bi bi-plus-circle"></i>
            Nuevo Producto
        </h1>
        <p class="text-muted">Registre un nuevo producto en el inventario</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Formulario -->
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-pencil-square"></i>
                    Información del Producto
                </div>
                <div class="card-body">
                    <form id="formProducto" method="POST" action="">
                        <!-- Información Básica -->
                        <h5 class="mb-3 text-primary">
                            <i class="bi bi-info-circle"></i>
                            Información Básica
                        </h5>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="codigo" class="form-label">
                                    <i class="bi bi-upc-scan"></i> Código *
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="codigo" 
                                       name="codigo" 
                                       placeholder="AN-001"
                                       required>
                            </div>
                            <div class="col-md-4">
                                <label for="codigo_barras" class="form-label">
                                    <i class="bi bi-upc"></i> Código de Barras
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="codigo_barras" 
                                       name="codigo_barras" 
                                       placeholder="7501234567890">
                            </div>
                            <div class="col-md-4">
                                <label for="categoria_id" class="form-label">
                                    <i class="bi bi-folder"></i> Categoría *
                                </label>
                                <select class="form-select" id="categoria_id" name="categoria_id" required>
                                    <option value="">Seleccione...</option>
                                    <option value="1">Anillos</option>
                                    <option value="2">Aretes</option>
                                    <option value="3">Collares</option>
                                    <option value="4">Pulseras</option>
                                    <option value="5">Cadenas</option>
                                    <option value="6">Dijes</option>
                                    <option value="7">Relojes</option>
                                    <option value="8">Otros</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="nombre" class="form-label">
                                <i class="bi bi-tag"></i> Nombre del Producto *
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="nombre" 
                                   name="nombre" 
                                   placeholder="Ej: Anillo de Oro con Diamante"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">
                                <i class="bi bi-file-text"></i> Descripción
                            </label>
                            <textarea class="form-control" 
                                      id="descripcion" 
                                      name="descripcion" 
                                      rows="3" 
                                      placeholder="Descripción detallada del producto"></textarea>
                        </div>

                        <hr class="my-4">

                        <!-- Características Físicas -->
                        <h5 class="mb-3 text-primary">
                            <i class="bi bi-gem"></i>
                            Características Físicas
                        </h5>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="peso_gramos" class="form-label">
                                    <i class="bi bi-weight"></i> Peso (gramos)
                                </label>
                                <input type="number" 
                                       class="form-control" 
                                       id="peso_gramos" 
                                       name="peso_gramos" 
                                       min="0" 
                                       step="0.001"
                                       placeholder="0.000">
                            </div>
                            <div class="col-md-4">
                                <label for="largo_cm" class="form-label">
                                    <i class="bi bi-rulers"></i> Largo (cm)
                                </label>
                                <input type="number" 
                                       class="form-control" 
                                       id="largo_cm" 
                                       name="largo_cm" 
                                       min="0" 
                                       step="0.01"
                                       placeholder="0.00">
                                <small class="text-muted">Para collares, cadenas, pulseras</small>
                            </div>
                            <div class="col-md-4">
                                <label for="estilo" class="form-label">
                                    <i class="bi bi-palette"></i> Estilo
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="estilo" 
                                       name="estilo" 
                                       placeholder="Clásico, Moderno, Elegante...">
                            </div>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="es_por_peso" 
                                   name="es_por_peso">
                            <label class="form-check-label" for="es_por_peso">
                                <i class="bi bi-scale"></i> Este producto se vende por peso
                            </label>
                            <small class="text-muted d-block">Marque si el precio se calcula según el peso (oro/plata por gramo)</small>
                        </div>

                        <hr class="my-4">

                        <!-- Precios -->
                        <h5 class="mb-3 text-primary">
                            <i class="bi bi-currency-dollar"></i>
                            Precios (se guardarán en tabla precios_producto)
                        </h5>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="precio_publico" class="form-label">
                                    <i class="bi bi-tag"></i> Precio Público (Q) *
                                </label>
                                <input type="number" 
                                       class="form-control" 
                                       id="precio_publico" 
                                       name="precio_publico" 
                                       min="0" 
                                       step="0.01"
                                       placeholder="0.00"
                                       required>
                            </div>
                            <div class="col-md-6">
                                <label for="precio_mayorista" class="form-label">
                                    <i class="bi bi-tag-fill"></i> Precio Mayorista (Q)
                                </label>
                                <input type="number" 
                                       class="form-control" 
                                       id="precio_mayorista" 
                                       name="precio_mayorista" 
                                       min="0" 
                                       step="0.01"
                                       placeholder="0.00">
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Stock Inicial -->
                        <h5 class="mb-3 text-primary">
                            <i class="bi bi-boxes"></i>
                            Stock Inicial por Sucursal (se guardarán en tabla inventario)
                        </h5>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="stock_los_arcos" class="form-label">
                                    <i class="bi bi-building"></i> Los Arcos
                                </label>
                                <input type="number" 
                                       class="form-control" 
                                       id="stock_los_arcos" 
                                       name="stock_los_arcos" 
                                       min="0" 
                                       value="0">
                            </div>
                            <div class="col-md-4">
                                <label for="stock_chinaca" class="form-label">
                                    <i class="bi bi-building"></i> Chinaca Central
                                </label>
                                <input type="number" 
                                       class="form-control" 
                                       id="stock_chinaca" 
                                       name="stock_chinaca" 
                                       min="0" 
                                       value="0">
                            </div>
                            <div class="col-md-4">
                                <label for="stock_minimo" class="form-label">
                                    <i class="bi bi-exclamation-triangle"></i> Stock Mínimo
                                </label>
                                <input type="number" 
                                       class="form-control" 
                                       id="stock_minimo" 
                                       name="stock_minimo" 
                                       min="0" 
                                       value="5">
                            </div>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="activo" 
                                   name="activo" 
                                   checked>
                            <label class="form-check-label" for="activo">
                                Producto activo
                            </label>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="lista.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i>
                                Guardar Producto
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Panel Lateral -->
        <div class="col-lg-4">
            <!-- Ayuda -->
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <i class="bi bi-lightbulb"></i>
                    Estructura de Datos
                </div>
                <div class="card-body">
                    <h6 class="fw-bold">Tablas involucradas:</h6>
                    <ul class="small mb-3">
                        <li><strong>productos:</strong> Info básica</li>
                        <li><strong>precios_producto:</strong> Precios por tipo</li>
                        <li><strong>inventario:</strong> Stock por sucursal</li>
                    </ul>

                    <h6 class="fw-bold">Venta por peso:</h6>
                    <p class="small mb-3">
                        Active "por peso" para oro/plata que se vende por gramo.
                        El precio se calculará: peso × precio_por_gramo
                    </p>

                    <h6 class="fw-bold">Stock Mínimo:</h6>
                    <p class="small mb-0">
                        Define cuándo alertar sobre stock bajo.
                        Se aplica al total de ambas sucursales.
                    </p>
                </div>
            </div>

            <!-- Vista Previa -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-eye"></i>
                    Vista Previa
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted">Stock Total:</small>
                        <h4 id="preview-stock-total" class="mb-0">0</h4>
                    </div>
                    <div>
                        <small class="text-muted">Precio Mayorista:</small>
                        <h4 id="preview-precio-mayorista" class="mb-0">Q 0.00</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Calcular stock total
function calcularStockTotal() {
    const losArcos = parseInt(document.getElementById('stock_los_arcos').value) || 0;
    const chinaca = parseInt(document.getElementById('stock_chinaca').value) || 0;
    const total = losArcos + chinaca;
    
    document.getElementById('preview-stock-total').textContent = total;
}

document.getElementById('stock_los_arcos').addEventListener('input', calcularStockTotal);
document.getElementById('stock_chinaca').addEventListener('input', calcularStockTotal);

// Mostrar precio mayorista
document.getElementById('precio_mayorista').addEventListener('input', function(e) {
    const precio = parseFloat(e.target.value) || 0;
    document.getElementById('preview-precio-mayorista').textContent = 'Q ' + precio.toFixed(2);
});

// Validación del formulario
document.getElementById('formProducto').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Aquí se conectará con la API que insertará en:
    // 1. productos (datos básicos)
    // 2. precios_producto (precio_publico, precio_mayorista)
    // 3. inventario (stock por sucursal)
    
    alert('Formulario listo para conectar con la API');
    
    const formData = new FormData(this);
    console.log('Datos del producto:', Object.fromEntries(formData));
});
</script>

<?php
// Incluir footer
include '../../includes/footer.php';
?>