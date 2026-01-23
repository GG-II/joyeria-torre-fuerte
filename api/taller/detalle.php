<?php
/**
 * ================================================
 * API: DETALLE DE TRABAJO DE TALLER
 * ================================================
 * Endpoint para obtener detalles completos de un trabajo incluyendo historial
 * 
 * Método: GET
 * Autenticación: Requerida
 * Permisos: taller.ver
 * 
 * Parámetros GET:
 * - id: ID del trabajo (requerido)
 * - incluir_historial: true/false (opcional, default: true)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "trabajo": {...},
 *     "historial_transferencias": [...]
 *   }
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/trabajo_taller.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('GET');
verificar_api_permiso('taller', 'ver');

try {
    // Validar ID requerido
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        responder_json(false, null, 'El ID del trabajo es requerido', 'ID_REQUERIDO');
    }
    
    $id = (int)$_GET['id'];
    $incluir_historial = isset($_GET['incluir_historial']) ? $_GET['incluir_historial'] === 'true' : true;
    
    // Obtener trabajo
    $trabajo = TrabajoTaller::obtenerPorId($id);
    
    if (!$trabajo) {
        responder_json(false, null, 'El trabajo no existe', 'TRABAJO_NO_ENCONTRADO');
    }
    
    $respuesta = [
        'trabajo' => $trabajo
    ];
    
    // Incluir historial de transferencias si se solicita
    if ($incluir_historial) {
        $historial = TrabajoTaller::obtenerHistorialTransferencias($id);
        $respuesta['historial_transferencias'] = $historial;
    }
    
    responder_json(
        true,
        $respuesta,
        'Trabajo obtenido exitosamente'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al obtener trabajo: ' . $e->getMessage(),
        'ERROR_OBTENER_TRABAJO'
    );
}
