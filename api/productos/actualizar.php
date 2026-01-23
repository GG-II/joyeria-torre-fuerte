<?php
/**
 * ================================================
 * API: ACTUALIZAR PRODUCTO
 * ================================================
 * Endpoint para actualizar un producto existente
 * Permite actualización parcial de campos
 * 
 * Método: POST
 * Autenticación: Requerida
 * Permisos: productos.editar
 * 
 * Parámetros POST requeridos:
 * - id: ID del producto a actualizar
 * 
 * Parámetros POST opcionales (se actualizan solo los enviados):
 * - codigo, nombre, categoria_id, descripcion, proveedor_id,
 *   es_por_peso, peso_gramos, largo_cm, imagen, codigo_barras
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/producto.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('POST');
verificar_api_permiso('productos', 'editar');

try {
    // Validar ID requerido
    validar_campos_requeridos(['id'], 'POST');
    
    $id = obtener_post('id', null, 'int');
    
    // Verificar que el producto exista y obtener datos actuales
    $producto_actual = Producto::obtenerPorId($id);
    
    if (!$producto_actual) {
        responder_json(
            false,
            null,
            'El producto no existe',
            'PRODUCTO_NO_ENCONTRADO'
        );
    }
    
    // ================================================
    // PREPARAR DATOS COMPLETOS (mezclar actual + nuevo)
    // ================================================
    
    $datos = [
        'codigo' => isset($_POST['codigo']) ? obtener_post('codigo', null, 'string') : $producto_actual['codigo'],
        'nombre' => isset($_POST['nombre']) ? obtener_post('nombre', null, 'string') : $producto_actual['nombre'],
        'categoria_id' => isset($_POST['categoria_id']) ? obtener_post('categoria_id', null, 'int') : $producto_actual['categoria_id'],
        'codigo_barras' => isset($_POST['codigo_barras']) ? obtener_post('codigo_barras', null, 'string') : $producto_actual['codigo_barras'],
        'descripcion' => isset($_POST['descripcion']) ? obtener_post('descripcion', null, 'string') : $producto_actual['descripcion'],
        'proveedor_id' => isset($_POST['proveedor_id']) ? obtener_post('proveedor_id', null, 'int') : $producto_actual['proveedor_id'],
        'es_por_peso' => isset($_POST['es_por_peso']) ? obtener_post('es_por_peso', 0, 'int') : $producto_actual['es_por_peso'],
        'peso_gramos' => isset($_POST['peso_gramos']) ? obtener_post('peso_gramos', null, 'float') : $producto_actual['peso_gramos'],
        'estilo' => isset($_POST['estilo']) ? obtener_post('estilo', null, 'string') : $producto_actual['estilo'],
        'largo_cm' => isset($_POST['largo_cm']) ? obtener_post('largo_cm', null, 'float') : $producto_actual['largo_cm'],
        'imagen' => isset($_POST['imagen']) ? obtener_post('imagen', null, 'string') : $producto_actual['imagen']
    ];
    
    // ================================================
    // VALIDACIONES MANUALES
    // ================================================
    $errores = [];
    
    // Validar código único (si cambió)
    if ($datos['codigo'] !== $producto_actual['codigo']) {
        if (empty($datos['codigo'])) {
            $errores[] = 'El código no puede estar vacío';
        } elseif (Producto::existeCodigo($datos['codigo'], $id)) {
            $errores[] = 'El código ya está en uso';
        }
    }
    
    // Validar nombre
    if (empty($datos['nombre'])) {
        $errores[] = 'El nombre no puede estar vacío';
    }
    
    // Validar categoría
    if (empty($datos['categoria_id']) || $datos['categoria_id'] <= 0) {
        $errores[] = 'La categoría no es válida';
    }
    
    // Validar código de barras único (si cambió y no está vacío)
    if (!empty($datos['codigo_barras']) && $datos['codigo_barras'] !== $producto_actual['codigo_barras']) {
        if (Producto::existeCodigoBarras($datos['codigo_barras'], $id)) {
            $errores[] = 'El código de barras ya está en uso';
        }
    }
    
    // Validar peso si es producto por peso
    if ($datos['es_por_peso'] == 1) {
        if (empty($datos['peso_gramos']) || $datos['peso_gramos'] <= 0) {
            $errores[] = 'El peso en gramos es requerido para productos por peso';
        }
    }
    
    // Si hay errores de validación
    if (!empty($errores)) {
        responder_json(
            false,
            [
                'errores' => $errores
            ],
            'Errores de validación: ' . implode(', ', $errores),
            'VALIDACION_FALLIDA'
        );
    }
    
    // ================================================
    // ACTUALIZAR PRODUCTO (ahora con datos completos)
    // ================================================
    $resultado = Producto::actualizar($id, $datos);
    
    if (!$resultado) {
        throw new Exception('No se pudo actualizar el producto');
    }
    
    // Obtener el producto actualizado
    $producto = Producto::obtenerPorId($id);
    
    // Responder con éxito
    responder_json(
        true,
        $producto,
        'Producto actualizado exitosamente'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al actualizar producto: ' . $e->getMessage(),
        'ERROR_ACTUALIZAR_PRODUCTO'
    );
}