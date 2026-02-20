<?php
/**
 * ================================================
 * API: LISTAR INVENTARIO COMPLETO
 * ================================================
 * Endpoint para consultar inventario de todas las sucursales o filtrado
 * 
 * Método: GET
 * Autenticación: Requerida
 * Permisos: inventario.ver
 * 
 * Parámetros GET (todos opcionales):
 * - sucursal_id: Filtrar por sucursal específica
 * - categoria_id: Filtrar por categoría
 * - producto_id: Filtrar por producto específico
 * - stock_bajo: 1 para solo productos con stock bajo
 * - agotados: 1 para solo productos agotados
 * - busqueda: Término de búsqueda en código/nombre
 * - pagina: Número de página (default: 1)
 * - por_pagina: Items por página (default: 100, max: 500)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "inventario": [...],
 *     "total": 150,
 *     "pagina": 1,
 *     "por_pagina": 100,
 *     "total_paginas": 2
 *   },
 *   "message": "X producto(s) en inventario"
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
    global $pdo;
    
    // Preparar filtros
    $where = ['p.activo = 1'];
    $params = [];
    
    // Filtro por sucursal
    if (isset($_GET['sucursal_id']) && !empty($_GET['sucursal_id'])) {
        $where[] = 'i.sucursal_id = ?';
        $params[] = (int)$_GET['sucursal_id'];
    }
    
    // Filtro por categoría
    if (isset($_GET['categoria_id']) && $_GET['categoria_id'] > 0) {
        $where[] = 'p.categoria_id = ?';
        $params[] = (int)$_GET['categoria_id'];
    }
    
    // Filtro por producto
    if (isset($_GET['producto_id']) && $_GET['producto_id'] > 0) {
        $where[] = 'p.id = ?';
        $params[] = (int)$_GET['producto_id'];
    }
    
    // Filtro stock bajo
    if (isset($_GET['stock_bajo']) && $_GET['stock_bajo'] == 1) {
        $where[] = 'i.cantidad > 0 AND i.cantidad <= i.stock_minimo';
    }
    
    // Filtro agotados
    if (isset($_GET['agotados']) && $_GET['agotados'] == 1) {
        $where[] = 'i.cantidad = 0';
    }
    
    // Búsqueda por código o nombre
    if (isset($_GET['busqueda']) && !empty(trim($_GET['busqueda']))) {
        $where[] = '(p.codigo LIKE ? OR p.nombre LIKE ?)';
        $termino = '%' . trim($_GET['busqueda']) . '%';
        $params[] = $termino;
        $params[] = $termino;
    }
    
    $where_sql = implode(' AND ', $where);
    
    // Paginación
    $pagina = obtener_get('pagina', 1, 'int');
    $por_pagina = obtener_get('por_pagina', 100, 'int');
    
    if ($pagina < 1) $pagina = 1;
    if ($por_pagina < 1 || $por_pagina > 500) $por_pagina = 100;
    
    $offset = ($pagina - 1) * $por_pagina;
    
    // Contar total
    $sql_count = "SELECT COUNT(*) as total 
                  FROM inventario i
                  INNER JOIN productos p ON i.producto_id = p.id
                  WHERE $where_sql";
    
    $stmt = $pdo->prepare($sql_count);
    $stmt->execute($params);
    $total_result = $stmt->fetch();
    $total = (int)$total_result['total'];
    
    // Consultar inventario
    $sql = "SELECT 
                i.id,
                i.producto_id,
                i.sucursal_id,
                i.cantidad,
                i.stock_minimo,
                i.es_compartido,
                p.codigo,
                p.codigo_barras,
                p.nombre,
                p.descripcion,
                p.categoria_id,
                p.proveedor_id,
                p.peso_gramos,
                p.estilo,
                p.largo_cm,
                c.nombre as categoria_nombre,
                s.nombre as sucursal_nombre,
                prov.nombre as proveedor_nombre,
                CASE 
                    WHEN i.cantidad = 0 THEN 'agotado'
                    WHEN i.cantidad <= i.stock_minimo THEN 'bajo'
                    ELSE 'disponible'
                END as estado_stock,
                CASE 
                    WHEN i.cantidad <= i.stock_minimo THEN 1
                    ELSE 0
                END as alerta_stock_bajo
            FROM inventario i
            INNER JOIN productos p ON i.producto_id = p.id
            LEFT JOIN categorias c ON p.categoria_id = c.id
            LEFT JOIN sucursales s ON i.sucursal_id = s.id
            LEFT JOIN proveedores prov ON p.proveedor_id = prov.id
            WHERE $where_sql
            ORDER BY p.nombre, s.nombre
            LIMIT ? OFFSET ?";
    
    $params[] = $por_pagina;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $inventario = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calcular total de páginas
    $total_paginas = ceil($total / $por_pagina);
    
    // Si no hay filtro de sucursal, agrupar por producto y sumar cantidades
    $agrupado_por_sucursal = isset($_GET['sucursal_id']) && !empty($_GET['sucursal_id']);
    
    if (!$agrupado_por_sucursal && count($inventario) > 0) {
        // Agrupar por producto_id
        $productos_map = [];
        
        foreach ($inventario as $item) {
            $prod_id = $item['producto_id'];
            
            if (!isset($productos_map[$prod_id])) {
                $productos_map[$prod_id] = $item;
                $productos_map[$prod_id]['cantidad'] = 0;
                $productos_map[$prod_id]['sucursal_nombre'] = 'Todas';
            }
            
            $productos_map[$prod_id]['cantidad'] += (int)$item['cantidad'];
        }
        
        $inventario = array_values($productos_map);
    }
    
    // Preparar respuesta
    $respuesta = [
        'inventario' => $inventario,
        'total' => count($inventario), // Total después de agrupar
        'pagina' => $pagina,
        'por_pagina' => $por_pagina,
        'total_paginas' => $total_paginas
    ];
    
    responder_json(
        true,
        $respuesta,
        count($inventario) . " producto(s) en inventario"
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al listar inventario: ' . $e->getMessage(),
        'ERROR_LISTAR_INVENTARIO'
    );
}