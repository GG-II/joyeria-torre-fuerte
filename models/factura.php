<?php
/**
 * Modelo Factura
 * 
 * Gestión completa de facturas:
 * - Generación de facturas simples y electrónicas
 * - Anulación de facturas
 * - Consultas y listados
 * - Integración con SAT (preparado)
 * 
 * @author Sistema Joyería Torre Fuerte
 * @version 1.0
 * @date 2026-01-22
 */

class Factura {
    
    /**
     * Crea una factura para una venta
     * 
     * @param int $venta_id ID de la venta
     * @param array $datos Datos de la factura (nit, nombre, direccion, tipo)
     * @return int|false ID de la factura creada o false
     */
    public static function crear($venta_id, $datos = []) {
        try {
            // Verificar que la venta existe y no está anulada
            $venta = db_query_one(
                "SELECT * FROM ventas WHERE id = ? AND estado != 'anulada'",
                [$venta_id]
            );
            
            if (!$venta) {
                throw new Exception('Venta no encontrada o anulada');
            }
            
            // Verificar que la venta no tenga ya una factura activa
            if (self::ventaTieneFactura($venta_id)) {
                throw new Exception('Esta venta ya tiene una factura emitida');
            }
            
            // Validar datos
            $errores = self::validar($datos);
            if (!empty($errores)) {
                throw new Exception(implode(', ', $errores));
            }
            
            // Generar número de factura
            $numero_factura = self::generarNumeroFactura($datos['tipo'] ?? 'simple');
            
            // Insertar factura
            $sql = "INSERT INTO facturas (
                        venta_id, numero_factura, serie, nit, nombre, 
                        direccion, tipo, estado
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, 'emitida')";
            
            $resultado = db_execute($sql, [
                $venta_id,
                $numero_factura,
                $datos['serie'] ?? null,
                $datos['nit'] ?? 'C/F',
                $datos['nombre'] ?? 'Consumidor Final',
                $datos['direccion'] ?? null,
                $datos['tipo'] ?? 'simple'
            ]);
            
            if ($resultado) {
                $factura_id = $resultado; // db_execute devuelve el ID en INSERT
                
                // Registrar en auditoría
                registrar_auditoria(
                    'facturas',
                    'INSERT',
                    $factura_id,
                    "Factura generada: {$numero_factura} - Venta: {$venta['numero_venta']}"
                );
                
                return $factura_id;
            }
            
            return false;
            
        } catch (Exception $e) {
            registrar_error("Error al crear factura: " . $e->getMessage());
            // Re-lanzar excepción en desarrollo para debugging
            if (ENVIRONMENT === 'development') {
                throw $e;
            }
            return false;
        }
    }
    
    /**
     * Anula una factura
     * 
     * @param int $id ID de la factura
     * @param string $motivo Motivo de anulación
     * @return bool
     */
    public static function anular($id, $motivo) {
        try {
            // Verificar que la factura existe
            $factura = self::obtenerPorId($id);
            if (!$factura) {
                throw new Exception('Factura no encontrada');
            }
            
            // Verificar que no esté ya anulada
            if ($factura['estado'] === 'anulada') {
                throw new Exception('La factura ya está anulada');
            }
            
            // Validar motivo
            if (empty($motivo)) {
                throw new Exception('Debe especificar el motivo de anulación');
            }
            
            // Anular factura
            $sql = "UPDATE facturas SET 
                        estado = 'anulada',
                        motivo_anulacion = ?
                    WHERE id = ?";
            
            $resultado = db_execute($sql, [$motivo, $id]);
            
            if ($resultado) {
                // Registrar en auditoría
                registrar_auditoria(
                    'facturas',
                    'UPDATE',
                    $id,
                    "Factura anulada: {$factura['numero_factura']} - Motivo: {$motivo}"
                );
            }
            
            return $resultado;
            
        } catch (Exception $e) {
            registrar_error("Error al anular factura: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Certifica una factura electrónica con SAT
     * 
     * @param int $id ID de la factura
     * @param string $uuid UUID del SAT
     * @param string $xml_ruta Ruta al archivo XML certificado
     * @return bool
     */
    public static function certificar($id, $uuid, $xml_ruta = null) {
        try {
            // Verificar que la factura existe y es electrónica
            $factura = self::obtenerPorId($id);
            if (!$factura) {
                throw new Exception('Factura no encontrada');
            }
            
            if ($factura['tipo'] !== 'electronica') {
                throw new Exception('Solo se pueden certificar facturas electrónicas');
            }
            
            if ($factura['estado'] === 'anulada') {
                throw new Exception('No se puede certificar una factura anulada');
            }
            
            // Actualizar factura con datos de certificación
            $sql = "UPDATE facturas SET 
                        uuid_sat = ?,
                        xml_ruta = ?,
                        fecha_certificacion = NOW()
                    WHERE id = ?";
            
            $resultado = db_execute($sql, [$uuid, $xml_ruta, $id]);
            
            if ($resultado) {
                registrar_auditoria(
                    'facturas',
                    'UPDATE',
                    $id,
                    "Factura certificada con SAT - UUID: {$uuid}"
                );
            }
            
            return $resultado;
            
        } catch (Exception $e) {
            registrar_error("Error al certificar factura: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene una factura por ID
     * 
     * @param int $id ID de la factura
     * @return array|false Datos de la factura o false
     */
    public static function obtenerPorId($id) {
        try {
            $sql = "SELECT 
                        f.*,
                        v.numero_venta,
                        v.total as venta_total,
                        v.fecha as venta_fecha,
                        c.nombre as cliente_nombre,
                        s.nombre as sucursal_nombre,
                        u.nombre as vendedor_nombre
                    FROM facturas f
                    INNER JOIN ventas v ON f.venta_id = v.id
                    LEFT JOIN clientes c ON v.cliente_id = c.id
                    INNER JOIN sucursales s ON v.sucursal_id = s.id
                    INNER JOIN usuarios u ON v.usuario_id = u.id
                    WHERE f.id = ?";
            
            return db_query_one($sql, [$id]);
            
        } catch (Exception $e) {
            registrar_error("Error al obtener factura: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene una factura por número
     * 
     * @param string $numero_factura Número de factura
     * @return array|false Datos de la factura o false
     */
    public static function obtenerPorNumero($numero_factura) {
        try {
            $sql = "SELECT 
                        f.*,
                        v.numero_venta,
                        v.total as venta_total,
                        v.fecha as venta_fecha
                    FROM facturas f
                    INNER JOIN ventas v ON f.venta_id = v.id
                    WHERE f.numero_factura = ?";
            
            return db_query_one($sql, [$numero_factura]);
            
        } catch (Exception $e) {
            registrar_error("Error al obtener factura por número: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene la factura de una venta
     * 
     * @param int $venta_id ID de la venta
     * @return array|false Datos de la factura o false
     */
    public static function obtenerPorVenta($venta_id) {
        try {
            $sql = "SELECT * FROM facturas 
                    WHERE venta_id = ? AND estado = 'emitida'
                    ORDER BY id DESC 
                    LIMIT 1";
            
            return db_query_one($sql, [$venta_id]);
            
        } catch (Exception $e) {
            registrar_error("Error al obtener factura por venta: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Lista facturas con filtros
     * 
     * @param array $filtros Filtros opcionales: tipo, estado, fecha_desde, fecha_hasta, buscar
     * @return array Lista de facturas
     */
    public static function listar($filtros = []) {
        try {
            $where = [];
            $params = [];
            
            // Filtro por tipo
            if (isset($filtros['tipo']) && !empty($filtros['tipo'])) {
                $where[] = "f.tipo = ?";
                $params[] = $filtros['tipo'];
            }
            
            // Filtro por estado
            if (isset($filtros['estado']) && !empty($filtros['estado'])) {
                $where[] = "f.estado = ?";
                $params[] = $filtros['estado'];
            }
            
            // Filtro por rango de fechas
            if (isset($filtros['fecha_desde']) && !empty($filtros['fecha_desde'])) {
                $where[] = "DATE(f.fecha_emision) >= ?";
                $params[] = $filtros['fecha_desde'];
            }
            
            if (isset($filtros['fecha_hasta']) && !empty($filtros['fecha_hasta'])) {
                $where[] = "DATE(f.fecha_emision) <= ?";
                $params[] = $filtros['fecha_hasta'];
            }
            
            // Búsqueda por número, NIT o nombre
            if (isset($filtros['buscar']) && !empty($filtros['buscar'])) {
                $where[] = "(f.numero_factura LIKE ? OR f.nit LIKE ? OR f.nombre LIKE ?)";
                $termino = "%{$filtros['buscar']}%";
                $params[] = $termino;
                $params[] = $termino;
                $params[] = $termino;
            }
            
            $where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            
            $sql = "SELECT 
                        f.id,
                        f.numero_factura,
                        f.serie,
                        f.nit,
                        f.nombre,
                        f.tipo,
                        f.estado,
                        f.fecha_emision,
                        v.numero_venta,
                        v.total as venta_total,
                        s.nombre as sucursal_nombre
                    FROM facturas f
                    INNER JOIN ventas v ON f.venta_id = v.id
                    INNER JOIN sucursales s ON v.sucursal_id = s.id
                    $where_sql
                    ORDER BY f.fecha_emision DESC";
            
            return db_query($sql, $params);
            
        } catch (Exception $e) {
            registrar_error("Error al listar facturas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene estadísticas de facturas
     * 
     * @param string $fecha_desde Fecha desde (opcional)
     * @param string $fecha_hasta Fecha hasta (opcional)
     * @return array Estadísticas
     */
    public static function obtenerEstadisticas($fecha_desde = null, $fecha_hasta = null) {
        try {
            $where = [];
            $params = [];
            
            if ($fecha_desde) {
                $where[] = "DATE(fecha_emision) >= ?";
                $params[] = $fecha_desde;
            }
            
            if ($fecha_hasta) {
                $where[] = "DATE(fecha_emision) <= ?";
                $params[] = $fecha_hasta;
            }
            
            $where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            
            $sql = "SELECT 
                        COUNT(*) as total,
                        COUNT(CASE WHEN estado = 'emitida' THEN 1 END) as emitidas,
                        COUNT(CASE WHEN estado = 'anulada' THEN 1 END) as anuladas,
                        COUNT(CASE WHEN tipo = 'simple' THEN 1 END) as simples,
                        COUNT(CASE WHEN tipo = 'electronica' THEN 1 END) as electronicas
                    FROM facturas
                    $where_sql";
            
            return db_query_one($sql, $params);
            
        } catch (Exception $e) {
            registrar_error("Error al obtener estadísticas de facturas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Verifica si una venta ya tiene factura activa
     * 
     * @param int $venta_id ID de la venta
     * @return bool
     */
    private static function ventaTieneFactura($venta_id) {
        $sql = "SELECT COUNT(*) as total FROM facturas 
                WHERE venta_id = ? AND estado = 'emitida'";
        $resultado = db_query_one($sql, [$venta_id]);
        return $resultado['total'] > 0;
    }
    
    /**
     * Genera el próximo número de factura
     * 
     * @param string $tipo Tipo de factura (simple o electronica)
     * @return string Número de factura generado
     */
    private static function generarNumeroFactura($tipo = 'simple') {
        // Obtener la última factura del tipo
        $sql = "SELECT numero_factura FROM facturas 
                WHERE tipo = ? 
                ORDER BY id DESC 
                LIMIT 1";
        
        $ultima = db_query_one($sql, [$tipo]);
        
        if ($ultima) {
            // Extraer el número y aumentarlo
            preg_match('/(\d+)$/', $ultima['numero_factura'], $matches);
            $numero = isset($matches[1]) ? intval($matches[1]) + 1 : 1;
        } else {
            $numero = 1;
        }
        
        // Formato: FAC-SIMPLE-00001 o FAC-ELEC-00001
        $prefijo = $tipo === 'electronica' ? 'FAC-ELEC-' : 'FAC-SIMPLE-';
        return $prefijo . str_pad($numero, 5, '0', STR_PAD_LEFT);
    }
    
    /**
     * Valida los datos de la factura
     * 
     * @param array $datos Datos a validar
     * @return array Errores encontrados
     */
    private static function validar($datos) {
        $errores = [];
        
        // Tipo válido
        $tipos_validos = ['simple', 'electronica'];
        if (isset($datos['tipo']) && !in_array($datos['tipo'], $tipos_validos)) {
            $errores[] = 'El tipo de factura no es válido';
        }
        
        // Si es factura electrónica, NIT y nombre son requeridos
        if (isset($datos['tipo']) && $datos['tipo'] === 'electronica') {
            if (empty($datos['nit']) || $datos['nit'] === 'C/F') {
                $errores[] = 'El NIT es requerido para facturas electrónicas';
            }
            
            if (empty($datos['nombre']) || $datos['nombre'] === 'Consumidor Final') {
                $errores[] = 'El nombre completo es requerido para facturas electrónicas';
            }
        }
        
        return $errores;
    }
}