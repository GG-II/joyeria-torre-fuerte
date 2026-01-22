<?php
// ================================================
// TEST: MODELO VENTA
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
require_once __DIR__ . '/../models/cliente.php';
require_once __DIR__ . '/../models/producto.php';
require_once __DIR__ . '/../models/inventario.php';
require_once __DIR__ . '/../models/venta.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Test - Modelo Venta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .test-success { background-color: #d4edda; border-left: 4px solid #28a745; }
        .test-error { background-color: #f8d7da; border-left: 4px solid #dc3545; }
        .test-info { background-color: #d1ecf1; border-left: 4px solid #0dcaf0; }
        .test-warning { background-color: #fff3cd; border-left: 4px solid #ffc107; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="container my-5">
        <h1 class="mb-4">🛒 Test: Modelo Venta</h1>
        <hr>

<?php

$tests_passed = 0;
$tests_failed = 0;

// PREPARACIÓN: Abrir caja
echo '<div class="card mb-3 border-warning"><div class="card-header bg-warning"><h5>⚙️ PREPARACIÓN: Abrir Caja</h5></div><div class="card-body">';
try {
    // Verificar si ya hay caja abierta
    $caja_existente = db_query_one(
        "SELECT id FROM cajas WHERE usuario_id = ? AND estado = 'abierta'",
        [usuario_actual_id()]
    );
    
    if (!$caja_existente) {
        $sql = "INSERT INTO cajas (usuario_id, sucursal_id, fecha_apertura, monto_inicial, estado)
                VALUES (?, ?, NOW(), 1000.00, 'abierta')";
        db_execute($sql, [usuario_actual_id(), 1]);
        echo '<div class="alert test-info">✓ Caja abierta con monto inicial de Q1,000.00</div>';
    } else {
        echo '<div class="alert test-info">✓ Ya existe caja abierta - ID: ' . $caja_existente['id'] . '</div>';
    }
} catch (Exception $e) {
    echo '<div class="alert test-warning">⚠️ Error al abrir caja: ' . $e->getMessage() . '</div>';
}
echo '</div></div>';

// TEST 1: Venta simple - 1 producto - pago en efectivo
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 1: Venta Simple (1 Producto - Efectivo)</h5></div><div class="card-body">';
try {
    $datos_venta = [
        'sucursal_id' => 1,
        'vendedor_id' => 1,
        'cliente_id' => null, // Venta mostrador
        'productos' => [
            [
                'producto_id' => 1, // Asume que existe producto ID 1
                'cantidad' => 2,
                'precio_unitario' => 50.00, // Precio manual
                'tipo_precio' => 'publico'
            ]
        ],
        'formas_pago' => [
            [
                'forma_pago' => 'efectivo',
                'monto' => 100.00
            ]
        ],
        'descuento' => 0,
        'tipo_venta' => 'normal'
    ];
    
    $venta_id = Venta::crear($datos_venta);
    
    if ($venta_id) {
        $venta = Venta::obtenerPorId($venta_id);
        echo '<div class="alert test-success">✅ ÉXITO: Venta creada - ID: ' . $venta_id . '</div>';
        echo '<div class="alert test-info">Número: ' . $venta['numero_venta'] . '<br>Total: ' . formato_dinero($venta['total']) . '</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se creó la venta</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 2: Venta con múltiples formas de pago
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 2: Venta con Múltiples Formas de Pago</h5></div><div class="card-body">';
try {
    $datos_venta_multiple = [
        'sucursal_id' => 1,
        'vendedor_id' => 1,
        'cliente_id' => 1, // Cliente del test anterior
        'productos' => [
            [
                'producto_id' => 1,
                'cantidad' => 3,
                'precio_unitario' => 50.00, // Precio manual
                'tipo_precio' => 'publico'
            ]
        ],
        'formas_pago' => [
            [
                'forma_pago' => 'efectivo',
                'monto' => 100.00
            ],
            [
                'forma_pago' => 'tarjeta_credito',
                'monto' => 50.00,
                'referencia' => 'AUTH-123456'
            ]
        ],
        'descuento' => 0,
        'tipo_venta' => 'normal'
    ];
    
    $venta_id2 = Venta::crear($datos_venta_multiple);
    
    if ($venta_id2) {
        $venta2 = Venta::obtenerPorId($venta_id2);
        echo '<div class="alert test-success">✅ ÉXITO: Venta con múltiples pagos creada</div>';
        echo '<div class="alert test-info">Formas de pago: ' . count($venta2['formas_pago']) . '<br>';
        echo 'Efectivo: Q100.00<br>Tarjeta: Q50.00</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se creó la venta</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 3: Venta con descuento
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 3: Venta con Descuento (Monto Fijo)</h5></div><div class="card-body">';
try {
    $datos_venta_descuento = [
        'sucursal_id' => 1,
        'vendedor_id' => 1,
        'cliente_id' => null,
        'productos' => [
            [
                'producto_id' => 1,
                'cantidad' => 2,
                'precio_unitario' => 50.00, // Precio manual - Total = 100
                'tipo_precio' => 'publico'
            ]
        ],
        'formas_pago' => [
            [
                'forma_pago' => 'efectivo',
                'monto' => 90.00 // Total después del descuento (100 - 10)
            ]
        ],
        'descuento' => 10.00, // Descuento de Q10
        'tipo_venta' => 'normal'
    ];
    
    $venta_id3 = Venta::crear($datos_venta_descuento);
    
    if ($venta_id3) {
        $venta3 = Venta::obtenerPorId($venta_id3);
        echo '<div class="alert test-success">✅ ÉXITO: Venta con descuento creada</div>';
        echo '<div class="alert test-info">Subtotal: ' . formato_dinero($venta3['subtotal']) . '<br>';
        echo 'Descuento: ' . formato_dinero($venta3['descuento']) . '<br>';
        echo 'Total: ' . formato_dinero($venta3['total']) . '</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se creó la venta con descuento</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 4: Validar formas de pago (debe fallar si no suma total)
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 4: Validar Formas de Pago (Debe Rechazar si no Suma)</h5></div><div class="card-body">';
try {
    $formas_pago_incorrectas = [
        ['forma_pago' => 'efectivo', 'monto' => 50.00]
    ];
    
    $validacion = Venta::validarFormasPago($formas_pago_incorrectas, 100.00);
    
    if (!$validacion['valido']) {
        echo '<div class="alert test-success">✅ ÉXITO: Validación correcta - Formas de pago no suman el total</div>';
        echo '<div class="alert test-info">Error detectado: ' . $validacion['error'] . '</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: Se permitió una venta con formas de pago incorrectas</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 5: Validar stock insuficiente (debe fallar)
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 5: Validar Stock Insuficiente (Debe Rechazar)</h5></div><div class="card-body">';
try {
    $datos_sin_stock = [
        'sucursal_id' => 1,
        'vendedor_id' => 1,
        'productos' => [
            [
                'producto_id' => 1,
                'cantidad' => 999999, // Cantidad imposible
                'precio_unitario' => 50.00 // Precio manual
            ]
        ],
        'formas_pago' => [
            ['forma_pago' => 'efectivo', 'monto' => 100.00]
        ],
        'tipo_venta' => 'normal'
    ];
    
    $venta_sin_stock = Venta::crear($datos_sin_stock);
    
    if (!$venta_sin_stock) {
        echo '<div class="alert test-success">✅ ÉXITO: Venta correctamente rechazada por falta de stock</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: Se permitió venta sin stock suficiente</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-success">✅ ÉXITO: Excepción capturada correctamente - ' . $e->getMessage() . '</div>';
    $tests_passed++;
}
echo '</div></div>';

// TEST 6: Verificar actualización de inventario
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 6: Verificar Actualización de Inventario</h5></div><div class="card-body">';
try {
    if (isset($venta_id)) {
        // Obtener detalles de la venta
        $venta = Venta::obtenerPorId($venta_id);
        
        // Verificar que existan movimientos de inventario
        $movimientos = db_query(
            "SELECT * FROM movimientos_inventario 
             WHERE referencia_tipo = 'venta' AND referencia_id = ?",
            [$venta_id]
        );
        
        if (count($movimientos) > 0) {
            echo '<div class="alert test-success">✅ ÉXITO: Inventario actualizado correctamente</div>';
            echo '<div class="alert test-info">Movimientos registrados: ' . count($movimientos) . '</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se registraron movimientos de inventario</div>';
            $tests_failed++;
        }
    } else {
        echo '<div class="alert test-error">❌ ERROR: No hay venta_id del TEST 1</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 7: Verificar movimientos de caja
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 7: Verificar Movimientos de Caja</h5></div><div class="card-body">';
try {
    if (isset($venta_id)) {
        $movimientos_caja = db_query(
            "SELECT * FROM movimientos_caja 
             WHERE referencia_tipo = 'venta' AND referencia_id = ?",
            [$venta_id]
        );
        
        if (count($movimientos_caja) > 0) {
            echo '<div class="alert test-success">✅ ÉXITO: Movimientos de caja registrados</div>';
            echo '<div class="alert test-info">Total movimientos: ' . count($movimientos_caja) . '</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se registraron movimientos de caja</div>';
            $tests_failed++;
        }
    } else {
        echo '<div class="alert test-error">❌ ERROR: No hay venta_id del TEST 1</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 8: Número de venta único por sucursal
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 8: Número de Venta Único y Consecutivo</h5></div><div class="card-body">';
try {
    if (isset($venta_id) && isset($venta_id2)) {
        $venta1 = Venta::obtenerPorId($venta_id);
        $venta2 = Venta::obtenerPorId($venta_id2);
        
        if ($venta1['numero_venta'] !== $venta2['numero_venta']) {
            echo '<div class="alert test-success">✅ ÉXITO: Números de venta únicos</div>';
            echo '<div class="alert test-info">Venta 1: ' . $venta1['numero_venta'] . '<br>';
            echo 'Venta 2: ' . $venta2['numero_venta'] . '</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: Números de venta duplicados</div>';
            $tests_failed++;
        }
    } else {
        echo '<div class="alert test-error">❌ ERROR: Faltan IDs de ventas anteriores</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 9: Anular venta (debe devolver stock)
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 9: Anular Venta (Reversión de Inventario)</h5></div><div class="card-body">';
try {
    if (isset($venta_id)) {
        // Obtener stock antes de anular
        $venta = Venta::obtenerPorId($venta_id);
        $detalle = $venta['detalles'][0];
        $stock_antes = obtener_stock_disponible($detalle['producto_id'], $venta['sucursal_id']);
        
        // Anular venta
        $resultado = Venta::anular($venta_id, 'Prueba de anulación');
        
        if ($resultado) {
            // Verificar que el stock se haya restaurado
            $stock_despues = obtener_stock_disponible($detalle['producto_id'], $venta['sucursal_id']);
            
            if ($stock_despues > $stock_antes) {
                echo '<div class="alert test-success">✅ ÉXITO: Venta anulada y stock restaurado</div>';
                echo '<div class="alert test-info">Stock antes: ' . $stock_antes . '<br>';
                echo 'Stock después: ' . $stock_despues . '<br>';
                echo 'Incremento: ' . ($stock_despues - $stock_antes) . '</div>';
                $tests_passed++;
            } else {
                echo '<div class="alert test-error">❌ ERROR: Stock no se restauró correctamente</div>';
                $tests_failed++;
            }
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se pudo anular la venta</div>';
            $tests_failed++;
        }
    } else {
        echo '<div class="alert test-error">❌ ERROR: No hay venta_id del TEST 1</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 10: Obtener ventas del día
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 10: Obtener Ventas del Día</h5></div><div class="card-body">';
try {
    $ventas_hoy = Venta::obtenerVentasDelDia(1);
    
    if (is_array($ventas_hoy)) {
        echo '<div class="alert test-success">✅ ÉXITO: Ventas del día obtenidas</div>';
        echo '<div class="alert test-info">Total ventas hoy: ' . count($ventas_hoy) . '</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se obtuvieron ventas del día</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 11: Obtener detalles completos de venta
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 11: Obtener Detalles Completos de Venta</h5></div><div class="card-body">';
try {
    if (isset($venta_id2)) {
        $venta_completa = Venta::obtenerPorId($venta_id2);
        
        if ($venta_completa && 
            isset($venta_completa['detalles']) && 
            isset($venta_completa['formas_pago'])) {
            echo '<div class="alert test-success">✅ ÉXITO: Venta completa con detalles y formas de pago</div>';
            echo '<div class="alert test-info">Productos: ' . count($venta_completa['detalles']) . '<br>';
            echo 'Formas de pago: ' . count($venta_completa['formas_pago']) . '</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: Falta información en la venta</div>';
            $tests_failed++;
        }
    } else {
        echo '<div class="alert test-error">❌ ERROR: No hay venta_id2 del TEST 2</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 12: Estadísticas de ventas
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 12: Obtener Estadísticas de Ventas</h5></div><div class="card-body">';
try {
    $stats = Venta::obtenerEstadisticas([
        'fecha_inicio' => date('Y-m-d'),
        'fecha_fin' => date('Y-m-d')
    ]);
    
    if (is_array($stats) && isset($stats['total_ventas'])) {
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