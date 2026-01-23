<?php
/**
 * ================================================
 * API: LISTAR ABONOS DE CRÉDITOS (CORREGIDO)
 * ================================================
 * Endpoint para consultar historial de abonos a créditos
 * 
 * Método: GET
 * Autenticación: Requerida
 * Permisos: creditos.ver
 * 
 * Parámetros GET (todos opcionales):
 * - credito_id: Filtrar por crédito específico
 * - cliente_id: Filtrar por cliente
 * - forma_pago: efectivo, tarjeta_debito, tarjeta_credito, transferencia, cheque
 * - usuario_id: Filtrar por usuario que registró
 * - caja_id: Filtrar por caja
 * - fecha_desde: Fecha inicio (YYYY-MM-DD)
 * - fecha_hasta: Fecha fin (YYYY-MM-DD)
 * - monto_min: Monto mínimo
 * - monto_max: Monto máximo
 * - buscar_cliente: Búsqueda por nombre de cliente
 * - limit: Límite de resultados (default: 100, max: 500)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": [...],
 *   "message": "X abono(s) encontrado(s)"
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('GET');
verificar_api_permiso('creditos', 'ver');

try {
    // Preparar filtros
    $where = array();
    $params = array();
    
    if (isset($_GET['credito_id']) && !empty($_GET['credito_id'])) {
        $where[] = "a.credito_id = ?";
        $params[] = (int)$_GET['credito_id'];
    }
    
    if (isset($_GET['cliente_id']) && !empty($_GET['cliente_id'])) {
        $where[] = "cr.cliente_id = ?";
        $params[] = (int)$_GET['cliente_id'];
    }
    
    if (isset($_GET['forma_pago']) && !empty($_GET['forma_pago'])) {
        $forma = strtolower($_GET['forma_pago']);
        $formas_validas = array('efectivo', 'tarjeta_debito', 'tarjeta_credito', 'transferencia', 'cheque');
        
        if (in_array($forma, $formas_validas)) {
            $where[] = "a.forma_pago = ?";
            $params[] = $forma;
        }
    }
    
    if (isset($_GET['usuario_id']) && !empty($_GET['usuario_id'])) {
        $where[] = "a.usuario_id = ?";
        $params[] = (int)$_GET['usuario_id'];
    }
    
    if (isset($_GET['caja_id']) && !empty($_GET['caja_id'])) {
        $where[] = "a.caja_id = ?";
        $params[] = (int)$_GET['caja_id'];
    }
    
    if (isset($_GET['fecha_desde']) && !empty($_GET['fecha_desde'])) {
        $where[] = "a.fecha_abono >= ?";
        $params[] = $_GET['fecha_desde'];
    }
    
    if (isset($_GET['fecha_hasta']) && !empty($_GET['fecha_hasta'])) {
        $where[] = "a.fecha_abono <= ?";
        $params[] = $_GET['fecha_hasta'];
    }
    
    if (isset($_GET['monto_min']) && !empty($_GET['monto_min'])) {
        $where[] = "a.monto >= ?";
        $params[] = floatval($_GET['monto_min']);
    }
    
    if (isset($_GET['monto_max']) && !empty($_GET['monto_max'])) {
        $where[] = "a.monto <= ?";
        $params[] = floatval($_GET['monto_max']);
    }
    
    if (isset($_GET['buscar_cliente']) && !empty($_GET['buscar_cliente'])) {
        $where[] = "cl.nombre LIKE ?";
        $params[] = "%{$_GET['buscar_cliente']}%";
    }
    
    // Límite de resultados
    $limit = 100;
    if (isset($_GET['limit']) && !empty($_GET['limit'])) {
        $limit = min((int)$_GET['limit'], 500);
    }
    
    $where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // Consulta corregida SIN numero_credito y SIN ca.nombre
    $sql = "SELECT 
                a.id,
                a.credito_id,
                a.monto,
                a.forma_pago,
                a.fecha_abono,
                a.saldo_anterior,
                a.saldo_nuevo,
                a.observaciones,
                a.fecha_hora,
                cr.id as credito_id,
                cl.nombre as cliente_nombre,
                u.nombre as usuario_nombre,
                a.caja_id
            FROM abonos_creditos a
            INNER JOIN creditos_clientes cr ON a.credito_id = cr.id
            INNER JOIN clientes cl ON cr.cliente_id = cl.id
            INNER JOIN usuarios u ON a.usuario_id = u.id
            $where_sql
            ORDER BY a.fecha_abono DESC, a.fecha_hora DESC
            LIMIT ?";
    
    $params[] = $limit;
    
    $abonos = db_query($sql, $params);
    
    responder_json(
        true,
        $abonos,
        count($abonos) . ' abono(s) encontrado(s)'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al listar abonos: ' . $e->getMessage(),
        'ERROR_LISTAR_ABONOS'
    );
}
