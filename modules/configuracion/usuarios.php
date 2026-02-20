<?php
/**
 * ================================================
 * MÓDULO USUARIOS - LISTA
 * ================================================
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();
requiere_rol(['administrador', 'dueño']);

require_once '../../includes/header.php';
require_once '../../includes/navbar.php';
?>

<div class="container-fluid px-4 py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-1"><i class="bi bi-people"></i> Gestión de Usuarios</h2>
            <p class="text-muted mb-0">Administración de usuarios del sistema</p>
        </div>
        <a href="agregar-usuario.php" class="btn btn-warning btn-lg">
            <i class="bi bi-plus-circle"></i> Nuevo Usuario
        </a>
    </div>

    <hr class="border-warning border-2 opacity-75 mb-4">

    <!-- Cards de Estadísticas -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-start border-primary border-4 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-light rounded-3 p-3">
                                <i class="bi bi-people fs-2 text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0" id="totalUsuarios">0</h3>
                            <p class="text-muted mb-0">Total Usuarios</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-start border-success border-4 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-light rounded-3 p-3">
                                <i class="bi bi-check-circle fs-2 text-success"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0" id="usuariosActivos">0</h3>
                            <p class="text-muted mb-0">Activos</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-start border-danger border-4 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-light rounded-3 p-3">
                                <i class="bi bi-x-circle fs-2 text-danger"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0" id="usuariosInactivos">0</h3>
                            <p class="text-muted mb-0">Inactivos</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-start border-warning border-4 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-light rounded-3 p-3">
                                <i class="bi bi-shield-check fs-2 text-warning"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0" id="administradores">0</h3>
                            <p class="text-muted mb-0">Administradores</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Buscar</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" id="inputBuscar" 
                               placeholder="Nombre o email...">
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Rol</label>
                    <select class="form-select" id="selectRol">
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
                    <select class="form-select" id="selectEstado">
                        <option value="" selected>Todos</option>
                        <option value="1">Activos</option>
                        <option value="0">Inactivos</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-primary w-100" id="btnLimpiar">
                        <i class="bi bi-x-circle"></i> Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-table"></i> Listado de Usuarios (0)</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="tablaUsuarios">
                    <thead class="table-dark">
                        <tr>
                            <th width="5%">#</th>
                            <th width="25%">Usuario</th>
                            <th width="25%">Email</th>
                            <th width="15%">Rol</th>
                            <th width="15%">Sucursal</th>
                            <th width="10%">Estado</th>
                            <th width="5%" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaUsuariosBody">
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php require_once '../../includes/footer.php'; ?>

<script src="../../assets/js/vendors/sweetalert2/sweetalert2.all.min.js"></script>
<script src="../../assets/js/common.js"></script>
<script src="../../assets/js/api-client.js"></script>

<script>
let usuariosData = [];
let usuariosFiltrados = [];

async function cargarUsuarios() {
    try {
        mostrarCargando();
        
        const estado = document.getElementById('selectEstado').value;
        const rol = document.getElementById('selectRol').value;
        const filtros = {};
        
        if (estado !== '') filtros.activo = estado;
        if (rol) filtros.rol = rol;
        
        const resultado = await api.listarUsuarios(filtros);
        
        ocultarCargando();
        
        if (resultado.success) {
            usuariosData = resultado.data || [];
            usuariosFiltrados = [...usuariosData];
            
            actualizarEstadisticas();
            aplicarFiltros();
        } else {
            mostrarError('No se pudieron cargar los usuarios');
        }
        
    } catch (error) {
        ocultarCargando();
        console.error('Error:', error);
        mostrarError('Error: ' + error.message);
        mostrarMensajeVacio('tablaUsuarios', 'Error al cargar datos', 7);
    }
}

function actualizarEstadisticas() {
    const total = usuariosData.length;
    const activos = usuariosData.filter(u => u.activo == 1).length;
    const inactivos = total - activos;
    const admins = usuariosData.filter(u => u.rol === 'administrador').length;
    
    document.getElementById('totalUsuarios').textContent = formatearNumero(total);
    document.getElementById('usuariosActivos').textContent = formatearNumero(activos);
    document.getElementById('usuariosInactivos').textContent = formatearNumero(inactivos);
    document.getElementById('administradores').textContent = formatearNumero(admins);
    
    document.querySelector('.card-header h5').textContent = `Listado de Usuarios (${usuariosFiltrados.length})`;
}

function aplicarFiltros() {
    const buscar = document.getElementById('inputBuscar').value.toLowerCase().trim();
    
    usuariosFiltrados = usuariosData.filter(usuario => {
        if (buscar) {
            const nombre = (usuario.nombre || '').toLowerCase();
            const email = (usuario.email || '').toLowerCase();
            
            if (!nombre.includes(buscar) && !email.includes(buscar)) {
                return false;
            }
        }
        
        return true;
    });
    
    mostrarUsuarios();
    actualizarEstadisticas();
}

function mostrarUsuarios() {
    const tbody = document.getElementById('tablaUsuariosBody');
    
    if (usuariosFiltrados.length === 0) {
        mostrarMensajeVacio('tablaUsuarios', 'No hay usuarios para mostrar', 7);
        return;
    }
    
    let html = '';
    
    usuariosFiltrados.forEach((usuario, index) => {
        const badgeEstado = usuario.activo == 1 
            ? '<span class="badge bg-success">Activo</span>'
            : '<span class="badge bg-secondary">Inactivo</span>';
        
        const badgeRol = obtenerBadgeRol(usuario.rol);
        
        const btnVer = `
            <a href="ver-usuario.php?id=${usuario.id}" 
               class="btn btn-sm btn-outline-info" 
               title="Ver">
                <i class="bi bi-eye"></i>
            </a>
        `;
        
        const btnEditar = `
            <a href="editar-usuario.php?id=${usuario.id}" 
               class="btn btn-sm btn-outline-warning" 
               title="Editar">
                <i class="bi bi-pencil"></i>
            </a>
        `;
        
        const btnEstado = usuario.activo == 1
            ? `<button class="btn btn-sm btn-outline-danger" 
                       onclick="cambiarEstado(${usuario.id}, 0)" 
                       title="Desactivar">
                   <i class="bi bi-x-circle"></i>
               </button>`
            : `<button class="btn btn-sm btn-outline-success" 
                       onclick="cambiarEstado(${usuario.id}, 1)" 
                       title="Activar">
                   <i class="bi bi-check-circle"></i>
               </button>`;
        
        html += `
            <tr>
                <td>${index + 1}</td>
                <td><strong>${escaparHTML(usuario.nombre || '')}</strong></td>
                <td>${escaparHTML(usuario.email || '-')}</td>
                <td>${badgeRol}</td>
                <td>${escaparHTML(usuario.sucursal_nombre || '-')}</td>
                <td>${badgeEstado}</td>
                <td class="text-center">
                    <div class="btn-group btn-group-sm" role="group">
                        ${btnVer}
                        ${btnEditar}
                        ${btnEstado}
                    </div>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

function obtenerBadgeRol(rol) {
    const badges = {
        'administrador': '<span class="badge bg-danger">Administrador</span>',
        'dueño': '<span class="badge bg-warning text-dark">Dueño</span>',
        'vendedor': '<span class="badge bg-primary">Vendedor</span>',
        'cajero': '<span class="badge bg-success">Cajero</span>',
        'orfebre': '<span class="badge bg-info">Orfebre</span>',
        'publicidad': '<span class="badge bg-secondary">Publicidad</span>'
    };
    
    return badges[rol] || `<span class="badge bg-light text-dark">${escaparHTML(rol)}</span>`;
}

async function cambiarEstado(id, nuevoEstado) {
    const accion = nuevoEstado == 1 ? 'activar' : 'desactivar';
    const confirmacion = await confirmarAccion(`¿Estás seguro de ${accion} este usuario?`);
    
    if (!confirmacion) return;
    
    try {
        mostrarCargando();
        
        const resultado = await api.cambiarEstadoUsuario(id, nuevoEstado);
        
        ocultarCargando();
        
        if (resultado.success) {
            mostrarExito(`Usuario ${accion === 'activar' ? 'activado' : 'desactivado'} correctamente`);
            cargarUsuarios();
        } else {
            mostrarError(resultado.message || 'Error al cambiar estado');
        }
        
    } catch (error) {
        ocultarCargando();
        console.error('Error:', error);
        mostrarError('Error: ' + error.message);
    }
}

function limpiarFiltros() {
    document.getElementById('inputBuscar').value = '';
    document.getElementById('selectEstado').value = '';
    document.getElementById('selectRol').value = '';
    cargarUsuarios();
}

document.addEventListener('DOMContentLoaded', function() {
    cargarUsuarios();
    
    document.getElementById('inputBuscar').addEventListener('input', aplicarFiltros);
    document.getElementById('selectEstado').addEventListener('change', cargarUsuarios);
    document.getElementById('selectRol').addEventListener('change', cargarUsuarios);
    document.getElementById('btnLimpiar').addEventListener('click', limpiarFiltros);
});

console.log('✅ Vista de Usuarios cargada correctamente');
</script>