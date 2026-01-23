<?php
/**
 * Modelo Sucursal
 * 
 * Gestión completa de sucursales (tiendas):
 * - CRUD de sucursales
 * - Asignación de responsables
 * - Activación/Desactivación
 * - Consultas y estadísticas
 * 
 * @author Sistema Joyería Torre Fuerte
 * @version 1.0
 * @date 2026-01-22
 */

class Sucursal {
    
    /**
     * Crea una nueva sucursal
     * 
     * @param array $datos Datos de la sucursal
     * @return int|false ID de la sucursal creada o false
     */
    public static function crear($datos) {
        try {
            // Validar datos
            $errores = self::validar($datos);
            if (!empty($errores)) {
                throw new Exception(implode(', ', $errores));
            }
            
            // Verificar que el nombre no exista
            if (self::nombreExiste($datos['nombre'])) {
                throw new Exception('Ya existe una sucursal con ese nombre');
            }
            
            // Insertar sucursal
            $sql = "INSERT INTO sucursales (
                        nombre, direccion, telefono, email, 
                        responsable_id, activo
                    ) VALUES (?, ?, ?, ?, ?, ?)";
            
            $resultado = db_execute($sql, [
                $datos['nombre'],
                $datos['direccion'],
                $datos['telefono'] ?? null,
                $datos['email'] ?? null,
                $datos['responsable_id'] ?? null,
                $datos['activo'] ?? 1
            ]);
            
            if ($resultado) {
                $sucursal_id = $resultado;
                
                // Registrar en auditoría
                registrar_auditoria(
                    'sucursales',
                    'INSERT',
                    $sucursal_id,
                    "Sucursal creada: {$datos['nombre']}"
                );
                
                return $sucursal_id;
            }
            
            return false;
            
        } catch (Exception $e) {
            registrar_error("Error al crear sucursal: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Edita una sucursal existente
     * 
     * @param int $id ID de la sucursal
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
            
            // Verificar que la sucursal existe
            $sucursal_actual = self::obtenerPorId($id);
            if (!$sucursal_actual) {
                throw new Exception('Sucursal no encontrada');
            }
            
            // Verificar nombre único (si cambió)
            if ($datos['nombre'] !== $sucursal_actual['nombre']) {
                if (self::nombreExiste($datos['nombre'], $id)) {
                    throw new Exception('Ya existe una sucursal con ese nombre');
                }
            }
            
            // Actualizar sucursal
            $sql = "UPDATE sucursales SET 
                        nombre = ?,
                        direccion = ?,
                        telefono = ?,
                        email = ?,
                        responsable_id = ?,
                        activo = ?
                    WHERE id = ?";
            
            $resultado = db_execute($sql, [
                $datos['nombre'],
                $datos['direccion'],
                $datos['telefono'] ?? null,
                $datos['email'] ?? null,
                $datos['responsable_id'] ?? null,
                $datos['activo'] ?? 1,
                $id
            ]);
            
            if ($resultado) {
                registrar_auditoria(
                    'sucursales',
                    'UPDATE',
                    $id,
                    "Sucursal actualizada: {$datos['nombre']}"
                );
            }
            
            return $resultado;
            
        } catch (Exception $e) {
            registrar_error("Error al editar sucursal: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Activa una sucursal
     * 
     * @param int $id ID de la sucursal
     * @return bool
     */
    public static function activar($id) {
        try {
            $sql = "UPDATE sucursales SET activo = 1 WHERE id = ?";
            $resultado = db_execute($sql, [$id]);
            
            if ($resultado) {
                registrar_auditoria('sucursales', 'UPDATE', $id, "Sucursal activada");
            }
            
            return $resultado;
            
        } catch (Exception $e) {
            registrar_error("Error al activar sucursal: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Desactiva una sucursal
     * 
     * @param int $id ID de la sucursal
     * @return bool
     */
    public static function desactivar($id) {
        try {
            // Verificar que no sea la única sucursal activa
            $activas = self::listar(['activo' => 1]);
            if (count($activas) <= 1) {
                throw new Exception('No se puede desactivar la única sucursal activa');
            }
            
            $sql = "UPDATE sucursales SET activo = 0 WHERE id = ?";
            $resultado = db_execute($sql, [$id]);
            
            if ($resultado) {
                registrar_auditoria('sucursales', 'UPDATE', $id, "Sucursal desactivada");
            }
            
            return $resultado;
            
        } catch (Exception $e) {
            registrar_error("Error al desactivar sucursal: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene una sucursal por ID
     * 
     * @param int $id ID de la sucursal
     * @return array|false Datos de la sucursal o false
     */
    public static function obtenerPorId($id) {
        try {
            $sql = "SELECT 
                        s.*,
                        u.nombre as responsable_nombre,
                        u.email as responsable_email
                    FROM sucursales s
                    LEFT JOIN usuarios u ON s.responsable_id = u.id
                    WHERE s.id = ?";
            
            return db_query_one($sql, [$id]);
            
        } catch (Exception $e) {
            registrar_error("Error al obtener sucursal: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Lista sucursales con filtros
     * 
     * @param array $filtros Filtros opcionales: activo, buscar
     * @return array Lista de sucursales
     */
    public static function listar($filtros = []) {
        try {
            $where = [];
            $params = [];
            
            // Filtro por estado
            if (isset($filtros['activo'])) {
                $where[] = "s.activo = ?";
                $params[] = $filtros['activo'];
            }
            
            // Búsqueda por nombre o dirección
            if (isset($filtros['buscar']) && !empty($filtros['buscar'])) {
                $where[] = "(s.nombre LIKE ? OR s.direccion LIKE ?)";
                $termino = "%{$filtros['buscar']}%";
                $params[] = $termino;
                $params[] = $termino;
            }
            
            $where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            
            $sql = "SELECT 
                        s.id,
                        s.nombre,
                        s.direccion,
                        s.telefono,
                        s.email,
                        s.activo,
                        s.fecha_creacion,
                        u.nombre as responsable_nombre,
                        s.responsable_id
                    FROM sucursales s
                    LEFT JOIN usuarios u ON s.responsable_id = u.id
                    $where_sql
                    ORDER BY s.nombre ASC";
            
            return db_query($sql, $params);
            
        } catch (Exception $e) {
            registrar_error("Error al listar sucursales: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Lista sucursales activas
     * 
     * @return array Lista de sucursales activas
     */
    public static function listarActivas() {
        return self::listar(['activo' => 1]);
    }
    
    /**
     * Asigna un responsable a la sucursal
     * 
     * @param int $sucursal_id ID de la sucursal
     * @param int $usuario_id ID del usuario responsable
     * @return bool
     */
    public static function asignarResponsable($sucursal_id, $usuario_id) {
        try {
            // Verificar que el usuario existe y está activo
            $usuario = db_query_one(
                "SELECT id, nombre FROM usuarios WHERE id = ? AND activo = 1",
                [$usuario_id]
            );
            
            if (!$usuario) {
                throw new Exception('Usuario no encontrado o inactivo');
            }
            
            $sql = "UPDATE sucursales SET responsable_id = ? WHERE id = ?";
            $resultado = db_execute($sql, [$usuario_id, $sucursal_id]);
            
            if ($resultado) {
                registrar_auditoria(
                    'sucursales',
                    'UPDATE',
                    $sucursal_id,
                    "Responsable asignado: {$usuario['nombre']}"
                );
            }
            
            return $resultado;
            
        } catch (Exception $e) {
            registrar_error("Error al asignar responsable: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene estadísticas de sucursales
     * 
     * @return array Estadísticas
     */
    public static function obtenerEstadisticas() {
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        COUNT(CASE WHEN activo = 1 THEN 1 END) as activas,
                        COUNT(CASE WHEN activo = 0 THEN 1 END) as inactivas,
                        COUNT(CASE WHEN responsable_id IS NOT NULL THEN 1 END) as con_responsable
                    FROM sucursales";
            
            return db_query_one($sql);
            
        } catch (Exception $e) {
            registrar_error("Error al obtener estadísticas de sucursales: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene usuarios de una sucursal
     * 
     * @param int $sucursal_id ID de la sucursal
     * @return array Lista de usuarios
     */
    public static function obtenerUsuarios($sucursal_id) {
        try {
            $sql = "SELECT 
                        id,
                        nombre,
                        email,
                        rol,
                        activo
                    FROM usuarios
                    WHERE sucursal_id = ?
                    ORDER BY nombre ASC";
            
            return db_query($sql, [$sucursal_id]);
            
        } catch (Exception $e) {
            registrar_error("Error al obtener usuarios de sucursal: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Verifica si existe una sucursal con ese nombre
     * 
     * @param string $nombre Nombre de la sucursal
     * @param int $excluir_id ID a excluir de la búsqueda (para edición)
     * @return bool
     */
    public static function nombreExiste($nombre, $excluir_id = null) {
        $sql = "SELECT COUNT(*) as total FROM sucursales WHERE nombre = ?";
        $params = [$nombre];
        
        if ($excluir_id !== null) {
            $sql .= " AND id != ?";
            $params[] = $excluir_id;
        }
        
        $resultado = db_query_one($sql, $params);
        return $resultado['total'] > 0;
    }
    
    /**
     * Verifica si una sucursal existe
     * 
     * @param int $id ID de la sucursal
     * @return bool
     */
    public static function existe($id) {
        $sucursal = self::obtenerPorId($id);
        return $sucursal !== false && $sucursal !== null;
    }
    
    /**
     * Valida los datos de la sucursal
     * 
     * @param array $datos Datos a validar
     * @return array Errores encontrados
     */
    private static function validar($datos) {
        $errores = [];
        
        // Nombre requerido
        if (empty($datos['nombre'])) {
            $errores[] = 'El nombre de la sucursal es requerido';
        } elseif (strlen($datos['nombre']) < 3) {
            $errores[] = 'El nombre debe tener al menos 3 caracteres';
        }
        
        // Dirección requerida
        if (empty($datos['direccion'])) {
            $errores[] = 'La dirección es requerida';
        } elseif (strlen($datos['direccion']) < 10) {
            $errores[] = 'La dirección debe tener al menos 10 caracteres';
        }
        
        // Email válido si se proporciona
        if (!empty($datos['email']) && !filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El email no es válido';
        }
        
        return $errores;
    }
}
