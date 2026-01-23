<?php
/**
 * ================================================
 * API: CREAR PROVEEDOR
 * ================================================
 * Endpoint para crear un nuevo proveedor
 * 
 * Método: POST
 * Autenticación: Requerida
 * Permisos: proveedores.crear
 * 
 * Parámetros POST requeridos:
 * - nombre: Nombre del proveedor (mínimo 3 caracteres)
 * - telefono: Teléfono de contacto
 * 
 * Parámetros POST opcionales:
 * - empresa: Nombre de la empresa
 * - contacto: Nombre de la persona de contacto
 * - email: Email del proveedor
 * - direccion: Dirección física
 * - productos_suministra: Descripción de productos que suministra
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "id": 123,
 *     "proveedor": {...}
 *   },
 *   "message": "Proveedor creado exitosamente"
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/proveedor.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('POST');
verificar_api_permiso('proveedores', 'crear');

try {
    // Leer JSON body
    $json_input = file_get_contents('php://input');
    $datos = json_decode($json_input, true);
    
    // Fallback a POST
    if (json_last_error() !== JSON_ERROR_NONE || empty($datos)) {
        $datos = $_POST;
    }
    
    // Validar campos requeridos
    if (empty($datos['nombre'])) {
        responder_json(false, null, 'El nombre del proveedor es requerido', 'CAMPO_REQUERIDO');
    }
    
    if (empty($datos['telefono'])) {
        responder_json(false, null, 'El teléfono es requerido', 'CAMPO_REQUERIDO');
    }
    
    // Preparar datos
    $datos_proveedor = [
        'nombre' => $datos['nombre'],
        'telefono' => $datos['telefono'],
        'empresa' => $datos['empresa'] ?? null,
        'contacto' => $datos['contacto'] ?? null,
        'email' => $datos['email'] ?? null,
        'direccion' => $datos['direccion'] ?? null,
        'productos_suministra' => $datos['productos_suministra'] ?? null,
        'activo' => 1
    ];
    
    // Crear proveedor (el modelo ya valida internamente)
    $proveedor_id = Proveedor::crear($datos_proveedor);
    
    if (!$proveedor_id) {
        throw new Exception('No se pudo crear el proveedor. Revise los logs para más detalles.');
    }
    
    // Obtener proveedor creado
    $proveedor = Proveedor::obtenerPorId($proveedor_id);
    
    responder_json(
        true,
        [
            'id' => $proveedor_id,
            'proveedor' => $proveedor
        ],
        'Proveedor creado exitosamente'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al crear proveedor: ' . $e->getMessage(),
        'ERROR_CREAR_PROVEEDOR'
    );
}
