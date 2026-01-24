<?php
// ================================================
// MÓDULO CONFIGURACIÓN - GESTIÓN DE SUCURSALES
// ================================================

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();
requiere_rol(['administrador', 'dueño']);

$titulo_pagina = 'Gestión de Sucursales';
include '../../includes/header.php';
include '../../includes/navbar.php';

// Datos dummy de sucursales (CAMPOS REALES)
$sucursales = [
    [
        'id' => 1,
        'nombre' => 'Los Arcos',
        'direccion' => 'Centro Comercial Los Arcos, Local 23, Zona 10',
        'telefono' => '2234-5678',
        'email' => 'losarcos@joyeria.com',
        'responsable_id' => 2,
        'responsable_nombre' => 'María Vendedor',
        'activo' => 1,
        'fecha_creacion' => '2024-01-01 08:00:00',
        'total_usuarios' => 3,
        'total_inventario' => 145
    ],
    [
        'id' => 2,
        'nombre' => 'Chinaca Central',
        'direccion' => 'Centro Comercial Chinaca Central, Local 45, Huehuetenango',
        'telefono' => '7765-4321',
        'email' => 'chinaca@joyeria.com',
        'responsable_id' => 4,
        'responsable_nombre' => 'Ana Cajero',
        'activo' => 1,
        'fecha_creacion' => '2024-06-15 10:00:00',
        'total_usuarios' => 2,
        'total_inventario' => 98
    ]
];
?>

<div class="container-fluid main-content">
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1><i class="bi bi-building"></i> Gestión de Sucursales</h1>
                <p class="text-muted">Administración de sucursales</p>
            </div>
            <div class="col-md-6 text-end">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalSucursal">
                    <i class="bi bi-plus-circle"></i>
                    Nueva Sucursal
                </button>
            </div>
        </div>
    </div>

    <!-- Resumen -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stat-card azul">
                <div class="stat-icon"><i class="bi bi-building"></i></div>
                <div class="stat-value"><?php echo count($sucursales); ?></div>
                <div class="stat-label">Total Sucursales</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card verde">
                <div class="stat-icon"><i class="bi bi-people"></i></div>
                <div class="stat-value">
                    <?php echo array_sum(array_column($sucursales, 'total_usuarios')); ?>
                </div>
                <div class="stat-label">Total Usuarios</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card dorado">
                <div class="stat-icon"><i class="bi bi-box-seam"></i></div>
                <div class="stat-value">
                    <?php echo array_sum(array_column($sucursales, 'total_inventario')); ?>
                </div>
                <div class="stat-label">Productos en Inventario</div>
            </div>
        </div>
    </div>

    <!-- Tarjetas de Sucursales -->
    <div class="row">
        <?php foreach ($sucursales as $sucursal): ?>
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-building"></i>
                            <?php echo $sucursal['nombre']; ?>
                        </h5>
                        <?php if ($sucursal['activo']): ?>
                            <span class="badge bg-success">Activa</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inactiva</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">
                            <i class="bi bi-geo-alt"></i> Dirección:
                        </small>
                        <strong><?php echo $sucursal['direccion']; ?></strong>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block">
                                <i class="bi bi-phone"></i> Teléfono:
                            </small>
                            <strong><?php echo $sucursal['telefono']; ?></strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">
                                <i class="bi bi-envelope"></i> Email:
                            </small>
                            <strong><?php echo $sucursal['email']; ?></strong>
                        </div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">
                            <i class="bi bi-person-badge"></i> Responsable:
                        </small>
                        <strong><?php echo $sucursal['responsable_nombre']; ?></strong>
                    </div>
                    <hr>
                    <div class="row text-center">
                        <div class="col-6">
                            <h4 class="text-primary mb-0"><?php echo $sucursal['total_usuarios']; ?></h4>
                            <small class="text-muted">Usuarios</small>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success mb-0"><?php echo $sucursal['total_inventario']; ?></h4>
                            <small class="text-muted">Productos</small>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="btn-group w-100" role="group">
                        <button class="btn btn-warning">
                            <i class="bi bi-pencil"></i> Editar
                        </button>
                        <button class="btn btn-info">
                            <i class="bi bi-eye"></i> Ver Detalles
                        </button>
                        <button class="btn btn-danger">
                            <i class="bi bi-x-circle"></i> Desactivar
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal Agregar/Editar Sucursal -->
<div class="modal fade" id="modalSucursal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-building"></i> Nueva Sucursal
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formSucursal">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre de la Sucursal *</label>
                            <input type="text" class="form-control" name="nombre" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Responsable</label>
                            <select class="form-select" name="responsable_id">
                                <option value="">Sin responsable</option>
                                <option value="2">María Vendedor</option>
                                <option value="4">Ana Cajero</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dirección *</label>
                        <textarea class="form-control" name="direccion" rows="2" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" name="telefono">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email">
                        </div>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="activo" checked>
                        <label class="form-check-label">Sucursal activa</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="guardarSucursal()">
                    <i class="bi bi-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function guardarSucursal() {
    const formData = new FormData(document.getElementById('formSucursal'));
    // API: INSERT INTO sucursales (nombre, direccion, telefono, email, responsable_id, activo)
    console.log('Sucursal a crear:', Object.fromEntries(formData));
    alert('Sucursal creada exitosamente');
}
</script>

<?php include '../../includes/footer.php'; ?>