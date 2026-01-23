<?php
/**
 * ================================================
 * API: CREAR USUARIO
 * ================================================
 * Endpoint para crear un nuevo usuario/empleado
 * 
 * Método: POST
 * Autenticación: Requerida
 * Permisos: usuarios.crear
 * 
 * Parámetros POST requeridos:
 * - nombre: Nombre del usuario (min 3 caracteres)
 * - email: Email válido y único
 * - password: Contraseña (min 6 caracteres)
 * - rol: administrador, dueño, vendedor, cajero, orfebre, publicidad
 * 
 * Parámetros POST opcionales:
 * - sucursal_id: ID de sucursal asignada
 * - activo: 1 (default) o 0
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "id": 123,
 *     "usuario": {...}
 *   },
 *   "message": "Usuario creado exitosamente"
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
verificar_api_permiso('usuarios', 'crear');

try {
    // Leer JSON body
    $json_input = file_get_contents('php://input');
    $datos = json_decode($json_input, true);
    
    // Fallback a POST
    if (json_last_error() !== JSON_ERROR_NONE || empty($datos)) {
        $datos = $_POST;
    }
    
    // Validar campos requeridos
    $campos_requeridos = ['nombre', 'email', 'password', 'rol'];
    
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
    
    // Preparar datos del usuario
    $datos_usuario = [
        'nombre' => $datos['nombre'],
        'email' => $datos['email'],
        'password' => $datos['password'],
        'rol' => $datos['rol'],
        'sucursal_id' => isset($datos['sucursal_id']) ? (int)$datos['sucursal_id'] : null,
        'activo' => isset($datos['activo']) ? (int)$datos['activo'] : 1
    ];
    
    // Crear usuario (el modelo valida internamente)
    $usuario_id = Usuario::crear($datos_usuario);
    
    if (!$usuario_id) {
        throw new Exception('No se pudo crear el usuario. Revise los logs para más detalles.');
    }
    
    // Obtener usuario creado (sin password)
    $usuario = Usuario::obtenerPorId($usuario_id);
    unset($usuario['password']);
    
    responder_json(
        true,
        [
            'id' => $usuario_id,
            'usuario' => $usuario
        ],
        'Usuario creado exitosamente'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al crear usuario: ' . $e->getMessage(),
        'ERROR_CREAR_USUARIO'
    );
}
