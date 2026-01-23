<?php
/**
 * ================================================
 * API: LISTAR PROVEEDORES
 * ================================================
 * Endpoint para obtener listado de proveedores con filtros
 * 
 * Método: GET
 * Autenticación: Requerida
 * Permisos: proveedores.ver
 * 
 * Parámetros GET (todos opcionales):
 * - activo: 1 = solo activos, 0 = solo inactivos
 * - buscar: Búsqueda en nombre, empresa o contacto
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
    // Preparar filtros
    $filtros = [];
    
    // Filtro por estado activo
    if (isset($_GET['activo'])) {
        $filtros['activo'] = $_GET['activo'] === '1' || $_GET['activo'] === 'true' ? 1 : 0;
    }
    
    // Filtro por búsqueda
    if (isset($_GET['buscar']) && !empty(trim($_GET['buscar']))) {
        $filtros['buscar'] = trim($_GET['buscar']);
    }
    
    // Obtener proveedores
    $proveedores = Proveedor::listar($filtros);
    
    responder_json(
        true,
        $proveedores,
        count($proveedores) . ' proveedor(es) encontrado(s)'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al listar proveedores: ' . $e->getMessage(),
        'ERROR_LISTAR_PROVEEDORES'
    );
}
