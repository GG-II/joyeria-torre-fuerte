<?php
/**
 * ================================================
 * API: CAMBIAR ESTADO PROVEEDOR
 * ================================================
 * Endpoint para activar o desactivar un proveedor
 * 
 * Método: POST
 * Autenticación: Requerida
 * Permisos: proveedores.editar
 * 
 * Parámetros POST:
 * - id: ID del proveedor (requerido)
 * - accion: 'activar' o 'desactivar' (requerido)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "id": 123,
 *     "nombre": "Proveedor XYZ",
 *     "estado_anterior": "activo",
 *     "estado_nuevo": "inactivo"
 *   },
 *   "message": "Proveedor desactivado exitosamente"
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/proveedor.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('POST');
verificar_api_permiso('proveedores', 'editar');

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
        responder_json(false, null, 'El ID del proveedor es requerido', 'ID_REQUERIDO');
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
    
    // Verificar que el proveedor existe
    $proveedor = Proveedor::obtenerPorId($id);
    
    if (!$proveedor) {
        responder_json(false, null, 'El proveedor no existe', 'PROVEEDOR_NO_ENCONTRADO');
    }
    
    $estado_anterior = $proveedor['activo'] == 1 ? 'activo' : 'inactivo';
    
    // Ejecutar acción
    if ($accion === 'activar') {
        if ($proveedor['activo'] == 1) {
            responder_json(false, null, 'El proveedor ya está activo', 'YA_ACTIVO');
        }
        
        $resultado = Proveedor::activar($id);
        $estado_nuevo = 'activo';
        $mensaje = 'Proveedor activado exitosamente';
        
    } else {
        if ($proveedor['activo'] == 0) {
            responder_json(false, null, 'El proveedor ya está inactivo', 'YA_INACTIVO');
        }
        
        $resultado = Proveedor::desactivar($id);
        $estado_nuevo = 'inactivo';
        $mensaje = 'Proveedor desactivado exitosamente';
    }
    
    if (!$resultado) {
        throw new Exception('No se pudo cambiar el estado del proveedor');
    }
    
    // Responder
    responder_json(
        true,
        [
            'id' => $id,
            'nombre' => $proveedor['nombre'],
            'estado_anterior' => $estado_anterior,
            'estado_nuevo' => $estado_nuevo
        ],
        $mensaje
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al cambiar estado del proveedor: ' . $e->getMessage(),
        'ERROR_CAMBIAR_ESTADO'
    );
}
