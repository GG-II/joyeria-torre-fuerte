<?php
/**
 * ================================================
 * API: ANULAR VENTA
 * ================================================
 * Endpoint para anular una venta (soft delete con reversión de inventario)
 * 
 * Método: POST
 * Autenticación: Requerida
 * Permisos: ventas.eliminar
 * 
 * Parámetros POST:
 * - id: ID de la venta (requerido)
 * - motivo: Motivo de anulación (opcional pero recomendado)
 * 
 * IMPORTANTE:
 * - No se puede anular una venta ya anulada
 * - No se puede anular venta a crédito con abonos realizados
 * - La anulación devuelve el stock al inventario
 * - Si es venta normal, registra egreso en caja
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "venta_id": 123,
 *     "numero_venta": "V-01-2026-0123",
 *     "monto_anulado": 250.00
 *   },
 *   "message": "Venta anulada exitosamente"
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/venta.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('POST');
verificar_api_permiso('ventas', 'eliminar');

try {
    // Validar ID requerido
    validar_campos_requeridos(['id'], 'POST');
    
    $id = obtener_post('id', null, 'int');
    $motivo = obtener_post('motivo', '', 'string');
    
    // Verificar que la venta existe
    if (!Venta::existe($id)) {
        responder_json(false, null, 'La venta no existe', 'VENTA_NO_ENCONTRADA');
    }
    
    // Obtener información de la venta antes de anular
    $venta = Venta::obtenerPorId($id);
    
    if (!$venta) {
        responder_json(false, null, 'No se pudo obtener información de la venta', 'ERROR_OBTENER_VENTA');
    }
    
    // Verificar si puede anularse
    $puede_anular = Venta::puedeAnular($id);
    
    if (!$puede_anular['puede']) {
        responder_json(
            false,
            [
                'venta_id' => $id,
                'numero_venta' => $venta['numero_venta'],
                'estado_actual' => $venta['estado']
            ],
            $puede_anular['razon'],
            'NO_SE_PUEDE_ANULAR'
        );
    }
    
    // Anular venta
    $resultado = Venta::anular($id, $motivo);
    
    if (!$resultado) {
        throw new Exception('No se pudo anular la venta');
    }
    
    // Preparar respuesta
    $respuesta = [
        'venta_id' => $id,
        'numero_venta' => $venta['numero_venta'],
        'monto_anulado' => $venta['total'],
        'tipo_venta' => $venta['tipo_venta'],
        'fecha_venta' => $venta['fecha'],
        'motivo' => $motivo
    ];
    
    responder_json(
        true,
        $respuesta,
        "Venta {$venta['numero_venta']} anulada exitosamente"
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al anular venta: ' . $e->getMessage(),
        'ERROR_ANULAR_VENTA'
    );
}
