<?php
/**
 * Modelo MovimientoInventario
 * 
 * Gestión y consultas de movimientos de inventario:
 * - Consultas detalladas de historial de movimientos
 * - Análisis por tipo de movimiento
 * - Reportes de movimientos por producto/sucursal/período
 * - Auditoría completa de cambios en inventario
 * 
 * NOTA: Los movimientos se registran automáticamente desde inventario.php,
 * venta.php y transferencia_inventario.php. Este modelo es para consultas.
 * 
 * @author Sistema Joyería Torre Fuerte
 * @version 1.0
 * @date 2026-01-23
 */

class MovimientoInventario {
    
    // Tipos de movimiento
    const TIPO_INGRESO = 'ingreso';
    const TIPO_SALIDA = 'salida';
    const TIPO_AJUSTE = 'ajuste';
    const TIPO_TRANSFERENCIA = 'transferencia';
    const TIPO_VENTA = 'venta';
    
    // Tipos de referencia
    const REF_VENTA = 'venta';
    const REF_COMPRA = 'compra';
    const REF_TRANSFERENCIA = 'transferencia';
    const REF_AJUSTE = 'ajuste_manual';
    
    /**
     * Obtiene un movimiento por ID
     * 
     * @param int $id ID del movimiento
     * @return array|false Datos del movimiento
     */
    public static function obtenerPorId($id) {
        try {
            $sql = "SELECT 
                        m.*,
                        p.nombre as producto_nombre,
                        p.codigo as producto_codigo,
                        s.nombre as sucursal_nombre,
                        u.nombre as usuario_nombre
                    FROM movimientos_inventario m
                    INNER JOIN productos p ON m.producto_id = p.id
                    INNER JOIN sucursales s ON m.sucursal_id = s.id
                    INNER JOIN usuarios u ON m.usuario_id = u.id
                    WHERE m.id = ?";
            
            return db_query_one($sql, [$id]);
            
        } catch (Exception $e) {
            registrar_error("Error al obtener movimiento: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Lista movimientos con filtros avanzados
     * 
     * @param array $filtros Filtros opcionales
     * @return array Lista de movimientos
     */
    public static function listar($filtros = []) {
        try {
            $where = [];
            $params = [];
            
            // Filtro por producto
            if (isset($filtros['producto_id']) && $filtros['producto_id'] > 0) {
                $where[] = "m.producto_id = ?";
                $params[] = $filtros['producto_id'];
            }
            
            // Filtro por sucursal
            if (isset($filtros['sucursal_id']) && $filtros['sucursal_id'] > 0) {
                $where[] = "m.sucursal_id = ?";
                $params[] = $filtros['sucursal_id'];
            }
            
            // Filtro por tipo de movimiento
            if (isset($filtros['tipo_movimiento']) && !empty($filtros['tipo_movimiento'])) {
                $where[] = "m.tipo_movimiento = ?";
                $params[] = $filtros['tipo_movimiento'];
            }
            
            // Filtro por tipo de referencia
            if (isset($filtros['referencia_tipo']) && !empty($filtros['referencia_tipo'])) {
                $where[] = "m.referencia_tipo = ?";
                $params[] = $filtros['referencia_tipo'];
            }
            
            // Filtro por referencia específica
            if (isset($filtros['referencia_id']) && $filtros['referencia_id'] > 0) {
                $where[] = "m.referencia_id = ?";
                $params[] = $filtros['referencia_id'];
            }
            
            // Filtro por usuario
            if (isset($filtros['usuario_id']) && $filtros['usuario_id'] > 0) {
                $where[] = "m.usuario_id = ?";
                $params[] = $filtros['usuario_id'];
            }
            
            // Filtro por rango de fechas
            if (isset($filtros['fecha_desde']) && !empty($filtros['fecha_desde'])) {
                $where[] = "DATE(m.fecha_hora) >= ?";
                $params[] = $filtros['fecha_desde'];
            }
            
            if (isset($filtros['fecha_hasta']) && !empty($filtros['fecha_hasta'])) {
                $where[] = "DATE(m.fecha_hora) <= ?";
                $params[] = $filtros['fecha_hasta'];
            }
            
            // Búsqueda por producto
            if (isset($filtros['buscar_producto']) && !empty($filtros['buscar_producto'])) {
                $where[] = "(p.nombre LIKE ? OR p.codigo LIKE ?)";
                $termino = "%{$filtros['buscar_producto']}%";
                $params[] = $termino;
                $params[] = $termino;
            }
            
            // Límite de resultados
            $limit = isset($filtros['limit']) ? intval($filtros['limit']) : 100;
            
            $where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            
            $sql = "SELECT 
                        m.id,
                        m.tipo_movimiento,
                        m.cantidad,
                        m.cantidad_anterior,
                        m.cantidad_nueva,
                        m.motivo,
                        m.referencia_tipo,
                        m.referencia_id,
                        m.fecha_hora,
                        p.nombre as producto_nombre,
                        p.codigo as producto_codigo,
                        s.nombre as sucursal_nombre,
                        u.nombre as usuario_nombre
                    FROM movimientos_inventario m
                    INNER JOIN productos p ON m.producto_id = p.id
                    INNER JOIN sucursales s ON m.sucursal_id = s.id
                    INNER JOIN usuarios u ON m.usuario_id = u.id
                    $where_sql
                    ORDER BY m.fecha_hora DESC
                    LIMIT ?";
            
            $params[] = $limit;
            
            return db_query($sql, $params);
            
        } catch (Exception $e) {
            registrar_error("Error al listar movimientos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene el historial completo de un producto
     * 
     * @param int $producto_id ID del producto
     * @param int $sucursal_id ID de sucursal (opcional)
     * @return array Historial de movimientos
     */
    public static function obtenerHistorialProducto($producto_id, $sucursal_id = null) {
        $filtros = ['producto_id' => $producto_id];
        
        if ($sucursal_id) {
            $filtros['sucursal_id'] = $sucursal_id;
        }
        
        return self::listar($filtros);
    }
    
    /**
     * Obtiene movimientos por tipo
     * 
     * @param string $tipo_movimiento Tipo de movimiento
     * @param array $filtros Filtros adicionales
     * @return array Lista de movimientos
     */
    public static function obtenerPorTipo($tipo_movimiento, $filtros = []) {
        $filtros['tipo_movimiento'] = $tipo_movimiento;
        return self::listar($filtros);
    }
    
    /**
     * Obtiene movimientos de una sucursal
     * 
     * @param int $sucursal_id ID de la sucursal
     * @param string $fecha_desde Fecha desde (opcional)
     * @param string $fecha_hasta Fecha hasta (opcional)
     * @return array Lista de movimientos
     */
    public static function obtenerPorSucursal($sucursal_id, $fecha_desde = null, $fecha_hasta = null) {
        $filtros = ['sucursal_id' => $sucursal_id];
        
        if ($fecha_desde) {
            $filtros['fecha_desde'] = $fecha_desde;
        }
        
        if ($fecha_hasta) {
            $filtros['fecha_hasta'] = $fecha_hasta;
        }
        
        return self::listar($filtros);
    }
    
    /**
     * Obtiene movimientos relacionados con una referencia
     * 
     * @param string $referencia_tipo Tipo de referencia
     * @param int $referencia_id ID de la referencia
     * @return array Lista de movimientos
     */
    public static function obtenerPorReferencia($referencia_tipo, $referencia_id) {
        return self::listar([
            'referencia_tipo' => $referencia_tipo,
            'referencia_id' => $referencia_id
        ]);
    }
    
    /**
     * Obtiene estadísticas de movimientos
     * 
     * @param array $filtros Filtros para las estadísticas
     * @return array Estadísticas
     */
    public static function obtenerEstadisticas($filtros = []) {
        try {
            $where = [];
            $params = [];
            
            if (isset($filtros['sucursal_id']) && $filtros['sucursal_id'] > 0) {
                $where[] = "sucursal_id = ?";
                $params[] = $filtros['sucursal_id'];
            }
            
            if (isset($filtros['producto_id']) && $filtros['producto_id'] > 0) {
                $where[] = "producto_id = ?";
                $params[] = $filtros['producto_id'];
            }
            
            if (isset($filtros['fecha_desde']) && !empty($filtros['fecha_desde'])) {
                $where[] = "DATE(fecha_hora) >= ?";
                $params[] = $filtros['fecha_desde'];
            }
            
            if (isset($filtros['fecha_hasta']) && !empty($filtros['fecha_hasta'])) {
                $where[] = "DATE(fecha_hora) <= ?";
                $params[] = $filtros['fecha_hasta'];
            }
            
            $where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            
            $sql = "SELECT 
                        COUNT(*) as total_movimientos,
                        COUNT(CASE WHEN tipo_movimiento = 'ingreso' THEN 1 END) as ingresos,
                        COUNT(CASE WHEN tipo_movimiento = 'salida' THEN 1 END) as salidas,
                        COUNT(CASE WHEN tipo_movimiento = 'ajuste' THEN 1 END) as ajustes,
                        COUNT(CASE WHEN tipo_movimiento = 'transferencia' THEN 1 END) as transferencias,
                        COUNT(CASE WHEN tipo_movimiento = 'venta' THEN 1 END) as ventas,
                        SUM(CASE WHEN tipo_movimiento = 'ingreso' THEN cantidad ELSE 0 END) as total_ingresos,
                        SUM(CASE WHEN tipo_movimiento = 'salida' THEN cantidad ELSE 0 END) as total_salidas,
                        SUM(CASE WHEN tipo_movimiento = 'venta' THEN cantidad ELSE 0 END) as total_vendidos
                    FROM movimientos_inventario
                    $where_sql";
            
            return db_query_one($sql, $params);
            
        } catch (Exception $e) {
            registrar_error("Error al obtener estadísticas de movimientos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene resumen de movimientos por producto
     * 
     * @param array $filtros Filtros opcionales
     * @return array Resumen por producto
     */
    public static function obtenerResumenPorProducto($filtros = []) {
        try {
            $where = [];
            $params = [];
            
            if (isset($filtros['sucursal_id']) && $filtros['sucursal_id'] > 0) {
                $where[] = "m.sucursal_id = ?";
                $params[] = $filtros['sucursal_id'];
            }
            
            if (isset($filtros['fecha_desde']) && !empty($filtros['fecha_desde'])) {
                $where[] = "DATE(m.fecha_hora) >= ?";
                $params[] = $filtros['fecha_desde'];
            }
            
            if (isset($filtros['fecha_hasta']) && !empty($filtros['fecha_hasta'])) {
                $where[] = "DATE(m.fecha_hora) <= ?";
                $params[] = $filtros['fecha_hasta'];
            }
            
            $where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            
            $sql = "SELECT 
                        p.id,
                        p.nombre,
                        p.codigo,
                        COUNT(*) as total_movimientos,
                        SUM(CASE WHEN m.tipo_movimiento = 'ingreso' THEN m.cantidad ELSE 0 END) as total_ingresos,
                        SUM(CASE WHEN m.tipo_movimiento = 'salida' THEN m.cantidad ELSE 0 END) as total_salidas,
                        SUM(CASE WHEN m.tipo_movimiento = 'venta' THEN m.cantidad ELSE 0 END) as total_ventas
                    FROM movimientos_inventario m
                    INNER JOIN productos p ON m.producto_id = p.id
                    $where_sql
                    GROUP BY p.id, p.nombre, p.codigo
                    ORDER BY total_movimientos DESC
                    LIMIT 50";
            
            return db_query($sql, $params);
            
        } catch (Exception $e) {
            registrar_error("Error al obtener resumen por producto: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene movimientos recientes
     * 
     * @param int $limit Cantidad de movimientos
     * @param int $sucursal_id Sucursal específica (opcional)
     * @return array Últimos movimientos
     */
    public static function obtenerRecientes($limit = 20, $sucursal_id = null) {
        $filtros = ['limit' => $limit];
        
        if ($sucursal_id) {
            $filtros['sucursal_id'] = $sucursal_id;
        }
        
        return self::listar($filtros);
    }
    
    /**
     * Obtiene movimientos de un usuario
     * 
     * @param int $usuario_id ID del usuario
     * @param array $filtros Filtros adicionales
     * @return array Movimientos del usuario
     */
    public static function obtenerPorUsuario($usuario_id, $filtros = []) {
        $filtros['usuario_id'] = $usuario_id;
        return self::listar($filtros);
    }
    
    /**
     * Analiza diferencias en inventario (discrepancias)
     * 
     * @param int $sucursal_id ID de la sucursal
     * @param string $fecha_desde Fecha desde
     * @param string $fecha_hasta Fecha hasta
     * @return array Productos con discrepancias
     */
    public static function analizarDiscrepancias($sucursal_id, $fecha_desde, $fecha_hasta) {
        try {
            $sql = "SELECT 
                        p.id,
                        p.nombre,
                        p.codigo,
                        m.cantidad_anterior as stock_inicial,
                        m.cantidad_nueva as stock_final,
                        (m.cantidad_nueva - m.cantidad_anterior) as diferencia,
                        COUNT(*) as num_movimientos,
                        GROUP_CONCAT(m.tipo_movimiento ORDER BY m.fecha_hora) as tipos_movimiento
                    FROM movimientos_inventario m
                    INNER JOIN productos p ON m.producto_id = p.id
                    WHERE m.sucursal_id = ?
                      AND DATE(m.fecha_hora) BETWEEN ? AND ?
                      AND m.tipo_movimiento = 'ajuste'
                    GROUP BY p.id, p.nombre, p.codigo, m.cantidad_anterior, m.cantidad_nueva
                    ORDER BY ABS(m.cantidad_nueva - m.cantidad_anterior) DESC";
            
            return db_query($sql, [$sucursal_id, $fecha_desde, $fecha_hasta]);
            
        } catch (Exception $e) {
            registrar_error("Error al analizar discrepancias: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene los tipos de movimiento válidos
     * 
     * @return array Tipos válidos
     */
    public static function getTiposValidos() {
        return [
            self::TIPO_INGRESO,
            self::TIPO_SALIDA,
            self::TIPO_AJUSTE,
            self::TIPO_TRANSFERENCIA,
            self::TIPO_VENTA
        ];
    }
    
    /**
     * Obtiene los tipos de referencia válidos
     * 
     * @return array Tipos de referencia válidos
     */
    public static function getTiposReferenciaValidos() {
        return [
            self::REF_VENTA,
            self::REF_COMPRA,
            self::REF_TRANSFERENCIA,
            self::REF_AJUSTE
        ];
    }
}
