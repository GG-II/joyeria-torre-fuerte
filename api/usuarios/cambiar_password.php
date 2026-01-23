<?php
/**
 * ================================================
 * API: CAMBIAR CONTRASEÑA
 * ================================================
 * Endpoint para cambiar la contraseña de un usuario
 * 
 * Método: POST
 * Autenticación: Requerida
 * 
 * Parámetros POST:
 * - id: ID del usuario (requerido)
 * - password_actual: Contraseña actual (requerido para validación)
 * - password_nueva: Nueva contraseña (requerido, min 6 caracteres)
 * - password_confirmacion: Confirmación de nueva contraseña (requerido)
 * 
 * IMPORTANTE: 
 * - El usuario puede cambiar su propia contraseña
 * - Los administradores pueden usar restablecer_password.php sin necesitar la contraseña actual
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "id": 123,
 *     "nombre": "Juan Pérez"
 *   },
 *   "message": "Contraseña cambiada exitosamente"
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/usuario.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('POST');

try {
    // Leer JSON body
    $json_input = file_get_contents('php://input');
    $datos = json_decode($json_input, true);
    
    // Fallback a POST
    if (json_last_error() !== JSON_ERROR_NONE || empty($datos)) {
        $datos = $_POST;
    }
    
    // Validar campos requeridos
    if (empty($datos['id'])) {
        responder_json(false, null, 'El ID del usuario es requerido', 'ID_REQUERIDO');
    }
    
    if (empty($datos['password_actual'])) {
        responder_json(false, null, 'La contraseña actual es requerida', 'PASSWORD_ACTUAL_REQUERIDO');
    }
    
    if (empty($datos['password_nueva'])) {
        responder_json(false, null, 'La nueva contraseña es requerida', 'PASSWORD_NUEVA_REQUERIDA');
    }
    
    if (empty($datos['password_confirmacion'])) {
        responder_json(false, null, 'La confirmación de contraseña es requerida', 'PASSWORD_CONFIRMACION_REQUERIDA');
    }
    
    $id = (int)$datos['id'];
    $password_actual = $datos['password_actual'];
    $password_nueva = $datos['password_nueva'];
    $password_confirmacion = $datos['password_confirmacion'];
    
    // Verificar que las contraseñas nuevas coincidan
    if ($password_nueva !== $password_confirmacion) {
        responder_json(false, null, 'Las contraseñas no coinciden', 'PASSWORDS_NO_COINCIDEN');
    }
    
    // Validar longitud de contraseña nueva
    if (strlen($password_nueva) < 6) {
        responder_json(false, null, 'La nueva contraseña debe tener al menos 6 caracteres', 'PASSWORD_MUY_CORTA');
    }
    
    // Verificar que el usuario existe
    $usuario = Usuario::obtenerPorId($id);
    
    if (!$usuario) {
        responder_json(false, null, 'El usuario no existe', 'USUARIO_NO_ENCONTRADO');
    }
    
    // Verificar que el usuario actual tiene permiso
    // Solo puede cambiar su propia contraseña o ser administrador
    $usuario_actual_id = usuario_actual_id();
    $es_admin = tiene_permiso('usuarios', 'editar');
    
    if ($id != $usuario_actual_id && !$es_admin) {
        responder_json(false, null, 'No tienes permiso para cambiar la contraseña de este usuario', 'PERMISO_DENEGADO');
    }
    
    // Cambiar contraseña
    $resultado = Usuario::cambiarPassword($id, $password_actual, $password_nueva);
    
    if (!$resultado) {
        throw new Exception('No se pudo cambiar la contraseña. Verifica que la contraseña actual sea correcta.');
    }
    
    responder_json(
        true,
        [
            'id' => $id,
            'nombre' => $usuario['nombre']
        ],
        'Contraseña cambiada exitosamente'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al cambiar contraseña: ' . $e->getMessage(),
        'ERROR_CAMBIAR_PASSWORD'
    );
}
