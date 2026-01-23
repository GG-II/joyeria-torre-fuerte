<?php
/**
 * ================================================
 * API: CERRAR CAJA
 * ================================================
 * Endpoint para cerrar una caja abierta
 * 
 * Método: POST
 * Autenticación: Requerida
 * Permisos: caja.cerrar
 * 
 * Parámetros POST:
 * - caja_id: ID de la caja a cerrar (opcional, usa caja actual del usuario si no se provee)
 * - monto_real: Monto real contado en efectivo (requerido)
 * - observaciones: Observaciones del cierre (opcional)
 * 
 * VALIDACIONES:
 * - La caja debe existir
 * - La caja debe estar abierta (no cerrada)
 * - Monto real debe ser mayor o igual a 0
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "caja_id": 123,
 *     "fecha_apertura": "2026-01-22 08:00:00",
 *     "fecha_cierre": "2026-01-22 20:00:00",
 *     "monto_inicial": 500.00,
 *     "monto_esperado": 2500.00,
 *     "monto_real": 2480.00,
 *     "diferencia": -20.00,
 *     "resumen": {...}
 *   },
 *   "message": "Caja cerrada exitosamente"
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/caja.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('POST');
verificar_api_permiso('caja', 'cerrar');

try {
    // Leer JSON body
    $json_input = file_get_contents('php://input');
    $datos = json_decode($json_input, true);
    
    // Fallback a POST
    if (json_last_error() !== JSON_ERROR_NONE || empty($datos)) {
        $datos = $_POST;
    }
    
    // Validar monto_real requerido
    if (!isset($datos['monto_real'])) {
        responder_json(false, null, 'El campo monto_real es requerido', 'CAMPO_REQUERIDO');
    }
    
    $monto_real = (float)$datos['monto_real'];
    $observaciones = isset($datos['observaciones']) ? $datos['observaciones'] : null;
    
    // Validar monto real
    if ($monto_real < 0) {
        responder_json(false, null, 'El monto real no puede ser negativo', 'MONTO_INVALIDO');
    }
    
    // Obtener caja_id
    if (!empty($datos['caja_id'])) {
        $caja_id = (int)$datos['caja_id'];
    } else {
        // Usar caja actual del usuario
        $caja_actual = Caja::obtenerCajaActual(usuario_actual_id());
        
        if (!$caja_actual) {
            responder_json(false, null, 'No tienes una caja abierta', 'SIN_CAJA_ABIERTA');
        }
        
        $caja_id = $caja_actual['id'];
    }
    
    // Obtener información de la caja antes de cerrar
    $caja = Caja::obtenerPorId($caja_id);
    
    if (!$caja) {
        responder_json(false, null, 'La caja no existe', 'CAJA_NO_ENCONTRADA');
    }
    
    // Verificar que la caja esté abierta
    if ($caja['estado'] !== 'abierta') {
        responder_json(false, null, 'La caja ya está cerrada', 'CAJA_YA_CERRADA');
    }
    
    // Verificar que el usuario sea el dueño de la caja (o tenga permisos admin)
    $usuario_actual = usuario_actual_id();
    if ($caja['usuario_id'] != $usuario_actual && !usuario_tiene_permiso('caja', 'admin')) {
        responder_json(false, null, 'No tienes permiso para cerrar esta caja', 'PERMISO_DENEGADO');
    }
    
    // Calcular totales antes del cierre
    $totales_antes = Caja::calcularTotalesCaja($caja_id);
    $monto_esperado = $totales_antes['total_final'];
    $diferencia = $monto_real - $monto_esperado;
    
    // Cerrar caja
    $resultado = Caja::cerrarCaja($caja_id, $monto_real, $observaciones);
    
    if (!$resultado) {
        throw new Exception('No se pudo cerrar la caja. Revise los logs para más detalles.');
    }
    
    // Obtener caja cerrada
    $caja_cerrada = Caja::obtenerPorId($caja_id);
    
    // Preparar respuesta
    $respuesta = [
        'caja_id' => $caja_id,
        'sucursal_nombre' => $caja_cerrada['sucursal_nombre'],
        'usuario_nombre' => $caja_cerrada['usuario_nombre'],
        'fecha_apertura' => $caja_cerrada['fecha_apertura'],
        'fecha_cierre' => $caja_cerrada['fecha_cierre'],
        'monto_inicial' => (float)$caja_cerrada['monto_inicial'],
        'monto_esperado' => (float)$monto_esperado,
        'monto_real' => (float)$monto_real,
        'diferencia' => (float)$diferencia,
        'observaciones' => $observaciones,
        'resumen' => [
            'total_ingresos' => (float)$totales_antes['total_ingresos'],
            'total_egresos' => (float)$totales_antes['total_egresos'],
            'cantidad_movimientos' => (int)$totales_antes['cantidad_movimientos'],
            'desglose_ingresos' => $totales_antes['desglose_ingresos'],
            'desglose_egresos' => $totales_antes['desglose_egresos']
        ]
    ];
    
    $mensaje = "Caja cerrada exitosamente";
    
    if (abs($diferencia) > 0.01) {
        if ($diferencia > 0) {
            $mensaje .= " (Sobrante: Q " . number_format($diferencia, 2) . ")";
        } else {
            $mensaje .= " (Faltante: Q " . number_format(abs($diferencia), 2) . ")";
        }
    } else {
        $mensaje .= " (Sin diferencias)";
    }
    
    responder_json(
        true,
        $respuesta,
        $mensaje
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al cerrar caja: ' . $e->getMessage(),
        'ERROR_CERRAR_CAJA'
    );
}
