<?php
/**
 * ================================================
 * DASHBOARD PRINCIPAL
 * ================================================
 * 
 * Vista principal del sistema con estadísticas generales
 * según los permisos del usuario autenticado.
 * 
 * TODO FASE 5: Las estadísticas se calculan en tiempo real
 * desde la base de datos usando las funciones helper.
 */

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
    <div class="welcome-card mb-4 fade-in">
        <h1 class="mb-2">
            <i class="bi bi-emoji-smile me-2"></i>
            ¡Bienvenido, <?php echo explode(' ', usuario_actual_nombre())[0]; ?>!
        </h1>
        <p class="mb-0">
            <i class="bi bi-calendar3 me-2"></i>
            <?php echo $fecha_texto; ?>
            <span class="ms-3">
                <i class="bi bi-clock me-2"></i>
                <?php echo date('h:i A'); ?>
            </span>
        </p>
    </div>
    
    <div class="row g-3">
        <!-- Estadísticas -->
        <div class="col-lg-9">
            <div class="row g-3">
                <!-- Total Productos -->
                <?php if (tiene_permiso('inventario', 'ver') || tiene_permiso('productos', 'ver')): ?>
                <div class="col-12 col-sm-6 col-lg-4">
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
                <div class="col-12 col-sm-6 col-lg-4">
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
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="stat-card plateado fade-in">
                        <div class="stat-icon">
                            <i class="bi bi-cart-check"></i>
                        </div>
                        <div class="stat-value"><?php echo number_format($estadisticas['ventas_hoy']); ?></div>
                        <div class="stat-label">Ventas Hoy</div>
                    </div>
                </div>
                
                <div class="col-12 col-sm-6 col-lg-4">
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
                <div class="col-12 col-sm-6 col-lg-4">
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
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="stat-card amarillo fade-in">
                        <div class="stat-icon">
                            <i class="bi bi-tools"></i>
                        </div>
                        <div class="stat-value"><?php echo number_format($estadisticas['trabajos_pendientes']); ?></div>
                        <div class="stat-label">Trabajos en Proceso</div>
                    </div>
                </div>
                
                <div class="col-12 col-sm-6 col-lg-4">
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
                <div class="col-12 col-sm-6 col-lg-4">
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
                <h5 class="mb-3">
                    <i class="bi bi-lightning-charge me-2"></i>
                    Acciones Rápidas
                </h5>
                
                <div class="d-grid gap-2">
                    <?php if (tiene_permiso('ventas', 'crear')): ?>
                    <a href="<?php echo BASE_URL; ?>modules/ventas/nueva.php" class="quick-action-btn">
                        <i class="bi bi-cart-plus"></i>
                        Nueva Venta
                    </a>
                    <?php endif; ?>
                    
                    <?php if (tiene_permiso('taller', 'crear')): ?>
                    <a href="<?php echo BASE_URL; ?>modules/taller/agregar.php" class="quick-action-btn">
                        <i class="bi bi-tools"></i>
                        Nuevo Trabajo
                    </a>
                    <?php endif; ?>
                    
                    <?php if (tiene_permiso('clientes', 'crear')): ?>
                    <a href="<?php echo BASE_URL; ?>modules/clientes/agregar.php" class="quick-action-btn">
                        <i class="bi bi-person-plus"></i>
                        Nuevo Cliente
                    </a>
                    <?php endif; ?>
                    
                    <?php if (tiene_permiso('caja', 'crear')): ?>
                    <a href="<?php echo BASE_URL; ?>modules/caja/abrir.php" class="quick-action-btn">
                        <i class="bi bi-cash-coin"></i>
                        Abrir Caja
                    </a>
                    <?php endif; ?>
                    
                    <a href="<?php echo BASE_URL; ?>modules/reportes/dashboard.php" class="quick-action-btn">
                        <i class="bi bi-graph-up"></i>
                        Ver Reportes
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* ============================================
   CORRECCIONES ESPECÍFICAS PARA DASHBOARD
   ============================================ */

/* Contenedor principal con padding adecuado */
.main-content {
    padding: 20px;
    min-height: calc(100vh - 120px);
}

/* Tarjeta de bienvenida */
.welcome-card {
    background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
    color: white;
    padding: 25px 30px;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.welcome-card h1 {
    font-size: 1.75rem;
    font-weight: 600;
}

.welcome-card p {
    font-size: 0.95rem;
    opacity: 0.95;
    color: white !important;
}

/* Stats cards con mejor espaciado */
.stat-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border-left: 4px solid;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12);
}

/* Colores de bordes según tipo */
.stat-card.dorado { border-left-color: #d4af37; }
.stat-card.azul { border-left-color: #1e3a8a; }
.stat-card.plateado { border-left-color: #c0c0c0; }
.stat-card.verde { border-left-color: #22c55e; }
.stat-card.amarillo { border-left-color: #eab308; }
.stat-card.rojo { border-left-color: #ef4444; }

/* Iconos de stats */
.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin-bottom: 15px;
}

.stat-card.dorado .stat-icon {
    background: rgba(212, 175, 55, 0.1);
    color: #d4af37;
}

.stat-card.azul .stat-icon {
    background: rgba(30, 58, 138, 0.1);
    color: #1e3a8a;
}

.stat-card.plateado .stat-icon {
    background: rgba(192, 192, 192, 0.1);
    color: #9ca3af;
}

.stat-card.verde .stat-icon {
    background: rgba(34, 197, 94, 0.1);
    color: #22c55e;
}

.stat-card.amarillo .stat-icon {
    background: rgba(234, 179, 8, 0.1);
    color: #eab308;
}

.stat-card.rojo .stat-icon {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

/* Valores y labels */
.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #1a1a1a;
    margin: 10px 0;
}

.stat-label {
    font-size: 0.875rem;
    color: #6b7280;
    font-weight: 500;
}

/* Acciones rápidas */
.quick-actions {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
    position: sticky;
    top: 20px;
}

.quick-actions h5 {
    color: #1a1a1a;
    font-weight: 600;
    font-size: 1.1rem;
}

.quick-action-btn {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    background: #f8f9fa;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    color: #374151;
    text-decoration: none;
    transition: all 0.2s ease;
    font-weight: 500;
}

.quick-action-btn i {
    font-size: 1.2rem;
    margin-right: 12px;
    color: #d4af37;
}

.quick-action-btn:hover {
    background: #d4af37;
    color: white;
    border-color: #d4af37;
    transform: translateX(5px);
}

.quick-action-btn:hover i {
    color: white;
}

/* Animación fade-in */
.fade-in {
    animation: fadeInUp 0.5s ease;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* ============================================
   RESPONSIVE - MOBILE FIRST
   ============================================ */

/* Móvil (< 576px) */
@media (max-width: 575.98px) {
    .main-content {
        padding: 15px 10px;
    }
    
    .welcome-card {
        padding: 20px 15px;
        margin-bottom: 15px;
    }
    
    .welcome-card h1 {
        font-size: 1.5rem;
    }
    
    .welcome-card p {
        font-size: 0.85rem;
    }
    
    .welcome-card p span {
        display: block;
        margin-top: 5px;
        margin-left: 0 !important;
    }
    
    .stat-card {
        padding: 15px;
    }
    
    .stat-icon {
        width: 45px;
        height: 45px;
        font-size: 20px;
        margin-bottom: 12px;
    }
    
    .stat-value {
        font-size: 1.75rem;
    }
    
    .stat-label {
        font-size: 0.8rem;
    }
    
    .quick-actions {
        position: static;
        margin-top: 15px;
    }
    
    .quick-action-btn {
        padding: 14px 16px;
        font-size: 0.95rem;
    }
}

/* Tablet (576px - 767.98px) */
@media (min-width: 576px) and (max-width: 767.98px) {
    .main-content {
        padding: 18px 15px;
    }
    
    .welcome-card {
        padding: 22px 25px;
    }
}

/* Tablet horizontal (768px - 991.98px) */
@media (min-width: 768px) and (max-width: 991.98px) {
    .main-content {
        padding: 20px;
    }
    
    .quick-actions {
        position: static;
        margin-top: 20px;
    }
}

/* Desktop (992px+) */
@media (min-width: 992px) {
    .main-content {
        padding: 25px 30px;
    }
    
    /* Grid de 3 columnas en desktop */
    .col-lg-4 {
        flex: 0 0 33.333333%;
        max-width: 33.333333%;
    }
}

/* Desktop grande (1200px+) */
@media (min-width: 1200px) {
    .main-content {
        padding: 30px 40px;
    }
}

/* Mejoras de accesibilidad para touch */
@media (max-width: 767.98px) {
    .quick-action-btn {
        min-height: 48px;
    }
}
</style>

<?php
// Incluir footer
include 'includes/footer.php';
?>