<?php
/**
 * ================================================
 * API: OBTENER MOVIMIENTOS DE CAJA
 * ================================================
 * Endpoint para obtener todos los movimientos de una caja específica
 * 
 * Método: GET
 * Autenticación: Requerida
 * Permisos: caja.ver
 * 
 * Parámetros GET:
 * - caja_id: ID de la caja (requerido)
 * - limite: Límite de resultados (opcional, default: 500)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": [
 *     {
 *       "id": 1,
 *       "caja_id": 1,
 *       "tipo_movimiento": "ingreso",
 *       "categoria": "venta",
 *       "concepto": "Venta V-01-2026-0001",
 *       "monto": 100.00,
 *       "fecha_hora": "2026-01-21 10:30:00",
 *       "usuario_id": 1,
 *       "referencia_tipo": "venta",
 *       "referencia_id": 1
 *     }
 *   ]
 * }
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
    // Validar parámetro requerido
    if (empty($_GET['caja_id'])) {
        responder_json(false, null, 'El parámetro caja_id es requerido', 'CAJA_ID_REQUERIDO');
    }
    
    $caja_id = (int)$_GET['caja_id'];
    $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 500;
    
    global $pdo;
    
    // Verificar que la caja existe
    $stmt = $pdo->prepare("SELECT id FROM cajas WHERE id = ?");
    $stmt->execute([$caja_id]);
    
    if (!$stmt->fetch()) {
        responder_json(false, null, 'La caja no existe', 'CAJA_NO_ENCONTRADA');
    }
    
    // Obtener movimientos
    $sql = "
        SELECT 
            id,
            caja_id,
            tipo_movimiento,
            categoria,
            concepto,
            monto,
            fecha_hora,
            usuario_id,
            referencia_tipo,
            referencia_id
        FROM movimientos_caja
        WHERE caja_id = ?
        ORDER BY fecha_hora ASC, id ASC
        LIMIT ?
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$caja_id, $limite]);
    $movimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convertir montos a float
    foreach ($movimientos as &$mov) {
        $mov['monto'] = (float)$mov['monto'];
        $mov['id'] = (int)$mov['id'];
        $mov['caja_id'] = (int)$mov['caja_id'];
        $mov['usuario_id'] = (int)$mov['usuario_id'];
        $mov['referencia_id'] = $mov['referencia_id'] ? (int)$mov['referencia_id'] : null;
    }
    
    responder_json(
        true,
        $movimientos,
        count($movimientos) . ' movimiento(s) encontrado(s)'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al obtener movimientos: ' . $e->getMessage(),
        'ERROR_OBTENER_MOVIMIENTOS'
    );
}