<?php
/**
 * ================================================
 * API: EDITAR PROVEEDOR
 * ================================================
 * Endpoint para editar un proveedor existente
 * 
 * Método: POST
 * Autenticación: Requerida
 * Permisos: proveedores.editar
 * 
 * Parámetros POST requeridos:
 * - id: ID del proveedor a editar
 * - nombre: Nombre del proveedor
 * - telefono: Teléfono de contacto
 * 
 * Parámetros POST opcionales:
 * - empresa, contacto, email, direccion, productos_suministra
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {...},
 *   "message": "Proveedor actualizado exitosamente"
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/proveedor.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('POST');
verificar_api_permiso('proveedores', 'editar');

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
        responder_json(false, null, 'El ID del proveedor es requerido', 'ID_REQUERIDO');
    }
    
    $id = (int)$datos['id'];
    
    // Verificar que el proveedor existe
    $proveedor_actual = Proveedor::obtenerPorId($id);
    
    if (!$proveedor_actual) {
        responder_json(false, null, 'El proveedor no existe', 'PROVEEDOR_NO_ENCONTRADO');
    }
    
    // Preparar datos (mezclar actual + nuevo para permitir actualización parcial)
    $datos_proveedor = [
        'nombre' => isset($datos['nombre']) ? $datos['nombre'] : $proveedor_actual['nombre'],
        'telefono' => isset($datos['telefono']) ? $datos['telefono'] : $proveedor_actual['telefono'],
        'empresa' => isset($datos['empresa']) ? $datos['empresa'] : $proveedor_actual['empresa'],
        'contacto' => isset($datos['contacto']) ? $datos['contacto'] : $proveedor_actual['contacto'],
        'email' => isset($datos['email']) ? $datos['email'] : $proveedor_actual['email'],
        'direccion' => isset($datos['direccion']) ? $datos['direccion'] : $proveedor_actual['direccion'],
        'productos_suministra' => isset($datos['productos_suministra']) ? $datos['productos_suministra'] : $proveedor_actual['productos_suministra'],
        'activo' => isset($datos['activo']) ? $datos['activo'] : $proveedor_actual['activo']
    ];
    
    // Editar proveedor
    $resultado = Proveedor::editar($id, $datos_proveedor);
    
    if (!$resultado) {
        throw new Exception('No se pudo actualizar el proveedor. Revise los logs para más detalles.');
    }
    
    // Obtener proveedor actualizado
    $proveedor = Proveedor::obtenerPorId($id);
    
    responder_json(
        true,
        $proveedor,
        'Proveedor actualizado exitosamente'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al editar proveedor: ' . $e->getMessage(),
        'ERROR_EDITAR_PROVEEDOR'
    );
}
