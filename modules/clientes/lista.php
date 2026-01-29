<?php
/**
 * ================================================
 * MÓDULO CLIENTES - LISTA
 * ================================================
 * 
 * Vista de listado de clientes con filtros y búsqueda.
 * 
 * TODO FASE 5: Conectar con API
 * GET /api/clientes/lista.php
 * 
 * Parámetros opcionales:
 * - buscar: texto de búsqueda
 * - tipo: publico|mayorista
 * - estado: 0|1
 * 
 * Respuesta esperada:
 * {
 *   "success": true,
 *   "data": [...],
 *   "total": 0
 * }
 */

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

// TODO FASE 5: Los datos se cargarán vía API
$clientes = [];
?>

<!-- Contenido Principal -->
<div class="container-fluid main-content">
    <!-- Encabezado de Página -->
    <div class="page-header mb-4">
        <div class="row align-items-center g-3">
            <div class="col-md-6">
                <h1 class="mb-2">
                    <i class="bi bi-people"></i>
                    Clientes
                </h1>
                <p class="text-muted mb-0">Gestión de clientes y cuentas por cobrar</p>
            </div>
            <div class="col-md-6 text-md-end">
                <?php if (tiene_permiso('clientes', 'crear')): ?>
                <a href="agregar.php" class="btn btn-primary btn-lg">
                    <i class="bi bi-person-plus"></i>
                    <span class="d-none d-sm-inline">Nuevo Cliente</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Filtros y Búsqueda -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Buscar Cliente</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white">
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
                    <label class="form-label d-none d-md-block">&nbsp;</label>
                    <button class="btn btn-secondary w-100" onclick="limpiarFiltros()">
                        <i class="bi bi-x-circle"></i>
                        <span class="d-md-none ms-2">Limpiar</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Clientes -->
    <div class="card shadow-sm">
        <div class="card-header" style="background-color: #1e3a8a; color: white;">
            <i class="bi bi-table"></i>
            <span id="tituloTabla">Listado de Clientes</span>
        </div>
        <div class="card-body p-0">
            <!-- Estado de carga -->
            <div id="loadingTable" class="text-center py-5">
                <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
                <p class="mt-3 text-muted">Cargando clientes...</p>
            </div>

            <!-- Tabla -->
            <div id="tableContainer" class="table-responsive" style="display: none;">
                <table class="table table-hover mb-0" id="tablaClientes">
                    <thead style="background-color: #1e3a8a; color: white;">
                        <tr>
                            <th class="d-none d-xl-table-cell">#</th>
                            <th>Cliente</th>
                            <th class="d-none d-md-table-cell">NIT</th>
                            <th class="d-none d-lg-table-cell">Contacto</th>
                            <th class="d-none d-md-table-cell">Tipo</th>
                            <th class="d-none d-xl-table-cell">Mercaderías</th>
                            <th>Crédito</th>
                            <th class="d-none d-sm-table-cell">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="clientesBody">
                        <!-- Se llenará dinámicamente -->
                    </tbody>
                </table>
            </div>

            <!-- Sin resultados -->
            <div id="noResults" class="text-center py-5" style="display: none;">
                <i class="bi bi-inbox" style="font-size: 48px; opacity: 0.3;"></i>
                <p class="mt-3 text-muted">No se encontraron clientes</p>
            </div>
        </div>
        <div class="card-footer" id="tableFooter" style="display: none;">
            <div class="row align-items-center g-2">
                <div class="col-md-6">
                    <small class="text-muted" id="contadorClientes">
                        Mostrando 0 clientes
                    </small>
                </div>
                <div class="col-md-6 text-md-end">
                    <!-- Paginación aquí cuando se conecte -->
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* ============================================
   ESTILOS ESPECÍFICOS LISTA CLIENTES
   ============================================ */

/* Contenedor principal */
.main-content {
    padding: 20px;
    min-height: calc(100vh - 120px);
}

/* Page header */
.page-header h1 {
    font-size: 1.75rem;
    font-weight: 600;
    color: #1a1a1a;
}

/* Cards */
.shadow-sm {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08) !important;
}

/* Avatar en tabla */
.user-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 600;
    flex-shrink: 0;
}

/* Tabla */
table thead th {
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    padding: 12px;
}

table tbody td {
    padding: 12px;
    vertical-align: middle;
}

/* Badges */
.badge {
    padding: 0.35em 0.65em;
    font-size: 0.85em;
}

.bg-dorado {
    background-color: #d4af37 !important;
}

/* Botones de acción */
.btn-group .btn {
    margin: 0;
}

/* ============================================
   RESPONSIVE - MOBILE FIRST
   ============================================ */

/* Móvil (< 576px) */
@media (max-width: 575.98px) {
    .main-content {
        padding: 15px 10px;
    }
    
    .page-header h1 {
        font-size: 1.5rem;
    }
    
    .user-avatar {
        width: 32px;
        height: 32px;
        font-size: 12px;
    }
    
    /* Tabla más compacta */
    table {
        font-size: 0.85rem;
    }
    
    table thead th,
    table tbody td {
        padding: 8px 6px;
    }
    
    /* Botones solo iconos */
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
    }
    
    /* Card body sin padding */
    .card-body {
        padding: 15px;
    }
}

/* Tablet (576px - 767.98px) */
@media (min-width: 576px) and (max-width: 767.98px) {
    .main-content {
        padding: 18px 15px;
    }
}

/* Desktop (992px+) */
@media (min-width: 992px) {
    .main-content {
        padding: 25px 30px;
    }
}

/* Touch targets */
@media (max-width: 767.98px) {
    .btn,
    .form-control,
    .form-select {
        min-height: 44px;
    }
}

/* Card header */
.card-header {
    font-weight: 600;
    padding: 12px 20px;
}

/* Animaciones */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

#clientesBody tr {
    animation: fadeIn 0.3s ease;
}
</style>

<script>
/**
 * ================================================
 * JAVASCRIPT - LISTA DE CLIENTES
 * ================================================
 */

// Cargar datos al iniciar
document.addEventListener('DOMContentLoaded', function() {
    cargarClientes();
    
    // Event listeners para filtros
    document.getElementById('searchInput').addEventListener('input', aplicarFiltros);
    document.getElementById('filterTipo').addEventListener('change', aplicarFiltros);
    document.getElementById('filterEstado').addEventListener('change', aplicarFiltros);
});

/**
 * Cargar clientes desde API
 * TODO FASE 5: Conectar con API real
 */
function cargarClientes() {
    // TODO FASE 5: Descomentar y conectar
    /*
    const params = new URLSearchParams({
        buscar: document.getElementById('searchInput').value,
        tipo: document.getElementById('filterTipo').value,
        estado: document.getElementById('filterEstado').value
    });
    
    fetch(`<?php echo BASE_URL; ?>api/clientes/lista.php?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderizarClientes(data.data);
            } else {
                mostrarError(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarError('Error al cargar los clientes');
        });
    */
    
    // Simular carga
    setTimeout(() => {
        document.getElementById('loadingTable').style.display = 'none';
        document.getElementById('noResults').style.display = 'block';
        document.getElementById('noResults').innerHTML = `
            <i class="bi bi-database" style="font-size: 48px; opacity: 0.3;"></i>
            <p class="mt-3 text-muted">MODO DESARROLLO: Esperando conexión con API</p>
        `;
    }, 1500);
}

/**
 * Renderizar clientes en la tabla
 */
function renderizarClientes(clientes) {
    const tbody = document.getElementById('clientesBody');
    
    if (clientes.length === 0) {
        document.getElementById('loadingTable').style.display = 'none';
        document.getElementById('noResults').style.display = 'block';
        return;
    }
    
    let html = '';
    
    clientes.forEach(cliente => {
        const inicial = cliente.nombre.charAt(0).toUpperCase();
        const creditoDisponible = parseFloat(cliente.limite_credito) - parseFloat(cliente.saldo_creditos || 0);
        
        html += `
            <tr>
                <td class="fw-bold d-none d-xl-table-cell">${cliente.id}</td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="user-avatar bg-primary text-white me-2">
                            ${inicial}
                        </div>
                        <div>
                            <div class="fw-bold">${cliente.nombre}</div>
                            <small class="text-muted">
                                <i class="bi bi-calendar3"></i>
                                Desde ${formatearFecha(cliente.fecha_creacion)}
                            </small>
                        </div>
                    </div>
                </td>
                <td class="d-none d-md-table-cell">${cliente.nit}</td>
                <td class="d-none d-lg-table-cell">
                    <div>
                        <i class="bi bi-telephone text-primary"></i>
                        ${cliente.telefono}
                    </div>
                    ${cliente.email ? `
                        <div>
                            <i class="bi bi-envelope text-muted"></i>
                            <small>${cliente.email}</small>
                        </div>
                    ` : ''}
                </td>
                <td class="d-none d-md-table-cell">
                    ${getBadgeTipo(cliente.tipo_cliente)}
                </td>
                <td class="d-none d-xl-table-cell">
                    ${getIconoMercaderias(cliente.tipo_mercaderias)}
                </td>
                <td>
                    ${cliente.saldo_creditos > 0 ? `
                        <span class="text-danger fw-bold">
                            Q ${formatearMoneda(cliente.saldo_creditos)}
                        </span>
                        <br>
                        <small class="text-muted">
                            Límite: Q ${formatearMoneda(cliente.limite_credito)}
                        </small>
                    ` : `
                        <span class="text-success">
                            <i class="bi bi-check-circle"></i> Sin deuda
                        </span>
                    `}
                </td>
                <td class="d-none d-sm-table-cell">
                    ${getBadgeEstado(cliente.activo)}
                </td>
                <td class="text-center">
                    <div class="btn-group" role="group">
                        <a href="ver.php?id=${cliente.id}" 
                           class="btn btn-sm btn-info" 
                           title="Ver detalles">
                            <i class="bi bi-eye"></i>
                        </a>
                        <?php if (tiene_permiso('clientes', 'editar')): ?>
                        <a href="editar.php?id=${cliente.id}" 
                           class="btn btn-sm btn-warning"
                           title="Editar">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <?php endif; ?>
                        <?php if (tiene_permiso('clientes', 'eliminar')): ?>
                        <button class="btn btn-sm btn-danger" 
                                onclick="eliminarCliente(${cliente.id})"
                                title="Eliminar">
                            <i class="bi bi-trash"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    
    // Mostrar tabla y footer
    document.getElementById('loadingTable').style.display = 'none';
    document.getElementById('tableContainer').style.display = 'block';
    document.getElementById('tableFooter').style.display = 'block';
    
    // Actualizar contador
    document.getElementById('contadorClientes').textContent = `Mostrando ${clientes.length} clientes`;
    document.getElementById('tituloTabla').textContent = `Listado de Clientes (${clientes.length})`;
}

/**
 * Aplicar filtros
 */
function aplicarFiltros() {
    // TODO FASE 5: Llamar a cargarClientes() con los nuevos filtros
    cargarClientes();
}

/**
 * Limpiar filtros
 */
function limpiarFiltros() {
    document.getElementById('searchInput').value = '';
    document.getElementById('filterTipo').value = '';
    document.getElementById('filterEstado').value = '';
    cargarClientes();
}

/**
 * Eliminar cliente
 * TODO FASE 5: Conectar con API
 */
function eliminarCliente(clienteId) {
    if (!confirm('¿Está seguro de eliminar este cliente?\n\nEsta acción no se puede deshacer.')) {
        return;
    }
    
    // TODO FASE 5: Descomentar y conectar
    /*
    fetch(`<?php echo BASE_URL; ?>api/clientes/eliminar.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id: clienteId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarAlerta('Cliente eliminado exitosamente', 'success');
            cargarClientes();
        } else {
            mostrarAlerta(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarAlerta('Error al eliminar el cliente', 'error');
    });
    */
    
    alert('MODO DESARROLLO: Eliminar cliente #' + clienteId + '\nEsperando API');
}

/**
 * Utilidades
 */
function formatearMoneda(monto) {
    return parseFloat(monto).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function formatearFecha(fecha) {
    const d = new Date(fecha);
    return d.toLocaleDateString('es-GT', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}

function getBadgeTipo(tipo) {
    const badges = {
        'publico': '<span class="badge" style="background-color: #1e3a8a;">Público</span>',
        'mayorista': '<span class="badge bg-dorado">Mayorista</span>'
    };
    return badges[tipo] || '';
}

function getIconoMercaderias(tipo) {
    const iconos = {
        'oro': '<span title="Oro">🟡 Oro</span>',
        'plata': '<span title="Plata">⚪ Plata</span>',
        'ambas': '<span title="Ambas">🟡⚪ Ambas</span>'
    };
    return iconos[tipo] || tipo;
}

function getBadgeEstado(activo) {
    return activo == 1 
        ? '<span class="badge bg-success">Activo</span>' 
        : '<span class="badge bg-secondary">Inactivo</span>';
}

function mostrarError(mensaje) {
    document.getElementById('loadingTable').style.display = 'none';
    document.getElementById('noResults').style.display = 'block';
    document.getElementById('noResults').innerHTML = `
        <i class="bi bi-exclamation-triangle text-danger" style="font-size: 48px;"></i>
        <p class="mt-3 text-danger">${mensaje}</p>
    `;
}

function mostrarAlerta(mensaje, tipo) {
    // TODO: Implementar sistema de notificaciones
    alert(mensaje);
}
</script>

<?php
// Incluir footer
include '../../includes/footer.php';
?>