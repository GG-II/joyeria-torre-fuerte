<?php
/**
 * API: ACTUALIZAR PRODUCTO
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
    // Leer JSON del body
    $json_input = file_get_contents('php://input');
    $datos_json = json_decode($json_input, true);
    
    if (json_last_error() === JSON_ERROR_NONE && !empty($datos_json)) {
        $_POST = array_merge($_POST, $datos_json);
    }
    
    // Validar ID requerido
    validar_campos_requeridos(['id'], 'POST');
    
    $id = obtener_post('id', null, 'int');
    
    // Verificar que el producto exista
    $producto_actual = Producto::obtenerPorId($id);
    
    if (!$producto_actual) {
        responder_json(false, null, 'El producto no existe', 'PRODUCTO_NO_ENCONTRADO');
    }
    
    // Preparar datos para actualizar (solo los campos enviados)
    $datos_actualizacion = [
        'codigo' => isset($_POST['codigo']) ? obtener_post('codigo', null, 'string') : $producto_actual['codigo'],
        'nombre' => isset($_POST['nombre']) ? obtener_post('nombre', null, 'string') : $producto_actual['nombre'],
        'categoria_id' => isset($_POST['categoria_id']) ? obtener_post('categoria_id', null, 'int') : $producto_actual['categoria_id'],
        'codigo_barras' => isset($_POST['codigo_barras']) ? obtener_post('codigo_barras', null, 'string') : $producto_actual['codigo_barras'],
        'descripcion' => isset($_POST['descripcion']) ? obtener_post('descripcion', null, 'string') : $producto_actual['descripcion'],
        'peso_gramos' => isset($_POST['peso_gramos']) ? obtener_post('peso_gramos', null, 'float') : $producto_actual['peso_gramos'],
        'largo_cm' => isset($_POST['largo_cm']) ? obtener_post('largo_cm', null, 'float') : $producto_actual['largo_cm'],
        'estilo' => isset($_POST['estilo']) ? obtener_post('estilo', null, 'string') : $producto_actual['estilo'],
        'es_por_peso' => isset($_POST['es_por_peso']) ? obtener_post('es_por_peso', 0, 'int') : $producto_actual['es_por_peso'],
        'activo' => isset($_POST['activo']) ? obtener_post('activo', 1, 'int') : $producto_actual['activo']
    ];
    
    // Validar que el código no esté duplicado
    if ($datos_actualizacion['codigo'] !== $producto_actual['codigo']) {
        if (db_exists('productos', 'codigo = ? AND id != ?', [$datos_actualizacion['codigo'], $id])) {
            responder_json(false, null, 'El código ya está en uso por otro producto', 'CODIGO_DUPLICADO');
        }
    }
    
    // Actualizar producto
    $resultado = Producto::actualizar($id, $datos_actualizacion);
    
    if (!$resultado) {
        responder_json(false, null, 'No se pudo actualizar el producto', 'ERROR_ACTUALIZACION');
    }
    
    // Actualizar precios si se enviaron
    if (isset($_POST['precio_publico']) || isset($_POST['precio_mayorista']) || isset($_POST['stock_minimo'])) {
        $precio_publico = isset($_POST['precio_publico']) ? obtener_post('precio_publico', null, 'float') : null;
        $precio_mayorista = isset($_POST['precio_mayorista']) ? obtener_post('precio_mayorista', null, 'float') : null;
        $stock_minimo = isset($_POST['stock_minimo']) ? obtener_post('stock_minimo', 5, 'int') : null;
        
        // Actualizar precio público
        if ($precio_publico !== null) {
            // Verificar si existe el precio público
            $existe = db_query_one(
                'SELECT id FROM precios_producto WHERE producto_id = ? AND tipo_precio = ?',
                [$id, 'publico']
            );
            
            if ($existe) {
                db_execute(
                    'UPDATE precios_producto SET precio = ? WHERE producto_id = ? AND tipo_precio = ?',
                    [$precio_publico, $id, 'publico']
                );
            } else {
                db_execute(
                    'INSERT INTO precios_producto (producto_id, tipo_precio, precio) VALUES (?, ?, ?)',
                    [$id, 'publico', $precio_publico]
                );
            }
        }
        
        // Actualizar precio mayorista
        if ($precio_mayorista !== null && $precio_mayorista > 0) {
            $existe = db_query_one(
                'SELECT id FROM precios_producto WHERE producto_id = ? AND tipo_precio = ?',
                [$id, 'mayorista']
            );
            
            if ($existe) {
                db_execute(
                    'UPDATE precios_producto SET precio = ? WHERE producto_id = ? AND tipo_precio = ?',
                    [$precio_mayorista, $id, 'mayorista']
                );
            } else {
                db_execute(
                    'INSERT INTO precios_producto (producto_id, tipo_precio, precio) VALUES (?, ?, ?)',
                    [$id, 'mayorista', $precio_mayorista]
                );
            }
        }
        
        // Actualizar stock mínimo
        if ($stock_minimo !== null) {
            db_execute('UPDATE inventario SET stock_minimo = ? WHERE producto_id = ?', [$stock_minimo, $id]);
        }
    }
    
    responder_json(true, ['id' => $id], 'Producto actualizado exitosamente', 'PRODUCTO_ACTUALIZADO');
    
} catch (Exception $e) {
    responder_json(false, null, 'Error al actualizar producto: ' . $e->getMessage(), 'ERROR_ACTUALIZACION');
}
