<?php
/**
 * ================================================
 * API: TRANSFERIR STOCK
 * ================================================
 * Endpoint para transferir stock de un producto entre sucursales
 * 
 * Método: POST
 * Autenticación: Requerida
 * Permisos: inventario.editar
 * 
 * Parámetros POST:
 * - producto_id: ID del producto (requerido)
 * - sucursal_origen_id: ID de la sucursal origen (requerido)
 * - sucursal_destino_id: ID de la sucursal destino (requerido)
 * - cantidad: Cantidad a transferir (requerido)
 * - motivo: Motivo de la transferencia (opcional)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "transferencia_id": 123,
 *     "producto_id": 5,
 *     "origen": {...},
 *     "destino": {...},
 *     "cantidad_transferida": 3
 *   },
 *   "message": "Transferencia completada exitosamente"
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/inventario.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('POST');
verificar_api_permiso('inventario', 'editar');

try {
    global $pdo;
    
    // Validar campos requeridos
    validar_campos_requeridos([
        'producto_id',
        'sucursal_origen_id',
        'sucursal_destino_id',
        'cantidad'
    ], 'POST');
    
    $producto_id = obtener_post('producto_id', null, 'int');
    $sucursal_origen_id = obtener_post('sucursal_origen_id', null, 'int');
    $sucursal_destino_id = obtener_post('sucursal_destino_id', null, 'int');
    $cantidad = obtener_post('cantidad', null, 'int');
    $motivo = obtener_post('motivo', 'Transferencia entre sucursales', 'string');
    
    // Validaciones
    $errores = [];
    
    if ($cantidad <= 0) {
        $errores[] = 'La cantidad debe ser mayor a 0';
    }
    
    if ($sucursal_origen_id === $sucursal_destino_id) {
        $errores[] = 'La sucursal origen y destino deben ser diferentes';
    }
    
    if (!db_exists('productos', 'id = ? AND activo = 1', [$producto_id])) {
        $errores[] = 'El producto no existe o está inactivo';
    }
    
    if (!db_exists('sucursales', 'id = ? AND activo = 1', [$sucursal_origen_id])) {
        $errores[] = 'La sucursal origen no existe o está inactiva';
    }
    
    if (!db_exists('sucursales', 'id = ? AND activo = 1', [$sucursal_destino_id])) {
        $errores[] = 'La sucursal destino no existe o está inactiva';
    }
    
    if (!empty($errores)) {
        responder_json(
            false,
            ['errores' => $errores],
            'Errores de validación: ' . implode(', ', $errores),
            'VALIDACION_FALLIDA'
        );
    }
    
    // Obtener stock actual en origen
    $inventario_origen = Inventario::obtenerPorProductoYSucursal($producto_id, $sucursal_origen_id);
    
    if (!$inventario_origen) {
        responder_json(
            false,
            null,
            'El producto no tiene inventario en la sucursal origen',
            'SIN_INVENTARIO_ORIGEN'
        );
    }
    
    $stock_origen_anterior = (int)$inventario_origen['cantidad'];
    
    // Verificar stock suficiente en origen
    if ($cantidad > $stock_origen_anterior) {
        responder_json(
            false,
            [
                'stock_disponible' => $stock_origen_anterior,
                'cantidad_solicitada' => $cantidad,
                'faltante' => $cantidad - $stock_origen_anterior
            ],
            "Stock insuficiente en origen. Disponible: {$stock_origen_anterior}, Solicitado: {$cantidad}",
            'STOCK_INSUFICIENTE'
        );
    }
    
    // Iniciar transacción
    $pdo->beginTransaction();
    
    try {
        $usuario_id = usuario_actual_id();
        
        // 1. Crear registro de transferencia
        $sql_transferencia = "INSERT INTO transferencias_inventario 
                             (sucursal_origen_id, sucursal_destino_id, usuario_id, estado, observaciones)
                             VALUES (?, ?, ?, 'pendiente', ?)";
        
        $stmt = $pdo->prepare($sql_transferencia);
        $stmt->execute([$sucursal_origen_id, $sucursal_destino_id, $usuario_id, $motivo]);
        $transferencia_id = $pdo->lastInsertId();
        
        // 2. Decrementar stock en origen
        $cantidad_nueva_origen = $stock_origen_anterior - $cantidad;
        
        $sql_update_origen = "UPDATE inventario SET cantidad = ? 
                             WHERE producto_id = ? AND sucursal_id = ?";
        $pdo->prepare($sql_update_origen)->execute([$cantidad_nueva_origen, $producto_id, $sucursal_origen_id]);
        
        // 3. Registrar movimiento de salida
        $sql_mov_salida = "INSERT INTO movimientos_inventario 
                          (producto_id, sucursal_id, tipo_movimiento, cantidad, cantidad_anterior, 
                           cantidad_nueva, motivo, usuario_id, referencia_tipo, referencia_id)
                          VALUES (?, ?, 'transferencia', ?, ?, ?, ?, ?, 'transferencia', ?)";
        
        $pdo->prepare($sql_mov_salida)->execute([
            $producto_id,
            $sucursal_origen_id,
            $cantidad,
            $stock_origen_anterior,
            $cantidad_nueva_origen,
            "Transferencia a sucursal {$sucursal_destino_id}",
            $usuario_id,
            $transferencia_id
        ]);
        
        // 4. Incrementar stock en destino
        $inventario_destino = Inventario::obtenerPorProductoYSucursal($producto_id, $sucursal_destino_id);
        
        if (!$inventario_destino) {
            // Crear inventario en destino si no existe
            $pdo->prepare("INSERT INTO inventario (producto_id, sucursal_id, cantidad, stock_minimo) VALUES (?, ?, ?, 5)")
                ->execute([$producto_id, $sucursal_destino_id, 0]);
            $cantidad_anterior_destino = 0;
        } else {
            $cantidad_anterior_destino = (int)$inventario_destino['cantidad'];
        }
        
        $cantidad_nueva_destino = $cantidad_anterior_destino + $cantidad;
        
        $sql_update_destino = "UPDATE inventario SET cantidad = ? 
                              WHERE producto_id = ? AND sucursal_id = ?";
        $pdo->prepare($sql_update_destino)->execute([$cantidad_nueva_destino, $producto_id, $sucursal_destino_id]);
        
        // 5. Registrar movimiento de entrada
        $sql_mov_entrada = "INSERT INTO movimientos_inventario 
                           (producto_id, sucursal_id, tipo_movimiento, cantidad, cantidad_anterior, 
                            cantidad_nueva, motivo, usuario_id, referencia_tipo, referencia_id)
                           VALUES (?, ?, 'transferencia', ?, ?, ?, ?, ?, 'transferencia', ?)";
        
        $pdo->prepare($sql_mov_entrada)->execute([
            $producto_id,
            $sucursal_destino_id,
            $cantidad,
            $cantidad_anterior_destino,
            $cantidad_nueva_destino,
            "Transferencia desde sucursal {$sucursal_origen_id}",
            $usuario_id,
            $transferencia_id
        ]);
        
        // 6. Marcar transferencia como completada
        $pdo->prepare("UPDATE transferencias_inventario SET estado = 'completada', fecha_completado = NOW() WHERE id = ?")
            ->execute([$transferencia_id]);
        
        // 7. Registrar auditoría
        registrar_auditoria('INSERT', 'transferencias_inventario', $transferencia_id,
            "Transferencia de {$cantidad} unidades de producto {$producto_id}");
        
        // Confirmar transacción
        $pdo->commit();
        
        // Obtener nombres de sucursales
        $sucursal_origen = db_query_one('SELECT nombre FROM sucursales WHERE id = ?', [$sucursal_origen_id]);
        $sucursal_destino = db_query_one('SELECT nombre FROM sucursales WHERE id = ?', [$sucursal_destino_id]);
        
        // Responder con éxito
        responder_json(
            true,
            [
                'transferencia_id' => $transferencia_id,
                'producto_id' => $producto_id,
                'origen' => [
                    'sucursal_id' => $sucursal_origen_id,
                    'sucursal_nombre' => $sucursal_origen['nombre'],
                    'stock_anterior' => $stock_origen_anterior,
                    'stock_actual' => $cantidad_nueva_origen
                ],
                'destino' => [
                    'sucursal_id' => $sucursal_destino_id,
                    'sucursal_nombre' => $sucursal_destino['nombre'],
                    'stock_anterior' => $cantidad_anterior_destino,
                    'stock_actual' => $cantidad_nueva_destino
                ],
                'cantidad_transferida' => $cantidad,
                'motivo' => $motivo
            ],
            "Transferencia completada: {$cantidad} unidad(es) de {$sucursal_origen['nombre']} a {$sucursal_destino['nombre']}"
        );
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al transferir stock: ' . $e->getMessage(),
        'ERROR_TRANSFERIR'
    );
}
