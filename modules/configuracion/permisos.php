<?php
// ================================================
// MÓDULO CONFIGURACIÓN - ROLES Y PERMISOS
// ================================================

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();
requiere_rol(['administrador', 'dueño']);

$titulo_pagina = 'Roles y Permisos';
include '../../includes/header.php';
include '../../includes/navbar.php';

// Roles del sistema
$roles = [
    'administrador' => 'Administrador',
    'dueño' => 'Dueño',
    'vendedor' => 'Vendedor',
    'cajero' => 'Cajero',
    'orfebre' => 'Orfebre',
    'publicidad' => 'Publicidad/Marketing'
];

// Módulos y permisos
$modulos = [
    'dashboard' => ['nombre' => 'Dashboard', 'permisos' => ['ver']],
    'ventas' => ['nombre' => 'Ventas', 'permisos' => ['ver', 'crear', 'editar', 'eliminar']],
    'clientes' => ['nombre' => 'Clientes', 'permisos' => ['ver', 'crear', 'editar', 'eliminar']],
    'inventario' => ['nombre' => 'Inventario', 'permisos' => ['ver', 'crear', 'editar', 'eliminar']],
    'taller' => ['nombre' => 'Taller', 'permisos' => ['ver', 'crear', 'editar', 'eliminar']],
    'caja' => ['nombre' => 'Caja', 'permisos' => ['ver', 'crear', 'editar', 'eliminar']],
    'proveedores' => ['nombre' => 'Proveedores', 'permisos' => ['ver', 'crear', 'editar', 'eliminar']],
    'reportes' => ['nombre' => 'Reportes', 'permisos' => ['ver', 'exportar']],
    'configuracion' => ['nombre' => 'Configuración', 'permisos' => ['ver', 'editar']]
];

// Permisos actuales (matriz dummy)
$permisos_actuales = [
    'administrador' => [
        'dashboard' => ['ver'],
        'ventas' => ['ver', 'crear', 'editar', 'eliminar'],
        'clientes' => ['ver', 'crear', 'editar', 'eliminar'],
        'inventario' => ['ver', 'crear', 'editar', 'eliminar'],
        'taller' => ['ver', 'crear', 'editar', 'eliminar'],
        'caja' => ['ver', 'crear', 'editar', 'eliminar'],
        'proveedores' => ['ver', 'crear', 'editar', 'eliminar'],
        'reportes' => ['ver', 'exportar'],
        'configuracion' => ['ver', 'editar']
    ],
    'vendedor' => [
        'dashboard' => ['ver'],
        'ventas' => ['ver', 'crear'],
        'clientes' => ['ver', 'crear'],
        'inventario' => ['ver'],
        'taller' => ['ver', 'crear'],
        'caja' => [],
        'proveedores' => [],
        'reportes' => ['ver'],
        'configuracion' => []
    ]
];
?>

<div class="container-fluid main-content">
    <div class="page-header">
        <h1><i class="bi bi-shield-check"></i> Roles y Permisos</h1>
        <p class="text-muted">Configuración de permisos por rol</p>
    </div>

    <!-- Selector de Rol -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Seleccione un Rol para Configurar:</label>
                    <select class="form-select form-select-lg" id="rolSelect" onchange="cargarPermisos()">
                        <?php foreach ($roles as $key => $nombre): ?>
                        <option value="<?php echo $key; ?>"><?php echo $nombre; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 text-end">
                    <button class="btn btn-primary btn-lg" onclick="guardarPermisos()">
                        <i class="bi bi-save"></i>
                        Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Matriz de Permisos -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <i class="bi bi-grid"></i>
            Matriz de Permisos para Rol: <span id="rolNombre" class="fw-bold">Administrador</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 200px;">Módulo</th>
                        <th class="text-center">Ver</th>
                        <th class="text-center">Crear</th>
                        <th class="text-center">Editar</th>
                        <th class="text-center">Eliminar</th>
                        <th class="text-center">Exportar</th>
                    </tr>
                </thead>
                <tbody id="tablaPermisos">
                    <?php foreach ($modulos as $mod_key => $modulo): ?>
                    <tr>
                        <td class="fw-bold">
                            <i class="bi bi-folder"></i>
                            <?php echo $modulo['nombre']; ?>
                        </td>
                        <?php
                        $permisos_posibles = ['ver', 'crear', 'editar', 'eliminar', 'exportar'];
                        foreach ($permisos_posibles as $permiso):
                            $tiene_permiso = in_array($permiso, $modulo['permisos']);
                            $checked = isset($permisos_actuales['administrador'][$mod_key]) && 
                                      in_array($permiso, $permisos_actuales['administrador'][$mod_key]);
                        ?>
                        <td class="text-center">
                            <?php if ($tiene_permiso): ?>
                            <input type="checkbox" 
                                   class="form-check-input permiso-check" 
                                   data-modulo="<?php echo $mod_key; ?>" 
                                   data-permiso="<?php echo $permiso; ?>"
                                   <?php echo $checked ? 'checked' : ''; ?>>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <div class="row">
                <div class="col-md-6">
                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i>
                        Los cambios se aplicarán inmediatamente para todos los usuarios con este rol
                    </small>
                </div>
                <div class="col-md-6 text-end">
                    <button class="btn btn-sm btn-outline-success" onclick="seleccionarTodos()">
                        <i class="bi bi-check-all"></i>
                        Seleccionar Todo
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deseleccionarTodos()">
                        <i class="bi bi-x"></i>
                        Deseleccionar Todo
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Información Adicional -->
    <div class="row mt-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <i class="bi bi-info-circle"></i>
                    Descripción de Roles
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li><strong>Administrador:</strong> Acceso total al sistema</li>
                        <li><strong>Dueño:</strong> Acceso a reportes financieros y configuración</li>
                        <li><strong>Vendedor:</strong> Gestión de ventas y clientes</li>
                        <li><strong>Cajero:</strong> Manejo de caja y pagos</li>
                        <li><strong>Orfebre:</strong> Gestión de trabajos del taller</li>
                        <li><strong>Publicidad:</strong> Acceso a clientes para marketing</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <i class="bi bi-exclamation-triangle"></i>
                    Advertencias
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li>Los cambios en permisos son permanentes</li>
                        <li>Los usuarios en sesión deben cerrar y volver a iniciar sesión</li>
                        <li>No se puede quitar acceso total al rol Administrador</li>
                        <li>Se recomienda probar en un usuario de prueba antes</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Datos de permisos por rol (en producción esto vendría de la API)
const permisosData = <?php echo json_encode($permisos_actuales); ?>;

function cargarPermisos() {
    const rol = document.getElementById('rolSelect').value;
    document.getElementById('rolNombre').textContent = 
        document.getElementById('rolSelect').options[document.getElementById('rolSelect').selectedIndex].text;
    
    // Actualizar checkboxes según el rol seleccionado
    document.querySelectorAll('.permiso-check').forEach(checkbox => {
        const modulo = checkbox.dataset.modulo;
        const permiso = checkbox.dataset.permiso;
        
        if (permisosData[rol] && permisosData[rol][modulo]) {
            checkbox.checked = permisosData[rol][modulo].includes(permiso);
        } else {
            checkbox.checked = false;
        }
    });
}

function guardarPermisos() {
    const rol = document.getElementById('rolSelect').value;
    const permisos = {};
    
    document.querySelectorAll('.permiso-check:checked').forEach(checkbox => {
        const modulo = checkbox.dataset.modulo;
        const permiso = checkbox.dataset.permiso;
        
        if (!permisos[modulo]) {
            permisos[modulo] = [];
        }
        permisos[modulo].push(permiso);
    });
    
    console.log('Guardar permisos para rol:', rol);
    console.log('Permisos:', permisos);
    
    // API: UPDATE permisos SET ... WHERE rol = ?
    // O guardar en tabla roles_permisos
    
    alert('Permisos actualizados exitosamente para el rol: ' + 
          document.getElementById('rolSelect').options[document.getElementById('rolSelect').selectedIndex].text);
}

function seleccionarTodos() {
    document.querySelectorAll('.permiso-check').forEach(cb => cb.checked = true);
}

function deseleccionarTodos() {
    document.querySelectorAll('.permiso-check').forEach(cb => cb.checked = false);
}
</script>

<?php include '../../includes/footer.php'; ?>