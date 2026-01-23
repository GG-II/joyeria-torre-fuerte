<?php
/**
 * ================================================
 * API: LISTAR MOVIMIENTOS DE CAJA (FINAL FIX)
 * ================================================
 * Endpoint para consultar historial de movimientos de caja
 * 
 * Método: GET
 * Autenticación: Requerida
 * Permisos: caja.ver
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('GET');
verificar_api_permiso('caja', 'ver');

try {
    // Preparar filtros
    $where = array();
    $params = array();
    
    if (isset($_GET['caja_id']) && !empty($_GET['caja_id'])) {
        $where[] = "m.caja_id = ?";
        $params[] = (int)$_GET['caja_id'];
    }
    
    if (isset($_GET['tipo_movimiento']) && !empty($_GET['tipo_movimiento'])) {
        $where[] = "m.tipo_movimiento = ?";
        $params[] = $_GET['tipo_movimiento'];
    }
    
    if (isset($_GET['categoria']) && !empty($_GET['categoria'])) {
        $categoria = strtolower($_GET['categoria']);
        if (in_array($categoria, array('ingreso', 'egreso'))) {
            $where[] = "m.categoria = ?";
            $params[] = $categoria;
        }
    }
    
    if (isset($_GET['usuario_id']) && !empty($_GET['usuario_id'])) {
        $where[] = "m.usuario_id = ?";
        $params[] = (int)$_GET['usuario_id'];
    }
    
    if (isset($_GET['referencia_tipo']) && !empty($_GET['referencia_tipo'])) {
        $where[] = "m.referencia_tipo = ?";
        $params[] = $_GET['referencia_tipo'];
    }
    
    if (isset($_GET['referencia_id']) && !empty($_GET['referencia_id'])) {
        $where[] = "m.referencia_id = ?";
        $params[] = (int)$_GET['referencia_id'];
    }
    
    if (isset($_GET['fecha_desde']) && !empty($_GET['fecha_desde'])) {
        $where[] = "DATE(m.fecha_hora) >= ?";
        $params[] = $_GET['fecha_desde'];
    }
    
    if (isset($_GET['fecha_hasta']) && !empty($_GET['fecha_hasta'])) {
        $where[] = "DATE(m.fecha_hora) <= ?";
        $params[] = $_GET['fecha_hasta'];
    }
    
    if (isset($_GET['monto_min']) && !empty($_GET['monto_min'])) {
        $where[] = "m.monto >= ?";
        $params[] = floatval($_GET['monto_min']);
    }
    
    if (isset($_GET['monto_max']) && !empty($_GET['monto_max'])) {
        $where[] = "m.monto <= ?";
        $params[] = floatval($_GET['monto_max']);
    }
    
    if (isset($_GET['buscar']) && !empty($_GET['buscar'])) {
        $where[] = "m.concepto LIKE ?";
        $params[] = "%{$_GET['buscar']}%";
    }
    
    // Límite de resultados
    $limit = 100;
    if (isset($_GET['limit']) && !empty($_GET['limit'])) {
        $limit = min((int)$_GET['limit'], 500);
    }
    
    $where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // Consulta SIMPLIFICADA - Sin JOIN a cajas, solo usuarios
    $sql = "SELECT 
                m.id,
                m.caja_id,
                m.tipo_movimiento,
                m.categoria,
                m.concepto,
                m.monto,
                m.referencia_tipo,
                m.referencia_id,
                m.fecha_hora,
                m.usuario_id,
                u.nombre as usuario_nombre
            FROM movimientos_caja m
            LEFT JOIN usuarios u ON m.usuario_id = u.id
            $where_sql
            ORDER BY m.fecha_hora DESC
            LIMIT ?";
    
    $params[] = $limit;
    
    $movimientos = db_query($sql, $params);
    
    responder_json(
        true,
        $movimientos,
        count($movimientos) . ' movimiento(s) encontrado(s)'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al listar movimientos de caja: ' . $e->getMessage(),
        'ERROR_LISTAR_MOVIMIENTOS'
    );
}
