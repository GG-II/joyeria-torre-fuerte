<?php
/**
 * ================================================
 * API: LISTAR PRECIOS DE PRODUCTOS
 * ================================================
 * Endpoint para obtener listado de precios con filtros
 * 
 * Método: GET
 * Autenticación: Requerida
 * Permisos: precios.ver
 * 
 * Parámetros GET (todos opcionales):
 * - tipo_precio: publico, mayorista, descuento, especial
 * - activo: 1 = activos, 0 = inactivos
 * - producto_id: ID del producto específico
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": [...],
 *   "message": "X precio(s) encontrado(s)"
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/precio_producto.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('GET');
verificar_api_permiso('precios', 'ver');

try {
    // Preparar filtros
    $filtros = array();
    
    if (isset($_GET['tipo_precio']) && !empty($_GET['tipo_precio'])) {
        $tipo = strtolower($_GET['tipo_precio']);
        
        // Validar tipo de precio
        $tipos_validos = array('publico', 'mayorista', 'descuento', 'especial');
        if (in_array($tipo, $tipos_validos)) {
            $filtros['tipo_precio'] = $tipo;
        }
    }
    
    if (isset($_GET['activo'])) {
        $filtros['activo'] = $_GET['activo'] === '1' ? 1 : 0;
    }
    
    if (isset($_GET['producto_id']) && !empty($_GET['producto_id'])) {
        $filtros['producto_id'] = (int)$_GET['producto_id'];
    }
    
    // Obtener precios
    $precios = PrecioProducto::listar($filtros);
    
    responder_json(
        true,
        $precios,
        count($precios) . ' precio(s) encontrado(s)'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al listar precios: ' . $e->getMessage(),
        'ERROR_LISTAR_PRECIOS'
    );
}
