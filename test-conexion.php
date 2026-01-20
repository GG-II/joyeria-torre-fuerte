<?php
require_once 'config.php';
require_once 'includes/db.php';

echo "<h1>🧪 Test de Configuración</h1>";

// Test 1: Versión de PHP
echo "<h2>✅ PHP</h2>";
echo "<p>Versión: " . phpversion() . "</p>";

// Test 2: Conexión a BD
echo "<h2>✅ Base de Datos</h2>";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total_tablas FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "'");
    $result = $stmt->fetch();
    echo "<p style='color: green;'>✅ Conexión exitosa</p>";
    echo "<p>Base de datos: <strong>" . DB_NAME . "</strong></p>";
    echo "<p>Puerto: <strong>" . DB_PORT . "</strong></p>";
    echo "<p>Tablas creadas: <strong>" . $result['total_tablas'] . "</strong></p>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

// Test 3: Listar algunas tablas
echo "<h2>📋 Tablas de la Base de Datos</h2>";
try {
    $stmt = $pdo->query("SHOW TABLES");
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<ul>";
    foreach ($tablas as $tabla) {
        echo "<li>$tabla</li>";
    }
    echo "</ul>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error al listar tablas: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>Ruta del proyecto:</strong> " . __DIR__ . "</p>";
?>