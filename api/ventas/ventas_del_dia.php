<?php
/**
 * ================================================
 * API: VENTAS DEL DÍA
 * ================================================
 * Endpoint para obtener las ventas del día con resumen
 * 
 * Método: GET
 * Autenticación: Requerida
 * Permisos: ventas.ver
 * 
 * Parámetros GET:
 * - sucursal_id: ID de la sucursal (requerido)
 * - fecha: Fecha a consultar (opcional, default: hoy, formato: YYYY-MM-DD)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "fecha": "2026-01-22",
 *     "sucursal": {...},
 *     "ventas": [...],
 *     "resumen": {
 *       "total_ventas": 15,
 *       "monto_total": 25000.50,
 *       "ticket_promedio": 1666.70,
 *       "por_tipo_venta": {...},
 *       "por_forma_pago": {...}
 *     }
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
    // Validar sucursal_id requerido
    if (!isset($_GET['sucursal_id']) || empty($_GET['sucursal_id'])) {
        responder_json(false, null, 'El ID de sucursal es requerido', 'SUCURSAL_REQUERIDA');
    }
    
    $sucursal_id = obtener_get('sucursal_id', null, 'int');
    $fecha = obtener_get('fecha', date('Y-m-d'), 'string');
    
    // Verificar que la sucursal existe
    $sucursal = db_query_one('SELECT id, nombre FROM sucursales WHERE id = ? AND activo = 1', [$sucursal_id]);
    
    if (!$sucursal) {
        responder_json(false, null, 'La sucursal no existe o está inactiva', 'SUCURSAL_INVALIDA');
    }
    
    // Obtener ventas del día
    $ventas = Venta::obtenerVentasDelDia($sucursal_id, $fecha);
    
    // Calcular resumen
    $resumen = [
        'total_ventas' => 0,
        'monto_total' => 0,
        'ticket_promedio' => 0,
        'por_tipo_venta' => [
            'normal' => ['cantidad' => 0, 'monto' => 0],
            'credito' => ['cantidad' => 0, 'monto' => 0],
            'apartado' => ['cantidad' => 0, 'monto' => 0]
        ],
        'por_forma_pago' => []
    ];
    
    foreach ($ventas as $venta) {
        $resumen['total_ventas']++;
        $resumen['monto_total'] += $venta['total'];
        
        // Por tipo de venta
        $tipo = $venta['tipo_venta'];
        if (isset($resumen['por_tipo_venta'][$tipo])) {
            $resumen['por_tipo_venta'][$tipo]['cantidad']++;
            $resumen['por_tipo_venta'][$tipo]['monto'] += $venta['total'];
        }
        
        // Por forma de pago (solo ventas normales)
        if ($venta['tipo_venta'] === 'normal') {
            $formas_pago = Venta::obtenerFormasPago($venta['id']);
            foreach ($formas_pago as $pago) {
                $forma = $pago['forma_pago'];
                if (!isset($resumen['por_forma_pago'][$forma])) {
                    $resumen['por_forma_pago'][$forma] = 0;
                }
                $resumen['por_forma_pago'][$forma] += $pago['monto'];
            }
        }
    }
    
    // Calcular ticket promedio
    if ($resumen['total_ventas'] > 0) {
        $resumen['ticket_promedio'] = round($resumen['monto_total'] / $resumen['total_ventas'], 2);
    }
    
    // Preparar respuesta
    $respuesta = [
        'fecha' => $fecha,
        'sucursal' => $sucursal,
        'ventas' => $ventas,
        'resumen' => $resumen
    ];
    
    responder_json(
        true,
        $respuesta,
        "{$resumen['total_ventas']} venta(s) del día " . date('d/m/Y', strtotime($fecha))
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al obtener ventas del día: ' . $e->getMessage(),
        'ERROR_VENTAS_DIA'
    );
}
