═══════════════════════════════════════════════════════════
    GUÍA METODOLÓGICA DE DESARROLLO PROFESIONAL
    Desarrollo de Sistemas Web por Fases con Claude
═══════════════════════════════════════════════════════════

## 📋 ÍNDICE

1. Filosofía de la Metodología
2. Antes de Empezar: Preparación del Proyecto
3. Fase 0: Planificación y Diseño
4. Fase 1: Arquitectura y Base de Datos
5. Fase 2: Backend - Lógica de Negocio
6. Fase 3: APIs y Endpoints
7. Fase 4: Frontend - Estructura y Componentes Base
8. Fase 5: Frontend - Módulos Funcionales
9. Fase 6: Integración y Pruebas
10. Fase 7: Refinamiento y Optimización
11. Fase 8: Deployment y Entrega
12. Gestión de Git y Ramas
13. Comunicación Efectiva con Claude
14. Checklist por Fase
15. Plantillas de Prompts

---

## 1. FILOSOFÍA DE LA METODOLOGÍA

### Por Qué Este Enfoque Funciona

Esta metodología combina dos principios fundamentales que han demostrado 
funcionar en la práctica:

**Desarrollo Incremental con Claude:**
Trabajar con Claude es más efectivo cuando construyes paso a paso, verificando 
cada pieza antes de seguir. No intentas hacer todo en un solo chat. Cada fase 
es un chat separado donde te enfocas en UNA cosa específica. Esto te permite:
- Entender completamente cada parte antes de seguir
- Debuggear problemas inmediatamente
- Aprender mientras construyes
- No sentirte abrumado

**Backend-First, Frontend-Second:**
Primero construyes toda la lógica de negocio (base de datos, validaciones, 
procesamiento de datos) y DESPUÉS te preocupas por cómo se ve. Esto funciona 
porque:
- La lógica de negocio es lo crítico del sistema
- Es más fácil cambiar diseño visual que lógica de datos
- Puedes probar la lógica sin tener frontend completo
- El cliente puede ver funcionalidad antes que diseño bonito

**Profesionalismo sin Complejidad:**
Agregamos pasos profesionales (diseño previo, validación con cliente, 
documentación) pero manteniendo la simplicidad. No adoptamos metodologías 
complejas que requieren certificaciones. Profesional no significa complicado.

### Estructura de Fases

Cada proyecto se divide en fases numeradas. Cada fase es:
- Un objetivo claro y específico
- Un chat separado con Claude
- Una rama de Git independiente
- Verificable antes de continuar

Las fases NO son lineales estrictas. A veces regresas a ajustar algo. Eso es 
normal y está bien.

---

## 2. ANTES DE EMPEZAR: PREPARACIÓN DEL PROYECTO

### 2.1 Reunión Inicial con el Cliente

Antes de escribir una sola línea de código, tienes una o más reuniones con 
el cliente para entender QUÉ necesita.

**Preguntas clave que debes hacer:**

**Sobre el negocio:**
- ¿Cuál es el problema principal que quieres resolver?
- ¿Cómo manejas esto actualmente? (papel, Excel, otro sistema)
- ¿Quiénes van a usar el sistema? (roles de usuarios)
- ¿Cuántas personas lo usarán simultáneamente?

**Sobre funcionalidades:**
- ¿Qué acciones principales necesitas hacer en el sistema?
- ¿Qué reportes necesitas ver?
- ¿Necesitas impresiones? (tickets, recetas, facturas)
- ¿Necesitas enviar correos automáticos?

**Sobre datos:**
- ¿Qué información manejas actualmente?
- ¿Tienes datos existentes que migrar?
- ¿Qué tan histórico necesitas mantener? (¿cuántos años atrás?)

**Sobre restricciones:**
- ¿Cuál es el presupuesto?
- ¿Cuándo necesitas tenerlo listo?
- ¿Tienes hosting? ¿Cuál?
- ¿Necesitas capacitación de usuarios?

**IMPORTANTE:** Graba la reunión (con permiso) o toma notas extensas. No confíes 
en tu memoria.

### 2.2 Documento de Requerimientos

Después de la reunión, creas un documento simple pero completo con TODO lo 
que el cliente necesita.

**Plantilla de Requerimientos:**

```
REQUERIMIENTOS DEL SISTEMA
Proyecto: [Nombre del Sistema]
Cliente: [Nombre del Cliente]
Fecha: [Fecha]

1. OBJETIVO GENERAL
   [Descripción de 2-3 párrafos de qué debe hacer el sistema]

2. USUARIOS DEL SISTEMA
   - Administrador: [qué puede hacer]
   - Gerente: [qué puede hacer]
   - Empleado: [qué puede hacer]
   - [otros roles...]

3. MÓDULOS PRINCIPALES
   3.1 [Nombre del Módulo] (ej: Inventario)
       - Funcionalidades:
         * [Lista de acciones que debe permitir]
       - Datos que maneja:
         * [Qué información se guarda]
       - Reportes:
         * [Qué reportes se necesitan]
   
   3.2 [Siguiente Módulo]
       [...]

4. FLUJOS DE TRABAJO IMPORTANTES
   [Describe procesos clave paso a paso, ej: "Proceso de Venta"]
   1. Usuario busca producto
   2. Agrega a carrito
   3. Confirma venta
   4. Sistema imprime ticket
   5. Sistema actualiza inventario

5. INTEGRACIONES
   - ¿Correos electrónicos? ¿A quién y cuándo?
   - ¿Impresiones? ¿De qué?
   - ¿Otras herramientas? (WhatsApp, facturación electrónica, etc.)

6. RESTRICCIONES Y CONSIDERACIONES
   - Presupuesto: [monto]
   - Fecha límite: [fecha]
   - Hosting: [Hostinger, otro]
   - Usuarios simultáneos estimados: [número]
   - Dispositivos de acceso: [PC, tablet, móvil]

7. DATOS EXISTENTES
   - ¿Hay datos actuales? [Sí/No]
   - Si sí, ¿en qué formato? [Excel, otro sistema, etc.]
   - ¿Cuántos registros aproximadamente?

8. PRIORIDADES
   [Qué es crítico vs qué puede esperar]
   CRÍTICO:
   - [Funcionalidad indispensable 1]
   - [Funcionalidad indispensable 2]
   
   IMPORTANTE:
   - [Funcionalidad necesaria pero no urgente 1]
   
   DESEABLE:
   - [Nice to have 1]
```

**Envía este documento al cliente para validación ANTES de empezar a programar.**

El cliente debe leerlo y confirmar que está correcto. Esto evita "pero yo pensé 
que..." después.

### 2.3 Propuesta y Cotización

Basándote en los requerimientos, haces una propuesta formal.

**Plantilla de Propuesta:**

```
PROPUESTA DE DESARROLLO
Proyecto: [Nombre]
Para: [Cliente]
De: [Tu Nombre/Empresa]
Fecha: [Fecha]

1. RESUMEN EJECUTIVO
   [2-3 párrafos describiendo qué vas a entregar]

2. ALCANCE DEL PROYECTO
   Lo que SÍ incluye:
   - [Lista clara de módulos y funcionalidades]
   
   Lo que NO incluye:
   - [Lista de cosas fuera del alcance]

3. ENTREGABLES
   - Sistema web completo y funcional
   - Base de datos implementada
   - Manual de usuario
   - Manual técnico (documentación de código)
   - 1 mes de soporte post-entrega
   - [otros entregables específicos]

4. CRONOGRAMA
   Fase 0-1: [Fechas] - Planificación y Base de Datos
   Fase 2-3: [Fechas] - Backend y APIs
   Fase 4-5: [Fechas] - Frontend
   Fase 6-7: [Fechas] - Pruebas y Ajustes
   Fase 8: [Fecha] - Deployment y Capacitación
   
   Entrega final: [Fecha]

5. INVERSIÓN
   Desarrollo: Q [monto]
   Hosting (primer año): Q [monto] (opcional, lo maneja el cliente)
   Dominio (primer año): Q [monto] (opcional)
   
   TOTAL: Q [monto]
   
   Forma de pago:
   - 50% al firmar contrato
   - 30% a la mitad del desarrollo
   - 20% al entregar proyecto terminado

6. SOPORTE Y MANTENIMIENTO
   Incluido:
   - 1 mes de soporte ilimitado post-entrega
   - Corrección de bugs durante ese mes
   
   Después del mes:
   - Soporte: Q [monto]/mes
   - Nuevas funcionalidades: Se cotizan por separado

7. CONDICIONES
   - Los requerimientos deben estar aprobados antes de iniciar
   - Cambios de requerimientos después de iniciado pueden tener costo adicional
   - Cliente debe proporcionar información necesaria en tiempo
   - Cliente debe revisar avances y dar feedback en máximo 3 días
```

**El cliente firma esto. Es tu contrato (puede ser más formal si quieres).**

### 2.4 Setup Inicial del Proyecto

Una vez aprobado, creas la estructura base del proyecto.

**Crear carpeta del proyecto:**
```
C:\Users\TuNombre\Proyectos\[nombre-proyecto]\
```

**Inicializar Git:**
```bash
cd C:\Users\TuNombre\Proyectos\[nombre-proyecto]
git init
git add .
git commit -m "Initial commit"
```

**Crear repositorio en GitHub:**
1. Ve a github.com
2. New Repository
3. Nombre: `[nombre-proyecto]`
4. Privado (no público, tiene datos del cliente)
5. No inicializar con README (ya tienes commits locales)
6. Copiar la URL del repo

**Conectar local con GitHub:**
```bash
git remote add origin [URL-del-repo]
git branch -M main
git push -u origin main
```

**Crear estructura base de carpetas:**
```
/proyecto/
├── /docs/                    (Documentación)
│   ├── requerimientos.md
│   ├── propuesta.md
│   ├── /diseño/
│   └── /manuales/
├── /database/                (Scripts de BD)
│   ├── schema.sql
│   └── /migraciones/
├── /src/                     (Código fuente - lo copias a htdocs después)
│   ├── index.php
│   ├── config.example.php
│   └── [resto de estructura del sistema]
└── README.md
```

**Crear README.md inicial:**
```markdown
# [Nombre del Proyecto]

Sistema de gestión para [Cliente]

## Descripción
[Breve descripción del sistema]

## Tecnologías
- PHP 8.2
- MySQL
- Bootstrap 5
- JavaScript (Vanilla)

## Instalación
[Instrucciones básicas - se completarán después]

## Estado del Proyecto
En desarrollo - Fase 0

## Desarrollador
[Tu nombre]
[Tu contacto]
```

**Crear .gitignore:**
```
# Archivos de configuración con credenciales
config.php
.env

# Logs
/logs/
*.log

# Archivos subidos por usuarios
/uploads/*
!/uploads/.gitkeep

# Cache
/cache/

# Sistema operativo
.DS_Store
Thumbs.db

# IDEs
.vscode/
.idea/
*.sublime-*

# Backups
*.backup
*.bak
```

**Commit inicial:**
```bash
git add .
git commit -m "Setup inicial del proyecto"
git push
```

---

## 3. FASE 0: PLANIFICACIÓN Y DISEÑO

**Objetivo:** Tener TODO planificado antes de escribir código. Diseños aprobados, 
base de datos diseñada, flujos definidos.

**Duración estimada:** 1-2 semanas (dependiendo del proyecto)

**Rama Git:** `fase-0-planificacion`

### 3.1 Plan de Fases del Proyecto

Creas un documento que divide el proyecto en fases específicas.

**Plantilla de Plan de Fases:**

```markdown
# PLAN DE FASES - [Nombre Proyecto]

## Resumen
Este documento define las fases de desarrollo del proyecto.

## Fase 0: Planificación y Diseño ✓
**Duración:** 1-2 semanas
**Objetivos:**
- Diseño completo de base de datos
- Mockups/wireframes de todas las pantallas
- Aprobación del cliente
- Plan detallado de fases siguientes

**Entregables:**
- Diagrama ER de la base de datos
- Mockups en Figma
- Este documento de plan de fases

## Fase 1: Arquitectura y Base de Datos
**Duración:** 3-5 días
**Objetivos:**
- Crear estructura de carpetas definitiva
- Implementar base de datos completa
- Crear archivo de configuración
- Implementar sistema de conexión a BD
- Poblar con datos de prueba

**Entregables:**
- Base de datos funcional en local
- Scripts SQL documentados
- Datos de prueba cargados

## Fase 2: Backend - Autenticación y Core
**Duración:** 5-7 días
**Objetivos:**
- Sistema de login/logout completo
- Gestión de sesiones
- Sistema de roles y permisos
- Funciones helper básicas
- Validaciones base

**Entregables:**
- Login funcional
- Sistema de sesiones robusto
- Middleware de autenticación

## Fase 3: Backend - Módulo [Nombre] (repetir por cada módulo)
**Duración:** 5-10 días por módulo
**Objetivos:**
- Implementar TODAS las funciones del módulo
- Validaciones completas
- Manejo de errores
- Funciones CRUD completas

**Entregables:**
- Archivos en /models/ del módulo
- Endpoints funcionales (aunque sin frontend)
- Tests en Thunder Client exitosos

## Fase 4: APIs y Endpoints
**Duración:** 3-5 días
**Objetivos:**
- Crear todos los endpoints AJAX necesarios
- Estandarizar respuestas JSON
- Documentar APIs
- Probar todos los endpoints

**Entregables:**
- Carpeta /api/ completa
- Documentación de endpoints
- Colección de Postman/Thunder Client

## Fase 5: Frontend - Estructura Base
**Duración:** 3-5 días
**Objetivos:**
- Implementar header/footer/navbar
- Dashboard principal
- Estructura HTML base de cada módulo
- Bootstrap integrado
- Assets organizados

**Entregables:**
- Plantillas base funcionales
- Navegación entre páginas
- Diseño responsive básico

## Fase 6: Frontend - Módulo [Nombre] (repetir por cada módulo)
**Duración:** 5-7 días por módulo
**Objetivos:**
- Implementar todas las vistas del módulo
- Conectar con backend
- Validaciones frontend
- Experiencia de usuario completa

**Entregables:**
- Vistas funcionales del módulo
- Integración frontend-backend completa
- Formularios con validación

## Fase 7: Integraciones Especiales
**Duración:** 5-10 días
**Objetivos:**
- Implementar generación de PDFs
- Sistema de correos
- Reportes y gráficas
- Exportaciones (Excel, etc.)

**Entregables:**
- PDFs generándose correctamente
- Correos enviándose
- Reportes funcionales

## Fase 8: Pruebas y Refinamiento
**Duración:** 1-2 semanas
**Objetivos:**
- Pruebas exhaustivas de cada funcionalidad
- Corrección de bugs
- Optimización de queries
- Revisión de seguridad
- Pruebas con cliente

**Entregables:**
- Lista de bugs corregidos
- Sistema completamente funcional
- Aprobación del cliente

## Fase 9: Deployment y Capacitación
**Duración:** 3-5 días
**Objetivos:**
- Subir a Hostinger
- Configurar producción
- Migrar datos reales si es necesario
- Capacitar usuarios
- Entrega formal

**Entregables:**
- Sistema en producción
- Usuarios capacitados
- Manuales entregados
- Proyecto terminado ✓
```

Este plan lo compartes con el cliente para que sepa qué esperar y cuándo.

### 3.2 Diseño de Base de Datos

Antes de escribir código, diseñas TODA la base de datos.

**Proceso:**

**Paso 1: Lista de Entidades**
Escribe en papel todas las "cosas" de las que necesitas guardar información.

Ejemplo para tienda:
- Usuarios
- Sucursales
- Categorías
- Productos
- Inventario (productos por sucursal)
- Clientes
- Proveedores
- Ventas
- Detalle de ventas (productos de cada venta)
- Compras
- Detalle de compras
- Transferencias (entre sucursales)
- Movimientos de inventario (historial)

**Paso 2: Definir Atributos**
Para cada entidad, lista QUÉ datos necesitas.

Ejemplo Producto:
- id (único)
- codigo (único, para buscar rápido)
- nombre
- descripcion
- categoria_id (relación con categorías)
- proveedor_id (relación con proveedores)
- precio_compra
- precio_venta
- imagen (ruta)
- activo (sí/no)
- fecha_creacion
- fecha_actualizacion

**Paso 3: Definir Relaciones**
Cómo se conectan las entidades entre sí.

- Producto → Categoría (muchos a uno)
- Producto → Proveedor (muchos a uno)
- Venta → Cliente (muchos a uno)
- Venta → Usuario (muchos a uno - quién hizo la venta)
- Venta → Detalle Venta (uno a muchos)
- Detalle Venta → Producto (muchos a uno)

**Paso 4: Crear Diagrama ER**
Usa Draw.io, Excalidraw, o incluso papel y lápiz.

Dibuja:
- Cajas para cada tabla
- Campos dentro de cada caja
- Líneas conectando las relaciones
- Marca las primary keys (PK) y foreign keys (FK)

**Paso 5: Normalización**
Revisa que no haya datos duplicados innecesariamente.

Pregúntate:
- ¿Este dato se repite en varias tablas? → Probablemente necesita su propia tabla
- ¿Puedo calcular este dato de otros? → No lo guardes
- ¿Tiene sentido que este dato esté aquí? → Si no, muévelo

**Paso 6: Validar con Claude**
Abre un chat con Claude, súbele tu diagrama o descripción de tablas, y pídele 
que lo revise.

**Prompt sugerido:**
```
Voy a desarrollar un sistema de [descripción]. He diseñado la base de datos 
pero quiero que la revises antes de implementarla.

[Pega aquí tu descripción de tablas o sube imagen del diagrama]

Por favor revisa:
1. ¿Están todas las entidades necesarias?
2. ¿Las relaciones son correctas?
3. ¿Hay redundancia de datos?
4. ¿Falta algún campo importante?
5. ¿Los tipos de datos son apropiados?
6. ¿Los índices están bien pensados?

Dame feedback específico y sugerencias de mejora.
```

**Paso 7: Iterar**
Claude te va a dar feedback. Ajusta tu diseño. Vuelve a validar. Repite hasta 
que Claude diga "Se ve muy bien, está listo para implementar".

**IMPORTANTE:** No pases a programar hasta que tengas el diseño de BD completo 
y validado. Cambiar la estructura de BD después de tener código es DOLOROSO.

### 3.3 Diseño Visual (Mockups/Wireframes)

Ahora diseñas cómo se verá el sistema ANTES de programarlo.

**Herramienta recomendada: Figma**

**Paso 1: Crear cuenta en Figma**
- Ve a figma.com
- Crea cuenta gratis
- Create new design file

**Paso 2: Wireframes de Baja Fidelidad**
Primero haces bocetos simples (wireframes) de cada pantalla.

No te preocupes por colores o diseño bonito. Solo estructura:
- ¿Dónde va el menú?
- ¿Dónde va la tabla de productos?
- ¿Dónde van los botones?
- ¿Qué campos tiene el formulario?

Usa rectángulos, líneas, y texto placeholder.

**Pantallas mínimas que debes diseñar:**
- Login
- Dashboard principal
- Lista de cada módulo (ej: lista de productos)
- Formulario de crear/editar de cada módulo
- Vista de detalle (ej: ver detalles de una venta)
- Pantallas de reportes principales

**Paso 3: Mostrar al Cliente**
Compartes los wireframes con el cliente.

"Mira, así se vería el sistema. ¿Esto es lo que esperabas? ¿Algo que cambiar?"

Cliente ve estructuras y da feedback ANTES de que programes.

**Paso 4: Mockups de Alta Fidelidad (opcional pero recomendado)**
Si quieres lucir más profesional, después de aprobar wireframes, haces diseños 
más detallados con:
- Colores del negocio del cliente
- Logo del cliente
- Tipografías bonitas
- Diseño visual atractivo

Esto no es obligatorio. Puedes ir directo a programar con Bootstrap y se verá 
profesional de todas formas. Pero si el cliente paga bien o quieres impresionar, 
este paso suma puntos.

**Paso 5: Exportar y Documentar**
Exporta las pantallas de Figma como imágenes PNG y guárdalas en `/docs/diseño/`.

Estas imágenes las usas como referencia mientras programas el frontend.

### 3.4 Documento de Casos de Uso

Define los flujos principales del sistema en formato simple.

**Plantilla de Caso de Uso:**

```
CASO DE USO: Registrar una Venta

Actor: Empleado (Cajero)

Precondiciones:
- El empleado está logueado
- Hay productos en inventario

Flujo Normal:
1. Empleado abre el módulo de Punto de Venta
2. Sistema muestra pantalla de venta vacía
3. Empleado busca producto por código o nombre
4. Sistema muestra resultados de búsqueda
5. Empleado selecciona producto
6. Sistema agrega producto al carrito temporal
7. Empleado ajusta cantidad si es necesario
8. Empleado repite pasos 3-7 para cada producto
9. Empleado selecciona cliente (opcional)
10. Sistema calcula total automáticamente
11. Empleado confirma venta
12. Sistema valida que haya stock suficiente
13. Sistema registra venta en BD
14. Sistema actualiza inventario (resta cantidades)
15. Sistema genera ticket de venta
16. Sistema imprime ticket (o descarga PDF)
17. Sistema muestra mensaje de éxito
18. Sistema limpia carrito para siguiente venta

Flujos Alternativos:
3a. No se encuentra el producto:
    - Sistema muestra "Producto no encontrado"
    - Empleado puede buscar otro producto
    
12a. No hay stock suficiente:
     - Sistema muestra error "Stock insuficiente de [producto]"
     - No permite confirmar venta
     - Empleado debe quitar producto o reducir cantidad

Postcondiciones:
- Venta registrada en BD
- Inventario actualizado
- Ticket generado
```

Haz esto para los 3-5 casos de uso más importantes del sistema.

¿Por qué? Porque cuando programes, sabrás EXACTAMENTE qué debe pasar en cada 
paso. No improvises.

### 3.5 Definición de Roles y Permisos

Define claramente qué puede hacer cada tipo de usuario.

**Plantilla:**

```markdown
# ROLES Y PERMISOS - [Proyecto]

## Rol: Administrador
**Descripción:** Control total del sistema

**Permisos:**
- ✓ Gestionar usuarios (crear, editar, eliminar, cambiar roles)
- ✓ Acceso a todos los módulos
- ✓ Ver todos los reportes
- ✓ Configuración del sistema
- ✓ Gestión de sucursales
- ✓ Todo lo que pueden hacer roles inferiores

## Rol: Gerente
**Descripción:** Gestión de operaciones y reportes

**Permisos:**
- ✓ Ver dashboard completo
- ✓ Gestionar inventario (agregar, editar productos)
- ✓ Gestionar clientes y proveedores
- ✓ Ver reportes completos
- ✓ Registrar compras
- ✓ Aprobar transferencias entre sucursales
- ✗ NO puede gestionar usuarios
- ✗ NO puede cambiar configuración del sistema

## Rol: Empleado (Cajero)
**Descripción:** Operación diaria de ventas

**Permisos:**
- ✓ Registrar ventas
- ✓ Ver inventario (solo lectura)
- ✓ Gestionar clientes (agregar nuevos, buscar)
- ✓ Ver reportes de sus propias ventas
- ✗ NO puede editar productos
- ✗ NO puede ver reportes completos
- ✗ NO puede registrar compras
- ✗ NO puede gestionar usuarios
```

Este documento es crítico. Cuando programes el sistema de permisos, esto es 
tu guía.

### 3.6 Checklist de Fase 0

Al terminar Fase 0, debes tener:

- [ ] Requerimientos aprobados por cliente
- [ ] Propuesta firmada y anticipo recibido
- [ ] Proyecto en Git (GitHub)
- [ ] Plan de fases completo
- [ ] Base de datos diseñada y validada con Claude
- [ ] Diagrama ER exportado en /docs/
- [ ] Wireframes de todas las pantallas principales
- [ ] Cliente aprobó wireframes
- [ ] Mockups (opcional)
- [ ] Casos de uso de funcionalidades críticas
- [ ] Roles y permisos definidos
- [ ] Estructura de carpetas inicial creada

**SOLO cuando todo esto esté listo, pasas a Fase 1.**

Esto parece mucho trabajo sin código, pero te ahorra SEMANAS de rehacer cosas 
después.

---

## 4. FASE 1: ARQUITECTURA Y BASE DE DATOS

**Objetivo:** Implementar toda la infraestructura: estructura de carpetas 
definitiva, base de datos completa con datos de prueba, sistema de configuración.

**Duración estimada:** 3-5 días

**Rama Git:** `fase-1-arquitectura`

### 4.1 Preparación de la Rama

**Crear rama desde main:**
```bash
git checkout main
git pull origin main
git checkout -b fase-1-arquitectura
git push -u origin fase-1-arquitectura
```

### 4.2 Prompt Inicial para Claude

Abre un NUEVO chat en Claude (cada fase es un chat separado) y usa este prompt:

```
Hola Claude, voy a desarrollar [descripción breve del sistema]. Estoy en la 
Fase 1: Arquitectura y Base de Datos.

He completado la Fase 0 (planificación y diseño). Tengo:
- Base de datos diseñada (te la subo)
- Wireframes aprobados
- Requerimientos claros

En esta fase necesito que me ayudes a implementar:
1. Estructura de carpetas definitiva del proyecto
2. Archivo de configuración
3. Sistema de conexión a base de datos
4. Scripts SQL para crear toda la base de datos
5. Datos de prueba realistas

IMPORTANTE sobre cómo trabajar conmigo:
- No soy muy experto en programación
- Guíame paso a paso
- No me entregues 10 archivos de golpe
- Dime QUÉ crear, DÓNDE crearlo, y POR QUÉ
- Después de cada paso, espera mi confirmación antes de continuar

Empecemos. ¿Qué hago primero?
```

**Adjunta:** Tu diagrama ER o descripción detallada de la base de datos.

### 4.3 Desarrollo Paso a Paso

Claude te irá guiando. Típicamente será algo así:

**Paso 1: Estructura de Carpetas**
Claude te dirá que crees la estructura completa. Te dará comandos.

En tu carpeta de proyecto (que luego copiarás a htdocs):

```bash
mkdir assets assets/css assets/js assets/img
mkdir includes models modules api uploads logs
mkdir modules/inventario modules/ventas modules/clientes
```

Después de cada paso, respondes: "Listo, ¿qué sigue?"

**Paso 2: Archivo de Configuración**
Claude te dará el código para `config.php` y `config.example.php`.

```php
<?php
// config.example.php
// Plantilla de configuración - copiar a config.php y ajustar valores

// Entorno (development o production)
define('ENVIRONMENT', 'development');

// Base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'nombre_base_datos');
define('DB_USER', 'root');
define('DB_PASS', '');

// Rutas
define('BASE_URL', 'http://localhost/nombre-proyecto/');
define('ASSETS_URL', BASE_URL . 'assets/');

// Configuración de sesiones
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
session_start();

// Manejo de errores según entorno
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/logs/php-errors.log');
}
?>
```

Tú creas el archivo, copias el código, respondes "Creado, ¿qué sigue?"

**Paso 3: Conexión a Base de Datos**
Claude te da el código para `includes/db.php`.

Tú lo creas, confirmas.

**Paso 4: Scripts SQL**
Claude te genera TODO el SQL para crear tu base de datos basándose en tu diseño.

```sql
-- database/schema.sql
-- Script de creación de base de datos completa

CREATE DATABASE IF NOT EXISTS nombre_bd CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nombre_bd;

-- Tabla usuarios
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'gerente', 'empleado') NOT NULL,
    sucursal_id INT,
    activo BOOLEAN DEFAULT 1,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_rol (rol)
) ENGINE=InnoDB;

-- Tabla sucursales
CREATE TABLE sucursales (
    -- ...
);

-- [Todas las demás tablas...]
```

Tú copias ese SQL, lo guardas en `database/schema.sql`.

**Paso 5: Ejecutar SQL**
Abres phpMyAdmin, creas la base de datos nueva, ejecutas el script.

Confirmas a Claude: "Base de datos creada exitosamente. ¿Qué sigue?"

**Paso 6: Datos de Prueba**
Claude te genera SQL con datos de prueba realistas.

```sql
-- database/seed.sql
-- Datos de prueba

-- Insertar sucursales
INSERT INTO sucursales (nombre, direccion, telefono) VALUES
('Sucursal Central', 'Calle Principal 123', '5555-1234'),
('Sucursal Norte', 'Zona 18 Ave. 10-20', '5555-5678');

-- Insertar usuarios (password: 123456)
INSERT INTO usuarios (nombre, email, password, rol, sucursal_id) VALUES
('Admin Sistema', 'admin@test.com', '$2y$10$...', 'admin', 1),
('Juan Gerente', 'gerente@test.com', '$2y$10$...', 'gerente', 1),
('Maria Cajera', 'cajera@test.com', '$2y$10$...', 'empleado', 1);

-- [Más datos de prueba...]
```

Ejecutas esto en phpMyAdmin. Ahora tu BD tiene datos para probar.

**Paso 7: Verificación**
Claude te pide que verifiques que todo funciona.

Creas un archivo `test-conexion.php`:

```php
<?php
require_once 'config.php';
require_once 'includes/db.php';

try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
    $result = $stmt->fetch();
    echo "Conexión exitosa. Usuarios en BD: " . $result['total'];
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
```

Lo ejecutas en `http://localhost/tu-proyecto/test-conexion.php`.

Si ves "Conexión exitosa. Usuarios en BD: 3", todo está perfecto.

Respondes a Claude: "Verificación exitosa. Todo funciona."

### 4.4 Commit y Push

Una vez que TODO está funcionando:

```bash
git add .
git commit -m "Fase 1 completa: Arquitectura y base de datos implementada"
git push origin fase-1-arquitectura
```

NO hagas merge a main todavía. Cada fase queda en su rama por ahora.

### 4.5 Documentación de Fase 1

Creas (o actualizas) un archivo `docs/fase-1-notas.md`:

```markdown
# Fase 1: Arquitectura y Base de Datos

## Fecha
[Fecha de inicio] - [Fecha de fin]

## Completado
- ✓ Estructura de carpetas creada
- ✓ Archivo de configuración implementado
- ✓ Conexión a BD funcional
- ✓ Base de datos completa creada
- ✓ Datos de prueba cargados
- ✓ Verificación exitosa

## Detalles Técnicos
- Base de datos: [nombre]
- Tablas creadas: [número]
- Registros de prueba: [número aproximado]

## Archivos Creados
- config.php, config.example.php
- includes/db.php
- database/schema.sql
- database/seed.sql
- [otros archivos importantes]

## Notas
[Cualquier nota importante, decisiones tomadas, problemas encontrados]

## Próxima Fase
Fase 2: Backend - Autenticación y Core
```

---

## 5. FASE 2: BACKEND - LÓGICA DE NEGOCIO

**Objetivo:** Implementar TODA la lógica de negocio del sistema sin preocuparte 
por vistas. Funciones, validaciones, procesamiento de datos.

**Duración estimada:** Depende del proyecto. Puede ser 2-4 semanas para un 
sistema mediano.

**Estrategia:** Esta fase normalmente se subdivide en sub-fases, una por módulo.

### 5.1 Sub-Fase 2.1: Sistema de Autenticación

**Rama Git:** `fase-2.1-autenticacion`

**Objetivo:** Login, logout, sesiones, middleware de autenticación.

**Prompt para Claude:**

```
Claude, estoy en Fase 2.1: Sistema de Autenticación.

Ya tengo (de Fase 1):
- Base de datos completa con tabla usuarios
- Sistema de configuración y conexión a BD

Necesito que me ayudes a implementar:
1. Sistema de login completo (validar credenciales, crear sesión)
2. Sistema de logout (destruir sesión)
3. Middleware para proteger páginas (verificar si está logueado)
4. Sistema de verificación de roles/permisos
5. Recuperación de contraseña (opcional pero deseable)

Recuerda: Guíame paso a paso, un archivo a la vez.

¿Por dónde empezamos?
```

Claude te guiará a crear:
- `login.php` (formulario y procesamiento)
- `logout.php`
- `includes/auth.php` (funciones de autenticación)
- `includes/funciones.php` (helpers generales)

**Flujo típico:**
1. Claude te da código para `includes/funciones.php` con helpers básicos
2. Tú lo creas, confirmas
3. Claude te da código para `includes/auth.php`
4. Tú lo creas, confirmas
5. Claude te da código para `login.php`
6. Tú lo creas, pruebas que funcione
7. Claude te da código para `logout.php`
8. Pruebas todo el flujo

**Verificación:**
Pruebas login con los usuarios de prueba que creaste en Fase 1.

**Commit:**
```bash
git add .
git commit -m "Sub-fase 2.1 completa: Sistema de autenticación funcional"
git push origin fase-2.1-autenticacion
```

### 5.2 Sub-Fase 2.2: Backend - Módulo Inventario

**Rama Git:** `fase-2.2-backend-inventario`

**Objetivo:** Todas las funciones CRUD y lógica del módulo de inventario.

**Prompt para Claude:**

```
Claude, estoy en Fase 2.2: Backend del Módulo Inventario.

Contexto:
- Sistema de autenticación ya funcional
- Base de datos tiene tablas: productos, categorias, inventario, proveedores

Necesito implementar las funciones backend para gestionar inventario:

FUNCIONES NECESARIAS:
1. Productos:
   - Crear producto
   - Editar producto
   - Eliminar producto (soft delete)
   - Listar productos (con filtros y paginación)
   - Buscar producto por código o nombre
   - Ver detalles de producto

2. Categorías:
   - CRUD completo de categorías

3. Inventario:
   - Ver stock por sucursal
   - Ajustar stock manualmente
   - Historial de movimientos

4. Proveedores:
   - CRUD completo de proveedores

IMPORTANTE:
- Todo debe tener validaciones completas
- Manejo de errores robusto
- Prepared statements siempre
- Funciones deben estar en /models/producto.php, /models/categoria.php, etc.
- NO necesito vistas todavía, solo la lógica

¿Empezamos con el modelo de Productos?
```

**Desarrollo:**

Claude irá paso a paso. Típicamente:

1. **Archivo `models/producto.php`:**
   Claude te dará todas las funciones para gestionar productos.
   
   ```php
   <?php
   // models/producto.php
   
   function obtenerProductos($pdo, $filtros = []) {
       // Código para listar productos con filtros opcionales
   }
   
   function obtenerProductoPorId($pdo, $id) {
       // Código para obtener un producto específico
   }
   
   function crearProducto($pdo, $datos) {
       // Validar datos
       // Insertar en BD
       // Retornar resultado
   }
   
   function actualizarProducto($pdo, $id, $datos) {
       // Validar datos
       // Actualizar en BD
       // Retornar resultado
   }
   
   function eliminarProducto($pdo, $id) {
       // Soft delete (activo = 0)
       // Retornar resultado
   }
   
   function buscarProductos($pdo, $termino) {
       // Búsqueda por nombre o código
   }
   ?>
   ```

2. **Tú creas el archivo, copias el código**

3. **Claude te da el siguiente archivo:** `models/categoria.php`

4. **Repites el proceso**

5. **Pruebas con archivos temporales:**
   Para verificar que funciona, creas archivos de prueba como:
   
   ```php
   <?php
   // test-productos.php
   require_once 'config.php';
   require_once 'includes/db.php';
   require_once 'models/producto.php';
   
   // Probar crear producto
   $datos = [
       'codigo' => 'TEST001',
       'nombre' => 'Producto de Prueba',
       'precio' => 100.00,
       'categoria_id' => 1,
       'stock' => 50
   ];
   
   $resultado = crearProducto($pdo, $datos);
   
   if ($resultado['success']) {
       echo "Producto creado con ID: " . $resultado['id'];
   } else {
       echo "Error: " . $resultado['error'];
   }
   
   // Probar listar
   $productos = obtenerProductos($pdo);
   echo "<pre>";
   print_r($productos);
   echo "</pre>";
   ?>
   ```
   
   Ejecutas este archivo y verificas que funcione.

6. **Una vez verificado todo el módulo, commit:**
   ```bash
   git add .
   git commit -m "Sub-fase 2.2 completa: Backend módulo inventario"
   git push origin fase-2.2-backend-inventario
   ```

### 5.3 Sub-Fase 2.3: Backend - Módulo Ventas

**Rama Git:** `fase-2.3-backend-ventas`

**Similar a la anterior pero para el módulo de ventas.**

Archivos que crearás:
- `models/venta.php`
- `models/cliente.php`

**Complejidad adicional: Transacciones**

Las ventas requieren transacciones porque afectan múltiples tablas:

```php
<?php
// models/venta.php

function registrarVenta($pdo, $datos) {
    try {
        $pdo->beginTransaction();
        
        // 1. Insertar venta
        $stmt = $pdo->prepare("INSERT INTO ventas (cliente_id, usuario_id, sucursal_id, total) VALUES (?, ?, ?, ?)");
        $stmt->execute([...]);
        $venta_id = $pdo->lastInsertId();
        
        // 2. Insertar detalles
        foreach($datos['items'] as $item) {
            $stmt = $pdo->prepare("INSERT INTO detalle_ventas ...");
            $stmt->execute([...]);
            
            // 3. Actualizar inventario
            $stmt = $pdo->prepare("UPDATE inventario SET cantidad = cantidad - ? WHERE ...");
            $stmt->execute([...]);
        }
        
        $pdo->commit();
        return ['success' => true, 'venta_id' => $venta_id];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'error' => $e->getMessage()];
    }
}
?>
```

### 5.4 Repetir para Cada Módulo

Creas una sub-fase para cada módulo del sistema:
- Fase 2.4: Backend - Compras
- Fase 2.5: Backend - Reportes
- Fase 2.6: Backend - Configuración
- etc.

**Cada uno en su propia rama.**

### 5.5 Pruebas del Backend Completo

Al terminar todas las sub-fases de backend, haces pruebas integrales:

1. **Creas una carpeta `/tests/` con archivos de prueba para cada módulo**

2. **Verificas que todas las funciones funcionan correctamente**

3. **Documentas cualquier comportamiento importante**

---

## 6. FASE 3: APIs Y ENDPOINTS

**Objetivo:** Crear todos los endpoints AJAX que el frontend usará. 
Estandarizar respuestas JSON.

**Duración estimada:** 3-5 días

**Rama Git:** `fase-3-apis`

### 6.1 Estructura de APIs

Todos los endpoints van en `/api/`.

Cada endpoint es un archivo PHP que:
1. Recibe datos (GET, POST, JSON)
2. Valida
3. Llama a funciones del modelo
4. Retorna JSON estandarizado

**Estructura de respuesta estándar:**

```json
{
  "success": true,
  "data": { },
  "message": "Operación exitosa"
}
```

O en caso de error:

```json
{
  "success": false,
  "error": "Descripción del error",
  "code": "CODIGO_ERROR"
}
```

### 6.2 Prompt para Claude

```
Claude, estoy en Fase 3: APIs y Endpoints.

Tengo completado:
- Todo el backend (modelos con funciones CRUD)
- Sistema de autenticación

Necesito crear los endpoints AJAX para que el frontend pueda consumir. 

Endpoints necesarios:
1. /api/productos/listar.php
2. /api/productos/buscar.php
3. /api/productos/crear.php
4. /api/productos/actualizar.php
5. /api/productos/eliminar.php
6. /api/ventas/crear.php
7. [lista completa de endpoints necesarios]

Cada endpoint debe:
- Verificar autenticación
- Validar datos recibidos
- Llamar a funciones del modelo correspondiente
- Retornar JSON estandarizado
- Manejar errores apropiadamente

Empecemos con el endpoint de listar productos. ¿Cómo debe ser?
```

### 6.3 Ejemplo de Endpoint

```php
<?php
// api/productos/listar.php

header('Content-Type: application/json');
session_start();

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'error' => 'No autenticado',
        'code' => 'NOT_AUTHENTICATED'
    ]);
    exit;
}

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../models/producto.php';

try {
    // Recibir filtros opcionales
    $filtros = [
        'categoria' => $_GET['categoria'] ?? null,
        'busqueda' => $_GET['busqueda'] ?? null,
        'activo' => $_GET['activo'] ?? 1
    ];
    
    // Llamar al modelo
    $productos = obtenerProductos($pdo, $filtros);
    
    // Retornar resultado
    echo json_encode([
        'success' => true,
        'data' => $productos,
        'total' => count($productos)
    ]);
    
} catch (Exception $e) {
    error_log("Error en api/productos/listar.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Error al obtener productos',
        'code' => 'DATABASE_ERROR'
    ]);
}
?>
```

### 6.4 Pruebas con Thunder Client

Después de crear cada endpoint, lo pruebas con Thunder Client (extensión de VS Code).

**Ejemplo de prueba:**

1. Abres Thunder Client en VS Code
2. New Request
3. GET: `http://localhost/tu-proyecto/api/productos/listar.php`
4. Send
5. Verificas que retorne JSON correcto

**Guardas las pruebas** en una colección de Thunder Client para reutilizar.

### 6.5 Documentación de APIs

Creas `docs/api-reference.md`:

```markdown
# Referencia de APIs

## Productos

### Listar Productos
**Endpoint:** GET `/api/productos/listar.php`

**Parámetros (opcionales):**
- `categoria` (int): Filtrar por categoría
- `busqueda` (string): Buscar por nombre
- `activo` (boolean): Solo productos activos (default: 1)

**Respuesta exitosa:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nombre": "Producto A",
      "precio": 100.00,
      ...
    }
  ],
  "total": 1
}
```

**Errores posibles:**
- `NOT_AUTHENTICATED`: No está logueado
- `DATABASE_ERROR`: Error de base de datos

---

### Buscar Producto
[...]

```

### 6.6 Commit

```bash
git add .
git commit -m "Fase 3 completa: APIs y endpoints implementados"
git push origin fase-3-apis
```

---

## 7. FASE 4: FRONTEND - ESTRUCTURA Y COMPONENTES BASE

**Objetivo:** Crear la estructura visual base del sistema (header, footer, navbar, 
dashboard) y las plantillas HTML de cada módulo SIN la funcionalidad todavía.

**Duración estimada:** 3-5 días

**Rama Git:** `fase-4-frontend-base`

### 7.1 Prompt para Claude

```
Claude, estoy en Fase 4: Frontend - Estructura Base.

Tengo completado:
- Backend completo con funciones
- APIs funcionales

Ahora necesito el frontend. Voy a usar:
- Bootstrap 5
- JavaScript vanilla
- PHP para renderizar

Necesito que me ayudes a crear:
1. Estructura HTML base (header, footer, navbar)
2. Dashboard principal
3. Plantilla base para cada módulo (solo estructura HTML, sin funcionalidad)
4. Sistema de inclusión de archivos (includes)

La idea es tener TODO el HTML estructurado antes de conectarlo con el backend.

¿Por dónde empezamos?
```

### 7.2 Archivos que Crearás

**includes/header.php:**
```php
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?? 'Sistema de Gestión' ?></title>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>css/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>css/estilos.css">
</head>
<body>
```

**includes/navbar.php:**
```php
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">Sistema</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="modules/inventario/lista.php">Inventario</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="modules/ventas/nueva.php">Ventas</a>
                </li>
                <!-- Más items según roles -->
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <?= $_SESSION['user_name'] ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="logout.php">Cerrar Sesión</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
```

**includes/footer.php:**
```php
    <footer class="bg-light text-center py-3 mt-5">
        <p class="mb-0">&copy; <?= date('Y') ?> Sistema de Gestión. Todos los derechos reservados.</p>
    </footer>
    
    <script src="<?= ASSETS_URL ?>js/bootstrap.bundle.min.js"></script>
    <script src="<?= ASSETS_URL ?>js/funciones.js"></script>
</body>
</html>
```

**dashboard.php:**
```php
<?php
session_start();
require_once 'includes/auth.php';
verificarSesion();

$titulo = "Dashboard";
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container mt-4">
    <h1>Dashboard</h1>
    
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Ventas Hoy</h5>
                    <h2 class="card-text">Q 5,420.00</h2>
                </div>
            </div>
        </div>
        <!-- Más cards de estadísticas -->
    </div>
    
    <!-- Gráficas -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Ventas del Mes</div>
                <div class="card-body">
                    <canvas id="chartVentas"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
```

### 7.3 Plantillas de Módulos

Para cada módulo, creas la estructura HTML básica:

**modules/inventario/lista.php:**
```php
<?php
session_start();
require_once '../../includes/auth.php';
verificarSesion();

$titulo = "Inventario de Productos";
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="container mt-4">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2>Inventario de Productos</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="agregar.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nuevo Producto
            </a>
        </div>
    </div>
    
    <!-- Filtros -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="buscar" class="form-control" 
                           placeholder="Buscar producto...">
                </div>
                <div class="col-md-3">
                    <select name="categoria" class="form-select">
                        <option value="">Todas las categorías</option>
                        <!-- Options dinámicas -->
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100">Buscar</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Tabla -->
    <div class="card">
        <div class="card-body">
            <table class="table table-striped" id="tablaProductos">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Se llenará dinámicamente -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
```

**Haces esto para TODAS las pantallas principales.**

### 7.4 Verificación Visual

Abres cada página en el navegador y verificas que:
- Se vea el diseño correctamente
- Bootstrap esté cargando
- La navegación funcione
- No haya errores en la consola

NO te preocupes todavía porque las tablas estén vacías o los formularios no 
funcionen. Eso es la siguiente fase.

---

## 8. FASE 5: FRONTEND - MÓDULOS FUNCIONALES

**Objetivo:** Conectar el frontend con el backend. Hacer que todo funcione.

**Duración estimada:** 2-3 semanas (dependiendo de la complejidad)

**Estrategia:** Similar a Fase 2, subdivides por módulos.

### 8.1 Sub-Fase 5.1: Módulo Inventario Funcional

**Rama Git:** `fase-5.1-frontend-inventario`

**Prompt para Claude:**

```
Claude, estoy en Fase 5.1: Hacer funcional el módulo de Inventario en el frontend.

Tengo:
- Backend completo (models/producto.php con todas las funciones)
- APIs funcionales (api/productos/*)
- HTML estructurado (modules/inventario/*.php con estructura)

Necesito:
1. Conectar la lista de productos con el backend (cargar datos reales)
2. Hacer funcional el formulario de agregar producto
3. Hacer funcional el formulario de editar producto
4. Implementar eliminación con confirmación
5. Búsqueda y filtros funcionales
6. Validaciones en frontend

Paso a paso. ¿Empezamos con cargar los datos en la tabla?
```

**Desarrollo:**

Claude te guiará a:

1. **Modificar `modules/inventario/lista.php` para cargar datos:**
   ```php
   <?php
   // Obtener productos del modelo
   require_once '../../models/producto.php';
   $productos = obtenerProductos($pdo);
   ?>
   
   <!-- En la tabla -->
   <tbody>
       <?php foreach($productos as $prod): ?>
       <tr>
           <td><?= htmlspecialchars($prod['codigo']) ?></td>
           <td><?= htmlspecialchars($prod['nombre']) ?></td>
           <td><?= htmlspecialchars($prod['categoria']) ?></td>
           <td>Q <?= number_format($prod['precio'], 2) ?></td>
           <td><?= $prod['stock'] ?></td>
           <td>
               <a href="editar.php?id=<?= $prod['id'] ?>" class="btn btn-sm btn-warning">
                   <i class="bi bi-pencil"></i>
               </a>
               <button class="btn btn-sm btn-danger" onclick="eliminar(<?= $prod['id'] ?>)">
                   <i class="bi bi-trash"></i>
               </button>
           </td>
       </tr>
       <?php endforeach; ?>
   </tbody>
   ```

2. **Crear `modules/inventario/agregar.php` funcional:**
   ```php
   <?php
   // Procesar formulario si es POST
   if ($_SERVER['REQUEST_METHOD'] === 'POST') {
       require_once '../../models/producto.php';
       
       $datos = [
           'codigo' => $_POST['codigo'],
           'nombre' => $_POST['nombre'],
           'precio' => $_POST['precio'],
           // ...
       ];
       
       $resultado = crearProducto($pdo, $datos);
       
       if ($resultado['success']) {
           header('Location: lista.php?msg=creado');
           exit;
       } else {
           $error = $resultado['error'];
       }
   }
   ?>
   
   <!-- Formulario HTML -->
   <form method="POST">
       <!-- Campos del formulario -->
   </form>
   ```

3. **Agregar JavaScript para validaciones y AJAX donde sea necesario:**
   ```javascript
   // assets/js/inventario.js
   
   function eliminar(id) {
       Swal.fire({
           title: '¿Eliminar producto?',
           text: "Esta acción no se puede deshacer",
           icon: 'warning',
           showCancelButton: true,
           confirmButtonText: 'Sí, eliminar'
       }).then((result) => {
           if (result.isConfirmed) {
               fetch(`../../api/productos/eliminar.php?id=${id}`)
                   .then(response => response.json())
                   .then(data => {
                       if (data.success) {
                           location.reload();
                       } else {
                           alert('Error: ' + data.error);
                       }
                   });
           }
       });
   }
   ```

4. **Pruebas completas del módulo**

5. **Commit:**
   ```bash
   git add .
   git commit -m "Sub-fase 5.1 completa: Módulo Inventario funcional"
   git push origin fase-5.1-frontend-inventario
   ```

### 8.2 Repetir para Cada Módulo

- Fase 5.2: Módulo Ventas funcional
- Fase 5.3: Módulo Clientes funcional
- etc.

**Cada uno en su rama.**

---

## 9. FASE 6: INTEGRACIÓN Y PRUEBAS

**Objetivo:** Probar TODO el sistema de principio a fin. Encontrar y corregir bugs.

**Duración estimada:** 1-2 semanas

**Rama Git:** `fase-6-pruebas`

### 9.1 Plan de Pruebas

Creas `docs/plan-de-pruebas.md`:

```markdown
# Plan de Pruebas

## Pruebas Funcionales

### Módulo Inventario
- [ ] Crear producto nuevo
- [ ] Editar producto existente
- [ ] Eliminar producto
- [ ] Buscar producto
- [ ] Filtrar por categoría
- [ ] Ver que el stock se actualice correctamente

### Módulo Ventas
- [ ] Registrar venta simple
- [ ] Registrar venta con múltiples productos
- [ ] Verificar actualización de inventario
- [ ] Generar ticket PDF
- [ ] Ver historial de ventas
- [ ] Filtrar ventas por fecha

[Continúa con todos los módulos...]

## Pruebas de Seguridad
- [ ] Intentar acceder sin login
- [ ] Intentar acceder a página de admin siendo empleado
- [ ] Intentar SQL injection en campos de texto
- [ ] Verificar que passwords estén hasheados en BD

## Pruebas de Usabilidad
- [ ] Sistema funciona en Chrome
- [ ] Sistema funciona en Firefox
- [ ] Sistema es responsive en tablet
- [ ] Sistema es responsive en móvil
- [ ] Mensajes de error son claros

## Pruebas de Rendimiento
- [ ] Página carga en menos de 2 segundos
- [ ] Búsquedas son rápidas (<1 segundo)
- [ ] Sistema funciona con 1000+ productos

## Bugs Encontrados
[Lista de bugs que vas encontrando, con su estado]

1. [RESUELTO] Al editar producto, no guardaba la categoría
   - Solución: Faltaba el campo categoria_id en el formulario
   
2. [PENDIENTE] ...
```

### 9.2 Proceso de Pruebas

1. **Pruebas manuales exhaustivas**
   - Pruebas cada funcionalidad como si fueras el usuario final
   - Intentas romper el sistema (inputs raros, clicks rápidos, etc.)
   - Anotas cada bug que encuentres

2. **Pruebas con datos reales**
   - Si el cliente tiene datos existentes, los importas
   - Pruebas con volumen real de datos

3. **Pruebas con el cliente**
   - Le das acceso al sistema en un servidor de pruebas
   - El cliente prueba y te da feedback
   - Ajustas según feedback

4. **Corrección de bugs**
   - Cada bug que encuentres, lo corriges inmediatamente
   - Commit por cada bug importante:
     ```bash
     git add .
     git commit -m "Fix: [descripción del bug]"
     ```

---

## 10. FASE 7: REFINAMIENTO Y OPTIMIZACIÓN

**Objetivo:** Pulir detalles, optimizar rendimiento, mejorar UX.

**Duración estimada:** 1 semana

**Rama Git:** `fase-7-optimizacion`

### 10.1 Optimizaciones

- Agregar índices a BD donde haga falta
- Optimizar queries lentas
- Comprimir assets (CSS, JS)
- Optimizar imágenes
- Agregar loading indicators
- Mejorar mensajes de error
- Agregar tooltips explicativos
- Mejorar validaciones

### 10.2 Documentación

**Manual de Usuario:**
Creas un documento PDF con screenshots explicando cómo usar cada módulo.

**Manual Técnico:**
Documenta la estructura del código, cómo funciona la BD, etc.

---

## 11. FASE 8: DEPLOYMENT Y ENTREGA

**Objetivo:** Subir el sistema a producción y entregarlo al cliente.

**Duración estimada:** 3-5 días

**Rama Git:** `fase-8-deployment`

### 11.1 Preparación para Producción

1. **Merge de todas las ramas a main:**
   ```bash
   git checkout main
   git merge fase-1-arquitectura
   git merge fase-2.1-autenticacion
   # ...merge todas las ramas
   git push origin main
   ```

2. **Configurar para producción:**
   - Cambiar `ENVIRONMENT` a 'production' en config.php
   - Cambiar URLs a las del dominio real
   - Deshabilitar display_errors

3. **Crear base de datos en Hostinger:**
   - Desde hPanel crear BD nueva
   - Ejecutar schema.sql
   - NO ejecutar seed.sql (datos de prueba)

4. **Subir archivos:**
   - Usando FileZilla, subes todo a /public_html/
   - IMPORTANTE: No subir .git/ ni archivos de prueba

5. **Configurar config.php en producción:**
   - Editar config.php directamente en el servidor con datos reales
   - Usuario y password de BD de Hostinger

6. **Verificación:**
   - Pruebas que todo funcione en producción
   - Corregir cualquier path incorrecto

### 11.2 Migración de Datos (si aplica)

Si el cliente tiene datos existentes:
1. Exportar datos del sistema viejo (Excel, CSV, etc.)
2. Crear script de migración
3. Importar a la BD de producción
4. Verificar que todo se importó correctamente

### 11.3 Capacitación

1. **Sesión de capacitación con usuarios:**
   - 1-2 horas presenciales o por videollamada
   - Demostrar cada módulo
   - Resolver dudas
   - Grabar la sesión para referencia

2. **Entregar manuales:**
   - Manual de usuario en PDF
   - Videos tutoriales (opcional)

### 11.4 Entrega Formal

1. **Checklist de entrega:**
   - [ ] Sistema funcionando en producción
   - [ ] Base de datos configurada
   - [ ] Usuarios creados
   - [ ] Manuales entregados
   - [ ] Capacitación realizada
   - [ ] Cliente satisfecho

2. **Acta de entrega:**
   Documento firmado por cliente confirmando recepción del sistema.

3. **Cobro final:**
   Si quedaba 20% pendiente, ahora lo cobras.

4. **Soporte post-entrega:**
   Durante 1 mes estás disponible para dudas y bugs.

---

## 12. GESTIÓN DE GIT Y RAMAS

### 12.1 Convención de Ramas

- `main` - Código en producción, siempre estable
- `fase-X-nombre` - Una rama por cada fase/subfase
- `hotfix-descripcion` - Para correcciones urgentes en producción

### 12.2 Comandos Útiles

**Crear rama nueva:**
```bash
git checkout main
git pull origin main
git checkout -b fase-X-nombre
git push -u origin fase-X-nombre
```

**Cambiar entre ramas:**
```bash
git checkout nombre-rama
```

**Ver en qué rama estás:**
```bash
git branch
```

**Hacer commit:**
```bash
git add .
git commit -m "Mensaje descriptivo"
git push
```

**Merge de rama a main:**
```bash
git checkout main
git merge fase-X-nombre
git push origin main
```

---

## 13. COMUNICACIÓN EFECTIVA CON CLAUDE

### 13.1 Estructura de Prompts Efectivos

**Prompt inicial de cada fase:**
```
Claude, estoy en [Fase X: Nombre de Fase].

CONTEXTO:
He completado:
- [Lista de fases anteriores completadas]
- [Archivos/funcionalidades ya implementadas]

Tengo disponible:
- [Archivos que Claude puede usar como referencia]

OBJETIVO DE ESTA FASE:
[Descripción clara de qué quieres lograr]

IMPORTANTE SOBRE CÓMO TRABAJAR:
- No soy experto en programación
- Guíame paso a paso
- Un archivo a la vez
- Explica QUÉ hacer, DÓNDE hacerlo, y POR QUÉ
- Espera mi confirmación antes de continuar

[Adjunta archivos relevantes si es necesario]

¿Por dónde empezamos?
```

### 13.2 Prompts Durante el Desarrollo

**Cuando algo funciona:**
```
Listo, funciona perfectamente. ¿Qué sigue?
```

**Cuando hay un error:**
```
Tuve este error:
[Copia exacta del error]

¿Qué puede estar pasando?
```

**Cuando no entiendes algo:**
```
No entiendo bien [concepto]. ¿Puedes explicármelo de forma más simple?
```

**Cuando quieres validación:**
```
Antes de continuar, ¿está bien implementado [lo que hiciste]? ¿Hay algo 
que debería mejorar?
```

### 13.3 Qué Compartir con Claude

**SIEMPRE comparte:**
- Mensajes de error completos
- Código relevante (no todo el proyecto, solo lo relevante)
- Estructura de BD (cuando sea pertinente)
- Objetivo claro de lo que intentas hacer

**NO necesitas compartir:**
- Todo el código si el problema es específico
- Archivos que no están relacionados con tu pregunta

---

## 14. CHECKLIST POR FASE

### Fase 0: Planificación ✓
- [ ] Reunión con cliente completada
- [ ] Requerimientos documentados y aprobados
- [ ] Propuesta enviada y firmada
- [ ] Anticipo recibido
- [ ] Proyecto en Git/GitHub
- [ ] Base de datos diseñada completamente
- [ ] Diseño validado con Claude
- [ ] Wireframes creados en Figma
- [ ] Cliente aprobó wireframes
- [ ] Plan de fases completo
- [ ] Roles y permisos definidos

### Fase 1: Arquitectura y BD ✓
- [ ] Estructura de carpetas creada
- [ ] Archivo de configuración funcional
- [ ] Conexión a BD implementada
- [ ] Schema SQL completo
- [ ] Base de datos creada
- [ ] Datos de prueba cargados
- [ ] Verificación exitosa
- [ ] Commit y push realizados

### Fase 2: Backend ✓
- [ ] Sistema de autenticación completo
- [ ] Modelos de TODOS los módulos implementados
- [ ] Validaciones completas
- [ ] Manejo de errores robusto
- [ ] Pruebas de cada función exitosas
- [ ] Commit por cada módulo

### Fase 3: APIs ✓
- [ ] Todos los endpoints creados
- [ ] Respuestas JSON estandarizadas
- [ ] Pruebas en Thunder Client exitosas
- [ ] Documentación de APIs completa

### Fase 4: Frontend Base ✓
- [ ] Header/Footer/Navbar implementados
- [ ] Dashboard creado
- [ ] Plantillas HTML de todos los módulos
- [ ] Bootstrap integrado correctamente
- [ ] Navegación funcionando
- [ ] Verificación visual exitosa

### Fase 5: Frontend Funcional ✓
- [ ] Todos los módulos conectados con backend
- [ ] Formularios funcionales
- [ ] Validaciones frontend implementadas
- [ ] Mensajes de éxito/error funcionando
- [ ] AJAX donde sea necesario
- [ ] Pruebas de cada módulo exitosas

### Fase 6: Pruebas ✓
- [ ] Plan de pruebas completado
- [ ] Pruebas funcionales realizadas
- [ ] Pruebas de seguridad realizadas
- [ ] Pruebas en múltiples navegadores
- [ ] Pruebas responsive
- [ ] Bugs encontrados y corregidos
- [ ] Cliente probó y aprobó

### Fase 7: Optimización ✓
- [ ] Queries optimizadas
- [ ] Índices agregados donde necesario
- [ ] Assets minificados
- [ ] Imágenes optimizadas
- [ ] UX mejorada
- [ ] Manuales creados

### Fase 8: Deployment ✓
- [ ] Todas las ramas mergeadas a main
- [ ] Base de datos en Hostinger creada
- [ ] Archivos subidos a producción
- [ ] Config de producción ajustado
- [ ] Sistema funcionando en producción
- [ ] Datos migrados (si aplica)
- [ ] Capacitación realizada
- [ ] Manuales entregados
- [ ] Acta de entrega firmada
- [ ] Pago final recibido

---

## 15. PLANTILLAS DE PROMPTS

### Prompt: Iniciar Nueva Fase

```
Claude, inicio la [Fase X: Nombre].

PROYECTO: [Nombre del sistema]

COMPLETADO HASTA AHORA:
- [Lista de fases completadas]

ARCHIVOS RELEVANTES:
[Adjuntar archivos si es necesario]

OBJETIVO DE ESTA FASE:
[Descripción detallada]

NOTAS IMPORTANTES:
- Trabajo paso a paso contigo
- Un archivo a la vez
- Necesito explicaciones de QUÉ, DÓNDE y POR QUÉ

¿Comenzamos?
```

### Prompt: Solicitar Revisión de Código

```
Claude, he implementado [descripción de lo que hiciste].

[Código relevante o archivo adjunto]

Por favor revisa:
1. ¿Está bien estructurado?
2. ¿Hay vulnerabilidades de seguridad?
3. ¿Se puede optimizar algo?
4. ¿Falta algo importante?

Dame feedback específico.
```

### Prompt: Resolver Bug

```
Claude, tengo un problema con [descripción del problema].

ESPERADO:
[Qué debería pasar]

ACTUAL:
[Qué está pasando]

ERROR (si hay):
[Mensaje de error completo]

CÓDIGO RELEVANTE:
[Código donde crees que está el problema]

¿Qué puede estar mal?
```

### Prompt: Pedir Explicación

```
Claude, no entiendo bien [concepto/código].

[Código o concepto en cuestión]

¿Puedes explicarme:
1. Qué hace exactamente
2. Por qué se hace así
3. Un ejemplo más simple

Nivel de explicación: Soy principiante/intermedio.
```

---

## CONCLUSIÓN

Esta metodología te permite:

✅ Trabajar de forma profesional y organizada
✅ Entregar proyectos de calidad
✅ No sentirte abrumado (una fase a la vez)
✅ Colaborar efectivamente con Claude
✅ Mantener al cliente informado
✅ Tener código mantenible y documentado
✅ Escalar tu negocio de desarrollo

**Recuerda los principios fundamentales:**

1. **Planifica antes de programar** - Fase 0 es crítica
2. **Una cosa a la vez** - No intentes hacer todo junto
3. **Backend primero** - Lógica antes que diseño
4. **Prueba constantemente** - No acumules código sin probar
5. **Documenta mientras avanzas** - No dejes documentación para el final
6. **Comunica con el cliente** - Mantenlos informados del progreso
7. **Usa Git religiosamente** - Cada fase en su rama
8. **Aprende de cada proyecto** - Mejora tu metodología constantemente

═══════════════════════════════════════════════════════════
              ¡Éxito en tus proyectos! 🚀
═══════════════════════════════════════════════════════════
