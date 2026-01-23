<?php
/**
 * ================================================
 * API: ESTADÍSTICAS DE MOVIMIENTOS DE INVENTARIO
 * ================================================
 * Endpoint para obtener estadísticas y análisis de movimientos
 * 
 * Método: GET
 * Autenticación: Requerida
 * Permisos: inventario.ver
 * 
 * Parámetros GET (todos opcionales):
 * - sucursal_id: Estadísticas de sucursal específica
 * - producto_id: Estadísticas de producto específico
 * - fecha_desde: Fecha inicio (YYYY-MM-DD)
 * - fecha_hasta: Fecha fin (YYYY-MM-DD)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "total_movimientos": 1250,
 *     "ingresos": 450,
 *     "salidas": 300,
 *     "ajustes": 50,
 *     "transferencias": 250,
 *     "ventas": 200,
 *     "total_ingresos": 5000,
 *     "total_salidas": 3500,
 *     "total_vendidos": 2800
 *   },
 *   "message": "Estadísticas obtenidas"
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
    
    if (isset($_GET['producto_id']) && !empty($_GET['producto_id'])) {
        $filtros['producto_id'] = (int)$_GET['producto_id'];
    }
    
    if (isset($_GET['fecha_desde']) && !empty($_GET['fecha_desde'])) {
        $filtros['fecha_desde'] = $_GET['fecha_desde'];
    }
    
    if (isset($_GET['fecha_hasta']) && !empty($_GET['fecha_hasta'])) {
        $filtros['fecha_hasta'] = $_GET['fecha_hasta'];
    }
    
    // Obtener estadísticas
    $estadisticas = MovimientoInventario::obtenerEstadisticas($filtros);
    
    if (empty($estadisticas)) {
        responder_json(true, array(
            'total_movimientos' => 0,
            'ingresos' => 0,
            'salidas' => 0,
            'ajustes' => 0,
            'transferencias' => 0,
            'ventas' => 0,
            'total_ingresos' => 0,
            'total_salidas' => 0,
            'total_vendidos' => 0
        ), 'No hay movimientos en el período seleccionado');
    }
    
    responder_json(
        true,
        $estadisticas,
        'Estadísticas obtenidas'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al obtener estadísticas: ' . $e->getMessage(),
        'ERROR_ESTADISTICAS'
    );
}
