<?php
// ================================================
// TEST: MODELO CREDITO
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
require_once __DIR__ . '/../models/venta.php';
require_once __DIR__ . '/../models/credito.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Test - Modelo Crédito</title>
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
        <h1 class="mb-4">💳 Test: Modelo Crédito</h1>
        <hr>

<?php

$tests_passed = 0;
$tests_failed = 0;

// PREPARACIÓN: Crear cliente con límite de crédito
echo '<div class="card mb-3 border-warning"><div class="card-header bg-warning"><h5>⚙️ PREPARACIÓN: Crear Cliente con Crédito</h5></div><div class="card-body">';
try {
    $datos_cliente = [
        'nombre' => 'María López - Cliente Crédito Test',
        'nit' => 'NIT-CREDITO-' . time(), // NIT único
        'telefono' => '55557890',
        'tipo_cliente' => 'publico',
        'tipo_mercaderias' => 'ambas',
        'limite_credito' => 10000.00,
        'activo' => 1
    ];
    
    $cliente_credito_id = Cliente::crear($datos_cliente);
    
    if ($cliente_credito_id) {
        $cliente_creado = Cliente::obtenerPorId($cliente_credito_id);
        echo '<div class="alert test-info">✓ Cliente creado - ID: ' . $cliente_credito_id . '<br>';
        echo 'Nombre: ' . $cliente_creado['nombre'] . '<br>';
        echo 'Límite de crédito: ' . formato_dinero($cliente_creado['limite_credito']) . '</div>';
    } else {
        echo '<div class="alert test-warning">⚠️ No se pudo crear cliente - Los tests de crédito fallarán</div>';
    }
} catch (Exception $e) {
    echo '<div class="alert test-warning">⚠️ Error: ' . $e->getMessage() . '</div>';
}
echo '</div></div>';

// PREPARACIÓN: Crear venta a crédito
echo '<div class="card mb-3 border-warning"><div class="card-header bg-warning"><h5>⚙️ PREPARACIÓN: Crear Venta a Crédito</h5></div><div class="card-body">';
try {
    if (isset($cliente_credito_id)) {
        $datos_venta_credito = [
            'sucursal_id' => 1,
            'vendedor_id' => 1,
            'cliente_id' => $cliente_credito_id,
            'productos' => [
                [
                    'producto_id' => 1,
                    'cantidad' => 1,
                    'precio_unitario' => 1000.00, // Precio manual para crédito
                    'tipo_precio' => 'publico'
                ]
            ],
            'formas_pago' => [], // No lleva formas de pago porque es crédito
            'descuento' => 0,
            'tipo_venta' => 'credito'
        ];
        
        $venta_credito_id = Venta::crear($datos_venta_credito);
        
        if ($venta_credito_id) {
            $venta = Venta::obtenerPorId($venta_credito_id);
            echo '<div class="alert test-info">✓ Venta a crédito creada - ID: ' . $venta_credito_id . '<br>';
            echo 'Número: ' . $venta['numero_venta'] . '<br>';
            echo 'Total: ' . formato_dinero($venta['total']) . '<br>';
            echo 'Tipo: ' . $venta['tipo_venta'] . '</div>';
        } else {
            echo '<div class="alert test-warning">⚠️ No se pudo crear venta a crédito - Los tests de crédito fallarán</div>';
        }
    } else {
        echo '<div class="alert test-warning">⚠️ No hay cliente_credito_id</div>';
    }
} catch (Exception $e) {
    echo '<div class="alert test-warning">⚠️ Error: ' . $e->getMessage() . '</div>';
}
echo '</div></div>';

// TEST 1: Crear crédito
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 1: Crear Crédito Semanal</h5></div><div class="card-body">';
try {
    if (isset($cliente_credito_id) && isset($venta_credito_id)) {
        $venta = Venta::obtenerPorId($venta_credito_id);
        
        $datos_credito = [
            'cliente_id' => $cliente_credito_id,
            'venta_id' => $venta_credito_id,
            'monto_total' => $venta['total'],
            'numero_cuotas' => 4, // 4 cuotas semanales
            'fecha_inicio' => date('Y-m-d')
        ];
        
        $credito_id = Credito::crear($datos_credito);
        
        if ($credito_id) {
            $credito = Credito::obtenerPorId($credito_id);
            echo '<div class="alert test-success">✅ ÉXITO: Crédito creado - ID: ' . $credito_id . '</div>';
            echo '<div class="alert test-info">Monto: ' . formato_dinero($credito['monto_total']) . '<br>';
            echo 'Cuota semanal: ' . formato_dinero($credito['cuota_semanal']) . '<br>';
            echo 'Próximo pago: ' . formato_fecha($credito['fecha_proximo_pago']) . '</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se creó el crédito</div>';
            $tests_failed++;
        }
    } else {
        echo '<div class="alert test-error">❌ ERROR: Faltan datos de preparación</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 2: Registrar abono parcial
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 2: Registrar Abono Parcial</h5></div><div class="card-body">';
try {
    if (isset($credito_id)) {
        $credito_antes = Credito::obtenerPorId($credito_id);
        
        $datos_abono = [
            'monto' => $credito_antes['cuota_semanal'],
            'forma_pago' => 'efectivo',
            'fecha_abono' => date('Y-m-d'),
            'observaciones' => 'Primera cuota - Test'
        ];
        
        $abono_id = Credito::registrarAbono($credito_id, $datos_abono);
        
        if ($abono_id) {
            $credito_despues = Credito::obtenerPorId($credito_id);
            
            echo '<div class="alert test-success">✅ ÉXITO: Abono registrado - ID: ' . $abono_id . '</div>';
            echo '<div class="alert test-info">Saldo anterior: ' . formato_dinero($credito_antes['saldo_pendiente']) . '<br>';
            echo 'Abono: ' . formato_dinero($datos_abono['monto']) . '<br>';
            echo 'Saldo nuevo: ' . formato_dinero($credito_despues['saldo_pendiente']) . '<br>';
            echo 'Cuotas pagadas: ' . $credito_despues['cuotas_pagadas'] . '/' . $credito_despues['numero_cuotas'] . '</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se registró el abono</div>';
            $tests_failed++;
        }
    } else {
        echo '<div class="alert test-error">❌ ERROR: No hay credito_id del TEST 1</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 3: Verificar historial inmutable de abonos
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 3: Verificar Historial Inmutable (Snapshots)</h5></div><div class="card-body">';
try {
    if (isset($credito_id)) {
        $abonos = Credito::obtenerAbonos($credito_id);
        
        if (count($abonos) > 0) {
            $abono = $abonos[0];
            
            if (isset($abono['saldo_anterior']) && isset($abono['saldo_nuevo'])) {
                echo '<div class="alert test-success">✅ ÉXITO: Historial inmutable verificado</div>';
                echo '<div class="alert test-info">Abono tiene snapshots:<br>';
                echo 'Saldo anterior: ' . formato_dinero($abono['saldo_anterior']) . '<br>';
                echo 'Saldo nuevo: ' . formato_dinero($abono['saldo_nuevo']) . '</div>';
                $tests_passed++;
            } else {
                echo '<div class="alert test-error">❌ ERROR: Faltan snapshots en el abono</div>';
                $tests_failed++;
            }
        } else {
            echo '<div class="alert test-error">❌ ERROR: No hay abonos registrados</div>';
            $tests_failed++;
        }
    } else {
        echo '<div class="alert test-error">❌ ERROR: No hay credito_id del TEST 1</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 4: Registrar múltiples abonos hasta liquidar
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 4: Liquidar Crédito con Múltiples Abonos</h5></div><div class="card-body">';
try {
    if (isset($credito_id)) {
        $credito = Credito::obtenerPorId($credito_id);
        $cuotas_restantes = $credito['numero_cuotas'] - $credito['cuotas_pagadas'];
        
        // Abonar las cuotas restantes
        for ($i = 0; $i < $cuotas_restantes; $i++) {
            $datos_abono = [
                'monto' => $credito['cuota_semanal'],
                'forma_pago' => 'efectivo',
                'fecha_abono' => date('Y-m-d'),
                'observaciones' => 'Cuota ' . ($credito['cuotas_pagadas'] + $i + 1)
            ];
            
            Credito::registrarAbono($credito_id, $datos_abono);
        }
        
        // Verificar que esté liquidado
        $credito_final = Credito::obtenerPorId($credito_id);
        
        if ($credito_final['estado'] === 'liquidado' && $credito_final['saldo_pendiente'] == 0) {
            echo '<div class="alert test-success">✅ ÉXITO: Crédito liquidado correctamente</div>';
            echo '<div class="alert test-info">Estado: ' . $credito_final['estado'] . '<br>';
            echo 'Saldo: ' . formato_dinero($credito_final['saldo_pendiente']) . '<br>';
            echo 'Cuotas pagadas: ' . $credito_final['cuotas_pagadas'] . '/' . $credito_final['numero_cuotas'] . '<br>';
            echo 'Fecha liquidación: ' . formato_fecha($credito_final['fecha_liquidacion']) . '</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: El crédito no se liquidó correctamente</div>';
            $tests_failed++;
        }
    } else {
        echo '<div class="alert test-error">❌ ERROR: No hay credito_id del TEST 1</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 5: Obtener créditos por cliente
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 5: Obtener Créditos por Cliente</h5></div><div class="card-body">';
try {
    if (isset($cliente_credito_id)) {
        $creditos_cliente = Credito::obtenerPorCliente($cliente_credito_id);
        
        if (is_array($creditos_cliente) && count($creditos_cliente) > 0) {
            echo '<div class="alert test-success">✅ ÉXITO: Créditos del cliente obtenidos</div>';
            echo '<div class="alert test-info">Total créditos: ' . count($creditos_cliente) . '</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se obtuvieron créditos del cliente</div>';
            $tests_failed++;
        }
    } else {
        echo '<div class="alert test-error">❌ ERROR: No hay cliente_credito_id</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 6: Crear crédito para pruebas de alertas (vencido)
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 6: Crear Crédito Vencido (Para Alertas)</h5></div><div class="card-body">';
try {
    // Crear otro cliente para esta venta
    $datos_cliente_vencido = [
        'nombre' => 'Cliente Vencido Test',
        'nit' => 'NIT-VENCIDO-' . time(), // NIT único
        'telefono' => '55558888',
        'tipo_cliente' => 'publico',
        'tipo_mercaderias' => 'ambas',
        'limite_credito' => 5000.00,
        'activo' => 1
    ];
    
    $cliente_vencido_id = Cliente::crear($datos_cliente_vencido);
    
    if ($cliente_vencido_id) {
        // Crear venta y crédito para pruebas
        $datos_venta_vencida = [
        'sucursal_id' => 1,
        'vendedor_id' => 1,
        'cliente_id' => $cliente_vencido_id, // Usar el cliente vencido creado
        'productos' => [
            [
                'producto_id' => 1, 
                'cantidad' => 1, 
                'precio_unitario' => 500.00, // Precio manual
                'tipo_precio' => 'publico'
            ]
        ],
        'formas_pago' => [],
        'tipo_venta' => 'credito'
    ];
    
    $venta_vencida_id = Venta::crear($datos_venta_vencida);
    
    if ($venta_vencida_id) {
        $venta_v = Venta::obtenerPorId($venta_vencida_id);
        
        // Crear crédito con fecha de inicio en el pasado
        $fecha_pasado = date('Y-m-d', strtotime('-15 days'));
        
        $datos_credito_vencido = [
            'cliente_id' => $cliente_vencido_id, // Usar el cliente vencido creado
            'venta_id' => $venta_vencida_id,
            'monto_total' => $venta_v['total'],
            'numero_cuotas' => 2,
            'fecha_inicio' => $fecha_pasado
        ];
        
        $credito_vencido_id = Credito::crear($datos_credito_vencido);
        
        if ($credito_vencido_id) {
            // Recalcular estado para marcar como vencido
            Credito::recalcularEstado($credito_vencido_id);
            
            $credito_v = Credito::obtenerPorId($credito_vencido_id);
            
            echo '<div class="alert test-success">✅ ÉXITO: Crédito vencido creado para pruebas</div>';
            echo '<div class="alert test-info">Días de atraso: ' . $credito_v['dias_atraso'] . '</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se creó el crédito vencido</div>';
            $tests_failed++;
        }
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se creó la venta para crédito vencido</div>';
        $tests_failed++;
    }
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se pudo crear cliente para crédito vencido</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 7: Obtener créditos vencidos
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 7: Obtener Créditos Vencidos (Alertas)</h5></div><div class="card-body">';
try {
    $creditos_vencidos = Credito::obtenerVencidos(1);
    
    if (is_array($creditos_vencidos)) {
        echo '<div class="alert test-success">✅ ÉXITO: Créditos vencidos obtenidos</div>';
        echo '<div class="alert test-info">Total vencidos: ' . count($creditos_vencidos);
        
        if (count($creditos_vencidos) > 0) {
            echo '<br>Primer vencido: ' . $creditos_vencidos[0]['cliente_nombre'] . 
                 ' - Días atraso: ' . $creditos_vencidos[0]['dias_atraso'];
        }
        echo '</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se obtuvieron créditos vencidos</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 8: Calcular días de atraso
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 8: Calcular Días de Atraso</h5></div><div class="card-body">';
try {
    $fecha_vencida = date('Y-m-d', strtotime('-10 days'));
    $dias_atraso = Credito::calcularDiasAtraso($fecha_vencida);
    
    if ($dias_atraso >= 10) {
        echo '<div class="alert test-success">✅ ÉXITO: Días de atraso calculados correctamente</div>';
        echo '<div class="alert test-info">Fecha próximo pago: ' . formato_fecha($fecha_vencida) . '<br>';
        echo 'Días de atraso: ' . $dias_atraso . '</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: Cálculo de días de atraso incorrecto</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 9: Generar plan de pagos
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 9: Generar Plan de Pagos (Simulador)</h5></div><div class="card-body">';
try {
    $monto_simulacion = 1000.00;
    $cuotas_simulacion = 4;
    
    $plan = Credito::generarPlanPagos($monto_simulacion, $cuotas_simulacion);
    
    if (is_array($plan) && count($plan) === $cuotas_simulacion) {
        echo '<div class="alert test-success">✅ ÉXITO: Plan de pagos generado</div>';
        echo '<div class="alert test-info">Monto: ' . formato_dinero($monto_simulacion) . '<br>';
        echo 'Cuotas: ' . $cuotas_simulacion . '<br>';
        echo 'Cuota semanal: ' . formato_dinero($plan[0]['monto_cuota']) . '<br><br>';
        echo '<strong>Plan de pagos:</strong><pre>' . print_r($plan, true) . '</pre></div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: Plan de pagos incorrecto</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 10: Obtener estadísticas de créditos
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 10: Obtener Estadísticas de Créditos</h5></div><div class="card-body">';
try {
    $stats = Credito::obtenerEstadisticas();
    
    if (is_array($stats) && isset($stats['total_activos'])) {
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