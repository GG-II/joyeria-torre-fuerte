# 📘 GUÍA COMPLETA: Creación de APIs REST - Joyería Torre Fuerte

**Versión:** 1.0  
**Fecha:** 23 de enero de 2026  
**Basada en:** Experiencia de desarrollo de 58 endpoints

---

## 📋 **ÍNDICE**

1. [Estructura de Archivos](#estructura-de-archivos)
2. [Plantilla Base de Endpoint](#plantilla-base-de-endpoint)
3. [Reglas y Convenciones](#reglas-y-convenciones)
4. [Validaciones Requeridas](#validaciones-requeridas)
5. [Manejo de Errores](#manejo-de-errores)
6. [Casos de Uso Comunes](#casos-de-uso-comunes)
7. [Errores Comunes y Soluciones](#errores-comunes-y-soluciones)
8. [Checklist de Calidad](#checklist-de-calidad)
9. [Ejemplos Completos](#ejemplos-completos)
10. [Mejores Prácticas](#mejores-prácticas)

---

## 📁 **ESTRUCTURA DE ARCHIVOS**

### **Organización de Carpetas:**
```
api/
├── productos/
│   ├── listar.php
│   ├── crear.php
│   ├── editar.php
│   ├── detalle.php
│   └── cambiar_estado.php
├── clientes/
│   ├── listar.php
│   ├── crear.php
│   └── ...
└── [modulo]/
    ├── listar.php
    ├── crear.php
    ├── editar.php
    └── ...
```

### **Convenciones de Nombres:**
- **Archivos:** `accion.php` en minúsculas con guiones bajos
- **Ejemplos:**
  - ✅ `listar.php`
  - ✅ `crear.php`
  - ✅ `cambiar_estado.php`
  - ✅ `ajustar_stock.php`
  - ❌ `Listar.php` (no capitalizar)
  - ❌ `cambiarEstado.php` (no camelCase)

---

## 📄 **PLANTILLA BASE DE ENDPOINT**

### **Estructura Completa:**

```php
<?php
/**
 * ================================================
 * API: [DESCRIPCIÓN DEL ENDPOINT]
 * ================================================
 * Descripción detallada de lo que hace el endpoint
 * 
 * Método: GET/POST/PUT/DELETE
 * Autenticación: Requerida/Opcional
 * Permisos: modulo.accion
 * 
 * Parámetros [MÉTODO] requeridos:
 * - param1: Descripción del parámetro
 * - param2: Descripción del parámetro
 * 
 * Parámetros [MÉTODO] opcionales:
 * - param3: Descripción del parámetro (default: valor)
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": {...},
 *   "message": "Mensaje de éxito"
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/modelo.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('POST'); // GET, POST, PUT, DELETE
verificar_api_permiso('modulo', 'accion'); // ver, crear, editar, eliminar

try {
    // PARA POST: Leer JSON body
    $json_input = file_get_contents('php://input');
    $datos = json_decode($json_input, true);
    
    // Fallback a POST para compatibilidad
    if (json_last_error() !== JSON_ERROR_NONE || empty($datos)) {
        $datos = $_POST;
    }
    
    // PARA GET: Leer parámetros
    // $param = isset($_GET['param']) ? $_GET['param'] : null;
    
    // Validar campos requeridos
    if (empty($datos['campo_requerido'])) {
        responder_json(false, null, 'El campo es requerido', 'CAMPO_REQUERIDO');
    }
    
    // Validar tipos de datos
    // Validar valores válidos
    // Validar existencia de registros relacionados
    
    // Ejecutar lógica de negocio
    $resultado = Modelo::metodo($datos);
    
    if (!$resultado) {
        throw new Exception('No se pudo completar la operación');
    }
    
    // Responder con éxito
    responder_json(
        true,
        $resultado,
        'Operación exitosa'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error: ' . $e->getMessage(),
        'ERROR_CODIGO'
    );
}
```

---

## 📏 **REGLAS Y CONVENCIONES**

### **Regla 1: Verificaciones de Seguridad SIEMPRE Primero**

```php
// ✅ CORRECTO - Orden requerido
verificar_api_autenticacion();     // 1. Verificar que hay usuario logueado
validar_metodo_http('POST');        // 2. Verificar método HTTP correcto
verificar_api_permiso('modulo', 'accion'); // 3. Verificar permisos

// ❌ INCORRECTO - No saltarse ninguna
// try {
//     $datos = $_POST; // ← NO, primero verificar seguridad
```

**Razón:** Las verificaciones de seguridad deben ejecutarse antes de cualquier lógica.

---

### **Regla 2: Leer JSON Body Correctamente (POST/PUT)**

```php
// ✅ CORRECTO - Leer JSON y tener fallback
$json_input = file_get_contents('php://input');
$datos = json_decode($json_input, true);

// Fallback a POST
if (json_last_error() !== JSON_ERROR_NONE || empty($datos)) {
    $datos = $_POST;
}

// ❌ INCORRECTO - Solo $_POST no funciona con JSON
// $datos = $_POST; // ← Esto no lee JSON body
```

**Razón:** Los clientes modernos envían JSON, pero algunos clientes viejos usan form-data.

---

### **Regla 3: Validar ANTES de Llamar al Modelo**

```php
// ✅ CORRECTO - Validar en el endpoint
if (empty($datos['nombre'])) {
    responder_json(false, null, 'El nombre es requerido', 'NOMBRE_REQUERIDO');
}

if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
    responder_json(false, null, 'Email inválido', 'EMAIL_INVALIDO');
}

$resultado = Modelo::crear($datos);

// ❌ INCORRECTO - Confiar solo en el modelo
// $resultado = Modelo::crear($datos);
// if (!$resultado) { ... } // ← Muy tarde, no sabemos por qué falló
```

**Razón:** Las validaciones tempranas dan mejor feedback al usuario.

---

### **Regla 4: Usar array() en Lugar de []**

```php
// ✅ CORRECTO - Sintaxis compatible
$datos = array(
    'nombre' => $valor,
    'email' => $email
);

// ❌ EVITAR - Puede causar problemas en PHP < 5.4
// $datos = [
//     'nombre' => $valor
// ];
```

**Razón:** Mayor compatibilidad con versiones de PHP.

---

### **Regla 5: Respuestas JSON Consistentes**

```php
// ✅ CORRECTO - Usar helper
responder_json(
    true,                    // success
    $data,                   // data
    'Mensaje descriptivo',   // message
    'CODIGO_OPCIONAL'        // code (opcional)
);

// ❌ INCORRECTO - JSON manual inconsistente
// echo json_encode(['ok' => true, 'result' => $data]); // ← Inconsistente
```

**Estructura estándar:**
```json
{
  "success": true,
  "data": {...},
  "message": "Operación exitosa"
}
```

---

### **Regla 6: Try-Catch Obligatorio**

```php
// ✅ CORRECTO - Todo dentro de try-catch
try {
    // Toda la lógica aquí
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error: ' . $e->getMessage(),
        'ERROR_CODIGO'
    );
}

// ❌ INCORRECTO - Sin try-catch
// $resultado = Modelo::crear($datos);
// responder_json(...); // ← Si hay excepción, se rompe
```

**Razón:** Manejo consistente de errores.

---

## ✅ **VALIDACIONES REQUERIDAS**

### **1. Validación de Campos Requeridos**

```php
// Lista de campos requeridos
$campos_requeridos = array('nombre', 'email', 'telefono');

foreach ($campos_requeridos as $campo) {
    if (!isset($datos[$campo]) || empty($datos[$campo])) {
        responder_json(false, null, "El campo {$campo} es requerido", 'CAMPO_REQUERIDO');
    }
}
```

---

### **2. Validación de Tipos de Datos**

```php
// Email
if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
    responder_json(false, null, 'Email inválido', 'EMAIL_INVALIDO');
}

// Número positivo
if (!is_numeric($datos['precio']) || $datos['precio'] <= 0) {
    responder_json(false, null, 'El precio debe ser un número positivo', 'PRECIO_INVALIDO');
}

// Entero
$id = (int)$datos['id'];
if ($id <= 0) {
    responder_json(false, null, 'ID inválido', 'ID_INVALIDO');
}

// Fecha
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $datos['fecha'])) {
    responder_json(false, null, 'Formato de fecha inválido (YYYY-MM-DD)', 'FECHA_INVALIDA');
}
```

---

### **3. Validación de Valores Permitidos**

```php
// Lista de valores válidos
$valores_validos = array('activo', 'inactivo', 'pendiente');

if (!in_array($datos['estado'], $valores_validos)) {
    responder_json(
        false, 
        null, 
        'Estado inválido. Use: ' . implode(', ', $valores_validos), 
        'ESTADO_INVALIDO'
    );
}
```

---

### **4. Validación de Existencia**

```php
// Verificar que el registro existe
$registro = Modelo::obtenerPorId($id);

if (!$registro) {
    responder_json(false, null, 'El registro no existe', 'NO_ENCONTRADO');
}

// Verificar que registro relacionado existe
if (!Modelo2::existe($datos['modelo2_id'])) {
    responder_json(false, null, 'El registro relacionado no existe', 'RELACION_NO_ENCONTRADA');
}
```

---

### **5. Validación de Duplicados**

```php
// Verificar que no existe duplicado
if (Modelo::existePorEmail($datos['email'], $id_excluir)) {
    responder_json(false, null, 'El email ya está registrado', 'EMAIL_DUPLICADO');
}
```

---

### **6. Validación de Longitud**

```php
// Longitud mínima
if (strlen($datos['password']) < 6) {
    responder_json(false, null, 'La contraseña debe tener al menos 6 caracteres', 'PASSWORD_MUY_CORTA');
}

// Longitud exacta (teléfono Guatemala)
if (strlen($datos['telefono']) != 8) {
    responder_json(false, null, 'El teléfono debe tener 8 dígitos', 'TELEFONO_INVALIDO');
}
```

---

## ⚠️ **MANEJO DE ERRORES**

### **Estructura de Errores:**

```php
responder_json(
    false,                           // success: false
    null,                            // data: null
    'Mensaje descriptivo del error', // message
    'CODIGO_ERROR'                   // code (MAYÚSCULAS con guiones bajos)
);
```

### **Códigos de Error Comunes:**

```php
// Validación
'CAMPO_REQUERIDO'
'EMAIL_INVALIDO'
'TELEFONO_INVALIDO'
'PRECIO_INVALIDO'
'FECHA_INVALIDA'

// Autenticación/Autorización
'NO_AUTENTICADO'
'PERMISO_DENEGADO'
'TOKEN_INVALIDO'

// Existencia
'NO_ENCONTRADO'
'YA_EXISTE'
'DUPLICADO'

// Operaciones
'ERROR_CREAR'
'ERROR_EDITAR'
'ERROR_ELIMINAR'
'ERROR_OPERACION'

// Negocio
'STOCK_INSUFICIENTE'
'VENTA_YA_FACTURADA'
'NO_PUEDE_ANULAR'
```

### **Ejemplo Completo de Manejo de Errores:**

```php
try {
    // Validaciones
    if (empty($datos['email'])) {
        responder_json(false, null, 'El email es requerido', 'CAMPO_REQUERIDO');
    }
    
    // Operación
    $resultado = Modelo::crear($datos);
    
    if (!$resultado) {
        // Intentar obtener más información
        $errores = Modelo::validar($datos);
        if (!empty($errores)) {
            throw new Exception(implode(', ', $errores));
        }
        throw new Exception('No se pudo crear el registro');
    }
    
    responder_json(true, $resultado, 'Registro creado exitosamente');
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al crear registro: ' . $e->getMessage(),
        'ERROR_CREAR'
    );
}
```

---

## 🎯 **CASOS DE USO COMUNES**

### **CASO 1: Endpoint LISTAR (GET)**

```php
<?php
/**
 * API: LISTAR REGISTROS
 * Método: GET
 * Parámetros opcionales: activo, buscar, pagina, por_pagina
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/modelo.php';

header('Content-Type: application/json; charset=utf-8');

verificar_api_autenticacion();
validar_metodo_http('GET');
verificar_api_permiso('modulo', 'ver');

try {
    // Preparar filtros
    $filtros = array();
    
    if (isset($_GET['activo'])) {
        $filtros['activo'] = $_GET['activo'] === '1' ? 1 : 0;
    }
    
    if (isset($_GET['buscar']) && !empty($_GET['buscar'])) {
        $filtros['buscar'] = $_GET['buscar'];
    }
    
    // Paginación (opcional)
    $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    $por_pagina = isset($_GET['por_pagina']) ? (int)$_GET['por_pagina'] : 20;
    
    // Limitar por_pagina
    if ($por_pagina > 100) {
        $por_pagina = 100;
    }
    
    // Obtener registros
    $registros = Modelo::listar($filtros, $pagina, $por_pagina);
    
    responder_json(
        true,
        $registros,
        count($registros) . ' registro(s) encontrado(s)'
    );
    
} catch (Exception $e) {
    responder_json(false, null, 'Error al listar: ' . $e->getMessage(), 'ERROR_LISTAR');
}
```

---

### **CASO 2: Endpoint CREAR (POST)**

```php
<?php
/**
 * API: CREAR REGISTRO
 * Método: POST
 * Parámetros requeridos: nombre, email
 * Parámetros opcionales: telefono, direccion
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/modelo.php';

header('Content-Type: application/json; charset=utf-8');

verificar_api_autenticacion();
validar_metodo_http('POST');
verificar_api_permiso('modulo', 'crear');

try {
    // Leer JSON body
    $json_input = file_get_contents('php://input');
    $datos = json_decode($json_input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE || empty($datos)) {
        $datos = $_POST;
    }
    
    // Validar campos requeridos
    if (empty($datos['nombre'])) {
        responder_json(false, null, 'El nombre es requerido', 'CAMPO_REQUERIDO');
    }
    
    if (empty($datos['email'])) {
        responder_json(false, null, 'El email es requerido', 'CAMPO_REQUERIDO');
    }
    
    // Validar email
    if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
        responder_json(false, null, 'Email inválido', 'EMAIL_INVALIDO');
    }
    
    // Preparar datos
    $datos_registro = array(
        'nombre' => $datos['nombre'],
        'email' => $datos['email'],
        'telefono' => isset($datos['telefono']) ? $datos['telefono'] : null,
        'direccion' => isset($datos['direccion']) ? $datos['direccion'] : null,
        'activo' => 1
    );
    
    // Crear registro
    $id = Modelo::crear($datos_registro);
    
    if (!$id) {
        throw new Exception('No se pudo crear el registro');
    }
    
    // Obtener registro creado
    $registro = Modelo::obtenerPorId($id);
    
    responder_json(
        true,
        array(
            'id' => $id,
            'registro' => $registro
        ),
        'Registro creado exitosamente'
    );
    
} catch (Exception $e) {
    responder_json(false, null, 'Error al crear: ' . $e->getMessage(), 'ERROR_CREAR');
}
```

---

### **CASO 3: Endpoint EDITAR (POST)**

```php
<?php
/**
 * API: EDITAR REGISTRO
 * Método: POST
 * Parámetros requeridos: id, nombre, email
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/modelo.php';

header('Content-Type: application/json; charset=utf-8');

verificar_api_autenticacion();
validar_metodo_http('POST');
verificar_api_permiso('modulo', 'editar');

try {
    $json_input = file_get_contents('php://input');
    $datos = json_decode($json_input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE || empty($datos)) {
        $datos = $_POST;
    }
    
    // Validar ID
    if (empty($datos['id'])) {
        responder_json(false, null, 'El ID es requerido', 'ID_REQUERIDO');
    }
    
    $id = (int)$datos['id'];
    
    // Verificar existencia
    $registro_actual = Modelo::obtenerPorId($id);
    
    if (!$registro_actual) {
        responder_json(false, null, 'El registro no existe', 'NO_ENCONTRADO');
    }
    
    // Validar campos requeridos
    if (empty($datos['nombre'])) {
        responder_json(false, null, 'El nombre es requerido', 'CAMPO_REQUERIDO');
    }
    
    // Preparar datos (mantener valores actuales si no se envían)
    $datos_registro = array(
        'nombre' => $datos['nombre'],
        'email' => isset($datos['email']) ? $datos['email'] : $registro_actual['email'],
        'telefono' => isset($datos['telefono']) ? $datos['telefono'] : $registro_actual['telefono']
    );
    
    // Actualizar
    $resultado = Modelo::actualizar($id, $datos_registro);
    
    if (!$resultado) {
        throw new Exception('No se pudo actualizar el registro');
    }
    
    // Obtener registro actualizado
    $registro = Modelo::obtenerPorId($id);
    
    responder_json(true, $registro, 'Registro actualizado exitosamente');
    
} catch (Exception $e) {
    responder_json(false, null, 'Error al editar: ' . $e->getMessage(), 'ERROR_EDITAR');
}
```

---

### **CASO 4: Endpoint CAMBIAR ESTADO (POST)**

```php
<?php
/**
 * API: CAMBIAR ESTADO
 * Método: POST
 * Parámetros: id, accion (activar/desactivar)
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/modelo.php';

header('Content-Type: application/json; charset=utf-8');

verificar_api_autenticacion();
validar_metodo_http('POST');
verificar_api_permiso('modulo', 'editar');

try {
    $json_input = file_get_contents('php://input');
    $datos = json_decode($json_input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE || empty($datos)) {
        $datos = $_POST;
    }
    
    // Validar campos
    if (empty($datos['id'])) {
        responder_json(false, null, 'El ID es requerido', 'ID_REQUERIDO');
    }
    
    if (empty($datos['accion'])) {
        responder_json(false, null, 'La acción es requerida', 'ACCION_REQUERIDA');
    }
    
    $id = (int)$datos['id'];
    $accion = strtolower($datos['accion']);
    
    // Validar acción
    if (!in_array($accion, array('activar', 'desactivar'))) {
        responder_json(false, null, 'Acción inválida. Use: activar o desactivar', 'ACCION_INVALIDA');
    }
    
    // Verificar existencia
    $registro = Modelo::obtenerPorId($id);
    
    if (!$registro) {
        responder_json(false, null, 'El registro no existe', 'NO_ENCONTRADO');
    }
    
    $estado_anterior = $registro['activo'] == 1 ? 'activo' : 'inactivo';
    
    // Ejecutar acción
    if ($accion === 'activar') {
        if ($registro['activo'] == 1) {
            responder_json(false, null, 'El registro ya está activo', 'YA_ACTIVO');
        }
        
        $resultado = Modelo::activar($id);
        $estado_nuevo = 'activo';
        $mensaje = 'Registro activado exitosamente';
        
    } else {
        if ($registro['activo'] == 0) {
            responder_json(false, null, 'El registro ya está inactivo', 'YA_INACTIVO');
        }
        
        $resultado = Modelo::desactivar($id);
        $estado_nuevo = 'inactivo';
        $mensaje = 'Registro desactivado exitosamente';
    }
    
    if (!$resultado) {
        throw new Exception('No se pudo cambiar el estado');
    }
    
    responder_json(
        true,
        array(
            'id' => $id,
            'estado_anterior' => $estado_anterior,
            'estado_nuevo' => $estado_nuevo
        ),
        $mensaje
    );
    
} catch (Exception $e) {
    responder_json(false, null, 'Error al cambiar estado: ' . $e->getMessage(), 'ERROR_CAMBIAR_ESTADO');
}
```

---

## 🐛 **ERRORES COMUNES Y SOLUCIONES**

### **Error 1: "Call to undefined function"**

**Síntoma:**
```
Fatal error: Call to undefined function responder_json()
```

**Causa:** No se incluyó el archivo de helpers.

**Solución:**
```php
// Asegurarse de incluir TODOS los requires
require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php'; // ← Este es crítico
require_once '../../models/modelo.php';
```

---

### **Error 2: JSON body vacío**

**Síntoma:** `$datos` está vacío en endpoints POST

**Causa:** No se lee el body correctamente

**Solución:**
```php
// ✅ CORRECTO
$json_input = file_get_contents('php://input');
$datos = json_decode($json_input, true);

// Fallback
if (json_last_error() !== JSON_ERROR_NONE || empty($datos)) {
    $datos = $_POST;
}

// ❌ INCORRECTO
// $datos = $_POST; // Solo funciona con form-data, no con JSON
```

---

### **Error 3: "Column not found"**

**Síntoma:**
```
SQLSTATE[42S22]: Column not found: Unknown column 'descripcion_trabajo'
```

**Causa:** Campo en el código no existe en la BD

**Solución:**
1. Verificar esquema de BD: `DESCRIBE tabla;`
2. Usar nombres exactos de columnas
3. No asumir nombres de campos

```php
// ❌ INCORRECTO - Asumido
$datos['descripcion_trabajo']

// ✅ CORRECTO - Verificado en BD
$datos['descripcion']
```

---

### **Error 4: Parse error con arrays**

**Síntoma:**
```
Parse error: syntax error, unexpected '['
```

**Causa:** Sintaxis `[]` no soportada en PHP antiguo

**Solución:**
```php
// ❌ EVITAR
$datos = [
    'nombre' => 'test'
];

// ✅ USAR
$datos = array(
    'nombre' => 'test'
);
```

---

### **Error 5: Headers already sent**

**Síntoma:**
```
Warning: Cannot modify header information - headers already sent
```

**Causa:** Salida (echo, print, espacio) antes de `header()`

**Solución:**
```php
<?php // ← Sin espacios antes
// Sin echo, print, var_dump antes de header()

header('Content-Type: application/json; charset=utf-8');
```

---

### **Error 6: No se muestran errores de validación**

**Síntoma:** Modelo retorna `false` pero no se sabe por qué

**Solución:**
```php
$resultado = Modelo::crear($datos);

if (!$resultado) {
    // Intentar obtener errores de validación
    $errores = Modelo::validar($datos);
    if (!empty($errores)) {
        throw new Exception(implode(', ', $errores));
    }
    throw new Exception('No se pudo crear el registro');
}
```

---

### **Error 7: Respuesta JSON malformada**

**Síntoma:** Cliente no puede parsear la respuesta

**Causa:** Mezcla de `echo` y `responder_json()`

**Solución:**
```php
// ❌ INCORRECTO
echo "Debug info"; // ← Esto rompe el JSON
responder_json(true, $data, 'OK');

// ✅ CORRECTO
// No hacer echo antes de responder_json()
responder_json(true, $data, 'OK');
```

---

## ✅ **CHECKLIST DE CALIDAD**

### **Antes de Considerar Completo un Endpoint:**

#### **Seguridad:**
- [ ] ✅ `verificar_api_autenticacion()` incluido
- [ ] ✅ `validar_metodo_http()` correcto
- [ ] ✅ `verificar_api_permiso()` apropiado
- [ ] ✅ Validación de inputs sanitizada

#### **Validaciones:**
- [ ] ✅ Campos requeridos validados
- [ ] ✅ Tipos de datos verificados
- [ ] ✅ Valores válidos comprobados
- [ ] ✅ Existencia de registros confirmada
- [ ] ✅ Duplicados prevenidos

#### **Código:**
- [ ] ✅ Try-catch implementado
- [ ] ✅ Mensajes de error descriptivos
- [ ] ✅ Códigos de error únicos
- [ ] ✅ Respuesta JSON consistente
- [ ] ✅ Sin `echo`, `print`, `var_dump`
- [ ] ✅ Sin espacios antes de `<?php`

#### **Documentación:**
- [ ] ✅ Comentario de encabezado completo
- [ ] ✅ Descripción clara
- [ ] ✅ Método HTTP especificado
- [ ] ✅ Parámetros documentados
- [ ] ✅ Ejemplo de respuesta incluido

#### **Testing:**
- [ ] ✅ Probado caso exitoso
- [ ] ✅ Probado casos de error
- [ ] ✅ Probado validaciones
- [ ] ✅ Probado permisos
- [ ] ✅ Agregado a guía de pruebas

---

## 📚 **MEJORES PRÁCTICAS**

### **1. Validar Temprano, Fallar Rápido**

```php
// ✅ Validar al inicio
if (empty($datos['email'])) {
    responder_json(false, null, 'Email requerido', 'CAMPO_REQUERIDO');
}

// No esperar a que el modelo falle
$resultado = Modelo::crear($datos);
```

---

### **2. Mensajes de Error Descriptivos**

```php
// ✅ Específico y útil
responder_json(false, null, 'El teléfono debe tener 8 dígitos', 'TELEFONO_INVALIDO');

// ❌ Vago y poco útil
// responder_json(false, null, 'Error', 'ERROR');
```

---

### **3. No Exponer Información Sensible**

```php
// ❌ NUNCA hacer esto
// responder_json(true, $usuario, 'OK'); // ← Incluye password

// ✅ Remover datos sensibles
$usuario_seguro = $usuario;
unset($usuario_seguro['password']);
responder_json(true, $usuario_seguro, 'OK');
```

---

### **4. Usar Transacciones para Operaciones Complejas**

```php
// Para operaciones que afectan múltiples tablas
$pdo->beginTransaction();

try {
    // Operación 1
    Tabla1::insertar($datos1);
    
    // Operación 2
    Tabla2::actualizar($datos2);
    
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    throw $e;
}
```

---

### **5. Documentar Comportamientos Especiales**

```php
/**
 * IMPORTANTE: Este endpoint NO actualiza la cantidad_disponible.
 * Para ajustar stock, usar ajustar_stock.php
 */
```

---

### **6. Mantener Consistencia**

```php
// ✅ Todos los endpoints de "listar" deben:
// - Aceptar filtros opcionales
// - Retornar array de registros
// - Incluir contador en mensaje

responder_json(
    true,
    $registros,
    count($registros) . ' registro(s) encontrado(s)'
);
```

---

### **7. Logging para Debugging**

```php
// En desarrollo, agregar logs
if (ENVIRONMENT === 'development') {
    error_log('Datos recibidos: ' . print_r($datos, true));
}

// En producción, loguear solo errores
registrar_error("Error al crear: " . $e->getMessage());
```

---

### **8. Versionamiento de API**

```php
// Estructura de carpetas
api/
├── v1/
│   ├── productos/
│   └── clientes/
└── v2/
    ├── productos/
    └── clientes/
```

---

### **9. Rate Limiting (Futuro)**

```php
// Implementar en config
// verificar_rate_limit($ip, $endpoint);
```

---

### **10. Documentación Swagger (Futuro)**

```php
/**
 * @OA\Post(
 *     path="/api/productos/crear.php",
 *     summary="Crear producto",
 *     @OA\Response(response="200", description="Producto creado")
 * )
 */
```

---

## 🎓 **RESUMEN**

### **Pasos para Crear un Endpoint:**

1. **Verificar esquema de BD** (`DESCRIBE tabla;`)
2. **Copiar plantilla base**
3. **Agregar documentación en encabezado**
4. **Incluir requires necesarios**
5. **Configurar header JSON**
6. **Agregar verificaciones de seguridad**
7. **Leer parámetros (GET/POST)**
8. **Validar datos**
9. **Ejecutar lógica de negocio**
10. **Responder con formato estándar**
11. **Manejar errores en catch**
12. **Probar todos los casos**
13. **Documentar en guía de pruebas**

---

## 📞 **RECURSOS ADICIONALES**

### **Archivos de Referencia:**
- `config.php` - Configuración global
- `includes/db.php` - Funciones de BD
- `includes/api-helpers.php` - Funciones de API
- `includes/funciones.php` - Funciones generales

### **Ejemplos Completos:**
- Ver cualquier endpoint en `/api/productos/`
- Ver cualquier guía en `/documentacion/GUIA-PRUEBAS-*.md`

### **Consultas:**
- Revisar código existente para patrones
- Verificar modelos antes de codificar
- Usar guías de pruebas como referencia

---

**Documento creado:** 23 de enero de 2026  
**Versión:** 1.0  
**Basado en:** 58 endpoints funcionales  
**Proyecto:** Joyería Torre Fuerte

---

🎉 **¡Éxito en tu Desarrollo de APIs!** 🎉
