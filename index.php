<?php
require_once 'config.php';
require_once 'includes/db.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión - Joyería Torre Fuerte</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #007bff;
            padding-bottom: 10px;
        }
        .status {
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .links {
            margin-top: 30px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 5px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏪 Sistema de Gestión - Joyería Torre Fuerte</h1>
        
        <?php
        // Verificar conexión
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "'");
            $result = $stmt->fetch();
            ?>
            <div class="status success">
                <strong>✅ Sistema Configurado Correctamente</strong>
                <ul>
                    <li>Base de datos: <strong><?= DB_NAME ?></strong></li>
                    <li>Tablas creadas: <strong><?= $result['total'] ?></strong></li>
                    <li>Versión PHP: <strong><?= phpversion() ?></strong></li>
                </ul>
            </div>
            <?php
        } catch (PDOException $e) {
            ?>
            <div class="status error">
                <strong>❌ Error de conexión</strong>
                <p><?= $e->getMessage() ?></p>
            </div>
            <?php
        }
        ?>
        
        <div class="status info">
            <strong>📌 Estado del Proyecto</strong>
            <ul>
                <li>Fase actual: <strong>Fase 0 - Planificación y Diseño</strong></li>
                <li>Progreso: <strong>Día 4 - Ambiente de Desarrollo</strong></li>
                <li>Siguiente: <strong>Día 3 - Wireframes de Interfaz</strong></li>
            </ul>
        </div>
        
        <div class="links">
            <h3>🔗 Enlaces Útiles</h3>
            <a href="test-conexion.php" class="btn">🧪 Test de Conexión</a>
            <a href="http://localhost/phpmyadmin" class="btn" target="_blank">📊 phpMyAdmin</a>
        </div>
        
        <hr style="margin: 30px 0;">
        
        <p><small>
            <strong>Ubicación del proyecto:</strong> <?= __DIR__ ?><br>
            <strong>Desarrollador:</strong> [Tu Nombre]<br>
            <strong>Cliente:</strong> Joyería Torre Fuerte
        </small></p>
    </div>
</body>
</html>