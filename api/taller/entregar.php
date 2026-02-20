<?php
/**
 * ================================================
 * API: ENTREGAR TRABAJO
 * ================================================
 * Endpoint para entregar un trabajo al cliente
 * 
 * Método: POST
 * Autenticación: Requerida
 * Permisos: taller.entregar
 * 
 * Parámetros POST:
 * - id: ID del trabajo (requerido)
 * - empleado_entrega_id: ID del empleado que entrega (requerido)
 * - observaciones: Observaciones (opcional)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "id": 123,
 *     "codigo": "TT-2026-0001",
 *     "saldo_pendiente": 150.00,
 *     "advertencia": "Entregado con saldo pendiente" (si aplica),
 *     "trabajo": {...}
 *   },
 *   "message": "Trabajo entregado exitosamente"
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/trabajo_taller.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('POST');
verificar_api_permiso('taller', 'entregar');

try {
    // Leer JSON body
    $json_input = file_get_contents('php://input');
    $datos = json_decode($json_input, true);
    
    // Fallback a POST
    if (json_last_error() !== JSON_ERROR_NONE || empty($datos)) {
        $datos = $_POST;
    }
    
    // Validar campos requeridos
    if (!isset($datos['id']) || empty($datos['id'])) {
        responder_json(false, null, 'El ID del trabajo es requerido', 'ID_REQUERIDO');
    }
    
    if (!isset($datos['empleado_entrega_id']) || empty($datos['empleado_entrega_id'])) {
        responder_json(false, null, 'El ID del empleado que entrega es requerido', 'EMPLEADO_REQUERIDO');
    }
    
    $id = (int)$datos['id'];
    $empleado_entrega_id = (int)$datos['empleado_entrega_id'];
    $observaciones = $datos['observaciones'] ?? '';
    
    // Verificar que el trabajo existe
    $trabajo = TrabajoTaller::obtenerPorId($id);
    
    if (!$trabajo) {
        responder_json(false, null, 'El trabajo no existe', 'TRABAJO_NO_ENCONTRADO');
    }
    
    // Verificar que esté completado
    if ($trabajo['estado'] !== 'completado') {
        responder_json(false, null, 'Solo se pueden entregar trabajos completados', 'ESTADO_INVALIDO');
    }
    
    // Verificar que el empleado existe
    $empleado = db_query_one("SELECT nombre FROM usuarios WHERE id = ?", [$empleado_entrega_id]);
    
    if (!$empleado) {
        responder_json(false, null, 'El empleado no existe', 'EMPLEADO_NO_ENCONTRADO');
    }
    
    // Verificar saldo pendiente
    $saldo_pendiente = $trabajo['saldo'];
    $advertencia = null;
    
    if ($saldo_pendiente > 0) {
        $advertencia = 'Entregado con saldo pendiente de Q ' . number_format($saldo_pendiente, 2);
    }
    
    // Entregar trabajo
    $resultado = TrabajoTaller::entregarTrabajo($id, $empleado_entrega_id, $observaciones);
    
    if (!$resultado) {
        throw new Exception('No se pudo entregar el trabajo');
    }
    
    // ========================================
    // REGISTRAR INGRESO EN CAJA
    // ========================================
    // Solo cobrar el saldo que quedaba pendiente al momento de entregar
    if ($saldo_pendiente > 0) {
        require_once '../../models/caja.php';
        
        $usuario_actual = usuario_actual_id();
        
        // Usar la caja abierta del usuario actual
        $caja_id = Caja::obtenerIdCajaAbierta($usuario_actual);
        
        if ($caja_id) {
            Caja::registrarMovimiento([
                'caja_id' => $caja_id,
                'tipo_movimiento' => 'ingreso_reparacion',
                'categoria' => 'ingreso',
                'concepto' => "Trabajo {$trabajo['codigo']} - {$trabajo['cliente_nombre']}",
                'monto' => $saldo_pendiente,
                'usuario_id' => $usuario_actual,
                'referencia_tipo' => 'trabajo_taller',
                'referencia_id' => $id
            ]);
        }
    }
    
    // Obtener trabajo actualizado
    $trabajo_actualizado = TrabajoTaller::obtenerPorId($id);
    
    $respuesta = [
        'id' => $id,
        'codigo' => $trabajo['codigo'],
        'saldo_pendiente' => $saldo_pendiente,
        'trabajo' => $trabajo_actualizado
    ];
    
    if ($advertencia) {
        $respuesta['advertencia'] = $advertencia;
    }
    
    $mensaje = 'Trabajo entregado exitosamente';
    if ($advertencia) {
        $mensaje .= ' (' . $advertencia . ')';
    }
    
    responder_json(true, $respuesta, $mensaje);
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al entregar trabajo: ' . $e->getMessage(),
        'ERROR_ENTREGAR_TRABAJO'
    );
}