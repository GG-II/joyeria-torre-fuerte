<?php
// ===================================
// TEST: MODELO MATERIA PRIMA
// VERSIÓN CORREGIDA - BD REAL
// ===================================

session_start();
$_SESSION['usuario_id'] = 1;
$_SESSION['usuario_nombre'] = 'Administrador Test';
$_SESSION['usuario_rol'] = 'administrador';
$_SESSION['usuario_sucursal_id'] = 1;

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/funciones.php';
require_once __DIR__ . '/../models/materia_prima.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Test - Modelo Materia Prima</title>
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
        <h1 class="mb-4">💎 Test: Modelo Materia Prima (BD REAL)</h1>
        <div class="alert alert-warning">
            <strong>⚠️ Columnas REALES:</strong> precio_por_unidad, stock_minimo (SIN sucursal_id)<br>
            <strong>Tipos:</strong> oro, plata, piedra, otro<br>
            <strong>Unidades:</strong> gramos, piezas, quilates
        </div>
        <hr>

<?php

$tests_passed = 0;
$tests_failed = 0;

// TEST 1: Crear materia prima (CON columnas correctas)
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 1: Crear Materia Prima (precio_por_unidad)</h5></div><div class="card-body">';
try {
    $datos = [
        'nombre' => 'Oro 18K Test ' . time(),
        'tipo' => 'oro',  // Valores: oro, plata, piedra, otro
        'unidad_medida' => 'gramos',  // Valores: gramos, piezas, quilates
        'precio_por_unidad' => 250.50,  // NO precio_actual
        'cantidad_disponible' => 50.5,
        'stock_minimo' => 10,  // Columna que SÍ existe
        'activo' => 1
    ];
    
    $materia_id = MateriaPrima::crear($datos);
    
    if ($materia_id) {
        echo '<div class="alert test-success">✅ ÉXITO: Materia prima creada con ID: ' . $materia_id . '</div>';
        echo '<div class="alert test-info"><strong>Confirmado:</strong> Usa precio_por_unidad y stock_minimo (SIN sucursal_id)</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se pudo crear la materia prima</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 2: Listar materias primas
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 2: Listar Materias Primas</h5></div><div class="card-body">';
try {
    $materias = MateriaPrima::listar(['activo' => 1]);
    
    if (is_array($materias)) {
        echo '<div class="alert test-success">✅ ÉXITO: Listado obtenido. Total: ' . count($materias) . '</div>';
        if (count($materias) > 0) {
            echo '<div class="alert test-info"><pre>' . print_r($materias[0], true) . '</pre></div>';
        }
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se listaron las materias primas</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 3: Listar por tipo
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 3: Listar por Tipo (oro, plata, piedra, otro)</h5></div><div class="card-body">';
try {
    $agrupadas = MateriaPrima::listarPorTipo();
    
    if (is_array($agrupadas) && isset($agrupadas['oro'])) {
        echo '<div class="alert test-success">✅ ÉXITO: Materias agrupadas por tipo</div>';
        echo '<div class="alert test-info">Oro: ' . count($agrupadas['oro']) . ', Plata: ' . count($agrupadas['plata']) . ', Piedra: ' . count($agrupadas['piedra']) . ', Otro: ' . count($agrupadas['otro']) . '</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se agruparon las materias</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 4: Actualizar materia prima
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 4: Actualizar Materia Prima</h5></div><div class="card-body">';
try {
    if (isset($materia_id)) {
        $datos_actualizacion = [
            'nombre' => 'Oro 18K ACTUALIZADO',
            'tipo' => 'oro',
            'unidad_medida' => 'gramos',
            'precio_por_unidad' => 275.00,
            'cantidad_disponible' => 60.0,
            'stock_minimo' => 15
        ];
        
        $resultado = MateriaPrima::actualizar($materia_id, $datos_actualizacion);
        
        if ($resultado) {
            echo '<div class="alert test-success">✅ ÉXITO: Materia prima actualizada</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se actualizó la materia prima</div>';
            $tests_failed++;
        }
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 5: Incrementar cantidad
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 5: Incrementar Cantidad</h5></div><div class="card-body">';
try {
    if (isset($materia_id)) {
        $resultado = MateriaPrima::incrementarCantidad($materia_id, 25.5, 'Compra de prueba');
        
        if ($resultado) {
            echo '<div class="alert test-success">✅ ÉXITO: Cantidad incrementada</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se incrementó</div>';
            $tests_failed++;
        }
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 6: Decrementar cantidad
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 6: Decrementar Cantidad</h5></div><div class="card-body">';
try {
    if (isset($materia_id)) {
        $resultado = MateriaPrima::decrementarCantidad($materia_id, 5.0, 'Uso en taller', 1);
        
        if ($resultado) {
            echo '<div class="alert test-success">✅ ÉXITO: Cantidad decrementada</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se decrementó</div>';
            $tests_failed++;
        }
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 7: Actualizar precio
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 7: Actualizar Precio</h5></div><div class="card-body">';
try {
    if (isset($materia_id)) {
        $resultado = MateriaPrima::actualizarPrecio($materia_id, 300.00);
        
        if ($resultado) {
            echo '<div class="alert test-success">✅ ÉXITO: Precio actualizado</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se actualizó el precio</div>';
            $tests_failed++;
        }
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 8: Estadísticas
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 8: Estadísticas</h5></div><div class="card-body">';
try {
    $stats = MateriaPrima::obtenerEstadisticas();
    
    if (is_array($stats) && isset($stats['total_materias'])) {
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

// TEST 9: Eliminar
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 9: Eliminar (Soft Delete)</h5></div><div class="card-body">';
try {
    if (isset($materia_id)) {
        $resultado = MateriaPrima::eliminar($materia_id);
        
        if ($resultado) {
            echo '<div class="alert test-success">✅ ÉXITO: Materia prima eliminada</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se eliminó</div>';
            $tests_failed++;
        }
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
echo '</div>';

if ($tests_failed == 0) {
    echo '<div class="alert alert-success text-center mt-3">';
    echo '<h5>🎉 ¡PERFECTO! Todos los tests pasaron</h5>';
    echo '<p>El modelo usa correctamente las columnas REALES de la BD.</p>';
    echo '</div>';
}

echo '</div></div>';

?>

        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-primary">← Volver</a>
            <a href="../dashboard.php" class="btn btn-secondary">Dashboard</a>
        </div>
    </div>
</body>
</html>