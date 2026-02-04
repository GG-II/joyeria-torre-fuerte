<?php
/**
 * ================================================
 * MÓDULO INVENTARIO - TRANSFERENCIAS
 * ================================================
 * 
 * TODO FASE 5: Conectar con APIs
 * GET /api/inventario/transferencias/lista.php - Listar transferencias
 * GET /api/inventario/productos.php - Listar productos para select
 * GET /api/inventario/stock.php?producto_id={id} - Obtener stock actual
 * POST /api/inventario/transferencias/crear.php - Crear transferencia
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();
requiere_rol(['administrador', 'dueño', 'gerente']);

$titulo_pagina = 'Transferencias de Inventario';
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="container-fluid main-content">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>dashboard.php"><i class="bi bi-house"></i> Dashboard</a></li>
            <li class="breadcrumb-item"><a href="lista.php"><i class="bi bi-box-seam"></i> Inventario</a></li>
            <li class="breadcrumb-item active">Transferencias</li>
        </ol>
    </nav>

    <div class="page-header mb-4">
        <div class="row align-items-center g-3">
            <div class="col-md-6">
                <h1 class="mb-2"><i class="bi bi-arrow-left-right"></i> Transferencias de Inventario</h1>
                <p class="text-muted mb-0">Movimientos de stock entre sucursales</p>
            </div>
            <div class="col-md-6 text-md-end">
                <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#modalNuevaTransferencia">
                    <i class="bi bi-plus-circle"></i> <span class="d-none d-sm-inline">Nueva Transferencia</span>
                </button>
            </div>
        </div>
    </div>

    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Fecha Desde</label>
                    <input type="date" class="form-control" id="fechaDesde">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha Hasta</label>
                    <input type="date" class="form-control" id="fechaHasta">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sucursal Origen</label>
                    <select class="form-select" id="filterOrigen">
                        <option value="">Todas</option>
                        <option value="1">Los Arcos</option>
                        <option value="2">Chinaca Central</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label d-none d-md-block">&nbsp;</label>
                    <button class="btn btn-secondary w-100" onclick="aplicarFiltros()">
                        <i class="bi bi-funnel"></i> <span class="d-md-none">Filtrar</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header" style="background-color: #1e3a8a; color: white;">
            <i class="bi bi-table"></i> <span id="tituloTabla">Historial de Transferencias</span>
        </div>
        
        <div id="loadingTable" class="text-center py-5">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
            <p class="mt-3 text-muted">Cargando transferencias...</p>
        </div>

        <div id="tableContainer" class="table-responsive" style="display: none;">
            <table class="table table-hover mb-0">
                <thead style="background-color: #1e3a8a; color: white;">
                    <tr>
                        <th class="d-none d-xl-table-cell">#</th>
                        <th>Fecha</th>
                        <th>Producto</th>
                        <th>Cant.</th>
                        <th class="d-none d-md-table-cell">Origen</th>
                        <th class="d-none d-md-table-cell">Destino</th>
                        <th class="d-none d-lg-table-cell">Usuario</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="transferenciasBody"></tbody>
            </table>
        </div>

        <div id="noResults" class="text-center py-5" style="display: none;">
            <i class="bi bi-inbox" style="font-size: 48px; opacity: 0.3;"></i>
            <p class="mt-3 text-muted">No se encontraron transferencias</p>
        </div>

        <div class="card-footer" id="tableFooter" style="display: none;">
            <small class="text-muted" id="contadorTransferencias">Mostrando 0 transferencias</small>
        </div>
    </div>
</div>

<div class="modal fade" id="modalNuevaTransferencia" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #1e3a8a; color: white;">
                <h5 class="modal-title"><i class="bi bi-arrow-left-right"></i> Nueva Transferencia</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formTransferencia">
                    <div class="mb-3">
                        <label for="producto_id" class="form-label"><i class="bi bi-box-seam"></i> Producto *</label>
                        <select class="form-select" id="producto_id" name="producto_id" required>
                            <option value="">Seleccione un producto...</option>
                        </select>
                    </div>

                    <div class="alert alert-info" id="infoStock" style="display: none;">
                        <strong>Stock Disponible:</strong>
                        <div class="row mt-2">
                            <div class="col-6"><i class="bi bi-building"></i> Los Arcos: <span id="stock-los-arcos" class="fw-bold">0</span></div>
                            <div class="col-6"><i class="bi bi-building"></i> Chinaca: <span id="stock-chinaca" class="fw-bold">0</span></div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="sucursal_origen" class="form-label"><i class="bi bi-arrow-right"></i> Sucursal Origen *</label>
                            <select class="form-select" id="sucursal_origen" name="sucursal_origen" required>
                                <option value="">Seleccione...</option>
                                <option value="1">Los Arcos</option>
                                <option value="2">Chinaca Central</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="sucursal_destino" class="form-label"><i class="bi bi-arrow-left"></i> Sucursal Destino *</label>
                            <select class="form-select" id="sucursal_destino" name="sucursal_destino" required>
                                <option value="">Seleccione...</option>
                                <option value="1">Los Arcos</option>
                                <option value="2">Chinaca Central</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="cantidad" class="form-label"><i class="bi bi-123"></i> Cantidad a Transferir *</label>
                        <input type="number" class="form-control" id="cantidad" name="cantidad" min="1" required placeholder="Ingrese la cantidad">
                    </div>

                    <div class="mb-3">
                        <label for="notas" class="form-label"><i class="bi bi-chat-left-text"></i> Notas (opcional)</label>
                        <textarea class="form-control" id="notas" name="notas" rows="2" placeholder="Motivo o comentarios"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle"></i> Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnTransferir" onclick="procesarTransferencia()">
                    <i class="bi bi-arrow-left-right"></i> Realizar Transferencia
                </button>
            </div>
        </div>
    </div>
</div>


<style>
.main-content { padding: 20px; min-height: calc(100vh - 120px); }
.page-header h1 { font-size: 1.75rem; font-weight: 600; color: #1a1a1a; }
.shadow-sm { box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08) !important; }
table thead th { font-weight: 600; font-size: 0.85rem; text-transform: uppercase; padding: 12px; }
table tbody td { padding: 12px; vertical-align: middle; }
.badge { padding: 0.35em 0.65em; font-size: 0.85em; }
@media (max-width: 575.98px) {
    .main-content { padding: 15px 10px; }
    .page-header h1 { font-size: 1.5rem; }
    table { font-size: 0.85rem; }
    table thead th, table tbody td { padding: 8px 6px; }
}
@media (min-width: 576px) and (max-width: 767.98px) { .main-content { padding: 18px 15px; } }
@media (min-width: 992px) { .main-content { padding: 25px 30px; } }
@media (max-width: 767.98px) { .btn, .form-control, .form-select { min-height: 44px; } }
</style>

<!-- Scripts específicos del módulo -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?php echo BASE_URL; ?>assets/js/api-helper.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/inventario.js"></script>

<script>
// Inicializar módulo de transferencias
document.addEventListener('DOMContentLoaded', function() {
    Inventario.transferencias.init();
});

// Función global para el botón del modal
function procesarTransferencia() {
    const form = document.getElementById('formTransferencia');
    const event = new Event('submit', { cancelable: true });
    form.dispatchEvent(event);
}
</script>

<?php include '../../includes/footer.php'; ?>
