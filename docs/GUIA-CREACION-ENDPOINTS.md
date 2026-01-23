# 📘 Guía para Creación de Endpoints API

**Sistema de Gestión - Joyería Torre Fuerte**  
Guía práctica basada en lecciones aprendidas durante el desarrollo

---

## 🎯 Filosofía

> "Revisar primero, codificar después. Probar siempre antes de avanzar."

Los endpoints son la **interfaz entre frontend y backend**. Un buen endpoint es:
- **Predecible**: Sigue estructura estándar
- **Robusto**: Maneja errores apropiadamente  
- **Documentado**: Se explica por sí mismo
- **Seguro**: Valida autenticación y permisos

---

## 📋 Checklist Pre-Desarrollo

**ANTES de escribir una sola línea de código:**

### 1. Revisar el Modelo
```bash
✅ ¿Qué métodos tiene el modelo?
✅ ¿Son estáticos o de instancia?
✅ ¿Qué parámetros reciben?
✅ ¿Qué retornan?
```

**Ejemplo:**
```php
// ❌ Asumir
$productos = $modelo->listarTodos();

// ✅ Verificar primero en el modelo
// Método real: public static function listar($filtros, $pagina, $por_pagina)
$productos = Producto::listar($filtros, 1, 20);
```

### 2. Revisar la Base de Datos
```bash
✅ ¿En qué tabla está cada campo?
✅ ¿Qué columnas existen realmente?
✅ ¿Cuáles son las relaciones (foreign keys)?
```

**Usar siempre `base_datos.txt` como referencia.**

### 3. Definir el Endpoint
```bash
✅ ¿Qué hace? (listar, crear, actualizar, eliminar, buscar)
✅ ¿Método HTTP? (GET para lectura, POST para escritura)
✅ ¿Parámetros requeridos?
✅ ¿Parámetros opcionales?
✅ ¿Qué permisos necesita?
```

---

## 🏗️ Estructura Estándar de un Endpoint

**TODOS los endpoints siguen esta estructura:**

```php
<?php
/**
 * ================================================
 * API: [NOMBRE DEL ENDPOINT]
 * ================================================
 * [Descripción breve de qué hace]
 * 
 * Método: GET/POST
 * Autenticación: Requerida
 * Permisos: modulo.accion
 * 
 * Parámetros [GET/POST]:
 * - parametro1: Descripción (requerido/opcional)
 * - parametro2: Descripción (requerido/opcional)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {...},
 *   "message": "..."
 * }
 */

// ================================================
// 1. INCLUDES (siempre los mismos)
// ================================================
require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/[modelo].php';

// ================================================
// 2. HEADERS
// ================================================
header('Content-Type: application/json; charset=utf-8');

// ================================================
// 3. VERIFICACIONES DE SEGURIDAD
// ================================================
verificar_api_autenticacion();
validar_metodo_http('GET'); // o 'POST'
verificar_api_permiso('modulo', 'accion');

// ================================================
// 4. LÓGICA DEL ENDPOINT
// ================================================
try {
    // 4.1 Obtener y validar parámetros
    // 4.2 Llamar al modelo
    // 4.3 Preparar respuesta
    // 4.4 Responder con éxito
    
    responder_json(true, $data, 'Mensaje de éxito');
    
} catch (Exception $e) {
    // 5. MANEJO DE ERRORES
    responder_json(false, null, $e->getMessage(), 'CODIGO_ERROR');
}
```

---

## 📝 Patrones por Tipo de Endpoint

### Patrón 1: Listar (GET)

```php
// Parámetros opcionales con defaults
$filtros = [];
if (isset($_GET['categoria_id'])) {
    $filtros['categoria_id'] = (int)$_GET['categoria_id'];
}

$pagina = obtener_get('pagina', 1, 'int');
$por_pagina = obtener_get('por_pagina', 20, 'int');

// Llamar modelo
$registros = Modelo::listar($filtros, $pagina, $por_pagina);
$total = Modelo::contar($filtros);

// Respuesta enriquecida
responder_json(true, [
    'registros' => $registros,
    'total' => $total,
    'pagina' => $pagina,
    'por_pagina' => $por_pagina,
    'total_paginas' => ceil($total / $por_pagina)
], "{$total} registro(s) encontrado(s)");
```

### Patrón 2: Crear (POST)

```php
// Validar campos requeridos
validar_campos_requeridos(['campo1', 'campo2'], 'POST');

// Obtener datos
$datos = [
    'campo1' => obtener_post('campo1', null, 'string'),
    'campo2' => obtener_post('campo2', null, 'int'),
    'campo_opcional' => obtener_post('campo_opcional', null, 'string')
];

// Validar con el modelo (si aplica)
$errores = Modelo::validar($datos);
if (!empty($errores)) {
    responder_json(false, ['errores' => $errores], 
        'Errores de validación', 'VALIDACION_FALLIDA');
}

// Crear
$id = Modelo::crear($datos);
if (!$id) {
    throw new Exception('No se pudo crear el registro');
}

// Obtener el registro creado
$registro = Modelo::obtenerPorId($id);

responder_json(true, ['id' => $id, 'registro' => $registro], 
    'Registro creado exitosamente');
```

### Patrón 3: Actualizar Parcial (POST)

```php
// Validar ID
validar_campos_requeridos(['id'], 'POST');
$id = obtener_post('id', null, 'int');

// Obtener registro actual
$registro_actual = Modelo::obtenerPorId($id);
if (!$registro_actual) {
    responder_json(false, null, 'Registro no encontrado', 'NO_ENCONTRADO');
}

// Mezclar datos actuales + nuevos
$datos = [
    'campo1' => isset($_POST['campo1']) ? 
        obtener_post('campo1', null, 'string') : 
        $registro_actual['campo1'],
    'campo2' => isset($_POST['campo2']) ? 
        obtener_post('campo2', null, 'int') : 
        $registro_actual['campo2'],
    // ... todos los campos
];

// Validaciones personalizadas (solo campos modificados)
$errores = [];
if (isset($_POST['campo1']) && empty($_POST['campo1'])) {
    $errores[] = 'Campo1 no puede estar vacío';
}
if (!empty($errores)) {
    responder_json(false, ['errores' => $errores], 
        'Errores de validación', 'VALIDACION_FALLIDA');
}

// Actualizar
$resultado = Modelo::actualizar($id, $datos);
if (!$resultado) {
    throw new Exception('No se pudo actualizar');
}

$registro = Modelo::obtenerPorId($id);
responder_json(true, $registro, 'Registro actualizado exitosamente');
```

### Patrón 4: Eliminar (POST)

```php
validar_campos_requeridos(['id'], 'POST');
$id = obtener_post('id', null, 'int');

// Verificar existencia
if (!Modelo::existe($id)) {
    responder_json(false, null, 'Registro no encontrado', 'NO_ENCONTRADO');
}

// Eliminar (soft delete)
$resultado = Modelo::eliminar($id);
if (!$resultado) {
    throw new Exception('No se pudo eliminar');
}

responder_json(true, null, 'Registro eliminado exitosamente');
```

### Patrón 5: Buscar (GET)

```php
// Validar término de búsqueda
if (!isset($_GET['termino']) || empty(trim($_GET['termino']))) {
    responder_json(false, null, 'Término de búsqueda requerido', 'TERMINO_REQUERIDO');
}

$termino = trim($_GET['termino']);
$limite = obtener_get('limite', 10, 'int');

// Buscar
$resultados = Modelo::buscar($termino, $limite);

responder_json(true, $resultados, count($resultados) . ' resultado(s) encontrado(s)');
```

---

## 🔐 Seguridad y Permisos

### Matriz de Permisos

```php
// Lectura - solo requiere ver
verificar_api_permiso('productos', 'ver');

// Crear - requiere crear
verificar_api_permiso('productos', 'crear');

// Actualizar - requiere editar
verificar_api_permiso('productos', 'editar');

// Eliminar - requiere eliminar
verificar_api_permiso('productos', 'eliminar');
```

### Roles especiales

```php
// Administrador y dueño tienen TODOS los permisos automáticamente
// Ver auth.php línea ~100 para matriz completa de permisos por rol
```

---

## ✅ Validaciones

### Regla de Oro
> "Validar solo lo que se envió, no lo que podría enviarse"

### Validaciones en Creación

```php
// Validar TODOS los campos requeridos
validar_campos_requeridos(['campo1', 'campo2', 'campo3'], 'POST');

// Validaciones de negocio
$errores = [];
if ($datos['precio'] <= 0) {
    $errores[] = 'Precio debe ser mayor a 0';
}
if (Modelo::existeCodigo($datos['codigo'])) {
    $errores[] = 'Código ya existe';
}
```

### Validaciones en Actualización

```php
// Validar SOLO los campos que fueron enviados
$errores = [];

if (isset($_POST['precio']) && $_POST['precio'] <= 0) {
    $errores[] = 'Precio debe ser mayor a 0';
}

if (isset($_POST['codigo']) && Modelo::existeCodigo($_POST['codigo'], $id)) {
    $errores[] = 'Código ya existe';
}
```

---

## 📊 Respuestas JSON

### Formato Estándar

```json
{
  "success": true/false,
  "data": {...},           // solo en éxito
  "message": "...",        // opcional
  "error": "...",          // solo en error
  "code": "CODIGO_ERROR"   // solo en error
}
```

### Respuestas Enriquecidas (Listados)

```json
{
  "success": true,
  "data": {
    "registros": [...],
    "total": 50,
    "pagina": 1,
    "por_pagina": 20,
    "total_paginas": 3
  },
  "message": "50 registros encontrados"
}
```

### Respuestas con Resumen

```json
{
  "success": true,
  "data": {
    "items": [...],
    "resumen": {
      "total": 25,
      "activos": 20,
      "inactivos": 5
    }
  }
}
```

### Errores con Contexto

```json
{
  "success": false,
  "error": "Errores de validación: Precio inválido, Código duplicado",
  "code": "VALIDACION_FALLIDA",
  "data": {
    "errores": [
      "Precio inválido",
      "Código duplicado"
    ]
  }
}
```

---

## 🚨 Errores Comunes y Soluciones

### Error 1: Método no existe
```
Fatal error: Call to undefined method Modelo::metodo()
```

**Causa:** No revisaste el modelo antes de codificar  
**Solución:** Abre el modelo y verifica el nombre exacto del método

### Error 2: Columna no existe
```
SQLSTATE[42S22]: Column not found: 'tabla.campo'
```

**Causa:** Asumiste estructura de BD sin verificar  
**Solución:** Revisa `base_datos.txt` para nombres correctos

### Error 3: Validación falla en actualización
```
"error": "Errores de validación: Campo X es requerido"
```

**Causa:** El método `validar()` requiere campos no enviados  
**Solución:** Usa patrón de actualización parcial (obtener datos actuales + mezclar)

### Error 4: Token inválido
```
"error": "No estás autenticado"
```

**Causa:** Token no enviado o inválido  
**Solución:** Verifica header `Authorization: Bearer {token}`

### Error 5: Sin permisos
```
"error": "No tienes permisos para realizar esta acción"
```

**Causa:** Usuario no tiene el rol adecuado  
**Solución:** Verifica matriz de permisos en `auth.php` o usa rol administrador

### Error 6: Parámetros incorrectos
```
"error": "Campos requeridos faltantes: campo1, campo2"
```

**Causa:** No se enviaron campos requeridos  
**Solución:** Verifica que el frontend envíe todos los campos necesarios

---

## 🧪 Testing con Thunder Client

### 1. Configurar Colección

```
Colección: Joyería Torre Fuerte
├── Auth
│   └── Login
├── Productos
│   ├── Listar
│   ├── Buscar
│   ├── Crear
│   ├── Actualizar
│   └── Eliminar
└── Inventario
    └── ...
```

### 2. Variables de Entorno

```
token: {tu_token_aqui}
base_url: http://localhost/joyeria-torre-fuerte
```

### 3. Request Template

**GET:**
```
GET {{base_url}}/api/productos/listar.php?activo=1
Authorization: Bearer {{token}}
```

**POST:**
```
POST {{base_url}}/api/productos/crear.php
Authorization: Bearer {{token}}
Body (Form):
  campo1: valor1
  campo2: valor2
```

### 4. Orden de Pruebas

1. ✅ Login (obtener token)
2. ✅ Listar (GET simple)
3. ✅ Crear (POST)
4. ✅ Actualizar (POST con id del creado)
5. ✅ Eliminar (POST con id del creado)

---

## ⚡ Tips de Productividad

### 1. Template Base
Crea un archivo `_template.php` con la estructura estándar y cópialo cada vez.

### 2. Snippets de VS Code
```json
{
  "API Endpoint": {
    "prefix": "apiend",
    "body": [
      "<?php",
      "require_once '../../config.php';",
      "require_once '../../includes/db.php';",
      "require_once '../../includes/api-helpers.php';",
      "require_once '../../models/${1:modelo}.php';",
      "",
      "header('Content-Type: application/json; charset=utf-8');",
      "",
      "verificar_api_autenticacion();",
      "validar_metodo_http('${2:GET}');",
      "verificar_api_permiso('${3:modulo}', '${4:ver}');",
      "",
      "try {",
      "    $0",
      "} catch (Exception \\$e) {",
      "    responder_json(false, null, \\$e->getMessage(), 'ERROR');",
      "}"
    ]
  }
}
```

### 3. Comentarios Útiles
```php
// TODO: Agregar validación de stock
// FIXME: Este método es lento, optimizar consulta
// NOTE: Este endpoint requiere transacción SQL
```

### 4. Git Commits Descriptivos
```bash
git commit -m "feat: Agregar endpoint listar productos con paginación"
git commit -m "fix: Corregir validación en actualizar producto"
git commit -m "refactor: Optimizar consulta en bajo_stock.php"
```

---

## 📈 Workflow Recomendado

```
1. Planificar
   ├─ Revisar modelo
   ├─ Revisar BD
   └─ Definir endpoint

2. Desarrollar
   ├─ Copiar template
   ├─ Ajustar verificaciones
   ├─ Implementar lógica
   └─ Agregar manejo de errores

3. Probar
   ├─ Crear request en Thunder Client
   ├─ Probar caso exitoso
   ├─ Probar casos de error
   └─ Verificar respuesta JSON

4. Documentar
   ├─ Comentarios en código
   └─ Agregar a docs/api-reference.md

5. Commit
   └─ git commit con mensaje descriptivo
```

---

## ✨ Principios SOLID para APIs

### Single Responsibility
Cada endpoint hace **una sola cosa** bien hecha.

### Open/Closed
Usa funciones de `api-helpers.php` en lugar de repetir código.

### Liskov Substitution
Todos los endpoints responden con el mismo formato JSON.

### Interface Segregation
Los endpoints no dependen de cosas que no necesitan.

### Dependency Inversion
Los endpoints dependen de abstracciones (funciones helper, modelos).

---

## 📚 Referencias Rápidas

**Archivos clave:**
- `/includes/api-helpers.php` - Funciones para endpoints
- `/includes/auth.php` - Sistema de autenticación
- `/includes/db.php` - Conexión y helpers de BD
- `/config.php` - Configuración general
- `base_datos.txt` - Schema de la BD

**Funciones importantes:**
- `responder_json($success, $data, $message, $code)` - Respuesta estándar
- `verificar_api_autenticacion()` - Valida sesión
- `verificar_api_permiso($modulo, $accion)` - Valida permisos
- `validar_metodo_http($metodo)` - Valida GET/POST
- `validar_campos_requeridos($campos, $metodo)` - Valida campos
- `obtener_get($clave, $default, $tipo)` - Obtiene GET sanitizado
- `obtener_post($clave, $default, $tipo)` - Obtiene POST sanitizado

---

## 🎯 Checklist Final

Antes de considerar un endpoint completo:

- [ ] ✅ Código sigue estructura estándar
- [ ] ✅ Verificaciones de seguridad presentes
- [ ] ✅ Validaciones apropiadas implementadas
- [ ] ✅ Manejo de errores con try-catch
- [ ] ✅ Respuestas JSON consistentes
- [ ] ✅ Probado en Thunder Client
- [ ] ✅ Casos de éxito funcionan
- [ ] ✅ Casos de error manejados
- [ ] ✅ Documentado en código
- [ ] ✅ Commit realizado

---

## 🚀 Recuerda

> "Un endpoint bien hecho es como una receta de cocina: cualquiera puede seguirla y obtener el mismo resultado delicioso."

**Mantén:**
- ✅ Consistencia
- ✅ Simplicidad
- ✅ Claridad
- ✅ Seguridad

**Evita:**
- ❌ Asumir en lugar de verificar
- ❌ Copiar/pegar sin entender
- ❌ Código sin probar
- ❌ Validaciones incompletas

---

**Última actualización:** Enero 2026  
**Versión:** 1.0  
**Autor:** Documentación basada en desarrollo real del sistema
