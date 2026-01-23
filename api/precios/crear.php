<?php
/**
 * ================================================
 * API: CREAR PRECIO DE PRODUCTO
 * ================================================
 * Endpoint para crear un nuevo precio para un producto
 * 
 * Método: POST
 * Autenticación: Requerida
 * Permisos: precios.crear
 * 
 * Parámetros POST requeridos:
 * - producto_id: ID del producto
 * - tipo_precio: publico, mayorista, descuento, especial
 * - precio: Precio (mayor a 0)
 * 
 * Parámetros POST opcionales:
 * - activo: 1 o 0 (default: 1)
 * 
 * IMPORTANTE: Solo puede existir UN precio de cada tipo por producto
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "id": 123,
 *     "precio": {...}
 *   },
 *   "message": "Precio creado exitosamente"
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
verificar_api_permiso('precios', 'crear');

try {
    // Leer JSON body
    $json_input = file_get_contents('php://input');
    $datos = json_decode($json_input, true);
    
    // Fallback a POST
    if (json_last_error() !== JSON_ERROR_NONE || empty($datos)) {
        $datos = $_POST;
    }
    
    // Validar campos requeridos
    if (empty($datos['producto_id'])) {
        responder_json(false, null, 'El ID del producto es requerido', 'CAMPO_REQUERIDO');
    }
    
    if (empty($datos['tipo_precio'])) {
        responder_json(false, null, 'El tipo de precio es requerido', 'CAMPO_REQUERIDO');
    }
    
    if (!isset($datos['precio']) || $datos['precio'] === '') {
        responder_json(false, null, 'El precio es requerido', 'CAMPO_REQUERIDO');
    }
    
    // Validar producto_id
    $producto_id = (int)$datos['producto_id'];
    if ($producto_id <= 0) {
        responder_json(false, null, 'El ID del producto no es válido', 'PRODUCTO_ID_INVALIDO');
    }
    
    // Validar que el producto existe
    $producto = db_query_one("SELECT id, nombre FROM productos WHERE id = ?", [$producto_id]);
    if (!$producto) {
        responder_json(false, null, 'El producto no existe', 'PRODUCTO_NO_ENCONTRADO');
    }
    
    // Validar tipo de precio
    $tipo_precio = strtolower($datos['tipo_precio']);
    $tipos_validos = array('publico', 'mayorista', 'descuento', 'especial');
    
    if (!in_array($tipo_precio, $tipos_validos)) {
        responder_json(
            false, 
            null, 
            'Tipo de precio inválido. Use: ' . implode(', ', $tipos_validos), 
            'TIPO_PRECIO_INVALIDO'
        );
    }
    
    // Validar precio
    $precio = floatval($datos['precio']);
    if ($precio <= 0) {
        responder_json(false, null, 'El precio debe ser mayor a 0', 'PRECIO_INVALIDO');
    }
    
    // Verificar que no existe ya este tipo de precio para el producto
    $precio_existente = PrecioProducto::obtenerPorProductoYTipo($producto_id, $tipo_precio);
    if ($precio_existente) {
        responder_json(
            false, 
            null, 
            "Ya existe un precio tipo '{$tipo_precio}' para este producto. Use el endpoint de editar para modificarlo.", 
            'PRECIO_YA_EXISTE'
        );
    }
    
    // Preparar datos del precio
    $datos_precio = array(
        'producto_id' => $producto_id,
        'tipo_precio' => $tipo_precio,
        'precio' => $precio,
        'activo' => isset($datos['activo']) ? (int)$datos['activo'] : 1
    );
    
    // Crear precio
    $precio_id = PrecioProducto::crear($datos_precio);
    
    if (!$precio_id) {
        throw new Exception('No se pudo crear el precio');
    }
    
    // Obtener precio creado
    $precio_creado = PrecioProducto::obtenerPorId($precio_id);
    
    responder_json(
        true,
        array(
            'id' => $precio_id,
            'precio' => $precio_creado
        ),
        'Precio creado exitosamente'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al crear precio: ' . $e->getMessage(),
        'ERROR_CREAR_PRECIO'
    );
}
