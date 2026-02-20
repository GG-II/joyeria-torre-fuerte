<?php
/**
 * ================================================
 * API: BUSCAR PRODUCTOS CON STOCK (POS)
 * ================================================
 * Endpoint específico para POS que busca productos y devuelve 
 * el stock disponible en la sucursal seleccionada
 * 
 * Método: GET
 * Autenticación: Requerida
 * 
 * Parámetros GET:
 * - termino: Término de búsqueda (requerido)
 * - sucursal_id: ID de la sucursal (requerido)
 * - limite: Número máximo de resultados (default: 20)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": [
 *     {
 *       "id": 1,
 *       "codigo": "AN-001",
 *       "codigo_barras": "123456789",
 *       "nombre": "Anillo de oro",
 *       "descripcion": "...",
 *       "categoria_id": 1,
 *       "categoria_nombre": "Anillos",
 *       "precio_publico": "150.00",
 *       "precio_mayorista": "120.00",
 *       "precio_especial": "100.00",
 *       "stock_disponible": 5,
 *       "stock_minimo": 2,
 *       "es_por_peso": 0,
 *       "peso_gramos": "10.00"
 *     }
 *   ],
 *   "message": "X producto(s) encontrado(s)"
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('GET');
verificar_api_permiso('productos', 'ver');

try {
    // Validar campos requeridos
    if (!isset($_GET['termino']) || empty(trim($_GET['termino']))) {
        responder_json(false, null, 'El término de búsqueda es requerido', 'TERMINO_REQUERIDO');
    }
    
    if (!isset($_GET['sucursal_id']) || empty($_GET['sucursal_id'])) {
        responder_json(false, null, 'El ID de sucursal es requerido', 'SUCURSAL_REQUERIDA');
    }
    
    $termino = trim($_GET['termino']);
    $sucursal_id = (int)$_GET['sucursal_id'];
    $limite = isset($_GET['limite']) && $_GET['limite'] > 0 ? (int)$_GET['limite'] : 20;
    
    global $pdo;
    
    // Verificar que la sucursal existe y está activa
    $sql_sucursal = "SELECT id, nombre FROM sucursales WHERE id = ? AND activo = 1";
    $stmt_sucursal = $pdo->prepare($sql_sucursal);
    $stmt_sucursal->execute([$sucursal_id]);
    $sucursal = $stmt_sucursal->fetch(PDO::FETCH_ASSOC);
    
    if (!$sucursal) {
        responder_json(false, null, 'La sucursal no existe o está inactiva', 'SUCURSAL_INVALIDA');
    }
    
    // Buscar productos con stock de la sucursal y precios
    $sql = "
        SELECT 
            p.id,
            p.codigo,
            p.codigo_barras,
            p.nombre,
            p.descripcion,
            p.categoria_id,
            c.nombre as categoria_nombre,
            MAX(CASE WHEN pp.tipo_precio = 'publico' THEN pp.precio END) as precio_publico,
            MAX(CASE WHEN pp.tipo_precio = 'mayorista' THEN pp.precio END) as precio_mayorista,
            MAX(CASE WHEN pp.tipo_precio = 'especial' THEN pp.precio END) as precio_especial,
            p.es_por_peso,
            p.peso_gramos,
            p.estilo,
            p.largo_cm,
            COALESCE(i.cantidad, 0) as stock_disponible,
            COALESCE(i.stock_minimo, 0) as stock_minimo
        FROM productos p
        LEFT JOIN categorias c ON c.id = p.categoria_id
        LEFT JOIN precios_producto pp ON pp.producto_id = p.id AND pp.activo = 1
        LEFT JOIN inventario i ON i.producto_id = p.id AND i.sucursal_id = ?
        WHERE p.activo = 1
          AND (
            p.nombre LIKE ? 
            OR p.codigo LIKE ? 
            OR p.codigo_barras = ?
            OR p.descripcion LIKE ?
          )
        GROUP BY p.id
        ORDER BY 
          CASE 
            WHEN p.codigo_barras = ? THEN 1
            WHEN p.codigo LIKE ? THEN 2
            WHEN p.nombre LIKE ? THEN 3
            ELSE 4
          END,
          p.nombre ASC
        LIMIT ?
    ";
    
    // Preparar parámetros para búsqueda
    $busqueda = '%' . $termino . '%';
    $codigo_inicio = $termino . '%';
    
    $params = [
        $sucursal_id,      // JOIN inventario
        $busqueda,         // nombre LIKE
        $busqueda,         // codigo LIKE
        $termino,          // codigo_barras exacto
        $busqueda,         // descripcion LIKE
        $termino,          // ORDER BY: codigo_barras exacto (prioridad 1)
        $codigo_inicio,    // ORDER BY: codigo empieza con (prioridad 2)
        $busqueda,         // ORDER BY: nombre contiene (prioridad 3)
        $limite
    ];
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear números para consistencia
    foreach ($productos as &$producto) {
        $producto['stock_disponible'] = (int)$producto['stock_disponible'];
        $producto['stock_minimo'] = (int)$producto['stock_minimo'];
        $producto['es_por_peso'] = (int)$producto['es_por_peso'];
    }
    
    // Responder
    responder_json(
        true,
        $productos,
        count($productos) . ' producto(s) encontrado(s) en ' . $sucursal['nombre']
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al buscar productos: ' . $e->getMessage(),
        'ERROR_BUSCAR_PRODUCTOS'
    );
}