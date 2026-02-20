<?php
/**
 * ================================================
 * API: REGISTRAR MOVIMIENTO MANUAL EN CAJA
 * ================================================
 * Endpoint para registrar ingresos y egresos manuales
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/caja.php';

header('Content-Type: application/json; charset=utf-8');

verificar_api_autenticacion();
validar_metodo_http('POST');
verificar_api_permiso('caja', 'editar');

try {
    $json_input = file_get_contents('php://input');
    $datos = json_decode($json_input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE || empty($datos)) {
        $datos = $_POST;
    }
    
    // Validar campos requeridos
    if (empty($datos['caja_id'])) {
        responder_json(false, null, 'El ID de la caja es requerido', 'CAJA_ID_REQUERIDO');
    }
    
    if (empty($datos['tipo_movimiento'])) {
        responder_json(false, null, 'El tipo de movimiento es requerido', 'TIPO_REQUERIDO');
    }
    
    if (empty($datos['categoria'])) {
        responder_json(false, null, 'La categoría es requerida', 'CATEGORIA_REQUERIDA');
    }
    
    if (empty($datos['concepto'])) {
        responder_json(false, null, 'El concepto es requerido', 'CONCEPTO_REQUERIDO');
    }
    
    if (!isset($datos['monto']) || $datos['monto'] <= 0) {
        responder_json(false, null, 'El monto debe ser mayor a 0', 'MONTO_INVALIDO');
    }
    
    $caja_id = (int)$datos['caja_id'];
    $tipo_movimiento = $datos['tipo_movimiento'];
    $categoria = $datos['categoria'];
    $concepto = trim($datos['concepto']);
    $monto = (float)$datos['monto'];
    $usuario_id = usuario_actual_id();
    
    // Validar que la categoría sea válida
    if (!in_array($categoria, ['ingreso', 'egreso'])) {
        responder_json(false, null, 'Categoría inválida (debe ser ingreso o egreso)', 'CATEGORIA_INVALIDA');
    }
    
    // Validar que la caja exista y esté abierta
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT estado FROM cajas WHERE id = ?");
    $stmt->execute([$caja_id]);
    $caja = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$caja) {
        responder_json(false, null, 'La caja no existe', 'CAJA_NO_ENCONTRADA');
    }
    
    if ($caja['estado'] !== 'abierta') {
        responder_json(false, null, 'La caja no está abierta', 'CAJA_CERRADA');
    }
    
    // Registrar movimiento usando el modelo
    $movimiento_id = Caja::registrarMovimiento([
        'caja_id' => $caja_id,
        'tipo_movimiento' => $tipo_movimiento,
        'categoria' => $categoria,
        'concepto' => $concepto,
        'monto' => $monto,
        'usuario_id' => $usuario_id,
        'referencia_tipo' => 'manual',
        'referencia_id' => null
    ]);
    
    if (!$movimiento_id) {
        throw new Exception('No se pudo registrar el movimiento');
    }
    
    // Obtener el movimiento registrado
    $stmt = $pdo->prepare("
        SELECT 
            id,
            tipo_movimiento,
            categoria,
            concepto,
            monto,
            fecha_hora
        FROM movimientos_caja
        WHERE id = ?
    ");
    $stmt->execute([$movimiento_id]);
    $movimiento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $tipo_texto = $categoria === 'ingreso' ? 'Ingreso' : 'Egreso';
    
    responder_json(
        true,
        [
            'movimiento' => $movimiento,
            'caja_id' => $caja_id
        ],
        $tipo_texto . ' de Q ' . number_format($monto, 2) . ' registrado exitosamente'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al registrar movimiento: ' . $e->getMessage(),
        'ERROR_REGISTRAR_MOVIMIENTO'
    );
}