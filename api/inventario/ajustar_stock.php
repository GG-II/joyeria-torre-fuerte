<?php
/**
 * API: AJUSTAR STOCK
 * Endpoint para ajustar el stock de un producto en una sucursal
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
    // ================================================
    // LEER JSON DEL BODY
    // ================================================
    $json_input = file_get_contents('php://input');
    $datos_json = json_decode($json_input, true);
    
    // Merge con $_POST para compatibilidad
    if (json_last_error() === JSON_ERROR_NONE && !empty($datos_json)) {
        $_POST = array_merge($_POST, $datos_json);
    }
    
    // ================================================
    // VALIDAR CAMPOS REQUERIDOS
    // ================================================
    validar_campos_requeridos(['tipo_ajuste', 'producto_id', 'sucursal_id', 'motivo'], 'POST');
    
    $tipo_ajuste = obtener_post('tipo_ajuste', null, 'string');
    $producto_id = obtener_post('producto_id', null, 'int');
    $sucursal_id = obtener_post('sucursal_id', null, 'int');
    $motivo = obtener_post('motivo', null, 'string');
    
    // Validar tipo de ajuste
    $tipos_validos = ['manual', 'incremento', 'entrada', 'decremento', 'salida'];
    if (!in_array($tipo_ajuste, $tipos_validos)) {
        responder_json(
            false,
            null,
            'Tipo de ajuste inválido. Valores permitidos: manual, incremento, entrada, decremento, salida',
            'TIPO_AJUSTE_INVALIDO'
        );
    }
    
    // Verificar que el producto existe
    if (!db_exists('productos', 'id = ? AND activo = 1', [$producto_id])) {
        responder_json(false, null, 'El producto no existe o está inactivo', 'PRODUCTO_INVALIDO');
    }
    
    // Verificar que la sucursal existe
    if (!db_exists('sucursales', 'id = ? AND activo = 1', [$sucursal_id])) {
        responder_json(false, null, 'La sucursal no existe o está inactiva', 'SUCURSAL_INVALIDA');
    }
    
    // Validar motivo no vacío
    if (empty(trim($motivo))) {
        responder_json(false, null, 'El motivo es requerido y no puede estar vacío', 'MOTIVO_REQUERIDO');
    }
    
    // ================================================
    // PROCESAR SEGÚN TIPO DE AJUSTE
    // ================================================
    
    // Obtener inventario actual
    $inventario_actual = Inventario::obtenerPorProductoYSucursal($producto_id, $sucursal_id);
    $cantidad_anterior = $inventario_actual ? (int)$inventario_actual['cantidad'] : 0;
    $cantidad_nueva = $cantidad_anterior;
    
    switch ($tipo_ajuste) {
        case 'manual':
            // Ajuste manual: establecer cantidad exacta
            $cantidad_nueva = obtener_post('cantidad_nueva', null, 'int');
            if ($cantidad_nueva === null) {
                responder_json(false, null, 'Para ajuste manual se requiere cantidad_nueva', 'CANTIDAD_NUEVA_REQUERIDA');
            }
            if ($cantidad_nueva < 0) {
                responder_json(false, null, 'La cantidad nueva no puede ser negativa', 'CANTIDAD_INVALIDA');
            }
            break;
            
        case 'incremento':
        case 'entrada':
            // Incremento: sumar cantidad
            $cantidad = obtener_post('cantidad', null, 'int');
            if ($cantidad === null || $cantidad <= 0) {
                responder_json(false, null, 'Para incremento se requiere cantidad positiva', 'CANTIDAD_REQUERIDA');
            }
            $cantidad_nueva = $cantidad_anterior + $cantidad;
            break;
            
        case 'decremento':
        case 'salida':
            // Decremento: restar cantidad
            $cantidad = obtener_post('cantidad', null, 'int');
            if ($cantidad === null || $cantidad <= 0) {
                responder_json(false, null, 'Para decremento se requiere cantidad positiva', 'CANTIDAD_REQUERIDA');
            }
            
            // Verificar que hay stock suficiente
            if ($cantidad > $cantidad_anterior) {
                responder_json(
                    false,
                    [
                        'stock_actual' => $cantidad_anterior,
                        'cantidad_solicitada' => $cantidad
                    ],
                    "Stock insuficiente. Disponible: {$cantidad_anterior}, solicitado: {$cantidad}",
                    'STOCK_INSUFICIENTE'
                );
            }
            
            $cantidad_nueva = $cantidad_anterior - $cantidad;
            break;
    }
    
    // ================================================
    // ACTUALIZAR INVENTARIO
    // ================================================
    
    try {
        // Obtener usuario actual
        $usuario_id = $_SESSION['usuario_id'] ?? null;
        
        switch ($tipo_ajuste) {
            case 'incremento':
            case 'entrada':
                $cantidad = obtener_post('cantidad', null, 'int');
                Inventario::incrementarStock(
                    $producto_id, 
                    $sucursal_id, 
                    $cantidad, 
                    $motivo, 
                    'ajuste_manual', 
                    null
                );
                $diferencia = $cantidad;
                break;
                
            case 'decremento':
            case 'salida':
                $cantidad = obtener_post('cantidad', null, 'int');
                Inventario::decrementarStock(
                    $producto_id, 
                    $sucursal_id, 
                    $cantidad, 
                    $motivo, 
                    'ajuste_manual', 
                    null
                );
                $diferencia = -$cantidad;
                break;
                
            case 'manual':
                $cantidad_nueva = obtener_post('cantidad_nueva', null, 'int');
                Inventario::ajustarStock(
                    $producto_id, 
                    $sucursal_id, 
                    $cantidad_nueva, 
                    $motivo
                );
                $diferencia = $cantidad_nueva - $cantidad_anterior;
                break;
        }
        
        // Obtener inventario actualizado
        $inventario_nuevo = Inventario::obtenerPorProductoYSucursal($producto_id, $sucursal_id);
        $cantidad_nueva = $inventario_nuevo ? (int)$inventario_nuevo['cantidad'] : 0;
        
        // Respuesta exitosa
        responder_json(
            true,
            [
                'cantidad_anterior' => $cantidad_anterior,
                'cantidad_nueva' => $cantidad_nueva,
                'diferencia' => $diferencia,
                'tipo_ajuste' => $tipo_ajuste
            ],
            'Stock ajustado correctamente',
            'AJUSTE_EXITOSO'
        );
        
    } catch (Exception $e) {
        throw $e;
    }
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al ajustar stock: ' . $e->getMessage(),
        'ERROR_AJUSTAR_STOCK'
    );
}
