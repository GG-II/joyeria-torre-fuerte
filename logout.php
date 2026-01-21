<?php
// ================================================
// CERRAR SESIÓN
// ================================================

require_once 'config.php';
require_once 'includes/db.php';
require_once 'includes/funciones.php';
require_once 'includes/auth.php';

// Cerrar sesión
cerrar_sesion();

// Redirigir al login con mensaje
mensaje_exito('Has cerrado sesión exitosamente');
redirigir('login.php');
?>