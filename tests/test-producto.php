<?php
// ================================================
// TEST: MODELO PRODUCTO
// Sistema de Gestión - Joyería Torre Fuerte
// ================================================

session_start();

// Simular usuario autenticado para las pruebas
$_SESSION['usuario_id'] = 1;
$_SESSION['usuario_nombre'] = 'Administrador Test';
$_SESSION['usuario_rol'] = 'administrador';
$_SESSION['usuario_sucursal_id'] = 1;

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/funciones.php';
require_once __DIR__ . '/../models/producto.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test - Modelo Producto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .test-success { background-color: #d4edda; border-left: 4px solid #28a745; }
        .test-error { background-color: #f8d7da; border-left: 4px solid #dc3545; }
        .test-info { background-color: #d1ecf1; border-left: 4px solid #0dcaf0; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container my-5">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">
                    <i class="bi bi-box-seam"></i> Test: Modelo Producto
                </h1>
                <p class="lead">Ejecutando pruebas del modelo Producto...</p>
                <hr>
            </div>
        </div>

<?php

$tests_passed = 0;
$tests_failed = 0;

// ================================================
// TEST 1: Crear producto con precios
// ================================================
echo '<div class="card mb-3">';
echo '<div class="card-header"><h5>TEST 1: Crear Producto con Precios</h5></div>';
echo '<div class="card-body">';

try {
    $datos_producto = [
        'codigo' => 'TEST-' . time(),
        'nombre' => 'Producto de Prueba - ' . date('H:i:s'),
        'descripcion' => 'Este es un producto de prueba creado automáticamente',
        'categoria_id' => 1,  // Asegúrate que existe
        'proveedor_id' => 1,
        'es_por_peso' => 0,
        'estilo' => 'Moderno',
        'activo' => 1
    ];
    
    $precios = [
        'publico' => 100.00,
        'mayorista' => 90.00,
        'descuento' => 80.00,
        'especial' => 70.00
    ];
    
    $producto_id = Producto::crear($datos_producto, $precios);
    
    if ($producto_id) {
        echo '<div class="alert test-success">';
        echo '<strong>✅ ÉXITO:</strong> Producto creado con ID: ' . $producto_id;
        echo '</div>';
        
        // Verificar que se crearon los precios
        $producto_completo = Producto::obtenerPorId($producto_id);
        echo '<div class="alert test-info">';
        echo '<strong>📋 Datos del producto:</strong>';
        echo '<pre>' . print_r($producto_completo, true) . '</pre>';
        echo '</div>';
        
        if (count($producto_completo['precios']) == 4) {
            echo '<div class="alert test-success">';
            echo '<strong>✅ ÉXITO:</strong> Se crearon los 4 precios correctamente';
            echo '</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">';
            echo '<strong>❌ ERROR:</strong> No se crearon todos los precios';
            echo '</div>';
            $tests_failed++;
        }
        
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">';
        echo '<strong>❌ ERROR:</strong> No se pudo crear el producto';
        echo '</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">';
    echo '<strong>❌ EXCEPCIÓN:</strong> ' . $e->getMessage();
    echo '</div>';
    $tests_failed++;
}

echo '</div></div>';

// ================================================
// TEST 2: Buscar producto por código
// ================================================
echo '<div class="card mb-3">';
echo '<div class="card-header"><h5>TEST 2: Buscar Producto por Código</h5></div>';
echo '<div class="card-body">';

try {
    if (isset($datos_producto['codigo'])) {
        $producto_encontrado = Producto::obtenerPorCodigo($datos_producto['codigo']);
        
        if ($producto_encontrado) {
            echo '<div class="alert test-success">';
            echo '<strong>✅ ÉXITO:</strong> Producto encontrado por código: ' . $datos_producto['codigo'];
            echo '</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">';
            echo '<strong>❌ ERROR:</strong> No se encontró el producto por código';
            echo '</div>';
            $tests_failed++;
        }
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">';
    echo '<strong>❌ EXCEPCIÓN:</strong> ' . $e->getMessage();
    echo '</div>';
    $tests_failed++;
}

echo '</div></div>';

// ================================================
// TEST 3: Actualizar producto
// ================================================
echo '<div class="card mb-3">';
echo '<div class="card-header"><h5>TEST 3: Actualizar Producto</h5></div>';
echo '<div class="card-body">';

try {
    if (isset($producto_id)) {
        $datos_actualizacion = [
            'codigo' => $datos_producto['codigo'],
            'nombre' => 'Producto ACTUALIZADO - ' . date('H:i:s'),
            'descripcion' => 'Descripción actualizada',
            'categoria_id' => 1,
            'es_por_peso' => 0
        ];
        
        $precios_actualizados = [
            'publico' => 150.00,
            'mayorista' => 140.00
        ];
        
        $resultado = Producto::actualizar($producto_id, $datos_actualizacion, $precios_actualizados);
        
        if ($resultado) {
            echo '<div class="alert test-success">';
            echo '<strong>✅ ÉXITO:</strong> Producto actualizado correctamente';
            echo '</div>';
            
            // Verificar actualización
            $producto_actualizado = Producto::obtenerPorId($producto_id);
            if ($producto_actualizado['nombre'] == $datos_actualizacion['nombre']) {
                echo '<div class="alert test-success">';
                echo '<strong>✅ ÉXITO:</strong> Los datos se actualizaron en la BD';
                echo '</div>';
                $tests_passed++;
            }
            
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">';
            echo '<strong>❌ ERROR:</strong> No se pudo actualizar el producto';
            echo '</div>';
            $tests_failed++;
        }
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">';
    echo '<strong>❌ EXCEPCIÓN:</strong> ' . $e->getMessage();
    echo '</div>';
    $tests_failed++;
}

echo '</div></div>';

// ================================================
// TEST 4: Listar productos
// ================================================
echo '<div class="card mb-3">';
echo '<div class="card-header"><h5>TEST 4: Listar Productos</h5></div>';
echo '<div class="card-body">';

try {
    $productos = Producto::listar(['activo' => 1], 1, 5);
    
    if (is_array($productos) && count($productos) > 0) {
        echo '<div class="alert test-success">';
        echo '<strong>✅ ÉXITO:</strong> Se listaron ' . count($productos) . ' productos';
        echo '</div>';
        
        echo '<div class="alert test-info">';
        echo '<strong>📋 Primeros productos:</strong>';
        echo '<pre>' . print_r(array_slice($productos, 0, 3), true) . '</pre>';
        echo '</div>';
        
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">';
        echo '<strong>❌ ERROR:</strong> No se pudieron listar productos';
        echo '</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">';
    echo '<strong>❌ EXCEPCIÓN:</strong> ' . $e->getMessage();
    echo '</div>';
    $tests_failed++;
}

echo '</div></div>';

// ================================================
// TEST 5: Buscar productos (autocompletado)
// ================================================
echo '<div class="card mb-3">';
echo '<div class="card-header"><h5>TEST 5: Búsqueda de Productos (Autocompletado)</h5></div>';
echo '<div class="card-body">';

try {
    $resultados = Producto::buscar('prod', 5);
    
    if (is_array($resultados)) {
        echo '<div class="alert test-success">';
        echo '<strong>✅ ÉXITO:</strong> Búsqueda ejecutada. Resultados: ' . count($resultados);
        echo '</div>';
        
        if (count($resultados) > 0) {
            echo '<div class="alert test-info">';
            echo '<strong>📋 Productos encontrados:</strong>';
            echo '<pre>' . print_r($resultados, true) . '</pre>';
            echo '</div>';
        }
        
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">';
        echo '<strong>❌ ERROR:</strong> La búsqueda no devolvió un array';
        echo '</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">';
    echo '<strong>❌ EXCEPCIÓN:</strong> ' . $e->getMessage();
    echo '</div>';
    $tests_failed++;
}

echo '</div></div>';

// ================================================
// TEST 6: Obtener precio específico
// ================================================
echo '<div class="card mb-3">';
echo '<div class="card-header"><h5>TEST 6: Obtener Precio Específico</h5></div>';
echo '<div class="card-body">';

try {
    if (isset($producto_id)) {
        $precio_publico = Producto::obtenerPrecio($producto_id, 'publico');
        
        if ($precio_publico !== false && $precio_publico > 0) {
            echo '<div class="alert test-success">';
            echo '<strong>✅ ÉXITO:</strong> Precio público obtenido: ' . formato_dinero($precio_publico);
            echo '</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">';
            echo '<strong>❌ ERROR:</strong> No se pudo obtener el precio';
            echo '</div>';
            $tests_failed++;
        }
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">';
    echo '<strong>❌ EXCEPCIÓN:</strong> ' . $e->getMessage();
    echo '</div>';
    $tests_failed++;
}

echo '</div></div>';

// ================================================
// TEST 7: Estadísticas
// ================================================
echo '<div class="card mb-3">';
echo '<div class="card-header"><h5>TEST 7: Obtener Estadísticas</h5></div>';
echo '<div class="card-body">';

try {
    $stats = Producto::obtenerEstadisticas();
    
    if (is_array($stats) && isset($stats['total_activos'])) {
        echo '<div class="alert test-success">';
        echo '<strong>✅ ÉXITO:</strong> Estadísticas obtenidas';
        echo '</div>';
        
        echo '<div class="alert test-info">';
        echo '<strong>📊 Estadísticas:</strong>';
        echo '<pre>' . print_r($stats, true) . '</pre>';
        echo '</div>';
        
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">';
        echo '<strong>❌ ERROR:</strong> No se pudieron obtener estadísticas';
        echo '</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">';
    echo '<strong>❌ EXCEPCIÓN:</strong> ' . $e->getMessage();
    echo '</div>';
    $tests_failed++;
}

echo '</div></div>';

// ================================================
// TEST 8: Eliminar (soft delete)
// ================================================
echo '<div class="card mb-3">';
echo '<div class="card-header"><h5>TEST 8: Eliminar Producto (Soft Delete)</h5></div>';
echo '<div class="card-body">';

try {
    if (isset($producto_id)) {
        $resultado = Producto::eliminar($producto_id);
        
        if ($resultado) {
            echo '<div class="alert test-success">';
            echo '<strong>✅ ÉXITO:</strong> Producto desactivado correctamente';
            echo '</div>';
            
            // Verificar que está desactivado
            $producto_eliminado = Producto::obtenerPorId($producto_id);
            if ($producto_eliminado && $producto_eliminado['activo'] == 0) {
                echo '<div class="alert test-success">';
                echo '<strong>✅ ÉXITO:</strong> El producto está marcado como inactivo en BD';
                echo '</div>';
                $tests_passed++;
            }
            
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">';
            echo '<strong>❌ ERROR:</strong> No se pudo eliminar el producto';
            echo '</div>';
            $tests_failed++;
        }
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">';
    echo '<strong>❌ EXCEPCIÓN:</strong> ' . $e->getMessage();
    echo '</div>';
    $tests_failed++;
}

echo '</div></div>';

// ================================================
// RESUMEN
// ================================================
echo '<div class="row mt-4">';
echo '<div class="col-12">';
echo '<div class="card">';
echo '<div class="card-header bg-dark text-white">';
echo '<h4>📊 Resumen de Tests</h4>';
echo '</div>';
echo '<div class="card-body">';

$total_tests = $tests_passed + $tests_failed;
$porcentaje = $total_tests > 0 ? round(($tests_passed / $total_tests) * 100, 2) : 0;

echo '<div class="row text-center">';
echo '<div class="col-md-4">';
echo '<h3 class="text-success">' . $tests_passed . '</h3>';
echo '<p>Tests Exitosos</p>';
echo '</div>';
echo '<div class="col-md-4">';
echo '<h3 class="text-danger">' . $tests_failed . '</h3>';
echo '<p>Tests Fallidos</p>';
echo '</div>';
echo '<div class="col-md-4">';
echo '<h3 class="text-primary">' . $porcentaje . '%</h3>';
echo '<p>Tasa de Éxito</p>';
echo '</div>';
echo '</div>';

if ($tests_failed == 0) {
    echo '<div class="alert alert-success text-center mt-3">';
    echo '<h5>🎉 ¡Todos los tests pasaron exitosamente!</h5>';
    echo '<p>El modelo Producto está funcionando correctamente.</p>';
    echo '</div>';
} else {
    echo '<div class="alert alert-warning text-center mt-3">';
    echo '<h5>⚠️ Algunos tests fallaron</h5>';
    echo '<p>Revisa los errores anteriores para más detalles.</p>';
    echo '</div>';
}

echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';

?>

        <div class="row mt-4">
            <div class="col-12 text-center">
                <a href="index.php" class="btn btn-primary">← Volver al índice de tests</a>
                <a href="../dashboard.php" class="btn btn-secondary">← Volver al dashboard</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
