<?php
/**
 * ================================================
 * API: ESTADÍSTICAS DE MOVIMIENTOS DE CAJA
 * ================================================
 * Endpoint para obtener estadísticas financieras de caja
 * 
 * Método: GET
 * Autenticación: Requerida
 * Permisos: caja.ver
 * 
 * Parámetros GET (todos opcionales):
 * - caja_id: Estadísticas de caja específica
 * - sucursal_id: Estadísticas de sucursal específica
 * - fecha_desde: Fecha inicio (YYYY-MM-DD)
 * - fecha_hasta: Fecha fin (YYYY-MM-DD)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "total_movimientos": 450,
 *     "total_ingresos_count": 320,
 *     "total_egresos_count": 130,
 *     "total_ingresos": 125000.50,
 *     "total_egresos": 45000.75,
 *     "saldo_neto": 79999.75,
 *     "promedio_ingreso": 390.63,
 *     "promedio_egreso": 346.16
 *   },
 *   "message": "Estadísticas obtenidas"
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/movimiento_caja.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('GET');
verificar_api_permiso('caja', 'ver');

try {
    // Preparar filtros
    $filtros = array();
    
    if (isset($_GET['caja_id']) && !empty($_GET['caja_id'])) {
        $filtros['caja_id'] = (int)$_GET['caja_id'];
    }
    
    if (isset($_GET['sucursal_id']) && !empty($_GET['sucursal_id'])) {
        $filtros['sucursal_id'] = (int)$_GET['sucursal_id'];
    }
    
    if (isset($_GET['fecha_desde']) && !empty($_GET['fecha_desde'])) {
        $filtros['fecha_desde'] = $_GET['fecha_desde'];
    }
    
    if (isset($_GET['fecha_hasta']) && !empty($_GET['fecha_hasta'])) {
        $filtros['fecha_hasta'] = $_GET['fecha_hasta'];
    }
    
    // Obtener estadísticas
    $estadisticas = MovimientoCaja::obtenerEstadisticas($filtros);
    
    if (empty($estadisticas)) {
        responder_json(true, array(
            'total_movimientos' => 0,
            'total_ingresos_count' => 0,
            'total_egresos_count' => 0,
            'total_ingresos' => 0,
            'total_egresos' => 0,
            'saldo_neto' => 0,
            'promedio_ingreso' => 0,
            'promedio_egreso' => 0
        ), 'No hay movimientos en el período seleccionado');
    }
    
    // Formatear montos
    $estadisticas['total_ingresos'] = number_format($estadisticas['total_ingresos'], 2, '.', '');
    $estadisticas['total_egresos'] = number_format($estadisticas['total_egresos'], 2, '.', '');
    $estadisticas['saldo_neto'] = number_format($estadisticas['saldo_neto'], 2, '.', '');
    $estadisticas['promedio_ingreso'] = number_format($estadisticas['promedio_ingreso'], 2, '.', '');
    $estadisticas['promedio_egreso'] = number_format($estadisticas['promedio_egreso'], 2, '.', '');
    
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
