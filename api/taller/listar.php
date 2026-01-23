<?php
/**
 * ================================================
 * API: LISTAR TRABAJOS DE TALLER
 * ================================================
 * Endpoint para obtener listado de trabajos con filtros y paginación
 * 
 * Método: GET
 * Autenticación: Requerida
 * Permisos: taller.ver
 * 
 * Parámetros GET (todos opcionales):
 * - estado: recibido, en_proceso, completado, entregado, cancelado
 * - empleado_actual_id: ID del empleado
 * - cliente_telefono: Teléfono del cliente
 * - tipo_trabajo: reparacion, ajuste, grabado, diseño, limpieza, engaste, repuesto, fabricacion
 * - material: oro, plata, otro
 * - fecha_recepcion_desde: YYYY-MM-DD
 * - fecha_recepcion_hasta: YYYY-MM-DD
 * - pagina: Número de página (default: 1)
 * - por_pagina: Registros por página (default: 20)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": [...],
 *   "message": "X trabajo(s) encontrado(s)"
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/trabajo_taller.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('GET');
verificar_api_permiso('taller', 'ver');

try {
    // Preparar filtros
    $filtros = [];
    
    if (isset($_GET['estado']) && !empty($_GET['estado'])) {
        $filtros['estado'] = $_GET['estado'];
    }
    
    if (isset($_GET['empleado_actual_id']) && !empty($_GET['empleado_actual_id'])) {
        $filtros['empleado_actual_id'] = (int)$_GET['empleado_actual_id'];
    }
    
    if (isset($_GET['cliente_telefono']) && !empty($_GET['cliente_telefono'])) {
        $filtros['cliente_telefono'] = $_GET['cliente_telefono'];
    }
    
    if (isset($_GET['tipo_trabajo']) && !empty($_GET['tipo_trabajo'])) {
        $filtros['tipo_trabajo'] = $_GET['tipo_trabajo'];
    }
    
    if (isset($_GET['material']) && !empty($_GET['material'])) {
        $filtros['material'] = $_GET['material'];
    }
    
    if (isset($_GET['fecha_recepcion_desde']) && !empty($_GET['fecha_recepcion_desde'])) {
        $filtros['fecha_recepcion_desde'] = $_GET['fecha_recepcion_desde'];
    }
    
    if (isset($_GET['fecha_recepcion_hasta']) && !empty($_GET['fecha_recepcion_hasta'])) {
        $filtros['fecha_recepcion_hasta'] = $_GET['fecha_recepcion_hasta'];
    }
    
    // Paginación
    $pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
    $por_pagina = isset($_GET['por_pagina']) ? max(1, min(100, (int)$_GET['por_pagina'])) : 20;
    
    // Obtener trabajos
    $trabajos = TrabajoTaller::listar($filtros, $pagina, $por_pagina);
    
    responder_json(
        true,
        $trabajos,
        count($trabajos) . ' trabajo(s) encontrado(s)'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al listar trabajos: ' . $e->getMessage(),
        'ERROR_LISTAR_TRABAJOS'
    );
}
