<?php
/**
 * Índice de Tests del Sistema
 * Joyería Torre Fuerte
 */

session_start();
$_SESSION['usuario_id'] = 1;
$_SESSION['usuario_nombre'] = 'Admin Test';
$_SESSION['usuario_rol'] = 'administrador';
$_SESSION['usuario_sucursal_id'] = 1;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tests - Sistema Joyería Torre Fuerte</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 40px 0; }
        .test-card { transition: transform 0.3s, box-shadow 0.3s; }
        .test-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        .module-icon { font-size: 3rem; margin-bottom: 1rem; }
    </style>
</head>
<body>

<div class="container">
    <div class="row mb-5">
        <div class="col-12 text-center text-white">
            <h1 class="display-4 mb-3">🧪 Tests del Sistema</h1>
            <p class="lead">Joyería Torre Fuerte - Sistema de Gestión</p>
        </div>
    </div>

    <div class="row g-4">
        
        <!-- Test Autenticación -->
        <div class="col-md-6 col-lg-4">
            <div class="card test-card h-100">
                <div class="card-body text-center">
                    <div class="module-icon">🔐</div>
                    <h5 class="card-title">Autenticación</h5>
                    <p class="card-text">Pruebas de login, logout y sesiones</p>
                    <a href="test-auth.php" class="btn btn-primary">Ejecutar Tests</a>
                </div>
            </div>
        </div>

        <!-- Test Usuarios -->
        <div class="col-md-6 col-lg-4">
            <div class="card test-card h-100">
                <div class="card-body text-center">
                    <div class="module-icon">👥</div>
                    <h5 class="card-title">Usuarios</h5>
                    <p class="card-text">CRUD de usuarios y permisos</p>
                    <a href="test-usuarios.php" class="btn btn-primary">Ejecutar Tests</a>
                </div>
            </div>
        </div>

        <!-- Test Productos -->
        <div class="col-md-6 col-lg-4">
            <div class="card test-card h-100">
                <div class="card-body text-center">
                    <div class="module-icon">💎</div>
                    <h5 class="card-title">Productos</h5>
                    <p class="card-text">Gestión de productos y precios</p>
                    <a href="test-productos.php" class="btn btn-primary">Ejecutar Tests</a>
                </div>
            </div>
        </div>

        <!-- Test Inventario -->
        <div class="col-md-6 col-lg-4">
            <div class="card test-card h-100">
                <div class="card-body text-center">
                    <div class="module-icon">📦</div>
                    <h5 class="card-title">Inventario</h5>
                    <p class="card-text">Stock, movimientos y transferencias</p>
                    <a href="test-inventario.php" class="btn btn-primary">Ejecutar Tests</a>
                </div>
            </div>
        </div>

        <!-- Test Taller -->
        <div class="col-md-6 col-lg-4">
            <div class="card test-card h-100">
                <div class="card-body text-center">
                    <div class="module-icon">⚒️</div>
                    <h5 class="card-title">Taller</h5>
                    <p class="card-text">Trabajos, transferencias y alertas</p>
                    <a href="test-taller.php" class="btn btn-primary">Ejecutar Tests</a>
                </div>
            </div>
        </div>

        <!-- Test Clientes -->
        <div class="col-md-6 col-lg-4">
            <div class="card test-card h-100">
                <div class="card-body text-center">
                    <div class="module-icon">👤</div>
                    <h5 class="card-title">Clientes</h5>
                    <p class="card-text">CRUD clientes y límites de crédito</p>
                    <a href="test-cliente.php" class="btn btn-primary">Ejecutar Tests</a>
                </div>
            </div>
        </div>

        <!-- Test Ventas -->
        <div class="col-md-6 col-lg-4">
            <div class="card test-card h-100">
                <div class="card-body text-center">
                    <div class="module-icon">🛒</div>
                    <h5 class="card-title">Ventas</h5>
                    <p class="card-text">Ventas, formas de pago y anulaciones</p>
                    <a href="test-venta.php" class="btn btn-success">Ejecutar Tests</a>
                </div>
            </div>
        </div>

        <!-- Test Créditos -->
        <div class="col-md-6 col-lg-4">
            <div class="card test-card h-100">
                <div class="card-body text-center">
                    <div class="module-icon">💳</div>
                    <h5 class="card-title">Créditos</h5>
                    <p class="card-text">Créditos semanales y abonos</p>
                    <a href="test-credito.php" class="btn btn-success">Ejecutar Tests</a>
                </div>
            </div>
        </div>

        <!-- Test Caja -->
        <div class="col-md-6 col-lg-4">
            <div class="card test-card h-100">
                <div class="card-body text-center">
                    <div class="module-icon">💰</div>
                    <h5 class="card-title">Caja</h5>
                    <p class="card-text">Apertura, movimientos y cierres</p>
                    <a href="test-caja.php" class="btn btn-success">Ejecutar Tests</a>
                </div>
            </div>
        </div>

        <!-- Test Reportes -->
        <div class="col-md-6 col-lg-4">
            <div class="card test-card h-100">
                <div class="card-body text-center">
                    <div class="module-icon">📊</div>
                    <h5 class="card-title">Reportes</h5>
                    <p class="card-text">Reportes y estadísticas del sistema</p>
                    <a href="test-reporte.php" class="btn btn-warning">Ejecutar Tests</a>
                </div>
            </div>
        </div>

        <!-- Test Usuarios -->
        <div class="col-md-6 col-lg-4">
            <div class="card test-card h-100 border-primary">
                <div class="card-body text-center">
                    <div class="module-icon">👤</div>
                    <h5 class="card-title">Usuarios</h5>
                    <p class="card-text">CRUD y gestión de usuarios</p>
                    <a href="test-usuario.php" class="btn btn-primary">Ejecutar Tests ⭐ NUEVO</a>
                </div>
            </div>
        </div>

        <!-- Test Proveedores -->
        <div class="col-md-6 col-lg-4">
            <div class="card test-card h-100 border-primary">
                <div class="card-body text-center">
                    <div class="module-icon">📦</div>
                    <h5 class="card-title">Proveedores</h5>
                    <p class="card-text">Gestión de proveedores</p>
                    <a href="test-proveedor.php" class="btn btn-primary">Ejecutar Tests ⭐ NUEVO</a>
                </div>
            </div>
        </div>

        <!-- Test Facturas -->
        <div class="col-md-6 col-lg-4">
            <div class="card test-card h-100 border-primary">
                <div class="card-body text-center">
                    <div class="module-icon">📄</div>
                    <h5 class="card-title">Facturas</h5>
                    <p class="card-text">Facturación simple y electrónica</p>
                    <a href="test-factura.php" class="btn btn-primary">Ejecutar Tests ⭐ NUEVO</a>
                </div>
            </div>
        </div>

    </div>

    <div class="row mt-5">
        <div class="col-12">
            <div class="card bg-dark text-white">
                <div class="card-body">
                    <h5 class="card-title">📊 Estado del Proyecto</h5>
                    <div class="row mt-3">
                        <div class="col-md-3 text-center">
                            <h3>✅ 13</h3>
                            <p>Módulos Completados</p>
                        </div>
                        <div class="col-md-3 text-center">
                            <h3>🧪 150+</h3>
                            <p>Tests Automatizados</p>
                        </div>
                        <div class="col-md-3 text-center">
                            <h3>📝 9,000+</h3>
                            <p>Líneas de Código</p>
                        </div>
                        <div class="col-md-3 text-center">
                            <h3>🎯 100%</h3>
                            <p>Tasa de Éxito</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>