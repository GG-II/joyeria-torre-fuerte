<?php
/**
 * Modelo Caja
 * 
 * Manejo completo del flujo de caja:
 * - Apertura y cierre de caja
 * - Registro de movimientos (ingresos y egresos)
 * - Cálculo de totales y diferencias
 * - Historial de cierres
 * 
 * @author Sistema Joyería Torre Fuerte
 * @version 1.0
 * @date 2026-01-22
 */

class Caja {
    
    // ========================================
    // APERTURA Y CIERRE DE CAJA
    // ========================================
    
    /**
     * Abrir una nueva caja
     * 
     * @param int $usuario_id ID del usuario que abre la caja
     * @param int $sucursal_id ID de la sucursal
     * @param float $monto_inicial Monto inicial en efectivo
     * @return int|false ID de la caja abierta o false si falla
     */
    public static function abrirCaja($usuario_id, $sucursal_id, $monto_inicial) {
        try {
            // Validar datos
            $errores = self::validarApertura([
                'usuario_id' => $usuario_id,
                'sucursal_id' => $sucursal_id,
                'monto_inicial' => $monto_inicial
            ]);
            
            if (!empty($errores)) {
                registrar_error("Error al validar apertura de caja: " . implode(', ', $errores));
                return false;
            }
            
            // Verificar que no haya una caja abierta para esta sucursal
$caja_abierta = db_query_one(
    "SELECT id, usuario_id FROM cajas 
     WHERE sucursal_id = ? AND estado = 'abierta'",
    [$sucursal_id]
);

if ($caja_abierta) {
    // Si es el mismo usuario, no puede abrir otra
    if ($caja_abierta['usuario_id'] == $usuario_id) {
        registrar_error("El usuario ya tiene una caja abierta (ID: {$caja_abierta['id']})");
        return false;
    }
    
    // Si es otro usuario, verificar permisos
    require_once __DIR__ . '/../includes/auth.php';
    
    if (!usuario_tiene_rol(['administrador', 'dueño'])) {
        registrar_error("Ya hay una caja abierta en esta sucursal por otro usuario");
        return false;
    }
    
    // Admin/dueño puede continuar
}

// Insertar nueva caja
$sql = "INSERT INTO cajas (
            usuario_id, sucursal_id, fecha_apertura, 
            monto_inicial, estado
        ) VALUES (?, ?, NOW(), ?, 'abierta')";
            
            $caja_id = db_execute($sql, [
                $usuario_id,
                $sucursal_id,
                $monto_inicial
            ]);
            
            if ($caja_id) {
                // Auditoría
                registrar_auditoria('INSERT', 'cajas', $caja_id, 
                    "Caja abierta - Sucursal: $sucursal_id - Monto inicial: " . formato_dinero($monto_inicial));
            }
            
            return $caja_id;
            
        } catch (PDOException $e) {
            registrar_error("Error al abrir caja: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cerrar una caja
     * 
     * @param int $caja_id ID de la caja a cerrar
     * @param float $monto_real Monto real contado en efectivo
     * @param string $observaciones Observaciones del cierre (opcional)
     * @return bool True si se cerró correctamente, false si falló
     */
    public static function cerrarCaja($caja_id, $monto_real, $observaciones = null) {
        try {
            // Obtener caja
            $caja = self::obtenerPorId($caja_id);
            
            if (!$caja) {
                registrar_error("Caja no encontrada (ID: $caja_id)");
                return false;
            }
            
            if ($caja['estado'] === 'cerrada') {
                registrar_error("La caja ya está cerrada");
                return false;
            }
            
            // Calcular totales
            $totales = self::calcularTotalesCaja($caja_id);
            $monto_esperado = $totales['total_final'];
            
            // Actualizar caja
            $sql = "UPDATE cajas SET 
                        fecha_cierre = NOW(),
                        monto_esperado = ?,
                        monto_real = ?,
                        observaciones_cierre = ?,
                        estado = 'cerrada'
                    WHERE id = ?";
            
            $resultado = db_execute($sql, [
                $monto_esperado,
                $monto_real,
                $observaciones,
                $caja_id
            ]);
            
            if ($resultado !== false) {
                $diferencia = $monto_real - $monto_esperado;
                
                registrar_auditoria('UPDATE', 'cajas', $caja_id, 
                    "Caja cerrada - Esperado: " . formato_dinero($monto_esperado) . 
                    " - Real: " . formato_dinero($monto_real) . 
                    " - Diferencia: " . formato_dinero($diferencia));
            }
            
            return $resultado !== false;
            
        } catch (PDOException $e) {
            registrar_error("Error al cerrar caja: " . $e->getMessage());
            return false;
        }
    }
    
    // ========================================
    // REGISTRO DE MOVIMIENTOS
    // ========================================
    
    /**
     * Registrar un movimiento en la caja
     * 
     * @param array $datos Array con los datos del movimiento:
     *   - caja_id: int (requerido)
     *   - tipo_movimiento: string (requerido) ver tipos válidos en BD
     *   - categoria: string (ingreso|egreso) (requerido)
     *   - concepto: string (requerido)
     *   - monto: decimal (requerido)
     *   - usuario_id: int (requerido)
     *   - referencia_tipo: string (opcional)
     *   - referencia_id: int (opcional)
     * @return int|false ID del movimiento o false si falla
     */
    public static function registrarMovimiento($datos) {
        try {
            // Validar datos
            $errores = self::validarMovimiento($datos);
            
            if (!empty($errores)) {
                registrar_error("Error al validar movimiento: " . implode(', ', $errores));
                return false;
            }
            
            // Verificar que la caja esté abierta
            $caja = db_query_one(
                "SELECT estado FROM cajas WHERE id = ?",
                [$datos['caja_id']]
            );
            
            if (!$caja || $caja['estado'] !== 'abierta') {
                registrar_error("La caja no está abierta");
                return false;
            }
            
            // Insertar movimiento
            $sql = "INSERT INTO movimientos_caja (
                        caja_id, tipo_movimiento, categoria, concepto,
                        monto, usuario_id, referencia_tipo, referencia_id
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $movimiento_id = db_execute($sql, [
                $datos['caja_id'],
                $datos['tipo_movimiento'],
                $datos['categoria'],
                $datos['concepto'],
                $datos['monto'],
                $datos['usuario_id'],
                $datos['referencia_tipo'] ?? null,
                $datos['referencia_id'] ?? null
            ]);
            
            if ($movimiento_id) {
                registrar_auditoria('INSERT', 'movimientos_caja', $movimiento_id,
                    "Movimiento registrado - Tipo: {$datos['tipo_movimiento']} - " .
                    "Categoría: {$datos['categoria']} - Monto: " . formato_dinero($datos['monto']));
            }
            
            return $movimiento_id;
            
        } catch (PDOException $e) {
            registrar_error("Error al registrar movimiento: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Registrar venta en caja (llamado automáticamente desde Venta::crear)
     * 
     * @param int $caja_id ID de la caja
     * @param int $venta_id ID de la venta
     * @param float $monto Monto de la venta
     * @param int $usuario_id ID del usuario
     * @param string $concepto Concepto del movimiento
     * @return int|false ID del movimiento o false si falla
     */
    public static function registrarVenta($caja_id, $venta_id, $monto, $usuario_id, $concepto) {
        return self::registrarMovimiento([
            'caja_id' => $caja_id,
            'tipo_movimiento' => 'venta',
            'categoria' => 'ingreso',
            'concepto' => $concepto,
            'monto' => $monto,
            'usuario_id' => $usuario_id,
            'referencia_tipo' => 'venta',
            'referencia_id' => $venta_id
        ]);
    }
    
    /**
     * Registrar ingreso por reparación
     * 
     * @param int $caja_id ID de la caja
     * @param int $trabajo_id ID del trabajo
     * @param float $monto Monto del ingreso
     * @param int $usuario_id ID del usuario
     * @param string $concepto Concepto del ingreso
     * @return int|false ID del movimiento o false si falla
     */
    public static function registrarIngresoReparacion($caja_id, $trabajo_id, $monto, $usuario_id, $concepto) {
        return self::registrarMovimiento([
            'caja_id' => $caja_id,
            'tipo_movimiento' => 'ingreso_reparacion',
            'categoria' => 'ingreso',
            'concepto' => $concepto,
            'monto' => $monto,
            'usuario_id' => $usuario_id,
            'referencia_tipo' => 'trabajo_taller',
            'referencia_id' => $trabajo_id
        ]);
    }
    
    /**
     * Registrar anticipo de trabajo
     * 
     * @param int $caja_id ID de la caja
     * @param int $trabajo_id ID del trabajo
     * @param float $monto Monto del anticipo
     * @param int $usuario_id ID del usuario
     * @param string $concepto Concepto del anticipo
     * @return int|false ID del movimiento o false si falla
     */
    public static function registrarAnticipoTrabajo($caja_id, $trabajo_id, $monto, $usuario_id, $concepto) {
        return self::registrarMovimiento([
            'caja_id' => $caja_id,
            'tipo_movimiento' => 'anticipo_trabajo',
            'categoria' => 'ingreso',
            'concepto' => $concepto,
            'monto' => $monto,
            'usuario_id' => $usuario_id,
            'referencia_tipo' => 'trabajo_taller',
            'referencia_id' => $trabajo_id
        ]);
    }
    
    /**
     * Registrar abono a crédito (llamado desde Credito::registrarAbono)
     * 
     * @param int $caja_id ID de la caja
     * @param int $abono_id ID del abono
     * @param float $monto Monto del abono
     * @param int $usuario_id ID del usuario
     * @param string $concepto Concepto del abono
     * @return int|false ID del movimiento o false si falla
     */
    public static function registrarAbonoCredito($caja_id, $abono_id, $monto, $usuario_id, $concepto) {
        return self::registrarMovimiento([
            'caja_id' => $caja_id,
            'tipo_movimiento' => 'abono_credito',
            'categoria' => 'ingreso',
            'concepto' => $concepto,
            'monto' => $monto,
            'usuario_id' => $usuario_id,
            'referencia_tipo' => 'abono_credito',
            'referencia_id' => $abono_id
        ]);
    }
    
    /**
     * Registrar anticipo de mercadería apartada
     * 
     * @param int $caja_id ID de la caja
     * @param int $apartado_id ID del apartado
     * @param float $monto Monto del anticipo
     * @param int $usuario_id ID del usuario
     * @param string $concepto Concepto del anticipo
     * @return int|false ID del movimiento o false si falla
     */
    public static function registrarAnticipoApartado($caja_id, $apartado_id, $monto, $usuario_id, $concepto) {
        return self::registrarMovimiento([
            'caja_id' => $caja_id,
            'tipo_movimiento' => 'anticipo_apartado',
            'categoria' => 'ingreso',
            'concepto' => $concepto,
            'monto' => $monto,
            'usuario_id' => $usuario_id,
            'referencia_tipo' => 'apartado',
            'referencia_id' => $apartado_id
        ]);
    }
    
    /**
     * Registrar gasto
     * 
     * @param int $caja_id ID de la caja
     * @param float $monto Monto del gasto
     * @param int $usuario_id ID del usuario
     * @param string $concepto Concepto del gasto
     * @return int|false ID del movimiento o false si falla
     */
    public static function registrarGasto($caja_id, $monto, $usuario_id, $concepto) {
        return self::registrarMovimiento([
            'caja_id' => $caja_id,
            'tipo_movimiento' => 'gasto',
            'categoria' => 'egreso',
            'concepto' => $concepto,
            'monto' => $monto,
            'usuario_id' => $usuario_id
        ]);
    }
    
    /**
     * Registrar pago a proveedor
     * 
     * @param int $caja_id ID de la caja
     * @param int $compra_id ID de la compra (opcional)
     * @param float $monto Monto del pago
     * @param int $usuario_id ID del usuario
     * @param string $concepto Concepto del pago
     * @return int|false ID del movimiento o false si falla
     */
    public static function registrarPagoProveedor($caja_id, $compra_id, $monto, $usuario_id, $concepto) {
        return self::registrarMovimiento([
            'caja_id' => $caja_id,
            'tipo_movimiento' => 'pago_proveedor',
            'categoria' => 'egreso',
            'concepto' => $concepto,
            'monto' => $monto,
            'usuario_id' => $usuario_id,
            'referencia_tipo' => $compra_id ? 'compra' : null,
            'referencia_id' => $compra_id
        ]);
    }
    
    /**
     * Registrar compra de material
     * 
     * @param int $caja_id ID de la caja
     * @param float $monto Monto de la compra
     * @param int $usuario_id ID del usuario
     * @param string $concepto Concepto de la compra
     * @return int|false ID del movimiento o false si falla
     */
    public static function registrarCompraMaterial($caja_id, $monto, $usuario_id, $concepto) {
        return self::registrarMovimiento([
            'caja_id' => $caja_id,
            'tipo_movimiento' => 'compra_material',
            'categoria' => 'egreso',
            'concepto' => $concepto,
            'monto' => $monto,
            'usuario_id' => $usuario_id
        ]);
    }
    
    /**
     * Registrar pago de alquiler
     * 
     * @param int $caja_id ID de la caja
     * @param float $monto Monto del alquiler
     * @param int $usuario_id ID del usuario
     * @param string $concepto Concepto del alquiler
     * @return int|false ID del movimiento o false si falla
     */
    public static function registrarAlquiler($caja_id, $monto, $usuario_id, $concepto) {
        return self::registrarMovimiento([
            'caja_id' => $caja_id,
            'tipo_movimiento' => 'alquiler',
            'categoria' => 'egreso',
            'concepto' => $concepto,
            'monto' => $monto,
            'usuario_id' => $usuario_id
        ]);
    }
    
    /**
     * Registrar pago de salario
     * 
     * @param int $caja_id ID de la caja
     * @param int $empleado_id ID del empleado
     * @param float $monto Monto del salario
     * @param int $usuario_id ID del usuario que registra
     * @param string $concepto Concepto del salario
     * @return int|false ID del movimiento o false si falla
     */
    public static function registrarSalario($caja_id, $empleado_id, $monto, $usuario_id, $concepto) {
        return self::registrarMovimiento([
            'caja_id' => $caja_id,
            'tipo_movimiento' => 'salario',
            'categoria' => 'egreso',
            'concepto' => $concepto,
            'monto' => $monto,
            'usuario_id' => $usuario_id,
            'referencia_tipo' => 'usuario',
            'referencia_id' => $empleado_id
        ]);
    }
    
    /**
     * Registrar otro ingreso
     * 
     * @param int $caja_id ID de la caja
     * @param float $monto Monto del ingreso
     * @param int $usuario_id ID del usuario
     * @param string $concepto Concepto del ingreso
     * @return int|false ID del movimiento o false si falla
     */
    public static function registrarOtroIngreso($caja_id, $monto, $usuario_id, $concepto) {
        return self::registrarMovimiento([
            'caja_id' => $caja_id,
            'tipo_movimiento' => 'otro_ingreso',
            'categoria' => 'ingreso',
            'concepto' => $concepto,
            'monto' => $monto,
            'usuario_id' => $usuario_id
        ]);
    }
    
    /**
     * Registrar otro egreso
     * 
     * @param int $caja_id ID de la caja
     * @param float $monto Monto del egreso
     * @param int $usuario_id ID del usuario
     * @param string $concepto Concepto del egreso
     * @return int|false ID del movimiento o false si falla
     */
    public static function registrarOtroEgreso($caja_id, $monto, $usuario_id, $concepto) {
        return self::registrarMovimiento([
            'caja_id' => $caja_id,
            'tipo_movimiento' => 'otro_egreso',
            'categoria' => 'egreso',
            'concepto' => $concepto,
            'monto' => $monto,
            'usuario_id' => $usuario_id
        ]);
    }
    
    // ========================================
    // CONSULTAS Y CÁLCULOS
    // ========================================
    
    /**
     * Obtener caja actual del usuario
     * 
     * @param int $usuario_id ID del usuario (opcional, usa usuario actual si no se provee)
     * @return array|false Datos de la caja o false si no tiene caja abierta
     */
    public static function obtenerCajaActual($usuario_id = null) {
        try {
            if ($usuario_id === null) {
                $usuario_id = usuario_actual_id();
            }
            
            $caja = db_query_one(
                "SELECT c.*, 
                        s.nombre as sucursal_nombre,
                        u.nombre as usuario_nombre
                 FROM cajas c
                 INNER JOIN sucursales s ON c.sucursal_id = s.id
                 INNER JOIN usuarios u ON c.usuario_id = u.id
                 WHERE c.usuario_id = ? AND c.estado = 'abierta'
                 ORDER BY c.id DESC
                 LIMIT 1",
                [$usuario_id]
            );
            
            if ($caja) {
                // Agregar totales actuales
                $totales = self::calcularTotalesCaja($caja['id']);
                $caja = array_merge($caja, $totales);
            }
            
            return $caja;
            
        } catch (PDOException $e) {
            registrar_error("Error al obtener caja actual: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener caja por ID
     * 
     * @param int $id ID de la caja
     * @return array|false Datos de la caja o false si no existe
     */
    public static function obtenerPorId($id) {
        try {
            $caja = db_query_one(
                "SELECT c.*,
                        s.nombre as sucursal_nombre,
                        u.nombre as usuario_nombre
                 FROM cajas c
                 INNER JOIN sucursales s ON c.sucursal_id = s.id
                 INNER JOIN usuarios u ON c.usuario_id = u.id
                 WHERE c.id = ?",
                [$id]
            );
            
            if ($caja) {
                // Agregar totales
                $totales = self::calcularTotalesCaja($caja['id']);
                $caja = array_merge($caja, $totales);
            }
            
            return $caja;
            
        } catch (PDOException $e) {
            registrar_error("Error al obtener caja: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener movimientos de una caja
     * 
     * @param int $caja_id ID de la caja
     * @param array $filtros Filtros opcionales:
     *   - tipo_movimiento: string
     *   - categoria: string (ingreso|egreso)
     *   - fecha_desde: date
     *   - fecha_hasta: date
     * @return array Lista de movimientos
     */
    public static function obtenerMovimientosCaja($caja_id, $filtros = []) {
        try {
            $where = ["mc.caja_id = ?"];
            $params = [$caja_id];
            
            // Filtros opcionales
            if (!empty($filtros['tipo_movimiento'])) {
                $where[] = "mc.tipo_movimiento = ?";
                $params[] = $filtros['tipo_movimiento'];
            }
            
            if (!empty($filtros['categoria'])) {
                $where[] = "mc.categoria = ?";
                $params[] = $filtros['categoria'];
            }
            
            if (!empty($filtros['fecha_desde'])) {
                $where[] = "DATE(mc.fecha_hora) >= ?";
                $params[] = $filtros['fecha_desde'];
            }
            
            if (!empty($filtros['fecha_hasta'])) {
                $where[] = "DATE(mc.fecha_hora) <= ?";
                $params[] = $filtros['fecha_hasta'];
            }
            
            $where_sql = implode(' AND ', $where);
            
            $movimientos = db_query(
                "SELECT mc.*,
                        u.nombre as usuario_nombre
                 FROM movimientos_caja mc
                 INNER JOIN usuarios u ON mc.usuario_id = u.id
                 WHERE $where_sql
                 ORDER BY mc.fecha_hora DESC",
                $params
            );
            
            return $movimientos;
            
        } catch (PDOException $e) {
            registrar_error("Error al obtener movimientos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Calcular totales de una caja
     * 
     * @param int $caja_id ID de la caja
     * @return array Array con totales:
     *   - monto_inicial
     *   - total_ingresos
     *   - total_egresos
     *   - total_final
     *   - cantidad_movimientos
     *   - desglose_ingresos (por tipo)
     *   - desglose_egresos (por tipo)
     */
    public static function calcularTotalesCaja($caja_id) {
        try {
            // Obtener monto inicial
            $caja = db_query_one("SELECT monto_inicial FROM cajas WHERE id = ?", [$caja_id]);
            $monto_inicial = $caja ? $caja['monto_inicial'] : 0;
            
            // Calcular totales por categoría
            $totales = db_query_one(
                "SELECT 
                    COALESCE(SUM(CASE WHEN categoria = 'ingreso' THEN monto ELSE 0 END), 0) as total_ingresos,
                    COALESCE(SUM(CASE WHEN categoria = 'egreso' THEN monto ELSE 0 END), 0) as total_egresos,
                    COUNT(*) as cantidad_movimientos
                 FROM movimientos_caja 
                 WHERE caja_id = ?",
                [$caja_id]
            );
            
            // Desglose de ingresos por tipo
            $desglose_ingresos = db_query(
                "SELECT tipo_movimiento, SUM(monto) as total, COUNT(*) as cantidad
                 FROM movimientos_caja
                 WHERE caja_id = ? AND categoria = 'ingreso'
                 GROUP BY tipo_movimiento
                 ORDER BY total DESC",
                [$caja_id]
            );
            
            // Desglose de egresos por tipo
            $desglose_egresos = db_query(
                "SELECT tipo_movimiento, SUM(monto) as total, COUNT(*) as cantidad
                 FROM movimientos_caja
                 WHERE caja_id = ? AND categoria = 'egreso'
                 GROUP BY tipo_movimiento
                 ORDER BY total DESC",
                [$caja_id]
            );
            
            $total_ingresos = $totales['total_ingresos'];
            $total_egresos = $totales['total_egresos'];
            $total_final = $monto_inicial + $total_ingresos - $total_egresos;
            
            return [
                'monto_inicial' => $monto_inicial,
                'total_ingresos' => $total_ingresos,
                'total_egresos' => $total_egresos,
                'total_final' => $total_final,
                'cantidad_movimientos' => $totales['cantidad_movimientos'],
                'desglose_ingresos' => $desglose_ingresos,
                'desglose_egresos' => $desglose_egresos
            ];
            
        } catch (PDOException $e) {
            registrar_error("Error al calcular totales: " . $e->getMessage());
            return [
                'monto_inicial' => 0,
                'total_ingresos' => 0,
                'total_egresos' => 0,
                'total_final' => 0,
                'cantidad_movimientos' => 0,
                'desglose_ingresos' => [],
                'desglose_egresos' => []
            ];
        }
    }
    
    /**
     * Obtener historial de cierres de caja
     * 
     * @param array $filtros Filtros opcionales:
     *   - sucursal_id: int
     *   - usuario_id: int
     *   - fecha_desde: date
     *   - fecha_hasta: date
     *   - con_diferencia: bool (solo cajas con diferencia)
     *   - limite: int (default 50)
     *   - offset: int (default 0)
     * @return array Lista de cierres
     */
    public static function obtenerHistorialCierres($filtros = []) {
        try {
            $where = ["c.estado = 'cerrada'"];
            $params = [];
            
            // Filtros opcionales
            if (!empty($filtros['sucursal_id'])) {
                $where[] = "c.sucursal_id = ?";
                $params[] = $filtros['sucursal_id'];
            }
            
            if (!empty($filtros['usuario_id'])) {
                $where[] = "c.usuario_id = ?";
                $params[] = $filtros['usuario_id'];
            }
            
            if (!empty($filtros['fecha_desde'])) {
                $where[] = "DATE(c.fecha_cierre) >= ?";
                $params[] = $filtros['fecha_desde'];
            }
            
            if (!empty($filtros['fecha_hasta'])) {
                $where[] = "DATE(c.fecha_cierre) <= ?";
                $params[] = $filtros['fecha_hasta'];
            }
            
            if (!empty($filtros['con_diferencia'])) {
                $where[] = "ABS(c.diferencia) > 0.01";
            }
            
            $where_sql = implode(' AND ', $where);
            
            $limite = isset($filtros['limite']) ? (int)$filtros['limite'] : 50;
            $offset = isset($filtros['offset']) ? (int)$filtros['offset'] : 0;
            
            $cierres = db_query(
                "SELECT c.*,
                        s.nombre as sucursal_nombre,
                        u.nombre as usuario_nombre
                 FROM cajas c
                 INNER JOIN sucursales s ON c.sucursal_id = s.id
                 INNER JOIN usuarios u ON c.usuario_id = u.id
                 WHERE $where_sql
                 ORDER BY c.fecha_cierre DESC
                 LIMIT ? OFFSET ?",
                array_merge($params, [$limite, $offset])
            );
            
            return $cierres;
            
        } catch (PDOException $e) {
            registrar_error("Error al obtener historial de cierres: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener estadísticas de cajas
     * 
     * @param array $filtros Filtros opcionales:
     *   - sucursal_id: int
     *   - fecha_desde: date
     *   - fecha_hasta: date
     * @return array Estadísticas generales
     */
    public static function obtenerEstadisticas($filtros = []) {
        try {
            $where = ["c.estado = 'cerrada'"];
            $params = [];
            
            if (!empty($filtros['sucursal_id'])) {
                $where[] = "c.sucursal_id = ?";
                $params[] = $filtros['sucursal_id'];
            }
            
            if (!empty($filtros['fecha_desde'])) {
                $where[] = "DATE(c.fecha_cierre) >= ?";
                $params[] = $filtros['fecha_desde'];
            }
            
            if (!empty($filtros['fecha_hasta'])) {
                $where[] = "DATE(c.fecha_cierre) <= ?";
                $params[] = $filtros['fecha_hasta'];
            }
            
            $where_sql = implode(' AND ', $where);
            
            $estadisticas = db_query_one(
                "SELECT 
                    COUNT(*) as total_cierres,
                    COALESCE(SUM(monto_esperado), 0) as total_esperado,
                    COALESCE(SUM(monto_real), 0) as total_real,
                    COALESCE(SUM(diferencia), 0) as diferencia_total,
                    COALESCE(AVG(diferencia), 0) as diferencia_promedio,
                    COUNT(CASE WHEN ABS(diferencia) > 0.01 THEN 1 END) as cierres_con_diferencia,
                    COUNT(CASE WHEN diferencia > 0 THEN 1 END) as cierres_sobrante,
                    COUNT(CASE WHEN diferencia < 0 THEN 1 END) as cierres_faltante
                 FROM cajas c
                 WHERE $where_sql",
                $params
            );
            
            return $estadisticas;
            
        } catch (PDOException $e) {
            registrar_error("Error al obtener estadísticas: " . $e->getMessage());
            return [];
        }
    }
    
    // ========================================
    // VALIDACIONES
    // ========================================
    
    /**
     * Validar datos de apertura de caja
     * 
     * @param array $datos Datos a validar
     * @return array Array de errores (vacío si no hay errores)
     */
    private static function validarApertura($datos) {
        $errores = [];
        
        // Usuario ID requerido
        if (empty($datos['usuario_id'])) {
            $errores[] = 'El ID del usuario es requerido';
        }
        
        // Sucursal ID requerida
        if (empty($datos['sucursal_id'])) {
            $errores[] = 'El ID de la sucursal es requerido';
        }
        
        // Monto inicial requerido y debe ser >= 0
        if (!isset($datos['monto_inicial']) || $datos['monto_inicial'] === '') {
            $errores[] = 'El monto inicial es requerido';
        } elseif (!is_numeric($datos['monto_inicial']) || $datos['monto_inicial'] < 0) {
            $errores[] = 'El monto inicial debe ser un número mayor o igual a cero';
        }
        
        return $errores;
    }
    
    /**
     * Validar datos de movimiento
     * 
     * @param array $datos Datos a validar
     * @return array Array de errores (vacío si no hay errores)
     */
    private static function validarMovimiento($datos) {
        $errores = [];
        
        // Caja ID requerida
        if (empty($datos['caja_id'])) {
            $errores[] = 'El ID de la caja es requerido';
        }
        
        // Tipo de movimiento requerido y válido
        $tipos_validos = [
            'venta', 'ingreso_reparacion', 'anticipo_trabajo', 'abono_credito',
            'anticipo_apartado', 'gasto', 'pago_proveedor', 'compra_material',
            'alquiler', 'salario', 'otro_ingreso', 'otro_egreso'
        ];
        
        if (empty($datos['tipo_movimiento'])) {
            $errores[] = 'El tipo de movimiento es requerido';
        } elseif (!in_array($datos['tipo_movimiento'], $tipos_validos)) {
            $errores[] = 'Tipo de movimiento inválido';
        }
        
        // Categoría requerida y válida
        if (empty($datos['categoria'])) {
            $errores[] = 'La categoría es requerida';
        } elseif (!in_array($datos['categoria'], ['ingreso', 'egreso'])) {
            $errores[] = 'Categoría inválida (debe ser ingreso o egreso)';
        }
        
        // Concepto requerido
        if (empty($datos['concepto'])) {
            $errores[] = 'El concepto es requerido';
        }
        
        // Monto requerido y debe ser > 0
        if (!isset($datos['monto']) || $datos['monto'] === '') {
            $errores[] = 'El monto es requerido';
        } elseif (!is_numeric($datos['monto']) || $datos['monto'] <= 0) {
            $errores[] = 'El monto debe ser un número mayor a cero';
        }
        
        // Usuario ID requerido
        if (empty($datos['usuario_id'])) {
            $errores[] = 'El ID del usuario es requerido';
        }
        
        return $errores;
    }
    
    // ========================================
    // UTILIDADES
    // ========================================
    
    /**
     * Verificar si un usuario tiene caja abierta
     * 
     * @param int $usuario_id ID del usuario
     * @return bool True si tiene caja abierta, false si no
     */
    public static function tieneCajaAbierta($usuario_id) {
        $caja = db_query_one(
            "SELECT id FROM cajas WHERE usuario_id = ? AND estado = 'abierta'",
            [$usuario_id]
        );
        
        return $caja !== false;
    }
    
    /**
     * Obtener ID de caja abierta del usuario
     * 
     * @param int $usuario_id ID del usuario
     * @return int|false ID de la caja o false si no tiene caja abierta
     */
    public static function obtenerIdCajaAbierta($usuario_id) {
        $caja = db_query_one(
            "SELECT id FROM cajas WHERE usuario_id = ? AND estado = 'abierta' ORDER BY id DESC LIMIT 1",
            [$usuario_id]
        );
        
        return $caja ? $caja['id'] : false;
    }

    /**
 * Obtener ID de caja abierta de una sucursal específica
 * 
 * @param int $sucursal_id ID de la sucursal
 * @return int|false ID de la caja o false si no hay caja abierta
 */
public static function obtenerCajaAbiertaPorSucursal($sucursal_id) {
    $caja = db_query_one(
        "SELECT id FROM cajas 
         WHERE sucursal_id = ? AND estado = 'abierta' 
         ORDER BY fecha_apertura DESC LIMIT 1",
        [$sucursal_id]
    );
    
    return $caja ? $caja['id'] : false;
}
    
    /**
     * Obtener tipos de movimiento disponibles
     * 
     * @return array Array con tipos de movimiento agrupados por categoría
     */
    public static function obtenerTiposMovimiento() {
        return [
            'ingreso' => [
                'venta' => 'Venta',
                'ingreso_reparacion' => 'Ingreso por Reparación',
                'anticipo_trabajo' => 'Anticipo de Trabajo',
                'abono_credito' => 'Abono a Crédito',
                'anticipo_apartado' => 'Anticipo Mercadería Apartada',
                'otro_ingreso' => 'Otro Ingreso'
            ],
            'egreso' => [
                'gasto' => 'Gasto',
                'pago_proveedor' => 'Pago a Proveedor',
                'compra_material' => 'Compra de Material',
                'alquiler' => 'Alquiler',
                'salario' => 'Salario',
                'otro_egreso' => 'Otro Egreso'
            ]
        ];
    }
}