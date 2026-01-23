<?php
/**
 * ================================================
 * API: LISTAR MATERIAS PRIMAS
 * ================================================
 * Endpoint para obtener listado de materias primas con filtros
 * 
 * Método: GET
 * Autenticación: Requerida
 * Permisos: materia_prima.ver
 * 
 * Parámetros GET (todos opcionales):
 * - tipo: oro, plata, piedra, otro
 * - activo: 1 = activas, 0 = inactivas
 * - busqueda: Término de búsqueda
 * - stock_bajo: true para obtener solo materias con stock bajo
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": [...],
 *   "message": "X materia(s) prima(s) encontrada(s)"
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/materia_prima.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('GET');
verificar_api_permiso('materia_prima', 'ver');

try {
    // Si se solicita solo stock bajo
    if (isset($_GET['stock_bajo']) && $_GET['stock_bajo'] === 'true') {
        $materias = MateriaPrima::obtenerStockBajo();
        responder_json(
            true,
            $materias,
            count($materias) . ' materia(s) prima(s) con stock bajo'
        );
    }
    
    // Preparar filtros
    $filtros = [];
    
    if (isset($_GET['tipo']) && !empty($_GET['tipo'])) {
        $filtros['tipo'] = $_GET['tipo'];
    }
    
    if (isset($_GET['activo'])) {
        $filtros['activo'] = $_GET['activo'] === '1' ? 1 : 0;
    }
    
    if (isset($_GET['busqueda']) && !empty($_GET['busqueda'])) {
        $filtros['busqueda'] = $_GET['busqueda'];
    }
    
    // Obtener materias primas
    $materias = MateriaPrima::listar($filtros);
    
    responder_json(
        true,
        $materias,
        count($materias) . ' materia(s) prima(s) encontrada(s)'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al listar materias primas: ' . $e->getMessage(),
        'ERROR_LISTAR_MATERIAS'
    );
}
