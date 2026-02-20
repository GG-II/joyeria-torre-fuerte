<?php
/**
 * ================================================
 * API: LISTAR CAJAS
 * ================================================
 * Endpoint para obtener listado de cajas con filtros
 * 
 * Método: GET
 * Autenticación: Requerida
 * Permisos: caja.ver
 * 
 * Parámetros GET (todos opcionales):
 * - fecha_desde: YYYY-MM-DD
 * - fecha_hasta: YYYY-MM-DD
 * - sucursal_id: Filtrar por sucursal
 * - usuario_id: Filtrar por usuario
 * - estado: abierta, cerrada
 * - limite: Límite de resultados (default: 100)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": [...]
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/caja.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('GET');
verificar_api_permiso('caja', 'ver');

try {
    global $pdo;
    
    // Preparar filtros
    $filtros = [];
    $params = [];
    
    // Fecha desde
    if (!empty($_GET['fecha_desde'])) {
        $filtros[] = "c.fecha_apertura >= ?";
        $params[] = $_GET['fecha_desde'] . ' 00:00:00';
    }
    
    // Fecha hasta
    if (!empty($_GET['fecha_hasta'])) {
        $filtros[] = "c.fecha_apertura <= ?";
        $params[] = $_GET['fecha_hasta'] . ' 23:59:59';
    }
    
    // Sucursal
    if (!empty($_GET['sucursal_id'])) {
        $filtros[] = "c.sucursal_id = ?";
        $params[] = (int)$_GET['sucursal_id'];
    }
    
    // Usuario
    if (!empty($_GET['usuario_id'])) {
        $filtros[] = "c.usuario_id = ?";
        $params[] = (int)$_GET['usuario_id'];
    }
    
    // Estado
    if (!empty($_GET['estado'])) {
        $filtros[] = "c.estado = ?";
        $params[] = $_GET['estado'];
    }
    
    // Límite
    $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 100;
    
    // Construir WHERE
    $where = count($filtros) > 0 ? 'WHERE ' . implode(' AND ', $filtros) : '';
    
    // Query
    $sql = "
        SELECT 
            c.id,
            c.sucursal_id,
            s.nombre AS sucursal_nombre,
            c.usuario_id,
            u.nombre AS usuario_nombre,
            c.fecha_apertura,
            c.fecha_cierre,
            c.monto_inicial,
            c.monto_esperado,
            c.monto_real,
            c.diferencia,
            c.observaciones_cierre,
            c.estado
        FROM cajas c
        LEFT JOIN sucursales s ON c.sucursal_id = s.id
        LEFT JOIN usuarios u ON c.usuario_id = u.id
        {$where}
        ORDER BY c.fecha_apertura DESC
        LIMIT ?
    ";
    
    $params[] = $limite;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $cajas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    responder_json(
        true,
        $cajas,
        count($cajas) . ' caja(s) encontrada(s)'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al listar cajas: ' . $e->getMessage(),
        'ERROR_LISTAR_CAJAS'
    );
}