<?php
/**
 * ================================================
 * API: CONSULTAR STOCK DE PRODUCTO
 * ================================================
 * Obtiene el stock actual de un producto en una sucursal específica
 * 
 * Método: GET
 * Autenticación: Requerida
 * Permisos: inventario.ver
 * 
 * Parámetros GET:
 * - producto_id: ID del producto (requerido)
 * - sucursal_id: ID de la sucursal (requerido)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "producto_id": 10,
 *     "sucursal_id": 1,
 *     "stock_actual": 15,
 *     "stock_minimo": 5,
 *     "stock_maximo": 50
 *   }
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('GET');
verificar_api_permiso('inventario', 'ver');

try {
    // Validar parámetros requeridos
    if (!isset($_GET['producto_id']) || empty($_GET['producto_id'])) {
        responder_json(false, null, 'El ID del producto es requerido', 'PRODUCTO_REQUERIDO');
    }
    
    if (!isset($_GET['sucursal_id']) || empty($_GET['sucursal_id'])) {
        responder_json(false, null, 'El ID de la sucursal es requerido', 'SUCURSAL_REQUERIDA');
    }
    
    $producto_id = (int)$_GET['producto_id'];
    $sucursal_id = (int)$_GET['sucursal_id'];
    
    global $pdo;
    
    // Consultar stock
    $sql = "
        SELECT 
            i.producto_id,
            i.sucursal_id,
            i.stock_actual,
            i.stock_minimo,
            i.stock_maximo,
            p.nombre as producto_nombre,
            p.sku,
            s.nombre as sucursal_nombre
        FROM inventario i
        INNER JOIN productos p ON i.producto_id = p.id
        INNER JOIN sucursales s ON i.sucursal_id = s.id
        WHERE i.producto_id = ? 
          AND i.sucursal_id = ?
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$producto_id, $sucursal_id]);
    $stock = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$stock) {
        // No existe registro de inventario, devolver stock 0
        responder_json(
            true,
            [
                'producto_id' => $producto_id,
                'sucursal_id' => $sucursal_id,
                'stock_actual' => 0,
                'stock_minimo' => 0,
                'stock_maximo' => 0
            ],
            'Sin inventario registrado'
        );
    }
    
    responder_json(
        true,
        $stock,
        'Stock obtenido exitosamente'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al consultar stock: ' . $e->getMessage(),
        'ERROR_CONSULTAR_STOCK'
    );
}