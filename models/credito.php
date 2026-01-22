<?php
// ================================================
// MODELO: CREDITO
// Sistema de Gestión - Joyería Torre Fuerte
// ================================================

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/funciones.php';
require_once __DIR__ . '/cliente.php';

class Credito {
    
    // ========================================
    // MÉTODOS DE CONSULTA (SELECT)
    // ========================================
    
    /**
     * Lista créditos con filtros
     * 
     * @param array $filtros Array de filtros (cliente_id, estado, fecha_inicio, fecha_fin)
     * @param int $pagina Número de página
     * @param int $por_pagina Registros por página
     * @return array Array de créditos
     */
    public static function listar($filtros = [], $pagina = 1, $por_pagina = 20) {
        global $pdo;
        
        $where = ['1=1'];
        $params = [];
        
        // Filtro por cliente
        if (isset($filtros['cliente_id']) && !empty($filtros['cliente_id'])) {
            $where[] = 'cc.cliente_id = ?';
            $params[] = $filtros['cliente_id'];
        }
        
        // Filtro por estado
        if (isset($filtros['estado']) && !empty($filtros['estado'])) {
            $where[] = 'cc.estado = ?';
            $params[] = $filtros['estado'];
        }
        
        // Filtro por rango de fechas de inicio
        if (isset($filtros['fecha_inicio']) && !empty($filtros['fecha_inicio'])) {
            $where[] = 'cc.fecha_inicio >= ?';
            $params[] = $filtros['fecha_inicio'];
        }
        
        if (isset($filtros['fecha_fin']) && !empty($filtros['fecha_fin'])) {
            $where[] = 'cc.fecha_inicio <= ?';
            $params[] = $filtros['fecha_fin'];
        }
        
        // Filtro por créditos vencidos
        if (isset($filtros['vencidos']) && $filtros['vencidos']) {
            $where[] = 'cc.dias_atraso > 0';
        }
        
        $where_sql = implode(' AND ', $where);
        
        // Calcular offset
        $offset = ($pagina - 1) * $por_pagina;
        
        $sql = "SELECT cc.*,
                       c.nombre as cliente_nombre,
                       c.telefono as cliente_telefono,
                       v.numero_venta,
                       (SELECT COUNT(*) FROM abonos_creditos WHERE credito_id = cc.id) as total_abonos
                FROM creditos_clientes cc
                INNER JOIN clientes c ON cc.cliente_id = c.id
                INNER JOIN ventas v ON cc.venta_id = v.id
                WHERE $where_sql
                ORDER BY cc.fecha_proximo_pago ASC, cc.dias_atraso DESC
                LIMIT ? OFFSET ?";
        
        $params[] = $por_pagina;
        $params[] = $offset;
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            registrar_error("Error al listar créditos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene un crédito por su ID
     * 
     * @param int $id ID del crédito
     * @return array|false Crédito o false
     */
    public static function obtenerPorId($id) {
        global $pdo;
        
        $sql = "SELECT cc.*,
                       c.nombre as cliente_nombre,
                       c.telefono as cliente_telefono,
                       c.nit as cliente_nit,
                       c.direccion as cliente_direccion,
                       v.numero_venta,
                       v.fecha as fecha_venta,
                       v.total as total_venta
                FROM creditos_clientes cc
                INNER JOIN clientes c ON cc.cliente_id = c.id
                INNER JOIN ventas v ON cc.venta_id = v.id
                WHERE cc.id = ?";
        
        try {
            $credito = db_query_one($sql, [$id]);
            
            if ($credito) {
                // Obtener historial de abonos
                $credito['abonos'] = self::obtenerAbonos($id);
            }
            
            return $credito;
            
        } catch (PDOException $e) {
            registrar_error("Error al obtener crédito: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene un crédito por ID de venta
     * 
     * @param int $venta_id ID de la venta
     * @return array|false Crédito o false
     */
    public static function obtenerPorVenta($venta_id) {
        $credito = db_query_one(
            "SELECT * FROM creditos_clientes WHERE venta_id = ?",
            [$venta_id]
        );
        
        if ($credito) {
            return self::obtenerPorId($credito['id']);
        }
        
        return false;
    }
    
    /**
     * Obtiene todos los créditos de un cliente
     * 
     * @param int $cliente_id ID del cliente
     * @param string $estado Estado del crédito (opcional)
     * @return array Array de créditos
     */
    public static function obtenerPorCliente($cliente_id, $estado = null) {
        $filtros = ['cliente_id' => $cliente_id];
        
        if ($estado) {
            $filtros['estado'] = $estado;
        }
        
        return self::listar($filtros, 1, 1000);
    }
    
    /**
     * Obtiene créditos activos de un cliente
     * 
     * @param int $cliente_id ID del cliente
     * @return array Array de créditos activos
     */
    public static function obtenerActivos($cliente_id) {
        return self::obtenerPorCliente($cliente_id, 'activo');
    }
    
    /**
     * Obtiene todos los créditos vencidos
     * 
     * @param int $dias_minimo Días mínimos de atraso (default: 1)
     * @return array Array de créditos vencidos
     */
    public static function obtenerVencidos($dias_minimo = 1) {
        global $pdo;
        
        $sql = "SELECT cc.*,
                       c.nombre as cliente_nombre,
                       c.telefono as cliente_telefono,
                       v.numero_venta
                FROM creditos_clientes cc
                INNER JOIN clientes c ON cc.cliente_id = c.id
                INNER JOIN ventas v ON cc.venta_id = v.id
                WHERE cc.estado = 'activo' AND cc.dias_atraso >= ?
                ORDER BY cc.dias_atraso DESC, cc.saldo_pendiente DESC";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$dias_minimo]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            registrar_error("Error al obtener créditos vencidos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene créditos próximos a vencer
     * 
     * @param int $dias Días de anticipación (default: 3)
     * @return array Array de créditos próximos a vencer
     */
    public static function obtenerProximosAVencer($dias = 3) {
        global $pdo;
        
        $fecha_limite = date('Y-m-d', strtotime("+{$dias} days"));
        
        $sql = "SELECT cc.*,
                       c.nombre as cliente_nombre,
                       c.telefono as cliente_telefono,
                       v.numero_venta,
                       DATEDIFF(cc.fecha_proximo_pago, CURDATE()) as dias_restantes
                FROM creditos_clientes cc
                INNER JOIN clientes c ON cc.cliente_id = c.id
                INNER JOIN ventas v ON cc.venta_id = v.id
                WHERE cc.estado = 'activo' 
                  AND cc.fecha_proximo_pago <= ?
                  AND cc.fecha_proximo_pago >= CURDATE()
                ORDER BY cc.fecha_proximo_pago ASC";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$fecha_limite]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            registrar_error("Error al obtener créditos próximos a vencer: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene el historial de abonos de un crédito
     * 
     * @param int $credito_id ID del crédito
     * @return array Array de abonos
     */
    public static function obtenerAbonos($credito_id) {
        global $pdo;
        
        $sql = "SELECT ac.*,
                       u.nombre as usuario_nombre,
                       c.id as caja_id
                FROM abonos_creditos ac
                LEFT JOIN usuarios u ON ac.usuario_id = u.id
                LEFT JOIN cajas c ON ac.caja_id = c.id
                WHERE ac.credito_id = ?
                ORDER BY ac.fecha_abono DESC, ac.fecha_hora DESC";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$credito_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            registrar_error("Error al obtener abonos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene el total de créditos con filtros
     * 
     * @param array $filtros Array de filtros
     * @return int Total de créditos
     */
    public static function contarTotal($filtros = []) {
        $where = ['1=1'];
        $params = [];
        
        if (isset($filtros['cliente_id']) && !empty($filtros['cliente_id'])) {
            $where[] = 'cliente_id = ?';
            $params[] = $filtros['cliente_id'];
        }
        
        if (isset($filtros['estado']) && !empty($filtros['estado'])) {
            $where[] = 'estado = ?';
            $params[] = $filtros['estado'];
        }
        
        $where_sql = implode(' AND ', $where);
        
        return db_count('creditos_clientes', $where_sql, $params);
    }
    
    // ========================================
    // MÉTODOS DE CREACIÓN (INSERT)
    // ========================================
    
    /**
     * Crea un nuevo crédito (llamado desde Venta::crear)
     * 
     * @param array $datos Datos del crédito
     *   - cliente_id (int): ID del cliente
     *   - venta_id (int): ID de la venta
     *   - monto_total (float): Monto total del crédito
     *   - numero_cuotas (int): Número de cuotas semanales
     *   - fecha_inicio (string): Fecha de inicio (default: hoy)
     * @return int|false ID del crédito creado o false
     */
    public static function crear($datos) {
        global $pdo;
        
        // Validar datos
        $errores = self::validar($datos);
        if (!empty($errores)) {
            registrar_error("Errores de validación al crear crédito: " . implode(', ', $errores));
            return false;
        }
        
        try {
            $fecha_inicio = $datos['fecha_inicio'] ?? date('Y-m-d');
            $monto_total = $datos['monto_total'];
            $numero_cuotas = $datos['numero_cuotas'];
            
            // Calcular cuota semanal
            $cuota_semanal = round($monto_total / $numero_cuotas, 2);
            
            // Calcular fecha de próximo pago (+7 días)
            $fecha_proximo_pago = date('Y-m-d', strtotime($fecha_inicio . ' +7 days'));
            
            $sql = "INSERT INTO creditos_clientes (
                        cliente_id, venta_id, monto_total, saldo_pendiente,
                        cuota_semanal, numero_cuotas, cuotas_pagadas,
                        fecha_inicio, fecha_proximo_pago, estado, dias_atraso
                    ) VALUES (?, ?, ?, ?, ?, ?, 0, ?, ?, 'activo', 0)";
            
            $params = [
                $datos['cliente_id'],
                $datos['venta_id'],
                $monto_total,
                $monto_total, // saldo_pendiente inicial = monto_total
                $cuota_semanal,
                $numero_cuotas,
                $fecha_inicio,
                $fecha_proximo_pago
            ];
            
            $credito_id = db_execute($sql, $params);
            
            if ($credito_id) {
                registrar_auditoria('INSERT', 'creditos_clientes', $credito_id, 
                    "Crédito creado: " . formato_dinero($monto_total) . " en {$numero_cuotas} cuotas semanales");
            }
            
            return $credito_id;
            
        } catch (PDOException $e) {
            registrar_error("Error al crear crédito: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Registra un abono a un crédito
     * 
     * @param int $credito_id ID del crédito
     * @param array $datos Datos del abono
     *   - monto (float): Monto del abono
     *   - forma_pago (string): Forma de pago
     *   - fecha_abono (string): Fecha del abono (default: hoy)
     *   - observaciones (string): Observaciones opcionales
     * @return int|false ID del abono o false
     */
    public static function registrarAbono($credito_id, $datos) {
        global $pdo;
        
        try {
            $credito = self::obtenerPorId($credito_id);
            
            if (!$credito) {
                registrar_error("Crédito no encontrado: ID $credito_id");
                return false;
            }
            
            // Validar estado del crédito
            if ($credito['estado'] !== 'activo') {
                registrar_error("No se puede abonar a un crédito con estado: {$credito['estado']}");
                return false;
            }
            
            // Validar monto
            if (empty($datos['monto']) || $datos['monto'] <= 0) {
                registrar_error("El monto del abono debe ser mayor a cero");
                return false;
            }
            
            // Si el abono excede el saldo, ajustar al saldo pendiente
            $monto_abono = $datos['monto'];
            if ($monto_abono > $credito['saldo_pendiente']) {
                $monto_abono = $credito['saldo_pendiente'];
            }
            
            $pdo->beginTransaction();
            
            // Calcular nuevo saldo
            $saldo_anterior = $credito['saldo_pendiente'];
            $saldo_nuevo = $saldo_anterior - $monto_abono;
            
            // Insertar abono (registro inmutable)
            $fecha_abono = $datos['fecha_abono'] ?? date('Y-m-d');
            
            // Obtener caja abierta
            $caja = db_query_one(
                "SELECT id FROM cajas WHERE usuario_id = ? AND estado = 'abierta' ORDER BY id DESC LIMIT 1",
                [usuario_actual_id()]
            );
            
            $sql_abono = "INSERT INTO abonos_creditos (
                             credito_id, monto, forma_pago, fecha_abono,
                             saldo_anterior, saldo_nuevo, usuario_id, caja_id, observaciones
                          ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $params_abono = [
                $credito_id,
                $monto_abono,
                $datos['forma_pago'],
                $fecha_abono,
                $saldo_anterior,
                $saldo_nuevo,
                usuario_actual_id(),
                $caja['id'] ?? null,
                $datos['observaciones'] ?? null
            ];
            
            $abono_id = db_execute($sql_abono, $params_abono);
            
            if (!$abono_id) {
                throw new Exception("No se pudo registrar el abono");
            }
            
            // Actualizar crédito
            $cuotas_pagadas = $credito['cuotas_pagadas'] + 1;
            $nuevo_estado = $credito['estado'];
            $fecha_liquidacion = null;
            
            // Si saldo = 0, marcar como liquidado
            if ($saldo_nuevo <= 0.01) { // Tolerancia para redondeo
                $saldo_nuevo = 0;
                $nuevo_estado = 'liquidado';
                $fecha_liquidacion = $fecha_abono;
                $cuotas_pagadas = $credito['numero_cuotas']; // Todas las cuotas pagadas
            }
            
            // Calcular nueva fecha de próximo pago (+7 días desde última fecha)
            $fecha_proximo_pago = date('Y-m-d', strtotime($credito['fecha_proximo_pago'] . ' +7 days'));
            
            // Recalcular días de atraso
            $dias_atraso = self::calcularDiasAtraso($fecha_proximo_pago);
            
            $sql_update = "UPDATE creditos_clientes SET
                              saldo_pendiente = ?,
                              cuotas_pagadas = ?,
                              fecha_proximo_pago = ?,
                              fecha_ultimo_abono = ?,
                              dias_atraso = ?,
                              estado = ?,
                              fecha_liquidacion = ?
                           WHERE id = ?";
            
            db_execute($sql_update, [
                $saldo_nuevo,
                $cuotas_pagadas,
                $fecha_proximo_pago,
                $fecha_abono,
                $dias_atraso,
                $nuevo_estado,
                $fecha_liquidacion,
                $credito_id
            ]);
            
            // Registrar movimiento en caja
            if ($caja) {
                $sql_movimiento = "INSERT INTO movimientos_caja (
                                      caja_id, tipo_movimiento, categoria, concepto,
                                      monto, usuario_id, referencia_tipo, referencia_id
                                  ) VALUES (?, 'abono_credito', 'ingreso', ?, ?, ?, 'credito', ?)";
                
                db_execute($sql_movimiento, [
                    $caja['id'],
                    "Abono crédito - Cliente: {$credito['cliente_nombre']} - Venta: {$credito['numero_venta']}",
                    $monto_abono,
                    usuario_actual_id(),
                    $credito_id
                ]);
            }
            
            // Auditoría
            $detalles = "Abono registrado: " . formato_dinero($monto_abono) . 
                        " - Saldo anterior: " . formato_dinero($saldo_anterior) . 
                        " - Nuevo saldo: " . formato_dinero($saldo_nuevo);
            
            if ($nuevo_estado === 'liquidado') {
                $detalles .= " - CRÉDITO LIQUIDADO";
            }
            
            registrar_auditoria('INSERT', 'abonos_creditos', $abono_id, $detalles);
            
            $pdo->commit();
            return $abono_id;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            registrar_error("Error al registrar abono: " . $e->getMessage());
            return false;
        }
    }
    
    // ========================================
    // MÉTODOS DE ACTUALIZACIÓN (UPDATE)
    // ========================================
    
    /**
     * Actualiza el estado de un crédito
     * 
     * @param int $credito_id ID del crédito
     * @param string $nuevo_estado Nuevo estado
     * @return bool
     */
    public static function actualizarEstado($credito_id, $nuevo_estado) {
        global $pdo;
        
        try {
            $credito = self::obtenerPorId($credito_id);
            
            if (!$credito) {
                return false;
            }
            
            if (!in_array($nuevo_estado, ['activo', 'liquidado', 'vencido'])) {
                registrar_error("Estado inválido: $nuevo_estado");
                return false;
            }
            
            $sql = "UPDATE creditos_clientes SET estado = ? WHERE id = ?";
            $resultado = db_execute($sql, [$nuevo_estado, $credito_id]);
            
            if ($resultado !== false) {
                registrar_auditoria('UPDATE', 'creditos_clientes', $credito_id, 
                    "Estado actualizado: {$credito['estado']} → {$nuevo_estado}");
            }
            
            return $resultado !== false;
            
        } catch (PDOException $e) {
            registrar_error("Error al actualizar estado de crédito: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Recalcula el estado de un crédito basado en días de atraso
     * 
     * @param int $credito_id ID del crédito
     * @return bool
     */
    public static function recalcularEstado($credito_id) {
        $credito = self::obtenerPorId($credito_id);
        
        if (!$credito || $credito['estado'] !== 'activo') {
            return false;
        }
        
        // Calcular días de atraso
        $dias_atraso = self::calcularDiasAtraso($credito['fecha_proximo_pago']);
        
        // Actualizar días de atraso
        db_execute(
            "UPDATE creditos_clientes SET dias_atraso = ? WHERE id = ?",
            [$dias_atraso, $credito_id]
        );
        
        // Si tiene más de 7 días de atraso, marcar como vencido
        if ($dias_atraso > 7) {
            return self::actualizarEstado($credito_id, 'vencido');
        }
        
        return true;
    }
    
    /**
     * Actualiza masivamente los estados de todos los créditos activos
     * 
     * @return int Cantidad de créditos actualizados
     */
    public static function actualizarEstadosMasivo() {
        $creditos_activos = self::listar(['estado' => 'activo'], 1, 10000);
        $actualizados = 0;
        
        foreach ($creditos_activos as $credito) {
            if (self::recalcularEstado($credito['id'])) {
                $actualizados++;
            }
        }
        
        registrar_auditoria('UPDATE', 'creditos_clientes', null, 
            "Actualización masiva de estados: {$actualizados} créditos procesados");
        
        return $actualizados;
    }
    
    // ========================================
    // MÉTODOS DE VALIDACIÓN
    // ========================================
    
    /**
     * Valida los datos de un crédito
     * 
     * @param array $datos Datos a validar
     * @return array Array de errores
     */
    public static function validar($datos) {
        $errores = [];
        
        // Cliente requerido
        if (empty($datos['cliente_id'])) {
            $errores[] = 'El cliente es requerido';
        } elseif (!Cliente::existe($datos['cliente_id'])) {
            $errores[] = 'El cliente no existe';
        }
        
        // Venta requerida
        if (empty($datos['venta_id'])) {
            $errores[] = 'La venta es requerida';
        } elseif (!db_exists('ventas', 'id = ?', [$datos['venta_id']])) {
            $errores[] = 'La venta no existe';
        }
        
        // Monto total requerido
        if (empty($datos['monto_total']) || $datos['monto_total'] <= 0) {
            $errores[] = 'El monto total debe ser mayor a cero';
        }
        
        // Número de cuotas requerido
        if (empty($datos['numero_cuotas']) || $datos['numero_cuotas'] <= 0) {
            $errores[] = 'El número de cuotas debe ser mayor a cero';
        } elseif ($datos['numero_cuotas'] > 52) {
            $errores[] = 'El número de cuotas no puede ser mayor a 52 (1 año)';
        }
        
        // Validar fecha de inicio si se proporciona
        if (isset($datos['fecha_inicio']) && !empty($datos['fecha_inicio'])) {
            // Validar formato de fecha simple
            $fecha_partes = explode('-', $datos['fecha_inicio']);
            if (count($fecha_partes) !== 3 || !checkdate($fecha_partes[1], $fecha_partes[2], $fecha_partes[0])) {
                $errores[] = 'La fecha de inicio no es válida';
            }
        }
        
        return $errores;
    }
    
    /**
     * Verifica si un crédito existe
     * 
     * @param int $id ID del crédito
     * @return bool
     */
    public static function existe($id) {
        return db_exists('creditos_clientes', 'id = ?', [$id]);
    }
    
    // ========================================
    // MÉTODOS AUXILIARES
    // ========================================
    
    /**
     * Calcula la fecha del próximo pago
     * 
     * @param string $fecha_inicio Fecha de inicio del crédito
     * @param int $cuotas_pagadas Cuotas ya pagadas
     * @return string Fecha del próximo pago
     */
    public static function calcularProximoPago($fecha_inicio, $cuotas_pagadas) {
        $semanas = $cuotas_pagadas + 1;
        return date('Y-m-d', strtotime($fecha_inicio . " +{$semanas} weeks"));
    }
    
    /**
     * Calcula los días de atraso
     * 
     * @param string $fecha_proximo_pago Fecha del próximo pago
     * @return int Días de atraso (0 si no está atrasado)
     */
    public static function calcularDiasAtraso($fecha_proximo_pago) {
        $hoy = strtotime(date('Y-m-d'));
        $fecha_pago = strtotime($fecha_proximo_pago);
        
        if ($hoy <= $fecha_pago) {
            return 0; // No está atrasado
        }
        
        $diferencia = $hoy - $fecha_pago;
        return (int)($diferencia / 86400); // Convertir a días
    }
    
    /**
     * Obtiene alertas de créditos (vencidos y próximos a vencer)
     * 
     * @param int $dias_anticipacion Días de anticipación para alertas (default: 3)
     * @return array Array con alertas
     */
    public static function obtenerAlertasCredito($dias_anticipacion = 3) {
        return [
            'vencidos' => self::obtenerVencidos(1),
            'proximos_vencer' => self::obtenerProximosAVencer($dias_anticipacion),
            'total_vencidos' => count(self::obtenerVencidos(1)),
            'total_proximos' => count(self::obtenerProximosAVencer($dias_anticipacion))
        ];
    }
    
    /**
     * Obtiene estadísticas de créditos
     * 
     * @param array $filtros Filtros opcionales
     * @return array Array con estadísticas
     */
    public static function obtenerEstadisticas($filtros = []) {
        global $pdo;
        
        try {
            $stats = [];
            
            $where = ['1=1'];
            $params = [];
            
            // Aplicar filtros
            if (isset($filtros['fecha_inicio'])) {
                $where[] = 'fecha_inicio >= ?';
                $params[] = $filtros['fecha_inicio'];
            }
            
            if (isset($filtros['fecha_fin'])) {
                $where[] = 'fecha_inicio <= ?';
                $params[] = $filtros['fecha_fin'];
            }
            
            $where_sql = implode(' AND ', $where);
            
            // Total de créditos por estado
            $sql = "SELECT estado, COUNT(*) as total, SUM(saldo_pendiente) as saldo_total
                    FROM creditos_clientes
                    WHERE $where_sql
                    GROUP BY estado";
            $stats['por_estado'] = db_query($sql, $params);
            
            // Créditos activos
            $stats['total_activos'] = db_count('creditos_clientes', 
                "$where_sql AND estado = 'activo'", $params);
            
            // Créditos liquidados
            $stats['total_liquidados'] = db_count('creditos_clientes', 
                "$where_sql AND estado = 'liquidado'", $params);
            
            // Créditos vencidos
            $stats['total_vencidos'] = db_count('creditos_clientes', 
                "$where_sql AND estado = 'vencido'", $params);
            
            // Monto total de saldo pendiente
            $sql = "SELECT COALESCE(SUM(saldo_pendiente), 0) as total
                    FROM creditos_clientes
                    WHERE $where_sql AND estado = 'activo'";
            $resultado = db_query_one($sql, $params);
            $stats['saldo_pendiente_total'] = $resultado ? (float)$resultado['total'] : 0;
            
            // Clientes con más créditos activos
            $sql = "SELECT c.nombre, c.telefono, COUNT(cc.id) as total_creditos,
                           SUM(cc.saldo_pendiente) as saldo_total
                    FROM creditos_clientes cc
                    INNER JOIN clientes c ON cc.cliente_id = c.id
                    WHERE cc.estado = 'activo'
                    GROUP BY c.id, c.nombre, c.telefono
                    ORDER BY saldo_total DESC
                    LIMIT 10";
            $stats['clientes_top'] = db_query($sql);
            
            // Promedio de cuotas
            $sql = "SELECT AVG(numero_cuotas) as promedio
                    FROM creditos_clientes
                    WHERE $where_sql";
            $resultado = db_query_one($sql, $params);
            $stats['promedio_cuotas'] = $resultado ? round((float)$resultado['promedio'], 1) : 0;
            
            return $stats;
            
        } catch (Exception $e) {
            registrar_error("Error al obtener estadísticas de créditos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Genera un plan de pagos para un crédito
     * 
     * @param float $monto_total Monto total del crédito
     * @param int $numero_cuotas Número de cuotas
     * @param string $fecha_inicio Fecha de inicio
     * @return array Array con el plan de pagos
     */
    public static function generarPlanPagos($monto_total, $numero_cuotas, $fecha_inicio = null) {
        if ($fecha_inicio === null) {
            $fecha_inicio = date('Y-m-d');
        }
        
        $cuota_semanal = round($monto_total / $numero_cuotas, 2);
        $plan = [];
        $saldo = $monto_total;
        
        for ($i = 1; $i <= $numero_cuotas; $i++) {
            $fecha_pago = date('Y-m-d', strtotime($fecha_inicio . " +{$i} weeks"));
            
            // Última cuota puede tener ajuste por redondeo
            if ($i === $numero_cuotas) {
                $cuota_semanal = $saldo;
            }
            
            $saldo -= $cuota_semanal;
            
            $plan[] = [
                'cuota_numero' => $i,
                'fecha_pago' => $fecha_pago,
                'monto_cuota' => $cuota_semanal,
                'saldo_restante' => max(0, $saldo)
            ];
        }
        
        return $plan;
    }
    
    /**
     * Obtiene resumen de un crédito
     * 
     * @param int $credito_id ID del crédito
     * @return array Array con resumen del crédito
     */
    public static function obtenerResumen($credito_id) {
        $credito = self::obtenerPorId($credito_id);
        
        if (!$credito) {
            return [];
        }
        
        $total_abonado = $credito['monto_total'] - $credito['saldo_pendiente'];
        $porcentaje_pagado = ($credito['monto_total'] > 0) 
            ? round(($total_abonado / $credito['monto_total']) * 100, 2)
            : 0;
        
        return [
            'credito' => $credito,
            'total_abonado' => $total_abonado,
            'porcentaje_pagado' => $porcentaje_pagado,
            'cuotas_restantes' => $credito['numero_cuotas'] - $credito['cuotas_pagadas'],
            'dias_atraso' => self::calcularDiasAtraso($credito['fecha_proximo_pago']),
            'esta_vencido' => self::calcularDiasAtraso($credito['fecha_proximo_pago']) > 0,
            'historial_abonos' => $credito['abonos']
        ];
    }
}
?>