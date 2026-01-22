# 📘 GUÍA COMPLETA: CREACIÓN DE MODELOS Y TESTS
## Sistema de Gestión - Joyería Torre Fuerte

---

## 📋 ÍNDICE

1. [Antes de Empezar](#antes-de-empezar)
2. [Estructura de un Modelo](#estructura-de-un-modelo)
3. [Métodos Estándar](#métodos-estándar)
4. [Validaciones](#validaciones)
5. [Manejo de Errores](#manejo-de-errores)
6. [Tests Automatizados](#tests-automatizados)
7. [Errores Comunes y Soluciones](#errores-comunes-y-soluciones)
8. [Checklist de Calidad](#checklist-de-calidad)
9. [Ejemplos Completos](#ejemplos-completos)

---

## 🎯 ANTES DE EMPEZAR

### **PASO 0: REVISAR LA BASE DE DATOS**

⚠️ **CRÍTICO:** Antes de escribir UNA SOLA LÍNEA de código:

```bash
# 1. Abrir base_datos.txt
# 2. Buscar la tabla que vas a modelar
# 3. Copiar EXACTAMENTE los nombres de campos
# 4. NO ASUMIR nada
```

**Ejemplo de verificación:**

```sql
-- EN base_datos.txt buscar:
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    nit VARCHAR(20) NULL,
    telefono VARCHAR(20) NOT NULL,  -- ← Verificar este campo existe
    email VARCHAR(100) NULL,
    -- ...
)
```

### **PASO 1: VERIFICAR FUNCIONES DISPONIBLES**

Antes de crear funciones, revisar qué ya existe:

**Archivo: `includes/funciones.php`**
```php
hash_password($password)           // Hash seguro
verificar_password($pass, $hash)   // Verificar hash
formato_dinero($cantidad)          // Q X,XXX.XX
fecha_legible($fecha)              // 22 de enero de 2026
registrar_auditoria(...)           // Log de acciones
registrar_error($mensaje)          // Log de errores
mensaje_exito($mensaje)            // Flash message
mensaje_error($mensaje)            // Flash message
redirigir($url)                    // Redirect
```

**Archivo: `includes/db.php`**
```php
db_query($sql, $params)           // SELECT múltiple
db_query_one($sql, $params)       // SELECT uno
db_execute($sql, $params)         // INSERT/UPDATE/DELETE
                                  // ⚠️ Retorna ID en INSERT
db_transaction_begin()            // Iniciar transacción
db_transaction_commit()           // Confirmar
db_transaction_rollback()         // Revertir
```

---

## 🏗️ ESTRUCTURA DE UN MODELO

### **Template Base:**

```php
<?php
/**
 * Modelo {NombreEntidad}
 * 
 * Descripción breve de lo que hace este modelo
 * 
 * @author Sistema Joyería Torre Fuerte
 * @version 1.0
 * @date 2026-01-22
 */

class {NombreEntidad} {
    
    /**
     * Crea un nuevo registro
     * 
     * @param array $datos Datos del registro
     * @return int|false ID del registro creado o false
     */
    public static function crear($datos) {
        try {
            // 1. Validar datos
            $errores = self::validar($datos);
            if (!empty($errores)) {
                throw new Exception(implode(', ', $errores));
            }
            
            // 2. Verificar reglas de negocio
            // (email único, código único, etc)
            
            // 3. Insertar
            $sql = "INSERT INTO tabla_real (...) VALUES (...)";
            $resultado = db_execute($sql, $params);
            
            // 4. Obtener ID
            if ($resultado) {
                $id = $resultado; // db_execute retorna ID en INSERT
                
                // 5. Auditoría
                registrar_auditoria(
                    'tabla_real',
                    'INSERT',
                    $id,
                    "Descripción de la acción"
                );
                
                return $id;
            }
            
            return false;
            
        } catch (Exception $e) {
            registrar_error("Error en crear: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Edita un registro existente
     * 
     * @param int $id ID del registro
     * @param array $datos Datos a actualizar
     * @return bool
     */
    public static function editar($id, $datos) {
        try {
            // 1. Validar
            // 2. Verificar que existe
            // 3. Actualizar
            // 4. Auditoría
            // 5. Retornar resultado
            
        } catch (Exception $e) {
            registrar_error("Error en editar: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene un registro por ID
     * 
     * @param int $id ID del registro
     * @return array|false Datos o false
     */
    public static function obtenerPorId($id) {
        try {
            $sql = "SELECT * FROM tabla_real WHERE id = ?";
            return db_query_one($sql, [$id]);
        } catch (Exception $e) {
            registrar_error("Error en obtenerPorId: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Lista registros con filtros
     * 
     * @param array $filtros Filtros opcionales
     * @return array Lista de registros
     */
    public static function listar($filtros = []) {
        try {
            $where = [];
            $params = [];
            
            // Construir WHERE dinámico
            if (isset($filtros['campo1'])) {
                $where[] = "campo1 = ?";
                $params[] = $filtros['campo1'];
            }
            
            $where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            
            $sql = "SELECT * FROM tabla_real $where_sql ORDER BY campo ASC";
            return db_query($sql, $params);
            
        } catch (Exception $e) {
            registrar_error("Error en listar: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Valida los datos
     * 
     * @param array $datos Datos a validar
     * @return array Errores encontrados
     */
    private static function validar($datos) {
        $errores = [];
        
        // Validaciones aquí
        if (empty($datos['campo_requerido'])) {
            $errores[] = 'El campo es requerido';
        }
        
        return $errores;
    }
}
```

---

## 📝 MÉTODOS ESTÁNDAR

### **1. CREAR**

```php
/**
 * Crea un nuevo registro
 * 
 * @param array $datos Datos del registro
 * @return int|false ID creado o false
 */
public static function crear($datos) {
    try {
        // PASO 1: Validar
        $errores = self::validar($datos);
        if (!empty($errores)) {
            throw new Exception(implode(', ', $errores));
        }
        
        // PASO 2: Verificar reglas de negocio
        if (self::existeCodigo($datos['codigo'])) {
            throw new Exception('El código ya existe');
        }
        
        // PASO 3: Preparar datos (si es necesario)
        if (isset($datos['password'])) {
            $datos['password'] = hash_password($datos['password']);
        }
        
        // PASO 4: Insertar
        $sql = "INSERT INTO tabla (campo1, campo2, campo3) 
                VALUES (?, ?, ?)";
        
        $resultado = db_execute($sql, [
            $datos['campo1'],
            $datos['campo2'] ?? null,  // Valor por defecto si es opcional
            $datos['campo3'] ?? 1
        ]);
        
        // PASO 5: Obtener ID y auditoría
        if ($resultado) {
            $id = $resultado; // ⚠️ db_execute YA retorna el ID
            
            registrar_auditoria(
                'tabla',
                'INSERT',
                $id,
                "Registro creado: {$datos['campo1']}"
            );
            
            return $id;
        }
        
        return false;
        
    } catch (Exception $e) {
        registrar_error("Error al crear: " . $e->getMessage());
        return false;
    }
}
```

**⚠️ IMPORTANTE:**
- `db_execute()` **YA retorna** el `lastInsertId()` en INSERT
- **NO usar** `db_ultimo_id()` (no existe)
- **NO asumir** que retorna `true`, retorna el ID o `false`

---

### **2. EDITAR**

```php
/**
 * Edita un registro existente
 * 
 * @param int $id ID del registro
 * @param array $datos Datos a actualizar
 * @return bool
 */
public static function editar($id, $datos) {
    try {
        // PASO 1: Validar datos nuevos
        $errores = self::validar($datos);
        if (!empty($errores)) {
            throw new Exception(implode(', ', $errores));
        }
        
        // PASO 2: Verificar que existe
        $actual = self::obtenerPorId($id);
        if (!$actual) {
            throw new Exception('Registro no encontrado');
        }
        
        // PASO 3: Verificar reglas de negocio
        // Ejemplo: email único (pero excluir el actual)
        if ($datos['email'] !== $actual['email']) {
            if (self::emailExiste($datos['email'], $id)) {
                throw new Exception('El email ya está registrado');
            }
        }
        
        // PASO 4: Actualizar
        $sql = "UPDATE tabla SET 
                    campo1 = ?,
                    campo2 = ?,
                    campo3 = ?
                WHERE id = ?";
        
        $resultado = db_execute($sql, [
            $datos['campo1'],
            $datos['campo2'] ?? null,
            $datos['campo3'] ?? 1,
            $id
        ]);
        
        // PASO 5: Auditoría
        if ($resultado) {
            registrar_auditoria(
                'tabla',
                'UPDATE',
                $id,
                "Registro actualizado"
            );
        }
        
        return $resultado;
        
    } catch (Exception $e) {
        registrar_error("Error al editar: " . $e->getMessage());
        return false;
    }
}
```

---

### **3. OBTENER POR ID**

```php
/**
 * Obtiene un registro por ID
 * 
 * @param int $id ID del registro
 * @return array|false Datos o false
 */
public static function obtenerPorId($id) {
    try {
        // Si necesitas JOIN:
        $sql = "SELECT 
                    t.*,
                    r.nombre as relacionado_nombre
                FROM tabla t
                LEFT JOIN relacionada r ON t.relacionada_id = r.id
                WHERE t.id = ?";
        
        // Si NO necesitas JOIN:
        // $sql = "SELECT * FROM tabla WHERE id = ?";
        
        return db_query_one($sql, [$id]);
        
    } catch (Exception $e) {
        registrar_error("Error al obtener: " . $e->getMessage());
        return false;
    }
}
```

---

### **4. LISTAR CON FILTROS**

```php
/**
 * Lista registros con filtros
 * 
 * @param array $filtros Filtros opcionales: campo1, campo2, activo, buscar
 * @return array Lista de registros
 */
public static function listar($filtros = []) {
    try {
        $where = [];
        $params = [];
        
        // Filtro por campo específico
        if (isset($filtros['campo1']) && !empty($filtros['campo1'])) {
            $where[] = "campo1 = ?";
            $params[] = $filtros['campo1'];
        }
        
        // Filtro por estado activo/inactivo
        if (isset($filtros['activo'])) {
            $where[] = "activo = ?";
            $params[] = $filtros['activo'];
        }
        
        // Búsqueda por texto (múltiples campos)
        if (isset($filtros['buscar']) && !empty($filtros['buscar'])) {
            $where[] = "(nombre LIKE ? OR codigo LIKE ?)";
            $termino = "%{$filtros['buscar']}%";
            $params[] = $termino;
            $params[] = $termino;
        }
        
        // Construir WHERE
        $where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Query final
        $sql = "SELECT 
                    t.*,
                    r.nombre as relacionado_nombre
                FROM tabla t
                LEFT JOIN relacionada r ON t.relacionada_id = r.id
                $where_sql
                ORDER BY t.nombre ASC";
        
        return db_query($sql, $params);
        
    } catch (Exception $e) {
        registrar_error("Error al listar: " . $e->getMessage());
        return [];
    }
}
```

---

### **5. ACTIVAR / DESACTIVAR**

```php
/**
 * Activa un registro
 * 
 * @param int $id ID del registro
 * @return bool
 */
public static function activar($id) {
    try {
        $sql = "UPDATE tabla SET activo = 1 WHERE id = ?";
        $resultado = db_execute($sql, [$id]);
        
        if ($resultado) {
            registrar_auditoria('tabla', 'UPDATE', $id, "Registro activado");
        }
        
        return $resultado;
        
    } catch (Exception $e) {
        registrar_error("Error al activar: " . $e->getMessage());
        return false;
    }
}

/**
 * Desactiva un registro
 * 
 * @param int $id ID del registro
 * @return bool
 */
public static function desactivar($id) {
    try {
        // Validación de negocio si es necesario
        // Ejemplo: No desactivar si tiene registros relacionados
        
        $sql = "UPDATE tabla SET activo = 0 WHERE id = ?";
        $resultado = db_execute($sql, [$id]);
        
        if ($resultado) {
            registrar_auditoria('tabla', 'UPDATE', $id, "Registro desactivado");
        }
        
        return $resultado;
        
    } catch (Exception $e) {
        registrar_error("Error al desactivar: " . $e->getMessage());
        return false;
    }
}
```

---

### **6. MÉTODOS DE BÚSQUEDA ESPECÍFICA**

```php
/**
 * Obtiene un registro por campo único
 * 
 * @param string $valor Valor a buscar
 * @return array|false Datos o false
 */
public static function obtenerPorEmail($email) {
    try {
        $sql = "SELECT * FROM tabla WHERE email = ?";
        return db_query_one($sql, [$email]);
    } catch (Exception $e) {
        registrar_error("Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Busca registros por término
 * 
 * @param string $termino Término de búsqueda
 * @return array Lista de registros
 */
public static function buscar($termino) {
    return self::listar(['buscar' => $termino, 'activo' => 1]);
}

/**
 * Lista registros activos
 * 
 * @return array Lista de registros activos
 */
public static function listarActivos() {
    return self::listar(['activo' => 1]);
}
```

---

### **7. ESTADÍSTICAS**

```php
/**
 * Obtiene estadísticas generales
 * 
 * @return array Estadísticas
 */
public static function obtenerEstadisticas() {
    try {
        $sql = "SELECT 
                    COUNT(*) as total,
                    COUNT(CASE WHEN activo = 1 THEN 1 END) as activos,
                    COUNT(CASE WHEN activo = 0 THEN 1 END) as inactivos,
                    SUM(campo_numerico) as suma_total,
                    AVG(campo_numerico) as promedio
                FROM tabla";
        
        return db_query_one($sql);
        
    } catch (Exception $e) {
        registrar_error("Error en estadísticas: " . $e->getMessage());
        return [];
    }
}
```

---

## ✅ VALIDACIONES

### **Método de Validación Base:**

```php
/**
 * Valida los datos del registro
 * 
 * @param array $datos Datos a validar
 * @param int|null $id ID del registro (null si es creación)
 * @return array Errores encontrados
 */
private static function validar($datos, $id = null) {
    $errores = [];
    
    // 1. CAMPOS REQUERIDOS
    if (empty($datos['nombre'])) {
        $errores[] = 'El nombre es requerido';
    } elseif (strlen($datos['nombre']) < 3) {
        $errores[] = 'El nombre debe tener al menos 3 caracteres';
    }
    
    // 2. FORMATO DE DATOS
    if (!empty($datos['email']) && !filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El email no es válido';
    }
    
    // 3. RANGOS NUMÉRICOS
    if (isset($datos['precio']) && $datos['precio'] < 0) {
        $errores[] = 'El precio no puede ser negativo';
    }
    
    // 4. VALIDACIONES CONDICIONALES
    if ($id === null && empty($datos['password'])) {
        $errores[] = 'La contraseña es requerida al crear';
    }
    
    // 5. VALORES PERMITIDOS
    $tipos_validos = ['tipo1', 'tipo2', 'tipo3'];
    if (!empty($datos['tipo']) && !in_array($datos['tipo'], $tipos_validos)) {
        $errores[] = 'El tipo no es válido';
    }
    
    return $errores;
}
```

### **Validaciones de Negocio (en métodos públicos):**

```php
/**
 * Verifica si un email ya existe
 * 
 * @param string $email Email a verificar
 * @param int|null $excluir_id ID a excluir (para edición)
 * @return bool
 */
private static function emailExiste($email, $excluir_id = null) {
    $sql = "SELECT COUNT(*) as total FROM tabla WHERE email = ?";
    $params = [$email];
    
    if ($excluir_id !== null) {
        $sql .= " AND id != ?";
        $params[] = $excluir_id;
    }
    
    $resultado = db_query_one($sql, $params);
    return $resultado['total'] > 0;
}
```

---

## ⚠️ MANEJO DE ERRORES

### **Patrón Try-Catch Consistente:**

```php
public static function metodo($params) {
    try {
        // 1. Validaciones
        if (!$condicion) {
            throw new Exception('Mensaje de error claro');
        }
        
        // 2. Operación
        $resultado = operacion();
        
        // 3. Auditoría si es necesario
        if ($resultado) {
            registrar_auditoria(...);
        }
        
        // 4. Retornar
        return $resultado;
        
    } catch (Exception $e) {
        // Log del error
        registrar_error("Error en metodo: " . $e->getMessage());
        
        // Re-lanzar en desarrollo para debugging
        if (ENVIRONMENT === 'development') {
            throw $e;
        }
        
        // Retornar false en producción
        return false;
    }
}
```

### **Tipos de Excepciones:**

```php
// Error de validación
throw new Exception('El nombre es requerido');

// Error de negocio
throw new Exception('El email ya está registrado');

// Error de estado
throw new Exception('No se puede eliminar un registro activo');

// Error de permisos
throw new Exception('No tiene permisos para esta operación');
```

---

## 🧪 TESTS AUTOMATIZADOS

### **Estructura de un Test:**

```php
<?php
/**
 * Tests para Modelo NombreModelo
 * 
 * Prueba todas las funcionalidades del modelo
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configurar sesión de prueba
session_start();
$_SESSION['usuario_id'] = 1;
$_SESSION['usuario_rol'] = 'administrador';

// Incluir archivos necesarios
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/funciones.php';
require_once __DIR__ . '/../models/nombre_modelo.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Test: Modelo NombreModelo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .test-success { background-color: #d4edda; color: #155724; }
        .test-error { background-color: #f8d7da; color: #721c24; }
        .test-info { background-color: #d1ecf1; color: #0c5460; }
    </style>
</head>
<body>

<div class="container">
    <h1>🧪 Test: Modelo NombreModelo</h1>
    
<?php

$tests_passed = 0;
$tests_failed = 0;
$registro_test_id = null;

// ========================================
// TEST 1: Crear Registro
// ========================================
echo '<div class="card mb-3"><div class="card-header"><h5>TEST 1: Crear Registro</h5></div><div class="card-body">';
try {
    $datos = [
        'campo1' => 'Valor Test',
        'campo2' => 'valor@test.com',
        'campo3' => 100
    ];
    
    $registro_test_id = NombreModelo::crear($datos);
    
    if ($registro_test_id) {
        echo '<div class="alert test-success">✅ ÉXITO: Registro creado</div>';
        echo '<div class="alert test-info">ID: ' . $registro_test_id . '</div>';
        $tests_passed++;
    } else {
        echo '<div class="alert test-error">❌ ERROR: No se pudo crear</div>';
        $tests_failed++;
    }
} catch (Exception $e) {
    echo '<div class="alert test-error">❌ EXCEPCIÓN: ' . $e->getMessage() . '</div>';
    $tests_failed++;
}
echo '</div></div>';

// Continuar con más tests...

// ========================================
// RESUMEN FINAL
// ========================================
$total_tests = $tests_passed + $tests_failed;
$porcentaje = $total_tests > 0 ? round(($tests_passed / $total_tests) * 100, 1) : 0;

echo '<div class="card border-success">
        <div class="card-header bg-success text-white">
            <h4>📊 Resumen</h4>
        </div>
        <div class="card-body">
            <h2>' . $tests_passed . '/' . $total_tests . ' tests exitosos (' . $porcentaje . '%)</h2>
        </div>
      </div>';

?>

</div>
</body>
</html>
```

### **Tests Esenciales:**

1. ✅ **TEST 1:** Crear registro
2. ✅ **TEST 2:** Validar restricciones (email único, etc)
3. ✅ **TEST 3:** Obtener por ID
4. ✅ **TEST 4:** Editar registro
5. ✅ **TEST 5:** Activar/Desactivar
6. ✅ **TEST 6:** Listar con filtros
7. ✅ **TEST 7:** Buscar
8. ✅ **TEST 8:** Estadísticas
9. ✅ **TEST 9:** Validaciones de negocio

---

## ❌ ERRORES COMUNES Y SOLUCIONES

### **Error 1: Column not found**

```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'campo_x'
```

**Causa:** Campo no existe en la tabla real

**Solución:**
```bash
1. Abrir base_datos.txt
2. Buscar CREATE TABLE nombre_tabla
3. Verificar campos reales
4. Corregir SQL
```

---

### **Error 2: Call to undefined function**

```
Call to undefined function crear_hash_password()
```

**Causa:** Función con nombre incorrecto

**Solución:**
```php
// ❌ INCORRECTO:
$hash = crear_hash_password($password);

// ✅ CORRECTO:
$hash = hash_password($password);
```

**Verificar en:**
- `includes/funciones.php`
- `includes/db.php`

---

### **Error 3: Expecting INSERT to return ID**

```php
// ❌ INCORRECTO:
$resultado = db_execute($sql, $params);
$id = db_ultimo_id(); // No existe esta función

// ✅ CORRECTO:
$id = db_execute($sql, $params); // YA retorna el ID
```

---

### **Error 4: Transacción no iniciada**

```
Error: There is no active transaction
```

**Causa:** Llamar commit/rollback sin begin

**Solución:**
```php
✅ try {
    db_transaction_begin();
    // operaciones
    db_transaction_commit();
} catch (Exception $e) {
    db_transaction_rollback();
    throw $e;
}
```

---

### **Error 5: Parámetros no coinciden**

```
SQLSTATE[HY093]: Invalid parameter number
```

**Causa:** Número de `?` no coincide con array de params

**Solución:**
```php
// Contar los ? en el SQL
$sql = "INSERT INTO tabla (a, b, c) VALUES (?, ?, ?)"; // 3 placeholders

// Verificar array tiene 3 elementos
$params = [$dato1, $dato2, $dato3]; // 3 valores ✅
```

---

## ✅ CHECKLIST DE CALIDAD

### **Antes de Considerar Completo:**

#### **Modelo (PHP):**
- [ ] PHPDoc completo en todos los métodos
- [ ] Try-catch en todos los métodos públicos
- [ ] Validaciones implementadas
- [ ] Auditoría en operaciones importantes
- [ ] Campos verificados contra base_datos.txt
- [ ] Funciones helper correctas (hash_password, no crear_hash_password)
- [ ] db_execute usado correctamente (retorna ID en INSERT)

#### **Tests:**
- [ ] Al menos 8 tests por modelo
- [ ] Test de crear exitoso
- [ ] Test de validaciones
- [ ] Test de editar
- [ ] Test de listar
- [ ] Test de activar/desactivar
- [ ] Manejo de excepciones en tests
- [ ] Resumen final con porcentaje

#### **Calidad del Código:**
- [ ] Sin warnings de PHP
- [ ] Sin variables indefinidas
- [ ] Nombres descriptivos
- [ ] Comentarios donde sea necesario
- [ ] Consistencia con otros modelos

---

## 📋 CHECKLIST DE IMPLEMENTACIÓN

### **Fase de Análisis:**
1. [ ] Revisar tabla en base_datos.txt
2. [ ] Identificar campos requeridos
3. [ ] Identificar relaciones (FK)
4. [ ] Identificar reglas de negocio
5. [ ] Listar validaciones necesarias

### **Fase de Desarrollo:**
6. [ ] Crear archivo modelo.php
7. [ ] Implementar método crear()
8. [ ] Implementar método editar()
9. [ ] Implementar método obtenerPorId()
10. [ ] Implementar método listar()
11. [ ] Implementar métodos especiales
12. [ ] Implementar validaciones

### **Fase de Testing:**
13. [ ] Crear archivo test-modelo.php
14. [ ] Implementar tests básicos
15. [ ] Ejecutar tests
16. [ ] Corregir errores
17. [ ] Verificar 100% tests

### **Fase de Documentación:**
18. [ ] PHPDoc completo
19. [ ] Comentarios en código complejo
20. [ ] Actualizar FASE-X-COMPLETADA.md

---

## 📚 EJEMPLO COMPLETO: MODELO CATEGORÍA

```php
<?php
/**
 * Modelo Categoria
 * 
 * Gestión de categorías de productos
 * 
 * @author Sistema Joyería Torre Fuerte
 * @version 1.0
 */

class Categoria {
    
    public static function crear($datos) {
        try {
            $errores = self::validar($datos);
            if (!empty($errores)) {
                throw new Exception(implode(', ', $errores));
            }
            
            $sql = "INSERT INTO categorias (nombre, descripcion, activo) 
                    VALUES (?, ?, ?)";
            
            $id = db_execute($sql, [
                $datos['nombre'],
                $datos['descripcion'] ?? null,
                $datos['activo'] ?? 1
            ]);
            
            if ($id) {
                registrar_auditoria('categorias', 'INSERT', $id, 
                    "Categoría creada: {$datos['nombre']}");
            }
            
            return $id;
            
        } catch (Exception $e) {
            registrar_error("Error al crear categoría: " . $e->getMessage());
            return false;
        }
    }
    
    public static function obtenerPorId($id) {
        try {
            return db_query_one("SELECT * FROM categorias WHERE id = ?", [$id]);
        } catch (Exception $e) {
            return false;
        }
    }
    
    public static function listar($filtros = []) {
        try {
            $where = [];
            $params = [];
            
            if (isset($filtros['activo'])) {
                $where[] = "activo = ?";
                $params[] = $filtros['activo'];
            }
            
            if (isset($filtros['buscar'])) {
                $where[] = "nombre LIKE ?";
                $params[] = "%{$filtros['buscar']}%";
            }
            
            $where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            
            return db_query("SELECT * FROM categorias $where_sql ORDER BY nombre", $params);
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    private static function validar($datos) {
        $errores = [];
        
        if (empty($datos['nombre'])) {
            $errores[] = 'El nombre es requerido';
        }
        
        return $errores;
    }
}
```

---

## 🎯 RESUMEN DE REGLAS DE ORO

1. ✅ **SIEMPRE revisar base_datos.txt PRIMERO**
2. ✅ **SIEMPRE usar funciones existentes** (hash_password, no crear_hash_password)
3. ✅ **db_execute() retorna ID en INSERT** (no usar db_ultimo_id)
4. ✅ **Try-catch en TODOS los métodos públicos**
5. ✅ **Registrar auditoría en operaciones importantes**
6. ✅ **Validar ANTES de operar**
7. ✅ **Tests para CADA modelo** (mínimo 8 tests)
8. ✅ **PHPDoc completo**
9. ✅ **Consistencia con modelos existentes**
10. ✅ **Probar antes de marcar completo**

---

**Guía creada por:** Sistema de Gestión Joyería Torre Fuerte  
**Fecha:** Enero 2026  
**Versión:** 1.0
