<?php
// ================================================
// MODELO: TRABAJO TALLER
// Sistema de Gestión - Joyería Torre Fuerte
// ================================================

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/funciones.php';

class TrabajoTaller {
    
    // ========================================
    // MÉTODOS DE CONSULTA (SELECT)
    // ========================================
    
    /**
     * Obtiene todos los trabajos con filtros y paginación
     * 
     * @param array $filtros Array de filtros (estado, empleado_actual_id, cliente_telefono, etc.)
     * @param int $pagina Número de página
     * @param int $por_pagina Registros por página
     * @return array Array de trabajos
     */
    public static function listar($filtros = [], $pagina = 1, $por_pagina = 20) {
        global $pdo;
        
        $where = ['1=1'];
        $params = [];
        
        // Filtro por estado
        if (isset($filtros['estado']) && !empty($filtros['estado'])) {
            $where[] = 't.estado = ?';
            $params[] = $filtros['estado'];
        }
        
        // Filtro por empleado actual
        if (isset($filtros['empleado_actual_id']) && !empty($filtros['empleado_actual_id'])) {
            $where[] = 't.empleado_actual_id = ?';
            $params[] = $filtros['empleado_actual_id'];
        }
        
        // Filtro por teléfono de cliente
        if (isset($filtros['cliente_telefono']) && !empty($filtros['cliente_telefono'])) {
            $where[] = 't.cliente_telefono LIKE ?';
            $params[] = '%' . $filtros['cliente_telefono'] . '%';
        }
        
        // Filtro por tipo de trabajo
        if (isset($filtros['tipo_trabajo']) && !empty($filtros['tipo_trabajo'])) {
            $where[] = 't.tipo_trabajo = ?';
            $params[] = $filtros['tipo_trabajo'];
        }
        
        // Filtro por material
        if (isset($filtros['material']) && !empty($filtros['material'])) {
            $where[] = 't.material = ?';
            $params[] = $filtros['material'];
        }
        
        // Filtro por rango de fechas de recepción
        if (isset($filtros['fecha_recepcion_desde']) && !empty($filtros['fecha_recepcion_desde'])) {
            $where[] = 'DATE(t.fecha_recepcion) >= ?';
            $params[] = $filtros['fecha_recepcion_desde'];
        }
        
        if (isset($filtros['fecha_recepcion_hasta']) && !empty($filtros['fecha_recepcion_hasta'])) {
            $where[] = 'DATE(t.fecha_recepcion) <= ?';
            $params[] = $filtros['fecha_recepcion_hasta'];
        }
        
        $where_sql = implode(' AND ', $where);
        
        // Calcular offset para paginación
        $offset = ($pagina - 1) * $por_pagina;
        
        $sql = "SELECT t.*,
                       c.nombre as cliente_nombre_completo,
                       u_recibe.nombre as empleado_recibe_nombre,
                       u_actual.nombre as empleado_actual_nombre,
                       u_entrega.nombre as empleado_entrega_nombre
                FROM trabajos_taller t
                LEFT JOIN clientes c ON t.cliente_id = c.id
                LEFT JOIN usuarios u_recibe ON t.empleado_recibe_id = u_recibe.id
                LEFT JOIN usuarios u_actual ON t.empleado_actual_id = u_actual.id
                LEFT JOIN usuarios u_entrega ON t.empleado_entrega_id = u_entrega.id
                WHERE $where_sql
                ORDER BY t.fecha_recepcion DESC, t.id DESC
                LIMIT ? OFFSET ?";
        
        $params[] = $por_pagina;
        $params[] = $offset;
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            registrar_error("Error al listar trabajos de taller: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene un trabajo por su ID
     * 
     * @param int $id ID del trabajo
     * @return array|false Trabajo o false
     */
    public static function obtenerPorId($id) {
        $sql = "SELECT t.*,
                       c.nombre as cliente_nombre_completo,
                       c.email as cliente_email,
                       c.direccion as cliente_direccion,
                       u_recibe.nombre as empleado_recibe_nombre,
                       u_actual.nombre as empleado_actual_nombre,
                       u_entrega.nombre as empleado_entrega_nombre
                FROM trabajos_taller t
                LEFT JOIN clientes c ON t.cliente_id = c.id
                LEFT JOIN usuarios u_recibe ON t.empleado_recibe_id = u_recibe.id
                LEFT JOIN usuarios u_actual ON t.empleado_actual_id = u_actual.id
                LEFT JOIN usuarios u_entrega ON t.empleado_entrega_id = u_entrega.id
                WHERE t.id = ?";
        
        try {
            return db_query_one($sql, [$id]);
        } catch (PDOException $e) {
            registrar_error("Error al obtener trabajo de taller: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene trabajos de un empleado específico
     * 
     * @param int $empleado_id ID del empleado
     * @param string $estado Estado del trabajo (opcional)
     * @return array Array de trabajos
     */
    public static function obtenerTrabajosPorEmpleado($empleado_id, $estado = null) {
        $filtros = ['empleado_actual_id' => $empleado_id];
        
        if ($estado) {
            $filtros['estado'] = $estado;
        }
        
        return self::listar($filtros, 1, 100);
    }
    
    /**
     * Obtiene trabajos de un cliente por teléfono
     * 
     * @param string $cliente_telefono Teléfono del cliente
     * @return array Array de trabajos
     */
    public static function obtenerTrabajosPorCliente($cliente_telefono) {
        $filtros = ['cliente_telefono' => $cliente_telefono];
        return self::listar($filtros, 1, 100);
    }
    
    /**
     * Obtiene trabajos próximos a su fecha de entrega
     * 
     * @param int $dias Número de días de anticipación (default: 3)
     * @return array Array de trabajos próximos a entrega
     */
    public static function obtenerTrabajosProximosEntrega($dias = 3) {
        global $pdo;
        
        $sql = "SELECT t.*,
                       c.nombre as cliente_nombre_completo,
                       u_actual.nombre as empleado_actual_nombre,
                       DATEDIFF(t.fecha_entrega_prometida, CURDATE()) as dias_restantes
                FROM trabajos_taller t
                LEFT JOIN clientes c ON t.cliente_id = c.id
                LEFT JOIN usuarios u_actual ON t.empleado_actual_id = u_actual.id
                WHERE t.fecha_entrega_prometida BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
                  AND t.estado NOT IN ('entregado', 'cancelado')
                ORDER BY t.fecha_entrega_prometida ASC";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$dias]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            registrar_error("Error al obtener trabajos próximos a entrega: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Busca trabajos por término general
     * 
     * @param string $termino Término de búsqueda
     * @return array Array de trabajos encontrados
     */
    public static function buscarTrabajos($termino) {
        global $pdo;
        
        $sql = "SELECT t.*,
                       c.nombre as cliente_nombre_completo,
                       u_actual.nombre as empleado_actual_nombre
                FROM trabajos_taller t
                LEFT JOIN clientes c ON t.cliente_id = c.id
                LEFT JOIN usuarios u_actual ON t.empleado_actual_id = u_actual.id
                WHERE t.codigo LIKE ?
                   OR t.cliente_nombre LIKE ?
                   OR t.cliente_telefono LIKE ?
                   OR t.descripcion_pieza LIKE ?
                   OR t.descripcion_trabajo LIKE ?
                ORDER BY t.fecha_recepcion DESC
                LIMIT 50";
        
        $param = '%' . $termino . '%';
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$param, $param, $param, $param, $param]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            registrar_error("Error al buscar trabajos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene el historial de transferencias de un trabajo
     * 
     * @param int $trabajo_id ID del trabajo
     * @return array Array de transferencias
     */
    public static function obtenerHistorialTransferencias($trabajo_id) {
        global $pdo;
        
        $sql = "SELECT tr.*,
                       u_origen.nombre as empleado_origen_nombre,
                       u_destino.nombre as empleado_destino_nombre,
                       u_registra.nombre as usuario_registra_nombre
                FROM transferencias_trabajo tr
                LEFT JOIN usuarios u_origen ON tr.empleado_origen_id = u_origen.id
                LEFT JOIN usuarios u_destino ON tr.empleado_destino_id = u_destino.id
                LEFT JOIN usuarios u_registra ON tr.usuario_registra_id = u_registra.id
                WHERE tr.trabajo_id = ?
                ORDER BY tr.fecha_transferencia DESC";
        
        try {
            return db_query($sql, [$trabajo_id]);
        } catch (PDOException $e) {
            registrar_error("Error al obtener historial de transferencias: " . $e->getMessage());
            return [];
        }
    }
    
    // ========================================
    // MÉTODOS DE CREACIÓN (INSERT)
    // ========================================
    
    /**
     * Crea un nuevo trabajo de taller
     * 
     * @param array $datos Datos del trabajo
     * @return int|false ID del trabajo creado o false
     */
    public static function crear($datos) {
        global $pdo;
        
        // Validar datos
        $errores = self::validar($datos);
        if (!empty($errores)) {
            return false;
        }
        
        try {
            // Generar código único
            $codigo = self::generarCodigoTrabajo();
            
            $sql = "INSERT INTO trabajos_taller (
                        codigo, cliente_nombre, cliente_telefono, cliente_id,
                        material, peso_gramos, largo_cm, con_piedra, estilo,
                        descripcion_pieza, tipo_trabajo, descripcion_trabajo,
                        precio_total, anticipo,
                        fecha_recepcion, fecha_entrega_prometida,
                        empleado_recibe_id, empleado_actual_id,
                        estado, observaciones
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $params = [
                $codigo,
                $datos['cliente_nombre'],
                $datos['cliente_telefono'],
                $datos['cliente_id'] ?? null,
                $datos['material'],
                $datos['peso_gramos'] ?? null,
                $datos['largo_cm'] ?? null,
                $datos['con_piedra'] ?? 0,
                $datos['estilo'] ?? null,
                $datos['descripcion_pieza'],
                $datos['tipo_trabajo'],
                $datos['descripcion_trabajo'],
                $datos['precio_total'],
                $datos['anticipo'] ?? 0,
                $datos['fecha_recepcion'] ?? date('Y-m-d H:i:s'),
                $datos['fecha_entrega_prometida'],
                $datos['empleado_recibe_id'],
                $datos['empleado_actual_id'] ?? $datos['empleado_recibe_id'],
                'recibido',
                $datos['observaciones'] ?? null
            ];
            
            $trabajo_id = db_execute($sql, $params);
            
            if ($trabajo_id) {
                registrar_auditoria('INSERT', 'trabajos_taller', $trabajo_id, 
                    "Trabajo creado: {$codigo} - Cliente: {$datos['cliente_nombre']} - Tipo: {$datos['tipo_trabajo']}");
            }
            
            return $trabajo_id;
            
        } catch (PDOException $e) {
            registrar_error("Error al crear trabajo de taller: " . $e->getMessage());
            return false;
        }
    }
    
    // ========================================
    // MÉTODOS DE ACTUALIZACIÓN (UPDATE)
    // ========================================
    
    /**
     * Actualiza un trabajo de taller
     * 
     * @param int $id ID del trabajo
     * @param array $datos Datos a actualizar
     * @return bool
     */
    public static function actualizar($id, $datos) {
        global $pdo;
        
        // Validar que el trabajo existe
        if (!self::existe($id)) {
            return false;
        }
        
        // Validar datos
        $errores = self::validar($datos, $id);
        if (!empty($errores)) {
            return false;
        }
        
        try {
            $sql = "UPDATE trabajos_taller SET
                        cliente_nombre = ?,
                        cliente_telefono = ?,
                        cliente_id = ?,
                        material = ?,
                        peso_gramos = ?,
                        largo_cm = ?,
                        con_piedra = ?,
                        estilo = ?,
                        descripcion_pieza = ?,
                        tipo_trabajo = ?,
                        descripcion_trabajo = ?,
                        precio_total = ?,
                        anticipo = ?,
                        fecha_entrega_prometida = ?,
                        observaciones = ?
                    WHERE id = ?";
            
            $params = [
                $datos['cliente_nombre'],
                $datos['cliente_telefono'],
                $datos['cliente_id'] ?? null,
                $datos['material'],
                $datos['peso_gramos'] ?? null,
                $datos['largo_cm'] ?? null,
                $datos['con_piedra'] ?? 0,
                $datos['estilo'] ?? null,
                $datos['descripcion_pieza'],
                $datos['tipo_trabajo'],
                $datos['descripcion_trabajo'],
                $datos['precio_total'],
                $datos['anticipo'] ?? 0,
                $datos['fecha_entrega_prometida'],
                $datos['observaciones'] ?? null,
                $id
            ];
            
            $resultado = db_execute($sql, $params);
            
            if ($resultado) {
                registrar_auditoria('UPDATE', 'trabajos_taller', $id, 
                    "Trabajo actualizado: {$datos['cliente_nombre']} - {$datos['tipo_trabajo']}");
            }
            
            return $resultado;
            
        } catch (PDOException $e) {
            registrar_error("Error al actualizar trabajo de taller: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cambia el estado de un trabajo
     * 
     * @param int $trabajo_id ID del trabajo
     * @param string $nuevo_estado Nuevo estado
     * @param string $observaciones Observaciones del cambio
     * @return bool
     */
    public static function cambiarEstado($trabajo_id, $nuevo_estado, $observaciones = '') {
        global $pdo;
        
        // Validar que el trabajo existe
        $trabajo = self::obtenerPorId($trabajo_id);
        if (!$trabajo) {
            return false;
        }
        
        // Validar que el estado es válido
        $estados_validos = ['recibido', 'en_proceso', 'completado', 'entregado', 'cancelado'];
        if (!in_array($nuevo_estado, $estados_validos)) {
            return false;
        }
        
        try {
            $sql = "UPDATE trabajos_taller SET 
                        estado = ?,
                        observaciones = CONCAT(COALESCE(observaciones, ''), ?)
                    WHERE id = ?";
            
            $obs_adicional = "\n[" . date('Y-m-d H:i:s') . "] Estado cambiado a: {$nuevo_estado}";
            if ($observaciones) {
                $obs_adicional .= " - {$observaciones}";
            }
            
            $params = [$nuevo_estado, $obs_adicional, $trabajo_id];
            
            $resultado = db_execute($sql, $params);
            
            if ($resultado) {
                registrar_auditoria('UPDATE', 'trabajos_taller', $trabajo_id, 
                    "Estado cambiado a: {$nuevo_estado} - {$trabajo['codigo']}");
            }
            
            return $resultado;
            
        } catch (PDOException $e) {
            registrar_error("Error al cambiar estado del trabajo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Marca un trabajo como completado
     * 
     * @param int $trabajo_id ID del trabajo
     * @param string $observaciones Observaciones
     * @return bool
     */
    public static function completarTrabajo($trabajo_id, $observaciones = '') {
        $trabajo = self::obtenerPorId($trabajo_id);
        
        if (!$trabajo) {
            return false;
        }
        
        // Solo se puede completar si está en recibido o en_proceso
        if (!in_array($trabajo['estado'], ['recibido', 'en_proceso'])) {
            return false;
        }
        
        return self::cambiarEstado($trabajo_id, 'completado', $observaciones);
    }
    
    /**
     * Entrega un trabajo al cliente
     * 
     * @param int $trabajo_id ID del trabajo
     * @param int $empleado_entrega_id ID del empleado que entrega
     * @param string $observaciones Observaciones
     * @return bool
     */
    public static function entregarTrabajo($trabajo_id, $empleado_entrega_id, $observaciones = '') {
        global $pdo;
        
        $trabajo = self::obtenerPorId($trabajo_id);
        
        if (!$trabajo) {
            return false;
        }
        
        // Solo se puede entregar si está completado
        if ($trabajo['estado'] !== 'completado') {
            return false;
        }
        
        // Verificar si hay saldo pendiente
        $saldo_pendiente = $trabajo['saldo'];
        $advertencia_saldo = '';
        
        if ($saldo_pendiente > 0) {
            $advertencia_saldo = " [ENTREGADO CON SALDO PENDIENTE: Q " . number_format($saldo_pendiente, 2) . "]";
        }
        
        try {
            $pdo->beginTransaction();
            
            // Actualizar trabajo
            $sql = "UPDATE trabajos_taller SET 
                        estado = 'entregado',
                        fecha_entrega_real = NOW(),
                        empleado_entrega_id = ?,
                        observaciones = CONCAT(COALESCE(observaciones, ''), ?)
                    WHERE id = ?";
            
            $obs_adicional = "\n[" . date('Y-m-d H:i:s') . "] Trabajo entregado al cliente" . $advertencia_saldo;
            if ($observaciones) {
                $obs_adicional .= " - {$observaciones}";
            }
            
            $params = [$empleado_entrega_id, $obs_adicional, $trabajo_id];
            
            db_execute($sql, $params);
            
            // Registrar auditoría
            registrar_auditoria('UPDATE', 'trabajos_taller', $trabajo_id, 
                "Trabajo entregado: {$trabajo['codigo']}" . $advertencia_saldo);
            
            $pdo->commit();
            return true;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            registrar_error("Error al entregar trabajo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Transfiere un trabajo entre empleados
     * 
     * @param int $trabajo_id ID del trabajo
     * @param int $empleado_destino_id ID del empleado destino
     * @param string $nota Nota de la transferencia
     * @return bool
     */
    public static function transferirTrabajo($trabajo_id, $empleado_destino_id, $nota = '') {
        global $pdo;
        
        $trabajo = self::obtenerPorId($trabajo_id);
        
        if (!$trabajo) {
            return false;
        }
        
        // No se puede transferir si ya está entregado o cancelado
        if (in_array($trabajo['estado'], ['entregado', 'cancelado'])) {
            return false;
        }
        
        // No se puede transferir a sí mismo
        if ($trabajo['empleado_actual_id'] == $empleado_destino_id) {
            return false;
        }
        
        // Verificar que el empleado destino existe
        if (!db_exists('usuarios', 'id = ?', [$empleado_destino_id])) {
            return false;
        }
        
        try {
            $pdo->beginTransaction();
            
            // 1. Actualizar empleado actual en trabajo
            $sql_trabajo = "UPDATE trabajos_taller SET empleado_actual_id = ? WHERE id = ?";
            db_execute($sql_trabajo, [$empleado_destino_id, $trabajo_id]);
            
            // 2. Registrar transferencia
            $sql_transferencia = "INSERT INTO transferencias_trabajo (
                                    trabajo_id, empleado_origen_id, empleado_destino_id,
                                    estado_trabajo_momento, nota, usuario_registra_id
                                  ) VALUES (?, ?, ?, ?, ?, ?)";
            
            $params_transferencia = [
                $trabajo_id,
                $trabajo['empleado_actual_id'],
                $empleado_destino_id,
                $trabajo['estado'],
                $nota,
                usuario_actual_id()
            ];
            
            db_execute($sql_transferencia, $params_transferencia);
            
            // 3. Registrar auditoría
            $empleado_origen = db_query_one("SELECT nombre FROM usuarios WHERE id = ?", [$trabajo['empleado_actual_id']]);
            $empleado_destino = db_query_one("SELECT nombre FROM usuarios WHERE id = ?", [$empleado_destino_id]);
            
            registrar_auditoria('UPDATE', 'trabajos_taller', $trabajo_id, 
                "Trabajo transferido de {$empleado_origen['nombre']} a {$empleado_destino['nombre']} - {$trabajo['codigo']}");
            
            $pdo->commit();
            return true;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            registrar_error("Error al transferir trabajo: " . $e->getMessage());
            return false;
        }
    }
    
    // ========================================
    // MÉTODOS DE ELIMINACIÓN (SOFT DELETE)
    // ========================================
    
    /**
     * Cancela un trabajo (cambia estado a cancelado)
     * 
     * @param int $id ID del trabajo
     * @param string $motivo Motivo de cancelación
     * @return bool
     */
    public static function eliminar($id, $motivo = '') {
        global $pdo;
        
        try {
            $trabajo = self::obtenerPorId($id);
            
            if (!$trabajo) {
                return false;
            }
            
            // No se puede cancelar si ya está entregado
            if ($trabajo['estado'] === 'entregado') {
                return false;
            }
            
            // Cambiar estado a cancelado
            $sql = "UPDATE trabajos_taller SET 
                        estado = 'cancelado',
                        observaciones = CONCAT(COALESCE(observaciones, ''), ?)
                    WHERE id = ?";
            
            $obs_cancelacion = "\n[" . date('Y-m-d H:i:s') . "] Trabajo CANCELADO";
            if ($motivo) {
                $obs_cancelacion .= " - Motivo: {$motivo}";
            }
            
            $resultado = db_execute($sql, [$obs_cancelacion, $id]);
            
            if ($resultado) {
                registrar_auditoria('DELETE', 'trabajos_taller', $id, 
                    "Trabajo cancelado: {$trabajo['codigo']} - Motivo: {$motivo}");
            }
            
            return $resultado;
            
        } catch (PDOException $e) {
            registrar_error("Error al cancelar trabajo: " . $e->getMessage());
            return false;
        }
    }
    
    // ========================================
    // MÉTODOS DE VALIDACIÓN
    // ========================================
    
    /**
     * Valida los datos de un trabajo de taller
     * 
     * @param array $datos Datos a validar
     * @param int $id ID del trabajo (para actualización)
     * @return array Array de errores
     */
    public static function validar($datos, $id = null) {
        $errores = [];
        
        // Cliente nombre requerido
        if (empty($datos['cliente_nombre'])) {
            $errores[] = 'El nombre del cliente es requerido';
        } elseif (strlen($datos['cliente_nombre']) > 150) {
            $errores[] = 'El nombre del cliente no puede exceder 150 caracteres';
        }
        
        // Cliente teléfono requerido
        if (empty($datos['cliente_telefono'])) {
            $errores[] = 'El teléfono del cliente es requerido';
        } elseif (!validar_telefono($datos['cliente_telefono'])) {
            $errores[] = 'El teléfono debe tener 8 dígitos';
        }
        
        // Material requerido
        if (empty($datos['material'])) {
            $errores[] = 'El material es requerido';
        } elseif (!in_array($datos['material'], ['oro', 'plata', 'otro'])) {
            $errores[] = 'El material no es válido';
        }
        
        // Descripción de pieza requerida
        if (empty($datos['descripcion_pieza'])) {
            $errores[] = 'La descripción de la pieza es requerida';
        }
        
        // Tipo de trabajo requerido
        if (empty($datos['tipo_trabajo'])) {
            $errores[] = 'El tipo de trabajo es requerido';
        } elseif (!in_array($datos['tipo_trabajo'], ['reparacion', 'ajuste', 'grabado', 'diseño', 'limpieza', 'engaste', 'repuesto', 'fabricacion'])) {
            $errores[] = 'El tipo de trabajo no es válido';
        }
        
        // Descripción de trabajo requerida
        if (empty($datos['descripcion_trabajo'])) {
            $errores[] = 'La descripción del trabajo es requerida';
        }
        
        // Precio total requerido y positivo
        if (!isset($datos['precio_total']) || $datos['precio_total'] === '') {
            $errores[] = 'El precio total es requerido';
        } elseif (!validar_decimal_positivo($datos['precio_total']) || $datos['precio_total'] <= 0) {
            $errores[] = 'El precio total debe ser mayor a 0';
        }
        
        // Anticipo debe ser válido
        if (isset($datos['anticipo'])) {
            if (!validar_decimal_positivo($datos['anticipo'])) {
                $errores[] = 'El anticipo debe ser un valor positivo';
            } elseif (isset($datos['precio_total']) && $datos['anticipo'] > $datos['precio_total']) {
                $errores[] = 'El anticipo no puede ser mayor al precio total';
            }
        }
        
        // Fecha de entrega prometida requerida
        if (empty($datos['fecha_entrega_prometida'])) {
            $errores[] = 'La fecha de entrega prometida es requerida';
        } else {
            // Validar formato de fecha
            if (!validar_fecha($datos['fecha_entrega_prometida'])) {
                $errores[] = 'La fecha de entrega prometida no es válida';
            } else {
                // Validar que sea posterior a la fecha de recepción
                $fecha_recepcion = $datos['fecha_recepcion'] ?? date('Y-m-d');
                if (strtotime($datos['fecha_entrega_prometida']) < strtotime($fecha_recepcion)) {
                    $errores[] = 'La fecha de entrega prometida debe ser posterior a la fecha de recepción';
                }
            }
        }
        
        // Empleado que recibe requerido
        if (empty($datos['empleado_recibe_id'])) {
            $errores[] = 'El empleado que recibe es requerido';
        } elseif (!db_exists('usuarios', 'id = ?', [$datos['empleado_recibe_id']])) {
            $errores[] = 'El empleado que recibe no existe';
        }
        
        // Validar empleado actual si se proporciona
        if (isset($datos['empleado_actual_id']) && !empty($datos['empleado_actual_id'])) {
            if (!db_exists('usuarios', 'id = ?', [$datos['empleado_actual_id']])) {
                $errores[] = 'El empleado actual no existe';
            }
        }
        
        // Validar peso si se proporciona
        if (isset($datos['peso_gramos']) && !empty($datos['peso_gramos'])) {
            if (!validar_decimal_positivo($datos['peso_gramos'])) {
                $errores[] = 'El peso debe ser un valor positivo';
            }
        }
        
        // Validar largo si se proporciona
        if (isset($datos['largo_cm']) && !empty($datos['largo_cm'])) {
            if (!validar_decimal_positivo($datos['largo_cm'])) {
                $errores[] = 'El largo debe ser un valor positivo';
            }
        }
        
        return $errores;
    }
    
    /**
     * Verifica si existe un trabajo
     * 
     * @param int $id ID del trabajo
     * @return bool
     */
    public static function existe($id) {
        return db_exists('trabajos_taller', 'id = ?', [$id]);
    }
    
    // ========================================
    // MÉTODOS AUXILIARES
    // ========================================
    
    /**
     * Genera un código único para trabajo de taller
     * 
     * @return string Código generado (formato: TT-YYYY-####)
     */
    public static function generarCodigoTrabajo() {
        global $pdo;
        
        $anio = date('Y');
        $prefijo = 'TT-' . $anio . '-';
        
        // Obtener último número del año
        $sql = "SELECT codigo FROM trabajos_taller 
                WHERE codigo LIKE ? 
                ORDER BY id DESC LIMIT 1";
        
        try {
            $resultado = db_query_one($sql, [$prefijo . '%']);
            
            if ($resultado) {
                // Extraer el número secuencial
                $numero = (int)substr($resultado['codigo'], -4) + 1;
            } else {
                $numero = 1;
            }
            
            return $prefijo . str_pad($numero, 4, '0', STR_PAD_LEFT);
            
        } catch (PDOException $e) {
            registrar_error("Error al generar código de trabajo: " . $e->getMessage());
            return $prefijo . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        }
    }
    
    /**
     * Obtiene estadísticas de trabajos de taller
     * 
     * @param string $fecha_inicio Fecha de inicio (opcional)
     * @param string $fecha_fin Fecha de fin (opcional)
     * @return array Array con estadísticas
     */
    public static function obtenerEstadisticas($fecha_inicio = null, $fecha_fin = null) {
        global $pdo;
        
        try {
            $stats = [];
            
            // Construir filtro de fechas
            $where_fecha = '';
            $params_fecha = [];
            
            if ($fecha_inicio && $fecha_fin) {
                $where_fecha = 'AND DATE(fecha_recepcion) BETWEEN ? AND ?';
                $params_fecha = [$fecha_inicio, $fecha_fin];
            }
            
            // Total por estado
            $sql = "SELECT estado, COUNT(*) as total
                    FROM trabajos_taller
                    WHERE 1=1 $where_fecha
                    GROUP BY estado";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params_fecha);
            $stats['por_estado'] = $stmt->fetchAll();
            
            // Total por tipo de trabajo
            $sql = "SELECT tipo_trabajo, COUNT(*) as total
                    FROM trabajos_taller
                    WHERE 1=1 $where_fecha
                    GROUP BY tipo_trabajo
                    ORDER BY total DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params_fecha);
            $stats['por_tipo_trabajo'] = $stmt->fetchAll();
            
            // Total por material
            $sql = "SELECT material, COUNT(*) as total
                    FROM trabajos_taller
                    WHERE 1=1 $where_fecha
                    GROUP BY material
                    ORDER BY total DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params_fecha);
            $stats['por_material'] = $stmt->fetchAll();
            
            // Trabajos por empleado
            $sql = "SELECT u.nombre as empleado, COUNT(t.id) as total_trabajos,
                           SUM(CASE WHEN t.estado = 'completado' THEN 1 ELSE 0 END) as completados
                    FROM trabajos_taller t
                    LEFT JOIN usuarios u ON t.empleado_actual_id = u.id
                    WHERE 1=1 $where_fecha
                    GROUP BY u.id, u.nombre
                    ORDER BY total_trabajos DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params_fecha);
            $stats['por_empleado'] = $stmt->fetchAll();
            
            // Montos totales
            $sql = "SELECT 
                        COUNT(*) as total_trabajos,
                        SUM(precio_total) as monto_total,
                        SUM(anticipo) as total_anticipos,
                        SUM(saldo) as total_saldo_pendiente,
                        AVG(precio_total) as precio_promedio
                    FROM trabajos_taller
                    WHERE estado != 'cancelado' $where_fecha";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params_fecha);
            $stats['montos'] = $stmt->fetch();
            
            // Trabajos próximos a vencer (próximos 7 días)
            $sql = "SELECT COUNT(*) as total
                    FROM trabajos_taller
                    WHERE fecha_entrega_prometida BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                      AND estado NOT IN ('entregado', 'cancelado')";
            $stats['proximos_vencer'] = db_query_one($sql)['total'];
            
            // Trabajos atrasados
            $sql = "SELECT COUNT(*) as total
                    FROM trabajos_taller
                    WHERE fecha_entrega_prometida < CURDATE()
                      AND estado NOT IN ('entregado', 'cancelado')";
            $stats['atrasados'] = db_query_one($sql)['total'];
            
            return $stats;
            
        } catch (Exception $e) {
            registrar_error("Error al obtener estadísticas: " . $e->getMessage());
            return [];
        }
    }
}
?>
