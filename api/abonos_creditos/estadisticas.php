<?php
/**
 * ================================================
 * API: ESTADÍSTICAS DE ABONOS A CRÉDITOS
 * ================================================
 * Endpoint para obtener estadísticas de cobranza y recuperación de cartera
 * 
 * Método: GET
 * Autenticación: Requerida
 * Permisos: creditos.ver
 * 
 * Parámetros GET (todos opcionales):
 * - cliente_id: Estadísticas de cliente específico
 * - fecha_desde: Fecha inicio (YYYY-MM-DD)
 * - fecha_hasta: Fecha fin (YYYY-MM-DD)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "total_abonos": 320,
 *     "creditos_con_abonos": 45,
 *     "clientes_con_abonos": 38,
 *     "monto_total": "125000.50",
 *     "monto_promedio": "390.63",
 *     "monto_minimo": "50.00",
 *     "monto_maximo": "5000.00",
 *     "abonos_efectivo": 220,
 *     "abonos_tarjeta": 100,
 *     "total_efectivo": "85000.25",
 *     "total_tarjeta": "40000.25"
 *   },
 *   "message": "Estadísticas obtenidas"
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/abono_credito.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('GET');
verificar_api_permiso('creditos', 'ver');

try {
    // Preparar filtros
    $filtros = array();
    
    if (isset($_GET['cliente_id']) && !empty($_GET['cliente_id'])) {
        $filtros['cliente_id'] = (int)$_GET['cliente_id'];
    }
    
    if (isset($_GET['fecha_desde']) && !empty($_GET['fecha_desde'])) {
        $filtros['fecha_desde'] = $_GET['fecha_desde'];
    }
    
    if (isset($_GET['fecha_hasta']) && !empty($_GET['fecha_hasta'])) {
        $filtros['fecha_hasta'] = $_GET['fecha_hasta'];
    }
    
    // Obtener estadísticas
    $estadisticas = AbonoCredito::obtenerEstadisticas($filtros);
    
    if (empty($estadisticas)) {
        responder_json(true, array(
            'total_abonos' => 0,
            'creditos_con_abonos' => 0,
            'clientes_con_abonos' => 0,
            'monto_total' => '0.00',
            'monto_promedio' => '0.00',
            'monto_minimo' => '0.00',
            'monto_maximo' => '0.00',
            'abonos_efectivo' => 0,
            'abonos_tarjeta' => 0,
            'total_efectivo' => '0.00',
            'total_tarjeta' => '0.00'
        ), 'No hay abonos en el período seleccionado');
    }
    
    // Formatear montos
    $estadisticas['monto_total'] = number_format($estadisticas['monto_total'], 2, '.', '');
    $estadisticas['monto_promedio'] = number_format($estadisticas['monto_promedio'], 2, '.', '');
    $estadisticas['monto_minimo'] = number_format($estadisticas['monto_minimo'], 2, '.', '');
    $estadisticas['monto_maximo'] = number_format($estadisticas['monto_maximo'], 2, '.', '');
    $estadisticas['total_efectivo'] = number_format($estadisticas['total_efectivo'], 2, '.', '');
    $estadisticas['total_tarjeta'] = number_format($estadisticas['total_tarjeta'], 2, '.', '');
    
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
