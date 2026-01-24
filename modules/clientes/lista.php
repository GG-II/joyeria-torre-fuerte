<?php
// ================================================
// MÓDULO CLIENTES - LISTA (CORREGIDO)
// ================================================

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

// Verificar autenticación y permisos
requiere_autenticacion();
requiere_rol(['administrador', 'dueño', 'vendedor', 'cajero']);

// Título de página
$titulo_pagina = 'Clientes';

// Incluir header
include '../../includes/header.php';

// Incluir navbar
include '../../includes/navbar.php';

// Datos dummy de clientes (CAMPOS REALES DEL SCHEMA)
$clientes = [
    [
        'id' => 1,
        'nombre' => 'María García López',
        'nit' => '12345678-9',
        'telefono' => '5512-3456',
        'email' => 'maria.garcia@email.com',
        'direccion' => 'Zona 10, Guatemala',
        'tipo_cliente' => 'publico',
        'tipo_mercaderias' => 'ambas',
        'limite_credito' => 5000.00,
        'plazo_credito_dias' => 30,
        'saldo_creditos' => 0.00,  // Viene de JOIN con creditos_clientes
        'fecha_creacion' => '2024-01-15',
        'activo' => 1
    ],
    [
        'id' => 2,
        'nombre' => 'Carlos Méndez Reyes',
        'nit' => '98765432-1',
        'telefono' => '5598-7654',
        'email' => 'carlos.mendez@email.com',
        'direccion' => 'Zona 1, Guatemala',
        'tipo_cliente' => 'mayorista',
        'tipo_mercaderias' => 'oro',
        'limite_credito' => 10000.00,
        'plazo_credito_dias' => 60,
        'saldo_creditos' => 2500.00,
        'fecha_creacion' => '2024-08-22',
        'activo' => 1
    ],
    [
        'id' => 3,
        'nombre' => 'Ana Sofía Ramírez',
        'nit' => '45678912-3',
        'telefono' => '5534-5678',
        'email' => 'ana.ramirez@email.com',
        'direccion' => 'Antigua Guatemala',
        'tipo_cliente' => 'publico',
        'tipo_mercaderias' => 'plata',
        'limite_credito' => 3000.00,
        'plazo_credito_dias' => 15,
        'saldo_creditos' => 500.00,
        'fecha_creacion' => '2023-03-10',
        'activo' => 1
    ],
    [
        'id' => 4,
        'nombre' => 'Roberto Torres Pérez',
        'nit' => 'CF',
        'telefono' => '5501-2345',
        'email' => '',
        'direccion' => 'Zona 11, Mixco',
        'tipo_cliente' => 'publico',
        'tipo_mercaderias' => 'ambas',
        'limite_credito' => 0.00,
        'plazo_credito_dias' => null,
        'saldo_creditos' => 0.00,
        'fecha_creacion' => '2025-01-10',
        'activo' => 1
    ],
    [
        'id' => 5,
        'nombre' => 'Lucía Fernández',
        'nit' => '78945612-0',
        'telefono' => '5567-8901',
        'email' => 'lucia.fernandez@email.com',
        'direccion' => 'Zona 15, Guatemala',
        'tipo_cliente' => 'mayorista',
        'tipo_mercaderias' => 'ambas',
        'limite_credito' => 15000.00,
        'plazo_credito_dias' => 90,
        'saldo_creditos' => 0.00,
        'fecha_creacion' => '2024-06-18',
        'activo' => 0
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
                    <i class="bi bi-people"></i>
                    Clientes
                </h1>
                <p class="text-muted">Gestión de clientes y cuentas por cobrar</p>
            </div>
            <div class="col-md-6 text-end">
                <?php if (tiene_permiso('clientes', 'crear')): ?>
                <a href="agregar.php" class="btn btn-primary">
                    <i class="bi bi-person-plus"></i>
                    Nuevo Cliente
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Filtros y Búsqueda -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Buscar Cliente</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control" id="searchInput" 
                               placeholder="Nombre, NIT, teléfono...">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tipo de Cliente</label>
                    <select class="form-select" id="filterTipo">
                        <option value="">Todos</option>
                        <option value="publico">Público</option>
                        <option value="mayorista">Mayorista</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Estado</label>
                    <select class="form-select" id="filterEstado">
                        <option value="">Todos</option>
                        <option value="1" selected>Activos</option>
                        <option value="0">Inactivos</option>
                    </select>
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

    <!-- Tabla de Clientes -->
    <div class="card">
        <div class="card-header">
            <i class="bi bi-table"></i>
            Listado de Clientes (<?php echo count($clientes); ?>)
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="tablaClientes">
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th>Cliente</th>
                            <th>NIT</th>
                            <th>Contacto</th>
                            <th>Tipo</th>
                            <th>Mercaderías</th>
                            <th>Crédito Pendiente</th>
                            <th>Estado</th>
                            <th width="180" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clientes as $cliente): ?>
                        <tr>
                            <td class="fw-bold"><?php echo $cliente['id']; ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar bg-primary text-white me-2">
                                        <?php echo strtoupper(substr($cliente['nombre'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold"><?php echo $cliente['nombre']; ?></div>
                                        <small class="text-muted">
                                            <i class="bi bi-calendar3"></i>
                                            Desde <?php echo date('d/m/Y', strtotime($cliente['fecha_creacion'])); ?>
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo $cliente['nit']; ?></td>
                            <td>
                                <div>
                                    <i class="bi bi-telephone text-primary"></i>
                                    <?php echo $cliente['telefono']; ?>
                                </div>
                                <?php if ($cliente['email']): ?>
                                <div>
                                    <i class="bi bi-envelope text-muted"></i>
                                    <small><?php echo $cliente['email']; ?></small>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $badges = [
                                    'publico' => 'bg-info',
                                    'mayorista' => 'bg-dorado'
                                ];
                                $badge_class = $badges[$cliente['tipo_cliente']] ?? 'bg-secondary';
                                ?>
                                <span class="badge <?php echo $badge_class; ?>">
                                    <?php echo ucfirst($cliente['tipo_cliente']); ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $icons = [
                                    'oro' => '🟡',
                                    'plata' => '⚪',
                                    'ambas' => '🟡⚪'
                                ];
                                ?>
                                <span title="<?php echo ucfirst($cliente['tipo_mercaderias']); ?>">
                                    <?php echo $icons[$cliente['tipo_mercaderias']]; ?>
                                    <?php echo ucfirst($cliente['tipo_mercaderias']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($cliente['saldo_creditos'] > 0): ?>
                                    <span class="text-danger fw-bold">
                                        Q <?php echo number_format($cliente['saldo_creditos'], 2); ?>
                                    </span>
                                    <br>
                                    <small class="text-muted">
                                        Límite: Q <?php echo number_format($cliente['limite_credito'], 2); ?>
                                    </small>
                                <?php else: ?>
                                    <span class="text-success">
                                        <i class="bi bi-check-circle"></i> Sin deuda
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($cliente['activo']): ?>
                                    <span class="badge bg-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <a href="ver.php?id=<?php echo $cliente['id']; ?>" 
                                       class="btn btn-sm btn-info" 
                                       data-bs-toggle="tooltip" 
                                       title="Ver detalles">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if (tiene_permiso('clientes', 'editar')): ?>
                                    <a href="editar.php?id=<?php echo $cliente['id']; ?>" 
                                       class="btn btn-sm btn-warning"
                                       data-bs-toggle="tooltip" 
                                       title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (tiene_permiso('clientes', 'eliminar')): ?>
                                    <button class="btn btn-sm btn-danger" 
                                            data-action="delete"
                                            data-bs-toggle="tooltip" 
                                            title="Eliminar">
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
            <div class="row align-items-center">
                <div class="col-md-6">
                    <small class="text-muted">
                        Mostrando <?php echo count($clientes); ?> clientes
                    </small>
                </div>
                <div class="col-md-6">
                    <!-- Paginación aquí cuando se conecte -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Funciones de filtrado
function limpiarFiltros() {
    document.getElementById('searchInput').value = '';
    document.getElementById('filterTipo').value = '';
    document.getElementById('filterEstado').value = '';
}

// Búsqueda en tiempo real
document.getElementById('searchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#tablaClientes tbody tr');
    
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