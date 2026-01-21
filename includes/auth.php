<?php
// ================================================
// SISTEMA DE AUTENTICACIÓN
// ================================================

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/funciones.php';

/**
 * Intenta autenticar un usuario
 * 
 * @param string $email Email del usuario
 * @param string $password Contraseña en texto plano
 * @return array|false Datos del usuario o false
 */
function intentar_login($email, $password) {
    global $pdo;
    
    // Buscar usuario por email
    $sql = "SELECT u.*, s.nombre as sucursal_nombre 
            FROM usuarios u 
            LEFT JOIN sucursales s ON u.sucursal_id = s.id 
            WHERE u.email = ? AND u.activo = 1";
    
    $usuario = db_query_one($sql, [$email]);
    
    if (!$usuario) {
        return false;
    }
    
    // Verificar contraseña
    if (!verificar_password($password, $usuario['password'])) {
        return false;
    }
    
    return $usuario;
}

/**
 * Inicia sesión para un usuario
 * 
 * @param array $usuario Datos del usuario
 */
function iniciar_sesion($usuario) {
    // Regenerar ID de sesión por seguridad
    session_regenerate_id(true);
    
    // Guardar datos en sesión
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_nombre'] = $usuario['nombre'];
    $_SESSION['usuario_email'] = $usuario['email'];
    $_SESSION['usuario_rol'] = $usuario['rol'];
    $_SESSION['usuario_sucursal_id'] = $usuario['sucursal_id'];
    $_SESSION['usuario_sucursal_nombre'] = $usuario['sucursal_nombre'];
    $_SESSION['sesion_inicio'] = time();
    $_SESSION['ultima_actividad'] = time();
    
    // Actualizar último acceso en BD
    $sql = "UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?";
    db_execute($sql, [$usuario['id']]);
    
    // Registrar en auditoría
    registrar_auditoria('usuarios', 'LOGIN', $usuario['id'], 'Inicio de sesión exitoso');
}

/**
 * Cierra la sesión del usuario
 */
function cerrar_sesion() {
    // Registrar en auditoría antes de cerrar sesión
    if (esta_autenticado()) {
        registrar_auditoria('usuarios', 'LOGOUT', usuario_actual_id(), 'Cierre de sesión');
    }
    
    // Destruir todas las variables de sesión
    $_SESSION = array();
    
    // Destruir la cookie de sesión
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 42000, '/');
    }
    
    // Destruir la sesión
    session_destroy();
}

/**
 * Verifica si la sesión es válida
 * 
 * @return bool
 */
function verificar_sesion() {
    if (!esta_autenticado()) {
        return false;
    }
    
    // Verificar timeout de sesión
    if (isset($_SESSION['ultima_actividad'])) {
        $tiempo_inactivo = time() - $_SESSION['ultima_actividad'];
        
        if ($tiempo_inactivo > SESSION_TIMEOUT) {
            cerrar_sesion();
            return false;
        }
    }
    
    // Actualizar última actividad
    $_SESSION['ultima_actividad'] = time();
    
    return true;
}

/**
 * Requiere que el usuario esté autenticado
 * Redirige a login si no lo está
 */
function requiere_autenticacion() {
    if (!verificar_sesion()) {
        mensaje_error('Debes iniciar sesión para acceder a esta página');
        redirigir('login.php');
    }
}

/**
 * Requiere un rol específico
 * Redirige si el usuario no tiene el rol
 * 
 * @param string|array $roles Rol(es) permitido(s)
 */
function requiere_rol($roles) {
    requiere_autenticacion();
    
    if (!tiene_rol($roles)) {
        mensaje_error('No tienes permisos para acceder a esta página');
        redirigir('dashboard.php');
    }
}

/**
 * Verifica si el usuario tiene permiso para una acción específica
 * 
 * @param string $modulo Nombre del módulo
 * @param string $accion Acción (ver, crear, editar, eliminar)
 * @return bool
 */
function tiene_permiso($modulo, $accion = 'ver') {
    if (!esta_autenticado()) {
        return false;
    }
    
    $rol = usuario_actual_rol();
    
    // Administrador y Dueño tienen todos los permisos
    if (in_array($rol, ['administrador', 'dueño'])) {
        return true;
    }
    
    // Matriz de permisos por rol
    $permisos = [
        'vendedor' => [
            'ventas' => ['ver', 'crear'],
            'clientes' => ['ver', 'crear', 'editar'],
            'productos' => ['ver'],
            'inventario' => ['ver'],
            'reportes' => ['ver']
        ],
        'cajero' => [
            'ventas' => ['ver', 'crear'],
            'caja' => ['ver', 'crear', 'editar'],
            'clientes' => ['ver'],
            'creditos' => ['ver', 'crear'],
            'reportes' => ['ver']
        ],
        'orfebre' => [
            'taller' => ['ver', 'crear', 'editar'],
            'materias_primas' => ['ver', 'crear'],
            'reportes' => ['ver']
        ],
        'publicidad' => [
            'productos' => ['ver'],
            'clientes' => ['ver'],
            'reportes' => ['ver']
        ]
    ];
    
    // Verificar si el rol tiene permiso para el módulo y acción
    if (isset($permisos[$rol][$modulo])) {
        return in_array($accion, $permisos[$rol][$modulo]);
    }
    
    return false;
}

/**
 * Obtiene el menú de navegación según el rol del usuario
 * 
 * @return array Items del menú
 */
function obtener_menu_usuario() {
    $rol = usuario_actual_rol();
    
    $menu = [];
    
    // Dashboard visible para todos
    $menu[] = [
        'texto' => 'Dashboard',
        'url' => 'dashboard.php',
        'icono' => 'bi-speedometer2'
    ];
    
    // Menú según rol
    switch ($rol) {
        case 'administrador':
        case 'dueño':
            $menu[] = ['texto' => 'Inventario', 'url' => 'modules/inventario/', 'icono' => 'bi-box-seam'];
            $menu[] = ['texto' => 'Ventas', 'url' => 'modules/ventas/', 'icono' => 'bi-cart'];
            $menu[] = ['texto' => 'Taller', 'url' => 'modules/taller/', 'icono' => 'bi-tools'];
            $menu[] = ['texto' => 'Clientes', 'url' => 'modules/clientes/', 'icono' => 'bi-people'];
            $menu[] = ['texto' => 'Caja', 'url' => 'modules/caja/', 'icono' => 'bi-cash-coin'];
            $menu[] = ['texto' => 'Proveedores', 'url' => 'modules/proveedores/', 'icono' => 'bi-truck'];
            $menu[] = ['texto' => 'Reportes', 'url' => 'modules/reportes/', 'icono' => 'bi-graph-up'];
            $menu[] = ['texto' => 'Configuración', 'url' => 'modules/configuracion/', 'icono' => 'bi-gear'];
            break;
            
        case 'vendedor':
            $menu[] = ['texto' => 'Ventas', 'url' => 'modules/ventas/', 'icono' => 'bi-cart'];
            $menu[] = ['texto' => 'Clientes', 'url' => 'modules/clientes/', 'icono' => 'bi-people'];
            $menu[] = ['texto' => 'Inventario', 'url' => 'modules/inventario/', 'icono' => 'bi-box-seam'];
            $menu[] = ['texto' => 'Reportes', 'url' => 'modules/reportes/', 'icono' => 'bi-graph-up'];
            break;
            
        case 'cajero':
            $menu[] = ['texto' => 'Ventas', 'url' => 'modules/ventas/', 'icono' => 'bi-cart'];
            $menu[] = ['texto' => 'Caja', 'url' => 'modules/caja/', 'icono' => 'bi-cash-coin'];
            $menu[] = ['texto' => 'Clientes', 'url' => 'modules/clientes/', 'icono' => 'bi-people'];
            $menu[] = ['texto' => 'Reportes', 'url' => 'modules/reportes/', 'icono' => 'bi-graph-up'];
            break;
            
        case 'orfebre':
            $menu[] = ['texto' => 'Taller', 'url' => 'modules/taller/', 'icono' => 'bi-tools'];
            $menu[] = ['texto' => 'Materias Primas', 'url' => 'modules/inventario/materias-primas.php', 'icono' => 'bi-gem'];
            $menu[] = ['texto' => 'Reportes', 'url' => 'modules/reportes/', 'icono' => 'bi-graph-up'];
            break;
            
        case 'publicidad':
            $menu[] = ['texto' => 'Productos', 'url' => 'modules/inventario/', 'icono' => 'bi-box-seam'];
            $menu[] = ['texto' => 'Clientes', 'url' => 'modules/clientes/', 'icono' => 'bi-people'];
            $menu[] = ['texto' => 'Reportes', 'url' => 'modules/reportes/', 'icono' => 'bi-graph-up'];
            break;
    }
    
    return $menu;
}
?>