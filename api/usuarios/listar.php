<?php
/**
 * ================================================
 * API: LISTAR USUARIOS
 * ================================================
 * Endpoint para obtener listado de usuarios/empleados con filtros
 * 
 * Método: GET
 * Autenticación: Requerida
 * Permisos: usuarios.ver
 * 
 * Parámetros GET (todos opcionales):
 * - rol: administrador, dueño, vendedor, cajero, orfebre, publicidad
 * - sucursal_id: ID de sucursal
 * - activo: 1 = activos, 0 = inactivos
 * - buscar: Término de búsqueda (nombre o email)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": [...],
 *   "message": "X usuario(s) encontrado(s)"
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/usuario.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('GET');
verificar_api_permiso('usuarios', 'ver');

try {
    // Preparar filtros
    $filtros = [];
    
    if (isset($_GET['rol']) && !empty($_GET['rol'])) {
        $filtros['rol'] = $_GET['rol'];
    }
    
    if (isset($_GET['sucursal_id']) && !empty($_GET['sucursal_id'])) {
        $filtros['sucursal_id'] = (int)$_GET['sucursal_id'];
    }
    
    if (isset($_GET['activo'])) {
        $filtros['activo'] = $_GET['activo'] === '1' ? 1 : 0;
    }
    
    if (isset($_GET['buscar']) && !empty($_GET['buscar'])) {
        $filtros['buscar'] = $_GET['buscar'];
    }
    
    // Obtener usuarios
    $usuarios = Usuario::listar($filtros);
    
    // Remover passwords de la respuesta
    foreach ($usuarios as &$usuario) {
        unset($usuario['password']);
    }
    
    responder_json(
        true,
        $usuarios,
        count($usuarios) . ' usuario(s) encontrado(s)'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al listar usuarios: ' . $e->getMessage(),
        'ERROR_LISTAR_USUARIOS'
    );
}
