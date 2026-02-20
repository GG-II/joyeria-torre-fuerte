<?php
/**
 * ================================================
 * MÓDULO MATERIA PRIMA - LISTA
 * ================================================
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();

require_once '../../includes/header.php';
require_once '../../includes/navbar.php';
?>

<div class="container-fluid px-4 py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-1"><i class="bi bi-gem"></i> Materias Primas</h2>
            <p class="text-muted mb-0">Gestión de oro, plata, piedras e insumos</p>
        </div>
        <?php if (tiene_permiso('materia_prima', 'crear')): ?>
        <a href="agregar.php" class="btn btn-warning">
            <i class="bi bi-plus-circle"></i> Nueva Materia Prima
        </a>
        <?php endif; ?>
    </div>

    <hr class="border-warning border-2 opacity-75 mb-4">

    <!-- Cards Estadísticas -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-start border-warning border-4 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Total Materias</p>
                            <h3 id="totalMaterias" class="mb-0">0</h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="bi bi-gem fs-2 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-start border-success border-4 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Oro</p>
                            <h3 id="totalOro" class="mb-0">0 g</h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="bi bi-circle-fill fs-2 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-start border-secondary border-4 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Plata</p>
                            <h3 id="totalPlata" class="mb-0">0 g</h3>
                        </div>
                        <div class="bg-secondary bg-opacity-10 p-3 rounded">
                            <i class="bi bi-circle-fill fs-2 text-secondary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-start border-danger border-4 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Stock Bajo</p>
                            <h3 id="totalStockBajo" class="mb-0">0</h3>
                        </div>
                        <div class="bg-danger bg-opacity-10 p-3 rounded">
                            <i class="bi bi-exclamation-triangle-fill fs-2 text-danger"></i>
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
                    <input type="text" class="form-control" id="busqueda" 
                           placeholder="Nombre de materia prima...">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Tipo</label>
                    <select class="form-select" id="filtroTipo">
                        <option value="">Todos</option>
                        <option value="oro">Oro</option>
                        <option value="plata">Plata</option>
                        <option value="piedra">Piedras</option>
                        <option value="otro">Otros</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Estado</label>
                    <select class="form-select" id="filtroActivo">
                        <option value="">Todos</option>
                        <option value="1" selected>Activos</option>
                        <option value="0">Inactivos</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label d-block">&nbsp;</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="filtroStockBajo">
                        <label class="form-check-label" for="filtroStockBajo">
                            Solo stock bajo
                        </label>
                    </div>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-secondary w-100" onclick="limpiarFiltros()">
                        <i class="bi bi-x-circle"></i> Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-list-ul"></i> Listado de Materias Primas</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th>Cantidad</th>
                            <th>Unidad</th>
                            <th>Stock Mínimo</th>
                            <th>Precio/Unidad</th>
                            <th>Valor Total</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaMaterias">
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                                <p class="mt-2">Cargando materias primas...</p>
                            </td>
                        </tr>
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
let materiasData = [];
let materiasFiltradas = [];

async function cargarMaterias() {
    try {
        mostrarCargando();
        
        const res = await fetch('/joyeria-torre-fuerte/api/materia_prima/listar.php');
        const data = await res.json();
        
        ocultarCargando();
        
        if (!data.success) {
            mostrarError(data.message || 'Error al cargar materias primas');
            return;
        }
        
        materiasData = data.data || [];
        materiasFiltradas = materiasData;
        
        actualizarEstadisticas();
        aplicarFiltros();
        
    } catch (error) {
        ocultarCargando();
        console.error('Error:', error);
        mostrarError('Error al cargar materias primas');
    }
}

function actualizarEstadisticas() {
    const stats = {
        total: materiasData.length,
        oro: 0,
        plata: 0,
        stockBajo: 0
    };
    
    materiasData.forEach(m => {
        if (m.activo != 1) return;
        
        if (m.tipo === 'oro' && m.unidad_medida === 'gramos') {
            stats.oro += parseFloat(m.cantidad_disponible) || 0;
        }
        if (m.tipo === 'plata' && m.unidad_medida === 'gramos') {
            stats.plata += parseFloat(m.cantidad_disponible) || 0;
        }
        
        const cantidad = parseFloat(m.cantidad_disponible) || 0;
        const minimo = parseFloat(m.stock_minimo) || 0;
        if (cantidad <= minimo) {
            stats.stockBajo++;
        }
    });
    
    document.getElementById('totalMaterias').textContent = stats.total;
    document.getElementById('totalOro').textContent = stats.oro.toFixed(3) + ' g';
    document.getElementById('totalPlata').textContent = stats.plata.toFixed(3) + ' g';
    document.getElementById('totalStockBajo').textContent = stats.stockBajo;
}

function aplicarFiltros() {
    const busqueda = document.getElementById('busqueda').value.toLowerCase();
    const tipo = document.getElementById('filtroTipo').value;
    const activo = document.getElementById('filtroActivo').value;
    const soloStockBajo = document.getElementById('filtroStockBajo').checked;
    
    materiasFiltradas = materiasData.filter(m => {
        // Búsqueda
        if (busqueda && !m.nombre.toLowerCase().includes(busqueda)) {
            return false;
        }
        
        // Tipo
        if (tipo && m.tipo !== tipo) return false;
        
        // Activo
        if (activo !== '' && m.activo != activo) return false;
        
        // Stock bajo
        if (soloStockBajo) {
            const cantidad = parseFloat(m.cantidad_disponible) || 0;
            const minimo = parseFloat(m.stock_minimo) || 0;
            if (cantidad > minimo) return false;
        }
        
        return true;
    });
    
    renderizarTabla();
}

function renderizarTabla() {
    const tbody = document.getElementById('tablaMaterias');
    
    if (materiasFiltradas.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center text-muted py-4">
                    <i class="bi bi-inbox fs-1"></i>
                    <p class="mt-2">No hay materias primas para mostrar</p>
                </td>
            </tr>
        `;
        return;
    }
    
    let html = '';
    
    materiasFiltradas.forEach(m => {
        const cantidad = parseFloat(m.cantidad_disponible) || 0;
        const minimo = parseFloat(m.stock_minimo) || 0;
        const precio = parseFloat(m.precio_por_unidad) || 0;
        const valorTotal = cantidad * precio;
        
        const tipoBadge = {
            'oro': 'bg-warning text-dark',
            'plata': 'bg-secondary',
            'piedra': 'bg-info',
            'otro': 'bg-primary'
        };
        
        const stockClass = cantidad <= minimo ? 'text-danger fw-bold' : cantidad <= (minimo * 2) ? 'text-warning fw-bold' : '';
        
        html += `
            <tr>
                <td><strong>${escaparHTML(m.nombre)}</strong></td>
                <td><span class="badge ${tipoBadge[m.tipo] || 'bg-secondary'}">${escaparHTML(m.tipo)}</span></td>
                <td class="${stockClass}">${cantidad.toFixed(3)}</td>
                <td><small>${escaparHTML(m.unidad_medida)}</small></td>
                <td>${minimo.toFixed(3)}</td>
                <td>${precio > 0 ? formatearMoneda(precio) : '-'}</td>
                <td>${valorTotal > 0 ? formatearMoneda(valorTotal) : '-'}</td>
                <td>
                    <span class="badge ${m.activo == 1 ? 'bg-success' : 'bg-secondary'}">
                        ${m.activo == 1 ? 'Activo' : 'Inactivo'}
                    </span>
                </td>
                <td class="text-center">
                    <div class="btn-group btn-group-sm">
                        <a href="ver.php?id=${m.id}" class="btn btn-outline-primary" title="Ver detalles">
                            <i class="bi bi-eye"></i>
                        </a>
                        ${m.activo == 1 ? `
                        <a href="editar.php?id=${m.id}" class="btn btn-outline-warning" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </a>
                        ` : ''}
                    </div>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

function limpiarFiltros() {
    document.getElementById('busqueda').value = '';
    document.getElementById('filtroTipo').value = '';
    document.getElementById('filtroActivo').value = '1';
    document.getElementById('filtroStockBajo').checked = false;
    aplicarFiltros();
}

// Event listeners
document.getElementById('busqueda').addEventListener('input', aplicarFiltros);
document.getElementById('filtroTipo').addEventListener('change', aplicarFiltros);
document.getElementById('filtroActivo').addEventListener('change', aplicarFiltros);
document.getElementById('filtroStockBajo').addEventListener('change', aplicarFiltros);

document.addEventListener('DOMContentLoaded', cargarMaterias);
</script>