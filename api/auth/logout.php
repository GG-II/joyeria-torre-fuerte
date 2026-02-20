<?php
// ================================================
// CERRAR SESIÓN
// ================================================

require_once 'config.php';
require_once 'includes/db.php';
require_once 'includes/funciones.php';
require_once 'includes/auth.php';

// Detectar si fue por timeout o logout manual
$por_timeout = isset($_GET['timeout']) && $_GET['timeout'] == '1';

// Cerrar sesión
cerrar_sesion();

// Mensaje según el motivo
if ($por_timeout) {
    mensaje_advertencia('Tu sesión fue cerrada por inactividad. Por favor inicia sesión nuevamente.');
} else {
    mensaje_exito('Has cerrado sesión exitosamente.');
}

// Redirigir al login
redirigir('login');
?>