# 📋 PLAN DE TRABAJO - SISTEMA DE GESTIÓN JOYERÍA TORRE FUERTE

**Cliente:** Joyería Torre Fuerte  
**Fecha de inicio:** 20 de enero de 2026  
**Desarrollador:** [Tu nombre]  
**Metodología:** Desarrollo por Fases con Claude

---

## 🎯 RESUMEN EJECUTIVO

**Tipo de proyecto:** Sistema de Gestión Integral para Joyería (2 sucursales)

**Complejidad:** Media-Alta
- Inventario multi-sucursal con productos por peso
- Módulo de taller con seguimiento de trabajos
- Punto de venta con múltiples formas de pago
- Control de caja diario
- Gestión de créditos semanales
- Facturación electrónica

**Duración estimada:** 3-4 semanas (15-20 días hábiles)

**Stack tecnológico:**
- Backend: PHP 8.2
- Base de datos: MySQL/MariaDB
- Frontend: HTML5 + Bootstrap 5 + JavaScript Vanilla
- Hosting: Hostinger Business Plan
- Herramientas: XAMPP (desarrollo local), VS Code, HeidiSQL, FileZilla

---

## 📅 CRONOGRAMA GENERAL

| Fase | Descripción | Duración | Fechas Estimadas |
|------|-------------|----------|------------------|
| **Fase 0** | Planificación y Diseño | 3-4 días | Ene 20-23 |
| **Fase 1** | Arquitectura y Base de Datos | 2-3 días | Ene 24-26 |
| **Fase 2** | Backend - Módulos Core | 5-7 días | Ene 27 - Feb 4 |
| **Fase 3** | APIs y Endpoints | 2-3 días | Feb 5-7 |
| **Fase 4** | Frontend - Estructura Base | 2-3 días | Feb 8-10 |
| **Fase 5** | Frontend - Módulos Funcionales | 5-6 días | Feb 11-17 |
| **Fase 6** | Integraciones Especiales | 3-4 días | Feb 18-21 |
| **Fase 7** | Pruebas y Refinamiento | 4-5 días | Feb 22-27 |
| **Fase 8** | Deployment y Capacitación | 2-3 días | Feb 28 - Mar 2 |

**Fecha de entrega estimada:** Primera semana de marzo 2026

---

## 📂 ESTRUCTURA DEL PROYECTO

### Organización en tu PC

```
C:\Users\[TuNombre]\Proyectos\joyeria-torre-fuerte\
├── /docs/
│   ├── requerimientos.md (✓ Ya tienes)
│   ├── propuesta.md
│   ├── /diseño/
│   │   ├── diagrama-er.png
│   │   ├── wireframes/
│   │   └── mockups/
│   └── /manuales/
├── /database/
│   ├── schema.sql
│   ├── seed.sql
│   └── /migraciones/
├── /src/ (código que copiarás a htdocs)
└── README.md
```

### Repositorio GitHub

- **Nombre:** `joyeria-torre-fuerte`
- **Visibilidad:** Privado
- **Ramas principales:**
  - `main` - Código en producción
  - Ramas por fase según metodología

---

## 🔄 FASE 0: PLANIFICACIÓN Y DISEÑO

**Duración:** 3-4 días  
**Rama Git:** `fase-0-planificacion`  
**Objetivo:** Tener TODO planificado antes de escribir código

### Día 1: Análisis y Documentación

**Actividades:**
1. ✅ Revisar formulario completado (Ya hecho)
2. Crear documento de requerimientos formales
3. Identificar entidades principales del sistema
4. Definir módulos del sistema
5. Priorizar funcionalidades (Crítico/Importante/Deseable)

**Entregables:**
- [ ] `docs/requerimientos-formales.md`
- [ ] `docs/modulos-del-sistema.md`
- [ ] `docs/priorizacion-funcionalidades.md`

**Prompt para Claude (Día 1):**
```
Claude, voy a desarrollar un sistema de gestión para una joyería con 2 sucursales. 

He completado el levantamiento de requisitos con el cliente. Te comparto 
toda la información recopilada:

[Adjuntar: Formulario_Levantamiento_Requisitos.md]

Necesito que me ayudes a:
1. Identificar todas las entidades principales del sistema
2. Definir los módulos que necesito desarrollar
3. Priorizar funcionalidades en: Críticas, Importantes y Deseables

Guíame paso a paso para documentar todo esto correctamente antes de 
empezar a diseñar la base de datos.
```

---

### Día 2: Diseño de Base de Datos

**Actividades:**
1. Listar todas las tablas necesarias
2. Definir campos de cada tabla
3. Establecer relaciones entre tablas
4. Normalizar hasta 3ra forma normal
5. Crear diagrama ER en Draw.io/Excalidraw
6. Validar diseño con Claude

**Entregables:**
- [ ] Lista de tablas con campos
- [ ] Diagrama ER completo
- [ ] `docs/diseño/diagrama-er.png`
- [ ] Diseño validado por Claude

**Tablas identificadas preliminarmente:**
- usuarios
- sucursales
- categorias
- productos
- inventario (por sucursal y producto)
- materias_primas
- clientes
- proveedores
- ventas
- detalle_ventas
- formas_pago_venta (múltiples formas de pago por venta)
- creditos_clientes
- abonos_creditos
- trabajos_taller
- transferencias_trabajo (entre empleados)
- estados_trabajo
- caja_movimientos
- caja_cierres
- precios_producto (público, mayorista, descuento, especial)
- audit_log

**Prompt para Claude (Día 2):**
```
Claude, estoy diseñando la base de datos para el sistema de joyería.

Basándome en los requisitos, he identificado las siguientes entidades:
[Lista de tablas arriba]

Necesito que me ayudes a:
1. Validar si están todas las tablas necesarias
2. Definir los campos específicos de cada tabla con tipos de datos apropiados
3. Establecer las relaciones (foreign keys)
4. Identificar campos que necesitan índices
5. Revisar normalización

Empecemos tabla por tabla. ¿Qué campos debe tener la tabla "productos"?
```

---

### Día 3: Diseño de Interfaz (Wireframes)

**Actividades:**
1. Identificar todas las pantallas del sistema
2. Crear wireframes de baja fidelidad en Excalidraw
3. Diseñar flujo de navegación
4. Validar wireframes con cliente (reunión)
5. Ajustar según feedback

**Pantallas principales a diseñar:**
- Login
- Dashboard principal
- **Inventario:**
  - Lista de productos
  - Agregar/Editar producto
  - Transferencias entre sucursales
  - Alertas de stock bajo
- **Taller:**
  - Lista de trabajos
  - Recibir nuevo trabajo
  - Transferir trabajo entre empleados
  - Entregar trabajo terminado
- **Punto de Venta:**
  - Pantalla de venta (POS)
  - Búsqueda de productos
  - Aplicar descuentos
  - Múltiples formas de pago
- **Caja:**
  - Apertura de caja
  - Registro de movimientos
  - Cierre de caja
- **Clientes:**
  - Lista de clientes
  - Ficha de cliente
  - Historial de compras
  - Créditos y abonos
- **Reportes:**
  - Ventas diarias/mensuales
  - Inventario
  - Trabajos de taller
  - Cuentas por cobrar

**Entregables:**
- [ ] Wireframes de todas las pantallas
- [ ] `docs/diseño/wireframes/` (imágenes)
- [ ] Wireframes aprobados por cliente

**Herramienta:** Excalidraw (https://excalidraw.com/)

---

### Día 4: Definición Técnica Final

**Actividades:**
1. Definir roles y permisos detallados
2. Documentar casos de uso críticos
3. Crear plan detallado de fases siguientes
4. Preparar ambiente de desarrollo

**Roles y permisos:**
```
ADMINISTRADOR (Acceso total)
- Gestionar usuarios
- Configuración del sistema
- Todos los módulos
- Todos los reportes

DUEÑO (Acceso casi total)
- Ver todos los reportes
- Gestión de inventario
- Gestión de clientes/proveedores
- No puede gestionar usuarios ni configuración

VENDEDOR
- Realizar ventas
- Ver inventario (solo lectura)
- Gestionar clientes
- Aplicar descuentos establecidos
- Ver sus propias ventas

CAJERO
- Apertura/cierre de caja
- Registrar movimientos de dinero
- Ver corte de caja

ORFEBRE (Taller)
- Ver trabajos del taller
- Actualizar estado de trabajos
- Recibir/entregar trabajos
- Transferir trabajos

PUBLICIDAD (Solo lectura)
- Ver reportes de ventas
- Ver inventario
- Ver clientes
```

**Casos de uso críticos a documentar:**
1. Registrar una venta con múltiples formas de pago
2. Recibir trabajo en taller y hacer seguimiento
3. Transferir trabajo entre empleados del taller
4. Realizar transferencia de inventario entre sucursales
5. Registrar venta a crédito semanal
6. Hacer cierre de caja diario

**Entregables:**
- [ ] `docs/roles-y-permisos.md`
- [ ] `docs/casos-de-uso.md`
- [ ] `docs/plan-de-fases-detallado.md`
- [ ] XAMPP instalado y configurado
- [ ] VS Code configurado con extensiones
- [ ] HeidiSQL instalado

---

### ✅ Checklist Fase 0

Al terminar esta fase debes tener:
- [ ] Requerimientos validados con cliente
- [ ] Base de datos diseñada completamente
- [ ] Diagrama ER exportado y validado con Claude
- [ ] Wireframes de todas las pantallas
- [ ] Cliente aprobó wireframes
- [ ] Roles y permisos definidos
- [ ] Casos de uso documentados
- [ ] Plan de fases completo
- [ ] Ambiente de desarrollo preparado
- [ ] Proyecto creado en GitHub (privado)

**SOLO cuando todo esto esté listo, pasas a Fase 1.**

---

## 🏗️ FASE 1: ARQUITECTURA Y BASE DE DATOS

**Duración:** 2-3 días  
**Rama Git:** `fase-1-arquitectura`  
**Objetivo:** Implementar toda la infraestructura base

### Actividades

**Día 1: Estructura y Configuración**
1. Crear estructura de carpetas definitiva
2. Configurar archivos base (config.php, .gitignore)
3. Implementar sistema de conexión a BD con PDO
4. Crear archivo config.example.php para Git

**Día 2: Implementación de Base de Datos**
1. Generar SQL completo de creación de BD
2. Crear base de datos en phpMyAdmin local
3. Ejecutar script de creación
4. Verificar todas las relaciones

**Día 3: Datos de Prueba**
1. Crear script de datos de prueba (seed.sql)
2. Poblar con datos realistas:
   - 2 sucursales (Los Arcos, Chinaca Central)
   - 6 usuarios (roles variados)
   - 50+ productos de prueba
   - 10+ categorías
   - 20+ clientes
   - Datos de inventario por sucursal
3. Ejecutar y verificar
4. Crear archivo test-conexion.php

### Prompt para Claude (Fase 1 - Inicio)

```
Claude, estoy en Fase 1: Arquitectura y Base de Datos.

He completado la Fase 0 con:
- Base de datos diseñada completamente
- Diagrama ER validado
- Wireframes aprobados

[Adjuntar: Diagrama ER o descripción detallada de tablas]

Necesito que me ayudes a implementar:
1. Estructura de carpetas definitiva del proyecto
2. Archivo de configuración (config.php)
3. Sistema de conexión a BD con PDO
4. Scripts SQL para crear toda la base de datos
5. Datos de prueba realistas

IMPORTANTE:
- No soy muy experto en programación
- Guíame paso a paso
- Un archivo a la vez
- Explica QUÉ crear, DÓNDE crearlo, y POR QUÉ

¿Empezamos?
```

### Estructura de carpetas a crear

```
/htdocs/joyeria-torre-fuerte/
├── index.php
├── dashboard.php
├── logout.php
├── config.php (NO subir a Git)
├── config.example.php (SÍ subir)
│
├── /assets/
│   ├── /css/
│   │   ├── bootstrap.min.css
│   │   ├── bootstrap-icons.css
│   │   └── estilos.css
│   ├── /js/
│   │   ├── bootstrap.bundle.min.js
│   │   ├── funciones.js
│   │   └── validaciones.js
│   └── /img/
│       └── logo-torre-fuerte.png
│
├── /includes/
│   ├── db.php
│   ├── funciones.php
│   ├── auth.php
│   ├── header.php
│   ├── footer.php
│   └── navbar.php
│
├── /models/
│   ├── producto.php
│   ├── cliente.php
│   ├── venta.php
│   ├── trabajo_taller.php
│   ├── caja.php
│   ├── usuario.php
│   └── inventario.php
│
├── /modules/
│   ├── /inventario/
│   ├── /ventas/
│   ├── /taller/
│   ├── /clientes/
│   ├── /proveedores/
│   ├── /caja/
│   ├── /reportes/
│   └── /configuracion/
│
├── /api/
│   ├── /productos/
│   ├── /ventas/
│   ├── /taller/
│   └── /clientes/
│
├── /uploads/
│   └── /trabajos_taller/
│
└── /logs/
    └── php-errors.log
```

### Entregables Fase 1

- [ ] Estructura completa de carpetas
- [ ] config.php funcional
- [ ] Conexión a BD implementada
- [ ] Base de datos creada completamente
- [ ] Datos de prueba cargados
- [ ] Verificación exitosa (test-conexion.php)
- [ ] Commit y push a rama fase-1-arquitectura

---

## ⚙️ FASE 2: BACKEND - LÓGICA DE NEGOCIO

**Duración:** 5-7 días  
**Objetivo:** Implementar toda la lógica sin preocuparse por vistas

### Sub-Fase 2.1: Sistema de Autenticación (1 día)
**Rama:** `fase-2.1-autenticacion`

**Actividades:**
1. Implementar login.php (formulario + procesamiento)
2. Implementar logout.php
3. Crear includes/auth.php (funciones de autenticación)
4. Middleware de verificación de sesión
5. Sistema de verificación de roles

**Prompt para Claude:**
```
Claude, estoy en Fase 2.1: Sistema de Autenticación.

Ya tengo:
- Base de datos completa con tabla usuarios
- Sistema de configuración y conexión a BD

Necesito implementar:
1. Sistema de login (validar credenciales con password_verify)
2. Sistema de logout
3. Middleware para proteger páginas
4. Sistema de verificación de roles
5. Sesiones seguras

Guíame paso a paso. ¿Empezamos?
```

**Entregables:**
- [ ] login.php funcional
- [ ] logout.php
- [ ] includes/auth.php
- [ ] includes/funciones.php (helpers generales)
- [ ] Pruebas de login exitosas

---

### Sub-Fase 2.2: Backend Módulo Inventario (1-2 días)
**Rama:** `fase-2.2-backend-inventario`

**Funciones a implementar en models/producto.php:**
- obtenerProductos($pdo, $filtros)
- obtenerProductoPorId($pdo, $id)
- obtenerProductoPorCodigo($pdo, $codigo)
- crearProducto($pdo, $datos)
- actualizarProducto($pdo, $id, $datos)
- eliminarProducto($pdo, $id) // Soft delete
- buscarProductos($pdo, $termino)
- obtenerProductosBajoStock($pdo, $sucursal_id)

**Funciones en models/inventario.php:**
- obtenerInventarioPorSucursal($pdo, $sucursal_id)
- obtenerStockProducto($pdo, $producto_id, $sucursal_id)
- actualizarStock($pdo, $producto_id, $sucursal_id, $cantidad, $tipo)
- transferirInventario($pdo, $producto_id, $sucursal_origen, $sucursal_destino, $cantidad)
- registrarMovimientoInventario($pdo, $datos)

**Funciones en models/categoria.php:**
- CRUD completo de categorías

**Funciones en models/materia_prima.php:**
- CRUD de materias primas para taller

**Prompt para Claude:**
```
Claude, estoy en Fase 2.2: Backend del Módulo Inventario.

Contexto:
- Sistema de autenticación funcional
- Base de datos tiene tablas: productos, categorias, inventario, materias_primas

Características especiales de este inventario:
- Productos con múltiples precios (público, mayorista, descuento, especial)
- Productos por peso (oro/plata por gramo)
- Control de inventario por sucursal
- Transferencias entre sucursales
- Alertas cuando stock < 5 unidades

Necesito implementar todas las funciones backend para:
1. Gestión de productos
2. Gestión de inventario por sucursal
3. Categorías
4. Materias primas

TODO debe tener:
- Validaciones completas
- Manejo de errores robusto
- Prepared statements siempre
- Transacciones donde corresponda

NO necesito vistas todavía, solo la lógica.

¿Empezamos con el modelo de Productos?
```

**Entregables:**
- [ ] models/producto.php completo
- [ ] models/inventario.php completo
- [ ] models/categoria.php completo
- [ ] models/materia_prima.php completo
- [ ] Archivos de prueba que validen cada función

---

### Sub-Fase 2.3: Backend Módulo Taller (1-2 días)
**Rama:** `fase-2.3-backend-taller`

**Este es un módulo CRÍTICO para el cliente**

**Funciones en models/trabajo_taller.php:**
- crearTrabajo($pdo, $datos)
- obtenerTrabajos($pdo, $filtros)
- obtenerTrabajoPorId($pdo, $id)
- actualizarEstadoTrabajo($pdo, $id, $estado)
- transferirTrabajo($pdo, $trabajo_id, $empleado_origen, $empleado_destino)
- entregarTrabajo($pdo, $trabajo_id)
- obtenerTrabajosProximosEntrega($pdo, $dias)
- obtenerHistorialTransferencias($pdo, $trabajo_id)
- obtenerTrabajosPorEmpleado($pdo, $empleado_id)

**Estados de trabajo:**
- Recibido
- En proceso
- Transferido
- Completado
- Entregado
- Cancelado

**Prompt para Claude:**
```
Claude, estoy en Fase 2.3: Backend del Módulo Taller.

Este módulo es CRÍTICO para el cliente. Actualmente pierden trabajos 
porque no saben dónde están o quién los tiene.

El sistema debe:
1. Registrar trabajos con toda la info (cliente, pieza, tipo de trabajo, fechas, precios)
2. Permitir transferir trabajos entre los 3 empleados del taller
3. Mantener historial completo de quién tuvo el trabajo y cuándo
4. Alertar cuando se acerca fecha de entrega
5. Registrar anticipos y saldos

Datos a guardar por trabajo:
- Información del cliente
- Descripción detallada de la pieza
- Tipo de trabajo a realizar
- Fechas (recepción, entrega prometida, entrega real)
- Precios (total, anticipo, saldo)
- Empleado que recibe, empleado actual
- Historial de transferencias
- Estado actual

Necesito todas las funciones para manejar esto. ¿Empezamos?
```

**Entregables:**
- [ ] models/trabajo_taller.php completo
- [ ] Tabla de transferencias funcionando
- [ ] Sistema de estados implementado
- [ ] Pruebas de todo el flujo

---

### Sub-Fase 2.4: Backend Módulo Ventas (1-2 días)
**Rama:** `fase-2.4-backend-ventas`

**Características especiales:**
- Múltiples formas de pago por venta
- Ventas a crédito semanal
- Descuentos (monto fijo)
- Actualización automática de inventario
- Transacciones para consistencia

**Funciones en models/venta.php:**
- crearVenta($pdo, $datos) // Con transacción
- obtenerVentas($pdo, $filtros)
- obtenerVentaPorId($pdo, $id)
- anularVenta($pdo, $id)
- obtenerVentasDelDia($pdo, $sucursal_id)
- obtenerVentasPorVendedor($pdo, $vendedor_id, $fecha_inicio, $fecha_fin)

**Funciones en models/credito.php:**
- registrarCreditoVenta($pdo, $venta_id, $cliente_id, $total, $plazo_semanal)
- registrarAbono($pdo, $credito_id, $monto)
- obtenerCreditosCliente($pdo, $cliente_id)
- obtenerCreditosPendientes($pdo)
- obtenerCreditosVencidos($pdo)

**Funciones en models/cliente.php:**
- CRUD de clientes
- obtenerHistorialCompras($pdo, $cliente_id)
- obtenerClientesMayoristas($pdo)
- calcularTotalComprado($pdo, $cliente_id)

**Prompt para Claude:**
```
Claude, estoy en Fase 2.4: Backend Módulo de Ventas.

Este módulo tiene complejidades:

1. MÚLTIPLES FORMAS DE PAGO POR VENTA
   - Una venta puede pagarse con efectivo + tarjeta + transferencia
   - Necesito tabla formas_pago_venta que registre cada forma de pago

2. VENTAS A CRÉDITO SEMANAL
   - Cliente compra y paga en cuotas semanales
   - Necesito registrar crédito y sus abonos
   - Alertas de cuotas vencidas

3. DESCUENTOS DE MONTO FIJO
   - Se aplica monto fijo de descuento, no porcentaje

4. ACTUALIZACIÓN DE INVENTARIO
   - Al vender, descontar del inventario de la sucursal
   - Usar transacciones para que si falla algo, todo se revierta

Necesito implementar todo esto. ¿Empezamos?
```

**Entregables:**
- [ ] models/venta.php con transacciones
- [ ] models/credito.php completo
- [ ] models/cliente.php completo
- [ ] Sistema de múltiples formas de pago
- [ ] Pruebas del flujo completo de venta

---

### Sub-Fase 2.5: Backend Módulo Caja (1 día)
**Rama:** `fase-2.5-backend-caja`

**Funciones en models/caja.php:**
- abrirCaja($pdo, $usuario_id, $sucursal_id, $monto_inicial)
- registrarMovimiento($pdo, $tipo, $concepto, $monto, $caja_id)
- cerrarCaja($pdo, $caja_id, $monto_final)
- obtenerCajaActual($pdo, $sucursal_id)
- obtenerMovimientosCaja($pdo, $caja_id)
- calcularTotalesCaja($pdo, $caja_id)
- obtenerHistorialCierres($pdo, $filtros)

**Tipos de movimientos a registrar:**
- Ventas (ingreso automático)
- Ingresos de reparaciones
- Gastos
- Anticipo de trabajos
- Abonos a créditos
- Anticipo mercadería apartada
- Pagos a proveedores
- Compras de material
- Alquileres
- Salarios

**Entregables:**
- [ ] models/caja.php completo
- [ ] Flujo de apertura/cierre funcionando
- [ ] Registro de todos los tipos de movimientos
- [ ] Cálculo de diferencias (esperado vs real)

---

### Sub-Fase 2.6: Backend Reportes (1 día)
**Rama:** `fase-2.6-backend-reportes`

**Funciones en models/reporte.php:**
- reporteVentasDiarias($pdo, $fecha, $sucursal_id)
- reporteVentasMensuales($pdo, $mes, $año, $sucursal_id)
- reporteProductosMasVendidos($pdo, $fecha_inicio, $fecha_fin)
- reporteProductosMenosMovimiento($pdo, $fecha_inicio, $fecha_fin)
- reporteVentasPorVendedor($pdo, $fecha_inicio, $fecha_fin)
- reporteVentasPorSucursal($pdo, $fecha_inicio, $fecha_fin)
- reporteInventarioActual($pdo, $sucursal_id)
- reporteTrabajosPendientes($pdo)
- reporteTrabajosCompletados($pdo, $fecha_inicio, $fecha_fin)
- reporteCuentasPorCobrar($pdo)
- reporteGanancias($pdo, $fecha_inicio, $fecha_fin)
- reporteComparativoPeriodos($pdo, $periodo1, $periodo2)

**Entregables:**
- [ ] models/reporte.php completo
- [ ] Queries optimizadas con índices
- [ ] Pruebas de cada reporte

---

### ✅ Checklist Fase 2 Completa

- [ ] Sistema de autenticación funcional
- [ ] Módulo inventario completamente implementado
- [ ] Módulo taller completamente implementado
- [ ] Módulo ventas completamente implementado
- [ ] Módulo caja completamente implementado
- [ ] Módulo reportes completamente implementado
- [ ] TODAS las funciones probadas
- [ ] Todas las sub-fases en Git

---

## 🔌 FASE 3: APIs Y ENDPOINTS

**Duración:** 2-3 días  
**Rama:** `fase-3-apis`  
**Objetivo:** Crear endpoints AJAX para consumo del frontend

### Estructura de APIs

Todos los endpoints en `/api/` organizados por módulo.

### Endpoints necesarios

**api/productos/**
- listar.php
- buscar.php
- crear.php
- actualizar.php
- eliminar.php
- bajo_stock.php

**api/inventario/**
- por_sucursal.php
- transferir.php
- ajustar_stock.php

**api/taller/**
- listar_trabajos.php
- crear_trabajo.php
- actualizar_estado.php
- transferir_trabajo.php
- entregar_trabajo.php
- proximos_entrega.php
- historial_trabajo.php

**api/ventas/**
- crear_venta.php (el más complejo)
- listar_ventas.php
- anular_venta.php
- detalle_venta.php

**api/clientes/**
- buscar.php
- crear.php
- historial_compras.php
- creditos.php
- registrar_abono.php

**api/caja/**
- abrir.php
- registrar_movimiento.php
- cerrar.php
- estado_actual.php
- movimientos.php

**api/reportes/**
- ventas_diarias.php
- productos_vendidos.php
- inventario.php
- taller.php
- cuentas_cobrar.php

### Estándar de respuestas JSON

```json
// Éxito
{
  "success": true,
  "data": { },
  "message": "Operación exitosa"
}

// Error
{
  "success": false,
  "error": "Descripción del error",
  "code": "CODIGO_ERROR"
}
```

### Prompt para Claude

```
Claude, estoy en Fase 3: APIs y Endpoints.

Tengo completado:
- Todo el backend (modelos con funciones CRUD)
- Sistema de autenticación

Necesito crear endpoints AJAX para consumo del frontend.

Cada endpoint debe:
- Verificar autenticación y roles
- Validar datos recibidos
- Llamar a funciones del modelo correspondiente
- Retornar JSON estandarizado
- Manejar errores apropiadamente
- Usar try-catch

Lista completa de endpoints necesarios:
[Pegar lista de arriba]

Empecemos con los endpoints de productos. ¿Cómo debe ser listar.php?
```

### Herramienta de prueba

**Thunder Client** (extensión de VS Code)

Crear colección con todos los endpoints y guardar las pruebas.

### Entregables Fase 3

- [ ] Todos los endpoints creados
- [ ] Respuestas JSON estandarizadas
- [ ] Validaciones implementadas
- [ ] Pruebas en Thunder Client exitosas
- [ ] `docs/api-reference.md` documentando cada endpoint

---

## 🎨 FASE 4: FRONTEND - ESTRUCTURA BASE

**Duración:** 2-3 días  
**Rama:** `fase-4-frontend-base`  
**Objetivo:** Estructura HTML completa sin funcionalidad

### Día 1: Componentes Base

**Actividades:**
1. Descargar Bootstrap 5 y Bootstrap Icons localmente
2. Crear includes/header.php
3. Crear includes/navbar.php (con menú por roles)
4. Crear includes/footer.php
5. Crear dashboard.php con cards de estadísticas
6. Implementar diseño responsive

**Colores según preferencia del cliente:**
- Dorados/Amarillos (primarios)
- Azul/Celeste (secundarios)
- Plateado (detalles)
- Negro (texto/contraste)

**Estilo:** Tradicional/Clásico (formal)

### Día 2-3: Plantillas HTML de Módulos

Crear estructura HTML de TODAS las pantallas sin JavaScript funcional.

**modules/inventario/**
- lista.php (tabla de productos vacía)
- agregar.php (formulario)
- editar.php (formulario)
- ver.php (detalles)
- transferencias.php

**modules/taller/**
- lista_trabajos.php
- recibir_trabajo.php
- transferir_trabajo.php
- entregar_trabajo.php
- detalle_trabajo.php

**modules/ventas/**
- nueva_venta.php (POS)
- historial.php
- detalle_venta.php

**modules/caja/**
- apertura.php
- movimientos.php
- cierre.php
- historial_cierres.php

**modules/clientes/**
- lista.php
- agregar.php
- ficha_cliente.php (con historial y créditos)
- creditos.php

**modules/reportes/**
- ventas.php
- inventario.php
- taller.php
- cuentas_cobrar.php
- comparativos.php

### Prompt para Claude

```
Claude, estoy en Fase 4: Frontend - Estructura Base.

Tengo:
- Backend completo
- APIs funcionales

Ahora el frontend usando:
- Bootstrap 5 (descargado local, no CDN)
- JavaScript vanilla
- PHP para renderizar

Preferencias del cliente:
- Estilo: Tradicional/clásico (formal)
- Colores: Dorados, azul, celeste, plateado, negro
- Interfaz simple con menús organizados
- Se familiariza con: WhatsApp, apps bancarias

Necesito:
1. Estructura HTML base (header, footer, navbar)
2. Dashboard con cards de estadísticas
3. Plantillas HTML de todos los módulos (sin funcionalidad)
4. Sistema de menú que se adapte según rol

La idea es tener TODO el HTML estructurado antes de conectarlo.

¿Empezamos con header, navbar y footer?
```

### Entregables Fase 4

- [ ] includes/ completos (header, navbar, footer)
- [ ] dashboard.php con diseño
- [ ] Plantillas HTML de TODOS los módulos
- [ ] Bootstrap integrado localmente
- [ ] Diseño responsive verificado
- [ ] Navegación entre páginas funciona
- [ ] Se ve profesional y ordenado

---

## ⚡ FASE 5: FRONTEND - MÓDULOS FUNCIONALES

**Duración:** 5-6 días  
**Objetivo:** Conectar frontend con backend y hacer todo funcional

### Sub-Fase 5.1: Módulo Inventario Funcional (1 día)
**Rama:** `fase-5.1-frontend-inventario`

**Conectar:**
- Lista de productos con datos reales
- Búsqueda y filtros funcionales
- Formularios de crear/editar funcionales
- Eliminación con confirmación (SweetAlert2)
- Transferencias entre sucursales
- Alertas de stock bajo visuales

### Sub-Fase 5.2: Módulo Taller Funcional (1-2 días)
**Rama:** `fase-5.2-frontend-taller`

**Este es el módulo MÁS IMPORTANTE para el cliente**

**Implementar:**
- Lista de trabajos con filtros (pendientes, en proceso, completados)
- Formulario de recibir trabajo completo
- Sistema de transferencia entre empleados
- Historial visual de transferencias
- Entrega de trabajo con cálculo de saldo
- Alertas de trabajos próximos a entrega
- Vista de trabajos por empleado

**Usar FullCalendar o Timeline para visualizar:**
- Trabajos por fecha de entrega
- Carga de trabajo por empleado

### Sub-Fase 5.3: Módulo Ventas (POS) Funcional (1-2 días)
**Rama:** `fase-5.3-frontend-ventas`

**Este es el módulo más complejo técnicamente**

**Implementar:**
- Búsqueda de productos en tiempo real
- Carrito de compra en JavaScript
- Cálculo automático de totales
- Aplicar descuento (monto fijo)
- Selector de cliente (autocompletado)
- Múltiples formas de pago en una venta
- Opción de venta a crédito semanal
- Generar e imprimir ticket
- Actualizar inventario en tiempo real

### Sub-Fase 5.4: Módulo Caja Funcional (1 día)
**Rama:** `fase-5.4-frontend-caja`

**Implementar:**
- Apertura de caja con monto inicial
- Registro de diferentes tipos de movimientos
- Tabla de movimientos del día
- Cierre de caja con cálculo de diferencia
- Historial de cierres anteriores

### Sub-Fase 5.5: Módulo Clientes y Créditos (1 día)
**Rama:** `fase-5.5-frontend-clientes`

**Implementar:**
- CRUD de clientes
- Ficha de cliente con:
  - Datos personales
  - Historial de compras
  - Créditos activos
  - Registro de abonos
- Sistema de abonos a créditos

### Sub-Fase 5.6: Módulo Reportes (1 día)
**Rama:** `fase-5.6-frontend-reportes`

**Implementar:**
- Filtros de fecha para cada reporte
- Visualización con Chart.js
- Tablas con DataTables
- Exportación a Excel/PDF
- Reportes implementados:
  - Ventas diarias/mensuales
  - Productos más vendidos
  - Inventario actual
  - Trabajos de taller
  - Cuentas por cobrar
  - Comparativos

### Entregables Fase 5

- [ ] TODOS los módulos completamente funcionales
- [ ] Frontend conectado con backend
- [ ] Validaciones frontend implementadas
- [ ] SweetAlert2 para confirmaciones
- [ ] DataTables en tablas grandes
- [ ] Chart.js en reportes
- [ ] Sistema 100% operativo

---

## 🔧 FASE 6: INTEGRACIONES ESPECIALES

**Duración:** 3-4 días  
**Rama:** `fase-6-integraciones`  
**Objetivo:** Funcionalidades avanzadas

### Día 1-2: Generación de Tickets (FPDF)

**Implementar:**
- Ticket de venta (con logo Torre Fuerte)
- Recibo de trabajo de taller
- Comprobante de cierre de caja
- Descarga automática o vista previa

### Día 3: Sistema de Notificaciones

**Implementar:**
- Notificaciones en dashboard:
  - Stock bajo
  - Trabajos próximos a entregar
  - Créditos vencidos
- Badge visual con contador

### Día 4: Facturación (Preparación)

**Preparar estructura para:**
- Facturación electrónica futura
- Por ahora: facturas/recibos simples imprimibles
- Opción para upgrade futuro a certificación SAT

### Entregables Fase 6

- [ ] PDFs generándose correctamente
- [ ] Sistema de notificaciones funcional
- [ ] Estructura para facturación preparada

---

## 🧪 FASE 7: PRUEBAS Y REFINAMIENTO

**Duración:** 4-5 días  
**Rama:** `fase-7-pruebas`  
**Objetivo:** Sistema libre de bugs y optimizado

### Día 1-2: Pruebas Funcionales

Crear `docs/plan-de-pruebas.md` y ejecutar:

**Módulo Inventario:**
- [ ] Crear producto
- [ ] Editar producto
- [ ] Eliminar producto
- [ ] Buscar productos
- [ ] Transferir entre sucursales
- [ ] Alertas de stock bajo funcionan

**Módulo Taller:**
- [ ] Recibir trabajo
- [ ] Transferir trabajo entre empleados
- [ ] Actualizar estado
- [ ] Entregar trabajo
- [ ] Alertas de fechas próximas
- [ ] Historial de transferencias completo

**Módulo Ventas:**
- [ ] Venta simple
- [ ] Venta con múltiples productos
- [ ] Venta con múltiples formas de pago
- [ ] Venta a crédito
- [ ] Aplicar descuento
- [ ] Inventario se actualiza
- [ ] Ticket se genera

**Módulo Caja:**
- [ ] Apertura de caja
- [ ] Registrar movimientos
- [ ] Cierre con cálculo correcto
- [ ] No se puede abrir si ya hay caja abierta

**Módulo Clientes:**
- [ ] CRUD completo
- [ ] Registrar abono a crédito
- [ ] Historial se actualiza

**Módulo Reportes:**
- [ ] Todos los reportes generan datos
- [ ] Exportación a Excel funciona
- [ ] Gráficas se visualizan

### Día 3: Pruebas de Seguridad

- [ ] Intentar acceder sin login → Redirige a login
- [ ] Intentar acceder con rol incorrecto → Acceso denegado
- [ ] Intentar SQL injection en campos → Bloqueado
- [ ] Passwords están hasheados en BD
- [ ] Sesiones expiran correctamente
- [ ] HTTPS funciona (en producción)

### Día 4: Pruebas de Usabilidad

- [ ] Sistema funciona en Chrome
- [ ] Sistema funciona en Firefox
- [ ] Sistema funciona en Edge
- [ ] Responsive en tablet
- [ ] Responsive en móvil
- [ ] Mensajes de error son claros
- [ ] Flujo de navegación es intuitivo

### Día 5: Optimización

**Actividades:**
1. Revisar queries lentas y optimizar
2. Agregar índices faltantes en BD
3. Comprimir assets (CSS, JS)
4. Optimizar imágenes
5. Agregar loading indicators
6. Mejorar mensajes de validación

### Pruebas con Cliente

**Agenda sesión de 2-3 horas con el cliente:**
1. Demostrar cada módulo
2. Cliente prueba el sistema
3. Anotar feedback y bugs
4. Corregir inmediatamente lo crítico
5. Agendar próxima sesión de ajustes

### Entregables Fase 7

- [ ] Plan de pruebas completado
- [ ] Todos los bugs críticos corregidos
- [ ] Sistema optimizado
- [ ] Cliente probó y dio feedback
- [ ] Ajustes del cliente implementados
- [ ] `docs/bugs-resueltos.md`

---

## 🚀 FASE 8: DEPLOYMENT Y CAPACITACIÓN

**Duración:** 2-3 días  
**Rama:** `fase-8-deployment`  
**Objetivo:** Sistema en producción y cliente capacitado

### Día 1: Preparación y Deploy

**Actividades:**
1. Merge de todas las ramas a main
2. Configurar para producción:
   - ENVIRONMENT = 'production'
   - Cambiar URLs a dominio real
   - Deshabilitar display_errors
3. Crear base de datos en Hostinger
4. Subir archivos con FileZilla
5. Configurar config.php en servidor
6. Ejecutar schema.sql en BD producción
7. Crear usuarios reales del cliente
8. Verificar que todo funciona

**Checklist técnico:**
- [ ] Dominio apuntando correctamente
- [ ] SSL activo (HTTPS)
- [ ] Base de datos creada
- [ ] Archivos subidos
- [ ] config.php configurado
- [ ] Permisos de carpetas correctos
- [ ] /uploads/ con permisos de escritura
- [ ] /logs/ con permisos de escritura
- [ ] Todo funciona en producción

### Día 2: Capacitación

**Sesión 1 (2-3 horas): Personal de Taller y Ventas**

**Agenda:**
1. Login y navegación básica (15 min)
2. Módulo de Taller (45 min):
   - Recibir trabajo
   - Transferir entre empleados
   - Entregar trabajo
   - Ver trabajos pendientes
3. Módulo de Ventas (45 min):
   - Realizar venta simple
   - Venta con múltiples formas de pago
   - Venta a crédito
   - Imprimir ticket
4. Módulo de Inventario (30 min):
   - Buscar productos
   - Ver stock
5. Preguntas y práctica (15 min)

**Sesión 2 (2-3 horas): Administrador/Dueño**

**Agenda:**
1. Repaso de módulos operativos (30 min)
2. Módulo de Caja (30 min):
   - Apertura
   - Movimientos
   - Cierre
3. Módulo de Clientes (20 min):
   - Gestión de mayoristas
   - Créditos y abonos
4. Módulo de Reportes (30 min):
   - Ventas
   - Inventario
   - Taller
   - Exportaciones
5. Configuración y Usuarios (20 min):
   - Crear usuarios
   - Asignar roles
6. Respaldo y seguridad (10 min)
7. Preguntas finales (10 min)

**Grabar ambas sesiones para referencia futura**

### Día 3: Videos Tutoriales

**Crear videos cortos (5-10 min cada uno):**
- [ ] Cómo hacer una venta
- [ ] Cómo recibir trabajo en taller
- [ ] Cómo transferir trabajo
- [ ] Cómo cerrar caja
- [ ] Cómo generar reportes
- [ ] Cómo gestionar clientes mayoristas
- [ ] Cómo registrar abono a crédito

**Herramienta:** OBS Studio o Loom

**Subir a:** YouTube (no listado) o Google Drive del cliente

### Entrega Formal

**Documentos a entregar:**
- [ ] Acta de entrega firmada
- [ ] Credenciales de acceso:
  - Panel Hostinger
  - Base de datos
  - Usuario administrador del sistema
- [ ] Links a videos tutoriales
- [ ] Documento de soporte:
  - Cómo contactarte
  - Horarios de soporte
  - Qué incluye el soporte

**Cobro final:**
- [ ] 50% final = Q750
- [ ] Factura/recibo entregado

### Entregables Fase 8

- [ ] Sistema 100% funcional en producción
- [ ] Cliente capacitado
- [ ] Videos tutoriales creados
- [ ] Acta de entrega firmada
- [ ] Pago final recibido
- [ ] Soporte post-entrega iniciado

---

## 📊 CONTROL DE PROYECTO

### Reuniones con Cliente

**Fase 0 (Diseño):**
- Reunión 1: Validar wireframes (1 hora)

**Fase 7 (Pruebas):**
- Reunión 2: Pruebas con cliente (2-3 horas)
- Reunión 3: Ajustes finales (1 hora)

**Fase 8 (Capacitación):**
- Reunión 4: Capacitación personal (2-3 horas)
- Reunión 5: Capacitación administrador (2-3 horas)
- Reunión 6: Entrega formal (30 min)

### Comunicación

**WhatsApp:**
- Updates al final de cada fase
- Screenshots de avances
- Avisar si hay retrasos

**Email:**
- Documentos formales
- Links a videos
- Credenciales de acceso

### Documentación del Proyecto

**Mantener actualizados:**
- [ ] README.md
- [ ] docs/requerimientos.md
- [ ] docs/manual-tecnico.md
- [ ] docs/cambios.md (log de cambios)

---

## 🎯 HITOS CLAVE

| Fecha | Hito | Validación |
|-------|------|------------|
| Ene 23 | Fase 0 completa | Cliente aprueba wireframes |
| Ene 26 | Base de datos lista | Pruebas de conexión exitosas |
| Feb 4 | Backend completo | Todas las funciones probadas |
| Feb 7 | APIs listas | Pruebas en Thunder Client exitosas |
| Feb 10 | Frontend base | Todas las pantallas visibles |
| Feb 17 | Sistema funcional | Todos los módulos operativos |
| Feb 21 | Integraciones completas | PDFs y notificaciones funcionan |
| Feb 27 | Pruebas finalizadas | Cliente probó y aprobó |
| Mar 2 | En producción | Sistema live y cliente capacitado |

---

## 🚨 RIESGOS Y MITIGACIÓN

| Riesgo | Probabilidad | Impacto | Mitigación |
|--------|--------------|---------|------------|
| Cliente tarda en aprobar wireframes | Media | Bajo | Dar plazo de 3 días, continuar si no responde |
| Módulo taller más complejo de lo pensado | Alta | Alto | Asignar 2 días extra en planning |
| Problemas con facturación electrónica | Media | Medio | Implementar básica ahora, upgrade después |
| Retrasos en feedback del cliente | Alta | Medio | Agendar reuniones con anticipación |
| Bugs en producción | Media | Alto | Fase 7 exhaustiva, soporte post-entrega |

---

## 💰 CONTROL DE COSTOS

**Inversión inicial:**
- Hostinger: Q1,432 (4 años = Q30/mes)
- Dominio año 1: Q89
- **Total:** Q1,521

**Ingreso del proyecto:**
- 50% inicial: Q750 ✓ (Ya recupera la inversión)
- 50% final: Q750
- **Total desarrollo:** Q1,500

**Mensualidades:**
- Q150/mes del cliente
- Costo: Q12/mes (hosting + dominio)
- **Ganancia mensual:** Q138/mes

**ROI Primer año:**
- Ingresos totales: Q1,500 + (Q150 × 12) = Q3,300
- Costos totales: Q1,521 + (Q12 × 12) = Q1,665
- **Ganancia neta año 1:** Q1,635
- **ROI:** 98%

---

## ✅ CHECKLIST GENERAL DEL PROYECTO

### Antes de empezar
- [x] Formulario de requisitos completo
- [ ] Proyecto en GitHub creado
- [ ] XAMPP instalado y configurado
- [ ] VS Code configurado
- [ ] HeidiSQL instalado

### Fase 0
- [ ] Requerimientos documentados
- [ ] BD diseñada y validada
- [ ] Wireframes aprobados
- [ ] Plan de fases detallado

### Fase 1
- [ ] Estructura de carpetas
- [ ] BD creada con datos de prueba
- [ ] Conexión funcionando

### Fase 2
- [ ] Autenticación
- [ ] Inventario backend
- [ ] Taller backend
- [ ] Ventas backend
- [ ] Caja backend
- [ ] Reportes backend

### Fase 3
- [ ] Todos los endpoints
- [ ] Pruebas en Thunder Client

### Fase 4
- [ ] Componentes base
- [ ] Plantillas HTML

### Fase 5
- [ ] Todos los módulos funcionales

### Fase 6
- [ ] PDFs
- [ ] Notificaciones

### Fase 7
- [ ] Todas las pruebas
- [ ] Cliente probó

### Fase 8
- [ ] En producción
- [ ] Cliente capacitado
- [ ] Entrega formal

---

## 📝 NOTAS FINALES

**Recuerda:**
1. **Un chat de Claude por cada fase** - No mezcles fases
2. **Una rama de Git por cada fase** - Organización clara
3. **Probar constantemente** - No acumules código sin probar
4. **Commits frecuentes** - Después de cada avance significativo
5. **Documentar mientras avanzas** - No dejes para el final
6. **Comunicar con cliente** - Updates regulares

**Prioridades si hay retrasos:**
1. Módulo Taller (CRÍTICO para cliente)
2. Módulo Ventas (POS)
3. Módulo Caja
4. Módulo Inventario
5. Módulo Clientes
6. Módulo Reportes

**Funcionalidades opcionales si hay presión de tiempo:**
- Facturación electrónica certificada (dejar para V2)
- Notificaciones por correo
- Estadísticas avanzadas en dashboard
- Exportación a todos los formatos (priorizar Excel)

---

═══════════════════════════════════════════════════════════
          ¡VAMOS A CREAR UN SISTEMA EXCELENTE! 🚀
═══════════════════════════════════════════════════════════

**Siguiente paso:** Iniciar Fase 0 - Diseño de Base de Datos

¿Listo para empezar?