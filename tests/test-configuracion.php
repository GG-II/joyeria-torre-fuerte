<?php
/**
 * Tests para Modelo Configuracion
 * 
 * Prueba todas las funcionalidades del modelo Configuracion
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
require_once __DIR__ . '/../models/configuracion.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>⚙️ Test: Modelo Configuracion</title>
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
    <h1 class="mb-4">⚙️ Test: Modelo Configuracion</h1>
    <p class="lead">Pruebas del sistema de configuraciones</p>
    
<?php

$tests_passed = 0;
$tests_failed = 0;

// Clave única para tests
$clave_test = 'test_config_' . time();

// ========================================
// TEST 1: Establecer Configuración String
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 1: Establecer Configuración String</h5></div><div class="card-body">';
try {
    $resultado = Configuracion::establecer(
        $clave_test . '_string',
        'Valor de prueba',
        Configuracion::TIPO_STRING,
        'Configuración de prueba tipo string'
    );
    
    if ($resultado) {
        echo '<div class="alert test-success">✅ ÉXITO: Configuración string creada</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se pudo crear</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 2: Establecer Configuración Number
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 2: Establecer Configuración Number</h5></div><div class="card-body">';
try {
    $resultado = Configuracion::establecer(
        $clave_test . '_number',
        42.5,
        Configuracion::TIPO_NUMBER,
        'Configuración de prueba tipo number'
    );
    
    if ($resultado) {
        echo '<div class="alert test-success">✅ ÉXITO: Configuración number creada</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se pudo crear</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 3: Establecer Configuración Boolean
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 3: Establecer Configuración Boolean</h5></div><div class="card-body">';
try {
    $resultado = Configuracion::establecer(
        $clave_test . '_boolean',
        true,
        Configuracion::TIPO_BOOLEAN,
        'Configuración de prueba tipo boolean'
    );
    
    if ($resultado) {
        echo '<div class="alert test-success">✅ ÉXITO: Configuración boolean creada</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se pudo crear</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 4: Establecer Configuración JSON
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 4: Establecer Configuración JSON</h5></div><div class="card-body">';
try {
    $datos_json = [
        'opcion1' => 'valor1',
        'opcion2' => 123,
        'opcion3' => true
    ];
    
    $resultado = Configuracion::establecer(
        $clave_test . '_json',
        $datos_json,
        Configuracion::TIPO_JSON,
        'Configuración de prueba tipo JSON'
    );
    
    if ($resultado) {
        echo '<div class="alert test-success">✅ ÉXITO: Configuración JSON creada</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se pudo crear</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 5: Obtener Configuración String
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 5: Obtener Configuración String</h5></div><div class="card-body">';
try {
    $valor = Configuracion::obtener($clave_test . '_string');
    
    if ($valor === 'Valor de prueba') {
        echo '<div class="alert test-success">✅ ÉXITO: Valor obtenido correctamente</div>';
        echo '<div class="alert test-info">Valor: ' . htmlspecialchars($valor) . '</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: Valor incorrecto</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 6: Obtener Configuración con Conversión de Tipos
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 6: Obtener con Conversión de Tipos</h5></div><div class="card-body">';
try {
    $numero = Configuracion::obtener($clave_test . '_number');
    $booleano = Configuracion::obtener($clave_test . '_boolean');
    $json = Configuracion::obtener($clave_test . '_json');
    
    if (is_float($numero) && is_bool($booleano) && is_array($json)) {
        echo '<div class="alert test-success">✅ ÉXITO: Tipos convertidos correctamente</div>';
        echo '<div class="alert test-info">';
        echo 'Number: ' . $numero . ' (' . gettype($numero) . ')<br>';
        echo 'Boolean: ' . ($booleano ? 'true' : 'false') . ' (' . gettype($booleano) . ')<br>';
        echo 'JSON: Array con ' . count($json) . ' elementos (' . gettype($json) . ')';
        echo '</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: Tipos incorrectos</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 7: Obtener con Valor por Defecto
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 7: Obtener con Valor por Defecto</h5></div><div class="card-body">';
try {
    $valor = Configuracion::obtener('clave_inexistente', 'Valor default');
    
    if ($valor === 'Valor default') {
        echo '<div class="alert test-success">✅ ÉXITO: Valor por defecto retornado</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: No retornó el default</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 8: Actualizar Configuración Existente
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 8: Actualizar Configuración Existente</h5></div><div class="card-body">';
try {
    $resultado = Configuracion::establecer(
        $clave_test . '_string',
        'Valor actualizado',
        Configuracion::TIPO_STRING
    );
    
    // Limpiar caché y volver a obtener
    Configuracion::limpiarCache($clave_test . '_string');
    $valor = Configuracion::obtener($clave_test . '_string');
    
    if ($valor === 'Valor actualizado') {
        echo '<div class="alert test-success">✅ ÉXITO: Configuración actualizada</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se actualizó</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 9: Verificar Existencia
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 9: Verificar Existencia</h5></div><div class="card-body">';
try {
    $existe = Configuracion::existe($clave_test . '_string');
    $no_existe = Configuracion::existe('clave_totalmente_inexistente');
    
    if ($existe && !$no_existe) {
        echo '<div class="alert test-success">✅ ÉXITO: Verificación correcta</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: Verificación incorrecta</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 10: Obtener Todas las Configuraciones
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 10: Obtener Todas las Configuraciones</h5></div><div class="card-body">';
try {
    $todas = Configuracion::obtenerTodas($clave_test);
    
    if (is_array($todas) && count($todas) >= 4) {
        echo '<div class="alert test-success">✅ ÉXITO: Configuraciones obtenidas</div>';
        echo '<div class="alert test-info">Configs encontradas con prefijo: ' . count($todas) . '</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se obtuvieron correctamente</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 11: Obtener Múltiples
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 11: Obtener Múltiples Configuraciones</h5></div><div class="card-body">';
try {
    $multiples = Configuracion::obtenerMultiples([
        $clave_test . '_string',
        $clave_test . '_number',
        $clave_test . '_boolean'
    ]);
    
    if (count($multiples) === 3) {
        echo '<div class="alert test-success">✅ ÉXITO: Múltiples configuraciones obtenidas</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se obtuvieron correctamente</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 12: Establecer Múltiples
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 12: Establecer Múltiples Configuraciones</h5></div><div class="card-body">';
try {
    $configs = [
        $clave_test . '_multi1' => ['valor' => 'Test1', 'tipo' => Configuracion::TIPO_STRING],
        $clave_test . '_multi2' => ['valor' => 100, 'tipo' => Configuracion::TIPO_NUMBER]
    ];
    
    $resultado = Configuracion::establecerMultiples($configs);
    
    if ($resultado) {
        echo '<div class="alert test-success">✅ ÉXITO: Múltiples configuraciones establecidas</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se establecieron</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 13: Inicializar Defaults
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 13: Inicializar Configuraciones por Defecto</h5></div><div class="card-body">';
try {
    $resultado = Configuracion::inicializarDefaults();
    
    if ($resultado) {
        // Verificar que existan algunas configs default
        $sistema_nombre = Configuracion::obtener('sistema_nombre');
        $sistema_iva = Configuracion::obtener('sistema_iva');
        
        if ($sistema_nombre && $sistema_iva !== null) {
            echo '<div class="alert test-success">✅ ÉXITO: Defaults inicializados</div>';
            echo '<div class="alert test-info">';
            echo 'Sistema nombre: ' . $sistema_nombre . '<br>';
            echo 'IVA: ' . $sistema_iva . '%';
            echo '</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: Defaults no creados</div>';
            $tests_failed++;
        }
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se inicializaron</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 14: Limpiar Caché
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 14: Limpiar Caché</h5></div><div class="card-body">';
try {
    // Obtener para cachear
    Configuracion::obtener($clave_test . '_string');
    
    // Limpiar caché
    Configuracion::limpiarCache();
    
    echo '<div class="alert test-success">✅ ÉXITO: Caché limpiado</div>';
    $tests_passed++;
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 15: Eliminar Configuración
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 15: Eliminar Configuración</h5></div><div class="card-body">';
try {
    $resultado = Configuracion::eliminar($clave_test . '_string');
    
    if ($resultado) {
        $existe = Configuracion::existe($clave_test . '_string');
        if (!$existe) {
            echo '<div class="alert test-success">✅ ÉXITO: Configuración eliminada</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: Configuración aún existe</div>';
            $tests_failed++;
        }
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se pudo eliminar</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// LIMPIEZA: Eliminar configs de prueba
// ========================================
try {
    $configs_prueba = Configuracion::obtenerTodas($clave_test);
    foreach ($configs_prueba as $config) {
        Configuracion::eliminar($config['clave']);
    }
} catch (Exception $e) {
    // Silenciar errores de limpieza
}

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
