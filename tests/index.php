<?php
// ================================================
// ÍNDICE DE TESTS
// Sistema de Gestión - Joyería Torre Fuerte
// ================================================

session_start();

// Simular usuario autenticado
$_SESSION['usuario_id'] = 1;
$_SESSION['usuario_nombre'] = 'Administrador Test';
$_SESSION['usuario_rol'] = 'administrador';
$_SESSION['usuario_sucursal_id'] = 1;

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tests - Sistema Joyería Torre Fuerte</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
        }
        .test-card {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .test-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.2);
        }
        .icon-box {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 1rem;
        }
        .bg-gold { background-color: #D4AF37; color: white; }
        .bg-blue { background-color: #1e3a8a; color: white; }
        .bg-silver { background-color: #C0C0C0; color: #1a1a1a; }
        .bg-green { background-color: #10b981; color: white; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row mb-5">
            <div class="col-12 text-center text-white">
                <h1 class="display-4 fw-bold mb-3">
                    🧪 Sistema de Tests
                </h1>
                <p class="lead">Joyería Torre Fuerte - Pruebas de Modelos</p>
                <hr class="border-white">
            </div>
        </div>

        <div class="row g-4 mb-4">
            <!-- Test Producto -->
            <div class="col-md-6 col-lg-3">
                <div class="card test-card h-100">
                    <div class="card-body text-center">
                        <div class="icon-box bg-gold">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <h5 class="card-title">Productos</h5>
                        <p class="card-text text-muted">
                            Pruebas del modelo de productos con múltiples precios
                        </p>
                        <a href="test-producto.php" class="btn btn-warning btn-sm w-100">
                            <i class="bi bi-play-fill"></i> Ejecutar Test
                        </a>
                    </div>
                </div>
            </div>

            <!-- Test Categoría -->
            <div class="col-md-6 col-lg-3">
                <div class="card test-card h-100">
                    <div class="card-body text-center">
                        <div class="icon-box bg-blue">
                            <i class="bi bi-folder"></i>
                        </div>
                        <h5 class="card-title">Categorías</h5>
                        <p class="card-text text-muted">
                            Pruebas del modelo de categorías jerárquicas
                        </p>
                        <a href="test-categoria.php" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-play-fill"></i> Ejecutar Test
                        </a>
                    </div>
                </div>
            </div>

            <!-- Test Inventario -->
            <div class="col-md-6 col-lg-3">
                <div class="card test-card h-100">
                    <div class="card-body text-center">
                        <div class="icon-box bg-green">
                            <i class="bi bi-boxes"></i>
                        </div>
                        <h5 class="card-title">Inventario</h5>
                        <p class="card-text text-muted">
                            Pruebas de stock, movimientos y transferencias
                        </p>
                        <a href="test-inventario.php" class="btn btn-success btn-sm w-100">
                            <i class="bi bi-play-fill"></i> Ejecutar Test
                        </a>
                    </div>
                </div>
            </div>

            <!-- Test Materia Prima -->
            <div class="col-md-6 col-lg-3">
                <div class="card test-card h-100">
                    <div class="card-body text-center">
                        <div class="icon-box bg-silver">
                            <i class="bi bi-gem"></i>
                        </div>
                        <h5 class="card-title">Materias Primas</h5>
                        <p class="card-text text-muted">
                            Pruebas de oro, plata y piedras (sin código)
                        </p>
                        <a href="test-materia-prima.php" class="btn btn-secondary btn-sm w-100">
                            <i class="bi bi-play-fill"></i> Ejecutar Test
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="bi bi-info-circle"></i> Información de Tests</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="bi bi-check-circle text-success"></i> Tests Incluidos:</h6>
                                <ul>
                                    <li><strong>Producto:</strong> Crear, actualizar, listar, buscar, precios, eliminar</li>
                                    <li><strong>Categoría:</strong> Crear, actualizar, listar por tipo, árbol jerárquico</li>
                                    <li><strong>Inventario:</strong> Stock, movimientos, transferencias, historial</li>
                                    <li><strong>Materia Prima:</strong> Crear sin código, incrementar/decrementar cantidad</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="bi bi-shield-check text-primary"></i> Verificaciones:</h6>
                                <ul>
                                    <li>✅ Transacciones SQL funcionando</li>
                                    <li>✅ Auditoría registrada correctamente</li>
                                    <li>✅ Validaciones de datos</li>
                                    <li>✅ Soft delete implementado</li>
                                    <li>✅ Funciones helper utilizadas</li>
                                </ul>
                            </div>
                        </div>

                        <div class="alert alert-info mt-3 mb-0">
                            <strong><i class="bi bi-lightbulb"></i> Nota:</strong> 
                            Los tests crean datos de prueba en la base de datos. 
                            Se utiliza soft delete, por lo que los registros de prueba pueden ser reactivados si es necesario.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones -->
        <div class="row mt-4">
            <div class="col-12 text-center">
                <a href="../dashboard.php" class="btn btn-light btn-lg">
                    <i class="bi bi-arrow-left"></i> Volver al Dashboard
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="row mt-5">
            <div class="col-12 text-center text-white">
                <p class="mb-0">
                    <small>
                        Sistema de Gestión - Joyería Torre Fuerte v1.0<br>
                        Tests ejecutados en: <?php echo date('d/m/Y H:i:s'); ?>
                    </small>
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
