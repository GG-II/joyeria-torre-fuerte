<?php
/**
 * ================================================
 * API: DETALLE DE VENTA
 * ================================================
 * Endpoint para obtener el detalle completo de una venta
 * 
 * Método: GET
 * Autenticación: Requerida
 * Permisos: ventas.ver
 * 
 * Parámetros GET:
 * - id: ID de la venta (requerido)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     ... datos de la venta ...,
 *     "detalles": [...],      // productos
 *     "formas_pago": [...],   // pagos realizados
 *     "credito": {...}        // info crédito (si aplica)
 *   }
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/venta.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('GET');
verificar_api_permiso('ventas', 'ver');

try {
    // Validar ID requerido
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        responder_json(false, null, 'El ID de la venta es requerido', 'ID_REQUERIDO');
    }
    
    $id = obtener_get('id', null, 'int');
    
    // Obtener venta completa
    $venta = Venta::obtenerPorId($id);
    
    if (!$venta) {
        responder_json(false, null, 'La venta no existe', 'VENTA_NO_ENCONTRADA');
    }
    
    // Responder con venta completa
    // El método obtenerPorId ya incluye:
    // - venta['detalles'] → productos
    // - venta['formas_pago'] → pagos
    // - venta['credito'] → info crédito (si aplica)
    
    responder_json(
        true,
        $venta,
        "Venta {$venta['numero_venta']} obtenida exitosamente"
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al obtener detalle de venta: ' . $e->getMessage(),
        'ERROR_DETALLE_VENTA'
    );
}
