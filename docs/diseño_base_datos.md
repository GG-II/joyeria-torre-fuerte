## 🗂️ ANÁLISIS PREVIO: ENTIDADES IDENTIFICADAS

Basándome en los requerimientos del cliente, necesitamos:

### **Entidades Core:**
1. **Usuarios y Seguridad** (login, roles, auditoría)
2. **Sucursales** (2 sucursales)
3. **Productos e Inventario** (multi-sucursal, multi-precio, por peso)
4. **Categorías y Proveedores**
5. **Taller** (trabajos, transferencias entre empleados, estados)
6. **Ventas** (POS, múltiples formas de pago, créditos)
7. **Clientes** (mayoristas, créditos semanales, historial)
8. **Caja** (apertura, movimientos, cierre diario)
9. **Reportes** (datos derivados de las tablas anteriores)

---

## 📋 DISEÑO DETALLADO TABLA POR TABLA

### **1. TABLA: usuarios**
Gestiona todos los usuarios del sistema (6 personas)

```sql
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- Hash bcrypt
    rol ENUM('administrador', 'dueño', 'vendedor', 'cajero', 'orfebre', 'publicidad') NOT NULL,
    sucursal_id INT NULL, -- NULL para roles sin sucursal fija
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
```

**Campos explicados:**
- `password`: Almacena hash bcrypt (seguridad)
- `sucursal_id`: Permite NULL porque admin/dueño/publicidad pueden no tener sucursal fija
- `activo`: Soft delete, nunca borramos usuarios
- `ultimo_acceso`: Para rastrear actividad

---

### **2. TABLA: sucursales**
Las 2 sucursales del negocio

```sql
CREATE TABLE sucursales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    direccion TEXT NOT NULL,
    telefono VARCHAR(20) NULL,
    email VARCHAR(100) NULL,
    responsable_id INT NULL,
    activo BOOLEAN DEFAULT 1,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_activo (activo),
    FOREIGN KEY (responsable_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### **3. TABLA: categorias**
Categorías de productos (tipo, material, peso)

```sql
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT NULL,
    tipo_clasificacion ENUM('tipo', 'material', 'peso') NOT NULL,
    categoria_padre_id INT NULL, -- Para subcategorías
    activo BOOLEAN DEFAULT 1,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_tipo (tipo_clasificacion),
    INDEX idx_activo (activo),
    FOREIGN KEY (categoria_padre_id) REFERENCES categorias(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### **4. TABLA: proveedores**
Proveedores de productos

```sql
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
```

---

### **5. TABLA: productos**
Catálogo de productos (joyería, estuches, exhibidores)

```sql
CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    codigo_barras VARCHAR(50) NULL UNIQUE,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT NULL,
    categoria_id INT NOT NULL,
    proveedor_id INT NULL,
    
    -- Características especiales
    es_por_peso BOOLEAN DEFAULT 0, -- Oro/plata por gramo
    peso_gramos DECIMAL(10,3) NULL, -- Si es por peso
    
    -- Presentaciones múltiples
    estilo VARCHAR(100) NULL,
    largo_cm DECIMAL(10,2) NULL,
    
    -- Imagen
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
```

**Características especiales:**
- `es_por_peso`: Oro/plata se vende por gramo
- `peso_gramos`: Peso del producto si aplica
- `estilo`, `largo_cm`: Presentaciones múltiples
- `FULLTEXT`: Para búsquedas rápidas por nombre

---

### **6. TABLA: precios_producto**
4 tipos de precio por producto (público, mayorista, descuento, especial)

```sql
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
```

**Lógica:** Cada producto tiene 4 registros (uno por cada tipo de precio)

---

### **7. TABLA: inventario**
Control de stock por sucursal y producto

```sql
CREATE TABLE inventario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    sucursal_id INT NOT NULL,
    cantidad INT NOT NULL DEFAULT 0,
    stock_minimo INT DEFAULT 5,
    es_compartido BOOLEAN DEFAULT 0, -- Algunos productos compartidos, otros separados
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_producto_sucursal (producto_id, sucursal_id),
    INDEX idx_stock_bajo (cantidad, stock_minimo),
    INDEX idx_compartido (es_compartido),
    
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    FOREIGN KEY (sucursal_id) REFERENCES sucursales(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Lógica importante:**
- `es_compartido`: Cliente dijo "algunos productos separados, otros compartidos"
- Cada producto tiene 2 registros (uno por sucursal)
- `stock_minimo` default 5 (cliente lo pidió)

---

### **8. TABLA: movimientos_inventario**
Historial completo de movimientos de inventario

```sql
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
    referencia_id INT NULL, -- ID de la venta, compra, etc.
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
```

**Auditoría completa:** Registra TODOS los movimientos de inventario

---

### **9. TABLA: transferencias_inventario**
Transferencias de productos entre sucursales

```sql
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
```

---

### **10. TABLA: detalle_transferencias_inventario**
Productos específicos de cada transferencia

```sql
CREATE TABLE detalle_transferencias_inventario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transferencia_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    
    INDEX idx_transferencia (transferencia_id),
    
    FOREIGN KEY (transferencia_id) REFERENCES transferencias_inventario(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### **11. TABLA: materias_primas**
Control de materias primas para taller (oro, plata, piedras)

```sql
CREATE TABLE materias_primas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    tipo ENUM('oro', 'plata', 'piedra', 'otro') NOT NULL,
    unidad_medida ENUM('gramos', 'piezas', 'quilates') NOT NULL,
    cantidad_disponible DECIMAL(10,3) NOT NULL DEFAULT 0,
    stock_minimo DECIMAL(10,3) DEFAULT 5,
    precio_por_unidad DECIMAL(10,2) NULL,
    activo BOOLEAN DEFAULT 1,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_tipo (tipo),
    INDEX idx_activo (activo),
    INDEX idx_stock_bajo (cantidad_disponible, stock_minimo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### **12. TABLA: clientes**
Gestión de clientes (públicos y mayoristas)

```sql
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    nit VARCHAR(20) NULL,
    telefono VARCHAR(20) NOT NULL,
    email VARCHAR(100) NULL,
    direccion TEXT NULL,
    tipo_cliente ENUM('publico', 'mayorista') DEFAULT 'publico',
    tipo_mercaderias ENUM('oro', 'plata', 'ambas') DEFAULT 'ambas',
    limite_credito DECIMAL(10,2) NULL, -- Para mayoristas
    plazo_credito_dias INT NULL, -- Plazo permitido
    activo BOOLEAN DEFAULT 1,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_nombre (nombre),
    INDEX idx_telefono (telefono),
    INDEX idx_tipo (tipo_cliente),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### **13. TABLA: trabajos_taller**
**MÓDULO CRÍTICO** - Gestión de trabajos del taller

```sql
CREATE TABLE trabajos_taller (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE, -- Auto-generado: TT-2026-0001
    
    -- Información del cliente
    cliente_nombre VARCHAR(150) NOT NULL,
    cliente_telefono VARCHAR(20) NOT NULL,
    cliente_id INT NULL, -- Opcional, puede ser cliente registrado o no
    
    -- Descripción de la pieza
    material ENUM('oro', 'plata', 'otro') NOT NULL,
    peso_gramos DECIMAL(10,3) NULL,
    largo_cm DECIMAL(10,2) NULL,
    con_piedra BOOLEAN DEFAULT 0,
    estilo VARCHAR(100) NULL,
    descripcion_pieza TEXT NOT NULL,
    
    -- Tipo de trabajo
    tipo_trabajo ENUM('reparacion', 'ajuste', 'grabado', 'diseño', 'limpieza', 'engaste', 'repuesto', 'fabricacion') NOT NULL,
    descripcion_trabajo TEXT NOT NULL,
    
    -- Precios
    precio_total DECIMAL(10,2) NOT NULL,
    anticipo DECIMAL(10,2) DEFAULT 0,
    saldo DECIMAL(10,2) AS (precio_total - anticipo) STORED, -- Calculado automáticamente
    
    -- Fechas
    fecha_recepcion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_entrega_prometida DATE NOT NULL,
    fecha_entrega_real DATETIME NULL,
    
    -- Control de empleados
    empleado_recibe_id INT NOT NULL, -- Quién recibe el trabajo
    empleado_actual_id INT NOT NULL, -- Quién tiene el trabajo AHORA
    empleado_entrega_id INT NULL, -- Quién entrega el trabajo terminado
    
    -- Estado
    estado ENUM('recibido', 'en_proceso', 'completado', 'entregado', 'cancelado') DEFAULT 'recibido',
    
    observaciones TEXT NULL,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_codigo (codigo),
    INDEX idx_cliente_telefono (cliente_telefono),
    INDEX idx_estado (estado),
    INDEX idx_fecha_entrega (fecha_entrega_prometida),
    INDEX idx_empleado_actual (empleado_actual_id),
    INDEX idx_material (material),
    
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
    FOREIGN KEY (empleado_recibe_id) REFERENCES usuarios(id) ON DELETE RESTRICT,
    FOREIGN KEY (empleado_actual_id) REFERENCES usuarios(id) ON DELETE RESTRICT,
    FOREIGN KEY (empleado_entrega_id) REFERENCES usuarios(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Campo clave:** `saldo` es calculado automáticamente (STORED)

---

### **14. TABLA: transferencias_trabajo**
**CRÍTICO** - Historial completo de transferencias entre empleados

```sql
CREATE TABLE transferencias_trabajo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trabajo_id INT NOT NULL,
    empleado_origen_id INT NOT NULL,
    empleado_destino_id INT NOT NULL,
    fecha_transferencia DATETIME DEFAULT CURRENT_TIMESTAMP,
    estado_trabajo_momento VARCHAR(50) NOT NULL, -- Estado del trabajo en ese momento
    nota TEXT NULL,
    usuario_registra_id INT NOT NULL, -- Puede ser diferente del empleado origen
    
    INDEX idx_trabajo (trabajo_id),
    INDEX idx_empleado_origen (empleado_origen_id),
    INDEX idx_empleado_destino (empleado_destino_id),
    INDEX idx_fecha (fecha_transferencia),
    
    FOREIGN KEY (trabajo_id) REFERENCES trabajos_taller(id) ON DELETE CASCADE,
    FOREIGN KEY (empleado_origen_id) REFERENCES usuarios(id) ON DELETE RESTRICT,
    FOREIGN KEY (empleado_destino_id) REFERENCES usuarios(id) ON DELETE RESTRICT,
    FOREIGN KEY (usuario_registra_id) REFERENCES usuarios(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**NUNCA se borra**: Historial inmutable para rastrear responsabilidades

---

### **15. TABLA: materiales_trabajo**
Materias primas utilizadas en cada trabajo

```sql
CREATE TABLE materiales_trabajo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trabajo_id INT NOT NULL,
    materia_prima_id INT NOT NULL,
    cantidad_utilizada DECIMAL(10,3) NOT NULL,
    fecha_uso DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_trabajo (trabajo_id),
    
    FOREIGN KEY (trabajo_id) REFERENCES trabajos_taller(id) ON DELETE CASCADE,
    FOREIGN KEY (materia_prima_id) REFERENCES materias_primas(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### **16. TABLA: ventas**
Registro de todas las ventas

```sql
CREATE TABLE ventas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_venta VARCHAR(50) NOT NULL UNIQUE, -- V-SUC1-2026-0001
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    
    cliente_id INT NULL, -- NULL si es venta sin cliente registrado
    usuario_id INT NOT NULL, -- Vendedor
    sucursal_id INT NOT NULL,
    
    -- Montos
    subtotal DECIMAL(10,2) NOT NULL,
    descuento DECIMAL(10,2) DEFAULT 0, -- Monto fijo
    total DECIMAL(10,2) AS (subtotal - descuento) STORED,
    
    -- Tipo de venta
    tipo_venta ENUM('normal', 'credito', 'apartado') DEFAULT 'normal',
    
    -- Estado
    estado ENUM('completada', 'apartada', 'anulada') DEFAULT 'completada',
    motivo_anulacion TEXT NULL,
    
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_numero (numero_venta),
    INDEX idx_fecha (fecha),
    INDEX idx_cliente (cliente_id),
    INDEX idx_vendedor (usuario_id),
    INDEX idx_sucursal (sucursal_id),
    INDEX idx_estado (estado),
    INDEX idx_tipo (tipo_venta),
    
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT,
    FOREIGN KEY (sucursal_id) REFERENCES sucursales(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### **17. TABLA: detalle_ventas**
Productos vendidos en cada venta

```sql
CREATE TABLE detalle_ventas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    venta_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL, -- Precio al momento de la venta
    tipo_precio_aplicado ENUM('publico', 'mayorista', 'descuento', 'especial') NOT NULL,
    subtotal DECIMAL(10,2) AS (cantidad * precio_unitario) STORED,
    
    INDEX idx_venta (venta_id),
    INDEX idx_producto (producto_id),
    
    FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### **18. TABLA: formas_pago_venta**
**CRÍTICO** - Múltiples formas de pago por venta

```sql
CREATE TABLE formas_pago_venta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    venta_id INT NOT NULL,
    forma_pago ENUM('efectivo', 'tarjeta_debito', 'tarjeta_credito', 'transferencia', 'cheque') NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    referencia VARCHAR(100) NULL, -- Número de cheque, referencia de transferencia, etc.
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_venta (venta_id),
    INDEX idx_forma_pago (forma_pago),
    
    FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Lógica:** Una venta puede tener múltiples registros (efectivo + tarjeta + transferencia)

---

### **19. TABLA: creditos_clientes**
Créditos semanales a clientes

```sql
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
```

---

### **20. TABLA: abonos_creditos**
Registro de pagos a créditos

```sql
CREATE TABLE abonos_creditos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    credito_id INT NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    forma_pago ENUM('efectivo', 'tarjeta_debito', 'tarjeta_credito', 'transferencia', 'cheque') NOT NULL,
    fecha_abono DATE NOT NULL,
    saldo_anterior DECIMAL(10,2) NOT NULL,
    saldo_nuevo DECIMAL(10,2) NOT NULL,
    usuario_id INT NOT NULL, -- Quien registró el abono
    caja_id INT NULL, -- Relacionado con el movimiento de caja
    observaciones TEXT NULL,
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_credito (credito_id),
    INDEX idx_fecha (fecha_abono),
    
    FOREIGN KEY (credito_id) REFERENCES creditos_clientes(id) ON DELETE RESTRICT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### **21. TABLA: cajas**
Control de cajas diarias por sucursal

```sql
CREATE TABLE cajas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL, -- Cajero responsable
    sucursal_id INT NOT NULL,
    fecha_apertura DATETIME NOT NULL,
    fecha_cierre DATETIME NULL,
    monto_inicial DECIMAL(10,2) NOT NULL,
    monto_esperado DECIMAL(10,2) NULL, -- Calculado al cerrar
    monto_real DECIMAL(10,2) NULL, -- Contado físicamente
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
```

---

### **22. TABLA: movimientos_caja**
Registro de todos los movimientos de dinero

```sql
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
    usuario_id INT NOT NULL, -- Quien registró el movimiento
    referencia_tipo VARCHAR(50) NULL, -- 'venta', 'trabajo', 'credito', etc.
    referencia_id INT NULL, -- ID de la venta, trabajo, etc.
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_caja (caja_id),
    INDEX idx_tipo (tipo_movimiento),
    INDEX idx_categoria (categoria),
    INDEX idx_fecha (fecha_hora),
    INDEX idx_referencia (referencia_tipo, referencia_id),
    
    FOREIGN KEY (caja_id) REFERENCES cajas(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**10 tipos de movimientos** solicitados por el cliente

---

### **23. TABLA: facturas**
Facturación (simple y electrónica futura)

```sql
CREATE TABLE facturas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    venta_id INT NOT NULL,
    numero_factura VARCHAR(50) NOT NULL UNIQUE,
    serie VARCHAR(10) NULL,
    nit VARCHAR(20) NULL,
    nombre VARCHAR(200) NULL,
    direccion TEXT NULL,
    
    -- Para facturación electrónica (futuro)
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
```

---

### **24. TABLA: audit_log**
Auditoría completa de operaciones (cliente lo solicitó)

```sql
CREATE TABLE audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    accion VARCHAR(100) NOT NULL, -- 'login', 'crear_venta', 'editar_producto', etc.
    tabla_afectada VARCHAR(50) NULL,
    registro_id INT NULL,
    detalles TEXT NULL, -- JSON con información adicional
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_usuario (usuario_id),
    INDEX idx_accion (accion),
    INDEX idx_tabla (tabla_afectada),
    INDEX idx_fecha (fecha_hora),
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### **25. TABLA: configuracion_sistema**
Configuraciones globales del sistema

```sql
CREATE TABLE configuracion_sistema (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT NOT NULL,
    tipo ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    descripcion TEXT NULL,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_clave (clave)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Ejemplos de configuraciones:**
- logo_negocio
- mensaje_ticket
- email_notificaciones
- stock_minimo_default
- dias_alerta_entrega

---

## 📊 RESUMEN DEL DISEÑO

**Total de tablas:** 25

**Tablas por módulo:**
- **Usuarios y Seguridad:** 3 (usuarios, audit_log, configuracion_sistema)
- **Estructura:** 2 (sucursales, categorias)
- **Productos e Inventario:** 7 (productos, precios_producto, inventario, movimientos_inventario, transferencias_inventario, detalle_transferencias_inventario, materias_primas)
- **Proveedores:** 1 (proveedores)
- **Taller:** 3 (trabajos_taller, transferencias_trabajo, materiales_trabajo)
- **Clientes:** 3 (clientes, creditos_clientes, abonos_creditos)
- **Ventas:** 4 (ventas, detalle_ventas, formas_pago_venta, facturas)
- **Caja:** 2 (cajas, movimientos_caja)

**Características especiales implementadas:**
✅ Múltiples precios por producto (4 tipos)  
✅ Productos por peso (oro/plata)  
✅ Inventario multi-sucursal (compartido/separado)  
✅ Transferencias entre sucursales  
✅ Sistema completo de taller con transferencias entre empleados  
✅ Historial inmutable de transferencias de trabajos  
✅ Múltiples formas de pago por venta  
✅ Créditos semanales  
✅ Control de caja diario  
✅ Auditoría completa  
✅ 10 tipos de movimientos de caja  
✅ Facturación (simple y preparada para electrónica)  

