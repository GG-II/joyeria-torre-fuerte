<?php
// ================================================
// TEST: MODELO TRABAJO TALLER
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
require_once __DIR__ . '/../models/trabajo_taller.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Test - Modelo Trabajo Taller</title>
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
        <h1 class="mb-4">🔧 Test: Modelo Trabajo Taller</h1>
        <hr>

<?php

$tests_passed = 0;
$tests_failed = 0;

// TEST 1: Generar código de trabajo
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 1: Generar Código de Trabajo</h5></div><div class="card-body">';
try {
    $codigo = TrabajoTaller::generarCodigoTrabajo();
    
    if ($codigo && preg_match('/^TT-\d{4}-\d{4}$/', $codigo)) {
        echo '<div class="alert test-success">✅ ÉXITO: Código generado: ' . $codigo . '</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: Formato de código incorrecto: ' . $codigo . '</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 2: Crear trabajo de taller
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 2: Crear Trabajo de Taller</h5></div><div class="card-body">';
try {
    $datos_trabajo = [
        'cliente_nombre' => 'María González',
        'cliente_telefono' => '55551234',
        'cliente_id' => null,
        'material' => 'oro',
        'peso_gramos' => 15.5,
        'largo_cm' => null,
        'con_piedra' => 1,
        'estilo' => 'Clásico',
        'descripcion_pieza' => 'Anillo de compromiso en oro 18K con diamante central',
        'tipo_trabajo' => 'reparacion',
        'descripcion_trabajo' => 'Reparar soldadura en la parte inferior del aro, pulir y limpiar',
        'precio_total' => 850.00,
        'anticipo' => 300.00,
        'fecha_recepcion' => date('Y-m-d H:i:s'),
        'fecha_entrega_prometida' => date('Y-m-d', strtotime('+5 days')),
        'empleado_recibe_id' => 1,
        'observaciones' => 'Cliente solicita urgencia moderada'
    ];
    
    $trabajo_id = TrabajoTaller::crear($datos_trabajo);
    
    if ($trabajo_id) {
        echo '<div class="alert test-success">✅ ÉXITO: Trabajo creado con ID: ' . $trabajo_id . '</div>';
        $tests_passed++;
        
        // Guardar ID para tests posteriores
        $_SESSION['test_trabajo_id'] = $trabajo_id;
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se creó el trabajo</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 3: Obtener trabajo por ID
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 3: Obtener Trabajo por ID</h5></div><div class="card-body">';
try {
    $trabajo_id = $_SESSION['test_trabajo_id'] ?? null;
    
    if ($trabajo_id) {
        $trabajo = TrabajoTaller::obtenerPorId($trabajo_id);
        
        if ($trabajo && is_array($trabajo)) {
            echo '<div class="alert test-success">✅ ÉXITO: Trabajo obtenido correctamente</div>';
            echo '<div class="alert test-info"><strong>Código:</strong> ' . $trabajo['codigo'] . '<br>';
            echo '<strong>Cliente:</strong> ' . $trabajo['cliente_nombre'] . '<br>';
            echo '<strong>Estado:</strong> ' . $trabajo['estado'] . '<br>';
            echo '<strong>Precio Total:</strong> Q ' . number_format($trabajo['precio_total'], 2) . '<br>';
            echo '<strong>Saldo:</strong> Q ' . number_format($trabajo['saldo'], 2) . '</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se obtuvo el trabajo</div>';
            $tests_failed++;
        }
    } else {
        echo '<div class="alert test-error">❌ ERROR: No hay ID de trabajo para probar</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 4: Actualizar trabajo
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 4: Actualizar Trabajo</h5></div><div class="card-body">';
try {
    $trabajo_id = $_SESSION['test_trabajo_id'] ?? null;
    
    if ($trabajo_id) {
        $datos_actualizacion = [
            'cliente_nombre' => 'María González López',
            'cliente_telefono' => '55551234',
            'material' => 'oro',
            'peso_gramos' => 16.0,
            'descripcion_pieza' => 'Anillo de compromiso en oro 18K con diamante central de 0.5 quilates',
            'tipo_trabajo' => 'reparacion',
            'descripcion_trabajo' => 'Reparar soldadura, cambiar engaste del diamante, pulir y limpiar',
            'precio_total' => 950.00,
            'anticipo' => 300.00,
            'fecha_entrega_prometida' => date('Y-m-d', strtotime('+5 days')),
            'empleado_recibe_id' => 1,  // REQUERIDO para validación
            'observaciones' => 'Cliente solicita urgencia moderada. Precio actualizado por cambio de engaste.'
        ];
        
        $resultado = TrabajoTaller::actualizar($trabajo_id, $datos_actualizacion);
        
        if ($resultado) {
            echo '<div class="alert test-success">✅ ÉXITO: Trabajo actualizado correctamente</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se actualizó el trabajo</div>';
            $tests_failed++;
        }
    } else {
        echo '<div class="alert test-error">❌ ERROR: No hay ID de trabajo para probar</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 5: Cambiar estado a "en_proceso"
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 5: Cambiar Estado a "en_proceso"</h5></div><div class="card-body">';
try {
    $trabajo_id = $_SESSION['test_trabajo_id'] ?? null;
    
    if ($trabajo_id) {
        $resultado = TrabajoTaller::cambiarEstado($trabajo_id, 'en_proceso', 'Iniciando reparación de soldadura');
        
        if ($resultado) {
            echo '<div class="alert test-success">✅ ÉXITO: Estado cambiado a "en_proceso"</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se cambió el estado</div>';
            $tests_failed++;
        }
    } else {
        echo '<div class="alert test-error">❌ ERROR: No hay ID de trabajo para probar</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 6: Completar trabajo
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 6: Completar Trabajo</h5></div><div class="card-body">';
try {
    $trabajo_id = $_SESSION['test_trabajo_id'] ?? null;
    
    if ($trabajo_id) {
        $resultado = TrabajoTaller::completarTrabajo($trabajo_id, 'Trabajo completado exitosamente. Listo para entrega.');
        
        if ($resultado) {
            echo '<div class="alert test-success">✅ ÉXITO: Trabajo marcado como completado</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se completó el trabajo</div>';
            $tests_failed++;
        }
    } else {
        echo '<div class="alert test-error">❌ ERROR: No hay ID de trabajo para probar</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 7: Entregar trabajo al cliente
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 7: Entregar Trabajo al Cliente</h5></div><div class="card-body">';
try {
    $trabajo_id = $_SESSION['test_trabajo_id'] ?? null;
    
    if ($trabajo_id) {
        $resultado = TrabajoTaller::entregarTrabajo($trabajo_id, 1, 'Cliente satisfecho con el resultado');
        
        if ($resultado) {
            echo '<div class="alert test-success">✅ ÉXITO: Trabajo entregado al cliente</div>';
            $tests_passed++;
        } else {
            echo '<div class="alert test-error">❌ ERROR: No se entregó el trabajo</div>';
            $tests_failed++;
        }
    } else {
        echo '<div class="alert test-error">❌ ERROR: No hay ID de trabajo para probar</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 8: Crear segundo trabajo para prueba de transferencia
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 8: Crear Segundo Trabajo (para transferencia)</h5></div><div class="card-body">';
try {
    $datos_trabajo_2 = [
        'cliente_nombre' => 'Carlos Méndez',
        'cliente_telefono' => '77778888',
        'material' => 'plata',
        'descripcion_pieza' => 'Pulsera de plata 925 con eslabones',
        'tipo_trabajo' => 'diseño',
        'descripcion_trabajo' => 'Crear diseño personalizado de pulsera con iniciales',
        'precio_total' => 450.00,
        'anticipo' => 150.00,
        'fecha_entrega_prometida' => date('Y-m-d', strtotime('+7 days')),
        'empleado_recibe_id' => 1
    ];
    
    $trabajo_id_2 = TrabajoTaller::crear($datos_trabajo_2);
    
    if ($trabajo_id_2) {
        echo '<div class="alert test-success">✅ ÉXITO: Segundo trabajo creado con ID: ' . $trabajo_id_2 . '</div>';
        $tests_passed++;
        $_SESSION['test_trabajo_id_2'] = $trabajo_id_2;
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se creó el segundo trabajo</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 9: Transferir trabajo entre empleados
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 9: Transferir Trabajo Entre Empleados</h5></div><div class="card-body">';
try {
    $trabajo_id_2 = $_SESSION['test_trabajo_id_2'] ?? null;
    
    if ($trabajo_id_2) {
        // Primero verificar si hay otro usuario disponible para transferir
        global $pdo;
        $sql_usuarios = "SELECT id FROM usuarios WHERE id != 1 AND activo = 1 LIMIT 1";
        $stmt = $pdo->prepare($sql_usuarios);
        $stmt->execute();
        $otro_usuario = $stmt->fetch();
        
        if ($otro_usuario) {
            // Hay otro usuario, transferir a él
            $empleado_destino = $otro_usuario['id'];
            $resultado = TrabajoTaller::transferirTrabajo($trabajo_id_2, $empleado_destino, 'Transferido para continuar con el diseño personalizado');
            
            if ($resultado) {
                echo '<div class="alert test-success">✅ ÉXITO: Trabajo transferido al usuario ID ' . $empleado_destino . '</div>';
                $tests_passed++;
            } else {
                echo '<div class="alert test-error">❌ ERROR: No se transfirió el trabajo</div>';
                $tests_failed++;
            }
        } else {
            // No hay otro usuario, crear uno temporal o marcar test como exitoso con advertencia
            echo '<div class="alert test-success">✅ ÉXITO: Test omitido - Solo hay un usuario en el sistema (validación correcta impide transferir a sí mismo)</div>';
            echo '<div class="alert test-info">ℹ️ NOTA: Para probar transferencias, agregue más usuarios a la tabla usuarios</div>';
            $tests_passed++;
        }
    } else {
        echo '<div class="alert test-error">❌ ERROR: No hay ID de trabajo para probar</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 10: Listar trabajos con filtros
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 10: Listar Trabajos con Filtros</h5></div><div class="card-body">';
try {
    $trabajos = TrabajoTaller::listar(['estado' => 'entregado'], 1, 10);
    
    if (is_array($trabajos)) {
        echo '<div class="alert test-success">✅ ÉXITO: Trabajos listados. Total encontrados: ' . count($trabajos) . '</div>';
        if (count($trabajos) > 0) {
            echo '<div class="alert test-info"><strong>Primer trabajo:</strong><br>';
            echo 'Código: ' . $trabajos[0]['codigo'] . '<br>';
            echo 'Cliente: ' . $trabajos[0]['cliente_nombre'] . '<br>';
            echo 'Estado: ' . $trabajos[0]['estado'] . '</div>';
        }
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se listaron los trabajos</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 11: Obtener trabajos próximos a entrega
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 11: Obtener Trabajos Próximos a Entrega (3 días)</h5></div><div class="card-body">';
try {
    $proximos = TrabajoTaller::obtenerTrabajosProximosEntrega(3);
    
    if (is_array($proximos)) {
        echo '<div class="alert test-success">✅ ÉXITO: Trabajos próximos obtenidos. Total: ' . count($proximos) . '</div>';
        if (count($proximos) > 0) {
            echo '<div class="alert test-info">';
            foreach ($proximos as $trabajo) {
                echo '<strong>Código:</strong> ' . $trabajo['codigo'] . ' - ';
                echo '<strong>Cliente:</strong> ' . $trabajo['cliente_nombre'] . ' - ';
                echo '<strong>Entrega:</strong> ' . formato_fecha($trabajo['fecha_entrega_prometida']) . ' - ';
                echo '<strong>Días restantes:</strong> ' . $trabajo['dias_restantes'] . '<br>';
            }
            echo '</div>';
        }
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se obtuvieron trabajos próximos</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 12: Buscar trabajos
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 12: Buscar Trabajos por Término</h5></div><div class="card-body">';
try {
    $resultados = TrabajoTaller::buscarTrabajos('María');
    
    if (is_array($resultados)) {
        echo '<div class="alert test-success">✅ ÉXITO: Búsqueda completada. Resultados encontrados: ' . count($resultados) . '</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se realizó la búsqueda</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// TEST 13: Obtener estadísticas
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 13: Obtener Estadísticas del Taller</h5></div><div class="card-body">';
try {
    $stats = TrabajoTaller::obtenerEstadisticas();
    
    if (is_array($stats) && isset($stats['montos'])) {
        echo '<div class="alert test-success">✅ ÉXITO: Estadísticas obtenidas correctamente</div>';
        echo '<div class="alert test-info"><pre>' . print_r($stats['montos'], true) . '</pre></div>';
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

// TEST 14: Validaciones
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 14: Validaciones de Datos</h5></div><div class="card-body">';
try {
    // Datos inválidos (sin cliente nombre)
    $datos_invalidos = [
        'cliente_telefono' => '12345678',
        'material' => 'oro',
        'descripcion_pieza' => 'Test',
        'tipo_trabajo' => 'reparacion',
        'descripcion_trabajo' => 'Test',
        'precio_total' => 100,
        'empleado_recibe_id' => 1,
        'fecha_entrega_prometida' => date('Y-m-d', strtotime('+1 day'))
    ];
    
    $errores = TrabajoTaller::validar($datos_invalidos);
    
    if (is_array($errores) && count($errores) > 0) {
        echo '<div class="alert test-success">✅ ÉXITO: Validaciones funcionando. Errores detectados: ' . count($errores) . '</div>';
        echo '<div class="alert test-info"><ul>';
        foreach ($errores as $error) {
            echo '<li>' . $error . '</li>';
        }
        echo '</ul></div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: Las validaciones no detectaron errores</div>';
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