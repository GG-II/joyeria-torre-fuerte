<?php
/**
 * ================================================
 * API: HISTORIAL DE COMPRAS DEL CLIENTE
 * ================================================
 * Endpoint para obtener el historial de compras de un cliente
 * 
 * Método: GET
 * Autenticación: Requerida
 * Permisos: clientes.ver
 * 
 * Parámetros GET:
 * - cliente_id: ID del cliente (requerido)
 * - limite: Número de compras a mostrar (default: 50, max: 100)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "cliente": {...},
 *     "compras": [...],
 *     "total_compras": 25,
 *     "total_comprado": 15000.50
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
    $limite = obtener_get('limite', 50, 'int');
    
    // Validar límite
    if ($limite < 1) $limite = 50;
    if ($limite > 100) $limite = 100;
    
    // Verificar que el cliente existe
    $cliente = Cliente::obtenerPorId($cliente_id);
    
    if (!$cliente) {
        responder_json(false, null, 'El cliente no existe', 'CLIENTE_NO_ENCONTRADO');
    }
    
    // Obtener historial de compras
    $compras = Cliente::obtenerHistorialCompras($cliente_id, $limite);
    
    // Preparar respuesta
    $respuesta = [
        'cliente' => [
            'id' => $cliente['id'],
            'nombre' => $cliente['nombre'],
            'telefono' => $cliente['telefono'],
            'tipo_cliente' => $cliente['tipo_cliente']
        ],
        'compras' => $compras,
        'total_compras' => (int)$cliente['total_compras'],
        'total_comprado' => (float)$cliente['total_comprado']
    ];
    
    responder_json(
        true,
        $respuesta,
        count($compras) . ' compra(s) en el historial'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al obtener historial de compras: ' . $e->getMessage(),
        'ERROR_HISTORIAL_COMPRAS'
    );
}