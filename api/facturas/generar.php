<?php
/**
 * ================================================
 * API: GENERAR FACTURA
 * ================================================
 * Endpoint para generar una factura para una venta
 * 
 * Método: POST
 * Autenticación: Requerida
 * Permisos: facturas.crear
 * 
 * Parámetros POST requeridos:
 * - venta_id: ID de la venta a facturar
 * 
 * Parámetros POST opcionales:
 * - tipo: simple (default) o electronica
 * - nit: NIT del cliente (default: C/F)
 * - nombre: Nombre del cliente (default: Consumidor Final)
 * - direccion: Dirección del cliente
 * - serie: Serie de la factura
 * 
 * IMPORTANTE:
 * - Facturas electrónicas requieren NIT y nombre válidos
 * - No se puede facturar una venta ya facturada
 * - No se puede facturar una venta anulada
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "id": 123,
 *     "numero_factura": "FAC-SIMPLE-00001",
 *     "factura": {...}
 *   },
 *   "message": "Factura generada exitosamente"
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/factura.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('POST');
verificar_api_permiso('facturas', 'crear');

try {
    // Leer JSON body
    $json_input = file_get_contents('php://input');
    $datos = json_decode($json_input, true);
    
    // Fallback a POST
    if (json_last_error() !== JSON_ERROR_NONE || empty($datos)) {
        $datos = $_POST;
    }
    
    // Validar campo requerido
    if (empty($datos['venta_id'])) {
        responder_json(false, null, 'El ID de la venta es requerido', 'VENTA_ID_REQUERIDO');
    }
    
    $venta_id = (int)$datos['venta_id'];
    
    // Preparar datos de la factura
    $datos_factura = array(
        'tipo' => isset($datos['tipo']) ? $datos['tipo'] : 'simple',
        'nit' => isset($datos['nit']) ? $datos['nit'] : 'C/F',
        'nombre' => isset($datos['nombre']) ? $datos['nombre'] : 'Consumidor Final',
        'direccion' => isset($datos['direccion']) ? $datos['direccion'] : null,
        'serie' => isset($datos['serie']) ? $datos['serie'] : null
    );
    
    // Validar tipo válido
    $tipos_validos = array('simple', 'electronica');
    if (!in_array($datos_factura['tipo'], $tipos_validos)) {
        responder_json(false, null, 'Tipo inválido. Use: simple o electronica', 'TIPO_INVALIDO');
    }
    
    // Validar requisitos de factura electrónica
    if ($datos_factura['tipo'] === 'electronica') {
        if ($datos_factura['nit'] === 'C/F' || empty($datos_factura['nit'])) {
            responder_json(false, null, 'El NIT es requerido para facturas electrónicas', 'NIT_REQUERIDO');
        }
        
        if ($datos_factura['nombre'] === 'Consumidor Final' || empty($datos_factura['nombre'])) {
            responder_json(false, null, 'El nombre es requerido para facturas electrónicas', 'NOMBRE_REQUERIDO');
        }
    }
    
    // Crear factura (el modelo valida internamente)
    $factura_id = Factura::crear($venta_id, $datos_factura);
    
    if (!$factura_id) {
        throw new Exception('No se pudo generar la factura. Verifique que la venta existe y no está anulada o ya facturada.');
    }
    
    // Obtener factura creada
    $factura = Factura::obtenerPorId($factura_id);
    
    responder_json(
        true,
        array(
            'id' => $factura_id,
            'numero_factura' => $factura['numero_factura'],
            'factura' => $factura
        ),
        'Factura generada exitosamente'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al generar factura: ' . $e->getMessage(),
        'ERROR_GENERAR_FACTURA'
    );
}
