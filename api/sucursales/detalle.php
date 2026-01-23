<?php
/**
 * ================================================
 * API: DETALLE DE SUCURSAL
 * ================================================
 * Endpoint para obtener información detallada de una sucursal
 * 
 * Método: GET
 * Autenticación: Requerida
 * Permisos: sucursales.ver
 * 
 * Parámetros GET requeridos:
 * - id: ID de la sucursal
 * 
 * Parámetros GET opcionales:
 * - incluir_usuarios: true/false (incluir usuarios de la sucursal)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "sucursal": {...},
 *     "usuarios": [...] (si incluir_usuarios=true)
 *   },
 *   "message": "Sucursal encontrada"
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
    // Validar parámetro ID
    if (empty($_GET['id'])) {
        responder_json(false, null, 'El ID de la sucursal es requerido', 'ID_REQUERIDO');
    }
    
    $id = (int)$_GET['id'];
    
    // Obtener sucursal
    $sucursal = Sucursal::obtenerPorId($id);
    
    if (!$sucursal) {
        responder_json(false, null, 'Sucursal no encontrada', 'SUCURSAL_NO_ENCONTRADA');
    }
    
    // Preparar respuesta
    $respuesta = array(
        'sucursal' => $sucursal
    );
    
    // Incluir usuarios si se solicita
    if (isset($_GET['incluir_usuarios']) && $_GET['incluir_usuarios'] === 'true') {
        $usuarios = Sucursal::obtenerUsuarios($id);
        $respuesta['usuarios'] = $usuarios;
        $respuesta['total_usuarios'] = count($usuarios);
    }
    
    responder_json(
        true,
        $respuesta,
        'Sucursal encontrada'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al obtener detalle de sucursal: ' . $e->getMessage(),
        'ERROR_DETALLE_SUCURSAL'
    );
}
