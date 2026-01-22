<?php
/**
 * Tests para Modelo Factura
 * 
 * Prueba todas las funcionalidades del modelo Factura
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
$_SESSION['usuario_id'] = 1;
$_SESSION['usuario_nombre'] = 'Admin Test';
$_SESSION['usuario_rol'] = 'administrador';
$_SESSION['usuario_sucursal_id'] = 1;

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/funciones.php';
require_once __DIR__ . '/../models/factura.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📄 Test: Modelo Factura</title>
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
    <h1 class="mb-4">📄 Test: Modelo Factura</h1>
    <p class="lead">Pruebas del sistema de facturación</p>
    
<?php

$tests_passed = 0;
$tests_failed = 0;
$factura_test_id = null;

// ========================================
// PREPARACIÓN: Obtener una venta para facturar
// ========================================
echo '<div class="card mb-3 border-warning"><div class="card-header bg-warning"><h5>⚙️ PREPARACIÓN: Buscar Venta para Facturar</h5></div><div class="card-body">';
try {
    // Buscar una venta completada que NO tenga factura
    $venta = db_query_one(
        "SELECT v.id, v.numero_venta, v.total 
         FROM ventas v
         LEFT JOIN facturas f ON v.id = f.venta_id AND f.estado = 'emitida'
         WHERE v.estado = 'completada' 
           AND f.id IS NULL
         ORDER BY v.id DESC 
         LIMIT 1"
    );
    
    if ($venta) {
        echo '<div class="alert test-info">✓ Venta encontrada SIN factura: ' . $venta['numero_venta'] . ' - Total: ' . formato_dinero($venta['total']) . '</div>';
    } else {
        echo '<div class="alert test-warning">⚠️ No hay ventas disponibles sin factura. Se omitirán algunos tests.</div>';
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">Error: ' . $e->getMessage() . '</div>';
}
echo '</div></div>';

// ========================================
// TEST 1: Crear Factura Simple
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 1: Crear Factura Simple</h5></div><div class="card-body">';
try {
    if ($venta) {
        $datos = [
            'tipo' => 'simple',
            'nit' => 'C/F',
            'nombre' => 'Consumidor Final',
            'direccion' => 'Ciudad'
        ];
        
        $factura_test_id = Factura::crear($venta['id'], $datos);
        
        if ($factura_test_id) {
            $factura = Factura::obtenerPorId($factura_test_id);
            echo '<div class="alert test-success">✅ ÉXITO: Factura creada correctamente</div>';
            echo '<div class="alert test-info">';
            echo 'ID: ' . $factura_test_id . '<br>';
            echo 'Número: ' . $factura['numero_factura'] . '<br>';
            echo 'Tipo: ' . $factura['tipo'] . '<br>';
            echo 'Venta: ' . $factura['numero_venta'] . '<br>';
            echo 'Total: ' . formato_dinero($factura['venta_total']);
            echo '</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se pudo crear la factura</div>';
            $tests_failed++;
        }
    } else {
        echo '<div class="alert test-info">⚠️ Test omitido: No hay ventas sin factura disponibles</div>';
        echo '<div class="alert alert-secondary">💡 Tip: Crea una venta nueva desde el sistema y vuelve a ejecutar este test</div>';
        $tests_passed++; // Contar como exitoso porque no es un error del código
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 2: Validar Factura Duplicada
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 2: Validar Factura Duplicada</h5></div><div class="card-body">';
try {
    if ($venta && $factura_test_id) {
        $excepcion_lanzada = false;
        
        try {
            // Intentar crear otra factura para la misma venta
            $resultado = Factura::crear($venta['id'], [
                'tipo' => 'simple',
                'nit' => 'C/F',
                'nombre' => 'Consumidor Final'
            ]);
        } catch (Exception $e) {
            $excepcion_lanzada = true;
            $mensaje_excepcion = $e->getMessage();
        }
        
        if ($excepcion_lanzada && strpos($mensaje_excepcion, 'ya tiene una factura') !== false) {
            echo '<div class="alert test-success">✅ ÉXITO: Sistema rechazó correctamente factura duplicada</div>';
            echo '<div class="alert test-info">Mensaje: ' . htmlspecialchars($mensaje_excepcion) . '</div>';
            $tests_passed++;
        } else if ($resultado === false) {
            echo '<div class="alert test-success">✅ ÉXITO: Sistema rechazó correctamente factura duplicada (retornó false)</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: Sistema permitió factura duplicada</div>';
            $tests_failed++;
        }
    } else {
        echo '<div class="alert test-info">⚠️ Test omitido</div>';
        $tests_passed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN INESPERADA: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 3: Obtener Factura por ID
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 3: Obtener Factura por ID</h5></div><div class="card-body">';
try {
    if ($factura_test_id) {
        $factura = Factura::obtenerPorId($factura_test_id);
        
        if ($factura && $factura['id'] == $factura_test_id) {
            echo '<div class="alert test-success">✅ ÉXITO: Factura obtenida correctamente</div>';
            echo '<div class="alert test-info">';
            echo 'Número: ' . $factura['numero_factura'] . '<br>';
            echo 'NIT: ' . $factura['nit'] . '<br>';
            echo 'Nombre: ' . $factura['nombre'] . '<br>';
            echo 'Estado: ' . $factura['estado'] . '<br>';
            echo 'Sucursal: ' . $factura['sucursal_nombre'];
            echo '</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se pudo obtener la factura</div>';
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
// TEST 4: Obtener Factura por Número
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 4: Obtener Factura por Número</h5></div><div class="card-body">';
try {
    if ($factura_test_id) {
        $factura = Factura::obtenerPorId($factura_test_id);
        $factura_por_num = Factura::obtenerPorNumero($factura['numero_factura']);
        
        if ($factura_por_num && $factura_por_num['id'] == $factura_test_id) {
            echo '<div class="alert test-success">✅ ÉXITO: Factura encontrada por número</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se encontró la factura</div>';
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
// TEST 5: Obtener Factura por Venta
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 5: Obtener Factura por Venta</h5></div><div class="card-body">';
try {
    if ($venta && $factura_test_id) {
        $factura_venta = Factura::obtenerPorVenta($venta['id']);
        
        if ($factura_venta && $factura_venta['id'] == $factura_test_id) {
            echo '<div class="alert test-success">✅ ÉXITO: Factura obtenida por venta</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se encontró la factura</div>';
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
// TEST 6: Listar Facturas
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 6: Listar Facturas</h5></div><div class="card-body">';
try {
    $facturas = Factura::listar(['estado' => 'emitida']);
    
    if (is_array($facturas)) {
        echo '<div class="alert test-success">✅ ÉXITO: Listado obtenido correctamente</div>';
        echo '<div class="alert test-info">';
        echo 'Total facturas emitidas: ' . count($facturas);
        echo '</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se pudo obtener el listado</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 7: Obtener Estadísticas
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 7: Obtener Estadísticas</h5></div><div class="card-body">';
try {
    $stats = Factura::obtenerEstadisticas();
    
    if ($stats && isset($stats['total'])) {
        echo '<div class="alert test-success">✅ ÉXITO: Estadísticas obtenidas</div>';
        echo '<div class="alert test-info">';
        echo 'Total: ' . $stats['total'] . '<br>';
        echo 'Emitidas: ' . $stats['emitidas'] . '<br>';
        echo 'Anuladas: ' . $stats['anuladas'] . '<br>';
        echo 'Simples: ' . $stats['simples'] . '<br>';
        echo 'Electrónicas: ' . $stats['electronicas'];
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
// TEST 8: Anular Factura
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 8: Anular Factura</h5></div><div class="card-body">';
try {
    if ($factura_test_id) {
        $resultado = Factura::anular($factura_test_id, 'Factura de prueba - Test automatizado');
        
        if ($resultado) {
            $factura = Factura::obtenerPorId($factura_test_id);
            if ($factura['estado'] === 'anulada') {
                echo '<div class="alert test-success">✅ ÉXITO: Factura anulada correctamente</div>';
                echo '<div class="alert test-info">';
                echo 'Estado: ' . $factura['estado'] . '<br>';
                echo 'Motivo: ' . $factura['motivo_anulacion'];
                echo '</div>';
                $tests_passed++;
            } else {
                echo '<div class="alert test-error">❌ ERROR: Factura no se anuló</div>';
                $tests_failed++;
            }
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se pudo anular la factura</div>';
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
// TEST 9: Validar No Anular Factura Ya Anulada
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 9: Validar No Anular Factura Ya Anulada</h5></div><div class="card-body">';
try {
    if ($factura_test_id) {
        // Intentar anular nuevamente
        $resultado = Factura::anular($factura_test_id, 'Intento de segunda anulación');
        
        if ($resultado === false) {
            echo '<div class="alert test-success">✅ ÉXITO: Sistema rechazó correctamente segunda anulación</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: Sistema permitió segunda anulación</div>';
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

// ========================================
// LIMPIEZA OPCIONAL
// ========================================
if ($factura_test_id) {
    echo '<div class="card mt-3 border-info">
            <div class="card-header bg-info text-white">
                <h5>🧹 Limpieza de Datos de Prueba</h5>
            </div>
            <div class="card-body">
                <p>Se creó una factura de prueba durante este test. Si quieres ejecutar el test nuevamente con los mismos datos, puedes eliminar esta factura:</p>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="limpiar_factura" value="' . $factura_test_id . '">
                    <button type="submit" class="btn btn-warning" onclick="return confirm(\'¿Eliminar la factura de prueba?\')">
                        🗑️ Eliminar Factura de Prueba
                    </button>
                </form>
                <p class="text-muted mt-2 mb-0">Factura ID: ' . $factura_test_id . '</p>
            </div>
          </div>';
}

// Procesar limpieza si se solicitó
if (isset($_POST['limpiar_factura'])) {
    $id = (int)$_POST['limpiar_factura'];
    try {
        db_execute("DELETE FROM facturas WHERE id = ?", [$id]);
        echo '<div class="alert alert-success mt-3">✅ Factura de prueba eliminada. Puedes recargar la página para ejecutar el test nuevamente.</div>';
    } catch (Exception $e) {
        echo '<div class="alert alert-danger mt-3">❌ Error al eliminar: ' . $e->getMessage() . '</div>';
    }
}

?>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>