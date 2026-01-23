<?php
/**
 * Modelo AbonoCredito
 * 
 * Gestión y consultas de abonos a créditos:
 * - Consultas detalladas de historial de abonos
 * - Análisis por cliente, período, forma de pago
 * - Reportes de recuperación de cartera
 * - Estadísticas de cobranza
 * - Auditoría de abonos
 * 
 * NOTA: Los abonos se registran desde credito.php con registrarAbono()
 * Este modelo es para consultas y reportes avanzados.
 * 
 * @author Sistema Joyería Torre Fuerte
 * @version 1.0
 * @date 2026-01-23
 */

class AbonoCredito {
    
    // Formas de pago
    const FORMA_EFECTIVO = 'efectivo';
    const FORMA_TARJETA_DEBITO = 'tarjeta_debito';
    const FORMA_TARJETA_CREDITO = 'tarjeta_credito';
    const FORMA_TRANSFERENCIA = 'transferencia';
    const FORMA_CHEQUE = 'cheque';
    
    /**
     * Obtiene un abono por ID
     * 
     * @param int $id ID del abono
     * @return array|false Datos del abono
     */
    public static function obtenerPorId($id) {
        try {
            $sql = "SELECT 
                        a.*,
                        cr.numero_credito,
                        cr.cliente_id,
                        cl.nombre as cliente_nombre,
                        u.nombre as usuario_nombre,
                        ca.nombre as caja_nombre
                    FROM abonos_creditos a
                    INNER JOIN creditos_clientes cr ON a.credito_id = cr.id
                    INNER JOIN clientes cl ON cr.cliente_id = cl.id
                    INNER JOIN usuarios u ON a.usuario_id = u.id
                    LEFT JOIN cajas ca ON a.caja_id = ca.id
                    WHERE a.id = ?";
            
            return db_query_one($sql, [$id]);
            
        } catch (Exception $e) {
            registrar_error("Error al obtener abono: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Lista abonos con filtros avanzados
     * 
     * @param array $filtros Filtros opcionales
     * @return array Lista de abonos
     */
    public static function listar($filtros = []) {
        try {
            $where = [];
            $params = [];
            
            // Filtro por crédito
            if (isset($filtros['credito_id']) && $filtros['credito_id'] > 0) {
                $where[] = "a.credito_id = ?";
                $params[] = $filtros['credito_id'];
            }
            
            // Filtro por cliente
            if (isset($filtros['cliente_id']) && $filtros['cliente_id'] > 0) {
                $where[] = "cr.cliente_id = ?";
                $params[] = $filtros['cliente_id'];
            }
            
            // Filtro por forma de pago
            if (isset($filtros['forma_pago']) && !empty($filtros['forma_pago'])) {
                $where[] = "a.forma_pago = ?";
                $params[] = $filtros['forma_pago'];
            }
            
            // Filtro por usuario
            if (isset($filtros['usuario_id']) && $filtros['usuario_id'] > 0) {
                $where[] = "a.usuario_id = ?";
                $params[] = $filtros['usuario_id'];
            }
            
            // Filtro por caja
            if (isset($filtros['caja_id']) && $filtros['caja_id'] > 0) {
                $where[] = "a.caja_id = ?";
                $params[] = $filtros['caja_id'];
            }
            
            // Filtro por rango de fechas
            if (isset($filtros['fecha_desde']) && !empty($filtros['fecha_desde'])) {
                $where[] = "a.fecha_abono >= ?";
                $params[] = $filtros['fecha_desde'];
            }
            
            if (isset($filtros['fecha_hasta']) && !empty($filtros['fecha_hasta'])) {
                $where[] = "a.fecha_abono <= ?";
                $params[] = $filtros['fecha_hasta'];
            }
            
            // Filtro por rango de montos
            if (isset($filtros['monto_min']) && $filtros['monto_min'] > 0) {
                $where[] = "a.monto >= ?";
                $params[] = $filtros['monto_min'];
            }
            
            if (isset($filtros['monto_max']) && $filtros['monto_max'] > 0) {
                $where[] = "a.monto <= ?";
                $params[] = $filtros['monto_max'];
            }
            
            // Búsqueda por cliente
            if (isset($filtros['buscar_cliente']) && !empty($filtros['buscar_cliente'])) {
                $where[] = "cl.nombre LIKE ?";
                $params[] = "%{$filtros['buscar_cliente']}%";
            }
            
            // Límite de resultados
            $limit = isset($filtros['limit']) ? intval($filtros['limit']) : 100;
            
            $where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            
            $sql = "SELECT 
                        a.id,
                        a.credito_id,
                        a.monto,
                        a.forma_pago,
                        a.fecha_abono,
                        a.saldo_anterior,
                        a.saldo_nuevo,
                        a.observaciones,
                        a.fecha_hora,
                        cr.numero_credito,
                        cl.nombre as cliente_nombre,
                        u.nombre as usuario_nombre,
                        ca.nombre as caja_nombre
                    FROM abonos_creditos a
                    INNER JOIN creditos_clientes cr ON a.credito_id = cr.id
                    INNER JOIN clientes cl ON cr.cliente_id = cl.id
                    INNER JOIN usuarios u ON a.usuario_id = u.id
                    LEFT JOIN cajas ca ON a.caja_id = ca.id
                    $where_sql
                    ORDER BY a.fecha_abono DESC, a.fecha_hora DESC
                    LIMIT ?";
            
            $params[] = $limit;
            
            return db_query($sql, $params);
            
        } catch (Exception $e) {
            registrar_error("Error al listar abonos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene abonos de un crédito específico
     * 
     * @param int $credito_id ID del crédito
     * @return array Lista de abonos
     */
    public static function obtenerPorCredito($credito_id) {
        return self::listar(['credito_id' => $credito_id, 'limit' => 1000]);
    }
    
    /**
     * Obtiene abonos de un cliente
     * 
     * @param int $cliente_id ID del cliente
     * @param array $filtros Filtros adicionales
     * @return array Lista de abonos
     */
    public static function obtenerPorCliente($cliente_id, $filtros = []) {
        $filtros['cliente_id'] = $cliente_id;
        return self::listar($filtros);
    }
    
    /**
     * Obtiene abonos por forma de pago
     * 
     * @param string $forma_pago Forma de pago
     * @param array $filtros Filtros adicionales
     * @return array Lista de abonos
     */
    public static function obtenerPorFormaPago($forma_pago, $filtros = []) {
        $filtros['forma_pago'] = $forma_pago;
        return self::listar($filtros);
    }
    
    /**
     * Obtiene estadísticas de abonos
     * 
     * @param array $filtros Filtros para las estadísticas
     * @return array Estadísticas
     */
    public static function obtenerEstadisticas($filtros = []) {
        try {
            $where = [];
            $params = [];
            
            if (isset($filtros['cliente_id']) && $filtros['cliente_id'] > 0) {
                $where[] = "cr.cliente_id = ?";
                $params[] = $filtros['cliente_id'];
            }
            
            if (isset($filtros['fecha_desde']) && !empty($filtros['fecha_desde'])) {
                $where[] = "a.fecha_abono >= ?";
                $params[] = $filtros['fecha_desde'];
            }
            
            if (isset($filtros['fecha_hasta']) && !empty($filtros['fecha_hasta'])) {
                $where[] = "a.fecha_abono <= ?";
                $params[] = $filtros['fecha_hasta'];
            }
            
            $where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            
            $sql = "SELECT 
                        COUNT(*) as total_abonos,
                        COUNT(DISTINCT a.credito_id) as creditos_con_abonos,
                        COUNT(DISTINCT cr.cliente_id) as clientes_con_abonos,
                        SUM(a.monto) as monto_total,
                        AVG(a.monto) as monto_promedio,
                        MIN(a.monto) as monto_minimo,
                        MAX(a.monto) as monto_maximo,
                        COUNT(CASE WHEN a.forma_pago = 'efectivo' THEN 1 END) as abonos_efectivo,
                        COUNT(CASE WHEN a.forma_pago IN ('tarjeta_debito', 'tarjeta_credito') THEN 1 END) as abonos_tarjeta,
                        SUM(CASE WHEN a.forma_pago = 'efectivo' THEN a.monto ELSE 0 END) as total_efectivo,
                        SUM(CASE WHEN a.forma_pago IN ('tarjeta_debito', 'tarjeta_credito') THEN a.monto ELSE 0 END) as total_tarjeta
                    FROM abonos_creditos a
                    INNER JOIN creditos_clientes cr ON a.credito_id = cr.id
                    $where_sql";
            
            return db_query_one($sql, $params);
            
        } catch (Exception $e) {
            registrar_error("Error al obtener estadísticas de abonos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene resumen de abonos por forma de pago
     * 
     * @param array $filtros Filtros opcionales
     * @return array Resumen por forma de pago
     */
    public static function obtenerResumenPorFormaPago($filtros = []) {
        try {
            $where = [];
            $params = [];
            
            if (isset($filtros['fecha_desde']) && !empty($filtros['fecha_desde'])) {
                $where[] = "a.fecha_abono >= ?";
                $params[] = $filtros['fecha_desde'];
            }
            
            if (isset($filtros['fecha_hasta']) && !empty($filtros['fecha_hasta'])) {
                $where[] = "a.fecha_abono <= ?";
                $params[] = $filtros['fecha_hasta'];
            }
            
            $where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            
            $sql = "SELECT 
                        a.forma_pago,
                        COUNT(*) as cantidad,
                        SUM(a.monto) as total,
                        AVG(a.monto) as promedio,
                        MIN(a.monto) as minimo,
                        MAX(a.monto) as maximo
                    FROM abonos_creditos a
                    $where_sql
                    GROUP BY a.forma_pago
                    ORDER BY total DESC";
            
            return db_query($sql, $params);
            
        } catch (Exception $e) {
            registrar_error("Error al obtener resumen por forma de pago: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene abonos diarios (para análisis de cobranza)
     * 
     * @param string $fecha_desde Fecha desde
     * @param string $fecha_hasta Fecha hasta
     * @return array Abonos por día
     */
    public static function obtenerAbonosDiarios($fecha_desde, $fecha_hasta) {
        try {
            $sql = "SELECT 
                        a.fecha_abono,
                        COUNT(*) as cantidad_abonos,
                        COUNT(DISTINCT a.credito_id) as creditos_abonados,
                        COUNT(DISTINCT cr.cliente_id) as clientes_abonaron,
                        SUM(a.monto) as total_cobrado
                    FROM abonos_creditos a
                    INNER JOIN creditos_clientes cr ON a.credito_id = cr.id
                    WHERE a.fecha_abono BETWEEN ? AND ?
                    GROUP BY a.fecha_abono
                    ORDER BY a.fecha_abono DESC";
            
            return db_query($sql, [$fecha_desde, $fecha_hasta]);
            
        } catch (Exception $e) {
            registrar_error("Error al obtener abonos diarios: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene clientes con más abonos (mejores pagadores)
     * 
     * @param string $fecha_desde Fecha desde (opcional)
     * @param string $fecha_hasta Fecha hasta (opcional)
     * @param int $limit Cantidad de resultados
     * @return array Top clientes
     */
    public static function obtenerMejoresPagadores($fecha_desde = null, $fecha_hasta = null, $limit = 10) {
        try {
            $where = [];
            $params = [];
            
            if ($fecha_desde) {
                $where[] = "a.fecha_abono >= ?";
                $params[] = $fecha_desde;
            }
            
            if ($fecha_hasta) {
                $where[] = "a.fecha_abono <= ?";
                $params[] = $fecha_hasta;
            }
            
            $where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            
            $sql = "SELECT 
                        cl.id,
                        cl.nombre,
                        cl.telefono,
                        COUNT(*) as cantidad_abonos,
                        SUM(a.monto) as total_abonado,
                        AVG(a.monto) as promedio_abono,
                        COUNT(DISTINCT a.credito_id) as creditos_abonados
                    FROM abonos_creditos a
                    INNER JOIN creditos_clientes cr ON a.credito_id = cr.id
                    INNER JOIN clientes cl ON cr.cliente_id = cl.id
                    $where_sql
                    GROUP BY cl.id, cl.nombre, cl.telefono
                    ORDER BY cantidad_abonos DESC, total_abonado DESC
                    LIMIT ?";
            
            $params[] = $limit;
            
            return db_query($sql, $params);
            
        } catch (Exception $e) {
            registrar_error("Error al obtener mejores pagadores: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene abonos recientes
     * 
     * @param int $limit Cantidad de abonos
     * @return array Últimos abonos
     */
    public static function obtenerRecientes($limit = 20) {
        return self::listar(['limit' => $limit]);
    }
    
    /**
     * Obtiene abonos de un usuario (cajero)
     * 
     * @param int $usuario_id ID del usuario
     * @param array $filtros Filtros adicionales
     * @return array Abonos del usuario
     */
    public static function obtenerPorUsuario($usuario_id, $filtros = []) {
        $filtros['usuario_id'] = $usuario_id;
        return self::listar($filtros);
    }
    
    /**
     * Analiza recuperación de cartera por período
     * 
     * @param string $fecha_desde Fecha desde
     * @param string $fecha_hasta Fecha hasta
     * @return array Análisis de recuperación
     */
    public static function analizarRecuperacion($fecha_desde, $fecha_hasta) {
        try {
            $sql = "SELECT 
                        DATE_FORMAT(a.fecha_abono, '%Y-%m') as periodo,
                        COUNT(*) as cantidad_abonos,
                        COUNT(DISTINCT a.credito_id) as creditos_abonados,
                        COUNT(DISTINCT cr.cliente_id) as clientes_activos,
                        SUM(a.monto) as monto_recuperado,
                        AVG(a.monto) as promedio_abono
                    FROM abonos_creditos a
                    INNER JOIN creditos_clientes cr ON a.credito_id = cr.id
                    WHERE a.fecha_abono BETWEEN ? AND ?
                    GROUP BY DATE_FORMAT(a.fecha_abono, '%Y-%m')
                    ORDER BY periodo DESC";
            
            return db_query($sql, [$fecha_desde, $fecha_hasta]);
            
        } catch (Exception $e) {
            registrar_error("Error al analizar recuperación: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene las formas de pago válidas
     * 
     * @return array Formas de pago válidas
     */
    public static function getFormasPagoValidas() {
        return [
            self::FORMA_EFECTIVO,
            self::FORMA_TARJETA_DEBITO,
            self::FORMA_TARJETA_CREDITO,
            self::FORMA_TRANSFERENCIA,
            self::FORMA_CHEQUE
        ];
    }
}
