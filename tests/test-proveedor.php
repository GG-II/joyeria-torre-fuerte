<?php
/**
 * Tests para Modelo Proveedor
 * 
 * Prueba todas las funcionalidades del modelo Proveedor
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
require_once __DIR__ . '/../models/proveedor.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📦 Test: Modelo Proveedor</title>
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
    <h1 class="mb-4">📦 Test: Modelo Proveedor</h1>
    <p class="lead">Pruebas del sistema de gestión de proveedores</p>
    
<?php

$tests_passed = 0;
$tests_failed = 0;
$proveedor_test_id = null;

// ========================================
// TEST 1: Crear Proveedor
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 1: Crear Proveedor</h5></div><div class="card-body">';
try {
    $datos = [
        'nombre' => 'Gold Suppliers S.A.',
        'empresa' => 'Gold Suppliers Sociedad Anónima',
        'contacto' => 'Juan Pérez',
        'telefono' => '2222-3333',
        'email' => 'contacto@goldsuppliers.com',
        'direccion' => 'Zona 10, Ciudad Guatemala',
        'productos_suministra' => 'Oro 18k, Plata 925, Diamantes',
        'activo' => 1
    ];
    
    $proveedor_test_id = Proveedor::crear($datos);
    
    if ($proveedor_test_id) {
        echo '<div class="alert test-success">✅ ÉXITO: Proveedor creado correctamente</div>';
        echo '<div class="alert test-info">';
        echo 'ID: ' . $proveedor_test_id . '<br>';
        echo 'Nombre: ' . $datos['nombre'] . '<br>';
        echo 'Contacto: ' . $datos['contacto'] . '<br>';
        echo 'Teléfono: ' . $datos['telefono'];
        echo '</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se pudo crear el proveedor</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 2: Obtener Proveedor por ID
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 2: Obtener Proveedor por ID</h5></div><div class="card-body">';
try {
    if ($proveedor_test_id) {
        $proveedor = Proveedor::obtenerPorId($proveedor_test_id);
        
        if ($proveedor && $proveedor['id'] == $proveedor_test_id) {
            echo '<div class="alert test-success">✅ ÉXITO: Proveedor obtenido correctamente</div>';
            echo '<div class="alert test-info">';
            echo 'Nombre: ' . $proveedor['nombre'] . '<br>';
            echo 'Empresa: ' . $proveedor['empresa'] . '<br>';
            echo 'Email: ' . $proveedor['email'] . '<br>';
            echo 'Productos: ' . $proveedor['productos_suministra'];
            echo '</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se pudo obtener el proveedor</div>';
            $tests_failed++;
        }
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 3: Editar Proveedor
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 3: Editar Proveedor</h5></div><div class="card-body">';
try {
    if ($proveedor_test_id) {
        $resultado = Proveedor::editar($proveedor_test_id, [
            'nombre' => 'Gold Suppliers Actualizado',
            'empresa' => 'Gold Suppliers S.A. de C.V.',
            'contacto' => 'María González',
            'telefono' => '2222-4444',
            'email' => 'ventas@goldsuppliers.com',
            'direccion' => 'Zona 9, Ciudad Guatemala',
            'productos_suministra' => 'Oro 18k, Plata 925, Piedras preciosas',
            'activo' => 1
        ]);
        
        if ($resultado) {
            $proveedor = Proveedor::obtenerPorId($proveedor_test_id);
            echo '<div class="alert test-success">✅ ÉXITO: Proveedor editado correctamente</div>';
            echo '<div class="alert test-info">';
            echo 'Nuevo nombre: ' . $proveedor['nombre'] . '<br>';
            echo 'Nuevo contacto: ' . $proveedor['contacto'];
            echo '</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se pudo editar el proveedor</div>';
            $tests_failed++;
        }
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 4: Desactivar Proveedor
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 4: Desactivar Proveedor</h5></div><div class="card-body">';
try {
    if ($proveedor_test_id) {
        $resultado = Proveedor::desactivar($proveedor_test_id);
        
        if ($resultado) {
            $proveedor = Proveedor::obtenerPorId($proveedor_test_id);
            if ($proveedor['activo'] == 0) {
                echo '<div class="alert test-success">✅ ÉXITO: Proveedor desactivado correctamente</div>';
                $tests_passed++;
            } else {
                echo '<div class="alert test-error">❌ ERROR: Proveedor no se desactivó</div>';
                $tests_failed++;
            }
        }
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 5: Activar Proveedor
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 5: Activar Proveedor</h5></div><div class="card-body">';
try {
    if ($proveedor_test_id) {
        $resultado = Proveedor::activar($proveedor_test_id);
        
        if ($resultado) {
            $proveedor = Proveedor::obtenerPorId($proveedor_test_id);
            if ($proveedor['activo'] == 1) {
                echo '<div class="alert test-success">✅ ÉXITO: Proveedor activado correctamente</div>';
                $tests_passed++;
            } else {
                echo '<div class="alert test-error">❌ ERROR: Proveedor no se activó</div>';
                $tests_failed++;
            }
        }
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 6: Listar Proveedores
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 6: Listar Proveedores</h5></div><div class="card-body">';
try {
    $proveedores = Proveedor::listar();
    
    if (is_array($proveedores)) {
        echo '<div class="alert test-success">✅ ÉXITO: Listado obtenido correctamente</div>';
        echo '<div class="alert test-info">';
        echo 'Total proveedores: ' . count($proveedores);
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
// TEST 7: Listar Proveedores Activos
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 7: Listar Proveedores Activos</h5></div><div class="card-body">';
try {
    $activos = Proveedor::listarActivos();
    
    echo '<div class="alert test-success">✅ ÉXITO: Proveedores activos obtenidos</div>';
    echo '<div class="alert test-info">';
    echo 'Total activos: ' . count($activos);
    echo '</div>';
    $tests_passed++;
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 8: Buscar Proveedor
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 8: Buscar Proveedor</h5></div><div class="card-body">';
try {
    if ($proveedor_test_id) {
        $resultados = Proveedor::buscar('Gold');
        
        if (count($resultados) > 0) {
            echo '<div class="alert test-success">✅ ÉXITO: Búsqueda realizada correctamente</div>';
            echo '<div class="alert test-info">';
            echo 'Resultados encontrados: ' . count($resultados);
            echo '</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se encontraron resultados</div>';
            $tests_failed++;
        }
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
    $stats = Proveedor::obtenerEstadisticas();
    
    if ($stats && isset($stats['total'])) {
        echo '<div class="alert test-success">✅ ÉXITO: Estadísticas obtenidas</div>';
        echo '<div class="alert test-info">';
        echo 'Total: ' . $stats['total'] . '<br>';
        echo 'Activos: ' . $stats['activos'] . '<br>';
        echo 'Inactivos: ' . $stats['inactivos'];
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
