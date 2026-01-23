<?php
/**
 * ================================================
 * API: REPORTE DE INVENTARIO
 * ================================================
 * Endpoint para obtener el estado actual del inventario
 * 
 * Método: GET
 * Autenticación: Requerida
 * Permisos: reportes.ver
 * 
 * Parámetros GET:
 * - sucursal_id: ID de sucursal (opcional, null para todas)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "resumen": {
 *       "total_productos": 150,
 *       "total_unidades": 2500,
 *       "productos_bajo_stock": 25,
 *       "productos_sin_stock": 5
 *     },
 *     "productos_bajo_stock": [...],
 *     "productos_sin_stock": [...],
 *     "inventario_detalle": [...]
 *   }
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/reporte.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('GET');
verificar_api_permiso('reportes', 'ver');

try {
    $sucursal_id = isset($_GET['sucursal_id']) ? (int)$_GET['sucursal_id'] : null;
    
    // Generar reporte
    $datos = Reporte::reporteInventarioActual($sucursal_id);
    
    if (empty($datos)) {
        responder_json(
            true,
            [],
            'No hay datos de inventario disponibles'
        );
    }
    
    responder_json(
        true,
        $datos,
        'Reporte de inventario generado exitosamente'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al generar reporte de inventario: ' . $e->getMessage(),
        'ERROR_REPORTE_INVENTARIO'
    );
}
