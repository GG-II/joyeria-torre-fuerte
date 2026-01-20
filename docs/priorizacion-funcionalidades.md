# PRIORIZACIÓN DE FUNCIONALIDADES
## Sistema de Gestión - Joyería Torre Fuerte

**Fecha:** 20 de enero de 2026  
**Versión:** 1.0  
**Criterio de priorización:** Impacto en el negocio + Urgencia del cliente

---

## METODOLOGÍA DE PRIORIZACIÓN

Las funcionalidades se clasifican en tres niveles:

- 🔴 **CRÍTICAS:** Sin estas funcionalidades el sistema NO es útil para el negocio
- 🟡 **IMPORTANTES:** Funcionalidades necesarias para versión 1.0 completa
- 🟢 **DESEABLES:** Mejoras que pueden implementarse en versión 2.0

---

## 🔴 FUNCIONALIDADES CRÍTICAS

### ¿Por qué son críticas?
Estas funcionalidades resuelven los problemas principales del cliente y sin ellas el sistema no cumple su propósito básico.

---

### MÓDULO TALLER (Máxima Prioridad)

**Razón:** Es el dolor de cabeza principal del cliente - pierden trabajos y no saben dónde están.

#### T-01: Recepción de Trabajos
- **Descripción:** Formulario completo para registrar trabajos nuevos del taller
- **Campos obligatorios:**
  - Cliente (nombre, teléfono)
  - Descripción detallada de la pieza
  - Tipo de trabajo
  - Precio, anticipo, saldo
  - Fecha de entrega prometida
  - Empleado asignado
- **Impacto:** ALTO - Base del sistema de taller
- **Urgencia:** ALTA
- **Tiempo estimado:** 1 día

#### T-02: Transferencias entre Empleados
- **Descripción:** Sistema para transferir trabajos de un empleado a otro con registro completo
- **Funcionalidad:**
  - Seleccionar trabajo y empleado destino
  - Registrar quién entrega y quién recibe
  - Fecha y hora automática
  - Historial inmutable
- **Impacto:** CRÍTICO - Resuelve el problema principal
- **Urgencia:** CRÍTICA
- **Tiempo estimado:** 1.5 días

#### T-03: Historial Completo de Transferencias
- **Descripción:** Ver línea de tiempo completa de cada trabajo
- **Funcionalidad:**
  - Quién recibió inicialmente
  - Todas las transferencias (de quién a quién, cuándo)
  - Empleado actual
  - Nunca se puede borrar historial
- **Impacto:** CRÍTICO - Transparencia total
- **Urgencia:** CRÍTICA
- **Tiempo estimado:** 1 día

#### T-04: Vista de Trabajos por Empleado
- **Descripción:** Cada empleado ve solo sus trabajos asignados
- **Impacto:** ALTO - Organización
- **Urgencia:** ALTA
- **Tiempo estimado:** 0.5 días

#### T-05: Entrega de Trabajos
- **Descripción:** Proceso de entrega al cliente con cobro de saldo
- **Funcionalidad:**
  - Mostrar precio total, anticipo, saldo
  - Registrar pago
  - Actualizar caja automáticamente
  - Generar comprobante
- **Impacto:** ALTO
- **Urgencia:** ALTA
- **Tiempo estimado:** 1 día

#### T-06: Alertas de Fechas de Entrega
- **Descripción:** Notificaciones de trabajos próximos a vencer
- **Funcionalidad:**
  - Alertas 3 días antes de fecha de entrega
  - Trabajos vencidos en rojo
  - Badge en dashboard
- **Impacto:** ALTO - Cumplir con clientes
- **Urgencia:** ALTA
- **Tiempo estimado:** 0.5 días

**Total Módulo Taller Crítico:** 5.5 días

---

### MÓDULO PUNTO DE VENTA (POS)

**Razón:** Sin ventas no hay negocio.

#### V-01: Búsqueda Rápida de Productos
- **Descripción:** Buscar productos por código de barras o nombre
- **Funcionalidad:**
  - Scanner de códigos de barras
  - Búsqueda con autocompletado
  - Ver stock disponible
- **Impacto:** CRÍTICO
- **Urgencia:** CRÍTICA
- **Tiempo estimado:** 1 día

#### V-02: Carrito de Compra Funcional
- **Descripción:** Agregar productos, ajustar cantidades, calcular totales
- **Impacto:** CRÍTICO
- **Urgencia:** CRÍTICA
- **Tiempo estimado:** 1 día

#### V-03: Múltiples Formas de Pago por Venta
- **Descripción:** Permitir combinar efectivo, tarjeta, transferencia en una venta
- **Funcionalidad:**
  - Agregar múltiples formas de pago
  - Validar que sumen el total exacto
  - Calcular cambio si es efectivo
- **Impacto:** CRÍTICO - Cliente lo necesita frecuentemente
- **Urgencia:** CRÍTICA
- **Tiempo estimado:** 1.5 días

#### V-04: Actualización Automática de Inventario
- **Descripción:** Al procesar venta, descontar automáticamente del inventario
- **Funcionalidad:**
  - Usar transacciones SQL
  - Si falla algo, revertir todo
  - Actualizar en sucursal correcta
- **Impacto:** CRÍTICO
- **Urgencia:** CRÍTICA
- **Tiempo estimado:** 1 día

#### V-05: Registro en Caja Automático
- **Descripción:** Al vender, registrar ingreso en caja automáticamente
- **Impacto:** CRÍTICO - Integridad de datos
- **Urgencia:** CRÍTICA
- **Tiempo estimado:** 0.5 días

#### V-06: Ventas a Crédito Semanal
- **Descripción:** Permitir ventas a crédito con cuotas semanales
- **Funcionalidad:**
  - Definir número de cuotas
  - Calcular cuota semanal
  - Crear registro de crédito
  - Cliente recibe mercadería de inmediato
- **Impacto:** ALTO - Forma de venta común
- **Urgencia:** ALTA
- **Tiempo estimado:** 1 día

#### V-07: Generación de Tickets Básicos
- **Descripción:** Imprimir ticket con logo y detalles de venta
- **Impacto:** ALTO
- **Urgencia:** ALTA
- **Tiempo estimado:** 1 día

**Total Módulo POS Crítico:** 7 días

---

### MÓDULO INVENTARIO

**Razón:** Base de datos de productos y control de stock.

#### I-01: CRUD de Productos
- **Descripción:** Crear, editar, eliminar productos
- **Impacto:** CRÍTICO
- **Urgencia:** CRÍTICA
- **Tiempo estimado:** 1.5 días

#### I-02: Múltiples Precios por Producto
- **Descripción:** 4 tipos de precio (público, mayorista, descuento, especial)
- **Impacto:** CRÍTICO - Requerimiento del cliente
- **Urgencia:** CRÍTICA
- **Tiempo estimado:** 1 día

#### I-03: Control de Stock por Sucursal
- **Descripción:** Ver y controlar inventario de cada sucursal
- **Impacto:** CRÍTICO
- **Urgencia:** CRÍTICA
- **Tiempo estimado:** 1 día

#### I-04: Productos por Peso
- **Descripción:** Soporte para oro/plata que se vende por gramo
- **Impacto:** ALTO - Tipo de producto importante
- **Urgencia:** ALTA
- **Tiempo estimado:** 0.5 días

#### I-05: Alertas de Stock Bajo
- **Descripción:** Notificar cuando stock < 5 unidades
- **Funcionalidad:**
  - Badge visual en lista
  - Alerta en dashboard
  - Lista de productos con stock bajo
- **Impacto:** ALTO
- **Urgencia:** ALTA
- **Tiempo estimado:** 0.5 días

**Total Módulo Inventario Crítico:** 4.5 días

---

### MÓDULO CAJA

**Razón:** Control del dinero diario.

#### C-01: Apertura de Caja
- **Descripción:** Abrir caja con monto inicial
- **Funcionalidad:**
  - Validar que no haya otra caja abierta
  - Registrar monto inicial
  - Asignar a usuario y sucursal
- **Impacto:** CRÍTICO
- **Urgencia:** CRÍTICA
- **Tiempo estimado:** 0.5 días

#### C-02: Registro de Movimientos Básicos
- **Descripción:** Registrar ingresos y egresos manuales
- **Funcionalidad:**
  - Ventas (automático)
  - Trabajos taller (automático)
  - Gastos (manual)
  - Otros movimientos
- **Impacto:** CRÍTICO
- **Urgencia:** CRÍTICA
- **Tiempo estimado:** 1 día

#### C-03: Cierre de Caja Diario
- **Descripción:** Cerrar caja con cálculo de diferencia
- **Funcionalidad:**
  - Calcular monto esperado
  - Ingresar monto real
  - Calcular diferencia
  - Generar reporte
- **Impacto:** CRÍTICO
- **Urgencia:** CRÍTICA
- **Tiempo estimado:** 1 día

**Total Módulo Caja Crítico:** 2.5 días

---

### MÓDULO AUTENTICACIÓN

**Razón:** Seguridad del sistema.

#### A-01: Login/Logout Seguro
- **Descripción:** Sistema de autenticación con password hasheado
- **Impacto:** CRÍTICO
- **Urgencia:** CRÍTICA
- **Tiempo estimado:** 0.5 días

#### A-02: Roles y Permisos Básicos
- **Descripción:** 6 roles con permisos diferentes
- **Impacto:** CRÍTICO
- **Urgencia:** CRÍTICA
- **Tiempo estimado:** 0.5 días

#### A-03: Middleware de Protección
- **Descripción:** Verificar sesión y rol en cada página
- **Impacto:** CRÍTICO - Seguridad
- **Urgencia:** CRÍTICA
- **Tiempo estimado:** 0.5 días

**Total Módulo Autenticación Crítico:** 1.5 días

---

### RESUMEN FUNCIONALIDADES CRÍTICAS

| Módulo | Funcionalidades | Tiempo Total |
|--------|----------------|--------------|
| Taller | 6 funcionalidades | 5.5 días |
| POS | 7 funcionalidades | 7 días |
| Inventario | 5 funcionalidades | 4.5 días |
| Caja | 3 funcionalidades | 2.5 días |
| Autenticación | 3 funcionalidades | 1.5 días |
| **TOTAL CRÍTICAS** | **24 funcionalidades** | **21 días** |

---

## 🟡 FUNCIONALIDADES IMPORTANTES (v1.0)

### ¿Por qué son importantes?
Estas funcionalidades completan el sistema y lo hacen realmente útil y profesional, pero el sistema podría funcionar sin ellas temporalmente.

---

### MÓDULO CLIENTES

#### CL-01: CRUD de Clientes
- **Descripción:** Gestión completa de clientes
- **Impacto:** ALTO
- **Tiempo estimado:** 1 día

#### CL-02: Clasificación Público/Mayorista
- **Descripción:** Diferenciar tipos de cliente y aplicar precios
- **Impacto:** ALTO
- **Tiempo estimado:** 0.5 días

#### CL-03: Historial de Compras
- **Descripción:** Ver todas las compras de cada cliente
- **Impacto:** MEDIO
- **Tiempo estimado:** 0.5 días

#### CL-04: Gestión de Abonos a Créditos
- **Descripción:** Registrar pagos de cuotas semanales
- **Funcionalidad:**
  - Seleccionar crédito
  - Registrar abono
  - Actualizar saldo
  - Registrar en caja
- **Impacto:** ALTO - Necesario para créditos
- **Tiempo estimado:** 1 día

#### CL-05: Alertas de Créditos Vencidos
- **Descripción:** Notificar cuotas atrasadas
- **Impacto:** ALTO
- **Tiempo estimado:** 0.5 días

**Total Módulo Clientes:** 3.5 días

---

### MÓDULO INVENTARIO (Complementario)

#### I-06: Transferencias entre Sucursales
- **Descripción:** Mover productos de una sucursal a otra
- **Funcionalidad:**
  - Seleccionar productos y cantidades
  - Actualizar inventarios
  - Historial de transferencias
- **Impacto:** MEDIO-ALTO
- **Tiempo estimado:** 1 día

#### I-07: Gestión de Categorías
- **Descripción:** CRUD de categorías de productos
- **Impacto:** MEDIO
- **Tiempo estimado:** 0.5 días

#### I-08: Soporte de Códigos de Barras
- **Descripción:** Asignar y usar códigos de barras
- **Impacto:** MEDIO
- **Tiempo estimado:** 0.5 días

#### I-09: Materias Primas para Taller
- **Descripción:** Control de oro, plata, piedras para taller
- **Impacto:** MEDIO
- **Tiempo estimado:** 1 día

**Total Inventario Complementario:** 3 días

---

### MÓDULO CAJA (Complementario)

#### C-04: Todos los Tipos de Movimientos
- **Descripción:** 10 tipos completos de movimientos
- **Funcionalidad:**
  - Anticipo trabajos
  - Abonos créditos
  - Apartados
  - Pagos proveedores
  - Compras material
  - Alquileres
  - Salarios
- **Impacto:** MEDIO-ALTO
- **Tiempo estimado:** 1 día

#### C-05: Historial de Cierres
- **Descripción:** Ver cierres anteriores con filtros
- **Impacto:** MEDIO
- **Tiempo estimado:** 0.5 días

**Total Caja Complementaria:** 1.5 días

---

### MÓDULO REPORTES

#### R-01: Reporte de Ventas Diarias/Mensuales
- **Descripción:** Ventas con filtros por fecha, vendedor, sucursal
- **Impacto:** ALTO
- **Tiempo estimado:** 1 día

#### R-02: Productos Más Vendidos
- **Descripción:** Top 10, 20, 50 productos
- **Impacto:** MEDIO
- **Tiempo estimado:** 0.5 días

#### R-03: Reporte de Inventario Actual
- **Descripción:** Stock por sucursal, valorización
- **Impacto:** ALTO
- **Tiempo estimado:** 0.5 días

#### R-04: Reporte de Trabajos de Taller
- **Descripción:** Pendientes, completados, por empleado
- **Impacto:** ALTO
- **Tiempo estimado:** 0.5 días

#### R-05: Reporte de Cuentas por Cobrar
- **Descripción:** Créditos activos, vencidos, total
- **Impacto:** ALTO
- **Tiempo estimado:** 0.5 días

#### R-06: Exportación a Excel
- **Descripción:** Exportar reportes a Excel
- **Impacto:** MEDIO-ALTO
- **Tiempo estimado:** 1 día

#### R-07: Gráficas Avanzadas (Chart.js)
- **Descripción:** Gráficas de tendencias, comparativas visuales
- **Funcionalidad:**
  - Ventas en el tiempo (línea)
  - Comparación sucursales (barras)
  - Distribución de ventas (dona)
  - Productos más vendidos (barras horizontales)
- **Impacto:** ALTO - Mejora toma de decisiones
- **Tiempo estimado:** 1.5 días
- **Nota:** Cliente lo solicitó para v1

#### R-08: Comparativas entre Períodos
- **Descripción:** Comparar ventas/ganancias de diferentes períodos
- **Funcionalidad:**
  - Este mes vs mes anterior
  - Este año vs año anterior
  - Crecimiento porcentual
  - Tendencias
- **Impacto:** ALTO - Análisis estratégico
- **Tiempo estimado:** 1 día
- **Nota:** Cliente lo solicitó para v1

#### R-09: Reportes Personalizables
- **Descripción:** Filtros avanzados y campos seleccionables
- **Funcionalidad:**
  - Seleccionar qué columnas mostrar
  - Múltiples filtros combinados
  - Guardar configuración de reportes
- **Impacto:** MEDIO-ALTO - Flexibilidad
- **Tiempo estimado:** 1.5 días
- **Nota:** Cliente lo solicitó para v1

**Total Módulo Reportes:** 8 días

---

### MÓDULO VENTAS (Complementario)

#### V-08: Facturación Electrónica Certificada SAT
- **Descripción:** Generar facturas certificadas por SAT
- **Funcionalidad:**
  - Generar XML con formato SAT
  - Firmar electrónicamente
  - Enviar a SAT para certificación
  - Recibir UUID
  - Generar PDF con código QR
  - Enviar por email
- **Impacto:** ALTO - Requerimiento legal y del cliente
- **Tiempo estimado:** 3 días
- **Nota:** Cliente lo solicitó para v1
- **Complejidad:** Alta - requiere certificación previa

#### V-09: Tickets con Logo Personalizado Avanzado
- **Descripción:** Tickets profesionales con diseño personalizado
- **Funcionalidad:**
  - Logo del cliente
  - Colores corporativos
  - Información detallada
  - Código QR (opcional)
  - Mensaje personalizado
  - Formato térmico profesional
- **Impacto:** MEDIO-ALTO - Imagen profesional
- **Tiempo estimado:** 1 día
- **Nota:** Cliente lo solicitó para v1

#### V-10: Apartados de Mercadería
- **Descripción:** Sistema de apartados con anticipo
- **Funcionalidad:**
  - Registrar apartado con anticipo
  - Reservar productos
  - Liquidar cuando cliente paga saldo
  - Cancelar apartado si no paga
  - Controlar tiempo de apartado
- **Impacto:** MEDIO - Forma de venta adicional
- **Tiempo estimado:** 1.5 días
- **Nota:** Cliente lo solicitó para v1

#### V-11: Historial de Ventas con Filtros
- **Descripción:** Ver todas las ventas con búsqueda avanzada
- **Impacto:** MEDIO
- **Tiempo estimado:** 1 día

#### V-12: Anulación de Ventas
- **Descripción:** Anular ventas del día con permisos
- **Impacto:** MEDIO-ALTO
- **Tiempo estimado:** 1 día

**Total Ventas Complementario:** 7.5 días

---

### MÓDULO PROVEEDORES

#### P-01: CRUD de Proveedores
- **Descripción:** Gestión básica de proveedores
- **Impacto:** BAJO-MEDIO
- **Tiempo estimado:** 1 día

**Total Proveedores:** 1 día

---

### MÓDULO AUTENTICACIÓN (Complementario)

#### A-04: Gestión de Usuarios
- **Descripción:** CRUD de usuarios con asignación de roles
- **Impacto:** MEDIO-ALTO
- **Tiempo estimado:** 1 día

#### A-05: Auditoría Completa
- **Descripción:** Registro de todas las operaciones (quién hizo qué)
- **Impacto:** ALTO - Seguridad y control
- **Tiempo estimado:** 1 día

**Total Autenticación Complementaria:** 2 días

---

### RESUMEN FUNCIONALIDADES IMPORTANTES

| Módulo | Funcionalidades | Tiempo Total |
|--------|----------------|--------------|
| Clientes | 5 funcionalidades | 3.5 días |
| Inventario | 4 funcionalidades | 3 días |
| Caja | 2 funcionalidades | 1.5 días |
| Reportes | 9 funcionalidades | 8 días |
| Ventas | 5 funcionalidades | 7.5 días |
| Proveedores | 1 funcionalidad | 1 día |
| Autenticación | 2 funcionalidades | 2 días |
| **TOTAL IMPORTANTES** | **28 funcionalidades** | **26.5 días** |

---

## 🟢 FUNCIONALIDADES DESEABLES (v2.0)

### ¿Por qué son deseables?
Estas funcionalidades mejorarían el sistema pero no son esenciales para la versión 1.0. Pueden implementarse en versiones futuras.

---

### COMUNICACIÓN Y NOTIFICACIONES

#### N-01: Notificaciones por Email Automáticas
- **Descripción:** Enviar emails automáticos
- **Casos de uso:**
  - Recordatorio de trabajo próximo a entregar
  - Recordatorio de cuota vencida
  - Confirmación de venta
- **Impacto:** BAJO-MEDIO
- **Tiempo estimado:** 2 días

#### N-02: Recordatorios por WhatsApp
- **Descripción:** Integración con WhatsApp Business API
- **Impacto:** MEDIO
- **Tiempo estimado:** 3 días
- **Dependencia:** WhatsApp Business API (costo adicional)

#### N-03: Mensajería Masiva
- **Descripción:** Enviar promociones a clientes
- **Impacto:** BAJO
- **Tiempo estimado:** 2 días

---

### TALLER (Mejoras)

#### T-07: Galería de Fotos de Trabajos
- **Descripción:** Subir fotos del trabajo antes/después
- **Impacto:** BAJO-MEDIO
- **Tiempo estimado:** 1.5 días

#### T-08: Cotizaciones de Trabajos
- **Descripción:** Generar cotización antes de recibir trabajo
- **Impacto:** BAJO
- **Tiempo estimado:** 1 día

---

### CLIENTES (Mejoras)

#### CL-06: Programa de Puntos/Lealtad
- **Descripción:** Acumular puntos por compras
- **Impacto:** BAJO-MEDIO
- **Tiempo estimado:** 2 días

#### CL-07: Segmentación de Clientes
- **Descripción:** Categorizar clientes por volumen, frecuencia
- **Impacto:** BAJO
- **Tiempo estimado:** 1 día

---

### INVENTARIO (Mejoras)

#### I-10: Órdenes de Compra a Proveedores
- **Descripción:** Sistema completo de compras
- **Impacto:** MEDIO
- **Tiempo estimado:** 3 días

#### I-11: Gestión de Lotes y Fechas de Vencimiento
- **Descripción:** Para productos que venzan (si aplica)
- **Impacto:** BAJO
- **Tiempo estimado:** 2 días

---

### REPORTES (Mejoras)

#### R-10: Dashboard Personalizable por Usuario
- **Descripción:** Cada usuario configura su dashboard
- **Impacto:** BAJO-MEDIO
- **Tiempo estimado:** 2 días

#### R-11: Reportes Programados Automáticos
- **Descripción:** Generar y enviar reportes por email automáticamente
- **Impacto:** BAJO
- **Tiempo estimado:** 2 días

---

### TECNOLOGÍA

#### TECH-01: App Móvil Nativa
- **Descripción:** App para iOS y Android
- **Impacto:** MEDIO (ya es responsive web)
- **Tiempo estimado:** 20+ días
- **Nota:** Costo adicional significativo

#### TECH-02: Notificaciones Push
- **Descripción:** Notificaciones en dispositivos móviles
- **Impacto:** BAJO-MEDIO
- **Tiempo estimado:** 3 días
- **Dependencia:** Requiere app móvil o PWA

---

### RESUMEN FUNCIONALIDADES DESEABLES

| Categoría | Funcionalidades | Tiempo Estimado |
|-----------|----------------|-----------------|
| Comunicación | 3 funcionalidades | 7 días |
| Taller | 2 funcionalidades | 2.5 días |
| Clientes | 2 funcionalidades | 3 días |
| Inventario | 2 funcionalidades | 5 días |
| Reportes | 2 funcionalidades | 4 días |
| Tecnología | 2 funcionalidades | 23+ días |
| **TOTAL DESEABLES** | **13 funcionalidades** | **44.5+ días** |

---

## RESUMEN GENERAL DE PRIORIZACIÓN

| Prioridad | Funcionalidades | Tiempo Total | Para v1.0 |
|-----------|----------------|--------------|-----------|
| 🔴 **CRÍTICAS** | 24 | 21 días | ✅ SÍ |
| 🟡 **IMPORTANTES** | 28 | 26.5 días | ✅ SÍ |
| 🟢 **DESEABLES** | 13 | 44.5+ días | ❌ NO (v2.0) |
| **TOTAL** | **65** | **92 días** | - |

**Para v1.0:** 52 funcionalidades en 47.5 días (~9-10 semanas)  
**Meta realista:** 3-4 semanas trabajando eficientemente con Claude

---

## ESTRATEGIA DE IMPLEMENTACIÓN

### Semana 1: Fundamentos Críticos
- Autenticación completa
- Base de datos
- CRUD de Inventario
- CRUD de Productos con precios

### Semana 2: Módulo Taller (Prioridad #1)
- Recepción de trabajos
- Sistema de transferencias
- Historial completo
- Entrega de trabajos
- Alertas

### Semana 3: POS y Caja
- Punto de venta completo
- Múltiples formas de pago
- Ventas a crédito
- Sistema de caja
- Tickets básicos

### Semana 4: Complementos y Reportes
- Módulo clientes completo
- Gestión de créditos y abonos
- Reportes principales
- Gráficas avanzadas
- Facturación electrónica
- Apartados

### Ajustes Finales
- Pruebas exhaustivas
- Corrección de bugs
- Refinamiento UX
- Capacitación

---

## FUNCIONALIDADES MOVIDAS DE DESEABLE A IMPORTANTE (v1.0)

Por solicitud del cliente, estas funcionalidades se implementarán en v1:

### Ventas:
- ✅ **V-08:** Facturación electrónica certificada SAT
- ✅ **V-09:** Tickets con logo personalizado avanzado
- ✅ **V-10:** Apartados de mercadería

### Reportes:
- ✅ **R-07:** Gráficas avanzadas (Chart.js)
- ✅ **R-08:** Comparativas entre períodos
- ✅ **R-09:** Reportes personalizables

**Impacto en tiempo:** +7 días adicionales  
**Nuevo tiempo total v1:** 54.5 días de desarrollo puro

---

## DEPENDENCIAS CRÍTICAS

### Para Facturación Electrónica SAT:
- ⚠️ Cliente debe tener certificación como emisor de facturas
- ⚠️ Firma electrónica (GFACE) activa
- ⚠️ Proveedor de certificación SAT contratado (o integración directa)

### Para Códigos de Barras:
- ⚠️ Lector de códigos de barras funcional
- ⚠️ Códigos asignados a productos

### Para Impresión de Tickets:
- ⚠️ Impresora térmica de tickets instalada
- ⚠️ Driver compatible con el sistema

---

## RIESGOS IDENTIFICADOS

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Facturación SAT más compleja de lo estimado | Alto | Implementar factura simple primero, certificación después |
| Sistema de transferencias de taller no cumple expectativa | Crítico | Validar constantemente con cliente durante desarrollo |
| Múltiples formas de pago genera bugs | Medio | Pruebas exhaustivas, transacciones SQL |
| Tiempo insuficiente para v1 completa | Medio | Priorizar críticas, dejar deseables para v2 |

---

## CRITERIOS DE ACEPTACIÓN POR PRIORIDAD

### Para Críticas (✅ Obligatorio para entrega):
- 100% de funcionalidades críticas funcionando
- Sin bugs que impidan operación
- Datos se guardan correctamente
- Integraciones automáticas funcionan

### Para Importantes (✅ Obligatorio para v1.0):
- 90%+ de funcionalidades importantes funcionando
- Bugs menores aceptables si no impiden uso
- Cliente puede hacer su operación diaria completa

### Para Deseables (❌ Opcional, v2.0):
- Se pueden entregar parcialmente
- Se pueden postponer completamente
- Cliente está informado que son mejoras futuras

---

═══════════════════════════════════════════════════════════
          PRIORIZACIÓN DE FUNCIONALIDADES COMPLETA
═══════════════════════════════════════════════════════════
