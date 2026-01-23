<?php
/**
 * ================================================
 * API: LISTAR SUCURSALES
 * ================================================
 * Endpoint para obtener listado de sucursales con filtros
 * 
 * Método: GET
 * Autenticación: Requerida
 * Permisos: sucursales.ver
 * 
 * Parámetros GET (todos opcionales):
 * - activo: 1 = activas, 0 = inactivas
 * - buscar: Término de búsqueda (nombre o dirección)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": [...],
 *   "message": "X sucursal(es) encontrada(s)"
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/sucursal.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('GET');
verificar_api_permiso('sucursales', 'ver');

try {
    // Preparar filtros
    $filtros = array();
    
    if (isset($_GET['activo'])) {
        $filtros['activo'] = $_GET['activo'] === '1' ? 1 : 0;
    }
    
    if (isset($_GET['buscar']) && !empty($_GET['buscar'])) {
        $filtros['buscar'] = $_GET['buscar'];
    }
    
    // Obtener sucursales
    $sucursales = Sucursal::listar($filtros);
    
    responder_json(
        true,
        $sucursales,
        count($sucursales) . ' sucursal(es) encontrada(s)'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al listar sucursales: ' . $e->getMessage(),
        'ERROR_LISTAR_SUCURSALES'
    );
}
