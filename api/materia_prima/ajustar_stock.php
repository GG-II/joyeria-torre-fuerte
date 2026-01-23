<?php
/**
 * ================================================
 * API: AJUSTAR STOCK MATERIA PRIMA
 * ================================================
 * Endpoint para ajustar la cantidad disponible de una materia prima
 * 
 * Método: POST
 * Autenticación: Requerida
 * Permisos: materia_prima.ajustar_stock
 * 
 * Parámetros POST requeridos:
 * - id: ID de la materia prima
 * - cantidad_nueva: Nueva cantidad disponible
 * - motivo: Motivo del ajuste (compra, uso_taller, inventario, etc.)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "id": 123,
 *     "nombre": "Oro 18K",
 *     "cantidad_anterior": 100.50,
 *     "cantidad_nueva": 150.75,
 *     "diferencia": 50.25,
 *     "unidad_medida": "gramos",
 *     "motivo": "Compra a proveedor",
 *     "materia": {...}
 *   },
 *   "message": "Stock ajustado exitosamente"
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
verificar_api_permiso('materia_prima', 'ajustar_stock');

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
    
    if (!isset($datos['cantidad_nueva']) || $datos['cantidad_nueva'] === '') {
        responder_json(false, null, 'La cantidad nueva es requerida', 'CANTIDAD_REQUERIDA');
    }
    
    if (empty($datos['motivo'])) {
        responder_json(false, null, 'El motivo del ajuste es requerido', 'MOTIVO_REQUERIDO');
    }
    
    $id = (int)$datos['id'];
    $cantidad_nueva = (float)$datos['cantidad_nueva'];
    $motivo = $datos['motivo'];
    
    // Validar cantidad positiva
    if ($cantidad_nueva < 0) {
        responder_json(false, null, 'La cantidad debe ser positiva', 'CANTIDAD_INVALIDA');
    }
    
    // Verificar que la materia prima existe
    $materia_actual = MateriaPrima::obtenerPorId($id);
    
    if (!$materia_actual) {
        responder_json(false, null, 'La materia prima no existe', 'MATERIA_NO_ENCONTRADA');
    }
    
    $cantidad_anterior = $materia_actual['cantidad_disponible'];
    $diferencia = $cantidad_nueva - $cantidad_anterior;
    
    // Ajustar stock
    $resultado = MateriaPrima::ajustarCantidad($id, $cantidad_nueva, $motivo);
    
    if (!$resultado) {
        throw new Exception('No se pudo ajustar el stock');
    }
    
    // Obtener materia prima actualizada
    $materia = MateriaPrima::obtenerPorId($id);
    
    responder_json(
        true,
        array(
            'id' => $id,
            'nombre' => $materia_actual['nombre'],
            'cantidad_anterior' => $cantidad_anterior,
            'cantidad_nueva' => $cantidad_nueva,
            'diferencia' => $diferencia,
            'unidad_medida' => $materia_actual['unidad_medida'],
            'motivo' => $motivo,
            'materia' => $materia
        ),
        'Stock ajustado exitosamente'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al ajustar stock: ' . $e->getMessage(),
        'ERROR_AJUSTAR_STOCK'
    );
}
