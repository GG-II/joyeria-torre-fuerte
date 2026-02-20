<?php
/**
 * ================================================
 * API: TRANSFERIR PRODUCTO ENTRE SUCURSALES
 * ================================================
 * Endpoint para realizar transferencias de inventario entre sucursales
 * 
 * Método: POST
 * Autenticación: Requerida
 * Permisos: inventario.editar
 * 
 * Parámetros POST (FormData):
 * - producto_id: ID del producto (requerido)
 * - sucursal_origen: ID sucursal origen (requerido)
 * - sucursal_destino: ID sucursal destino (requerido)
 * - cantidad: Cantidad a transferir (requerido)
 * - motivo: Motivo de la transferencia (requerido)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "transferencia_id": 15,
 *     "producto_id": 5,
 *     "cantidad": 10,
 *     "stock_origen_anterior": 50,
 *     "stock_origen_nuevo": 40,
 *     "stock_destino_anterior": 20,
 *     "stock_destino_nuevo": 30
 *   },
 *   "message": "Transferencia realizada exitosamente"
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
    // Leer JSON del body
    $json_input = file_get_contents('php://input');
    $datos_json = json_decode($json_input, true);
    
    // Merge con $_POST para compatibilidad
    if (json_last_error() === JSON_ERROR_NONE && !empty($datos_json)) {
        $_POST = array_merge($_POST, $datos_json);
    }
    
    // ================================================
    // VALIDAR CAMPOS REQUERIDOS
    // ================================================
    validar_campos_requeridos(['producto_id', 'sucursal_origen', 'sucursal_destino', 'cantidad', 'motivo'], 'POST');
    
    $producto_id = obtener_post('producto_id', null, 'int');
    $sucursal_origen = obtener_post('sucursal_origen', null, 'int');
    $sucursal_destino = obtener_post('sucursal_destino', null, 'int');
    $cantidad = obtener_post('cantidad', null, 'int');
    $motivo = obtener_post('motivo', null, 'string');
    $usuario_id = $_SESSION['usuario_id'] ?? null;
    
    // ================================================
    // VALIDACIONES DE NEGOCIO
    // ================================================
    
    // Validar que las sucursales sean diferentes
    if ($sucursal_origen === $sucursal_destino) {
        responder_json(false, null, 'La sucursal origen y destino deben ser diferentes', 'SUCURSALES_IGUALES');
    }
    
    // Validar cantidad positiva
    if ($cantidad <= 0) {
        responder_json(false, null, 'La cantidad debe ser mayor a 0', 'CANTIDAD_INVALIDA');
    }
    
    // Validar que el producto existe
    if (!db_exists('productos', 'id = ? AND activo = 1', [$producto_id])) {
        responder_json(false, null, 'El producto no existe o está inactivo', 'PRODUCTO_INVALIDO');
    }
    
    // Validar que ambas sucursales existen
    if (!db_exists('sucursales', 'id = ? AND activo = 1', [$sucursal_origen])) {
        responder_json(false, null, 'La sucursal origen no existe o está inactiva', 'SUCURSAL_ORIGEN_INVALIDA');
    }
    
    if (!db_exists('sucursales', 'id = ? AND activo = 1', [$sucursal_destino])) {
        responder_json(false, null, 'La sucursal destino no existe o está inactiva', 'SUCURSAL_DESTINO_INVALIDA');
    }
    
    // Validar motivo no vacío
    if (empty(trim($motivo))) {
        responder_json(false, null, 'El motivo es requerido', 'MOTIVO_REQUERIDO');
    }
    
    // ================================================
    // OBTENER INVENTARIOS ACTUALES
    // ================================================
    
    $inventario_origen = Inventario::obtenerPorProductoYSucursal($producto_id, $sucursal_origen);
    $inventario_destino = Inventario::obtenerPorProductoYSucursal($producto_id, $sucursal_destino);
    
    if (!$inventario_origen) {
        responder_json(false, null, 'No hay inventario registrado en la sucursal origen', 'SIN_INVENTARIO_ORIGEN');
    }
    
    $stock_origen_anterior = (int)$inventario_origen['cantidad'];
    
    // Validar stock suficiente
    if ($stock_origen_anterior < $cantidad) {
        responder_json(
            false,
            [
                'stock_disponible' => $stock_origen_anterior,
                'cantidad_solicitada' => $cantidad
            ],
            "Stock insuficiente en sucursal origen. Disponible: {$stock_origen_anterior}, solicitado: {$cantidad}",
            'STOCK_INSUFICIENTE'
        );
    }
    
    // Si no existe inventario en destino, obtener valores por defecto
    $stock_destino_anterior = 0;
    $stock_minimo_destino = 5;
    
    if ($inventario_destino) {
        $stock_destino_anterior = (int)$inventario_destino['cantidad'];
        $stock_minimo_destino = (int)$inventario_destino['stock_minimo'];
    }
    
    // ================================================
    // INICIAR TRANSACCIÓN
    // ================================================
    
    global $pdo;
    $pdo->beginTransaction();
    
    try {
        // ================================================
        // 1. CREAR TRANSFERENCIA PRINCIPAL
        // ================================================
        
        $sql_transferencia = "INSERT INTO transferencias_inventario (
            sucursal_origen_id,
            sucursal_destino_id,
            usuario_id,
            estado,
            observaciones,
            fecha_completado
        ) VALUES (?, ?, ?, 'completada', ?, NOW())";
        
        $stmt_trans = $pdo->prepare($sql_transferencia);
        $stmt_trans->execute([
            $sucursal_origen,
            $sucursal_destino,
            $usuario_id,
            $motivo
        ]);
        
        $transferencia_id = $pdo->lastInsertId();
        
        // ================================================
        // 2. CREAR DETALLE DE TRANSFERENCIA
        // ================================================
        
        $sql_detalle = "INSERT INTO detalle_transferencias_inventario (
            transferencia_id,
            producto_id,
            cantidad
        ) VALUES (?, ?, ?)";
        
        $stmt_detalle = $pdo->prepare($sql_detalle);
        $stmt_detalle->execute([
            $transferencia_id,
            $producto_id,
            $cantidad
        ]);
        
        // ================================================
        // 3. ACTUALIZAR STOCK EN SUCURSAL ORIGEN (RESTAR)
        // ================================================
        
        $stock_origen_nuevo = $stock_origen_anterior - $cantidad;
        
        $sql_update_origen = "UPDATE inventario 
                             SET cantidad = ?,
                                 fecha_actualizacion = NOW()
                             WHERE producto_id = ? AND sucursal_id = ?";
        
        $stmt_origen = $pdo->prepare($sql_update_origen);
        $stmt_origen->execute([
            $stock_origen_nuevo,
            $producto_id,
            $sucursal_origen
        ]);
        
        // ================================================
        // 4. ACTUALIZAR STOCK EN SUCURSAL DESTINO (SUMAR)
        // ================================================
        
        $stock_destino_nuevo = $stock_destino_anterior + $cantidad;
        
        if ($inventario_destino) {
            // Actualizar existente
            $sql_update_destino = "UPDATE inventario 
                                  SET cantidad = ?,
                                      fecha_actualizacion = NOW()
                                  WHERE producto_id = ? AND sucursal_id = ?";
            
            $stmt_destino = $pdo->prepare($sql_update_destino);
            $stmt_destino->execute([
                $stock_destino_nuevo,
                $producto_id,
                $sucursal_destino
            ]);
        } else {
            // Crear nuevo registro
            $sql_insert_destino = "INSERT INTO inventario (
                producto_id,
                sucursal_id,
                cantidad,
                stock_minimo,
                es_compartido
            ) VALUES (?, ?, ?, ?, 0)";
            
            $stmt_destino = $pdo->prepare($sql_insert_destino);
            $stmt_destino->execute([
                $producto_id,
                $sucursal_destino,
                $stock_destino_nuevo,
                $stock_minimo_destino
            ]);
        }
        
        // ================================================
        // 5. REGISTRAR MOVIMIENTO EN ORIGEN (SALIDA)
        // ================================================
        
        $sql_mov_origen = "INSERT INTO movimientos_inventario (
            producto_id,
            sucursal_id,
            tipo_movimiento,
            cantidad,
            cantidad_anterior,
            cantidad_nueva,
            motivo,
            usuario_id,
            referencia_tipo,
            referencia_id,
            fecha_hora
        ) VALUES (?, ?, 'transferencia', ?, ?, ?, ?, ?, 'transferencia', ?, NOW())";
        
        $stmt_mov_origen = $pdo->prepare($sql_mov_origen);
        $stmt_mov_origen->execute([
            $producto_id,
            $sucursal_origen,
            $cantidad,
            $stock_origen_anterior,
            $stock_origen_nuevo,
            "Transferencia a sucursal {$sucursal_destino}",
            $usuario_id,
            $transferencia_id
        ]);
        
        // ================================================
        // 6. REGISTRAR MOVIMIENTO EN DESTINO (ENTRADA)
        // ================================================
        
        $sql_mov_destino = "INSERT INTO movimientos_inventario (
            producto_id,
            sucursal_id,
            tipo_movimiento,
            cantidad,
            cantidad_anterior,
            cantidad_nueva,
            motivo,
            usuario_id,
            referencia_tipo,
            referencia_id,
            fecha_hora
        ) VALUES (?, ?, 'transferencia', ?, ?, ?, ?, ?, 'transferencia', ?, NOW())";
        
        $stmt_mov_destino = $pdo->prepare($sql_mov_destino);
        $stmt_mov_destino->execute([
            $producto_id,
            $sucursal_destino,
            $cantidad,
            $stock_destino_anterior,
            $stock_destino_nuevo,
            "Transferencia desde sucursal {$sucursal_origen}",
            $usuario_id,
            $transferencia_id
        ]);
        
        // ================================================
        // 7. REGISTRAR AUDITORÍA
        // ================================================
        
        registrar_auditoria(
            'INSERT',
            'transferencias_inventario',
            $transferencia_id,
            "Transferencia de {$cantidad} unidades - Producto ID: {$producto_id} - Sucursal {$sucursal_origen} → {$sucursal_destino}"
        );
        
        // ================================================
        // COMMIT TRANSACCIÓN
        // ================================================
        
        $pdo->commit();
        
        // ================================================
        // RESPUESTA EXITOSA
        // ================================================
        
        responder_json(
            true,
            [
                'transferencia_id' => $transferencia_id,
                'producto_id' => $producto_id,
                'cantidad' => $cantidad,
                'sucursal_origen' => $sucursal_origen,
                'sucursal_destino' => $sucursal_destino,
                'stock_origen_anterior' => $stock_origen_anterior,
                'stock_origen_nuevo' => $stock_origen_nuevo,
                'stock_destino_anterior' => $stock_destino_anterior,
                'stock_destino_nuevo' => $stock_destino_nuevo
            ],
            'Transferencia realizada exitosamente',
            'TRANSFERENCIA_EXITOSA'
        );
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    responder_json(
        false,
        ['error_detalle' => $e->getMessage()],
        'Error al realizar transferencia: ' . $e->getMessage(),
        'ERROR_TRANSFERENCIA'
    );
}