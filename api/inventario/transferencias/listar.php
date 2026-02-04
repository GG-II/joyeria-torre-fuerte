<?php
/**
 * API - LISTAR TRANSFERENCIAS
 * Endpoint: GET /api/inventario/transferencias/listar.php
 */

require_once '../../../config.php';
require_once '../../../includes/db.php';
require_once '../../../includes/api-helpers.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('GET');
verificar_api_permiso('inventario', 'ver');

try {
    // Obtener parámetros opcionales
    $fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : null;
    $fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : null;
    $sucursal_origen = isset($_GET['sucursal_origen']) ? intval($_GET['sucursal_origen']) : null;
    $limite = isset($_GET['limite']) ? intval($_GET['limite']) : 100;
    
    // Construir query
    $sql = "SELECT 
                t.id,
                t.sucursal_origen_id,
                t.sucursal_destino_id,
                t.usuario_id,
                t.estado,
                t.observaciones,
                t.fecha_creacion,
                t.fecha_completado,
                so.nombre as sucursal_origen_nombre,
                sd.nombre as sucursal_destino_nombre,
                u.nombre as usuario_nombre,
                dt.producto_id,
                dt.cantidad,
                p.codigo as producto_codigo,
                p.nombre as producto_nombre
            FROM transferencias_inventario t
            INNER JOIN detalle_transferencias_inventario dt ON t.id = dt.transferencia_id
            INNER JOIN sucursales so ON t.sucursal_origen_id = so.id
            INNER JOIN sucursales sd ON t.sucursal_destino_id = sd.id
            INNER JOIN usuarios u ON t.usuario_id = u.id
            INNER JOIN productos p ON dt.producto_id = p.id
            WHERE 1=1";
    
    $params = [];
    
    // Filtros opcionales
    if ($fecha_desde) {
        $sql .= " AND DATE(t.fecha_creacion) >= ?";
        $params[] = $fecha_desde;
    }
    
    if ($fecha_hasta) {
        $sql .= " AND DATE(t.fecha_creacion) <= ?";
        $params[] = $fecha_hasta;
    }
    
    if ($sucursal_origen) {
        $sql .= " AND t.sucursal_origen_id = ?";
        $params[] = $sucursal_origen;
    }
    
    $sql .= " ORDER BY t.fecha_creacion DESC LIMIT ?";
    $params[] = $limite;
    
    // Ejecutar query
    $transferencias = db_query($sql, $params);
    
    responder_json(
        true,
        $transferencias,
        count($transferencias) . ' transferencia(s) encontrada(s)',
        'TRANSFERENCIAS_LISTADAS'
    );
    
} catch (Exception $e) {
    responder_json(false, null, 'Error al listar transferencias: ' . $e->getMessage(), 'ERROR_LISTAR');
}
