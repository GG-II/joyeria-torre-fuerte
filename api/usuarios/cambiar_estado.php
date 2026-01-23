<?php
/**
 * ================================================
 * API: CAMBIAR ESTADO USUARIO
 * ================================================
 * Endpoint para activar o desactivar un usuario
 * 
 * Método: POST
 * Autenticación: Requerida
 * Permisos: usuarios.editar
 * 
 * Parámetros POST:
 * - id: ID del usuario (requerido)
 * - accion: 'activar' o 'desactivar' (requerido)
 * 
 * IMPORTANTE: No se puede desactivar el usuario actualmente logueado
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "id": 123,
 *     "nombre": "Juan Pérez",
 *     "estado_anterior": "activo",
 *     "estado_nuevo": "inactivo"
 *   },
 *   "message": "Usuario desactivado exitosamente"
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
verificar_api_permiso('usuarios', 'editar');

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
    
    if (empty($datos['accion'])) {
        responder_json(false, null, 'La acción es requerida (activar o desactivar)', 'ACCION_REQUERIDA');
    }
    
    $id = (int)$datos['id'];
    $accion = strtolower($datos['accion']);
    
    // Validar acción
    if (!in_array($accion, ['activar', 'desactivar'])) {
        responder_json(false, null, 'Acción inválida. Use: activar o desactivar', 'ACCION_INVALIDA');
    }
    
    // Verificar que el usuario existe
    $usuario = Usuario::obtenerPorId($id);
    
    if (!$usuario) {
        responder_json(false, null, 'El usuario no existe', 'USUARIO_NO_ENCONTRADO');
    }
    
    $estado_anterior = $usuario['activo'] == 1 ? 'activo' : 'inactivo';
    
    // Ejecutar acción
    if ($accion === 'activar') {
        if ($usuario['activo'] == 1) {
            responder_json(false, null, 'El usuario ya está activo', 'YA_ACTIVO');
        }
        
        $resultado = Usuario::activar($id);
        $estado_nuevo = 'activo';
        $mensaje = 'Usuario activado exitosamente';
        
    } else {
        if ($usuario['activo'] == 0) {
            responder_json(false, null, 'El usuario ya está inactivo', 'YA_INACTIVO');
        }
        
        // Verificar que no sea el usuario actual
        if ($id == usuario_actual_id()) {
            responder_json(false, null, 'No puedes desactivar tu propio usuario', 'NO_PUEDE_AUTODESACTIVAR');
        }
        
        $resultado = Usuario::desactivar($id);
        $estado_nuevo = 'inactivo';
        $mensaje = 'Usuario desactivado exitosamente';
    }
    
    if (!$resultado) {
        throw new Exception('No se pudo cambiar el estado del usuario');
    }
    
    // Responder
    responder_json(
        true,
        [
            'id' => $id,
            'nombre' => $usuario['nombre'],
            'estado_anterior' => $estado_anterior,
            'estado_nuevo' => $estado_nuevo
        ],
        $mensaje
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al cambiar estado del usuario: ' . $e->getMessage(),
        'ERROR_CAMBIAR_ESTADO'
    );
}
