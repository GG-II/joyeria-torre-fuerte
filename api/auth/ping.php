<?php
/**
 * ================================================
 * API: PING - RENOVAR SESIÓN
 * ================================================
 * El frontend llama este endpoint cuando el usuario
 * hace clic en "Continuar sesión" en el modal de timeout.
 * Actualiza ultima_actividad en la sesión PHP.
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/api-helpers.php';

header('Content-Type: application/json; charset=utf-8');

if (!esta_autenticado()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'mensaje' => 'Sesión expirada']);
    exit;
}

// Renovar timestamp de actividad
$_SESSION['ultima_actividad'] = time();

echo json_encode(['ok' => true, 'mensaje' => 'Sesión renovada']);