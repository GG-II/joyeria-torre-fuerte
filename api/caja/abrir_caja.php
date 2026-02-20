<?php
/**
 * ================================================
 * API: ABRIR CAJA
 * ================================================
 * Endpoint para abrir una nueva caja
 * 
 * Método: POST
 * Autenticación: Requerida
 * Permisos: caja.abrir
 * 
 * Parámetros POST:
 * - sucursal_id: ID de la sucursal (requerido)
 * - monto_inicial: Monto inicial en efectivo (requerido)
 * 
 * VALIDACIONES:
 * - El usuario no debe tener otra caja abierta
 * - Monto inicial debe ser mayor o igual a 0
 * - Sucursal debe existir y estar activa
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "caja_id": 123,
 *     "sucursal_id": 1,
 *     "monto_inicial": 500.00,
 *     "fecha_apertura": "2026-01-22 19:30:15",
 *     "estado": "abierta"
 *   },
 *   "message": "Caja abierta exitosamente"
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/caja.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('POST');
verificar_api_permiso('caja', 'abrir');

try {
    // Leer JSON body
    $json_input = file_get_contents('php://input');
    $datos = json_decode($json_input, true);
    
    // Fallback a POST
    if (json_last_error() !== JSON_ERROR_NONE || empty($datos)) {
        $datos = $_POST;
    }
    
    // Validar campos requeridos
    if (empty($datos['sucursal_id'])) {
        responder_json(false, null, 'El campo sucursal_id es requerido', 'CAMPO_REQUERIDO');
    }
    
    if (!isset($datos['monto_inicial'])) {
        responder_json(false, null, 'El campo monto_inicial es requerido', 'CAMPO_REQUERIDO');
    }
    
    $sucursal_id = (int)$datos['sucursal_id'];
    $monto_inicial = (float)$datos['monto_inicial'];
    $usuario_id = usuario_actual_id();
    
    // Validar monto inicial
    if ($monto_inicial < 0) {
        responder_json(false, null, 'El monto inicial no puede ser negativo', 'MONTO_INVALIDO');
    }
    

    // Verificar que la sucursal existe
if (!db_exists('sucursales', 'id = ? AND activo = 1', [$sucursal_id])) {
    responder_json(false, null, 'La sucursal no existe o está inactiva', 'SUCURSAL_INVALIDA');
    exit; // ← AGREGAR
}

// Verificar que el usuario no tenga otra caja abierta
$caja_existente = db_query_one(
    "SELECT id FROM cajas WHERE usuario_id = ? AND estado = 'abierta'",
    [$usuario_id]
);

if ($caja_existente) {
    // Verificar si hay caja en la sucursal que está intentando abrir
    $caja_en_sucursal = db_query_one(
        "SELECT id, usuario_id FROM cajas WHERE sucursal_id = ? AND estado = 'abierta'",
        [$sucursal_id]
    );
    
    // Si ya tiene caja en ESTA sucursal, no puede abrir otra
    if ($caja_en_sucursal && $caja_en_sucursal['usuario_id'] == $usuario_id) {
        responder_json(
            false,
            ['caja_id' => $caja_existente['id']],
            'Ya tienes una caja abierta en esta sucursal',
            'CAJA_YA_ABIERTA'
        );
        exit; // ← AGREGAR
    }
    
// Si tiene caja en OTRA sucursal, verificar si es admin/dueño consultando BD
global $pdo;

$stmt = $pdo->prepare("SELECT rol FROM usuarios WHERE id = ?");
$stmt->execute([$usuario_id]);
$usuario_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario_data) {
    responder_json(
        false,
        null,
        'Usuario no encontrado',
        'USUARIO_NO_ENCONTRADO'
    );
    exit;
}

$rol_usuario = $usuario_data['rol'];

// Verificar rol (case-insensitive)
$es_admin = in_array(strtolower(trim($rol_usuario)), ['administrador', 'dueño']);

if (!$es_admin) {
    responder_json(
        false,
        [
            'caja_id' => $caja_existente['id'], 
            'rol_detectado' => $rol_usuario
        ],
        "Ya tienes una caja abierta. Solo Admin/Dueño puede abrir múltiples cajas. (Tu rol: $rol_usuario)",
        'CAJA_YA_ABIERTA'
    );
    exit;
}

// Admin/dueño puede continuar
}

// Abrir caja
$caja_id = Caja::abrirCaja($usuario_id, $sucursal_id, $monto_inicial);

if (!$caja_id) {
    throw new Exception('No se pudo abrir la caja. Revise los logs para más detalles.');
}

// Obtener caja completa
$caja = Caja::obtenerPorId($caja_id);

if (!$caja) {
    // La caja se creó pero no se pudo obtener
    responder_json(
        true,
        ['caja_id' => $caja_id],
        'Caja abierta exitosamente pero no se pudo obtener el detalle completo',
        'CAJA_ABIERTA_CON_ADVERTENCIA'
    );
    exit; // ← AGREGAR
}

// Preparar respuesta
$respuesta = [
    'caja_id' => $caja_id,
    'sucursal_id' => $sucursal_id,
    'sucursal_nombre' => $caja['sucursal_nombre'],
    'monto_inicial' => $monto_inicial,
    'fecha_apertura' => $caja['fecha_apertura'],
    'estado' => 'abierta',
    'caja_completa' => $caja
];
    
    responder_json(
        true,
        $respuesta,
        "Caja abierta exitosamente con monto inicial de Q " . number_format($monto_inicial, 2)
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al abrir caja: ' . $e->getMessage(),
        'ERROR_ABRIR_CAJA'
    );
}
