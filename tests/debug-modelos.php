<?php
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

echo "<h1>DEBUG CAJA</h1>";

// 1. Verificar cajas abiertas actuales
echo "<h2>1. Cajas Abiertas en BD</h2>";
global $pdo;

$cajas_abiertas = $pdo->query(
    "SELECT c.*, u.nombre as usuario_nombre, s.nombre as sucursal_nombre
     FROM cajas c
     INNER JOIN usuarios u ON c.usuario_id = u.id
     INNER JOIN sucursales s ON c.sucursal_id = s.id
     WHERE c.estado = 'abierta'
     ORDER BY c.id DESC"
)->fetchAll();

if (count($cajas_abiertas) > 0) {
    echo "<strong style='color:orange'>HAY " . count($cajas_abiertas) . " CAJA(S) ABIERTA(S):</strong><br>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Usuario</th><th>Sucursal</th><th>Fecha Apertura</th><th>Monto Inicial</th></tr>";
    foreach ($cajas_abiertas as $caja) {
        echo "<tr>";
        echo "<td>{$caja['id']}</td>";
        echo "<td>{$caja['usuario_nombre']} (ID: {$caja['usuario_id']})</td>";
        echo "<td>{$caja['sucursal_nombre']}</td>";
        echo "<td>{$caja['fecha_apertura']}</td>";
        echo "<td>Q {$caja['monto_inicial']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<strong style='color:green'>No hay cajas abiertas</strong><br>";
}

echo "<hr><h2>2. Intentar Abrir Caja con Usuario Actual</h2>";
echo "Usuario ID: " . usuario_actual_id() . "<br>";
echo "Usuario Nombre: " . usuario_actual_nombre() . "<br>";

try {
    $caja_id = Caja::abrirCaja(
        usuario_actual_id(),
        1,
        500.00
    );
    
    echo "Resultado: ";
    var_dump($caja_id);
    
    if ($caja_id) {
        echo "<br><strong style='color:green'>✅ CAJA ABIERTA - ID: $caja_id</strong>";
    } else {
        echo "<br><strong style='color:red'>❌ NO SE PUDO ABRIR CAJA</strong>";
        
        // Verificar por qué falló
        echo "<h3>Posibles causas:</h3>";
        
        $ya_tiene_abierta = $pdo->query(
            "SELECT id FROM cajas WHERE usuario_id = " . usuario_actual_id() . " AND estado = 'abierta'"
        )->fetch();
        
        if ($ya_tiene_abierta) {
            echo "<strong style='color:red'>CAUSA: Usuario ya tiene caja abierta (ID: {$ya_tiene_abierta['id']})</strong><br>";
        }
    }
} catch (Exception $e) {
    echo "<strong style='color:red'>EXCEPCIÓN: " . $e->getMessage() . "</strong>";
}

echo "<hr><h2>3. Solución: Cerrar Cajas Abiertas del Usuario de Test</h2>";
echo "<p>Si necesitas que los tests pasen, cierra primero las cajas abiertas del usuario:</p>";
echo "<pre>";
echo "UPDATE cajas \n";
echo "SET estado = 'cerrada', \n";
echo "    fecha_cierre = NOW(), \n";
echo "    monto_real = monto_inicial, \n";
echo "    monto_esperado = monto_inicial \n";
echo "WHERE usuario_id = 1 AND estado = 'abierta';";
echo "</pre>";

echo "<hr><h2>4. O Limpiar TODAS las Cajas para Tests Limpios</h2>";
echo "<pre>";
echo "-- Eliminar cajas de test (solo desarrollo)\n";
echo "TRUNCATE TABLE movimientos_caja;\n";
echo "TRUNCATE TABLE cajas;\n";
echo "</pre>";

echo "<hr><h2>5. Verificar Tabla de Errores del Sistema</h2>";
try {
    $errores = $pdo->query(
        "SELECT * FROM audit_log 
         WHERE accion LIKE '%caja%' 
         ORDER BY id DESC LIMIT 10"
    )->fetchAll();
    
    if (count($errores) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Fecha</th><th>Usuario</th><th>Acción</th><th>Detalles</th></tr>";
        foreach ($errores as $err) {
            echo "<tr>";
            echo "<td>{$err['fecha_hora']}</td>";
            echo "<td>{$err['usuario_id']}</td>";
            echo "<td>{$err['accion']}</td>";
            echo "<td>" . htmlspecialchars($err['detalles']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No hay errores de caja en audit_log";
    }
} catch (Exception $e) {
    echo "Error al consultar audit_log: " . $e->getMessage();
}
?>