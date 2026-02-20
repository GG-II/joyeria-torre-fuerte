<?php
/**
 * API: Anular venta
 * Método: POST
 * Body JSON: { venta_id, motivo }
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

// Requiere autenticación
requiere_autenticacion();

try {
    // Obtener datos
    $json = file_get_contents('php://input');
    $datos = json_decode($json, true);
    
    if (!$datos) {
        throw new Exception('Datos JSON inválidos');
    }
    
    // Validar parámetros
    if (empty($datos['venta_id'])) {
        throw new Exception('ID de venta requerido');
    }
    
    if (empty($datos['motivo'])) {
        throw new Exception('Motivo de anulación requerido');
    }
    
    $venta_id = (int)$datos['venta_id'];
    $motivo = trim($datos['motivo']);
    
    $db = getDB();
    $db->beginTransaction();
    
    // Obtener información de la venta
    $sql_venta = "
        SELECT 
            v.id,
            v.numero_venta,
            v.estado,
            v.sucursal_id,
            v.tipo_venta
        FROM ventas v
        WHERE v.id = ?
    ";
    
    $stmt = $db->prepare($sql_venta);
    $stmt->execute([$venta_id]);
    $venta = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$venta) {
        throw new Exception('Venta no encontrada');
    }
    
    // Validar que esté completada
    if ($venta['estado'] !== 'completada') {
        throw new Exception('Solo se pueden anular ventas completadas');
    }
    
    // Obtener productos vendidos
    $sql_productos = "
        SELECT 
            producto_id,
            cantidad
        FROM detalle_ventas
        WHERE venta_id = ?
    ";
    
    $stmt = $db->prepare($sql_productos);
    $stmt->execute([$venta_id]);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Revertir inventario - devolver productos al stock
    foreach ($productos as $prod) {
        $sql_inventario = "
            UPDATE inventario 
            SET cantidad = cantidad + ?
            WHERE producto_id = ? 
              AND sucursal_id = ?
        ";
        
        $stmt = $db->prepare($sql_inventario);
        $stmt->execute([
            $prod['cantidad'],
            $prod['producto_id'],
            $venta['sucursal_id']
        ]);
        
        // Registrar movimiento de inventario (reversión de venta)
        $sql_movimiento = "
            INSERT INTO movimientos_inventario (
                producto_id,
                sucursal_id,
                tipo_movimiento,
                cantidad,
                cantidad_anterior,
                cantidad_nueva,
                motivo,
                usuario_id,
                referencia_tipo,
                referencia_id
            )
            SELECT
                ?,
                ?,
                'ingreso',
                ?,
                i.cantidad - ?,
                i.cantidad,
                ?,
                ?,
                'venta',
                ?
            FROM inventario i
            WHERE i.producto_id = ? 
              AND i.sucursal_id = ?
        ";
        
        $stmt = $db->prepare($sql_movimiento);
        $stmt->execute([
            $prod['producto_id'],
            $venta['sucursal_id'],
            $prod['cantidad'],
            $prod['cantidad'],
            'Reversión por anulación de venta ' . $venta['numero_venta'],
            $_SESSION['usuario_id'],
            $venta_id,
            $prod['producto_id'],
            $venta['sucursal_id']
        ]);
    }
    
    // Anular venta
    $sql_anular = "
        UPDATE ventas 
        SET estado = 'anulada',
            motivo_anulacion = ?,
            fecha_actualizacion = NOW()
        WHERE id = ?
    ";
    
    $stmt = $db->prepare($sql_anular);
    $stmt->execute([$motivo, $venta_id]);
    
    // Registrar en audit log
    registrar_auditoria(
        $_SESSION['usuario_id'],
        'ANULAR_VENTA',
        'ventas',
        $venta_id,
        json_encode([
            'numero_venta' => $venta['numero_venta'],
            'motivo' => $motivo,
            'productos_revertidos' => count($productos)
        ])
    );
    
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Venta anulada exitosamente',
        'data' => [
            'venta_id' => $venta_id,
            'numero_venta' => $venta['numero_venta']
        ]
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}