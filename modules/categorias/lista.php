<?php
/**
 * ================================================
 * MÓDULO CATEGORÍAS - LISTA
 * ================================================
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();
requiere_rol(['administrador', 'dueño', 'vendedor']);

require_once '../../includes/header.php';
require_once '../../includes/navbar.php';
?>

<div class="container-fluid px-4 py-4">
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-1">
                <i class="bi bi-tags"></i> Categorías
            </h2>
            <p class="text-muted mb-0">Gestión de categorías de productos</p>
        </div>
        <?php if (in_array($_SESSION['usuario_rol'], ['administrador', 'dueño'])): ?>
        <a href="agregar.php" class="btn btn-warning btn-lg">
            <i class="bi bi-plus-circle"></i> Nueva Categoría
        </a>
        <?php endif; ?>
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
                                <i class="bi bi-tags fs-2 text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0" id="totalCategorias">0</h3>
                            <p class="text-muted mb-0">Total Categorías</p>
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
                            <h3 class="mb-0" id="categoriasActivas">0</h3>
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
                            <h3 class="mb-0" id="categoriasInactivas">0</h3>
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
                <div class="col-md-6">
                    <label class="form-label">Buscar</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control" id="inputBuscar" 
                               placeholder="Nombre de categoría...">
                    </div>
                </div>

                <!-- Estado -->
                <div class="col-md-4">
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
                <i class="bi bi-table"></i> Listado de Categorías
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="tablaCategorias">
                    <thead class="table-dark">
                        <tr>
                            <th width="25%">Nombre</th>
                            <th width="15%">Tipo</th>
                            <th width="40%">Descripción</th>
                            <th width="10%">Estado</th>
                            <th width="10%" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaCategoriasBody">
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
let categoriasData = [];
let categoriasFiltradas = [];

// ============================================================================
// FUNCIONES PRINCIPALES
// ============================================================================

async function cargarCategorias() {
    try {
        mostrarCargando();
        
        const estado = document.getElementById('selectEstado').value;
        const filtros = {};
        
        if (estado !== '') {
            filtros.activo = estado;
        }
        
        const resultado = await api.listarCategorias(filtros);
        
        ocultarCargando();
        
        if (resultado.success) {
            categoriasData = resultado.data || [];
            categoriasFiltradas = [...categoriasData];
            
            actualizarEstadisticas();
            aplicarFiltros();
        } else {
            mostrarError('No se pudieron cargar las categorías');
        }
        
    } catch (error) {
        ocultarCargando();
        console.error('Error al cargar categorías:', error);
        mostrarError('Error al cargar categorías: ' + error.message);
        mostrarMensajeVacio('tablaCategorias', 'Error al cargar datos', 4);
    }
}

function actualizarEstadisticas() {
    const total = categoriasData.length;
    const activas = categoriasData.filter(c => c.activo == 1).length;
    const inactivas = total - activas;
    
    document.getElementById('totalCategorias').textContent = formatearNumero(total);
    document.getElementById('categoriasActivas').textContent = formatearNumero(activas);
    document.getElementById('categoriasInactivas').textContent = formatearNumero(inactivas);
}

function aplicarFiltros() {
    const buscar = document.getElementById('inputBuscar').value.toLowerCase().trim();
    
    categoriasFiltradas = categoriasData.filter(categoria => {
        if (buscar) {
            const nombre = (categoria.nombre || '').toLowerCase();
            const descripcion = (categoria.descripcion || '').toLowerCase();
            
            if (!nombre.includes(buscar) && !descripcion.includes(buscar)) {
                return false;
            }
        }
        
        return true;
    });
    
    mostrarCategorias();
}

function mostrarCategorias() {
    const tbody = document.getElementById('tablaCategoriasBody');
    
    if (categoriasFiltradas.length === 0) {
        mostrarMensajeVacio('tablaCategorias', 'No hay categorías para mostrar', 5);
        return;
    }
    
    const rolUsuario = '<?php echo $_SESSION["usuario_rol"] ?? ""; ?>';
    const puedeEditar = ['administrador', 'dueño'].includes(rolUsuario);
    
    let html = '';
    
    categoriasFiltradas.forEach(categoria => {
        const badgeEstado = categoria.activo == 1 
            ? '<span class="badge bg-success">Activa</span>'
            : '<span class="badge bg-secondary">Inactiva</span>';
        
        // Badge de tipo
        const badgeTipo = categoria.tipo_clasificacion === 'tipo'
            ? '<span class="badge bg-primary">Tipo</span>'
            : '<span class="badge bg-warning text-dark">Material</span>';
        
        const btnVer = `
            <a href="ver.php?id=${categoria.id}" 
               class="btn btn-sm btn-outline-info" 
               title="Ver">
                <i class="bi bi-eye"></i>
            </a>
        `;
        
        let botonesAdmin = '';
        if (puedeEditar) {
            const btnEditar = `
                <a href="editar.php?id=${categoria.id}" 
                   class="btn btn-sm btn-outline-warning" 
                   title="Editar">
                    <i class="bi bi-pencil"></i>
                </a>
            `;
            
            const btnEstado = categoria.activo == 1
                ? `<button class="btn btn-sm btn-outline-danger" 
                           onclick="cambiarEstado(${categoria.id}, 0)" 
                           title="Desactivar">
                       <i class="bi bi-x-circle"></i>
                   </button>`
                : `<button class="btn btn-sm btn-outline-success" 
                           onclick="cambiarEstado(${categoria.id}, 1)" 
                           title="Activar">
                       <i class="bi bi-check-circle"></i>
                   </button>`;
            
            botonesAdmin = btnEditar + ' ' + btnEstado;
        }
        
        html += `
            <tr>
                <td><strong>${escaparHTML(categoria.nombre || '')}</strong></td>
                <td>${badgeTipo}</td>
                <td>${escaparHTML(categoria.descripcion || '-')}</td>
                <td>${badgeEstado}</td>
                <td class="text-center">
                    <div class="btn-group btn-group-sm" role="group">
                        ${btnVer}
                        ${botonesAdmin}
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
        `¿Estás seguro de ${accion} esta categoría?`
    );
    
    if (!confirmacion) return;
    
    try {
        mostrarCargando();
        
        const resultado = await api.cambiarEstadoCategoria(id, nuevoEstado);
        
        ocultarCargando();
        
        if (resultado.success) {
            mostrarExito(`Categoría ${accion === 'activar' ? 'activada' : 'desactivada'} correctamente`);
            cargarCategorias();
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
    cargarCategorias();
}

// ============================================================================
// EVENT LISTENERS
// ============================================================================

document.addEventListener('DOMContentLoaded', function() {
    cargarCategorias();
    
    document.getElementById('inputBuscar').addEventListener('input', aplicarFiltros);
    document.getElementById('selectEstado').addEventListener('change', cargarCategorias);
    document.getElementById('btnLimpiar').addEventListener('click', limpiarFiltros);
});

console.log('✅ Vista de Categorías cargada correctamente');
</script>