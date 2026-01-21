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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo_pagina; ?> - <?php echo SISTEMA_NOMBRE; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --color-dorado: #D4AF37;
            --color-dorado-oscuro: #b8941f;
            --color-azul: #1e3a8a;
            --color-azul-claro: #3b82f6;
            --color-negro: #1a1a1a;
            --color-gris: #4b5563;
            --color-gris-claro: #6b7280;
            --color-plateado: #C0C0C0;
            --color-blanco: #FFFFFF;
            --color-fondo: #f9fafb;
            --color-verde: #059669;
            --color-rojo: #dc2626;
            --color-amarillo: #f59e0b;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: var(--color-fondo);
            font-family: 'Inter', sans-serif;
            color: var(--color-negro);
        }
        
        /* NAVBAR */
        .navbar {
            background: var(--color-azul);
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            padding: 12px 0;
            border-bottom: 3px solid var(--color-dorado);
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.4rem;
            color: var(--color-blanco) !important;
            font-family: 'Montserrat', sans-serif;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .navbar-brand img {
            width: 40px;
            height: 40px;
            object-fit: contain;
        }
        
        .navbar-brand i {
            font-size: 32px;
            color: var(--color-dorado);
        }
        
        .nav-link {
            color: rgba(255,255,255,0.9) !important;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .nav-link:hover {
            color: var(--color-dorado) !important;
        }
        
        .dropdown-menu {
            border: 2px solid var(--color-dorado);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        /* CONTENIDO PRINCIPAL */
        .main-content {
            padding: 30px 0;
            min-height: calc(100vh - 200px);
        }
        
        /* TARJETA DE BIENVENIDA */
        .welcome-card {
            background: var(--color-azul);
            color: var(--color-blanco);
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(30, 58, 138, 0.2);
            border: 2px solid var(--color-dorado);
        }
        
        .welcome-card h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
            font-family: 'Montserrat', sans-serif;
        }
        
        .welcome-card p {
            opacity: 0.95;
            margin: 0;
            font-size: 15px;
        }
        
        /* TARJETAS DE ESTADÍSTICAS */
        .stat-card {
            background: var(--color-blanco);
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.3s;
            border-left: 5px solid;
            border-top: 1px solid #e5e7eb;
            border-right: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.12);
        }
        
        .stat-card.dorado { border-left-color: var(--color-dorado); }
        .stat-card.azul { border-left-color: var(--color-azul); }
        .stat-card.verde { border-left-color: var(--color-verde); }
        .stat-card.amarillo { border-left-color: var(--color-amarillo); }
        .stat-card.rojo { border-left-color: var(--color-rojo); }
        .stat-card.plateado { border-left-color: var(--color-plateado); }
        
        .stat-icon {
            width: 55px;
            height: 55px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            margin-bottom: 15px;
        }
        
        .stat-card.dorado .stat-icon { background: #fffbeb; color: var(--color-dorado); }
        .stat-card.azul .stat-icon { background: #eff6ff; color: var(--color-azul); }
        .stat-card.verde .stat-icon { background: #ecfdf5; color: var(--color-verde); }
        .stat-card.amarillo .stat-icon { background: #fffbeb; color: var(--color-amarillo); }
        .stat-card.rojo .stat-icon { background: #fee; color: var(--color-rojo); }
        .stat-card.plateado .stat-icon { background: #f3f4f6; color: var(--color-gris); }
        
        .stat-value {
            font-size: 2.2rem;
            font-weight: 700;
            margin: 10px 0;
            color: var(--color-negro);
            font-family: 'Montserrat', sans-serif;
        }
        
        .stat-label {
            color: var(--color-gris);
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }
        
        /* ACCIONES RÁPIDAS */
        .quick-actions {
            background: var(--color-blanco);
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border: 1px solid #e5e7eb;
        }
        
        .quick-actions h5 {
            margin-bottom: 20px;
            font-weight: 700;
            color: var(--color-negro);
            font-family: 'Montserrat', sans-serif;
            font-size: 18px;
            border-bottom: 2px solid var(--color-dorado);
            padding-bottom: 10px;
        }
        
        .quick-action-btn {
            display: block;
            width: 100%;
            padding: 14px 16px;
            margin-bottom: 10px;
            border-radius: 8px;
            text-decoration: none;
            color: var(--color-negro);
            background: var(--color-fondo);
            border: 2px solid #e5e7eb;
            transition: all 0.3s;
            font-weight: 500;
        }
        
        .quick-action-btn:hover {
            background: var(--color-dorado);
            color: var(--color-blanco);
            border-color: var(--color-dorado);
            transform: translateX(5px);
        }
        
        .quick-action-btn i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        /* ALERTAS */
        .alert {
            border-radius: 8px;
            border: 2px solid;
            font-weight: 500;
        }
        
        .alert-success {
            background: #ecfdf5;
            border-color: var(--color-verde);
            color: #065f46;
        }
        
        .alert-danger {
            background: #fee;
            border-color: var(--color-rojo);
            color: #991b1b;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <?php if (file_exists('assets/img/logo-torre-fuerte.png')): ?>
                    <img src="assets/img/logo-torre-fuerte.png" alt="Logo">
                <?php else: ?>
                    <i class="bi bi-gem"></i>
                <?php endif; ?>
                <?php echo SISTEMA_NOMBRE; ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i>
                            <?php echo usuario_actual_nombre(); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <span class="dropdown-item-text">
                                    <small class="text-muted">
                                        <i class="bi bi-tag me-1"></i>
                                        <?php echo ucfirst(usuario_actual_rol()); ?>
                                    </small>
                                </span>
                            </li>
                            <?php if (usuario_actual_sucursal()): ?>
                            <li>
                                <span class="dropdown-item-text">
                                    <small class="text-muted">
                                        <i class="bi bi-building me-1"></i>
                                        <?php echo $_SESSION['usuario_sucursal_nombre']; ?>
                                    </small>
                                </span>
                            </li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="logout.php">
                                    <i class="bi bi-box-arrow-right me-2"></i>
                                    Cerrar Sesión
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
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
        <div class="welcome-card">
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
                        <div class="stat-card dorado">
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
                        <div class="stat-card azul">
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
                        <div class="stat-card plateado">
                            <div class="stat-icon">
                                <i class="bi bi-cart-check"></i>
                            </div>
                            <div class="stat-value"><?php echo number_format($estadisticas['ventas_hoy']); ?></div>
                            <div class="stat-label">Ventas Hoy</div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="stat-card verde">
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
                        <div class="stat-card amarillo">
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
                        <div class="stat-card amarillo">
                            <div class="stat-icon">
                                <i class="bi bi-tools"></i>
                            </div>
                            <div class="stat-value"><?php echo number_format($estadisticas['trabajos_pendientes']); ?></div>
                            <div class="stat-label">Trabajos en Proceso</div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="stat-card verde">
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
                        <div class="stat-card <?php echo $estadisticas['cajas_abiertas'] > 0 ? 'verde' : 'rojo'; ?>">
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
                <div class="quick-actions">
                    <h5>
                        <i class="bi bi-lightning-charge me-2"></i>
                        Acciones Rápidas
                    </h5>
                    
                    <?php if (tiene_permiso('ventas', 'crear')): ?>
                    <a href="modules/ventas/nueva.php" class="quick-action-btn">
                        <i class="bi bi-cart-plus"></i>
                        Nueva Venta
                    </a>
                    <?php endif; ?>
                    
                    <?php if (tiene_permiso('taller', 'crear')): ?>
                    <a href="modules/taller/nuevo.php" class="quick-action-btn">
                        <i class="bi bi-tools"></i>
                        Nuevo Trabajo
                    </a>
                    <?php endif; ?>
                    
                    <?php if (tiene_permiso('clientes', 'crear')): ?>
                    <a href="modules/clientes/nuevo.php" class="quick-action-btn">
                        <i class="bi bi-person-plus"></i>
                        Nuevo Cliente
                    </a>
                    <?php endif; ?>
                    
                    <?php if (tiene_permiso('caja', 'crear')): ?>
                    <a href="modules/caja/apertura.php" class="quick-action-btn">
                        <i class="bi bi-cash-coin"></i>
                        Abrir Caja
                    </a>
                    <?php endif; ?>
                    
                    <a href="modules/reportes/" class="quick-action-btn">
                        <i class="bi bi-graph-up"></i>
                        Ver Reportes
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>