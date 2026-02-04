<?php
/**
 * ================================================
 * MÓDULO INVENTARIO - VER DETALLES PRODUCTO
 * ================================================
 * 
 * Vista actualizada - FASE 5.1 COMPLETADA
 * Conectada con API mediante api-helper.js e inventario.js
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();

$producto_id = $_GET['id'] ?? null;
if (!$producto_id) {
    header('Location: lista.php');
    exit;
}

$producto = null;
$titulo_pagina = 'Detalles del Producto';
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="container-fluid main-content">
    <div id="loadingState" class="text-center py-5">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
        <p class="mt-3 text-muted">Cargando detalles del producto...</p>
    </div>

    <div id="mainContent" style="display: none;">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>dashboard.php"><i class="bi bi-house"></i> Dashboard</a></li>
                <li class="breadcrumb-item"><a href="lista.php"><i class="bi bi-box-seam"></i> Inventario</a></li>
                <li class="breadcrumb-item active" id="breadcrumbCodigo">-</li>
            </ol>
        </nav>

        <div class="card mb-4 shadow-sm" style="border-left: 5px solid #d4af37;">
            <div class="card-body">
                <div class="row align-items-center g-3">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon bg-primary text-white me-3 d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; border-radius: 12px;">
                                <i class="bi bi-gem" style="font-size: 28px;"></i>
                            </div>
                            <div>
                                <h2 class="mb-2" id="productoNombre">-</h2>
                                <div class="d-flex flex-wrap gap-3 text-muted">
                                    <span id="productoCodigo"><i class="bi bi-upc-scan"></i> -</span>
                                    <span id="productoCategoria"><i class="bi bi-folder"></i> -</span>
                                    <span id="productoEstado"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                            <button class="btn btn-success" onclick="Inventario.ver.descargarCodigoBarras()">
                                <i class="bi bi-upc-scan"></i> <span class="d-none d-sm-inline">Código de Barras</span>
                            </button>
                            <?php if (tiene_permiso('inventario', 'editar')): ?>
                            <a href="editar.php?id=<?php echo $producto_id; ?>" class="btn btn-warning">
                                <i class="bi bi-pencil"></i> <span class="d-none d-sm-inline">Editar</span>
                            </a>
                            <?php endif; ?>
                            <a href="lista.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> <span class="d-none d-sm-inline">Volver</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card mb-3 shadow-sm">
                    <div class="card-header" style="background-color: #1e3a8a; color: white;">
                        <i class="bi bi-info-circle"></i> Información del Producto
                    </div>
                    <div class="card-body" id="infoProducto">
                        <div class="text-center py-3"><div class="spinner-border spinner-border-sm"></div></div>
                    </div>
                </div>

                <div class="card mb-3 shadow-sm">
                    <div class="card-header bg-success text-white">
                        <i class="bi bi-cash-coin"></i> Precios
                    </div>
                    <div class="card-body" id="infoPrecio">
                        <div class="text-center py-3"><div class="spinner-border spinner-border-sm"></div></div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header" style="background-color: #1e3a8a; color: white;">
                        <i class="bi bi-building"></i> Stock por Sucursal
                    </div>
                    <div class="card-body" id="infoStock">
                        <div class="text-center py-3"><div class="spinner-border spinner-border-sm"></div></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="row g-3 mb-4" id="estadisticas">
                    <div class="col-6 col-md-3">
                        <div class="stat-card azul">
                            <div class="stat-icon"><i class="bi bi-boxes"></i></div>
                            <div class="stat-value" id="statStockTotal">0</div>
                            <div class="stat-label">Stock Total</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-card verde">
                            <div class="stat-icon"><i class="bi bi-cash-stack"></i></div>
                            <div class="stat-value" id="statValorTotal">Q 0</div>
                            <div class="stat-label">Valor Total</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-card amarillo">
                            <div class="stat-icon"><i class="bi bi-exclamation-triangle"></i></div>
                            <div class="stat-value" id="statStockMin">0</div>
                            <div class="stat-label">Stock Mínimo</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-card dorado">
                            <div class="stat-icon"><i class="bi bi-graph-up"></i></div>
                            <div class="stat-value" id="statGanancia">Q 0</div>
                            <div class="stat-label">Ganancia/Unidad</div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header">
                        <i class="bi bi-clock-history"></i> <span id="tituloMovimientos">Historial de Movimientos</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background-color: #1e3a8a; color: white;">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Cantidad</th>
                                    <th class="d-none d-md-table-cell">Sucursal</th>
                                    <th class="d-none d-lg-table-cell">Motivo</th>
                                    <th class="d-none d-xl-table-cell">Usuario</th>
                                </tr>
                            </thead>
                            <tbody id="movimientosBody">
                                <tr><td colspan="6" class="text-center py-4"><div class="spinner-border spinner-border-sm"></div></td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer" id="movimientosFooter" style="display: none;">
                        <small class="text-muted" id="contadorMovimientos">Mostrando 0 movimientos</small>
                    </div>
                </div>

                <?php if (tiene_permiso('inventario', 'editar')): ?>
                <div class="card mt-3 shadow-sm">
                    <div class="card-header"><i class="bi bi-gear"></i> Acciones Disponibles</div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <a href="transferir.php?producto_id=<?php echo $producto_id; ?>" class="btn btn-warning w-100">
                                    <i class="bi bi-arrow-left-right"></i> Transferir Stock
                                </a>
                            </div>
                            <div class="col-md-4">
                                <button class="btn btn-success w-100" onclick="entradaStock()">
                                    <i class="bi bi-plus-circle"></i> Entrada de Stock
                                </button>
                            </div>
                            <div class="col-md-4">
                                <button class="btn btn-info w-100" onclick="ajusteInventario()">
                                    <i class="bi bi-arrow-repeat"></i> Ajuste
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div id="errorState" style="display: none;" class="text-center py-5">
        <i class="bi bi-exclamation-triangle text-danger" style="font-size: 48px;"></i>
        <h4 class="mt-3">Error al cargar el producto</h4>
        <p class="text-muted" id="errorMessage">No se pudo cargar la información.</p>
        <a href="lista.php" class="btn btn-primary mt-3"><i class="bi bi-arrow-left"></i> Volver al listado</a>
    </div>
</div>

<style>
.main-content { padding: 20px; min-height: calc(100vh - 120px); }
.shadow-sm { box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08) !important; }
.stat-card { background: white; border-radius: 12px; padding: 15px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08); transition: transform 0.2s ease; border-left: 4px solid; height: 100%; }
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12); }
.stat-card.azul { border-left-color: #1e3a8a; }
.stat-card.verde { border-left-color: #22c55e; }
.stat-card.amarillo { border-left-color: #eab308; }
.stat-card.dorado { border-left-color: #d4af37; }
.stat-icon { width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 22px; margin-bottom: 12px; }
.stat-card.azul .stat-icon { background: rgba(30, 58, 138, 0.1); color: #1e3a8a; }
.stat-card.verde .stat-icon { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
.stat-card.amarillo .stat-icon { background: rgba(234, 179, 8, 0.1); color: #eab308; }
.stat-card.dorado .stat-icon { background: rgba(212, 175, 55, 0.1); color: #d4af37; }
.stat-value { font-size: 1.5rem; font-weight: 700; color: #1a1a1a; margin: 8px 0; }
.stat-label { font-size: 0.8rem; color: #6b7280; font-weight: 500; }
.card-body > div:not(:last-child) { padding-bottom: 12px; margin-bottom: 12px; border-bottom: 1px solid #e5e7eb; }
table thead th { font-weight: 600; font-size: 0.85rem; text-transform: uppercase; padding: 12px; }
table tbody td { padding: 12px; vertical-align: middle; }
@media (max-width: 575.98px) {
    .main-content { padding: 15px 10px; }
    h2 { font-size: 1.5rem; }
    .stat-card { padding: 12px; }
    .stat-icon { width: 38px; height: 38px; font-size: 18px; margin-bottom: 8px; }
    .stat-value { font-size: 1.25rem; }
    .stat-label { font-size: 0.75rem; }
    table { font-size: 0.85rem; }
    table thead th, table tbody td { padding: 8px 6px; }
}
@media (min-width: 576px) and (max-width: 767.98px) { .main-content { padding: 18px 15px; } }
@media (min-width: 992px) { .main-content { padding: 25px 30px; } }
@media (max-width: 767.98px) { .btn { min-height: 44px; } }
</style>

<!-- Scripts específicos del módulo -->
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?php echo BASE_URL; ?>assets/js/api-helper.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/inventario.js"></script>

<!-- Canvas oculto para generar código de barras -->
<canvas id="barcodeCanvas" style="display: none;"></canvas>

<script>
// Inicializar vista de detalle
document.addEventListener('DOMContentLoaded', function() {
    const productoId = <?php echo $producto_id; ?>;
    Inventario.ver.init(productoId);
});

// Funciones de acciones
function entradaStock() {
    Inventario.ver.mostrarModalEntrada();
}

function ajusteInventario() {
    Inventario.ver.mostrarModalAjuste();
}
</script>

<?php include '../../includes/footer.php'; ?>
