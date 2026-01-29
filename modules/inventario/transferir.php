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

<script>
document.addEventListener('DOMContentLoaded', function() {
    cargarTransferencias();
    cargarProductos();
});

function cargarTransferencias() {
    /* TODO FASE 5: Descomentar
    const params = new URLSearchParams({
        fecha_desde: document.getElementById('fechaDesde').value,
        fecha_hasta: document.getElementById('fechaHasta').value,
        sucursal_origen: document.getElementById('filterOrigen').value
    });
    
    fetch('<?php echo BASE_URL; ?>api/inventario/transferencias/lista.php?' + params)
        .then(response => response.json())
        .then(data => {
            if (data.success) renderizarTransferencias(data.data);
            else mostrarError(data.message);
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarError('Error al cargar transferencias');
        });
    */
    
    setTimeout(() => {
        document.getElementById('loadingTable').style.display = 'none';
        document.getElementById('noResults').style.display = 'block';
        document.getElementById('noResults').innerHTML = '<i class="bi bi-database" style="font-size: 48px; opacity: 0.3;"></i><p class="mt-3 text-muted">MODO DESARROLLO: Esperando API</p>';
    }, 1500);
}

function cargarProductos() {
    /* TODO FASE 5: Descomentar
    fetch('<?php echo BASE_URL; ?>api/inventario/productos.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('producto_id');
                data.data.forEach(prod => {
                    const option = document.createElement('option');
                    option.value = prod.id;
                    option.textContent = prod.codigo + ' - ' + prod.nombre;
                    select.appendChild(option);
                });
            }
        });
    */
}

function renderizarTransferencias(transferencias) {
    const tbody = document.getElementById('transferenciasBody');
    
    if (transferencias.length === 0) {
        document.getElementById('loadingTable').style.display = 'none';
        document.getElementById('noResults').style.display = 'block';
        return;
    }
    
    let html = '';
    transferencias.forEach(trans => {
        html += `
            <tr>
                <td class="fw-bold d-none d-xl-table-cell">${trans.id}</td>
                <td><small>${formatearFecha(trans.fecha)}</small></td>
                <td>
                    <div class="fw-bold">${trans.producto_nombre}</div>
                    <small class="text-muted">${trans.producto_codigo}</small>
                </td>
                <td><span class="badge" style="background-color: #1e3a8a;">${trans.cantidad}</span></td>
                <td class="d-none d-md-table-cell"><span class="badge bg-primary"><i class="bi bi-building"></i> ${trans.origen}</span></td>
                <td class="d-none d-md-table-cell"><span class="badge bg-success"><i class="bi bi-building"></i> ${trans.destino}</span></td>
                <td class="d-none d-lg-table-cell"><small class="text-muted">${trans.usuario}</small></td>
                <td><span class="badge bg-success"><i class="bi bi-check-circle"></i> Completada</span></td>
                <td class="text-center">
                    <button class="btn btn-sm btn-info" onclick="verDetalle(${trans.id})" title="Ver detalles">
                        <i class="bi bi-eye"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    document.getElementById('loadingTable').style.display = 'none';
    document.getElementById('tableContainer').style.display = 'block';
    document.getElementById('tableFooter').style.display = 'block';
    document.getElementById('contadorTransferencias').textContent = `Mostrando ${transferencias.length} transferencias`;
    document.getElementById('tituloTabla').textContent = `Historial de Transferencias (${transferencias.length})`;
}

document.getElementById('producto_id').addEventListener('change', function() {
    const infoStock = document.getElementById('infoStock');
    
    if (this.value) {
        /* TODO FASE 5: Descomentar
        fetch('<?php echo BASE_URL; ?>api/inventario/stock.php?producto_id=' + this.value)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('stock-los-arcos').textContent = data.data.los_arcos;
                    document.getElementById('stock-chinaca').textContent = data.data.chinaca;
                    infoStock.style.display = 'block';
                }
            });
        */
        
        document.getElementById('stock-los-arcos').textContent = '3';
        document.getElementById('stock-chinaca').textContent = '2';
        infoStock.style.display = 'block';
    } else {
        infoStock.style.display = 'none';
    }
});

document.getElementById('sucursal_destino').addEventListener('change', function() {
    const origen = document.getElementById('sucursal_origen').value;
    if (origen && this.value && origen === this.value) {
        alert('La sucursal de origen y destino deben ser diferentes');
        this.value = '';
    }
});

function procesarTransferencia() {
    const form = document.getElementById('formTransferencia');
    
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);
    const cantidad = parseInt(formData.get('cantidad'));
    const origen = parseInt(formData.get('sucursal_origen'));
    const stockOrigen = origen === 1 
        ? parseInt(document.getElementById('stock-los-arcos').textContent)
        : parseInt(document.getElementById('stock-chinaca').textContent);
    
    if (cantidad > stockOrigen) {
        alert('La cantidad a transferir excede el stock disponible en la sucursal origen');
        return;
    }
    
    const datos = {
        producto_id: formData.get('producto_id'),
        sucursal_origen: formData.get('sucursal_origen'),
        sucursal_destino: formData.get('sucursal_destino'),
        cantidad: cantidad,
        notas: formData.get('notas') || null
    };
    
    const btnTransferir = document.getElementById('btnTransferir');
    btnTransferir.disabled = true;
    btnTransferir.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...';
    
    /* TODO FASE 5: Descomentar
    fetch('<?php echo BASE_URL; ?>api/inventario/transferencias/crear.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(datos)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Transferencia realizada exitosamente');
            bootstrap.Modal.getInstance(document.getElementById('modalNuevaTransferencia')).hide();
            form.reset();
            cargarTransferencias();
        } else {
            alert(data.message);
        }
        btnTransferir.disabled = false;
        btnTransferir.innerHTML = '<i class="bi bi-arrow-left-right"></i> Realizar Transferencia';
    });
    */
    
    console.log('Datos transferencia:', datos);
    setTimeout(() => {
        alert('MODO DESARROLLO: Transferencia lista.\n\n' + JSON.stringify(datos, null, 2));
        btnTransferir.disabled = false;
        btnTransferir.innerHTML = '<i class="bi bi-arrow-left-right"></i> Realizar Transferencia';
    }, 1000);
}

function aplicarFiltros() { cargarTransferencias(); }
function verDetalle(id) { alert('MODO DESARROLLO: Ver detalle transferencia #' + id); }

function formatearFecha(fecha) {
    const d = new Date(fecha);
    return d.toLocaleDateString('es-GT', { day: '2-digit', month: '2-digit', year: 'numeric' }) + ' ' + 
           d.toLocaleTimeString('es-GT', { hour: '2-digit', minute: '2-digit' });
}

function mostrarError(mensaje) {
    document.getElementById('loadingTable').style.display = 'none';
    document.getElementById('noResults').style.display = 'block';
    document.getElementById('noResults').innerHTML = `<i class="bi bi-exclamation-triangle text-danger" style="font-size: 48px;"></i><p class="mt-3 text-danger">${mensaje}</p>`;
}
</script>

<?php include '../../includes/footer.php'; ?>