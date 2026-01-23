<?php
/**
 * ================================================
 * API: OBTENER CAJA ACTUAL
 * ================================================
 * Endpoint para obtener el estado de la caja actual del usuario
 * 
 * Método: GET
 * Autenticación: Requerida
 * Permisos: caja.ver
 * 
 * Parámetros GET (todos opcionales):
 * - incluir_movimientos: Si se deben incluir los movimientos (default: false)
 * - limite_movimientos: Límite de movimientos a traer (default: 50)
 * 
 * Respuesta exitosa (con caja abierta):
 * {
 *   "success": true,
 *   "data": {
 *     "tiene_caja_abierta": true,
 *     "caja": {
 *       "id": 123,
 *       "sucursal_id": 1,
 *       "sucursal_nombre": "Los Arcos",
 *       "fecha_apertura": "2026-01-22 08:00:00",
 *       "monto_inicial": 500.00,
 *       "estado": "abierta",
 *       "totales": {
 *         "total_ingresos": 2000.00,
 *         "total_egresos": 300.00,
 *         "total_final": 2200.00,
 *         "cantidad_movimientos": 15
 *       }
 *     },
 *     "movimientos": [...]
 *   }
 * }
 * 
 * Respuesta sin caja abierta:
 * {
 *   "success": true,
 *   "data": {
 *     "tiene_caja_abierta": false
 *   },
 *   "message": "No tienes una caja abierta"
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/caja.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('GET');
verificar_api_permiso('caja', 'ver');

try {
    $usuario_id = usuario_actual_id();
    $incluir_movimientos = isset($_GET['incluir_movimientos']) && $_GET['incluir_movimientos'] === 'true';
    $limite_movimientos = isset($_GET['limite_movimientos']) ? (int)$_GET['limite_movimientos'] : 50;
    
    // Obtener caja actual
    $caja = Caja::obtenerCajaActual($usuario_id);
    
    if (!$caja) {
        // No hay caja abierta
        responder_json(
            true,
            ['tiene_caja_abierta' => false],
            'No tienes una caja abierta'
        );
    }
    
    // Preparar respuesta
    $respuesta = [
        'tiene_caja_abierta' => true,
        'caja' => [
            'id' => (int)$caja['id'],
            'sucursal_id' => (int)$caja['sucursal_id'],
            'sucursal_nombre' => $caja['sucursal_nombre'],
            'usuario_id' => (int)$caja['usuario_id'],
            'usuario_nombre' => $caja['usuario_nombre'],
            'fecha_apertura' => $caja['fecha_apertura'],
            'monto_inicial' => (float)$caja['monto_inicial'],
            'estado' => $caja['estado'],
            'totales' => [
                'monto_inicial' => (float)$caja['monto_inicial'],
                'total_ingresos' => (float)$caja['total_ingresos'],
                'total_egresos' => (float)$caja['total_egresos'],
                'total_final' => (float)$caja['total_final'],
                'cantidad_movimientos' => (int)$caja['cantidad_movimientos']
            ],
            'desglose' => [
                'ingresos' => $caja['desglose_ingresos'],
                'egresos' => $caja['desglose_egresos']
            ]
        ]
    ];
    
    // Incluir movimientos si se solicita
    if ($incluir_movimientos) {
        $movimientos = Caja::obtenerMovimientosCaja($caja['id']);
        
        // Limitar cantidad de movimientos
        if (count($movimientos) > $limite_movimientos) {
            $movimientos = array_slice($movimientos, 0, $limite_movimientos);
        }
        
        $respuesta['movimientos'] = $movimientos;
        $respuesta['total_movimientos_mostrados'] = count($movimientos);
    }
    
    responder_json(
        true,
        $respuesta,
        "Caja abierta desde " . date('d/m/Y H:i', strtotime($caja['fecha_apertura']))
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al obtener caja actual: ' . $e->getMessage(),
        'ERROR_OBTENER_CAJA'
    );
}
