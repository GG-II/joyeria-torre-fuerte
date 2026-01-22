<?php
// ================================================
// TEST: MODELO CLIENTE
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

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Test - Modelo Cliente</title>
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
        <h1 class="mb-4">👤 Test: Modelo Cliente</h1>
        <hr>

<?php

$tests_passed = 0;
$tests_failed = 0;

// TEST 1: Crear cliente público sin crédito
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 1: Crear Cliente Público (Sin Crédito)</h5></div><div class="card-body">';
try {
    $datos_cliente = [
        'nombre' => 'Juan Pérez Test',
        'nit' => 'NIT-' . time(), // NIT único con timestamp
        'telefono' => '55551234',
        'email' => 'juan.test@example.com',
        'direccion' => 'Zona 1, Ciudad de Guatemala',
        'tipo_cliente' => 'publico',
        'tipo_mercaderias' => 'ambas',
        'limite_credito' => null, // Sin crédito
        'activo' => 1
    ];
    
    $cliente_id = Cliente::crear($datos_cliente);
    
    if ($cliente_id) {
        echo '<div class="alert test-success">✅ ÉXITO: Cliente público creado - ID: ' . $cliente_id . '</div>';
        echo '<div class="alert test-info">Cliente: ' . $datos_cliente['nombre'] . ' (Tipo: ' . $datos_cliente['tipo_cliente'] . ')</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se creó el cliente</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 2: Crear cliente mayorista con límite de crédito
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 2: Crear Cliente Mayorista (Con Límite de Crédito)</h5></div><div class="card-body">';
try {
    $nit_unico = 'MAY-' . time() . '-' . rand(100, 999); // NIT único con timestamp + random
    
    $datos_mayorista = [
        'nombre' => 'Joyería La Esmeralda S.A.',
        'nit' => $nit_unico,
        'telefono' => '55559876',
        'email' => 'ventas@esmeralda.com',
        'direccion' => 'Zona 10, Ciudad de Guatemala',
        'tipo_cliente' => 'mayorista',
        'tipo_mercaderias' => 'oro',
        'limite_credito' => 50000.00,
        'plazo_credito_dias' => 30,
        'activo' => 1
    ];
    
    $mayorista_id = Cliente::crear($datos_mayorista);
    
    if ($mayorista_id) {
        echo '<div class="alert test-success">✅ ÉXITO: Cliente mayorista creado - ID: ' . $mayorista_id . '</div>';
        echo '<div class="alert test-info">Cliente: ' . $datos_mayorista['nombre'] . '<br>Límite de crédito: ' . formato_dinero($datos_mayorista['limite_credito']) . '</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se creó el cliente mayorista</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 3: Validar NIT único (debe fallar con duplicado)
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 3: Validar NIT Único (Debe Rechazar Duplicado)</h5></div><div class="card-body">';
try {
    if (isset($nit_unico)) {
        $datos_duplicado = [
            'nombre' => 'Cliente Duplicado',
            'nit' => $nit_unico, // Usar el mismo NIT del TEST 2
            'telefono' => '55550000',
            'tipo_cliente' => 'publico',
            'tipo_mercaderias' => 'ambas'
        ];
        
        $duplicado_id = Cliente::crear($datos_duplicado);
        
        if (!$duplicado_id) {
            echo '<div class="alert test-success">✅ ÉXITO: Validación correcta - NIT duplicado rechazado</div>';
            echo '<div class="alert test-info">El sistema correctamente rechazó un NIT duplicado</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: Se permitió crear cliente con NIT duplicado</div>';
            $tests_failed++;
        }
    } else {
        echo '<div class="alert test-error">❌ ERROR: No hay nit_unico del TEST 2</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 4: Obtener cliente por ID
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 4: Obtener Cliente por ID</h5></div><div class="card-body">';
try {
    if (isset($cliente_id)) {
        $cliente = Cliente::obtenerPorId($cliente_id);
        
        if ($cliente && $cliente['nombre'] === 'Juan Pérez Test') {
            echo '<div class="alert test-success">✅ ÉXITO: Cliente obtenido correctamente</div>';
            echo '<div class="alert test-info"><pre>' . print_r($cliente, true) . '</pre></div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se obtuvo el cliente o los datos no coinciden</div>';
            $tests_failed++;
        }
    } else {
        echo '<div class="alert test-error">❌ ERROR: No hay cliente_id del TEST 1</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 5: Actualizar límite de crédito
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 5: Actualizar Límite de Crédito</h5></div><div class="card-body">';
try {
    if (isset($mayorista_id)) {
        $nuevo_limite = 75000.00;
        $resultado = Cliente::actualizarLimiteCredito($mayorista_id, $nuevo_limite);
        
        if ($resultado) {
            $cliente_actualizado = Cliente::obtenerPorId($mayorista_id);
            
            if ($cliente_actualizado['limite_credito'] == $nuevo_limite) {
                echo '<div class="alert test-success">✅ ÉXITO: Límite de crédito actualizado</div>';
                echo '<div class="alert test-info">Nuevo límite: ' . formato_dinero($nuevo_limite) . '</div>';
                $tests_passed++;
            } else {
                echo '<div class="alert test-error">❌ ERROR: El límite no se actualizó correctamente</div>';
                $tests_failed++;
            }
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se actualizó el límite de crédito</div>';
            $tests_failed++;
        }
    } else {
        echo '<div class="alert test-error">❌ ERROR: No hay mayorista_id del TEST 2</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 6: Validar límite de crédito
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 6: Validar Límite de Crédito (Simular Uso)</h5></div><div class="card-body">';
try {
    if (isset($mayorista_id)) {
        // Validar un monto dentro del límite
        $validacion = Cliente::validarLimiteCredito($mayorista_id, 50000.00);
        
        if ($validacion['valido']) {
            echo '<div class="alert test-success">✅ ÉXITO: Validación correcta - Monto dentro del límite</div>';
            echo '<div class="alert test-info">Crédito disponible: ' . formato_dinero($validacion['disponible']) . '</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: La validación falló incorrectamente</div>';
            $tests_failed++;
        }
    } else {
        echo '<div class="alert test-error">❌ ERROR: No hay mayorista_id del TEST 2</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 7: Listar clientes
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 7: Listar Clientes</h5></div><div class="card-body">';
try {
    $clientes = Cliente::listar(['activo' => 1], 1, 10);
    
    if (is_array($clientes) && count($clientes) >= 2) {
        echo '<div class="alert test-success">✅ ÉXITO: Clientes listados - Total: ' . count($clientes) . '</div>';
        echo '<div class="alert test-info">Primeros 3 clientes:<pre>' . print_r(array_slice($clientes, 0, 3), true) . '</pre></div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se listaron los clientes correctamente</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 8: Obtener estadísticas
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 8: Obtener Estadísticas de Clientes</h5></div><div class="card-body">';
try {
    $stats = Cliente::obtenerEstadisticas();
    
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