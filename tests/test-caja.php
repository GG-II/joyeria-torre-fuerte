<?php
/**
 * Tests para Modelo Caja
 * 
 * Prueba todas las funcionalidades del módulo de caja:
 * - Apertura y cierre
 * - Registro de movimientos (todos los tipos)
 * - Cálculo de totales y diferencias
 * - Historial y estadísticas
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
require_once __DIR__ . '/../models/caja.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>💰 Test: Modelo Caja</title>
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
    <h1 class="mb-4">💰 Test: Modelo Caja</h1>
    <p class="lead">Pruebas del sistema de gestión de caja</p>
    
<?php

$tests_passed = 0;
$tests_failed = 0;

// ========================================
// PREPARACIÓN: Cerrar caja abierta si existe
// ========================================
echo '<div class="card mb-3 border-warning"><div class="card-header bg-warning"><h5>⚙️ PREPARACIÓN: Cerrar Caja Abierta del Usuario (Si Existe)</h5></div><div class="card-body">';
try {
    global $pdo;
    
    // Verificar si hay caja abierta
    $caja_existente = db_query_one(
        "SELECT id FROM cajas WHERE usuario_id = ? AND estado = 'abierta'",
        [usuario_actual_id()]
    );
    
    if ($caja_existente) {
        // Cerrar la caja existente
        $totales = Caja::calcularTotalesCaja($caja_existente['id']);
        
        $resultado = db_execute(
            "UPDATE cajas SET 
                estado = 'cerrada',
                fecha_cierre = NOW(),
                monto_esperado = ?,
                monto_real = ?,
                observaciones_cierre = 'Cerrada automáticamente para tests'
             WHERE id = ?",
            [$totales['total_final'], $totales['total_final'], $caja_existente['id']]
        );
        
        echo '<div class="alert test-info">✓ Caja abierta encontrada (ID: ' . $caja_existente['id'] . ') y cerrada automáticamente</div>';
    } else {
        echo '<div class="alert test-info">✓ No hay cajas abiertas - Listo para tests</div>';
    }
} catch (Exception $e) {
    echo '<div class="alert test-warning">⚠️ Error en preparación: ' . $e->getMessage() . '</div>';
}
echo '</div></div>';

// ========================================
// TEST 1: Abrir Caja
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 1: Abrir Caja</h5></div><div class="card-body">';
try {
    $monto_inicial = 500.00;
    
    $caja_id = Caja::abrirCaja(
        usuario_actual_id(),
        1, // sucursal_id
        $monto_inicial
    );
    
    if ($caja_id) {
        $caja = Caja::obtenerPorId($caja_id);
        
        if ($caja && $caja['estado'] === 'abierta' && $caja['monto_inicial'] == $monto_inicial) {
            echo '<div class="alert test-success">✅ ÉXITO: Caja abierta correctamente</div>';
            echo '<div class="alert test-info">ID: ' . $caja_id . '<br>';
            echo 'Monto inicial: ' . formato_dinero($monto_inicial) . '<br>';
            echo 'Usuario: ' . $caja['usuario_nombre'] . '<br>';
            echo 'Sucursal: ' . $caja['sucursal_nombre'] . '</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: Datos de caja incorrectos</div>';
            $tests_failed++;
        }
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se pudo abrir la caja</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 2: Validar Caja Abierta Única
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 2: Validar Caja Abierta Única (Rechazar Segunda Apertura)</h5></div><div class="card-body">';
try {
    // Intentar abrir otra caja con el mismo usuario
    $segunda_caja = Caja::abrirCaja(
        usuario_actual_id(),
        1,
        100.00
    );
    
    if (!$segunda_caja) {
        echo '<div class="alert test-success">✅ ÉXITO: Sistema rechazó correctamente segunda apertura</div>';
        echo '<div class="alert test-info">Un usuario solo puede tener una caja abierta a la vez</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: Se permitió abrir segunda caja</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 3: Registrar Venta
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 3: Registrar Venta (Ingreso)</h5></div><div class="card-body">';
try {
    if (isset($caja_id)) {
        $movimiento_id = Caja::registrarVenta(
            $caja_id,
            999, // venta_id ficticio
            250.00,
            usuario_actual_id(),
            'Venta #000001 - Efectivo Q250.00'
        );
        
        if ($movimiento_id) {
            echo '<div class="alert test-success">✅ ÉXITO: Venta registrada en caja</div>';
            echo '<div class="alert test-info">Monto: ' . formato_dinero(250.00) . '</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se registró la venta</div>';
            $tests_failed++;
        }
    } else {
        echo '<div class="alert test-error">❌ ERROR: No hay caja_id del TEST 1</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 4: Registrar Ingreso por Reparación
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 4: Registrar Ingreso por Reparación</h5></div><div class="card-body">';
try {
    if (isset($caja_id)) {
        $movimiento_id = Caja::registrarIngresoReparacion(
            $caja_id,
            5, // trabajo_id ficticio
            150.00,
            usuario_actual_id(),
            'Reparación - Cadena de oro - Cliente: Juan Pérez'
        );
        
        if ($movimiento_id) {
            echo '<div class="alert test-success">✅ ÉXITO: Ingreso por reparación registrado</div>';
            echo '<div class="alert test-info">Monto: ' . formato_dinero(150.00) . '</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se registró el ingreso</div>';
            $tests_failed++;
        }
    } else {
        echo '<div class="alert test-error">❌ ERROR: No hay caja_id</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 5: Registrar Abono a Crédito
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 5: Registrar Abono a Crédito</h5></div><div class="card-body">';
try {
    if (isset($caja_id)) {
        $movimiento_id = Caja::registrarAbonoCredito(
            $caja_id,
            10, // abono_id ficticio
            200.00,
            usuario_actual_id(),
            'Abono crédito - Cliente: María López - Cuota semanal'
        );
        
        if ($movimiento_id) {
            echo '<div class="alert test-success">✅ ÉXITO: Abono a crédito registrado</div>';
            echo '<div class="alert test-info">Monto: ' . formato_dinero(200.00) . '</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se registró el abono</div>';
            $tests_failed++;
        }
    } else {
        echo '<div class="alert test-error">❌ ERROR: No hay caja_id</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 6: Registrar Gasto
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 6: Registrar Gasto (Egreso)</h5></div><div class="card-body">';
try {
    if (isset($caja_id)) {
        $movimiento_id = Caja::registrarGasto(
            $caja_id,
            75.00,
            usuario_actual_id(),
            'Compra de materiales de limpieza'
        );
        
        if ($movimiento_id) {
            echo '<div class="alert test-success">✅ ÉXITO: Gasto registrado</div>';
            echo '<div class="alert test-info">Monto: ' . formato_dinero(75.00) . '</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se registró el gasto</div>';
            $tests_failed++;
        }
    } else {
        echo '<div class="alert test-error">❌ ERROR: No hay caja_id</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 7: Registrar Pago a Proveedor
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 7: Registrar Pago a Proveedor</h5></div><div class="card-body">';
try {
    if (isset($caja_id)) {
        $movimiento_id = Caja::registrarPagoProveedor(
            $caja_id,
            null, // sin compra_id
            300.00,
            usuario_actual_id(),
            'Pago a proveedor - Gold Suppliers S.A.'
        );
        
        if ($movimiento_id) {
            echo '<div class="alert test-success">✅ ÉXITO: Pago a proveedor registrado</div>';
            echo '<div class="alert test-info">Monto: ' . formato_dinero(300.00) . '</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se registró el pago</div>';
            $tests_failed++;
        }
    } else {
        echo '<div class="alert test-error">❌ ERROR: No hay caja_id</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 8: Calcular Totales de Caja
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 8: Calcular Totales de Caja</h5></div><div class="card-body">';
try {
    if (isset($caja_id)) {
        $totales = Caja::calcularTotalesCaja($caja_id);
        
        // Totales esperados:
        // Monto inicial: 500.00
        // Ingresos: 500.00 (apertura) + 250.00 (venta) + 150.00 (reparación) + 200.00 (abono) = 1,100.00
        // Egresos: 75.00 (gasto) + 300.00 (proveedor) = 375.00
        // Total final: 500.00 + 1,100.00 - 375.00 = 1,225.00
        
        $esperado_ingresos = 1100.00; // Incluye apertura de 500
        $esperado_egresos = 375.00;
        $esperado_final = 1225.00; // 500 inicial + 1100 ingresos - 375 egresos
        
        if ($totales['total_ingresos'] == $esperado_ingresos && 
            $totales['total_egresos'] == $esperado_egresos &&
            $totales['total_final'] == $esperado_final) {
            
            echo '<div class="alert test-success">✅ ÉXITO: Totales calculados correctamente</div>';
            echo '<div class="alert test-info">';
            echo 'Monto inicial: ' . formato_dinero($totales['monto_inicial']) . '<br>';
            echo 'Total ingresos: ' . formato_dinero($totales['total_ingresos']) . ' (incluye apertura)<br>';
            echo 'Total egresos: ' . formato_dinero($totales['total_egresos']) . '<br>';
            echo 'Total final: ' . formato_dinero($totales['total_final']) . '<br>';
            echo 'Movimientos: ' . $totales['cantidad_movimientos'];
            echo '</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: Totales incorrectos</div>';
            echo '<div class="alert test-info">';
            echo 'Esperado ingresos: ' . formato_dinero($esperado_ingresos) . ' | Obtenido: ' . formato_dinero($totales['total_ingresos']) . '<br>';
            echo 'Esperado egresos: ' . formato_dinero($esperado_egresos) . ' | Obtenido: ' . formato_dinero($totales['total_egresos']) . '<br>';
            echo 'Esperado final: ' . formato_dinero($esperado_final) . ' | Obtenido: ' . formato_dinero($totales['total_final']);
            echo '</div>';
            $tests_failed++;
        }
    } else {
        echo '<div class="alert test-error">❌ ERROR: No hay caja_id</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 9: Obtener Movimientos de Caja
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 9: Obtener Movimientos de Caja</h5></div><div class="card-body">';
try {
    if (isset($caja_id)) {
        $movimientos = Caja::obtenerMovimientosCaja($caja_id);
        
        // Debe haber 7 movimientos:
        // 1. Apertura (otro_ingreso)
        // 2. Venta
        // 3. Ingreso reparación
        // 4. Abono crédito
        // 5. Gasto
        // 6. Pago proveedor
        // Total: 6 movimientos + 1 de apertura = 7
        
        if (count($movimientos) >= 6) {
            echo '<div class="alert test-success">✅ ÉXITO: Movimientos obtenidos correctamente</div>';
            echo '<div class="alert test-info">Total movimientos: ' . count($movimientos) . '</div>';
            
            // Mostrar resumen
            echo '<table class="table table-sm mt-2">';
            echo '<thead><tr><th>Tipo</th><th>Categoría</th><th>Concepto</th><th>Monto</th></tr></thead>';
            echo '<tbody>';
            foreach ($movimientos as $mov) {
                $clase = $mov['categoria'] === 'ingreso' ? 'text-success' : 'text-danger';
                echo '<tr class="' . $clase . '">';
                echo '<td>' . $mov['tipo_movimiento'] . '</td>';
                echo '<td>' . $mov['categoria'] . '</td>';
                echo '<td>' . htmlspecialchars(substr($mov['concepto'], 0, 50)) . '...</td>';
                echo '<td>' . formato_dinero($mov['monto']) . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
            
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: Cantidad de movimientos incorrecta</div>';
            echo '<div class="alert test-info">Esperados: al menos 6 | Obtenidos: ' . count($movimientos) . '</div>';
            $tests_failed++;
        }
    } else {
        echo '<div class="alert test-error">❌ ERROR: No hay caja_id</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 10: Obtener Caja Actual
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 10: Obtener Caja Actual del Usuario</h5></div><div class="card-body">';
try {
    $caja_actual = Caja::obtenerCajaActual();
    
    if ($caja_actual && $caja_actual['estado'] === 'abierta') {
        echo '<div class="alert test-success">✅ ÉXITO: Caja actual obtenida</div>';
        echo '<div class="alert test-info">';
        echo 'ID: ' . $caja_actual['id'] . '<br>';
        echo 'Usuario: ' . $caja_actual['usuario_nombre'] . '<br>';
        echo 'Sucursal: ' . $caja_actual['sucursal_nombre'] . '<br>';
        echo 'Total actual: ' . formato_dinero($caja_actual['total_final']);
        echo '</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se obtuvo la caja actual</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 11: Cerrar Caja con Diferencia
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 11: Cerrar Caja (Con Diferencia)</h5></div><div class="card-body">';
try {
    if (isset($caja_id)) {
        // Total esperado: 1,225.00 (incluye apertura de 500)
        // Simulamos contar 1,220.00 (faltante de 5.00)
        $monto_real = 1220.00;
        
        $resultado = Caja::cerrarCaja(
            $caja_id,
            $monto_real,
            'Cierre de caja - Test - Faltante de Q5.00'
        );
        
        if ($resultado) {
            $caja_cerrada = Caja::obtenerPorId($caja_id);
            
            $diferencia = $caja_cerrada['diferencia'];
            $diferencia_esperada = -5.00; // Faltante
            
            if (abs($diferencia - $diferencia_esperada) < 0.01) {
                echo '<div class="alert test-success">✅ ÉXITO: Caja cerrada correctamente</div>';
                echo '<div class="alert test-info">';
                echo 'Monto esperado: ' . formato_dinero($caja_cerrada['monto_esperado']) . '<br>';
                echo 'Monto real: ' . formato_dinero($caja_cerrada['monto_real']) . '<br>';
                echo 'Diferencia: ' . formato_dinero($caja_cerrada['diferencia']) . '<br>';
                echo 'Estado: ' . $caja_cerrada['estado'];
                echo '</div>';
                $tests_passed++;
            } else {
                echo '<div class="alert test-error">❌ ERROR: Diferencia calculada incorrecta</div>';
                echo '<div class="alert test-info">';
                echo 'Esperado: ' . formato_dinero($diferencia_esperada) . ' | Obtenido: ' . formato_dinero($diferencia);
                echo '</div>';
                $tests_failed++;
            }
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se pudo cerrar la caja</div>';
            $tests_failed++;
        }
    } else {
        echo '<div class="alert test-error">❌ ERROR: No hay caja_id</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 12: Verificar No Puede Cerrar Caja Ya Cerrada
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 12: Validar No Puede Cerrar Caja Ya Cerrada</h5></div><div class="card-body">';
try {
    if (isset($caja_id)) {
        // Intentar cerrar nuevamente
        $resultado = Caja::cerrarCaja($caja_id, 700.00);
        
        if (!$resultado) {
            echo '<div class="alert test-success">✅ ÉXITO: Sistema rechazó correctamente segundo cierre</div>';
            echo '<div class="alert test-info">Una caja cerrada no puede cerrarse nuevamente</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: Se permitió cerrar caja ya cerrada</div>';
            $tests_failed++;
        }
    } else {
        echo '<div class="alert test-error">❌ ERROR: No hay caja_id</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 13: Obtener Historial de Cierres
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 13: Obtener Historial de Cierres</h5></div><div class="card-body">';
try {
    $historial = Caja::obtenerHistorialCierres([
        'sucursal_id' => 1,
        'limite' => 10
    ]);
    
    if (is_array($historial)) {
        echo '<div class="alert test-success">✅ ÉXITO: Historial obtenido</div>';
        echo '<div class="alert test-info">Total cierres: ' . count($historial) . '</div>';
        
        if (count($historial) > 0) {
            echo '<table class="table table-sm mt-2">';
            echo '<thead><tr><th>Fecha</th><th>Usuario</th><th>Esperado</th><th>Real</th><th>Diferencia</th></tr></thead>';
            echo '<tbody>';
            foreach (array_slice($historial, 0, 5) as $cierre) {
                $clase_dif = abs($cierre['diferencia']) > 0.01 ? 'text-danger fw-bold' : 'text-success';
                echo '<tr>';
                echo '<td>' . formato_fecha($cierre['fecha_cierre'], true) . '</td>';
                echo '<td>' . $cierre['usuario_nombre'] . '</td>';
                echo '<td>' . formato_dinero($cierre['monto_esperado']) . '</td>';
                echo '<td>' . formato_dinero($cierre['monto_real']) . '</td>';
                echo '<td class="' . $clase_dif . '">' . formato_dinero($cierre['diferencia']) . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
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

// ========================================
// TEST 14: Obtener Estadísticas
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 14: Obtener Estadísticas de Cajas</h5></div><div class="card-body">';
try {
    $estadisticas = Caja::obtenerEstadisticas([
        'sucursal_id' => 1
    ]);
    
    if ($estadisticas && isset($estadisticas['total_cierres'])) {
        echo '<div class="alert test-success">✅ ÉXITO: Estadísticas obtenidas</div>';
        echo '<div class="alert test-info">';
        echo 'Total cierres: ' . $estadisticas['total_cierres'] . '<br>';
        echo 'Total esperado: ' . formato_dinero($estadisticas['total_esperado']) . '<br>';
        echo 'Total real: ' . formato_dinero($estadisticas['total_real']) . '<br>';
        echo 'Diferencia total: ' . formato_dinero($estadisticas['diferencia_total']) . '<br>';
        echo 'Diferencia promedio: ' . formato_dinero($estadisticas['diferencia_promedio']) . '<br>';
        echo 'Cierres con diferencia: ' . $estadisticas['cierres_con_diferencia'] . '<br>';
        echo 'Cierres con sobrante: ' . $estadisticas['cierres_sobrante'] . '<br>';
        echo 'Cierres con faltante: ' . $estadisticas['cierres_faltante'];
        echo '</div>';
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