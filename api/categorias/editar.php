<?php
/**
 * ================================================
 * API: EDITAR CATEGORÍA
 * ================================================
 * Endpoint para editar una categoría existente
 * 
 * Método: POST
 * Autenticación: Requerida
 * Permisos: categorias.editar
 * 
 * Parámetros POST requeridos:
 * - id: ID de la categoría
 * - nombre: Nombre de la categoría
 * - tipo_clasificacion: tipo, material, peso
 * 
 * Parámetros POST opcionales:
 * - descripcion: Descripción
 * - categoria_padre_id: ID de categoría padre
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {...},
 *   "message": "Categoría actualizada exitosamente"
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
    
    // Validar ID requerido
    if (empty($datos['id'])) {
        responder_json(false, null, 'El ID de la categoría es requerido', 'ID_REQUERIDO');
    }
    
    $id = (int)$datos['id'];
    
    // Verificar que la categoría existe
    $categoria_actual = Categoria::obtenerPorId($id);
    
    if (!$categoria_actual) {
        responder_json(false, null, 'La categoría no existe', 'CATEGORIA_NO_ENCONTRADA');
    }
    
    // Validar campos requeridos
    if (empty($datos['nombre'])) {
        responder_json(false, null, 'El nombre es requerido', 'CAMPO_REQUERIDO');
    }
    
    if (empty($datos['tipo_clasificacion'])) {
        responder_json(false, null, 'El tipo de clasificación es requerido', 'CAMPO_REQUERIDO');
    }
    
    // Validar tipo de clasificación
    $tipos_validos = array('tipo', 'material', 'peso');
    if (!in_array($datos['tipo_clasificacion'], $tipos_validos)) {
        responder_json(false, null, 'Tipo de clasificación inválido. Use: tipo, material, peso', 'TIPO_INVALIDO');
    }
    
    // Preparar datos de actualización
    $datos_categoria = array(
        'nombre' => $datos['nombre'],
        'tipo_clasificacion' => $datos['tipo_clasificacion'],
        'descripcion' => isset($datos['descripcion']) ? $datos['descripcion'] : $categoria_actual['descripcion'],
        'categoria_padre_id' => isset($datos['categoria_padre_id']) ? (int)$datos['categoria_padre_id'] : $categoria_actual['categoria_padre_id'],
        'activo' => isset($datos['activo']) ? $datos['activo'] : $categoria_actual['activo']
    );
    
    // Actualizar categoría
    $resultado = Categoria::actualizar($id, $datos_categoria);
    
    if (!$resultado) {
        throw new Exception('No se pudo actualizar la categoría. Revise los logs para más detalles.');
    }
    
    // Obtener categoría actualizada
    $categoria = Categoria::obtenerPorId($id);
    
    responder_json(
        true,
        $categoria,
        'Categoría actualizada exitosamente'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al editar categoría: ' . $e->getMessage(),
        'ERROR_EDITAR_CATEGORIA'
    );
}