<?php
// ================================================
// FUNCIONES GENERALES DEL SISTEMA
// ================================================

/**
 * Sanitiza una cadena de texto para prevenir XSS
 * 
 * @param string $data Texto a sanitizar
 * @return string Texto sanitizado
 */
function limpiar_texto($data) {
    if (is_array($data)) {
        return array_map('limpiar_texto', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Valida un email
 * 
 * @param string $email Email a validar
 * @return bool
 */
function validar_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valida un número de teléfono guatemalteco (8 dígitos)
 * 
 * @param string $telefono Teléfono a validar
 * @return bool
 */
function validar_telefono($telefono) {
    // Eliminar espacios, guiones, paréntesis
    $telefono = preg_replace('/[^0-9]/', '', $telefono);
    // Debe tener 8 dígitos
    return strlen($telefono) === 8;
}

/**
 * Valida un NIT guatemalteco (formato: 12345678-9 o 1234567-K)
 * 
 * @param string $nit NIT a validar
 * @return bool
 */
function validar_nit($nit) {
    // Formato básico: números-dígito verificador
    return preg_match('/^[0-9]{7,8}-[0-9K]$/', $nit) === 1;
}

/**
 * Genera una contraseña hash segura
 * 
 * @param string $password Contraseña en texto plano
 * @return string Hash de la contraseña
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verifica una contraseña contra su hash
 * 
 * @param string $password Contraseña en texto plano
 * @param string $hash Hash almacenado
 * @return bool
 */
function verificar_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Formatea un número como moneda (Q 1,234.56)
 * 
 * @param float $numero Número a formatear
 * @return string Número formateado
 */
function formato_dinero($numero) {
    return 'Q ' . number_format($numero, 2, '.', ',');
}

/**
 * Formatea una fecha de MySQL a formato legible
 * 
 * @param string $fecha Fecha en formato MySQL (YYYY-MM-DD)
 * @param bool $incluir_hora Si incluir la hora
 * @return string Fecha formateada
 */
function formato_fecha($fecha, $incluir_hora = false) {
    if (empty($fecha) || $fecha === '0000-00-00' || $fecha === '0000-00-00 00:00:00') {
        return '-';
    }
    
    $timestamp = strtotime($fecha);
    
    if ($incluir_hora) {
        return date('d/m/Y H:i', $timestamp);
    } else {
        return date('d/m/Y', $timestamp);
    }
}

/**
 * Convierte una fecha de formato DD/MM/YYYY a YYYY-MM-DD para MySQL
 * 
 * @param string $fecha Fecha en formato DD/MM/YYYY
 * @return string Fecha en formato MySQL
 */
function fecha_a_mysql($fecha) {
    if (empty($fecha)) {
        return null;
    }
    
    $partes = explode('/', $fecha);
    if (count($partes) === 3) {
        return $partes[2] . '-' . $partes[1] . '-' . $partes[0];
    }
    
    return $fecha; // Si ya está en formato correcto
}

/**
 * Genera un código único alfanumérico
 * 
 * @param int $longitud Longitud del código
 * @return string Código generado
 */
function generar_codigo($longitud = 8) {
    $caracteres = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $codigo = '';
    for ($i = 0; $i < $longitud; $i++) {
        $codigo .= $caracteres[rand(0, strlen($caracteres) - 1)];
    }
    return $codigo;
}

/**
 * Redirecciona a otra página
 * 
 * @param string $url URL de destino
 */
function redirigir($url) {
    header("Location: $url");
    exit;
}

/**
 * Verifica si el usuario está autenticado
 * 
 * @return bool
 */
function esta_autenticado() {
    return isset($_SESSION['usuario_id']);
}

/**
 * Verifica si el usuario tiene un rol específico
 * 
 * @param string|array $roles Rol(es) permitido(s)
 * @return bool
 */
function tiene_rol($roles) {
    if (!esta_autenticado()) {
        return false;
    }
    
    if (is_string($roles)) {
        $roles = [$roles];
    }
    
    return in_array($_SESSION['usuario_rol'], $roles);
}

/**
 * Obtiene el ID del usuario actual
 * 
 * @return int|null
 */
function usuario_actual_id() {
    return $_SESSION['usuario_id'] ?? null;
}

/**
 * Obtiene el nombre del usuario actual
 * 
 * @return string|null
 */
function usuario_actual_nombre() {
    return $_SESSION['usuario_nombre'] ?? null;
}

/**
 * Obtiene el rol del usuario actual
 * 
 * @return string|null
 */
function usuario_actual_rol() {
    return $_SESSION['usuario_rol'] ?? null;
}

/**
 * Obtiene la sucursal del usuario actual
 * 
 * @return int|null
 */
function usuario_actual_sucursal() {
    return $_SESSION['usuario_sucursal_id'] ?? null;
}

/**
 * Muestra un mensaje de éxito en la sesión (para mostrar después de redirección)
 * 
 * @param string $mensaje Mensaje a mostrar
 */
function mensaje_exito($mensaje) {
    $_SESSION['mensaje_exito'] = $mensaje;
}

/**
 * Muestra un mensaje de error en la sesión
 * 
 * @param string $mensaje Mensaje a mostrar
 */
function mensaje_error($mensaje) {
    $_SESSION['mensaje_error'] = $mensaje;
}

/**
 * Obtiene y limpia el mensaje de éxito de la sesión
 * 
 * @return string|null
 */
function obtener_mensaje_exito() {
    if (isset($_SESSION['mensaje_exito'])) {
        $mensaje = $_SESSION['mensaje_exito'];
        unset($_SESSION['mensaje_exito']);
        return $mensaje;
    }
    return null;
}

/**
 * Obtiene y limpia el mensaje de error de la sesión
 * 
 * @return string|null
 */
function obtener_mensaje_error() {
    if (isset($_SESSION['mensaje_error'])) {
        $mensaje = $_SESSION['mensaje_error'];
        unset($_SESSION['mensaje_error']);
        return $mensaje;
    }
    return null;
}

/**
 * Registra una acción en el log de auditoría
 * 
 * @param string $tabla Tabla afectada
 * @param string $accion Tipo de acción (INSERT, UPDATE, DELETE)
 * @param int $registro_id ID del registro afectado
 * @param string $descripcion Descripción de la acción
 */
function registrar_auditoria($tabla, $accion, $registro_id, $descripcion = '') {
    global $pdo;
    
    $usuario_id = usuario_actual_id();
    
    if (!$usuario_id) {
        return; // No registrar si no hay usuario autenticado
    }
    
    $sql = "INSERT INTO audit_log (usuario_id, tabla, accion, registro_id, descripcion, ip_address) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario_id, $tabla, $accion, $registro_id, $descripcion, $ip]);
    } catch (PDOException $e) {
        error_log("Error al registrar auditoría: " . $e->getMessage());
    }
}
?>