<?php
// ================================================
// MODELO: PRODUCTO
// Sistema de Gestión - Joyería Torre Fuerte
// ================================================

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/funciones.php';

class Producto {
    
    // ========================================
    // MÉTODOS DE CONSULTA (SELECT)
    // ========================================
    
    /**
     * Obtiene todos los productos con paginación y filtros
     * 
     * @param array $filtros Array de filtros (categoria_id, activo, busqueda)
     * @param int $pagina Número de página
     * @param int $por_pagina Items por página
     * @return array Array de productos
     */
    public static function listar($filtros = [], $pagina = 1, $por_pagina = 20) {
        global $pdo;
        
        $where = ['1=1'];
        $params = [];
        
        // Filtro por categoría
        if (isset($filtros['categoria_id']) && $filtros['categoria_id'] > 0) {
            $where[] = 'p.categoria_id = ?';
            $params[] = $filtros['categoria_id'];
        }
        
        // Filtro por estado activo
        if (isset($filtros['activo'])) {
            $where[] = 'p.activo = ?';
            $params[] = $filtros['activo'];
        } else {
            // Por defecto solo activos
            $where[] = 'p.activo = 1';
        }
        
        // Filtro por búsqueda (código, nombre, descripción)
        if (isset($filtros['busqueda']) && !empty($filtros['busqueda'])) {
            $where[] = '(p.codigo LIKE ? OR p.nombre LIKE ? OR p.descripcion LIKE ? OR p.codigo_barras LIKE ?)';
            $termino = '%' . $filtros['busqueda'] . '%';
            $params[] = $termino;
            $params[] = $termino;
            $params[] = $termino;
            $params[] = $termino;
        }
        
        // Filtro por proveedor
        if (isset($filtros['proveedor_id']) && $filtros['proveedor_id'] > 0) {
            $where[] = 'p.proveedor_id = ?';
            $params[] = $filtros['proveedor_id'];
        }
        
        // Filtro por productos por peso
        if (isset($filtros['es_por_peso'])) {
            $where[] = 'p.es_por_peso = ?';
            $params[] = $filtros['es_por_peso'];
        }
        
        $where_sql = implode(' AND ', $where);
        
        // Calcular offset
        $offset = ($pagina - 1) * $por_pagina;
        
        // Consulta principal
        $sql = "SELECT p.*, 
                       c.nombre as categoria_nombre,
                       pr.nombre as proveedor_nombre,
                       (SELECT precio FROM precios_producto WHERE producto_id = p.id AND tipo_precio = 'publico' AND activo = 1 LIMIT 1) as precio_publico
                FROM productos p
                LEFT JOIN categorias c ON p.categoria_id = c.id
                LEFT JOIN proveedores pr ON p.proveedor_id = pr.id
                WHERE $where_sql
                ORDER BY p.fecha_creacion DESC
                LIMIT ? OFFSET ?";
        
        $params[] = $por_pagina;
        $params[] = $offset;
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            registrar_error("Error al listar productos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Cuenta el total de productos según filtros
     * 
     * @param array $filtros Array de filtros
     * @return int Total de productos
     */
    public static function contar($filtros = []) {
        global $pdo;
        
        $where = ['1=1'];
        $params = [];
        
        if (isset($filtros['categoria_id']) && $filtros['categoria_id'] > 0) {
            $where[] = 'categoria_id = ?';
            $params[] = $filtros['categoria_id'];
        }
        
        if (isset($filtros['activo'])) {
            $where[] = 'activo = ?';
            $params[] = $filtros['activo'];
        } else {
            $where[] = 'activo = 1';
        }
        
        if (isset($filtros['busqueda']) && !empty($filtros['busqueda'])) {
            $where[] = '(codigo LIKE ? OR nombre LIKE ? OR descripcion LIKE ? OR codigo_barras LIKE ?)';
            $termino = '%' . $filtros['busqueda'] . '%';
            $params[] = $termino;
            $params[] = $termino;
            $params[] = $termino;
            $params[] = $termino;
        }
        
        $where_sql = implode(' AND ', $where);
        
        return db_count('productos', $where_sql, $params);
    }
    
    /**
     * Obtiene un producto por su ID con todos sus precios
     * 
     * @param int $id ID del producto
     * @return array|false Producto con precios o false
     */
    public static function obtenerPorId($id) {
        global $pdo;
        
        $sql = "SELECT p.*, 
                       c.nombre as categoria_nombre,
                       pr.nombre as proveedor_nombre
                FROM productos p
                LEFT JOIN categorias c ON p.categoria_id = c.id
                LEFT JOIN proveedores pr ON p.proveedor_id = pr.id
                WHERE p.id = ?";
        
        try {
            $producto = db_query_one($sql, [$id]);
            
            if ($producto) {
                // Obtener todos los precios
                $producto['precios'] = self::obtenerPrecios($id);
            }
            
            return $producto;
        } catch (PDOException $e) {
            registrar_error("Error al obtener producto: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene un producto por su código
     * 
     * @param string $codigo Código del producto
     * @return array|false Producto o false
     */
    public static function obtenerPorCodigo($codigo) {
        global $pdo;
        
        $sql = "SELECT p.*, 
                       c.nombre as categoria_nombre,
                       (SELECT precio FROM precios_producto WHERE producto_id = p.id AND tipo_precio = 'publico' AND activo = 1 LIMIT 1) as precio_publico
                FROM productos p
                LEFT JOIN categorias c ON p.categoria_id = c.id
                WHERE p.codigo = ? AND p.activo = 1";
        
        try {
            $producto = db_query_one($sql, [$codigo]);
            
            if ($producto) {
                $producto['precios'] = self::obtenerPrecios($producto['id']);
            }
            
            return $producto;
        } catch (PDOException $e) {
            registrar_error("Error al obtener producto por código: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene un producto por código de barras
     * 
     * @param string $codigo_barras Código de barras
     * @return array|false Producto o false
     */
    public static function obtenerPorCodigoBarras($codigo_barras) {
        global $pdo;
        
        $sql = "SELECT p.*, 
                       c.nombre as categoria_nombre,
                       (SELECT precio FROM precios_producto WHERE producto_id = p.id AND tipo_precio = 'publico' AND activo = 1 LIMIT 1) as precio_publico
                FROM productos p
                LEFT JOIN categorias c ON p.categoria_id = c.id
                WHERE p.codigo_barras = ? AND p.activo = 1";
        
        try {
            $producto = db_query_one($sql, [$codigo_barras]);
            
            if ($producto) {
                $producto['precios'] = self::obtenerPrecios($producto['id']);
            }
            
            return $producto;
        } catch (PDOException $e) {
            registrar_error("Error al obtener producto por código de barras: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Busca productos por término (autocompletado)
     * 
     * @param string $termino Término de búsqueda
     * @param int $limite Límite de resultados
     * @return array Array de productos
     */
    public static function buscar($termino, $limite = 10) {
        global $pdo;
        
        $sql = "SELECT p.id, p.codigo, p.nombre, p.codigo_barras, p.es_por_peso,
                       c.nombre as categoria_nombre,
                       (SELECT precio FROM precios_producto WHERE producto_id = p.id AND tipo_precio = 'publico' AND activo = 1 LIMIT 1) as precio_publico
                FROM productos p
                LEFT JOIN categorias c ON p.categoria_id = c.id
                WHERE (p.codigo LIKE ? OR p.nombre LIKE ? OR p.codigo_barras LIKE ?)
                AND p.activo = 1
                ORDER BY p.nombre
                LIMIT ?";
        
        $termino_like = '%' . $termino . '%';
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$termino_like, $termino_like, $termino_like, $limite]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            registrar_error("Error al buscar productos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene todos los precios de un producto
     * 
     * @param int $producto_id ID del producto
     * @return array Array de precios
     */
    public static function obtenerPrecios($producto_id) {
        $sql = "SELECT * FROM precios_producto 
                WHERE producto_id = ? AND activo = 1
                ORDER BY tipo_precio";
        
        try {
            return db_query($sql, [$producto_id]);
        } catch (PDOException $e) {
            registrar_error("Error al obtener precios: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene un precio específico de un producto
     * 
     * @param int $producto_id ID del producto
     * @param string $tipo_precio Tipo de precio (publico, mayorista, descuento, especial)
     * @return float|false Precio o false
     */
    public static function obtenerPrecio($producto_id, $tipo_precio = 'publico') {
        $sql = "SELECT precio FROM precios_producto 
                WHERE producto_id = ? AND tipo_precio = ? AND activo = 1
                LIMIT 1";
        
        try {
            $resultado = db_query_one($sql, [$producto_id, $tipo_precio]);
            return $resultado ? $resultado['precio'] : false;
        } catch (PDOException $e) {
            registrar_error("Error al obtener precio: " . $e->getMessage());
            return false;
        }
    }
    
    // ========================================
    // MÉTODOS DE CREACIÓN (INSERT)
    // ========================================
    
    /**
     * Crea un nuevo producto con sus precios
     * 
     * @param array $datos Datos del producto
     * @param array $precios Array de precios [tipo_precio => monto]
     * @return int|false ID del producto creado o false
     */
    public static function crear($datos, $precios = []) {
        global $pdo;
        
        // Validar datos
        $errores = self::validar($datos);
        if (!empty($errores)) {
            return false;
        }
        
        // Iniciar transacción
        $pdo->beginTransaction();
        
        try {
            // Generar código si no se proporcionó
            if (empty($datos['codigo'])) {
                $datos['codigo'] = generar_codigo_producto('PROD', 6);
            }
            
            // Insertar producto
            $sql = "INSERT INTO productos (
                        codigo, codigo_barras, nombre, descripcion, 
                        categoria_id, proveedor_id, es_por_peso, peso_gramos,
                        estilo, largo_cm, imagen, activo
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $params = [
                $datos['codigo'],
                $datos['codigo_barras'] ?? null,
                $datos['nombre'],
                $datos['descripcion'] ?? null,
                $datos['categoria_id'],
                $datos['proveedor_id'] ?? null,
                $datos['es_por_peso'] ?? 0,
                $datos['peso_gramos'] ?? null,
                $datos['estilo'] ?? null,
                $datos['largo_cm'] ?? null,
                $datos['imagen'] ?? null,
                $datos['activo'] ?? 1
            ];
            
            $producto_id = db_execute($sql, $params);
            
            if (!$producto_id) {
                throw new Exception("Error al crear producto");
            }
            
            // Insertar precios
            if (!empty($precios)) {
                foreach ($precios as $tipo_precio => $precio) {
                    if ($precio > 0) {
                        $sql_precio = "INSERT INTO precios_producto (producto_id, tipo_precio, precio, activo)
                                      VALUES (?, ?, ?, 1)";
                        db_execute($sql_precio, [$producto_id, $tipo_precio, $precio]);
                    }
                }
            }
            
            // Registrar auditoría
            registrar_auditoria('INSERT', 'productos', $producto_id, 
                "Producto creado: {$datos['nombre']} (Código: {$datos['codigo']})");
            
            // Confirmar transacción
            $pdo->commit();
            
            return $producto_id;
            
        } catch (Exception $e) {
            // Revertir transacción
            $pdo->rollBack();
            registrar_error("Error al crear producto: " . $e->getMessage());
            return false;
        }
    }
    
    // ========================================
    // MÉTODOS DE ACTUALIZACIÓN (UPDATE)
    // ========================================
    
    /**
     * Actualiza un producto
     * 
     * @param int $id ID del producto
     * @param array $datos Datos a actualizar
     * @param array $precios Array de precios (opcional)
     * @return bool
     */
    public static function actualizar($id, $datos, $precios = null) {
        global $pdo;
        
        // Validar que el producto existe
        if (!self::existe($id)) {
            return false;
        }
        
        // Validar datos
        $errores = self::validar($datos, $id);
        if (!empty($errores)) {
            return false;
        }
        
        // Iniciar transacción
        $pdo->beginTransaction();
        
        try {
            // Actualizar producto
            $sql = "UPDATE productos SET
                        codigo = ?,
                        codigo_barras = ?,
                        nombre = ?,
                        descripcion = ?,
                        categoria_id = ?,
                        proveedor_id = ?,
                        es_por_peso = ?,
                        peso_gramos = ?,
                        estilo = ?,
                        largo_cm = ?,
                        imagen = ?
                    WHERE id = ?";
            
            $params = [
                $datos['codigo'],
                $datos['codigo_barras'] ?? null,
                $datos['nombre'],
                $datos['descripcion'] ?? null,
                $datos['categoria_id'],
                $datos['proveedor_id'] ?? null,
                $datos['es_por_peso'] ?? 0,
                $datos['peso_gramos'] ?? null,
                $datos['estilo'] ?? null,
                $datos['largo_cm'] ?? null,
                $datos['imagen'] ?? null,
                $id
            ];
            
            db_execute($sql, $params);
            
            // Actualizar precios si se proporcionaron
            if ($precios !== null) {
                // Desactivar precios anteriores
                $sql_desactivar = "UPDATE precios_producto SET activo = 0 WHERE producto_id = ?";
                db_execute($sql_desactivar, [$id]);
                
                // Insertar nuevos precios
                foreach ($precios as $tipo_precio => $precio) {
                    if ($precio > 0) {
                        // Verificar si existe el precio
                        $sql_existe = "SELECT id FROM precios_producto 
                                      WHERE producto_id = ? AND tipo_precio = ?";
                        $existe = db_query_one($sql_existe, [$id, $tipo_precio]);
                        
                        if ($existe) {
                            // Actualizar precio existente
                            $sql_update = "UPDATE precios_producto 
                                          SET precio = ?, activo = 1 
                                          WHERE id = ?";
                            db_execute($sql_update, [$precio, $existe['id']]);
                        } else {
                            // Insertar nuevo precio
                            $sql_insert = "INSERT INTO precios_producto (producto_id, tipo_precio, precio, activo)
                                          VALUES (?, ?, ?, 1)";
                            db_execute($sql_insert, [$id, $tipo_precio, $precio]);
                        }
                    }
                }
            }
            
            // Registrar auditoría
            registrar_auditoria('UPDATE', 'productos', $id, 
                "Producto actualizado: {$datos['nombre']}");
            
            // Confirmar transacción
            $pdo->commit();
            
            return true;
            
        } catch (Exception $e) {
            // Revertir transacción
            $pdo->rollBack();
            registrar_error("Error al actualizar producto: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualiza solo la imagen de un producto
     * 
     * @param int $id ID del producto
     * @param string $ruta_imagen Ruta de la imagen
     * @return bool
     */
    public static function actualizarImagen($id, $ruta_imagen) {
        global $pdo;
        
        try {
            // Obtener imagen anterior para eliminarla
            $producto = self::obtenerPorId($id);
            if ($producto && !empty($producto['imagen'])) {
                eliminar_archivo($producto['imagen']);
            }
            
            // Actualizar imagen
            $sql = "UPDATE productos SET imagen = ? WHERE id = ?";
            $resultado = db_execute($sql, [$ruta_imagen, $id]);
            
            if ($resultado) {
                registrar_auditoria('UPDATE', 'productos', $id, "Imagen actualizada");
            }
            
            return $resultado;
            
        } catch (Exception $e) {
            registrar_error("Error al actualizar imagen: " . $e->getMessage());
            return false;
        }
    }
    
    // ========================================
    // MÉTODOS DE ELIMINACIÓN (SOFT DELETE)
    // ========================================
    
    /**
     * Elimina (desactiva) un producto
     * 
     * @param int $id ID del producto
     * @return bool
     */
    public static function eliminar($id) {
        global $pdo;
        
        try {
            // Obtener nombre del producto para auditoría
            $producto = self::obtenerPorId($id);
            
            if (!$producto) {
                return false;
            }
            
            // Soft delete
            $sql = "UPDATE productos SET activo = 0 WHERE id = ?";
            $resultado = db_execute($sql, [$id]);
            
            if ($resultado) {
                registrar_auditoria('DELETE', 'productos', $id, 
                    "Producto desactivado: {$producto['nombre']}");
            }
            
            return $resultado;
            
        } catch (Exception $e) {
            registrar_error("Error al eliminar producto: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Reactiva un producto
     * 
     * @param int $id ID del producto
     * @return bool
     */
    public static function reactivar($id) {
        global $pdo;
        
        try {
            $producto = self::obtenerPorId($id);
            
            if (!$producto) {
                return false;
            }
            
            $sql = "UPDATE productos SET activo = 1 WHERE id = ?";
            $resultado = db_execute($sql, [$id]);
            
            if ($resultado) {
                registrar_auditoria('UPDATE', 'productos', $id, 
                    "Producto reactivado: {$producto['nombre']}");
            }
            
            return $resultado;
            
        } catch (Exception $e) {
            registrar_error("Error al reactivar producto: " . $e->getMessage());
            return false;
        }
    }
    
    // ========================================
    // MÉTODOS DE VALIDACIÓN
    // ========================================
    
    /**
     * Valida los datos de un producto
     * 
     * @param array $datos Datos a validar
     * @param int $id ID del producto (para actualización)
     * @return array Array de errores
     */
    public static function validar($datos, $id = null) {
        $errores = [];
        
        // Código requerido
        if (empty($datos['codigo'])) {
            $errores[] = 'El código es requerido';
        } else {
            // Verificar que el código no exista
            if (self::existeCodigo($datos['codigo'], $id)) {
                $errores[] = 'El código ya está en uso';
            }
        }
        
        // Código de barras único (si se proporciona)
        if (!empty($datos['codigo_barras'])) {
            if (!validar_codigo_barras($datos['codigo_barras'])) {
                $errores[] = 'El código de barras no es válido';
            }
            if (self::existeCodigoBarras($datos['codigo_barras'], $id)) {
                $errores[] = 'El código de barras ya está en uso';
            }
        }
        
        // Nombre requerido
        if (empty($datos['nombre'])) {
            $errores[] = 'El nombre es requerido';
        }
        
        // Categoría requerida
        if (empty($datos['categoria_id']) || $datos['categoria_id'] <= 0) {
            $errores[] = 'La categoría es requerida';
        }
        
        // Si es por peso, validar peso
        if (!empty($datos['es_por_peso']) && $datos['es_por_peso'] == 1) {
            if (empty($datos['peso_gramos']) || !validar_decimal_positivo($datos['peso_gramos'])) {
                $errores[] = 'El peso en gramos es requerido para productos por peso';
            }
        }
        
        // Validar largo si se proporciona
        if (!empty($datos['largo_cm']) && !validar_decimal_positivo($datos['largo_cm'])) {
            $errores[] = 'El largo debe ser un número positivo';
        }
        
        return $errores;
    }
    
    /**
     * Verifica si existe un producto
     * 
     * @param int $id ID del producto
     * @return bool
     */
    public static function existe($id) {
        return db_exists('productos', 'id = ?', [$id]);
    }
    
    /**
     * Verifica si un código ya existe
     * 
     * @param string $codigo Código a verificar
     * @param int $excluir_id ID a excluir de la búsqueda
     * @return bool
     */
    public static function existeCodigo($codigo, $excluir_id = null) {
        if ($excluir_id) {
            return db_exists('productos', 'codigo = ? AND id != ?', [$codigo, $excluir_id]);
        }
        return db_exists('productos', 'codigo = ?', [$codigo]);
    }
    
    /**
     * Verifica si un código de barras ya existe
     * 
     * @param string $codigo_barras Código de barras a verificar
     * @param int $excluir_id ID a excluir de la búsqueda
     * @return bool
     */
    public static function existeCodigoBarras($codigo_barras, $excluir_id = null) {
        if ($excluir_id) {
            return db_exists('productos', 'codigo_barras = ? AND id != ?', [$codigo_barras, $excluir_id]);
        }
        return db_exists('productos', 'codigo_barras = ?', [$codigo_barras]);
    }
    
    // ========================================
    // MÉTODOS AUXILIARES
    // ========================================
    
    /**
     * Obtiene estadísticas de productos
     * 
     * @return array Array con estadísticas
     */
    public static function obtenerEstadisticas() {
        global $pdo;
        
        try {
            $stats = [];
            
            // Total de productos activos
            $stats['total_activos'] = db_count('productos', 'activo = 1');
            
            // Total de productos inactivos
            $stats['total_inactivos'] = db_count('productos', 'activo = 0');
            
            // Productos por peso
            $stats['por_peso'] = db_count('productos', 'es_por_peso = 1 AND activo = 1');
            
            // Productos por categoría (top 5)
            $sql = "SELECT c.nombre, COUNT(*) as total
                    FROM productos p
                    INNER JOIN categorias c ON p.categoria_id = c.id
                    WHERE p.activo = 1
                    GROUP BY c.id, c.nombre
                    ORDER BY total DESC
                    LIMIT 5";
            $stats['por_categoria'] = db_query($sql);
            
            return $stats;
            
        } catch (Exception $e) {
            registrar_error("Error al obtener estadísticas: " . $e->getMessage());
            return [];
        }
    }
}
?>