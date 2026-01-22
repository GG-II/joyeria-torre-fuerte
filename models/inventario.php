<?php
// ================================================
// MODELO: INVENTARIO
// Sistema de Gestión - Joyería Torre Fuerte
// ================================================

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/funciones.php';

class Inventario {
    
    // ========================================
    // MÉTODOS DE CONSULTA (SELECT)
    // ========================================
    
    /**
     * Obtiene el inventario de una sucursal con filtros
     * 
     * @param int $sucursal_id ID de la sucursal
     * @param array $filtros Filtros adicionales
     * @param int $pagina Número de página
     * @param int $por_pagina Items por página
     * @return array Array de inventario
     */
    public static function listarPorSucursal($sucursal_id, $filtros = [], $pagina = 1, $por_pagina = 20) {
        global $pdo;
        
        $where = ['i.sucursal_id = ?'];
        $params = [$sucursal_id];
        
        // Filtro por categoría
        if (isset($filtros['categoria_id']) && $filtros['categoria_id'] > 0) {
            $where[] = 'p.categoria_id = ?';
            $params[] = $filtros['categoria_id'];
        }
        
        // Filtro por stock bajo
        if (isset($filtros['stock_bajo']) && $filtros['stock_bajo'] == 1) {
            $where[] = 'i.cantidad <= i.stock_minimo';
        }
        
        // Filtro por búsqueda
        if (isset($filtros['busqueda']) && !empty($filtros['busqueda'])) {
            $where[] = '(p.codigo LIKE ? OR p.nombre LIKE ?)';
            $termino = '%' . $filtros['busqueda'] . '%';
            $params[] = $termino;
            $params[] = $termino;
        }
        
        // Solo productos activos
        $where[] = 'p.activo = 1';
        
        $where_sql = implode(' AND ', $where);
        
        // Calcular offset
        $offset = ($pagina - 1) * $por_pagina;
        
        $sql = "SELECT i.*, 
                       p.codigo, p.nombre, p.codigo_barras, p.es_por_peso,
                       c.nombre as categoria_nombre,
                       (SELECT precio FROM precios_producto WHERE producto_id = p.id AND tipo_precio = 'publico' AND activo = 1 LIMIT 1) as precio_publico,
                       CASE 
                           WHEN i.cantidad <= i.stock_minimo THEN 1
                           ELSE 0
                       END as alerta_stock_bajo
                FROM inventario i
                INNER JOIN productos p ON i.producto_id = p.id
                LEFT JOIN categorias c ON p.categoria_id = c.id
                WHERE $where_sql
                ORDER BY p.nombre
                LIMIT ? OFFSET ?";
        
        $params[] = $por_pagina;
        $params[] = $offset;
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            registrar_error("Error al listar inventario: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene productos con stock bajo en una sucursal
     * 
     * @param int $sucursal_id ID de la sucursal (null para todas)
     * @return array Array de productos con stock bajo
     */
    public static function obtenerStockBajo($sucursal_id = null) {
        global $pdo;
        
        $where = ['i.cantidad <= i.stock_minimo', 'p.activo = 1'];
        $params = [];
        
        if ($sucursal_id !== null) {
            $where[] = 'i.sucursal_id = ?';
            $params[] = $sucursal_id;
        }
        
        $where_sql = implode(' AND ', $where);
        
        $sql = "SELECT i.*, 
                       p.codigo, p.nombre,
                       s.nombre as sucursal_nombre,
                       c.nombre as categoria_nombre
                FROM inventario i
                INNER JOIN productos p ON i.producto_id = p.id
                INNER JOIN sucursales s ON i.sucursal_id = s.id
                LEFT JOIN categorias c ON p.categoria_id = c.id
                WHERE $where_sql
                ORDER BY s.nombre, p.nombre";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            registrar_error("Error al obtener stock bajo: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene el inventario de un producto en una sucursal específica
     * 
     * @param int $producto_id ID del producto
     * @param int $sucursal_id ID de la sucursal
     * @return array|false Inventario o false
     */
    public static function obtenerPorProductoYSucursal($producto_id, $sucursal_id) {
        global $pdo;
        
        $sql = "SELECT i.*, 
                       p.codigo, p.nombre, p.es_por_peso
                FROM inventario i
                INNER JOIN productos p ON i.producto_id = p.id
                WHERE i.producto_id = ? AND i.sucursal_id = ?";
        
        try {
            return db_query_one($sql, [$producto_id, $sucursal_id]);
        } catch (PDOException $e) {
            registrar_error("Error al obtener inventario: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene el inventario total de un producto en todas las sucursales
     * 
     * @param int $producto_id ID del producto
     * @return array Array de inventario por sucursal
     */
    public static function obtenerPorProducto($producto_id) {
        global $pdo;
        
        $sql = "SELECT i.*, 
                       s.nombre as sucursal_nombre
                FROM inventario i
                INNER JOIN sucursales s ON i.sucursal_id = s.id
                WHERE i.producto_id = ?
                ORDER BY s.nombre";
        
        try {
            return db_query($sql, [$producto_id]);
        } catch (PDOException $e) {
            registrar_error("Error al obtener inventario del producto: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene la cantidad total disponible de un producto en todas las sucursales
     * 
     * @param int $producto_id ID del producto
     * @return int Cantidad total
     */
    public static function obtenerCantidadTotal($producto_id) {
        global $pdo;
        
        $sql = "SELECT SUM(cantidad) as total FROM inventario WHERE producto_id = ?";
        
        try {
            $resultado = db_query_one($sql, [$producto_id]);
            return $resultado ? (int)$resultado['total'] : 0;
        } catch (PDOException $e) {
            registrar_error("Error al obtener cantidad total: " . $e->getMessage());
            return 0;
        }
    }
    
    // ========================================
    // MÉTODOS DE CREACIÓN Y ACTUALIZACIÓN
    // ========================================
    
    /**
     * Crea o actualiza el inventario de un producto en una sucursal
     * 
     * @param int $producto_id ID del producto
     * @param int $sucursal_id ID de la sucursal
     * @param int $cantidad Cantidad inicial
     * @param int $stock_minimo Stock mínimo
     * @param bool $es_compartido Si es compartido entre sucursales
     * @return bool
     */
    public static function crear($producto_id, $sucursal_id, $cantidad = 0, $stock_minimo = 5, $es_compartido = 0) {
        global $pdo;
        
        try {
            // Verificar si ya existe
            $existe = self::existe($producto_id, $sucursal_id);
            
            if ($existe) {
                // Actualizar
                $sql = "UPDATE inventario SET
                            cantidad = ?,
                            stock_minimo = ?,
                            es_compartido = ?
                        WHERE producto_id = ? AND sucursal_id = ?";
                
                $resultado = db_execute($sql, [$cantidad, $stock_minimo, $es_compartido, $producto_id, $sucursal_id]);
            } else {
                // Insertar
                $sql = "INSERT INTO inventario (producto_id, sucursal_id, cantidad, stock_minimo, es_compartido)
                        VALUES (?, ?, ?, ?, ?)";
                
                $resultado = db_execute($sql, [$producto_id, $sucursal_id, $cantidad, $stock_minimo, $es_compartido]);
            }
            
            if ($resultado) {
                registrar_auditoria('INSERT', 'inventario', $producto_id, 
                    "Inventario creado/actualizado - Sucursal: $sucursal_id, Cantidad: $cantidad");
            }
            
            return $resultado;
            
        } catch (PDOException $e) {
            registrar_error("Error al crear inventario: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Incrementa el stock de un producto
     * 
     * @param int $producto_id ID del producto
     * @param int $sucursal_id ID de la sucursal
     * @param int $cantidad Cantidad a incrementar
     * @param string $motivo Motivo del movimiento
     * @param string $tipo_referencia Tipo de referencia (compra, ajuste_manual, etc)
     * @param int $referencia_id ID de la referencia
     * @return bool
     */
    public static function incrementarStock($producto_id, $sucursal_id, $cantidad, $motivo = '', $tipo_referencia = 'ajuste_manual', $referencia_id = null) {
        global $pdo;
        
        if ($cantidad <= 0) {
            return false;
        }
        
        // Iniciar transacción
        $pdo->beginTransaction();
        
        try {
            // Obtener cantidad actual
            $inventario = self::obtenerPorProductoYSucursal($producto_id, $sucursal_id);
            
            if (!$inventario) {
                // Crear inventario si no existe
                self::crear($producto_id, $sucursal_id, 0);
                $cantidad_anterior = 0;
            } else {
                $cantidad_anterior = $inventario['cantidad'];
            }
            
            $cantidad_nueva = $cantidad_anterior + $cantidad;
            
            // Actualizar cantidad
            $sql = "UPDATE inventario SET cantidad = ? 
                    WHERE producto_id = ? AND sucursal_id = ?";
            db_execute($sql, [$cantidad_nueva, $producto_id, $sucursal_id]);
            
            // Registrar movimiento
            self::registrarMovimiento(
                $producto_id,
                $sucursal_id,
                'ingreso',
                $cantidad,
                $cantidad_anterior,
                $cantidad_nueva,
                $motivo,
                $tipo_referencia,
                $referencia_id
            );
            
            // Confirmar transacción
            $pdo->commit();
            
            return true;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            registrar_error("Error al incrementar stock: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Decrementa el stock de un producto
     * 
     * @param int $producto_id ID del producto
     * @param int $sucursal_id ID de la sucursal
     * @param int $cantidad Cantidad a decrementar
     * @param string $motivo Motivo del movimiento
     * @param string $tipo_referencia Tipo de referencia (venta, ajuste_manual, etc)
     * @param int $referencia_id ID de la referencia
     * @return bool
     */
    public static function decrementarStock($producto_id, $sucursal_id, $cantidad, $motivo = '', $tipo_referencia = 'ajuste_manual', $referencia_id = null) {
        global $pdo;
        
        if ($cantidad <= 0) {
            return false;
        }
        
        // Verificar stock suficiente
        if (!validar_stock_suficiente($producto_id, $sucursal_id, $cantidad)) {
            return false;
        }
        
        // Iniciar transacción
        $pdo->beginTransaction();
        
        try {
            // Obtener cantidad actual
            $inventario = self::obtenerPorProductoYSucursal($producto_id, $sucursal_id);
            $cantidad_anterior = $inventario['cantidad'];
            $cantidad_nueva = $cantidad_anterior - $cantidad;
            
            // Actualizar cantidad
            $sql = "UPDATE inventario SET cantidad = ? 
                    WHERE producto_id = ? AND sucursal_id = ?";
            db_execute($sql, [$cantidad_nueva, $producto_id, $sucursal_id]);
            
            // Registrar movimiento
            self::registrarMovimiento(
                $producto_id,
                $sucursal_id,
                'salida',
                $cantidad,
                $cantidad_anterior,
                $cantidad_nueva,
                $motivo,
                $tipo_referencia,
                $referencia_id
            );
            
            // Confirmar transacción
            $pdo->commit();
            
            return true;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            registrar_error("Error al decrementar stock: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ajusta el stock de un producto manualmente
     * 
     * @param int $producto_id ID del producto
     * @param int $sucursal_id ID de la sucursal
     * @param int $cantidad_nueva Nueva cantidad
     * @param string $motivo Motivo del ajuste
     * @return bool
     */
    public static function ajustarStock($producto_id, $sucursal_id, $cantidad_nueva, $motivo = '') {
        global $pdo;
        
        if ($cantidad_nueva < 0) {
            return false;
        }
        
        // Iniciar transacción
        $pdo->beginTransaction();
        
        try {
            // Obtener cantidad actual
            $inventario = self::obtenerPorProductoYSucursal($producto_id, $sucursal_id);
            
            if (!$inventario) {
                // Crear inventario si no existe
                self::crear($producto_id, $sucursal_id, $cantidad_nueva);
                $cantidad_anterior = 0;
            } else {
                $cantidad_anterior = $inventario['cantidad'];
            }
            
            // Calcular diferencia
            $diferencia = $cantidad_nueva - $cantidad_anterior;
            
            // Actualizar cantidad
            $sql = "UPDATE inventario SET cantidad = ? 
                    WHERE producto_id = ? AND sucursal_id = ?";
            db_execute($sql, [$cantidad_nueva, $producto_id, $sucursal_id]);
            
            // Registrar movimiento
            self::registrarMovimiento(
                $producto_id,
                $sucursal_id,
                'ajuste',
                abs($diferencia),
                $cantidad_anterior,
                $cantidad_nueva,
                $motivo,
                'ajuste_manual',
                null
            );
            
            // Confirmar transacción
            $pdo->commit();
            
            return true;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            registrar_error("Error al ajustar stock: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Transfiere stock entre sucursales
     * 
     * @param int $producto_id ID del producto
     * @param int $sucursal_origen_id ID de la sucursal origen
     * @param int $sucursal_destino_id ID de la sucursal destino
     * @param int $cantidad Cantidad a transferir
     * @param string $observaciones Observaciones de la transferencia
     * @return int|false ID de la transferencia o false
     */
    public static function transferir($producto_id, $sucursal_origen_id, $sucursal_destino_id, $cantidad, $observaciones = '') {
        global $pdo;
        
        if ($cantidad <= 0) {
            return false;
        }
        
        // Verificar stock suficiente en origen
        if (!validar_stock_suficiente($producto_id, $sucursal_origen_id, $cantidad)) {
            return false;
        }
        
        // Iniciar transacción
        $pdo->beginTransaction();
        
        try {
            // Crear registro de transferencia
            $sql_trans = "INSERT INTO transferencias_inventario (
                             sucursal_origen_id, sucursal_destino_id, usuario_id, 
                             estado, observaciones
                         ) VALUES (?, ?, ?, 'completada', ?)";
            
            $transferencia_id = db_execute($sql_trans, [
                $sucursal_origen_id,
                $sucursal_destino_id,
                usuario_actual_id(),
                $observaciones
            ]);
            
            // Crear detalle de transferencia
            $sql_detalle = "INSERT INTO detalle_transferencias_inventario (
                               transferencia_id, producto_id, cantidad
                           ) VALUES (?, ?, ?)";
            
            db_execute($sql_detalle, [$transferencia_id, $producto_id, $cantidad]);
            
            // Decrementar en origen
            self::decrementarStock(
                $producto_id,
                $sucursal_origen_id,
                $cantidad,
                "Transferencia a sucursal $sucursal_destino_id",
                'transferencia',
                $transferencia_id
            );
            
            // Incrementar en destino
            self::incrementarStock(
                $producto_id,
                $sucursal_destino_id,
                $cantidad,
                "Transferencia desde sucursal $sucursal_origen_id",
                'transferencia',
                $transferencia_id
            );
            
            // Marcar transferencia como completada
            $sql_completar = "UPDATE transferencias_inventario 
                             SET fecha_completado = NOW() 
                             WHERE id = ?";
            db_execute($sql_completar, [$transferencia_id]);
            
            // Registrar auditoría
            registrar_auditoria('INSERT', 'transferencias_inventario', $transferencia_id,
                "Transferencia de $cantidad unidades de producto $producto_id");
            
            // Confirmar transacción
            $pdo->commit();
            
            return $transferencia_id;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            registrar_error("Error al transferir stock: " . $e->getMessage());
            return false;
        }
    }
    
    // ========================================
    // MÉTODOS DE MOVIMIENTOS
    // ========================================
    
    /**
     * Registra un movimiento de inventario
     * 
     * @param int $producto_id ID del producto
     * @param int $sucursal_id ID de la sucursal
     * @param string $tipo_movimiento Tipo (ingreso, salida, ajuste, transferencia, venta)
     * @param int $cantidad Cantidad del movimiento
     * @param int $cantidad_anterior Cantidad anterior
     * @param int $cantidad_nueva Cantidad nueva
     * @param string $motivo Motivo del movimiento
     * @param string $tipo_referencia Tipo de referencia
     * @param int $referencia_id ID de la referencia
     * @return int|false ID del movimiento o false
     */
    private static function registrarMovimiento($producto_id, $sucursal_id, $tipo_movimiento, $cantidad, 
                                               $cantidad_anterior, $cantidad_nueva, $motivo = '', 
                                               $tipo_referencia = null, $referencia_id = null) {
        try {
            $sql = "INSERT INTO movimientos_inventario (
                        producto_id, sucursal_id, tipo_movimiento, cantidad,
                        cantidad_anterior, cantidad_nueva, motivo, usuario_id,
                        referencia_tipo, referencia_id
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            return db_execute($sql, [
                $producto_id,
                $sucursal_id,
                $tipo_movimiento,
                $cantidad,
                $cantidad_anterior,
                $cantidad_nueva,
                $motivo,
                usuario_actual_id(),
                $tipo_referencia,
                $referencia_id
            ]);
            
        } catch (PDOException $e) {
            registrar_error("Error al registrar movimiento: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene el historial de movimientos de un producto
     * 
     * @param int $producto_id ID del producto
     * @param int $sucursal_id ID de la sucursal (null para todas)
     * @param int $limite Límite de registros
     * @return array Array de movimientos
     */
    public static function obtenerHistorial($producto_id, $sucursal_id = null, $limite = 50) {
        global $pdo;
        
        $where = ['m.producto_id = ?'];
        $params = [$producto_id];
        
        if ($sucursal_id !== null) {
            $where[] = 'm.sucursal_id = ?';
            $params[] = $sucursal_id;
        }
        
        $where_sql = implode(' AND ', $where);
        
        $sql = "SELECT m.*, 
                       u.nombre as usuario_nombre,
                       s.nombre as sucursal_nombre
                FROM movimientos_inventario m
                INNER JOIN usuarios u ON m.usuario_id = u.id
                INNER JOIN sucursales s ON m.sucursal_id = s.id
                WHERE $where_sql
                ORDER BY m.fecha_hora DESC
                LIMIT ?";
        
        $params[] = $limite;
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            registrar_error("Error al obtener historial: " . $e->getMessage());
            return [];
        }
    }
    
    // ========================================
    // MÉTODOS DE VALIDACIÓN
    // ========================================
    
    /**
     * Verifica si existe inventario para un producto en una sucursal
     * 
     * @param int $producto_id ID del producto
     * @param int $sucursal_id ID de la sucursal
     * @return bool
     */
    public static function existe($producto_id, $sucursal_id) {
        return db_exists('inventario', 'producto_id = ? AND sucursal_id = ?', [$producto_id, $sucursal_id]);
    }
    
    // ========================================
    // MÉTODOS AUXILIARES
    // ========================================
    
    /**
     * Obtiene estadísticas de inventario
     * 
     * @param int $sucursal_id ID de la sucursal (null para todas)
     * @return array Array con estadísticas
     */
    public static function obtenerEstadisticas($sucursal_id = null) {
        global $pdo;
        
        try {
            $stats = [];
            
            $where = ['p.activo = 1'];
            $params = [];
            
            if ($sucursal_id !== null) {
                $where[] = 'i.sucursal_id = ?';
                $params[] = $sucursal_id;
            }
            
            $where_sql = implode(' AND ', $where);
            
            // Total de productos en inventario
            $sql = "SELECT COUNT(DISTINCT i.producto_id) as total
                    FROM inventario i
                    INNER JOIN productos p ON i.producto_id = p.id
                    WHERE $where_sql";
            $resultado = db_query_one($sql, $params);
            $stats['total_productos'] = $resultado['total'];
            
            // Total de unidades
            $sql = "SELECT SUM(i.cantidad) as total
                    FROM inventario i
                    INNER JOIN productos p ON i.producto_id = p.id
                    WHERE $where_sql";
            $resultado = db_query_one($sql, $params);
            $stats['total_unidades'] = $resultado['total'] ?? 0;
            
            // Productos con stock bajo
            $sql = "SELECT COUNT(*) as total
                    FROM inventario i
                    INNER JOIN productos p ON i.producto_id = p.id
                    WHERE i.cantidad <= i.stock_minimo AND $where_sql";
            $resultado = db_query_one($sql, $params);
            $stats['stock_bajo'] = $resultado['total'];
            
            // Valor total del inventario (aproximado)
            $sql = "SELECT SUM(i.cantidad * COALESCE(pr.precio, 0)) as total
                    FROM inventario i
                    INNER JOIN productos p ON i.producto_id = p.id
                    LEFT JOIN precios_producto pr ON p.id = pr.producto_id 
                        AND pr.tipo_precio = 'publico' AND pr.activo = 1
                    WHERE $where_sql";
            $resultado = db_query_one($sql, $params);
            $stats['valor_total'] = $resultado['total'] ?? 0;
            
            return $stats;
            
        } catch (Exception $e) {
            registrar_error("Error al obtener estadísticas: " . $e->getMessage());
            return [];
        }
    }
}
?>