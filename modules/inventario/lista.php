<?php
/**
 * ================================================
 * MÓDULO INVENTARIO - PRODUCTOS
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
            <h2 class="mb-1"><i class="bi bi-box"></i> Inventario de Productos</h2>
            <p class="text-muted mb-0">Control de stock por sucursal</p>
        </div>
        <div>
            <a href="agregar.php" class="btn btn-warning me-2">
                <i class="bi bi-plus-circle"></i> Nuevo
            </a>
            <a href="transferencia.php" class="btn btn-primary">
                <i class="bi bi-arrow-left-right"></i> Transferencias
            </a>
        </div>
    </div>

    <hr class="border-warning border-2 opacity-75 mb-4">

    <!-- Cards Estadísticas -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-start border-primary border-4 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-light rounded-3 p-3">
                                <i class="bi bi-boxes fs-2 text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0" id="totalProductos">0</h3>
                            <p class="text-muted mb-0">Productos Totales</p>
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
                            <h3 class="mb-0" id="disponibles">0</h3>
                            <p class="text-muted mb-0">Disponibles</p>
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
                                <i class="bi bi-exclamation-triangle fs-2 text-warning"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0" id="stockBajo">0</h3>
                            <p class="text-muted mb-0">Stock Bajo</p>
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
                            <h3 class="mb-0" id="agotados">0</h3>
                            <p class="text-muted mb-0">Agotados</p>
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
                <div class="col-md-3">
                    <label class="form-label">Buscar Producto</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" id="inputBuscar" 
                               placeholder="Código, nombre...">
                    </div>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Categoría</label>
                    <select class="form-select" id="selectCategoria">
                        <option value="">Todas</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Estado</label>
                    <select class="form-select" id="selectEstado">
                        <option value="">Todos</option>
                        <option value="disponible">Disponibles</option>
                        <option value="bajo">Stock Bajo</option>
                        <option value="agotado">Agotados</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Sucursal</label>
                    <select class="form-select" id="selectSucursal">
                        <option value="">Todas</option>
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
            <h5 class="mb-0"><i class="bi bi-table"></i> Listado de Productos</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="tablaProductos">
                    <thead class="table-dark">
                        <tr>
                            <th width="8%">Código</th>
                            <th width="20%">Nombre</th>
                            <th width="12%">Categoría</th>
                            <th width="10%">Estilo</th>
                            <th width="8%">Peso</th>
                            <th width="10%">Proveedor</th>
                            <th width="8%">Stock</th>
                            <th width="8%">Estado</th>
                            <th width="8%" class="text-center">Sucursal</th>
                            <th width="8%" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaProductosBody">
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
let productosData = [];
let productosFiltrados = [];
let categorias = [];
let sucursales = [];

async function cargarDatos() {
    try {
        mostrarCargando();
        
        // Cargar categorías
        const resCategorias = await api.listarCategorias();
        if (resCategorias.success) {
            categorias = Array.isArray(resCategorias.data) ? resCategorias.data : (resCategorias.data.categorias || []);
            llenarSelectCategorias();
        }
        
        // Cargar sucursales
        const resSucursales = await api.listarSucursales({ activo: 1 });
        if (resSucursales.success) {
            sucursales = Array.isArray(resSucursales.data) ? resSucursales.data : [];
            llenarSelectSucursales();
        }
        
        // Cargar productos
        await cargarProductos();
        
        ocultarCargando();
        
    } catch (error) {
        ocultarCargando();
        console.error('Error:', error);
        mostrarError('Error al cargar datos: ' + error.message);
    }
}

function llenarSelectCategorias() {
    const select = document.getElementById('selectCategoria');
    categorias.forEach(cat => {
        const option = document.createElement('option');
        option.value = cat.id;
        option.textContent = cat.nombre;
        select.appendChild(option);
    });
}

function llenarSelectSucursales() {
    const select = document.getElementById('selectSucursal');
    sucursales.forEach(suc => {
        const option = document.createElement('option');
        option.value = suc.id;
        option.textContent = suc.nombre;
        select.appendChild(option);
    });
}

async function cargarProductos() {
    try {
        const catId = document.getElementById('selectCategoria').value;
        const sucId = document.getElementById('selectSucursal').value;
        
        const params = new URLSearchParams();
        if (catId) params.append('categoria_id', catId);
        if (sucId) params.append('sucursal_id', sucId);
        params.append('por_pagina', '500');
        
        const res = await fetch(`/api/inventario/listar.php?${params}`);
        const resultado = await res.json();
        
        if (!resultado.success) {
            productosData = [];
            productosFiltrados = [];
            mostrarMensajeVacio('tablaProductos', 'No hay productos', 10);
            return;
        }
        
        productosData = (resultado.data.inventario || []).map(item => ({
            ...item,
            id: item.producto_id,
            cantidad: parseInt(item.cantidad) || 0
        }));
        
        productosFiltrados = [...productosData];
        actualizarEstadisticas();
        aplicarFiltros();
        
    } catch (error) {
        console.error('Error:', error);
        mostrarMensajeVacio('tablaProductos', 'Error al cargar', 10);
    }
}

function actualizarEstadisticas() {
    const total = productosData.length;
    const disponibles = productosData.filter(p => (p.cantidad || 0) > 0).length; // ← cantidad en vez de stock
    const agotados = productosData.filter(p => (p.cantidad || 0) === 0).length;
    const stockBajo = productosData.filter(p => {
        const stock = p.cantidad || 0; // ← cantidad
        const minimo = p.stock_minimo || 5;
        return stock > 0 && stock <= minimo;
    }).length;
    
    document.getElementById('totalProductos').textContent = formatearNumero(total);
    document.getElementById('disponibles').textContent = formatearNumero(disponibles);
    document.getElementById('stockBajo').textContent = formatearNumero(stockBajo);
    document.getElementById('agotados').textContent = formatearNumero(agotados);
}

function aplicarFiltros() {
    const buscar = document.getElementById('inputBuscar').value.toLowerCase().trim();
    const estado = document.getElementById('selectEstado').value;
    
    productosFiltrados = productosData.filter(prod => {
        // Filtro búsqueda
        if (buscar) {
            const codigo = (prod.codigo || '').toLowerCase();
            const nombre = (prod.nombre || '').toLowerCase();
            if (!codigo.includes(buscar) && !nombre.includes(buscar)) {
                return false;
            }
        }
        
        // Filtro estado
        if (estado) {
            const stock = prod.cantidad || 0; // ← cantidad
            const minimo = prod.stock_minimo || 5;
            
            if (estado === 'agotado' && stock > 0) return false;
            if (estado === 'bajo' && (stock === 0 || stock > minimo)) return false;
            if (estado === 'disponible' && stock === 0) return false;
        }
        
        return true;
    });
    
    mostrarProductos();
}

function mostrarProductos() {
    const tbody = document.getElementById('tablaProductosBody');
    
    if (productosFiltrados.length === 0) {
        mostrarMensajeVacio('tablaProductos', 'No hay productos para mostrar', 10);
        return;
    }
    
    let html = '';
    
    productosFiltrados.forEach(prod => {
        const stock = prod.cantidad || 0; // ← cantidad
        const minimo = prod.stock_minimo || 5;
        
        let badgeStock = '';
        if (stock === 0) {
            badgeStock = '<span class="badge bg-danger">Agotado</span>';
        } else if (stock <= minimo) {
            badgeStock = '<span class="badge bg-warning text-dark">Stock Bajo</span>';
        } else {
            badgeStock = '<span class="badge bg-success">Disponible</span>';
        }
        
        const peso = prod.peso_gramos ? `${prod.peso_gramos}g` : '-';
        
        html += `
            <tr>
                <td><code>${escaparHTML(prod.codigo || '-')}</code></td>
                <td><strong>${escaparHTML(prod.nombre || prod.producto_nombre || '')}</strong></td>
                <td>${escaparHTML(prod.categoria_nombre || '-')}</td>
                <td>${escaparHTML(prod.estilo || '-')}</td>
                <td>${peso}</td>
                <td><small>${escaparHTML(prod.proveedor_nombre || '-')}</small></td>
                <td><strong>${stock}</strong></td>
                <td>${badgeStock}</td>
                <td class="text-center"><small>${escaparHTML(prod.sucursal_nombre || 'Todas')}</small></td>
                <td class="text-center">
                    <a href="ver.php?id=${prod.producto_id || prod.id}" 
                       class="btn btn-sm btn-outline-info" title="Ver">
                        <i class="bi bi-eye"></i>
                    </a>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

function limpiarFiltros() {
    document.getElementById('inputBuscar').value = '';
    document.getElementById('selectCategoria').value = '';
    document.getElementById('selectEstado').value = '';
    document.getElementById('selectSucursal').value = '';
    cargarProductos();
}

document.addEventListener('DOMContentLoaded', function() {
    cargarDatos();
    
    document.getElementById('inputBuscar').addEventListener('input', aplicarFiltros);
    document.getElementById('selectCategoria').addEventListener('change', cargarProductos);
    document.getElementById('selectEstado').addEventListener('change', aplicarFiltros);
    document.getElementById('selectSucursal').addEventListener('change', cargarProductos);
    document.getElementById('btnLimpiar').addEventListener('click', limpiarFiltros);
});

console.log('✅ Vista de Inventario cargada correctamente');
</script>