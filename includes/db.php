<?php
// ================================================
// CONEXIÓN A BASE DE DATOS
// ================================================

require_once __DIR__ . '/../config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    if (ENVIRONMENT === 'development') {
        die("Error de conexión: " . $e->getMessage());
    } else {
        error_log("Error de conexión a BD: " . $e->getMessage());
        die("Error al conectar con la base de datos. Contacte al administrador.");
    }
}
?>