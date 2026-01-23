<?php
/**
 * ================================================
 * API: CAMBIAR ESTADO MATERIA PRIMA
 * ================================================
 * Endpoint para activar o desactivar una materia prima
 * 
 * Método: POST
 * Autenticación: Requerida
 * Permisos: materia_prima.editar
 * 
 * Parámetros POST:
 * - id: ID de la materia prima (requerido)
 * - accion: 'activar' o 'desactivar' (requerido)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "id": 123,
 *     "nombre": "Oro 18K",
 *     "estado_anterior": "activo",
 *     "estado_nuevo": "inactivo"
 *   },
 *   "message": "Materia prima desactivada exitosamente"
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/materia_prima.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('POST');
verificar_api_permiso('materia_prima', 'editar');

try {
    // Leer JSON body
    $json_input = file_get_contents('php://input');
    $datos = json_decode($json_input, true);
    
    // Fallback a POST
    if (json_last_error() !== JSON_ERROR_NONE || empty($datos)) {
        $datos = $_POST;
    }
    
    // Validar campos requeridos
    if (empty($datos['id'])) {
        responder_json(false, null, 'El ID de la materia prima es requerido', 'ID_REQUERIDO');
    }
    
    if (empty($datos['accion'])) {
        responder_json(false, null, 'La acción es requerida (activar o desactivar)', 'ACCION_REQUERIDA');
    }
    
    $id = (int)$datos['id'];
    $accion = strtolower($datos['accion']);
    
    // Validar acción
    if (!in_array($accion, ['activar', 'desactivar'])) {
        responder_json(false, null, 'Acción inválida. Use: activar o desactivar', 'ACCION_INVALIDA');
    }
    
    // Verificar que la materia prima existe
    $materia = MateriaPrima::obtenerPorId($id);
    
    if (!$materia) {
        responder_json(false, null, 'La materia prima no existe', 'MATERIA_NO_ENCONTRADA');
    }
    
    $estado_anterior = $materia['activo'] == 1 ? 'activo' : 'inactivo';
    
    // Ejecutar acción
    if ($accion === 'activar') {
        if ($materia['activo'] == 1) {
            responder_json(false, null, 'La materia prima ya está activa', 'YA_ACTIVA');
        }
        
        $resultado = MateriaPrima::reactivar($id);
        $estado_nuevo = 'activo';
        $mensaje = 'Materia prima activada exitosamente';
        
    } else {
        if ($materia['activo'] == 0) {
            responder_json(false, null, 'La materia prima ya está inactiva', 'YA_INACTIVA');
        }
        
        $resultado = MateriaPrima::eliminar($id);
        $estado_nuevo = 'inactivo';
        $mensaje = 'Materia prima desactivada exitosamente';
    }
    
    if (!$resultado) {
        throw new Exception('No se pudo cambiar el estado de la materia prima');
    }
    
    // Responder
    responder_json(
        true,
        array(
            'id' => $id,
            'nombre' => $materia['nombre'],
            'estado_anterior' => $estado_anterior,
            'estado_nuevo' => $estado_nuevo
        ),
        $mensaje
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al cambiar estado de la materia prima: ' . $e->getMessage(),
        'ERROR_CAMBIAR_ESTADO'
    );
}
