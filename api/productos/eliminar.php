<?php
/**
 * ================================================
 * API: ELIMINAR PRODUCTO
 * ================================================
 * Endpoint para eliminar (desactivar) un producto
 * 
 * Método: POST
 * Autenticación: Requerida
 * Permisos: productos.eliminar
 * 
 * Parámetros POST:
 * - id: ID del producto a eliminar
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": null,
 *   "message": "Producto eliminado exitosamente"
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/producto.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('POST');
verificar_api_permiso('productos', 'eliminar');

try {
    // Validar ID requerido
    validar_campos_requeridos(['id'], 'POST');
    
    $id = obtener_post('id', null, 'int');
    
    // Verificar que el producto exista
    if (!Producto::existe($id)) {
        responder_json(
            false,
            null,
            'El producto no existe',
            'PRODUCTO_NO_ENCONTRADO'
        );
    }
    
    // Eliminar producto (soft delete)
    $resultado = Producto::eliminar($id);
    
    if (!$resultado) {
        throw new Exception('No se pudo eliminar el producto');
    }
    
    // Responder con éxito
    responder_json(
        true,
        null,
        'Producto eliminado exitosamente'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al eliminar producto: ' . $e->getMessage(),
        'ERROR_ELIMINAR_PRODUCTO'
    );
}