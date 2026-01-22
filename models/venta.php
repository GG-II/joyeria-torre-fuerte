<?php
// ================================================
// MODELO: VENTA
// Sistema de Gestión - Joyería Torre Fuerte
// ================================================

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/funciones.php';
require_once __DIR__ . '/producto.php';
require_once __DIR__ . '/inventario.php';
require_once __DIR__ . '/cliente.php';

class Venta {
    
    // ========================================
    // MÉTODOS DE CONSULTA (SELECT)
    // ========================================
    
    /**
     * Lista ventas con filtros y paginación
     * 
     * @param array $filtros Array de filtros (fecha_inicio, fecha_fin, vendedor_id, cliente_id, sucursal_id, estado, tipo_venta)
     * @param int $pagina Número de página
     * @param int $por_pagina Registros por página
     * @return array Array de ventas
     */
    public static function listar($filtros = [], $pagina = 1, $por_pagina = 20) {
        global $pdo;
        
        $where = ['1=1'];
        $params = [];
        
        // Filtro por rango de fechas
        if (isset($filtros['fecha_inicio']) && !empty($filtros['fecha_inicio'])) {
            $where[] = 'v.fecha >= ?';
            $params[] = $filtros['fecha_inicio'];
        }
        
        if (isset($filtros['fecha_fin']) && !empty($filtros['fecha_fin'])) {
            $where[] = 'v.fecha <= ?';
            $params[] = $filtros['fecha_fin'];
        }
        
        // Filtro por vendedor
        if (isset($filtros['vendedor_id']) && !empty($filtros['vendedor_id'])) {
            $where[] = 'v.usuario_id = ?';
            $params[] = $filtros['vendedor_id'];
        }
        
        // Filtro por cliente
        if (isset($filtros['cliente_id']) && !empty($filtros['cliente_id'])) {
            $where[] = 'v.cliente_id = ?';
            $params[] = $filtros['cliente_id'];
        }
        
        // Filtro por sucursal
        if (isset($filtros['sucursal_id']) && !empty($filtros['sucursal_id'])) {
            $where[] = 'v.sucursal_id = ?';
            $params[] = $filtros['sucursal_id'];
        }
        
        // Filtro por estado
        if (isset($filtros['estado']) && !empty($filtros['estado'])) {
            $where[] = 'v.estado = ?';
            $params[] = $filtros['estado'];
        }
        
        // Filtro por tipo de venta
        if (isset($filtros['tipo_venta']) && !empty($filtros['tipo_venta'])) {
            $where[] = 'v.tipo_venta = ?';
            $params[] = $filtros['tipo_venta'];
        }
        
        // Búsqueda por número de venta
        if (isset($filtros['numero_venta']) && !empty($filtros['numero_venta'])) {
            $where[] = 'v.numero_venta LIKE ?';
            $params[] = '%' . $filtros['numero_venta'] . '%';
        }
        
        $where_sql = implode(' AND ', $where);
        
        // Calcular offset
        $offset = ($pagina - 1) * $por_pagina;
        
        $sql = "SELECT v.*,
                       c.nombre as cliente_nombre,
                       u.nombre as vendedor_nombre,
                       s.nombre as sucursal_nombre,
                       (SELECT COUNT(*) FROM detalle_ventas WHERE venta_id = v.id) as total_productos,
                       (SELECT COUNT(*) FROM formas_pago_venta WHERE venta_id = v.id) as total_formas_pago
                FROM ventas v
                LEFT JOIN clientes c ON v.cliente_id = c.id
                LEFT JOIN usuarios u ON v.usuario_id = u.id
                LEFT JOIN sucursales s ON v.sucursal_id = s.id
                WHERE $where_sql
                ORDER BY v.fecha DESC, v.hora DESC
                LIMIT ? OFFSET ?";
        
        $params[] = $por_pagina;
        $params[] = $offset;
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            registrar_error("Error al listar ventas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene una venta por su ID con todos sus detalles
     * 
     * @param int $id ID de la venta
     * @return array|false Venta completa o false
     */
    public static function obtenerPorId($id) {
        global $pdo;
        
        $sql = "SELECT v.*,
                       c.nombre as cliente_nombre,
                       c.nit as cliente_nit,
                       c.telefono as cliente_telefono,
                       u.nombre as vendedor_nombre,
                       s.nombre as sucursal_nombre
                FROM ventas v
                LEFT JOIN clientes c ON v.cliente_id = c.id
                LEFT JOIN usuarios u ON v.usuario_id = u.id
                LEFT JOIN sucursales s ON v.sucursal_id = s.id
                WHERE v.id = ?";
        
        try {
            $venta = db_query_one($sql, [$id]);
            
            if ($venta) {
                // Obtener detalle de productos
                $venta['detalles'] = self::obtenerDetalles($id);
                
                // Obtener formas de pago
                $venta['formas_pago'] = self::obtenerFormasPago($id);
                
                // Si es crédito, obtener info del crédito
                if ($venta['tipo_venta'] === 'credito') {
                    $venta['credito'] = db_query_one(
                        "SELECT * FROM creditos_clientes WHERE venta_id = ?",
                        [$id]
                    );
                }
            }
            
            return $venta;
            
        } catch (PDOException $e) {
            registrar_error("Error al obtener venta: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene una venta por su número
     * 
     * @param string $numero_venta Número de venta
     * @return array|false Venta o false
     */
    public static function obtenerPorNumero($numero_venta) {
        $venta = db_query_one(
            "SELECT * FROM ventas WHERE numero_venta = ?",
            [$numero_venta]
        );
        
        if ($venta) {
            return self::obtenerPorId($venta['id']);
        }
        
        return false;
    }
    
    /**
     * Obtiene los detalles (productos) de una venta
     * 
     * @param int $venta_id ID de la venta
     * @return array Array de detalles
     */
    public static function obtenerDetalles($venta_id) {
        global $pdo;
        
        $sql = "SELECT dv.*, 
                       p.codigo as producto_codigo,
                       p.nombre as producto_nombre,
                       p.imagen as producto_imagen
                FROM detalle_ventas dv
                INNER JOIN productos p ON dv.producto_id = p.id
                WHERE dv.venta_id = ?
                ORDER BY dv.id";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$venta_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            registrar_error("Error al obtener detalles de venta: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene las formas de pago de una venta
     * 
     * @param int $venta_id ID de la venta
     * @return array Array de formas de pago
     */
    public static function obtenerFormasPago($venta_id) {
        global $pdo;
        
        $sql = "SELECT * FROM formas_pago_venta WHERE venta_id = ? ORDER BY id";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$venta_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            registrar_error("Error al obtener formas de pago: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene ventas del día de una sucursal
     * 
     * @param int $sucursal_id ID de la sucursal
     * @param string $fecha Fecha (default: hoy)
     * @return array Array de ventas
     */
    public static function obtenerVentasDelDia($sucursal_id, $fecha = null) {
        if ($fecha === null) {
            $fecha = date('Y-m-d');
        }
        
        return self::listar([
            'sucursal_id' => $sucursal_id,
            'fecha_inicio' => $fecha,
            'fecha_fin' => $fecha,
            'estado' => 'completada'
        ], 1, 1000);
    }
    
    /**
     * Obtiene ventas de un vendedor en un rango de fechas
     * 
     * @param int $vendedor_id ID del vendedor
     * @param string $fecha_inicio Fecha inicio
     * @param string $fecha_fin Fecha fin
     * @return array Array de ventas
     */
    public static function obtenerPorVendedor($vendedor_id, $fecha_inicio = null, $fecha_fin = null) {
        $filtros = ['vendedor_id' => $vendedor_id];
        
        if ($fecha_inicio) {
            $filtros['fecha_inicio'] = $fecha_inicio;
        }
        
        if ($fecha_fin) {
            $filtros['fecha_fin'] = $fecha_fin;
        }
        
        return self::listar($filtros, 1, 1000);
    }
    
    /**
     * Obtiene ventas de un cliente
     * 
     * @param int $cliente_id ID del cliente
     * @param int $limite Límite de resultados
     * @return array Array de ventas
     */
    public static function obtenerPorCliente($cliente_id, $limite = 50) {
        return self::listar(['cliente_id' => $cliente_id], 1, $limite);
    }
    
    /**
     * Obtiene el total de ventas con filtros
     * 
     * @param array $filtros Array de filtros
     * @return int Total de ventas
     */
    public static function contarTotal($filtros = []) {
        $where = ['1=1'];
        $params = [];
        
        if (isset($filtros['fecha_inicio']) && !empty($filtros['fecha_inicio'])) {
            $where[] = 'fecha >= ?';
            $params[] = $filtros['fecha_inicio'];
        }
        
        if (isset($filtros['fecha_fin']) && !empty($filtros['fecha_fin'])) {
            $where[] = 'fecha <= ?';
            $params[] = $filtros['fecha_fin'];
        }
        
        if (isset($filtros['vendedor_id']) && !empty($filtros['vendedor_id'])) {
            $where[] = 'usuario_id = ?';
            $params[] = $filtros['vendedor_id'];
        }
        
        if (isset($filtros['sucursal_id']) && !empty($filtros['sucursal_id'])) {
            $where[] = 'sucursal_id = ?';
            $params[] = $filtros['sucursal_id'];
        }
        
        if (isset($filtros['estado']) && !empty($filtros['estado'])) {
            $where[] = 'estado = ?';
            $params[] = $filtros['estado'];
        }
        
        $where_sql = implode(' AND ', $where);
        
        return db_count('ventas', $where_sql, $params);
    }
    
    // ========================================
    // MÉTODOS DE CREACIÓN (INSERT)
    // ========================================
    
    /**
     * Crea una nueva venta con transacción completa
     * 
     * @param array $datos Datos de la venta
     *   - sucursal_id (int): ID de la sucursal
     *   - vendedor_id (int): ID del vendedor (usuario)
     *   - cliente_id (int|null): ID del cliente (null para venta mostrador)
     *   - productos (array): Array de productos con estructura:
     *       - producto_id (int)
     *       - cantidad (int)
     *       - precio_unitario (float) [opcional, se obtiene automáticamente]
     *       - tipo_precio (string) [opcional: publico, mayorista, descuento, especial]
     *   - formas_pago (array): Array de formas de pago (solo para tipo_venta = 'normal'):
     *       - forma_pago (string): efectivo, tarjeta_debito, tarjeta_credito, transferencia, cheque
     *       - monto (float)
     *       - referencia (string) [opcional]
     *   - descuento (float): Monto de descuento (default: 0)
     *   - tipo_venta (string): 'normal', 'credito', 'apartado' (default: 'normal')
     * @return int|false ID de venta creada o false
     */
    public static function crear($datos) {
        global $pdo;
        
        // Validar datos
        $errores = self::validar($datos);
        if (!empty($errores)) {
            registrar_error("Errores de validación al crear venta: " . implode(', ', $errores));
            return false;
        }
        
        try {
            $pdo->beginTransaction();
            
            // 1. Generar número de venta
            $numero_venta = self::generarNumeroVenta($datos['sucursal_id']);
            
            // 2. Calcular totales
            $totales = self::calcularTotales($datos['productos'], $datos['descuento'] ?? 0);
            
            // 3. Insertar venta principal
            $sql_venta = "INSERT INTO ventas (
                            numero_venta, fecha, hora, cliente_id, usuario_id, sucursal_id,
                            subtotal, descuento, tipo_venta, estado
                          ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $tipo_venta = $datos['tipo_venta'] ?? 'normal';
            $estado = ($tipo_venta === 'apartado') ? 'apartada' : 'completada';
            
            $params_venta = [
                $numero_venta,
                date('Y-m-d'),
                date('H:i:s'),
                $datos['cliente_id'] ?? null,
                $datos['vendedor_id'] ?? usuario_actual_id(),
                $datos['sucursal_id'],
                $totales['subtotal'],
                $datos['descuento'] ?? 0,
                $tipo_venta,
                $estado
            ];
            
            $venta_id = db_execute($sql_venta, $params_venta);
            
            if (!$venta_id) {
                throw new Exception("No se pudo crear la venta");
            }
            
            // 4. Insertar detalles de venta y actualizar inventario
            foreach ($datos['productos'] as $producto) {
                // Obtener información del producto
                $producto_info = Producto::obtenerPorId($producto['producto_id']);
                
                if (!$producto_info) {
                    throw new Exception("Producto no encontrado: {$producto['producto_id']}");
                }
                
                // Determinar tipo de precio
                $tipo_precio = $producto['tipo_precio'] ?? 'publico';
                
                // Si hay cliente, usar su tipo para determinar precio
                if (isset($datos['cliente_id']) && $datos['cliente_id']) {
                    $cliente = Cliente::obtenerPorId($datos['cliente_id']);
                    if ($cliente && $cliente['tipo_cliente'] === 'mayorista') {
                        $tipo_precio = 'mayorista';
                    }
                }
                
                // Obtener precio
                if (isset($producto['precio_unitario']) && $producto['precio_unitario'] > 0) {
                    // Usar precio especificado manualmente
                    $precio_unitario = $producto['precio_unitario'];
                } else {
                    // Intentar obtener precio del producto
                    if (method_exists('Producto', 'obtenerPrecio')) {
                        $precio_unitario = Producto::obtenerPrecio($producto['producto_id'], $tipo_precio);
                    } else {
                        // Si no existe el método, buscar en precios_producto
                        $precio_db = db_query_one(
                            "SELECT precio FROM precios_producto 
                             WHERE producto_id = ? AND tipo_precio = ? AND activo = 1",
                            [$producto['producto_id'], $tipo_precio]
                        );
                        
                        if ($precio_db) {
                            $precio_unitario = $precio_db['precio'];
                        } else {
                            throw new Exception("No se encontró precio {$tipo_precio} para el producto: {$producto_info['nombre']}");
                        }
                    }
                }
                
                if (!$precio_unitario || $precio_unitario <= 0) {
                    throw new Exception("Precio inválido para el producto: {$producto_info['nombre']}");
                }
                
                // Insertar detalle
                $sql_detalle = "INSERT INTO detalle_ventas (
                                   venta_id, producto_id, cantidad, precio_unitario, tipo_precio_aplicado
                               ) VALUES (?, ?, ?, ?, ?)";
                
                db_execute($sql_detalle, [
                    $venta_id,
                    $producto['producto_id'],
                    $producto['cantidad'],
                    $precio_unitario,
                    $tipo_precio
                ]);
                
                // Actualizar inventario (decrementar) - sin depender de clase Inventario
                $inventario_actual = db_query_one(
                    "SELECT id, cantidad FROM inventario WHERE producto_id = ? AND sucursal_id = ?",
                    [$producto['producto_id'], $datos['sucursal_id']]
                );
                
                if (!$inventario_actual) {
                    throw new Exception("No existe inventario del producto: {$producto_info['nombre']}");
                }
                
                if ($inventario_actual['cantidad'] < $producto['cantidad']) {
                    throw new Exception("Stock insuficiente del producto: {$producto_info['nombre']}");
                }
                
                // Actualizar cantidad
                $cantidad_anterior = $inventario_actual['cantidad'];
                $cantidad_nueva = $cantidad_anterior - $producto['cantidad'];
                
                $sql_update_inv = "UPDATE inventario SET cantidad = ? 
                                   WHERE producto_id = ? AND sucursal_id = ?";
                db_execute($sql_update_inv, [
                    $cantidad_nueva,
                    $producto['producto_id'],
                    $datos['sucursal_id']
                ]);
                
                // Registrar movimiento
                $sql_mov = "INSERT INTO movimientos_inventario 
                           (producto_id, sucursal_id, tipo_movimiento, cantidad, 
                            cantidad_anterior, cantidad_nueva, motivo, usuario_id, 
                            referencia_tipo, referencia_id)
                           VALUES (?, ?, 'venta', ?, ?, ?, ?, ?, 'venta', ?)";
                
                db_execute($sql_mov, [
                    $producto['producto_id'],
                    $datos['sucursal_id'],
                    $producto['cantidad'],
                    $cantidad_anterior,
                    $cantidad_nueva,
                    "Venta {$numero_venta}",
                    usuario_actual_id(),
                    $venta_id
                ]);
            }
            
            // 5. Insertar formas de pago (solo para ventas normales)
            if ($tipo_venta === 'normal') {
                if (empty($datos['formas_pago'])) {
                    throw new Exception("Las ventas normales deben incluir formas de pago");
                }
                
                foreach ($datos['formas_pago'] as $pago) {
                    $sql_pago = "INSERT INTO formas_pago_venta (
                                    venta_id, forma_pago, monto, referencia
                                 ) VALUES (?, ?, ?, ?)";
                    
                    db_execute($sql_pago, [
                        $venta_id,
                        $pago['forma_pago'],
                        $pago['monto'],
                        $pago['referencia'] ?? null
                    ]);
                }
                
                // 6. Registrar movimientos de caja
                $caja = db_query_one(
                    "SELECT id FROM cajas WHERE usuario_id = ? AND estado = 'abierta' ORDER BY id DESC LIMIT 1",
                    [usuario_actual_id()]
                );
                
                if ($caja) {
                    foreach ($datos['formas_pago'] as $pago) {
                        $sql_movimiento = "INSERT INTO movimientos_caja (
                                              caja_id, tipo_movimiento, categoria, concepto,
                                              monto, usuario_id, referencia_tipo, referencia_id
                                          ) VALUES (?, 'venta', 'ingreso', ?, ?, ?, 'venta', ?)";
                        
                        db_execute($sql_movimiento, [
                            $caja['id'],
                            "Venta {$numero_venta} - " . strtoupper(str_replace('_', ' ', $pago['forma_pago'])),
                            $pago['monto'],
                            usuario_actual_id(),
                            $venta_id
                        ]);
                    }
                }
            }
            
            // 7. Si es venta a crédito, crear registro en creditos_clientes
            if ($tipo_venta === 'credito') {
                if (empty($datos['cliente_id'])) {
                    throw new Exception("Las ventas a crédito requieren un cliente");
                }
                
                // Calcular número de cuotas (default: 4 cuotas semanales)
                $numero_cuotas = isset($datos['numero_cuotas']) ? $datos['numero_cuotas'] : 4;
                $cuota_semanal = round($totales['total'] / $numero_cuotas, 2);
                $fecha_inicio = date('Y-m-d');
                $fecha_proximo_pago = date('Y-m-d', strtotime('+7 days'));
                
                $sql_credito = "INSERT INTO creditos_clientes (
                                   cliente_id, venta_id, monto_total, saldo_pendiente,
                                   cuota_semanal, numero_cuotas, cuotas_pagadas,
                                   fecha_inicio, fecha_proximo_pago, estado, dias_atraso
                                ) VALUES (?, ?, ?, ?, ?, ?, 0, ?, ?, 'activo', 0)";
                
                db_execute($sql_credito, [
                    $datos['cliente_id'],
                    $venta_id,
                    $totales['total'],
                    $totales['total'], // saldo_pendiente inicial = monto_total
                    $cuota_semanal,
                    $numero_cuotas,
                    $fecha_inicio,
                    $fecha_proximo_pago
                ]);
            }
            
            // 8. Auditoría
            $detalles_auditoria = [
                'numero_venta' => $numero_venta,
                'tipo_venta' => $tipo_venta,
                'total' => $totales['total'],
                'productos' => count($datos['productos'])
            ];
            
            registrar_auditoria('INSERT', 'ventas', $venta_id, 
                "Venta creada: {$numero_venta} - Total: " . formato_dinero($totales['total']) . " ({$tipo_venta})");
            
            $pdo->commit();
            return $venta_id;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            registrar_error("Error al crear venta: " . $e->getMessage());
            return false;
        }
    }
    
    // ========================================
    // MÉTODOS DE ACTUALIZACIÓN (UPDATE)
    // ========================================
    
    /**
     * Anula una venta (soft delete con reversión de inventario)
     * 
     * @param int $id ID de la venta
     * @param string $motivo Motivo de anulación
     * @return bool
     */
    public static function anular($id, $motivo = '') {
        global $pdo;
        
        try {
            $venta = self::obtenerPorId($id);
            
            if (!$venta) {
                registrar_error("Venta no encontrada: ID $id");
                return false;
            }
            
            // No se puede anular si ya está anulada
            if ($venta['estado'] === 'anulada') {
                registrar_error("La venta ya está anulada: {$venta['numero_venta']}");
                return false;
            }
            
            // Verificar si es venta a crédito con abonos
            if ($venta['tipo_venta'] === 'credito' && isset($venta['credito'])) {
                $total_abonos = db_count('abonos_creditos', 'credito_id = ?', [$venta['credito']['id']]);
                
                if ($total_abonos > 0) {
                    registrar_error("No se puede anular venta a crédito con abonos realizados: {$venta['numero_venta']}");
                    return false;
                }
            }
            
            $pdo->beginTransaction();
            
            // 1. Cambiar estado de la venta
            $sql = "UPDATE ventas SET 
                       estado = 'anulada',
                       motivo_anulacion = ?
                    WHERE id = ?";
            
            $motivo_completo = "[" . date('Y-m-d H:i:s') . "] Venta ANULADA";
            if ($motivo) {
                $motivo_completo .= " - Motivo: {$motivo}";
            }
            $motivo_completo .= " - Usuario: " . usuario_actual_nombre();
            
            db_execute($sql, [$motivo_completo, $id]);
            
            // 2. Devolver stock al inventario
            $detalles = $venta['detalles'];
            
            foreach ($detalles as $detalle) {
                // Verificar si existe el registro de inventario
                $inventario_actual = db_query_one(
                    "SELECT cantidad FROM inventario WHERE producto_id = ? AND sucursal_id = ?",
                    [$detalle['producto_id'], $venta['sucursal_id']]
                );
                
                if ($inventario_actual) {
                    // Actualizar inventario existente
                    $sql_inventario = "UPDATE inventario 
                                       SET cantidad = cantidad + ? 
                                       WHERE producto_id = ? AND sucursal_id = ?";
                    db_execute($sql_inventario, [
                        $detalle['cantidad'],
                        $detalle['producto_id'],
                        $venta['sucursal_id']
                    ]);
                } else {
                    // Crear registro de inventario si no existe
                    $sql_inventario = "INSERT INTO inventario (producto_id, sucursal_id, cantidad) 
                                       VALUES (?, ?, ?)";
                    db_execute($sql_inventario, [
                        $detalle['producto_id'],
                        $venta['sucursal_id'],
                        $detalle['cantidad']
                    ]);
                }
                
                // Registrar movimiento
                $sql_mov = "INSERT INTO movimientos_inventario 
                           (producto_id, sucursal_id, tipo_movimiento, cantidad, 
                            cantidad_anterior, cantidad_nueva, motivo, usuario_id, 
                            referencia_tipo, referencia_id)
                           VALUES (?, ?, 'ajuste', ?, ?, ?, ?, ?, 'venta_anulada', ?)";
                
                $cantidad_anterior = $inventario_actual ? $inventario_actual['cantidad'] : 0;
                $cantidad_nueva = $cantidad_anterior + $detalle['cantidad'];
                
                db_execute($sql_mov, [
                    $detalle['producto_id'],
                    $venta['sucursal_id'],
                    $detalle['cantidad'],
                    $cantidad_anterior,
                    $cantidad_nueva,
                    "Anulación venta {$venta['numero_venta']}",
                    usuario_actual_id(),
                    $id
                ]);
            }
            
            // 3. Si es venta normal, registrar egreso en caja
            if ($venta['tipo_venta'] === 'normal') {
                $caja = db_query_one(
                    "SELECT id FROM cajas WHERE usuario_id = ? AND estado = 'abierta' ORDER BY id DESC LIMIT 1",
                    [usuario_actual_id()]
                );
                
                if ($caja) {
                    $sql_egreso = "INSERT INTO movimientos_caja (
                                      caja_id, tipo_movimiento, categoria, concepto,
                                      monto, usuario_id, referencia_tipo, referencia_id
                                  ) VALUES (?, 'venta', 'egreso', ?, ?, ?, 'venta_anulada', ?)";
                    
                    db_execute($sql_egreso, [
                        $caja['id'],
                        "ANULACIÓN Venta {$venta['numero_venta']}",
                        $venta['total'],
                        usuario_actual_id(),
                        $id
                    ]);
                }
            }
            
            // 4. Si es crédito, anular el crédito
            if ($venta['tipo_venta'] === 'credito' && isset($venta['credito'])) {
                $sql_credito = "UPDATE creditos_clientes 
                                SET estado = 'liquidado',
                                    saldo_pendiente = 0,
                                    fecha_liquidacion = CURDATE()
                                WHERE id = ?";
                
                db_execute($sql_credito, [$venta['credito']['id']]);
            }
            
            // 5. Auditoría
            registrar_auditoria('DELETE', 'ventas', $id, 
                "Venta anulada: {$venta['numero_venta']} - Total: " . formato_dinero($venta['total']) . " - Motivo: {$motivo}");
            
            $pdo->commit();
            return true;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            registrar_error("Error al anular venta: " . $e->getMessage());
            return false;
        }
    }
    
    // ========================================
    // MÉTODOS DE VALIDACIÓN
    // ========================================
    
    /**
     * Valida los datos de una venta
     * 
     * @param array $datos Datos a validar
     * @return array Array de errores
     */
    public static function validar($datos) {
        $errores = [];
        
        // Sucursal requerida
        if (empty($datos['sucursal_id'])) {
            $errores[] = 'La sucursal es requerida';
        } elseif (!db_exists('sucursales', 'id = ? AND activo = 1', [$datos['sucursal_id']])) {
            $errores[] = 'La sucursal no existe o está inactiva';
        }
        
        // Vendedor (si no se proporciona, se usa el usuario actual)
        $vendedor_id = $datos['vendedor_id'] ?? usuario_actual_id();
        if (!db_exists('usuarios', 'id = ? AND activo = 1', [$vendedor_id])) {
            $errores[] = 'El vendedor no existe o está inactivo';
        }
        
        // Cliente (opcional, pero si se proporciona debe existir)
        if (isset($datos['cliente_id']) && $datos['cliente_id']) {
            if (!Cliente::existe($datos['cliente_id'])) {
                $errores[] = 'El cliente no existe';
            }
        }
        
        // Productos requeridos
        if (empty($datos['productos']) || !is_array($datos['productos'])) {
            $errores[] = 'Debe incluir al menos un producto';
        } else {
            // Validar cada producto
            foreach ($datos['productos'] as $i => $producto) {
                if (empty($producto['producto_id'])) {
                    $errores[] = "Producto #{$i}: ID requerido";
                }
                
                if (empty($producto['cantidad']) || $producto['cantidad'] <= 0) {
                    $errores[] = "Producto #{$i}: Cantidad inválida";
                }
                
                // Validar stock disponible
                if (!empty($producto['producto_id']) && !empty($producto['cantidad']) && !empty($datos['sucursal_id'])) {
                    // Verificar stock usando query directa
                    $stock = db_query_one(
                        "SELECT cantidad FROM inventario WHERE producto_id = ? AND sucursal_id = ?",
                        [$producto['producto_id'], $datos['sucursal_id']]
                    );
                    
                    if ($stock && $stock['cantidad'] < $producto['cantidad']) {
                        $producto_info = Producto::obtenerPorId($producto['producto_id']);
                        $errores[] = "Stock insuficiente de '{$producto_info['nombre']}': {$stock['cantidad']} disponible, {$producto['cantidad']} requerido";
                    }
                }
            }
        }
        
        // Tipo de venta
        $tipo_venta = $datos['tipo_venta'] ?? 'normal';
        if (!in_array($tipo_venta, ['normal', 'credito', 'apartado'])) {
            $errores[] = 'Tipo de venta inválido';
        }
        
        // Validar formas de pago (solo para ventas normales)
        if ($tipo_venta === 'normal') {
            if (empty($datos['formas_pago']) || !is_array($datos['formas_pago'])) {
                $errores[] = 'Las ventas normales deben incluir formas de pago';
            } else {
                // Calcular total esperado
                $totales = self::calcularTotales($datos['productos'], $datos['descuento'] ?? 0);
                
                // Validar formas de pago
                $validacion_pagos = self::validarFormasPago($datos['formas_pago'], $totales['total']);
                if (!$validacion_pagos['valido']) {
                    $errores[] = $validacion_pagos['error'];
                }
            }
            
            // Verificar que hay caja abierta
            $caja_abierta = db_exists('cajas', 'usuario_id = ? AND estado = ?', [usuario_actual_id(), 'abierta']);
            if (!$caja_abierta) {
                $errores[] = 'No hay caja abierta. Debe abrir caja antes de realizar ventas';
            }
        }
        
        // Validar venta a crédito
        if ($tipo_venta === 'credito') {
            // Cliente obligatorio
            if (empty($datos['cliente_id'])) {
                $errores[] = 'Las ventas a crédito requieren un cliente';
            } else {
                // Validar límite de crédito
                $totales = self::calcularTotales($datos['productos'], $datos['descuento'] ?? 0);
                $validacion_credito = Cliente::validarLimiteCredito($datos['cliente_id'], $totales['total']);
                
                if (!$validacion_credito['valido']) {
                    $errores[] = $validacion_credito['mensaje'];
                }
            }
        }
        
        // Validar descuento
        if (isset($datos['descuento']) && $datos['descuento'] !== '') {
            if (!is_numeric($datos['descuento']) || $datos['descuento'] < 0) {
                $errores[] = 'El descuento debe ser un número positivo';
            }
        }
        
        return $errores;
    }
    
    /**
     * Valida que las formas de pago sumen el total exacto
     * 
     * @param array $formas_pago Array de formas de pago
     * @param float $total Total esperado
     * @return array ['valido' => bool, 'error' => string]
     */
    public static function validarFormasPago($formas_pago, $total) {
        if (empty($formas_pago)) {
            return [
                'valido' => false,
                'error' => 'Debe incluir al menos una forma de pago'
            ];
        }
        
        $suma_pagos = 0;
        $formas_validas = ['efectivo', 'tarjeta_debito', 'tarjeta_credito', 'transferencia', 'cheque'];
        
        foreach ($formas_pago as $pago) {
            // Validar estructura
            if (empty($pago['forma_pago'])) {
                return [
                    'valido' => false,
                    'error' => 'Forma de pago inválida: falta especificar tipo'
                ];
            }
            
            if (!in_array($pago['forma_pago'], $formas_validas)) {
                return [
                    'valido' => false,
                    'error' => "Forma de pago inválida: {$pago['forma_pago']}"
                ];
            }
            
            if (empty($pago['monto']) || $pago['monto'] <= 0) {
                return [
                    'valido' => false,
                    'error' => 'Todas las formas de pago deben tener un monto mayor a cero'
                ];
            }
            
            $suma_pagos += $pago['monto'];
        }
        
        // Validar que suma = total (con tolerancia de 0.01 por redondeo)
        if (abs($suma_pagos - $total) > 0.01) {
            return [
                'valido' => false,
                'error' => "Formas de pago (" . formato_dinero($suma_pagos) . ") no coinciden con total (" . formato_dinero($total) . ")"
            ];
        }
        
        return ['valido' => true, 'error' => ''];
    }
    
    /**
     * Verifica si una venta existe
     * 
     * @param int $id ID de la venta
     * @return bool
     */
    public static function existe($id) {
        return db_exists('ventas', 'id = ?', [$id]);
    }
    
    /**
     * Verifica si una venta puede ser anulada
     * 
     * @param int $id ID de la venta
     * @return array ['puede' => bool, 'razon' => string]
     */
    public static function puedeAnular($id) {
        $venta = self::obtenerPorId($id);
        
        if (!$venta) {
            return ['puede' => false, 'razon' => 'Venta no encontrada'];
        }
        
        if ($venta['estado'] === 'anulada') {
            return ['puede' => false, 'razon' => 'La venta ya está anulada'];
        }
        
        // Si es crédito, verificar que no tenga abonos
        if ($venta['tipo_venta'] === 'credito' && isset($venta['credito'])) {
            $total_abonos = db_count('abonos_creditos', 'credito_id = ?', [$venta['credito']['id']]);
            
            if ($total_abonos > 0) {
                return ['puede' => false, 'razon' => 'No se puede anular venta a crédito con abonos realizados'];
            }
        }
        
        return ['puede' => true, 'razon' => ''];
    }
    
    // ========================================
    // MÉTODOS AUXILIARES
    // ========================================
    
    /**
     * Genera el número consecutivo de venta
     * 
     * @param int $sucursal_id ID de la sucursal
     * @return string Número de venta (formato: V-01-2026-0001)
     */
    public static function generarNumeroVenta($sucursal_id) {
        global $pdo;
        
        $anio = date('Y');
        $prefijo = 'V-' . str_pad($sucursal_id, 2, '0', STR_PAD_LEFT) . '-' . $anio . '-';
        
        // Obtener último número del año para esta sucursal con FOR UPDATE para evitar duplicados
        $sql = "SELECT numero_venta FROM ventas 
                WHERE numero_venta LIKE ? 
                ORDER BY id DESC LIMIT 1
                FOR UPDATE";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$prefijo . '%']);
            $resultado = $stmt->fetch();
            
            if ($resultado) {
                $numero = (int)substr($resultado['numero_venta'], -4) + 1;
            } else {
                $numero = 1;
            }
            
            return $prefijo . str_pad($numero, 4, '0', STR_PAD_LEFT);
            
        } catch (PDOException $e) {
            registrar_error("Error al generar número de venta: " . $e->getMessage());
            return $prefijo . '0001';
        }
    }
    
    /**
     * Calcula subtotal y total de una venta
     * 
     * @param array $productos Array de productos con precio_unitario y cantidad
     * @param float $descuento Descuento a aplicar
     * @return array ['subtotal' => float, 'total' => float]
     */
    public static function calcularTotales($productos, $descuento = 0) {
        $subtotal = 0;
        
        foreach ($productos as $producto) {
            $precio = $producto['precio_unitario'] ?? 0;
            $cantidad = $producto['cantidad'] ?? 0;
            $subtotal += ($precio * $cantidad);
        }
        
        $total = $subtotal - $descuento;
        
        return [
            'subtotal' => round($subtotal, 2),
            'total' => round($total, 2)
        ];
    }
    
    /**
     * Obtiene estadísticas de ventas
     * 
     * @param array $filtros Filtros de fecha, sucursal, etc.
     * @return array Array con estadísticas
     */
    public static function obtenerEstadisticas($filtros = []) {
        global $pdo;
        
        try {
            $stats = [];
            
            $where = ["estado != 'anulada'"];
            $params = [];
            
            // Aplicar filtros
            if (isset($filtros['fecha_inicio'])) {
                $where[] = 'fecha >= ?';
                $params[] = $filtros['fecha_inicio'];
            }
            
            if (isset($filtros['fecha_fin'])) {
                $where[] = 'fecha <= ?';
                $params[] = $filtros['fecha_fin'];
            }
            
            if (isset($filtros['sucursal_id'])) {
                $where[] = 'sucursal_id = ?';
                $params[] = $filtros['sucursal_id'];
            }
            
            $where_sql = implode(' AND ', $where);
            
            // Total de ventas
            $stats['total_ventas'] = db_count('ventas', $where_sql, $params);
            
            // Monto total
            $sql = "SELECT COALESCE(SUM(total), 0) as monto_total
                    FROM ventas
                    WHERE $where_sql";
            $resultado = db_query_one($sql, $params);
            $stats['monto_total'] = $resultado ? (float)$resultado['monto_total'] : 0;
            
            // Ticket promedio
            $stats['ticket_promedio'] = $stats['total_ventas'] > 0 
                ? $stats['monto_total'] / $stats['total_ventas'] 
                : 0;
            
            // Por tipo de venta
            $sql = "SELECT tipo_venta, COUNT(*) as total, SUM(total) as monto
                    FROM ventas
                    WHERE $where_sql
                    GROUP BY tipo_venta";
            $stats['por_tipo'] = db_query($sql, $params);
            
            // Productos más vendidos
            $sql = "SELECT p.nombre, p.codigo, SUM(dv.cantidad) as total_vendido, 
                           SUM(dv.subtotal) as monto_total
                    FROM detalle_ventas dv
                    INNER JOIN productos p ON dv.producto_id = p.id
                    INNER JOIN ventas v ON dv.venta_id = v.id
                    WHERE $where_sql
                    GROUP BY p.id, p.nombre, p.codigo
                    ORDER BY total_vendido DESC
                    LIMIT 10";
            $stats['productos_top'] = db_query($sql, $params);
            
            // Ventas por vendedor
            $sql = "SELECT u.nombre as vendedor, COUNT(v.id) as total_ventas, 
                           SUM(v.total) as monto_total
                    FROM ventas v
                    INNER JOIN usuarios u ON v.usuario_id = u.id
                    WHERE $where_sql
                    GROUP BY u.id, u.nombre
                    ORDER BY monto_total DESC";
            $stats['por_vendedor'] = db_query($sql, $params);
            
            return $stats;
            
        } catch (Exception $e) {
            registrar_error("Error al obtener estadísticas de ventas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene top productos vendidos en un período
     * 
     * @param string $fecha_inicio Fecha inicio
     * @param string $fecha_fin Fecha fin
     * @param int $limite Límite de resultados
     * @return array Array de productos
     */
    public static function obtenerTopProductos($fecha_inicio = null, $fecha_fin = null, $limite = 10) {
        global $pdo;
        
        $where = ["v.estado != 'anulada'"];
        $params = [];
        
        if ($fecha_inicio) {
            $where[] = 'v.fecha >= ?';
            $params[] = $fecha_inicio;
        }
        
        if ($fecha_fin) {
            $where[] = 'v.fecha <= ?';
            $params[] = $fecha_fin;
        }
        
        $where_sql = implode(' AND ', $where);
        
        $sql = "SELECT p.nombre, p.codigo, c.nombre as categoria,
                       SUM(dv.cantidad) as total_vendido,
                       SUM(dv.subtotal) as monto_total
                FROM detalle_ventas dv
                INNER JOIN productos p ON dv.producto_id = p.id
                INNER JOIN categorias c ON p.categoria_id = c.id
                INNER JOIN ventas v ON dv.venta_id = v.id
                WHERE $where_sql
                GROUP BY p.id, p.nombre, p.codigo, c.nombre
                ORDER BY total_vendido DESC
                LIMIT ?";
        
        $params[] = $limite;
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            registrar_error("Error al obtener top productos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene estadísticas de un vendedor
     * 
     * @param int $vendedor_id ID del vendedor
     * @param string $fecha_inicio Fecha inicio (opcional)
     * @param string $fecha_fin Fecha fin (opcional)
     * @return array Array con estadísticas del vendedor
     */
    public static function obtenerEstadisticasVendedor($vendedor_id, $fecha_inicio = null, $fecha_fin = null) {
        $filtros = ['vendedor_id' => $vendedor_id];
        
        if ($fecha_inicio) {
            $filtros['fecha_inicio'] = $fecha_inicio;
        }
        
        if ($fecha_fin) {
            $filtros['fecha_fin'] = $fecha_fin;
        }
        
        $stats = self::obtenerEstadisticas($filtros);
        
        // Agregar información del vendedor
        $vendedor = db_query_one("SELECT nombre, email FROM usuarios WHERE id = ?", [$vendedor_id]);
        $stats['vendedor'] = $vendedor;
        
        return $stats;
    }
}
?>