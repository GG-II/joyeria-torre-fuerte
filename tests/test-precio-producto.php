<?php
/**
 * Tests para Modelo PrecioProducto
 * 
 * Prueba todas las funcionalidades del modelo PrecioProducto
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
$_SESSION['usuario_id'] = 1;
$_SESSION['usuario_nombre'] = 'Admin Test';
$_SESSION['usuario_rol'] = 'administrador';

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/funciones.php';
require_once __DIR__ . '/../models/precio_producto.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>💰 Test: Modelo PrecioProducto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f5f5f5; padding: 20px; }
        .test-success { background-color: #d4edda; border-color: #c3e6cb; color: #155724; }
        .test-error { background-color: #f8d7da; border-color: #f5c6cb; color: #721c24; }
        .test-info { background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; }
    </style>
</head>
<body>

<div class="container">
    <h1 class="mb-4">💰 Test: Modelo PrecioProducto</h1>
    <p class="lead">Pruebas del sistema de gestión de precios</p>
    
<?php

$tests_passed = 0;
$tests_failed = 0;
$precio_test_id = null;
$producto_test = null;

// ========================================
// PREPARACIÓN: Obtener un producto para pruebas
// ========================================
echo '<div class="card mb-3 border-warning"><div class="card-header bg-warning"><h5>⚙️ PREPARACIÓN: Buscar Producto</h5></div><div class="card-body">';
try {
    // Buscar un producto activo
    $producto_test = db_query_one("SELECT id, nombre, codigo FROM productos WHERE activo = 1 LIMIT 1");
    
    if ($producto_test) {
        echo '<div class="alert test-info">✓ Producto encontrado: ' . $producto_test['nombre'] . ' (ID: ' . $producto_test['id'] . ')</div>';
        
        // Limpiar precios de prueba anteriores (si existen)
        $precios_existentes = PrecioProducto::obtenerPorProducto($producto_test['id']);
        if (count($precios_existentes) > 0) {
            echo '<div class="alert alert-secondary">ℹ️ Este producto ya tiene ' . count($precios_existentes) . ' precios. Los tests usarán un tipo diferente.</div>';
        }
    } else {
        echo '<div class="alert test-warning">⚠️ No hay productos activos. Algunos tests serán omitidos.</div>';
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">Error: ' . $e->getMessage() . '</div>';
}
echo '</div></div>';

// ========================================
// TEST 1: Crear Precio Descuento
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 1: Crear Precio Descuento (Test)</h5></div><div class="card-body">';
try {
    if ($producto_test) {
        // Verificar si ya existe este tipo de precio
        $precio_existente = PrecioProducto::obtenerPorProductoYTipo($producto_test['id'], PrecioProducto::TIPO_DESCUENTO);
        
        if ($precio_existente) {
            // Eliminar el precio existente para poder hacer el test
            PrecioProducto::eliminar($precio_existente['id']);
        }
        
        $datos = [
            'producto_id' => $producto_test['id'],
            'tipo_precio' => PrecioProducto::TIPO_DESCUENTO,
            'precio' => 2500.00,
            'activo' => 1
        ];
        
        $precio_test_id = PrecioProducto::crear($datos);
        
        if ($precio_test_id) {
            echo '<div class="alert test-success">✅ ÉXITO: Precio creado correctamente</div>';
            echo '<div class="alert test-info">';
            echo 'ID: ' . $precio_test_id . '<br>';
            echo 'Producto: ' . $producto_test['nombre'] . '<br>';
            echo 'Tipo: descuento<br>';
            echo 'Precio: ' . formato_dinero($datos['precio']);
            echo '</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se pudo crear el precio</div>';
            $tests_failed++;
        }
    } else {
        echo '<div class="alert test-info">⚠️ Test omitido</div>';
        $tests_passed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 2: Crear Precio Especial
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 2: Crear Precio Especial (Test)</h5></div><div class="card-body">';
try {
    if ($producto_test) {
        // Verificar si ya existe este tipo de precio
        $precio_existente = PrecioProducto::obtenerPorProductoYTipo($producto_test['id'], PrecioProducto::TIPO_ESPECIAL);
        
        if ($precio_existente) {
            // Eliminar el precio existente para poder hacer el test
            PrecioProducto::eliminar($precio_existente['id']);
        }
        
        $resultado = PrecioProducto::crear([
            'producto_id' => $producto_test['id'],
            'tipo_precio' => PrecioProducto::TIPO_ESPECIAL,
            'precio' => 2200.00,
            'activo' => 1
        ]);
        
        if ($resultado) {
            echo '<div class="alert test-success">✅ ÉXITO: Precio especial creado correctamente</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se pudo crear el precio</div>';
            $tests_failed++;
        }
    } else {
        echo '<div class="alert test-info">⚠️ Test omitido</div>';
        $tests_passed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 3: Validar Tipo Duplicado
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 3: Validar Tipo Duplicado</h5></div><div class="card-body">';
try {
    if ($producto_test && $precio_test_id) {
        $excepcion_lanzada = false;
        
        try {
            // Intentar crear otro precio descuento para el mismo producto
            $resultado = PrecioProducto::crear([
                'producto_id' => $producto_test['id'],
                'tipo_precio' => PrecioProducto::TIPO_DESCUENTO,
                'precio' => 3000.00
            ]);
        } catch (Exception $e) {
            $excepcion_lanzada = true;
            $mensaje = $e->getMessage();
        }
        
        if ($resultado === false || $excepcion_lanzada) {
            echo '<div class="alert test-success">✅ ÉXITO: Sistema rechazó correctamente tipo duplicado</div>';
            if ($excepcion_lanzada) {
                echo '<div class="alert test-info">Mensaje: ' . htmlspecialchars($mensaje) . '</div>';
            }
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: Sistema permitió tipo duplicado</div>';
            $tests_failed++;
        }
    } else {
        echo '<div class="alert test-info">⚠️ Test omitido</div>';
        $tests_passed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 4: Obtener Precio por ID
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 4: Obtener Precio por ID</h5></div><div class="card-body">';
try {
    if ($precio_test_id) {
        $precio = PrecioProducto::obtenerPorId($precio_test_id);
        
        if ($precio && $precio['id'] == $precio_test_id) {
            echo '<div class="alert test-success">✅ ÉXITO: Precio obtenido correctamente</div>';
            echo '<div class="alert test-info">';
            echo 'Producto: ' . $precio['producto_nombre'] . '<br>';
            echo 'Tipo: ' . $precio['tipo_precio'] . '<br>';
            echo 'Precio: ' . formato_dinero($precio['precio']);
            echo '</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se pudo obtener el precio</div>';
            $tests_failed++;
        }
    } else {
        echo '<div class="alert test-info">⚠️ Test omitido</div>';
        $tests_passed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 5: Obtener Precios por Producto
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 5: Obtener Precios por Producto</h5></div><div class="card-body">';
try {
    if ($producto_test) {
        $precios = PrecioProducto::obtenerPorProducto($producto_test['id']);
        
        if (is_array($precios)) {
            echo '<div class="alert test-success">✅ ÉXITO: Precios obtenidos correctamente</div>';
            echo '<div class="alert test-info">';
            echo 'Cantidad de precios: ' . count($precios) . '<br>';
            if (count($precios) > 0) {
                echo '<strong>Tipos disponibles:</strong><br>';
                foreach ($precios as $p) {
                    echo '- ' . $p['tipo_precio'] . ': ' . formato_dinero($p['precio']) . '<br>';
                }
            }
            echo '</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se pudieron obtener los precios</div>';
            $tests_failed++;
        }
    } else {
        echo '<div class="alert test-info">⚠️ Test omitido</div>';
        $tests_passed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 6: Obtener Precio Aplicable
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 6: Obtener Precio Aplicable</h5></div><div class="card-body">';
try {
    if ($producto_test) {
        $precio_mayorista = PrecioProducto::obtenerPrecioAplicable($producto_test['id'], PrecioProducto::TIPO_MAYORISTA);
        $precio_publico = PrecioProducto::obtenerPrecioAplicable($producto_test['id'], PrecioProducto::TIPO_PUBLICO);
        
        if ($precio_mayorista !== false && $precio_publico !== false) {
            echo '<div class="alert test-success">✅ ÉXITO: Precios aplicables obtenidos</div>';
            echo '<div class="alert test-info">';
            echo 'Precio mayorista: ' . formato_dinero($precio_mayorista) . '<br>';
            echo 'Precio público: ' . formato_dinero($precio_publico);
            echo '</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se pudieron obtener precios aplicables</div>';
            $tests_failed++;
        }
    } else {
        echo '<div class="alert test-info">⚠️ Test omitido</div>';
        $tests_passed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 7: Actualizar Precio por Tipo
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 7: Actualizar Precio por Tipo</h5></div><div class="card-body">';
try {
    if ($producto_test) {
        $resultado = PrecioProducto::actualizarPrecioPorTipo(
            $producto_test['id'],
            PrecioProducto::TIPO_MAYORISTA,
            1800.00
        );
        
        if ($resultado) {
            $precio_actualizado = PrecioProducto::obtenerPrecioAplicable($producto_test['id'], PrecioProducto::TIPO_MAYORISTA);
            echo '<div class="alert test-success">✅ ÉXITO: Precio actualizado correctamente</div>';
            echo '<div class="alert test-info">';
            echo 'Nuevo precio mayorista: ' . formato_dinero($precio_actualizado);
            echo '</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se pudo actualizar el precio</div>';
            $tests_failed++;
        }
    } else {
        echo '<div class="alert test-info">⚠️ Test omitido</div>';
        $tests_passed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 8: Obtener Precios por Tipo
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 8: Obtener Precios por Tipo</h5></div><div class="card-body">';
try {
    $precios_mayorista = PrecioProducto::obtenerPorTipo(PrecioProducto::TIPO_MAYORISTA);
    
    echo '<div class="alert test-success">✅ ÉXITO: Precios mayorista obtenidos</div>';
    echo '<div class="alert test-info">';
    echo 'Productos con precio mayorista: ' . count($precios_mayorista);
    echo '</div>';
    $tests_passed++;
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 9: Cambiar Estado
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 9: Cambiar Estado</h5></div><div class="card-body">';
try {
    if ($precio_test_id) {
        // Desactivar
        $resultado1 = PrecioProducto::desactivar($precio_test_id);
        $precio = PrecioProducto::obtenerPorId($precio_test_id);
        $desactivado = ($precio['activo'] == 0);
        
        // Activar
        $resultado2 = PrecioProducto::activar($precio_test_id);
        $precio = PrecioProducto::obtenerPorId($precio_test_id);
        $activado = ($precio['activo'] == 1);
        
        if ($resultado1 && $resultado2 && $desactivado && $activado) {
            echo '<div class="alert test-success">✅ ÉXITO: Estado cambiado correctamente</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se pudo cambiar el estado</div>';
            $tests_failed++;
        }
    } else {
        echo '<div class="alert test-info">⚠️ Test omitido</div>';
        $tests_passed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 10: Obtener Estadísticas
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 10: Obtener Estadísticas</h5></div><div class="card-body">';
try {
    $stats = PrecioProducto::obtenerEstadisticas();
    
    if ($stats && isset($stats['total'])) {
        echo '<div class="alert test-success">✅ ÉXITO: Estadísticas obtenidas</div>';
        echo '<div class="alert test-info">';
        echo 'Total precios: ' . $stats['total'] . '<br>';
        echo 'Activos: ' . $stats['activos'] . '<br>';
        echo 'Públicos: ' . $stats['tipo_publico'] . '<br>';
        echo 'Mayoristas: ' . $stats['tipo_mayorista'] . '<br>';
        echo 'Precio promedio: ' . formato_dinero($stats['precio_promedio']);
        echo '</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se pudieron obtener estadísticas</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// RESUMEN FINAL
// ========================================
$total_tests = $tests_passed + $tests_failed;
$porcentaje = $total_tests > 0 ? round(($tests_passed / $total_tests) * 100, 1) : 0;
$clase_resumen = $tests_failed === 0 ? 'success' : ($porcentaje >= 70 ? 'warning' : 'danger');

echo '<div class="card border-' . $clase_resumen . '">
        <div class="card-header bg-' . $clase_resumen . ' text-white">
            <h4>📊 Resumen de Tests</h4>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-3">
                    <h2 class="text-success">' . $tests_passed . '</h2>
                    <p>Exitosos</p>
                </div>
                <div class="col-md-3">
                    <h2 class="text-danger">' . $tests_failed . '</h2>
                    <p>Fallidos</p>
                </div>
                <div class="col-md-3">
                    <h2 class="text-primary">' . $total_tests . '</h2>
                    <p>Total</p>
                </div>
                <div class="col-md-3">
                    <h2 class="text-info">' . $porcentaje . '%</h2>
                    <p>Tasa de Éxito</p>
                </div>
            </div>
        </div>
      </div>';

?>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
