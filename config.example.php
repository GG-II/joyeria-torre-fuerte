<?php
// ================================================
// CONFIGURACIÓN DEL SISTEMA - JOYERÍA TORRE FUERTE
// PLANTILLA DE EJEMPLO - NO CONTIENE DATOS REALES
// ================================================
// 
// INSTRUCCIONES:
// 1. Copia este archivo como "config.php"
// 2. Ajusta los valores según tu entorno
// 3. NUNCA subas config.php a Git (está en .gitignore)
//

// ================================================
// ENTORNO
// ================================================
// Valores: 'development' o 'production'
define('ENVIRONMENT', 'development');

// ================================================
// BASE DE DATOS
// ================================================
define('DB_HOST', 'localhost');
define('DB_PORT', '3307');  // Ajustar según tu configuración
define('DB_NAME', 'joyeria_torre_fuerte');
define('DB_USER', 'root');
define('DB_PASS', '');  // Cambiar en producción

// ================================================
// RUTAS
// ================================================
define('BASE_URL', 'http://localhost/joyeria-torre-fuerte/');
define('ASSETS_URL', BASE_URL . 'assets/');
define('UPLOADS_URL', BASE_URL . 'uploads/');

// ================================================
// CONFIGURACIÓN DE SESIONES
// ================================================
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_lifetime', 0);
session_start();

// ================================================
// ZONA HORARIA
// ================================================
date_default_timezone_set('America/Guatemala');

// ================================================
// MANEJO DE ERRORES SEGÚN ENTORNO
// ================================================
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/logs/php-errors.log');
}

// ================================================
// CONFIGURACIÓN DE SUBIDA DE ARCHIVOS
// ================================================
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB en bytes
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('UPLOAD_PATH', __DIR__ . '/uploads/');

// ================================================
// CONFIGURACIÓN DEL SISTEMA
// ================================================
define('SISTEMA_NOMBRE', 'Joyería Torre Fuerte');
define('SISTEMA_VERSION', '1.0.0');
define('ITEMS_PER_PAGE', 20);

// ================================================
// SEGURIDAD
// ================================================
define('PASSWORD_MIN_LENGTH', 6);
define('SESSION_TIMEOUT', 3600); // 1 hora en segundos
?>