<?php
/**
 * ================================================
 * API: CREAR MATERIA PRIMA
 * ================================================
 * Endpoint para crear una nueva materia prima
 * 
 * Método: POST
 * Autenticación: Requerida
 * Permisos: materia_prima.crear
 * 
 * Parámetros POST requeridos:
 * - nombre: Nombre de la materia prima
 * - tipo: oro, plata, piedra, otro
 * - unidad_medida: gramos, piezas, quilates
 * 
 * Parámetros POST opcionales:
 * - cantidad_disponible: Cantidad inicial (default: 0)
 * - stock_minimo: Stock mínimo de alerta (default: 5)
 * - precio_por_unidad: Precio por unidad
 * - activo: 1 o 0 (default: 1)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "id": 123,
 *     "materia": {...}
 *   },
 *   "message": "Materia prima creada exitosamente"
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
verificar_api_permiso('materia_prima', 'crear');

try {
    // Leer JSON body
    $json_input = file_get_contents('php://input');
    $datos = json_decode($json_input, true);
    
    // Fallback a POST
    if (json_last_error() !== JSON_ERROR_NONE || empty($datos)) {
        $datos = $_POST;
    }
    
    // Validar campos requeridos
    if (empty($datos['nombre'])) {
        responder_json(false, null, 'El nombre es requerido', 'CAMPO_REQUERIDO');
    }
    
    if (empty($datos['tipo'])) {
        responder_json(false, null, 'El tipo es requerido', 'CAMPO_REQUERIDO');
    }
    
    if (empty($datos['unidad_medida'])) {
        responder_json(false, null, 'La unidad de medida es requerida', 'CAMPO_REQUERIDO');
    }
    
    // Validar tipo válido
    $tipos_validos = ['oro', 'plata', 'piedra', 'otro'];
    if (!in_array($datos['tipo'], $tipos_validos)) {
        responder_json(false, null, 'Tipo inválido. Use: oro, plata, piedra, otro', 'TIPO_INVALIDO');
    }
    
    // Validar unidad válida
    $unidades_validas = ['gramos', 'piezas', 'quilates'];
    if (!in_array($datos['unidad_medida'], $unidades_validas)) {
        responder_json(false, null, 'Unidad inválida. Use: gramos, piezas, quilates', 'UNIDAD_INVALIDA');
    }
    
    // Preparar datos de la materia prima
    $datos_materia = array(
        'nombre' => $datos['nombre'],
        'tipo' => $datos['tipo'],
        'unidad_medida' => $datos['unidad_medida'],
        'cantidad_disponible' => isset($datos['cantidad_disponible']) ? (float)$datos['cantidad_disponible'] : 0,
        'stock_minimo' => isset($datos['stock_minimo']) ? (float)$datos['stock_minimo'] : 5,
        'precio_por_unidad' => isset($datos['precio_por_unidad']) ? (float)$datos['precio_por_unidad'] : null,
        'activo' => isset($datos['activo']) ? (int)$datos['activo'] : 1
    );
    
    // Crear materia prima (el modelo valida internamente)
    $materia_id = MateriaPrima::crear($datos_materia);
    
    if (!$materia_id) {
        throw new Exception('No se pudo crear la materia prima. Revise los datos enviados.');
    }
    
    // Obtener materia prima creada
    $materia = MateriaPrima::obtenerPorId($materia_id);
    
    responder_json(
        true,
        array(
            'id' => $materia_id,
            'materia' => $materia
        ),
        'Materia prima creada exitosamente'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al crear materia prima: ' . $e->getMessage(),
        'ERROR_CREAR_MATERIA'
    );
}
