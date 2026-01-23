<?php
/**
 * Modelo TransferenciaInventario
 * 
 * Gestión completa de transferencias de inventario entre sucursales:
 * - Creación de transferencias con detalle de productos
 * - Estados: pendiente, completada, cancelada
 * - Historial y seguimiento de transferencias
 * - Consultas y reportes
 * 
 * NOTA: Este modelo complementa la funcionalidad básica en inventario.php
 * proporcionando acceso directo al historial y gestión avanzada de transferencias.
 * 
 * @author Sistema Joyería Torre Fuerte
 * @version 1.0
 * @date 2026-01-22
 */

class TransferenciaInventario {
    
    // Estados de transferencia
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_COMPLETADA = 'completada';
    const ESTADO_CANCELADA = 'cancelada';
    
    /**
     * Crea una nueva transferencia de inventario
     * 
     * @param int $sucursal_origen_id ID de sucursal origen
     * @param int $sucursal_destino_id ID de sucursal destino
     * @param array $productos Array de productos: [['producto_id' => X, 'cantidad' => Y], ...]
     * @param string $observaciones Observaciones de la transferencia
     * @return int|false ID de la transferencia creada o false
     */
    public static function crear($sucursal_origen_id, $sucursal_destino_id, $productos, $observaciones = null) {
        global $pdo;
        
        try {
            // Validaciones
            if ($sucursal_origen_id == $sucursal_destino_id) {
                throw new Exception('La sucursal origen y destino no pueden ser la misma');
            }
            
            if (empty($productos)) {
                throw new Exception('Debe incluir al menos un producto en la transferencia');
            }
            
            // Iniciar transacción
            $pdo->beginTransaction();
            
            // Crear transferencia
            $sql = "INSERT INTO transferencias_inventario (
                        sucursal_origen_id, sucursal_destino_id, 
                        usuario_id, observaciones, estado
                    ) VALUES (?, ?, ?, ?, ?)";
            
            $resultado = db_execute($sql, [
                $sucursal_origen_id,
                $sucursal_destino_id,
                usuario_actual_id(),
                $observaciones,
                self::ESTADO_PENDIENTE
            ]);
            
            if (!$resultado) {
                throw new Exception('Error al crear la transferencia');
            }
            
            $transferencia_id = $resultado;
            
            // Insertar detalle de productos
            foreach ($productos as $producto) {
                if (!isset($producto['producto_id']) || !isset($producto['cantidad'])) {
                    throw new Exception('Formato de producto inválido');
                }
                
                if ($producto['cantidad'] <= 0) {
                    throw new Exception('La cantidad debe ser mayor a 0');
                }
                
                $sql_detalle = "INSERT INTO detalle_transferencias_inventario (
                                    transferencia_id, producto_id, cantidad
                                ) VALUES (?, ?, ?)";
                
                db_execute($sql_detalle, [
                    $transferencia_id,
                    $producto['producto_id'],
                    $producto['cantidad']
                ]);
            }
            
            // Confirmar transacción
            $pdo->commit();
            
            // Registrar en auditoría
            registrar_auditoria(
                'transferencias_inventario',
                'INSERT',
                $transferencia_id,
                "Transferencia creada: Sucursal $sucursal_origen_id → $sucursal_destino_id - " . count($productos) . " productos"
            );
            
            return $transferencia_id;
            
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            registrar_error("Error al crear transferencia: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Completa una transferencia (ejecuta el movimiento de inventario)
     * 
     * @param int $id ID de la transferencia
     * @return bool
     */
    public static function completar($id) {
        global $pdo;
        
        try {
            // Obtener transferencia
            $transferencia = self::obtenerPorId($id);
            if (!$transferencia) {
                throw new Exception('Transferencia no encontrada');
            }
            
            if ($transferencia['estado'] !== self::ESTADO_PENDIENTE) {
                throw new Exception('Solo se pueden completar transferencias pendientes');
            }
            
            // Iniciar transacción
            $pdo->beginTransaction();
            
            // Obtener productos de la transferencia
            $productos = self::obtenerDetalle($id);
            
            // Verificar stock en origen
            foreach ($productos as $producto) {
                $stock = db_query_one(
                    "SELECT cantidad FROM inventario 
                     WHERE producto_id = ? AND sucursal_id = ?",
                    [$producto['producto_id'], $transferencia['sucursal_origen_id']]
                );
                
                if (!$stock || $stock['cantidad'] < $producto['cantidad']) {
                    throw new Exception("Stock insuficiente para: {$producto['producto_nombre']}");
                }
            }
            
            // Ejecutar movimientos de inventario
            foreach ($productos as $producto) {
                // Descontar de origen
                $sql_origen = "UPDATE inventario 
                              SET cantidad = cantidad - ? 
                              WHERE producto_id = ? AND sucursal_id = ?";
                
                db_execute($sql_origen, [
                    $producto['cantidad'],
                    $producto['producto_id'],
                    $transferencia['sucursal_origen_id']
                ]);
                
                // Registrar movimiento de salida
                db_execute(
                    "INSERT INTO movimientos_inventario 
                     (producto_id, sucursal_id, tipo_movimiento, cantidad, usuario_id, referencia)
                     VALUES (?, ?, 'transferencia_salida', ?, ?, ?)",
                    [
                        $producto['producto_id'],
                        $transferencia['sucursal_origen_id'],
                        $producto['cantidad'],
                        usuario_actual_id(),
                        "Transferencia #{$id}"
                    ]
                );
                
                // Sumar a destino (crear si no existe)
                $stock_destino = db_query_one(
                    "SELECT id FROM inventario 
                     WHERE producto_id = ? AND sucursal_id = ?",
                    [$producto['producto_id'], $transferencia['sucursal_destino_id']]
                );
                
                if ($stock_destino) {
                    db_execute(
                        "UPDATE inventario 
                         SET cantidad = cantidad + ? 
                         WHERE producto_id = ? AND sucursal_id = ?",
                        [
                            $producto['cantidad'],
                            $producto['producto_id'],
                            $transferencia['sucursal_destino_id']
                        ]
                    );
                } else {
                    db_execute(
                        "INSERT INTO inventario (producto_id, sucursal_id, cantidad, stock_minimo)
                         VALUES (?, ?, ?, 0)",
                        [
                            $producto['producto_id'],
                            $transferencia['sucursal_destino_id'],
                            $producto['cantidad']
                        ]
                    );
                }
                
                // Registrar movimiento de entrada
                db_execute(
                    "INSERT INTO movimientos_inventario 
                     (producto_id, sucursal_id, tipo_movimiento, cantidad, usuario_id, referencia)
                     VALUES (?, ?, 'transferencia_entrada', ?, ?, ?)",
                    [
                        $producto['producto_id'],
                        $transferencia['sucursal_destino_id'],
                        $producto['cantidad'],
                        usuario_actual_id(),
                        "Transferencia #{$id}"
                    ]
                );
            }
            
            // Actualizar estado de transferencia
            db_execute(
                "UPDATE transferencias_inventario 
                 SET estado = ?, fecha_completado = NOW() 
                 WHERE id = ?",
                [self::ESTADO_COMPLETADA, $id]
            );
            
            // Confirmar transacción
            $pdo->commit();
            
            // Registrar en auditoría
            registrar_auditoria(
                'transferencias_inventario',
                'UPDATE',
                $id,
                "Transferencia completada - " . count($productos) . " productos transferidos"
            );
            
            return true;
            
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            registrar_error("Error al completar transferencia: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cancela una transferencia
     * 
     * @param int $id ID de la transferencia
     * @param string $motivo Motivo de cancelación
     * @return bool
     */
    public static function cancelar($id, $motivo = null) {
        try {
            $transferencia = self::obtenerPorId($id);
            if (!$transferencia) {
                throw new Exception('Transferencia no encontrada');
            }
            
            if ($transferencia['estado'] !== self::ESTADO_PENDIENTE) {
                throw new Exception('Solo se pueden cancelar transferencias pendientes');
            }
            
            $sql = "UPDATE transferencias_inventario 
                    SET estado = ?, observaciones = CONCAT(COALESCE(observaciones, ''), '\nCANCELADA: ', ?)
                    WHERE id = ?";
            
            $resultado = db_execute($sql, [
                self::ESTADO_CANCELADA,
                $motivo ?? 'Sin motivo especificado',
                $id
            ]);
            
            if ($resultado) {
                registrar_auditoria(
                    'transferencias_inventario',
                    'UPDATE',
                    $id,
                    "Transferencia cancelada: " . ($motivo ?? 'Sin motivo')
                );
            }
            
            return $resultado;
            
        } catch (Exception $e) {
            registrar_error("Error al cancelar transferencia: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene una transferencia por ID
     * 
     * @param int $id ID de la transferencia
     * @return array|false Datos de la transferencia o false
     */
    public static function obtenerPorId($id) {
        try {
            $sql = "SELECT 
                        t.*,
                        so.nombre as sucursal_origen_nombre,
                        sd.nombre as sucursal_destino_nombre,
                        u.nombre as usuario_nombre
                    FROM transferencias_inventario t
                    INNER JOIN sucursales so ON t.sucursal_origen_id = so.id
                    INNER JOIN sucursales sd ON t.sucursal_destino_id = sd.id
                    INNER JOIN usuarios u ON t.usuario_id = u.id
                    WHERE t.id = ?";
            
            return db_query_one($sql, [$id]);
            
        } catch (Exception $e) {
            registrar_error("Error al obtener transferencia: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene el detalle de productos de una transferencia
     * 
     * @param int $transferencia_id ID de la transferencia
     * @return array Lista de productos
     */
    public static function obtenerDetalle($transferencia_id) {
        try {
            $sql = "SELECT 
                        d.*,
                        p.nombre as producto_nombre,
                        p.codigo as producto_codigo
                    FROM detalle_transferencias_inventario d
                    INNER JOIN productos p ON d.producto_id = p.id
                    WHERE d.transferencia_id = ?
                    ORDER BY p.nombre ASC";
            
            return db_query($sql, [$transferencia_id]);
            
        } catch (Exception $e) {
            registrar_error("Error al obtener detalle de transferencia: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Lista transferencias con filtros
     * 
     * @param array $filtros Filtros: estado, sucursal_origen_id, sucursal_destino_id, fecha_desde, fecha_hasta
     * @return array Lista de transferencias
     */
    public static function listar($filtros = []) {
        try {
            $where = [];
            $params = [];
            
            // Filtro por estado
            if (isset($filtros['estado']) && !empty($filtros['estado'])) {
                $where[] = "t.estado = ?";
                $params[] = $filtros['estado'];
            }
            
            // Filtro por sucursal origen
            if (isset($filtros['sucursal_origen_id']) && $filtros['sucursal_origen_id'] > 0) {
                $where[] = "t.sucursal_origen_id = ?";
                $params[] = $filtros['sucursal_origen_id'];
            }
            
            // Filtro por sucursal destino
            if (isset($filtros['sucursal_destino_id']) && $filtros['sucursal_destino_id'] > 0) {
                $where[] = "t.sucursal_destino_id = ?";
                $params[] = $filtros['sucursal_destino_id'];
            }
            
            // Filtro por rango de fechas
            if (isset($filtros['fecha_desde']) && !empty($filtros['fecha_desde'])) {
                $where[] = "DATE(t.fecha_creacion) >= ?";
                $params[] = $filtros['fecha_desde'];
            }
            
            if (isset($filtros['fecha_hasta']) && !empty($filtros['fecha_hasta'])) {
                $where[] = "DATE(t.fecha_creacion) <= ?";
                $params[] = $filtros['fecha_hasta'];
            }
            
            $where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            
            $sql = "SELECT 
                        t.id,
                        t.estado,
                        t.fecha_creacion,
                        t.fecha_completado,
                        so.nombre as sucursal_origen_nombre,
                        sd.nombre as sucursal_destino_nombre,
                        u.nombre as usuario_nombre,
                        (SELECT COUNT(*) FROM detalle_transferencias_inventario WHERE transferencia_id = t.id) as cantidad_productos
                    FROM transferencias_inventario t
                    INNER JOIN sucursales so ON t.sucursal_origen_id = so.id
                    INNER JOIN sucursales sd ON t.sucursal_destino_id = sd.id
                    INNER JOIN usuarios u ON t.usuario_id = u.id
                    $where_sql
                    ORDER BY t.fecha_creacion DESC";
            
            return db_query($sql, $params);
            
        } catch (Exception $e) {
            registrar_error("Error al listar transferencias: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene transferencias pendientes
     * 
     * @return array Lista de transferencias pendientes
     */
    public static function obtenerPendientes() {
        return self::listar(['estado' => self::ESTADO_PENDIENTE]);
    }
    
    /**
     * Obtiene estadísticas de transferencias
     * 
     * @param string $fecha_desde Fecha desde (opcional)
     * @param string $fecha_hasta Fecha hasta (opcional)
     * @return array Estadísticas
     */
    public static function obtenerEstadisticas($fecha_desde = null, $fecha_hasta = null) {
        try {
            $where = [];
            $params = [];
            
            if ($fecha_desde) {
                $where[] = "DATE(fecha_creacion) >= ?";
                $params[] = $fecha_desde;
            }
            
            if ($fecha_hasta) {
                $where[] = "DATE(fecha_creacion) <= ?";
                $params[] = $fecha_hasta;
            }
            
            $where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            
            $sql = "SELECT 
                        COUNT(*) as total,
                        COUNT(CASE WHEN estado = 'pendiente' THEN 1 END) as pendientes,
                        COUNT(CASE WHEN estado = 'completada' THEN 1 END) as completadas,
                        COUNT(CASE WHEN estado = 'cancelada' THEN 1 END) as canceladas
                    FROM transferencias_inventario
                    $where_sql";
            
            return db_query_one($sql, $params);
            
        } catch (Exception $e) {
            registrar_error("Error al obtener estadísticas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene el historial de transferencias de un producto
     * 
     * @param int $producto_id ID del producto
     * @return array Historial de transferencias
     */
    public static function obtenerHistorialProducto($producto_id) {
        try {
            $sql = "SELECT 
                        t.id,
                        t.estado,
                        t.fecha_creacion,
                        t.fecha_completado,
                        d.cantidad,
                        so.nombre as sucursal_origen,
                        sd.nombre as sucursal_destino,
                        u.nombre as usuario
                    FROM detalle_transferencias_inventario d
                    INNER JOIN transferencias_inventario t ON d.transferencia_id = t.id
                    INNER JOIN sucursales so ON t.sucursal_origen_id = so.id
                    INNER JOIN sucursales sd ON t.sucursal_destino_id = sd.id
                    INNER JOIN usuarios u ON t.usuario_id = u.id
                    WHERE d.producto_id = ?
                    ORDER BY t.fecha_creacion DESC";
            
            return db_query($sql, [$producto_id]);
            
        } catch (Exception $e) {
            registrar_error("Error al obtener historial de producto: " . $e->getMessage());
            return [];
        }
    }
}
