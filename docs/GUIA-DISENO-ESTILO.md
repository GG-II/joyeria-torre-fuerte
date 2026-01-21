# 🎨 GUÍA DE DISEÑO Y ESTILO - SISTEMA JOYERÍA TORRE FUERTE
## Manual de Identidad Visual y Estándares de Desarrollo

**Versión:** 1.0  
**Fecha:** 21 de enero de 2026  
**Última actualización:** 21 de enero de 2026  
**Estado:** ✅ Activo

---

## 📋 ÍNDICE

1. [Introducción](#introducción)
2. [Paleta de Colores](#paleta-de-colores)
3. [Tipografía](#tipografía)
4. [Logo e Identidad](#logo-e-identidad)
5. [Componentes UI](#componentes-ui)
6. [Estructura de Páginas](#estructura-de-páginas)
7. [Responsive Design](#responsive-design)
8. [Iconografía](#iconografía)
9. [Formularios](#formularios)
10. [Tablas](#tablas)
11. [Mensajes y Alertas](#mensajes-y-alertas)
12. [Estándares de Código](#estándares-de-código)
13. [Ejemplos de Uso](#ejemplos-de-uso)

---

## 1. INTRODUCCIÓN

### 1.1 Propósito de esta Guía

Este documento establece los estándares visuales y de desarrollo para el Sistema de Gestión de Joyería Torre Fuerte. Su objetivo es:

- ✅ Mantener consistencia visual en todo el sistema
- ✅ Facilitar el trabajo en equipo
- ✅ Acelerar el desarrollo con componentes reutilizables
- ✅ Cumplir con los requisitos del cliente
- ✅ Garantizar una experiencia de usuario profesional

### 1.2 Principios de Diseño

**Formalidad:** Diseño tradicional y clásico, apropiado para una joyería establecida.

**Claridad:** Interfaces limpias e intuitivas, inspiradas en apps bancarias y WhatsApp.

**Elegancia:** Uso de dorado y azul para transmitir lujo y confianza.

**Accesibilidad:** Texto legible, colores con buen contraste, diseño responsive.

### 1.3 Inspiración

- **Apps bancarias:** Organización clara, secciones bien definidas
- **WhatsApp:** Interfaz simple y funcional
- **Joyerías de prestigio:** Uso de dorado y colores elegantes

---

## 2. PALETA DE COLORES

### 2.1 Colores Principales

Estos son los colores base que definen la identidad del sistema:

#### 🟡 Dorado (Color Principal)
```css
--color-dorado: #D4AF37;
--color-dorado-oscuro: #b8941f;
--color-dorado-claro: #e6c960;
```

**Uso:**
- Botones primarios
- Bordes destacados
- Acentos importantes
- Elementos interactivos activos

**Ejemplo visual:**
- Botón "Iniciar Sesión"
- Borde del logo
- Líneas divisoras importantes
- Hover en elementos del menú

---

#### 🔵 Azul (Color Secundario)
```css
--color-azul: #1e3a8a;
--color-azul-claro: #3b82f6;
--color-azul-muy-claro: #eff6ff;
```

**Uso:**
- Navbar principal
- Headers de secciones
- Tarjetas de información
- Fondos de login

**Ejemplo visual:**
- Barra de navegación superior
- Tarjeta de bienvenida en dashboard
- Fondo del login
- Tarjetas con información de clientes

---

#### ⚪ Plateado (Color Terciario)
```css
--color-plateado: #C0C0C0;
--color-plateado-oscuro: #a8a8a8;
--color-plateado-claro: #e5e5e5;
```

**Uso:**
- Acentos secundarios
- Bordes suaves
- Fondos de tarjetas secundarias
- Separadores visuales

---

#### ⚫ Negro (Textos)
```css
--color-negro: #1a1a1a;
--color-gris-oscuro: #4b5563;
--color-gris: #6b7280;
--color-gris-claro: #9ca3af;
```

**Uso:**
- Textos principales (#1a1a1a)
- Textos secundarios (#4b5563)
- Textos terciarios (#6b7280)
- Textos deshabilitados (#9ca3af)

---

### 2.2 Colores de Estado

Estos colores comunican el estado de acciones y elementos:

#### 🟢 Verde (Éxito)
```css
--color-verde: #059669;
--color-verde-claro: #ecfdf5;
```

**Uso:**
- Mensajes de éxito
- Confirmaciones
- Estados positivos (stock suficiente, pago completado)
- Iconos de verificación

---

#### 🔴 Rojo (Error/Peligro)
```css
--color-rojo: #dc2626;
--color-rojo-claro: #fee;
```

**Uso:**
- Mensajes de error
- Advertencias críticas
- Estados negativos (stock agotado, pago vencido)
- Botones de eliminar

---

#### 🟡 Amarillo (Advertencia)
```css
--color-amarillo: #f59e0b;
--color-amarillo-claro: #fffbeb;
```

**Uso:**
- Alertas de advertencia
- Estados que requieren atención (stock bajo)
- Información importante pero no crítica

---

#### 🔵 Celeste (Información)
```css
--color-celeste: #3b82f6;
--color-celeste-claro: #dbeafe;
```

**Uso:**
- Mensajes informativos
- Tooltips
- Estados neutrales

---

### 2.3 Colores de Fondo

```css
--color-blanco: #FFFFFF;
--color-fondo: #f9fafb;
--color-fondo-gris: #f3f4f6;
```

**Uso:**
- `--color-blanco`: Tarjetas, modales, fondos de contenido
- `--color-fondo`: Fondo principal de páginas
- `--color-fondo-gris`: Fondos alternativos, secciones secundarias

---

### 2.4 Variables CSS (Implementación)

**Archivo:** Incluir en `<style>` de cada página o en `assets/css/estilos.css`

```css
:root {
    /* Colores principales */
    --color-dorado: #D4AF37;
    --color-dorado-oscuro: #b8941f;
    --color-dorado-claro: #e6c960;
    
    --color-azul: #1e3a8a;
    --color-azul-claro: #3b82f6;
    --color-azul-muy-claro: #eff6ff;
    
    --color-plateado: #C0C0C0;
    --color-plateado-oscuro: #a8a8a8;
    --color-plateado-claro: #e5e5e5;
    
    --color-negro: #1a1a1a;
    --color-gris-oscuro: #4b5563;
    --color-gris: #6b7280;
    --color-gris-claro: #9ca3af;
    
    /* Colores de estado */
    --color-verde: #059669;
    --color-verde-claro: #ecfdf5;
    
    --color-rojo: #dc2626;
    --color-rojo-claro: #fee;
    
    --color-amarillo: #f59e0b;
    --color-amarillo-claro: #fffbeb;
    
    --color-celeste: #3b82f6;
    --color-celeste-claro: #dbeafe;
    
    /* Fondos */
    --color-blanco: #FFFFFF;
    --color-fondo: #f9fafb;
    --color-fondo-gris: #f3f4f6;
    
    /* Sombras */
    --sombra-sm: 0 2px 4px rgba(0,0,0,0.05);
    --sombra-md: 0 2px 8px rgba(0,0,0,0.08);
    --sombra-lg: 0 4px 12px rgba(0,0,0,0.12);
    --sombra-xl: 0 8px 24px rgba(0,0,0,0.15);
    
    /* Bordes */
    --borde-radius-sm: 6px;
    --borde-radius-md: 10px;
    --borde-radius-lg: 12px;
    --borde-width: 2px;
}
```

---

## 3. TIPOGRAFÍA

### 3.1 Fuentes Utilizadas

**Fuentes principales:** Sans-serif modernas para máxima legibilidad

#### Fuente Principal (Textos)
```css
font-family: 'Inter', sans-serif;
```

**Características:**
- Diseñada para pantallas
- Excelente legibilidad
- Múltiples pesos disponibles
- Gratuita (Google Fonts)

**Pesos usados:**
- Regular (400): Textos normales
- Medium (500): Textos destacados
- SemiBold (600): Subtítulos
- Bold (700): Títulos importantes

---

#### Fuente Secundaria (Títulos)
```css
font-family: 'Montserrat', sans-serif;
```

**Características:**
- Moderna y profesional
- Impacto visual
- Para títulos y encabezados
- Gratuita (Google Fonts)

**Pesos usados:**
- SemiBold (600): Subtítulos
- Bold (700): Títulos principales

---

### 3.2 Jerarquía Tipográfica

#### Títulos Principales (H1)
```css
h1 {
    font-family: 'Montserrat', sans-serif;
    font-size: 2rem;        /* 32px */
    font-weight: 700;
    color: var(--color-negro);
    margin-bottom: 1rem;
    line-height: 1.2;
}
```

**Uso:** Título principal de cada página

---

#### Subtítulos Principales (H2)
```css
h2 {
    font-family: 'Montserrat', sans-serif;
    font-size: 1.5rem;      /* 24px */
    font-weight: 600;
    color: var(--color-negro);
    margin-bottom: 0.75rem;
    line-height: 1.3;
}
```

**Uso:** Secciones principales dentro de una página

---

#### Subtítulos Secundarios (H3)
```css
h3 {
    font-family: 'Inter', sans-serif;
    font-size: 1.25rem;     /* 20px */
    font-weight: 600;
    color: var(--color-gris-oscuro);
    margin-bottom: 0.5rem;
    line-height: 1.4;
}
```

**Uso:** Subsecciones

---

#### Texto Normal
```css
body, p {
    font-family: 'Inter', sans-serif;
    font-size: 1rem;        /* 16px */
    font-weight: 400;
    color: var(--color-negro);
    line-height: 1.6;
}
```

**Uso:** Párrafos, textos generales

---

#### Texto Pequeño
```css
.text-small, small {
    font-size: 0.875rem;    /* 14px */
    color: var(--color-gris);
}
```

**Uso:** Notas, fechas, información secundaria

---

#### Texto Destacado
```css
strong, .text-bold {
    font-weight: 600;
}
```

---

### 3.3 Importar Fuentes

**En el `<head>` de cada página HTML:**

```html
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@600;700&display=swap" rel="stylesheet">
```

---

## 4. LOGO E IDENTIDAD

### 4.1 Especificaciones del Logo

**Archivo:** `assets/img/logo-torre-fuerte.png`

**Formato:** PNG con fondo transparente

**Dimensiones:**
- Archivo principal: 256×256 px (1:1)
- Navbar: 40×40 px
- Login header: 100×100 px
- Favicon: 64×64 px

**Uso:**
```html
<!-- En navbar -->
<img src="assets/img/logo-torre-fuerte.png" alt="Logo Torre Fuerte" style="width: 40px; height: 40px;">

<!-- En login -->
<img src="assets/img/logo-torre-fuerte.png" alt="Logo Torre Fuerte" style="width: 100px; height: 100px;">
```

---

### 4.2 Espacio y Contexto

**Contenedor del logo en login:**
```css
.logo-container {
    width: 100px;
    height: 100px;
    margin: 0 auto 20px;
    background: var(--color-blanco);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 15px rgba(212, 175, 55, 0.4);
    border: 3px solid var(--color-dorado);
}

.logo-container img {
    width: 70px;
    height: 70px;
    object-fit: contain;
}
```

---

### 4.3 Nombre del Sistema

**Texto:** "Joyería Torre Fuerte"

**Tipografía en navbar:**
```css
.navbar-brand {
    font-family: 'Montserrat', sans-serif;
    font-size: 1.4rem;
    font-weight: 700;
    color: var(--color-blanco);
}
```

---

## 5. COMPONENTES UI

### 5.1 Botones

#### Botón Primario (Dorado)
```html
<button class="btn btn-primary">
    <i class="bi bi-save me-2"></i>
    Guardar
</button>
```

**CSS:**
```css
.btn-primary {
    background: var(--color-dorado);
    border: 2px solid var(--color-dorado);
    color: var(--color-blanco);
    padding: 12px 24px;
    border-radius: var(--borde-radius-sm);
    font-weight: 600;
    font-family: 'Inter', sans-serif;
    transition: all 0.3s;
}

.btn-primary:hover {
    background: var(--color-dorado-oscuro);
    border-color: var(--color-dorado-oscuro);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(212, 175, 55, 0.3);
}
```

---

#### Botón Secundario (Azul)
```html
<button class="btn btn-secondary">
    <i class="bi bi-search me-2"></i>
    Buscar
</button>
```

**CSS:**
```css
.btn-secondary {
    background: var(--color-azul);
    border: 2px solid var(--color-azul);
    color: var(--color-blanco);
    padding: 12px 24px;
    border-radius: var(--borde-radius-sm);
    font-weight: 600;
}

.btn-secondary:hover {
    background: var(--color-azul-claro);
    border-color: var(--color-azul-claro);
}
```

---

#### Botón de Peligro (Rojo)
```html
<button class="btn btn-danger">
    <i class="bi bi-trash me-2"></i>
    Eliminar
</button>
```

**CSS:**
```css
.btn-danger {
    background: var(--color-rojo);
    border: 2px solid var(--color-rojo);
    color: var(--color-blanco);
}

.btn-danger:hover {
    background: #b91c1c;
    border-color: #b91c1c;
}
```

---

#### Botón Outline
```html
<button class="btn btn-outline">
    <i class="bi bi-x me-2"></i>
    Cancelar
</button>
```

**CSS:**
```css
.btn-outline {
    background: transparent;
    border: 2px solid var(--color-gris);
    color: var(--color-gris-oscuro);
}

.btn-outline:hover {
    background: var(--color-fondo-gris);
    border-color: var(--color-gris-oscuro);
}
```

---

### 5.2 Tarjetas (Cards)

#### Tarjeta de Estadística
```html
<div class="stat-card dorado">
    <div class="stat-icon">
        <i class="bi bi-box-seam"></i>
    </div>
    <div class="stat-value">25</div>
    <div class="stat-label">Productos Activos</div>
</div>
```

**CSS:**
```css
.stat-card {
    background: var(--color-blanco);
    border-radius: var(--borde-radius-md);
    padding: 25px;
    box-shadow: var(--sombra-md);
    transition: all 0.3s;
    border-left: 5px solid;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--sombra-lg);
}

.stat-card.dorado { border-left-color: var(--color-dorado); }
.stat-card.azul { border-left-color: var(--color-azul); }
.stat-card.verde { border-left-color: var(--color-verde); }
.stat-card.amarillo { border-left-color: var(--color-amarillo); }
.stat-card.rojo { border-left-color: var(--color-rojo); }

.stat-icon {
    width: 55px;
    height: 55px;
    border-radius: var(--borde-radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    margin-bottom: 15px;
}

.stat-card.dorado .stat-icon {
    background: var(--color-amarillo-claro);
    color: var(--color-dorado);
}

.stat-value {
    font-size: 2.2rem;
    font-weight: 700;
    margin: 10px 0;
    color: var(--color-negro);
    font-family: 'Montserrat', sans-serif;
}

.stat-label {
    color: var(--color-gris);
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
}
```

---

#### Tarjeta de Contenido
```html
<div class="content-card">
    <div class="card-header">
        <h3>Título de Sección</h3>
    </div>
    <div class="card-body">
        <p>Contenido de la tarjeta...</p>
    </div>
</div>
```

**CSS:**
```css
.content-card {
    background: var(--color-blanco);
    border-radius: var(--borde-radius-md);
    box-shadow: var(--sombra-md);
    overflow: hidden;
}

.card-header {
    padding: 20px 25px;
    border-bottom: 2px solid var(--color-dorado);
    background: var(--color-fondo);
}

.card-header h3 {
    margin: 0;
    font-family: 'Montserrat', sans-serif;
    font-size: 1.25rem;
    color: var(--color-negro);
}

.card-body {
    padding: 25px;
}
```

---

### 5.3 Navbar

```html
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">
            <img src="assets/img/logo-torre-fuerte.png" alt="Logo" style="width: 40px; height: 40px;">
            Joyería Torre Fuerte
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i>
                        Usuario
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

**CSS:**
```css
.navbar {
    background: var(--color-azul);
    box-shadow: var(--sombra-md);
    padding: 12px 0;
    border-bottom: 3px solid var(--color-dorado);
}

.navbar-brand {
    font-weight: 700;
    font-size: 1.4rem;
    color: var(--color-blanco) !important;
    font-family: 'Montserrat', sans-serif;
    display: flex;
    align-items: center;
    gap: 12px;
}

.nav-link {
    color: rgba(255,255,255,0.9) !important;
    font-weight: 500;
    transition: all 0.3s;
}

.nav-link:hover {
    color: var(--color-dorado) !important;
}

.dropdown-menu {
    border: 2px solid var(--color-dorado);
    box-shadow: var(--sombra-lg);
}
```

---

## 6. ESTRUCTURA DE PÁGINAS

### 6.1 Plantilla HTML Básica

```html
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Título de Página - Joyería Torre Fuerte</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@600;700&display=swap" rel="stylesheet">
    
    <!-- CSS Personalizado -->
    <style>
        :root {
            /* Variables de colores aquí */
        }
        
        body {
            background: var(--color-fondo);
            font-family: 'Inter', sans-serif;
        }
        
        /* Más estilos aquí */
    </style>
</head>
<body>
    <!-- Navbar aquí -->
    
    <div class="container-fluid main-content">
        <!-- Contenido de la página -->
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- JS Personalizado -->
    <script>
        // JavaScript aquí
    </script>
</body>
</html>
```

---

### 6.2 Espaciado y Layout

```css
.main-content {
    padding: 30px;
    min-height: calc(100vh - 80px);
}

/* Espaciado entre secciones */
.section {
    margin-bottom: 30px;
}

/* Espaciado entre elementos */
.mb-small { margin-bottom: 15px; }
.mb-medium { margin-bottom: 25px; }
.mb-large { margin-bottom: 40px; }
```

---

## 7. RESPONSIVE DESIGN

### 7.1 Breakpoints

```css
/* Mobile First */
/* Extra Small (default): < 576px */

/* Small devices (tablets): ≥ 576px */
@media (min-width: 576px) { }

/* Medium devices (desktops): ≥ 768px */
@media (min-width: 768px) { }

/* Large devices (large desktops): ≥ 992px */
@media (min-width: 992px) { }

/* Extra large devices: ≥ 1200px */
@media (min-width: 1200px) { }
```

---

### 7.2 Ajustes Mobile

```css
@media (max-width: 768px) {
    /* Reducir tamaño de títulos */
    h1 { font-size: 1.5rem; }
    h2 { font-size: 1.25rem; }
    
    /* Padding reducido */
    .main-content {
        padding: 15px;
    }
    
    /* Tarjetas a full width */
    .stat-card {
        margin-bottom: 15px;
    }
    
    /* Botones a full width */
    .btn-mobile-full {
        width: 100%;
        margin-bottom: 10px;
    }
}
```

---

## 8. ICONOGRAFÍA

### 8.1 Librería de Iconos

**Usamos:** Bootstrap Icons 1.11.0

**CDN:**
```html
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
```

---

### 8.2 Iconos por Módulo

| Módulo | Icono | Código |
|--------|-------|--------|
| Dashboard | Velocímetro | `<i class="bi bi-speedometer2"></i>` |
| Inventario | Caja | `<i class="bi bi-box-seam"></i>` |
| Ventas | Carrito | `<i class="bi bi-cart"></i>` |
| Taller | Herramientas | `<i class="bi bi-tools"></i>` |
| Clientes | Personas | `<i class="bi bi-people"></i>` |
| Caja | Monedas | `<i class="bi bi-cash-coin"></i>` |
| Proveedores | Camión | `<i class="bi bi-truck"></i>` |
| Reportes | Gráfico | `<i class="bi bi-graph-up"></i>` |
| Configuración | Engranaje | `<i class="bi bi-gear"></i>` |

---

### 8.3 Iconos de Acciones

| Acción | Icono | Código |
|--------|-------|--------|
| Crear/Nuevo | Más | `<i class="bi bi-plus-circle"></i>` |
| Editar | Lápiz | `<i class="bi bi-pencil"></i>` |
| Eliminar | Basura | `<i class="bi bi-trash"></i>` |
| Ver | Ojo | `<i class="bi bi-eye"></i>` |
| Guardar | Disco | `<i class="bi bi-save"></i>` |
| Buscar | Lupa | `<i class="bi bi-search"></i>` |
| Descargar | Flecha abajo | `<i class="bi bi-download"></i>` |
| Imprimir | Impresora | `<i class="bi bi-printer"></i>` |
| Éxito | Check círculo | `<i class="bi bi-check-circle"></i>` |
| Error | X círculo | `<i class="bi bi-x-circle"></i>` |
| Advertencia | Triángulo | `<i class="bi bi-exclamation-triangle"></i>` |

---

## 9. FORMULARIOS

### 9.1 Campos de Entrada

```html
<div class="mb-3">
    <label for="campo" class="form-label">
        <i class="bi bi-tag me-1"></i> Etiqueta del Campo
    </label>
    <input 
        type="text" 
        class="form-control" 
        id="campo" 
        name="campo"
        placeholder="Ingrese texto..."
        required
    >
</div>
```

**CSS:**
```css
.form-label {
    font-weight: 600;
    color: var(--color-negro);
    margin-bottom: 8px;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.form-control {
    border-radius: var(--borde-radius-sm);
    border: 2px solid #d1d5db;
    padding: 12px 15px;
    font-size: 15px;
    transition: all 0.3s;
    font-family: 'Inter', sans-serif;
}

.form-control:focus {
    border-color: var(--color-dorado);
    box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1);
    outline: none;
}
```

---

### 9.2 Input Group (con icono)

```html
<div class="input-group mb-3">
    <span class="input-group-text">
        <i class="bi bi-person"></i>
    </span>
    <input type="text" class="form-control" placeholder="Nombre">
</div>
```

**CSS:**
```css
.input-group-text {
    border-radius: var(--borde-radius-sm) 0 0 var(--borde-radius-sm);
    border: 2px solid #d1d5db;
    border-right: none;
    background: #f9fafb;
    color: var(--color-azul);
}

.input-group .form-control {
    border-left: none;
    border-radius: 0 var(--borde-radius-sm) var(--borde-radius-sm) 0;
}

.input-group:focus-within .input-group-text {
    border-color: var(--color-dorado);
    background: var(--color-amarillo-claro);
}
```

---

### 9.3 Select

```html
<select class="form-select" name="categoria">
    <option value="">Seleccione una opción</option>
    <option value="1">Opción 1</option>
    <option value="2">Opción 2</option>
</select>
```

---

### 9.4 Textarea

```html
<textarea class="form-control" rows="4" placeholder="Descripción..."></textarea>
```

---

## 10. TABLAS

### 10.1 Tabla Básica

```html
<div class="table-responsive">
    <table class="table table-custom">
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>Producto A</td>
                <td>Q 1,234.56</td>
                <td>
                    <button class="btn btn-sm btn-primary">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-danger">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</div>
```

**CSS:**
```css
.table-custom {
    background: var(--color-blanco);
    border-radius: var(--borde-radius-md);
    overflow: hidden;
}

.table-custom thead {
    background: var(--color-azul);
    color: var(--color-blanco);
}

.table-custom thead th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 13px;
    letter-spacing: 0.5px;
    padding: 15px;
    border: none;
}

.table-custom tbody tr {
    border-bottom: 1px solid #e5e7eb;
    transition: all 0.2s;
}

.table-custom tbody tr:hover {
    background: var(--color-fondo);
}

.table-custom tbody td {
    padding: 12px 15px;
    vertical-align: middle;
}
```

---

## 11. MENSAJES Y ALERTAS

### 11.1 Alerta de Éxito

```html
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i>
    Operación completada exitosamente
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
```

**CSS:**
```css
.alert-success {
    background: var(--color-verde-claro);
    border: 2px solid var(--color-verde);
    color: #065f46;
    border-radius: var(--borde-radius-sm);
    font-weight: 500;
}
```

---

### 11.2 Alerta de Error

```html
<div class="alert alert-danger" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    Ha ocurrido un error
</div>
```

**CSS:**
```css
.alert-danger {
    background: var(--color-rojo-claro);
    border: 2px solid var(--color-rojo);
    color: #991b1b;
    border-radius: var(--borde-radius-sm);
}
```

---

### 11.3 Alerta de Advertencia

```html
<div class="alert alert-warning" role="alert">
    <i class="bi bi-info-circle-fill me-2"></i>
    Información importante
</div>
```

**CSS:**
```css
.alert-warning {
    background: var(--color-amarillo-claro);
    border: 2px solid var(--color-amarillo);
    color: #92400e;
}
```

---

## 12. ESTÁNDARES DE CÓDIGO

### 12.1 Estructura de Archivos PHP

```php
<?php
// ================================================
// MÓDULO: Nombre del Módulo
// PÁGINA: Descripción de la página
// ================================================

// Includes necesarios
require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

// Protección de página
requiere_autenticacion();
requiere_rol(['administrador', 'vendedor']);

// Lógica PHP aquí

// Título de página
$titulo_pagina = 'Título';
?>
<!DOCTYPE html>
<html lang="es">
<!-- HTML aquí -->
</html>
```

---

### 12.2 Nomenclatura

**Variables PHP:**
```php
$nombre_variable = 'valor';
$usuario_nombre = 'Juan';
$total_ventas = 1234.56;
```

**Funciones:**
```php
function nombre_funcion($parametro) {
    // código
}

function obtener_productos_activos() {
    // código
}
```

**Clases CSS:**
```css
.nombre-clase { }
.stat-card { }
.btn-primary { }
```

**IDs:**
```html
<div id="nombre-id"></div>
```

---

### 12.3 Comentarios

**PHP:**
```php
// Comentario de una línea

/**
 * Comentario de función
 * 
 * @param string $email Email del usuario
 * @return bool
 */
function validar_email($email) {
    // código
}
```

**CSS:**
```css
/* === SECCIÓN PRINCIPAL === */

/* Comentario específico */
.clase { }
```

**JavaScript:**
```javascript
// Comentario de una línea

/**
 * Comentario de función
 */
function nombreFuncion() {
    // código
}
```

---

### 12.4 Indentación

- **4 espacios** para PHP
- **4 espacios** para CSS
- **2 espacios** para HTML
- **2 espacios** para JavaScript

---

## 13. EJEMPLOS DE USO

### 13.1 Página de Listado Completa

Ver archivo: `ejemplos/listado-productos.php`

### 13.2 Formulario de Creación

Ver archivo: `ejemplos/nuevo-producto.php`

### 13.3 Dashboard con Estadísticas

Ver archivo: `dashboard.php` (ya implementado)

### 13.4 Login

Ver archivo: `login.php` (ya implementado)

---

## 📚 RECURSOS ADICIONALES

### Documentación Oficial

- **Bootstrap 5:** https://getbootstrap.com/docs/5.3/
- **Bootstrap Icons:** https://icons.getbootstrap.com/
- **Google Fonts:** https://fonts.google.com/

### Herramientas Útiles

- **Generador de paletas:** https://coolors.co/
- **Convertidor de colores:** https://convertingcolors.com/
- **Medidor de contraste:** https://webaim.org/resources/contrastchecker/

---

## 📝 CONTROL DE CAMBIOS

| Versión | Fecha | Cambios | Autor |
|---------|-------|---------|-------|
| 1.0 | 21/01/2026 | Versión inicial | Gerbert Méndez |

---

**Última actualización:** 21 de enero de 2026  
**Próxima revisión:** Al completar Fase 3

═══════════════════════════════════════════════════════════
            🎨 GUÍA DE DISEÑO Y ESTILO - V1.0
              SISTEMA JOYERÍA TORRE FUERTE
═══════════════════════════════════════════════════════════
