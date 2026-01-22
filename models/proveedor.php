<?php
/**
 * Modelo Proveedor
 * 
 * Gestión completa de proveedores:
 * - CRUD de proveedores
 * - Listados con filtros
 * - Activación/Desactivación
 * - Historial de compras (integración futura)
 * 
 * @author Sistema Joyería Torre Fuerte
 * @version 1.0
 * @date 2026-01-22
 */

class Proveedor {
    
    /**
     * Crea un nuevo proveedor
     * 
     * @param array $datos Datos del proveedor
     * @return int|false ID del proveedor creado o false
     */
    public static function crear($datos) {
        try {
            // Validar datos
            $errores = self::validar($datos);
            if (!empty($errores)) {
                throw new Exception(implode(', ', $errores));
            }
            
            // Insertar proveedor
            $sql = "INSERT INTO proveedores (
                        nombre, empresa, contacto, telefono, 
                        email, direccion, productos_suministra, activo
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $resultado = db_execute($sql, [
                $datos['nombre'],
                $datos['empresa'] ?? null,
                $datos['contacto'] ?? null,
                $datos['telefono'],
                $datos['email'] ?? null,
                $datos['direccion'] ?? null,
                $datos['productos_suministra'] ?? null,
                $datos['activo'] ?? 1
            ]);
            
            if ($resultado) {
                $proveedor_id = $resultado; // db_execute devuelve el ID en INSERT
                
                // Registrar en auditoría
                registrar_auditoria(
                    'proveedores',
                    'INSERT',
                    $proveedor_id,
                    "Proveedor creado: {$datos['nombre']}"
                );
                
                return $proveedor_id;
            }
            
            return false;
            
        } catch (Exception $e) {
            registrar_error("Error al crear proveedor: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Edita un proveedor existente
     * 
     * @param int $id ID del proveedor
     * @param array $datos Datos a actualizar
     * @return bool
     */
    public static function editar($id, $datos) {
        try {
            // Validar datos
            $errores = self::validar($datos);
            if (!empty($errores)) {
                throw new Exception(implode(', ', $errores));
            }
            
            // Verificar que el proveedor exista
            $proveedor = self::obtenerPorId($id);
            if (!$proveedor) {
                throw new Exception('Proveedor no encontrado');
            }
            
            // Actualizar proveedor
            $sql = "UPDATE proveedores SET 
                        nombre = ?,
                        empresa = ?,
                        contacto = ?,
                        telefono = ?,
                        email = ?,
                        direccion = ?,
                        productos_suministra = ?,
                        activo = ?
                    WHERE id = ?";
            
            $resultado = db_execute($sql, [
                $datos['nombre'],
                $datos['empresa'] ?? null,
                $datos['contacto'] ?? null,
                $datos['telefono'],
                $datos['email'] ?? null,
                $datos['direccion'] ?? null,
                $datos['productos_suministra'] ?? null,
                $datos['activo'] ?? 1,
                $id
            ]);
            
            if ($resultado) {
                // Registrar en auditoría
                registrar_auditoria(
                    'proveedores',
                    'UPDATE',
                    $id,
                    "Proveedor actualizado: {$datos['nombre']}"
                );
            }
            
            return $resultado;
            
        } catch (Exception $e) {
            registrar_error("Error al editar proveedor: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Activa un proveedor
     * 
     * @param int $id ID del proveedor
     * @return bool
     */
    public static function activar($id) {
        try {
            $sql = "UPDATE proveedores SET activo = 1 WHERE id = ?";
            $resultado = db_execute($sql, [$id]);
            
            if ($resultado) {
                registrar_auditoria('proveedores', 'UPDATE', $id, "Proveedor activado");
            }
            
            return $resultado;
            
        } catch (Exception $e) {
            registrar_error("Error al activar proveedor: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Desactiva un proveedor
     * 
     * @param int $id ID del proveedor
     * @return bool
     */
    public static function desactivar($id) {
        try {
            $sql = "UPDATE proveedores SET activo = 0 WHERE id = ?";
            $resultado = db_execute($sql, [$id]);
            
            if ($resultado) {
                registrar_auditoria('proveedores', 'UPDATE', $id, "Proveedor desactivado");
            }
            
            return $resultado;
            
        } catch (Exception $e) {
            registrar_error("Error al desactivar proveedor: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene un proveedor por ID
     * 
     * @param int $id ID del proveedor
     * @return array|false Datos del proveedor o false
     */
    public static function obtenerPorId($id) {
        try {
            $sql = "SELECT * FROM proveedores WHERE id = ?";
            return db_query_one($sql, [$id]);
            
        } catch (Exception $e) {
            registrar_error("Error al obtener proveedor: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Lista proveedores con filtros
     * 
     * @param array $filtros Filtros opcionales: activo, buscar
     * @return array Lista de proveedores
     */
    public static function listar($filtros = []) {
        try {
            $where = [];
            $params = [];
            
            // Filtro por estado
            if (isset($filtros['activo'])) {
                $where[] = "activo = ?";
                $params[] = $filtros['activo'];
            }
            
            // Búsqueda por nombre, empresa o contacto
            if (isset($filtros['buscar']) && !empty($filtros['buscar'])) {
                $where[] = "(nombre LIKE ? OR empresa LIKE ? OR contacto LIKE ?)";
                $termino = "%{$filtros['buscar']}%";
                $params[] = $termino;
                $params[] = $termino;
                $params[] = $termino;
            }
            
            $where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            
            $sql = "SELECT 
                        id,
                        nombre,
                        empresa,
                        contacto,
                        telefono,
                        email,
                        direccion,
                        productos_suministra,
                        activo,
                        fecha_creacion
                    FROM proveedores
                    $where_sql
                    ORDER BY nombre ASC";
            
            return db_query($sql, $params);
            
        } catch (Exception $e) {
            registrar_error("Error al listar proveedores: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Lista proveedores activos
     * 
     * @return array Lista de proveedores activos
     */
    public static function listarActivos() {
        return self::listar(['activo' => 1]);
    }
    
    /**
     * Obtiene estadísticas de proveedores
     * 
     * @return array Estadísticas
     */
    public static function obtenerEstadisticas() {
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        COUNT(CASE WHEN activo = 1 THEN 1 END) as activos,
                        COUNT(CASE WHEN activo = 0 THEN 1 END) as inactivos
                    FROM proveedores";
            
            return db_query_one($sql);
            
        } catch (Exception $e) {
            registrar_error("Error al obtener estadísticas de proveedores: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Busca proveedores por nombre o empresa
     * 
     * @param string $termino Término de búsqueda
     * @return array Lista de proveedores encontrados
     */
    public static function buscar($termino) {
        return self::listar(['buscar' => $termino, 'activo' => 1]);
    }
    
    /**
     * Valida los datos del proveedor
     * 
     * @param array $datos Datos a validar
     * @return array Errores encontrados
     */
    private static function validar($datos) {
        $errores = [];
        
        // Nombre requerido
        if (empty($datos['nombre'])) {
            $errores[] = 'El nombre del proveedor es requerido';
        } elseif (strlen($datos['nombre']) < 3) {
            $errores[] = 'El nombre debe tener al menos 3 caracteres';
        }
        
        // Teléfono requerido
        if (empty($datos['telefono'])) {
            $errores[] = 'El teléfono es requerido';
        }
        
        // Email válido si se proporciona
        if (!empty($datos['email']) && !filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El email no es válido';
        }
        
        return $errores;
    }
}