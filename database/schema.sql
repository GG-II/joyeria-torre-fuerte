-- ========================================
-- SCHEMA COMPLETO - JOYERÍA TORRE FUERTE
-- Sistema de Gestión Integral
-- Fecha: 20 de enero de 2026
-- Versión: 1.0
-- ========================================

-- Eliminar BD si existe (SOLO PARA DESARROLLO)
DROP DATABASE IF EXISTS joyeria_torre_fuerte;

-- Crear base de datos
CREATE DATABASE joyeria_torre_fuerte 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE joyeria_torre_fuerte;

-- ========================================
-- 1. TABLAS DE ESTRUCTURA
-- ========================================

-- Sucursales
CREATE TABLE sucursales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    direccion TEXT NOT NULL,
    telefono VARCHAR(20) NULL,
    email VARCHAR(100) NULL,
    responsable_id INT NULL,
    activo BOOLEAN DEFAULT 1,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('administrador', 'dueño', 'vendedor', 'cajero', 'orfebre', 'publicidad') NOT NULL,
    sucursal_id INT NULL,
    foto_perfil VARCHAR(255) NULL,
    activo BOOLEAN DEFAULT 1,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    ultimo_acceso DATETIME NULL,
    
    INDEX idx_email (email),
    INDEX idx_rol (rol),
    INDEX idx_activo (activo),
    FOREIGN KEY (sucursal_id) REFERENCES sucursales(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Actualizar foreign key de responsable_id en sucursales
ALTER TABLE sucursales
ADD CONSTRAINT fk_sucursal_responsable
FOREIGN KEY (responsable_id) REFERENCES usuarios(id) ON DELETE SET NULL;

-- ========================================
-- 2. PRODUCTOS E INVENTARIO
-- ========================================

-- Categorías
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT NULL,
    tipo_clasificacion ENUM('tipo', 'material', 'peso') NOT NULL,
    categoria_padre_id INT NULL,
    activo BOOLEAN DEFAULT 1,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_tipo (tipo_clasificacion),
    INDEX idx_activo (activo),
    FOREIGN KEY (categoria_padre_id) REFERENCES categorias(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Proveedores
CREATE TABLE proveedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    empresa VARCHAR(150) NULL,
    contacto VARCHAR(100) NULL,
    telefono VARCHAR(20) NULL,
    email VARCHAR(100) NULL,
    direccion TEXT NULL,
    productos_suministra TEXT NULL,
    activo BOOLEAN DEFAULT 1,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Productos
CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    codigo_barras VARCHAR(50) NULL UNIQUE,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT NULL,
    categoria_id INT NOT NULL,
    proveedor_id INT NULL,
    es_por_peso BOOLEAN DEFAULT 0,
    peso_gramos DECIMAL(10,3) NULL,
    estilo VARCHAR(100) NULL,
    largo_cm DECIMAL(10,2) NULL,
    imagen VARCHAR(255) NULL,
    activo BOOLEAN DEFAULT 1,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_codigo (codigo),
    INDEX idx_codigo_barras (codigo_barras),
    INDEX idx_nombre (nombre),
    INDEX idx_categoria (categoria_id),
    INDEX idx_activo (activo),
    INDEX idx_por_peso (es_por_peso),
    FULLTEXT idx_busqueda (nombre, descripcion),
    
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE RESTRICT,
    FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Precios de productos
CREATE TABLE precios_producto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    tipo_precio ENUM('publico', 'mayorista', 'descuento', 'especial') NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    activo BOOLEAN DEFAULT 1,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_producto_tipo (producto_id, tipo_precio),
    INDEX idx_tipo_precio (tipo_precio),
    INDEX idx_activo (activo),
    
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inventario
CREATE TABLE inventario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    sucursal_id INT NOT NULL,
    cantidad INT NOT NULL DEFAULT 0,
    stock_minimo INT DEFAULT 5,
    es_compartido BOOLEAN DEFAULT 0,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_producto_sucursal (producto_id, sucursal_id),
    INDEX idx_stock_bajo (cantidad, stock_minimo),
    INDEX idx_compartido (es_compartido),
    
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    FOREIGN KEY (sucursal_id) REFERENCES sucursales(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Movimientos de inventario
CREATE TABLE movimientos_inventario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    sucursal_id INT NOT NULL,
    tipo_movimiento ENUM('ingreso', 'salida', 'ajuste', 'transferencia', 'venta') NOT NULL,
    cantidad INT NOT NULL,
    cantidad_anterior INT NOT NULL,
    cantidad_nueva INT NOT NULL,
    motivo TEXT NULL,
    usuario_id INT NOT NULL,
    referencia_tipo ENUM('venta', 'compra', 'transferencia', 'ajuste_manual') NULL,
    referencia_id INT NULL,
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_producto (producto_id),
    INDEX idx_sucursal (sucursal_id),
    INDEX idx_tipo (tipo_movimiento),
    INDEX idx_fecha (fecha_hora),
    INDEX idx_referencia (referencia_tipo, referencia_id),
    
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    FOREIGN KEY (sucursal_id) REFERENCES sucursales(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Transferencias de inventario
CREATE TABLE transferencias_inventario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sucursal_origen_id INT NOT NULL,
    sucursal_destino_id INT NOT NULL,
    usuario_id INT NOT NULL,
    estado ENUM('pendiente', 'completada', 'cancelada') DEFAULT 'pendiente',
    observaciones TEXT NULL,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_completado DATETIME NULL,
    
    INDEX idx_estado (estado),
    INDEX idx_fecha (fecha_creacion),
    
    FOREIGN KEY (sucursal_origen_id) REFERENCES sucursales(id) ON DELETE RESTRICT,
    FOREIGN KEY (sucursal_destino_id) REFERENCES sucursales(id) ON DELETE RESTRICT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Detalle de transferencias
CREATE TABLE detalle_transferencias_inventario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transferencia_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    
    INDEX idx_transferencia (transferencia_id),
    
    FOREIGN KEY (transferencia_id) REFERENCES transferencias_inventario(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Materias primas
CREATE TABLE materias_primas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    tipo ENUM('oro', 'plata', 'piedras', 'otros') NOT NULL,
    unidad_medida ENUM('gramos', 'quilates', 'unidades') NOT NULL,
    precio_actual DECIMAL(10,2) NOT NULL,
    sucursal_id INT NOT NULL,
    cantidad_disponible DECIMAL(10,3) NOT NULL DEFAULT 0,
    activo BOOLEAN DEFAULT 1,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_tipo (tipo),
    INDEX idx_sucursal (sucursal_id),
    INDEX idx_activo (activo),
    
    FOREIGN KEY (sucursal_id) REFERENCES sucursales(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 3. TALLER
-- ========================================

-- Trabajos de taller
CREATE TABLE trabajos_taller (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_orden VARCHAR(50) NOT NULL UNIQUE,
    cliente_id INT NULL,
    nombre_cliente VARCHAR(100) NOT NULL,
    telefono_cliente VARCHAR(20) NULL,
    tipo_trabajo ENUM('reparacion', 'fabricacion', 'otro') NOT NULL,
    descripcion TEXT NOT NULL,
    peso_inicial DECIMAL(10,3) NULL,
    precio_total DECIMAL(10,2) NOT NULL,
    anticipo DECIMAL(10,2) DEFAULT 0,
    saldo DECIMAL(10,2) AS (precio_total - anticipo) STORED,
    fecha_entrega_estimada DATE NULL,
    fecha_entrega_real DATE NULL,
    estado ENUM('recepcion', 'en_proceso', 'finalizado', 'entregado', 'cancelado') DEFAULT 'recepcion',
    empleado_actual_id INT NULL,
    sucursal_id INT NOT NULL,
    usuario_creacion_id INT NOT NULL,
    observaciones TEXT NULL,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_numero_orden (numero_orden),
    INDEX idx_cliente (cliente_id),
    INDEX idx_estado (estado),
    INDEX idx_empleado (empleado_actual_id),
    INDEX idx_sucursal (sucursal_id),
    INDEX idx_fecha_entrega (fecha_entrega_estimada),
    
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
    FOREIGN KEY (empleado_actual_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (sucursal_id) REFERENCES sucursales(id) ON DELETE RESTRICT,
    FOREIGN KEY (usuario_creacion_id) REFERENCES usuarios(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Transferencias de trabajos (HISTORIAL INMUTABLE)
CREATE TABLE transferencias_trabajo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trabajo_id INT NOT NULL,
    empleado_origen_id INT NULL,
    empleado_destino_id INT NOT NULL,
    estado_trabajo_anterior ENUM('recepcion', 'en_proceso', 'finalizado', 'entregado', 'cancelado') NOT NULL,
    estado_trabajo_nuevo ENUM('recepcion', 'en_proceso', 'finalizado', 'entregado', 'cancelado') NOT NULL,
    motivo TEXT NULL,
    usuario_registro_id INT NOT NULL,
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_trabajo (trabajo_id),
    INDEX idx_empleado_origen (empleado_origen_id),
    INDEX idx_empleado_destino (empleado_destino_id),
    INDEX idx_fecha (fecha_hora),
    
    FOREIGN KEY (trabajo_id) REFERENCES trabajos_taller(id) ON DELETE RESTRICT,
    FOREIGN KEY (empleado_origen_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (empleado_destino_id) REFERENCES usuarios(id) ON DELETE RESTRICT,
    FOREIGN KEY (usuario_registro_id) REFERENCES usuarios(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Materiales usados en trabajos
CREATE TABLE materiales_trabajo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trabajo_id INT NOT NULL,
    materia_prima_id INT NULL,
    descripcion VARCHAR(200) NOT NULL,
    cantidad DECIMAL(10,3) NOT NULL,
    unidad_medida VARCHAR(20) NOT NULL,
    costo DECIMAL(10,2) NOT NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_trabajo (trabajo_id),
    INDEX idx_materia_prima (materia_prima_id),
    
    FOREIGN KEY (trabajo_id) REFERENCES trabajos_taller(id) ON DELETE CASCADE,
    FOREIGN KEY (materia_prima_id) REFERENCES materias_primas(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 4. CLIENTES
-- ========================================

-- Clientes
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    nit VARCHAR(20) NULL,
    telefono VARCHAR(20) NULL,
    email VARCHAR(100) NULL,
    direccion TEXT NULL,
    tipo ENUM('normal', 'mayorista') DEFAULT 'normal',
    limite_credito DECIMAL(10,2) DEFAULT 0,
    credito_disponible DECIMAL(10,2) DEFAULT 0,
    activo BOOLEAN DEFAULT 1,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_codigo (codigo),
    INDEX idx_nombre (nombre),
    INDEX idx_nit (nit),
    INDEX idx_tipo (tipo),
    INDEX idx_activo (activo),
    
    FULLTEXT idx_busqueda_cliente (nombre, nit, telefono)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 5. VENTAS
-- ========================================

-- Ventas
CREATE TABLE ventas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_venta VARCHAR(50) NOT NULL UNIQUE,
    fecha_venta DATETIME DEFAULT CURRENT_TIMESTAMP,
    cliente_id INT NULL,
    usuario_id INT NOT NULL,
    sucursal_id INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    descuento DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) AS (subtotal - descuento) STORED,
    tipo_venta ENUM('contado', 'credito') DEFAULT 'contado',
    estado ENUM('completada', 'cancelada') DEFAULT 'completada',
    observaciones TEXT NULL,
    
    INDEX idx_numero_venta (numero_venta),
    INDEX idx_fecha (fecha_venta),
    INDEX idx_cliente (cliente_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_sucursal (sucursal_id),
    INDEX idx_tipo (tipo_venta),
    INDEX idx_estado (estado),
    
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT,
    FOREIGN KEY (sucursal_id) REFERENCES sucursales(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Detalle de ventas
CREATE TABLE detalle_ventas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    venta_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    tipo_precio_aplicado ENUM('publico', 'mayorista', 'descuento', 'especial') NOT NULL,
    subtotal DECIMAL(10,2) AS (cantidad * precio_unitario) STORED,
    
    INDEX idx_venta (venta_id),
    INDEX idx_producto (producto_id),
    
    FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Formas de pago por venta (MÚLTIPLES)
CREATE TABLE formas_pago_venta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    venta_id INT NOT NULL,
    forma_pago ENUM('efectivo', 'tarjeta_debito', 'tarjeta_credito', 'transferencia', 'cheque') NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    referencia VARCHAR(100) NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_venta (venta_id),
    INDEX idx_forma_pago (forma_pago),
    
    FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Créditos a clientes
CREATE TABLE creditos_clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    venta_id INT NOT NULL,
    monto_total DECIMAL(10,2) NOT NULL,
    saldo_pendiente DECIMAL(10,2) NOT NULL,
    cuota_semanal DECIMAL(10,2) NOT NULL,
    numero_cuotas INT NOT NULL,
    cuotas_pagadas INT DEFAULT 0,
    fecha_inicio DATE NOT NULL,
    fecha_proximo_pago DATE NOT NULL,
    fecha_ultimo_abono DATE NULL,
    estado ENUM('activo', 'liquidado', 'vencido') DEFAULT 'activo',
    dias_atraso INT DEFAULT 0,
    fecha_liquidacion DATE NULL,
    
    INDEX idx_cliente (cliente_id),
    INDEX idx_venta (venta_id),
    INDEX idx_estado (estado),
    INDEX idx_proximo_pago (fecha_proximo_pago),
    INDEX idx_atraso (dias_atraso),
    
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE RESTRICT,
    FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Abonos a créditos
CREATE TABLE abonos_creditos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    credito_id INT NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    forma_pago ENUM('efectivo', 'tarjeta_debito', 'tarjeta_credito', 'transferencia', 'cheque') NOT NULL,
    fecha_abono DATE NOT NULL,
    saldo_anterior DECIMAL(10,2) NOT NULL,
    saldo_nuevo DECIMAL(10,2) NOT NULL,
    usuario_id INT NOT NULL,
    caja_id INT NULL,
    observaciones TEXT NULL,
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_credito (credito_id),
    INDEX idx_fecha (fecha_abono),
    
    FOREIGN KEY (credito_id) REFERENCES creditos_clientes(id) ON DELETE RESTRICT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Facturas
CREATE TABLE facturas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    venta_id INT NOT NULL,
    numero_factura VARCHAR(50) NOT NULL UNIQUE,
    serie VARCHAR(10) NULL,
    nit VARCHAR(20) NULL,
    nombre VARCHAR(200) NULL,
    direccion TEXT NULL,
    uuid_sat VARCHAR(100) NULL UNIQUE,
    xml_ruta VARCHAR(255) NULL,
    fecha_certificacion DATETIME NULL,
    tipo ENUM('simple', 'electronica') DEFAULT 'simple',
    estado ENUM('emitida', 'anulada') DEFAULT 'emitida',
    motivo_anulacion TEXT NULL,
    fecha_emision DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_venta (venta_id),
    INDEX idx_numero (numero_factura),
    INDEX idx_tipo (tipo),
    INDEX idx_estado (estado),
    
    FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 6. CAJA
-- ========================================

-- Cajas
CREATE TABLE cajas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    sucursal_id INT NOT NULL,
    fecha_apertura DATETIME NOT NULL,
    fecha_cierre DATETIME NULL,
    monto_inicial DECIMAL(10,2) NOT NULL,
    monto_esperado DECIMAL(10,2) NULL,
    monto_real DECIMAL(10,2) NULL,
    diferencia DECIMAL(10,2) AS (monto_real - monto_esperado) STORED,
    observaciones_cierre TEXT NULL,
    estado ENUM('abierta', 'cerrada') DEFAULT 'abierta',
    
    INDEX idx_usuario (usuario_id),
    INDEX idx_sucursal (sucursal_id),
    INDEX idx_estado (estado),
    INDEX idx_fecha_apertura (fecha_apertura),
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT,
    FOREIGN KEY (sucursal_id) REFERENCES sucursales(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Foreign key de abonos_creditos a cajas
ALTER TABLE abonos_creditos
ADD CONSTRAINT fk_abono_caja
FOREIGN KEY (caja_id) REFERENCES cajas(id) ON DELETE SET NULL;

-- Movimientos de caja
CREATE TABLE movimientos_caja (
    id INT AUTO_INCREMENT PRIMARY KEY,
    caja_id INT NOT NULL,
    tipo_movimiento ENUM(
        'venta',
        'ingreso_reparacion',
        'anticipo_trabajo',
        'abono_credito',
        'anticipo_apartado',
        'gasto',
        'pago_proveedor',
        'compra_material',
        'alquiler',
        'salario',
        'otro_ingreso',
        'otro_egreso'
    ) NOT NULL,
    categoria ENUM('ingreso', 'egreso') NOT NULL,
    concepto TEXT NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    usuario_id INT NOT NULL,
    referencia_tipo VARCHAR(50) NULL,
    referencia_id INT NULL,
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_caja (caja_id),
    INDEX idx_tipo (tipo_movimiento),
    INDEX idx_categoria (categoria),
    INDEX idx_fecha (fecha_hora),
    INDEX idx_referencia (referencia_tipo, referencia_id),
    
    FOREIGN KEY (caja_id) REFERENCES cajas(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 7. AUDITORÍA Y CONFIGURACIÓN
-- ========================================

-- Log de auditoría
CREATE TABLE audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    accion VARCHAR(100) NOT NULL,
    tabla_afectada VARCHAR(50) NULL,
    registro_id INT NULL,
    detalles TEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_usuario (usuario_id),
    INDEX idx_accion (accion),
    INDEX idx_tabla (tabla_afectada),
    INDEX idx_fecha (fecha_hora),
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Configuración del sistema
CREATE TABLE configuracion_sistema (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT NOT NULL,
    tipo ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    descripcion TEXT NULL,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_clave (clave)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- FIN DEL SCHEMA
-- ========================================
-- 
-- RESUMEN:
-- - 25 tablas creadas
-- - Todas las relaciones establecidas
-- - Índices optimizados
-- - Campos calculados automáticamente
-- - UTF-8 configurado correctamente
-- 
-- Para ejecutar:
-- 1. Abre phpMyAdmin
-- 2. Clic en "SQL" en el menú superior
-- 3. Pega todo este código
-- 4. Clic en "Continuar"
-- 
-- ========================================