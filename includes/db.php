<?php
// ================================================
// CONEXIÓN A BASE DE DATOS - PDO
// ================================================

require_once __DIR__ . '/../config.php';

// Variable global de conexión
$pdo = null;

try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    
    $pdo = new PDO(
        $dsn,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );
    
} catch (PDOException $e) {
    // En desarrollo: mostrar el error
    if (ENVIRONMENT === 'development') {
        die("❌ Error de conexión a la base de datos:<br><br>" . 
            "<strong>Mensaje:</strong> " . $e->getMessage() . "<br>" .
            "<strong>Código:</strong> " . $e->getCode() . "<br><br>" .
            "<em>Revisa config.php y verifica que MySQL esté corriendo en XAMPP.</em>");
    } else {
        // En producción: error genérico y log
        error_log("Error de conexión a BD: " . $e->getMessage());
        die("Error al conectar con la base de datos. Por favor, contacte al administrador.");
    }
}

// ================================================
// FUNCIONES HELPER PARA BASE DE DATOS
// ================================================

/**
 * Ejecuta una consulta SELECT y devuelve todos los resultados
 * 
 * @param string $sql Consulta SQL
 * @param array $params Parámetros para prepared statement
 * @return array Resultados
 */
function db_query($sql, $params = []) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error en db_query: " . $e->getMessage());
        if (ENVIRONMENT === 'development') {
            die("Error en consulta: " . $e->getMessage());
        }
        return false;
    }
}

/**
 * Ejecuta una consulta SELECT y devuelve un solo resultado
 * 
 * @param string $sql Consulta SQL
 * @param array $params Parámetros para prepared statement
 * @return array|false Resultado o false
 */
function db_query_one($sql, $params = []) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error en db_query_one: " . $e->getMessage());
        if (ENVIRONMENT === 'development') {
            die("Error en consulta: " . $e->getMessage());
        }
        return false;
    }
}

/**
 * Ejecuta INSERT, UPDATE o DELETE
 * 
 * @param string $sql Consulta SQL
 * @param array $params Parámetros para prepared statement
 * @return bool|int ID del último insert o true/false
 */
function db_execute($sql, $params = []) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        // Si es un INSERT, devolver el ID insertado
        if (stripos(trim($sql), 'INSERT') === 0) {
            return $pdo->lastInsertId();
        }
        
        // Para UPDATE/DELETE devolver true
        return true;
        
    } catch (PDOException $e) {
        error_log("Error en db_execute: " . $e->getMessage());
        if (ENVIRONMENT === 'development') {
            die("Error en ejecución: " . $e->getMessage());
        }
        return false;
    }
}

/**
 * Cuenta registros de una tabla con condiciones opcionales
 * 
 * @param string $table Nombre de la tabla
 * @param string $where Condición WHERE (sin la palabra WHERE)
 * @param array $params Parámetros para prepared statement
 * @return int Cantidad de registros
 */
function db_count($table, $where = '', $params = []) {
    global $pdo;
    
    $sql = "SELECT COUNT(*) as total FROM $table";
    if ($where) {
        $sql .= " WHERE $where";
    }
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return (int)$result['total'];
    } catch (PDOException $e) {
        error_log("Error en db_count: " . $e->getMessage());
        return 0;
    }
}

/**
 * Verifica si existe un registro
 * 
 * @param string $table Nombre de la tabla
 * @param string $where Condición WHERE
 * @param array $params Parámetros
 * @return bool
 */
function db_exists($table, $where, $params = []) {
    return db_count($table, $where, $params) > 0;
}
?>