<?php
/**
 * ================================================
 * API: CREAR VENTA
 * ================================================
 * Endpoint para crear una nueva venta completa
 * 
 * ESTE ES EL ENDPOINT MÁS COMPLEJO DEL SISTEMA
 * 
 * Método: POST
 * Autenticación: Requerida
 * Permisos: ventas.crear
 * 
 * Parámetros POST:
 * 
 * REQUERIDOS:
 * - sucursal_id: ID de la sucursal (int)
 * - productos: Array de productos en formato JSON string:
 *     [
 *       {
 *         "producto_id": 10,
 *         "cantidad": 2,
 *         "precio_unitario": 150.00,     // opcional, se obtiene automáticamente
 *         "tipo_precio": "publico"       // opcional: publico, mayorista, especial
 *       }
 *     ]
 * 
 * OPCIONALES:
 * - cliente_id: ID del cliente (null para venta mostrador)
 * - tipo_venta: 'normal' (default), 'credito', 'apartado'
 * - descuento: Monto de descuento (default: 0)
 * - formas_pago: Array de formas de pago (REQUERIDO para tipo_venta = 'normal'):
 *     [
 *       {
 *         "forma_pago": "efectivo",      // efectivo, tarjeta_debito, tarjeta_credito, transferencia, cheque
 *         "monto": 250.00,
 *         "referencia": "REF-123"        // opcional
 *       }
 *     ]
 * - numero_cuotas: Número de cuotas (solo para tipo_venta = 'credito', default: 4)
 * 
 * VALIDACIONES AUTOMÁTICAS:
 * - Stock suficiente de todos los productos
 * - Sucursal existe y está activa
 * - Cliente existe (si se proporciona)
 * - Formas de pago suman el total exacto (ventas normales)
 * - Hay caja abierta (ventas normales)
 * - Límite de crédito suficiente (ventas a crédito)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {
 *     "venta_id": 123,
 *     "numero_venta": "V-01-2026-0123",
 *     "total": 250.00,
 *     "tipo_venta": "normal",
 *     "venta_completa": {...}
 *   },
 *   "message": "Venta creada exitosamente"
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/venta.php';
require_once '../../models/producto.php';
require_once '../../models/cliente.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('POST');
verificar_api_permiso('ventas', 'crear');

try {
    // Leer el body JSON
    $json_input = file_get_contents('php://input');
    $datos_json = json_decode($json_input, true);
    
    // Si no hay JSON válido, intentar leer de POST normal
    if (json_last_error() !== JSON_ERROR_NONE || empty($datos_json)) {
        // Modo Form-Data (fallback)
        $datos_json = $_POST;
    }
    
    // Validar campos requeridos
    if (empty($datos_json['sucursal_id'])) {
        responder_json(false, null, 'El campo sucursal_id es requerido', 'CAMPO_REQUERIDO');
    }
    
    if (empty($datos_json['productos'])) {
        responder_json(false, null, 'El campo productos es requerido', 'CAMPO_REQUERIDO');
    }
    
    // Obtener datos base
    $sucursal_id = (int)$datos_json['sucursal_id'];
    $cliente_id = isset($datos_json['cliente_id']) ? (int)$datos_json['cliente_id'] : null;
    $tipo_venta = isset($datos_json['tipo_venta']) ? $datos_json['tipo_venta'] : 'normal';
    $descuento = isset($datos_json['descuento']) ? (float)$datos_json['descuento'] : 0;
    
    // Validar tipo de venta
    if (!in_array($tipo_venta, ['normal', 'credito', 'apartado'])) {
        responder_json(false, null, 'Tipo de venta inválido', 'TIPO_VENTA_INVALIDO');
    }
    
    // Obtener productos
    $productos = $datos_json['productos'];
    
    // Si productos viene como string JSON, decodificar
    if (is_string($productos)) {
        $productos = json_decode($productos, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            responder_json(false, null, 'Formato de productos inválido: ' . json_last_error_msg(), 'PRODUCTOS_INVALIDOS');
        }
    }
    
    if (empty($productos) || !is_array($productos)) {
        responder_json(false, null, 'Debe incluir al menos un producto', 'PRODUCTOS_REQUERIDOS');
    }
    
    // Procesar formas de pago (solo para ventas normales)
    $formas_pago = null;
    if ($tipo_venta === 'normal') {
        if (empty($datos_json['formas_pago'])) {
            responder_json(false, null, 'Las ventas normales deben incluir formas de pago', 'FORMAS_PAGO_REQUERIDAS');
        }
        
        $formas_pago = $datos_json['formas_pago'];
        
        // Si formas_pago viene como string JSON, decodificar
        if (is_string($formas_pago)) {
            $formas_pago = json_decode($formas_pago, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                responder_json(false, null, 'Formato de formas de pago inválido: ' . json_last_error_msg(), 'FORMAS_PAGO_INVALIDAS');
            }
        }
        
        if (empty($formas_pago) || !is_array($formas_pago)) {
            responder_json(false, null, 'Las formas de pago deben ser un array', 'FORMAS_PAGO_INVALIDAS');
        }
    }
    
    // Obtener número de cuotas (solo para crédito)
    $numero_cuotas = 4;
    if ($tipo_venta === 'credito') {
        $numero_cuotas = isset($datos_json['numero_cuotas']) ? (int)$datos_json['numero_cuotas'] : 4;
        if ($numero_cuotas < 1) {
            $numero_cuotas = 4;
        }
    }
    
    // Preparar datos para el modelo
    $datos_venta = [
        'sucursal_id' => $sucursal_id,
        'cliente_id' => $cliente_id,
        'tipo_venta' => $tipo_venta,
        'descuento' => $descuento,
        'productos' => $productos,
        'vendedor_id' => usuario_actual_id()
    ];
    
    // Agregar formas de pago si aplica
    if ($formas_pago !== null) {
        $datos_venta['formas_pago'] = $formas_pago;
    }
    
    // Agregar número de cuotas si es crédito
    if ($tipo_venta === 'credito') {
        $datos_venta['numero_cuotas'] = $numero_cuotas;
    }
    
    // Validar datos antes de crear
    // El modelo Venta::validar() hace todas las validaciones:
    // - Stock suficiente
    // - Sucursal válida
    // - Cliente válido (si aplica)
    // - Formas de pago válidas y suman el total
    // - Caja abierta (ventas normales)
    // - Límite de crédito (ventas a crédito)
    $errores = Venta::validar($datos_venta);
    
    if (!empty($errores)) {
        responder_json(
            false,
            ['errores' => $errores],
            'Errores de validación: ' . implode(', ', $errores),
            'VALIDACION_FALLIDA'
        );
    }
    
    // Crear venta
    // El modelo maneja todo el proceso:
    // 1. Genera número de venta
    // 2. Calcula totales
    // 3. Inserta venta
    // 4. Inserta detalles
    // 5. Actualiza inventario
    // 6. Registra movimientos
    // 7. Inserta formas de pago (si aplica)
    // 8. Registra en caja (si aplica)
    // 9. Crea crédito (si aplica)
    $venta_id = Venta::crear($datos_venta);
    
    if (!$venta_id) {
        throw new Exception('No se pudo crear la venta. Revise los logs para más detalles.');
    }
    
    // Obtener venta completa creada
    $venta_completa = Venta::obtenerPorId($venta_id);
    
    if (!$venta_completa) {
        // La venta se creó pero no se pudo obtener
        responder_json(
            true,
            [
                'venta_id' => $venta_id,
                'advertencia' => 'Venta creada pero no se pudo obtener el detalle completo'
            ],
            'Venta creada exitosamente pero con advertencia',
            'VENTA_CREADA_CON_ADVERTENCIA'
        );
    }
    
    // Preparar respuesta exitosa
    $respuesta = [
        'venta_id' => $venta_id,
        'numero_venta' => $venta_completa['numero_venta'],
        'total' => $venta_completa['total'],
        'tipo_venta' => $venta_completa['tipo_venta'],
        'estado' => $venta_completa['estado'],
        'fecha' => $venta_completa['fecha'],
        'hora' => $venta_completa['hora'],
        'venta_completa' => $venta_completa
    ];
    
    $mensaje = "Venta {$venta_completa['numero_venta']} creada exitosamente";
    
    // Agregar información adicional según tipo de venta
    if ($tipo_venta === 'credito' && isset($venta_completa['credito'])) {
        $respuesta['credito'] = [
            'cuota_semanal' => $venta_completa['credito']['cuota_semanal'],
            'numero_cuotas' => $venta_completa['credito']['numero_cuotas'],
            'fecha_proximo_pago' => $venta_completa['credito']['fecha_proximo_pago']
        ];
        $mensaje .= " (Crédito: {$numero_cuotas} cuotas semanales)";
    }
    
    responder_json(
        true,
        $respuesta,
        $mensaje
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al crear venta: ' . $e->getMessage(),
        'ERROR_CREAR_VENTA'
    );
}
