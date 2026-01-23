<?php
/**
 * ================================================
 * API: CREAR CATEGORÍA
 * ================================================
 * Endpoint para crear una nueva categoría
 * 
 * Método: POST
 * Autenticación: Requerida
 * Permisos: categorias.crear
 * 
 * Parámetros POST requeridos:
 * - nombre: Nombre de la categoría
 * - tipo_clasificacion: tipo, material, peso
 * 
 * Parámetros POST opcionales:
 * - descripcion: Descripción de la categoría
 * - categoria_padre_id: ID de categoría padre (para subcategorías)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "id": 123,
 *     "categoria": {...}
 *   },
 *   "message": "Categoría creada exitosamente"
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
verificar_api_permiso('categorias', 'crear');

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
        responder_json(false, null, 'El nombre de la categoría es requerido', 'CAMPO_REQUERIDO');
    }
    
    if (empty($datos['tipo_clasificacion'])) {
        responder_json(false, null, 'El tipo de clasificación es requerido', 'CAMPO_REQUERIDO');
    }
    
    // Validar tipo de clasificación
    $tipos_validos = ['tipo', 'material', 'peso'];
    if (!in_array($datos['tipo_clasificacion'], $tipos_validos)) {
        responder_json(false, null, 'Tipo de clasificación inválido. Use: tipo, material, peso', 'TIPO_INVALIDO');
    }
    
    // Preparar datos de la categoría
    $datos_categoria = [
        'nombre' => $datos['nombre'],
        'tipo_clasificacion' => $datos['tipo_clasificacion'],
        'descripcion' => $datos['descripcion'] ?? null,
        'categoria_padre_id' => isset($datos['categoria_padre_id']) ? (int)$datos['categoria_padre_id'] : null,
        'activo' => 1
    ];
    
    // Crear categoría (el modelo valida internamente)
    $categoria_id = Categoria::crear($datos_categoria);
    
    if (!$categoria_id) {
        // Intentar obtener errores de validación
        $errores_validacion = Categoria::validar($datos_categoria);
        if (!empty($errores_validacion)) {
            throw new Exception(implode(', ', $errores_validacion));
        }
        throw new Exception('No se pudo crear la categoría. Revise los datos enviados.');
    }
    
    // Obtener categoría creada
    $categoria = Categoria::obtenerPorId($categoria_id);
    
    responder_json(
        true,
        [
            'id' => $categoria_id,
            'categoria' => $categoria
        ],
        'Categoría creada exitosamente'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al crear categoría: ' . $e->getMessage(),
        'ERROR_CREAR_CATEGORIA'
    );
}