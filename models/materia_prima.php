<?php
// ================================================
// MODELO: MATERIA PRIMA
// Sistema de Gestión - Joyería Torre Fuerte
// VERSIÓN CORREGIDA - Columnas reales de la BD
// ================================================

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/funciones.php';

class MateriaPrima {
    
    // ========================================
    // MÉTODOS DE CONSULTA (SELECT)
    // ========================================
    
    /**
     * Obtiene todas las materias primas con filtros
     * 
     * @param array $filtros Array de filtros (tipo, activo)
     * @return array Array de materias primas
     */
    public static function listar($filtros = []) {
        global $pdo;
        
        $where = ['1=1'];
        $params = [];
        
        // Filtro por tipo
        if (isset($filtros['tipo']) && !empty($filtros['tipo'])) {
            $where[] = 'tipo = ?';
            $params[] = $filtros['tipo'];
        }
        
        // Filtro por estado activo
        if (isset($filtros['activo'])) {
            $where[] = 'activo = ?';
            $params[] = $filtros['activo'];
        } else {
            // Por defecto solo activas
            $where[] = 'activo = 1';
        }
        
        // Filtro por búsqueda
        if (isset($filtros['busqueda']) && !empty($filtros['busqueda'])) {
            $where[] = 'nombre LIKE ?';
            $params[] = '%' . $filtros['busqueda'] . '%';
        }
        
        $where_sql = implode(' AND ', $where);
        
        $sql = "SELECT * FROM materias_primas
                WHERE $where_sql
                ORDER BY tipo, nombre";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            registrar_error("Error al listar materias primas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene materias primas agrupadas por tipo
     * 
     * @param bool $solo_activas Si solo incluir activas
     * @return array Array agrupado por tipo
     */
    public static function listarPorTipo($solo_activas = true) {
        $filtros = [];
        
        if ($solo_activas) {
            $filtros['activo'] = 1;
        }
        
        $materias_primas = self::listar($filtros);
        
        $agrupadas = [
            'oro' => [],
            'plata' => [],
            'piedra' => [],
            'otro' => []
        ];
        
        foreach ($materias_primas as $materia) {
            $agrupadas[$materia['tipo']][] = $materia;
        }
        
        return $agrupadas;
    }
    
    /**
     * Obtiene una materia prima por su ID
     * 
     * @param int $id ID de la materia prima
     * @return array|false Materia prima o false
     */
    public static function obtenerPorId($id) {
        global $pdo;
        
        $sql = "SELECT * FROM materias_primas WHERE id = ?";
        
        try {
            return db_query_one($sql, [$id]);
        } catch (PDOException $e) {
            registrar_error("Error al obtener materia prima: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Busca materias primas por término
     * 
     * @param string $termino Término de búsqueda
     * @param int $limite Límite de resultados
     * @return array Array de materias primas
     */
    public static function buscar($termino, $limite = 10) {
        global $pdo;
        
        $sql = "SELECT * FROM materias_primas
                WHERE nombre LIKE ? AND activo = 1
                ORDER BY nombre
                LIMIT ?";
        
        $params = ['%' . $termino . '%', $limite];
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            registrar_error("Error al buscar materias primas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene materias primas con stock bajo
     * 
     * @param float $umbral Umbral de stock bajo (si null, usa stock_minimo)
     * @return array Array de materias primas con stock bajo
     */
    public static function obtenerStockBajo($umbral = null) {
        global $pdo;
        
        if ($umbral === null) {
            // Usar stock_minimo de cada materia prima
            $sql = "SELECT * FROM materias_primas
                    WHERE cantidad_disponible <= stock_minimo AND activo = 1
                    ORDER BY cantidad_disponible ASC";
            $params = [];
        } else {
            $sql = "SELECT * FROM materias_primas
                    WHERE cantidad_disponible < ? AND activo = 1
                    ORDER BY cantidad_disponible ASC";
            $params = [$umbral];
        }
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            registrar_error("Error al obtener stock bajo: " . $e->getMessage());
            return [];
        }
    }
    
    // ========================================
    // MÉTODOS DE CREACIÓN (INSERT)
    // ========================================
    
    /**
     * Crea una nueva materia prima
     * 
     * @param array $datos Datos de la materia prima
     * @return int|false ID de la materia prima creada o false
     */
    public static function crear($datos) {
        global $pdo;
        
        // Validar datos
        $errores = self::validar($datos);
        if (!empty($errores)) {
            return false;
        }
        
        try {
            $sql = "INSERT INTO materias_primas (
                        nombre, tipo, unidad_medida, cantidad_disponible,
                        stock_minimo, precio_por_unidad, activo
                    ) VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $params = [
                $datos['nombre'],
                $datos['tipo'],
                $datos['unidad_medida'],
                $datos['cantidad_disponible'] ?? 0,
                $datos['stock_minimo'] ?? 5,
                $datos['precio_por_unidad'] ?? null,
                $datos['activo'] ?? 1
            ];
            
            $materia_id = db_execute($sql, $params);
            
            if ($materia_id) {
                registrar_auditoria('INSERT', 'materias_primas', $materia_id, 
                    "Materia prima creada: {$datos['nombre']} (Tipo: {$datos['tipo']})");
            }
            
            return $materia_id;
            
        } catch (PDOException $e) {
            registrar_error("Error al crear materia prima: " . $e->getMessage());
            return false;
        }
    }
    
    // ========================================
    // MÉTODOS DE ACTUALIZACIÓN (UPDATE)
    // ========================================
    
    /**
     * Actualiza una materia prima
     * 
     * @param int $id ID de la materia prima
     * @param array $datos Datos a actualizar
     * @return bool
     */
    public static function actualizar($id, $datos) {
        global $pdo;
        
        // Validar que la materia prima existe
        if (!self::existe($id)) {
            return false;
        }
        
        // Validar datos
        $errores = self::validar($datos, $id);
        if (!empty($errores)) {
            return false;
        }
        
        try {
            $sql = "UPDATE materias_primas SET
                        nombre = ?,
                        tipo = ?,
                        unidad_medida = ?,
                        cantidad_disponible = ?,
                        stock_minimo = ?,
                        precio_por_unidad = ?
                    WHERE id = ?";
            
            $params = [
                $datos['nombre'],
                $datos['tipo'],
                $datos['unidad_medida'],
                $datos['cantidad_disponible'],
                $datos['stock_minimo'],
                $datos['precio_por_unidad'],
                $id
            ];
            
            $resultado = db_execute($sql, $params);
            
            if ($resultado) {
                registrar_auditoria('UPDATE', 'materias_primas', $id, 
                    "Materia prima actualizada: {$datos['nombre']}");
            }
            
            return $resultado;
            
        } catch (PDOException $e) {
            registrar_error("Error al actualizar materia prima: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualiza solo el precio de una materia prima
     * 
     * @param int $id ID de la materia prima
     * @param float $precio_nuevo Nuevo precio
     * @return bool
     */
    public static function actualizarPrecio($id, $precio_nuevo) {
        global $pdo;
        
        if ($precio_nuevo !== null && !validar_decimal_positivo($precio_nuevo)) {
            return false;
        }
        
        try {
            $sql = "UPDATE materias_primas SET precio_por_unidad = ? WHERE id = ?";
            $resultado = db_execute($sql, [$precio_nuevo, $id]);
            
            if ($resultado) {
                registrar_auditoria('UPDATE', 'materias_primas', $id, 
                    "Precio actualizado a: " . formato_dinero($precio_nuevo));
            }
            
            return $resultado;
            
        } catch (PDOException $e) {
            registrar_error("Error al actualizar precio: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Incrementa la cantidad disponible
     * 
     * @param int $id ID de la materia prima
     * @param float $cantidad Cantidad a incrementar
     * @param string $motivo Motivo del incremento
     * @return bool
     */
    public static function incrementarCantidad($id, $cantidad, $motivo = '') {
        global $pdo;
        
        if (!validar_decimal_positivo($cantidad) || $cantidad <= 0) {
            return false;
        }
        
        try {
            $sql = "UPDATE materias_primas 
                    SET cantidad_disponible = cantidad_disponible + ? 
                    WHERE id = ?";
            
            $resultado = db_execute($sql, [$cantidad, $id]);
            
            if ($resultado) {
                $materia = self::obtenerPorId($id);
                registrar_auditoria('UPDATE', 'materias_primas', $id, 
                    "Incremento de {$cantidad} {$materia['unidad_medida']}. Motivo: $motivo");
            }
            
            return $resultado;
            
        } catch (PDOException $e) {
            registrar_error("Error al incrementar cantidad: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Decrementa la cantidad disponible (uso de material)
     * 
     * @param int $id ID de la materia prima
     * @param float $cantidad Cantidad a decrementar
     * @param string $motivo Motivo del uso
     * @param int $trabajo_id ID del trabajo relacionado (opcional)
     * @return bool
     */
    public static function decrementarCantidad($id, $cantidad, $motivo = '', $trabajo_id = null) {
        global $pdo;
        
        if (!validar_decimal_positivo($cantidad) || $cantidad <= 0) {
            return false;
        }
        
        try {
            // Verificar que hay cantidad suficiente
            $materia = self::obtenerPorId($id);
            
            if (!$materia || $materia['cantidad_disponible'] < $cantidad) {
                return false; // Stock insuficiente
            }
            
            $sql = "UPDATE materias_primas 
                    SET cantidad_disponible = cantidad_disponible - ? 
                    WHERE id = ?";
            
            $resultado = db_execute($sql, [$cantidad, $id]);
            
            if ($resultado) {
                $detalles = "Uso de {$cantidad} {$materia['unidad_medida']}. Motivo: $motivo";
                if ($trabajo_id) {
                    $detalles .= ". Trabajo ID: $trabajo_id";
                }
                
                registrar_auditoria('UPDATE', 'materias_primas', $id, $detalles);
            }
            
            return $resultado;
            
        } catch (PDOException $e) {
            registrar_error("Error al decrementar cantidad: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ajusta la cantidad disponible manualmente
     * 
     * @param int $id ID de la materia prima
     * @param float $cantidad_nueva Nueva cantidad
     * @param string $motivo Motivo del ajuste
     * @return bool
     */
    public static function ajustarCantidad($id, $cantidad_nueva, $motivo = '') {
        global $pdo;
        
        if (!validar_decimal_positivo($cantidad_nueva)) {
            return false;
        }
        
        try {
            $materia = self::obtenerPorId($id);
            
            if (!$materia) {
                return false;
            }
            
            $cantidad_anterior = $materia['cantidad_disponible'];
            
            $sql = "UPDATE materias_primas SET cantidad_disponible = ? WHERE id = ?";
            $resultado = db_execute($sql, [$cantidad_nueva, $id]);
            
            if ($resultado) {
                registrar_auditoria('UPDATE', 'materias_primas', $id, 
                    "Ajuste de cantidad de {$cantidad_anterior} a {$cantidad_nueva} {$materia['unidad_medida']}. Motivo: $motivo");
            }
            
            return $resultado;
            
        } catch (PDOException $e) {
            registrar_error("Error al ajustar cantidad: " . $e->getMessage());
            return false;
        }
    }
    
    // ========================================
    // MÉTODOS DE ELIMINACIÓN (SOFT DELETE)
    // ========================================
    
    /**
     * Elimina (desactiva) una materia prima
     * 
     * @param int $id ID de la materia prima
     * @return bool
     */
    public static function eliminar($id) {
        global $pdo;
        
        try {
            $materia = self::obtenerPorId($id);
            
            if (!$materia) {
                return false;
            }
            
            // Soft delete
            $sql = "UPDATE materias_primas SET activo = 0 WHERE id = ?";
            $resultado = db_execute($sql, [$id]);
            
            if ($resultado) {
                registrar_auditoria('DELETE', 'materias_primas', $id, 
                    "Materia prima desactivada: {$materia['nombre']}");
            }
            
            return $resultado;
            
        } catch (PDOException $e) {
            registrar_error("Error al eliminar materia prima: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Reactiva una materia prima
     * 
     * @param int $id ID de la materia prima
     * @return bool
     */
    public static function reactivar($id) {
        global $pdo;
        
        try {
            $materia = self::obtenerPorId($id);
            
            if (!$materia) {
                return false;
            }
            
            $sql = "UPDATE materias_primas SET activo = 1 WHERE id = ?";
            $resultado = db_execute($sql, [$id]);
            
            if ($resultado) {
                registrar_auditoria('UPDATE', 'materias_primas', $id, 
                    "Materia prima reactivada: {$materia['nombre']}");
            }
            
            return $resultado;
            
        } catch (PDOException $e) {
            registrar_error("Error al reactivar materia prima: " . $e->getMessage());
            return false;
        }
    }
    
    // ========================================
    // MÉTODOS DE VALIDACIÓN
    // ========================================
    
    /**
     * Valida los datos de una materia prima
     * 
     * @param array $datos Datos a validar
     * @param int $id ID de la materia prima (para actualización)
     * @return array Array de errores
     */
    public static function validar($datos, $id = null) {
        $errores = [];
        
        // Nombre requerido
        if (empty($datos['nombre'])) {
            $errores[] = 'El nombre es requerido';
        }
        
        // Tipo requerido - valores REALES: oro, plata, piedra, otro
        if (empty($datos['tipo'])) {
            $errores[] = 'El tipo es requerido';
        } elseif (!in_array($datos['tipo'], ['oro', 'plata', 'piedra', 'otro'])) {
            $errores[] = 'El tipo no es válido (oro, plata, piedra, otro)';
        }
        
        // Unidad de medida requerida - valores REALES: gramos, piezas, quilates
        if (empty($datos['unidad_medida'])) {
            $errores[] = 'La unidad de medida es requerida';
        } elseif (!in_array($datos['unidad_medida'], ['gramos', 'piezas', 'quilates'])) {
            $errores[] = 'La unidad de medida no es válida (gramos, piezas, quilates)';
        }
        
        // Precio por unidad opcional pero debe ser positivo si se proporciona
        if (isset($datos['precio_por_unidad']) && $datos['precio_por_unidad'] !== null && !validar_decimal_positivo($datos['precio_por_unidad'])) {
            $errores[] = 'El precio por unidad debe ser un número positivo';
        }
        
        // Cantidad disponible debe ser positiva
        if (isset($datos['cantidad_disponible']) && !validar_decimal_positivo($datos['cantidad_disponible'])) {
            $errores[] = 'La cantidad disponible debe ser un número positivo';
        }
        
        // Stock mínimo debe ser positivo
        if (isset($datos['stock_minimo']) && !validar_decimal_positivo($datos['stock_minimo'])) {
            $errores[] = 'El stock mínimo debe ser un número positivo';
        }
        
        return $errores;
    }
    
    /**
     * Verifica si existe una materia prima
     * 
     * @param int $id ID de la materia prima
     * @return bool
     */
    public static function existe($id) {
        return db_exists('materias_primas', 'id = ?', [$id]);
    }
    
    /**
     * Verifica si hay cantidad suficiente
     * 
     * @param int $id ID de la materia prima
     * @param float $cantidad Cantidad requerida
     * @return bool
     */
    public static function hayCantidadSuficiente($id, $cantidad) {
        $materia = self::obtenerPorId($id);
        
        if (!$materia) {
            return false;
        }
        
        return $materia['cantidad_disponible'] >= $cantidad;
    }
    
    // ========================================
    // MÉTODOS AUXILIARES
    // ========================================
    
    /**
     * Obtiene estadísticas de materias primas
     * 
     * @return array Array con estadísticas
     */
    public static function obtenerEstadisticas() {
        global $pdo;
        
        try {
            $stats = [];
            
            $where = 'activo = 1';
            
            // Total por tipo
            $sql = "SELECT tipo, COUNT(*) as total, 
                           SUM(cantidad_disponible * COALESCE(precio_por_unidad, 0)) as valor_total
                    FROM materias_primas
                    WHERE $where
                    GROUP BY tipo";
            $stats['por_tipo'] = db_query($sql);
            
            // Valor total del inventario
            $sql = "SELECT SUM(cantidad_disponible * COALESCE(precio_por_unidad, 0)) as total
                    FROM materias_primas
                    WHERE $where";
            $resultado = db_query_one($sql);
            $stats['valor_total'] = $resultado['total'] ?? 0;
            
            // Total de materias primas
            $stats['total_materias'] = db_count('materias_primas', $where);
            
            // Con stock bajo
            $sql = "SELECT COUNT(*) as total FROM materias_primas 
                    WHERE cantidad_disponible <= stock_minimo AND activo = 1";
            $resultado = db_query_one($sql);
            $stats['stock_bajo'] = $resultado['total'] ?? 0;
            
            return $stats;
            
        } catch (Exception $e) {
            registrar_error("Error al obtener estadísticas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Calcula el valor total de una materia prima
     * 
     * @param int $id ID de la materia prima
     * @return float Valor total
     */
    public static function calcularValorTotal($id) {
        $materia = self::obtenerPorId($id);
        
        if (!$materia) {
            return 0;
        }
        
        $precio = $materia['precio_por_unidad'] ?? 0;
        return $materia['cantidad_disponible'] * $precio;
    }
}
?>