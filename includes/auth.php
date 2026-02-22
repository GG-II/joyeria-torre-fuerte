<?php
// ================================================
// SISTEMA DE AUTENTICACIÓN
// ================================================

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/funciones.php';

function intentar_login($email, $password) {
    global $pdo;
    
    $sql = "SELECT u.*, s.nombre as sucursal_nombre 
            FROM usuarios u 
            LEFT JOIN sucursales s ON u.sucursal_id = s.id 
            WHERE u.email = ? AND u.activo = 1";
    
    $usuario = db_query_one($sql, [$email]);
    
    if (!$usuario) return false;
    if (!verificar_password($password, $usuario['password'])) return false;
    
    return $usuario;
}

function iniciar_sesion($usuario) {
    session_regenerate_id(true);
    
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_nombre'] = $usuario['nombre'];
    $_SESSION['usuario_email'] = $usuario['email'];
    $_SESSION['usuario_rol'] = $usuario['rol'];
    $_SESSION['usuario_sucursal_id'] = $usuario['sucursal_id'];
    $_SESSION['usuario_sucursal_nombre'] = $usuario['sucursal_nombre'];
    $_SESSION['sesion_inicio'] = time();
    $_SESSION['ultima_actividad'] = time();
    
    $sql = "UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?";
    db_execute($sql, [$usuario['id']]);
    
    registrar_auditoria('usuarios', 'LOGIN', $usuario['id'], 'Inicio de sesión exitoso');
}

function cerrar_sesion() {
    if (esta_autenticado()) {
        registrar_auditoria('usuarios', 'LOGOUT', usuario_actual_id(), 'Cierre de sesión');
    }
    
    $_SESSION = array();
    
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 42000, '/');
    }
    
    session_destroy();
}

function verificar_sesion() {
    if (!esta_autenticado()) return false;
    
    if (isset($_SESSION['ultima_actividad'])) {
        $tiempo_inactivo = time() - $_SESSION['ultima_actividad'];
        if ($tiempo_inactivo > SESSION_TIMEOUT) {
            cerrar_sesion();
            return false;
        }
    }
    
    $_SESSION['ultima_actividad'] = time();
    return true;
}

function requiere_autenticacion() {
    if (!verificar_sesion()) {
        mensaje_error('Debes iniciar sesión para acceder a esta página');
        redirigir('login.php');
    }
}

function requiere_rol($roles) {
    requiere_autenticacion();
    
    if (!tiene_rol($roles)) {
        mensaje_error('No tienes permisos para acceder a esta página');
        redirigir('dashboard.php');
    }
}

function tiene_permiso($modulo, $accion = 'ver') {
    if (!esta_autenticado()) return false;
    
    $rol = usuario_actual_rol();
    
    // Administrador y Dueño tienen todos los permisos
    if (in_array($rol, ['administrador', 'dueño'])) return true;
    
    $permisos = [
'vendedor' => [
    'ventas'          => ['ver', 'crear'],
    'clientes'        => ['ver', 'crear', 'editar'],
    'productos'       => ['ver'],
    'inventario'      => ['ver'],
    'caja'            => ['ver', 'crear', 'editar'],
    'taller'          => ['ver', 'crear', 'editar'],
    'categorias'      => ['ver'],   // ← agregar
    'sucursales'      => ['ver'],   // ← agregar
],
'cajero' => [
    'ventas'          => ['ver', 'crear'],
    'clientes'        => ['ver'],
    'productos'       => ['ver'],
    'inventario'      => ['ver'],
    'caja'            => ['ver', 'crear', 'editar'],
    'creditos'        => ['ver', 'crear'],
    'categorias'      => ['ver'],   // ← agregar
    'sucursales'      => ['ver'],   // ← agregar
],
'orfebre' => [
    'ventas'          => ['ver', 'crear'],
    'clientes'        => ['ver'],
    'productos'       => ['ver'],
    'inventario'      => ['ver'],
    'taller'          => ['ver', 'crear', 'editar'],
    'materias_primas' => ['ver', 'crear'],
    'categorias'      => ['ver'],   // ← agregar
    'sucursales'      => ['ver'],   // ← agregar
],
'publicidad' => [
    'productos'       => ['ver'],
    'inventario'      => ['ver'],
    'clientes'        => ['ver'],
    'reportes'        => ['ver'],
    'categorias'      => ['ver'],   // ← agregar
    'sucursales'      => ['ver'],   // ← agregar
]
    ];
    
    if (isset($permisos[$rol][$modulo])) {
        return in_array($accion, $permisos[$rol][$modulo]);
    }
    
    return false;
}

function obtener_menu_usuario() {
    $rol = usuario_actual_rol();
    
    $menu = [];
    $menu[] = [
        'texto' => 'Dashboard',
        'url' => 'dashboard.php',
        'icono' => 'bi-speedometer2'
    ];
    
    switch ($rol) {
        case 'administrador':
        case 'dueño':
            $menu[] = ['texto' => 'Ventas',        'url' => 'modules/ventas/',          'icono' => 'bi-cart'];
            $menu[] = ['texto' => 'Clientes',       'url' => 'modules/clientes/',        'icono' => 'bi-people'];
            $menu[] = ['texto' => 'Inventario',     'url' => 'modules/inventario/',      'icono' => 'bi-box-seam'];
            $menu[] = ['texto' => 'Taller',         'url' => 'modules/taller/',          'icono' => 'bi-tools'];
            $menu[] = ['texto' => 'Caja',           'url' => 'modules/caja/',            'icono' => 'bi-cash-coin'];
            $menu[] = ['texto' => 'Proveedores',    'url' => 'modules/proveedores/',     'icono' => 'bi-truck'];
            $menu[] = ['texto' => 'Reportes',       'url' => 'modules/reportes/',        'icono' => 'bi-graph-up'];
            $menu[] = ['texto' => 'Configuración',  'url' => 'modules/configuracion/',   'icono' => 'bi-gear'];
            break;
            
        case 'vendedor':
            $menu[] = ['texto' => 'Ventas',         'url' => 'modules/ventas/',          'icono' => 'bi-cart'];
            $menu[] = ['texto' => 'Clientes',       'url' => 'modules/clientes/',        'icono' => 'bi-people'];
            $menu[] = ['texto' => 'Inventario',     'url' => 'modules/inventario/',      'icono' => 'bi-box-seam'];
            $menu[] = ['texto' => 'Caja',           'url' => 'modules/caja/',            'icono' => 'bi-cash-coin'];
            $menu[] = ['texto' => 'Taller',         'url' => 'modules/taller/',          'icono' => 'bi-tools'];
            break;
            
        case 'cajero':
            $menu[] = ['texto' => 'Ventas',         'url' => 'modules/ventas/',          'icono' => 'bi-cart'];
            $menu[] = ['texto' => 'Clientes',       'url' => 'modules/clientes/',        'icono' => 'bi-people'];
            $menu[] = ['texto' => 'Inventario',     'url' => 'modules/inventario/',      'icono' => 'bi-box-seam'];
            $menu[] = ['texto' => 'Caja',           'url' => 'modules/caja/',            'icono' => 'bi-cash-coin'];
            break;
            
        case 'orfebre':
            $menu[] = ['texto' => 'Ventas',         'url' => 'modules/ventas/',          'icono' => 'bi-cart'];
            $menu[] = ['texto' => 'Clientes',       'url' => 'modules/clientes/',        'icono' => 'bi-people'];
            $menu[] = ['texto' => 'Inventario',     'url' => 'modules/inventario/',      'icono' => 'bi-box-seam'];
            $menu[] = ['texto' => 'Taller',         'url' => 'modules/taller/',          'icono' => 'bi-tools'];
            break;
            
        case 'publicidad':
            $menu[] = ['texto' => 'Inventario',     'url' => 'modules/inventario/',      'icono' => 'bi-box-seam'];
            $menu[] = ['texto' => 'Clientes',       'url' => 'modules/clientes/',        'icono' => 'bi-people'];
            $menu[] = ['texto' => 'Reportes',       'url' => 'modules/reportes/',        'icono' => 'bi-graph-up'];
            break;
    }
    
    return $menu;
}
?>