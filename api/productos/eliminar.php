<?php
/**
 * API - ELIMINAR (DESACTIVAR) PRODUCTO
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
    // Leer JSON del body
    $json_input = file_get_contents('php://input');
    $datos_json = json_decode($json_input, true);
    
    // Merge con $_POST para compatibilidad
    if (json_last_error() === JSON_ERROR_NONE && !empty($datos_json)) {
        $_POST = array_merge($_POST, $datos_json);
    }
    
    // Validar campos requeridos
    validar_campos_requeridos(['id'], 'POST');
    
    $id = obtener_post('id', null, 'int');
    
    // Verificar que el producto existe
    $producto = Producto::obtenerPorId($id);
    
    if (!$producto) {
        responder_json(false, null, 'Producto no encontrado', 'PRODUCTO_NO_ENCONTRADO');
    }
    
    // Desactivar producto (soft delete)
    $resultado = Producto::eliminar($id);
    
    if (!$resultado) {
        responder_json(false, null, 'No se pudo desactivar el producto', 'ERROR_ELIMINACION');
    }
    
    // Respuesta exitosa
    responder_json(
        true,
        [
            'id' => $id,
            'producto' => $producto['nombre']
        ],
        'Producto desactivado exitosamente',
        'PRODUCTO_DESACTIVADO'
    );
    
} catch (Exception $e) {
    responder_json(false, null, $e->getMessage(), 'ERROR_ELIMINACION');
}
