<?php
/**
 * ================================================
 * API: LISTAR MOVIMIENTOS DE INVENTARIO
 * ================================================
 * Endpoint para consultar historial de movimientos de inventario
 * 
 * Método: GET
 * Autenticación: Requerida
 * Permisos: inventario.ver
 * 
 * Parámetros GET (todos opcionales):
 * - producto_id: Filtrar por producto
 * - sucursal_id: Filtrar por sucursal
 * - tipo_movimiento: ingreso, salida, ajuste, transferencia, venta
 * - referencia_tipo: venta, compra, transferencia, ajuste_manual
 * - referencia_id: ID de la referencia
 * - usuario_id: Filtrar por usuario
 * - fecha_desde: Fecha inicio (YYYY-MM-DD)
 * - fecha_hasta: Fecha fin (YYYY-MM-DD)
 * - buscar_producto: Búsqueda por nombre o código
 * - limit: Límite de resultados (default: 100, max: 500)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": [...],
 *   "message": "X movimiento(s) encontrado(s)"
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/movimiento_inventario.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('GET');
verificar_api_permiso('inventario', 'ver');

try {
    // Preparar filtros
    $filtros = array();
    
    if (isset($_GET['producto_id']) && !empty($_GET['producto_id'])) {
        $filtros['producto_id'] = (int)$_GET['producto_id'];
    }
    
    if (isset($_GET['sucursal_id']) && !empty($_GET['sucursal_id'])) {
        $filtros['sucursal_id'] = (int)$_GET['sucursal_id'];
    }
    
    if (isset($_GET['tipo_movimiento']) && !empty($_GET['tipo_movimiento'])) {
        $tipo = strtolower($_GET['tipo_movimiento']);
        $tipos_validos = array('ingreso', 'salida', 'ajuste', 'transferencia', 'venta');
        
        if (in_array($tipo, $tipos_validos)) {
            $filtros['tipo_movimiento'] = $tipo;
        }
    }
    
    if (isset($_GET['referencia_tipo']) && !empty($_GET['referencia_tipo'])) {
        $filtros['referencia_tipo'] = $_GET['referencia_tipo'];
    }
    
    if (isset($_GET['referencia_id']) && !empty($_GET['referencia_id'])) {
        $filtros['referencia_id'] = (int)$_GET['referencia_id'];
    }
    
    if (isset($_GET['usuario_id']) && !empty($_GET['usuario_id'])) {
        $filtros['usuario_id'] = (int)$_GET['usuario_id'];
    }
    
    if (isset($_GET['fecha_desde']) && !empty($_GET['fecha_desde'])) {
        $filtros['fecha_desde'] = $_GET['fecha_desde'];
    }
    
    if (isset($_GET['fecha_hasta']) && !empty($_GET['fecha_hasta'])) {
        $filtros['fecha_hasta'] = $_GET['fecha_hasta'];
    }
    
    if (isset($_GET['buscar_producto']) && !empty($_GET['buscar_producto'])) {
        $filtros['buscar_producto'] = $_GET['buscar_producto'];
    }
    
    // Límite de resultados
    if (isset($_GET['limit']) && !empty($_GET['limit'])) {
        $limit = (int)$_GET['limit'];
        // Máximo 500 para evitar sobrecargas
        $filtros['limit'] = min($limit, 500);
    }
    
    // Obtener movimientos
    $movimientos = MovimientoInventario::listar($filtros);
    
    responder_json(
        true,
        $movimientos,
        count($movimientos) . ' movimiento(s) encontrado(s)'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al listar movimientos: ' . $e->getMessage(),
        'ERROR_LISTAR_MOVIMIENTOS'
    );
}
