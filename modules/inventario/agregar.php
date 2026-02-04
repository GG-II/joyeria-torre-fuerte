<?php
/**
 * ================================================
 * MÓDULO INVENTARIO - AGREGAR PRODUCTO
 * ================================================
 * 
 * Vista para agregar un nuevo producto al inventario.
 * Registra en 3 tablas: productos, precios_producto, inventario.
 * 
 * TODO FASE 5: Conectar con API
 * GET /api/categorias/lista.php - Cargar categorías
 * POST /api/inventario/crear.php - Crear producto
 * 
 * El API debe insertar en:
 * 1. productos (codigo, nombre, categoria_id, peso_gramos, etc.)
 * 2. precios_producto (precio_publico, precio_mayorista)
 * 3. inventario (stock por sucursal)
 */

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
    <nav aria-label="breadcrumb" class="mb-3">
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
    <div class="page-header mb-4">
        <h1 class="mb-2">
            <i class="bi bi-plus-circle"></i>
            Nuevo Producto
        </h1>
        <p class="text-muted mb-0">Registre un nuevo producto en el inventario</p>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <!-- Formulario -->
            <div class="card shadow-sm">
                <div class="card-header" style="background-color: #1e3a8a; color: white;">
                    <i class="bi bi-pencil-square"></i>
                    Información del Producto
                </div>
                <div class="card-body">
                    <form id="formProducto" method="POST">
                        <!-- Información Básica -->
                        <h5 class="mb-3 text-primary">
                            <i class="bi bi-info-circle"></i>
                            Información Básica
                        </h5>

                        <div class="row g-3 mb-3">
                            <div class="col-md-12">
                                <label for="categoria_id" class="form-label">
                                    <i class="bi bi-folder"></i> Categoría *
                                </label>
                                <select class="form-select" id="categoria_id" name="categoria_id" required>
                                    <option value="">Seleccione...</option>
                                    <!-- Se llenarán vía API -->
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

                        <div class="mb-4">
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

                        <div class="row g-3 mb-3">
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
                                <div class="form-text">Para collares, cadenas, pulseras</div>
                            </div>
                            <div class="col-md-4">
                                <label for="estilo" class="form-label">
                                    <i class="bi bi-palette"></i> Estilo
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="estilo" 
                                       name="estilo" 
                                       placeholder="Clásico, Moderno...">
                            </div>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="es_por_peso" 
                                   name="es_por_peso">
                            <label class="form-check-label" for="es_por_peso">
                                <i class="bi bi-scale"></i> Este producto se vende por peso
                            </label>
                            <div class="form-text">Marque si el precio se calcula según el peso (oro/plata por gramo)</div>
                        </div>

                        <hr class="my-4">

                        <!-- Precios -->
                        <h5 class="mb-3 text-primary">
                            <i class="bi bi-currency-dollar"></i>
                            Precios
                        </h5>

                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
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
                            Stock Inicial por Sucursal
                        </h5>

                        <div class="row g-3 mb-3">
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

                        <div class="form-check mb-4">
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
                        <div class="d-flex flex-column flex-sm-row justify-content-end gap-2">
                            <a href="lista.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary" id="btnGuardar">
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
            <!-- Consejos -->
            <div class="card mb-3 shadow-sm">
                <div class="card-header" style="background-color: #1e3a8a; color: white;">
                    <i class="bi bi-lightbulb"></i>
                    Consejos
                </div>
                <div class="card-body">
                    <h6 class="fw-bold"><i class="bi bi-scale"></i> Venta por Peso</h6>
                    <p class="small mb-3">
                        Active esta opción para productos que se venden por gramo (oro/plata).
                        El precio final se calculará multiplicando el peso por el precio.
                    </p>

                    <h6 class="fw-bold"><i class="bi bi-exclamation-triangle"></i> Stock Mínimo</h6>
                    <p class="small mb-0">
                        El sistema le alertará cuando el stock total baje de este número.
                    </p>
                </div>
            </div>

            <!-- Vista Previa -->
            <div class="card shadow-sm">
                <div class="card-header" style="background-color: #1e3a8a; color: white;">
                    <i class="bi bi-eye"></i>
                    Vista Previa
                </div>
                <div class="card-body">
                    <div class="mb-3 pb-3 border-bottom">
                        <small class="text-muted d-block">Stock Total:</small>
                        <h4 id="preview-stock-total" class="mb-0 text-primary">0</h4>
                        <small class="text-muted">unidades</small>
                    </div>
                    <div>
                        <small class="text-muted d-block">Precio Mayorista:</small>
                        <h4 id="preview-precio-mayorista" class="mb-0 text-success">Q 0.00</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* ============================================
   ESTILOS ESPECÍFICOS AGREGAR PRODUCTO
   ============================================ */

/* Contenedor principal */
.main-content {
    padding: 20px;
    min-height: calc(100vh - 120px);
}

/* Page header */
.page-header h1 {
    font-size: 1.75rem;
    font-weight: 600;
    color: #1a1a1a;
}

/* Cards */
.shadow-sm {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08) !important;
}

.card-body {
    padding: 25px;
}

/* Formulario */
.form-label {
    font-weight: 500;
    margin-bottom: 0.5rem;
    color: #374151;
}

.form-control,
.form-select {
    border: 1px solid #d1d5db;
    border-radius: 6px;
    padding: 0.625rem 0.75rem;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-control:focus,
.form-select:focus {
    border-color: #1e3a8a;
    box-shadow: 0 0 0 0.2rem rgba(30, 58, 138, 0.15);
}

textarea.form-control {
    resize: vertical;
}

.form-text {
    font-size: 0.875rem;
    color: #6b7280;
    margin-top: 0.25rem;
}

.form-check-input {
    width: 1.2em;
    height: 1.2em;
    margin-top: 0.15em;
}

.form-check-input:checked {
    background-color: #1e3a8a;
    border-color: #1e3a8a;
}

.form-check-label {
    padding-left: 0.5rem;
}

/* Secciones */
h5.text-primary {
    color: #1e3a8a !important;
    font-weight: 600;
}

hr {
    opacity: 0.1;
}

/* Panel de ayuda */
.card-body h6 {
    color: #1a1a1a;
    font-size: 0.95rem;
    margin-bottom: 0.75rem;
}

.card-body ul {
    padding-left: 20px;
}

.card-body ul li {
    margin-bottom: 0.35rem;
}

/* Vista previa */
.card-body .border-bottom {
    border-bottom: 1px solid #e5e7eb !important;
}

/* Botones */
.btn {
    padding: 0.625rem 1.25rem;
    font-weight: 500;
    border-radius: 6px;
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
    
    .card-body {
        padding: 15px;
    }
    
    h5 {
        font-size: 1.1rem;
    }
    
    .form-label {
        font-size: 0.9rem;
    }
    
    .btn {
        width: 100%;
        padding: 0.75rem;
    }
}

/* Tablet (576px - 767.98px) */
@media (min-width: 576px) and (max-width: 767.98px) {
    .main-content {
        padding: 18px 15px;
    }
}

/* Desktop (992px+) */
@media (min-width: 992px) {
    .main-content {
        padding: 25px 30px;
    }
}

/* Touch targets */
@media (max-width: 767.98px) {
    .btn,
    .form-control,
    .form-select,
    textarea {
        min-height: 44px;
    }
    
    .form-check-input {
        width: 1.35em;
        height: 1.35em;
    }
}
</style>

<script>
/**
 * ================================================
 * JAVASCRIPT - AGREGAR PRODUCTO
 * ================================================
 */

// Cargar categorías al iniciar
document.addEventListener('DOMContentLoaded', function() {
    cargarCategorias();
    
    // Event listeners para vista previa
    document.getElementById('stock_los_arcos').addEventListener('input', calcularStockTotal);
    document.getElementById('stock_chinaca').addEventListener('input', calcularStockTotal);
    document.getElementById('precio_mayorista').addEventListener('input', actualizarPrecioMayorista);
});

/**
 * Cargar categorías desde API
 * TODO FASE 5: Conectar con API
 */
function cargarCategorias() {
    // TODO FASE 5: Descomentar y conectar
    /*
    fetch('<?php echo BASE_URL; ?>api/categorias/lista.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('categoria_id');
                data.data.forEach(cat => {
                    const option = document.createElement('option');
                    option.value = cat.id;
                    option.textContent = cat.nombre;
                    select.appendChild(option);
                });
            }
        })
        .catch(error => console.error('Error:', error));
    */
    

<!-- Scripts específicos del módulo -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?php echo BASE_URL; ?>assets/js/api-helper.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/agregar-producto.js"></script>

<?php include '../../includes/footer.php'; ?>
