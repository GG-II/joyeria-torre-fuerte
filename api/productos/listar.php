<?php
/**
 * ================================================
 * API: LISTAR PRODUCTOS
 * ================================================
 * Endpoint para obtener listado de productos con filtros opcionales
 * 
 * Método: GET
 * Autenticación: Requerida
 * Permisos: productos.ver
 * 
 * Parámetros GET (todos opcionales):
 * - categoria_id: ID de categoría para filtrar
 * - activo: 1 = solo activos, 0 = solo inactivos, null = todos
 * - busqueda: Término de búsqueda en nombre/código/descripción
 * - proveedor_id: ID de proveedor para filtrar
 * - es_por_peso: 1 = productos por peso, 0 = productos por unidad
 * - pagina: Número de página (default: 1)
 * - por_pagina: Items por página (default: 20)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "productos": [array de productos],
 *     "total": 50,
 *     "pagina": 1,
 *     "por_pagina": 20,
 *     "total_paginas": 3
 *   },
 *   "message": "50 producto(s) encontrado(s)"
 * }
 */

// ================================================
// INCLUDES
// ================================================
require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/producto.php';

// ================================================
// CONFIGURACIÓN DE RESPUESTA
// ================================================
header('Content-Type: application/json; charset=utf-8');

// ================================================
// VERIFICACIONES DE SEGURIDAD
// ================================================

// 1. Verificar autenticación
verificar_api_autenticacion();

// 2. Verificar método HTTP
validar_metodo_http('GET');

// 3. Verificar permisos (productos.ver)
verificar_api_permiso('productos', 'ver');

// ================================================
// LÓGICA DEL ENDPOINT
// ================================================

try {
    // Preparar filtros desde parámetros GET
    $filtros = [];
    
    // Filtro por categoría
    if (isset($_GET['categoria_id']) && $_GET['categoria_id'] > 0) {
        $filtros['categoria_id'] = (int)$_GET['categoria_id'];
    }
    
    // Filtro por estado activo
    if (isset($_GET['activo'])) {
        $filtros['activo'] = $_GET['activo'] === '1' || $_GET['activo'] === 'true' ? 1 : 0;
    }
    // Si no se especifica, el modelo por defecto solo trae activos
    
    // Filtro por búsqueda
    if (isset($_GET['busqueda']) && !empty(trim($_GET['busqueda']))) {
        $filtros['busqueda'] = trim($_GET['busqueda']);
    }
    
    // Filtro por proveedor
    if (isset($_GET['proveedor_id']) && $_GET['proveedor_id'] > 0) {
        $filtros['proveedor_id'] = (int)$_GET['proveedor_id'];
    }
    
    // Filtro por productos por peso
    if (isset($_GET['es_por_peso'])) {
        $filtros['es_por_peso'] = $_GET['es_por_peso'] === '1' || $_GET['es_por_peso'] === 'true' ? 1 : 0;
    }
    
    // Paginación
    $pagina = isset($_GET['pagina']) && $_GET['pagina'] > 0 ? (int)$_GET['pagina'] : 1;
    $por_pagina = isset($_GET['por_pagina']) && $_GET['por_pagina'] > 0 ? (int)$_GET['por_pagina'] : 20;
    
    // Llamar al método del modelo
    $productos = Producto::listar($filtros, $pagina, $por_pagina);
    
    // Contar total de productos con los mismos filtros
    $total = Producto::contar($filtros);
    
    // Calcular total de páginas
    $total_paginas = ceil($total / $por_pagina);
    
    // Preparar respuesta
    $respuesta = [
        'productos' => $productos,
        'total' => $total,
        'pagina' => $pagina,
        'por_pagina' => $por_pagina,
        'total_paginas' => $total_paginas
    ];
    
    // Responder con éxito
    responder_json(
        true, 
        $respuesta, 
        count($productos) . ' producto(s) encontrado(s) en página ' . $pagina . ' de ' . $total_paginas
    );
    
} catch (Exception $e) {
    // Capturar cualquier error y responder con formato JSON
    responder_json(
        false, 
        null, 
        'Error al listar productos: ' . $e->getMessage(), 
        'ERROR_LISTAR_PRODUCTOS'
    );
}