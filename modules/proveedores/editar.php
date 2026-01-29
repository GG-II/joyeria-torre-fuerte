<?php
/**
 * ================================================
 * MÓDULO PROVEEDORES - EDITAR
 * ================================================
 * 
 * TODO FASE 5: Conectar con APIs
 * GET /api/proveedores/ver.php?id={proveedor_id} - Cargar datos
 * POST /api/proveedores/actualizar.php - Guardar cambios
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();
requiere_rol(['administrador', 'dueño']);

$proveedor_id = $_GET['id'] ?? null;
if (!$proveedor_id) {
    header('Location: lista.php');
    exit;
}

$proveedor = null;
$titulo_pagina = 'Editar Proveedor';
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="container-fluid main-content">
    <div id="loadingState" class="text-center py-5">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
        <p class="mt-3 text-muted">Cargando datos del proveedor...</p>
    </div>

    <div id="mainContent" style="display: none;">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>dashboard.php"><i class="bi bi-house"></i> Dashboard</a></li>
                <li class="breadcrumb-item"><a href="lista.php"><i class="bi bi-truck"></i> Proveedores</a></li>
                <li class="breadcrumb-item active">Editar Proveedor</li>
            </ol>
        </nav>

        <div class="page-header mb-4">
            <div class="row align-items-center g-3">
                <div class="col-md-6">
                    <h1 class="mb-2"><i class="bi bi-pencil-square"></i> Editar Proveedor</h1>
                    <p class="text-muted mb-0" id="proveedorId">ID: <?php echo $proveedor_id; ?></p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="ver.php?id=<?php echo $proveedor_id; ?>" class="btn btn-info">
                        <i class="bi bi-eye"></i> <span class="d-none d-sm-inline">Ver Detalles</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header" style="background-color: #1e3a8a; color: white;">
                        <i class="bi bi-pencil-square"></i> Información del Proveedor
                    </div>
                    <div class="card-body">
                        <form id="formProveedor" method="POST">
                            <input type="hidden" name="id" value="<?php echo $proveedor_id; ?>">

                            <h5 class="mb-3 text-primary"><i class="bi bi-person"></i> Información Básica</h5>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="nombre" class="form-label"><i class="bi bi-person-badge"></i> Nombre del Proveedor *</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="empresa" class="form-label"><i class="bi bi-building"></i> Empresa</label>
                                    <input type="text" class="form-control" id="empresa" name="empresa">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="contacto" class="form-label"><i class="bi bi-person-lines-fill"></i> Persona de Contacto</label>
                                <input type="text" class="form-control" id="contacto" name="contacto">
                            </div>

                            <hr class="my-4">

                            <h5 class="mb-3 text-primary"><i class="bi bi-telephone"></i> Datos de Contacto</h5>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="telefono" class="form-label"><i class="bi bi-phone"></i> Teléfono</label>
                                    <input type="tel" class="form-control" id="telefono" name="telefono">
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label"><i class="bi bi-envelope"></i> Email</label>
                                    <input type="email" class="form-control" id="email" name="email">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="direccion" class="form-label"><i class="bi bi-geo-alt"></i> Dirección</label>
                                <textarea class="form-control" id="direccion" name="direccion" rows="2"></textarea>
                            </div>

                            <hr class="my-4">

                            <h5 class="mb-3 text-primary"><i class="bi bi-box-seam"></i> Productos que Suministra</h5>

                            <div class="mb-3">
                                <label for="productos_suministra" class="form-label"><i class="bi bi-list-ul"></i> Productos / Servicios</label>
                                <textarea class="form-control" id="productos_suministra" name="productos_suministra" rows="3"></textarea>
                            </div>

                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="activo" name="activo">
                                <label class="form-check-label" for="activo">Proveedor activo</label>
                            </div>

                            <div class="d-flex flex-column flex-sm-row justify-content-between gap-2">
                                <a href="lista.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Volver
                                </a>
                                <div class="d-flex flex-column flex-sm-row gap-2">
                                    <a href="ver.php?id=<?php echo $proveedor_id; ?>" class="btn btn-info">
                                        <i class="bi bi-eye"></i> Ver Detalles
                                    </a>
                                    <button type="submit" class="btn btn-primary" id="btnGuardar">
                                        <i class="bi bi-save"></i> Guardar Cambios
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card mb-3 shadow-sm">
                    <div class="card-header" style="background-color: #1e3a8a; color: white;">
                        <i class="bi bi-info-circle"></i> Información Actual
                    </div>
                    <div class="card-body" id="infoActual">
                        <div class="text-center py-3"><div class="spinner-border spinner-border-sm"></div></div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header"><i class="bi bi-lightning"></i> Acciones Rápidas</div>
                    <div class="list-group list-group-flush">
                        <a href="ver.php?id=<?php echo $proveedor_id; ?>" class="list-group-item list-group-item-action">
                            <i class="bi bi-eye"></i> Ver detalles completos
                        </a>
                        <a href="#" class="list-group-item list-group-item-action" onclick="alert('MODO DESARROLLO: Ver historial'); return false;">
                            <i class="bi bi-cart"></i> Ver historial de compras
                        </a>
                        <a href="#" class="list-group-item list-group-item-action text-danger" onclick="desactivarProveedor(); return false;">
                            <i class="bi bi-trash"></i> Desactivar proveedor
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="errorState" style="display: none;" class="text-center py-5">
        <i class="bi bi-exclamation-triangle text-danger" style="font-size: 48px;"></i>
        <h4 class="mt-3">Error al cargar el proveedor</h4>
        <p class="text-muted" id="errorMessage">No se pudo cargar la información.</p>
        <a href="lista.php" class="btn btn-primary mt-3"><i class="bi bi-arrow-left"></i> Volver al listado</a>
    </div>
</div>

<style>
.main-content { padding: 20px; min-height: calc(100vh - 120px); }
.page-header h1 { font-size: 1.75rem; font-weight: 600; color: #1a1a1a; }
.shadow-sm { box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08) !important; }
.card-body { padding: 25px; }
.card-body > div:not(:last-child) { padding-bottom: 12px; margin-bottom: 12px; border-bottom: 1px solid #e5e7eb; }
.form-label { font-weight: 500; margin-bottom: 0.5rem; color: #374151; }
.form-control, .form-select, textarea { border: 1px solid #d1d5db; border-radius: 6px; }
.form-control:focus, .form-select:focus, textarea:focus { border-color: #1e3a8a; box-shadow: 0 0 0 0.2rem rgba(30, 58, 138, 0.15); }
textarea.form-control { resize: vertical; }
.form-check-input { width: 1.2em; height: 1.2em; margin-top: 0.15em; }
.form-check-input:checked { background-color: #1e3a8a; border-color: #1e3a8a; }
h5.text-primary { color: #1e3a8a !important; font-weight: 600; }
hr { opacity: 0.1; }
.list-group-item { transition: background-color 0.15s ease; }
.list-group-item:hover { background-color: #f3f4f6; }
@media (max-width: 575.98px) {
    .main-content { padding: 15px 10px; }
    .page-header h1 { font-size: 1.5rem; }
    .card-body { padding: 15px; }
    h5 { font-size: 1.1rem; }
}
@media (min-width: 576px) and (max-width: 767.98px) { .main-content { padding: 18px 15px; } }
@media (min-width: 992px) { .main-content { padding: 25px 30px; } }
@media (max-width: 767.98px) { .btn, .form-control, .form-select, textarea { min-height: 44px; } .form-check-input { width: 1.35em; height: 1.35em; } }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    cargarDatosProveedor();
});

function cargarDatosProveedor() {
    const proveedorId = <?php echo $proveedor_id; ?>;
    
    /* TODO FASE 5: Descomentar
    fetch('<?php echo BASE_URL; ?>api/proveedores/ver.php?id=' + proveedorId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                llenarFormulario(data.data);
                mostrarInfoActual(data.data);
            } else {
                mostrarError(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarError('Error al cargar los datos');
        });
    */
    
    setTimeout(() => mostrarError('MODO DESARROLLO: Esperando API'), 1500);
}

function llenarFormulario(proveedor) {
    document.getElementById('loadingState').style.display = 'none';
    document.getElementById('mainContent').style.display = 'block';
    
    document.getElementById('proveedorId').textContent = 'ID: ' + proveedor.id;
    document.getElementById('nombre').value = proveedor.nombre || '';
    document.getElementById('empresa').value = proveedor.empresa || '';
    document.getElementById('contacto').value = proveedor.contacto || '';
    document.getElementById('telefono').value = proveedor.telefono || '';
    document.getElementById('email').value = proveedor.email || '';
    document.getElementById('direccion').value = proveedor.direccion || '';
    document.getElementById('productos_suministra').value = proveedor.productos_suministra || '';
    document.getElementById('activo').checked = proveedor.activo == 1;
}

function mostrarInfoActual(proveedor) {
    const badgeEstado = proveedor.activo 
        ? '<span class="badge bg-success">Activo</span>'
        : '<span class="badge bg-secondary">Inactivo</span>';
    
    document.getElementById('infoActual').innerHTML = `
        <div><small class="text-muted d-block">Fecha de Registro:</small><strong>${formatearFechaHora(proveedor.fecha_creacion)}</strong></div>
        <div><small class="text-muted d-block">Estado:</small>${badgeEstado}</div>
    `;
}

document.getElementById('formProveedor').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const datos = {
        id: formData.get('id'),
        nombre: formData.get('nombre'),
        empresa: formData.get('empresa') || null,
        contacto: formData.get('contacto') || null,
        telefono: formData.get('telefono') || null,
        email: formData.get('email') || null,
        direccion: formData.get('direccion') || null,
        productos_suministra: formData.get('productos_suministra') || null,
        activo: formData.get('activo') ? 1 : 0
    };
    
    const btnGuardar = document.getElementById('btnGuardar');
    btnGuardar.disabled = true;
    btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
    
    /* TODO FASE 5: Descomentar
    fetch('<?php echo BASE_URL; ?>api/proveedores/actualizar.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(datos)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Proveedor actualizado exitosamente');
            setTimeout(() => window.location.href = 'lista.php', 1500);
        } else {
            alert(data.message);
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = '<i class="bi bi-save"></i> Guardar Cambios';
        }
    });
    */
    
    console.log('Datos actualizados:', datos);
    setTimeout(() => {
        alert('MODO DESARROLLO: Proveedor actualizado.\n\n' + JSON.stringify(datos, null, 2));
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = '<i class="bi bi-save"></i> Guardar Cambios';
    }, 1000);
});

function desactivarProveedor() {
    if (confirm('¿Está seguro de desactivar este proveedor?')) {
        alert('MODO DESARROLLO: Desactivar proveedor - Pendiente implementar');
    }
}

function formatearFechaHora(fecha) {
    const d = new Date(fecha);
    return d.toLocaleDateString('es-GT', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function mostrarError(mensaje) {
    document.getElementById('loadingState').style.display = 'none';
    document.getElementById('errorState').style.display = 'block';
    document.getElementById('errorMessage').textContent = mensaje;
}
</script>

<?php include '../../includes/footer.php'; ?>