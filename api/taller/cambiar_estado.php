<?php
/**
 * ================================================
 * API: CAMBIAR ESTADO DE TRABAJO
 * ================================================
 * Endpoint para cambiar el estado de un trabajo de taller
 * 
 * Método: POST
 * Autenticación: Requerida
 * Permisos: taller.editar
 * 
 * Parámetros POST:
 * - id: ID del trabajo (requerido)
 * - nuevo_estado: recibido, en_proceso, completado, entregado, cancelado (requerido)
 * - observaciones: Observaciones del cambio (opcional)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "id": 123,
 *     "codigo": "TT-2026-0001",
 *     "estado_anterior": "recibido",
 *     "estado_nuevo": "en_proceso",
 *     "trabajo": {...}
 *   },
 *   "message": "Estado cambiado exitosamente"
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
verificar_api_permiso('taller', 'editar');

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
    
    if (!isset($datos['nuevo_estado']) || empty($datos['nuevo_estado'])) {
        responder_json(false, null, 'El nuevo estado es requerido', 'ESTADO_REQUERIDO');
    }
    
    $id = (int)$datos['id'];
    $nuevo_estado = $datos['nuevo_estado'];
    $observaciones = $datos['observaciones'] ?? '';
    
    // Validar estado válido
    $estados_validos = ['recibido', 'en_proceso', 'completado', 'entregado', 'cancelado'];
    if (!in_array($nuevo_estado, $estados_validos)) {
        responder_json(false, null, 'Estado inválido. Use: ' . implode(', ', $estados_validos), 'ESTADO_INVALIDO');
    }
    
    // Obtener trabajo actual
    $trabajo_actual = TrabajoTaller::obtenerPorId($id);
    
    if (!$trabajo_actual) {
        responder_json(false, null, 'El trabajo no existe', 'TRABAJO_NO_ENCONTRADO');
    }
    
    $estado_anterior = $trabajo_actual['estado'];
    
    // Verificar que el estado sea diferente
    if ($estado_anterior === $nuevo_estado) {
        responder_json(false, null, "El trabajo ya está en estado: {$nuevo_estado}", 'ESTADO_YA_ESTABLECIDO');
    }
    
    // Cambiar estado
    $resultado = TrabajoTaller::cambiarEstado($id, $nuevo_estado, $observaciones);
    
    if (!$resultado) {
        throw new Exception('No se pudo cambiar el estado del trabajo');
    }
    
    // Obtener trabajo actualizado
    $trabajo = TrabajoTaller::obtenerPorId($id);
    
    responder_json(
        true,
        [
            'id' => $id,
            'codigo' => $trabajo['codigo'],
            'estado_anterior' => $estado_anterior,
            'estado_nuevo' => $nuevo_estado,
            'trabajo' => $trabajo
        ],
        "Estado cambiado exitosamente de '{$estado_anterior}' a '{$nuevo_estado}'"
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al cambiar estado: ' . $e->getMessage(),
        'ERROR_CAMBIAR_ESTADO'
    );
}
