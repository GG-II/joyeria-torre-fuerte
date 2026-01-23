<?php
/**
 * ================================================
 * API: RESUMEN DE MOVIMIENTOS POR PRODUCTO
 * ================================================
 * Endpoint para obtener resumen de movimientos agrupados por producto
 * 
 * Método: GET
 * Autenticación: Requerida
 * Permisos: inventario.ver
 * 
 * Parámetros GET (todos opcionales):
 * - sucursal_id: Filtrar por sucursal
 * - fecha_desde: Fecha inicio (YYYY-MM-DD)
 * - fecha_hasta: Fecha fin (YYYY-MM-DD)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": [
 *     {
 *       "id": 5,
 *       "nombre": "Anillo de Oro 18K",
 *       "codigo": "ANI-001",
 *       "total_movimientos": 45,
 *       "total_ingresos": 120,
 *       "total_salidas": 80,
 *       "total_ventas": 75
 *     },
 *     ...
 *   ],
 *   "message": "X producto(s) con movimientos"
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/movimiento_inventario.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('GET');
verificar_api_permiso('inventario', 'ver');

try {
    // Preparar filtros
    $filtros = array();
    
    if (isset($_GET['sucursal_id']) && !empty($_GET['sucursal_id'])) {
        $filtros['sucursal_id'] = (int)$_GET['sucursal_id'];
    }
    
    if (isset($_GET['fecha_desde']) && !empty($_GET['fecha_desde'])) {
        $filtros['fecha_desde'] = $_GET['fecha_desde'];
    }
    
    if (isset($_GET['fecha_hasta']) && !empty($_GET['fecha_hasta'])) {
        $filtros['fecha_hasta'] = $_GET['fecha_hasta'];
    }
    
    // Obtener resumen por producto
    $resumen = MovimientoInventario::obtenerResumenPorProducto($filtros);
    
    responder_json(
        true,
        $resumen,
        count($resumen) . ' producto(s) con movimientos'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al obtener resumen: ' . $e->getMessage(),
        'ERROR_RESUMEN'
    );
}
