<?php
/**
 * ================================================
 * API: TRANSFERIR TRABAJO DE TALLER
 * ================================================
 * Endpoint para transferir un trabajo entre empleados
 * 
 * Método: POST
 * Autenticación: Requerida
 * Permisos: taller.transferir
 * 
 * Parámetros POST:
 * - trabajo_id: ID del trabajo (requerido)
 * - empleado_destino_id: ID del empleado destino (requerido)
 * - nota: Nota de la transferencia (opcional)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "trabajo_id": 123,
 *     "empleado_origen": "Juan Pérez",
 *     "empleado_destino": "María López",
 *     "trabajo": {...}
 *   },
 *   "message": "Trabajo transferido exitosamente"
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
verificar_api_permiso('taller', 'transferir');

try {
    // Leer JSON body
    $json_input = file_get_contents('php://input');
    $datos = json_decode($json_input, true);
    
    // Fallback a POST
    if (json_last_error() !== JSON_ERROR_NONE || empty($datos)) {
        $datos = $_POST;
    }
    
    // Validar campos requeridos
    if (!isset($datos['trabajo_id']) || empty($datos['trabajo_id'])) {
        responder_json(false, null, 'El ID del trabajo es requerido', 'TRABAJO_ID_REQUERIDO');
    }
    
    if (!isset($datos['empleado_destino_id']) || empty($datos['empleado_destino_id'])) {
        responder_json(false, null, 'El ID del empleado destino es requerido', 'EMPLEADO_DESTINO_REQUERIDO');
    }
    
    $trabajo_id = (int)$datos['trabajo_id'];
    $empleado_destino_id = (int)$datos['empleado_destino_id'];
    $nota = $datos['nota'] ?? '';
    
    // Obtener trabajo actual
    $trabajo_actual = TrabajoTaller::obtenerPorId($trabajo_id);
    
    if (!$trabajo_actual) {
        responder_json(false, null, 'El trabajo no existe', 'TRABAJO_NO_ENCONTRADO');
    }
    
    // Verificar que no esté entregado o cancelado
    if (in_array($trabajo_actual['estado'], ['entregado', 'cancelado'])) {
        responder_json(false, null, 'No se puede transferir un trabajo entregado o cancelado', 'ESTADO_INVALIDO');
    }
    
    // Verificar que no se transfiera a sí mismo
    if ($trabajo_actual['empleado_actual_id'] == $empleado_destino_id) {
        responder_json(false, null, 'No se puede transferir a sí mismo', 'TRANSFERENCIA_INVALIDA');
    }
    
    // Obtener nombres de empleados
    $empleado_origen = db_query_one("SELECT nombre FROM usuarios WHERE id = ?", [$trabajo_actual['empleado_actual_id']]);
    $empleado_destino = db_query_one("SELECT nombre FROM usuarios WHERE id = ?", [$empleado_destino_id]);
    
    if (!$empleado_destino) {
        responder_json(false, null, 'El empleado destino no existe', 'EMPLEADO_NO_ENCONTRADO');
    }
    
    // Transferir trabajo
    $resultado = TrabajoTaller::transferirTrabajo($trabajo_id, $empleado_destino_id, $nota);
    
    if (!$resultado) {
        throw new Exception('No se pudo transferir el trabajo. Revise los logs para más detalles.');
    }
    
    // Obtener trabajo actualizado
    $trabajo = TrabajoTaller::obtenerPorId($trabajo_id);
    
    responder_json(
        true,
        [
            'trabajo_id' => $trabajo_id,
            'empleado_origen' => $empleado_origen['nombre'],
            'empleado_destino' => $empleado_destino['nombre'],
            'trabajo' => $trabajo
        ],
        "Trabajo transferido exitosamente de {$empleado_origen['nombre']} a {$empleado_destino['nombre']}"
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al transferir trabajo: ' . $e->getMessage(),
        'ERROR_TRANSFERIR_TRABAJO'
    );
}
