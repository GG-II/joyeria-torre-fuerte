# GUÍA DE CREACIÓN DE VISTAS PHP
## Mejores Prácticas para Frontend en Joyería Torre Fuerte

**Versión:** 2.0  
**Última actualización:** 24 de enero de 2025  
**Basado en:** Fase 4 completada exitosamente

---

## 📋 TABLA DE CONTENIDOS

1. [Principios Fundamentales](#principios-fundamentales)
2. [Estructura de Archivos](#estructura-de-archivos)
3. [Plantilla Base](#plantilla-base)
4. [Componentes Estándar](#componentes-estándar)
5. [Reglas de Campos y Schema](#reglas-de-campos-y-schema)
6. [Validaciones](#validaciones)
7. [Diseño y UX](#diseño-y-ux)
8. [JavaScript y Eventos](#javascript-y-eventos)
9. [Errores Comunes y Soluciones](#errores-comunes-y-soluciones)
10. [Checklist de Calidad](#checklist-de-calidad)

---

## 🎯 PRINCIPIOS FUNDAMENTALES

### 1. El Schema es la Única Fuente de Verdad

**REGLA DE ORO:** Antes de escribir una sola línea de código, SIEMPRE consulta `base_datos.txt`

#### ✅ HACER:
```php
// 1. Abrir base_datos.txt
// 2. Buscar la tabla correspondiente
// 3. Copiar nombres exactos de campos
// 4. Verificar tipos de datos
// 5. Copiar valores ENUM exactos

// Ejemplo correcto:
<input type="text" name="nombre" maxlength="100">  
// ↑ del schema: nombre VARCHAR(100)

<option value="en_proceso">En Proceso</option>
// ↑ del schema ENUM: 'en_proceso' (con guión bajo)
```

#### ❌ NO HACER:
```php
// ❌ Inventar nombres de campos
<input type="text" name="nombre_completo">  // No existe en schema

// ❌ Inventar valores ENUM
<option value="en-proceso">En Proceso</option>  // Guión en vez de guión bajo

// ❌ Asumir tipos de datos
<input type="number" name="telefono">  // Teléfono es VARCHAR, no INT
```

### 2. Consistencia Visual en Todo el Sistema

Todos los módulos deben verse como parte del mismo sistema.

#### Componentes Estandarizados:
- ✅ Mismo diseño de stat-cards
- ✅ Mismo diseño de tablas
- ✅ Mismos colores de badges
- ✅ Mismos iconos para acciones
- ✅ Mismo espaciado y márgenes

### 3. Separación de Responsabilidades

```
Frontend:
  - Presentación de datos
  - Validación básica de formularios
  - Experiencia de usuario
  - Llamadas a APIs

Backend (NO en frontend):
  - Lógica de negocio
  - Cálculos complejos
  - Acceso a base de datos
  - Seguridad y autenticación
```

### 4. Mobile-First Siempre

Diseñar primero para móvil, luego expandir a desktop.

```html
<!-- ✅ CORRECTO -->
<div class="col-12 col-md-6 col-lg-4">
  <!-- Móvil: 100%, Tablet: 50%, Desktop: 33% -->
</div>

<!-- ❌ INCORRECTO -->
<div class="col-lg-4">
  <!-- Solo funciona bien en desktop -->
</div>
```

---

## 📁 ESTRUCTURA DE ARCHIVOS

### Anatomía de un Archivo Vista

```php
<?php
// ================================================
// MÓDULO [NOMBRE] - [FUNCIÓN]
// ================================================

// 1. IMPORTS Y CONFIGURACIÓN (líneas 1-10)
require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

// 2. VERIFICACIÓN DE PERMISOS (líneas 11-15)
requiere_autenticacion();
requiere_rol(['administrador', 'vendedor']);

// 3. LÓGICA DE DATOS (líneas 16-50)
$titulo_pagina = 'Título de la Página';
// Obtener datos (dummy o de BD)
// Procesar información

// 4. INCLUDES (líneas 51-60)
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<!-- 5. CONTENIDO HTML (líneas 61-200+) -->
<div class="container-fluid main-content">
    <!-- Breadcrumb -->
    <!-- Encabezado -->
    <!-- Contenido principal -->
</div>

<!-- 6. JAVASCRIPT (si es necesario) -->
<script>
// Código JavaScript específico de la vista
</script>

<?php
// 7. FOOTER (última línea)
include '../../includes/footer.php';
?>
```

### Nomenclatura de Archivos

```
✅ CORRECTO:
- lista.php          (listado general)
- agregar.php        (formulario nuevo registro)
- editar.php         (formulario editar registro)
- ver.php            (vista detallada)
- transferir.php     (acción específica)
- dashboard.php      (vista principal)

❌ INCORRECTO:
- listar.php         (usar "lista")
- nuevo.php          (usar "agregar")
- detalle.php        (usar "ver")
- modificar.php      (usar "editar")
```

---

## 📄 PLANTILLA BASE

### Template Completo para Nueva Vista

```php
<?php
// ================================================
// MÓDULO [NOMBRE_MODULO] - [FUNCIÓN_VISTA]
// ================================================

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

// Verificar autenticación y permisos
requiere_autenticacion();
requiere_rol(['administrador', 'dueño', 'vendedor']); // Ajustar roles según necesidad

// Título de página
$titulo_pagina = 'Título de la Vista';

// Incluir header
include '../../includes/header.php';

// Incluir navbar
include '../../includes/navbar.php';

/**
 * CAMPOS REALES DEL SCHEMA:
 * tabla_principal:
 *   - campo1 VARCHAR(100) NOT NULL
 *   - campo2 INT NULL
 *   - campo3 ENUM('valor1','valor2','valor3')
 *   - campo4 DECIMAL(10,2)
 *   - campo5 DATETIME DEFAULT CURRENT_TIMESTAMP
 */

// Datos dummy (reemplazar con query real en Fase 5)
$datos = [
    [
        'id' => 1,
        'campo1' => 'Valor 1',
        'campo2' => 100,
        'campo3' => 'valor1',
        'campo4' => 1500.50,
        'campo5' => '2025-01-24 10:00:00'
    ]
];
?>

<!-- Contenido Principal -->
<div class="container-fluid main-content">
    
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="<?php echo BASE_URL; ?>dashboard.php">
                    <i class="bi bi-house"></i> Dashboard
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="lista.php">
                    <i class="bi bi-[icono]"></i> Módulo
                </a>
            </li>
            <li class="breadcrumb-item active">Vista Actual</li>
        </ol>
    </nav>

    <!-- Encabezado -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1>
                    <i class="bi bi-[icono]"></i>
                    Título de la Vista
                </h1>
                <p class="text-muted">Descripción breve de la funcionalidad</p>
            </div>
            <div class="col-md-6 text-end">
                <!-- Botones de acción -->
                <a href="agregar.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i>
                    Nuevo Registro
                </a>
            </div>
        </div>
    </div>

    <!-- Stat Cards (si aplica) -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card azul">
                <div class="stat-icon">
                    <i class="bi bi-[icono]"></i>
                </div>
                <div class="stat-value">123</div>
                <div class="stat-label">Métrica 1</div>
            </div>
        </div>
        <!-- Más stat-cards según necesidad -->
    </div>

    <!-- Filtros (si aplica) -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Búsqueda</label>
                    <input type="text" class="form-control" placeholder="Buscar...">
                </div>
                <!-- Más filtros según necesidad -->
            </div>
        </div>
    </div>

    <!-- Contenido Principal -->
    <div class="card">
        <div class="card-header">
            <i class="bi bi-[icono]"></i>
            Título del Contenido
        </div>
        <div class="card-body">
            <!-- Contenido específico de la vista -->
        </div>
    </div>

</div>

<!-- JavaScript específico (si es necesario) -->
<script>
// Código JavaScript
</script>

<?php
// Incluir footer
include '../../includes/footer.php';
?>
```

---

## 🧩 COMPONENTES ESTÁNDAR

### 1. Stat-Cards (Tarjetas de Métricas)

```html
<!-- 4 variantes de color disponibles -->

<!-- Azul - Para métricas generales -->
<div class="stat-card azul">
    <div class="stat-icon">
        <i class="bi bi-box-seam"></i>
    </div>
    <div class="stat-value">245</div>
    <div class="stat-label">Total Productos</div>
</div>

<!-- Dorado - Para métricas financieras -->
<div class="stat-card dorado">
    <div class="stat-icon">
        <i class="bi bi-currency-dollar"></i>
    </div>
    <div class="stat-value">Q 145,800</div>
    <div class="stat-label">Ventas del Mes</div>
</div>

<!-- Verde - Para métricas positivas -->
<div class="stat-card verde">
    <div class="stat-icon">
        <i class="bi bi-check-circle"></i>
    </div>
    <div class="stat-value">37</div>
    <div class="stat-label">Completados</div>
</div>

<!-- Rojo - Para alertas o métricas críticas -->
<div class="stat-card rojo">
    <div class="stat-icon">
        <i class="bi bi-exclamation-triangle"></i>
    </div>
    <div class="stat-value">12</div>
    <div class="stat-label">Bajo Stock</div>
</div>
```

### 2. Badges de Estado

```php
<?php
// Función helper para badges consistentes
function badge_estado($estado) {
    $badges = [
        'activo' => '<span class="badge bg-success">Activo</span>',
        'inactivo' => '<span class="badge bg-secondary">Inactivo</span>',
        'pendiente' => '<span class="badge bg-warning">Pendiente</span>',
        'completado' => '<span class="badge bg-success">Completado</span>',
        'cancelado' => '<span class="badge bg-danger">Cancelado</span>',
        'en_proceso' => '<span class="badge bg-info">En Proceso</span>'
    ];
    return $badges[$estado] ?? '<span class="badge bg-secondary">' . ucfirst($estado) . '</span>';
}

// Uso:
echo badge_estado($trabajo['estado']);
?>
```

### 3. Tablas Responsive

```html
<div class="card">
    <div class="card-header">
        <i class="bi bi-table"></i>
        Listado de Registros
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Estado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($datos as $item): ?>
                <tr>
                    <td class="fw-bold"><?php echo $item['id']; ?></td>
                    <td><?php echo htmlspecialchars($item['nombre']); ?></td>
                    <td><?php echo badge_estado($item['estado']); ?></td>
                    <td class="text-center">
                        <div class="btn-group" role="group">
                            <a href="ver.php?id=<?php echo $item['id']; ?>" 
                               class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="editar.php?id=<?php echo $item['id']; ?>" 
                               class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
```

### 4. Formularios Consistentes

```html
<form id="formRegistro" method="POST">
    
    <!-- Sección del formulario -->
    <h5 class="mb-3 text-primary">
        <i class="bi bi-person"></i>
        Información Básica
    </h5>

    <!-- Campo de texto -->
    <div class="mb-3">
        <label for="nombre" class="form-label">
            <i class="bi bi-person-badge"></i> Nombre Completo *
        </label>
        <input type="text" 
               class="form-control" 
               id="nombre" 
               name="nombre" 
               maxlength="100"
               required>
        <small class="text-muted">Máximo 100 caracteres</small>
    </div>

    <!-- Campo select -->
    <div class="mb-3">
        <label for="tipo" class="form-label">
            <i class="bi bi-tag"></i> Tipo *
        </label>
        <select class="form-select" id="tipo" name="tipo" required>
            <option value="">Seleccione...</option>
            <option value="tipo1">Tipo 1</option>
            <option value="tipo2">Tipo 2</option>
        </select>
    </div>

    <!-- Campo numérico -->
    <div class="mb-3">
        <label for="precio" class="form-label">
            <i class="bi bi-currency-dollar"></i> Precio
        </label>
        <div class="input-group">
            <span class="input-group-text">Q</span>
            <input type="number" 
                   class="form-control" 
                   id="precio" 
                   name="precio"
                   step="0.01"
                   min="0">
        </div>
    </div>

    <!-- Checkbox -->
    <div class="form-check mb-3">
        <input class="form-check-input" 
               type="checkbox" 
               id="activo" 
               name="activo"
               checked>
        <label class="form-check-label" for="activo">
            Registro activo
        </label>
    </div>

    <!-- Botones de acción -->
    <div class="d-flex justify-content-end gap-2">
        <a href="lista.php" class="btn btn-secondary">
            <i class="bi bi-x-circle"></i>
            Cancelar
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save"></i>
            Guardar
        </button>
    </div>
    
</form>
```

### 5. Modales

```html
<!-- Modal para confirmaciones o formularios rápidos -->
<div class="modal fade" id="modalRegistro" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle"></i>
                    Título del Modal
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Contenido del modal -->
                <form id="formModal">
                    <!-- Campos del formulario -->
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" class="btn btn-primary" onclick="guardar()">
                    <i class="bi bi-save"></i>
                    Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Limpiar modal al cerrar
$('#modalRegistro').on('hidden.bs.modal', function() {
    document.getElementById('formModal').reset();
});
</script>
```

---

## 📊 REGLAS DE CAMPOS Y SCHEMA

### Mapeo de Tipos SQL a HTML

```php
/**
 * GUÍA DE CONVERSIÓN SCHEMA → HTML
 */

// VARCHAR → input text
// Schema: nombre VARCHAR(100)
<input type="text" name="nombre" maxlength="100">

// INT → input number
// Schema: cantidad INT
<input type="number" name="cantidad" step="1">

// DECIMAL → input number
// Schema: precio DECIMAL(10,2)
<input type="number" name="precio" step="0.01">

// DATE → input date
// Schema: fecha_nacimiento DATE
<input type="date" name="fecha_nacimiento">

// DATETIME → input datetime-local
// Schema: fecha_entrega DATETIME
<input type="datetime-local" name="fecha_entrega">

// TEXT → textarea
// Schema: observaciones TEXT
<textarea name="observaciones" rows="3"></textarea>

// BOOLEAN → checkbox
// Schema: activo BOOLEAN DEFAULT 1
<input type="checkbox" name="activo" checked>

// ENUM → select
// Schema: estado ENUM('activo','inactivo','pendiente')
<select name="estado">
    <option value="activo">Activo</option>
    <option value="inactivo">Inactivo</option>
    <option value="pendiente">Pendiente</option>
</select>
```

### Manejo de Foreign Keys

```php
/**
 * REGLA: Siempre mostrar el nombre legible, no solo el ID
 */

// ❌ INCORRECTO
<td><?php echo $venta['usuario_id']; ?></td>  // Muestra: 3

// ✅ CORRECTO
<td><?php echo $venta['usuario_nombre']; ?></td>  // Muestra: Carlos Admin

// En SELECT (JOIN necesario):
// SELECT v.*, u.nombre as usuario_nombre 
// FROM ventas v 
// JOIN usuarios u ON v.usuario_id = u.id
```

### Campos Calculados (GENERATED ALWAYS)

```php
/**
 * REGLA: NO calcular en frontend si MySQL lo hace
 */

// ❌ INCORRECTO
$total = $subtotal - $descuento;  // Calcular en PHP

// ✅ CORRECTO
// Dejar que MySQL calcule automáticamente
// total DECIMAL(10,2) GENERATED ALWAYS AS (subtotal - descuento) STORED

// Solo mostrar el valor:
echo "Total: Q " . number_format($venta['total'], 2);
```

### Campos NOT NULL vs NULL

```php
/**
 * REGLA: Respetar la obligatoriedad del schema
 */

// Schema: nombre VARCHAR(100) NOT NULL
<input type="text" name="nombre" required>  // ✅ required

// Schema: email VARCHAR(100) NULL
<input type="email" name="email">  // ✅ sin required

// Schema: sucursal_id INT NULL
<select name="sucursal_id">
    <option value="">Sin sucursal</option>  // ✅ Opción vacía permitida
    <option value="1">Los Arcos</option>
</select>
```

---

## ✔️ VALIDACIONES

### Validaciones Frontend (JavaScript)

```javascript
/**
 * PLANTILLA DE VALIDACIÓN ESTÁNDAR
 */

document.getElementById('formRegistro').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // 1. Obtener valores
    const nombre = document.getElementById('nombre').value.trim();
    const email = document.getElementById('email').value.trim();
    const cantidad = parseInt(document.getElementById('cantidad').value);
    
    // 2. Validar campos obligatorios
    if (nombre.length < 3) {
        alert('El nombre debe tener al menos 3 caracteres');
        document.getElementById('nombre').focus();
        return false;
    }
    
    // 3. Validar formato de email
    if (email && !esEmailValido(email)) {
        alert('El email no tiene un formato válido');
        document.getElementById('email').focus();
        return false;
    }
    
    // 4. Validar rangos numéricos
    if (cantidad <= 0) {
        alert('La cantidad debe ser mayor a 0');
        document.getElementById('cantidad').focus();
        return false;
    }
    
    // 5. Si todo está bien, enviar (o simular)
    console.log('Datos válidos:', {nombre, email, cantidad});
    
    // TODO FASE 5: Llamada a API
    // fetch('/api/endpoint', {...})
    
    alert('Registro guardado exitosamente');
    // window.location.href = 'lista.php';
});

// Función helper
function esEmailValido(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}
```

### Validaciones por Tipo de Campo

```javascript
/**
 * VALIDACIONES COMUNES
 */

// Texto - Longitud mínima y máxima
function validarTexto(valor, min, max) {
    const longitud = valor.trim().length;
    return longitud >= min && longitud <= max;
}

// Número - Rango
function validarNumero(valor, min, max) {
    const num = parseFloat(valor);
    return !isNaN(num) && num >= min && num <= max;
}

// Email
function validarEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

// Teléfono Guatemala (8 dígitos)
function validarTelefono(telefono) {
    const regex = /^\d{8}$/;
    return regex.test(telefono.replace(/\D/g, ''));
}

// NIT Guatemala
function validarNIT(nit) {
    // Formato: 12345678-9
    const regex = /^\d{7,9}-?\d{1}$/;
    return regex.test(nit);
}

// DPI Guatemala (13 dígitos)
function validarDPI(dpi) {
    const regex = /^\d{13}$/;
    return regex.test(dpi.replace(/\s/g, ''));
}

// Fecha no futura
function validarFechaNoFutura(fecha) {
    const fechaInput = new Date(fecha);
    const hoy = new Date();
    return fechaInput <= hoy;
}

// Fecha no pasada
function validarFechaFutura(fecha) {
    const fechaInput = new Date(fecha);
    const hoy = new Date();
    return fechaInput >= hoy;
}
```

### Sanitización de Datos

```php
/**
 * SIEMPRE sanitizar antes de mostrar en HTML
 */

// ✅ CORRECTO
<?php echo htmlspecialchars($cliente['nombre']); ?>

// ❌ INCORRECTO
<?php echo $cliente['nombre']; ?>  // Vulnerable a XSS

// Para URLs
<a href="<?php echo htmlspecialchars($url, ENT_QUOTES); ?>">

// Para atributos
<input value="<?php echo htmlspecialchars($valor, ENT_QUOTES); ?>">
```

---

## 🎨 DISEÑO Y UX

### Paleta de Colores

```css
/**
 * COLORES CORPORATIVOS - USAR CONSISTENTEMENTE
 */

:root {
    /* Primarios */
    --color-dorado: #D4AF37;      /* Elementos destacados, dinero */
    --color-azul: #1e3a8a;        /* Elementos primarios, información */
    --color-plata: #C0C0C0;       /* Elementos secundarios */
    --color-negro: #1a1a1a;       /* Texto principal, fondos */
    
    /* Semánticos */
    --color-exito: #22c55e;       /* Operaciones exitosas */
    --color-peligro: #ef4444;     /* Errores, eliminaciones */
    --color-advertencia: #f59e0b; /* Alertas, pendientes */
    --color-info: #3b82f6;        /* Información adicional */
}
```

### Uso de Colores por Contexto

```html
<!-- DORADO: Dinero, métricas financieras -->
<div class="stat-card dorado">...</div>
<span class="text-warning">Q 1,500.00</span>

<!-- AZUL: Elementos primarios, información general -->
<div class="stat-card azul">...</div>
<button class="btn btn-primary">Guardar</button>

<!-- VERDE: Confirmaciones, estados activos -->
<div class="stat-card verde">...</div>
<span class="badge bg-success">Activo</span>

<!-- ROJO: Alertas, errores, eliminaciones -->
<div class="stat-card rojo">...</div>
<button class="btn btn-danger">Eliminar</button>
<span class="badge bg-danger">Crítico</span>

<!-- AMARILLO: Advertencias, pendientes -->
<span class="badge bg-warning">Pendiente</span>

<!-- GRIS: Elementos inactivos -->
<span class="badge bg-secondary">Inactivo</span>
```

### Iconografía Bootstrap Icons

```html
/**
 * ICONOS ESTÁNDAR POR CONTEXTO
 */

<!-- Navegación -->
<i class="bi bi-house"></i>         <!-- Dashboard -->
<i class="bi bi-arrow-left"></i>    <!-- Volver -->
<i class="bi bi-list-ul"></i>       <!-- Listado -->

<!-- Acciones -->
<i class="bi bi-plus-circle"></i>   <!-- Agregar -->
<i class="bi bi-pencil"></i>        <!-- Editar -->
<i class="bi bi-eye"></i>           <!-- Ver -->
<i class="bi bi-trash"></i>         <!-- Eliminar -->
<i class="bi bi-save"></i>          <!-- Guardar -->
<i class="bi bi-x-circle"></i>      <!-- Cancelar -->
<i class="bi bi-printer"></i>       <!-- Imprimir -->
<i class="bi bi-download"></i>      <!-- Descargar -->

<!-- Módulos -->
<i class="bi bi-cart-check"></i>    <!-- Ventas -->
<i class="bi bi-people"></i>        <!-- Clientes -->
<i class="bi bi-box-seam"></i>      <!-- Inventario -->
<i class="bi bi-tools"></i>         <!-- Taller -->
<i class="bi bi-cash-stack"></i>    <!-- Caja -->
<i class="bi bi-truck"></i>         <!-- Proveedores -->
<i class="bi bi-graph-up"></i>      <!-- Reportes -->
<i class="bi bi-gear"></i>          <!-- Configuración -->

<!-- Estados -->
<i class="bi bi-check-circle"></i>  <!-- Completado -->
<i class="bi bi-clock"></i>         <!-- Pendiente -->
<i class="bi bi-x-circle"></i>      <!-- Cancelado -->
<i class="bi bi-exclamation-triangle"></i> <!-- Alerta -->
```

### Espaciado Consistente

```html
/**
 * USAR CLASES DE BOOTSTRAP PARA ESPACIADO
 */

<!-- Márgenes -->
<div class="mb-3">  <!-- margin-bottom: 1rem -->
<div class="mb-4">  <!-- margin-bottom: 1.5rem -->
<div class="mt-4">  <!-- margin-top: 1.5rem -->
<div class="my-3">  <!-- margin vertical -->
<div class="mx-auto"> <!-- margin horizontal auto (centrar) -->

<!-- Padding -->
<div class="p-3">   <!-- padding: 1rem -->
<div class="p-4">   <!-- padding: 1.5rem -->
<div class="py-3">  <!-- padding vertical -->
<div class="px-4">  <!-- padding horizontal -->

<!-- Gaps (para flexbox/grid) -->
<div class="d-flex gap-2">  <!-- gap: 0.5rem -->
<div class="d-flex gap-3">  <!-- gap: 1rem -->
```

### Responsive Breakpoints

```html
/**
 * BREAKPOINTS DE BOOTSTRAP
 */

<!-- Extra Small: < 576px (móviles) -->
<div class="col-12">100% en móvil</div>

<!-- Small: ≥ 576px (tablets verticales) -->
<div class="col-sm-6">50% en tablets</div>

<!-- Medium: ≥ 768px (tablets horizontales) -->
<div class="col-md-4">33% en tablets h.</div>

<!-- Large: ≥ 992px (laptops) -->
<div class="col-lg-3">25% en laptops</div>

<!-- Extra Large: ≥ 1200px (desktops) -->
<div class="col-xl-2">16% en desktops</div>

<!-- Ejemplo completo -->
<div class="col-12 col-md-6 col-lg-4">
  <!-- Móvil: 100%, Tablet: 50%, Desktop: 33% -->
</div>
```

---

## 💻 JAVASCRIPT Y EVENTOS

### Estructura de JavaScript en Vista

```javascript
/**
 * PLANTILLA DE JAVASCRIPT PARA VISTAS
 */

// 1. CONSTANTES Y CONFIGURACIÓN
const BASE_URL = '<?php echo BASE_URL; ?>';
const ID_ACTUAL = <?php echo $id ?? 'null'; ?>;

// 2. ESTADO DE LA APLICACIÓN
let datosCarrito = [];
let totalGeneral = 0;

// 3. FUNCIONES DE INICIALIZACIÓN
document.addEventListener('DOMContentLoaded', function() {
    inicializarEventos();
    cargarDatosIniciales();
});

// 4. EVENT LISTENERS
function inicializarEventos() {
    // Formularios
    document.getElementById('formPrincipal')?.addEventListener('submit', handleSubmit);
    
    // Botones
    document.querySelectorAll('.btn-eliminar').forEach(btn => {
        btn.addEventListener('click', handleEliminar);
    });
    
    // Búsqueda en tiempo real
    document.getElementById('searchInput')?.addEventListener('input', handleBusqueda);
}

// 5. FUNCIONES DE NEGOCIO
function handleSubmit(e) {
    e.preventDefault();
    
    if (!validarFormulario()) {
        return false;
    }
    
    const datos = obtenerDatosFormulario();
    guardarDatos(datos);
}

function validarFormulario() {
    // Validaciones aquí
    return true;
}

function obtenerDatosFormulario() {
    const formData = new FormData(document.getElementById('formPrincipal'));
    return Object.fromEntries(formData);
}

// 6. LLAMADAS A API (preparadas para Fase 5)
async function guardarDatos(datos) {
    try {
        // TODO FASE 5: Activar cuando API esté lista
        // const response = await fetch(`${BASE_URL}api/endpoint`, {
        //     method: 'POST',
        //     headers: {'Content-Type': 'application/json'},
        //     body: JSON.stringify(datos)
        // });
        // const resultado = await response.json();
        
        // Por ahora: simular
        console.log('Datos a guardar:', datos);
        alert('Operación exitosa');
        window.location.href = 'lista.php';
        
    } catch (error) {
        console.error('Error:', error);
        alert('Error al guardar. Intente nuevamente.');
    }
}

// 7. FUNCIONES DE UI
function mostrarMensaje(mensaje, tipo = 'success') {
    // Implementar toast o alert
    alert(mensaje);
}

function actualizarUI() {
    // Actualizar elementos visuales
}
```

### Búsqueda en Tiempo Real

```javascript
/**
 * BÚSQUEDA EN TABLA
 */

function handleBusqueda(e) {
    const termino = e.target.value.toLowerCase();
    const filas = document.querySelectorAll('tbody tr');
    
    filas.forEach(fila => {
        const texto = fila.textContent.toLowerCase();
        fila.style.display = texto.includes(termino) ? '' : 'none';
    });
    
    // Actualizar contador
    const visibles = document.querySelectorAll('tbody tr:not([style*="display: none"])').length;
    document.getElementById('contador').textContent = `Mostrando ${visibles} registros`;
}
```

### Confirmaciones Antes de Eliminar

```javascript
/**
 * CONFIRMACIÓN DE ELIMINACIÓN
 */

function confirmarEliminacion(id, nombre) {
    if (!confirm(`¿Está seguro de eliminar "${nombre}"?\n\nEsta acción no se puede deshacer.`)) {
        return false;
    }
    
    // TODO FASE 5: Llamar a API de eliminación
    // fetch(`${BASE_URL}api/endpoint/${id}`, {method: 'DELETE'})
    
    console.log(`Eliminando registro ${id}`);
    alert('Registro eliminado exitosamente');
    location.reload();
}

// Uso en HTML:
// <button onclick="confirmarEliminacion(1, 'Cliente X')">Eliminar</button>
```

### Cálculos Dinámicos (Ejemplo: Carrito de Compras)

```javascript
/**
 * CARRITO DINÁMICO
 */

let productosCarrito = [];

function agregarAlCarrito(productoId, nombre, precio) {
    // Verificar si ya existe
    const existe = productosCarrito.find(p => p.id === productoId);
    
    if (existe) {
        existe.cantidad++;
    } else {
        productosCarrito.push({
            id: productoId,
            nombre: nombre,
            precio: precio,
            cantidad: 1
        });
    }
    
    actualizarCarrito();
}

function actualizarCarrito() {
    const tbody = document.getElementById('tablaCarrito');
    tbody.innerHTML = '';
    
    let subtotal = 0;
    
    productosCarrito.forEach(producto => {
        const total = producto.precio * producto.cantidad;
        subtotal += total;
        
        tbody.innerHTML += `
            <tr>
                <td>${producto.nombre}</td>
                <td>
                    <input type="number" 
                           value="${producto.cantidad}" 
                           min="1"
                           onchange="cambiarCantidad(${producto.id}, this.value)"
                           class="form-control form-control-sm" 
                           style="width: 80px;">
                </td>
                <td>Q ${producto.precio.toFixed(2)}</td>
                <td>Q ${total.toFixed(2)}</td>
                <td>
                    <button onclick="quitarDelCarrito(${producto.id})" 
                            class="btn btn-sm btn-danger">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    
    document.getElementById('subtotal').textContent = 'Q ' + subtotal.toFixed(2);
    calcularTotal();
}

function cambiarCantidad(productoId, nuevaCantidad) {
    const producto = productosCarrito.find(p => p.id === productoId);
    if (producto) {
        producto.cantidad = parseInt(nuevaCantidad);
        actualizarCarrito();
    }
}

function quitarDelCarrito(productoId) {
    productosCarrito = productosCarrito.filter(p => p.id !== productoId);
    actualizarCarrito();
}

function calcularTotal() {
    const subtotal = productosCarrito.reduce((sum, p) => sum + (p.precio * p.cantidad), 0);
    const descuento = parseFloat(document.getElementById('descuento').value) || 0;
    const total = subtotal - descuento;
    
    document.getElementById('total').textContent = 'Q ' + total.toFixed(2);
}
```

---

## ⚠️ ERRORES COMUNES Y SOLUCIONES

### Error 1: Campo No Coincide con Schema

**Síntoma:**
```php
// Error: Campo 'precio_venta' no existe
<input name="precio_venta">
```

**Diagnóstico:**
```bash
# Revisar schema
grep -i "precio" base_datos.txt
```

**Solución:**
```php
// El schema tiene tabla "precios_producto" separada
// No existe campo "precio_venta" directo en "productos"
```

**Prevención:**
- ✅ Siempre consultar base_datos.txt primero
- ✅ Copiar nombres exactos de campos
- ✅ No inventar nombres "que suenen lógicos"

---

### Error 2: Valores ENUM Incorrectos

**Síntoma:**
```php
// Error: Valor ENUM inválido
<option value="en-proceso">En Proceso</option>
// Schema tiene: ENUM('en_proceso') con guión bajo
```

**Solución:**
```php
// ✅ Copiar valores EXACTOS del schema
<option value="en_proceso">En Proceso</option>
```

**Prevención:**
- ✅ Copiar y pegar valores ENUM del schema
- ✅ Nunca cambiar guiones bajos por guiones
- ✅ Respetar mayúsculas/minúsculas

---

### Error 3: Foreign Key Sin JOIN

**Síntoma:**
```php
// Solo muestra ID, no el nombre
<?php echo $venta['cliente_id']; ?>  // Muestra: 5
```

**Solución:**
```php
// Hacer JOIN en la query
// SELECT v.*, c.nombre as cliente_nombre
// FROM ventas v
// JOIN clientes c ON v.cliente_id = c.id

<?php echo $venta['cliente_nombre']; ?>  // Muestra: Juan Pérez
```

**Prevención:**
- ✅ Siempre hacer JOIN para mostrar nombres
- ✅ Usar alias descriptivos (cliente_nombre, usuario_nombre)
- ✅ No mostrar IDs al usuario final

---

### Error 4: Calcular Campos GENERATED

**Síntoma:**
```php
// Calcular en PHP lo que MySQL calcula
$total = $subtotal - $descuento;
```

**Diagnóstico:**
```sql
-- El schema dice:
total DECIMAL(10,2) GENERATED ALWAYS AS (subtotal - descuento) STORED
```

**Solución:**
```php
// NO calcular, solo mostrar
echo "Total: Q " . number_format($venta['total'], 2);
```

**Prevención:**
- ✅ Identificar campos GENERATED en schema
- ✅ Dejar que MySQL haga el cálculo
- ✅ Solo mostrar el valor en frontend

---

### Error 5: XSS por No Sanitizar

**Síntoma:**
```php
// Vulnerable a XSS
<?php echo $cliente['nombre']; ?>
```

**Ataque:**
```javascript
// Si nombre = "<script>alert('XSS')</script>"
// Se ejecuta el código malicioso
```

**Solución:**
```php
// ✅ SIEMPRE usar htmlspecialchars
<?php echo htmlspecialchars($cliente['nombre']); ?>
```

**Prevención:**
- ✅ Sanitizar TODO lo que venga de BD
- ✅ Usar htmlspecialchars() consistentemente
- ✅ Para URLs usar htmlspecialchars($url, ENT_QUOTES)

---

### Error 6: Rutas Relativas Rotas

**Síntoma:**
```php
// No funciona desde diferentes niveles
href="../../modules/ventas/lista.php"  // ¿Cuántos ../ necesito?
```

**Solución:**
```php
// ✅ Usar BASE_URL
href="<?php echo BASE_URL; ?>modules/ventas/lista.php"
```

**Prevención:**
- ✅ Definir BASE_URL en config.php
- ✅ Usar siempre BASE_URL para rutas internas
- ✅ No usar rutas relativas con ../

---

### Error 7: Modal No Se Limpia

**Síntoma:**
```javascript
// Modal muestra datos del registro anterior
// al abrirlo para crear uno nuevo
```

**Solución:**
```javascript
// Limpiar modal al cerrar
$('#modalProducto').on('hidden.bs.modal', function() {
    document.getElementById('formProducto').reset();
    // Limpiar variables globales si las hay
    productoEditando = null;
});
```

**Prevención:**
- ✅ Siempre agregar evento hidden.bs.modal
- ✅ Resetear formulario y variables
- ✅ Probar abrir modal múltiples veces

---

### Error 8: No Validar en Backend

**Síntoma:**
```javascript
// Solo validar en JavaScript
if (cantidad <= 0) {
    alert('Cantidad inválida');
    return;
}
```

**Problema:**
- Usuario puede deshabilitar JavaScript
- Puede enviar datos directamente a la API

**Solución:**
```php
// Backend DEBE validar también
if ($cantidad <= 0) {
    throw new ValidationException('Cantidad inválida');
}
```

**Prevención:**
- ✅ Validar en frontend (UX)
- ✅ Validar en backend (SEGURIDAD)
- ✅ Nunca confiar solo en frontend

---

### Error 9: Formato de Fecha Incorrecto

**Síntoma:**
```html
<!-- Fecha en formato US: MM/DD/YYYY -->
<input type="date" value="01/24/2025">
<!-- Input no muestra la fecha -->
```

**Solución:**
```html
<!-- Formato ISO: YYYY-MM-DD -->
<input type="date" value="2025-01-24">
```

**En PHP:**
```php
// Convertir de MySQL a formato date input
$fecha_entrega = date('Y-m-d', strtotime($trabajo['fecha_entrega']));
<input type="date" value="<?php echo $fecha_entrega; ?>">

// Convertir de input a MySQL
$fecha_mysql = date('Y-m-d H:i:s', strtotime($_POST['fecha_entrega']));
```

**Prevención:**
- ✅ Usar formato ISO (YYYY-MM-DD) para inputs
- ✅ Convertir con date() en PHP
- ✅ Probar con diferentes fechas

---

### Error 10: Responsive Roto en Móvil

**Síntoma:**
```html
<!-- Tabla se sale del contenedor en móvil -->
<table class="table">...</table>
```

**Solución:**
```html
<!-- Envolver en div con table-responsive -->
<div class="table-responsive">
    <table class="table">...</table>
</div>
```

**Prevención:**
- ✅ SIEMPRE usar div table-responsive
- ✅ Probar en diferentes tamaños de pantalla
- ✅ Usar Chrome DevTools para mobile testing

---

## ✅ CHECKLIST DE CALIDAD

### Antes de Marcar Vista como Completa

#### Estructura y Código
- [ ] Archivo tiene comentario de header con módulo y función
- [ ] Incluye header.php y navbar.php correctamente
- [ ] Incluye footer.php al final
- [ ] Tiene verificación de autenticación
- [ ] Tiene verificación de permisos (si aplica)
- [ ] Usa BASE_URL para todas las rutas
- [ ] Código indentado correctamente
- [ ] Sin líneas de código comentadas innecesarias

#### Campos y Schema
- [ ] TODOS los campos vienen de base_datos.txt
- [ ] Nombres de campos son EXACTOS (no inventados)
- [ ] Valores ENUM son EXACTOS del schema
- [ ] Tipos de input coinciden con tipos SQL
- [ ] Campos NOT NULL tienen atributo required
- [ ] Foreign Keys muestran nombres, no solo IDs
- [ ] No se calculan campos GENERATED en PHP

#### Validaciones
- [ ] Campos obligatorios tienen validación
- [ ] Rangos numéricos validados
- [ ] Formatos (email, teléfono) validados
- [ ] Longitudes máximas respetadas
- [ ] Confirmación antes de eliminar
- [ ] Mensajes de error claros y útiles

#### Seguridad
- [ ] htmlspecialchars() en TODOS los outputs
- [ ] Permisos verificados por rol
- [ ] No hay SQL directo (queries comentados para backend)
- [ ] No hay credenciales hardcodeadas
- [ ] Inputs tienen sanitización básica

#### UI/UX
- [ ] Usa colores corporativos consistentemente
- [ ] Iconos apropiados de Bootstrap Icons
- [ ] Badges de estado con colores correctos
- [ ] Tablas tienen clase table-responsive
- [ ] Formularios tienen labels claros
- [ ] Botones tienen iconos y texto
- [ ] Mensajes de éxito/error implementados

#### Responsive
- [ ] Probado en móvil (< 576px)
- [ ] Probado en tablet (768px)
- [ ] Probado en desktop (1200px+)
- [ ] Tablas scrollean correctamente
- [ ] Botones no se rompen
- [ ] Cards se apilan correctamente

#### Navegación
- [ ] Breadcrumb correcto
- [ ] Enlaces a otras vistas funcionan
- [ ] Botón volver/cancelar existe
- [ ] Navbar marca módulo activo
- [ ] Enlaces usan BASE_URL

#### JavaScript
- [ ] Event listeners en DOMContentLoaded
- [ ] Funciones tienen nombres descriptivos
- [ ] Comentarios de TODO FASE 5 en API calls
- [ ] Console.log solo para debugging (eliminar en producción)
- [ ] Manejo de errores implementado

#### Documentación
- [ ] Comentarios de campos del schema incluidos
- [ ] Queries SQL comentados para backend
- [ ] Funciones complejas tienen comentarios
- [ ] TODOs claros para Fase 5

---

## 📚 RECURSOS DE REFERENCIA

### Documentación Oficial

- **Bootstrap 5:** https://getbootstrap.com/docs/5.3/
- **Bootstrap Icons:** https://icons.getbootstrap.com/
- **Chart.js:** https://www.chartjs.org/docs/
- **PHP Manual:** https://www.php.net/manual/es/
- **MySQL Reference:** https://dev.mysql.com/doc/

### Archivos del Proyecto

- `base_datos.txt` - Schema completo de base de datos
- `FASE-4-COMPLETADA.md` - Documentación de la fase
- Ejemplos en `/modules/` - Vistas ya completadas

### Patrones a Seguir

**Para crear nueva vista, usar como referencia:**
- `clientes/lista.php` - Listado con búsqueda y filtros
- `ventas/nueva.php` - Formulario complejo con JavaScript
- `taller/ver.php` - Vista detallada con timeline
- `reportes/dashboard.php` - Gráficas con Chart.js

---

## 🎓 EJERCICIOS DE PRÁCTICA

### Ejercicio 1: Crear Vista Lista

**Tarea:** Crear `categorias/lista.php`

**Requisitos:**
1. Mostrar tabla de categorías
2. Incluir búsqueda
3. Mostrar cantidad de productos por categoría
4. Badges para activo/inactivo
5. Botones ver/editar/eliminar

**Campos del schema:**
```sql
categorias: id, nombre, descripcion, activo
```

### Ejercicio 2: Crear Formulario

**Tarea:** Crear `categorias/agregar.php`

**Requisitos:**
1. Formulario con nombre y descripción
2. Checkbox activo (checked por defecto)
3. Validación: nombre mínimo 3 caracteres
4. Botones cancelar y guardar

### Ejercicio 3: Implementar Búsqueda

**Tarea:** Agregar búsqueda en tiempo real a lista

**Requisitos:**
1. Input de búsqueda
2. Filtrar tabla sin recargar página
3. Mostrar contador de resultados
4. Mensaje si no hay resultados

---

## 🔄 PROCESO DE REVISIÓN

### Auto-Revisión (Desarrollador)

1. Ejecutar checklist de calidad completo
2. Probar en diferentes navegadores
3. Probar en diferentes tamaños de pantalla
4. Validar que campos coincidan con schema
5. Verificar que no hay console.log innecesarios

### Revisión de Pares (Opcional)

1. Otro desarrollador revisa el código
2. Verifica consistencia con otras vistas
3. Prueba flujos de usuario
4. Identifica mejoras

### Integración

1. Mergear a rama principal
2. Actualizar documentación si es necesario
3. Notificar a equipo backend de nuevos endpoints necesarios

---

## 📝 PLANTILLA DE COMMIT

```bash
# Formato de commits para vistas

git commit -m "feat(modulo): agregar vista lista
- Tabla responsive con búsqueda
- 4 stat-cards de resumen
- Filtros por estado y fecha
- Botones de acciones con permisos
- Campos verificados con schema"

git commit -m "fix(modulo): corregir validación en formulario
- Validar longitud mínima de nombre
- Corregir formato de fecha
- Sanitizar inputs antes de mostrar"
```

---

## 🎯 CONCLUSIÓN

Esta guía debe ser consultada **SIEMPRE** antes de crear una nueva vista. Los patrones aquí establecidos aseguran:

1. ✅ Consistencia visual en todo el sistema
2. ✅ Código mantenible y escalable
3. ✅ Integración fácil con backend
4. ✅ Experiencia de usuario profesional
5. ✅ Reducción de errores comunes

**Recuerda:** El tiempo invertido en seguir estas guías se recupera con creces al evitar refactorización posterior.

---

**Documento creado:** 24 de enero de 2025  
**Versión:** 2.0  
**Última actualización:** Post Fase 4  
**Mantenido por:** Equipo de Desarrollo
