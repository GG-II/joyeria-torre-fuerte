<?php
/**
 * ================================================
 * API: REPORTE DE PRODUCTOS
 * ================================================
 * Endpoint para obtener reportes de productos más/menos vendidos
 * 
 * Método: GET
 * Autenticación: Requerida
 * Permisos: reportes.ver
 * 
 * Parámetros GET:
 * - tipo: 'mas_vendidos' o 'menos_movimiento' (requerido)
 * - fecha_inicio: Fecha inicio (YYYY-MM-DD) (requerido)
 * - fecha_fin: Fecha fin (YYYY-MM-DD) (requerido)
 * - limite: Cantidad de productos a retornar (opcional, default: 20, max: 100)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "fecha_inicio": "2026-01-01",
 *     "fecha_fin": "2026-01-31",
 *     "productos": [...]
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
    // Validar parámetros requeridos
    if (!isset($_GET['tipo']) || empty($_GET['tipo'])) {
        responder_json(false, null, 'El parámetro tipo es requerido', 'TIPO_REQUERIDO');
    }
    
    if (!isset($_GET['fecha_inicio']) || !isset($_GET['fecha_fin'])) {
        responder_json(false, null, 'fecha_inicio y fecha_fin son requeridas', 'FECHAS_REQUERIDAS');
    }
    
    $tipo = $_GET['tipo'];
    $fecha_inicio = $_GET['fecha_inicio'];
    $fecha_fin = $_GET['fecha_fin'];
    $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 20;
    
    // Validar límite
    if ($limite < 1) $limite = 20;
    if ($limite > 100) $limite = 100;
    
    // Validar tipo
    if (!in_array($tipo, ['mas_vendidos', 'menos_movimiento'])) {
        responder_json(false, null, 'Tipo inválido. Use: mas_vendidos, menos_movimiento', 'TIPO_INVALIDO');
    }
    
    $datos = null;
    
    // Generar reporte según tipo
    if ($tipo === 'mas_vendidos') {
        $datos = Reporte::reporteProductosMasVendidos($fecha_inicio, $fecha_fin, $limite);
    } else {
        $datos = Reporte::reporteProductosMenosMovimiento($fecha_inicio, $fecha_fin, $limite);
    }
    
    if (empty($datos)) {
        responder_json(
            true,
            [],
            'No hay datos para el período solicitado'
        );
    }
    
    responder_json(
        true,
        $datos,
        "Reporte de productos ({$tipo}) generado exitosamente"
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al generar reporte de productos: ' . $e->getMessage(),
        'ERROR_REPORTE_PRODUCTOS'
    );
}
