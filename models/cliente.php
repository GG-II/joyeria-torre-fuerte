<?php
// ================================================
// MODELO: CLIENTE
// Sistema de Gestión - Joyería Torre Fuerte
// ================================================

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/funciones.php';

class Cliente {
    
    // ========================================
    // MÉTODOS DE CONSULTA (SELECT)
    // ========================================
    
    /**
     * Lista todos los clientes con filtros
     * 
     * @param array $filtros Array de filtros (tipo_cliente, activo, busqueda)
     * @param int $pagina Número de página
     * @param int $por_pagina Registros por página
     * @return array Array de clientes
     */
    public static function listar($filtros = [], $pagina = 1, $por_pagina = 20) {
        global $pdo;
        
        $where = ['1=1'];
        $params = [];
        
        // Filtro por tipo de cliente
        if (isset($filtros['tipo_cliente']) && !empty($filtros['tipo_cliente'])) {
            $where[] = 'tipo_cliente = ?';
            $params[] = $filtros['tipo_cliente'];
        }
        
        // Filtro por tipo de mercaderías
        if (isset($filtros['tipo_mercaderias']) && !empty($filtros['tipo_mercaderias'])) {
            $where[] = 'tipo_mercaderias = ?';
            $params[] = $filtros['tipo_mercaderias'];
        }
        
        // Filtro por estado activo
        if (isset($filtros['activo'])) {
            $where[] = 'activo = ?';
            $params[] = $filtros['activo'];
        } else {
            // Por defecto solo activos
            $where[] = 'activo = 1';
        }
        
        // Búsqueda general (nombre, teléfono, NIT)
        if (isset($filtros['busqueda']) && !empty($filtros['busqueda'])) {
            $where[] = '(nombre LIKE ? OR telefono LIKE ? OR nit LIKE ?)';
            $termino = '%' . $filtros['busqueda'] . '%';
            $params[] = $termino;
            $params[] = $termino;
            $params[] = $termino;
        }
        
        $where_sql = implode(' AND ', $where);
        
        // Calcular offset
        $offset = ($pagina - 1) * $por_pagina;
        
        $sql = "SELECT c.*,
                       (SELECT COUNT(*) FROM ventas WHERE cliente_id = c.id AND estado != 'anulada') as total_compras,
                       (SELECT SUM(total) FROM ventas WHERE cliente_id = c.id AND estado != 'anulada') as total_comprado,
                       (SELECT COUNT(*) FROM creditos_clientes WHERE cliente_id = c.id AND estado = 'activo') as creditos_activos,
                       (SELECT SUM(saldo_pendiente) FROM creditos_clientes WHERE cliente_id = c.id AND estado = 'activo') as saldo_credito
                FROM clientes c
                WHERE $where_sql
                ORDER BY c.nombre
                LIMIT ? OFFSET ?";
        
        $params[] = $por_pagina;
        $params[] = $offset;
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            registrar_error("Error al listar clientes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene un cliente por su ID
     * 
     * @param int $id ID del cliente
     * @return array|false Cliente o false
     */
    public static function obtenerPorId($id) {
        global $pdo;
        
        $sql = "SELECT c.*,
                       (SELECT COUNT(*) FROM ventas WHERE cliente_id = c.id AND estado != 'anulada') as total_compras,
                       (SELECT SUM(total) FROM ventas WHERE cliente_id = c.id AND estado != 'anulada') as total_comprado,
                       (SELECT COUNT(*) FROM creditos_clientes WHERE cliente_id = c.id AND estado = 'activo') as creditos_activos,
                       (SELECT SUM(saldo_pendiente) FROM creditos_clientes WHERE cliente_id = c.id AND estado = 'activo') as saldo_credito
                FROM clientes c
                WHERE c.id = ?";
        
        try {
            return db_query_one($sql, [$id]);
        } catch (PDOException $e) {
            registrar_error("Error al obtener cliente: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene un cliente por su NIT
     * 
     * @param string $nit NIT del cliente
     * @return array|false Cliente o false
     */
    public static function obtenerPorNit($nit) {
        global $pdo;
        
        $sql = "SELECT * FROM clientes WHERE nit = ? AND activo = 1";
        
        try {
            return db_query_one($sql, [$nit]);
        } catch (PDOException $e) {
            registrar_error("Error al obtener cliente por NIT: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene un cliente por su teléfono
     * 
     * @param string $telefono Teléfono del cliente
     * @return array|false Cliente o false
     */
    public static function obtenerPorTelefono($telefono) {
        global $pdo;
        
        $sql = "SELECT * FROM clientes WHERE telefono = ? AND activo = 1";
        
        try {
            return db_query_one($sql, [$telefono]);
        } catch (PDOException $e) {
            registrar_error("Error al obtener cliente por teléfono: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene clientes para un select HTML (búsqueda rápida)
     * 
     * @param string $termino Término de búsqueda
     * @param int $limite Límite de resultados
     * @return array Array de clientes [id, nombre, telefono, tipo_cliente]
     */
    public static function buscarParaSelect($termino = '', $limite = 10) {
        global $pdo;
        
        $sql = "SELECT id, nombre, telefono, nit, tipo_cliente
                FROM clientes
                WHERE activo = 1";
        
        $params = [];
        
        if (!empty($termino)) {
            $sql .= " AND (nombre LIKE ? OR telefono LIKE ? OR nit LIKE ?)";
            $termino_like = '%' . $termino . '%';
            $params[] = $termino_like;
            $params[] = $termino_like;
            $params[] = $termino_like;
        }
        
        $sql .= " ORDER BY nombre LIMIT ?";
        $params[] = $limite;
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            registrar_error("Error al buscar clientes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene historial de compras de un cliente
     * 
     * @param int $cliente_id ID del cliente
     * @param int $limite Límite de resultados
     * @return array Array de ventas
     */
    public static function obtenerHistorialCompras($cliente_id, $limite = 50) {
        global $pdo;
        
        $sql = "SELECT v.*, 
                       u.nombre as vendedor_nombre,
                       s.nombre as sucursal_nombre,
                       (SELECT COUNT(*) FROM detalle_ventas WHERE venta_id = v.id) as total_productos
                FROM ventas v
                LEFT JOIN usuarios u ON v.usuario_id = u.id
                LEFT JOIN sucursales s ON v.sucursal_id = s.id
                WHERE v.cliente_id = ?
                ORDER BY v.fecha DESC, v.hora DESC
                LIMIT ?";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$cliente_id, $limite]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            registrar_error("Error al obtener historial de compras: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene créditos activos de un cliente
     * 
     * @param int $cliente_id ID del cliente
     * @return array Array de créditos
     */
    public static function obtenerCreditosActivos($cliente_id) {
        global $pdo;
        
        $sql = "SELECT cc.*, v.numero_venta
                FROM creditos_clientes cc
                LEFT JOIN ventas v ON cc.venta_id = v.id
                WHERE cc.cliente_id = ? AND cc.estado = 'activo'
                ORDER BY cc.fecha_proximo_pago ASC";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$cliente_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            registrar_error("Error al obtener créditos activos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Calcula el total comprado por un cliente
     * 
     * @param int $cliente_id ID del cliente
     * @param string $fecha_inicio Fecha inicio (opcional)
     * @param string $fecha_fin Fecha fin (opcional)
     * @return float Total comprado
     */
    public static function calcularTotalComprado($cliente_id, $fecha_inicio = null, $fecha_fin = null) {
        global $pdo;
        
        $sql = "SELECT COALESCE(SUM(total), 0) as total
                FROM ventas
                WHERE cliente_id = ? AND estado != 'anulada'";
        
        $params = [$cliente_id];
        
        if ($fecha_inicio) {
            $sql .= " AND fecha >= ?";
            $params[] = $fecha_inicio;
        }
        
        if ($fecha_fin) {
            $sql .= " AND fecha <= ?";
            $params[] = $fecha_fin;
        }
        
        try {
            $resultado = db_query_one($sql, $params);
            return $resultado ? (float)$resultado['total'] : 0;
        } catch (PDOException $e) {
            registrar_error("Error al calcular total comprado: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Obtiene el total de clientes con filtros
     * 
     * @param array $filtros Array de filtros
     * @return int Total de clientes
     */
    public static function contarTotal($filtros = []) {
        $where = ['1=1'];
        $params = [];
        
        if (isset($filtros['tipo_cliente']) && !empty($filtros['tipo_cliente'])) {
            $where[] = 'tipo_cliente = ?';
            $params[] = $filtros['tipo_cliente'];
        }
        
        if (isset($filtros['activo'])) {
            $where[] = 'activo = ?';
            $params[] = $filtros['activo'];
        } else {
            $where[] = 'activo = 1';
        }
        
        if (isset($filtros['busqueda']) && !empty($filtros['busqueda'])) {
            $where[] = '(nombre LIKE ? OR telefono LIKE ? OR nit LIKE ?)';
            $termino = '%' . $filtros['busqueda'] . '%';
            $params[] = $termino;
            $params[] = $termino;
            $params[] = $termino;
        }
        
        $where_sql = implode(' AND ', $where);
        
        return db_count('clientes', $where_sql, $params);
    }
    
    // ========================================
    // MÉTODOS DE CREACIÓN (INSERT)
    // ========================================
    
    /**
     * Crea un nuevo cliente
     * 
     * @param array $datos Datos del cliente
     *   - nombre (string): Nombre completo
     *   - nit (string|null): NIT del cliente
     *   - telefono (string): Teléfono
     *   - email (string|null): Email
     *   - direccion (string|null): Dirección
     *   - tipo_cliente (string): 'publico' o 'mayorista'
     *   - tipo_mercaderias (string): 'oro', 'plata', 'ambas'
     *   - limite_credito (float|null): Límite de crédito
     *   - plazo_credito_dias (int|null): Plazo en días
     * @return int|false ID del cliente creado o false
     */
    public static function crear($datos) {
        global $pdo;
        
        // Validar datos
        $errores = self::validar($datos);
        if (!empty($errores)) {
            registrar_error("Errores de validación al crear cliente: " . implode(', ', $errores));
            return false;
        }
        
        try {
            $sql = "INSERT INTO clientes (
                        nombre, nit, telefono, email, direccion,
                        tipo_cliente, tipo_mercaderias, limite_credito,
                        plazo_credito_dias, activo
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $params = [
                $datos['nombre'],
                $datos['nit'] ?? null,
                $datos['telefono'],
                $datos['email'] ?? null,
                $datos['direccion'] ?? null,
                $datos['tipo_cliente'] ?? 'publico',
                $datos['tipo_mercaderias'] ?? 'ambas',
                isset($datos['limite_credito']) && $datos['limite_credito'] !== '' ? $datos['limite_credito'] : null,
                $datos['plazo_credito_dias'] ?? null,
                $datos['activo'] ?? 1
            ];
            
            $cliente_id = db_execute($sql, $params);
            
            if ($cliente_id) {
                $tipo = $datos['tipo_cliente'] ?? 'publico';
                registrar_auditoria('INSERT', 'clientes', $cliente_id, 
                    "Cliente creado: {$datos['nombre']} (Tipo: {$tipo})");
            }
            
            return $cliente_id;
            
        } catch (PDOException $e) {
            registrar_error("Error al crear cliente: " . $e->getMessage());
            return false;
        }
    }
    
    // ========================================
    // MÉTODOS DE ACTUALIZACIÓN (UPDATE)
    // ========================================
    
    /**
     * Actualiza un cliente existente
     * 
     * @param int $id ID del cliente
     * @param array $datos Datos a actualizar
     * @return bool
     */
    public static function actualizar($id, $datos) {
        global $pdo;
        
        // Validar datos
        $errores = self::validar($datos, $id);
        if (!empty($errores)) {
            registrar_error("Errores de validación al actualizar cliente: " . implode(', ', $errores));
            return false;
        }
        
        // Verificar que el cliente existe
        $cliente = self::obtenerPorId($id);
        if (!$cliente) {
            registrar_error("Cliente no encontrado: ID $id");
            return false;
        }
        
        try {
            $sql = "UPDATE clientes SET
                        nombre = ?,
                        nit = ?,
                        telefono = ?,
                        email = ?,
                        direccion = ?,
                        tipo_cliente = ?,
                        tipo_mercaderias = ?,
                        limite_credito = ?,
                        plazo_credito_dias = ?
                    WHERE id = ?";
            
            $params = [
                $datos['nombre'],
                $datos['nit'] ?? null,
                $datos['telefono'],
                $datos['email'] ?? null,
                $datos['direccion'] ?? null,
                $datos['tipo_cliente'] ?? 'publico',
                $datos['tipo_mercaderias'] ?? 'ambas',
                isset($datos['limite_credito']) && $datos['limite_credito'] !== '' ? $datos['limite_credito'] : null,
                $datos['plazo_credito_dias'] ?? null,
                $id
            ];
            
            $resultado = db_execute($sql, $params);
            
            if ($resultado !== false) {
                registrar_auditoria('UPDATE', 'clientes', $id, 
                    "Cliente actualizado: {$datos['nombre']}");
            }
            
            return $resultado !== false;
            
        } catch (PDOException $e) {
            registrar_error("Error al actualizar cliente: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualiza el límite de crédito de un cliente
     * 
     * @param int $id ID del cliente
     * @param float|null $limite_credito Nuevo límite de crédito
     * @return bool
     */
    public static function actualizarLimiteCredito($id, $limite_credito) {
        global $pdo;
        
        try {
            $cliente = self::obtenerPorId($id);
            
            if (!$cliente) {
                return false;
            }
            
            $sql = "UPDATE clientes SET limite_credito = ? WHERE id = ?";
            $resultado = db_execute($sql, [$limite_credito, $id]);
            
            if ($resultado !== false) {
                $limite_formateado = $limite_credito !== null ? formato_dinero($limite_credito) : 'SIN LÍMITE';
                registrar_auditoria('UPDATE', 'clientes', $id, 
                    "Límite de crédito actualizado: {$limite_formateado}");
            }
            
            return $resultado !== false;
            
        } catch (PDOException $e) {
            registrar_error("Error al actualizar límite de crédito: " . $e->getMessage());
            return false;
        }
    }
    
    // ========================================
    // MÉTODOS DE ELIMINACIÓN (SOFT DELETE)
    // ========================================
    
    /**
     * Elimina (desactiva) un cliente
     * 
     * @param int $id ID del cliente
     * @return bool
     */
    public static function eliminar($id) {
        global $pdo;
        
        try {
            // Obtener cliente para auditoría
            $cliente = self::obtenerPorId($id);
            
            if (!$cliente) {
                return false;
            }
            
            // Verificar si puede eliminarse
            if (!self::puedeEliminar($id)) {
                registrar_error("No se puede eliminar el cliente con créditos activos: ID $id");
                return false;
            }
            
            // Soft delete
            $sql = "UPDATE clientes SET activo = 0 WHERE id = ?";
            $resultado = db_execute($sql, [$id]);
            
            if ($resultado !== false) {
                registrar_auditoria('DELETE', 'clientes', $id, 
                    "Cliente desactivado: {$cliente['nombre']}");
            }
            
            return $resultado !== false;
            
        } catch (PDOException $e) {
            registrar_error("Error al eliminar cliente: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Reactiva un cliente
     * 
     * @param int $id ID del cliente
     * @return bool
     */
    public static function reactivar($id) {
        global $pdo;
        
        try {
            $cliente = self::obtenerPorId($id);
            
            if (!$cliente) {
                return false;
            }
            
            $sql = "UPDATE clientes SET activo = 1 WHERE id = ?";
            $resultado = db_execute($sql, [$id]);
            
            if ($resultado !== false) {
                registrar_auditoria('UPDATE', 'clientes', $id, 
                    "Cliente reactivado: {$cliente['nombre']}");
            }
            
            return $resultado !== false;
            
        } catch (PDOException $e) {
            registrar_error("Error al reactivar cliente: " . $e->getMessage());
            return false;
        }
    }
    
    // ========================================
    // MÉTODOS DE VALIDACIÓN
    // ========================================
    
    /**
     * Valida los datos de un cliente
     * 
     * @param array $datos Datos a validar
     * @param int $id ID del cliente (para actualización)
     * @return array Array de errores
     */
    public static function validar($datos, $id = null) {
        $errores = [];
        
        // Nombre requerido
        if (empty($datos['nombre'])) {
            $errores[] = 'El nombre es requerido';
        }
        
        // Teléfono requerido
        if (empty($datos['telefono'])) {
            $errores[] = 'El teléfono es requerido';
        } elseif (strlen($datos['telefono']) < 8) {
            $errores[] = 'El teléfono debe tener al menos 8 dígitos';
        }
        
        // Validar NIT si se proporciona
        if (isset($datos['nit']) && !empty($datos['nit'])) {
            // Verificar que el NIT sea único
            if (!self::validarNitUnico($datos['nit'], $id)) {
                $errores[] = 'Ya existe un cliente con ese NIT';
            }
        }
        
        // Validar email si se proporciona
        if (isset($datos['email']) && !empty($datos['email'])) {
            if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
                $errores[] = 'El formato del email no es válido';
            }
        }
        
        // Validar tipo de cliente
        if (isset($datos['tipo_cliente']) && !in_array($datos['tipo_cliente'], ['publico', 'mayorista'])) {
            $errores[] = 'El tipo de cliente no es válido';
        }
        
        // Validar tipo de mercaderías
        if (isset($datos['tipo_mercaderias']) && !in_array($datos['tipo_mercaderias'], ['oro', 'plata', 'ambas'])) {
            $errores[] = 'El tipo de mercaderías no es válido';
        }
        
        // Validar límite de crédito si se proporciona
        if (isset($datos['limite_credito']) && $datos['limite_credito'] !== null && $datos['limite_credito'] !== '') {
            if (!is_numeric($datos['limite_credito']) || $datos['limite_credito'] < 0) {
                $errores[] = 'El límite de crédito debe ser un número positivo';
            }
        }
        
        // Validar plazo de crédito si se proporciona
        if (isset($datos['plazo_credito_dias']) && $datos['plazo_credito_dias'] !== null && $datos['plazo_credito_dias'] !== '') {
            if (!is_numeric($datos['plazo_credito_dias']) || $datos['plazo_credito_dias'] <= 0) {
                $errores[] = 'El plazo de crédito debe ser un número positivo';
            }
        }
        
        return $errores;
    }
    
    /**
     * Verifica si un NIT es único
     * 
     * @param string $nit NIT a verificar
     * @param int $excluir_id ID a excluir de la búsqueda
     * @return bool
     */
    public static function validarNitUnico($nit, $excluir_id = null) {
        if (empty($nit)) {
            return true; // NIT vacío es válido (no es obligatorio)
        }
        
        if ($excluir_id) {
            return !db_exists('clientes', 
                'nit = ? AND id != ? AND activo = 1', 
                [$nit, $excluir_id]);
        }
        
        return !db_exists('clientes', 
            'nit = ? AND activo = 1', 
            [$nit]);
    }
    
    /**
     * Valida el límite de crédito de un cliente
     * 
     * @param int $cliente_id ID del cliente
     * @param float $monto_nuevo Monto del nuevo crédito
     * @return array ['valido' => bool, 'mensaje' => string, 'disponible' => float]
     */
    public static function validarLimiteCredito($cliente_id, $monto_nuevo) {
        $cliente = self::obtenerPorId($cliente_id);
        
        if (!$cliente) {
            return [
                'valido' => false,
                'mensaje' => 'Cliente no encontrado',
                'disponible' => 0
            ];
        }
        
        // Si limite_credito es NULL, el cliente no tiene crédito habilitado
        if ($cliente['limite_credito'] === null) {
            return [
                'valido' => false,
                'mensaje' => 'El cliente no tiene crédito habilitado',
                'disponible' => 0
            ];
        }
        
        // Si limite_credito es 0, significa sin límite
        if ($cliente['limite_credito'] == 0) {
            return [
                'valido' => true,
                'mensaje' => 'Sin límite de crédito',
                'disponible' => PHP_FLOAT_MAX
            ];
        }
        
        // Calcular crédito utilizado
        $saldo_actual = $cliente['saldo_credito'] ?? 0;
        $disponible = $cliente['limite_credito'] - $saldo_actual;
        
        if ($monto_nuevo > $disponible) {
            return [
                'valido' => false,
                'mensaje' => 'Excede el límite de crédito disponible (' . formato_dinero($disponible) . ')',
                'disponible' => $disponible
            ];
        }
        
        return [
            'valido' => true,
            'mensaje' => 'Crédito disponible: ' . formato_dinero($disponible),
            'disponible' => $disponible
        ];
    }
    
    /**
     * Verifica si un cliente existe
     * 
     * @param int $id ID del cliente
     * @return bool
     */
    public static function existe($id) {
        return db_exists('clientes', 'id = ?', [$id]);
    }
    
    /**
     * Verifica si un cliente puede ser eliminado
     * 
     * @param int $id ID del cliente
     * @return bool
     */
    public static function puedeEliminar($id) {
        // No puede eliminar si tiene créditos activos
        $creditos_activos = db_count('creditos_clientes', 
            'cliente_id = ? AND estado = ?', 
            [$id, 'activo']);
        
        if ($creditos_activos > 0) {
            return false;
        }
        
        return true;
    }
    
    // ========================================
    // MÉTODOS AUXILIARES
    // ========================================
    
    /**
     * Obtiene estadísticas de clientes
     * 
     * @return array Array con estadísticas
     */
    public static function obtenerEstadisticas() {
        global $pdo;
        
        try {
            $stats = [];
            
            // Total de clientes por tipo
            $sql = "SELECT tipo_cliente, COUNT(*) as total
                    FROM clientes
                    WHERE activo = 1
                    GROUP BY tipo_cliente";
            $stats['por_tipo'] = db_query($sql);
            
            // Clientes con más compras
            $sql = "SELECT c.id, c.nombre, c.telefono, 
                           COUNT(v.id) as total_compras,
                           SUM(v.total) as monto_total
                    FROM clientes c
                    LEFT JOIN ventas v ON c.id = v.cliente_id AND v.estado != 'anulada'
                    WHERE c.activo = 1
                    GROUP BY c.id, c.nombre, c.telefono
                    HAVING total_compras > 0
                    ORDER BY monto_total DESC
                    LIMIT 10";
            $stats['top_clientes'] = db_query($sql);
            
            // Clientes con créditos activos
            $stats['con_creditos_activos'] = db_count('clientes c', 
                'c.activo = 1 AND EXISTS (SELECT 1 FROM creditos_clientes cc WHERE cc.cliente_id = c.id AND cc.estado = "activo")');
            
            // Total de clientes activos
            $stats['total_activos'] = db_count('clientes', 'activo = 1');
            
            // Total de clientes inactivos
            $stats['total_inactivos'] = db_count('clientes', 'activo = 0');
            
            // Clientes mayoristas
            $stats['total_mayoristas'] = db_count('clientes', 'tipo_cliente = ? AND activo = 1', ['mayorista']);
            
            // Clientes públicos
            $stats['total_publicos'] = db_count('clientes', 'tipo_cliente = ? AND activo = 1', ['publico']);
            
            return $stats;
            
        } catch (Exception $e) {
            registrar_error("Error al obtener estadísticas de clientes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene resumen del cliente (para dashboard/reportes)
     * 
     * @param int $cliente_id ID del cliente
     * @return array Array con resumen del cliente
     */
    public static function obtenerResumen($cliente_id) {
        $cliente = self::obtenerPorId($cliente_id);
        
        if (!$cliente) {
            return [];
        }
        
        $resumen = [
            'cliente' => $cliente,
            'total_compras' => $cliente['total_compras'],
            'total_comprado' => $cliente['total_comprado'],
            'creditos_activos' => $cliente['creditos_activos'],
            'saldo_credito' => $cliente['saldo_credito'],
            'ultima_compra' => null,
            'producto_favorito' => null
        ];
        
        // Obtener última compra
        $ultima_compra = db_query_one(
            "SELECT fecha, total FROM ventas 
             WHERE cliente_id = ? AND estado != 'anulada' 
             ORDER BY fecha DESC, hora DESC LIMIT 1",
            [$cliente_id]
        );
        
        if ($ultima_compra) {
            $resumen['ultima_compra'] = $ultima_compra;
        }
        
        // Obtener producto más comprado
        $producto_favorito = db_query_one(
            "SELECT p.nombre, SUM(dv.cantidad) as total_comprado
             FROM detalle_ventas dv
             INNER JOIN productos p ON dv.producto_id = p.id
             INNER JOIN ventas v ON dv.venta_id = v.id
             WHERE v.cliente_id = ? AND v.estado != 'anulada'
             GROUP BY p.id, p.nombre
             ORDER BY total_comprado DESC
             LIMIT 1",
            [$cliente_id]
        );
        
        if ($producto_favorito) {
            $resumen['producto_favorito'] = $producto_favorito;
        }
        
        return $resumen;
    }
}
?>