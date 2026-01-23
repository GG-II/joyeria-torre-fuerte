<?php
/**
 * ================================================
 * API: CAMBIAR ESTADO CATEGORÍA
 * ================================================
 * Endpoint para activar o desactivar una categoría
 * 
 * Método: POST
 * Autenticación: Requerida
 * Permisos: categorias.editar
 * 
 * Parámetros POST:
 * - id: ID de la categoría (requerido)
 * - accion: 'activar' o 'desactivar' (requerido)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "id": 123,
 *     "nombre": "Anillos",
 *     "estado_anterior": "activo",
 *     "estado_nuevo": "inactivo"
 *   },
 *   "message": "Categoría desactivada exitosamente"
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/categoria.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('POST');
verificar_api_permiso('categorias', 'editar');

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
        responder_json(false, null, 'El ID de la categoría es requerido', 'ID_REQUERIDO');
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
    
    // Verificar que la categoría existe
    $categoria = Categoria::obtenerPorId($id);
    
    if (!$categoria) {
        responder_json(false, null, 'La categoría no existe', 'CATEGORIA_NO_ENCONTRADA');
    }
    
    $estado_anterior = $categoria['activo'] == 1 ? 'activo' : 'inactivo';
    
    // Ejecutar acción
    if ($accion === 'activar') {
        if ($categoria['activo'] == 1) {
            responder_json(false, null, 'La categoría ya está activa', 'YA_ACTIVA');
        }
        
        $resultado = Categoria::reactivar($id);
        $estado_nuevo = 'activo';
        $mensaje = 'Categoría activada exitosamente';
        
    } else {
        if ($categoria['activo'] == 0) {
            responder_json(false, null, 'La categoría ya está inactiva', 'YA_INACTIVA');
        }
        
        // Verificar que se pueda desactivar
        if (!Categoria::puedeEliminar($id)) {
            responder_json(false, null, 'No se puede desactivar. La categoría tiene productos o subcategorías activas', 'NO_PUEDE_DESACTIVAR');
        }
        
        $resultado = Categoria::eliminar($id);
        $estado_nuevo = 'inactivo';
        $mensaje = 'Categoría desactivada exitosamente';
    }
    
    if (!$resultado) {
        throw new Exception('No se pudo cambiar el estado de la categoría');
    }
    
    // Responder
    responder_json(
        true,
        [
            'id' => $id,
            'nombre' => $categoria['nombre'],
            'estado_anterior' => $estado_anterior,
            'estado_nuevo' => $estado_nuevo
        ],
        $mensaje
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al cambiar estado de la categoría: ' . $e->getMessage(),
        'ERROR_CAMBIAR_ESTADO'
    );
}
