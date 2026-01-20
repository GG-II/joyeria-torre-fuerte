<?php
// ================================================
// CONFIGURACIÓN DEL SISTEMA - JOYERÍA TORRE FUERTE
// ================================================

// Entorno (development o production)
define('ENVIRONMENT', 'development');

// ================================================
// BASE DE DATOS
// ================================================
define('DB_HOST', 'localhost');
define('DB_PORT', '3307');  // ← IMPORTANTE: Puerto 3307
define('DB_NAME', 'joyeria_torre_fuerte');
define('DB_USER', 'root');
define('DB_PASS', '');  // XAMPP por defecto no tiene password

// ================================================
// RUTAS
// ================================================
define('BASE_URL', 'http://localhost/joyeria-torre-fuerte/');
define('ASSETS_URL', BASE_URL . 'assets/');

// ================================================
// CONFIGURACIÓN DE SESIONES
// ================================================
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
session_start();

// ================================================
// MANEJO DE ERRORES SEGÚN ENTORNO
// ================================================
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/logs/php-errors.log');
}
?>