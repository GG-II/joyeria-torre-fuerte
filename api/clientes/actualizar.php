<?php
/**
 * ================================================
 * API: ACTUALIZAR CLIENTE
 * ================================================
 * Endpoint para actualizar un cliente existente
 * 
 * Método: POST
 * Autenticación: Requerida
 * Permisos: clientes.editar
 * 
 * Parámetros POST requeridos:
 * - id: ID del cliente a actualizar
 * 
 * Parámetros POST opcionales (se actualizan solo los enviados):
 * - nombre, telefono, nit, email, direccion
 * - tipo_cliente, tipo_mercaderias
 * - limite_credito, plazo_credito_dias
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {datos del cliente actualizado},
 *   "message": "Cliente actualizado exitosamente"
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
verificar_api_permiso('clientes', 'editar');

try {
    // Validar ID requerido
    validar_campos_requeridos(['id'], 'POST');
    
    $id = obtener_post('id', null, 'int');
    
    // Verificar que el cliente exista y obtener datos actuales
    $cliente_actual = Cliente::obtenerPorId($id);
    
    if (!$cliente_actual) {
        responder_json(
            false,
            null,
            'El cliente no existe',
            'CLIENTE_NO_ENCONTRADO'
        );
    }
    
    // Preparar datos completos (mezclar actual + nuevo)
    $datos = [
        'nombre' => isset($_POST['nombre']) ? 
            obtener_post('nombre', null, 'string') : 
            $cliente_actual['nombre'],
        
        'telefono' => isset($_POST['telefono']) ? 
            obtener_post('telefono', null, 'string') : 
            $cliente_actual['telefono'],
        
        'nit' => isset($_POST['nit']) ? 
            obtener_post('nit', null, 'string') : 
            $cliente_actual['nit'],
        
        'email' => isset($_POST['email']) ? 
            obtener_post('email', null, 'string') : 
            $cliente_actual['email'],
        
        'direccion' => isset($_POST['direccion']) ? 
            obtener_post('direccion', null, 'string') : 
            $cliente_actual['direccion'],
        
        'tipo_cliente' => isset($_POST['tipo_cliente']) ? 
            obtener_post('tipo_cliente', null, 'string') : 
            $cliente_actual['tipo_cliente'],
        
        'tipo_mercaderias' => isset($_POST['tipo_mercaderias']) ? 
            obtener_post('tipo_mercaderias', null, 'string') : 
            $cliente_actual['tipo_mercaderias'],
        
        'limite_credito' => isset($_POST['limite_credito']) ? 
            obtener_post('limite_credito', null, 'float') : 
            $cliente_actual['limite_credito'],
        
        'plazo_credito_dias' => isset($_POST['plazo_credito_dias']) ? 
            obtener_post('plazo_credito_dias', null, 'int') : 
            $cliente_actual['plazo_credito_dias']
    ];
    
    // Validar datos
    $errores = Cliente::validar($datos, $id);
    
    if (!empty($errores)) {
        responder_json(
            false,
            ['errores' => $errores],
            'Errores de validación: ' . implode(', ', $errores),
            'VALIDACION_FALLIDA'
        );
    }
    
    // Actualizar cliente
    $resultado = Cliente::actualizar($id, $datos);
    
    if (!$resultado) {
        throw new Exception('No se pudo actualizar el cliente');
    }
    
    // Obtener el cliente actualizado
    $cliente = Cliente::obtenerPorId($id);
    
    // Responder con éxito
    responder_json(
        true,
        $cliente,
        'Cliente actualizado exitosamente'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al actualizar cliente: ' . $e->getMessage(),
        'ERROR_ACTUALIZAR_CLIENTE'
    );
}