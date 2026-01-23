<?php
/**
 * ================================================
 * API: ELIMINAR CLIENTE
 * ================================================
 * Endpoint para eliminar (desactivar) un cliente
 * 
 * Método: POST
 * Autenticación: Requerida
 * Permisos: clientes.eliminar
 * 
 * Parámetros POST:
 * - id: ID del cliente a eliminar
 * 
 * IMPORTANTE: No se puede eliminar un cliente con créditos activos
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": null,
 *   "message": "Cliente eliminado exitosamente"
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/cliente.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('POST');
verificar_api_permiso('clientes', 'eliminar');

try {
    // Validar ID requerido
    validar_campos_requeridos(['id'], 'POST');
    
    $id = obtener_post('id', null, 'int');
    
    // Verificar que el cliente exista
    if (!Cliente::existe($id)) {
        responder_json(
            false,
            null,
            'El cliente no existe',
            'CLIENTE_NO_ENCONTRADO'
        );
    }
    
    // Verificar si puede eliminarse (no tiene créditos activos)
    if (!Cliente::puedeEliminar($id)) {
        // Obtener información de los créditos
        $creditos_activos = Cliente::obtenerCreditosActivos($id);
        $total_creditos = count($creditos_activos);
        $saldo_total = 0;
        
        foreach ($creditos_activos as $credito) {
            $saldo_total += $credito['saldo_pendiente'];
        }
        
        responder_json(
            false,
            [
                'creditos_activos' => $total_creditos,
                'saldo_pendiente' => $saldo_total,
                'creditos' => $creditos_activos
            ],
            "No se puede eliminar el cliente porque tiene {$total_creditos} crédito(s) activo(s) con saldo pendiente de Q " . number_format($saldo_total, 2),
            'CLIENTE_CON_CREDITOS_ACTIVOS'
        );
    }
    
    // Eliminar cliente (soft delete)
    $resultado = Cliente::eliminar($id);
    
    if (!$resultado) {
        throw new Exception('No se pudo eliminar el cliente');
    }
    
    // Responder con éxito
    responder_json(
        true,
        null,
        'Cliente eliminado exitosamente'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al eliminar cliente: ' . $e->getMessage(),
        'ERROR_ELIMINAR_CLIENTE'
    );
}