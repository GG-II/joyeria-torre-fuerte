<?php
/**
 * ================================================
 * API: AJUSTAR STOCK
 * ================================================
 * Endpoint para ajustar el stock de un producto en una sucursal
 * 
 * Método: POST
 * Autenticación: Requerida
 * Permisos: inventario.editar
 * 
 * Parámetros POST:
 * - tipo_ajuste: Tipo de ajuste ('manual', 'incremento', 'decremento')
 * - producto_id: ID del producto (requerido)
 * - sucursal_id: ID de la sucursal (requerido)
 * - cantidad: Cantidad a ajustar (requerido para incremento/decremento)
 * - cantidad_nueva: Nueva cantidad exacta (requerido para manual)
 * - motivo: Motivo del ajuste (requerido)
 * 
 * Tipos de ajuste:
 * - 'manual': Establece una cantidad exacta
 * - 'incremento' o 'entrada': Suma al stock actual
 * - 'decremento' o 'salida': Resta del stock actual
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "cantidad_anterior": 10,
 *     "cantidad_nueva": 15,
 *     "diferencia": 5,
 *     "tipo_ajuste": "incremento"
 *   },
 *   "message": "Stock ajustado correctamente"
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
    // Validar campos requeridos base
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
    
    // Obtener cantidad anterior
    $inventario_actual = Inventario::obtenerPorProductoYSucursal($producto_id, $sucursal_id);
    $cantidad_anterior = $inventario_actual ? (int)$inventario_actual['cantidad'] : 0;
    
    $cantidad_nueva = 0;
    $resultado = false;
    
    // Procesar según tipo de ajuste
    switch ($tipo_ajuste) {
        case 'manual':
            // Ajuste manual - establece cantidad exacta
            if (!isset($_POST['cantidad_nueva'])) {
                responder_json(false, null, 'Para ajuste manual se requiere cantidad_nueva', 'CANTIDAD_NUEVA_REQUERIDA');
            }
            
            $cantidad_nueva = obtener_post('cantidad_nueva', null, 'int');
            
            if ($cantidad_nueva < 0) {
                responder_json(false, null, 'La cantidad nueva no puede ser negativa', 'CANTIDAD_INVALIDA');
            }
            
            $resultado = Inventario::ajustarStock($producto_id, $sucursal_id, $cantidad_nueva, $motivo);
            break;
            
        case 'incremento':
        case 'entrada':
            // Incremento - suma al stock actual
            if (!isset($_POST['cantidad'])) {
                responder_json(false, null, 'Para incremento se requiere cantidad', 'CANTIDAD_REQUERIDA');
            }
            
            $cantidad = obtener_post('cantidad', null, 'int');
            
            if ($cantidad <= 0) {
                responder_json(false, null, 'La cantidad debe ser mayor a 0', 'CANTIDAD_INVALIDA');
            }
            
            $resultado = Inventario::incrementarStock($producto_id, $sucursal_id, $cantidad, $motivo);
            $cantidad_nueva = $cantidad_anterior + $cantidad;
            break;
            
        case 'decremento':
        case 'salida':
            // Decremento - resta del stock actual
            if (!isset($_POST['cantidad'])) {
                responder_json(false, null, 'Para decremento se requiere cantidad', 'CANTIDAD_REQUERIDA');
            }
            
            $cantidad = obtener_post('cantidad', null, 'int');
            
            if ($cantidad <= 0) {
                responder_json(false, null, 'La cantidad debe ser mayor a 0', 'CANTIDAD_INVALIDA');
            }
            
            // Verificar stock suficiente
            if ($cantidad > $cantidad_anterior) {
                responder_json(
                    false,
                    [
                        'stock_actual' => $cantidad_anterior,
                        'cantidad_solicitada' => $cantidad,
                        'faltante' => $cantidad - $cantidad_anterior
                    ],
                    "Stock insuficiente. Disponible: {$cantidad_anterior}, Solicitado: {$cantidad}",
                    'STOCK_INSUFICIENTE'
                );
            }
            
            $resultado = Inventario::decrementarStock($producto_id, $sucursal_id, $cantidad, $motivo);
            $cantidad_nueva = $cantidad_anterior - $cantidad;
            break;
    }
    
    if (!$resultado) {
        throw new Exception('No se pudo ajustar el stock');
    }
    
    // Calcular diferencia
    $diferencia = $cantidad_nueva - $cantidad_anterior;
    
    // Responder con éxito
    responder_json(
        true,
        [
            'cantidad_anterior' => $cantidad_anterior,
            'cantidad_nueva' => $cantidad_nueva,
            'diferencia' => $diferencia,
            'tipo_ajuste' => $tipo_ajuste,
            'producto_id' => $producto_id,
            'sucursal_id' => $sucursal_id
        ],
        'Stock ajustado correctamente'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al ajustar stock: ' . $e->getMessage(),
        'ERROR_AJUSTAR_STOCK'
    );
}