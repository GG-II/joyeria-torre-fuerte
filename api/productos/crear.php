<?php
/**
 * ================================================
 * API: CREAR PRODUCTO
 * ================================================
 * Endpoint para crear un nuevo producto
 * 
 * Método: POST
 * Autenticación: Requerida
 * Permisos: productos.crear
 * 
 * Parámetros POST requeridos:
 * - codigo: Código único del producto
 * - nombre: Nombre del producto
 * - categoria_id: ID de la categoría
 * 
 * Parámetros POST opcionales:
 * - codigo_barras: Código de barras
 * - descripcion: Descripción del producto
 * - proveedor_id: ID del proveedor
 * - es_por_peso: 1 o 0
 * - peso_gramos: Peso en gramos (requerido si es_por_peso = 1)
 * - largo_cm: Largo en centímetros
 * - imagen: Ruta de la imagen
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "id": 26,
 *     "producto": {datos del producto}
 *   },
 *   "message": "Producto creado exitosamente"
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
verificar_api_permiso('productos', 'crear');

try {
    // Validar campos requeridos
    $campos_requeridos = ['codigo', 'nombre', 'categoria_id'];
    validar_campos_requeridos($campos_requeridos, 'POST');
    
    // Preparar datos para crear
    $datos = [
        'codigo' => obtener_post('codigo', null, 'string'),
        'nombre' => obtener_post('nombre', null, 'string'),
        'categoria_id' => obtener_post('categoria_id', null, 'int'),
        'codigo_barras' => obtener_post('codigo_barras', null, 'string'),
        'descripcion' => obtener_post('descripcion', null, 'string'),
        'proveedor_id' => obtener_post('proveedor_id', null, 'int'),
        'es_por_peso' => obtener_post('es_por_peso', 0, 'int'),
        'peso_gramos' => obtener_post('peso_gramos', null, 'float'),
        'largo_cm' => obtener_post('largo_cm', null, 'float'),
        'imagen' => obtener_post('imagen', null, 'string')
    ];
    
    // Validar datos
    $errores = Producto::validar($datos);
    
    if (!empty($errores)) {
        responder_json(
            false,
            ['errores' => $errores],
            'Errores de validación',
            'VALIDACION_FALLIDA'
        );
    }
    
    // Crear producto
    $producto_id = Producto::crear($datos);
    
    if (!$producto_id) {
        throw new Exception('No se pudo crear el producto');
    }
    
    // Obtener el producto creado
    $producto = Producto::obtenerPorId($producto_id);
    
    // Responder con éxito
    responder_json(
        true,
        [
            'id' => $producto_id,
            'producto' => $producto
        ],
        'Producto creado exitosamente'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al crear producto: ' . $e->getMessage(),
        'ERROR_CREAR_PRODUCTO'
    );
}