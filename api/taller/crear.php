<?php
/**
 * ================================================
 * API: CREAR TRABAJO DE TALLER
 * ================================================
 * Endpoint para crear un nuevo trabajo de taller
 * 
 * Método: POST
 * Autenticación: Requerida
 * Permisos: taller.crear
 * 
 * Parámetros POST requeridos:
 * - cliente_nombre: Nombre del cliente
 * - cliente_telefono: Teléfono del cliente (8 dígitos)
 * - material: oro, plata, otro
 * - descripcion_pieza: Descripción de la pieza
 * - tipo_trabajo: reparacion, ajuste, grabado, diseño, limpieza, engaste, repuesto, fabricacion
 * - descripcion_trabajo: Descripción del trabajo a realizar
 * - precio_total: Precio total del trabajo
 * - fecha_entrega_prometida: YYYY-MM-DD
 * - empleado_recibe_id: ID del empleado que recibe
 * 
 * Parámetros POST opcionales:
 * - cliente_id: ID del cliente (si existe en sistema)
 * - peso_gramos: Peso en gramos
 * - largo_cm: Largo en centímetros
 * - con_piedra: 0 o 1
 * - estilo: Estilo de la pieza
 * - anticipo: Monto de anticipo
 * - observaciones: Observaciones adicionales
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "id": 123,
 *     "codigo": "TT-2026-0001",
 *     "trabajo": {...}
 *   },
 *   "message": "Trabajo creado exitosamente"
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/trabajo_taller.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('POST');
verificar_api_permiso('taller', 'crear');

try {
    // Leer JSON body
    $json_input = file_get_contents('php://input');
    $datos = json_decode($json_input, true);
    
    // Fallback a POST
    if (json_last_error() !== JSON_ERROR_NONE || empty($datos)) {
        $datos = $_POST;
    }
    
    // Validar campos requeridos
    $campos_requeridos = [
        'cliente_nombre', 'cliente_telefono', 'material', 
        'descripcion_pieza', 'tipo_trabajo', 'descripcion_trabajo',
        'precio_total', 'fecha_entrega_prometida', 'empleado_recibe_id'
    ];
    
    foreach ($campos_requeridos as $campo) {
        if (!isset($datos[$campo]) || $datos[$campo] === '') {
            responder_json(false, null, "El campo {$campo} es requerido", 'CAMPO_REQUERIDO');
        }
    }
    
    // Preparar datos del trabajo
    $datos_trabajo = [
        'cliente_nombre' => $datos['cliente_nombre'],
        'cliente_telefono' => $datos['cliente_telefono'],
        'cliente_id' => $datos['cliente_id'] ?? null,
        'material' => $datos['material'],
        'peso_gramos' => $datos['peso_gramos'] ?? null,
        'largo_cm' => $datos['largo_cm'] ?? null,
        'con_piedra' => isset($datos['con_piedra']) ? (int)$datos['con_piedra'] : 0,
        'estilo' => $datos['estilo'] ?? null,
        'descripcion_pieza' => $datos['descripcion_pieza'],
        'tipo_trabajo' => $datos['tipo_trabajo'],
        'descripcion_trabajo' => $datos['descripcion_trabajo'],
        'precio_total' => (float)$datos['precio_total'],
        'anticipo' => isset($datos['anticipo']) ? (float)$datos['anticipo'] : 0,
        'fecha_entrega_prometida' => $datos['fecha_entrega_prometida'],
        'empleado_recibe_id' => (int)$datos['empleado_recibe_id'],
        'observaciones' => $datos['observaciones'] ?? null
    ];
    
    // Crear trabajo (el modelo valida internamente)
    $trabajo_id = TrabajoTaller::crear($datos_trabajo);
    
    if (!$trabajo_id) {
        throw new Exception('No se pudo crear el trabajo. Revise los logs para más detalles.');
    }
    
    // Obtener trabajo creado
    $trabajo = TrabajoTaller::obtenerPorId($trabajo_id);
    
    responder_json(
        true,
        [
            'id' => $trabajo_id,
            'codigo' => $trabajo['codigo'],
            'trabajo' => $trabajo
        ],
        'Trabajo creado exitosamente'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al crear trabajo: ' . $e->getMessage(),
        'ERROR_CREAR_TRABAJO'
    );
}
