<?php
/**
 * Modelo PrecioProducto
 * 
 * Gestión completa de precios de productos:
 * - Múltiples tipos de precio (público, mayorista, descuento, especial)
 * - CRUD de precios
 * - Activación/Desactivación por tipo
 * - Consultas y validaciones
 * 
 * @author Sistema Joyería Torre Fuerte
 * @version 1.0
 * @date 2026-01-22
 */

class PrecioProducto {
    
    // Tipos de precio válidos
    const TIPO_PUBLICO = 'publico';
    const TIPO_MAYORISTA = 'mayorista';
    const TIPO_DESCUENTO = 'descuento';
    const TIPO_ESPECIAL = 'especial';
    
    /**
     * Crea un nuevo precio para un producto
     * 
     * @param array $datos Datos del precio
     * @return int|false ID del precio creado o false
     */
    public static function crear($datos) {
        try {
            // Validar datos
            $errores = self::validar($datos);
            if (!empty($errores)) {
                throw new Exception(implode(', ', $errores));
            }
            
            // Verificar que el producto existe
            if (!self::productoExiste($datos['producto_id'])) {
                throw new Exception('El producto no existe');
            }
            
            // Verificar que no exista ya este tipo de precio para el producto
            if (self::existePrecioTipo($datos['producto_id'], $datos['tipo_precio'])) {
                throw new Exception("Ya existe un precio tipo '{$datos['tipo_precio']}' para este producto");
            }
            
            // Insertar precio
            $sql = "INSERT INTO precios_producto (
                        producto_id, tipo_precio, precio, activo
                    ) VALUES (?, ?, ?, ?)";
            
            $resultado = db_execute($sql, [
                $datos['producto_id'],
                $datos['tipo_precio'],
                $datos['precio'],
                $datos['activo'] ?? 1
            ]);
            
            if ($resultado) {
                $precio_id = $resultado;
                
                // Registrar en auditoría
                registrar_auditoria(
                    'precios_producto',
                    'INSERT',
                    $precio_id,
                    "Precio {$datos['tipo_precio']} creado para producto {$datos['producto_id']}: " . 
                    formato_dinero($datos['precio'])
                );
                
                return $precio_id;
            }
            
            return false;
            
        } catch (Exception $e) {
            registrar_error("Error al crear precio: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Edita un precio existente
     * 
     * @param int $id ID del precio
     * @param array $datos Datos a actualizar
     * @return bool
     */
    public static function editar($id, $datos) {
        try {
            // Validar datos
            $errores = self::validar($datos, true);
            if (!empty($errores)) {
                throw new Exception(implode(', ', $errores));
            }
            
            // Verificar que el precio existe
            $precio_actual = self::obtenerPorId($id);
            if (!$precio_actual) {
                throw new Exception('Precio no encontrado');
            }
            
            // Actualizar precio
            $sql = "UPDATE precios_producto SET 
                        precio = ?,
                        activo = ?
                    WHERE id = ?";
            
            $resultado = db_execute($sql, [
                $datos['precio'],
                $datos['activo'] ?? 1,
                $id
            ]);
            
            if ($resultado) {
                registrar_auditoria(
                    'precios_producto',
                    'UPDATE',
                    $id,
                    "Precio actualizado: " . formato_dinero($datos['precio'])
                );
            }
            
            return $resultado;
            
        } catch (Exception $e) {
            registrar_error("Error al editar precio: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualiza el precio de un tipo específico para un producto
     * 
     * @param int $producto_id ID del producto
     * @param string $tipo_precio Tipo de precio
     * @param float $precio Nuevo precio
     * @return bool
     */
    public static function actualizarPrecioPorTipo($producto_id, $tipo_precio, $precio) {
        try {
            // Validar tipo de precio
            if (!in_array($tipo_precio, self::getTiposValidos())) {
                throw new Exception('Tipo de precio no válido');
            }
            
            // Validar precio
            if ($precio <= 0) {
                throw new Exception('El precio debe ser mayor a 0');
            }
            
            // Buscar el precio existente
            $precio_existente = self::obtenerPorProductoYTipo($producto_id, $tipo_precio);
            
            if ($precio_existente) {
                // Actualizar precio existente
                return self::editar($precio_existente['id'], [
                    'precio' => $precio,
                    'activo' => 1
                ]);
            } else {
                // Crear nuevo precio
                return self::crear([
                    'producto_id' => $producto_id,
                    'tipo_precio' => $tipo_precio,
                    'precio' => $precio,
                    'activo' => 1
                ]);
            }
            
        } catch (Exception $e) {
            registrar_error("Error al actualizar precio por tipo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cambia el estado de un precio (activo/inactivo)
     * 
     * @param int $id ID del precio
     * @param bool $activo Nuevo estado
     * @return bool
     */
    public static function cambiarEstado($id, $activo) {
        try {
            $sql = "UPDATE precios_producto SET activo = ? WHERE id = ?";
            $resultado = db_execute($sql, [$activo ? 1 : 0, $id]);
            
            if ($resultado) {
                $estado = $activo ? 'activado' : 'desactivado';
                registrar_auditoria(
                    'precios_producto',
                    'UPDATE',
                    $id,
                    "Precio {$estado}"
                );
            }
            
            return $resultado;
            
        } catch (Exception $e) {
            registrar_error("Error al cambiar estado de precio: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Activa un precio
     * 
     * @param int $id ID del precio
     * @return bool
     */
    public static function activar($id) {
        return self::cambiarEstado($id, true);
    }
    
    /**
     * Desactiva un precio
     * 
     * @param int $id ID del precio
     * @return bool
     */
    public static function desactivar($id) {
        return self::cambiarEstado($id, false);
    }
    
    /**
     * Obtiene un precio por ID
     * 
     * @param int $id ID del precio
     * @return array|false Datos del precio o false
     */
    public static function obtenerPorId($id) {
        try {
            $sql = "SELECT 
                        pp.*,
                        p.nombre as producto_nombre,
                        p.codigo as producto_codigo
                    FROM precios_producto pp
                    INNER JOIN productos p ON pp.producto_id = p.id
                    WHERE pp.id = ?";
            
            return db_query_one($sql, [$id]);
            
        } catch (Exception $e) {
            registrar_error("Error al obtener precio: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene todos los precios de un producto
     * 
     * @param int $producto_id ID del producto
     * @param bool $solo_activos Solo precios activos
     * @return array Lista de precios
     */
    public static function obtenerPorProducto($producto_id, $solo_activos = false) {
        try {
            $where = "pp.producto_id = ?";
            $params = [$producto_id];
            
            if ($solo_activos) {
                $where .= " AND pp.activo = 1";
            }
            
            $sql = "SELECT 
                        pp.id,
                        pp.producto_id,
                        pp.tipo_precio,
                        pp.precio,
                        pp.activo,
                        pp.fecha_creacion,
                        pp.fecha_actualizacion
                    FROM precios_producto pp
                    WHERE $where
                    ORDER BY 
                        CASE pp.tipo_precio
                            WHEN 'publico' THEN 1
                            WHEN 'mayorista' THEN 2
                            WHEN 'descuento' THEN 3
                            WHEN 'especial' THEN 4
                        END";
            
            return db_query($sql, $params);
            
        } catch (Exception $e) {
            registrar_error("Error al obtener precios por producto: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene un precio específico por producto y tipo
     * 
     * @param int $producto_id ID del producto
     * @param string $tipo_precio Tipo de precio
     * @return array|false Datos del precio o false
     */
    public static function obtenerPorProductoYTipo($producto_id, $tipo_precio) {
        try {
            $sql = "SELECT * FROM precios_producto 
                    WHERE producto_id = ? AND tipo_precio = ?";
            
            return db_query_one($sql, [$producto_id, $tipo_precio]);
            
        } catch (Exception $e) {
            registrar_error("Error al obtener precio por tipo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene todos los precios de un tipo específico
     * 
     * @param string $tipo_precio Tipo de precio
     * @param bool $solo_activos Solo precios activos
     * @return array Lista de precios
     */
    public static function obtenerPorTipo($tipo_precio, $solo_activos = true) {
        try {
            $where = ["pp.tipo_precio = ?"];
            $params = [$tipo_precio];
            
            if ($solo_activos) {
                $where[] = "pp.activo = 1";
                $where[] = "p.activo = 1";
            }
            
            $where_sql = implode(' AND ', $where);
            
            $sql = "SELECT 
                        pp.id,
                        pp.producto_id,
                        pp.precio,
                        pp.activo,
                        p.nombre as producto_nombre,
                        p.codigo as producto_codigo,
                        c.nombre as categoria_nombre
                    FROM precios_producto pp
                    INNER JOIN productos p ON pp.producto_id = p.id
                    LEFT JOIN categorias c ON p.categoria_id = c.id
                    WHERE $where_sql
                    ORDER BY p.nombre ASC";
            
            return db_query($sql, $params);
            
        } catch (Exception $e) {
            registrar_error("Error al obtener precios por tipo: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Lista todos los precios con filtros
     * 
     * @param array $filtros Filtros opcionales: tipo_precio, activo, producto_id
     * @return array Lista de precios
     */
    public static function listar($filtros = []) {
        try {
            $where = [];
            $params = [];
            
            // Filtro por tipo de precio
            if (isset($filtros['tipo_precio']) && !empty($filtros['tipo_precio'])) {
                $where[] = "pp.tipo_precio = ?";
                $params[] = $filtros['tipo_precio'];
            }
            
            // Filtro por estado
            if (isset($filtros['activo'])) {
                $where[] = "pp.activo = ?";
                $params[] = $filtros['activo'];
            }
            
            // Filtro por producto
            if (isset($filtros['producto_id']) && $filtros['producto_id'] > 0) {
                $where[] = "pp.producto_id = ?";
                $params[] = $filtros['producto_id'];
            }
            
            $where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            
            $sql = "SELECT 
                        pp.id,
                        pp.producto_id,
                        pp.tipo_precio,
                        pp.precio,
                        pp.activo,
                        pp.fecha_creacion,
                        p.nombre as producto_nombre,
                        p.codigo as producto_codigo
                    FROM precios_producto pp
                    INNER JOIN productos p ON pp.producto_id = p.id
                    $where_sql
                    ORDER BY p.nombre ASC, pp.tipo_precio ASC";
            
            return db_query($sql, $params);
            
        } catch (Exception $e) {
            registrar_error("Error al listar precios: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene el precio aplicable para un producto según el tipo
     * 
     * @param int $producto_id ID del producto
     * @param string $tipo_precio Tipo de precio a buscar
     * @return float|false Precio o false
     */
    public static function obtenerPrecioAplicable($producto_id, $tipo_precio = self::TIPO_PUBLICO) {
        try {
            $precio = self::obtenerPorProductoYTipo($producto_id, $tipo_precio);
            
            if ($precio && $precio['activo'] == 1) {
                return floatval($precio['precio']);
            }
            
            // Si no hay precio de ese tipo, buscar precio público
            if ($tipo_precio !== self::TIPO_PUBLICO) {
                $precio_publico = self::obtenerPorProductoYTipo($producto_id, self::TIPO_PUBLICO);
                if ($precio_publico && $precio_publico['activo'] == 1) {
                    return floatval($precio_publico['precio']);
                }
            }
            
            return false;
            
        } catch (Exception $e) {
            registrar_error("Error al obtener precio aplicable: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Elimina un precio (físicamente)
     * 
     * @param int $id ID del precio
     * @return bool
     */
    public static function eliminar($id) {
        try {
            $precio = self::obtenerPorId($id);
            if (!$precio) {
                throw new Exception('Precio no encontrado');
            }
            
            $sql = "DELETE FROM precios_producto WHERE id = ?";
            $resultado = db_execute($sql, [$id]);
            
            if ($resultado) {
                registrar_auditoria(
                    'precios_producto',
                    'DELETE',
                    $id,
                    "Precio {$precio['tipo_precio']} eliminado para producto {$precio['producto_id']}"
                );
            }
            
            return $resultado;
            
        } catch (Exception $e) {
            registrar_error("Error al eliminar precio: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene estadísticas de precios
     * 
     * @return array Estadísticas
     */
    public static function obtenerEstadisticas() {
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        COUNT(CASE WHEN activo = 1 THEN 1 END) as activos,
                        COUNT(CASE WHEN tipo_precio = 'publico' THEN 1 END) as tipo_publico,
                        COUNT(CASE WHEN tipo_precio = 'mayorista' THEN 1 END) as tipo_mayorista,
                        COUNT(CASE WHEN tipo_precio = 'descuento' THEN 1 END) as tipo_descuento,
                        COUNT(CASE WHEN tipo_precio = 'especial' THEN 1 END) as tipo_especial,
                        AVG(precio) as precio_promedio,
                        MIN(precio) as precio_minimo,
                        MAX(precio) as precio_maximo
                    FROM precios_producto";
            
            return db_query_one($sql);
            
        } catch (Exception $e) {
            registrar_error("Error al obtener estadísticas de precios: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Verifica si existe un precio de un tipo para un producto
     * 
     * @param int $producto_id ID del producto
     * @param string $tipo_precio Tipo de precio
     * @return bool
     */
    private static function existePrecioTipo($producto_id, $tipo_precio) {
        $precio = self::obtenerPorProductoYTipo($producto_id, $tipo_precio);
        return $precio !== false && $precio !== null;
    }
    
    /**
     * Verifica si un producto existe
     * 
     * @param int $producto_id ID del producto
     * @return bool
     */
    private static function productoExiste($producto_id) {
        $sql = "SELECT COUNT(*) as total FROM productos WHERE id = ?";
        $resultado = db_query_one($sql, [$producto_id]);
        return $resultado['total'] > 0;
    }
    
    /**
     * Obtiene los tipos de precio válidos
     * 
     * @return array Tipos válidos
     */
    public static function getTiposValidos() {
        return [
            self::TIPO_PUBLICO,
            self::TIPO_MAYORISTA,
            self::TIPO_DESCUENTO,
            self::TIPO_ESPECIAL
        ];
    }
    
    /**
     * Valida los datos del precio
     * 
     * @param array $datos Datos a validar
     * @param bool $es_edicion Si es una edición (no requiere producto_id ni tipo)
     * @return array Errores encontrados
     */
    private static function validar($datos, $es_edicion = false) {
        $errores = [];
        
        // Producto ID requerido solo en creación
        if (!$es_edicion) {
            if (empty($datos['producto_id'])) {
                $errores[] = 'El ID del producto es requerido';
            } elseif (!is_numeric($datos['producto_id']) || $datos['producto_id'] <= 0) {
                $errores[] = 'El ID del producto no es válido';
            }
            
            // Tipo de precio requerido y válido
            if (empty($datos['tipo_precio'])) {
                $errores[] = 'El tipo de precio es requerido';
            } elseif (!in_array($datos['tipo_precio'], self::getTiposValidos())) {
                $errores[] = 'El tipo de precio no es válido';
            }
        }
        
        // Precio requerido y válido
        if (!isset($datos['precio'])) {
            $errores[] = 'El precio es requerido';
        } elseif (!is_numeric($datos['precio']) || $datos['precio'] <= 0) {
            $errores[] = 'El precio debe ser mayor a 0';
        }
        
        return $errores;
    }
}
