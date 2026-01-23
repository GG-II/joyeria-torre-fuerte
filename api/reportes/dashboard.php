<?php
/**
 * ================================================
 * API: DASHBOARD (BONUS)
 * ================================================
 * Endpoint para obtener resumen general del sistema
 * 
 * Método: GET
 * Autenticación: Requerida
 * Permisos: reportes.ver
 * 
 * Parámetros GET:
 * - fecha: Fecha del dashboard (opcional, default: hoy, YYYY-MM-DD)
 * - sucursal_id: ID de sucursal (opcional, null para todas)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "fecha": "2026-01-22",
 *     "ventas_hoy": {...},
 *     "inventario": {...},
 *     "cuentas_por_cobrar": {...},
 *     "taller": {...},
 *     "alertas": [...]
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
    $fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');
    $sucursal_id = isset($_GET['sucursal_id']) ? (int)$_GET['sucursal_id'] : null;
    
    // Obtener datos de dashboard
    
    // 1. Ventas del día
    $ventas_hoy = Reporte::reporteVentasDiarias($fecha, $sucursal_id);
    
    // 2. Resumen de inventario
    $inventario = Reporte::reporteInventarioActual($sucursal_id);
    $resumen_inventario = [
        'total_productos' => $inventario['resumen']['total_productos'] ?? 0,
        'productos_bajo_stock' => $inventario['resumen']['productos_bajo_stock'] ?? 0,
        'productos_sin_stock' => $inventario['resumen']['productos_sin_stock'] ?? 0
    ];
    
    // 3. Cuentas por cobrar (resumen)
    $cuentas = Reporte::reporteCuentasPorCobrar();
    $resumen_cuentas = [
        'total_clientes_credito' => $cuentas['resumen']['total_clientes'] ?? 0,
        'total_por_cobrar' => $cuentas['resumen']['total_por_cobrar'] ?? 0,
        'creditos_vencidos' => count($cuentas['creditos_vencidos'] ?? [])
    ];
    
    // 4. Trabajos de taller (resumen)
    $taller = Reporte::reporteTrabajosPendientes();
    $resumen_taller = [
        'trabajos_pendientes' => $taller['resumen']['total'] ?? 0,
        'trabajos_atrasados' => count($taller['trabajos_atrasados'] ?? []),
        'en_proceso' => $taller['resumen']['en_proceso'] ?? 0
    ];
    
    // 5. Generar alertas
    $alertas = [];
    
    // Alertas de inventario
    if ($resumen_inventario['productos_sin_stock'] > 0) {
        $alertas[] = [
            'tipo' => 'inventario',
            'nivel' => 'critico',
            'mensaje' => "{$resumen_inventario['productos_sin_stock']} producto(s) sin stock",
            'accion' => 'Revisar inventario'
        ];
    }
    
    if ($resumen_inventario['productos_bajo_stock'] > 0) {
        $alertas[] = [
            'tipo' => 'inventario',
            'nivel' => 'advertencia',
            'mensaje' => "{$resumen_inventario['productos_bajo_stock']} producto(s) con stock bajo",
            'accion' => 'Reabastecer productos'
        ];
    }
    
    // Alertas de créditos
    if ($resumen_cuentas['creditos_vencidos'] > 0) {
        $alertas[] = [
            'tipo' => 'credito',
            'nivel' => 'critico',
            'mensaje' => "{$resumen_cuentas['creditos_vencidos']} crédito(s) vencido(s)",
            'accion' => 'Gestionar cobro'
        ];
    }
    
    // Alertas de taller
    if ($resumen_taller['trabajos_atrasados'] > 0) {
        $alertas[] = [
            'tipo' => 'taller',
            'nivel' => 'advertencia',
            'mensaje' => "{$resumen_taller['trabajos_atrasados']} trabajo(s) atrasado(s)",
            'accion' => 'Revisar compromisos'
        ];
    }
    
    // Construir respuesta
    $dashboard = [
        'fecha' => $fecha,
        'sucursal_id' => $sucursal_id,
        'ventas_hoy' => [
            'total_ventas' => $ventas_hoy['totales']['total_ventas'] ?? 0,
            'monto_total' => $ventas_hoy['totales']['monto_total'] ?? 0,
            'ticket_promedio' => $ventas_hoy['totales']['ticket_promedio'] ?? 0,
            'ventas_contado' => $ventas_hoy['totales']['ventas_contado'] ?? 0,
            'ventas_credito' => $ventas_hoy['totales']['ventas_credito'] ?? 0
        ],
        'inventario' => $resumen_inventario,
        'cuentas_por_cobrar' => $resumen_cuentas,
        'taller' => $resumen_taller,
        'alertas' => $alertas,
        'total_alertas' => count($alertas)
    ];
    
    responder_json(
        true,
        $dashboard,
        'Dashboard generado exitosamente'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al generar dashboard: ' . $e->getMessage(),
        'ERROR_DASHBOARD'
    );
}
