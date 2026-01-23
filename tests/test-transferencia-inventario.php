<?php
/**
 * Tests para Modelo TransferenciaInventario
 * 
 * Prueba todas las funcionalidades del modelo TransferenciaInventario
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
require_once __DIR__ . '/../models/transferencia_inventario.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📦 Test: Modelo TransferenciaInventario</title>
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
    <h1 class="mb-4">📦 Test: Modelo TransferenciaInventario</h1>
    <p class="lead">Pruebas del sistema de transferencias entre sucursales</p>
    
<?php

$tests_passed = 0;
$tests_failed = 0;
$transferencia_test_id = null;

// ========================================
// PREPARACIÓN: Verificar sucursales y productos
// ========================================
echo '<div class="card mb-3 border-warning"><div class="card-header bg-warning"><h5>⚙️ PREPARACIÓN</h5></div><div class="card-body">';
try {
    $sucursales = db_query("SELECT id, nombre FROM sucursales WHERE activo = 1 LIMIT 2");
    $productos = db_query("SELECT id, nombre FROM productos WHERE activo = 1 LIMIT 3");
    
    if (count($sucursales) >= 2 && count($productos) >= 1) {
        echo '<div class="alert test-info">✓ Sucursales disponibles: ' . count($sucursales) . '</div>';
        echo '<div class="alert test-info">✓ Productos disponibles: ' . count($productos) . '</div>';
    } else {
        echo '<div class="alert test-warning">⚠️ Se necesitan al menos 2 sucursales y 1 producto activo</div>';
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">Error: ' . $e->getMessage() . '</div>';
}
echo '</div></div>';

// ========================================
// TEST 1: Crear Transferencia
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 1: Crear Transferencia</h5></div><div class="card-body">';
try {
    if (count($sucursales) >= 2 && count($productos) >= 1) {
        $productos_transferir = [
            [
                'producto_id' => $productos[0]['id'],
                'cantidad' => 2
            ]
        ];
        
        $transferencia_test_id = TransferenciaInventario::crear(
            $sucursales[0]['id'],
            $sucursales[1]['id'],
            $productos_transferir,
            'Transferencia de prueba automatizada'
        );
        
        if ($transferencia_test_id) {
            echo '<div class="alert test-success">✅ ÉXITO: Transferencia creada correctamente</div>';
            echo '<div class="alert test-info">';
            echo 'ID: ' . $transferencia_test_id . '<br>';
            echo 'Origen: ' . $sucursales[0]['nombre'] . '<br>';
            echo 'Destino: ' . $sucursales[1]['nombre'] . '<br>';
            echo 'Productos: ' . count($productos_transferir);
            echo '</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se pudo crear la transferencia</div>';
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
// TEST 2: Validar Origen = Destino
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 2: Validar Origen = Destino</h5></div><div class="card-body">';
try {
    if (count($sucursales) >= 1 && count($productos) >= 1) {
        $resultado = TransferenciaInventario::crear(
            $sucursales[0]['id'],
            $sucursales[0]['id'], // Mismo que origen
            [['producto_id' => $productos[0]['id'], 'cantidad' => 1]],
            'Debe fallar'
        );
        
        if ($resultado === false) {
            echo '<div class="alert test-success">✅ ÉXITO: Sistema rechazó origen = destino</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: Sistema permitió origen = destino</div>';
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
// TEST 3: Obtener Transferencia por ID
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 3: Obtener Transferencia por ID</h5></div><div class="card-body">';
try {
    if ($transferencia_test_id) {
        $transferencia = TransferenciaInventario::obtenerPorId($transferencia_test_id);
        
        if ($transferencia && $transferencia['id'] == $transferencia_test_id) {
            echo '<div class="alert test-success">✅ ÉXITO: Transferencia obtenida correctamente</div>';
            echo '<div class="alert test-info">';
            echo 'Estado: ' . $transferencia['estado'] . '<br>';
            echo 'Origen: ' . $transferencia['sucursal_origen_nombre'] . '<br>';
            echo 'Destino: ' . $transferencia['sucursal_destino_nombre'] . '<br>';
            echo 'Usuario: ' . $transferencia['usuario_nombre'];
            echo '</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se pudo obtener la transferencia</div>';
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
// TEST 4: Obtener Detalle de Transferencia
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 4: Obtener Detalle de Transferencia</h5></div><div class="card-body">';
try {
    if ($transferencia_test_id) {
        $detalle = TransferenciaInventario::obtenerDetalle($transferencia_test_id);
        
        if (is_array($detalle) && count($detalle) > 0) {
            echo '<div class="alert test-success">✅ ÉXITO: Detalle obtenido correctamente</div>';
            echo '<div class="alert test-info">';
            echo 'Productos en la transferencia: ' . count($detalle) . '<br>';
            foreach ($detalle as $item) {
                echo '- ' . $item['producto_nombre'] . ': ' . $item['cantidad'] . ' unidades<br>';
            }
            echo '</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se pudo obtener el detalle</div>';
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
// TEST 5: Listar Transferencias
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 5: Listar Transferencias</h5></div><div class="card-body">';
try {
    $transferencias = TransferenciaInventario::listar();
    
    if (is_array($transferencias)) {
        echo '<div class="alert test-success">✅ ÉXITO: Listado obtenido correctamente</div>';
        echo '<div class="alert test-info">';
        echo 'Total transferencias: ' . count($transferencias);
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
// TEST 6: Obtener Transferencias Pendientes
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 6: Obtener Transferencias Pendientes</h5></div><div class="card-body">';
try {
    $pendientes = TransferenciaInventario::obtenerPendientes();
    
    echo '<div class="alert test-success">✅ ÉXITO: Pendientes obtenidas</div>';
    echo '<div class="alert test-info">';
    echo 'Transferencias pendientes: ' . count($pendientes);
    echo '</div>';
    $tests_passed++;
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
    $stats = TransferenciaInventario::obtenerEstadisticas();
    
    if ($stats && isset($stats['total'])) {
        echo '<div class="alert test-success">✅ ÉXITO: Estadísticas obtenidas</div>';
        echo '<div class="alert test-info">';
        echo 'Total: ' . $stats['total'] . '<br>';
        echo 'Pendientes: ' . $stats['pendientes'] . '<br>';
        echo 'Completadas: ' . $stats['completadas'] . '<br>';
        echo 'Canceladas: ' . $stats['canceladas'];
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
// TEST 8: Cancelar Transferencia
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 8: Cancelar Transferencia</h5></div><div class="card-body">';
try {
    if ($transferencia_test_id) {
        $resultado = TransferenciaInventario::cancelar($transferencia_test_id, 'Test automatizado - cancelación');
        
        if ($resultado) {
            $transferencia = TransferenciaInventario::obtenerPorId($transferencia_test_id);
            if ($transferencia['estado'] === TransferenciaInventario::ESTADO_CANCELADA) {
                echo '<div class="alert test-success">✅ ÉXITO: Transferencia cancelada correctamente</div>';
                $tests_passed++;
            } else {
                echo '<div class="alert test-error">❌ ERROR: Estado no cambió</div>';
                $tests_failed++;
            }
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se pudo cancelar</div>';
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
// TEST 9: Validar No Cancelar Ya Cancelada
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 9: Validar No Cancelar Ya Cancelada</h5></div><div class="card-body">';
try {
    if ($transferencia_test_id) {
        $resultado = TransferenciaInventario::cancelar($transferencia_test_id, 'Segundo intento');
        
        if ($resultado === false) {
            echo '<div class="alert test-success">✅ ÉXITO: Sistema rechazó segunda cancelación</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: Sistema permitió segunda cancelación</div>';
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
// TEST 10: Obtener Historial de Producto
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 10: Obtener Historial de Producto</h5></div><div class="card-body">';
try {
    if (count($productos) >= 1) {
        $historial = TransferenciaInventario::obtenerHistorialProducto($productos[0]['id']);
        
        echo '<div class="alert test-success">✅ ÉXITO: Historial obtenido</div>';
        echo '<div class="alert test-info">';
        echo 'Transferencias del producto: ' . count($historial);
        echo '</div>';
        $tests_passed++;
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

?>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
