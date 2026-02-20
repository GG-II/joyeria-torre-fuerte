<?php
/**
 * ================================================
 * MÓDULO PROVEEDORES - LISTA
 * ================================================
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();
requiere_rol(['administrador', 'dueño', 'vendedor']);

// Incluir header y navbar
require_once '../../includes/header.php';
require_once '../../includes/navbar.php';
?>

<!-- Contenedor Principal -->
<div class="container-fluid px-4 py-4">
    
    <!-- Header con Título y Botón -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-1">
                <i class="bi bi-truck"></i> Proveedores
            </h2>
            <p class="text-muted mb-0">Gestión de proveedores y suministros</p>
        </div>
        <a href="agregar.php" class="btn btn-warning btn-lg">
            <i class="bi bi-plus-circle"></i> Nuevo Proveedor
        </a>
    </div>

    <!-- Línea Separadora Dorada -->
    <hr class="border-warning border-2 opacity-75 mb-4">

    <!-- Cards de Estadísticas -->
    <div class="row g-3 mb-4">
        <!-- Total Proveedores -->
        <div class="col-md-3">
            <div class="card border-start border-secondary border-4 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-light rounded-3 p-3">
                                <i class="bi bi-building fs-2 text-secondary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0" id="totalProveedores">0</h3>
                            <p class="text-muted mb-0">Total Proveedores</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activos -->
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
                            <h3 class="mb-0" id="proveedoresActivos">0</h3>
                            <p class="text-muted mb-0">Activos</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inactivos -->
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
                            <h3 class="mb-0" id="proveedoresInactivos">0</h3>
                            <p class="text-muted mb-0">Inactivos</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Compras Totales -->
        <div class="col-md-3">
            <div class="card border-start border-warning border-4 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-light rounded-3 p-3">
                                <i class="bi bi-cart-check fs-2 text-warning"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0" id="comprasTotales">0</h3>
                            <p class="text-muted mb-0">Compras Totales</p>
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
                <div class="col-md-4">
                    <label class="form-label">Buscar</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control" id="inputBuscar" 
                               placeholder="Nombre, empresa...">
                    </div>
                </div>

                <!-- Estado -->
                <div class="col-md-3">
                    <label class="form-label">Estado</label>
                    <select class="form-select" id="selectEstado">
                        <option value="">Todos</option>
                        <option value="1" selected>Activos</option>
                        <option value="0">Inactivos</option>
                    </select>
                </div>

                <!-- Productos -->
                <div class="col-md-3">
                    <label class="form-label">Productos</label>
                    <input type="text" class="form-control" id="inputProductos" 
                           placeholder="Ej: oro, plata...">
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

    <!-- Tabla de Proveedores -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="bi bi-table"></i> Listado de Proveedores
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="tablaProveedores">
                    <thead class="table-dark">
                        <tr>
                            <th width="25%">Nombre</th>
                            <th width="18%">Empresa</th>
                            <th width="15%">Contacto</th>
                            <th width="12%">Teléfono</th>
                            <th width="18%">Productos Suministra</th>
                            <th width="8%">Estado</th>
                            <th width="4%" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaProveedoresBody">
                        <!-- Los datos se cargan con JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php require_once '../../includes/footer.php'; ?>

<!-- Scripts -->
<script src="../../assets/js/vendors/sweetalert2/sweetalert2.all.min.js"></script>
<script src="../../assets/js/common.js"></script>
<script src="../../assets/js/api-client.js"></script>

<script>
// ============================================================================
// VARIABLES GLOBALES
// ============================================================================
let proveedoresData = []; // Almacena todos los proveedores
let proveedoresFiltrados = []; // Almacena proveedores después de filtrar

// ============================================================================
// FUNCIONES PRINCIPALES
// ============================================================================

/**
 * Cargar proveedores desde la API
 */
async function cargarProveedores() {
    try {
        mostrarCargando();
        
        // Obtener filtro de estado
        const estado = document.getElementById('selectEstado').value;
        const filtros = {};
        
        if (estado !== '') {
            filtros.activo = estado;
        }
        
        // Llamar a la API
        const resultado = await api.listarProveedores(filtros);
        
        ocultarCargando();
        
        if (resultado.success) {
            proveedoresData = resultado.data || [];
            proveedoresFiltrados = [...proveedoresData];
            
            actualizarEstadisticas();
            aplicarFiltros();
        } else {
            mostrarError('No se pudieron cargar los proveedores');
        }
        
    } catch (error) {
        ocultarCargando();
        console.error('Error al cargar proveedores:', error);
        mostrarError('Error al cargar proveedores: ' + error.message);
        
        // Mostrar tabla vacía
        mostrarMensajeVacio('tablaProveedores', 'Error al cargar datos', 8);
    }
}

/**
 * Actualizar estadísticas en los cards
 */
function actualizarEstadisticas() {
    const total = proveedoresData.length;
    const activos = proveedoresData.filter(p => p.activo == 1).length;
    const inactivos = total - activos;
    
    document.getElementById('totalProveedores').textContent = formatearNumero(total);
    document.getElementById('proveedoresActivos').textContent = formatearNumero(activos);
    document.getElementById('proveedoresInactivos').textContent = formatearNumero(inactivos);
    
    // Compras totales (placeholder por ahora)
    document.getElementById('comprasTotales').textContent = '0';
}

/**
 * Aplicar filtros a los proveedores
 */
function aplicarFiltros() {
    const buscar = document.getElementById('inputBuscar').value.toLowerCase().trim();
    const productos = document.getElementById('inputProductos').value.toLowerCase().trim();
    
    proveedoresFiltrados = proveedoresData.filter(proveedor => {
        // Filtro de búsqueda (nombre o empresa)
        if (buscar) {
            const nombre = (proveedor.nombre || '').toLowerCase();
            const empresa = (proveedor.empresa || '').toLowerCase();
            const contacto = (proveedor.contacto || '').toLowerCase();
            
            if (!nombre.includes(buscar) && !empresa.includes(buscar) && !contacto.includes(buscar)) {
                return false;
            }
        }
        
        // Filtro de productos
        if (productos) {
            const productosSuministra = (proveedor.productos_suministra || '').toLowerCase();
            if (!productosSuministra.includes(productos)) {
                return false;
            }
        }
        
        return true;
    });
    
    mostrarProveedores();
}

/**
 * Mostrar proveedores en la tabla
 */
function mostrarProveedores() {
    const tbody = document.getElementById('tablaProveedoresBody');
    
    if (proveedoresFiltrados.length === 0) {
        mostrarMensajeVacio('tablaProveedores', 'No hay proveedores para mostrar', 7);
        return;
    }
    
    // Obtener rol del usuario desde sesión PHP
    const rolUsuario = '<?php echo $_SESSION["usuario_rol"] ?? ""; ?>';
    const puedeEditar = ['administrador', 'dueño'].includes(rolUsuario);
    
    let html = '';
    
    proveedoresFiltrados.forEach(proveedor => {
        const badgeEstado = proveedor.activo == 1 
            ? '<span class="badge bg-success">Activo</span>'
            : '<span class="badge bg-secondary">Inactivo</span>';
        
        // Botón VER (para todos los usuarios)
        const btnVer = `
            <a href="ver.php?id=${proveedor.id}" 
               class="btn btn-sm btn-outline-info" 
               title="Ver Detalles">
                <i class="bi bi-eye"></i>
            </a>
        `;
        
        // Botones EDITAR y ESTADO (solo para admin y dueño)
        let botonesAdmin = '';
        if (puedeEditar) {
            const btnEditar = `
                <a href="editar.php?id=${proveedor.id}" 
                   class="btn btn-sm btn-outline-warning" 
                   title="Editar">
                    <i class="bi bi-pencil"></i>
                </a>
            `;
            
            const btnEstado = proveedor.activo == 1
                ? `<button class="btn btn-sm btn-outline-danger" 
                           onclick="cambiarEstado(${proveedor.id}, 0)" 
                           title="Desactivar">
                       <i class="bi bi-x-circle"></i>
                   </button>`
                : `<button class="btn btn-sm btn-outline-success" 
                           onclick="cambiarEstado(${proveedor.id}, 1)" 
                           title="Activar">
                       <i class="bi bi-check-circle"></i>
                   </button>`;
            
            botonesAdmin = btnEditar + ' ' + btnEstado;
        }
        
        html += `
            <tr>
                <td><strong>${escaparHTML(proveedor.nombre || '')}</strong></td>
                <td>${escaparHTML(proveedor.empresa || '-')}</td>
                <td>${escaparHTML(proveedor.contacto || '-')}</td>
                <td>${escaparHTML(proveedor.telefono || '-')}</td>
                <td>
                    <small class="text-muted">
                        ${escaparHTML(proveedor.productos_suministra || '-')}
                    </small>
                </td>
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

/**
 * Cambiar estado de proveedor (activar/desactivar)
 */
async function cambiarEstado(id, nuevoEstado) {
    const accion = nuevoEstado == 1 ? 'activar' : 'desactivar';
    const confirmacion = await confirmarAccion(
        `¿Estás seguro de ${accion} este proveedor?`,
        `Confirmar ${accion}`
    );
    
    if (!confirmacion) return;
    
    try {
        mostrarCargando();
        
        const resultado = await api.cambiarEstadoProveedor(id, nuevoEstado);
        
        ocultarCargando();
        
        if (resultado.success) {
            mostrarExito(`Proveedor ${accion === 'activar' ? 'activado' : 'desactivado'} correctamente`);
            cargarProveedores(); // Recargar lista
        } else {
            mostrarError(resultado.message || 'Error al cambiar estado');
        }
        
    } catch (error) {
        ocultarCargando();
        console.error('Error al cambiar estado:', error);
        mostrarError('Error al cambiar estado: ' + error.message);
    }
}

/**
 * Limpiar todos los filtros
 */
function limpiarFiltros() {
    document.getElementById('inputBuscar').value = '';
    document.getElementById('selectEstado').value = '1'; // Activos por defecto
    document.getElementById('inputProductos').value = '';
    
    cargarProveedores();
}

// ============================================================================
// EVENT LISTENERS
// ============================================================================

document.addEventListener('DOMContentLoaded', function() {
    // Cargar proveedores al iniciar
    cargarProveedores();
    
    // Búsqueda en tiempo real
    document.getElementById('inputBuscar').addEventListener('input', aplicarFiltros);
    
    // Filtro de productos
    document.getElementById('inputProductos').addEventListener('input', aplicarFiltros);
    
    // Cambio de estado (select)
    document.getElementById('selectEstado').addEventListener('change', cargarProveedores);
    
    // Botón limpiar
    document.getElementById('btnLimpiar').addEventListener('click', limpiarFiltros);
});

// ============================================================================
// LOG DE INICIALIZACIÓN
// ============================================================================
console.log('✅ Vista de Proveedores cargada correctamente');
</script>