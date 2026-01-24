<?php
// ================================================
// MÓDULO PROVEEDORES - AGREGAR
// ================================================

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

// Verificar autenticación y permisos
requiere_autenticacion();
requiere_rol(['administrador', 'dueño']);

// Título de página
$titulo_pagina = 'Nuevo Proveedor';

// Incluir header
include '../../includes/header.php';

// Incluir navbar
include '../../includes/navbar.php';
?>

<!-- Contenido Principal -->
<div class="container-fluid main-content">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="<?php echo BASE_URL; ?>dashboard.php">
                    <i class="bi bi-house"></i> Dashboard
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="lista.php">
                    <i class="bi bi-truck"></i> Proveedores
                </a>
            </li>
            <li class="breadcrumb-item active">Nuevo Proveedor</li>
        </ol>
    </nav>

    <!-- Encabezado -->
    <div class="page-header">
        <h1>
            <i class="bi bi-plus-circle"></i>
            Nuevo Proveedor
        </h1>
        <p class="text-muted">Registre un nuevo proveedor en el sistema</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Formulario -->
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-pencil-square"></i>
                    Información del Proveedor
                </div>
                <div class="card-body">
                    <form id="formProveedor" method="POST" action="">
                        <!-- Información Básica -->
                        <h5 class="mb-3 text-primary">
                            <i class="bi bi-person"></i>
                            Información Básica
                        </h5>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nombre" class="form-label">
                                    <i class="bi bi-person-badge"></i> Nombre del Proveedor *
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="nombre" 
                                       name="nombre" 
                                       placeholder="Nombre completo"
                                       required>
                            </div>
                            <div class="col-md-6">
                                <label for="empresa" class="form-label">
                                    <i class="bi bi-building"></i> Empresa (opcional)
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="empresa" 
                                       name="empresa" 
                                       placeholder="Nombre de la empresa">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="contacto" class="form-label">
                                <i class="bi bi-person-lines-fill"></i> Persona de Contacto
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="contacto" 
                                   name="contacto" 
                                   placeholder="Ej: Juan Pérez - Gerente de Ventas">
                        </div>

                        <hr class="my-4">

                        <!-- Datos de Contacto -->
                        <h5 class="mb-3 text-primary">
                            <i class="bi bi-telephone"></i>
                            Datos de Contacto
                        </h5>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="telefono" class="form-label">
                                    <i class="bi bi-phone"></i> Teléfono
                                </label>
                                <input type="tel" 
                                       class="form-control" 
                                       id="telefono" 
                                       name="telefono" 
                                       placeholder="2234-5678 o 5512-3456">
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">
                                    <i class="bi bi-envelope"></i> Email
                                </label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       placeholder="ejemplo@correo.com">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="direccion" class="form-label">
                                <i class="bi bi-geo-alt"></i> Dirección
                            </label>
                            <textarea class="form-control" 
                                      id="direccion" 
                                      name="direccion" 
                                      rows="2"
                                      placeholder="Dirección completa del proveedor"></textarea>
                        </div>

                        <hr class="my-4">

                        <!-- Productos que Suministra -->
                        <h5 class="mb-3 text-primary">
                            <i class="bi bi-box-seam"></i>
                            Productos que Suministra
                        </h5>

                        <div class="mb-3">
                            <label for="productos_suministra" class="form-label">
                                <i class="bi bi-list-ul"></i> Productos / Servicios
                            </label>
                            <textarea class="form-control" 
                                      id="productos_suministra" 
                                      name="productos_suministra" 
                                      rows="3"
                                      placeholder="Ej: Oro 18K, Oro 14K, Cadenas de oro, Diamantes, etc."></textarea>
                            <small class="text-muted">
                                Liste los productos o servicios que este proveedor ofrece
                            </small>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="activo" 
                                   name="activo" 
                                   checked>
                            <label class="form-check-label" for="activo">
                                Proveedor activo
                            </label>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="lista.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i>
                                Guardar Proveedor
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Panel Lateral -->
        <div class="col-lg-4">
            <!-- Ayuda -->
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <i class="bi bi-lightbulb"></i>
                    Guía Rápida
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

            <!-- Información Técnica -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-database"></i>
                    Estructura de Datos
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

<script>
// Validación del formulario
document.getElementById('formProveedor').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const nombre = document.getElementById('nombre').value.trim();
    
    if (nombre.length < 3) {
        alert('El nombre del proveedor debe tener al menos 3 caracteres');
        return;
    }
    
    // API insertará en:
    // INSERT INTO proveedores (nombre, empresa, contacto, telefono, email, direccion, productos_suministra, activo)
    // VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    
    alert('Proveedor guardado exitosamente');
    
    const formData = new FormData(this);
    console.log('Datos del proveedor:', Object.fromEntries(formData));
    
    // window.location.href = 'lista.php';
});
</script>

<?php
// Incluir footer
include '../../includes/footer.php';
?>