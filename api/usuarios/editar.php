<?php
/**
 * ================================================
 * API: EDITAR USUARIO
 * ================================================
 * Endpoint para editar un usuario/empleado existente
 * 
 * Método: POST
 * Autenticación: Requerida
 * Permisos: usuarios.editar
 * 
 * Parámetros POST requeridos:
 * - id: ID del usuario
 * - nombre: Nombre del usuario
 * - email: Email válido y único
 * - rol: administrador, dueño, vendedor, cajero, orfebre, publicidad
 * 
 * Parámetros POST opcionales:
 * - sucursal_id: ID de sucursal
 * - activo: 1 o 0
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {...},
 *   "message": "Usuario actualizado exitosamente"
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
    
    // Validar ID requerido
    if (empty($datos['id'])) {
        responder_json(false, null, 'El ID del usuario es requerido', 'ID_REQUERIDO');
    }
    
    $id = (int)$datos['id'];
    
    // Verificar que el usuario existe
    $usuario_actual = Usuario::obtenerPorId($id);
    
    if (!$usuario_actual) {
        responder_json(false, null, 'El usuario no existe', 'USUARIO_NO_ENCONTRADO');
    }
    
    // Validar campos requeridos
    $campos_requeridos = ['nombre', 'email', 'rol'];
    
    foreach ($campos_requeridos as $campo) {
        if (!isset($datos[$campo]) || empty($datos[$campo])) {
            responder_json(false, null, "El campo {$campo} es requerido", 'CAMPO_REQUERIDO');
        }
    }
    
    // Validar rol válido
    $roles_validos = ['administrador', 'dueño', 'vendedor', 'cajero', 'orfebre', 'publicidad'];
    if (!in_array($datos['rol'], $roles_validos)) {
        responder_json(false, null, 'Rol inválido. Use: ' . implode(', ', $roles_validos), 'ROL_INVALIDO');
    }
    
    // Preparar datos de actualización
    $datos_usuario = array(
        'nombre' => $datos['nombre'],
        'email' => $datos['email'],
        'rol' => $datos['rol'],
        'sucursal_id' => isset($datos['sucursal_id']) ? (int)$datos['sucursal_id'] : $usuario_actual['sucursal_id'],
        'activo' => isset($datos['activo']) ? (int)$datos['activo'] : $usuario_actual['activo']
    );
    
    // Actualizar usuario
    $resultado = Usuario::editar($id, $datos_usuario);
    
    if (!$resultado) {
        throw new Exception('No se pudo actualizar el usuario. Revise los logs para más detalles.');
    }
    
    // Obtener usuario actualizado (sin password)
    $usuario = Usuario::obtenerPorId($id);
    unset($usuario['password']);
    
    responder_json(
        true,
        $usuario,
        'Usuario actualizado exitosamente'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al editar usuario: ' . $e->getMessage(),
        'ERROR_EDITAR_USUARIO'
    );
}
