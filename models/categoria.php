<?php
// ================================================
// MODELO: CATEGORIA
// Sistema de Gestión - Joyería Torre Fuerte
// ================================================

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/funciones.php';

class Categoria {
    
    // ========================================
    // MÉTODOS DE CONSULTA (SELECT)
    // ========================================
    
    /**
     * Obtiene todas las categorías con filtros
     * 
     * @param array $filtros Array de filtros (tipo_clasificacion, activo)
     * @return array Array de categorías
     */
    public static function listar($filtros = []) {
        global $pdo;
        
        $where = ['1=1'];
        $params = [];
        
        // Filtro por tipo de clasificación
        if (isset($filtros['tipo_clasificacion']) && !empty($filtros['tipo_clasificacion'])) {
            $where[] = 'tipo_clasificacion = ?';
            $params[] = $filtros['tipo_clasificacion'];
        }
        
        // Filtro por estado activo
        if (isset($filtros['activo'])) {
            $where[] = 'activo = ?';
            $params[] = $filtros['activo'];
        } else {
            // Por defecto solo activas
            $where[] = 'activo = 1';
        }
        
        // Filtro por categoría padre (para obtener subcategorías)
        if (isset($filtros['categoria_padre_id'])) {
            if ($filtros['categoria_padre_id'] === null) {
                $where[] = 'categoria_padre_id IS NULL';
            } else {
                $where[] = 'categoria_padre_id = ?';
                $params[] = $filtros['categoria_padre_id'];
            }
        }
        
        $where_sql = implode(' AND ', $where);
        
        $sql = "SELECT c.*, 
                       cp.nombre as categoria_padre_nombre,
                       (SELECT COUNT(*) FROM productos WHERE categoria_id = c.id AND activo = 1) as total_productos
                FROM categorias c
                LEFT JOIN categorias cp ON c.categoria_padre_id = cp.id
                WHERE $where_sql
                ORDER BY c.tipo_clasificacion, c.nombre";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            registrar_error("Error al listar categorías: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene categorías agrupadas por tipo de clasificación
     * 
     * @param bool $solo_activas Si solo incluir categorías activas
     * @return array Array agrupado por tipo_clasificacion
     */
    public static function listarPorTipo($solo_activas = true) {
        $filtros = $solo_activas ? ['activo' => 1] : [];
        $categorias = self::listar($filtros);
        
        $agrupadas = [
            'tipo' => [],
            'material' => [],
            'peso' => []
        ];
        
        foreach ($categorias as $categoria) {
            $agrupadas[$categoria['tipo_clasificacion']][] = $categoria;
        }
        
        return $agrupadas;
    }
    
    /**
     * Obtiene una categoría por su ID
     * 
     * @param int $id ID de la categoría
     * @return array|false Categoría o false
     */
    public static function obtenerPorId($id) {
        global $pdo;
        
        $sql = "SELECT c.*, 
                       cp.nombre as categoria_padre_nombre,
                       (SELECT COUNT(*) FROM productos WHERE categoria_id = c.id AND activo = 1) as total_productos
                FROM categorias c
                LEFT JOIN categorias cp ON c.categoria_padre_id = cp.id
                WHERE c.id = ?";
        
        try {
            $categoria = db_query_one($sql, [$id]);
            
            if ($categoria) {
                // Obtener subcategorías si es categoría padre
                $categoria['subcategorias'] = self::listar(['categoria_padre_id' => $id]);
            }
            
            return $categoria;
        } catch (PDOException $e) {
            registrar_error("Error al obtener categoría: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene categorías principales (sin padre)
     * 
     * @param bool $solo_activas Si solo incluir activas
     * @return array Array de categorías principales
     */
    public static function obtenerPrincipales($solo_activas = true) {
        $filtros = [
            'categoria_padre_id' => null
        ];
        
        if ($solo_activas) {
            $filtros['activo'] = 1;
        }
        
        return self::listar($filtros);
    }
    
    /**
     * Obtiene las subcategorías de una categoría padre
     * 
     * @param int $categoria_padre_id ID de la categoría padre
     * @param bool $solo_activas Si solo incluir activas
     * @return array Array de subcategorías
     */
    public static function obtenerSubcategorias($categoria_padre_id, $solo_activas = true) {
        $filtros = [
            'categoria_padre_id' => $categoria_padre_id
        ];
        
        if ($solo_activas) {
            $filtros['activo'] = 1;
        }
        
        return self::listar($filtros);
    }
    
    /**
     * Obtiene categorías para un select HTML
     * 
     * @param string $tipo_clasificacion Filtrar por tipo (opcional)
     * @return array Array de categorías [id => nombre]
     */
    public static function obtenerParaSelect($tipo_clasificacion = null) {
        $filtros = ['activo' => 1];
        
        if ($tipo_clasificacion) {
            $filtros['tipo_clasificacion'] = $tipo_clasificacion;
        }
        
        $categorias = self::listar($filtros);
        
        $opciones = [];
        foreach ($categorias as $categoria) {
            $nombre = $categoria['nombre'];
            if ($categoria['categoria_padre_nombre']) {
                $nombre = $categoria['categoria_padre_nombre'] . ' > ' . $nombre;
            }
            $opciones[$categoria['id']] = $nombre;
        }
        
        return $opciones;
    }
    
    // ========================================
    // MÉTODOS DE CREACIÓN (INSERT)
    // ========================================
    
    /**
     * Crea una nueva categoría
     * 
     * @param array $datos Datos de la categoría
     * @return int|false ID de la categoría creada o false
     */
    public static function crear($datos) {
        global $pdo;
        
        // Validar datos
        $errores = self::validar($datos);
        if (!empty($errores)) {
            return false;
        }
        
        try {
            $sql = "INSERT INTO categorias (
                        nombre, descripcion, tipo_clasificacion, 
                        categoria_padre_id, activo
                    ) VALUES (?, ?, ?, ?, ?)";
            
            $params = [
                $datos['nombre'],
                $datos['descripcion'] ?? null,
                $datos['tipo_clasificacion'],
                $datos['categoria_padre_id'] ?? null,
                $datos['activo'] ?? 1
            ];
            
            $categoria_id = db_execute($sql, $params);
            
            if ($categoria_id) {
                registrar_auditoria('INSERT', 'categorias', $categoria_id, 
                    "Categoría creada: {$datos['nombre']} (Tipo: {$datos['tipo_clasificacion']})");
            }
            
            return $categoria_id;
            
        } catch (PDOException $e) {
            registrar_error("Error al crear categoría: " . $e->getMessage());
            return false;
        }
    }
    
    // ========================================
    // MÉTODOS DE ACTUALIZACIÓN (UPDATE)
    // ========================================
    
    /**
     * Actualiza una categoría
     * 
     * @param int $id ID de la categoría
     * @param array $datos Datos a actualizar
     * @return bool
     */
    public static function actualizar($id, $datos) {
        global $pdo;
        
        // Validar que la categoría existe
        if (!self::existe($id)) {
            return false;
        }
        
        // Validar que no sea su propia subcategoría
        if (isset($datos['categoria_padre_id']) && $datos['categoria_padre_id'] == $id) {
            return false;
        }
        
        // Validar datos
        $errores = self::validar($datos, $id);
        if (!empty($errores)) {
            return false;
        }
        
        try {
            $sql = "UPDATE categorias SET
                        nombre = ?,
                        descripcion = ?,
                        tipo_clasificacion = ?,
                        categoria_padre_id = ?
                    WHERE id = ?";
            
            $params = [
                $datos['nombre'],
                $datos['descripcion'] ?? null,
                $datos['tipo_clasificacion'],
                $datos['categoria_padre_id'] ?? null,
                $id
            ];
            
            $resultado = db_execute($sql, $params);
            
            if ($resultado) {
                registrar_auditoria('UPDATE', 'categorias', $id, 
                    "Categoría actualizada: {$datos['nombre']}");
            }
            
            return $resultado;
            
        } catch (PDOException $e) {
            registrar_error("Error al actualizar categoría: " . $e->getMessage());
            return false;
        }
    }
    
    // ========================================
    // MÉTODOS DE ELIMINACIÓN (SOFT DELETE)
    // ========================================
    
    /**
     * Elimina (desactiva) una categoría
     * 
     * @param int $id ID de la categoría
     * @return bool
     */
    public static function eliminar($id) {
        global $pdo;
        
        try {
            // Verificar que no tenga productos activos
            $total_productos = db_count('productos', 'categoria_id = ? AND activo = 1', [$id]);
            
            if ($total_productos > 0) {
                return false; // No se puede eliminar si tiene productos
            }
            
            // Verificar que no tenga subcategorías activas
            $total_subcategorias = db_count('categorias', 'categoria_padre_id = ? AND activo = 1', [$id]);
            
            if ($total_subcategorias > 0) {
                return false; // No se puede eliminar si tiene subcategorías
            }
            
            // Obtener nombre para auditoría
            $categoria = self::obtenerPorId($id);
            
            if (!$categoria) {
                return false;
            }
            
            // Soft delete
            $sql = "UPDATE categorias SET activo = 0 WHERE id = ?";
            $resultado = db_execute($sql, [$id]);
            
            if ($resultado) {
                registrar_auditoria('DELETE', 'categorias', $id, 
                    "Categoría desactivada: {$categoria['nombre']}");
            }
            
            return $resultado;
            
        } catch (PDOException $e) {
            registrar_error("Error al eliminar categoría: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Reactiva una categoría
     * 
     * @param int $id ID de la categoría
     * @return bool
     */
    public static function reactivar($id) {
        global $pdo;
        
        try {
            $categoria = self::obtenerPorId($id);
            
            if (!$categoria) {
                return false;
            }
            
            $sql = "UPDATE categorias SET activo = 1 WHERE id = ?";
            $resultado = db_execute($sql, [$id]);
            
            if ($resultado) {
                registrar_auditoria('UPDATE', 'categorias', $id, 
                    "Categoría reactivada: {$categoria['nombre']}");
            }
            
            return $resultado;
            
        } catch (PDOException $e) {
            registrar_error("Error al reactivar categoría: " . $e->getMessage());
            return false;
        }
    }
    
    // ========================================
    // MÉTODOS DE VALIDACIÓN
    // ========================================
    
    /**
     * Valida los datos de una categoría
     * 
     * @param array $datos Datos a validar
     * @param int $id ID de la categoría (para actualización)
     * @return array Array de errores
     */
    public static function validar($datos, $id = null) {
        $errores = [];
        
        // Nombre requerido
        if (empty($datos['nombre'])) {
            $errores[] = 'El nombre es requerido';
        } else {
            // Verificar que el nombre no exista en el mismo tipo
            if (isset($datos['tipo_clasificacion'])) {
                if (self::existeNombre($datos['nombre'], $datos['tipo_clasificacion'], $id)) {
                    $errores[] = 'Ya existe una categoría con ese nombre en este tipo';
                }
            }
        }
        
        // Tipo de clasificación requerido
        if (empty($datos['tipo_clasificacion'])) {
            $errores[] = 'El tipo de clasificación es requerido';
        } elseif (!in_array($datos['tipo_clasificacion'], ['tipo', 'material', 'peso'])) {
            $errores[] = 'El tipo de clasificación no es válido';
        }
        
        // Validar categoría padre si se proporciona
        if (isset($datos['categoria_padre_id']) && !empty($datos['categoria_padre_id'])) {
            if (!self::existe($datos['categoria_padre_id'])) {
                $errores[] = 'La categoría padre no existe';
            }
            
            // No puede ser su propia categoría padre
            if ($id && $datos['categoria_padre_id'] == $id) {
                $errores[] = 'Una categoría no puede ser su propia subcategoría';
            }
        }
        
        return $errores;
    }
    
    /**
     * Verifica si existe una categoría
     * 
     * @param int $id ID de la categoría
     * @return bool
     */
    public static function existe($id) {
        return db_exists('categorias', 'id = ?', [$id]);
    }
    
    /**
     * Verifica si un nombre ya existe en un tipo de clasificación
     * 
     * @param string $nombre Nombre a verificar
     * @param string $tipo_clasificacion Tipo de clasificación
     * @param int $excluir_id ID a excluir de la búsqueda
     * @return bool
     */
    public static function existeNombre($nombre, $tipo_clasificacion, $excluir_id = null) {
        if ($excluir_id) {
            return db_exists('categorias', 
                'nombre = ? AND tipo_clasificacion = ? AND id != ?', 
                [$nombre, $tipo_clasificacion, $excluir_id]);
        }
        return db_exists('categorias', 
            'nombre = ? AND tipo_clasificacion = ?', 
            [$nombre, $tipo_clasificacion]);
    }
    
    /**
     * Verifica si una categoría puede ser eliminada
     * 
     * @param int $id ID de la categoría
     * @return bool
     */
    public static function puedeEliminar($id) {
        // No puede eliminar si tiene productos activos
        $total_productos = db_count('productos', 'categoria_id = ? AND activo = 1', [$id]);
        if ($total_productos > 0) {
            return false;
        }
        
        // No puede eliminar si tiene subcategorías activas
        $total_subcategorias = db_count('categorias', 'categoria_padre_id = ? AND activo = 1', [$id]);
        if ($total_subcategorias > 0) {
            return false;
        }
        
        return true;
    }
    
    // ========================================
    // MÉTODOS AUXILIARES
    // ========================================
    
    /**
     * Obtiene estadísticas de categorías
     * 
     * @return array Array con estadísticas
     */
    public static function obtenerEstadisticas() {
        global $pdo;
        
        try {
            $stats = [];
            
            // Total por tipo de clasificación
            $sql = "SELECT tipo_clasificacion, COUNT(*) as total
                    FROM categorias
                    WHERE activo = 1
                    GROUP BY tipo_clasificacion";
            $stats['por_tipo'] = db_query($sql);
            
            // Categorías con más productos
            $sql = "SELECT c.nombre, c.tipo_clasificacion, COUNT(p.id) as total_productos
                    FROM categorias c
                    LEFT JOIN productos p ON c.id = p.categoria_id AND p.activo = 1
                    WHERE c.activo = 1
                    GROUP BY c.id, c.nombre, c.tipo_clasificacion
                    HAVING total_productos > 0
                    ORDER BY total_productos DESC
                    LIMIT 5";
            $stats['top_categorias'] = db_query($sql);
            
            // Total de categorías activas
            $stats['total_activas'] = db_count('categorias', 'activo = 1');
            
            // Total de categorías inactivas
            $stats['total_inactivas'] = db_count('categorias', 'activo = 0');
            
            return $stats;
            
        } catch (Exception $e) {
            registrar_error("Error al obtener estadísticas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene el árbol completo de categorías (jerárquico)
     * 
     * @return array Árbol de categorías
     */
    public static function obtenerArbol() {
        $categorias = self::listar(['activo' => 1]);
        
        // Organizar en árbol
        $arbol = [];
        $refs = [];
        
        // Primera pasada: crear referencias
        foreach ($categorias as $categoria) {
            $categoria['hijos'] = [];
            $refs[$categoria['id']] = $categoria;
        }
        
        // Segunda pasada: construir árbol
        foreach ($refs as $id => $categoria) {
            if ($categoria['categoria_padre_id'] === null) {
                // Es categoría principal
                $arbol[] = &$refs[$id];
            } else {
                // Es subcategoría
                if (isset($refs[$categoria['categoria_padre_id']])) {
                    $refs[$categoria['categoria_padre_id']]['hijos'][] = &$refs[$id];
                }
            }
        }
        
        return $arbol;
    }
}
?>