<?php
/**
 * ================================================
 * API: CANCELAR TRABAJO
 * ================================================
 * Endpoint para cancelar un trabajo de taller
 * 
 * Método: POST
 * Autenticación: Requerida
 * Permisos: taller.cancelar
 * 
 * Parámetros POST:
 * - id: ID del trabajo (requerido)
 * - motivo: Motivo de cancelación (requerido)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "id": 123,
 *     "codigo": "TT-2026-0001",
 *     "motivo": "Cliente no respondió",
 *     "trabajo": {...}
 *   },
 *   "message": "Trabajo cancelado exitosamente"
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
verificar_api_permiso('taller', 'cancelar');

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
    
    if (!isset($datos['motivo']) || empty(trim($datos['motivo']))) {
        responder_json(false, null, 'El motivo de cancelación es requerido', 'MOTIVO_REQUERIDO');
    }
    
    $id = (int)$datos['id'];
    $motivo = trim($datos['motivo']);
    
    // Verificar que el trabajo existe
    $trabajo = TrabajoTaller::obtenerPorId($id);
    
    if (!$trabajo) {
        responder_json(false, null, 'El trabajo no existe', 'TRABAJO_NO_ENCONTRADO');
    }
    
    // Verificar que no esté entregado
    if ($trabajo['estado'] === 'entregado') {
        responder_json(false, null, 'No se puede cancelar un trabajo ya entregado', 'TRABAJO_ENTREGADO');
    }
    
    // Verificar que no esté ya cancelado
    if ($trabajo['estado'] === 'cancelado') {
        responder_json(false, null, 'El trabajo ya está cancelado', 'YA_CANCELADO');
    }
    
    // Cancelar trabajo
    $resultado = TrabajoTaller::eliminar($id, $motivo);
    
    if (!$resultado) {
        throw new Exception('No se pudo cancelar el trabajo');
    }
    
    // Obtener trabajo actualizado
    $trabajo_actualizado = TrabajoTaller::obtenerPorId($id);
    
    responder_json(
        true,
        [
            'id' => $id,
            'codigo' => $trabajo['codigo'],
            'motivo' => $motivo,
            'trabajo' => $trabajo_actualizado
        ],
        'Trabajo cancelado exitosamente'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al cancelar trabajo: ' . $e->getMessage(),
        'ERROR_CANCELAR_TRABAJO'
    );
}
