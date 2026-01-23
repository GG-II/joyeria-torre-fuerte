<?php
/**
 * ================================================
 * API: LISTAR CLIENTES
 * ================================================
 * Endpoint para obtener listado de clientes con filtros y paginación
 * 
 * Método: GET
 * Autenticación: Requerida
 * Permisos: clientes.ver
 * 
 * Parámetros GET (todos opcionales):
 * - tipo_cliente: 'publico' o 'mayorista'
 * - tipo_mercaderias: 'oro', 'plata', 'ambas'
 * - activo: 1 = solo activos, 0 = solo inactivos
 * - busqueda: Búsqueda en nombre, teléfono o NIT
 * - pagina: Número de página (default: 1)
 * - por_pagina: Items por página (default: 20)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "clientes": [...],
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
require_once '../../models/cliente.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('GET');
verificar_api_permiso('clientes', 'ver');

try {
    // Preparar filtros
    $filtros = [];
    
    // Filtro por tipo de cliente
    if (isset($_GET['tipo_cliente']) && !empty($_GET['tipo_cliente'])) {
        $tipo = obtener_get('tipo_cliente', null, 'string');
        if (in_array($tipo, ['publico', 'mayorista'])) {
            $filtros['tipo_cliente'] = $tipo;
        }
    }
    
    // Filtro por tipo de mercaderías
    if (isset($_GET['tipo_mercaderias']) && !empty($_GET['tipo_mercaderias'])) {
        $tipo_merc = obtener_get('tipo_mercaderias', null, 'string');
        if (in_array($tipo_merc, ['oro', 'plata', 'ambas'])) {
            $filtros['tipo_mercaderias'] = $tipo_merc;
        }
    }
    
    // Filtro por estado activo
    if (isset($_GET['activo'])) {
        $filtros['activo'] = $_GET['activo'] === '1' || $_GET['activo'] === 'true' ? 1 : 0;
    }
    
    // Filtro por búsqueda
    if (isset($_GET['busqueda']) && !empty(trim($_GET['busqueda']))) {
        $filtros['busqueda'] = trim($_GET['busqueda']);
    }
    
    // Paginación
    $pagina = obtener_get('pagina', 1, 'int');
    $por_pagina = obtener_get('por_pagina', 20, 'int');
    
    // Validar rango de paginación
    if ($pagina < 1) $pagina = 1;
    if ($por_pagina < 1 || $por_pagina > 100) $por_pagina = 20;
    
    // Obtener clientes
    $clientes = Cliente::listar($filtros, $pagina, $por_pagina);
    
    // Contar total
    $total = Cliente::contarTotal($filtros);
    
    // Calcular total de páginas
    $total_paginas = ceil($total / $por_pagina);
    
    // Preparar respuesta
    $respuesta = [
        'clientes' => $clientes,
        'total' => $total,
        'pagina' => $pagina,
        'por_pagina' => $por_pagina,
        'total_paginas' => $total_paginas
    ];
    
    responder_json(
        true,
        $respuesta,
        count($clientes) . " cliente(s) encontrado(s) (página {$pagina} de {$total_paginas})"
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al listar clientes: ' . $e->getMessage(),
        'ERROR_LISTAR_CLIENTES'
    );
}