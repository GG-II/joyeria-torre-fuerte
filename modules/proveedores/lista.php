<?php
// ================================================
// MÓDULO PROVEEDORES - LISTA
// ================================================

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

// Verificar autenticación y permisos
requiere_autenticacion();

// Título de página
$titulo_pagina = 'Proveedores';

// Incluir header
include '../../includes/header.php';

// Incluir navbar
include '../../includes/navbar.php';

// Datos dummy de proveedores (CAMPOS REALES DEL SCHEMA)
$proveedores = [
    [
        'id' => 1,
        'nombre' => 'Juan Pérez',
        'empresa' => 'Oro Fino Guatemala',
        'contacto' => 'Juan Pérez - Gerente de Ventas',
        'telefono' => '2234-5678',
        'email' => 'ventas@orofino.gt',
        'direccion' => 'Zona 10, Ciudad de Guatemala',
        'productos_suministra' => 'Oro 18K, Oro 14K, Cadenas de oro',
        'activo' => 1,
        'fecha_creacion' => '2024-01-15 10:00:00',
        'total_compras' => 15
    ],
    [
        'id' => 2,
        'nombre' => 'María González',
        'empresa' => 'Plata Sterling S.A.',
        'contacto' => 'María González',
        'telefono' => '5512-3456',
        'email' => 'maria@plasterling.com',
        'direccion' => 'Antigua Guatemala',
        'productos_suministra' => 'Plata 925, Aretes de plata, Dijes',
        'activo' => 1,
        'fecha_creacion' => '2024-02-20 14:30:00',
        'total_compras' => 8
    ],
    [
        'id' => 3,
        'nombre' => 'Carlos Méndez',
        'empresa' => 'Piedras Preciosas del Mundo',
        'contacto' => 'Carlos Méndez - Director',
        'telefono' => '5598-7654',
        'email' => 'info@piedraspreciosas.com',
        'direccion' => 'Zona 9, Ciudad de Guatemala',
        'productos_suministra' => 'Diamantes, Rubíes, Esmeraldas, Zafiros',
        'activo' => 1,
        'fecha_creacion' => '2024-03-10 09:15:00',
        'total_compras' => 12
    ],
    [
        'id' => 4,
        'nombre' => 'Roberto Sánchez',
        'empresa' => 'Insumos Joyería Total',
        'contacto' => 'Roberto Sánchez',
        'telefono' => '2445-6789',
        'email' => 'roberto@insumostotal.com',
        'direccion' => 'Zona 12, Ciudad de Guatemala',
        'productos_suministra' => 'Herramientas, Cajas para joyas, Empaques',
        'activo' => 1,
        'fecha_creacion' => '2024-04-05 11:00:00',
        'total_compras' => 5
    ],
    [
        'id' => 5,
        'nombre' => 'Ana Rodríguez',
        'empresa' => 'Metales Preciosos Internacional',
        'contacto' => 'Ana Rodríguez - Asesora Comercial',
        'telefono' => '5534-5678',
        'email' => null,
        'direccion' => null,
        'productos_suministra' => 'Oro, Plata, Platino',
        'activo' => 0,
        'fecha_creacion' => '2023-12-01 08:00:00',
        'total_compras' => 3
    ]
];
?>

<!-- Contenido Principal -->
<div class="container-fluid main-content">
    <!-- Encabezado de Página -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1>
                    <i class="bi bi-truck"></i>
                    Proveedores
                </h1>
                <p class="text-muted">Gestión de proveedores y suministros</p>
            </div>
            <div class="col-md-6 text-end">
                <?php if (tiene_permiso('proveedores', 'crear')): ?>
                <a href="agregar.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i>
                    Nuevo Proveedor
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Resumen de Proveedores -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card azul">
                <div class="stat-icon">
                    <i class="bi bi-building"></i>
                </div>
                <div class="stat-value">
                    <?php echo count($proveedores); ?>
                </div>
                <div class="stat-label">Total Proveedores</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card verde">
                <div class="stat-icon">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-value">
                    <?php echo count(array_filter($proveedores, fn($p) => $p['activo'] == 1)); ?>
                </div>
                <div class="stat-label">Activos</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card rojo">
                <div class="stat-icon">
                    <i class="bi bi-x-circle"></i>
                </div>
                <div class="stat-value">
                    <?php echo count(array_filter($proveedores, fn($p) => $p['activo'] == 0)); ?>
                </div>
                <div class="stat-label">Inactivos</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card dorado">
                <div class="stat-icon">
                    <i class="bi bi-cart"></i>
                </div>
                <div class="stat-value">
                    <?php echo array_sum(array_column($proveedores, 'total_compras')); ?>
                </div>
                <div class="stat-label">Compras Totales</div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Buscar</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control" id="searchInput" 
                               placeholder="Nombre, empresa, teléfono...">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Estado</label>
                    <select class="form-select" id="filterEstado">
                        <option value="">Todos</option>
                        <option value="1">Activos</option>
                        <option value="0">Inactivos</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Productos</label>
                    <input type="text" class="form-control" id="filterProductos" 
                           placeholder="Ej: oro, plata...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button class="btn btn-secondary w-100" onclick="limpiarFiltros()">
                        <i class="bi bi-x-circle"></i>
                        Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Proveedores -->
    <div class="card">
        <div class="card-header">
            <i class="bi bi-table"></i>
            Listado de Proveedores (<?php echo count($proveedores); ?>)
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="tablaProveedores">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre / Empresa</th>
                            <th>Contacto</th>
                            <th>Productos Suministra</th>
                            <th>Compras</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($proveedores as $prov): ?>
                        <tr>
                            <td class="fw-bold"><?php echo $prov['id']; ?></td>
                            <td>
                                <div class="fw-bold"><?php echo $prov['nombre']; ?></div>
                                <?php if ($prov['empresa']): ?>
                                    <small class="text-muted"><?php echo $prov['empresa']; ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($prov['telefono']): ?>
                                    <div>
                                        <i class="bi bi-phone"></i>
                                        <?php echo $prov['telefono']; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($prov['email']): ?>
                                    <div>
                                        <i class="bi bi-envelope"></i>
                                        <small><?php echo $prov['email']; ?></small>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?php 
                                    $productos = $prov['productos_suministra'];
                                    echo strlen($productos) > 50 ? substr($productos, 0, 50) . '...' : $productos;
                                    ?>
                                </small>
                            </td>
                            <td>
                                <span class="badge bg-info"><?php echo $prov['total_compras']; ?></span>
                            </td>
                            <td>
                                <?php if ($prov['activo']): ?>
                                    <span class="badge bg-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <a href="ver.php?id=<?php echo $prov['id']; ?>" 
                                       class="btn btn-sm btn-info"
                                       data-bs-toggle="tooltip" 
                                       title="Ver detalles">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if (tiene_permiso('proveedores', 'editar')): ?>
                                    <a href="editar.php?id=<?php echo $prov['id']; ?>" 
                                       class="btn btn-sm btn-warning"
                                       data-bs-toggle="tooltip" 
                                       title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (tiene_permiso('proveedores', 'eliminar')): ?>
                                    <button class="btn btn-sm btn-danger"
                                            data-bs-toggle="tooltip" 
                                            title="Desactivar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <small class="text-muted">
                Mostrando <?php echo count($proveedores); ?> proveedores
            </small>
        </div>
    </div>
</div>

<script>
function limpiarFiltros() {
    document.getElementById('searchInput').value = '';
    document.getElementById('filterEstado').value = '';
    document.getElementById('filterProductos').value = '';
}

// Búsqueda en tiempo real
document.getElementById('searchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#tablaProveedores tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});
</script>

<?php
// Incluir footer
include '../../includes/footer.php';
?>