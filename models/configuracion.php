<?php
/**
 * Modelo Configuracion
 * 
 * Gestión completa de configuraciones del sistema:
 * - Almacenamiento clave-valor tipado
 * - Tipos soportados: string, number, boolean, json
 * - Obtención y actualización de valores
 * - Valores por defecto
 * - Caché de configuraciones
 * 
 * @author Sistema Joyería Torre Fuerte
 * @version 1.0
 * @date 2026-01-22
 */

class Configuracion {
    
    // Tipos de dato
    const TIPO_STRING = 'string';
    const TIPO_NUMBER = 'number';
    const TIPO_BOOLEAN = 'boolean';
    const TIPO_JSON = 'json';
    
    // Caché de configuraciones
    private static $cache = [];
    
    /**
     * Obtiene el valor de una configuración
     * 
     * @param string $clave Clave de la configuración
     * @param mixed $default Valor por defecto si no existe
     * @return mixed Valor de la configuración
     */
    public static function obtener($clave, $default = null) {
        try {
            // Verificar caché
            if (isset(self::$cache[$clave])) {
                return self::$cache[$clave];
            }
            
            $sql = "SELECT valor, tipo FROM configuracion_sistema WHERE clave = ?";
            $config = db_query_one($sql, [$clave]);
            
            if (!$config) {
                return $default;
            }
            
            // Convertir valor según tipo
            $valor = self::convertirValor($config['valor'], $config['tipo']);
            
            // Guardar en caché
            self::$cache[$clave] = $valor;
            
            return $valor;
            
        } catch (Exception $e) {
            registrar_error("Error al obtener configuración '$clave': " . $e->getMessage());
            return $default;
        }
    }
    
    /**
     * Establece el valor de una configuración
     * 
     * @param string $clave Clave de la configuración
     * @param mixed $valor Valor a guardar
     * @param string $tipo Tipo de dato
     * @param string $descripcion Descripción de la configuración
     * @return bool
     */
    public static function establecer($clave, $valor, $tipo = self::TIPO_STRING, $descripcion = null) {
        try {
            // Validar tipo
            if (!in_array($tipo, self::getTiposValidos())) {
                throw new Exception('Tipo de dato no válido');
            }
            
            // Preparar valor según tipo
            $valor_guardado = self::prepararValor($valor, $tipo);
            
            // Verificar si existe
            $existe = self::existe($clave);
            
            if ($existe) {
                // Actualizar
                $sql = "UPDATE configuracion_sistema 
                        SET valor = ?, tipo = ?, descripcion = ?
                        WHERE clave = ?";
                
                $resultado = db_execute($sql, [$valor_guardado, $tipo, $descripcion, $clave]);
            } else {
                // Crear
                $sql = "INSERT INTO configuracion_sistema 
                        (clave, valor, tipo, descripcion) 
                        VALUES (?, ?, ?, ?)";
                
                $resultado = db_execute($sql, [$clave, $valor_guardado, $tipo, $descripcion]);
            }
            
            if ($resultado) {
                // Limpiar caché
                unset(self::$cache[$clave]);
                
                // Registrar en auditoría
                $accion = $existe ? 'UPDATE' : 'INSERT';
                registrar_auditoria(
                    'configuracion_sistema',
                    $accion,
                    null,
                    "Configuración '$clave' " . ($existe ? 'actualizada' : 'creada')
                );
            }
            
            return $resultado;
            
        } catch (Exception $e) {
            registrar_error("Error al establecer configuración '$clave': " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Elimina una configuración
     * 
     * @param string $clave Clave de la configuración
     * @return bool
     */
    public static function eliminar($clave) {
        try {
            $sql = "DELETE FROM configuracion_sistema WHERE clave = ?";
            $resultado = db_execute($sql, [$clave]);
            
            if ($resultado) {
                // Limpiar caché
                unset(self::$cache[$clave]);
                
                registrar_auditoria(
                    'configuracion_sistema',
                    'DELETE',
                    null,
                    "Configuración '$clave' eliminada"
                );
            }
            
            return $resultado;
            
        } catch (Exception $e) {
            registrar_error("Error al eliminar configuración '$clave': " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verifica si existe una configuración
     * 
     * @param string $clave Clave de la configuración
     * @return bool
     */
    public static function existe($clave) {
        $sql = "SELECT COUNT(*) as total FROM configuracion_sistema WHERE clave = ?";
        $resultado = db_query_one($sql, [$clave]);
        return $resultado['total'] > 0;
    }
    
    /**
     * Obtiene todas las configuraciones
     * 
     * @param string $prefijo Filtrar por prefijo de clave (opcional)
     * @return array Lista de configuraciones
     */
    public static function obtenerTodas($prefijo = null) {
        try {
            $where = '';
            $params = [];
            
            if ($prefijo) {
                $where = "WHERE clave LIKE ?";
                $params[] = $prefijo . '%';
            }
            
            $sql = "SELECT clave, valor, tipo, descripcion, fecha_actualizacion 
                    FROM configuracion_sistema 
                    $where
                    ORDER BY clave ASC";
            
            $configuraciones = db_query($sql, $params);
            
            // Convertir valores según tipo
            foreach ($configuraciones as &$config) {
                $config['valor_convertido'] = self::convertirValor($config['valor'], $config['tipo']);
            }
            
            return $configuraciones;
            
        } catch (Exception $e) {
            registrar_error("Error al obtener todas las configuraciones: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene múltiples configuraciones como array asociativo
     * 
     * @param array $claves Array de claves a obtener
     * @return array Array asociativo clave => valor
     */
    public static function obtenerMultiples($claves) {
        $resultado = [];
        
        foreach ($claves as $clave) {
            $resultado[$clave] = self::obtener($clave);
        }
        
        return $resultado;
    }
    
    /**
     * Establece múltiples configuraciones a la vez
     * 
     * @param array $configuraciones Array asociativo clave => [valor, tipo, descripcion]
     * @return bool
     */
    public static function establecerMultiples($configuraciones) {
        try {
            foreach ($configuraciones as $clave => $config) {
                $valor = $config['valor'] ?? $config;
                $tipo = $config['tipo'] ?? self::TIPO_STRING;
                $descripcion = $config['descripcion'] ?? null;
                
                if (!self::establecer($clave, $valor, $tipo, $descripcion)) {
                    return false;
                }
            }
            
            return true;
            
        } catch (Exception $e) {
            registrar_error("Error al establecer múltiples configuraciones: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Limpia el caché de configuraciones
     * 
     * @param string $clave Clave específica o null para limpiar todo
     * @return void
     */
    public static function limpiarCache($clave = null) {
        if ($clave === null) {
            self::$cache = [];
        } else {
            unset(self::$cache[$clave]);
        }
    }
    
    /**
     * Obtiene configuraciones por categoría (usando prefijo)
     * 
     * @param string $categoria Categoría/prefijo (ej: 'smtp_', 'sistema_')
     * @return array Array asociativo de configuraciones
     */
    public static function obtenerCategoria($categoria) {
        $configuraciones = self::obtenerTodas($categoria);
        $resultado = [];
        
        foreach ($configuraciones as $config) {
            // Remover prefijo de la clave
            $clave_limpia = str_replace($categoria, '', $config['clave']);
            $resultado[$clave_limpia] = $config['valor_convertido'];
        }
        
        return $resultado;
    }
    
    /**
     * Inicializa configuraciones por defecto del sistema
     * 
     * @return bool
     */
    public static function inicializarDefaults() {
        try {
            $defaults = [
                // Sistema
                'sistema_nombre' => [
                    'valor' => 'Joyería Torre Fuerte',
                    'tipo' => self::TIPO_STRING,
                    'descripcion' => 'Nombre del sistema'
                ],
                'sistema_moneda' => [
                    'valor' => 'GTQ',
                    'tipo' => self::TIPO_STRING,
                    'descripcion' => 'Código de moneda (ISO 4217)'
                ],
                'sistema_iva' => [
                    'valor' => '12',
                    'tipo' => self::TIPO_NUMBER,
                    'descripcion' => 'Porcentaje de IVA'
                ],
                
                // Inventario
                'inventario_alerta_stock_minimo' => [
                    'valor' => 'true',
                    'tipo' => self::TIPO_BOOLEAN,
                    'descripcion' => 'Alertar cuando productos lleguen a stock mínimo'
                ],
                
                // Ventas
                'ventas_permitir_credito' => [
                    'valor' => 'true',
                    'tipo' => self::TIPO_BOOLEAN,
                    'descripcion' => 'Permitir ventas a crédito'
                ],
                'ventas_dias_credito_default' => [
                    'valor' => '30',
                    'tipo' => self::TIPO_NUMBER,
                    'descripcion' => 'Días de crédito por defecto'
                ],
                
                // Facturación
                'facturacion_serie_default' => [
                    'valor' => 'A',
                    'tipo' => self::TIPO_STRING,
                    'descripcion' => 'Serie de facturación por defecto'
                ],
                
                // Notificaciones
                'notificaciones_email_ventas' => [
                    'valor' => 'false',
                    'tipo' => self::TIPO_BOOLEAN,
                    'descripcion' => 'Enviar email al completar venta'
                ]
            ];
            
            foreach ($defaults as $clave => $config) {
                if (!self::existe($clave)) {
                    self::establecer(
                        $clave,
                        $config['valor'],
                        $config['tipo'],
                        $config['descripcion']
                    );
                }
            }
            
            return true;
            
        } catch (Exception $e) {
            registrar_error("Error al inicializar configuraciones: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Convierte un valor de string a su tipo correspondiente
     * 
     * @param string $valor Valor en formato string
     * @param string $tipo Tipo de dato
     * @return mixed Valor convertido
     */
    private static function convertirValor($valor, $tipo) {
        switch ($tipo) {
            case self::TIPO_NUMBER:
                return is_numeric($valor) ? floatval($valor) : 0;
                
            case self::TIPO_BOOLEAN:
                return filter_var($valor, FILTER_VALIDATE_BOOLEAN);
                
            case self::TIPO_JSON:
                $decoded = json_decode($valor, true);
                return $decoded !== null ? $decoded : [];
                
            case self::TIPO_STRING:
            default:
                return (string)$valor;
        }
    }
    
    /**
     * Prepara un valor para guardarse según su tipo
     * 
     * @param mixed $valor Valor a preparar
     * @param string $tipo Tipo de dato
     * @return string Valor preparado para guardar
     */
    private static function prepararValor($valor, $tipo) {
        switch ($tipo) {
            case self::TIPO_NUMBER:
                return (string)floatval($valor);
                
            case self::TIPO_BOOLEAN:
                return $valor ? 'true' : 'false';
                
            case self::TIPO_JSON:
                return json_encode($valor);
                
            case self::TIPO_STRING:
            default:
                return (string)$valor;
        }
    }
    
    /**
     * Obtiene los tipos de dato válidos
     * 
     * @return array Tipos válidos
     */
    private static function getTiposValidos() {
        return [
            self::TIPO_STRING,
            self::TIPO_NUMBER,
            self::TIPO_BOOLEAN,
            self::TIPO_JSON
        ];
    }
    
    /**
     * Exporta todas las configuraciones a un array
     * 
     * @return array Configuraciones exportadas
     */
    public static function exportar() {
        $configuraciones = self::obtenerTodas();
        $exportado = [];
        
        foreach ($configuraciones as $config) {
            $exportado[$config['clave']] = [
                'valor' => $config['valor_convertido'],
                'tipo' => $config['tipo'],
                'descripcion' => $config['descripcion']
            ];
        }
        
        return $exportado;
    }
    
    /**
     * Importa configuraciones desde un array
     * 
     * @param array $configuraciones Array de configuraciones
     * @return bool
     */
    public static function importar($configuraciones) {
        return self::establecerMultiples($configuraciones);
    }
}
