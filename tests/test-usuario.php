<?php
/**
 * Tests para Modelo Usuario
 * 
 * Prueba todas las funcionalidades del modelo Usuario:
 * - Creación de usuarios
 * - Edición de datos
 * - Cambio de contraseñas
 * - Activación/Desactivación
 * - Listados y filtros
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
require_once __DIR__ . '/../models/usuario.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>👤 Test: Modelo Usuario</title>
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
    <h1 class="mb-4">👤 Test: Modelo Usuario</h1>
    <p class="lead">Pruebas del sistema de gestión de usuarios</p>
    
<?php

$tests_passed = 0;
$tests_failed = 0;

// Variable para almacenar ID de usuario de prueba
$usuario_test_id = null;

// ========================================
// TEST 1: Crear Usuario
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 1: Crear Usuario</h5></div><div class="card-body">';
try {
    $datos = [
        'nombre' => 'Usuario Test',
        'email' => 'test_' . time() . '@joyeria.com',
        'password' => 'password123',
        'rol' => 'vendedor',
        'sucursal_id' => 1,
        'telefono' => '5555-5555',
        'activo' => 1
    ];
    
    $usuario_test_id = Usuario::crear($datos);
    
    if ($usuario_test_id) {
        echo '<div class="alert test-success">✅ ÉXITO: Usuario creado correctamente</div>';
        echo '<div class="alert test-info">';
        echo 'ID: ' . $usuario_test_id . '<br>';
        echo 'Nombre: ' . $datos['nombre'] . '<br>';
        echo 'Email: ' . $datos['email'] . '<br>';
        echo 'Rol: ' . $datos['rol'];
        echo '</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se pudo crear el usuario</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 2: Validar Email Duplicado
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 2: Validar Email Duplicado</h5></div><div class="card-body">';
try {
    if ($usuario_test_id) {
        $usuario = Usuario::obtenerPorId($usuario_test_id);
        
        // Intentar crear otro usuario con el mismo email
        $resultado = Usuario::crear([
            'nombre' => 'Otro Usuario',
            'email' => $usuario['email'],
            'password' => 'password123',
            'rol' => 'cajero',
            'telefono' => '4444-4444'
        ]);
        
        if ($resultado === false) {
            echo '<div class="alert test-success">✅ ÉXITO: Sistema rechazó correctamente email duplicado</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: Sistema permitió email duplicado</div>';
            $tests_failed++;
        }
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 3: Obtener Usuario por ID
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 3: Obtener Usuario por ID</h5></div><div class="card-body">';
try {
    if ($usuario_test_id) {
        $usuario = Usuario::obtenerPorId($usuario_test_id);
        
        if ($usuario && $usuario['id'] == $usuario_test_id) {
            echo '<div class="alert test-success">✅ ÉXITO: Usuario obtenido correctamente</div>';
            echo '<div class="alert test-info">';
            echo 'ID: ' . $usuario['id'] . '<br>';
            echo 'Nombre: ' . $usuario['nombre'] . '<br>';
            echo 'Email: ' . $usuario['email'] . '<br>';
            echo 'Rol: ' . $usuario['rol'] . '<br>';
            echo 'Sucursal: ' . ($usuario['sucursal_nombre'] ?? 'Sin sucursal');
            echo '</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se pudo obtener el usuario</div>';
            $tests_failed++;
        }
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 4: Editar Usuario
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 4: Editar Usuario</h5></div><div class="card-body">';
try {
    if ($usuario_test_id) {
        $resultado = Usuario::editar($usuario_test_id, [
            'nombre' => 'Usuario Test Editado',
            'email' => 'test_editado_' . time() . '@joyeria.com',
            'rol' => 'cajero',
            'sucursal_id' => 1,
            'telefono' => '6666-6666',
            'activo' => 1
        ]);
        
        if ($resultado) {
            $usuario = Usuario::obtenerPorId($usuario_test_id);
            echo '<div class="alert test-success">✅ ÉXITO: Usuario editado correctamente</div>';
            echo '<div class="alert test-info">';
            echo 'Nuevo nombre: ' . $usuario['nombre'] . '<br>';
            echo 'Nuevo email: ' . $usuario['email'] . '<br>';
            echo 'Nuevo rol: ' . $usuario['rol'];
            echo '</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se pudo editar el usuario</div>';
            $tests_failed++;
        }
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 5: Cambiar Contraseña
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 5: Cambiar Contraseña</h5></div><div class="card-body">';
try {
    if ($usuario_test_id) {
        // Cambiar contraseña
        $resultado = Usuario::cambiarPassword($usuario_test_id, 'password123', 'nuevapassword456');
        
        if ($resultado) {
            echo '<div class="alert test-success">✅ ÉXITO: Contraseña cambiada correctamente</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se pudo cambiar la contraseña</div>';
            $tests_failed++;
        }
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 6: Desactivar Usuario
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 6: Desactivar Usuario</h5></div><div class="card-body">';
try {
    if ($usuario_test_id) {
        $resultado = Usuario::desactivar($usuario_test_id);
        
        if ($resultado) {
            $usuario = Usuario::obtenerPorId($usuario_test_id);
            if ($usuario['activo'] == 0) {
                echo '<div class="alert test-success">✅ ÉXITO: Usuario desactivado correctamente</div>';
                $tests_passed++;
            } else {
                echo '<div class="alert test-error">❌ ERROR: Usuario no se desactivó</div>';
                $tests_failed++;
            }
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se pudo desactivar el usuario</div>';
            $tests_failed++;
        }
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 7: Activar Usuario
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 7: Activar Usuario</h5></div><div class="card-body">';
try {
    if ($usuario_test_id) {
        $resultado = Usuario::activar($usuario_test_id);
        
        if ($resultado) {
            $usuario = Usuario::obtenerPorId($usuario_test_id);
            if ($usuario['activo'] == 1) {
                echo '<div class="alert test-success">✅ ÉXITO: Usuario activado correctamente</div>';
                $tests_passed++;
            } else {
                echo '<div class="alert test-error">❌ ERROR: Usuario no se activó</div>';
                $tests_failed++;
            }
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se pudo activar el usuario</div>';
            $tests_failed++;
        }
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 8: Listar Usuarios
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 8: Listar Usuarios</h5></div><div class="card-body">';
try {
    $usuarios = Usuario::listar(['activo' => 1]);
    
    if (is_array($usuarios) && count($usuarios) > 0) {
        echo '<div class="alert test-success">✅ ÉXITO: Listado de usuarios obtenido</div>';
        echo '<div class="alert test-info">';
        echo 'Total usuarios activos: ' . count($usuarios);
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
// TEST 9: Listar por Rol
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 9: Listar Usuarios por Rol</h5></div><div class="card-body">';
try {
    $vendedores = Usuario::listarPorRol('vendedor');
    $cajeros = Usuario::listarPorRol('cajero');
    
    echo '<div class="alert test-success">✅ ÉXITO: Listado por rol obtenido</div>';
    echo '<div class="alert test-info">';
    echo 'Vendedores: ' . count($vendedores) . '<br>';
    echo 'Cajeros: ' . count($cajeros);
    echo '</div>';
    $tests_passed++;
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// ========================================
// TEST 10: Obtener Estadísticas
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 10: Obtener Estadísticas</h5></div><div class="card-body">';
try {
    $stats = Usuario::obtenerEstadisticas();
    
    if ($stats && isset($stats['total'])) {
        echo '<div class="alert test-success">✅ ÉXITO: Estadísticas obtenidas</div>';
        echo '<div class="alert test-info">';
        echo 'Total usuarios: ' . $stats['total'] . '<br>';
        echo 'Activos: ' . $stats['activos'] . '<br>';
        echo 'Inactivos: ' . $stats['inactivos'] . '<br>';
        echo 'Administradores: ' . $stats['administradores'] . '<br>';
        echo 'Vendedores: ' . $stats['vendedores'];
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
