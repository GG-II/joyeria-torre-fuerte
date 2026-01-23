<?php
/**
 * ================================================
 * API: REPORTE DE VENTAS
 * ================================================
 * Endpoint para obtener reportes de ventas con diferentes agrupaciones
 * 
 * Método: GET
 * Autenticación: Requerida
 * Permisos: reportes.ver
 * 
 * Parámetros GET:
 * - tipo: 'diario', 'mensual', 'vendedor', 'sucursal' (requerido)
 * 
 * Para tipo='diario':
 * - fecha: Fecha del reporte (YYYY-MM-DD) (requerido)
 * - sucursal_id: ID de sucursal (opcional)
 * 
 * Para tipo='mensual':
 * - mes: Mes (1-12) (requerido)
 * - año: Año (YYYY) (requerido)
 * - sucursal_id: ID de sucursal (opcional)
 * 
 * Para tipo='vendedor' o 'sucursal':
 * - fecha_inicio: Fecha inicio (YYYY-MM-DD) (requerido)
 * - fecha_fin: Fecha fin (YYYY-MM-DD) (requerido)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     ... datos del reporte según tipo ...
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
    if (!in_array($tipo, ['diario', 'mensual', 'vendedor', 'sucursal'])) {
        responder_json(false, null, 'Tipo inválido. Use: diario, mensual, vendedor, sucursal', 'TIPO_INVALIDO');
    }
    
    $datos = null;
    
    // Procesar según tipo
    switch ($tipo) {
        case 'diario':
            if (!isset($_GET['fecha']) || empty($_GET['fecha'])) {
                responder_json(false, null, 'La fecha es requerida para reporte diario', 'FECHA_REQUERIDA');
            }
            
            $fecha = $_GET['fecha'];
            $sucursal_id = isset($_GET['sucursal_id']) ? (int)$_GET['sucursal_id'] : null;
            
            $datos = Reporte::reporteVentasDiarias($fecha, $sucursal_id);
            break;
            
        case 'mensual':
            if (!isset($_GET['mes']) || !isset($_GET['año'])) {
                responder_json(false, null, 'Mes y año son requeridos para reporte mensual', 'PARAMETROS_REQUERIDOS');
            }
            
            $mes = (int)$_GET['mes'];
            $año = (int)$_GET['año'];
            $sucursal_id = isset($_GET['sucursal_id']) ? (int)$_GET['sucursal_id'] : null;
            
            if ($mes < 1 || $mes > 12) {
                responder_json(false, null, 'Mes inválido (debe ser 1-12)', 'MES_INVALIDO');
            }
            
            $datos = Reporte::reporteVentasMensuales($mes, $año, $sucursal_id);
            break;
            
        case 'vendedor':
            if (!isset($_GET['fecha_inicio']) || !isset($_GET['fecha_fin'])) {
                responder_json(false, null, 'fecha_inicio y fecha_fin son requeridas', 'FECHAS_REQUERIDAS');
            }
            
            $fecha_inicio = $_GET['fecha_inicio'];
            $fecha_fin = $_GET['fecha_fin'];
            
            $datos = Reporte::reporteVentasPorVendedor($fecha_inicio, $fecha_fin);
            break;
            
        case 'sucursal':
            if (!isset($_GET['fecha_inicio']) || !isset($_GET['fecha_fin'])) {
                responder_json(false, null, 'fecha_inicio y fecha_fin son requeridas', 'FECHAS_REQUERIDAS');
            }
            
            $fecha_inicio = $_GET['fecha_inicio'];
            $fecha_fin = $_GET['fecha_fin'];
            
            $datos = Reporte::reporteVentasPorSucursal($fecha_inicio, $fecha_fin);
            break;
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
        "Reporte de ventas ({$tipo}) generado exitosamente"
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al generar reporte de ventas: ' . $e->getMessage(),
        'ERROR_REPORTE_VENTAS'
    );
}
