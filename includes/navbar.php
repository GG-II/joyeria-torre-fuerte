<?php
// ================================================
// NAVBAR - NAVEGACIÓN PRINCIPAL
// ================================================

// Obtener la ruta actual para marcar el menú activo
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// Función para determinar si un menú está activo
function is_active($module) {
    global $current_dir;
    return $current_dir === $module ? 'active' : '';
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <!-- Logo y Nombre -->
        <a class="navbar-brand d-flex align-items-center" href="<?php echo BASE_URL; ?>dashboard.php">
            <i class="bi bi-gem me-2" style="font-size: 1.5rem; color: var(--color-dorado);"></i>
            <span class="fw-bold">Joyería Torre Fuerte</span>
        </a>

        <!-- Botón toggle para móvil -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Menú de Navegación -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page === 'dashboard.php') ? 'active' : ''; ?>" 
                       href="<?php echo BASE_URL; ?>dashboard.php">
                        <i class="bi bi-speedometer2"></i>
                        Dashboard
                    </a>
                </li>

                <!-- Ventas -->
                <?php if (tiene_permiso('ventas', 'ver')): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo is_active('ventas'); ?>" 
                       href="#" 
                       role="button" 
                       data-bs-toggle="dropdown">
                        <i class="bi bi-cart-check"></i>
                        Ventas
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/ventas/lista.php">
                                <i class="bi bi-list-ul"></i>
                                Historial de Ventas
                            </a>
                        </li>
                        <?php if (tiene_permiso('ventas', 'crear')): ?>
                        <li>
                            <a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/ventas/nueva.php">
                                <i class="bi bi-cart-plus"></i>
                                Nueva Venta (POS)
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- Clientes -->
                <?php if (tiene_permiso('clientes', 'ver')): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo is_active('clientes'); ?>" 
                       href="#" 
                       role="button" 
                       data-bs-toggle="dropdown">
                        <i class="bi bi-people"></i>
                        Clientes
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/clientes/lista.php">
                                <i class="bi bi-list-ul"></i>
                                Listado de Clientes
                            </a>
                        </li>
                        <?php if (tiene_permiso('clientes', 'crear')): ?>
                        <li>
                            <a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/clientes/agregar.php">
                                <i class="bi bi-person-plus"></i>
                                Nuevo Cliente
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- Inventario -->
                <?php if (tiene_permiso('inventario', 'ver')): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo is_active('inventario'); ?>" 
                       href="#" 
                       role="button" 
                       data-bs-toggle="dropdown">
                        <i class="bi bi-box-seam"></i>
                        Inventario
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/inventario/lista.php">
                                <i class="bi bi-list-ul"></i>
                                Listado de Productos
                            </a>
                        </li>
                        <?php if (tiene_permiso('inventario', 'crear')): ?>
                        <li>
                            <a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/inventario/agregar.php">
                                <i class="bi bi-plus-circle"></i>
                                Nuevo Producto
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/inventario/transferir.php">
                                <i class="bi bi-arrow-left-right"></i>
                                Transferir entre Sucursales
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- Taller -->
                <?php if (tiene_permiso('taller', 'ver')): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo is_active('taller'); ?>" 
                       href="#" 
                       role="button" 
                       data-bs-toggle="dropdown">
                        <i class="bi bi-tools"></i>
                        Taller
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/taller/lista.php">
                                <i class="bi bi-list-ul"></i>
                                Trabajos del Taller
                            </a>
                        </li>
                        <?php if (tiene_permiso('taller', 'crear')): ?>
                        <li>
                            <a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/taller/agregar.php">
                                <i class="bi bi-plus-circle"></i>
                                Nuevo Trabajo
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/taller/transferir.php">
                                <i class="bi bi-arrow-left-right"></i>
                                Transferir Trabajo
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- Caja -->
                <?php if (tiene_permiso('caja', 'ver')): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo is_active('caja'); ?>" 
                       href="#" 
                       role="button" 
                       data-bs-toggle="dropdown">
                        <i class="bi bi-cash-stack"></i>
                        Caja
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/caja/lista.php">
                                <i class="bi bi-list-ul"></i>
                                Historial de Cajas
                            </a>
                        </li>
                        <?php if (tiene_permiso('caja', 'crear')): ?>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/caja/abrir.php">
                                <i class="bi bi-box-arrow-in-down"></i>
                                Abrir Caja
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- Proveedores -->
                <?php if (tiene_permiso('proveedores', 'ver')): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo is_active('proveedores'); ?>" 
                       href="#" 
                       role="button" 
                       data-bs-toggle="dropdown">
                        <i class="bi bi-truck"></i>
                        Proveedores
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/proveedores/lista.php">
                                <i class="bi bi-list-ul"></i>
                                Listado de Proveedores
                            </a>
                        </li>
                        <?php if (tiene_permiso('proveedores', 'crear')): ?>
                        <li>
                            <a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/proveedores/agregar.php">
                                <i class="bi bi-plus-circle"></i>
                                Nuevo Proveedor
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- Reportes -->
                <?php if (tiene_permiso('reportes', 'ver')): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo is_active('reportes'); ?>" 
                       href="#" 
                       role="button" 
                       data-bs-toggle="dropdown">
                        <i class="bi bi-graph-up"></i>
                        Reportes
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/reportes/dashboard.php">
                                <i class="bi bi-speedometer2"></i>
                                Dashboard General
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/reportes/ventas.php">
                                <i class="bi bi-cart-check"></i>
                                Reporte de Ventas
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/reportes/inventario.php">
                                <i class="bi bi-box-seam"></i>
                                Reporte de Inventario
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/reportes/taller.php">
                                <i class="bi bi-tools"></i>
                                Reporte de Taller
                            </a>
                        </li>
                        <?php if (tiene_permiso('reportes', 'ver') && in_array($_SESSION['usuario_rol'] ?? '', ['administrador', 'dueño'])): ?>
                        <li>
                            <a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/reportes/financiero.php">
                                <i class="bi bi-cash-stack"></i>
                                Reporte Financiero
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- Configuración (solo admin/dueño) -->
                <?php if (in_array($_SESSION['usuario_rol'] ?? '', ['administrador', 'dueño'])): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo is_active('configuracion'); ?>" 
                       href="#" 
                       role="button" 
                       data-bs-toggle="dropdown">
                        <i class="bi bi-gear"></i>
                        Configuración
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/configuracion/usuarios.php">
                                <i class="bi bi-people"></i>
                                Gestión de Usuarios
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/configuracion/sucursales.php">
                                <i class="bi bi-building"></i>
                                Sucursales
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/configuracion/permisos.php">
                                <i class="bi bi-shield-check"></i>
                                Roles y Permisos
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/configuracion/sistema.php">
                                <i class="bi bi-sliders"></i>
                                Configuración del Sistema
                            </a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>
            </ul>

            <!-- Menú de Usuario (derecha) -->
            <ul class="navbar-nav ms-auto">
                <!-- Notificaciones -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" 
                       href="#" 
                       role="button" 
                       data-bs-toggle="dropdown">
                        <i class="bi bi-bell"></i>
                        <span class="badge bg-danger rounded-pill">3</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" style="min-width: 300px;">
                        <li class="dropdown-header">Notificaciones</li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="#">
                                <small class="text-danger">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    12 productos bajo stock
                                </small>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#">
                                <small class="text-warning">
                                    <i class="bi bi-clock"></i>
                                    3 trabajos con entrega próxima
                                </small>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#">
                                <small class="text-info">
                                    <i class="bi bi-cash"></i>
                                    Recordatorio: Cerrar caja
                                </small>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-center" href="#">
                                <small>Ver todas las notificaciones</small>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Usuario -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" 
                       href="#" 
                       role="button" 
                       data-bs-toggle="dropdown">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                             style="width: 32px; height: 32px;">
                            <i class="bi bi-person"></i>
                        </div>
                        <span><?php echo $_SESSION['usuario_nombre'] ?? 'Usuario'; ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li class="dropdown-header">
                            <div class="text-center">
                                <strong><?php echo $_SESSION['usuario_nombre'] ?? 'Usuario'; ?></strong>
                                <br>
                                <small class="text-muted">
                                    <?php echo ucfirst($_SESSION['usuario_rol'] ?? 'usuario'); ?>
                                </small>
                                <br>
                                <small class="text-muted">
                                    <?php echo $_SESSION['usuario_sucursal_nombre'] ?? 'Todas las sucursales'; ?>
                                </small>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="<?php echo BASE_URL; ?>perfil.php">
                                <i class="bi bi-person-circle"></i>
                                Mi Perfil
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?php echo BASE_URL; ?>configuracion/sistema.php">
                                <i class="bi bi-gear"></i>
                                Configuración
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>logout.php">
                                <i class="bi bi-box-arrow-right"></i>
                                Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<style>
/* Estilos adicionales para la navbar */
.navbar-dark .nav-link {
    color: rgba(255, 255, 255, 0.8);
    transition: all 0.3s ease;
}

.navbar-dark .nav-link:hover,
.navbar-dark .nav-link.active {
    color: var(--color-dorado);
}

.navbar-dark .dropdown-menu {
    background-color: #2d3748;
    border: none;
}

.navbar-dark .dropdown-item {
    color: rgba(255, 255, 255, 0.8);
    transition: all 0.3s ease;
}

.navbar-dark .dropdown-item:hover {
    background-color: rgba(212, 175, 55, 0.1);
    color: var(--color-dorado);
}

.dropdown-divider {
    border-color: rgba(255, 255, 255, 0.1);
}

.dropdown-header {
    color: var(--color-dorado);
    font-weight: bold;
}

/* Badge de notificaciones */
.badge.rounded-pill {
    position: absolute;
    top: 5px;
    right: 5px;
    font-size: 0.65rem;
}
</style>