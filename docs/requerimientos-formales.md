# REQUERIMIENTOS FORMALES DEL SISTEMA
## Sistema de Gestión - Joyería Torre Fuerte

**Fecha de elaboración:** 20 de enero de 2026  
**Versión:** 1.0  
**Cliente:** Joyería Torre Fuerte  
**Desarrollador:** [Tu nombre]  
**Estado:** Aprobado por cliente

---

## 1. OBJETIVO GENERAL

Desarrollar un sistema web integral de gestión para Joyería Torre Fuerte que permita:

- Controlar inventario multi-sucursal con productos por peso y múltiples precios
- Gestionar trabajos de taller con seguimiento completo entre empleados
- Realizar ventas con múltiples formas de pago y créditos semanales
- Controlar caja diaria con registro detallado de movimientos
- Gestionar clientes mayoristas con historial y créditos
- Generar reportes completos de ventas, inventario y operaciones
- Facturación electrónica certificada por SAT
- Auditar todas las operaciones del sistema

El sistema debe resolver los problemas actuales de:
- Pérdida de trabajos en taller por falta de seguimiento
- Desconocimiento de ubicación actual de trabajos
- Falta de control de inventario entre sucursales
- Control manual de caja propenso a errores
- Ausencia de reportes para toma de decisiones

---

## 2. ALCANCE DEL SISTEMA

### 2.1 Lo que SÍ incluye el sistema

**Módulos incluidos:**
- ✅ Sistema de autenticación con 6 roles de usuario
- ✅ Gestión de inventario multi-sucursal
- ✅ Módulo de taller con seguimiento de trabajos
- ✅ Punto de venta (POS) completo
- ✅ Control de caja diario
- ✅ Gestión de clientes y proveedores
- ✅ Sistema de créditos semanales
- ✅ Reportes y estadísticas
- ✅ Facturación electrónica certificada SAT
- ✅ Auditoría completa de operaciones
- ✅ Acceso desde computadoras y dispositivos móviles

**Funcionalidades especiales:**
- ✅ Productos con 4 tipos de precio
- ✅ Productos por peso (oro/plata)
- ✅ Múltiples formas de pago por venta
- ✅ Transferencias de inventario entre sucursales
- ✅ Transferencias de trabajos entre empleados
- ✅ Alertas automáticas (stock bajo, fechas entrega, créditos vencidos)
- ✅ Generación de tickets con logo personalizado
- ✅ Exportación de reportes a Excel y PDF
- ✅ Gráficas y comparativas avanzadas

### 2.2 Lo que NO incluye el sistema (v1)

**Fuera del alcance inicial:**
- ❌ App móvil nativa (iOS/Android)
- ❌ Notificaciones push móviles
- ❌ Integración con WhatsApp Business API
- ❌ Mensajería masiva automatizada
- ❌ Programa de puntos/lealtad
- ❌ Galería de fotos de trabajos de taller
- ❌ Gestión completa de compras a proveedores
- ❌ Órdenes de compra formales
- ❌ Sistema de nómina
- ❌ Contabilidad completa

---

## 3. USUARIOS DEL SISTEMA

### 3.1 Roles y Responsabilidades

**ADMINISTRADOR** (1 usuario)
- Control total del sistema
- Gestionar usuarios y roles
- Configuración del sistema
- Acceso a todos los módulos
- Todos los reportes
- Auditoría completa

**DUEÑO** (1 usuario)
- Ver todos los reportes
- Gestión de inventario completa
- Gestión de clientes y proveedores
- Acceso a módulo de taller
- Acceso a módulo de ventas
- Acceso a módulo de caja
- NO puede gestionar usuarios ni configuración del sistema

**VENDEDOR** (2 usuarios)
- Realizar ventas
- Ver inventario (solo lectura)
- Gestionar clientes (crear, editar)
- Aplicar descuentos establecidos por administrador
- Ver historial de sus propias ventas
- NO puede editar inventario
- NO puede acceder a caja
- NO puede ver ventas de otros vendedores

**CAJERO** (1 usuario)
- Apertura y cierre de caja
- Registrar movimientos de dinero
- Ver corte de caja del día
- Generar reportes de caja
- NO puede realizar ventas
- NO puede modificar inventario

**ORFEBRE** (3 usuarios - Taller)
- Ver trabajos del taller
- Actualizar estado de trabajos
- Recibir trabajos nuevos
- Entregar trabajos completados
- Transferir trabajos a otros empleados
- Ver solo sus trabajos asignados
- NO acceso a ventas, inventario o caja

**PUBLICIDAD** (1 usuario - opcional)
- Ver reportes de ventas (solo lectura)
- Ver inventario (solo lectura)
- Ver lista de clientes
- NO puede modificar nada
- NO acceso a información sensible (precios de compra, ganancias)

**Total de usuarios simultáneos:** 6 personas

---

## 4. MÓDULOS PRINCIPALES DEL SISTEMA

### 4.1 MÓDULO AUTENTICACIÓN Y SEGURIDAD

**Funcionalidades:**
- Login seguro con email y contraseña
- Logout con cierre de sesión
- Recuperación de contraseña
- Sesiones con timeout automático (30 minutos inactividad)
- Verificación de roles antes de cada acción
- Protección contra SQL injection
- Protección contra XSS
- Tokens CSRF en formularios críticos
- Passwords hasheados con bcrypt
- Forzar HTTPS en producción
- Auditoría de todos los accesos

**Datos que maneja:**
- Usuarios (id, nombre, email, password, rol, sucursal_id, activo)
- Sesiones activas
- Intentos fallidos de login
- Registro de accesos (audit_log)

---

### 4.2 MÓDULO INVENTARIO

**Funcionalidades:**
- **Productos:**
  - Crear, editar, eliminar productos (soft delete)
  - Búsqueda rápida por código o nombre
  - Clasificación por categorías
  - 4 tipos de precio por producto:
    * Precio público
    * Precio mayorista
    * Precio con descuento
    * Precio descuento especial
  - Productos por peso (oro/plata por gramo)
  - Múltiples presentaciones (estilo, largo, peso)
  - Soporte para códigos de barras
  - Carga de imagen del producto

- **Categorías:**
  - Gestión de categorías y subcategorías
  - Clasificación por tipo, material, peso

- **Control de Stock:**
  - Inventario por sucursal
  - Inventario combinado (algunos productos compartidos, otros separados)
  - Actualización automática al vender
  - Actualización automática al recibir compra
  - Ajustes manuales de inventario (con justificación)
  - Transferencias entre sucursales
  - Alertas cuando stock < 5 unidades
  - Historial de movimientos de inventario

- **Materias Primas:**
  - Control de materias primas para taller (oro, plata, piedras)
  - Asignación a trabajos
  - Control de consumo

**Datos que maneja:**
- Productos (código, nombre, descripción, categoría, proveedor, imagen)
- Precios (4 tipos por producto)
- Categorías
- Inventario (producto_id, sucursal_id, cantidad, stock_mínimo)
- Movimientos de inventario (historial)
- Materias primas
- Transferencias entre sucursales

**Reportes:**
- Inventario actual por sucursal
- Productos con stock bajo
- Productos sin movimiento
- Valorización de inventario
- Historial de transferencias

---

### 4.3 MÓDULO TALLER (CRÍTICO)

**Funcionalidades:**
- **Recepción de Trabajos:**
  - Registrar trabajo nuevo
  - Información del cliente (nombre, teléfono)
  - Descripción detallada de la pieza:
    * Clase de material (oro/plata)
    * Peso de la pieza
    * Largo de la pieza
    * Con piedra / sin piedra
    * Estilo de la pieza
  - Tipo de trabajo (reparación, ajuste, grabado, diseño, limpieza, engaste, fabricación)
  - Descripción detallada del trabajo a realizar
  - Precio total del trabajo
  - Anticipo recibido
  - Saldo pendiente (calculado automáticamente)
  - Fecha de recepción (automática)
  - Fecha de entrega prometida
  - Asignación inicial a empleado
  - Estado inicial: "Recibido"

- **Seguimiento de Trabajos:**
  - Ver lista de todos los trabajos
  - Filtros por:
    * Estado (recibido, en proceso, completado, entregado, cancelado)
    * Empleado asignado
    * Fecha de entrega
    * Cliente
  - Búsqueda por nombre de cliente o descripción
  - Actualizar estado del trabajo
  - Ver historial completo de cada trabajo

- **Transferencias entre Empleados:**
  - Transferir trabajo de un empleado a otro
  - Registrar:
    * Quién entrega
    * Quién recibe
    * Fecha y hora de transferencia
    * Motivo de transferencia (opcional)
    * Estado del trabajo en el momento
  - Historial completo de todas las transferencias
  - Ver trabajos actuales por empleado

- **Entrega de Trabajos:**
  - Marcar trabajo como completado
  - Registrar fecha de entrega real
  - Registrar quién entrega
  - Calcular saldo final
  - Opción de cobrar saldo en ese momento
  - Generar comprobante de entrega
  - Cambiar estado a "Entregado"

- **Alertas y Notificaciones:**
  - Trabajos con fecha de entrega próxima (3 días antes)
  - Trabajos vencidos (pasada fecha de entrega)
  - Dashboard con resumen de trabajos por estado

**Datos que maneja:**
- Trabajos (id, código, cliente, teléfono, descripción_pieza, tipo_trabajo, descripción_trabajo, precio, anticipo, saldo, fecha_recepción, fecha_entrega_prometida, fecha_entrega_real, estado, empleado_actual)
- Transferencias de trabajo (trabajo_id, empleado_origen, empleado_destino, fecha, motivo)
- Estados: Recibido, En Proceso, Completado, Entregado, Cancelado

**Reportes:**
- Trabajos pendientes
- Trabajos por empleado
- Trabajos completados en período
- Trabajos vencidos
- Ingresos por trabajos de taller
- Tiempo promedio de completado

---

### 4.4 MÓDULO PUNTO DE VENTA (POS)

**Funcionalidades:**
- **Búsqueda de Productos:**
  - Búsqueda por código de barras (scanner)
  - Búsqueda por nombre (autocompletado)
  - Búsqueda por categoría
  - Ver precio según tipo de cliente (público/mayorista)

- **Carrito de Compra:**
  - Agregar productos al carrito
  - Ajustar cantidad
  - Eliminar productos
  - Aplicar descuento (monto fijo) a la venta total
  - Cálculo automático de subtotal y total
  - Mostrar stock disponible

- **Selección de Cliente:**
  - Búsqueda de cliente existente
  - Cliente mayorista aplicar precio automáticamente
  - Crear cliente rápido en el momento
  - Venta sin cliente (público general)

- **Formas de Pago:**
  - Múltiples formas de pago en una sola venta:
    * Efectivo
    * Tarjeta de débito
    * Tarjeta de crédito
    * Transferencia bancaria
    * Cheque
  - Especificar monto por cada forma de pago
  - Calcular cambio si es efectivo
  - Validar que suma de pagos = total de venta

- **Ventas a Crédito:**
  - Opción de venta a crédito semanal
  - Definir número de cuotas semanales
  - Calcular monto de cuota
  - Registrar crédito automáticamente
  - Solo para clientes registrados

- **Finalización de Venta:**
  - Validar que hay stock suficiente
  - Registrar venta en base de datos
  - Actualizar inventario automáticamente (descontar de sucursal)
  - Registrar movimiento en caja (ingreso automático)
  - Generar número de venta consecutivo
  - Generar ticket/factura
  - Opción de imprimir o descargar PDF
  - Limpiar carrito para siguiente venta

- **Apartados de Mercadería:**
  - Marcar venta como "Apartado"
  - Registrar anticipo
  - Saldo pendiente
  - Reservar productos en inventario
  - Liquidar apartado cuando cliente paga saldo

- **Facturación:**
  - Opción de factura simple o factura electrónica SAT
  - Solicitar NIT al cliente
  - Generar factura certificada
  - Envío de factura por correo electrónico
  - Registro de facturas emitidas

**Datos que maneja:**
- Ventas (número, fecha, hora, cliente_id, usuario_id, sucursal_id, subtotal, descuento, total, estado, tipo)
- Detalle de ventas (venta_id, producto_id, cantidad, precio_unitario, subtotal)
- Formas de pago por venta (venta_id, forma_pago, monto)
- Estados de venta: Completada, Apartada, Anulada
- Facturas (venta_id, número_factura, nit, nombre, serie, uuid_sat, xml, fecha_certificación)

**Reportes:**
- Ventas del día
- Ventas por período
- Ventas por vendedor
- Ventas por forma de pago
- Productos más vendidos
- Ticket promedio

---

### 4.5 MÓDULO CAJA

**Funcionalidades:**
- **Apertura de Caja:**
  - Registrar monto inicial en efectivo
  - Asignar a usuario y sucursal
  - No permitir abrir si ya hay caja abierta
  - Registrar fecha y hora de apertura

- **Registro de Movimientos:**
  - Ingresos automáticos de ventas
  - Ingresos manuales:
    * Ingresos de reparaciones/taller
    * Anticipo de trabajos
    * Abonos a créditos
    * Anticipo mercadería apartada
  - Egresos manuales:
    * Gastos generales (con descripción)
    * Pagos a proveedores
    * Compras de material
    * Pago de alquileres
    * Pago de salarios
  - Cada movimiento requiere:
    * Tipo de movimiento
    * Concepto/descripción
    * Monto
    * Usuario que registra

- **Cierre de Caja:**
  - Calcular totales por tipo de movimiento
  - Calcular total esperado
  - Registrar efectivo real contado
  - Calcular diferencia (faltante/sobrante)
  - Registrar observaciones si hay diferencia
  - Generar reporte de cierre
  - Cerrar caja (no permite más movimientos)
  - Registrar fecha y hora de cierre

- **Consultas:**
  - Ver movimientos del día
  - Ver estado actual de caja
  - Historial de cierres anteriores
  - Comparativas entre días

**Datos que maneja:**
- Cajas (id, usuario_id, sucursal_id, fecha_apertura, fecha_cierre, monto_inicial, monto_final_esperado, monto_final_real, diferencia, estado)
- Movimientos (caja_id, tipo, concepto, monto, usuario_id, fecha_hora)
- Estados de caja: Abierta, Cerrada

**Reportes:**
- Cierre de caja diario
- Diferencias de caja
- Movimientos por tipo
- Ingresos vs egresos

---

### 4.6 MÓDULO CLIENTES

**Funcionalidades:**
- **Gestión de Clientes:**
  - Crear cliente nuevo
  - Editar información de cliente
  - Eliminar cliente (soft delete)
  - Búsqueda por nombre, teléfono o NIT
  - Clasificación: Público / Mayorista
  - Información a guardar:
    * Nombre completo
    * NIT (opcional)
    * Teléfono
    * Email (opcional)
    * Dirección
    * Tipo de cliente (público/mayorista)
    * Tipo de mercaderías que compra (oro/plata)
    * Activo/Inactivo

- **Historial de Compras:**
  - Ver todas las compras del cliente
  - Filtrar por fechas
  - Total comprado histórico
  - Productos que más compra
  - Frecuencia de compra

- **Gestión de Créditos:**
  - Ver créditos activos del cliente
  - Detalles del crédito:
    * Monto total
    * Saldo pendiente
    * Cuota semanal
    * Fecha de inicio
    * Próximo pago
    * Días de atraso
  - Registrar abono:
    * Monto del abono
    * Forma de pago
    * Fecha
    * Recalcular saldo automáticamente
  - Historial de abonos
  - Alertas de cuotas vencidas
  - Estado del crédito: Al día, Atrasado, Liquidado

- **Clientes Mayoristas:**
  - Precio especial asignado
  - Límite de crédito
  - Plazo de crédito permitido
  - Descuentos automáticos

**Datos que maneja:**
- Clientes (id, nombre, nit, teléfono, email, dirección, tipo, activo)
- Créditos (id, cliente_id, venta_id, monto_total, saldo_pendiente, cuota_semanal, fecha_inicio, estado)
- Abonos (id, credito_id, monto, forma_pago, fecha, usuario_id)

**Reportes:**
- Clientes más frecuentes
- Clientes mayoristas
- Total de créditos por cobrar
- Créditos vencidos
- Historial de abonos

---

### 4.7 MÓDULO PROVEEDORES

**Funcionalidades:**
- Crear proveedor nuevo
- Editar información
- Eliminar proveedor (soft delete)
- Información a guardar:
  * Nombre del proveedor
  * Empresa
  * Contacto (nombre persona)
  * Teléfono
  * Email
  * Dirección
  * Productos que suministra
  * Activo/Inactivo

**Datos que maneja:**
- Proveedores (id, nombre, empresa, contacto, teléfono, email, dirección, activo)

---

### 4.8 MÓDULO REPORTES Y ESTADÍSTICAS

**Funcionalidades:**
- **Reportes de Ventas:**
  - Ventas diarias (por día seleccionado)
  - Ventas semanales
  - Ventas mensuales
  - Ventas anuales
  - Filtros por:
    * Rango de fechas
    * Sucursal
    * Vendedor
    * Forma de pago
  - Exportar a Excel
  - Exportar a PDF
  - Gráficas de tendencias (Chart.js)
  - Comparativas entre períodos

- **Reportes de Productos:**
  - Productos más vendidos (top 10, 20, 50)
  - Productos con menos movimiento
  - Productos con stock bajo
  - Valorización de inventario
  - Exportar a Excel

- **Reportes de Inventario:**
  - Inventario actual por sucursal
  - Existencias en bodegas
  - Movimientos de inventario
  - Transferencias entre sucursales
  - Exportar a Excel

- **Reportes de Taller:**
  - Trabajos pendientes
  - Trabajos completados en período
  - Trabajos por empleado
  - Trabajos vencidos
  - Ingresos por taller
  - Exportar a Excel y PDF

- **Reportes de Caja:**
  - Cierres de caja por período
  - Movimientos por tipo
  - Diferencias de caja
  - Ingresos vs egresos
  - Exportar a Excel y PDF

- **Reportes de Clientes:**
  - Cuentas por cobrar (créditos activos)
  - Créditos vencidos
  - Abonos del período
  - Clientes más frecuentes
  - Clientes mayoristas
  - Exportar a Excel

- **Reportes de Ganancias:**
  - Ganancias por período
  - Margen de ganancia por producto
  - Rentabilidad por sucursal
  - Rentabilidad por vendedor
  - Comparativas entre períodos
  - Exportar a Excel y PDF

- **Dashboard Ejecutivo:**
  - Ventas del día (tiempo real)
  - Ventas del mes
  - Productos por agotarse
  - Trabajos próximos a entregar
  - Créditos vencidos
  - Gráficas y estadísticas visuales

**Datos que maneja:**
- Consultas a todas las tablas del sistema
- Cálculos agregados
- Datos históricos

**Exportación:**
- Excel (XLS/XLSX)
- PDF con formato profesional
- Gráficas incluidas

---

## 5. REQUERIMIENTOS TÉCNICOS

### 5.1 Tecnologías del Sistema

**Backend:**
- PHP 8.1 o superior
- MySQL 5.7+ / MariaDB 10.3+
- PDO para conexiones a base de datos
- Prepared statements (seguridad)

**Frontend:**
- HTML5 semántico
- CSS3 + Bootstrap 5
- JavaScript ES6+ (Vanilla, sin jQuery)
- Responsive design (móvil, tablet, desktop)

**Librerías Frontend:**
- Bootstrap 5.3+ (framework CSS)
- Bootstrap Icons
- DataTables (tablas avanzadas)
- Chart.js (gráficas)
- SweetAlert2 (alertas bonitas)
- FullCalendar (calendario para taller - opcional)

**Librerías Backend:**
- FPDF (generación de PDFs)
- PHPMailer (envío de emails)
- PHPSpreadsheet (exportación Excel - opcional)

**Hosting:**
- Hostinger Business Plan
- SSL certificado (HTTPS)
- Backup diario automático
- Correos corporativos

**Control de Versiones:**
- Git
- GitHub (repositorio privado)

### 5.2 Arquitectura del Sistema

- Monolítico modular
- Patrón MVC simplificado
- Server-side rendering
- AJAX para operaciones específicas
- Sesiones PHP para autenticación
- APIs internas (endpoints JSON)

### 5.3 Seguridad

**Implementaciones obligatorias:**
- Prepared statements (anti SQL injection)
- htmlspecialchars en todos los outputs (anti XSS)
- Validación frontend y backend
- Tokens CSRF en formularios
- Headers de seguridad HTTP
- HTTPS forzado en producción
- Passwords con bcrypt (password_hash)
- Sesiones con timeout
- Verificación de roles en cada acción
- Auditoría completa (quién hace qué)
- Logs de errores en archivo
- Backups automáticos diarios

### 5.4 Rendimiento

- Índices en campos de búsqueda frecuente
- Paginación en listados grandes (50 items/página)
- Queries optimizados
- Assets minificados en producción
- Imágenes optimizadas
- Caché de sesión para datos frecuentes

---

## 6. REQUERIMIENTOS DE INFRAESTRUCTURA

### 6.1 Equipos del Cliente

**Disponibles:**
- 3 computadoras (entre ambas sucursales)
- Laptops
- Tablets
- Lector de códigos de barras
- Impresora de tickets/recibos
- Impresora de etiquetas
- Cajón de dinero
- Terminal de tarjetas (POS)

**Requerimientos del sistema:**
- Navegador moderno (Chrome, Firefox, Edge)
- Conexión a internet estable
- Resolución mínima: 1024x768

### 6.2 Conectividad

**Actual:**
- Internet en ambas sucursales: Sí
- Velocidad recomendada: 5 Mbps mínimo

**Acceso:**
- Desde computadoras: Sí
- Desde tablets: Sí
- Desde celular: Sí (responsive)
- App móvil nativa: No (v1)

### 6.3 Hosting

**Especificaciones:**
- Hostinger Business Plan
- 50 GB de espacio NVMe
- SSL incluido
- Backups diarios automáticos
- 5 cuentas de correo por sitio
- Panel de control (hPanel)
- Soporte PHP 8.1+
- MySQL/MariaDB incluido

---

## 7. FLUJOS DE TRABAJO CRÍTICOS

### 7.1 Proceso de Venta Completa

1. Vendedor inicia sesión
2. Abre módulo de Punto de Venta
3. Busca productos (código de barras o nombre)
4. Agrega productos al carrito
5. Selecciona cliente (opcional, obligatorio si es crédito o mayorista)
6. Aplica descuento si aplica (monto fijo)
7. Selecciona forma(s) de pago:
   - Si es una forma: ingresa monto total
   - Si son múltiples: distribuye monto entre formas
8. Si es crédito: define número de cuotas
9. Sistema valida que hay stock suficiente
10. Confirma venta
11. Sistema procesa:
    - Registra venta en BD
    - Descuenta inventario de sucursal
    - Registra ingreso en caja automáticamente
    - Si es crédito: crea registro de crédito
    - Genera número de venta
12. Genera ticket/factura
13. Opción de imprimir o enviar por email
14. Si es factura electrónica: certifica con SAT
15. Limpia carrito para siguiente venta

### 7.2 Proceso de Trabajo en Taller

**Recepción:**
1. Cliente llega con pieza
2. Orfebre/recepcionista abre módulo Taller
3. Click en "Recibir Trabajo"
4. Llena formulario:
   - Datos del cliente
   - Descripción detallada de la pieza
   - Tipo de trabajo
   - Precio acordado
   - Anticipo (si hay)
   - Fecha de entrega prometida
5. Asigna a empleado inicial
6. Sistema genera código de trabajo
7. Sistema calcula saldo automáticamente
8. Guarda trabajo con estado "Recibido"
9. Imprime comprobante para cliente

**Transferencia:**
1. Empleado A termina su parte del trabajo
2. Abre lista de trabajos
3. Selecciona el trabajo
4. Click en "Transferir"
5. Selecciona empleado B (destino)
6. Agrega nota (opcional)
7. Confirma transferencia
8. Sistema registra:
   - Quién entrega (Empleado A)
   - Quién recibe (Empleado B)
   - Fecha y hora
   - Estado del trabajo
9. Empleado B ahora ve el trabajo en su lista

**Entrega:**
1. Cliente llega a recoger
2. Orfebre busca el trabajo
3. Verifica que esté completado
4. Click en "Entregar Trabajo"
5. Sistema muestra:
   - Precio total
   - Anticipo ya pagado
   - Saldo pendiente
6. Cliente paga saldo
7. Registra forma de pago
8. Sistema:
   - Marca trabajo como "Entregado"
   - Registra fecha de entrega real
   - Registra ingreso en caja
9. Genera comprobante final
10. Imprime o envía por email

### 7.3 Proceso de Apertura y Cierre de Caja

**Apertura:**
1. Cajero inicia sesión
2. Abre módulo Caja
3. Click en "Abrir Caja"
4. Sistema verifica que no haya caja abierta
5. Cuenta efectivo inicial
6. Ingresa monto inicial
7. Confirma apertura
8. Sistema registra:
   - Usuario
   - Sucursal
   - Fecha y hora
   - Monto inicial
9. Caja queda en estado "Abierta"

**Durante el día:**
- Ventas registran ingresos automáticamente
- Se registran movimientos manuales según necesidad
- Todos los movimientos quedan asociados a la caja

**Cierre:**
1. Cajero abre módulo Caja
2. Click en "Cerrar Caja"
3. Sistema calcula:
   - Total de ingresos
   - Total de egresos
   - Monto esperado = inicial + ingresos - egresos
4. Cajero cuenta efectivo real
5. Ingresa monto real contado
6. Sistema calcula diferencia
7. Si hay diferencia, solicita observación
8. Confirma cierre
9. Sistema genera reporte de cierre con:
   - Movimientos del día
   - Totales por tipo
   - Diferencia
10. Imprime o descarga reporte
11. Caja queda en estado "Cerrada"
12. No permite más movimientos hasta nueva apertura

### 7.4 Proceso de Crédito y Abonos

**Venta a Crédito:**
1. En el POS, marcar como "Venta a Crédito"
2. Seleccionar cliente (obligatorio)
3. Definir número de cuotas semanales
4. Sistema calcula cuota semanal automáticamente
5. Procesar venta normalmente
6. Sistema crea registro de crédito:
   - Monto total
   - Saldo pendiente (igual al total inicialmente)
   - Cuota semanal
   - Fecha de inicio
   - Próximo pago (1 semana después)
7. Cliente recibe mercadería
8. Queda registrado el crédito

**Registro de Abono:**
1. Cliente llega a pagar cuota
2. Abrir módulo Clientes
3. Buscar cliente
4. Ver créditos activos
5. Seleccionar crédito
6. Click en "Registrar Abono"
7. Ingresar monto del abono
8. Seleccionar forma de pago
9. Confirmar abono
10. Sistema:
    - Descuenta del saldo pendiente
    - Registra en historial de abonos
    - Recalcula próximo pago
    - Registra ingreso en caja
    - Si saldo = 0, marca crédito como "Liquidado"
11. Genera comprobante de pago

---

## 8. INTEGRACIONES

### 8.1 Facturación Electrónica SAT

**Requerimientos:**
- Certificación como emisor de facturas electrónicas
- Firma electrónica (GFACE)
- Integración con portal SAT o proveedor certificado
- Generación de XML con formato SAT
- Firma digital del XML
- Envío a SAT para certificación
- Recepción de UUID (folio fiscal)
- Almacenamiento de XML certificado
- Envío de factura por email a cliente
- Registro en libro de ventas

**Datos de factura:**
- Serie y número
- Fecha y hora de emisión
- NIT y nombre del cliente
- Detalle de productos/servicios
- Subtotal, descuentos, total
- Firma electrónica
- UUID de certificación SAT
- Código QR

### 8.2 Impresión de Tickets

**Hardware compatible:**
- Impresoras térmicas de tickets
- Formato 80mm o 58mm
- Conexión USB o Red

**Contenido del ticket:**
- Logo de Joyería Torre Fuerte
- Nombre del negocio
- Dirección y teléfono
- Número de ticket
- Fecha y hora
- Vendedor
- Cliente (si aplica)
- Detalle de productos
- Subtotal
- Descuento (si aplica)
- Total
- Formas de pago
- Cambio (si aplica)
- Mensaje: "Gracias por su preferencia y bendiciones"

### 8.3 Lector de Códigos de Barras

**Funcionalidad:**
- Búsqueda automática al leer código
- Agregar al carrito automáticamente
- Compatible con códigos estándar (EAN-13, UPC, Code 39, etc.)

---

## 9. CAPACITACIÓN Y SOPORTE

### 9.1 Capacitación

**Modalidad:** Combinada (presencial y videollamada)

**Sesiones:**
- **Sesión 1** (2-3 horas): Personal operativo
  - Vendedores
  - Cajeros
  - Orfebres
  - Contenido: Uso diario del sistema
  
- **Sesión 2** (2-3 horas): Administradores
  - Dueño
  - Administrador
  - Contenido: Reportes, configuración, gestión completa

**Material de capacitación:**
- Videos tutoriales (5-10 min cada uno):
  * Cómo hacer una venta
  * Cómo recibir trabajo en taller
  * Cómo transferir trabajo
  * Cómo cerrar caja
  * Cómo generar reportes
  * Cómo gestionar clientes
  * Cómo registrar abono a crédito
- Manual de usuario (preferencia: videos sobre documentos escritos)

### 9.2 Soporte Post-Entrega

**Incluido en el primer mes:**
- Soporte ilimitado
- Corrección de bugs
- Respuesta en máximo 24 horas
- Atención vía WhatsApp, llamada o email

**Después del primer mes:**
- Mensualidad de Q150/mes incluye:
  * Soporte técnico
  * Corrección de bugs
  * Actualizaciones de seguridad
  * Backups monitoreados
  * Hosting y dominio
- Nuevas funcionalidades: Se cotizan por separado

---

## 10. CRITERIOS DE ACEPTACIÓN

El sistema será considerado completo y aceptado cuando:

### 10.1 Funcionalidad
- ✅ Todos los módulos funcionan correctamente
- ✅ Todos los flujos de trabajo críticos operan sin errores
- ✅ Reportes generan información correcta
- ✅ Exportaciones funcionan (Excel, PDF)
- ✅ Facturación electrónica certifica con SAT
- ✅ Tickets se imprimen correctamente
- ✅ Alertas funcionan (stock bajo, fechas, créditos)

### 10.2 Seguridad
- ✅ Sistema de roles funciona correctamente
- ✅ No hay vulnerabilidades de seguridad
- ✅ HTTPS activo
- ✅ Passwords encriptados
- ✅ Auditoría registra todas las operaciones

### 10.3 Rendimiento
- ✅ Páginas cargan en menos de 2 segundos
- ✅ Búsquedas responden en menos de 1 segundo
- ✅ Sistema funciona con 6 usuarios simultáneos
- ✅ Base de datos soporta 1000+ productos
- ✅ Sistema soporta 100+ ventas diarias

### 10.4 Usabilidad
- ✅ Interfaz intuitiva y fácil de usar
- ✅ Responsive (funciona en móvil, tablet, desktop)
- ✅ Mensajes de error claros
- ✅ Navegación lógica
- ✅ Diseño acorde a preferencias del cliente (colores, estilo)

### 10.5 Capacitación
- ✅ Personal capacitado
- ✅ Videos tutoriales entregados
- ✅ Manual de usuario disponible

### 10.6 Documentación
- ✅ Manual técnico entregado
- ✅ Documentación de código
- ✅ Documentación de base de datos
- ✅ Credenciales de acceso entregadas

### 10.7 Cliente
- ✅ Cliente prueba el sistema
- ✅ Cliente aprueba funcionalidades
- ✅ Cliente firma acta de entrega

---

## 11. RESTRICCIONES Y CONSIDERACIONES

### 11.1 Presupuesto
- Desarrollo: Q1,500 (Q750 inicial + Q750 al entregar)
- Hosting primer año: Incluido en inversión inicial
- Dominio primer año: Incluido en inversión inicial

### 11.2 Tiempo
- Fecha límite de entrega: Primera semana de marzo 2026
- Tiempo estimado de desarrollo: 3-4 semanas

### 11.3 Datos Existentes
- No hay datos a migrar de sistema anterior
- Sistema se estrena con datos limpios
- Se cargarán productos manualmente al inicio

### 11.4 Usuarios Simultáneos
- Estimado: 6 personas máximo
- El sistema debe soportar sin degradación

### 11.5 Dispositivos de Acceso
- PC de escritorio: Sí
- Laptops: Sí
- Tablets: Sí
- Celulares: Sí (responsive web)

---

## 12. RIESGOS IDENTIFICADOS

| Riesgo | Probabilidad | Impacto | Mitigación |
|--------|--------------|---------|------------|
| Complejidad del módulo Taller mayor a lo estimado | Alta | Alto | Asignar 2 días extra en planning |
| Cliente tarda en dar feedback | Media | Medio | Establecer plazos de respuesta (3 días) |
| Problemas con certificación SAT | Media | Medio | Implementar factura básica primero, certificación después |
| Bugs en producción | Media | Alto | Fase de pruebas exhaustiva + soporte post-entrega |
| Falta claridad en algún requerimiento | Baja | Alto | Validar constantemente con cliente |

---

## 13. SUPUESTOS Y DEPENDENCIAS

### 13.1 Supuestos
- Cliente tiene conexión a internet estable en ambas sucursales
- Cliente proveerá logo del negocio en formato adecuado
- Equipos del cliente (computadoras, impresoras) funcionan correctamente
- Cliente estará disponible para validaciones y pruebas
- Cliente tiene proceso de certificación SAT en trámite

### 13.2 Dependencias
- Hosting contratado antes de deployment
- Dominio registrado
- Proceso de certificación SAT completado (para facturación electrónica)
- Logo y materiales gráficos del cliente
- Información completa de productos para carga inicial

---

## 14. CONTROL DE CAMBIOS

Cualquier cambio a estos requerimientos después de aprobados debe:
1. Ser solicitado por escrito
2. Evaluarse impacto en tiempo y costo
3. Aprobarse por ambas partes
4. Documentarse en este archivo

---

## 15. APROBACIONES

**Cliente:**
- Nombre: _______________________________
- Firma: _______________________________
- Fecha: _______________________________

**Desarrollador:**
- Nombre: _______________________________
- Firma: _______________________________
- Fecha: _______________________________

---

**Versión del documento:** 1.0  
**Última actualización:** 20 de enero de 2026  
**Estado:** Pendiente de aprobación

═══════════════════════════════════════════════════════════
           DOCUMENTO DE REQUERIMIENTOS COMPLETO
═══════════════════════════════════════════════════════════
