<?php
/**
 * ================================================
 * API: CAMBIAR ESTADO DE SUCURSAL
 * ================================================
 * Endpoint para activar o desactivar una sucursal
 * 
 * Método: POST
 * Autenticación: Requerida
 * Permisos: sucursales.editar
 * 
 * Parámetros POST:
 * - id: ID de la sucursal (requerido)
 * - accion: 'activar' o 'desactivar' (requerido)
 * 
 * IMPORTANTE: No se puede desactivar la única sucursal activa del sistema
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "id": 123,
 *     "nombre": "Sucursal Centro",
 *     "estado_anterior": "activo",
 *     "estado_nuevo": "inactivo"
 *   },
 *   "message": "Sucursal desactivada exitosamente"
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/sucursal.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('POST');
verificar_api_permiso('sucursales', 'editar');

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
        responder_json(false, null, 'El ID de la sucursal es requerido', 'ID_REQUERIDO');
    }
    
    if (empty($datos['accion'])) {
        responder_json(false, null, 'La acción es requerida (activar o desactivar)', 'ACCION_REQUERIDA');
    }
    
    $id = (int)$datos['id'];
    $accion = strtolower($datos['accion']);
    
    // Validar acción
    if (!in_array($accion, array('activar', 'desactivar'))) {
        responder_json(false, null, 'Acción inválida. Use: activar o desactivar', 'ACCION_INVALIDA');
    }
    
    // Verificar que la sucursal existe
    $sucursal = Sucursal::obtenerPorId($id);
    
    if (!$sucursal) {
        responder_json(false, null, 'La sucursal no existe', 'SUCURSAL_NO_ENCONTRADA');
    }
    
    $estado_anterior = $sucursal['activo'] == 1 ? 'activo' : 'inactivo';
    
    // Ejecutar acción
    if ($accion === 'activar') {
        if ($sucursal['activo'] == 1) {
            responder_json(false, null, 'La sucursal ya está activa', 'YA_ACTIVA');
        }
        
        $resultado = Sucursal::activar($id);
        $estado_nuevo = 'activo';
        $mensaje = 'Sucursal activada exitosamente';
        
    } else {
        if ($sucursal['activo'] == 0) {
            responder_json(false, null, 'La sucursal ya está inactiva', 'YA_INACTIVA');
        }
        
        // Verificar que no sea la única activa (el modelo lo valida)
        $resultado = Sucursal::desactivar($id);
        
        if (!$resultado) {
            throw new Exception('No se puede desactivar la única sucursal activa del sistema');
        }
        
        $estado_nuevo = 'inactivo';
        $mensaje = 'Sucursal desactivada exitosamente';
    }
    
    if (!$resultado) {
        throw new Exception('No se pudo cambiar el estado de la sucursal');
    }
    
    // Responder
    responder_json(
        true,
        array(
            'id' => $id,
            'nombre' => $sucursal['nombre'],
            'estado_anterior' => $estado_anterior,
            'estado_nuevo' => $estado_nuevo
        ),
        $mensaje
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al cambiar estado de la sucursal: ' . $e->getMessage(),
        'ERROR_CAMBIAR_ESTADO'
    );
}
