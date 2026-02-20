<?php
/**
 * API - CREAR PRODUCTO
 * Endpoint: POST /api/inventario/crear.php
 * 
 * Crea un producto completo insertando en 3 tablas:
 * 1. productos - Información básica
 * 2. precios_producto - Precios por tipo
 * 3. inventario - Stock inicial por sucursal
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('POST');
verificar_api_permiso('inventario', 'crear');

try {
    // Leer JSON del body
    $json_input = file_get_contents('php://input');
    $datos_json = json_decode($json_input, true);
    
    if (json_last_error() === JSON_ERROR_NONE && !empty($datos_json)) {
        $_POST = array_merge($_POST, $datos_json);
    }
    
    // Validar campos requeridos
    validar_campos_requeridos(['codigo', 'nombre', 'categoria_id', 'precio_publico'], 'POST');
    
    // Obtener datos
    $codigo = obtener_post('codigo', null, 'string');
    $codigo_barras = obtener_post('codigo_barras', null, 'string');
    $nombre = obtener_post('nombre', null, 'string');
    $descripcion = obtener_post('descripcion', '', 'string');
    $categoria_id = obtener_post('categoria_id', null, 'int');
    $proveedor_id = obtener_post('proveedor_id', null, 'int'); 
    $peso_gramos = obtener_post('peso_gramos', null, 'float');
    $largo_cm = obtener_post('largo_cm', null, 'float');
    $estilo = obtener_post('estilo', null, 'string');
    $es_por_peso = obtener_post('es_por_peso', 0, 'int');
    $activo = obtener_post('activo', 1, 'int');
    
    // Precios
    $precio_publico = obtener_post('precio_publico', null, 'float');
    $precio_mayorista = obtener_post('precio_mayorista', null, 'float');
    
    // Stock
    $stock_los_arcos = obtener_post('stock_los_arcos', 0, 'int');
    $stock_chinaca = obtener_post('stock_chinaca', 0, 'int');
    $stock_minimo = obtener_post('stock_minimo', 5, 'int');
    
    // Validaciones adicionales
    if ($precio_publico <= 0) {
        responder_json(false, null, 'El precio público debe ser mayor a 0', 'PRECIO_INVALIDO');
    }
    
    // Verificar que el código no esté duplicado
    if (db_exists('productos', 'codigo = ? AND activo = 1', [$codigo])) {
        responder_json(false, null, "El código '$codigo' ya está en uso", 'CODIGO_DUPLICADO');
    }
    
    // Verificar que la categoría exista
    if (!db_exists('categorias', 'id = ?', [$categoria_id])) {
        responder_json(false, null, 'La categoría seleccionada no existe', 'CATEGORIA_INVALIDA');
    }
    
    global $pdo;
    $pdo->beginTransaction();
    
    try {
        // 1. Insertar producto
        $sql_producto = "INSERT INTO productos (
            codigo, codigo_barras, nombre, descripcion, categoria_id, proveedor_id,
            peso_gramos, largo_cm, estilo, es_por_peso, imagen, activo
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql_producto);
        $stmt->execute([
            $codigo,
            $codigo_barras,
            $nombre,
            $descripcion,
            $categoria_id,
            $proveedor_id, // ← CAMBIAR: era null
            $peso_gramos,
            $largo_cm,
            $estilo,
            $es_por_peso,
            null, // imagen
            $activo
        ]);
        
        $producto_id = $pdo->lastInsertId();
        
        // 2. Insertar precio público (siempre requerido)
        $sql_precio = "INSERT INTO precios_producto (producto_id, tipo_precio, precio) 
                       VALUES (?, 'publico', ?)";
        $stmt_precio = $pdo->prepare($sql_precio);
        $stmt_precio->execute([$producto_id, $precio_publico]);
        
        // 3. Insertar precio mayorista (si se proporcionó)
        if ($precio_mayorista !== null && $precio_mayorista > 0) {
            $stmt_precio->execute([$producto_id, 'mayorista', $precio_mayorista]);
        }
        
        // 4. Insertar inventario para Los Arcos (sucursal_id = 1)
        if ($stock_los_arcos > 0 || true) { // Siempre crear registro
            $sql_inv = "INSERT INTO inventario (producto_id, sucursal_id, cantidad, stock_minimo)
                        VALUES (?, ?, ?, ?)";
            $stmt_inv = $pdo->prepare($sql_inv);
            $stmt_inv->execute([$producto_id, 1, $stock_los_arcos, $stock_minimo]);
            
            // Registrar movimiento de entrada inicial
            if ($stock_los_arcos > 0) {
                $sql_mov = "INSERT INTO movimientos_inventario (
                    producto_id, sucursal_id, tipo_movimiento, cantidad,
                    cantidad_anterior, cantidad_nueva, motivo, usuario_id, referencia_tipo
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt_mov = $pdo->prepare($sql_mov);
                $stmt_mov->execute([
                    $producto_id,
                    1, // sucursal Los Arcos
                    'ingreso',
                    $stock_los_arcos,
                    0, // cantidad_anterior
                    $stock_los_arcos, // cantidad_nueva
                    'Stock inicial',
                    $_SESSION['usuario_id'],
                    'ajuste_manual'
                ]);
            }
        }
        
        // 5. Insertar inventario para Chinaca Central (sucursal_id = 2)
        if ($stock_chinaca > 0 || true) { // Siempre crear registro
            $sql_inv = "INSERT INTO inventario (producto_id, sucursal_id, cantidad, stock_minimo)
                        VALUES (?, ?, ?, ?)";
            $stmt_inv = $pdo->prepare($sql_inv);
            $stmt_inv->execute([$producto_id, 2, $stock_chinaca, $stock_minimo]);
            
            // Registrar movimiento de entrada inicial
            if ($stock_chinaca > 0) {
                $sql_mov = "INSERT INTO movimientos_inventario (
                    producto_id, sucursal_id, tipo_movimiento, cantidad,
                    cantidad_anterior, cantidad_nueva, motivo, usuario_id, referencia_tipo
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt_mov = $pdo->prepare($sql_mov);
                $stmt_mov->execute([
                    $producto_id,
                    2, // sucursal Chinaca
                    'ingreso',
                    $stock_chinaca,
                    0, // cantidad_anterior
                    $stock_chinaca, // cantidad_nueva
                    'Stock inicial',
                    $_SESSION['usuario_id'],
                    'ajuste_manual'
                ]);
            }
        }
        
        // Registrar auditoría
        registrar_auditoria(
            'INSERT',
            'productos',
            $producto_id,
            "Producto creado: $codigo - $nombre"
        );
        
        $pdo->commit();
        
        responder_json(
            true,
            [
                'producto_id' => $producto_id,
                'codigo' => $codigo,
                'nombre' => $nombre,
                'stock_total' => $stock_los_arcos + $stock_chinaca
            ],
            'Producto creado exitosamente',
            'PRODUCTO_CREADO'
        );
        
    } catch (Exception $e) {
        $pdo->rollBack();
        responder_json(
            false,
            ['error_detalle' => $e->getMessage()],
            'Error al crear el producto: ' . $e->getMessage(),
            'ERROR_TRANSACCION'
        );
    }
    
} catch (Exception $e) {
    responder_json(false, null, 'Error al crear producto: ' . $e->getMessage(), 'ERROR_CREAR');
}
