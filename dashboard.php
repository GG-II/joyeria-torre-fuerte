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
    
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.3rem;
        }
        
        .main-content {
            padding: 30px 0;
        }
        
        .welcome-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
        }
        
        .welcome-card h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .welcome-card p {
            opacity: 0.9;
            margin: 0;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s;
            border-left: 4px solid;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .stat-card.primary { border-color: #667eea; }
        .stat-card.success { border-color: #28a745; }
        .stat-card.warning { border-color: #ffc107; }
        .stat-card.danger { border-color: #dc3545; }
        .stat-card.info { border-color: #17a2b8; }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 15px;
        }
        
        .stat-card.primary .stat-icon { background: rgba(102, 126, 234, 0.1); color: #667eea; }
        .stat-card.success .stat-icon { background: rgba(40, 167, 69, 0.1); color: #28a745; }
        .stat-card.warning .stat-icon { background: rgba(255, 193, 7, 0.1); color: #ffc107; }
        .stat-card.danger .stat-icon { background: rgba(220, 53, 69, 0.1); color: #dc3545; }
        .stat-card.info .stat-icon { background: rgba(23, 162, 184, 0.1); color: #17a2b8; }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin: 10px 0;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .quick-actions {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .quick-actions h5 {
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .quick-action-btn {
            display: block;
            width: 100%;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 10px;
            text-decoration: none;
            color: #495057;
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            transition: all 0.3s;
        }
        
        .quick-action-btn:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
            transform: translateX(5px);
        }
        
        .quick-action-btn i {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-gem me-2"></i>
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
                <?php
                $fecha = new DateTime();
                $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
                echo $dias[$fecha->format('w')] . ', ' . $fecha->format('d') . ' de ' . $meses[$fecha->format('n')-1] . ' de ' . $fecha->format('Y');
                ?>
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
                        <div class="stat-card primary">
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
                        <div class="stat-card success">
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
                        <div class="stat-card info">
                            <div class="stat-icon">
                                <i class="bi bi-cart-check"></i>
                            </div>
                            <div class="stat-value"><?php echo number_format($estadisticas['ventas_hoy']); ?></div>
                            <div class="stat-label">Ventas Hoy</div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="stat-card success">
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
                        <div class="stat-card warning">
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
                        <div class="stat-card warning">
                            <div class="stat-icon">
                                <i class="bi bi-tools"></i>
                            </div>
                            <div class="stat-value"><?php echo number_format($estadisticas['trabajos_pendientes']); ?></div>
                            <div class="stat-label">Trabajos en Proceso</div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="stat-card success">
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
                        <div class="stat-card <?php echo $estadisticas['cajas_abiertas'] > 0 ? 'success' : 'danger'; ?>">
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