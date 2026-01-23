<?php
/**
 * Modelo MovimientoCaja
 * 
 * Gestión y consultas de movimientos de caja:
 * - Consultas detalladas de historial de caja
 * - Análisis por tipo de movimiento y categoría
 * - Reportes de ingresos y egresos
 * - Auditoría completa de movimientos
 * - Análisis financiero
 * 
 * NOTA: Los movimientos se registran automáticamente desde caja.php
 * Este modelo es para consultas y reportes avanzados.
 * 
 * @author Sistema Joyería Torre Fuerte
 * @version 1.0
 * @date 2026-01-23
 */

class MovimientoCaja {
    
    // Tipos de movimiento - Ingresos
    const TIPO_VENTA = 'venta';
    const TIPO_INGRESO_REPARACION = 'ingreso_reparacion';
    const TIPO_ANTICIPO_TRABAJO = 'anticipo_trabajo';
    const TIPO_ABONO_CREDITO = 'abono_credito';
    const TIPO_ANTICIPO_APARTADO = 'anticipo_apartado';
    const TIPO_OTRO_INGRESO = 'otro_ingreso';
    
    // Tipos de movimiento - Egresos
    const TIPO_GASTO = 'gasto';
    const TIPO_PAGO_PROVEEDOR = 'pago_proveedor';
    const TIPO_COMPRA_MATERIAL = 'compra_material';
    const TIPO_ALQUILER = 'alquiler';
    const TIPO_SALARIO = 'salario';
    const TIPO_OTRO_EGRESO = 'otro_egreso';
    
    // Categorías
    const CATEGORIA_INGRESO = 'ingreso';
    const CATEGORIA_EGRESO = 'egreso';
    
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
                        c.nombre as caja_nombre,
                        c.sucursal_id,
                        s.nombre as sucursal_nombre,
                        u.nombre as usuario_nombre
                    FROM movimientos_caja m
                    INNER JOIN cajas c ON m.caja_id = c.id
                    INNER JOIN sucursales s ON c.sucursal_id = s.id
                    INNER JOIN usuarios u ON m.usuario_id = u.id
                    WHERE m.id = ?";
            
            return db_query_one($sql, [$id]);
            
        } catch (Exception $e) {
            registrar_error("Error al obtener movimiento de caja: " . $e->getMessage());
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
            
            // Filtro por caja
            if (isset($filtros['caja_id']) && $filtros['caja_id'] > 0) {
                $where[] = "m.caja_id = ?";
                $params[] = $filtros['caja_id'];
            }
            
            // Filtro por sucursal
            if (isset($filtros['sucursal_id']) && $filtros['sucursal_id'] > 0) {
                $where[] = "c.sucursal_id = ?";
                $params[] = $filtros['sucursal_id'];
            }
            
            // Filtro por tipo de movimiento
            if (isset($filtros['tipo_movimiento']) && !empty($filtros['tipo_movimiento'])) {
                $where[] = "m.tipo_movimiento = ?";
                $params[] = $filtros['tipo_movimiento'];
            }
            
            // Filtro por categoría (ingreso/egreso)
            if (isset($filtros['categoria']) && !empty($filtros['categoria'])) {
                $where[] = "m.categoria = ?";
                $params[] = $filtros['categoria'];
            }
            
            // Filtro por usuario
            if (isset($filtros['usuario_id']) && $filtros['usuario_id'] > 0) {
                $where[] = "m.usuario_id = ?";
                $params[] = $filtros['usuario_id'];
            }
            
            // Filtro por referencia
            if (isset($filtros['referencia_tipo']) && !empty($filtros['referencia_tipo'])) {
                $where[] = "m.referencia_tipo = ?";
                $params[] = $filtros['referencia_tipo'];
            }
            
            if (isset($filtros['referencia_id']) && $filtros['referencia_id'] > 0) {
                $where[] = "m.referencia_id = ?";
                $params[] = $filtros['referencia_id'];
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
            
            // Filtro por rango de montos
            if (isset($filtros['monto_min']) && $filtros['monto_min'] > 0) {
                $where[] = "m.monto >= ?";
                $params[] = $filtros['monto_min'];
            }
            
            if (isset($filtros['monto_max']) && $filtros['monto_max'] > 0) {
                $where[] = "m.monto <= ?";
                $params[] = $filtros['monto_max'];
            }
            
            // Búsqueda en concepto
            if (isset($filtros['buscar']) && !empty($filtros['buscar'])) {
                $where[] = "m.concepto LIKE ?";
                $params[] = "%{$filtros['buscar']}%";
            }
            
            // Límite de resultados
            $limit = isset($filtros['limit']) ? intval($filtros['limit']) : 100;
            
            $where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            
            $sql = "SELECT 
                        m.id,
                        m.tipo_movimiento,
                        m.categoria,
                        m.concepto,
                        m.monto,
                        m.referencia_tipo,
                        m.referencia_id,
                        m.fecha_hora,
                        c.nombre as caja_nombre,
                        s.nombre as sucursal_nombre,
                        u.nombre as usuario_nombre
                    FROM movimientos_caja m
                    INNER JOIN cajas c ON m.caja_id = c.id
                    INNER JOIN sucursales s ON c.sucursal_id = s.id
                    INNER JOIN usuarios u ON m.usuario_id = u.id
                    $where_sql
                    ORDER BY m.fecha_hora DESC
                    LIMIT ?";
            
            $params[] = $limit;
            
            return db_query($sql, $params);
            
        } catch (Exception $e) {
            registrar_error("Error al listar movimientos de caja: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene movimientos de una caja
     * 
     * @param int $caja_id ID de la caja
     * @param array $filtros Filtros adicionales
     * @return array Lista de movimientos
     */
    public static function obtenerPorCaja($caja_id, $filtros = []) {
        $filtros['caja_id'] = $caja_id;
        return self::listar($filtros);
    }
    
    /**
     * Obtiene ingresos de una caja
     * 
     * @param int $caja_id ID de la caja
     * @param string $fecha_desde Fecha desde
     * @param string $fecha_hasta Fecha hasta
     * @return array Lista de ingresos
     */
    public static function obtenerIngresos($caja_id, $fecha_desde = null, $fecha_hasta = null) {
        $filtros = [
            'caja_id' => $caja_id,
            'categoria' => self::CATEGORIA_INGRESO
        ];
        
        if ($fecha_desde) $filtros['fecha_desde'] = $fecha_desde;
        if ($fecha_hasta) $filtros['fecha_hasta'] = $fecha_hasta;
        
        return self::listar($filtros);
    }
    
    /**
     * Obtiene egresos de una caja
     * 
     * @param int $caja_id ID de la caja
     * @param string $fecha_desde Fecha desde
     * @param string $fecha_hasta Fecha hasta
     * @return array Lista de egresos
     */
    public static function obtenerEgresos($caja_id, $fecha_desde = null, $fecha_hasta = null) {
        $filtros = [
            'caja_id' => $caja_id,
            'categoria' => self::CATEGORIA_EGRESO
        ];
        
        if ($fecha_desde) $filtros['fecha_desde'] = $fecha_desde;
        if ($fecha_hasta) $filtros['fecha_hasta'] = $fecha_hasta;
        
        return self::listar($filtros);
    }
    
    /**
     * Obtiene estadísticas de movimientos de caja
     * 
     * @param array $filtros Filtros para las estadísticas
     * @return array Estadísticas
     */
    public static function obtenerEstadisticas($filtros = []) {
        try {
            $where = [];
            $params = [];
            
            if (isset($filtros['caja_id']) && $filtros['caja_id'] > 0) {
                $where[] = "m.caja_id = ?";
                $params[] = $filtros['caja_id'];
            }
            
            if (isset($filtros['sucursal_id']) && $filtros['sucursal_id'] > 0) {
                $where[] = "c.sucursal_id = ?";
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
                        COUNT(*) as total_movimientos,
                        COUNT(CASE WHEN m.categoria = 'ingreso' THEN 1 END) as total_ingresos_count,
                        COUNT(CASE WHEN m.categoria = 'egreso' THEN 1 END) as total_egresos_count,
                        COALESCE(SUM(CASE WHEN m.categoria = 'ingreso' THEN m.monto ELSE 0 END), 0) as total_ingresos,
                        COALESCE(SUM(CASE WHEN m.categoria = 'egreso' THEN m.monto ELSE 0 END), 0) as total_egresos,
                        COALESCE(SUM(CASE WHEN m.categoria = 'ingreso' THEN m.monto ELSE -m.monto END), 0) as saldo_neto,
                        COALESCE(AVG(CASE WHEN m.categoria = 'ingreso' THEN m.monto END), 0) as promedio_ingreso,
                        COALESCE(AVG(CASE WHEN m.categoria = 'egreso' THEN m.monto END), 0) as promedio_egreso
                    FROM movimientos_caja m
                    INNER JOIN cajas c ON m.caja_id = c.id
                    $where_sql";
            
            return db_query_one($sql, $params);
            
        } catch (Exception $e) {
            registrar_error("Error al obtener estadísticas de caja: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene resumen por tipo de movimiento
     * 
     * @param array $filtros Filtros opcionales
     * @return array Resumen por tipo
     */
    public static function obtenerResumenPorTipo($filtros = []) {
        try {
            $where = [];
            $params = [];
            
            if (isset($filtros['caja_id']) && $filtros['caja_id'] > 0) {
                $where[] = "m.caja_id = ?";
                $params[] = $filtros['caja_id'];
            }
            
            if (isset($filtros['sucursal_id']) && $filtros['sucursal_id'] > 0) {
                $where[] = "c.sucursal_id = ?";
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
                        m.tipo_movimiento,
                        m.categoria,
                        COUNT(*) as cantidad,
                        SUM(m.monto) as total,
                        AVG(m.monto) as promedio,
                        MIN(m.monto) as minimo,
                        MAX(m.monto) as maximo
                    FROM movimientos_caja m
                    INNER JOIN cajas c ON m.caja_id = c.id
                    $where_sql
                    GROUP BY m.tipo_movimiento, m.categoria
                    ORDER BY total DESC";
            
            return db_query($sql, $params);
            
        } catch (Exception $e) {
            registrar_error("Error al obtener resumen por tipo: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene flujo de caja diario
     * 
     * @param int $caja_id ID de la caja
     * @param string $fecha_desde Fecha desde
     * @param string $fecha_hasta Fecha hasta
     * @return array Flujo diario
     */
    public static function obtenerFlujoDiario($caja_id, $fecha_desde, $fecha_hasta) {
        try {
            $sql = "SELECT 
                        DATE(m.fecha_hora) as fecha,
                        COALESCE(SUM(CASE WHEN m.categoria = 'ingreso' THEN m.monto ELSE 0 END), 0) as ingresos,
                        COALESCE(SUM(CASE WHEN m.categoria = 'egreso' THEN m.monto ELSE 0 END), 0) as egresos,
                        COALESCE(SUM(CASE WHEN m.categoria = 'ingreso' THEN m.monto ELSE -m.monto END), 0) as saldo_diario,
                        COUNT(*) as num_movimientos
                    FROM movimientos_caja m
                    WHERE m.caja_id = ?
                      AND DATE(m.fecha_hora) BETWEEN ? AND ?
                    GROUP BY DATE(m.fecha_hora)
                    ORDER BY fecha DESC";
            
            return db_query($sql, [$caja_id, $fecha_desde, $fecha_hasta]);
            
        } catch (Exception $e) {
            registrar_error("Error al obtener flujo diario: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene movimientos por referencia
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
     * Obtiene movimientos recientes
     * 
     * @param int $caja_id ID de la caja (opcional)
     * @param int $limit Cantidad de movimientos
     * @return array Últimos movimientos
     */
    public static function obtenerRecientes($caja_id = null, $limit = 20) {
        $filtros = ['limit' => $limit];
        
        if ($caja_id) {
            $filtros['caja_id'] = $caja_id;
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
     * Analiza gastos por categoría
     * 
     * @param int $sucursal_id ID de la sucursal
     * @param string $fecha_desde Fecha desde
     * @param string $fecha_hasta Fecha hasta
     * @return array Resumen de gastos
     */
    public static function analizarGastos($sucursal_id, $fecha_desde, $fecha_hasta) {
        try {
            $sql = "SELECT 
                        m.tipo_movimiento,
                        COUNT(*) as cantidad,
                        SUM(m.monto) as total,
                        AVG(m.monto) as promedio
                    FROM movimientos_caja m
                    INNER JOIN cajas c ON m.caja_id = c.id
                    WHERE c.sucursal_id = ?
                      AND m.categoria = 'egreso'
                      AND DATE(m.fecha_hora) BETWEEN ? AND ?
                    GROUP BY m.tipo_movimiento
                    ORDER BY total DESC";
            
            return db_query($sql, [$sucursal_id, $fecha_desde, $fecha_hasta]);
            
        } catch (Exception $e) {
            registrar_error("Error al analizar gastos: " . $e->getMessage());
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
            // Ingresos
            self::TIPO_VENTA,
            self::TIPO_INGRESO_REPARACION,
            self::TIPO_ANTICIPO_TRABAJO,
            self::TIPO_ABONO_CREDITO,
            self::TIPO_ANTICIPO_APARTADO,
            self::TIPO_OTRO_INGRESO,
            // Egresos
            self::TIPO_GASTO,
            self::TIPO_PAGO_PROVEEDOR,
            self::TIPO_COMPRA_MATERIAL,
            self::TIPO_ALQUILER,
            self::TIPO_SALARIO,
            self::TIPO_OTRO_EGRESO
        ];
    }
}
