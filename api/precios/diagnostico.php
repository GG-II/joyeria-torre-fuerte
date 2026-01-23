<?php
/**
 * ================================================
 * API: DIAGNÓSTICO DE PRECIOS (TEMPORAL)
 * ================================================
 * Endpoint temporal para verificar la estructura de la tabla
 * 
 * USAR SOLO PARA DEBUGGING - ELIMINAR EN PRODUCCIÓN
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';

header('Content-Type: application/json; charset=utf-8');

verificar_api_autenticacion();
validar_metodo_http('GET');

try {
    // 1. Verificar si la tabla existe
    $tabla_existe = db_query_one("SHOW TABLES LIKE 'precios_producto'");
    
    if (!$tabla_existe) {
        responder_json(false, null, 'La tabla precios_producto no existe', 'TABLA_NO_EXISTE');
    }
    
    // 2. Ver estructura de la tabla
    $estructura = db_query("DESCRIBE precios_producto");
    
    // 3. Contar registros
    $conteo = db_query_one("SELECT COUNT(*) as total FROM precios_producto");
    
    // 4. Ver primeros 5 registros
    $registros = db_query("
        SELECT 
            pp.*,
            p.nombre as producto_nombre
        FROM precios_producto pp
        LEFT JOIN productos p ON pp.producto_id = p.id
        LIMIT 5
    ");
    
    // 5. Ver todos los IDs que existen
    $ids_existentes = db_query("SELECT id FROM precios_producto ORDER BY id");
    
    responder_json(true, array(
        'tabla_existe' => true,
        'estructura' => $estructura,
        'total_registros' => $conteo['total'],
        'primeros_5_registros' => $registros,
        'ids_existentes' => array_column($ids_existentes, 'id')
    ), 'Diagnóstico completado');
    
} catch (Exception $e) {
    responder_json(false, null, 'Error en diagnóstico: ' . $e->getMessage(), 'ERROR_DIAGNOSTICO');
}
