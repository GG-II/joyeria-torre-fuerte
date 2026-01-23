<?php
/**
 * ================================================
 * API: PRODUCTOS CON BAJO STOCK
 * ================================================
 * Endpoint para obtener productos con stock bajo o agotado
 * 
 * Método: GET
 * Autenticación: Requerida
 * 
 * Parámetros GET (opcionales):
 * - sucursal_id: ID de sucursal para filtrar
 * - limite: Número máximo de resultados (default: 50)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "productos": [...],
 *     "agotados": [...],
 *     "bajo_stock": [...],
 *     "resumen": {...}
 *   }
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
verificar_api_permiso('inventario', 'ver');

try {
    global $pdo;
    
    // Obtener parámetros
    $sucursal_id = obtener_get('sucursal_id', null, 'int');
    $limite = obtener_get('limite', 50, 'int');
    
    // Construir consulta CORREGIDA (stock_minimo está en inventario, no en productos)
    $sql = "SELECT p.id, 
                   p.codigo, 
                   p.nombre,
                   p.imagen,
                   c.nombre as categoria_nombre,
                   i.sucursal_id,
                   s.nombre as sucursal_nombre,
                   i.cantidad as stock_actual,
                   i.stock_minimo
            FROM productos p
            INNER JOIN inventario i ON p.id = i.producto_id
            INNER JOIN sucursales s ON i.sucursal_id = s.id
            LEFT JOIN categorias c ON p.categoria_id = c.id
            WHERE p.activo = 1 
            AND (i.cantidad <= i.stock_minimo OR i.cantidad = 0)";
    
    $params = [];
    
    // Filtrar por sucursal si se especifica
    if ($sucursal_id) {
        $sql .= " AND i.sucursal_id = ?";
        $params[] = $sucursal_id;
    }
    
    $sql .= " ORDER BY i.cantidad ASC, p.nombre ASC LIMIT ?";
    $params[] = $limite;
    
    // Ejecutar consulta
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $productos = $stmt->fetchAll();
    
    // Clasificar productos por estado
    $resultado = [
        'agotados' => [],
        'bajo_stock' => []
    ];
    
    foreach ($productos as $producto) {
        if ($producto['stock_actual'] == 0) {
            $resultado['agotados'][] = $producto;
        } else {
            $resultado['bajo_stock'][] = $producto;
        }
    }
    
    $total = count($productos);
    $total_agotados = count($resultado['agotados']);
    $total_bajo_stock = count($resultado['bajo_stock']);
    
    // Responder
    responder_json(
        true,
        [
            'productos' => $productos,
            'agotados' => $resultado['agotados'],
            'bajo_stock' => $resultado['bajo_stock'],
            'resumen' => [
                'total' => $total,
                'agotados' => $total_agotados,
                'bajo_stock' => $total_bajo_stock
            ]
        ],
        "{$total} producto(s) con stock bajo o agotado ({$total_agotados} agotados, {$total_bajo_stock} bajo stock)"
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al obtener productos con bajo stock: ' . $e->getMessage(),
        'ERROR_BAJO_STOCK'
    );
}