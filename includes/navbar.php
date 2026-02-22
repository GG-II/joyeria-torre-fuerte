<!-- Navbar Principal -->
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
        <!-- Logo y Nombre -->
        <a class="navbar-brand" href="<?php echo BASE_URL; ?>dashboard.php">
            <?php if (file_exists(__DIR__ . '/../assets/img/logo-torre-fuerte.png')): ?>
                <img src="<?php echo BASE_URL; ?>assets/img/logo-torre-fuerte.png" alt="Logo Torre Fuerte">
            <?php else: ?>
                <i class="bi bi-gem"></i>
            <?php endif; ?>
            <span><?php echo SISTEMA_NOMBRE; ?></span>
        </a>
        
        <!-- Botón Hamburguesa (Móvil) -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Menú de Navegación -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <?php
                // Página actual para marcar activo
                $pagina_actual = basename($_SERVER['PHP_SELF']);
                $directorio_actual = basename(dirname($_SERVER['PHP_SELF']));
                
                // Función helper para determinar si un menú está activo
                function menu_activo($modulo) {
                    global $directorio_actual;
                    return $directorio_actual === $modulo ? 'active' : '';
                }
                ?>
                
                <!-- Dashboard -->
                <li class="nav-item">
                    <a class="nav-link <?php echo ($pagina_actual === 'dashboard.php') ? 'active' : ''; ?>" 
                       href="<?php echo BASE_URL; ?>dashboard.php">
                        <i class="bi bi-speedometer2"></i>
                        Dashboard
                    </a>
                </li>
                
                <!-- Ventas -->
                <?php if (tiene_permiso('ventas', 'ver')): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo menu_activo('ventas'); ?>" 
                       href="#" role="button" data-bs-toggle="dropdown">
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
                    <a class="nav-link dropdown-toggle <?php echo menu_activo('clientes'); ?>" 
                       href="#" role="button" data-bs-toggle="dropdown">
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
                    <a class="nav-link dropdown-toggle <?php echo menu_activo('inventario'); ?>" 
                       href="#" role="button" data-bs-toggle="dropdown">
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
                    <a class="nav-link dropdown-toggle <?php echo menu_activo('taller'); ?>" 
                       href="#" role="button" data-bs-toggle="dropdown">
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
                    <a class="nav-link dropdown-toggle <?php echo menu_activo('caja'); ?>" 
                       href="#" role="button" data-bs-toggle="dropdown">
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
                    </ul>
                </li>
                <?php endif; ?>
                
                <!-- Proveedores -->
                <?php if (tiene_permiso('proveedores', 'ver')): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo menu_activo('proveedores'); ?>" 
                       href="#" role="button" data-bs-toggle="dropdown">
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
                    <a class="nav-link dropdown-toggle <?php echo menu_activo('reportes'); ?>" 
                       href="#" role="button" data-bs-toggle="dropdown">
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
                        <?php if (in_array($_SESSION['usuario_rol'] ?? '', ['administrador', 'dueño'])): ?>
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
                    <a class="nav-link dropdown-toggle <?php echo menu_activo('configuracion'); ?>" 
                       href="#" role="button" data-bs-toggle="dropdown">
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
            
            <!-- Usuario y Dropdown -->
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" 
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="user-info d-inline-flex align-items-center">
                            <div class="user-avatar">
                                <?php echo strtoupper(substr(usuario_actual_nombre(), 0, 1)); ?>
                            </div>
                            <div class="d-none d-lg-block ms-2">
                                <div class="user-name"><?php echo explode(' ', usuario_actual_nombre())[0]; ?></div>
                                <div class="user-role"><?php echo ucfirst(usuario_actual_rol()); ?></div>
                            </div>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li>
                            <a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/perfil/perfil.php">
                                <i class="bi bi-person-circle"></i>
                                Mi Perfil
                            </a>
                        </li>
                        <?php if (tiene_permiso('configuracion', 'ver')): ?>
                        <li>
                            <a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/configuracion/sistema.php">
                                <i class="bi bi-gear"></i>
                                Configuración
                            </a>
                        </li>
                        <?php endif; ?>
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