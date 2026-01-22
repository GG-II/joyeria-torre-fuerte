<?php
// ================================================
// TEST: MODELO INVENTARIO
// Sistema de Gestión - Joyería Torre Fuerte
// ================================================

session_start();
$_SESSION['usuario_id'] = 1;
$_SESSION['usuario_nombre'] = 'Administrador Test';
$_SESSION['usuario_rol'] = 'administrador';
$_SESSION['usuario_sucursal_id'] = 1;

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/funciones.php';
require_once __DIR__ . '/../models/inventario.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Test - Modelo Inventario</title>
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
        <h1 class="mb-4">📦 Test: Modelo Inventario</h1>
        <hr>

<?php

$tests_passed = 0;
$tests_failed = 0;

// TEST 1: Crear inventario
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 1: Crear Inventario</h5></div><div class="card-body">';
try {
    $producto_id = 1;  // Asume que existe producto con ID 1
    $sucursal_id = 1;
    
    $resultado = Inventario::crear($producto_id, $sucursal_id, 100, 10, 0);
    
    if ($resultado) {
        echo '<div class="alert test-success">✅ ÉXITO: Inventario creado/actualizado</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se creó el inventario</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 2: Obtener inventario por sucursal
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 2: Listar Inventario por Sucursal</h5></div><div class="card-body">';
try {
    $inventario = Inventario::listarPorSucursal(1, [], 1, 10);
    
    if (is_array($inventario)) {
        echo '<div class="alert test-success">✅ ÉXITO: Inventario listado. Productos: ' . count($inventario) . '</div>';
        if (count($inventario) > 0) {
            echo '<div class="alert test-info"><pre>' . print_r($inventario[0], true) . '</pre></div>';
        }
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se listó el inventario</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 3: Incrementar stock
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 3: Incrementar Stock</h5></div><div class="card-body">';
try {
    $resultado = Inventario::incrementarStock(1, 1, 50, 'Compra de prueba', 'compra', null);
    
    if ($resultado) {
        echo '<div class="alert test-success">✅ ÉXITO: Stock incrementado correctamente</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se incrementó el stock</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 4: Decrementar stock
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 4: Decrementar Stock</h5></div><div class="card-body">';
try {
    $resultado = Inventario::decrementarStock(1, 1, 10, 'Venta de prueba', 'venta', null);
    
    if ($resultado) {
        echo '<div class="alert test-success">✅ ÉXITO: Stock decrementado correctamente</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se decrementó el stock</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 5: Obtener historial
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 5: Obtener Historial de Movimientos</h5></div><div class="card-body">';
try {
    $historial = Inventario::obtenerHistorial(1, 1, 10);
    
    if (is_array($historial)) {
        echo '<div class="alert test-success">✅ ÉXITO: Historial obtenido. Movimientos: ' . count($historial) . '</div>';
        if (count($historial) > 0) {
            echo '<div class="alert test-info"><pre>' . print_r($historial[0], true) . '</pre></div>';
        }
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se obtuvo el historial</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 6: Obtener stock bajo
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 6: Obtener Productos con Stock Bajo</h5></div><div class="card-body">';
try {
    $stock_bajo = Inventario::obtenerStockBajo(1);
    
    if (is_array($stock_bajo)) {
        echo '<div class="alert test-success">✅ ÉXITO: Stock bajo consultado. Productos: ' . count($stock_bajo) . '</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se consultó el stock bajo</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 7: Estadísticas
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 7: Obtener Estadísticas de Inventario</h5></div><div class="card-body">';
try {
    $stats = Inventario::obtenerEstadisticas(1);
    
    if (is_array($stats) && isset($stats['total_productos'])) {
        echo '<div class="alert test-success">✅ ÉXITO: Estadísticas obtenidas</div>';
        echo '<div class="alert test-info"><pre>' . print_r($stats, true) . '</pre></div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se obtuvieron estadísticas</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// RESUMEN
$total_tests = $tests_passed + $tests_failed;
$porcentaje = $total_tests > 0 ? round(($tests_passed / $total_tests) * 100, 2) : 0;

echo '<div class="card mt-4"><div class="card-header bg-dark text-white"><h4>📊 Resumen</h4></div><div class="card-body">';
echo '<div class="row text-center">';
echo '<div class="col-md-4"><h3 class="text-success">' . $tests_passed . '</h3><p>Exitosos</p></div>';
echo '<div class="col-md-4"><h3 class="text-danger">' . $tests_failed . '</h3><p>Fallidos</p></div>';
echo '<div class="col-md-4"><h3 class="text-primary">' . $porcentaje . '%</h3><p>Tasa de Éxito</p></div>';
echo '</div></div></div>';

?>

        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-primary">← Volver</a>
            <a href="../dashboard.php" class="btn btn-secondary">Dashboard</a>
        </div>
    </div>
</body>
</html>
