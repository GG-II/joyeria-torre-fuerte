<?php
/**
 * ================================================
 * API: BUSCAR PRODUCTOS PARA VENTA (POS)
 * ================================================
 * Búsqueda optimizada para el punto de venta
 * Incluye stock disponible en la sucursal
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';

header('Content-Type: application/json; charset=utf-8');

verificar_api_autenticacion();
validar_metodo_http('GET');
verificar_api_permiso('ventas', 'crear');

try {
    if (!isset($_GET['q']) || empty(trim($_GET['q']))) {
        responder_json(false, null, 'El término de búsqueda es requerido', 'TERMINO_REQUERIDO');
    }
    
    if (!isset($_GET['sucursal_id']) || empty($_GET['sucursal_id'])) {
        responder_json(false, null, 'El ID de sucursal es requerido', 'SUCURSAL_REQUERIDA');
    }
    
    $busqueda = trim($_GET['q']);
    $sucursal_id = (int)$_GET['sucursal_id'];
    $limite = isset($_GET['limite']) && $_GET['limite'] > 0 ? (int)$_GET['limite'] : 10;
    
    global $pdo;
    
    // Consulta con los campos correctos: codigo y cantidad
    $sql = "
        SELECT 
            p.id,
            p.codigo as sku,
            p.codigo_barras,
            p.nombre,
            p.precio_venta,
            c.nombre as categoria_nombre,
            COALESCE(i.cantidad, 0) as stock_actual
        FROM productos p
        LEFT JOIN categorias c ON p.categoria_id = c.id
        LEFT JOIN inventario i ON p.id = i.producto_id AND i.sucursal_id = ?
        WHERE p.activo = 1
          AND (
              p.codigo = ? 
              OR p.codigo LIKE ?
              OR p.codigo_barras = ?
              OR p.codigo_barras LIKE ?
              OR p.nombre LIKE ?
          )
        ORDER BY 
            CASE 
                WHEN p.codigo = ? THEN 1
                WHEN p.codigo_barras = ? THEN 2
                WHEN p.codigo LIKE ? THEN 3
                WHEN p.nombre LIKE ? THEN 4
                ELSE 5
            END,
            p.nombre
        LIMIT ?
    ";
    
    $busqueda_like = "%{$busqueda}%";
    $busqueda_start = "{$busqueda}%";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $sucursal_id,           // Para LEFT JOIN
        $busqueda,              // codigo exacto
        $busqueda_like,         // codigo parcial
        $busqueda,              // codigo_barras exacto
        $busqueda_like,         // codigo_barras parcial
        $busqueda_like,         // nombre parcial
        $busqueda,              // ORDER BY - codigo exacto
        $busqueda,              // ORDER BY - codigo_barras exacto
        $busqueda_start,        // ORDER BY - codigo empieza con
        $busqueda_start,        // ORDER BY - nombre empieza con
        $limite
    ]);
    
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convertir valores numéricos
    foreach ($productos as &$p) {
        $p['id'] = (int)$p['id'];
        $p['precio_venta'] = (float)$p['precio_venta'];
        $p['stock_actual'] = (int)$p['stock_actual'];
    }
    
    responder_json(
        true,
        $productos,
        count($productos) . ' producto(s) encontrado(s)'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error en búsqueda: ' . $e->getMessage(),
        'ERROR_BUSQUEDA'
    );
}