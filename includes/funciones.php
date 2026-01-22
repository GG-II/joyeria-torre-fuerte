<?php
// ================================================
// FUNCIONES GENERALES DEL SISTEMA
// Sistema de Gestión - Joyería Torre Fuerte
// Versión: 2.0 - Completado al 110%
// ================================================

// ================================================
// 1. SANITIZACIÓN Y VALIDACIÓN
// ================================================

/**
 * Sanitiza una cadena de texto para prevenir XSS
 * 
 * @param string|array $data Texto a sanitizar
 * @return string|array Texto sanitizado
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
 * Valida un código de barras (EAN-13, UPC-A, etc.)
 * 
 * @param string $codigo Código de barras
 * @return bool
 */
function validar_codigo_barras($codigo) {
    // Limpiar el código
    $codigo = preg_replace('/[^0-9]/', '', $codigo);
    // Debe tener entre 8 y 13 dígitos
    $longitud = strlen($codigo);
    return $longitud >= 8 && $longitud <= 13;
}

/**
 * Valida que un número sea decimal positivo
 * 
 * @param mixed $numero Número a validar
 * @return bool
 */
function validar_decimal_positivo($numero) {
    return is_numeric($numero) && $numero >= 0;
}

/**
 * Valida que un número sea entero positivo
 * 
 * @param mixed $numero Número a validar
 * @return bool
 */
function validar_entero_positivo($numero) {
    return is_numeric($numero) && $numero >= 0 && $numero == (int)$numero;
}

/**
 * Valida que una fecha esté en formato válido
 * 
 * @param string $fecha Fecha a validar (DD/MM/YYYY o YYYY-MM-DD)
 * @return bool
 */
function validar_fecha($fecha) {
    if (empty($fecha)) {
        return false;
    }
    
    // Formato DD/MM/YYYY
    if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $fecha, $matches)) {
        return checkdate($matches[2], $matches[1], $matches[3]);
    }
    
    // Formato YYYY-MM-DD
    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $fecha, $matches)) {
        return checkdate($matches[2], $matches[3], $matches[1]);
    }
    
    return false;
}

// ================================================
// 2. SEGURIDAD - CONTRASEÑAS Y HASH
// ================================================

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
 * Genera un token de seguridad único
 * 
 * @param int $longitud Longitud del token
 * @return string Token generado
 */
function generar_token($longitud = 32) {
    return bin2hex(random_bytes($longitud / 2));
}

// ================================================
// 3. FORMATO - DINERO, FECHAS, NÚMEROS
// ================================================

/**
 * Formatea un número como moneda guatemalteca (Q 1,234.56)
 * 
 * @param float $numero Número a formatear
 * @param bool $incluir_simbolo Si incluir el símbolo Q
 * @return string Número formateado
 */
function formato_dinero($numero, $incluir_simbolo = true) {
    $formateado = number_format($numero, 2, '.', ',');
    return $incluir_simbolo ? 'Q ' . $formateado : $formateado;
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
 * Formatea una fecha en texto legible en español
 * 
 * @param string $fecha Fecha en formato MySQL
 * @return string Fecha en texto (ej: "Lunes, 21 de enero de 2026")
 */
function formato_fecha_texto($fecha) {
    if (empty($fecha)) {
        return '-';
    }
    
    $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 
              'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    
    $timestamp = strtotime($fecha);
    $dia_semana = $dias[date('w', $timestamp)];
    $dia = date('j', $timestamp);
    $mes = $meses[date('n', $timestamp) - 1];
    $anio = date('Y', $timestamp);
    
    return "$dia_semana, $dia de $mes de $anio";
}

/**
 * Convierte una fecha de formato DD/MM/YYYY a YYYY-MM-DD para MySQL
 * 
 * @param string $fecha Fecha en formato DD/MM/YYYY
 * @return string|null Fecha en formato MySQL
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
 * Formatea un peso en gramos de forma legible
 * 
 * @param float $gramos Peso en gramos
 * @return string Peso formateado
 */
function formato_peso($gramos) {
    if ($gramos >= 1000) {
        return number_format($gramos / 1000, 2) . ' kg';
    }
    return number_format($gramos, 2) . ' g';
}

/**
 * Formatea un número como porcentaje
 * 
 * @param float $numero Número a formatear (0.15 = 15%)
 * @param int $decimales Decimales a mostrar
 * @return string Porcentaje formateado
 */
function formato_porcentaje($numero, $decimales = 2) {
    return number_format($numero * 100, $decimales) . '%';
}

/**
 * Formatea un número grande de forma compacta (1.5K, 2.3M)
 * 
 * @param float $numero Número a formatear
 * @return string Número formateado
 */
function formato_numero_compacto($numero) {
    if ($numero >= 1000000) {
        return number_format($numero / 1000000, 1) . 'M';
    }
    if ($numero >= 1000) {
        return number_format($numero / 1000, 1) . 'K';
    }
    return number_format($numero, 0);
}

// ================================================
// 4. CÓDIGOS Y GENERADORES
// ================================================

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
 * Genera un código de producto único con prefijo
 * 
 * @param string $prefijo Prefijo del código (ej: PROD, ORO, ART)
 * @param int $longitud_numero Longitud de la parte numérica
 * @return string Código generado
 */
function generar_codigo_producto($prefijo = 'PROD', $longitud_numero = 6) {
    global $pdo;
    
    do {
        $numero = str_pad(rand(0, pow(10, $longitud_numero) - 1), $longitud_numero, '0', STR_PAD_LEFT);
        $codigo = $prefijo . '-' . $numero;
        
        // Verificar que no exista
        $sql = "SELECT COUNT(*) as total FROM productos WHERE codigo = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$codigo]);
        $resultado = $stmt->fetch();
        
    } while ($resultado['total'] > 0);
    
    return $codigo;
}

/**
 * Genera un número de orden de trabajo único
 * 
 * @param string $prefijo Prefijo (ej: ORD)
 * @return string Número de orden
 */
function generar_numero_orden($prefijo = 'ORD') {
    global $pdo;
    
    $fecha = date('Ymd'); // 20260121
    
    // Obtener el último número del día
    $sql = "SELECT numero_orden FROM trabajos_taller 
            WHERE numero_orden LIKE ? 
            ORDER BY id DESC LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$prefijo . '-' . $fecha . '-%']);
    $ultimo = $stmt->fetch();
    
    if ($ultimo) {
        // Extraer el número secuencial
        $partes = explode('-', $ultimo['numero_orden']);
        $secuencial = (int)$partes[2] + 1;
    } else {
        $secuencial = 1;
    }
    
    return $prefijo . '-' . $fecha . '-' . str_pad($secuencial, 4, '0', STR_PAD_LEFT);
}

/**
 * Genera un número de factura único
 * 
 * @param string $serie Serie de la factura
 * @return string Número de factura
 */
function generar_numero_factura($serie = 'A') {
    global $pdo;
    
    // Obtener el último número de la serie
    $sql = "SELECT numero_factura FROM facturas 
            WHERE serie = ? 
            ORDER BY id DESC LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$serie]);
    $ultimo = $stmt->fetch();
    
    if ($ultimo) {
        // Extraer el número
        $numero = (int)str_replace($serie . '-', '', $ultimo['numero_factura']) + 1;
    } else {
        $numero = 1;
    }
    
    return $serie . '-' . str_pad($numero, 8, '0', STR_PAD_LEFT);
}

// ================================================
// 5. NAVEGACIÓN Y REDIRECCIÓN
// ================================================

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
 * Recarga la página actual
 */
function recargar_pagina() {
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

/**
 * Obtiene la URL base del sistema
 * 
 * @return string URL base
 */
function obtener_url_base() {
    return defined('BASE_URL') ? BASE_URL : '';
}

// ================================================
// 6. AUTENTICACIÓN Y SESIÓN
// ================================================

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
 * Verifica si el usuario actual es administrador o dueño
 * 
 * @return bool
 */
function es_admin_o_dueno() {
    return tiene_rol(['administrador', 'dueño']);
}

// ================================================
// 7. MENSAJES FLASH
// ================================================

/**
 * Muestra un mensaje de éxito en la sesión
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
 * Muestra un mensaje de advertencia en la sesión
 * 
 * @param string $mensaje Mensaje a mostrar
 */
function mensaje_advertencia($mensaje) {
    $_SESSION['mensaje_advertencia'] = $mensaje;
}

/**
 * Muestra un mensaje de información en la sesión
 * 
 * @param string $mensaje Mensaje a mostrar
 */
function mensaje_info($mensaje) {
    $_SESSION['mensaje_info'] = $mensaje;
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
 * Obtiene y limpia el mensaje de advertencia de la sesión
 * 
 * @return string|null
 */
function obtener_mensaje_advertencia() {
    if (isset($_SESSION['mensaje_advertencia'])) {
        $mensaje = $_SESSION['mensaje_advertencia'];
        unset($_SESSION['mensaje_advertencia']);
        return $mensaje;
    }
    return null;
}

/**
 * Obtiene y limpia el mensaje de información de la sesión
 * 
 * @return string|null
 */
function obtener_mensaje_info() {
    if (isset($_SESSION['mensaje_info'])) {
        $mensaje = $_SESSION['mensaje_info'];
        unset($_SESSION['mensaje_info']);
        return $mensaje;
    }
    return null;
}

// ================================================
// 8. AUDITORÍA Y LOGS
// ================================================

/**
 * Registra una acción en el log de auditoría
 * 
 * @param string $accion Tipo de acción realizada
 * @param string $tabla Tabla afectada
 * @param int $registro_id ID del registro afectado
 * @param string $detalles Detalles de la acción
 */
function registrar_auditoria($accion, $tabla, $registro_id, $detalles = '') {
    global $pdo;
    
    $usuario_id = usuario_actual_id();
    
    if (!$usuario_id) {
        return; // No registrar si no hay usuario autenticado
    }
    
    $sql = "INSERT INTO audit_log (usuario_id, accion, tabla_afectada, registro_id, detalles, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'desconocido';
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario_id, $accion, $tabla, $registro_id, $detalles, $ip, $user_agent]);
    } catch (PDOException $e) {
        error_log("Error al registrar auditoría: " . $e->getMessage());
    }
}

/**
 * Registra un error en el log del sistema
 * 
 * @param string $mensaje Mensaje de error
 * @param string $contexto Contexto adicional
 */
function registrar_error($mensaje, $contexto = '') {
    $log = "[" . date('Y-m-d H:i:s') . "] ";
    $log .= "Usuario: " . (usuario_actual_id() ?? 'no autenticado') . " | ";
    $log .= "Error: $mensaje";
    if ($contexto) {
        $log .= " | Contexto: $contexto";
    }
    $log .= "\n";
    
    error_log($log, 3, __DIR__ . '/../logs/errores.log');
}

// ================================================
// 9. MANEJO DE ARCHIVOS E IMÁGENES
// ================================================

/**
 * Sube un archivo al servidor
 * 
 * @param array $archivo Array de $_FILES
 * @param string $carpeta Carpeta destino dentro de uploads/
 * @param array $extensiones_permitidas Extensiones permitidas
 * @param int $tamano_maximo Tamaño máximo en bytes
 * @return string|false Nombre del archivo subido o false si falla
 */
function subir_archivo($archivo, $carpeta = '', $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'pdf'], $tamano_maximo = 5242880) {
    
    // Validar que el archivo existe
    if (!isset($archivo['tmp_name']) || empty($archivo['tmp_name'])) {
        return false;
    }
    
    // Validar errores de upload
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    // Validar tamaño
    if ($archivo['size'] > $tamano_maximo) {
        return false;
    }
    
    // Obtener extensión
    $nombre_original = $archivo['name'];
    $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
    
    // Validar extensión
    if (!in_array($extension, $extensiones_permitidas)) {
        return false;
    }
    
    // Generar nombre único
    $nombre_nuevo = uniqid() . '_' . time() . '.' . $extension;
    
    // Crear carpeta si no existe
    $ruta_carpeta = __DIR__ . '/../uploads/' . $carpeta;
    if (!file_exists($ruta_carpeta)) {
        mkdir($ruta_carpeta, 0755, true);
    }
    
    // Mover archivo
    $ruta_completa = $ruta_carpeta . '/' . $nombre_nuevo;
    if (move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
        return $carpeta . '/' . $nombre_nuevo;
    }
    
    return false;
}

/**
 * Sube una imagen con redimensionamiento automático
 * 
 * @param array $archivo Array de $_FILES
 * @param string $carpeta Carpeta destino
 * @param int $ancho_max Ancho máximo
 * @param int $alto_max Alto máximo
 * @return string|false Ruta de la imagen o false
 */
function subir_imagen($archivo, $carpeta = 'productos', $ancho_max = 800, $alto_max = 800) {
    
    $extensiones_imagen = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ruta_relativa = subir_archivo($archivo, $carpeta, $extensiones_imagen);
    
    if (!$ruta_relativa) {
        return false;
    }
    
    // Ruta completa
    $ruta_completa = __DIR__ . '/../uploads/' . $ruta_relativa;
    
    // Redimensionar imagen
    redimensionar_imagen($ruta_completa, $ancho_max, $alto_max);
    
    return $ruta_relativa;
}

/**
 * Redimensiona una imagen manteniendo proporciones
 * 
 * @param string $ruta_imagen Ruta de la imagen
 * @param int $ancho_max Ancho máximo
 * @param int $alto_max Alto máximo
 * @return bool
 */
function redimensionar_imagen($ruta_imagen, $ancho_max, $alto_max) {
    
    if (!file_exists($ruta_imagen)) {
        return false;
    }
    
    // Obtener información de la imagen
    list($ancho, $alto, $tipo) = getimagesize($ruta_imagen);
    
    // Si la imagen es más pequeña que el máximo, no hacer nada
    if ($ancho <= $ancho_max && $alto <= $alto_max) {
        return true;
    }
    
    // Calcular nuevas dimensiones manteniendo proporción
    $ratio = min($ancho_max / $ancho, $alto_max / $alto);
    $nuevo_ancho = round($ancho * $ratio);
    $nuevo_alto = round($alto * $ratio);
    
    // Crear imagen según el tipo
    switch ($tipo) {
        case IMAGETYPE_JPEG:
            $imagen_origen = imagecreatefromjpeg($ruta_imagen);
            break;
        case IMAGETYPE_PNG:
            $imagen_origen = imagecreatefrompng($ruta_imagen);
            break;
        case IMAGETYPE_GIF:
            $imagen_origen = imagecreatefromgif($ruta_imagen);
            break;
        case IMAGETYPE_WEBP:
            $imagen_origen = imagecreatefromwebp($ruta_imagen);
            break;
        default:
            return false;
    }
    
    // Crear imagen nueva
    $imagen_nueva = imagecreatetruecolor($nuevo_ancho, $nuevo_alto);
    
    // Preservar transparencia para PNG
    if ($tipo == IMAGETYPE_PNG) {
        imagealphablending($imagen_nueva, false);
        imagesavealpha($imagen_nueva, true);
    }
    
    // Redimensionar
    imagecopyresampled($imagen_nueva, $imagen_origen, 0, 0, 0, 0, 
                       $nuevo_ancho, $nuevo_alto, $ancho, $alto);
    
    // Guardar imagen
    switch ($tipo) {
        case IMAGETYPE_JPEG:
            imagejpeg($imagen_nueva, $ruta_imagen, 85);
            break;
        case IMAGETYPE_PNG:
            imagepng($imagen_nueva, $ruta_imagen, 8);
            break;
        case IMAGETYPE_GIF:
            imagegif($imagen_nueva, $ruta_imagen);
            break;
        case IMAGETYPE_WEBP:
            imagewebp($imagen_nueva, $ruta_imagen, 85);
            break;
    }
    
    // Liberar memoria
    imagedestroy($imagen_origen);
    imagedestroy($imagen_nueva);
    
    return true;
}

/**
 * Elimina un archivo del servidor
 * 
 * @param string $ruta_relativa Ruta relativa desde uploads/
 * @return bool
 */
function eliminar_archivo($ruta_relativa) {
    $ruta_completa = __DIR__ . '/../uploads/' . $ruta_relativa;
    
    if (file_exists($ruta_completa)) {
        return unlink($ruta_completa);
    }
    
    return false;
}

// ================================================
// 10. INVENTARIO Y STOCK
// ================================================

/**
 * Verifica si hay stock suficiente de un producto
 * 
 * @param int $producto_id ID del producto
 * @param int $sucursal_id ID de la sucursal
 * @param int $cantidad Cantidad requerida
 * @return bool
 */
function validar_stock_suficiente($producto_id, $sucursal_id, $cantidad) {
    global $pdo;
    
    $sql = "SELECT cantidad FROM inventario 
            WHERE producto_id = ? AND sucursal_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$producto_id, $sucursal_id]);
    $inventario = $stmt->fetch();
    
    if (!$inventario) {
        return false;
    }
    
    return $inventario['cantidad'] >= $cantidad;
}

/**
 * Obtiene la cantidad disponible de un producto en una sucursal
 * 
 * @param int $producto_id ID del producto
 * @param int $sucursal_id ID de la sucursal
 * @return int Cantidad disponible
 */
function obtener_stock_disponible($producto_id, $sucursal_id) {
    global $pdo;
    
    $sql = "SELECT cantidad FROM inventario 
            WHERE producto_id = ? AND sucursal_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$producto_id, $sucursal_id]);
    $inventario = $stmt->fetch();
    
    return $inventario ? $inventario['cantidad'] : 0;
}

/**
 * Verifica si un producto está con stock bajo
 * 
 * @param int $producto_id ID del producto
 * @param int $sucursal_id ID de la sucursal
 * @return bool
 */
function esta_stock_bajo($producto_id, $sucursal_id) {
    global $pdo;
    
    $sql = "SELECT cantidad, stock_minimo FROM inventario 
            WHERE producto_id = ? AND sucursal_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$producto_id, $sucursal_id]);
    $inventario = $stmt->fetch();
    
    if (!$inventario) {
        return false;
    }
    
    return $inventario['cantidad'] <= $inventario['stock_minimo'];
}

// ================================================
// 11. PRECIOS Y DESCUENTOS
// ================================================

/**
 * Calcula el precio con descuento
 * 
 * @param float $precio Precio original
 * @param float $porcentaje_descuento Porcentaje de descuento (0-100)
 * @return float Precio con descuento
 */
function calcular_precio_con_descuento($precio, $porcentaje_descuento) {
    return $precio * (1 - ($porcentaje_descuento / 100));
}

/**
 * Calcula el porcentaje de descuento entre dos precios
 * 
 * @param float $precio_original Precio original
 * @param float $precio_descuento Precio con descuento
 * @return float Porcentaje de descuento
 */
function calcular_porcentaje_descuento($precio_original, $precio_descuento) {
    if ($precio_original == 0) {
        return 0;
    }
    return (($precio_original - $precio_descuento) / $precio_original) * 100;
}

/**
 * Calcula el IVA de un monto
 * 
 * @param float $monto Monto sin IVA
 * @param float $porcentaje_iva Porcentaje de IVA (12 en Guatemala)
 * @return float IVA calculado
 */
function calcular_iva($monto, $porcentaje_iva = 12) {
    return $monto * ($porcentaje_iva / 100);
}

/**
 * Calcula el total con IVA incluido
 * 
 * @param float $monto Monto sin IVA
 * @param float $porcentaje_iva Porcentaje de IVA
 * @return float Total con IVA
 */
function calcular_total_con_iva($monto, $porcentaje_iva = 12) {
    return $monto * (1 + ($porcentaje_iva / 100));
}

// ================================================
// 12. CÁLCULOS FINANCIEROS
// ================================================

/**
 * Calcula la cuota de un crédito
 * 
 * @param float $monto_total Monto total del crédito
 * @param int $numero_cuotas Número de cuotas
 * @return float Valor de cada cuota
 */
function calcular_cuota_credito($monto_total, $numero_cuotas) {
    if ($numero_cuotas <= 0) {
        return 0;
    }
    return $monto_total / $numero_cuotas;
}

/**
 * Calcula el saldo pendiente de un crédito
 * 
 * @param float $monto_total Monto total
 * @param float $monto_pagado Monto ya pagado
 * @return float Saldo pendiente
 */
function calcular_saldo_pendiente($monto_total, $monto_pagado) {
    return max(0, $monto_total - $monto_pagado);
}

/**
 * Calcula días de atraso en un crédito
 * 
 * @param string $fecha_proximo_pago Fecha esperada de pago (YYYY-MM-DD)
 * @return int Días de atraso (0 si no hay atraso)
 */
function calcular_dias_atraso($fecha_proximo_pago) {
    $hoy = new DateTime();
    $fecha_pago = new DateTime($fecha_proximo_pago);
    
    if ($hoy <= $fecha_pago) {
        return 0;
    }
    
    $diferencia = $hoy->diff($fecha_pago);
    return $diferencia->days;
}

// ================================================
// 13. UTILIDADES GENERALES
// ================================================

/**
 * Trunca un texto a un número máximo de caracteres
 * 
 * @param string $texto Texto a truncar
 * @param int $longitud Longitud máxima
 * @param string $sufijo Sufijo a agregar (ej: "...")
 * @return string Texto truncado
 */
function truncar_texto($texto, $longitud = 100, $sufijo = '...') {
    if (mb_strlen($texto) <= $longitud) {
        return $texto;
    }
    return mb_substr($texto, 0, $longitud) . $sufijo;
}

/**
 * Convierte un texto a slug (URL amigable)
 * 
 * @param string $texto Texto a convertir
 * @return string Slug generado
 */
function texto_a_slug($texto) {
    // Convertir a minúsculas
    $slug = strtolower($texto);
    
    // Reemplazar caracteres especiales
    $slug = str_replace(
        ['á', 'é', 'í', 'ó', 'ú', 'ñ'],
        ['a', 'e', 'i', 'o', 'u', 'n'],
        $slug
    );
    
    // Eliminar todo lo que no sea letra, número o espacio
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    
    // Reemplazar espacios y guiones múltiples con un solo guion
    $slug = preg_replace('/[\s-]+/', '-', $slug);
    
    // Eliminar guiones al inicio y final
    $slug = trim($slug, '-');
    
    return $slug;
}

/**
 * Obtiene el nombre del día en español
 * 
 * @param int $numero_dia Número del día (0=Domingo, 6=Sábado)
 * @return string Nombre del día
 */
function nombre_dia($numero_dia) {
    $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    return $dias[$numero_dia] ?? '';
}

/**
 * Obtiene el nombre del mes en español
 * 
 * @param int $numero_mes Número del mes (1-12)
 * @return string Nombre del mes
 */
function nombre_mes($numero_mes) {
    $meses = [
        1 => 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
    ];
    return $meses[$numero_mes] ?? '';
}

/**
 * Calcula la edad a partir de una fecha de nacimiento
 * 
 * @param string $fecha_nacimiento Fecha de nacimiento (YYYY-MM-DD)
 * @return int Edad en años
 */
function calcular_edad($fecha_nacimiento) {
    $fecha_nac = new DateTime($fecha_nacimiento);
    $hoy = new DateTime();
    $edad = $hoy->diff($fecha_nac);
    return $edad->y;
}

/**
 * Genera un array de años para un select
 * 
 * @param int $inicio Año de inicio
 * @param int $fin Año de fin (null = año actual)
 * @return array Array de años
 */
function generar_array_anios($inicio = null, $fin = null) {
    $inicio = $inicio ?? (date('Y') - 10);
    $fin = $fin ?? date('Y');
    
    $anios = [];
    for ($i = $fin; $i >= $inicio; $i--) {
        $anios[] = $i;
    }
    return $anios;
}

/**
 * Convierte un array asociativo a XML
 * 
 * @param array $data Array de datos
 * @param string $root_element Elemento raíz
 * @return string XML generado
 */
function array_a_xml($data, $root_element = 'root') {
    $xml = new SimpleXMLElement("<$root_element/>");
    
    $array_to_xml = function($data, $xml) use (&$array_to_xml) {
        foreach($data as $key => $value) {
            if(is_array($value)) {
                if(is_numeric($key)) {
                    $key = 'item';
                }
                $subnode = $xml->addChild($key);
                $array_to_xml($value, $subnode);
            } else {
                $xml->addChild($key, htmlspecialchars($value));
            }
        }
    };
    
    $array_to_xml($data, $xml);
    
    return $xml->asXML();
}

/**
 * Calcula el tiempo transcurrido en formato legible
 * 
 * @param string $fecha_hora Fecha y hora (YYYY-MM-DD HH:MM:SS)
 * @return string Tiempo transcurrido (ej: "hace 5 minutos")
 */
function tiempo_transcurrido($fecha_hora) {
    $timestamp = strtotime($fecha_hora);
    $diferencia = time() - $timestamp;
    
    if ($diferencia < 60) {
        return 'hace ' . $diferencia . ' segundos';
    }
    
    $minutos = floor($diferencia / 60);
    if ($minutos < 60) {
        return 'hace ' . $minutos . ' minuto' . ($minutos > 1 ? 's' : '');
    }
    
    $horas = floor($minutos / 60);
    if ($horas < 24) {
        return 'hace ' . $horas . ' hora' . ($horas > 1 ? 's' : '');
    }
    
    $dias = floor($horas / 24);
    if ($dias < 30) {
        return 'hace ' . $dias . ' día' . ($dias > 1 ? 's' : '');
    }
    
    $meses = floor($dias / 30);
    if ($meses < 12) {
        return 'hace ' . $meses . ' mes' . ($meses > 1 ? 'es' : '');
    }
    
    $anios = floor($meses / 12);
    return 'hace ' . $anios . ' año' . ($anios > 1 ? 's' : '');
}

// ================================================
// 14. DEBUG Y DESARROLLO
// ================================================

/**
 * Imprime un array de forma legible (solo en desarrollo)
 * 
 * @param mixed $data Datos a imprimir
 * @param bool $die Si terminar la ejecución
 */
function debug($data, $die = false) {
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
        
        if ($die) {
            die();
        }
    }
}

/**
 * Imprime un var_dump formateado (solo en desarrollo)
 * 
 * @param mixed $data Datos a imprimir
 * @param bool $die Si terminar la ejecución
 */
function dd($data, $die = true) {
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
        
        if ($die) {
            die();
        }
    }
}

?>