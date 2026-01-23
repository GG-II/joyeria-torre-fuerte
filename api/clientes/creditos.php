<?php
/**
 * ================================================
 * API: CRÉDITOS ACTIVOS DEL CLIENTE
 * ================================================
 * Endpoint para obtener los créditos activos de un cliente
 * 
 * Método: GET
 * Autenticación: Requerida
 * Permisos: clientes.ver
 * 
 * Parámetros GET:
 * - cliente_id: ID del cliente (requerido)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "cliente": {...},
 *     "creditos": [...],
 *     "total_creditos": 2,
 *     "saldo_total": 5000.00
 *   }
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/cliente.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('GET');
verificar_api_permiso('clientes', 'ver');

try {
    // Validar cliente_id requerido
    if (!isset($_GET['cliente_id']) || empty($_GET['cliente_id'])) {
        responder_json(false, null, 'El ID del cliente es requerido', 'CLIENTE_ID_REQUERIDO');
    }
    
    $cliente_id = obtener_get('cliente_id', null, 'int');
    
    // Verificar que el cliente existe
    $cliente = Cliente::obtenerPorId($cliente_id);
    
    if (!$cliente) {
        responder_json(false, null, 'El cliente no existe', 'CLIENTE_NO_ENCONTRADO');
    }
    
    // Obtener créditos activos
    $creditos = Cliente::obtenerCreditosActivos($cliente_id);
    
    // Calcular saldo total
    $saldo_total = 0;
    foreach ($creditos as $credito) {
        $saldo_total += $credito['saldo_pendiente'];
    }
    
    // Preparar respuesta
    $respuesta = [
        'cliente' => [
            'id' => $cliente['id'],
            'nombre' => $cliente['nombre'],
            'telefono' => $cliente['telefono'],
            'limite_credito' => $cliente['limite_credito']
        ],
        'creditos' => $creditos,
        'total_creditos' => count($creditos),
        'saldo_total' => $saldo_total,
        'credito_disponible' => $cliente['limite_credito'] ? ($cliente['limite_credito'] - $saldo_total) : null
    ];
    
    responder_json(
        true,
        $respuesta,
        count($creditos) . ' crédito(s) activo(s) con saldo total de Q ' . number_format($saldo_total, 2)
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al obtener créditos: ' . $e->getMessage(),
        'ERROR_CREDITOS'
    );
}