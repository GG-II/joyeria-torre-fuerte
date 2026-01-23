<?php
/**
 * ================================================
 * API: COMPLETAR TRABAJO
 * ================================================
 * Endpoint para marcar un trabajo como completado
 * 
 * Método: POST
 * Autenticación: Requerida
 * Permisos: taller.completar
 * 
 * Parámetros POST:
 * - id: ID del trabajo (requerido)
 * - observaciones: Observaciones (opcional)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "id": 123,
 *     "codigo": "TT-2026-0001",
 *     "trabajo": {...}
 *   },
 *   "message": "Trabajo completado exitosamente"
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
verificar_api_permiso('taller', 'completar');

try {
    // Leer JSON body
    $json_input = file_get_contents('php://input');
    $datos = json_decode($json_input, true);
    
    // Fallback a POST
    if (json_last_error() !== JSON_ERROR_NONE || empty($datos)) {
        $datos = $_POST;
    }
    
    // Validar ID requerido
    if (!isset($datos['id']) || empty($datos['id'])) {
        responder_json(false, null, 'El ID del trabajo es requerido', 'ID_REQUERIDO');
    }
    
    $id = (int)$datos['id'];
    $observaciones = $datos['observaciones'] ?? '';
    
    // Verificar que el trabajo existe
    $trabajo = TrabajoTaller::obtenerPorId($id);
    
    if (!$trabajo) {
        responder_json(false, null, 'El trabajo no existe', 'TRABAJO_NO_ENCONTRADO');
    }
    
    // Verificar que esté en estado válido para completar
    if (!in_array($trabajo['estado'], ['recibido', 'en_proceso'])) {
        responder_json(false, null, 'Solo se pueden completar trabajos en estado: recibido, en_proceso', 'ESTADO_INVALIDO');
    }
    
    // Completar trabajo
    $resultado = TrabajoTaller::completarTrabajo($id, $observaciones);
    
    if (!$resultado) {
        throw new Exception('No se pudo completar el trabajo');
    }
    
    // Obtener trabajo actualizado
    $trabajo_actualizado = TrabajoTaller::obtenerPorId($id);
    
    responder_json(
        true,
        [
            'id' => $id,
            'codigo' => $trabajo['codigo'],
            'trabajo' => $trabajo_actualizado
        ],
        'Trabajo completado exitosamente'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al completar trabajo: ' . $e->getMessage(),
        'ERROR_COMPLETAR_TRABAJO'
    );
}
