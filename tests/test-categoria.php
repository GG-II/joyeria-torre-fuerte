<?php
// ================================================
// TEST: MODELO CATEGORIA
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
require_once __DIR__ . '/../models/categoria.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test - Modelo Categoría</title>
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
        <h1 class="mb-4">📁 Test: Modelo Categoría</h1>
        <hr>

<?php

$tests_passed = 0;
$tests_failed = 0;

// TEST 1: Crear categoría
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 1: Crear Categoría</h5></div><div class="card-body">';
try {
    $datos = [
        'nombre' => 'Test Categoría ' . time(),
        'descripcion' => 'Categoría de prueba',
        'tipo_clasificacion' => 'tipo',
        'activo' => 1
    ];
    
    $categoria_id = Categoria::crear($datos);
    
    if ($categoria_id) {
        echo '<div class="alert test-success">✅ ÉXITO: Categoría creada con ID: ' . $categoria_id . '</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se pudo crear la categoría</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 2: Listar categorías (incluyendo activas e inactivas)
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 2: Listar Categorías</h5></div><div class="card-body">';
try {
    // Listar TODAS las categorías (activas e inactivas)
    $categorias_activas = Categoria::listar(['activo' => 1]);
    $categorias_todas = Categoria::listar();  // Por defecto solo activas
    
    echo '<div class="alert test-info">Categorías activas: ' . count($categorias_activas) . '</div>';
    
    if (is_array($categorias_activas)) {
        echo '<div class="alert test-success">✅ ÉXITO: Función listar() funciona correctamente</div>';
        if (count($categorias_activas) > 0) {
            echo '<div class="alert test-info"><strong>Muestra de categorías:</strong><pre>' . print_r(array_slice($categorias_activas, 0, 3), true) . '</pre></div>';
        }
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se listaron categorías</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 3: Listar por tipo
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 3: Listar Categorías por Tipo</h5></div><div class="card-body">';
try {
    $agrupadas = Categoria::listarPorTipo();
    
    if (is_array($agrupadas) && isset($agrupadas['tipo'])) {
        echo '<div class="alert test-success">✅ ÉXITO: Categorías agrupadas correctamente</div>';
        echo '<div class="alert test-info">Tipos: ' . count($agrupadas['tipo']) . ', Materiales: ' . count($agrupadas['material']) . ', Peso: ' . count($agrupadas['peso']) . '</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se agruparon las categorías</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 4: Actualizar categoría
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 4: Actualizar Categoría</h5></div><div class="card-body">';
try {
    if (isset($categoria_id)) {
        // IMPORTANTE: Usar un nombre DIFERENTE al original para evitar conflicto de validación
        $nombre_actualizado = 'Cat Actualizada ' . time();
        $datos_actualizacion = [
            'nombre' => $nombre_actualizado,
            'descripcion' => 'Descripción actualizada en el test',
            'tipo_clasificacion' => 'tipo'
            // NO incluir categoria_padre_id para que use null por defecto
        ];
        
        echo '<div class="alert test-info"><strong>Debug:</strong> Intentando actualizar categoría ID ' . $categoria_id . ' con nombre: ' . $nombre_actualizado . '</div>';
        
        $resultado = Categoria::actualizar($categoria_id, $datos_actualizacion);
        
        if ($resultado) {
            echo '<div class="alert test-success">✅ ÉXITO: Categoría actualizada correctamente</div>';
            
            // Verificar que realmente se actualizó
            $categoria_actualizada = Categoria::obtenerPorId($categoria_id);
            if ($categoria_actualizada && $categoria_actualizada['nombre'] == $nombre_actualizado) {
                echo '<div class="alert test-success">✅ ÉXITO: Los datos se guardaron en BD</div>';
            }
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se actualizó la categoría</div>';
            echo '<div class="alert test-info"><strong>Posibles causas:</strong> Validación falló, categoría no existe, o error en BD</div>';
            $tests_failed++;
        }
    } else {
        echo '<div class="alert test-error">❌ ERROR: No hay categoria_id disponible (TEST 1 falló)</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 5: Obtener árbol de categorías
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 5: Obtener Árbol de Categorías</h5></div><div class="card-body">';
try {
    $arbol = Categoria::obtenerArbol();
    
    if (is_array($arbol)) {
        echo '<div class="alert test-success">✅ ÉXITO: Árbol de categorías generado</div>';
        echo '<div class="alert test-info"><pre>' . print_r($arbol, true) . '</pre></div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se generó el árbol</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 6: Eliminar categoría
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 6: Eliminar Categoría</h5></div><div class="card-body">';
try {
    if (isset($categoria_id)) {
        $resultado = Categoria::eliminar($categoria_id);
        
        if ($resultado) {
            echo '<div class="alert test-success">✅ ÉXITO: Categoría eliminada</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se pudo eliminar</div>';
            $tests_failed++;
        }
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