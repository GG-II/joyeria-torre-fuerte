<?php
/**
 * ================================================
 * API: BUSCAR PRODUCTOS
 * ================================================
 * Endpoint para búsqueda rápida de productos (autocompletado)
 * 
 * Método: GET
 * Autenticación: Requerida
 * 
 * Parámetros GET:
 * - termino: Término de búsqueda (requerido)
 * - limite: Número máximo de resultados (default: 10)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": [array de productos],
 *   "message": "X producto(s) encontrado(s)"
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/producto.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('GET');
verificar_api_permiso('productos', 'ver');

try {
    // Validar campo requerido
    if (!isset($_GET['termino']) || empty(trim($_GET['termino']))) {
        responder_json(false, null, 'El término de búsqueda es requerido', 'TERMINO_REQUERIDO');
    }
    
    $termino = trim($_GET['termino']);
    $limite = isset($_GET['limite']) && $_GET['limite'] > 0 ? (int)$_GET['limite'] : 10;
    
    // Buscar productos
    $productos = Producto::buscar($termino, $limite);
    
    // Responder
    responder_json(
        true,
        $productos,
        count($productos) . ' producto(s) encontrado(s)'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al buscar productos: ' . $e->getMessage(),
        'ERROR_BUSCAR_PRODUCTOS'
    );
}