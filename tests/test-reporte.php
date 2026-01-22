<?php
/**
 * Tests para Modelo Reporte
 * 
 * Prueba todos los reportes del sistema:
 * - Reportes de ventas
 * - Reportes de productos
 * - Reportes de inventario
 * - Reportes de taller
 * - Reportes financieros
 * - Reportes comparativos
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
require_once __DIR__ . '/../models/reporte.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📊 Test: Modelo Reporte</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f5f5f5; padding: 20px; }
        .test-success { background-color: #d4edda; border-color: #c3e6cb; color: #155724; }
        .test-error { background-color: #f8d7da; border-color: #f5c6cb; color: #721c24; }
        .test-info { background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; }
        .metric-card { border-left: 4px solid #007bff; }
    </style>
</head>
<body>

<div class="container">
    <h1 class="mb-4">📊 Test: Modelo Reporte</h1>
    <p class="lead">Pruebas del sistema de reportes y estadísticas</p>
    
<?php

$tests_passed = 0;
$tests_failed = 0;

// ========================================
// TEST 1: Reporte de Ventas Diarias
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 1: Reporte de Ventas Diarias</h5></div><div class="card-body">';
try {
    $fecha = date('Y-m-d');
    $reporte = Reporte::reporteVentasDiarias($fecha);
    
    if (isset($reporte['totales']) && is_array($reporte['ventas'])) {
        echo '<div class="alert test-success">✅ ÉXITO: Reporte de ventas diarias generado</div>';
        echo '<div class="alert test-info">';
        echo '<strong>Fecha:</strong> ' . formato_fecha($fecha) . '<br>';
        echo '<strong>Total ventas:</strong> ' . $reporte['totales']['total_ventas'] . '<br>';
        echo '<strong>Monto total:</strong> ' . formato_dinero($reporte['totales']['monto_total']) . '<br>';
        echo '<strong>Ticket promedio:</strong> ' . formato_dinero($reporte['totales']['ticket_promedio']) . '<br>';
        echo '<strong>Ventas contado:</strong> ' . $reporte['totales']['ventas_contado'] . '<br>';
        echo '<strong>Ventas crédito:</strong> ' . $reporte['totales']['ventas_credito'];
        echo '</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: Estructura de reporte incorrecta</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 2: Reporte de Ventas Mensuales
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 2: Reporte de Ventas Mensuales</h5></div><div class="card-body">';
try {
    $mes = date('n');
    $año = date('Y');
    $reporte = Reporte::reporteVentasMensuales($mes, $año);
    
    if (isset($reporte['totales']) && is_array($reporte['ventas_por_dia'])) {
        echo '<div class="alert test-success">✅ ÉXITO: Reporte de ventas mensuales generado</div>';
        echo '<div class="alert test-info">';
        echo '<strong>Periodo:</strong> ' . $mes . '/' . $año . '<br>';
        echo '<strong>Total ventas:</strong> ' . $reporte['totales']['total_ventas'] . '<br>';
        echo '<strong>Monto total:</strong> ' . formato_dinero($reporte['totales']['monto_total']) . '<br>';
        echo '<strong>Días con ventas:</strong> ' . count($reporte['ventas_por_dia']) . '<br>';
        echo '<strong>Productos top:</strong> ' . count($reporte['productos_top']);
        echo '</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: Estructura de reporte incorrecta</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 3: Reporte de Productos Más Vendidos
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 3: Reporte de Productos Más Vendidos</h5></div><div class="card-body">';
try {
    $fecha_inicio = date('Y-m-01'); // Primer día del mes
    $fecha_fin = date('Y-m-d');
    $reporte = Reporte::reporteProductosMasVendidos($fecha_inicio, $fecha_fin, 10);
    
    if (is_array($reporte['productos'])) {
        echo '<div class="alert test-success">✅ ÉXITO: Reporte de productos más vendidos generado</div>';
        echo '<div class="alert test-info">';
        echo '<strong>Periodo:</strong> ' . formato_fecha($fecha_inicio) . ' - ' . formato_fecha($fecha_fin) . '<br>';
        echo '<strong>Productos en ranking:</strong> ' . count($reporte['productos']);
        echo '</div>';
        
        if (count($reporte['productos']) > 0) {
            echo '<table class="table table-sm mt-2">';
            echo '<thead><tr><th>#</th><th>Producto</th><th>Categoría</th><th>Cantidad</th><th>Monto</th></tr></thead>';
            echo '<tbody>';
            $i = 1;
            foreach (array_slice($reporte['productos'], 0, 5) as $prod) {
                echo '<tr>';
                echo '<td>' . $i++ . '</td>';
                echo '<td>' . htmlspecialchars($prod['nombre']) . '</td>';
                echo '<td>' . htmlspecialchars($prod['categoria']) . '</td>';
                echo '<td>' . $prod['cantidad_vendida'] . '</td>';
                echo '<td>' . formato_dinero($prod['monto_total']) . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        }
        
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: Estructura de reporte incorrecta</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 4: Reporte de Productos con Menos Movimiento
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 4: Reporte de Productos con Menos Movimiento</h5></div><div class="card-body">';
try {
    $fecha_inicio = date('Y-m-01');
    $fecha_fin = date('Y-m-d');
    $reporte = Reporte::reporteProductosMenosMovimiento($fecha_inicio, $fecha_fin, 10);
    
    if (is_array($reporte['productos'])) {
        echo '<div class="alert test-success">✅ ÉXITO: Reporte de productos con menos movimiento generado</div>';
        echo '<div class="alert test-info">';
        echo '<strong>Periodo:</strong> ' . formato_fecha($fecha_inicio) . ' - ' . formato_fecha($fecha_fin) . '<br>';
        echo '<strong>Productos identificados:</strong> ' . count($reporte['productos']);
        echo '</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: Estructura de reporte incorrecta</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 5: Reporte de Ventas por Vendedor
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 5: Reporte de Ventas por Vendedor</h5></div><div class="card-body">';
try {
    $fecha_inicio = date('Y-m-01');
    $fecha_fin = date('Y-m-d');
    $reporte = Reporte::reporteVentasPorVendedor($fecha_inicio, $fecha_fin);
    
    if (is_array($reporte['vendedores'])) {
        echo '<div class="alert test-success">✅ ÉXITO: Reporte de ventas por vendedor generado</div>';
        echo '<div class="alert test-info">';
        echo '<strong>Periodo:</strong> ' . formato_fecha($fecha_inicio) . ' - ' . formato_fecha($fecha_fin) . '<br>';
        echo '<strong>Vendedores con ventas:</strong> ' . count($reporte['vendedores']);
        echo '</div>';
        
        if (count($reporte['vendedores']) > 0) {
            echo '<table class="table table-sm mt-2">';
            echo '<thead><tr><th>Vendedor</th><th>Ventas</th><th>Monto</th><th>Ticket Prom.</th></tr></thead>';
            echo '<tbody>';
            foreach (array_slice($reporte['vendedores'], 0, 5) as $v) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($v['vendedor']) . '</td>';
                echo '<td>' . $v['total_ventas'] . '</td>';
                echo '<td>' . formato_dinero($v['monto_total']) . '</td>';
                echo '<td>' . formato_dinero($v['ticket_promedio']) . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        }
        
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: Estructura de reporte incorrecta</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 6: Reporte de Ventas por Sucursal
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 6: Reporte de Ventas por Sucursal</h5></div><div class="card-body">';
try {
    $fecha_inicio = date('Y-m-01');
    $fecha_fin = date('Y-m-d');
    $reporte = Reporte::reporteVentasPorSucursal($fecha_inicio, $fecha_fin);
    
    if (is_array($reporte['sucursales'])) {
        echo '<div class="alert test-success">✅ ÉXITO: Reporte de ventas por sucursal generado</div>';
        echo '<div class="alert test-info">';
        echo '<strong>Periodo:</strong> ' . formato_fecha($fecha_inicio) . ' - ' . formato_fecha($fecha_fin) . '<br>';
        echo '<strong>Sucursales con ventas:</strong> ' . count($reporte['sucursales']);
        echo '</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: Estructura de reporte incorrecta</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 7: Reporte de Inventario Actual
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 7: Reporte de Inventario Actual</h5></div><div class="card-body">';
try {
    $reporte = Reporte::reporteInventarioActual();
    
    if (isset($reporte['resumen']) && is_array($reporte['inventario_detalle'])) {
        echo '<div class="alert test-success">✅ ÉXITO: Reporte de inventario actual generado</div>';
        echo '<div class="alert test-info">';
        echo '<strong>Total productos:</strong> ' . $reporte['resumen']['total_productos'] . '<br>';
        echo '<strong>Total unidades:</strong> ' . $reporte['resumen']['total_unidades'] . '<br>';
        echo '<strong>Productos bajo stock:</strong> ' . $reporte['resumen']['productos_bajo_stock'] . '<br>';
        echo '<strong>Productos sin stock:</strong> ' . $reporte['resumen']['productos_sin_stock'];
        echo '</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: Estructura de reporte incorrecta</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 8: Reporte de Trabajos Pendientes
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 8: Reporte de Trabajos Pendientes</h5></div><div class="card-body">';
try {
    $reporte = Reporte::reporteTrabajosPendientes();
    
    if (isset($reporte['resumen']) && is_array($reporte['trabajos_pendientes'])) {
        echo '<div class="alert test-success">✅ ÉXITO: Reporte de trabajos pendientes generado</div>';
        echo '<div class="alert test-info">';
        echo '<strong>Total pendientes:</strong> ' . $reporte['resumen']['total'] . '<br>';
        echo '<strong>Recibidos:</strong> ' . ($reporte['resumen']['recibidos'] ?? 0) . '<br>';
        echo '<strong>En proceso:</strong> ' . ($reporte['resumen']['en_proceso'] ?? 0) . '<br>';
        echo '<strong>Trabajos atrasados:</strong> ' . count($reporte['trabajos_atrasados']);
        echo '</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: Estructura de reporte incorrecta</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 9: Reporte de Trabajos Completados
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 9: Reporte de Trabajos Completados</h5></div><div class="card-body">';
try {
    $fecha_inicio = date('Y-m-01');
    $fecha_fin = date('Y-m-d');
    $reporte = Reporte::reporteTrabajosCompletados($fecha_inicio, $fecha_fin);
    
    if (isset($reporte['resumen']) && is_array($reporte['trabajos'])) {
        echo '<div class="alert test-success">✅ ÉXITO: Reporte de trabajos completados generado</div>';
        echo '<div class="alert test-info">';
        echo '<strong>Periodo:</strong> ' . formato_fecha($fecha_inicio) . ' - ' . formato_fecha($fecha_fin) . '<br>';
        echo '<strong>Trabajos completados:</strong> ' . $reporte['resumen']['total_trabajos'] . '<br>';
        echo '<strong>Ingresos totales:</strong> ' . formato_dinero($reporte['resumen']['ingresos_totales']) . '<br>';
        echo '<strong>Tiempo promedio:</strong> ' . round($reporte['resumen']['tiempo_promedio_dias'], 1) . ' días';
        echo '</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: Estructura de reporte incorrecta</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 10: Reporte de Cuentas por Cobrar
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 10: Reporte de Cuentas por Cobrar</h5></div><div class="card-body">';
try {
    $reporte = Reporte::reporteCuentasPorCobrar();
    
    if (isset($reporte['resumen']) && is_array($reporte['por_cliente'])) {
        echo '<div class="alert test-success">✅ ÉXITO: Reporte de cuentas por cobrar generado</div>';
        echo '<div class="alert test-info">';
        echo '<strong>Total créditos:</strong> ' . $reporte['resumen']['total_creditos'] . '<br>';
        echo '<strong>Créditos activos:</strong> ' . $reporte['resumen']['creditos_activos'] . '<br>';
        echo '<strong>Créditos vencidos:</strong> ' . $reporte['resumen']['creditos_vencidos'] . '<br>';
        echo '<strong>Total por cobrar:</strong> ' . formato_dinero($reporte['resumen']['total_por_cobrar']) . '<br>';
        echo '<strong>Saldo al día:</strong> ' . formato_dinero($reporte['resumen']['saldo_al_dia']) . '<br>';
        echo '<strong>Saldo vencido:</strong> ' . formato_dinero($reporte['resumen']['saldo_vencido']);
        echo '</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: Estructura de reporte incorrecta</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 11: Reporte de Ganancias
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 11: Reporte de Ganancias</h5></div><div class="card-body">';
try {
    $fecha_inicio = date('Y-m-01');
    $fecha_fin = date('Y-m-d');
    $reporte = Reporte::reporteGanancias($fecha_inicio, $fecha_fin);
    
    if (isset($reporte['ingresos']) && isset($reporte['egresos']) && isset($reporte['resumen'])) {
        echo '<div class="alert test-success">✅ ÉXITO: Reporte de ganancias generado</div>';
        echo '<div class="alert test-info">';
        echo '<strong>Periodo:</strong> ' . formato_fecha($fecha_inicio) . ' - ' . formato_fecha($fecha_fin) . '<br>';
        echo '<h6 class="mt-2">Ingresos:</h6>';
        echo 'Ventas: ' . formato_dinero($reporte['ingresos']['ventas']['total_ventas']) . '<br>';
        echo 'Taller: ' . formato_dinero($reporte['ingresos']['taller']['total_reparaciones']) . '<br>';
        echo 'Abonos: ' . formato_dinero($reporte['ingresos']['abonos']['total_abonos']) . '<br>';
        echo '<strong>Total ingresos: ' . formato_dinero($reporte['ingresos']['total']) . '</strong><br>';
        echo '<h6 class="mt-2">Egresos:</h6>';
        echo '<strong>Total egresos: ' . formato_dinero($reporte['egresos']['total']) . '</strong><br>';
        echo '<h6 class="mt-2">Resumen:</h6>';
        echo '<strong>Ganancia neta: ' . formato_dinero($reporte['resumen']['ganancia_neta']) . '</strong><br>';
        echo '<strong>Margen: ' . round($reporte['resumen']['margen_porcentaje'], 2) . '%</strong>';
        echo '</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: Estructura de reporte incorrecta</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 12: Reporte Comparativo de Periodos
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 12: Reporte Comparativo de Periodos</h5></div><div class="card-body">';
try {
    // Mes actual vs mes anterior
    $periodo1 = [
        'inicio' => date('Y-m-01', strtotime('-1 month')),
        'fin' => date('Y-m-t', strtotime('-1 month'))
    ];
    $periodo2 = [
        'inicio' => date('Y-m-01'),
        'fin' => date('Y-m-d')
    ];
    
    $reporte = Reporte::reporteComparativoPeriodos($periodo1, $periodo2);
    
    if (isset($reporte['periodo1']) && isset($reporte['periodo2']) && isset($reporte['variaciones'])) {
        echo '<div class="alert test-success">✅ ÉXITO: Reporte comparativo generado</div>';
        echo '<div class="alert test-info">';
        echo '<strong>Periodo 1:</strong> ' . formato_fecha($periodo1['inicio']) . ' - ' . formato_fecha($periodo1['fin']) . '<br>';
        echo '<strong>Periodo 2:</strong> ' . formato_fecha($periodo2['inicio']) . ' - ' . formato_fecha($periodo2['fin']) . '<br>';
        echo '<h6 class="mt-2">Variaciones:</h6>';
        echo 'Ventas: ' . ($reporte['variaciones']['ventas'] >= 0 ? '+' : '') . round($reporte['variaciones']['ventas'], 2) . '%<br>';
        echo 'Monto ventas: ' . ($reporte['variaciones']['monto_ventas'] >= 0 ? '+' : '') . round($reporte['variaciones']['monto_ventas'], 2) . '%<br>';
        echo 'Ticket promedio: ' . ($reporte['variaciones']['ticket_promedio'] >= 0 ? '+' : '') . round($reporte['variaciones']['ticket_promedio'], 2) . '%<br>';
        echo 'Clientes únicos: ' . ($reporte['variaciones']['clientes_unicos'] >= 0 ? '+' : '') . round($reporte['variaciones']['clientes_unicos'], 2) . '%';
        echo '</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: Estructura de reporte incorrecta</div>';
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