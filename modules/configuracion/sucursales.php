<?php
/**
 * ================================================
 * MÓDULO SUCURSALES - LISTA
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
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-1">
                <i class="bi bi-building"></i> Gestión de Sucursales
            </h2>
            <p class="text-muted mb-0">Administración de sucursales</p>
        </div>
        <a href="agregar.php" class="btn btn-warning btn-lg">
            <i class="bi bi-plus-circle"></i> Nueva Sucursal
        </a>
    </div>

    <hr class="border-warning border-2 opacity-75 mb-4">

    <!-- Cards de Estadísticas -->
    <div class="row g-3 mb-4">
        <!-- Total -->
        <div class="col-md-4">
            <div class="card border-start border-primary border-4 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-light rounded-3 p-3">
                                <i class="bi bi-building fs-2 text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0" id="totalSucursales">0</h3>
                            <p class="text-muted mb-0">Total Sucursales</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activas -->
        <div class="col-md-4">
            <div class="card border-start border-success border-4 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-light rounded-3 p-3">
                                <i class="bi bi-check-circle fs-2 text-success"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0" id="sucursalesActivas">0</h3>
                            <p class="text-muted mb-0">Activas</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inactivas -->
        <div class="col-md-4">
            <div class="card border-start border-danger border-4 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-light rounded-3 p-3">
                                <i class="bi bi-x-circle fs-2 text-danger"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0" id="sucursalesInactivas">0</h3>
                            <p class="text-muted mb-0">Inactivas</p>
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
                <!-- Búsqueda -->
                <div class="col-md-8">
                    <label class="form-label">Buscar</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control" id="inputBuscar" 
                               placeholder="Nombre, dirección...">
                    </div>
                </div>

                <!-- Estado -->
                <div class="col-md-2">
                    <label class="form-label">Estado</label>
                    <select class="form-select" id="selectEstado">
                        <option value="" selected>Todos</option>
                        <option value="1">Activos</option>
                        <option value="0">Inactivos</option>
                    </select>
                </div>

                <!-- Botón Limpiar -->
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
            <h5 class="mb-0">
                <i class="bi bi-table"></i> Listado de Sucursales
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="tablaSucursales">
                    <thead class="table-dark">
                        <tr>
                            <th width="25%">Nombre</th>
                            <th width="30%">Dirección</th>
                            <th width="12%">Teléfono</th>
                            <th width="18%">Email</th>
                            <th width="8%">Estado</th>
                            <th width="7%" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaSucursalesBody">
                        <!-- Datos dinámicos -->
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
// ============================================================================
// VARIABLES GLOBALES
// ============================================================================
let sucursalesData = [];
let sucursalesFiltradas = [];

// ============================================================================
// FUNCIONES PRINCIPALES
// ============================================================================

async function cargarSucursales() {
    try {
        mostrarCargando();
        
        const estado = document.getElementById('selectEstado').value;
        const filtros = {};
        
        if (estado !== '') {
            filtros.activo = estado;
        }
        
        const resultado = await api.listarSucursales(filtros);
        
        ocultarCargando();
        
        if (resultado.success) {
            sucursalesData = resultado.data || [];
            sucursalesFiltradas = [...sucursalesData];
            
            actualizarEstadisticas();
            aplicarFiltros();
        } else {
            mostrarError('No se pudieron cargar las sucursales');
        }
        
    } catch (error) {
        ocultarCargando();
        console.error('Error al cargar sucursales:', error);
        mostrarError('Error al cargar sucursales: ' + error.message);
        mostrarMensajeVacio('tablaSucursales', 'Error al cargar datos', 6);
    }
}

function actualizarEstadisticas() {
    const total = sucursalesData.length;
    const activas = sucursalesData.filter(s => s.activo == 1).length;
    const inactivas = total - activas;
    
    document.getElementById('totalSucursales').textContent = formatearNumero(total);
    document.getElementById('sucursalesActivas').textContent = formatearNumero(activas);
    document.getElementById('sucursalesInactivas').textContent = formatearNumero(inactivas);
}

function aplicarFiltros() {
    const buscar = document.getElementById('inputBuscar').value.toLowerCase().trim();
    
    sucursalesFiltradas = sucursalesData.filter(sucursal => {
        if (buscar) {
            const nombre = (sucursal.nombre || '').toLowerCase();
            const direccion = (sucursal.direccion || '').toLowerCase();
            
            if (!nombre.includes(buscar) && !direccion.includes(buscar)) {
                return false;
            }
        }
        
        return true;
    });
    
    mostrarSucursales();
}

function mostrarSucursales() {
    const tbody = document.getElementById('tablaSucursalesBody');
    
    if (sucursalesFiltradas.length === 0) {
        mostrarMensajeVacio('tablaSucursales', 'No hay sucursales para mostrar', 6);
        return;
    }
    
    let html = '';
    
    sucursalesFiltradas.forEach(sucursal => {
        const badgeEstado = sucursal.activo == 1 
            ? '<span class="badge bg-success">Activa</span>'
            : '<span class="badge bg-secondary">Inactiva</span>';
        
        const btnVer = `
            <a href="ver.php?id=${sucursal.id}" 
               class="btn btn-sm btn-outline-info" 
               title="Ver">
                <i class="bi bi-eye"></i>
            </a>
        `;
        
        const btnEditar = `
            <a href="editar.php?id=${sucursal.id}" 
               class="btn btn-sm btn-outline-warning" 
               title="Editar">
                <i class="bi bi-pencil"></i>
            </a>
        `;
        
        const btnEstado = sucursal.activo == 1
            ? `<button class="btn btn-sm btn-outline-danger" 
                       onclick="cambiarEstado(${sucursal.id}, 0)" 
                       title="Desactivar">
                   <i class="bi bi-x-circle"></i>
               </button>`
            : `<button class="btn btn-sm btn-outline-success" 
                       onclick="cambiarEstado(${sucursal.id}, 1)" 
                       title="Activar">
                   <i class="bi bi-check-circle"></i>
               </button>`;
        
        html += `
            <tr>
                <td><strong>${escaparHTML(sucursal.nombre || '')}</strong></td>
                <td>${escaparHTML(sucursal.direccion || '-')}</td>
                <td>${escaparHTML(sucursal.telefono || '-')}</td>
                <td>${escaparHTML(sucursal.email || '-')}</td>
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

async function cambiarEstado(id, nuevoEstado) {
    const accion = nuevoEstado == 1 ? 'activar' : 'desactivar';
    const confirmacion = await confirmarAccion(
        `¿Estás seguro de ${accion} esta sucursal?`
    );
    
    if (!confirmacion) return;
    
    try {
        mostrarCargando();
        
        const resultado = await api.cambiarEstadoSucursal(id, nuevoEstado);
        
        ocultarCargando();
        
        if (resultado.success) {
            mostrarExito(`Sucursal ${accion === 'activar' ? 'activada' : 'desactivada'} correctamente`);
            cargarSucursales();
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
    cargarSucursales();
}

// ============================================================================
// EVENT LISTENERS
// ============================================================================

document.addEventListener('DOMContentLoaded', function() {
    cargarSucursales();
    
    document.getElementById('inputBuscar').addEventListener('input', aplicarFiltros);
    document.getElementById('selectEstado').addEventListener('change', cargarSucursales);
    document.getElementById('btnLimpiar').addEventListener('click', limpiarFiltros);
});

console.log('✅ Vista de Sucursales cargada correctamente');
</script>