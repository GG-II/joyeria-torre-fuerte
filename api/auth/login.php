<?php
/**
 * ================================================
 * API: LOGIN
 * ================================================
 * Endpoint para autenticación y generación de token
 * 
 * Método: POST
 * Autenticación: No requerida
 * 
 * Parámetros POST:
 * - email: Email del usuario
 * - password: Contraseña
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "token": "session_id_hash",
 *     "usuario": {datos del usuario}
 *   },
 *   "message": "Login exitoso"
 * }
 */

// ================================================
// INCLUDES
// ================================================
require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/api-helpers.php';

// ================================================
// CONFIGURACIÓN
// ================================================
header('Content-Type: application/json; charset=utf-8');

// ================================================
// VALIDACIONES
// ================================================

// Verificar método POST
validar_metodo_http('POST');

// Validar campos requeridos
validar_campos_requeridos(['email', 'password'], 'POST');

// ================================================
// LÓGICA DE LOGIN
// ================================================

try {
    // Obtener credenciales
    $email = obtener_post('email', null, 'email');
    $password = $_POST['password']; // Sin sanitizar para contraseñas
    
    // Intentar autenticar
    $usuario = intentar_login($email, $password);
    
    if (!$usuario) {
        responder_json(
            false, 
            null, 
            'Credenciales incorrectas', 
            'CREDENCIALES_INVALIDAS'
        );
    }
    
    // Iniciar sesión
    iniciar_sesion($usuario);
    
    // Obtener el session ID como token
    $token = session_id();
    
    // Preparar datos del usuario (sin contraseña)
    $datos_usuario = [
        'id' => $usuario['id'],
        'nombre' => $usuario['nombre'],
        'email' => $usuario['email'],
        'rol' => $usuario['rol'],
        'sucursal_id' => $usuario['sucursal_id'],
        'sucursal_nombre' => $usuario['sucursal_nombre']
    ];
    
    // Responder con éxito
    responder_json(
        true,
        [
            'token' => $token,
            'usuario' => $datos_usuario
        ],
        'Login exitoso. Bienvenido ' . $usuario['nombre']
    );
    
} catch (Exception $e) {
    responder_json(
        false, 
        null, 
        'Error al procesar login: ' . $e->getMessage(), 
        'ERROR_LOGIN'
    );
}