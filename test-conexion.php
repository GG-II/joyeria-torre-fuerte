<?php
// ================================================
// TEST DE CONEXIÓN A BASE DE DATOS
// ================================================

require_once 'config.php';
require_once 'includes/db.php';
require_once 'includes/funciones.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Conexión - <?php echo SISTEMA_NOMBRE; ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .card {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        h1 {
            color: #2c3e50;
            border-bottom: 3px solid #3498db;
            padding-bottom: 10px;
        }
        h2 {
            color: #34495e;
            margin-top: 30px;
        }
        .success {
            color: #27ae60;
            background: #d4edda;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #27ae60;
            margin: 10px 0;
        }
        .error {
            color: #c0392b;
            background: #f8d7da;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #c0392b;
            margin: 10px 0;
        }
        .info {
            color: #2980b9;
            background: #d1ecf1;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #2980b9;
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #3498db;
            color: white;
        }
        tr:hover {
            background: #f5f5f5;
        }
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-success {
            background: #27ae60;
            color: white;
        }
        .badge-info {
            background: #3498db;
            color: white;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>🔍 Test de Conexión - Sistema Joyería Torre Fuerte</h1>
        
        <?php
        // ================================================
        // 1. VERIFICAR CONEXIÓN PDO
        // ================================================
        echo "<h2>1. Conexión a Base de Datos</h2>";
        
        if ($pdo) {
            echo '<div class="success">✅ <strong>Conexión exitosa</strong> a la base de datos</div>';
            
            echo '<div class="info">';
            echo '<strong>Configuración:</strong><br>';
            echo 'Host: ' . DB_HOST . '<br>';
            echo 'Puerto: ' . DB_PORT . '<br>';
            echo 'Base de datos: ' . DB_NAME . '<br>';
            echo 'Usuario: ' . DB_USER . '<br>';
            echo 'Entorno: ' . ENVIRONMENT;
            echo '</div>';
        } else {
            echo '<div class="error">❌ <strong>Error:</strong> No se pudo conectar a la base de datos</div>';
            exit;
        }
        
        // ================================================
        // 2. LISTAR TODAS LAS TABLAS
        // ================================================
        echo "<h2>2. Tablas en la Base de Datos</h2>";
        
        try {
            $tablas = db_query("SHOW TABLES");
            
            if ($tablas) {
                echo '<div class="success">✅ Se encontraron <strong>' . count($tablas) . ' tablas</strong></div>';
                
                echo '<table>';
                echo '<thead><tr><th>#</th><th>Nombre de la Tabla</th><th>Registros</th></tr></thead>';
                echo '<tbody>';
                
                $i = 1;
                foreach ($tablas as $tabla) {
                    $nombre_tabla = array_values($tabla)[0];
                    
                    // Contar registros en cada tabla
                    $count_result = db_query_one("SELECT COUNT(*) as total FROM `$nombre_tabla`");
                    $total_registros = $count_result['total'];
                    
                    echo '<tr>';
                    echo '<td>' . $i . '</td>';
                    echo '<td><strong>' . $nombre_tabla . '</strong></td>';
                    echo '<td><span class="badge badge-info">' . $total_registros . ' registros</span></td>';
                    echo '</tr>';
                    
                    $i++;
                }
                
                echo '</tbody>';
                echo '</table>';
            } else {
                echo '<div class="error">❌ No se pudieron listar las tablas</div>';
            }
            
        } catch (Exception $e) {
            echo '<div class="error">❌ Error al listar tablas: ' . $e->getMessage() . '</div>';
        }
        
        // ================================================
        // 3. PROBAR FUNCIONES HELPER
        // ================================================
        echo "<h2>3. Prueba de Funciones Helper</h2>";
        
        echo '<table>';
        echo '<thead><tr><th>Función</th><th>Resultado</th></tr></thead>';
        echo '<tbody>';
        
        // Test 1: formato_dinero
        echo '<tr>';
        echo '<td><code>formato_dinero(1234.56)</code></td>';
        echo '<td>' . formato_dinero(1234.56) . '</td>';
        echo '</tr>';
        
        // Test 2: formato_fecha
        echo '<tr>';
        echo '<td><code>formato_fecha("2026-01-20")</code></td>';
        echo '<td>' . formato_fecha("2026-01-20") . '</td>';
        echo '</tr>';
        
        // Test 3: validar_email
        $email_test = "test@example.com";
        echo '<tr>';
        echo '<td><code>validar_email("' . $email_test . '")</code></td>';
        echo '<td>' . (validar_email($email_test) ? '<span class="badge badge-success">✅ Válido</span>' : '<span class="badge badge-error">❌ Inválido</span>') . '</td>';
        echo '</tr>';
        
        // Test 4: validar_telefono
        $tel_test = "12345678";
        echo '<tr>';
        echo '<td><code>validar_telefono("' . $tel_test . '")</code></td>';
        echo '<td>' . (validar_telefono($tel_test) ? '<span class="badge badge-success">✅ Válido</span>' : '<span class="badge badge-error">❌ Inválido</span>') . '</td>';
        echo '</tr>';
        
        // Test 5: generar_codigo
        echo '<tr>';
        echo '<td><code>generar_codigo(10)</code></td>';
        echo '<td><strong>' . generar_codigo(10) . '</strong></td>';
        echo '</tr>';
        
        echo '</tbody>';
        echo '</table>';
        
        // ================================================
        // 4. INFORMACIÓN DEL SISTEMA
        // ================================================
        echo "<h2>4. Información del Sistema</h2>";
        
        echo '<table>';
        echo '<thead><tr><th>Parámetro</th><th>Valor</th></tr></thead>';
        echo '<tbody>';
        
        echo '<tr><td>Nombre del Sistema</td><td><strong>' . SISTEMA_NOMBRE . '</strong></td></tr>';
        echo '<tr><td>Versión</td><td>' . SISTEMA_VERSION . '</td></tr>';
        echo '<tr><td>Entorno</td><td><span class="badge badge-' . (ENVIRONMENT === 'development' ? 'info' : 'success') . '">' . ENVIRONMENT . '</span></td></tr>';
        echo '<tr><td>Base URL</td><td>' . BASE_URL . '</td></tr>';
        echo '<tr><td>Zona Horaria</td><td>' . date_default_timezone_get() . '</td></tr>';
        echo '<tr><td>Fecha/Hora Actual</td><td>' . date('d/m/Y H:i:s') . '</td></tr>';
        echo '<tr><td>Versión PHP</td><td>' . phpversion() . '</td></tr>';
        echo '<tr><td>Items por Página</td><td>' . ITEMS_PER_PAGE . '</td></tr>';
        
        echo '</tbody>';
        echo '</table>';
        
        ?>
        
        <div class="success" style="margin-top: 30px;">
            <strong>🎉 ¡Todo funciona correctamente!</strong><br>
            La arquitectura base está lista para empezar a desarrollar los módulos.
        </div>
    </div>
</body>
</html>