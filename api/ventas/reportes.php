<?php
/**
 * API: Estadísticas de Ventas
 * Método: GET
 * Parámetros:
 * - tipo=diario: fecha=YYYY-MM-DD
 * - tipo=rango: fecha_inicio=YYYY-MM-DD, fecha_fin=YYYY-MM-DD
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';

try {
    $tipo = $_GET['tipo'] ?? '';
    
    if (!in_array($tipo, ['diario', 'rango'])) {
        throw new Exception('Tipo inválido. Use: diario o rango');
    }
    
    global $pdo;
    
    // Determinar fechas según tipo
    if ($tipo === 'diario') {
        $fecha = $_GET['fecha'] ?? date('Y-m-d');
        $fecha_inicio = $fecha;
        $fecha_fin = $fecha;
    } else {
        // tipo = rango
        $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-d', strtotime('-7 days'));
        $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
    }
    
    $sucursal_id = isset($_GET['sucursal_id']) ? (int)$_GET['sucursal_id'] : null;
    
    // Query base
    $sql = "
        SELECT 
            COUNT(*) as total_ventas,
            SUM(CASE WHEN estado = 'completada' THEN total ELSE 0 END) as monto_total,
            COUNT(CASE WHEN tipo_venta = 'credito' AND estado = 'completada' THEN 1 END) as ventas_credito,
            SUM(CASE WHEN tipo_venta = 'credito' AND estado = 'completada' THEN total ELSE 0 END) as monto_credito,
            COUNT(CASE WHEN estado = 'anulada' THEN 1 END) as ventas_anuladas
        FROM ventas
        WHERE fecha BETWEEN ? AND ?
    ";
    
    $params = [$fecha_inicio, $fecha_fin];
    
    if ($sucursal_id) {
        $sql .= " AND sucursal_id = ?";
        $params[] = $sucursal_id;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Calcular ventas completadas (sin anuladas)
    $ventas_completadas = $stats['total_ventas'] - $stats['ventas_anuladas'];
    
    // Calcular ticket promedio
    $ticket_promedio = $ventas_completadas > 0 
        ? $stats['monto_total'] / $ventas_completadas 
        : 0;
    
    $resultado = [
        'total_ventas' => (int)$ventas_completadas,
        'monto_total' => (float)$stats['monto_total'],
        'ticket_promedio' => (float)$ticket_promedio,
        'ventas_credito' => (int)$stats['ventas_credito'],
        'monto_credito' => (float)$stats['monto_credito'],
        'ventas_anuladas' => (int)$stats['ventas_anuladas'],
        'periodo' => [
            'tipo' => $tipo,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin
        ]
    ];
    
    echo json_encode([
        'success' => true,
        'data' => $resultado
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}