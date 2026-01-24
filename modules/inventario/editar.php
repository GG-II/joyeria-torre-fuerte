<?php
// ================================================
// MÓDULO INVENTARIO - EDITAR (CORREGIDO)
// ================================================

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

// Verificar autenticación y permisos
requiere_autenticacion();
requiere_rol(['administrador', 'dueño']);

// Obtener ID del producto (dummy)
$producto_id = $_GET['id'] ?? 1;

// Datos dummy del producto a editar (CAMPOS REALES DEL SCHEMA)
$producto = [
    'id' => $producto_id,
    'codigo' => 'AN-001',
    'codigo_barras' => '7501234567890',
    'nombre' => 'Anillo de Oro con Diamante',
    'descripcion' => 'Elegante anillo con diamante central de 0.5 quilates',
    'categoria_id' => 1,
    'proveedor_id' => null,
    'es_por_peso' => 0,
    'peso_gramos' => 5.500,
    'estilo' => 'Clásico',
    'largo_cm' => null,
    'activo' => 1,
    // Datos de otras tablas (JOIN)
    'precio_publico' => 8500.00,      // De precios_producto
    'precio_mayorista' => 7500.00,    // De precios_producto
    'stock_los_arcos' => 3,           // De inventario
    'stock_chinaca' => 2,             // De inventario
    'stock_minimo' => 2               // De inventario
];

$stock_total = $producto['stock_los_arcos'] + $producto['stock_chinaca'];

// Título de página
$titulo_pagina = 'Editar Producto';

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
            <li class="breadcrumb-item active">Editar Producto</li>
        </ol>
    </nav>

    <!-- Encabezado -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1>
                    <i class="bi bi-pencil-square"></i>
                    Editar Producto
                </h1>
                <p class="text-muted">Código: <?php echo $producto['codigo']; ?></p>
            </div>
            <div class="col-md-6 text-end">
                <a href="ver.php?id=<?php echo $producto['id']; ?>" class="btn btn-info">
                    <i class="bi bi-eye"></i>
                    Ver Detalles
                </a>
            </div>
        </div>
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
                        <input type="hidden" name="id" value="<?php echo $producto['id']; ?>">

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
                                       value="<?php echo htmlspecialchars($producto['codigo']); ?>"
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
                                       value="<?php echo htmlspecialchars($producto['codigo_barras']); ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="categoria_id" class="form-label">
                                    <i class="bi bi-folder"></i> Categoría *
                                </label>
                                <select class="form-select" id="categoria_id" name="categoria_id" required>
                                    <option value="1" <?php echo $producto['categoria_id'] == 1 ? 'selected' : ''; ?>>Anillos</option>
                                    <option value="2" <?php echo $producto['categoria_id'] == 2 ? 'selected' : ''; ?>>Aretes</option>
                                    <option value="3" <?php echo $producto['categoria_id'] == 3 ? 'selected' : ''; ?>>Collares</option>
                                    <option value="4" <?php echo $producto['categoria_id'] == 4 ? 'selected' : ''; ?>>Pulseras</option>
                                    <option value="5" <?php echo $producto['categoria_id'] == 5 ? 'selected' : ''; ?>>Cadenas</option>
                                    <option value="6" <?php echo $producto['categoria_id'] == 6 ? 'selected' : ''; ?>>Dijes</option>
                                    <option value="7" <?php echo $producto['categoria_id'] == 7 ? 'selected' : ''; ?>>Relojes</option>
                                    <option value="8" <?php echo $producto['categoria_id'] == 8 ? 'selected' : ''; ?>>Otros</option>
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
                                   value="<?php echo htmlspecialchars($producto['nombre']); ?>"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">
                                <i class="bi bi-file-text"></i> Descripción
                            </label>
                            <textarea class="form-control" 
                                      id="descripcion" 
                                      name="descripcion" 
                                      rows="3"><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>
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
                                       value="<?php echo $producto['peso_gramos']; ?>">
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
                                       value="<?php echo $producto['largo_cm']; ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="estilo" class="form-label">
                                    <i class="bi bi-palette"></i> Estilo
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="estilo" 
                                       name="estilo" 
                                       value="<?php echo htmlspecialchars($producto['estilo']); ?>">
                            </div>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="es_por_peso" 
                                   name="es_por_peso"
                                   <?php echo $producto['es_por_peso'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="es_por_peso">
                                Este producto se vende por peso
                            </label>
                        </div>

                        <hr class="my-4">

                        <!-- Precios (tabla precios_producto) -->
                        <h5 class="mb-3 text-primary">
                            <i class="bi bi-currency-dollar"></i>
                            Precios
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
                                       value="<?php echo $producto['precio_publico']; ?>"
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
                                       value="<?php echo $producto['precio_mayorista']; ?>">
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Stock (tabla inventario - SOLO LECTURA) -->
                        <h5 class="mb-3 text-primary">
                            <i class="bi bi-boxes"></i>
                            Control de Stock
                        </h5>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Nota:</strong> Para modificar el stock actual, use el módulo de 
                            <a href="transferencias.php">Transferencias</a> o registre una entrada/salida desde el inventario.
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">
                                    <i class="bi bi-building"></i> Stock Los Arcos
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       value="<?php echo $producto['stock_los_arcos']; ?> unidades"
                                       readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">
                                    <i class="bi bi-building"></i> Stock Chinaca
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       value="<?php echo $producto['stock_chinaca']; ?> unidades"
                                       readonly>
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
                                       value="<?php echo $producto['stock_minimo']; ?>">
                            </div>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="activo" 
                                   name="activo"
                                   <?php echo $producto['activo'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="activo">
                                Producto activo
                            </label>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-between">
                            <a href="lista.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i>
                                Volver al Listado
                            </a>
                            <div class="d-flex gap-2">
                                <a href="ver.php?id=<?php echo $producto['id']; ?>" class="btn btn-info">
                                    <i class="bi bi-eye"></i>
                                    Ver Detalles
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i>
                                    Guardar Cambios
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Panel Lateral -->
        <div class="col-lg-4">
            <!-- Información Actual -->
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-info-circle"></i>
                    Información Actual
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Stock Total:</small>
                        <h4 class="mb-0"><?php echo $stock_total; ?> unidades</h4>
                    </div>
                    <div>
                        <small class="text-muted">Estado:</small>
                        <br>
                        <?php
                        if ($stock_total == 0) {
                            echo '<span class="badge bg-danger">Agotado</span>';
                        } elseif ($stock_total <= $producto['stock_minimo']) {
                            echo '<span class="badge bg-warning">Stock Bajo</span>';
                        } else {
                            echo '<span class="badge bg-success">Disponible</span>';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-lightning"></i>
                    Acciones Rápidas
                </div>
                <div class="list-group list-group-flush">
                    <a href="ver.php?id=<?php echo $producto['id']; ?>" class="list-group-item list-group-item-action">
                        <i class="bi bi-clock-history"></i>
                        Ver historial de movimientos
                    </a>
                    <a href="transferencias.php?producto_id=<?php echo $producto['id']; ?>" class="list-group-item list-group-item-action">
                        <i class="bi bi-arrow-left-right"></i>
                        Transferir entre sucursales
                    </a>
                    <a href="#" class="list-group-item list-group-item-action text-danger">
                        <i class="bi bi-trash"></i>
                        Eliminar producto
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validación del formulario
document.getElementById('formProducto').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // API actualizará:
    // 1. productos (datos básicos)
    // 2. precios_producto (precios)
    // 3. inventario (solo stock_minimo)
    
    alert('Formulario listo para conectar con la API de actualización');
    
    const formData = new FormData(this);
    console.log('Datos actualizados:', Object.fromEntries(formData));
});
</script>

<?php
// Incluir footer
include '../../includes/footer.php';
?>