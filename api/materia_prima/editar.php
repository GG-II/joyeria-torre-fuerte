<?php
/**
 * ================================================
 * API: EDITAR MATERIA PRIMA
 * ================================================
 * Endpoint para editar una materia prima existente
 * 
 * Método: POST
 * Autenticación: Requerida
 * Permisos: materia_prima.editar
 * 
 * Parámetros POST requeridos:
 * - id: ID de la materia prima
 * - nombre: Nombre de la materia prima
 * - tipo: oro, plata, piedra, otro
 * - unidad_medida: gramos, piezas, quilates
 * 
 * Parámetros POST opcionales:
 * - stock_minimo: Stock mínimo
 * - precio_por_unidad: Precio por unidad
 * - activo: 1 o 0
 * 
 * NOTA: La cantidad_disponible NO se edita aquí, usar ajustar_stock.php
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {...},
 *   "message": "Materia prima actualizada exitosamente"
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
    
    // Validar ID requerido
    if (empty($datos['id'])) {
        responder_json(false, null, 'El ID de la materia prima es requerido', 'ID_REQUERIDO');
    }
    
    $id = (int)$datos['id'];
    
    // Verificar que la materia prima existe
    $materia_actual = MateriaPrima::obtenerPorId($id);
    
    if (!$materia_actual) {
        responder_json(false, null, 'La materia prima no existe', 'MATERIA_NO_ENCONTRADA');
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
    
    // Preparar datos de actualización (mantener cantidad_disponible actual)
    $datos_materia = array(
        'nombre' => $datos['nombre'],
        'tipo' => $datos['tipo'],
        'unidad_medida' => $datos['unidad_medida'],
        'cantidad_disponible' => $materia_actual['cantidad_disponible'], // NO se cambia aquí
        'stock_minimo' => isset($datos['stock_minimo']) ? (float)$datos['stock_minimo'] : $materia_actual['stock_minimo'],
        'precio_por_unidad' => isset($datos['precio_por_unidad']) ? (float)$datos['precio_por_unidad'] : $materia_actual['precio_por_unidad'],
        'activo' => isset($datos['activo']) ? (int)$datos['activo'] : $materia_actual['activo']
    );
    
    // Actualizar materia prima
    $resultado = MateriaPrima::actualizar($id, $datos_materia);
    
    if (!$resultado) {
        throw new Exception('No se pudo actualizar la materia prima. Revise los datos enviados.');
    }
    
    // Obtener materia prima actualizada
    $materia = MateriaPrima::obtenerPorId($id);
    
    responder_json(
        true,
        $materia,
        'Materia prima actualizada exitosamente'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al editar materia prima: ' . $e->getMessage(),
        'ERROR_EDITAR_MATERIA'
    );
}
