<?php
/**
 * ================================================
 * API: CAMBIAR ESTADO DE PRECIO
 * ================================================
 * Endpoint para activar o desactivar un precio
 * 
 * Método: POST
 * Autenticación: Requerida
 * Permisos: precios.editar
 * 
 * Parámetros POST:
 * - id: ID del precio (requerido)
 * - accion: 'activar' o 'desactivar' (requerido)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "id": 123,
 *     "producto_nombre": "Anillo de Oro",
 *     "tipo_precio": "mayorista",
 *     "estado_anterior": "activo",
 *     "estado_nuevo": "inactivo"
 *   },
 *   "message": "Precio desactivado exitosamente"
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
    
    // Validar campos requeridos
    if (empty($datos['id'])) {
        responder_json(false, null, 'El ID del precio es requerido', 'ID_REQUERIDO');
    }
    
    if (empty($datos['accion'])) {
        responder_json(false, null, 'La acción es requerida (activar o desactivar)', 'ACCION_REQUERIDA');
    }
    
    $id = (int)$datos['id'];
    $accion = strtolower($datos['accion']);
    
    // Validar acción
    if (!in_array($accion, array('activar', 'desactivar'))) {
        responder_json(false, null, 'Acción inválida. Use: activar o desactivar', 'ACCION_INVALIDA');
    }
    
    // Verificar que el precio existe
    $precio = PrecioProducto::obtenerPorId($id);
    
    if (!$precio) {
        responder_json(false, null, 'El precio no existe', 'PRECIO_NO_ENCONTRADO');
    }
    
    $estado_anterior = $precio['activo'] == 1 ? 'activo' : 'inactivo';
    
    // Ejecutar acción
    if ($accion === 'activar') {
        if ($precio['activo'] == 1) {
            responder_json(false, null, 'El precio ya está activo', 'YA_ACTIVO');
        }
        
        $resultado = PrecioProducto::activar($id);
        $estado_nuevo = 'activo';
        $mensaje = 'Precio activado exitosamente';
        
    } else {
        if ($precio['activo'] == 0) {
            responder_json(false, null, 'El precio ya está inactivo', 'YA_INACTIVO');
        }
        
        $resultado = PrecioProducto::desactivar($id);
        $estado_nuevo = 'inactivo';
        $mensaje = 'Precio desactivado exitosamente';
    }
    
    if (!$resultado) {
        throw new Exception('No se pudo cambiar el estado del precio');
    }
    
    // Responder
    responder_json(
        true,
        array(
            'id' => $id,
            'producto_id' => $precio['producto_id'],
            'producto_nombre' => $precio['producto_nombre'],
            'tipo_precio' => $precio['tipo_precio'],
            'estado_anterior' => $estado_anterior,
            'estado_nuevo' => $estado_nuevo
        ),
        $mensaje
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al cambiar estado del precio: ' . $e->getMessage(),
        'ERROR_CAMBIAR_ESTADO'
    );
}
