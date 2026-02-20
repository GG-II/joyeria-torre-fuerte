<?php
/**
 * ================================================
 * API: ACTUALIZAR TRABAJO (CON REGISTRO EN CAJA)
 * ================================================
 * Actualiza un trabajo y registra cambios de anticipo en caja
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/trabajo_taller.php';

header('Content-Type: application/json; charset=utf-8');

verificar_api_autenticacion();
validar_metodo_http('POST');
verificar_api_permiso('taller', 'editar');

try {
    $json_input = file_get_contents('php://input');
    $datos = json_decode($json_input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE || empty($datos)) {
        $datos = $_POST;
    }
    
    if (empty($datos['id'])) {
        responder_json(false, null, 'El ID del trabajo es requerido', 'ID_REQUERIDO');
    }
    
    $id = (int)$datos['id'];
    
    // Obtener trabajo actual ANTES de actualizar
    $trabajo_antes = TrabajoTaller::obtenerPorId($id);
    
    if (!$trabajo_antes) {
        responder_json(false, null, 'El trabajo no existe', 'TRABAJO_NO_ENCONTRADO');
    }
    
    $anticipo_antes = (float)$trabajo_antes['anticipo'];
    
    // Actualizar trabajo
    $resultado = TrabajoTaller::actualizar($id, $datos);
    
    if (!$resultado) {
        throw new Exception('No se pudo actualizar el trabajo');
    }
    
    // ========================================
    // REGISTRAR CAMBIO DE ANTICIPO EN CAJA
    // ========================================
    if (isset($datos['anticipo'])) {
        $anticipo_nuevo = (float)$datos['anticipo'];
        $diferencia = $anticipo_nuevo - $anticipo_antes;
        
        // Solo registrar si hubo un aumento (nuevo pago)
        if ($diferencia > 0) {
            require_once '../../models/caja.php';
            
            $usuario_actual = usuario_actual_id();
            
            // Usar la caja abierta del usuario actual
            $caja_id = Caja::obtenerIdCajaAbierta($usuario_actual);
            
            if ($caja_id) {
                Caja::registrarMovimiento([
                    'caja_id' => $caja_id,
                    'tipo_movimiento' => 'anticipo_trabajo',
                    'categoria' => 'ingreso',
                    'concepto' => "Anticipo trabajo {$trabajo_antes['codigo']} - {$trabajo_antes['cliente_nombre']}",
                    'monto' => $diferencia,
                    'usuario_id' => $usuario_actual,
                    'referencia_tipo' => 'trabajo_taller',
                    'referencia_id' => $id
                ]);
            }
        }
    }
    
    // Obtener trabajo actualizado
    $trabajo = TrabajoTaller::obtenerPorId($id);
    
    responder_json(true, $trabajo, 'Trabajo actualizado exitosamente');
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al actualizar trabajo: ' . $e->getMessage(),
        'ERROR_ACTUALIZAR_TRABAJO'
    );
}