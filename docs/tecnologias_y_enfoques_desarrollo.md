═══════════════════════════════════════════════════════════
   GUÍA DE TECNOLOGÍAS Y ENFOQUES PARA DESARROLLO
   Sistemas de Gestión Web para Hostinger
═══════════════════════════════════════════════════════════

## 📋 ÍNDICE

1. Filosofía de Desarrollo
2. Stack Tecnológico Recomendado
3. Backend: PHP
4. Base de Datos: MySQL
5. Frontend: HTML + CSS + JavaScript
6. Arquitectura del Sistema
7. Librerías y Herramientas Útiles
8. Enfoque de Desarrollo
9. Características Específicas por Sistema
10. Seguridad Integrada
11. Optimización y Rendimiento
12. Testing y Debugging
13. Documentación
14. Resumen del Stack Ideal

---

## 1. FILOSOFÍA DE DESARROLLO

### La Regla de Oro: KISS (Keep It Simple, Stupid)

Antes de entrar en tecnologías específicas, entiende este principio fundamental: 
**mantén todo lo más simple posible**. No porque seas principiante, sino porque 
sistemas simples son más fáciles de mantener, debuggear, y escalar.

**Razones para mantenerlo simple:**

Tus clientes no van a pagar más porque uses React o Vue. Les importa que el 
sistema funcione bien, sea rápido, y lo puedas arreglar cuando algo falle. Un 
sistema simple bien hecho supera a un sistema complejo mal implementado.

Los frameworks modernos y arquitecturas complejas tienen su lugar en 
aplicaciones empresariales masivas. Pero para sistemas de gestión de pequeños 
y medianos negocios en Guatemala, la simplicidad es una virtud, no una debilidad.

**Beneficios de la simplicidad:**
- Menor tiempo de desarrollo (cobras más rápido)
- Fácil de debuggear cuando hay problemas
- Más barato de hostear (menos recursos necesarios)
- Más fácil de explicar y documentar
- Tu cliente puede contratar a otro desarrollador fácilmente si es necesario

---

## 2. STACK TECNOLÓGICO RECOMENDADO

### Por Qué Este Stack Específico

El stack LAMP clásico (Linux, Apache, MySQL, PHP) es perfecto para tu caso 
por varias razones concretas y prácticas.

**Compatibilidad con Hostinger:**
Hostinger está literalmente optimizado para PHP. Es su lenguaje nativo. Cuando 
subes archivos PHP a Hostinger, simplemente funcionan. No necesitas configurar 
nada especial, no hay procesos de build, no hay compilación. Subes y ya.

**Facilidad de deployment:**
Con Node.js tendrías que configurar PM2, manejar procesos, reiniciar servicios. 
Con Python tendrías que configurar WSGI. Con PHP simplemente subes archivos 
.php y Apache los ejecuta automáticamente.

**Recursos y documentación:**
Hay literalmente millones de tutoriales de PHP en español. Cuando tengas un 
problema, una búsqueda rápida en Google te da 10 soluciones. La comunidad es 
masiva y madura.

**Costo de mantenimiento:**
PHP funciona bien en hosting compartido económico. Node.js o Python normalmente 
requieren VPS más caros. Para tus clientes, esto significa que pueden pagar 
Q150/mes en lugar de Q500/mes.

---

## 3. BACKEND: PHP

### Versión y Configuración

**Versión recomendada:** PHP 8.1 o PHP 8.2

No uses versiones antiguas como PHP 7.4 aunque veas tutoriales que las usen. 
Las versiones nuevas son significativamente más rápidas (hasta 2x en algunos 
casos) y más seguras. Además, tienen mejor manejo de tipos y características 
modernas que hacen tu código más limpio.

En hPanel de Hostinger puedes cambiar la versión de PHP con un simple click. 
No hay razón para usar versiones viejas.

### Sin Frameworks Pesados

Para los sistemas que vas a desarrollar (tiendas y consultorios), NO necesitas 
frameworks como Laravel o Symfony. Estos frameworks son excelentes para 
aplicaciones empresariales masivas, pero para tus casos específicos te van a 
complicar la vida innecesariamente.

**Problemas de usar Laravel para estos proyectos:**
- Tiempo de aprendizaje de varias semanas
- Deployment más complicado (necesitas Composer, configurar permisos, etc.)
- Más lento en hosting compartido
- Overhead innecesario para aplicaciones simples
- Más difícil de debuggear cuando algo falla

**Ventajas de PHP "vanilla" bien organizado:**
- Total control sobre tu código
- Deployment ultra simple (subes archivos y ya)
- Más rápido en ejecución
- Fácil de entender para otros desarrolladores
- No dependes de actualizaciones del framework

### PDO para Base de Datos

Usa PDO (PHP Data Objects) para todas tus conexiones a base de datos. Es el 
estándar moderno de PHP y es mucho más seguro que mysqli.

**Ejemplo de conexión con PDO:**

```php
<?php
// config.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'tienda_db');
define('DB_USER', 'usuario');
define('DB_PASS', 'password');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch(PDOException $e) {
    error_log("Error de conexión: " . $e->getMessage());
    die("Error al conectar con la base de datos");
}
?>
```

**Por qué PDO:**
- Soporta prepared statements nativamente (seguridad contra SQL injection)
- Funciona con múltiples bases de datos (MySQL, PostgreSQL, SQLite)
- Manejo de errores más robusto
- Sintaxis más limpia y moderna

**Ejemplo de query segura:**

```php
<?php
// MAL - Vulnerable a SQL injection
$nombre = $_POST['nombre'];
$query = "SELECT * FROM productos WHERE nombre = '$nombre'";
$result = mysqli_query($conn, $query);

// BIEN - Seguro con PDO
$nombre = $_POST['nombre'];
$stmt = $pdo->prepare("SELECT * FROM productos WHERE nombre = ?");
$stmt->execute([$nombre]);
$productos = $stmt->fetchAll();
?>
```

---

## 4. BASE DE DATOS: MySQL

### Por Qué MySQL

MySQL es la elección obvia para tus proyectos por razones muy prácticas.

**Compatibilidad total con Hostinger:**
Hostinger te da MySQL de forma nativa. phpMyAdmin viene incluido. Todo está 
configurado y listo para usar. No tienes que instalar nada adicional.

**Simplicidad de uso:**
Para sistemas de gestión, las relaciones de datos son relativamente simples. 
MySQL maneja esto perfectamente. No necesitas las características avanzadas 
de PostgreSQL.

**Recursos abundantes:**
Hay miles de tutoriales y soluciones para cualquier problema que encuentres. 
La documentación es excelente y en español.

### Diseño de Base de Datos

El diseño de tu base de datos es crítico. Dedica tiempo a pensarlo ANTES de 
empezar a programar. Un mal diseño te va a causar problemas enormes después.

**Proceso recomendado:**

1. **Dibuja en papel** todas las entidades (tablas) que necesitas
2. **Define las relaciones** entre ellas (uno a muchos, muchos a muchos)
3. **Normaliza hasta 3ra forma normal** (elimina redundancia)
4. **Crea el diagrama ER** (usa Draw.io o Excalidraw)
5. **Revisa con Claude** tu diseño antes de implementar

**Ejemplo para Sistema de Tienda:**

```
Tablas necesarias:
├── usuarios (id, nombre, email, password, rol, sucursal_id, activo)
├── sucursales (id, nombre, direccion, telefono)
├── categorias (id, nombre, descripcion)
├── productos (id, codigo, nombre, descripcion, categoria_id, proveedor_id)
├── inventario (id, producto_id, sucursal_id, cantidad, stock_minimo)
├── clientes (id, nombre, nit, telefono, email, direccion)
├── proveedores (id, nombre, nit, telefono, email, direccion)
├── ventas (id, numero, fecha, cliente_id, usuario_id, sucursal_id, total, estado)
├── detalle_ventas (id, venta_id, producto_id, cantidad, precio, subtotal)
├── compras (id, fecha, proveedor_id, usuario_id, sucursal_id, total)
├── detalle_compras (id, compra_id, producto_id, cantidad, precio, subtotal)
└── transferencias (id, fecha, sucursal_origen, sucursal_destino, usuario_id, estado)
```

**Ejemplo para Sistema de Consultorio:**

```
Tablas necesarias:
├── usuarios (id, nombre, email, password, rol, activo)
├── pacientes (id, codigo, nombre, fecha_nacimiento, sexo, telefono, email, direccion)
├── citas (id, fecha, hora_inicio, hora_fin, paciente_id, motivo, estado, tipo)
├── consultas (id, fecha, paciente_id, usuario_id, peso, presion, temperatura)
├── diagnosticos (id, consulta_id, diagnostico, tratamiento, observaciones)
├── recetas (id, consulta_id, medicamentos, indicaciones, fecha_emision)
├── medicamentos_inventario (id, nombre, presentacion, cantidad, fecha_vencimiento)
├── cobros (id, fecha, paciente_id, concepto, monto, forma_pago, estado)
└── archivos_adjuntos (id, paciente_id, consulta_id, tipo, ruta, fecha)
```

### Buenas Prácticas de Base de Datos

**Convenciones de nombres:**
- Tablas en plural: `productos`, `clientes`, `ventas`
- Campos en singular: `nombre`, `precio`, `fecha`
- Primary key siempre: `id` (INT AUTO_INCREMENT)
- Foreign keys descriptivas: `producto_id`, `cliente_id`
- Todo en minúsculas con guiones bajos

**Tipos de datos apropiados:**
```sql
-- IDs
id INT PRIMARY KEY AUTO_INCREMENT

-- Textos cortos
nombre VARCHAR(100)
email VARCHAR(100)
telefono VARCHAR(20)

-- Textos largos
descripcion TEXT
observaciones TEXT

-- Números
precio DECIMAL(10,2)  -- Para dinero SIEMPRE DECIMAL
cantidad INT
stock INT

-- Fechas y tiempo
fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP
fecha_actualizacion DATETIME ON UPDATE CURRENT_TIMESTAMP
fecha DATE
hora TIME

-- Booleanos
activo BOOLEAN DEFAULT 1
```

**Índices para performance:**
```sql
-- Índice en columnas que usas frecuentemente en WHERE
CREATE INDEX idx_producto_nombre ON productos(nombre);
CREATE INDEX idx_venta_fecha ON ventas(fecha);
CREATE INDEX idx_cliente_telefono ON clientes(telefono);

-- Índice único para campos únicos
CREATE UNIQUE INDEX idx_usuario_email ON usuarios(email);
CREATE UNIQUE INDEX idx_producto_codigo ON productos(codigo);
```

---

## 5. FRONTEND: HTML + CSS + JavaScript

### Por Qué NO Usar Frameworks Frontend

Para sistemas de gestión internos, frameworks como React, Vue o Angular son 
overkill (exceso innecesario). Estos frameworks brillan en aplicaciones web 
complejas con interacciones muy dinámicas, pero para tus casos específicos, 
el stack clásico es superior.

**Desventajas de usar React/Vue para estos proyectos:**
- Semanas de aprendizaje antes de ser productivo
- Proceso de build complicado (webpack, vite, etc.)
- Deployment más complejo
- Mayor tamaño de archivos (más lento en conexiones lentas)
- Innecesario para formularios y tablas CRUD simples

**Ventajas del stack clásico:**
- Empiezas a ser productivo desde el día uno
- No hay proceso de build, escribes y ya funciona
- Más liviano y rápido
- Compatible con todos los navegadores sin configuración
- Más fácil de mantener y debuggear

### HTML5 Semántico

Usa HTML5 moderno y semántico. Esto significa usar las etiquetas correctas 
para cada tipo de contenido.

**Ejemplo de HTML semántico:**

```html
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión - Tienda</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/estilos.css">
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <!-- Navegación -->
        </nav>
    </header>
    
    <main class="container mt-4">
        <section>
            <h1>Inventario de Productos</h1>
            <article>
                <!-- Contenido -->
            </article>
        </section>
    </main>
    
    <footer class="bg-light text-center p-3 mt-5">
        <p>&copy; 2025 Sistema de Gestión</p>
    </footer>
    
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/funciones.js"></script>
</body>
</html>
```

### Bootstrap 5 para CSS

Bootstrap es tu mejor amigo para estos proyectos. Te da componentes listos 
que se ven profesionales sin necesidad de ser diseñador.

**Por qué Bootstrap:**
- Grid system responsive (se adapta a móviles automáticamente)
- Componentes prefabricados (botones, formularios, tablas, modals)
- Documentación excelente en español
- Temas y personalizaciones disponibles
- No tienes que inventar diseños desde cero

**Descarga local (IMPORTANTE):**
NO uses CDN para proyectos de clientes. Descarga Bootstrap y guárdalo en tu 
carpeta `assets/`. Si el CDN falla o cambia, tu sistema se rompe.

**Componentes que más usarás:**

```html
<!-- Cards para secciones -->
<div class="card">
    <div class="card-header">
        <h5>Registro de Producto</h5>
    </div>
    <div class="card-body">
        <form>
            <!-- Formulario -->
        </form>
    </div>
</div>

<!-- Tablas con estilo -->
<table class="table table-striped table-hover">
    <thead class="table-dark">
        <tr>
            <th>Código</th>
            <th>Producto</th>
            <th>Stock</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <!-- Datos -->
    </tbody>
</table>

<!-- Botones con iconos -->
<button class="btn btn-primary">
    <i class="bi bi-plus-circle"></i> Nuevo Producto
</button>
<button class="btn btn-danger">
    <i class="bi bi-trash"></i> Eliminar
</button>

<!-- Alerts para mensajes -->
<div class="alert alert-success" role="alert">
    Producto guardado exitosamente
</div>

<!-- Modals para confirmaciones -->
<div class="modal fade" id="confirmarEliminar">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5>¿Eliminar producto?</h5>
            </div>
            <div class="modal-body">
                Esta acción no se puede deshacer
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button class="btn btn-danger">
                    Eliminar
                </button>
            </div>
        </div>
    </div>
</div>
```

### JavaScript Vanilla (Sin jQuery)

Usa JavaScript moderno (ES6+) sin dependencias adicionales. En 2025, JavaScript 
nativo ya tiene todo lo que necesitas.

**NO necesitas jQuery.** Todo lo que jQuery hacía, ahora JavaScript lo hace 
nativamente de forma más eficiente.

**Ejemplos de JavaScript moderno:**

```javascript
// Seleccionar elementos
const boton = document.querySelector('#miBoton');
const tabla = document.querySelector('.tabla-productos');

// Event listeners
boton.addEventListener('click', function() {
    console.log('Click en botón');
});

// Fetch API para peticiones AJAX
async function buscarProducto(codigo) {
    try {
        const response = await fetch(`api/buscar_producto.php?codigo=${codigo}`);
        const data = await response.json();
        
        if (data.success) {
            mostrarProducto(data.producto);
        } else {
            mostrarError(data.mensaje);
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarError('Error al buscar producto');
    }
}

// Manipular DOM
function mostrarProducto(producto) {
    const html = `
        <tr>
            <td>${producto.codigo}</td>
            <td>${producto.nombre}</td>
            <td>${producto.precio}</td>
        </tr>
    `;
    tabla.innerHTML += html;
}

// Validación de formularios
const form = document.querySelector('#formProducto');
form.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const nombre = document.querySelector('#nombre').value.trim();
    const precio = document.querySelector('#precio').value;
    
    if (nombre === '') {
        alert('El nombre es requerido');
        return;
    }
    
    if (precio <= 0) {
        alert('El precio debe ser mayor a 0');
        return;
    }
    
    // Enviar formulario
    this.submit();
});
```

---

## 6. ARQUITECTURA DEL SISTEMA

### Estructura de Carpetas

Organiza tu proyecto desde el día uno. Esta estructura te va a ahorrar dolores 
de cabeza cuando el proyecto crezca.

```
/public_html/
├── index.php                    (Login o página principal)
├── dashboard.php                (Dashboard después de login)
├── logout.php                   (Cerrar sesión)
├── config.php                   (Configuración - NO subir a Git)
├── config.example.php           (Plantilla de config para Git)
│
├── /assets/                     (Recursos estáticos)
│   ├── /css/
│   │   ├── bootstrap.min.css
│   │   └── estilos.css         (Tus estilos personalizados)
│   ├── /js/
│   │   ├── bootstrap.bundle.min.js
│   │   └── funciones.js        (Tu JavaScript)
│   └── /img/
│       ├── logo.png
│       └── placeholder.png
│
├── /includes/                   (Archivos reutilizables)
│   ├── db.php                  (Conexión a BD)
│   ├── funciones.php           (Funciones generales)
│   ├── auth.php                (Verificar autenticación)
│   ├── header.php              (Header común)
│   ├── footer.php              (Footer común)
│   └── navbar.php              (Menú de navegación)
│
├── /models/                     (Lógica de datos)
│   ├── producto.php            (Funciones de productos)
│   ├── cliente.php             (Funciones de clientes)
│   ├── venta.php               (Funciones de ventas)
│   └── usuario.php             (Funciones de usuarios)
│
├── /modules/                    (Páginas del sistema)
│   ├── /inventario/
│   │   ├── lista.php           (Listar productos)
│   │   ├── agregar.php         (Agregar producto)
│   │   ├── editar.php          (Editar producto)
│   │   ├── ver.php             (Ver detalles)
│   │   └── eliminar.php        (Eliminar producto)
│   ├── /ventas/
│   │   ├── nueva.php           (POS - Punto de venta)
│   │   ├── lista.php           (Historial de ventas)
│   │   └── ver.php             (Ver detalle de venta)
│   ├── /clientes/
│   ├── /proveedores/
│   ├── /reportes/
│   └── /usuarios/
│
├── /api/                        (Endpoints para AJAX)
│   ├── buscar_producto.php
│   ├── guardar_venta.php
│   ├── actualizar_stock.php
│   └── generar_reporte.php
│
├── /uploads/                    (Archivos subidos)
│   ├── /productos/
│   ├── /pacientes/
│   └── /documentos/
│
└── /logs/                       (Logs del sistema - NO subir a Git)
    └── php-errors.log
```

**Ventajas de esta estructura:**
- Fácil de navegar
- Escalable (agregar módulos es simple)
- Separación clara de responsabilidades
- Fácil de explicar a otros desarrolladores

### Patrón MVC Simplificado

No necesitas implementar MVC estrictamente con clases y abstracciones, pero 
SÍ seguir la idea general de separar responsabilidades.

**Model (Modelo) - Lógica de Datos:**

```php
<?php
// models/producto.php

function obtenerProductos($pdo, $filtro = '') {
    $sql = "SELECT p.*, c.nombre as categoria 
            FROM productos p 
            LEFT JOIN categorias c ON p.categoria_id = c.id 
            WHERE p.activo = 1";
    
    if ($filtro) {
        $sql .= " AND p.nombre LIKE ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['%' . $filtro . '%']);
    } else {
        $stmt = $pdo->query($sql);
    }
    
    return $stmt->fetchAll();
}

function crearProducto($pdo, $datos) {
    $sql = "INSERT INTO productos (codigo, nombre, precio, categoria_id, stock) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $datos['codigo'],
        $datos['nombre'],
        $datos['precio'],
        $datos['categoria_id'],
        $datos['stock']
    ]);
}

function actualizarProducto($pdo, $id, $datos) {
    $sql = "UPDATE productos 
            SET nombre = ?, precio = ?, categoria_id = ?, stock = ? 
            WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $datos['nombre'],
        $datos['precio'],
        $datos['categoria_id'],
        $datos['stock'],
        $id
    ]);
}
?>
```

**Controller (Controlador) - Lógica de Negocio:**

```php
<?php
// modules/inventario/lista.php

session_start();
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
require_once '../../models/producto.php';

// Verificar autenticación
verificarSesion();

// Procesar filtros
$filtro = isset($_GET['buscar']) ? $_GET['buscar'] : '';

// Obtener datos del modelo
$productos = obtenerProductos($pdo, $filtro);

// Variables para la vista
$titulo = "Inventario de Productos";
$totalProductos = count($productos);

// Cargar vista
include '../../includes/header.php';
?>

<!-- VISTA AQUÍ -->
<div class="container mt-4">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2><?= $titulo ?></h2>
            <p class="text-muted">Total: <?= $totalProductos ?> productos</p>
        </div>
        <div class="col-md-6 text-end">
            <a href="agregar.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nuevo Producto
            </a>
        </div>
    </div>
    
    <!-- Búsqueda -->
    <form method="GET" class="mb-3">
        <div class="input-group">
            <input type="text" name="buscar" class="form-control" 
                   placeholder="Buscar producto..." 
                   value="<?= htmlspecialchars($filtro) ?>">
            <button class="btn btn-outline-secondary" type="submit">Buscar</button>
        </div>
    </form>
    
    <!-- Tabla -->
    <div class="table-responsive">
        <table class="table table-striped table-hover" id="tablaProductos">
            <thead class="table-dark">
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
                <?php foreach($productos as $prod): ?>
                <tr>
                    <td><?= htmlspecialchars($prod['codigo']) ?></td>
                    <td><?= htmlspecialchars($prod['nombre']) ?></td>
                    <td><?= htmlspecialchars($prod['categoria']) ?></td>
                    <td>Q <?= number_format($prod['precio'], 2) ?></td>
                    <td>
                        <span class="badge <?= $prod['stock'] < 10 ? 'bg-danger' : 'bg-success' ?>">
                            <?= $prod['stock'] ?>
                        </span>
                    </td>
                    <td>
                        <a href="ver.php?id=<?= $prod['id'] ?>" class="btn btn-sm btn-info">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="editar.php?id=<?= $prod['id'] ?>" class="btn btn-sm btn-warning">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <a href="eliminar.php?id=<?= $prod['id'] ?>" 
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('¿Eliminar este producto?')">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
```

**View (Vista) - ya está incluida en el ejemplo anterior**

---

## 7. LIBRERÍAS Y HERRAMIENTAS ÚTILES

### Frontend

**Bootstrap 5:**
Ya lo mencionamos extensivamente. Descarga la versión compilada y guárdala 
en `assets/css/` y `assets/js/`. Incluye también Bootstrap Icons.

```html
<link rel="stylesheet" href="assets/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/css/bootstrap-icons.css">
<script src="assets/js/bootstrap.bundle.min.js"></script>
```

**DataTables:**
Esta librería convierte tus tablas HTML en super-tablas con búsqueda, 
ordenamiento, paginación, exportación a Excel/PDF, todo automático.

Descarga: https://datatables.net/download/

```html
<link rel="stylesheet" href="assets/datatables/datatables.min.css">
<script src="assets/datatables/datatables.min.js"></script>

<script>
$('#tablaProductos').DataTable({
    language: {
        url: 'assets/datatables/es-ES.json' // Traducción español
    },
    pageLength: 25,
    order: [[0, 'asc']],
    dom: 'Bfrtip',
    buttons: ['copy', 'excel', 'pdf', 'print']
});
</script>
```

**Chart.js:**
Para gráficos en reportes y dashboard. Fácil de usar y se ve profesional.

Descarga: https://www.chartjs.org/

```html
<canvas id="ventasMensual"></canvas>

<script src="assets/js/chart.min.js"></script>
<script>
const ctx = document.getElementById('ventasMensual');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Enero', 'Febrero', 'Marzo', 'Abril'],
        datasets: [{
            label: 'Ventas Q',
            data: [12000, 19000, 15000, 17000],
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>
```

**SweetAlert2:**
Alertas y confirmaciones hermosas en lugar de los alert() feos de JavaScript.

Descarga: https://sweetalert2.github.io/

```html
<script src="assets/js/sweetalert2.all.min.js"></script>

<script>
// En lugar de: if(confirm("¿Eliminar?"))
Swal.fire({
    title: '¿Eliminar producto?',
    text: "Esta acción no se puede deshacer",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar'
}).then((result) => {
    if (result.isConfirmed) {
        window.location = 'eliminar.php?id=' + id;
    }
});
</script>
```

**FullCalendar:**
Para el calendario de citas del consultorio.

Descarga: https://fullcalendar.io/

```html
<div id="calendario"></div>

<script src="assets/fullcalendar/index.global.min.js"></script>
<script>
const calendar = new FullCalendar.Calendar(document.getElementById('calendario'), {
    initialView: 'dayGridMonth',
    locale: 'es',
    events: 'api/obtener_citas.php',
    dateClick: function(info) {
        // Agendar nueva cita
    }
});
calendar.render();
</script>
```

### Backend

**PHPMailer:**
Para enviar correos electrónicos desde tu sistema (confirmaciones, recordatorios).

Descarga: https://github.com/PHPMailer/PHPMailer

```php
<?php
use PHPMailer\PHPMailer\PHPMailer;
require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/phpmailer/src/SMTP.php';

$mail = new PHPMailer(true);

try {
    // Configuración SMTP
    $mail->isSMTP();
    $mail->Host = 'smtp.hostinger.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'noreply@tutienda.com';
    $mail->Password = 'tu_password';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    
    // Destinatarios
    $mail->setFrom('noreply@tutienda.com', 'Sistema Tienda');
    $mail->addAddress($email_cliente, $nombre_cliente);
    
    // Contenido
    $mail->isHTML(true);
    $mail->Subject = 'Confirmación de Venta';
    $mail->Body = $html_email;
    
    $mail->send();
} catch (Exception $e) {
    error_log("Error al enviar email: {$mail->ErrorInfo}");
}
?>
```

**FPDF:**
Para generar PDFs (tickets, recetas, reportes).

Descarga: http://www.fpdf.org/

```php
<?php
require('fpdf/fpdf.php');

$pdf = new FPDF();
$pdf->AddPage();

// Header
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Ticket de Venta', 0, 1, 'C');
$pdf->Ln(5);

// Datos
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 6, 'Fecha: ' . date('d/m/Y H:i'), 0, 1);
$pdf->Cell(0, 6, 'Cliente: ' . $cliente, 0, 1);
$pdf->Ln(5);

// Tabla de productos
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(80, 6, 'Producto', 1);
$pdf->Cell(30, 6, 'Cant.', 1);
$pdf->Cell(30, 6, 'Precio', 1);
$pdf->Cell(30, 6, 'Subtotal', 1);
$pdf->Ln();

$pdf->SetFont('Arial', '', 10);
foreach($productos as $prod) {
    $pdf->Cell(80, 6, $prod['nombre'], 1);
    $pdf->Cell(30, 6, $prod['cantidad'], 1);
    $pdf->Cell(30, 6, 'Q' . number_format($prod['precio'], 2), 1);
    $pdf->Cell(30, 6, 'Q' . number_format($prod['subtotal'], 2), 1);
    $pdf->Ln();
}

// Total
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(140, 6, 'TOTAL:', 1);
$pdf->Cell(30, 6, 'Q' . number_format($total, 2), 1);

$pdf->Output('D', 'ticket_' . $numero_venta . '.pdf'); // D = Descargar
?>
```

---

## 8. ENFOQUE DE DESARROLLO

### Sistema Monolítico Modular

Tu sistema debe ser monolítico (todo en un solo proyecto) pero modular 
(organizado en módulos independientes).

**Qué significa:**
- Un solo proyecto con una sola base de datos
- Pero dividido en módulos (inventario, ventas, clientes, etc.)
- Cada módulo es independiente pero comparte autenticación y diseño
- Fácil de desarrollar, deployar y mantener

**Ventajas:**
- No necesitas microservicios ni arquitecturas distribuidas complejas
- Deploy ultra simple (subes archivos y ya)
- Una sola base de datos (más fácil de respaldar)
- Menos overhead de comunicación entre servicios

### Server-Side Rendering

Usa rendering del lado del servidor. Cada página genera HTML completo en 
el servidor y lo envía al navegador.

**NO hagas una SPA (Single Page Application)** donde todo el contenido se 
carga con JavaScript. Para sistemas internos, el enfoque tradicional es mejor:

**Ventajas del Server-Side Rendering:**
- Más simple de desarrollar
- Funciona sin JavaScript (accesibilidad)
- Mejor en conexiones lentas
- Botones de navegador (atrás/adelante) funcionan naturalmente
- El SEO no importa (es sistema interno)

**Usa AJAX solo cuando mejore la experiencia:**
- Búsquedas en tiempo real
- Autocompletado
- Actualizar una tabla sin recargar toda la página
- Validaciones asíncronas

### Autenticación Basada en Sesiones

Para estos sistemas usa sesiones PHP tradicionales. No te compliques con 
JWT tokens, OAuth, o sistemas complejos.

**Sistema de login simple y efectivo:**

```php
<?php
// login.php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];
    
    if (!$email) {
        $error = "Email inválido";
    } else {
        require_once 'includes/db.php';
        
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? AND activo = 1");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();
        
        if ($usuario && password_verify($password, $usuario['password'])) {
            // Login exitoso
            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['user_name'] = $usuario['nombre'];
            $_SESSION['user_role'] = $usuario['rol'];
            $_SESSION['user_sucursal'] = $usuario['sucursal_id'];
            $_SESSION['login_time'] = time();
            
            // Regenerar ID de sesión (seguridad)
            session_regenerate_id(true);
            
            header('Location: dashboard.php');
            exit;
        } else {
            $error = "Credenciales incorrectas";
        }
    }
}
?>
```

**Proteger páginas:**

```php
<?php
// includes/auth.php
function verificarSesion() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login.php');
        exit;
    }
    
    // Timeout de sesión (30 minutos)
    if (isset($_SESSION['login_time']) && 
        (time() - $_SESSION['login_time'] > 1800)) {
        session_destroy();
        header('Location: /login.php?timeout=1');
        exit;
    }
    
    $_SESSION['login_time'] = time();
}

function verificarRol($roles_permitidos) {
    if (!in_array($_SESSION['user_role'], $roles_permitidos)) {
        http_response_code(403);
        die('Acceso denegado');
    }
}
?>
```

**Uso en páginas:**

```php
<?php
session_start();
require_once '../../includes/auth.php';

verificarSesion();
verificarRol(['admin', 'gerente']); // Solo admin y gerente pueden acceder

// Resto del código...
?>
```

---

## 9. CARACTERÍSTICAS ESPECÍFICAS POR SISTEMA

### Para la Tienda

**Punto de Venta (POS):**

El POS debe ser simple y rápido. No intentes hacer algo ultra complejo como 
los sistemas comerciales de Q10,000. Un POS funcional es:

1. Campo de búsqueda de producto (por código o nombre)
2. Lista temporal de productos agregados (en JavaScript o sesión)
3. Cálculo automático de total
4. Botón de confirmar venta

```javascript
// pos.js - Carrito en JavaScript
let carrito = [];

function agregarProducto(producto) {
    const existe = carrito.find(item => item.id === producto.id);
    
    if (existe) {
        existe.cantidad++;
    } else {
        carrito.push({
            id: producto.id,
            nombre: producto.nombre,
            precio: producto.precio,
            cantidad: 1
        });
    }
    
    actualizarVista();
}

function calcularTotal() {
    return carrito.reduce((total, item) => {
        return total + (item.precio * item.cantidad);
    }, 0);
}

function confirmarVenta() {
    fetch('api/guardar_venta.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            items: carrito,
            total: calcularTotal(),
            cliente_id: clienteSeleccionado
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            carrito = [];
            actualizarVista();
            imprimirTicket(data.venta_id);
        }
    });
}
```

**Inventario en Tiempo Real:**

Cuando se registra una venta, el inventario debe actualizarse automáticamente. 
Usa transacciones MySQL para que si algo falla, todo se revierta.

```php
<?php
// api/guardar_venta.php
try {
    $pdo->beginTransaction();
    
    // 1. Insertar venta
    $stmt = $pdo->prepare("
        INSERT INTO ventas (fecha, usuario_id, sucursal_id, total, estado) 
        VALUES (NOW(), ?, ?, ?, 'completada')
    ");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_sucursal'], $total]);
    $venta_id = $pdo->lastInsertId();
    
    // 2. Insertar detalle y actualizar inventario
    foreach($items as $item) {
        // Detalle de venta
        $stmt = $pdo->prepare("
            INSERT INTO detalle_ventas (venta_id, producto_id, cantidad, precio, subtotal) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $venta_id,
            $item['id'],
            $item['cantidad'],
            $item['precio'],
            $item['cantidad'] * $item['precio']
        ]);
        
        // Actualizar inventario
        $stmt = $pdo->prepare("
            UPDATE inventario 
            SET cantidad = cantidad - ? 
            WHERE producto_id = ? AND sucursal_id = ?
        ");
        $stmt->execute([
            $item['cantidad'],
            $item['id'],
            $_SESSION['user_sucursal']
        ]);
    }
    
    $pdo->commit();
    echo json_encode(['success' => true, 'venta_id' => $venta_id]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Error en venta: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Error al procesar venta']);
}
?>
```

**Reportes Útiles:**

```php
<?php
// Ventas del día
$stmt = $pdo->prepare("
    SELECT COUNT(*) as total_ventas, SUM(total) as monto_total 
    FROM ventas 
    WHERE DATE(fecha) = CURDATE() AND sucursal_id = ?
");
$stmt->execute([$_SESSION['user_sucursal']]);

// Top 10 productos más vendidos (último mes)
$stmt = $pdo->prepare("
    SELECT p.nombre, SUM(dv.cantidad) as total_vendido 
    FROM detalle_ventas dv 
    JOIN productos p ON dv.producto_id = p.id 
    JOIN ventas v ON dv.venta_id = v.id 
    WHERE v.fecha >= DATE_SUB(NOW(), INTERVAL 1 MONTH) 
    GROUP BY p.id 
    ORDER BY total_vendido DESC 
    LIMIT 10
");
$stmt->execute();

// Productos con stock bajo
$stmt = $pdo->prepare("
    SELECT p.nombre, i.cantidad, i.stock_minimo 
    FROM inventario i 
    JOIN productos p ON i.producto_id = p.id 
    WHERE i.cantidad <= i.stock_minimo AND i.sucursal_id = ?
");
$stmt->execute([$_SESSION['user_sucursal']]);
?>
```

### Para el Consultorio

**Calendario de Citas:**

Usa FullCalendar con backend PHP que devuelva JSON.

```php
<?php
// api/obtener_citas.php
session_start();
require_once '../includes/auth.php';
require_once '../includes/db.php';

verificarSesion();

$start = $_GET['start']; // Fecha inicio del calendario
$end = $_GET['end'];     // Fecha fin del calendario

$stmt = $pdo->prepare("
    SELECT 
        c.id,
        CONCAT(c.fecha, ' ', c.hora_inicio) as start,
        CONCAT(c.fecha, ' ', c.hora_fin) as end,
        CONCAT(p.nombre, ' - ', c.motivo) as title,
        c.estado,
        p.id as paciente_id
    FROM citas c
    JOIN pacientes p ON c.paciente_id = p.id
    WHERE c.fecha BETWEEN ? AND ?
    ORDER BY c.fecha, c.hora_inicio
");
$stmt->execute([$start, $end]);
$citas = $stmt->fetchAll();

// Agregar color según estado
foreach($citas as &$cita) {
    switch($cita['estado']) {
        case 'confirmada':
            $cita['backgroundColor'] = '#28a745';
            break;
        case 'pendiente':
            $cita['backgroundColor'] = '#ffc107';
            break;
        case 'cancelada':
            $cita['backgroundColor'] = '#dc3545';
            break;
    }
}

header('Content-Type: application/json');
echo json_encode($citas);
?>
```

**Historial Clínico:**

```php
<?php
// modules/pacientes/historial.php
$paciente_id = $_GET['id'];

// Información del paciente
$stmt = $pdo->prepare("SELECT * FROM pacientes WHERE id = ?");
$stmt->execute([$paciente_id]);
$paciente = $stmt->fetch();

// Todas las consultas (más reciente primero)
$stmt = $pdo->prepare("
    SELECT 
        c.*,
        u.nombre as doctor
    FROM consultas c
    JOIN usuarios u ON c.usuario_id = u.id
    WHERE c.paciente_id = ?
    ORDER BY c.fecha DESC
");
$stmt->execute([$paciente_id]);
$consultas = $stmt->fetchAll();
?>

<!-- Vista -->
<div class="container">
    <h2>Historial Clínico - <?= htmlspecialchars($paciente['nombre']) ?></h2>
    
    <div class="card mb-3">
        <div class="card-header">Datos del Paciente</div>
        <div class="card-body">
            <p><strong>Edad:</strong> <?= calcularEdad($paciente['fecha_nacimiento']) ?> años</p>
            <p><strong>Tipo de sangre:</strong> <?= $paciente['tipo_sangre'] ?></p>
            <p><strong>Alergias:</strong> <?= $paciente['alergias'] ?></p>
        </div>
    </div>
    
    <h3>Consultas Anteriores</h3>
    <?php foreach($consultas as $consulta): ?>
    <div class="card mb-2">
        <div class="card-header">
            <?= date('d/m/Y H:i', strtotime($consulta['fecha'])) ?> - 
            Dr. <?= htmlspecialchars($consulta['doctor']) ?>
        </div>
        <div class="card-body">
            <p><strong>Diagnóstico:</strong> <?= htmlspecialchars($consulta['diagnostico']) ?></p>
            <p><strong>Tratamiento:</strong> <?= htmlspecialchars($consulta['tratamiento']) ?></p>
            <?php if($consulta['observaciones']): ?>
            <p><strong>Observaciones:</strong> <?= htmlspecialchars($consulta['observaciones']) ?></p>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
```

**Generación de Recetas en PDF:**

```php
<?php
// modules/consultas/generar_receta.php
require('fpdf/fpdf.php');

$consulta_id = $_GET['id'];

// Obtener datos
$stmt = $pdo->prepare("
    SELECT 
        c.*,
        p.nombre as paciente,
        p.fecha_nacimiento,
        u.nombre as doctor,
        u.especialidad,
        u.num_colegiado
    FROM consultas c
    JOIN pacientes p ON c.paciente_id = p.id
    JOIN usuarios u ON c.usuario_id = u.id
    WHERE c.id = ?
");
$stmt->execute([$consulta_id]);
$data = $stmt->fetch();

$pdf = new FPDF();
$pdf->AddPage();

// Header del doctor
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 8, 'Dr. ' . $data['doctor'], 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 5, $data['especialidad'], 0, 1, 'C');
$pdf->Cell(0, 5, 'Col. ' . $data['num_colegiado'], 0, 1, 'C');
$pdf->Ln(10);

// Datos del paciente
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(30, 6, 'Paciente:');
$pdf->Cell(0, 6, $data['paciente'], 0, 1);
$pdf->Cell(30, 6, 'Edad:');
$pdf->Cell(0, 6, calcularEdad($data['fecha_nacimiento']) . ' años', 0, 1);
$pdf->Cell(30, 6, 'Fecha:');
$pdf->Cell(0, 6, date('d/m/Y', strtotime($data['fecha'])), 0, 1);
$pdf->Ln(10);

// Rx (Prescripción)
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, 'Rx', 0, 1);
$pdf->SetFont('Arial', '', 11);

// Aquí los medicamentos de la consulta
$medicamentos = json_decode($data['medicamentos_json'], true);
foreach($medicamentos as $med) {
    $pdf->MultiCell(0, 5, "• {$med['nombre']} - {$med['dosis']} - {$med['frecuencia']} - {$med['duracion']}");
}

$pdf->Ln(10);

// Indicaciones
if($data['indicaciones']) {
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(0, 6, 'Indicaciones:', 0, 1);
    $pdf->SetFont('Arial', '', 10);
    $pdf->MultiCell(0, 5, $data['indicaciones']);
}

// Firma
$pdf->Ln(20);
$pdf->Cell(0, 6, '_____________________________', 0, 1, 'R');
$pdf->Cell(0, 6, 'Dr. ' . $data['doctor'], 0, 1, 'R');

$pdf->Output('D', 'receta_' . $consulta_id . '.pdf');
?>
```

---

## 10. SEGURIDAD INTEGRADA DESDE EL INICIO

La seguridad NO es algo que agregas al final. Debe estar integrada en tu 
forma de programar desde el día uno.

### Prepared Statements - OBLIGATORIO

NUNCA concatenes variables en queries SQL. SIEMPRE usa prepared statements.

```php
<?php
// ❌ MAL - Vulnerable a SQL Injection
$nombre = $_POST['nombre'];
$sql = "SELECT * FROM productos WHERE nombre = '$nombre'";
$result = $pdo->query($sql);

// ✅ BIEN - Seguro
$nombre = $_POST['nombre'];
$stmt = $pdo->prepare("SELECT * FROM productos WHERE nombre = ?");
$stmt->execute([$nombre]);
$result = $stmt->fetchAll();
?>
```

Esto debe ser automático. Ni siquiera consideres la forma insegura.

### Escapar Todo Output

Todo lo que muestres en HTML que venga de la base de datos o del usuario, 
escápalo con htmlspecialchars().

```php
<?php
// ❌ MAL - Vulnerable a XSS
echo "<h1>Bienvenido " . $nombre . "</h1>";

// ✅ BIEN - Seguro
echo "<h1>Bienvenido " . htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') . "</h1>";

// O usa sintaxis corta:
?>
<h1>Bienvenido <?= htmlspecialchars($nombre) ?></h1>
```

### Validación de Datos

Valida TODO input tanto en frontend (UX) como en backend (seguridad).

```php
<?php
// Validar email
$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
if (!$email) {
    die("Email inválido");
}

// Validar entero positivo
$cantidad = filter_var($_POST['cantidad'], FILTER_VALIDATE_INT);
if ($cantidad === false || $cantidad < 1) {
    die("Cantidad inválida");
}

// Validar precio (decimal positivo)
$precio = filter_var($_POST['precio'], FILTER_VALIDATE_FLOAT);
if ($precio === false || $precio <= 0) {
    die("Precio inválido");
}

// Sanitizar texto
$nombre = filter_var($_POST['nombre'], FILTER_SANITIZE_STRING);
if (empty($nombre)) {
    die("El nombre es requerido");
}

// Validar fecha
$fecha = DateTime::createFromFormat('Y-m-d', $_POST['fecha']);
if (!$fecha) {
    die("Fecha inválida");
}
?>
```

### Protección CSRF

Implementa tokens CSRF en formularios importantes.

```php
<?php
// Generar token (en el formulario)
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<form method="POST">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    <!-- resto del formulario -->
</form>

<?php
// Validar token (al procesar)
if (!isset($_POST['csrf_token']) || 
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die('Token CSRF inválido');
}
?>
```

### Headers de Seguridad

Agrega headers de seguridad en todas las páginas.

```php
<?php
// Al inicio de cada página PHP
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: no-referrer-when-downgrade");

// Forzar HTTPS
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    header("Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit;
}
?>
```

### Passwords Seguros

```php
<?php
// Al crear usuario
$password = $_POST['password'];
$password_hash = password_hash($password, PASSWORD_BCRYPT);

$stmt = $pdo->prepare("INSERT INTO usuarios (email, password) VALUES (?, ?)");
$stmt->execute([$email, $password_hash]);

// Al validar login
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
$usuario = $stmt->fetch();

if ($usuario && password_verify($password_ingresado, $usuario['password'])) {
    // Login correcto
} else {
    // Login incorrecto
}
?>
```

### Logs de Auditoría (Para Consultorio)

Para el consultorio médico, es OBLIGATORIO registrar quién accede a qué datos.

```php
<?php
// includes/audit.php
function registrarAcceso($accion, $tabla, $registro_id, $detalles = null) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        INSERT INTO audit_log 
        (usuario_id, accion, tabla, registro_id, detalles, ip_address, user_agent, fecha) 
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([
        $_SESSION['user_id'],
        $accion,
        $tabla,
        $registro_id,
        $detalles,
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT']
    ]);
}

// Uso:
// Cuando vean un paciente
registrarAcceso('VER', 'pacientes', $paciente_id, 'Vio historial clínico');

// Cuando editen consulta
registrarAcceso('EDITAR', 'consultas', $consulta_id, 'Modificó diagnóstico');
?>
```

---

## 11. OPTIMIZACIÓN Y RENDIMIENTO

### Queries Eficientes

Aprende a escribir queries optimizadas desde el principio.

**Paginación:**

```php
<?php
// Número de página
$pagina = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$por_pagina = 50;
$offset = ($pagina - 1) * $por_pagina;

// Total de registros
$total = $pdo->query("SELECT COUNT(*) FROM productos")->fetchColumn();
$total_paginas = ceil($total / $por_pagina);

// Obtener página actual
$stmt = $pdo->prepare("SELECT * FROM productos LIMIT ? OFFSET ?");
$stmt->execute([$por_pagina, $offset]);
$productos = $stmt->fetchAll();
?>

<!-- Paginación -->
<nav>
    <ul class="pagination">
        <?php for($i = 1; $i <= $total_paginas; $i++): ?>
        <li class="page-item <?= $i == $pagina ? 'active' : '' ?>">
            <a class="page-link" href="?p=<?= $i ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
```

**Índices:**

```sql
-- En campos que uses frecuentemente en WHERE
CREATE INDEX idx_producto_nombre ON productos(nombre);
CREATE INDEX idx_venta_fecha ON ventas(fecha);

-- Índices compuestos para queries comunes
CREATE INDEX idx_inventario_lookup ON inventario(sucursal_id, producto_id);

-- Único para campos que deben ser únicos
CREATE UNIQUE INDEX idx_usuario_email ON usuarios(email);
CREATE UNIQUE INDEX idx_producto_codigo ON productos(codigo);
```

### Caché de Sesión

Guarda en sesión datos que no cambian frecuentemente.

```php
<?php
// Al hacer login, guardar datos del usuario
$_SESSION['user_id'] = $usuario['id'];
$_SESSION['user_name'] = $usuario['nombre'];
$_SESSION['user_role'] = $usuario['rol'];
$_SESSION['user_email'] = $usuario['email'];

// En cualquier página, usar directo de sesión
echo "Bienvenido " . $_SESSION['user_name'];
// No necesitas hacer query cada vez
?>
```

### Optimización de Assets

**Combinar archivos CSS/JS:**
En producción, combina todos tus CSS en uno solo y todos tus JS en uno solo.

**Minificar:**
Usa herramientas online para minificar tu CSS y JS (eliminar espacios, comentarios).

**Imágenes:**
Comprime todas las imágenes antes de subirlas. TinyPNG es excelente para esto.

---

## 12. TESTING Y DEBUGGING

### Error Logging

Configura PHP para que registre errores en archivo de log.

```php
<?php
// config.php

if (ENVIRONMENT === 'production') {
    // Producción: NO mostrar errores, guardar en log
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/logs/php-errors.log');
} else {
    // Desarrollo: Mostrar errores
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}
?>
```

### Datos de Prueba

Crea un script para llenar la BD con datos realistas para probar.

```php
<?php
// scripts/seed.php
require_once '../config.php';
require_once '../includes/db.php';

echo "Creando datos de prueba...\n";

// Crear 100 productos
for($i = 1; $i <= 100; $i++) {
    $stmt = $pdo->prepare("
        INSERT INTO productos (codigo, nombre, precio, categoria_id, stock) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        'PROD' . str_pad($i, 5, '0', STR_PAD_LEFT),
        'Producto de Prueba ' . $i,
        rand(10, 500),
        rand(1, 5),
        rand(0, 100)
    ]);
}

echo "100 productos creados\n";

// Crear clientes
for($i = 1; $i <= 50; $i++) {
    $stmt = $pdo->prepare("
        INSERT INTO clientes (nombre, telefono, email) 
        VALUES (?, ?, ?)
    ");
    $stmt->execute([
        'Cliente Test ' . $i,
        '5555-' . str_pad($i, 4, '0', STR_PAD_LEFT),
        'cliente' . $i . '@test.com'
    ]);
}

echo "50 clientes creados\n";
echo "Completado!\n";
?>
```

Ejecutar: `php scripts/seed.php`

### Try-Catch en Operaciones Críticas

```php
<?php
try {
    $pdo->beginTransaction();
    
    // Operaciones...
    
    $pdo->commit();
    echo json_encode(['success' => true]);
    
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Error en venta: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'error' => 'Error al procesar la operación'
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Error general: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'error' => 'Error inesperado'
    ]);
}
?>
```

---

## 13. DOCUMENTACIÓN Y COMENTARIOS

Comenta tu código, especialmente lógica compleja.

```php
<?php
/**
 * Calcula el total de una venta incluyendo descuentos
 * 
 * @param array $items Array de productos con cantidad y precio
 * @param float $descuento_porcentaje Descuento a aplicar (0-100)
 * @return float Total calculado
 */
function calcularTotal($items, $descuento_porcentaje = 0) {
    $subtotal = 0;
    
    foreach($items as $item) {
        $subtotal += $item['precio'] * $item['cantidad'];
    }
    
    if ($descuento_porcentaje > 0) {
        $descuento = $subtotal * ($descuento_porcentaje / 100);
        $subtotal -= $descuento;
    }
    
    return round($subtotal, 2);
}

/**
 * Verifica si hay stock suficiente antes de procesar venta
 * 
 * @param array $items Items a vender
 * @param int $sucursal_id ID de la sucursal
 * @return array ['success' => bool, 'message' => string]
 */
function verificarStock($items, $sucursal_id) {
    global $pdo;
    
    foreach($items as $item) {
        $stmt = $pdo->prepare("
            SELECT cantidad 
            FROM inventario 
            WHERE producto_id = ? AND sucursal_id = ?
        ");
        $stmt->execute([$item['id'], $sucursal_id]);
        $stock_actual = $stmt->fetchColumn();
        
        if ($stock_actual < $item['cantidad']) {
            return [
                'success' => false,
                'message' => "Stock insuficiente para {$item['nombre']}"
            ];
        }
    }
    
    return ['success' => true];
}
?>
```

---

## 14. RESUMEN DEL STACK IDEAL

### Backend
- **PHP 8.1+** con PDO
- **MySQL 5.7+** / MariaDB
- Sin frameworks pesados, código bien organizado
- Estructura MVC simplificada

### Frontend
- **HTML5** semántico
- **Bootstrap 5** para diseño responsive
- **JavaScript ES6+** vanilla (sin jQuery)
- DataTables para tablas avanzadas
- Chart.js para gráficos
- SweetAlert2 para alertas
- FullCalendar para calendario (consultorio)

### Librerías Backend
- **PHPMailer** para correos
- **FPDF** para PDFs

### Arquitectura
- Sistema monolítico modular
- Server-side rendering
- Sesiones PHP para autenticación
- AJAX solo cuando mejore UX

### Seguridad
- Prepared statements (obligatorio)
- htmlspecialchars en todo output
- Validación frontend y backend
- Headers de seguridad
- HTTPS forzado
- Passwords con bcrypt
- Logs de auditoría (consultorio)

### Optimización
- Paginación en listados
- Índices en BD
- Caché de sesión
- Assets minificados
- Imágenes comprimidas

---

## VENTAJAS DE ESTE STACK

✅ **Perfecto para Hostinger:** Todo funciona nativamente
✅ **Fácil deployment:** Subes archivos y ya
✅ **Fácil de mantener:** Código simple y claro
✅ **Documentación abundante:** Millones de tutoriales
✅ **Bajo costo:** Funciona en hosting compartido económico
✅ **Profesional:** Produce sistemas de calidad
✅ **Escalable:** Crece con tus necesidades
✅ **Compatible:** Funciona en todos los navegadores

Este stack no es el más moderno ni el más trendy, pero es el más apropiado 
para el tipo de proyectos que vas a desarrollar. Es sólido, probado en batalla, 
y va a funcionar sin problemas por años.

═══════════════════════════════════════════════════════════
            ¡Éxito en tus proyectos! 🚀
═══════════════════════════════════════════════════════════
