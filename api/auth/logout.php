<?php
/**
 * ================================================
 * API: LOGOUT
 * ================================================
 * Endpoint para cerrar sesión y destruir token
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/api-helpers.php';

header('Content-Type: application/json; charset=utf-8');

// Verificar autenticación
verificar_api_autenticacion();

// Cerrar sesión
cerrar_sesion();

// Responder
responder_json(
    true,
    null,
    'Sesión cerrada correctamente'
);