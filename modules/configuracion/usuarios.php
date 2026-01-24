<?php
// ================================================
// MÓDULO CONFIGURACIÓN - GESTIÓN DE USUARIOS
// ================================================

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

// Verificar autenticación y permisos
requiere_autenticacion();
requiere_rol(['administrador', 'dueño']);

// Título de página
$titulo_pagina = 'Gestión de Usuarios';

// Incluir header
include '../../includes/header.php';

// Incluir navbar
include '../../includes/navbar.php';

// Datos dummy de usuarios (CAMPOS REALES DEL SCHEMA)
$usuarios = [
    [
        'id' => 1,
        'nombre' => 'Carlos Administrador',
        'email' => 'admin@joyeria.com',
        'rol' => 'administrador',
        'sucursal_id' => null,
        'sucursal_nombre' => 'Todas',
        'foto_perfil' => null,
        'activo' => 1,
        'fecha_creacion' => '2024-01-01 08:00:00',
        'ultimo_acceso' => '2025-01-24 14:30:00'
    ],
    [
        'id' => 2,
        'nombre' => 'María Vendedor',
        'email' => 'maria@joyeria.com',
        'rol' => 'vendedor',
        'sucursal_id' => 1,
        'sucursal_nombre' => 'Los Arcos',
        'foto_perfil' => null,
        'activo' => 1,
        'fecha_creacion' => '2024-02-15 10:00:00',
        'ultimo_acceso' => '2025-01-24 15:00:00'
    ],
    [
        'id' => 3,
        'nombre' => 'Roberto Orfebre',
        'email' => 'roberto@joyeria.com',
        'rol' => 'orfebre',
        'sucursal_id' => 1,
        'sucursal_nombre' => 'Los Arcos',
        'foto_perfil' => null,
        'activo' => 1,
        'fecha_creacion' => '2024-03-01 09:00:00',
        'ultimo_acceso' => '2025-01-24 13:45:00'
    ],
    [
        'id' => 4,
        'nombre' => 'Ana Cajero',
        'email' => 'ana@joyeria.com',
        'rol' => 'cajero',
        'sucursal_id' => 2,
        'sucursal_nombre' => 'Chinaca Central',
        'foto_perfil' => null,
        'activo' => 1,
        'fecha_creacion' => '2024-04-10 11:00:00',
        'ultimo_acceso' => '2025-01-23 18:00:00'
    ],
    [
        'id' => 5,
        'nombre' => 'Luis Marketing',
        'email' => 'luis@joyeria.com',
        'rol' => 'publicidad',
        'sucursal_id' => null,
        'sucursal_nombre' => 'Todas',
        'foto_perfil' => null,
        'activo' => 0,
        'fecha_creacion' => '2024-05-20 14:00:00',
        'ultimo_acceso' => '2025-01-10 10:00:00'
    ]
];
?>

<!-- Contenido Principal -->
<div class="container-fluid main-content">
    <!-- Encabezado -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1>
                    <i class="bi bi-people"></i>
                    Gestión de Usuarios
                </h1>
                <p class="text-muted">Administración de usuarios del sistema</p>
            </div>
            <div class="col-md-6 text-end">
                <a href="agregar-usuario.php" class="btn btn-primary">
                    <i class="bi bi-person-plus"></i>
                    Nuevo Usuario
                </a>
            </div>
        </div>
    </div>

    <!-- Resumen de Usuarios -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card azul">
                <div class="stat-icon">
                    <i class="bi bi-people"></i>
                </div>
                <div class="stat-value">
                    <?php echo count($usuarios); ?>
                </div>
                <div class="stat-label">Total Usuarios</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card verde">
                <div class="stat-icon">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-value">
                    <?php echo count(array_filter($usuarios, fn($u) => $u['activo'] == 1)); ?>
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
                    <?php echo count(array_filter($usuarios, fn($u) => $u['activo'] == 0)); ?>
                </div>
                <div class="stat-label">Inactivos</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card dorado">
                <div class="stat-icon">
                    <i class="bi bi-shield-check"></i>
                </div>
                <div class="stat-value">
                    <?php echo count(array_filter($usuarios, fn($u) => in_array($u['rol'], ['administrador', 'dueño']))); ?>
                </div>
                <div class="stat-label">Administradores</div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Buscar</label>
                    <input type="text" class="form-control" id="searchInput" 
                           placeholder="Nombre o email...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Rol</label>
                    <select class="form-select" id="filterRol">
                        <option value="">Todos</option>
                        <option value="administrador">Administrador</option>
                        <option value="dueño">Dueño</option>
                        <option value="vendedor">Vendedor</option>
                        <option value="cajero">Cajero</option>
                        <option value="orfebre">Orfebre</option>
                        <option value="publicidad">Publicidad</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Estado</label>
                    <select class="form-select" id="filterEstado">
                        <option value="">Todos</option>
                        <option value="1">Activos</option>
                        <option value="0">Inactivos</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button class="btn btn-secondary w-100">
                        <i class="bi bi-x-circle"></i>
                        Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Usuarios -->
    <div class="card">
        <div class="card-header">
            <i class="bi bi-table"></i>
            Listado de Usuarios (<?php echo count($usuarios); ?>)
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Sucursal</th>
                            <th>Último Acceso</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td class="fw-bold"><?php echo $usuario['id']; ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                         style="width: 35px; height: 35px;">
                                        <i class="bi bi-person"></i>
                                    </div>
                                    <strong><?php echo $usuario['nombre']; ?></strong>
                                </div>
                            </td>
                            <td><?php echo $usuario['email']; ?></td>
                            <td>
                                <?php
                                $badge_roles = [
                                    'administrador' => 'bg-danger',
                                    'dueño' => 'bg-warning text-dark',
                                    'vendedor' => 'bg-primary',
                                    'cajero' => 'bg-info',
                                    'orfebre' => 'bg-success',
                                    'publicidad' => 'bg-secondary'
                                ];
                                ?>
                                <span class="badge <?php echo $badge_roles[$usuario['rol']]; ?>">
                                    <?php echo ucfirst($usuario['rol']); ?>
                                </span>
                            </td>
                            <td>
                                <small class="text-muted"><?php echo $usuario['sucursal_nombre']; ?></small>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?php echo date('d/m/Y H:i', strtotime($usuario['ultimo_acceso'])); ?>
                                </small>
                            </td>
                            <td>
                                <?php if ($usuario['activo']): ?>
                                    <span class="badge bg-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-info"
                                            data-bs-toggle="tooltip" 
                                            title="Ver detalles">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning"
                                            data-bs-toggle="tooltip" 
                                            title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-secondary"
                                            data-bs-toggle="tooltip" 
                                            title="Resetear contraseña">
                                        <i class="bi bi-key"></i>
                                    </button>
                                    <?php if ($usuario['id'] != $_SESSION['usuario_id']): ?>
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
    </div>
</div>

<!-- Modal Agregar/Editar Usuario -->
<div class="modal fade" id="modalUsuario" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-person-plus"></i>
                    Agregar Usuario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formUsuario">
                    <div class="mb-3">
                        <label class="form-label">Nombre Completo *</label>
                        <input type="text" class="form-control" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contraseña *</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rol *</label>
                        <select class="form-select" name="rol" required>
                            <option value="">Seleccione...</option>
                            <option value="administrador">Administrador</option>
                            <option value="dueño">Dueño</option>
                            <option value="vendedor">Vendedor</option>
                            <option value="cajero">Cajero</option>
                            <option value="orfebre">Orfebre</option>
                            <option value="publicidad">Publicidad</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sucursal</label>
                        <select class="form-select" name="sucursal_id">
                            <option value="">Sin sucursal específica</option>
                            <option value="1">Los Arcos</option>
                            <option value="2">Chinaca Central</option>
                        </select>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="activo" checked>
                        <label class="form-check-label">Usuario activo</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="guardarUsuario()">
                    <i class="bi bi-save"></i>
                    Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function guardarUsuario() {
    const formData = new FormData(document.getElementById('formUsuario'));
    
    // API: INSERT INTO usuarios (nombre, email, password, rol, sucursal_id, activo)
    // password debe ser hasheado con password_hash()
    
    console.log('Usuario a crear:', Object.fromEntries(formData));
    alert('Usuario creado exitosamente');
}
</script>

<?php
// Incluir footer
include '../../includes/footer.php';
?>