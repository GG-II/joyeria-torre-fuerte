<?php
/**
 * ================================================
 * API: LISTAR VENTAS
 * ================================================
 * Endpoint para obtener listado de ventas con filtros y paginación
 * 
 * Método: GET
 * Autenticación: Requerida
 * Permisos: ventas.ver
 * 
 * Parámetros GET (todos opcionales):
 * - fecha_inicio: Fecha inicio (YYYY-MM-DD)
 * - fecha_fin: Fecha fin (YYYY-MM-DD)
 * - vendedor_id: ID del vendedor
 * - cliente_id: ID del cliente
 * - sucursal_id: ID de la sucursal
 * - estado: 'completada', 'anulada', 'apartada'
 * - tipo_venta: 'normal', 'credito', 'apartado'
 * - numero_venta: Búsqueda parcial por número
 * - pagina: Número de página (default: 1)
 * - por_pagina: Items por página (default: 20)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "ventas": [...],
 *     "total": 50,
 *     "pagina": 1,
 *     "por_pagina": 20,
 *     "total_paginas": 3
 *   }
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/venta.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('GET');
verificar_api_permiso('ventas', 'ver');

try {
    // Preparar filtros
    $filtros = [];
    
    // Filtro por rango de fechas
    if (isset($_GET['fecha_inicio']) && !empty($_GET['fecha_inicio'])) {
        $filtros['fecha_inicio'] = obtener_get('fecha_inicio', null, 'string');
    }
    
    if (isset($_GET['fecha_fin']) && !empty($_GET['fecha_fin'])) {
        $filtros['fecha_fin'] = obtener_get('fecha_fin', null, 'string');
    }
    
    // Filtro por vendedor
    if (isset($_GET['vendedor_id']) && !empty($_GET['vendedor_id'])) {
        $filtros['vendedor_id'] = obtener_get('vendedor_id', null, 'int');
    }
    
    // Filtro por cliente
    if (isset($_GET['cliente_id']) && !empty($_GET['cliente_id'])) {
        $filtros['cliente_id'] = obtener_get('cliente_id', null, 'int');
    }
    
    // Filtro por sucursal
    if (isset($_GET['sucursal_id']) && !empty($_GET['sucursal_id'])) {
        $filtros['sucursal_id'] = obtener_get('sucursal_id', null, 'int');
    }
    
    // Filtro por estado
    if (isset($_GET['estado']) && !empty($_GET['estado'])) {
        $estado = obtener_get('estado', null, 'string');
        if (in_array($estado, ['completada', 'anulada', 'apartada'])) {
            $filtros['estado'] = $estado;
        }
    }
    
    // Filtro por tipo de venta
    if (isset($_GET['tipo_venta']) && !empty($_GET['tipo_venta'])) {
        $tipo = obtener_get('tipo_venta', null, 'string');
        if (in_array($tipo, ['normal', 'credito', 'apartado'])) {
            $filtros['tipo_venta'] = $tipo;
        }
    }
    
    // Filtro por número de venta
    if (isset($_GET['numero_venta']) && !empty(trim($_GET['numero_venta']))) {
        $filtros['numero_venta'] = trim($_GET['numero_venta']);
    }
    
    // Paginación
    $pagina = obtener_get('pagina', 1, 'int');
    $por_pagina = obtener_get('por_pagina', 20, 'int');
    
    // Validar rango de paginación
    if ($pagina < 1) $pagina = 1;
    if ($por_pagina < 1 || $por_pagina > 100) $por_pagina = 20;
    
    // Obtener ventas
    $ventas = Venta::listar($filtros, $pagina, $por_pagina);
    
    // Contar total
    $total = Venta::contarTotal($filtros);
    
    // Calcular total de páginas
    $total_paginas = ceil($total / $por_pagina);
    
    // Preparar respuesta
    $respuesta = [
        'ventas' => $ventas,
        'total' => $total,
        'pagina' => $pagina,
        'por_pagina' => $por_pagina,
        'total_paginas' => $total_paginas
    ];
    
    responder_json(
        true,
        $respuesta,
        count($ventas) . " venta(s) encontrada(s) (página {$pagina} de {$total_paginas})"
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al listar ventas: ' . $e->getMessage(),
        'ERROR_LISTAR_VENTAS'
    );
}
