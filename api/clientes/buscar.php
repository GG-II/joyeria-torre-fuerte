<?php
/**
 * ================================================
 * API: BUSCAR CLIENTES
 * ================================================
 * Endpoint para búsqueda rápida de clientes (autocompletado)
 * 
 * Método: GET
 * Autenticación: Requerida
 * Permisos: clientes.ver
 * 
 * Parámetros GET:
 * - termino: Término de búsqueda (opcional, si está vacío trae todos)
 * - limite: Número máximo de resultados (default: 10, max: 50)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": [
 *     {
 *       "id": 1,
 *       "nombre": "Juan Pérez",
 *       "telefono": "12345678",
 *       "nit": "1234567-8",
 *       "tipo_cliente": "publico"
 *     }
 *   ],
 *   "message": "5 cliente(s) encontrado(s)"
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
    // Obtener parámetros
    $termino = obtener_get('termino', '', 'string');
    $limite = obtener_get('limite', 10, 'int');
    
    // Validar límite
    if ($limite < 1) $limite = 10;
    if ($limite > 50) $limite = 50;
    
    // Buscar clientes
    $clientes = Cliente::buscarParaSelect($termino, $limite);
    
    // Responder
    responder_json(
        true,
        $clientes,
        count($clientes) . ' cliente(s) encontrado(s)'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al buscar clientes: ' . $e->getMessage(),
        'ERROR_BUSCAR_CLIENTES'
    );
}