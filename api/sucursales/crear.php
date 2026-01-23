<?php
/**
 * ================================================
 * API: CREAR SUCURSAL
 * ================================================
 * Endpoint para crear una nueva sucursal
 * 
 * Método: POST
 * Autenticación: Requerida
 * Permisos: sucursales.crear
 * 
 * Parámetros POST requeridos:
 * - nombre: Nombre de la sucursal (min 3 caracteres)
 * - direccion: Dirección completa (min 10 caracteres)
 * 
 * Parámetros POST opcionales:
 * - telefono: Teléfono de contacto
 * - email: Email de la sucursal
 * - responsable_id: ID del usuario responsable
 * - activo: 1 o 0 (default: 1)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "id": 123,
 *     "sucursal": {...}
 *   },
 *   "message": "Sucursal creada exitosamente"
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/sucursal.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('POST');
verificar_api_permiso('sucursales', 'crear');

try {
    // Leer JSON body
    $json_input = file_get_contents('php://input');
    $datos = json_decode($json_input, true);
    
    // Fallback a POST
    if (json_last_error() !== JSON_ERROR_NONE || empty($datos)) {
        $datos = $_POST;
    }
    
    // Validar campos requeridos
    if (empty($datos['nombre'])) {
        responder_json(false, null, 'El nombre es requerido', 'CAMPO_REQUERIDO');
    }
    
    if (empty($datos['direccion'])) {
        responder_json(false, null, 'La dirección es requerida', 'CAMPO_REQUERIDO');
    }
    
    // Validar longitudes mínimas
    if (strlen($datos['nombre']) < 3) {
        responder_json(false, null, 'El nombre debe tener al menos 3 caracteres', 'NOMBRE_MUY_CORTO');
    }
    
    if (strlen($datos['direccion']) < 10) {
        responder_json(false, null, 'La dirección debe tener al menos 10 caracteres', 'DIRECCION_MUY_CORTA');
    }
    
    // Validar email si se proporciona
    if (!empty($datos['email']) && !filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
        responder_json(false, null, 'El email no es válido', 'EMAIL_INVALIDO');
    }
    
    // Validar responsable si se proporciona
    if (!empty($datos['responsable_id'])) {
        $usuario = db_query_one(
            "SELECT id FROM usuarios WHERE id = ? AND activo = 1",
            [(int)$datos['responsable_id']]
        );
        
        if (!$usuario) {
            responder_json(false, null, 'El usuario responsable no existe o está inactivo', 'RESPONSABLE_INVALIDO');
        }
    }
    
    // Preparar datos de la sucursal
    $datos_sucursal = array(
        'nombre' => $datos['nombre'],
        'direccion' => $datos['direccion'],
        'telefono' => isset($datos['telefono']) ? $datos['telefono'] : null,
        'email' => isset($datos['email']) ? $datos['email'] : null,
        'responsable_id' => isset($datos['responsable_id']) ? (int)$datos['responsable_id'] : null,
        'activo' => isset($datos['activo']) ? (int)$datos['activo'] : 1
    );
    
    // Crear sucursal (el modelo valida internamente)
    $sucursal_id = Sucursal::crear($datos_sucursal);
    
    if (!$sucursal_id) {
        throw new Exception('No se pudo crear la sucursal. Verifique que el nombre no esté duplicado.');
    }
    
    // Obtener sucursal creada
    $sucursal = Sucursal::obtenerPorId($sucursal_id);
    
    responder_json(
        true,
        array(
            'id' => $sucursal_id,
            'sucursal' => $sucursal
        ),
        'Sucursal creada exitosamente'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al crear sucursal: ' . $e->getMessage(),
        'ERROR_CREAR_SUCURSAL'
    );
}
