<?php
/**
 * ================================================
 * API: LISTAR FACTURAS
 * ================================================
 * Endpoint para obtener listado de facturas con filtros
 * 
 * Método: GET
 * Autenticación: Requerida
 * Permisos: facturas.ver
 * 
 * Parámetros GET (todos opcionales):
 * - tipo: simple, electronica
 * - estado: emitida, anulada
 * - fecha_desde: Fecha inicio (YYYY-MM-DD)
 * - fecha_hasta: Fecha fin (YYYY-MM-DD)
 * - buscar: Término de búsqueda (número, NIT, nombre)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": [...],
 *   "message": "X factura(s) encontrada(s)"
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/factura.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('GET');
verificar_api_permiso('facturas', 'ver');

try {
    // Preparar filtros
    $filtros = array();
    
    if (isset($_GET['tipo']) && !empty($_GET['tipo'])) {
        $filtros['tipo'] = $_GET['tipo'];
    }
    
    if (isset($_GET['estado']) && !empty($_GET['estado'])) {
        $filtros['estado'] = $_GET['estado'];
    }
    
    if (isset($_GET['fecha_desde']) && !empty($_GET['fecha_desde'])) {
        $filtros['fecha_desde'] = $_GET['fecha_desde'];
    }
    
    if (isset($_GET['fecha_hasta']) && !empty($_GET['fecha_hasta'])) {
        $filtros['fecha_hasta'] = $_GET['fecha_hasta'];
    }
    
    if (isset($_GET['buscar']) && !empty($_GET['buscar'])) {
        $filtros['buscar'] = $_GET['buscar'];
    }
    
    // Obtener facturas
    $facturas = Factura::listar($filtros);
    
    responder_json(
        true,
        $facturas,
        count($facturas) . ' factura(s) encontrada(s)'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al listar facturas: ' . $e->getMessage(),
        'ERROR_LISTAR_FACTURAS'
    );
}
