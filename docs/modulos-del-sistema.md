# MÓDULOS DEL SISTEMA
## Sistema de Gestión - Joyería Torre Fuerte

**Fecha:** 20 de enero de 2026  
**Versión:** 1.0

---

## RESUMEN EJECUTIVO

El sistema se divide en **8 módulos principales** que cubren todas las necesidades operativas de Joyería Torre Fuerte. Cada módulo tiene su propia complejidad, prioridad y tiempo estimado de desarrollo.

**Total de módulos:** 8  
**Módulos críticos:** 3 (Taller, POS, Inventario)  
**Tiempo estimado total:** 3-4 semanas

---

## 1. MÓDULO AUTENTICACIÓN Y USUARIOS

### Información General
- **Prioridad:** 🔴 CRÍTICA
- **Complejidad:** Baja
- **Tiempo estimado:** 1 día
- **Dependencias:** Ninguna (se desarrolla primero)

### Objetivo
Controlar el acceso al sistema mediante autenticación segura y gestión de roles y permisos.

### Componentes

#### 1.1 Sistema de Login
**Archivos:**
- `login.php` - Formulario de login
- `includes/auth.php` - Funciones de autenticación

**Funcionalidades:**
- Formulario de login (email + password)
- Validación de credenciales con BD
- Verificación de password con `password_verify()`
- Creación de sesión segura
- Regeneración de ID de sesión
- Registro de acceso en audit_log
- Redirección a dashboard
- Mensajes de error claros
- Protección contra fuerza bruta (opcional v1)

#### 1.2 Sistema de Logout
**Archivos:**
- `logout.php`

**Funcionalidades:**
- Destrucción de sesión
- Limpieza de cookies
- Redirección a login
- Registro de cierre de sesión

#### 1.3 Middleware de Protección
**Archivos:**
- `includes/auth.php`

**Funcionalidades:**
- Función `verificarSesion()` - verifica si usuario está logueado
- Función `verificarRol($roles)` - verifica si tiene permiso
- Timeout de sesión (30 minutos de inactividad)
- Renovación automática de sesión con actividad
- Redirección a login si no autorizado

#### 1.4 Gestión de Usuarios
**Archivos:**
- `modules/usuarios/lista.php`
- `modules/usuarios/agregar.php`
- `modules/usuarios/editar.php`
- `models/usuario.php`

**Funcionalidades:**
- Crear usuario nuevo
- Editar usuario existente
- Desactivar/activar usuario (no eliminar)
- Asignar rol
- Asignar sucursal
- Cambiar contraseña (con confirmación)
- Listar usuarios con filtros
- Solo rol ADMINISTRADOR puede acceder

### Roles del Sistema

**6 roles definidos:**

1. **ADMINISTRADOR**
   - Todos los permisos
   - Gestión de usuarios
   - Configuración del sistema

2. **DUEÑO**
   - Todos los módulos operativos
   - Todos los reportes
   - No gestión de usuarios/configuración

3. **VENDEDOR**
   - Módulo ventas (POS)
   - Ver inventario (lectura)
   - Gestionar clientes
   - Ver sus propias ventas

4. **CAJERO**
   - Módulo caja completo
   - No acceso a ventas

5. **ORFEBRE**
   - Módulo taller completo
   - Solo sus trabajos

6. **PUBLICIDAD**
   - Reportes (solo lectura)
   - Inventario (lectura)
   - Clientes (lectura)

### Datos que Maneja

**Tabla: usuarios**
```sql
- id (PK)
- nombre
- email (unique)
- password (hash bcrypt)
- rol (enum)
- sucursal_id (FK nullable)
- activo (boolean)
- fecha_creacion
- fecha_actualizacion
```

**Tabla: audit_log**
```sql
- id (PK)
- usuario_id (FK)
- accion (login, logout, crear, editar, etc.)
- tabla_afectada
- registro_id
- ip_address
- user_agent
- fecha_hora
```

### Seguridad Implementada
- ✅ Passwords con bcrypt (password_hash)
- ✅ Prepared statements
- ✅ Validación de roles en cada acción
- ✅ Sesiones con timeout
- ✅ HTTPS forzado
- ✅ CSRF tokens en formularios
- ✅ Auditoría completa

---

## 2. MÓDULO INVENTARIO

### Información General
- **Prioridad:** 🔴 CRÍTICA
- **Complejidad:** Media-Alta
- **Tiempo estimado:** 3-4 días
- **Dependencias:** Autenticación

### Objetivo
Gestionar el inventario de productos en ambas sucursales, con soporte para múltiples precios, productos por peso, códigos de barras y alertas de stock.

### Componentes

#### 2.1 Gestión de Productos
**Archivos:**
- `modules/inventario/lista.php`
- `modules/inventario/agregar.php`
- `modules/inventario/editar.php`
- `modules/inventario/ver.php`
- `models/producto.php`

**Funcionalidades:**
- **CRUD completo:**
  - Crear producto con todos sus datos
  - Editar producto
  - Eliminar (soft delete - marcar inactivo)
  - Ver detalle completo
  
- **Información del producto:**
  - Código (único, alfanumérico)
  - Código de barras (opcional)
  - Nombre
  - Descripción
  - Categoría
  - Proveedor
  - Imagen (upload)
  
- **Sistema de precios (4 tipos):**
  - Precio público
  - Precio mayorista
  - Precio con descuento
  - Precio descuento especial
  
- **Productos por peso:**
  - Marcar como "producto por peso"
  - Precio por gramo (oro/plata)
  - Peso en gramos
  
- **Múltiples presentaciones:**
  - Variaciones por estilo
  - Variaciones por largo
  - Variaciones por peso
  
- **Búsqueda y filtros:**
  - Búsqueda por código
  - Búsqueda por nombre
  - Filtro por categoría
  - Filtro por proveedor
  - Filtro por activo/inactivo
  - Ordenamiento

#### 2.2 Gestión de Categorías
**Archivos:**
- `modules/inventario/categorias.php`
- `models/categoria.php`

**Funcionalidades:**
- CRUD de categorías
- Clasificación por:
  - Tipo de producto
  - Material (oro, plata, otro)
  - Peso
- Activo/Inactivo

#### 2.3 Control de Stock por Sucursal
**Archivos:**
- `modules/inventario/stock.php`
- `models/inventario.php`

**Funcionalidades:**
- **Visualización:**
  - Stock por producto y sucursal
  - Inventario combinado (algunos compartidos, otros separados)
  - Productos con stock bajo (< 5 unidades)
  - Alertas visuales (badge rojo)
  
- **Ajustes de inventario:**
  - Ajuste manual con justificación
  - Ingreso por compra
  - Salida por venta (automático)
  - Salida por merma/robo
  
- **Configuración:**
  - Stock mínimo por producto
  - Productos compartidos entre sucursales
  - Productos exclusivos de sucursal

#### 2.4 Transferencias entre Sucursales
**Archivos:**
- `modules/inventario/transferencias.php`
- `modules/inventario/nueva_transferencia.php`
- `models/inventario.php`

**Funcionalidades:**
- Crear transferencia
- Seleccionar productos y cantidades
- Sucursal origen y destino
- Validar stock disponible
- Registrar quién autoriza
- Actualizar inventarios automáticamente
- Historial de transferencias
- Imprimir comprobante de transferencia

#### 2.5 Materias Primas (Taller)
**Archivos:**
- `modules/inventario/materias_primas.php`
- `models/materia_prima.php`

**Funcionalidades:**
- CRUD de materias primas
- Control de stock de materias
- Asignación a trabajos de taller
- Alertas de stock bajo

### Datos que Maneja

**Tabla: productos**
```sql
- id (PK)
- codigo (unique)
- codigo_barras
- nombre
- descripcion
- categoria_id (FK)
- proveedor_id (FK)
- imagen (ruta)
- es_por_peso (boolean)
- activo (boolean)
- fecha_creacion
- fecha_actualizacion
```

**Tabla: precios_producto**
```sql
- id (PK)
- producto_id (FK)
- tipo_precio (público, mayorista, descuento, especial)
- precio (decimal 10,2)
- activo (boolean)
```

**Tabla: categorias**
```sql
- id (PK)
- nombre
- descripcion
- tipo_clasificacion (tipo, material, peso)
- activo (boolean)
```

**Tabla: inventario**
```sql
- id (PK)
- producto_id (FK)
- sucursal_id (FK)
- cantidad (int)
- stock_minimo (int)
- es_compartido (boolean)
- fecha_actualizacion
```

**Tabla: movimientos_inventario**
```sql
- id (PK)
- producto_id (FK)
- sucursal_id (FK)
- tipo_movimiento (ingreso, salida, ajuste, transferencia)
- cantidad
- cantidad_anterior
- cantidad_nueva
- motivo
- usuario_id (FK)
- referencia_id (venta_id, transferencia_id, etc.)
- fecha_hora
```

**Tabla: transferencias_inventario**
```sql
- id (PK)
- sucursal_origen_id (FK)
- sucursal_destino_id (FK)
- usuario_id (FK)
- estado (pendiente, completada, cancelada)
- observaciones
- fecha_creacion
- fecha_completado
```

**Tabla: materias_primas**
```sql
- id (PK)
- nombre
- tipo (oro, plata, piedra, otro)
- unidad_medida (gramos, piezas)
- cantidad_disponible
- stock_minimo
- activo (boolean)
```

### Reportes de Inventario
- Inventario actual por sucursal
- Productos con stock bajo
- Productos sin movimiento (últimos 30 días)
- Valorización de inventario
- Historial de transferencias
- Movimientos de inventario por período

### Alertas Automáticas
- 🔴 Stock bajo (cantidad < stock_mínimo)
- 🟡 Productos sin movimiento (> 30 días)
- 🔵 Transferencias pendientes

---

## 3. MÓDULO TALLER ⭐⭐⭐

### Información General
- **Prioridad:** 🔴 CRÍTICA (La más importante para el cliente)
- **Complejidad:** Alta
- **Tiempo estimado:** 4-5 días
- **Dependencias:** Autenticación, Módulo Caja (para ingresos)

### Objetivo
**Resolver el problema crítico del cliente:** Evitar pérdida de trabajos y siempre saber dónde está cada trabajo y quién lo tiene.

### Componentes

#### 3.1 Recepción de Trabajos
**Archivos:**
- `modules/taller/recibir_trabajo.php`
- `models/trabajo_taller.php`

**Funcionalidades:**
- Formulario completo de recepción
- Generación automática de código de trabajo
- Información a capturar:
  - **Cliente:**
    - Nombre
    - Teléfono
  - **Pieza:**
    - Material (oro/plata)
    - Peso en gramos
    - Largo (si aplica)
    - Con/sin piedra
    - Estilo
    - Descripción detallada
  - **Trabajo:**
    - Tipo (reparación, ajuste, grabado, diseño, limpieza, engaste, fabricación)
    - Descripción del trabajo a realizar
    - Precio total acordado
    - Anticipo recibido
    - Saldo calculado automáticamente
  - **Fechas:**
    - Fecha recepción (automática)
    - Fecha entrega prometida
  - **Asignación:**
    - Empleado que recibe
    - Empleado asignado inicialmente
- Estados: Recibido
- Imprimir comprobante para cliente

#### 3.2 Lista y Seguimiento de Trabajos
**Archivos:**
- `modules/taller/lista_trabajos.php`
- `modules/taller/detalle_trabajo.php`
- `models/trabajo_taller.php`

**Funcionalidades:**
- **Vista de lista con filtros:**
  - Por estado (recibido, en proceso, completado, entregado, cancelado)
  - Por empleado actual
  - Por fecha de entrega (próximos 7 días, vencidos)
  - Por cliente
  - Búsqueda por código o cliente
  
- **Información visible:**
  - Código de trabajo
  - Cliente
  - Tipo de trabajo
  - Empleado actual
  - Estado
  - Fecha de entrega
  - Saldo pendiente
  - Días para entrega (o días vencidos)
  
- **Acciones rápidas:**
  - Ver detalle
  - Transferir
  - Actualizar estado
  - Entregar
  
- **Detalle completo:**
  - Toda la información del trabajo
  - Historial de transferencias
  - Línea de tiempo visual
  - Empleado actual marcado

#### 3.3 Transferencias entre Empleados (CRÍTICO)
**Archivos:**
- `modules/taller/transferir_trabajo.php`
- `models/trabajo_taller.php`

**Funcionalidades:**
- Seleccionar trabajo a transferir
- Ver empleado actual
- Seleccionar empleado destino
- Agregar nota/motivo (opcional)
- Confirmar transferencia
- Registrar automáticamente:
  - Empleado origen (quien entrega)
  - Empleado destino (quien recibe)
  - Fecha y hora exacta
  - Estado del trabajo en ese momento
  - Nota/motivo
- Actualizar empleado_actual del trabajo
- Notificar en dashboard al empleado destino (opcional)
- **Historial completo e inmutable**

#### 3.4 Actualización de Estados
**Archivos:**
- `modules/taller/actualizar_estado.php`

**Funcionalidades:**
- Cambiar estado del trabajo:
  - Recibido → En Proceso
  - En Proceso → Completado
  - Completado → Entregado
  - Cualquiera → Cancelado
- Agregar nota del cambio
- Registrar quién y cuándo
- Solo empleado asignado puede actualizar

#### 3.5 Entrega de Trabajos
**Archivos:**
- `modules/taller/entregar_trabajo.php`
- `models/trabajo_taller.php`

**Funcionalidades:**
- Verificar que trabajo esté "Completado"
- Mostrar información de cobro:
  - Precio total
  - Anticipo ya pagado
  - **Saldo pendiente**
- Registrar pago del saldo:
  - Forma de pago
  - Monto
- Registrar ingreso en caja automáticamente
- Actualizar estado a "Entregado"
- Registrar fecha de entrega real
- Registrar quién entrega
- Generar comprobante de entrega
- Imprimir o enviar por email

#### 3.6 Historial de Trabajo
**Archivos:**
- `modules/taller/historial_trabajo.php`

**Funcionalidades:**
- Línea de tiempo completa del trabajo:
  - Recepción (quién, cuándo)
  - Cada transferencia (de quién a quién, cuándo)
  - Cambios de estado (quién, cuándo)
  - Entrega (quién, cuándo)
- Vista cronológica visual
- Exportar historial a PDF

### Datos que Maneja

**Tabla: trabajos_taller**
```sql
- id (PK)
- codigo (unique, auto-generado)
- cliente_nombre
- cliente_telefono
- material (oro, plata)
- peso_gramos (decimal)
- largo_cm (decimal, nullable)
- con_piedra (boolean)
- estilo
- descripcion_pieza (text)
- tipo_trabajo (enum: reparación, ajuste, grabado, etc.)
- descripcion_trabajo (text)
- precio_total (decimal 10,2)
- anticipo (decimal 10,2)
- saldo (decimal 10,2)
- fecha_recepcion (datetime)
- fecha_entrega_prometida (date)
- fecha_entrega_real (datetime, nullable)
- empleado_recibe_id (FK usuarios)
- empleado_actual_id (FK usuarios)
- empleado_entrega_id (FK usuarios, nullable)
- estado (enum: recibido, en_proceso, completado, entregado, cancelado)
- observaciones (text)
- fecha_creacion
- fecha_actualizacion
```

**Tabla: transferencias_trabajo**
```sql
- id (PK)
- trabajo_id (FK)
- empleado_origen_id (FK usuarios)
- empleado_destino_id (FK usuarios)
- fecha_transferencia (datetime)
- estado_trabajo_momento (varchar)
- nota (text, nullable)
- usuario_registra_id (FK usuarios)
```

**Tabla: estados_trabajo (historial)**
```sql
- id (PK)
- trabajo_id (FK)
- estado_anterior (varchar)
- estado_nuevo (varchar)
- usuario_id (FK)
- nota (text)
- fecha_cambio (datetime)
```

### Reportes de Taller
- Trabajos pendientes (por entregar)
- Trabajos completados en período
- Trabajos por empleado
- Trabajos vencidos (pasada fecha de entrega)
- Ingresos por taller en período
- Tiempo promedio de completado por tipo
- Productividad por empleado

### Alertas Críticas
- 🔴 Trabajos vencidos (pasada fecha de entrega)
- 🟡 Trabajos próximos a vencer (3 días antes)
- 🔵 Trabajos en proceso más de 15 días
- ⚠️ Trabajos sin movimiento (sin transferencias) más de 7 días

### Dashboard de Taller
- Total de trabajos activos
- Trabajos por estado (gráfica)
- Trabajos próximos a entregar
- Trabajos vencidos
- Trabajos por empleado
- Ingresos del mes

---

## 4. MÓDULO PUNTO DE VENTA (POS)

### Información General
- **Prioridad:** 🔴 CRÍTICA
- **Complejidad:** Alta
- **Tiempo estimado:** 4-5 días
- **Dependencias:** Autenticación, Inventario, Clientes, Caja

### Objetivo
Procesar ventas de manera rápida y eficiente, con soporte para múltiples formas de pago, créditos, descuentos y actualización automática de inventario.

### Componentes

#### 4.1 Pantalla Principal de Venta
**Archivos:**
- `modules/ventas/nueva_venta.php` (POS)
- `models/venta.php`

**Funcionalidades:**
- **Búsqueda de productos:**
  - Por código de barras (scanner automático)
  - Por nombre (autocompletado en tiempo real)
  - Por código manual
  - Búsqueda con AJAX
  
- **Carrito de compra:**
  - Agregar producto al carrito (JavaScript)
  - Mostrar imagen, nombre, precio
  - Ajustar cantidad con + / -
  - Eliminar producto del carrito
  - Precio según tipo de cliente (público/mayorista automático)
  - Verificar stock disponible en tiempo real
  - Subtotal por producto
  - Subtotal general
  - Descuento (monto fijo en quetzales)
  - **Total calculado automáticamente**
  
- **Selección de cliente:**
  - Búsqueda de cliente (autocompletado)
  - Crear cliente rápido sin salir del POS
  - Venta sin cliente (público general)
  - Si cliente mayorista → aplicar precio automáticamente
  - Mostrar info del cliente (nombre, tipo, crédito disponible)
  
- **Opciones de venta:**
  - Venta normal
  - Venta a crédito
  - Apartado de mercadería

#### 4.2 Sistema de Múltiples Formas de Pago
**Archivos:**
- `modules/ventas/nueva_venta.php`
- `api/ventas/procesar_venta.php`

**Funcionalidades:**
- Seleccionar forma(s) de pago:
  - Efectivo
  - Tarjeta de débito
  - Tarjeta de crédito
  - Transferencia bancaria
  - Cheque
  
- **Una sola forma de pago:**
  - Ingresar monto (auto-completar con total)
  - Si efectivo: calcular cambio
  
- **Múltiples formas de pago:**
  - Agregar forma de pago 1 (ej: efectivo Q100)
  - Agregar forma de pago 2 (ej: tarjeta Q50)
  - Sistema muestra cuánto falta por pagar
  - Validar que suma = total exacto
  - No permitir procesar si no cuadra
  
- **Visual:**
  - Tabla mostrando cada forma de pago
  - Total pagado actualizado
  - Monto faltante
  - Botón eliminar forma de pago

#### 4.3 Ventas a Crédito
**Archivos:**
- `modules/ventas/nueva_venta.php`
- `models/credito.php`

**Funcionalidades:**
- Marcar venta como "A Crédito"
- Cliente obligatorio
- Definir número de cuotas semanales
- Sistema calcula cuota automáticamente
- Mostrar plan de pagos
- Registrar venta normalmente
- Crear registro de crédito:
  - Monto total
  - Saldo pendiente (= total)
  - Cuota semanal
  - Fecha inicio
  - Próximo pago (+7 días)
- Cliente recibe mercadería inmediatamente
- Inventario se descuenta igual que venta normal

#### 4.4 Apartados de Mercadería
**Archivos:**
- `modules/ventas/apartados.php`
- `models/venta.php`

**Funcionalidades:**
- Marcar venta como "Apartado"
- Registrar anticipo
- Calcular saldo pendiente
- Productos se reservan (descuentan de inventario disponible)
- Estado: "Apartado"
- Cuando cliente paga saldo:
  - Cambiar estado a "Completada"
  - Registrar pago
  - Cliente se lleva mercadería
- Si no paga en X días:
  - Opción de cancelar apartado
  - Regresar productos a inventario
  - Devolver anticipo o no (política del negocio)

#### 4.5 Procesamiento de Venta
**Archivos:**
- `api/ventas/procesar_venta.php`
- `models/venta.php`

**Funcionalidades (con transacción):**
1. Validar que haya stock suficiente de todos los productos
2. Iniciar transacción SQL
3. Insertar venta principal
4. Insertar detalle de venta (cada producto)
5. Insertar formas de pago (cada una)
6. Si es crédito: insertar registro de crédito
7. Actualizar inventario (descontar de sucursal)
8. Registrar movimiento en caja (ingreso automático)
9. Si todo OK: commit
10. Si algo falla: rollback completo
11. Generar número de venta
12. Retornar venta_id

#### 4.6 Generación de Tickets/Facturas
**Archivos:**
- `modules/ventas/generar_ticket.php` (PDF)
- `modules/ventas/generar_factura.php` (PDF)
- Librería: FPDF

**Funcionalidades:**
- **Ticket básico:**
  - Logo Joyería Torre Fuerte
  - Nombre del negocio
  - Dirección y teléfono
  - Número de ticket
  - Fecha y hora
  - Vendedor
  - Cliente (si hay)
  - Detalle de productos (código, nombre, cantidad, precio, subtotal)
  - Subtotal
  - Descuento
  - **Total**
  - Formas de pago
  - Cambio (si aplica)
  - Mensaje: "Gracias por su preferencia y bendiciones"
  
- **Factura simple:**
  - Igual que ticket pero con formato de factura
  - Campo para NIT
  - Numeración consecutiva
  
- **Facturación electrónica SAT:**
  - Generar XML con formato SAT
  - Firmar electrónicamente
  - Enviar a SAT para certificación
  - Recibir UUID
  - Guardar XML certificado
  - Generar PDF con código QR
  - Enviar por email a cliente
  - Registrar en libro de ventas

#### 4.7 Historial de Ventas
**Archivos:**
- `modules/ventas/historial.php`
- `modules/ventas/detalle_venta.php`

**Funcionalidades:**
- Lista de ventas con filtros:
  - Por fecha (hoy, ayer, semana, mes, rango)
  - Por vendedor
  - Por cliente
  - Por estado (completada, apartada, anulada)
  - Por forma de pago
  
- Información visible:
  - Número de venta
  - Fecha y hora
  - Cliente
  - Vendedor
  - Total
  - Estado
  - Acciones (ver detalle, reimprimir, anular)
  
- Detalle de venta:
  - Toda la información de la venta
  - Productos vendidos
  - Formas de pago utilizadas
  - Si es crédito: estado del crédito
  - Opción reimprimir ticket
  - Opción anular (si es del día y tiene permisos)

#### 4.8 Anulación de Ventas
**Archivos:**
- `modules/ventas/anular_venta.php`

**Funcionalidades:**
- Solo ventas del mismo día
- Solo con rol Administrador o Dueño
- Motivo de anulación obligatorio
- Con transacción:
  - Marcar venta como anulada
  - Reversar inventario (regresar cantidades)
  - Reversar movimiento de caja (egreso)
  - Si tenía crédito: cancelar crédito
  - Registrar en audit_log
- No se puede eliminar, solo anular
- Queda en historial marcada como "Anulada"

### Datos que Maneja

**Tabla: ventas**
```sql
- id (PK)
- numero_venta (unique, consecutivo)
- fecha (date)
- hora (time)
- cliente_id (FK, nullable)
- usuario_id (FK - vendedor)
- sucursal_id (FK)
- subtotal (decimal 10,2)
- descuento (decimal 10,2)
- total (decimal 10,2)
- tipo_venta (normal, credito, apartado)
- estado (completada, apartada, anulada)
- motivo_anulacion (text, nullable)
- fecha_creacion
```

**Tabla: detalle_ventas**
```sql
- id (PK)
- venta_id (FK)
- producto_id (FK)
- cantidad (int)
- precio_unitario (decimal 10,2)
- subtotal (decimal 10,2)
```

**Tabla: formas_pago_venta**
```sql
- id (PK)
- venta_id (FK)
- forma_pago (enum: efectivo, tarjeta_debito, tarjeta_credito, transferencia, cheque)
- monto (decimal 10,2)
- referencia (varchar, nullable - para cheques, transferencias)
```

**Tabla: facturas**
```sql
- id (PK)
- venta_id (FK)
- numero_factura (unique)
- serie
- nit (varchar)
- nombre (varchar)
- direccion (varchar)
- uuid_sat (varchar, nullable - para factura electrónica)
- xml_ruta (varchar, nullable)
- fecha_certificacion (datetime, nullable)
- tipo (simple, electronica)
- estado (emitida, anulada)
```

### Reportes de Ventas
- Ventas diarias por vendedor
- Ventas por período
- Ventas por forma de pago
- Productos más vendidos
- Ticket promedio
- Ventas anuladas

---

## 5. MÓDULO CAJA

### Información General
- **Prioridad:** 🔴 CRÍTICA
- **Complejidad:** Media
- **Tiempo estimado:** 2-3 días
- **Dependencias:** Autenticación

### Objetivo
Controlar el flujo de efectivo diario mediante apertura y cierre de caja con registro detallado de todos los movimientos de dinero.

### Componentes

#### 5.1 Apertura de Caja
**Archivos:**
- `modules/caja/apertura.php`
- `models/caja.php`

**Funcionalidades:**
- Verificar que no haya caja abierta en la sucursal
- Contar efectivo inicial
- Registrar monto inicial
- Asignar a usuario y sucursal
- Fecha y hora automática
- Estado: "Abierta"
- A partir de este momento se pueden registrar movimientos

#### 5.2 Registro de Movimientos
**Archivos:**
- `modules/caja/movimientos.php`
- `models/caja.php`

**Tipos de movimientos (10):**

**INGRESOS:**
1. Ventas (automático al procesar venta)
2. Ingresos de reparaciones/taller
3. Anticipo de trabajos
4. Abonos a créditos de clientes
5. Anticipo de mercadería apartada

**EGRESOS:**
6. Gastos generales (con descripción)
7. Pagos a proveedores
8. Compras de material
9. Pago de alquileres
10. Pago de salarios

**Funcionalidades:**
- Formulario de registro:
  - Tipo de movimiento
  - Concepto/descripción
  - Monto
  - Categoría (ingreso/egreso)
- Validar que haya caja abierta
- Registrar usuario que hace el movimiento
- Fecha y hora automática
- Confirmación visual
- Ver movimientos del día en tabla

#### 5.3 Consulta de Movimientos
**Archivos:**
- `modules/caja/movimientos.php`

**Funcionalidades:**
- Ver todos los movimientos de la caja actual
- Filtrar por tipo
- Buscar por concepto
- Ordenar por fecha/hora
- Ver usuario que registró
- Totales por tipo
- Total de ingresos
- Total de egresos
- Saldo calculado

#### 5.4 Cierre de Caja
**Archivos:**
- `modules/caja/cierre.php`
- `models/caja.php`

**Funcionalidades:**
- Verificar que haya caja abierta
- Sistema calcula automáticamente:
  - Monto inicial
  - Total de ingresos
  - Total de egresos
  - **Monto esperado** = inicial + ingresos - egresos
  
- Cajero cuenta efectivo real
- Ingresa monto real contado
- Sistema calcula diferencia:
  - Faltante (si real < esperado)
  - Sobrante (si real > esperado)
  
- Si hay diferencia:
  - Solicitar observación/explicación
  - Marcar en reporte
  
- Generar reporte de cierre con:
  - Resumen ejecutivo
  - Movimientos del día por tipo
  - Totales
  - Diferencia
  
- Cerrar caja (cambiar estado a "Cerrada")
- No permite más movimientos
- Registrar fecha y hora de cierre

#### 5.5 Historial de Cierres
**Archivos:**
- `modules/caja/historial.php`

**Funcionalidades:**
- Ver todos los cierres anteriores
- Filtrar por:
  - Fecha
  - Sucursal
  - Usuario
  - Con diferencia / sin diferencia
- Ver reporte de cada cierre
- Comparativas entre días
- Gráfica de tendencias
- Exportar a Excel/PDF

### Datos que Maneja

**Tabla: cajas**
```sql
- id (PK)
- usuario_id (FK)
- sucursal_id (FK)
- fecha_apertura (datetime)
- fecha_cierre (datetime, nullable)
- monto_inicial (decimal 10,2)
- monto_esperado (decimal 10,2, nullable - al cerrar)
- monto_real (decimal 10,2, nullable - al cerrar)
- diferencia (decimal 10,2, nullable - al cerrar)
- observaciones_cierre (text, nullable)
- estado (abierta, cerrada)
```

**Tabla: movimientos_caja**
```sql
- id (PK)
- caja_id (FK)
- tipo_movimiento (enum: ventas, reparaciones, anticipo_trabajo, abono_credito, anticipo_apartado, gasto, pago_proveedor, compra_material, alquiler, salario)
- categoria (ingreso, egreso)
- concepto (text)
- monto (decimal 10,2)
- usuario_id (FK)
- referencia_id (nullable - venta_id, trabajo_id, etc.)
- fecha_hora (datetime)
```

### Reportes de Caja
- Cierre de caja diario
- Movimientos por tipo
- Ingresos vs egresos por período
- Diferencias de caja (faltantes/sobrantes)
- Comparativa entre cierres
- Usuario con más diferencias (auditoría)

### Validaciones Importantes
- ✅ Solo una caja abierta por sucursal a la vez
- ✅ No se puede abrir si ya hay una abierta
- ✅ No se pueden registrar movimientos sin caja abierta
- ✅ No se puede cerrar dos veces la misma caja
- ✅ Diferencias se marcan visualmente (rojo/verde)

---

## 6. MÓDULO CLIENTES

### Información General
- **Prioridad:** 🟡 IMPORTANTE
- **Complejidad:** Media
- **Tiempo estimado:** 2 días
- **Dependencias:** Autenticación, Ventas (para historial)

### Objetivo
Gestionar información de clientes, clasificarlos (público/mayorista), llevar historial de compras y administrar créditos semanales.

### Componentes

#### 6.1 Gestión de Clientes
**Archivos:**
- `modules/clientes/lista.php`
- `modules/clientes/agregar.php`
- `modules/clientes/editar.php`
- `modules/clientes/ficha_cliente.php`
- `models/cliente.php`

**Funcionalidades:**
- **CRUD completo:**
  - Crear cliente
  - Editar cliente
  - Eliminar (soft delete - desactivar)
  - Ver ficha completa
  
- **Información del cliente:**
  - Nombre completo
  - NIT (opcional)
  - Teléfono
  - Email (opcional)
  - Dirección
  - Tipo (Público / Mayorista)
  - Tipo de mercaderías que compra (oro/plata/ambas)
  - Activo/Inactivo
  
- **Clasificación automática:**
  - Si es mayorista:
    - Aplicar precio mayorista en ventas
    - Permitir créditos
    - Límite de crédito (opcional)
  
- **Búsqueda y filtros:**
  - Por nombre
  - Por teléfono
  - Por NIT
  - Por tipo (público/mayorista)
  - Solo activos / todos

#### 6.2 Historial de Compras
**Archivos:**
- `modules/clientes/ficha_cliente.php`

**Funcionalidades:**
- Ver todas las compras del cliente
- Información visible:
  - Fecha de compra
  - Productos comprados
  - Total de la compra
  - Vendedor
  - Estado (completada, anulada)
  
- **Estadísticas del cliente:**
  - Total comprado histórico
  - Número de compras
  - Ticket promedio
  - Última compra
  - Frecuencia de compra
  - Productos favoritos (más comprados)
  
- Filtrar historial por fechas
- Exportar historial a PDF

#### 6.3 Gestión de Créditos
**Archivos:**
- `modules/clientes/creditos.php`
- `modules/clientes/detalle_credito.php`
- `modules/clientes/registrar_abono.php`
- `models/credito.php`

**Funcionalidades:**
- **Ver créditos del cliente:**
  - Créditos activos
  - Créditos liquidados
  - Créditos vencidos
  
- **Información de cada crédito:**
  - Venta relacionada (número, fecha)
  - Monto total
  - Saldo pendiente
  - Cuota semanal
  - Fecha de inicio
  - Próximo pago (fecha)
  - Días de atraso (si aplica)
  - Estado (al día, atrasado, liquidado)
  
- **Registro de abono:**
  - Seleccionar crédito
  - Ingresar monto del abono
  - Seleccionar forma de pago
  - Validar que no sea mayor al saldo
  - Confirmar
  - Sistema:
    - Descuenta del saldo
    - Registra en historial de abonos
    - Recalcula próximo pago
    - Si saldo = 0, marca como "Liquidado"
    - Registra ingreso en caja
  
- **Historial de abonos:**
  - Fecha de cada abono
  - Monto
  - Forma de pago
  - Saldo después del abono
  - Usuario que registró
  
- **Alertas:**
  - Créditos con cuotas vencidas (rojo)
  - Próximo pago en 3 días (amarillo)

#### 6.4 Clientes Mayoristas
**Archivos:**
- `modules/clientes/mayoristas.php`

**Funcionalidades:**
- Lista de clientes mayoristas
- Configuración especial:
  - Precio mayorista asignado
  - Límite de crédito
  - Plazo de crédito permitido (semanas)
  - Descuentos especiales
- Estadísticas de mayoristas:
  - Total comprado
  - Crédito utilizado
  - Crédito disponible

### Datos que Maneja

**Tabla: clientes**
```sql
- id (PK)
- nombre
- nit (varchar, nullable)
- telefono
- email (varchar, nullable)
- direccion (text, nullable)
- tipo_cliente (publico, mayorista)
- tipo_mercaderias (oro, plata, ambas)
- limite_credito (decimal 10,2, nullable)
- activo (boolean)
- fecha_creacion
- fecha_actualizacion
```

**Tabla: creditos_clientes**
```sql
- id (PK)
- cliente_id (FK)
- venta_id (FK)
- monto_total (decimal 10,2)
- saldo_pendiente (decimal 10,2)
- cuota_semanal (decimal 10,2)
- fecha_inicio (date)
- fecha_proximo_pago (date)
- estado (activo, liquidado, vencido)
- fecha_liquidacion (date, nullable)
```

**Tabla: abonos_creditos**
```sql
- id (PK)
- credito_id (FK)
- monto (decimal 10,2)
- forma_pago (enum)
- fecha_abono (date)
- saldo_anterior (decimal 10,2)
- saldo_nuevo (decimal 10,2)
- usuario_id (FK)
- caja_id (FK, nullable)
- fecha_hora
```

### Reportes de Clientes
- Clientes más frecuentes
- Clientes mayoristas
- Total de créditos por cobrar
- Créditos vencidos
- Morosidad
- Abonos del período
- Ranking de clientes (por monto comprado)

---

## 7. MÓDULO REPORTES Y ESTADÍSTICAS

### Información General
- **Prioridad:** 🟡 IMPORTANTE
- **Complejidad:** Media
- **Tiempo estimado:** 3-4 días
- **Dependencias:** Todos los módulos anteriores

### Objetivo
Proporcionar información analítica para la toma de decisiones mediante reportes, gráficas y estadísticas.

### Componentes

#### 7.1 Dashboard Ejecutivo
**Archivos:**
- `dashboard.php`

**Funcionalidades:**
- **Cards de resumen:**
  - Ventas del día (tiempo real)
  - Ventas del mes
  - Total en inventario
  - Trabajos pendientes
  - Créditos por cobrar
  
- **Gráficas (Chart.js):**
  - Ventas últimos 7 días (línea)
  - Ventas por sucursal (barras)
  - Productos más vendidos (barras horizontales)
  - Estado de trabajos de taller (dona)
  
- **Alertas importantes:**
  - Productos con stock bajo
  - Trabajos próximos a entregar
  - Créditos vencidos
  - Diferencias de caja

#### 7.2 Reportes de Ventas
**Archivos:**
- `modules/reportes/ventas.php`
- `models/reporte.php`

**Funcionalidades:**
- **Filtros:**
  - Rango de fechas (hoy, ayer, semana, mes, año, personalizado)
  - Sucursal (todas, específica)
  - Vendedor (todos, específico)
  - Forma de pago
  
- **Información mostrada:**
  - Total de ventas (cantidad)
  - Monto total vendido
  - Ticket promedio
  - Descuentos otorgados
  - Ventas por día (tabla)
  - Gráfica de tendencia
  
- **Desglose:**
  - Por vendedor
  - Por sucursal
  - Por forma de pago
  - Por producto
  
- **Comparativas entre períodos:**
  - Ventas este mes vs mes anterior (%)
  - Ventas este año vs año anterior (%)
  - Mejor día de la semana
  - Mejor mes del año
  
- **Exportación:**
  - Excel con datos detallados
  - PDF con gráficas

#### 7.3 Reportes de Productos
**Archivos:**
- `modules/reportes/productos.php`

**Funcionalidades:**
- Productos más vendidos (top 10, 20, 50)
- Productos con menos movimiento
- Productos que nunca se han vendido
- Valorización de inventario
- Margen de ganancia por producto
- Exportar a Excel

#### 7.4 Reportes de Inventario
**Archivos:**
- `modules/reportes/inventario.php`

**Funcionalidades:**
- Inventario actual por sucursal
- Productos con stock bajo
- Existencias en bodegas
- Valorización total
- Movimientos de inventario en período
- Transferencias entre sucursales
- Exportar a Excel/PDF

#### 7.5 Reportes de Taller
**Archivos:**
- `modules/reportes/taller.php`

**Funcionalidades:**
- Trabajos pendientes
- Trabajos completados en período
- Trabajos por empleado
- Trabajos vencidos
- Ingresos por taller
- Tiempo promedio de completado
- Productividad por empleado
- Exportar a Excel/PDF

#### 7.6 Reportes de Cuentas por Cobrar
**Archivos:**
- `modules/reportes/cuentas_cobrar.php`

**Funcionalidades:**
- Total de créditos activos
- Créditos vencidos
- Créditos al día
- Por cliente
- Antigüedad de saldo
- Abonos del período
- Proyección de cobros
- Exportar a Excel/PDF

#### 7.7 Reportes de Ganancias
**Archivos:**
- `modules/reportes/ganancias.php`

**Funcionalidades:**
- Ganancias por período
- Margen de ganancia (%)
- Rentabilidad por sucursal
- Rentabilidad por vendedor
- Rentabilidad por producto
- Costo vs ingreso
- Exportar a Excel/PDF

### Datos que Maneja
- Consultas agregadas a todas las tablas
- Cálculos complejos
- Joins múltiples
- Agrupaciones

### Exportación
- **Excel:** PHPSpreadsheet
- **PDF:** FPDF con formato profesional
- **Gráficas:** Chart.js incluidas en PDF

---

## 8. MÓDULO PROVEEDORES

### Información General
- **Prioridad:** 🟢 DESEABLE (v1 básico)
- **Complejidad:** Baja
- **Tiempo estimado:** 1 día
- **Dependencias:** Autenticación

### Objetivo
Mantener catálogo de proveedores para referencia en compras.

### Componentes

#### 8.1 Gestión de Proveedores
**Archivos:**
- `modules/proveedores/lista.php`
- `modules/proveedores/agregar.php`
- `modules/proveedores/editar.php`
- `models/proveedor.php`

**Funcionalidades:**
- CRUD básico de proveedores
- Información:
  - Nombre del proveedor
  - Empresa
  - Contacto (persona)
  - Teléfono
  - Email
  - Dirección
  - Productos que suministra
  - Activo/Inactivo
  
- Búsqueda por nombre
- Filtro activo/inactivo

### Datos que Maneja

**Tabla: proveedores**
```sql
- id (PK)
- nombre
- empresa
- contacto
- telefono
- email (nullable)
- direccion (text, nullable)
- productos_suministra (text)
- activo (boolean)
- fecha_creacion
```

---

## RESUMEN DE COMPLEJIDADES

| Módulo | Prioridad | Complejidad | Tiempo | Archivos Aprox. |
|--------|-----------|-------------|--------|-----------------|
| 1. Autenticación | 🔴 Crítica | Baja | 1 día | 5-8 |
| 2. Inventario | 🔴 Crítica | Media-Alta | 3-4 días | 15-20 |
| 3. Taller | 🔴 Crítica | Alta | 4-5 días | 12-15 |
| 4. POS | 🔴 Crítica | Alta | 4-5 días | 10-12 |
| 5. Caja | 🔴 Crítica | Media | 2-3 días | 8-10 |
| 6. Clientes | 🟡 Importante | Media | 2 días | 10-12 |
| 7. Reportes | 🟡 Importante | Media | 3-4 días | 8-10 |
| 8. Proveedores | 🟢 Deseable | Baja | 1 día | 4-5 |

**Total estimado:** 20-27 días hábiles (3-4 semanas)

---

## INTEGRACIÓN ENTRE MÓDULOS

Los módulos están interconectados de la siguiente manera:

**Flujo de Venta Completo:**
1. Autenticación → Login del vendedor
2. Inventario → Buscar productos disponibles
3. Clientes → Seleccionar cliente (opcional)
4. POS → Procesar venta
5. Caja → Registrar ingreso automáticamente
6. Inventario → Actualizar stock automáticamente
7. Si es crédito → Clientes (crear registro de crédito)

**Flujo de Trabajo de Taller:**
1. Autenticación → Login del orfebre
2. Taller → Recibir trabajo
3. Caja → Registrar anticipo (si hay)
4. Taller → Transferir entre empleados (múltiples veces)
5. Taller → Marcar como completado
6. Taller → Entregar al cliente
7. Caja → Registrar cobro de saldo

**Flujo de Reportes:**
1. Autenticación → Login (Admin/Dueño)
2. Reportes → Seleccionar tipo de reporte
3. Consultar datos de:
   - Ventas
   - Inventario
   - Taller
   - Clientes
   - Caja
4. Generar PDF o Excel

---

═══════════════════════════════════════════════════════════
                 MÓDULOS DEL SISTEMA COMPLETO
═══════════════════════════════════════════════════════════
