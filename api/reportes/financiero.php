<?php
/**
 * ================================================
 * API: REPORTE FINANCIERO
 * ================================================
 * Endpoint para obtener reportes financieros (cuentas por cobrar y ganancias)
 * 
 * Método: GET
 * Autenticación: Requerida
 * Permisos: reportes.ver
 * 
 * Parámetros GET:
 * - tipo: 'cuentas_por_cobrar' o 'ganancias' (requerido)
 * 
 * Para tipo='ganancias':
 * - fecha_inicio: Fecha inicio (YYYY-MM-DD) (requerido)
 * - fecha_fin: Fecha fin (YYYY-MM-DD) (requerido)
 * 
 * Para tipo='cuentas_por_cobrar':
 * - No requiere parámetros adicionales
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     ... datos según tipo ...
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
    // Validar tipo requerido
    if (!isset($_GET['tipo']) || empty($_GET['tipo'])) {
        responder_json(false, null, 'El parámetro tipo es requerido', 'TIPO_REQUERIDO');
    }
    
    $tipo = $_GET['tipo'];
    
    // Validar tipo válido
    if (!in_array($tipo, ['cuentas_por_cobrar', 'ganancias'])) {
        responder_json(false, null, 'Tipo inválido. Use: cuentas_por_cobrar, ganancias', 'TIPO_INVALIDO');
    }
    
    $datos = null;
    
    // Procesar según tipo
    if ($tipo === 'cuentas_por_cobrar') {
        $datos = Reporte::reporteCuentasPorCobrar();
        
    } else if ($tipo === 'ganancias') {
        if (!isset($_GET['fecha_inicio']) || !isset($_GET['fecha_fin'])) {
            responder_json(false, null, 'fecha_inicio y fecha_fin son requeridas para reporte de ganancias', 'FECHAS_REQUERIDAS');
        }
        
        $fecha_inicio = $_GET['fecha_inicio'];
        $fecha_fin = $_GET['fecha_fin'];
        
        $datos = Reporte::reporteGanancias($fecha_inicio, $fecha_fin);
    }
    
    if (empty($datos)) {
        responder_json(
            true,
            [],
            'No hay datos para el reporte solicitado'
        );
    }
    
    responder_json(
        true,
        $datos,
        "Reporte financiero ({$tipo}) generado exitosamente"
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al generar reporte financiero: ' . $e->getMessage(),
        'ERROR_REPORTE_FINANCIERO'
    );
}
