<?php
/**
 * Modelo Usuario
 * 
 * Gestión completa de usuarios del sistema:
 * - CRUD de usuarios
 * - Gestión de contraseñas
 * - Asignación de roles
 * - Activación/Desactivación
 * - Listados con filtros
 * 
 * @author Sistema Joyería Torre Fuerte
 * @version 1.0
 * @date 2026-01-22
 */

class Usuario {
    
    /**
     * Crea un nuevo usuario
     * 
     * @param array $datos Datos del usuario
     * @return int|false ID del usuario creado o false
     */
    public static function crear($datos) {
        try {
            // Validar datos
            $errores = self::validar($datos);
            if (!empty($errores)) {
                throw new Exception(implode(', ', $errores));
            }
            
            // Verificar que el email no exista
            if (self::emailExiste($datos['email'])) {
                throw new Exception('El email ya está registrado en el sistema');
            }
            
            // Hash de la contraseña
            $password_hash = crear_hash_password($datos['password']);
            
            // Insertar usuario
            $sql = "INSERT INTO usuarios (
                        nombre, email, password, rol, 
                        sucursal_id, telefono, activo
                    ) VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $resultado = db_execute($sql, [
                $datos['nombre'],
                $datos['email'],
                $password_hash,
                $datos['rol'],
                $datos['sucursal_id'] ?? null,
                $datos['telefono'] ?? null,
                $datos['activo'] ?? 1
            ]);
            
            if ($resultado) {
                $usuario_id = db_ultimo_id();
                
                // Registrar en auditoría
                registrar_auditoria(
                    'usuarios',
                    'INSERT',
                    $usuario_id,
                    "Usuario creado: {$datos['nombre']} - Rol: {$datos['rol']}"
                );
                
                return $usuario_id;
            }
            
            return false;
            
        } catch (Exception $e) {
            registrar_error("Error al crear usuario: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Edita un usuario existente
     * 
     * @param int $id ID del usuario
     * @param array $datos Datos a actualizar
     * @return bool
     */
    public static function editar($id, $datos) {
        try {
            // Validar datos
            $errores = self::validar($datos, $id);
            if (!empty($errores)) {
                throw new Exception(implode(', ', $errores));
            }
            
            // Verificar que el usuario exista
            $usuario_actual = self::obtenerPorId($id);
            if (!$usuario_actual) {
                throw new Exception('Usuario no encontrado');
            }
            
            // Verificar email único (si cambió)
            if ($datos['email'] !== $usuario_actual['email']) {
                if (self::emailExiste($datos['email'], $id)) {
                    throw new Exception('El email ya está registrado en el sistema');
                }
            }
            
            // Actualizar usuario
            $sql = "UPDATE usuarios SET 
                        nombre = ?,
                        email = ?,
                        rol = ?,
                        sucursal_id = ?,
                        telefono = ?,
                        activo = ?
                    WHERE id = ?";
            
            $resultado = db_execute($sql, [
                $datos['nombre'],
                $datos['email'],
                $datos['rol'],
                $datos['sucursal_id'] ?? null,
                $datos['telefono'] ?? null,
                $datos['activo'] ?? 1,
                $id
            ]);
            
            if ($resultado) {
                // Registrar en auditoría
                registrar_auditoria(
                    'usuarios',
                    'UPDATE',
                    $id,
                    "Usuario actualizado: {$datos['nombre']}"
                );
            }
            
            return $resultado;
            
        } catch (Exception $e) {
            registrar_error("Error al editar usuario: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cambia la contraseña de un usuario
     * 
     * @param int $id ID del usuario
     * @param string $password_actual Contraseña actual (para verificación)
     * @param string $password_nueva Nueva contraseña
     * @return bool
     */
    public static function cambiarPassword($id, $password_actual, $password_nueva) {
        try {
            // Obtener usuario
            $usuario = self::obtenerPorId($id);
            if (!$usuario) {
                throw new Exception('Usuario no encontrado');
            }
            
            // Verificar contraseña actual
            if (!verificar_password($password_actual, $usuario['password'])) {
                throw new Exception('La contraseña actual es incorrecta');
            }
            
            // Validar nueva contraseña
            if (strlen($password_nueva) < 6) {
                throw new Exception('La nueva contraseña debe tener al menos 6 caracteres');
            }
            
            // Hash de nueva contraseña
            $password_hash = crear_hash_password($password_nueva);
            
            // Actualizar contraseña
            $sql = "UPDATE usuarios SET password = ? WHERE id = ?";
            $resultado = db_execute($sql, [$password_hash, $id]);
            
            if ($resultado) {
                registrar_auditoria(
                    'usuarios',
                    'UPDATE',
                    $id,
                    "Contraseña cambiada"
                );
            }
            
            return $resultado;
            
        } catch (Exception $e) {
            registrar_error("Error al cambiar contraseña: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Restablece la contraseña de un usuario (admin)
     * 
     * @param int $id ID del usuario
     * @param string $password_nueva Nueva contraseña
     * @return bool
     */
    public static function restablecerPassword($id, $password_nueva) {
        try {
            // Validar nueva contraseña
            if (strlen($password_nueva) < 6) {
                throw new Exception('La contraseña debe tener al menos 6 caracteres');
            }
            
            // Hash de nueva contraseña
            $password_hash = crear_hash_password($password_nueva);
            
            // Actualizar contraseña
            $sql = "UPDATE usuarios SET password = ? WHERE id = ?";
            $resultado = db_execute($sql, [$password_hash, $id]);
            
            if ($resultado) {
                registrar_auditoria(
                    'usuarios',
                    'UPDATE',
                    $id,
                    "Contraseña restablecida por administrador"
                );
            }
            
            return $resultado;
            
        } catch (Exception $e) {
            registrar_error("Error al restablecer contraseña: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Activa un usuario
     * 
     * @param int $id ID del usuario
     * @return bool
     */
    public static function activar($id) {
        try {
            $sql = "UPDATE usuarios SET activo = 1 WHERE id = ?";
            $resultado = db_execute($sql, [$id]);
            
            if ($resultado) {
                registrar_auditoria('usuarios', 'UPDATE', $id, "Usuario activado");
            }
            
            return $resultado;
            
        } catch (Exception $e) {
            registrar_error("Error al activar usuario: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Desactiva un usuario
     * 
     * @param int $id ID del usuario
     * @return bool
     */
    public static function desactivar($id) {
        try {
            // No permitir desactivar al usuario logueado
            if ($id == usuario_actual_id()) {
                throw new Exception('No puedes desactivar tu propio usuario');
            }
            
            $sql = "UPDATE usuarios SET activo = 0 WHERE id = ?";
            $resultado = db_execute($sql, [$id]);
            
            if ($resultado) {
                registrar_auditoria('usuarios', 'UPDATE', $id, "Usuario desactivado");
            }
            
            return $resultado;
            
        } catch (Exception $e) {
            registrar_error("Error al desactivar usuario: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene un usuario por ID
     * 
     * @param int $id ID del usuario
     * @return array|false Datos del usuario o false
     */
    public static function obtenerPorId($id) {
        try {
            $sql = "SELECT 
                        u.*,
                        s.nombre as sucursal_nombre
                    FROM usuarios u
                    LEFT JOIN sucursales s ON u.sucursal_id = s.id
                    WHERE u.id = ?";
            
            return db_query_one($sql, [$id]);
            
        } catch (Exception $e) {
            registrar_error("Error al obtener usuario: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene un usuario por email
     * 
     * @param string $email Email del usuario
     * @return array|false Datos del usuario o false
     */
    public static function obtenerPorEmail($email) {
        try {
            $sql = "SELECT 
                        u.*,
                        s.nombre as sucursal_nombre
                    FROM usuarios u
                    LEFT JOIN sucursales s ON u.sucursal_id = s.id
                    WHERE u.email = ?";
            
            return db_query_one($sql, [$email]);
            
        } catch (Exception $e) {
            registrar_error("Error al obtener usuario por email: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Lista usuarios con filtros
     * 
     * @param array $filtros Filtros opcionales: rol, sucursal_id, activo, buscar
     * @return array Lista de usuarios
     */
    public static function listar($filtros = []) {
        try {
            $where = [];
            $params = [];
            
            // Filtro por rol
            if (isset($filtros['rol']) && !empty($filtros['rol'])) {
                $where[] = "u.rol = ?";
                $params[] = $filtros['rol'];
            }
            
            // Filtro por sucursal
            if (isset($filtros['sucursal_id']) && $filtros['sucursal_id'] > 0) {
                $where[] = "u.sucursal_id = ?";
                $params[] = $filtros['sucursal_id'];
            }
            
            // Filtro por estado
            if (isset($filtros['activo'])) {
                $where[] = "u.activo = ?";
                $params[] = $filtros['activo'];
            }
            
            // Búsqueda por nombre o email
            if (isset($filtros['buscar']) && !empty($filtros['buscar'])) {
                $where[] = "(u.nombre LIKE ? OR u.email LIKE ?)";
                $termino = "%{$filtros['buscar']}%";
                $params[] = $termino;
                $params[] = $termino;
            }
            
            $where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            
            $sql = "SELECT 
                        u.id,
                        u.nombre,
                        u.email,
                        u.rol,
                        u.telefono,
                        u.activo,
                        u.ultimo_acceso,
                        u.fecha_creacion,
                        s.nombre as sucursal_nombre,
                        u.sucursal_id
                    FROM usuarios u
                    LEFT JOIN sucursales s ON u.sucursal_id = s.id
                    $where_sql
                    ORDER BY u.nombre ASC";
            
            return db_query($sql, $params);
            
        } catch (Exception $e) {
            registrar_error("Error al listar usuarios: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Lista usuarios por rol
     * 
     * @param string $rol Rol de los usuarios
     * @return array Lista de usuarios
     */
    public static function listarPorRol($rol) {
        return self::listar(['rol' => $rol, 'activo' => 1]);
    }
    
    /**
     * Obtiene estadísticas de usuarios
     * 
     * @return array Estadísticas
     */
    public static function obtenerEstadisticas() {
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        COUNT(CASE WHEN activo = 1 THEN 1 END) as activos,
                        COUNT(CASE WHEN activo = 0 THEN 1 END) as inactivos,
                        COUNT(CASE WHEN rol = 'administrador' THEN 1 END) as administradores,
                        COUNT(CASE WHEN rol = 'vendedor' THEN 1 END) as vendedores,
                        COUNT(CASE WHEN rol = 'cajero' THEN 1 END) as cajeros,
                        COUNT(CASE WHEN rol = 'orfebre' THEN 1 END) as orfebres
                    FROM usuarios";
            
            return db_query_one($sql);
            
        } catch (Exception $e) {
            registrar_error("Error al obtener estadísticas de usuarios: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Verifica si un email ya existe
     * 
     * @param string $email Email a verificar
     * @param int $excluir_id ID a excluir de la búsqueda (para edición)
     * @return bool
     */
    private static function emailExiste($email, $excluir_id = null) {
        $sql = "SELECT COUNT(*) as total FROM usuarios WHERE email = ?";
        $params = [$email];
        
        if ($excluir_id !== null) {
            $sql .= " AND id != ?";
            $params[] = $excluir_id;
        }
        
        $resultado = db_query_one($sql, $params);
        return $resultado['total'] > 0;
    }
    
    /**
     * Valida los datos del usuario
     * 
     * @param array $datos Datos a validar
     * @param int $id ID del usuario (null si es creación)
     * @return array Errores encontrados
     */
    private static function validar($datos, $id = null) {
        $errores = [];
        
        // Nombre requerido
        if (empty($datos['nombre'])) {
            $errores[] = 'El nombre es requerido';
        } elseif (strlen($datos['nombre']) < 3) {
            $errores[] = 'El nombre debe tener al menos 3 caracteres';
        }
        
        // Email requerido y válido
        if (empty($datos['email'])) {
            $errores[] = 'El email es requerido';
        } elseif (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El email no es válido';
        }
        
        // Password requerido solo en creación
        if ($id === null && empty($datos['password'])) {
            $errores[] = 'La contraseña es requerida';
        } elseif ($id === null && strlen($datos['password']) < 6) {
            $errores[] = 'La contraseña debe tener al menos 6 caracteres';
        }
        
        // Rol requerido y válido
        $roles_validos = ['administrador', 'dueño', 'vendedor', 'cajero', 'orfebre', 'publicidad'];
        if (empty($datos['rol'])) {
            $errores[] = 'El rol es requerido';
        } elseif (!in_array($datos['rol'], $roles_validos)) {
            $errores[] = 'El rol no es válido';
        }
        
        return $errores;
    }
}
