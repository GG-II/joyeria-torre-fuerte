<?php
/**
 * Modelo Reporte
 * 
 * Genera reportes y estadísticas del sistema:
 * - Reportes de ventas (diarias, mensuales, por vendedor, por sucursal)
 * - Reportes de productos (más vendidos, menos movimiento)
 * - Reportes de inventario
 * - Reportes de trabajos de taller
 * - Reportes financieros (cuentas por cobrar, ganancias)
 * - Reportes comparativos
 * 
 * @author Sistema Joyería Torre Fuerte
 * @version 1.0
 * @date 2026-01-22
 */

class Reporte {
    
    // ========================================
    // REPORTES DE VENTAS
    // ========================================
    
    /**
     * Reporte de ventas diarias
     * 
     * @param string $fecha Fecha del reporte (YYYY-MM-DD)
     * @param int $sucursal_id ID de la sucursal (null para todas)
     * @return array Datos del reporte con totales y detalle de ventas
     */
    public static function reporteVentasDiarias($fecha, $sucursal_id = null) {
        try {
            $where = ["v.fecha = ?"];
            $params = [$fecha];
            
            if ($sucursal_id !== null) {
                $where[] = "v.sucursal_id = ?";
                $params[] = $sucursal_id;
            }
            
            $where_sql = implode(' AND ', $where);
            
            // Totales generales del día
            $totales = db_query_one(
                "SELECT 
                    COUNT(*) as total_ventas,
                    COALESCE(SUM(v.total), 0) as monto_total,
                    COALESCE(SUM(v.descuento), 0) as total_descuentos,
                    COALESCE(AVG(v.total), 0) as ticket_promedio,
                    COUNT(CASE WHEN v.tipo_venta = 'normal' THEN 1 END) as ventas_contado,
                    COUNT(CASE WHEN v.tipo_venta = 'credito' THEN 1 END) as ventas_credito
                 FROM ventas v
                 WHERE $where_sql AND v.estado != 'anulada'",
                $params
            );
            
            // Detalle de ventas
            $ventas = db_query(
                "SELECT 
                    v.id,
                    v.numero_venta,
                    v.fecha,
                    v.hora,
                    v.total,
                    v.descuento,
                    v.tipo_venta,
                    s.nombre as sucursal,
                    u.nombre as vendedor,
                    COALESCE(c.nombre, 'Sin cliente') as cliente
                 FROM ventas v
                 INNER JOIN sucursales s ON v.sucursal_id = s.id
                 INNER JOIN usuarios u ON v.usuario_id = u.id
                 LEFT JOIN clientes c ON v.cliente_id = c.id
                 WHERE $where_sql AND v.estado != 'anulada'
                 ORDER BY v.hora DESC",
                $params
            );
            
            // Ventas por forma de pago
            $formas_pago = db_query(
                "SELECT 
                    fp.forma_pago,
                    COUNT(DISTINCT fp.venta_id) as cantidad_ventas,
                    SUM(fp.monto) as monto_total
                 FROM formas_pago_venta fp
                 INNER JOIN ventas v ON fp.venta_id = v.id
                 WHERE $where_sql AND v.estado != 'anulada'
                 GROUP BY fp.forma_pago
                 ORDER BY monto_total DESC",
                $params
            );
            
            return [
                'fecha' => $fecha,
                'sucursal_id' => $sucursal_id,
                'totales' => $totales,
                'ventas' => $ventas,
                'formas_pago' => $formas_pago
            ];
            
        } catch (PDOException $e) {
            registrar_error("Error en reporte de ventas diarias: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Reporte de ventas mensuales
     * 
     * @param int $mes Mes (1-12)
     * @param int $año Año (YYYY)
     * @param int $sucursal_id ID de la sucursal (null para todas)
     * @return array Datos del reporte con totales y desglose por días
     */
    public static function reporteVentasMensuales($mes, $año, $sucursal_id = null) {
        try {
            $where = ["YEAR(v.fecha) = ?", "MONTH(v.fecha) = ?"];
            $params = [$año, $mes];
            
            if ($sucursal_id !== null) {
                $where[] = "v.sucursal_id = ?";
                $params[] = $sucursal_id;
            }
            
            $where_sql = implode(' AND ', $where);
            
            // Totales del mes
            $totales = db_query_one(
                "SELECT 
                    COUNT(*) as total_ventas,
                    COALESCE(SUM(v.total), 0) as monto_total,
                    COALESCE(SUM(v.descuento), 0) as total_descuentos,
                    COALESCE(AVG(v.total), 0) as ticket_promedio
                 FROM ventas v
                 WHERE $where_sql AND v.estado != 'anulada'",
                $params
            );
            
            // Ventas por día del mes
            $ventas_por_dia = db_query(
                "SELECT 
                    v.fecha,
                    COUNT(*) as cantidad_ventas,
                    SUM(v.total) as monto_total
                 FROM ventas v
                 WHERE $where_sql AND v.estado != 'anulada'
                 GROUP BY v.fecha
                 ORDER BY v.fecha",
                $params
            );
            
            // Productos más vendidos del mes
            $productos_top = db_query(
                "SELECT 
                    p.nombre as producto,
                    SUM(dv.cantidad) as cantidad_vendida,
                    SUM(dv.subtotal) as monto_total
                 FROM detalle_ventas dv
                 INNER JOIN ventas v ON dv.venta_id = v.id
                 INNER JOIN productos p ON dv.producto_id = p.id
                 WHERE $where_sql AND v.estado != 'anulada'
                 GROUP BY p.id, p.nombre
                 ORDER BY cantidad_vendida DESC
                 LIMIT 10",
                $params
            );
            
            return [
                'mes' => $mes,
                'año' => $año,
                'sucursal_id' => $sucursal_id,
                'totales' => $totales,
                'ventas_por_dia' => $ventas_por_dia,
                'productos_top' => $productos_top
            ];
            
        } catch (PDOException $e) {
            registrar_error("Error en reporte de ventas mensuales: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Reporte de ventas por vendedor
     * 
     * @param string $fecha_inicio Fecha inicio (YYYY-MM-DD)
     * @param string $fecha_fin Fecha fin (YYYY-MM-DD)
     * @return array Estadísticas por vendedor
     */
    public static function reporteVentasPorVendedor($fecha_inicio, $fecha_fin) {
        try {
            $ventas_por_vendedor = db_query(
                "SELECT 
                    u.id as vendedor_id,
                    u.nombre as vendedor,
                    s.nombre as sucursal,
                    COUNT(v.id) as total_ventas,
                    SUM(v.total) as monto_total,
                    AVG(v.total) as ticket_promedio,
                    MAX(v.total) as venta_mayor,
                    MIN(v.total) as venta_menor
                 FROM ventas v
                 INNER JOIN usuarios u ON v.usuario_id = u.id
                 INNER JOIN sucursales s ON v.sucursal_id = s.id
                 WHERE v.fecha BETWEEN ? AND ?
                   AND v.estado != 'anulada'
                 GROUP BY u.id, u.nombre, s.nombre
                 ORDER BY monto_total DESC",
                [$fecha_inicio, $fecha_fin]
            );
            
            return [
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
                'vendedores' => $ventas_por_vendedor
            ];
            
        } catch (PDOException $e) {
            registrar_error("Error en reporte de ventas por vendedor: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Reporte de ventas por sucursal
     * 
     * @param string $fecha_inicio Fecha inicio (YYYY-MM-DD)
     * @param string $fecha_fin Fecha fin (YYYY-MM-DD)
     * @return array Estadísticas por sucursal
     */
    public static function reporteVentasPorSucursal($fecha_inicio, $fecha_fin) {
        try {
            $ventas_por_sucursal = db_query(
                "SELECT 
                    s.id as sucursal_id,
                    s.nombre as sucursal,
                    COUNT(v.id) as total_ventas,
                    SUM(v.total) as monto_total,
                    AVG(v.total) as ticket_promedio,
                    COUNT(DISTINCT v.usuario_id) as vendedores_activos
                 FROM ventas v
                 INNER JOIN sucursales s ON v.sucursal_id = s.id
                 WHERE v.fecha BETWEEN ? AND ?
                   AND v.estado != 'anulada'
                 GROUP BY s.id, s.nombre
                 ORDER BY monto_total DESC",
                [$fecha_inicio, $fecha_fin]
            );
            
            return [
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
                'sucursales' => $ventas_por_sucursal
            ];
            
        } catch (PDOException $e) {
            registrar_error("Error en reporte de ventas por sucursal: " . $e->getMessage());
            return [];
        }
    }
    
    // ========================================
    // REPORTES DE PRODUCTOS
    // ========================================
    
    /**
     * Reporte de productos más vendidos
     * 
     * @param string $fecha_inicio Fecha inicio (YYYY-MM-DD)
     * @param string $fecha_fin Fecha fin (YYYY-MM-DD)
     * @param int $limite Cantidad de productos a retornar (default 20)
     * @return array Lista de productos más vendidos
     */
    public static function reporteProductosMasVendidos($fecha_inicio, $fecha_fin, $limite = 20) {
        try {
            $productos = db_query(
                "SELECT 
                    p.id,
                    p.codigo,
                    p.nombre,
                    c.nombre as categoria,
                    SUM(dv.cantidad) as cantidad_vendida,
                    SUM(dv.subtotal) as monto_total,
                    COUNT(DISTINCT dv.venta_id) as numero_ventas,
                    AVG(dv.precio_unitario) as precio_promedio
                 FROM detalle_ventas dv
                 INNER JOIN ventas v ON dv.venta_id = v.id
                 INNER JOIN productos p ON dv.producto_id = p.id
                 INNER JOIN categorias c ON p.categoria_id = c.id
                 WHERE v.fecha BETWEEN ? AND ?
                   AND v.estado != 'anulada'
                 GROUP BY p.id, p.codigo, p.nombre, c.nombre
                 ORDER BY cantidad_vendida DESC
                 LIMIT ?",
                [$fecha_inicio, $fecha_fin, $limite]
            );
            
            return [
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
                'productos' => $productos
            ];
            
        } catch (PDOException $e) {
            registrar_error("Error en reporte de productos más vendidos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Reporte de productos con menos movimiento
     * 
     * @param string $fecha_inicio Fecha inicio (YYYY-MM-DD)
     * @param string $fecha_fin Fecha fin (YYYY-MM-DD)
     * @param int $limite Cantidad de productos a retornar (default 20)
     * @return array Lista de productos con menos movimiento
     */
    public static function reporteProductosMenosMovimiento($fecha_inicio, $fecha_fin, $limite = 20) {
        try {
            // Productos que NO se han vendido en el periodo O se han vendido muy poco
            $productos = db_query(
                "SELECT 
                    p.id,
                    p.codigo,
                    p.nombre,
                    c.nombre as categoria,
                    COALESCE(SUM(i.cantidad), 0) as stock_actual,
                    COALESCE(ventas.cantidad_vendida, 0) as cantidad_vendida,
                    COALESCE(ventas.monto_total, 0) as monto_total,
                    DATEDIFF(?, p.fecha_creacion) as dias_sin_vender
                 FROM productos p
                 INNER JOIN categorias c ON p.categoria_id = c.id
                 LEFT JOIN inventario i ON p.id = i.producto_id
                 LEFT JOIN (
                     SELECT 
                         dv.producto_id,
                         SUM(dv.cantidad) as cantidad_vendida,
                         SUM(dv.subtotal) as monto_total
                     FROM detalle_ventas dv
                     INNER JOIN ventas v ON dv.venta_id = v.id
                     WHERE v.fecha BETWEEN ? AND ?
                       AND v.estado != 'anulada'
                     GROUP BY dv.producto_id
                 ) ventas ON p.id = ventas.producto_id
                 WHERE p.activo = 1
                 GROUP BY p.id, p.codigo, p.nombre, c.nombre, p.fecha_creacion, ventas.cantidad_vendida, ventas.monto_total
                 ORDER BY cantidad_vendida ASC, dias_sin_vender DESC
                 LIMIT ?",
                [$fecha_fin, $fecha_inicio, $fecha_fin, $limite]
            );
            
            return [
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
                'productos' => $productos
            ];
            
        } catch (PDOException $e) {
            registrar_error("Error en reporte de productos con menos movimiento: " . $e->getMessage());
            return [];
        }
    }
    
    // ========================================
    // REPORTES DE INVENTARIO
    // ========================================
    
    /**
     * Reporte de inventario actual
     * 
     * @param int $sucursal_id ID de la sucursal (null para todas)
     * @return array Estado actual del inventario
     */
    public static function reporteInventarioActual($sucursal_id = null) {
        try {
            $where = ["i.cantidad >= 0"];
            $params = [];
            
            if ($sucursal_id !== null) {
                $where[] = "i.sucursal_id = ?";
                $params[] = $sucursal_id;
            }
            
            $where_sql = implode(' AND ', $where);
            
            // Resumen general
            $resumen = db_query_one(
                "SELECT 
                    COUNT(DISTINCT i.producto_id) as total_productos,
                    SUM(i.cantidad) as total_unidades,
                    COUNT(CASE WHEN i.cantidad < i.stock_minimo THEN 1 END) as productos_bajo_stock,
                    COUNT(CASE WHEN i.cantidad = 0 THEN 1 END) as productos_sin_stock
                 FROM inventario i
                 WHERE $where_sql",
                $params
            );
            
            // Productos con stock bajo
            $productos_bajo_stock = db_query(
                "SELECT 
                    p.codigo,
                    p.nombre,
                    c.nombre as categoria,
                    i.cantidad as stock_actual,
                    i.stock_minimo,
                    s.nombre as sucursal
                 FROM inventario i
                 INNER JOIN productos p ON i.producto_id = p.id
                 INNER JOIN categorias c ON p.categoria_id = c.id
                 INNER JOIN sucursales s ON i.sucursal_id = s.id
                 WHERE $where_sql AND i.cantidad < i.stock_minimo
                 ORDER BY (i.stock_minimo - i.cantidad) DESC",
                $params
            );
            
            // Productos sin stock
            $productos_sin_stock = db_query(
                "SELECT 
                    p.codigo,
                    p.nombre,
                    c.nombre as categoria,
                    s.nombre as sucursal
                 FROM inventario i
                 INNER JOIN productos p ON i.producto_id = p.id
                 INNER JOIN categorias c ON p.categoria_id = c.id
                 INNER JOIN sucursales s ON i.sucursal_id = s.id
                 WHERE $where_sql AND i.cantidad = 0
                 ORDER BY p.nombre",
                $params
            );
            
            // Inventario detallado
            $inventario_detalle = db_query(
                "SELECT 
                    p.codigo,
                    p.nombre,
                    c.nombre as categoria,
                    i.cantidad as stock_actual,
                    i.stock_minimo,
                    s.nombre as sucursal,
                    CASE 
                        WHEN i.cantidad = 0 THEN 'Sin stock'
                        WHEN i.cantidad < i.stock_minimo THEN 'Bajo stock'
                        ELSE 'Normal'
                    END as estado
                 FROM inventario i
                 INNER JOIN productos p ON i.producto_id = p.id
                 INNER JOIN categorias c ON p.categoria_id = c.id
                 INNER JOIN sucursales s ON i.sucursal_id = s.id
                 WHERE $where_sql
                 ORDER BY estado DESC, p.nombre",
                $params
            );
            
            return [
                'sucursal_id' => $sucursal_id,
                'resumen' => $resumen,
                'productos_bajo_stock' => $productos_bajo_stock,
                'productos_sin_stock' => $productos_sin_stock,
                'inventario_detalle' => $inventario_detalle
            ];
            
        } catch (PDOException $e) {
            registrar_error("Error en reporte de inventario actual: " . $e->getMessage());
            return [];
        }
    }
    
    // ========================================
    // REPORTES DE TALLER
    // ========================================
    
    /**
     * Reporte de trabajos pendientes
     * 
     * @return array Lista de trabajos pendientes con alertas
     */
    public static function reporteTrabajosPendientes() {
        try {
            // Trabajos por estado
            $resumen = db_query_one(
                "SELECT 
                    COUNT(CASE WHEN estado = 'recibido' THEN 1 END) as recibidos,
                    COUNT(CASE WHEN estado = 'en_proceso' THEN 1 END) as en_proceso,
                    COUNT(*) as total
                 FROM trabajos_taller
                 WHERE estado NOT IN ('completado', 'entregado', 'cancelado')"
            );
            
            // Trabajos pendientes detallados
            $trabajos_pendientes = db_query(
                "SELECT 
                    t.id,
                    t.codigo,
                    t.descripcion_pieza,
                    t.descripcion_trabajo,
                    t.fecha_recepcion,
                    t.fecha_entrega_prometida,
                    t.estado,
                    t.tipo_trabajo,
                    t.cliente_nombre,
                    o.nombre as empleado_actual,
                    DATEDIFF(?, t.fecha_recepcion) as dias_transcurridos,
                    DATEDIFF(t.fecha_entrega_prometida, ?) as dias_restantes
                 FROM trabajos_taller t
                 LEFT JOIN usuarios o ON t.empleado_actual_id = o.id
                 WHERE t.estado NOT IN ('completado', 'entregado', 'cancelado')
                 ORDER BY t.fecha_entrega_prometida ASC",
                [date('Y-m-d'), date('Y-m-d')]
            );
            
            // Trabajos atrasados (fecha prometida vencida)
            $trabajos_atrasados = db_query(
                "SELECT 
                    t.id,
                    t.codigo,
                    t.descripcion_pieza,
                    t.fecha_entrega_prometida,
                    t.estado,
                    t.cliente_nombre,
                    o.nombre as empleado_actual,
                    DATEDIFF(?, t.fecha_entrega_prometida) as dias_atraso
                 FROM trabajos_taller t
                 LEFT JOIN usuarios o ON t.empleado_actual_id = o.id
                 WHERE t.estado NOT IN ('completado', 'entregado', 'cancelado')
                   AND t.fecha_entrega_prometida < ?
                 ORDER BY dias_atraso DESC",
                [date('Y-m-d'), date('Y-m-d')]
            );
            
            return [
                'resumen' => $resumen,
                'trabajos_pendientes' => $trabajos_pendientes,
                'trabajos_atrasados' => $trabajos_atrasados
            ];
            
        } catch (PDOException $e) {
            registrar_error("Error en reporte de trabajos pendientes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Reporte de trabajos completados
     * 
     * @param string $fecha_inicio Fecha inicio (YYYY-MM-DD)
     * @param string $fecha_fin Fecha fin (YYYY-MM-DD)
     * @return array Lista de trabajos completados en el periodo
     */
    public static function reporteTrabajosCompletados($fecha_inicio, $fecha_fin) {
        try {
            // Resumen
            $resumen = db_query_one(
                "SELECT 
                    COUNT(*) as total_trabajos,
                    SUM(precio_total) as ingresos_totales,
                    AVG(precio_total) as ingreso_promedio,
                    AVG(DATEDIFF(fecha_entrega_real, fecha_recepcion)) as tiempo_promedio_dias
                 FROM trabajos_taller
                 WHERE DATE(fecha_entrega_real) BETWEEN ? AND ?
                   AND estado = 'entregado'",
                [$fecha_inicio, $fecha_fin]
            );
            
            // Trabajos por tipo
            $por_tipo = db_query(
                "SELECT 
                    tipo_trabajo,
                    COUNT(*) as cantidad,
                    SUM(precio_total) as ingresos,
                    AVG(precio_total) as ingreso_promedio
                 FROM trabajos_taller
                 WHERE DATE(fecha_entrega_real) BETWEEN ? AND ?
                   AND estado = 'entregado'
                 GROUP BY tipo_trabajo
                 ORDER BY ingresos DESC",
                [$fecha_inicio, $fecha_fin]
            );
            
            // Trabajos por empleado
            $por_empleado = db_query(
                "SELECT 
                    u.nombre as empleado,
                    COUNT(*) as trabajos_completados,
                    SUM(t.precio_total) as ingresos_generados,
                    AVG(DATEDIFF(t.fecha_entrega_real, t.fecha_recepcion)) as tiempo_promedio_dias
                 FROM trabajos_taller t
                 INNER JOIN usuarios u ON t.empleado_actual_id = u.id
                 WHERE DATE(t.fecha_entrega_real) BETWEEN ? AND ?
                   AND t.estado = 'entregado'
                 GROUP BY u.id, u.nombre
                 ORDER BY trabajos_completados DESC",
                [$fecha_inicio, $fecha_fin]
            );
            
            // Detalle de trabajos
            $trabajos = db_query(
                "SELECT 
                    t.codigo,
                    t.descripcion_pieza,
                    t.tipo_trabajo,
                    t.fecha_recepcion,
                    t.fecha_entrega_real,
                    t.precio_total,
                    t.cliente_nombre,
                    o.nombre as empleado,
                    DATEDIFF(t.fecha_entrega_real, t.fecha_recepcion) as dias_duracion
                 FROM trabajos_taller t
                 LEFT JOIN usuarios o ON t.empleado_actual_id = o.id
                 WHERE DATE(t.fecha_entrega_real) BETWEEN ? AND ?
                   AND t.estado = 'entregado'
                 ORDER BY t.fecha_entrega_real DESC",
                [$fecha_inicio, $fecha_fin]
            );
            
            return [
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
                'resumen' => $resumen,
                'por_tipo' => $por_tipo,
                'por_empleado' => $por_empleado,
                'trabajos' => $trabajos
            ];
            
        } catch (PDOException $e) {
            registrar_error("Error en reporte de trabajos completados: " . $e->getMessage());
            return [];
        }
    }
    
    // ========================================
    // REPORTES FINANCIEROS
    // ========================================
    
    /**
     * Reporte de cuentas por cobrar
     * 
     * @return array Lista de créditos activos y vencidos
     */
    public static function reporteCuentasPorCobrar() {
        try {
            // Resumen general
            $resumen = db_query_one(
                "SELECT 
                    COUNT(*) as total_creditos,
                    COUNT(CASE WHEN estado = 'activo' THEN 1 END) as creditos_activos,
                    COUNT(CASE WHEN estado = 'vencido' THEN 1 END) as creditos_vencidos,
                    SUM(saldo_pendiente) as total_por_cobrar,
                    SUM(CASE WHEN estado = 'activo' THEN saldo_pendiente ELSE 0 END) as saldo_al_dia,
                    SUM(CASE WHEN estado = 'vencido' THEN saldo_pendiente ELSE 0 END) as saldo_vencido
                 FROM creditos_clientes
                 WHERE estado IN ('activo', 'vencido')"
            );
            
            // Créditos por cliente
            $por_cliente = db_query(
                "SELECT 
                    c.id as cliente_id,
                    c.nombre as cliente,
                    c.telefono,
                    COUNT(cr.id) as numero_creditos,
                    SUM(cr.saldo_pendiente) as saldo_total,
                    MAX(cr.dias_atraso) as dias_atraso_max,
                    GROUP_CONCAT(cr.numero_cuotas - cr.cuotas_pagadas SEPARATOR ', ') as cuotas_pendientes
                 FROM clientes c
                 INNER JOIN creditos_clientes cr ON c.id = cr.cliente_id
                 WHERE cr.estado IN ('activo', 'vencido')
                 GROUP BY c.id, c.nombre, c.telefono
                 ORDER BY saldo_total DESC"
            );
            
            // Créditos vencidos (alertas)
            $creditos_vencidos = db_query(
                "SELECT 
                    c.nombre as cliente,
                    c.telefono,
                    cr.id as credito_id,
                    cr.monto_total,
                    cr.saldo_pendiente,
                    cr.cuota_semanal,
                    cr.cuotas_pagadas,
                    cr.numero_cuotas,
                    cr.fecha_proximo_pago,
                    cr.dias_atraso
                 FROM creditos_clientes cr
                 INNER JOIN clientes c ON cr.cliente_id = c.id
                 WHERE cr.estado = 'vencido'
                 ORDER BY cr.dias_atraso DESC"
            );
            
            // Créditos próximos a vencer (7 días)
            $proximos_vencer = db_query(
                "SELECT 
                    c.nombre as cliente,
                    c.telefono,
                    cr.id as credito_id,
                    cr.saldo_pendiente,
                    cr.cuota_semanal,
                    cr.fecha_proximo_pago,
                    DATEDIFF(cr.fecha_proximo_pago, ?) as dias_restantes
                 FROM creditos_clientes cr
                 INNER JOIN clientes c ON cr.cliente_id = c.id
                 WHERE cr.estado = 'activo'
                   AND cr.fecha_proximo_pago BETWEEN ? AND DATE_ADD(?, INTERVAL 7 DAY)
                 ORDER BY cr.fecha_proximo_pago ASC",
                [date('Y-m-d'), date('Y-m-d'), date('Y-m-d')]
            );
            
            return [
                'resumen' => $resumen,
                'por_cliente' => $por_cliente,
                'creditos_vencidos' => $creditos_vencidos,
                'proximos_vencer' => $proximos_vencer
            ];
            
        } catch (PDOException $e) {
            registrar_error("Error en reporte de cuentas por cobrar: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Reporte de ganancias
     * 
     * @param string $fecha_inicio Fecha inicio (YYYY-MM-DD)
     * @param string $fecha_fin Fecha fin (YYYY-MM-DD)
     * @return array Análisis de ganancias del periodo
     */
    public static function reporteGanancias($fecha_inicio, $fecha_fin) {
        try {
            // Ingresos por ventas
            $ingresos_ventas = db_query_one(
                "SELECT 
                    COALESCE(SUM(total), 0) as total_ventas,
                    COUNT(*) as numero_ventas
                 FROM ventas
                 WHERE fecha BETWEEN ? AND ?
                   AND estado != 'anulada'
                   AND tipo_venta = 'normal'",
                [$fecha_inicio, $fecha_fin]
            );
            
            // Ingresos por reparaciones
            $ingresos_taller = db_query_one(
                "SELECT 
                    COALESCE(SUM(precio_total), 0) as total_reparaciones,
                    COUNT(*) as numero_trabajos
                 FROM trabajos_taller
                 WHERE DATE(fecha_entrega_real) BETWEEN ? AND ?
                   AND estado = 'entregado'",
                [$fecha_inicio, $fecha_fin]
            );
            
            // Ingresos por abonos a crédito
            $ingresos_abonos = db_query_one(
                "SELECT 
                    COALESCE(SUM(monto), 0) as total_abonos,
                    COUNT(*) as numero_abonos
                 FROM abonos_creditos
                 WHERE DATE(fecha_abono) BETWEEN ? AND ?",
                [$fecha_inicio, $fecha_fin]
            );
            
            // Egresos de caja
            $egresos = db_query_one(
                "SELECT 
                    COALESCE(SUM(monto), 0) as total_egresos
                 FROM movimientos_caja mc
                 INNER JOIN cajas c ON mc.caja_id = c.id
                 WHERE DATE(mc.fecha_hora) BETWEEN ? AND ?
                   AND mc.categoria = 'egreso'",
                [$fecha_inicio, $fecha_fin]
            );
            
            // Desglose de egresos por tipo
            $egresos_detalle = db_query(
                "SELECT 
                    mc.tipo_movimiento,
                    SUM(mc.monto) as monto_total,
                    COUNT(*) as cantidad
                 FROM movimientos_caja mc
                 INNER JOIN cajas c ON mc.caja_id = c.id
                 WHERE DATE(mc.fecha_hora) BETWEEN ? AND ?
                   AND mc.categoria = 'egreso'
                 GROUP BY mc.tipo_movimiento
                 ORDER BY monto_total DESC",
                [$fecha_inicio, $fecha_fin]
            );
            
            // Calcular totales
            $total_ingresos = 
                $ingresos_ventas['total_ventas'] + 
                $ingresos_taller['total_reparaciones'] +
                $ingresos_abonos['total_abonos'];
            
            $ganancia_neta = $total_ingresos - $egresos['total_egresos'];
            $margen = $total_ingresos > 0 ? ($ganancia_neta / $total_ingresos) * 100 : 0;
            
            return [
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
                'ingresos' => [
                    'ventas' => $ingresos_ventas,
                    'taller' => $ingresos_taller,
                    'abonos' => $ingresos_abonos,
                    'total' => $total_ingresos
                ],
                'egresos' => [
                    'total' => $egresos['total_egresos'],
                    'detalle' => $egresos_detalle
                ],
                'resumen' => [
                    'ganancia_neta' => $ganancia_neta,
                    'margen_porcentaje' => $margen
                ]
            ];
            
        } catch (PDOException $e) {
            registrar_error("Error en reporte de ganancias: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Reporte comparativo de periodos
     * 
     * @param array $periodo1 ['inicio' => 'YYYY-MM-DD', 'fin' => 'YYYY-MM-DD']
     * @param array $periodo2 ['inicio' => 'YYYY-MM-DD', 'fin' => 'YYYY-MM-DD']
     * @return array Comparación entre dos periodos
     */
    public static function reporteComparativoPeriodos($periodo1, $periodo2) {
        try {
            // Función auxiliar para obtener métricas de un periodo
            $obtenerMetricas = function($fecha_inicio, $fecha_fin) {
                $ventas = db_query_one(
                    "SELECT 
                        COUNT(*) as total_ventas,
                        COALESCE(SUM(total), 0) as monto_ventas,
                        COALESCE(AVG(total), 0) as ticket_promedio
                     FROM ventas
                     WHERE fecha BETWEEN ? AND ?
                       AND estado != 'anulada'",
                    [$fecha_inicio, $fecha_fin]
                );
                
                $clientes = db_query_one(
                    "SELECT COUNT(DISTINCT cliente_id) as clientes_unicos
                     FROM ventas
                     WHERE fecha BETWEEN ? AND ?
                       AND estado != 'anulada'
                       AND cliente_id IS NOT NULL",
                    [$fecha_inicio, $fecha_fin]
                );
                
                $trabajos = db_query_one(
                    "SELECT 
                        COUNT(*) as trabajos_completados,
                        COALESCE(SUM(precio_total), 0) as ingresos_taller
                     FROM trabajos_taller
                     WHERE DATE(fecha_entrega_real) BETWEEN ? AND ?
                       AND estado = 'entregado'",
                    [$fecha_inicio, $fecha_fin]
                );
                
                return array_merge($ventas, $clientes, $trabajos);
            };
            
            $metricas_p1 = $obtenerMetricas($periodo1['inicio'], $periodo1['fin']);
            $metricas_p2 = $obtenerMetricas($periodo2['inicio'], $periodo2['fin']);
            
            // Calcular variaciones
            $calcularVariacion = function($actual, $anterior) {
                if ($anterior == 0) return $actual > 0 ? 100 : 0;
                return (($actual - $anterior) / $anterior) * 100;
            };
            
            $variaciones = [
                'ventas' => $calcularVariacion($metricas_p2['total_ventas'], $metricas_p1['total_ventas']),
                'monto_ventas' => $calcularVariacion($metricas_p2['monto_ventas'], $metricas_p1['monto_ventas']),
                'ticket_promedio' => $calcularVariacion($metricas_p2['ticket_promedio'], $metricas_p1['ticket_promedio']),
                'clientes_unicos' => $calcularVariacion($metricas_p2['clientes_unicos'], $metricas_p1['clientes_unicos']),
                'trabajos' => $calcularVariacion($metricas_p2['trabajos_completados'], $metricas_p1['trabajos_completados']),
                'ingresos_taller' => $calcularVariacion($metricas_p2['ingresos_taller'], $metricas_p1['ingresos_taller'])
            ];
            
            return [
                'periodo1' => [
                    'fechas' => $periodo1,
                    'metricas' => $metricas_p1
                ],
                'periodo2' => [
                    'fechas' => $periodo2,
                    'metricas' => $metricas_p2
                ],
                'variaciones' => $variaciones
            ];
            
        } catch (PDOException $e) {
            registrar_error("Error en reporte comparativo: " . $e->getMessage());
            return [];
        }
    }
}