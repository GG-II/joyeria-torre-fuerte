<?php
// ================================================
// DASHBOARD PRINCIPAL
// ================================================

require_once 'config.php';
require_once 'includes/db.php';
require_once 'includes/funciones.php';
require_once 'includes/auth.php';

// Requiere autenticación
requiere_autenticacion();

// Obtener estadísticas según el rol
$estadisticas = [];

// Estadísticas generales
$estadisticas['total_productos'] = db_count('productos', 'activo = 1');
$estadisticas['total_clientes'] = db_count('clientes', 'activo = 1');
$estadisticas['total_usuarios'] = db_count('usuarios', 'activo = 1');

// Si tiene acceso a inventario
if (tiene_permiso('inventario', 'ver')) {
    $sucursal_id = usuario_actual_sucursal();
    $where_sucursal = $sucursal_id ? "sucursal_id = $sucursal_id" : "1=1";
    
    $estadisticas['productos_stock_bajo'] = db_count('inventario', "cantidad <= stock_minimo AND $where_sucursal");
}

// Si tiene acceso a ventas
if (tiene_permiso('ventas', 'ver')) {
    $sql_ventas_hoy = "SELECT COUNT(*) as total FROM ventas WHERE DATE(fecha) = CURDATE() AND estado = 'completada'";
    $result = db_query_one($sql_ventas_hoy);
    $estadisticas['ventas_hoy'] = $result['total'];
    
    $sql_total_hoy = "SELECT COALESCE(SUM(total), 0) as monto FROM ventas WHERE DATE(fecha) = CURDATE() AND estado = 'completada'";
    $result = db_query_one($sql_total_hoy);
    $estadisticas['monto_ventas_hoy'] = $result['monto'];
}

// Si tiene acceso a taller
if (tiene_permiso('taller', 'ver')) {
    $estadisticas['trabajos_pendientes'] = db_count('trabajos_taller', "estado IN ('recibido', 'en_proceso')");
    $estadisticas['trabajos_listos'] = db_count('trabajos_taller', "estado = 'listo'");
}

// Si tiene acceso a caja
if (tiene_permiso('caja', 'ver')) {
    $sucursal_id = usuario_actual_sucursal();
    $where_caja = $sucursal_id ? "sucursal_id = $sucursal_id" : "1=1";
    
    $estadisticas['cajas_abiertas'] = db_count('cajas', "estado = 'abierta' AND $where_caja");
}

// Fecha en español
$dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
$meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$fecha_obj = new DateTime();
$fecha_texto = $dias[$fecha_obj->format('w')] . ', ' . $fecha_obj->format('d') . ' de ' . $meses[$fecha_obj->format('n')-1] . ' de ' . $fecha_obj->format('Y');

// Título de página
$titulo_pagina = 'Dashboard';

// Incluir header
include 'includes/header.php';

// Incluir navbar
include 'includes/navbar.php';
?>

<!-- Contenido Principal -->
<div class="container-fluid main-content">
    <!-- Mensaje de éxito -->
    <?php
    $mensaje_exito = obtener_mensaje_exito();
    if ($mensaje_exito):
    ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        <?php echo $mensaje_exito; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <!-- Tarjeta de Bienvenida -->
    <div class="welcome-card fade-in">
        <h1>
            <i class="bi bi-emoji-smile me-2"></i>
            ¡Bienvenido, <?php echo explode(' ', usuario_actual_nombre())[0]; ?>!
        </h1>
        <p>
            <i class="bi bi-calendar3 me-2"></i>
            <?php echo $fecha_texto; ?>
            <span class="ms-3">
                <i class="bi bi-clock me-2"></i>
                <?php echo date('h:i A'); ?>
            </span>
        </p>
    </div>
    
    <div class="row">
        <!-- Estadísticas -->
        <div class="col-lg-9">
            <div class="row">
                <!-- Total Productos -->
                <?php if (tiene_permiso('inventario', 'ver') || tiene_permiso('productos', 'ver')): ?>
                <div class="col-md-4">
                    <div class="stat-card dorado fade-in">
                        <div class="stat-icon">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <div class="stat-value"><?php echo number_format($estadisticas['total_productos']); ?></div>
                        <div class="stat-label">Productos Activos</div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Total Clientes -->
                <?php if (tiene_permiso('clientes', 'ver')): ?>
                <div class="col-md-4">
                    <div class="stat-card azul fade-in">
                        <div class="stat-icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="stat-value"><?php echo number_format($estadisticas['total_clientes']); ?></div>
                        <div class="stat-label">Clientes Registrados</div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Ventas Hoy -->
                <?php if (tiene_permiso('ventas', 'ver')): ?>
                <div class="col-md-4">
                    <div class="stat-card plateado fade-in">
                        <div class="stat-icon">
                            <i class="bi bi-cart-check"></i>
                        </div>
                        <div class="stat-value"><?php echo number_format($estadisticas['ventas_hoy']); ?></div>
                        <div class="stat-label">Ventas Hoy</div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="stat-card verde fade-in">
                        <div class="stat-icon">
                            <i class="bi bi-cash-coin"></i>
                        </div>
                        <div class="stat-value"><?php echo formato_dinero($estadisticas['monto_ventas_hoy']); ?></div>
                        <div class="stat-label">Total Vendido Hoy</div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Stock Bajo -->
                <?php if (tiene_permiso('inventario', 'ver')): ?>
                <div class="col-md-4">
                    <div class="stat-card amarillo fade-in">
                        <div class="stat-icon">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <div class="stat-value"><?php echo number_format($estadisticas['productos_stock_bajo']); ?></div>
                        <div class="stat-label">Productos Stock Bajo</div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Trabajos Taller -->
                <?php if (tiene_permiso('taller', 'ver')): ?>
                <div class="col-md-4">
                    <div class="stat-card amarillo fade-in">
                        <div class="stat-icon">
                            <i class="bi bi-tools"></i>
                        </div>
                        <div class="stat-value"><?php echo number_format($estadisticas['trabajos_pendientes']); ?></div>
                        <div class="stat-label">Trabajos en Proceso</div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="stat-card verde fade-in">
                        <div class="stat-icon">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div class="stat-value"><?php echo number_format($estadisticas['trabajos_listos']); ?></div>
                        <div class="stat-label">Trabajos Listos</div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Cajas Abiertas -->
                <?php if (tiene_permiso('caja', 'ver')): ?>
                <div class="col-md-4">
                    <div class="stat-card <?php echo $estadisticas['cajas_abiertas'] > 0 ? 'verde' : 'rojo'; ?> fade-in">
                        <div class="stat-icon">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                        <div class="stat-value"><?php echo number_format($estadisticas['cajas_abiertas']); ?></div>
                        <div class="stat-label">Cajas Abiertas</div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Acciones Rápidas -->
        <div class="col-lg-3">
            <div class="quick-actions fade-in">
                <h5>
                    <i class="bi bi-lightning-charge me-2"></i>
                    Acciones Rápidas
                </h5>
                
                <?php if (tiene_permiso('ventas', 'crear')): ?>
                <a href="<?php echo BASE_URL; ?>modules/ventas/nueva.php" class="quick-action-btn">
                    <i class="bi bi-cart-plus"></i>
                    Nueva Venta
                </a>
                <?php endif; ?>
                
                <?php if (tiene_permiso('taller', 'crear')): ?>
                <a href="<?php echo BASE_URL; ?>modules/taller/nuevo.php" class="quick-action-btn">
                    <i class="bi bi-tools"></i>
                    Nuevo Trabajo
                </a>
                <?php endif; ?>
                
                <?php if (tiene_permiso('clientes', 'crear')): ?>
                <a href="<?php echo BASE_URL; ?>modules/clientes/nuevo.php" class="quick-action-btn">
                    <i class="bi bi-person-plus"></i>
                    Nuevo Cliente
                </a>
                <?php endif; ?>
                
                <?php if (tiene_permiso('caja', 'crear')): ?>
                <a href="<?php echo BASE_URL; ?>modules/caja/apertura.php" class="quick-action-btn">
                    <i class="bi bi-cash-coin"></i>
                    Abrir Caja
                </a>
                <?php endif; ?>
                
                <a href="<?php echo BASE_URL; ?>modules/reportes/" class="quick-action-btn">
                    <i class="bi bi-graph-up"></i>
                    Ver Reportes
                </a>
            </div>
        </div>
    </div>
</div>

<?php
// Incluir footer
include 'includes/footer.php';
?>
