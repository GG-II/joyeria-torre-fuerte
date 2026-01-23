<?php
/**
 * ================================================
 * API: INVENTARIO POR SUCURSAL
 * ================================================
 * Endpoint para obtener el inventario completo de una sucursal
 * 
 * Método: GET
 * Autenticación: Requerida
 * Permisos: inventario.ver
 * 
 * Parámetros GET:
 * - sucursal_id: ID de la sucursal (requerido)
 * - categoria_id: Filtrar por categoría (opcional)
 * - stock_bajo: 1 para solo productos con stock bajo (opcional)
 * - busqueda: Término de búsqueda en código/nombre (opcional)
 * - pagina: Número de página (opcional, default: 1)
 * - por_pagina: Items por página (opcional, default: 20)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "inventario": [...],
 *     "total": 50,
 *     "pagina": 1,
 *     "por_pagina": 20,
 *     "total_paginas": 3,
 *     "resumen": {...}
 *   }
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/inventario.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('GET');
verificar_api_permiso('inventario', 'ver');

try {
    // Validar sucursal_id (requerido)
    if (!isset($_GET['sucursal_id']) || empty($_GET['sucursal_id'])) {
        responder_json(false, null, 'El ID de sucursal es requerido', 'SUCURSAL_REQUERIDA');
    }
    
    $sucursal_id = obtener_get('sucursal_id', null, 'int');
    
    // Verificar que la sucursal exista
    if (!db_exists('sucursales', 'id = ? AND activo = 1', [$sucursal_id])) {
        responder_json(false, null, 'La sucursal no existe o está inactiva', 'SUCURSAL_INVALIDA');
    }
    
    // Preparar filtros
    $filtros = [];
    
    if (isset($_GET['categoria_id']) && $_GET['categoria_id'] > 0) {
        $filtros['categoria_id'] = (int)$_GET['categoria_id'];
    }
    
    if (isset($_GET['stock_bajo']) && $_GET['stock_bajo'] == 1) {
        $filtros['stock_bajo'] = 1;
    }
    
    if (isset($_GET['busqueda']) && !empty(trim($_GET['busqueda']))) {
        $filtros['busqueda'] = trim($_GET['busqueda']);
    }
    
    // Paginación
    $pagina = obtener_get('pagina', 1, 'int');
    $por_pagina = obtener_get('por_pagina', 20, 'int');
    
    // Validar rango de paginación
    if ($pagina < 1) $pagina = 1;
    if ($por_pagina < 1 || $por_pagina > 100) $por_pagina = 20;
    
    // Obtener inventario
    $inventario = Inventario::listarPorSucursal($sucursal_id, $filtros, $pagina, $por_pagina);
    
    // Contar total (no hay método contar(), usar query directo)
    global $pdo;
    
    $where = ['i.sucursal_id = ?'];
    $params = [$sucursal_id];
    
    if (isset($filtros['categoria_id'])) {
        $where[] = 'p.categoria_id = ?';
        $params[] = $filtros['categoria_id'];
    }
    
    if (isset($filtros['stock_bajo'])) {
        $where[] = 'i.cantidad <= i.stock_minimo';
    }
    
    if (isset($filtros['busqueda'])) {
        $where[] = '(p.codigo LIKE ? OR p.nombre LIKE ?)';
        $termino = '%' . $filtros['busqueda'] . '%';
        $params[] = $termino;
        $params[] = $termino;
    }
    
    $where[] = 'p.activo = 1';
    $where_sql = implode(' AND ', $where);
    
    $sql_count = "SELECT COUNT(*) as total 
                  FROM inventario i
                  INNER JOIN productos p ON i.producto_id = p.id
                  WHERE $where_sql";
    
    $stmt = $pdo->prepare($sql_count);
    $stmt->execute($params);
    $total_result = $stmt->fetch();
    $total = (int)$total_result['total'];
    
    // Calcular total de páginas
    $total_paginas = ceil($total / $por_pagina);
    
    // Obtener resumen (productos con stock bajo)
    $resumen = [
        'total_productos' => $total,
        'con_stock_bajo' => 0,
        'agotados' => 0
    ];
    
    foreach ($inventario as $item) {
        if ($item['cantidad'] == 0) {
            $resumen['agotados']++;
        } elseif ($item['alerta_stock_bajo'] == 1) {
            $resumen['con_stock_bajo']++;
        }
    }
    
    // Preparar respuesta
    $respuesta = [
        'inventario' => $inventario,
        'total' => $total,
        'pagina' => $pagina,
        'por_pagina' => $por_pagina,
        'total_paginas' => $total_paginas,
        'resumen' => $resumen
    ];
    
    responder_json(
        true,
        $respuesta,
        count($inventario) . " producto(s) en inventario (página {$pagina} de {$total_paginas})"
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al obtener inventario: ' . $e->getMessage(),
        'ERROR_INVENTARIO'
    );
}