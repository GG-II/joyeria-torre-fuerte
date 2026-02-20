<?php
/**
 * ================================================
 * API: ACTUALIZAR ANTICIPO DE TRABAJO
 * ================================================
 * Endpoint simplificado solo para actualizar el anticipo
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';

header('Content-Type: application/json; charset=utf-8');

verificar_api_autenticacion();
validar_metodo_http('POST');
verificar_api_permiso('taller', 'editar');

try {
    $json_input = file_get_contents('php://input');
    $datos = json_decode($json_input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE || empty($datos)) {
        $datos = $_POST;
    }
    
    if (empty($datos['id'])) {
        responder_json(false, null, 'El ID del trabajo es requerido', 'ID_REQUERIDO');
    }
    
    if (!isset($datos['anticipo'])) {
        responder_json(false, null, 'El anticipo es requerido', 'ANTICIPO_REQUERIDO');
    }
    
    $id = (int)$datos['id'];
    $anticipo = (float)$datos['anticipo'];
    
    // Validar que el anticipo sea positivo
    if ($anticipo < 0) {
        responder_json(false, null, 'El anticipo no puede ser negativo', 'ANTICIPO_INVALIDO');
    }
    
    global $pdo;
    
    // Obtener trabajo actual
    $stmt = $pdo->prepare("SELECT id, anticipo, precio_total FROM trabajos_taller WHERE id = ?");
    $stmt->execute([$id]);
    $trabajo_antes = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$trabajo_antes) {
        responder_json(false, null, 'El trabajo no existe', 'TRABAJO_NO_ENCONTRADO');
    }
    
    $anticipo_antes = (float)$trabajo_antes['anticipo'];
    $diferencia = $anticipo - $anticipo_antes;
    
    // Actualizar solo el anticipo
    $sql = "UPDATE trabajos_taller SET anticipo = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $resultado = $stmt->execute([$anticipo, $id]);
    
    if (!$resultado) {
        throw new Exception('No se pudo actualizar el anticipo');
    }
    
    // ========================================
    // REGISTRAR EN CAJA SI HUBO AUMENTO
    // ========================================
    if ($diferencia > 0) {
        require_once '../../models/caja.php';
        
        $usuario_actual = usuario_actual_id();
        
        // Usar la caja abierta del usuario actual
        $caja_id = Caja::obtenerIdCajaAbierta($usuario_actual);
        
        if ($caja_id) {
            // Obtener datos del trabajo para el concepto
            $stmt = $pdo->prepare("SELECT codigo, cliente_nombre FROM trabajos_taller WHERE id = ?");
            $stmt->execute([$id]);
            $trabajo_data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            Caja::registrarMovimiento([
                'caja_id' => $caja_id,
                'tipo_movimiento' => 'anticipo_trabajo',
                'categoria' => 'ingreso',
                'concepto' => "Anticipo trabajo {$trabajo_data['codigo']} - {$trabajo_data['cliente_nombre']}",
                'monto' => $diferencia,
                'usuario_id' => $usuario_actual,
                'referencia_tipo' => 'trabajo_taller',
                'referencia_id' => $id
            ]);
        }
    }
    
    // Obtener trabajo actualizado
    $stmt = $pdo->prepare("
        SELECT 
            t.*,
            (t.precio_total - t.anticipo) as saldo
        FROM trabajos_taller t
        WHERE t.id = ?
    ");
    $stmt->execute([$id]);
    $trabajo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    responder_json(
        true,
        [
            'trabajo' => $trabajo,
            'anticipo_anterior' => $anticipo_antes,
            'anticipo_nuevo' => $anticipo,
            'diferencia' => $diferencia
        ],
        'Anticipo actualizado exitosamente'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al actualizar anticipo: ' . $e->getMessage(),
        'ERROR_ACTUALIZAR_ANTICIPO'
    );
}