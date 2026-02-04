<?php
/**
 * ================================================
 * MÓDULO INVENTARIO - LISTA
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

$titulo_pagina = 'Inventario';
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="container-fluid main-content">
    <div class="page-header mb-4">
        <div class="row align-items-center g-3">
            <div class="col-md-6">
                <h1 class="mb-2"><i class="bi bi-box-seam"></i> Inventario de Productos</h1>
                <p class="text-muted mb-0">Control de stock por sucursal</p>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                    <?php if (tiene_permiso('inventario', 'crear')): ?>
                    <a href="agregar.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> <span class="d-none d-sm-inline">Nuevo</span>
                    </a>
                    <a href="transferir.php" class="btn btn-warning">
                        <i class="bi bi-arrow-left-right"></i> <span class="d-none d-sm-inline">Transferencias</span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4" id="estadisticas">
        <div class="col-6 col-md-3">
            <div class="stat-card azul">
                <div class="stat-icon"><i class="bi bi-boxes"></i></div>
                <div class="stat-value" id="statTotal">0</div>
                <div class="stat-label">Productos Totales</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card verde">
                <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
                <div class="stat-value" id="statDisponibles">0</div>
                <div class="stat-label">Disponibles</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card amarillo">
                <div class="stat-icon"><i class="bi bi-exclamation-triangle"></i></div>
                <div class="stat-value" id="statBajoStock">0</div>
                <div class="stat-label">Stock Bajo</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card rojo">
                <div class="stat-icon"><i class="bi bi-x-circle"></i></div>
                <div class="stat-value" id="statAgotados">0</div>
                <div class="stat-label">Agotados</div>
            </div>
        </div>
    </div>

    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Buscar Producto</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" id="searchInput" placeholder="Código, nombre...">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Categoría</label>
                    <select class="form-select" id="filterCategoria">
                        <option value="">Todas</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Estado</label>
                    <select class="form-select" id="filterEstado">
                        <option value="">Todos</option>
                        <option value="disponible">Disponible</option>
                        <option value="bajo_stock">Stock Bajo</option>
                        <option value="agotado">Agotado</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sucursal</label>
                    <select class="form-select" id="filterSucursal">
                        <option value="">Todas</option>
                        <option value="1">Los Arcos</option>
                        <option value="2">Chinaca Central</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label d-none d-md-block">&nbsp;</label>
                    <button class="btn btn-secondary w-100" onclick="limpiarFiltros()">
                        <i class="bi bi-x-circle"></i> <span class="d-md-none">Limpiar</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header" style="background-color: #1e3a8a; color: white;">
            <i class="bi bi-table"></i> <span id="tituloTabla">Listado de Productos</span>
        </div>
        
        <div id="loadingTable" class="text-center py-5">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
            <p class="mt-3 text-muted">Cargando inventario...</p>
        </div>

        <div id="tableContainer" class="table-responsive" style="display: none;">
            <table class="table table-hover mb-0">
                <thead style="background-color: #1e3a8a; color: white;">
                    <tr>
                        <th>Código</th>
                        <th>Producto</th>
                        <th class="d-none d-lg-table-cell">Categoría</th>
                        <th class="d-none d-md-table-cell">Precio</th>
                        <th class="text-center d-none d-xl-table-cell">Los Arcos</th>
                        <th class="text-center d-none d-xl-table-cell">Chinaca</th>
                        <th class="text-center">Total</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="productosBody"></tbody>
            </table>
        </div>

        <div id="noResults" class="text-center py-5" style="display: none;">
            <i class="bi bi-inbox" style="font-size: 48px; opacity: 0.3;"></i>
            <p class="mt-3 text-muted">No se encontraron productos</p>
        </div>

        <div class="card-footer" id="tableFooter" style="display: none;">
            <div class="row align-items-center g-2">
                <div class="col-md-6">
                    <small class="text-muted" id="contadorProductos">Mostrando 0 productos</small>
                </div>
                <div class="col-md-6 text-md-end">
                    <button class="btn btn-sm btn-secondary" onclick="Inventario.exportarExcel()">
                        <i class="bi bi-file-earmark-excel"></i> Exportar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.main-content { padding: 20px; min-height: calc(100vh - 120px); }
.page-header h1 { font-size: 1.75rem; font-weight: 600; color: #1a1a1a; }
.shadow-sm { box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08) !important; }
.stat-card { background: white; border-radius: 12px; padding: 15px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08); transition: transform 0.2s ease; border-left: 4px solid; height: 100%; }
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12); }
.stat-card.azul { border-left-color: #1e3a8a; }
.stat-card.verde { border-left-color: #22c55e; }
.stat-card.amarillo { border-left-color: #f59e0b; }
.stat-card.rojo { border-left-color: #ef4444; }
.stat-icon { font-size: 2rem; color: #6b7280; margin-bottom: 8px; }
.stat-value { font-size: 2rem; font-weight: 700; color: #1a1a1a; margin-bottom: 4px; }
.stat-label { font-size: 0.875rem; color: #6b7280; font-weight: 500; }
.card { border: 1px solid #e5e7eb; border-radius: 12px; }
.card-header { font-weight: 600; border-radius: 12px 12px 0 0 !important; }
.table { margin-bottom: 0; }
.table thead th { border-bottom: 2px solid #1e3a8a; font-weight: 600; font-size: 0.9rem; padding: 12px; }
.table tbody td { padding: 12px; vertical-align: middle; font-size: 0.9rem; }
.table-hover tbody tr:hover { background-color: #f8fafc; }
.btn-group .btn { padding: 0.375rem 0.5rem; }
.form-label { font-weight: 500; color: #374151; margin-bottom: 0.5rem; }
.form-control, .form-select { border-radius: 8px; border: 1px solid #d1d5db; }
.form-control:focus, .form-select:focus { border-color: #1e3a8a; box-shadow: 0 0 0 0.2rem rgba(30, 58, 138, 0.15); }
.badge { padding: 0.35em 0.65em; font-weight: 500; }
@media (max-width: 767.98px) { .btn, .form-control, .form-select { min-height: 44px; } }
</style>

<!-- Scripts específicos del módulo -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/api-helper.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/inventario.js"></script>

<script>
// Inicializar módulo al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    // Configurar permisos
    Inventario.state.puedeEditar = <?php echo tiene_permiso('inventario', 'editar') ? 'true' : 'false'; ?>;
    Inventario.state.puedeEliminar = <?php echo tiene_permiso('inventario', 'eliminar') ? 'true' : 'false'; ?>;
    
    // Cargar inventario
    Inventario.lista.cargar();
    
    // Cargar categorías en filtro
    Inventario.categorias.cargarFiltro();
    
    // Event listeners para filtros con debounce en búsqueda
    document.getElementById('searchInput').addEventListener('input', debounce(aplicarFiltros, 500));
    document.getElementById('filterCategoria').addEventListener('change', aplicarFiltros);
    document.getElementById('filterEstado').addEventListener('change', aplicarFiltros);
    document.getElementById('filterSucursal').addEventListener('change', aplicarFiltros);
});
</script>

<?php include '../../includes/footer.php'; ?>
