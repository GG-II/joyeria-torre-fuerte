<?php
/**
 * Tests para Modelo Sucursal
 * 
 * Prueba todas las funcionalidades del modelo Sucursal
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
require_once __DIR__ . '/../models/sucursal.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🏪 Test: Modelo Sucursal</title>
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
    <h1 class="mb-4">🏪 Test: Modelo Sucursal</h1>
    <p class="lead">Pruebas del sistema de gestión de sucursales</p>
    
<?php

$tests_passed = 0;
$tests_failed = 0;
$sucursal_test_id = null;

// ========================================
// TEST 1: Crear Sucursal
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 1: Crear Sucursal</h5></div><div class="card-body">';
try {
    $datos = [
        'nombre' => 'Sucursal Test ' . time(),
        'direccion' => 'Calle de Prueba #123, Zona 10, Guatemala',
        'telefono' => '2222-3333',
        'email' => 'test@sucursal.com',
        'activo' => 1
    ];
    
    $sucursal_test_id = Sucursal::crear($datos);
    
    if ($sucursal_test_id) {
        echo '<div class="alert test-success">✅ ÉXITO: Sucursal creada correctamente</div>';
        echo '<div class="alert test-info">';
        echo 'ID: ' . $sucursal_test_id . '<br>';
        echo 'Nombre: ' . $datos['nombre'] . '<br>';
        echo 'Dirección: ' . $datos['direccion'];
        echo '</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se pudo crear la sucursal</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 2: Validar Nombre Duplicado
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 2: Validar Nombre Duplicado</h5></div><div class="card-body">';
try {
    if ($sucursal_test_id) {
        $sucursal = Sucursal::obtenerPorId($sucursal_test_id);
        
        // Intentar crear otra sucursal con el mismo nombre
        $resultado = Sucursal::crear([
            'nombre' => $sucursal['nombre'],
            'direccion' => 'Otra dirección diferente',
            'telefono' => '3333-4444'
        ]);
        
        if ($resultado === false) {
            echo '<div class="alert test-success">✅ ÉXITO: Sistema rechazó correctamente nombre duplicado</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: Sistema permitió nombre duplicado</div>';
            $tests_failed++;
        }
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 3: Obtener Sucursal por ID
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 3: Obtener Sucursal por ID</h5></div><div class="card-body">';
try {
    if ($sucursal_test_id) {
        $sucursal = Sucursal::obtenerPorId($sucursal_test_id);
        
        if ($sucursal && $sucursal['id'] == $sucursal_test_id) {
            echo '<div class="alert test-success">✅ ÉXITO: Sucursal obtenida correctamente</div>';
            echo '<div class="alert test-info">';
            echo 'ID: ' . $sucursal['id'] . '<br>';
            echo 'Nombre: ' . $sucursal['nombre'] . '<br>';
            echo 'Dirección: ' . $sucursal['direccion'] . '<br>';
            echo 'Activa: ' . ($sucursal['activo'] ? 'Sí' : 'No');
            echo '</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se pudo obtener la sucursal</div>';
            $tests_failed++;
        }
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 4: Editar Sucursal
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 4: Editar Sucursal</h5></div><div class="card-body">';
try {
    if ($sucursal_test_id) {
        $resultado = Sucursal::editar($sucursal_test_id, [
            'nombre' => 'Sucursal Test Editada ' . time(),
            'direccion' => 'Nueva Dirección de Prueba, Zona 15',
            'telefono' => '4444-5555',
            'email' => 'editada@sucursal.com',
            'activo' => 1
        ]);
        
        if ($resultado) {
            $sucursal = Sucursal::obtenerPorId($sucursal_test_id);
            echo '<div class="alert test-success">✅ ÉXITO: Sucursal editada correctamente</div>';
            echo '<div class="alert test-info">';
            echo 'Nuevo nombre: ' . $sucursal['nombre'] . '<br>';
            echo 'Nueva dirección: ' . $sucursal['direccion'];
            echo '</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se pudo editar la sucursal</div>';
            $tests_failed++;
        }
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 5: Asignar Responsable
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 5: Asignar Responsable</h5></div><div class="card-body">';
try {
    if ($sucursal_test_id) {
        // Obtener un usuario activo
        $usuario = db_query_one("SELECT id, nombre FROM usuarios WHERE activo = 1 LIMIT 1");
        
        if ($usuario) {
            $resultado = Sucursal::asignarResponsable($sucursal_test_id, $usuario['id']);
            
            if ($resultado) {
                $sucursal = Sucursal::obtenerPorId($sucursal_test_id);
                echo '<div class="alert test-success">✅ ÉXITO: Responsable asignado correctamente</div>';
                echo '<div class="alert test-info">';
                echo 'Responsable: ' . ($sucursal['responsable_nombre'] ?? 'N/A');
                echo '</div>';
                $tests_passed++;
            } else {
                echo '<div class="alert test-error">❌ ERROR: No se pudo asignar responsable</div>';
                $tests_failed++;
            }
        } else {
            echo '<div class="alert test-info">⚠️ Test omitido: No hay usuarios activos</div>';
            $tests_passed++;
        }
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 6: Listar Sucursales
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 6: Listar Sucursales</h5></div><div class="card-body">';
try {
    $sucursales = Sucursal::listar();
    
    if (is_array($sucursales)) {
        echo '<div class="alert test-success">✅ ÉXITO: Listado obtenido correctamente</div>';
        echo '<div class="alert test-info">';
        echo 'Total sucursales: ' . count($sucursales);
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
// TEST 7: Listar Sucursales Activas
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 7: Listar Sucursales Activas</h5></div><div class="card-body">';
try {
    $activas = Sucursal::listarActivas();
    
    echo '<div class="alert test-success">✅ ÉXITO: Sucursales activas obtenidas</div>';
    echo '<div class="alert test-info">';
    echo 'Total activas: ' . count($activas);
    echo '</div>';
    $tests_passed++;
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 8: Obtener Usuarios de Sucursal
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 8: Obtener Usuarios de Sucursal</h5></div><div class="card-body">';
try {
    if ($sucursal_test_id) {
        $usuarios = Sucursal::obtenerUsuarios($sucursal_test_id);
        
        echo '<div class="alert test-success">✅ ÉXITO: Usuarios obtenidos correctamente</div>';
        echo '<div class="alert test-info">';
        echo 'Usuarios en esta sucursal: ' . count($usuarios);
        echo '</div>';
        $tests_passed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 9: Obtener Estadísticas
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 9: Obtener Estadísticas</h5></div><div class="card-body">';
try {
    $stats = Sucursal::obtenerEstadisticas();
    
    if ($stats && isset($stats['total'])) {
        echo '<div class="alert test-success">✅ ÉXITO: Estadísticas obtenidas</div>';
        echo '<div class="alert test-info">';
        echo 'Total: ' . $stats['total'] . '<br>';
        echo 'Activas: ' . $stats['activas'] . '<br>';
        echo 'Inactivas: ' . $stats['inactivas'] . '<br>';
        echo 'Con responsable: ' . $stats['con_responsable'];
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
// TEST 10: Verificar Existencia
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 10: Verificar Existencia</h5></div><div class="card-body">';
try {
    if ($sucursal_test_id) {
        $existe = Sucursal::existe($sucursal_test_id);
        $no_existe = Sucursal::existe(99999);
        
        if ($existe && !$no_existe) {
            echo '<div class="alert test-success">✅ ÉXITO: Verificación de existencia correcta</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: Verificación incorrecta</div>';
            $tests_failed++;
        }
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
