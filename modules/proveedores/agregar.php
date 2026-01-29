<?php
/**
 * ================================================
 * MÓDULO PROVEEDORES - AGREGAR
 * ================================================
 * 
 * TODO FASE 5: Conectar con API
 * POST /api/proveedores/crear.php
 * 
 * Inserta en tabla: proveedores
 * Campos: nombre, empresa, contacto, telefono, email, direccion, productos_suministra, activo
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();
requiere_rol(['administrador', 'dueño']);

$titulo_pagina = 'Nuevo Proveedor';
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="container-fluid main-content">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>dashboard.php"><i class="bi bi-house"></i> Dashboard</a></li>
            <li class="breadcrumb-item"><a href="lista.php"><i class="bi bi-truck"></i> Proveedores</a></li>
            <li class="breadcrumb-item active">Nuevo Proveedor</li>
        </ol>
    </nav>

    <div class="page-header mb-4">
        <h1 class="mb-2"><i class="bi bi-plus-circle"></i> Nuevo Proveedor</h1>
        <p class="text-muted mb-0">Registre un nuevo proveedor en el sistema</p>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header" style="background-color: #1e3a8a; color: white;">
                    <i class="bi bi-pencil-square"></i> Información del Proveedor
                </div>
                <div class="card-body">
                    <form id="formProveedor" method="POST">
                        <h5 class="mb-3 text-primary"><i class="bi bi-person"></i> Información Básica</h5>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="nombre" class="form-label"><i class="bi bi-person-badge"></i> Nombre del Proveedor *</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre completo" required>
                            </div>
                            <div class="col-md-6">
                                <label for="empresa" class="form-label"><i class="bi bi-building"></i> Empresa (opcional)</label>
                                <input type="text" class="form-control" id="empresa" name="empresa" placeholder="Nombre de la empresa">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="contacto" class="form-label"><i class="bi bi-person-lines-fill"></i> Persona de Contacto</label>
                            <input type="text" class="form-control" id="contacto" name="contacto" placeholder="Ej: Juan Pérez - Gerente de Ventas">
                        </div>

                        <hr class="my-4">

                        <h5 class="mb-3 text-primary"><i class="bi bi-telephone"></i> Datos de Contacto</h5>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="telefono" class="form-label"><i class="bi bi-phone"></i> Teléfono</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono" placeholder="2234-5678 o 5512-3456">
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label"><i class="bi bi-envelope"></i> Email</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="ejemplo@correo.com">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="direccion" class="form-label"><i class="bi bi-geo-alt"></i> Dirección</label>
                            <textarea class="form-control" id="direccion" name="direccion" rows="2" placeholder="Dirección completa del proveedor"></textarea>
                        </div>

                        <hr class="my-4">

                        <h5 class="mb-3 text-primary"><i class="bi bi-box-seam"></i> Productos que Suministra</h5>

                        <div class="mb-3">
                            <label for="productos_suministra" class="form-label"><i class="bi bi-list-ul"></i> Productos / Servicios</label>
                            <textarea class="form-control" id="productos_suministra" name="productos_suministra" rows="3" placeholder="Ej: Oro 18K, Oro 14K, Cadenas de oro, Diamantes, etc."></textarea>
                            <small class="text-muted">Liste los productos o servicios que este proveedor ofrece</small>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="activo" name="activo" checked>
                            <label class="form-check-label" for="activo">Proveedor activo</label>
                        </div>

                        <div class="d-flex flex-column flex-sm-row justify-content-end gap-2">
                            <a href="lista.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary" id="btnGuardar">
                                <i class="bi bi-save"></i> Guardar Proveedor
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3 shadow-sm">
                <div class="card-header" style="background-color: #1e3a8a; color: white;">
                    <i class="bi bi-lightbulb"></i> Guía Rápida
                </div>
                <div class="card-body">
                    <h6 class="fw-bold">Datos Obligatorios:</h6>
                    <ul class="small mb-3">
                        <li>Nombre del proveedor</li>
                    </ul>

                    <h6 class="fw-bold">Datos Opcionales:</h6>
                    <ul class="small mb-3">
                        <li>Empresa</li>
                        <li>Persona de contacto</li>
                        <li>Teléfono y email</li>
                        <li>Dirección</li>
                        <li>Productos que suministra</li>
                    </ul>

                    <h6 class="fw-bold">Recomendaciones:</h6>
                    <ul class="small mb-0">
                        <li>Complete todos los datos de contacto disponibles</li>
                        <li>Especifique claramente los productos que ofrece</li>
                        <li>Mantenga la información actualizada</li>
                    </ul>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-database"></i> Estructura de Datos
                </div>
                <div class="card-body">
                    <h6 class="fw-bold">Campos de la tabla proveedores:</h6>
                    <ul class="small mb-0">
                        <li><code>nombre</code> - Obligatorio</li>
                        <li><code>empresa</code> - Opcional</li>
                        <li><code>contacto</code> - Opcional</li>
                        <li><code>telefono</code> - Opcional</li>
                        <li><code>email</code> - Opcional</li>
                        <li><code>direccion</code> - Opcional</li>
                        <li><code>productos_suministra</code> - Opcional</li>
                        <li><code>activo</code> - Boolean (default: 1)</li>
                        <li><code>fecha_creacion</code> - Automática</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.main-content { padding: 20px; min-height: calc(100vh - 120px); }
.page-header h1 { font-size: 1.75rem; font-weight: 600; color: #1a1a1a; }
.shadow-sm { box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08) !important; }
.card-body { padding: 25px; }
.form-label { font-weight: 500; margin-bottom: 0.5rem; color: #374151; }
.form-control, .form-select, textarea { border: 1px solid #d1d5db; border-radius: 6px; }
.form-control:focus, .form-select:focus, textarea:focus { border-color: #1e3a8a; box-shadow: 0 0 0 0.2rem rgba(30, 58, 138, 0.15); }
textarea.form-control { resize: vertical; }
.form-check-input { width: 1.2em; height: 1.2em; margin-top: 0.15em; }
.form-check-input:checked { background-color: #1e3a8a; border-color: #1e3a8a; }
h5.text-primary { color: #1e3a8a !important; font-weight: 600; }
h6.fw-bold { color: #1a1a1a; font-size: 0.95rem; margin-bottom: 0.75rem; }
code { background-color: #f3f4f6; padding: 2px 6px; border-radius: 4px; font-size: 0.9em; color: #1e3a8a; }
hr { opacity: 0.1; }
@media (max-width: 575.98px) {
    .main-content { padding: 15px 10px; }
    .page-header h1 { font-size: 1.5rem; }
    .card-body { padding: 15px; }
    h5 { font-size: 1.1rem; }
    .btn { width: 100%; }
}
@media (min-width: 576px) and (max-width: 767.98px) { .main-content { padding: 18px 15px; } }
@media (min-width: 992px) { .main-content { padding: 25px 30px; } }
@media (max-width: 767.98px) { .btn, .form-control, .form-select, textarea { min-height: 44px; } .form-check-input { width: 1.35em; height: 1.35em; } }
</style>

<script>
document.getElementById('formProveedor').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const nombre = document.getElementById('nombre').value.trim();
    
    if (nombre.length < 3) {
        alert('El nombre del proveedor debe tener al menos 3 caracteres');
        document.getElementById('nombre').focus();
        return;
    }
    
    const formData = new FormData(this);
    const datos = {
        nombre: nombre,
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
    fetch('<?php echo BASE_URL; ?>api/proveedores/crear.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(datos)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Proveedor guardado exitosamente');
            setTimeout(() => window.location.href = 'lista.php', 1500);
        } else {
            alert(data.message);
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = '<i class="bi bi-save"></i> Guardar Proveedor';
        }
    });
    */
    
    console.log('Datos proveedor:', datos);
    setTimeout(() => {
        alert('MODO DESARROLLO: Proveedor guardado.\n\n' + JSON.stringify(datos, null, 2));
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = '<i class="bi bi-save"></i> Guardar Proveedor';
    }, 1000);
});
</script>

<?php include '../../includes/footer.php'; ?>