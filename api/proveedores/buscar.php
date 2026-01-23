<?php
/**
 * ================================================
 * API: BUSCAR PROVEEDORES
 * ================================================
 * Endpoint para búsqueda rápida de proveedores
 * 
 * Método: GET
 * Autenticación: Requerida
 * Permisos: proveedores.ver
 * 
 * Parámetros GET:
 * - termino: Término de búsqueda (opcional)
 * 
 * Busca en: nombre, empresa y contacto
 * Solo retorna proveedores activos
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": [...],
 *   "message": "X proveedor(es) encontrado(s)"
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/proveedor.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('GET');
verificar_api_permiso('proveedores', 'ver');

try {
    $termino = isset($_GET['termino']) ? trim($_GET['termino']) : '';
    
    // Buscar proveedores
    $proveedores = Proveedor::buscar($termino);
    
    responder_json(
        true,
        $proveedores,
        count($proveedores) . ' proveedor(es) encontrado(s)'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al buscar proveedores: ' . $e->getMessage(),
        'ERROR_BUSCAR_PROVEEDORES'
    );
}
