<?php
// ================================================
// MÓDULO PROVEEDORES - EDITAR
// ================================================

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

// Verificar autenticación y permisos
requiere_autenticacion();
requiere_rol(['administrador', 'dueño']);

// Obtener ID del proveedor (dummy)
$proveedor_id = $_GET['id'] ?? 1;

// Datos dummy del proveedor (CAMPOS REALES)
$proveedor = [
    'id' => $proveedor_id,
    'nombre' => 'Juan Pérez',
    'empresa' => 'Oro Fino Guatemala',
    'contacto' => 'Juan Pérez - Gerente de Ventas',
    'telefono' => '2234-5678',
    'email' => 'ventas@orofino.gt',
    'direccion' => 'Zona 10, Ciudad de Guatemala',
    'productos_suministra' => 'Oro 18K, Oro 14K, Cadenas de oro',
    'activo' => 1,
    'fecha_creacion' => '2024-01-15 10:00:00'
];

// Título de página
$titulo_pagina = 'Editar Proveedor';

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
            <li class="breadcrumb-item active">Editar Proveedor</li>
        </ol>
    </nav>

    <!-- Encabezado -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1>
                    <i class="bi bi-pencil-square"></i>
                    Editar Proveedor
                </h1>
                <p class="text-muted">ID: <?php echo $proveedor['id']; ?></p>
            </div>
            <div class="col-md-6 text-end">
                <a href="ver.php?id=<?php echo $proveedor['id']; ?>" class="btn btn-info">
                    <i class="bi bi-eye"></i>
                    Ver Detalles
                </a>
            </div>
        </div>
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
                        <input type="hidden" name="id" value="<?php echo $proveedor['id']; ?>">

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
                                       value="<?php echo htmlspecialchars($proveedor['nombre']); ?>"
                                       required>
                            </div>
                            <div class="col-md-6">
                                <label for="empresa" class="form-label">
                                    <i class="bi bi-building"></i> Empresa
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="empresa" 
                                       name="empresa" 
                                       value="<?php echo htmlspecialchars($proveedor['empresa']); ?>">
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
                                   value="<?php echo htmlspecialchars($proveedor['contacto']); ?>">
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
                                       value="<?php echo htmlspecialchars($proveedor['telefono']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">
                                    <i class="bi bi-envelope"></i> Email
                                </label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       value="<?php echo htmlspecialchars($proveedor['email']); ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="direccion" class="form-label">
                                <i class="bi bi-geo-alt"></i> Dirección
                            </label>
                            <textarea class="form-control" 
                                      id="direccion" 
                                      name="direccion" 
                                      rows="2"><?php echo htmlspecialchars($proveedor['direccion']); ?></textarea>
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
                                      rows="3"><?php echo htmlspecialchars($proveedor['productos_suministra']); ?></textarea>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="activo" 
                                   name="activo"
                                   <?php echo $proveedor['activo'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="activo">
                                Proveedor activo
                            </label>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-between">
                            <a href="lista.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i>
                                Volver al Listado
                            </a>
                            <div class="d-flex gap-2">
                                <a href="ver.php?id=<?php echo $proveedor['id']; ?>" class="btn btn-info">
                                    <i class="bi bi-eye"></i>
                                    Ver Detalles
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i>
                                    Guardar Cambios
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Panel Lateral -->
        <div class="col-lg-4">
            <!-- Información Actual -->
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-info-circle"></i>
                    Información Actual
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted d-block">Fecha de Registro:</small>
                        <strong><?php echo date('d/m/Y H:i', strtotime($proveedor['fecha_creacion'])); ?></strong>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block">Estado:</small>
                        <?php if ($proveedor['activo']): ?>
                            <span class="badge bg-success">Activo</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inactivo</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-lightning"></i>
                    Acciones Rápidas
                </div>
                <div class="list-group list-group-flush">
                    <a href="ver.php?id=<?php echo $proveedor['id']; ?>" class="list-group-item list-group-item-action">
                        <i class="bi bi-eye"></i>
                        Ver detalles completos
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <i class="bi bi-cart"></i>
                        Ver historial de compras
                    </a>
                    <a href="#" class="list-group-item list-group-item-action text-danger">
                        <i class="bi bi-trash"></i>
                        Desactivar proveedor
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validación del formulario
document.getElementById('formProveedor').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // API actualizará:
    // UPDATE proveedores 
    // SET nombre = ?, empresa = ?, contacto = ?, telefono = ?, 
    //     email = ?, direccion = ?, productos_suministra = ?, activo = ?
    // WHERE id = ?
    
    alert('Proveedor actualizado exitosamente');
    
    const formData = new FormData(this);
    console.log('Datos actualizados:', Object.fromEntries(formData));
    
    // window.location.href = 'lista.php';
});
</script>

<?php
// Incluir footer
include '../../includes/footer.php';
?>