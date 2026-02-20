<?php
/**
 * ================================================
 * API: CREAR CLIENTE
 * ================================================
 * Endpoint para crear un nuevo cliente
 * 
 * Método: POST
 * Autenticación: Requerida
 * Permisos: clientes.crear
 * 
 * Parámetros POST requeridos:
 * - nombre: Nombre completo del cliente
 * - telefono: Teléfono (mínimo 8 dígitos)
 * 
 * Parámetros POST opcionales:
 * - nit: NIT del cliente
 * - email: Email del cliente
 * - direccion: Dirección del cliente
 * - tipo_cliente: 'publico' o 'mayorista' (default: 'publico')
 * - tipo_mercaderias: 'oro', 'plata', 'ambas' (default: 'ambas')
 * - limite_credito: Límite de crédito en quetzales
 * - plazo_credito_dias: Plazo de crédito en días
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "id": 123,
 *     "cliente": {datos del cliente creado}
 *   },
 *   "message": "Cliente creado exitosamente"
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/cliente.php';
//require_once __DIR__ . '/../cors.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('POST');
verificar_api_permiso('clientes', 'crear');

try {
    // Validar campos requeridos
    validar_campos_requeridos(['nombre', 'telefono'], 'POST');
    
    // Preparar datos para crear
    $datos = [
        'nombre' => obtener_post('nombre', null, 'string'),
        'telefono' => obtener_post('telefono', null, 'string'),
        'nit' => obtener_post('nit', null, 'string'),
        'email' => obtener_post('email', null, 'string'),
        'direccion' => obtener_post('direccion', null, 'string'),
        'tipo_cliente' => obtener_post('tipo_cliente', 'publico', 'string'),
        'tipo_mercaderias' => obtener_post('tipo_mercaderias', 'ambas', 'string'),
        'limite_credito' => obtener_post('limite_credito', null, 'float'),
        'plazo_credito_dias' => obtener_post('plazo_credito_dias', null, 'int')
    ];
    
    // Validar datos con el método del modelo
    $errores = Cliente::validar($datos);
    
    if (!empty($errores)) {
        responder_json(
            false,
            ['errores' => $errores],
            'Errores de validación: ' . implode(', ', $errores),
            'VALIDACION_FALLIDA'
        );
    }
    
    // Crear cliente
    $cliente_id = Cliente::crear($datos);
    
    if (!$cliente_id) {
        throw new Exception('No se pudo crear el cliente');
    }
    
    // Obtener el cliente creado
    $cliente = Cliente::obtenerPorId($cliente_id);
    
    // Responder con éxito
    responder_json(
        true,
        [
            'id' => $cliente_id,
            'cliente' => $cliente
        ],
        'Cliente creado exitosamente'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al crear cliente: ' . $e->getMessage(),
        'ERROR_CREAR_CLIENTE'
    );
}