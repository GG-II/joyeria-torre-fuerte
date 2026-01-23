<?php
/**
 * ================================================
 * FUNCIONES AUXILIARES PARA APIs
 * ================================================
 * Funciones reutilizables para endpoints AJAX/JSON
 */

// ================================================
// 1. RESPUESTA JSON ESTANDARIZADA
// ================================================

/**
 * Envía una respuesta JSON estandarizada y finaliza la ejecución
 * 
 * @param bool $success Estado de la operación
 * @param mixed $data Datos a enviar (null si error)
 * @param string $message Mensaje descriptivo
 * @param string $code Código de error opcional
 */
function responder_json($success, $data = null, $message = '', $code = '') {
    $response = [
        'success' => $success
    ];
    
    if ($success) {
        // Respuesta exitosa
        $response['data'] = $data;
        if ($message) {
            $response['message'] = $message;
        }
    } else {
        // Respuesta de error
        $response['error'] = $message;
        if ($code) {
            $response['code'] = $code;
        }
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// ================================================
// 2. VERIFICACIÓN DE AUTENTICACIÓN PARA APIs
// ================================================

/**
 * Verifica que el usuario esté autenticado
 * Acepta token desde header Authorization: Bearer {token}
 * Si no lo está, devuelve error JSON y finaliza
 */
function verificar_api_autenticacion() {
    // Incluir sistema de autenticación si no está cargado
    if (!function_exists('verificar_sesion')) {
        require_once __DIR__ . '/auth.php';
    }
    
    // Verificar si viene token en el header Authorization
    $headers = getallheaders();
    
    if (isset($headers['Authorization'])) {
        // Extraer token del header "Bearer {token}"
        $auth_header = $headers['Authorization'];
        
        if (preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
            $token = $matches[1];
            
            // El token es el session_id
            // Cerrar sesión actual si existe
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }
            
            // Iniciar sesión con el ID proporcionado
            session_id($token);
            session_start();
        }
    }
    
    // Ahora verificar si la sesión es válida
    if (!verificar_sesion()) {
        responder_json(false, null, 'No estás autenticado. Por favor inicia sesión.', 'NO_AUTENTICADO');
    }
}

/**
 * Verifica que el usuario tenga permiso para una acción específica
 * 
 * @param string $modulo Módulo del sistema
 * @param string $accion Acción (ver, crear, editar, eliminar)
 */
function verificar_api_permiso($modulo, $accion = 'ver') {
    // Primero verificar autenticación
    verificar_api_autenticacion();
    
    // Incluir sistema de autenticación si no está cargado
    if (!function_exists('tiene_permiso')) {
        require_once __DIR__ . '/auth.php';
    }
    
    if (!tiene_permiso($modulo, $accion)) {
        responder_json(false, null, 'No tienes permisos para realizar esta acción.', 'SIN_PERMISO');
    }
}

// ================================================
// 3. VALIDACIÓN DE MÉTODO HTTP
// ================================================

/**
 * Valida que el método HTTP sea el esperado
 * 
 * @param string $metodo_esperado GET, POST, PUT, DELETE
 */
function validar_metodo_http($metodo_esperado) {
    $metodo_actual = $_SERVER['REQUEST_METHOD'];
    
    if ($metodo_actual !== strtoupper($metodo_esperado)) {
        responder_json(
            false, 
            null, 
            "Método HTTP incorrecto. Se esperaba {$metodo_esperado}, se recibió {$metodo_actual}.", 
            'METODO_INCORRECTO'
        );
    }
}

// ================================================
// 4. VALIDACIÓN DE CAMPOS REQUERIDOS
// ================================================

/**
 * Valida que los campos requeridos estén presentes en el request
 * 
 * @param array $campos_requeridos Array con nombres de campos requeridos
 * @param string $metodo Método HTTP (GET o POST)
 * @return array Datos validados
 */
function validar_campos_requeridos($campos_requeridos, $metodo = 'POST') {
    $datos = $metodo === 'POST' ? $_POST : $_GET;
    $campos_faltantes = [];
    
    foreach ($campos_requeridos as $campo) {
        if (!isset($datos[$campo]) || trim($datos[$campo]) === '') {
            $campos_faltantes[] = $campo;
        }
    }
    
    if (!empty($campos_faltantes)) {
        responder_json(
            false, 
            null, 
            'Campos requeridos faltantes: ' . implode(', ', $campos_faltantes), 
            'CAMPOS_FALTANTES'
        );
    }
    
    return $datos;
}

// ================================================
// 5. SANITIZACIÓN DE DATOS
// ================================================

/**
 * Sanitiza un valor según su tipo
 * 
 * @param mixed $valor Valor a sanitizar
 * @param string $tipo Tipo de dato (string, int, float, email, bool)
 * @return mixed Valor sanitizado
 */
function sanitizar_dato($valor, $tipo = 'string') {
    switch ($tipo) {
        case 'int':
            return (int) $valor;
            
        case 'float':
            return (float) $valor;
            
        case 'email':
            return filter_var($valor, FILTER_SANITIZE_EMAIL);
            
        case 'bool':
            return (bool) $valor;
            
        case 'string':
        default:
            return htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Sanitiza un array de datos según especificaciones
 * 
 * @param array $datos Datos a sanitizar
 * @param array $tipos Array asociativo [campo => tipo]
 * @return array Datos sanitizados
 */
function sanitizar_datos($datos, $tipos = []) {
    $datos_sanitizados = [];
    
    foreach ($datos as $campo => $valor) {
        $tipo = $tipos[$campo] ?? 'string';
        $datos_sanitizados[$campo] = sanitizar_dato($valor, $tipo);
    }
    
    return $datos_sanitizados;
}

// ================================================
// 6. OBTENCIÓN SEGURA DE DATOS DEL REQUEST
// ================================================

/**
 * Obtiene datos de $_GET de forma segura
 * 
 * @param string $clave Clave del parámetro
 * @param mixed $valor_default Valor por defecto si no existe
 * @param string $tipo Tipo de dato para sanitizar
 * @return mixed Valor sanitizado o valor por defecto
 */
function obtener_get($clave, $valor_default = null, $tipo = 'string') {
    if (!isset($_GET[$clave])) {
        return $valor_default;
    }
    
    return sanitizar_dato($_GET[$clave], $tipo);
}

/**
 * Obtiene datos de $_POST de forma segura
 * 
 * @param string $clave Clave del parámetro
 * @param mixed $valor_default Valor por defecto si no existe
 * @param string $tipo Tipo de dato para sanitizar
 * @return mixed Valor sanitizado o valor por defecto
 */
function obtener_post($clave, $valor_default = null, $tipo = 'string') {
    if (!isset($_POST[$clave])) {
        return $valor_default;
    }
    
    return sanitizar_dato($_POST[$clave], $tipo);
}

/**
 * Obtiene el cuerpo de la petición como JSON
 * 
 * @return array|null Array con los datos o null si hay error
 */
function obtener_json_body() {
    $json_body = file_get_contents('php://input');
    $datos = json_decode($json_body, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        responder_json(false, null, 'JSON inválido en el cuerpo de la petición.', 'JSON_INVALIDO');
    }
    
    return $datos;
}