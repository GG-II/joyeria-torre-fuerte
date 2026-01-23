<?php
/**
 * ================================================
 * API: EDITAR PRECIO DE PRODUCTO
 * ================================================
 * Endpoint para editar un precio existente
 * 
 * Método: POST
 * Autenticación: Requerida
 * Permisos: precios.editar
 * 
 * Parámetros POST requeridos:
 * - id: ID del precio
 * - precio: Nuevo precio (mayor a 0)
 * 
 * Parámetros POST opcionales:
 * - activo: 1 o 0
 * 
 * NOTA: No se puede cambiar el producto_id ni el tipo_precio.
 *       Para eso debe eliminar y crear uno nuevo.
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {...},
 *   "message": "Precio actualizado exitosamente"
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/precio_producto.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('POST');
verificar_api_permiso('precios', 'editar');

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
        responder_json(false, null, 'El ID del precio es requerido', 'ID_REQUERIDO');
    }
    
    $id = (int)$datos['id'];
    
    // Verificar que el precio existe
    $precio_actual = PrecioProducto::obtenerPorId($id);
    
    if (!$precio_actual) {
        responder_json(false, null, 'El precio no existe', 'PRECIO_NO_ENCONTRADO');
    }
    
    // Validar precio si se proporciona
    if (!isset($datos['precio']) || $datos['precio'] === '') {
        responder_json(false, null, 'El precio es requerido', 'CAMPO_REQUERIDO');
    }
    
    $precio = floatval($datos['precio']);
    if ($precio <= 0) {
        responder_json(false, null, 'El precio debe ser mayor a 0', 'PRECIO_INVALIDO');
    }
    
    // Preparar datos de actualización
    $datos_precio = array(
        'precio' => $precio,
        'activo' => isset($datos['activo']) ? (int)$datos['activo'] : $precio_actual['activo']
    );
    
    // Actualizar precio
    $resultado = PrecioProducto::editar($id, $datos_precio);
    
    if (!$resultado) {
        throw new Exception('No se pudo actualizar el precio');
    }
    
    // Obtener precio actualizado
    $precio_actualizado = PrecioProducto::obtenerPorId($id);
    
    responder_json(
        true,
        $precio_actualizado,
        'Precio actualizado exitosamente'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al editar precio: ' . $e->getMessage(),
        'ERROR_EDITAR_PRECIO'
    );
}
