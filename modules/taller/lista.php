<?php
/**
 * ================================================
 * MÓDULO TALLER - LISTA DE TRABAJOS
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
            <h2 class="mb-1"><i class="bi bi-wrench-adjustable"></i> Trabajos de Taller</h2>
            <p class="text-muted mb-0">Gestión de reparaciones y trabajos de orfebrería</p>
        </div>
        <?php if (tiene_permiso('taller', 'crear')): ?>
        <a href="agregar.php" class="btn btn-warning">
            <i class="bi bi-plus-circle"></i> Nuevo Trabajo
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
                            <p class="text-muted mb-1">Recibidos</p>
                            <h3 id="totalRecibidos" class="mb-0">0</h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="bi bi-hourglass-split fs-2 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-start border-primary border-4 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">En Proceso</p>
                            <h3 id="totalEnProceso" class="mb-0">0</h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="bi bi-gear-fill fs-2 text-primary"></i>
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
                            <p class="text-muted mb-1">Completados</p>
                            <h3 id="totalCompletados" class="mb-0">0</h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="bi bi-check-circle-fill fs-2 text-success"></i>
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
                            <p class="text-muted mb-1">Atrasados</p>
                            <h3 id="totalAtrasados" class="mb-0">0</h3>
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
                <div class="col-md-3">
                    <label class="form-label">Buscar</label>
                    <input type="text" class="form-control" id="busqueda" 
                           placeholder="Código, cliente...">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Estado</label>
                    <select class="form-select" id="filtroEstado">
                        <option value="">Todos</option>
                        <option value="recibido">Recibido</option>
                        <option value="en_proceso">En Proceso</option>
                        <option value="completado">Completado</option>
                        <option value="entregado">Entregado</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Material</label>
                    <select class="form-select" id="filtroMaterial">
                        <option value="">Todos</option>
                        <option value="oro">Oro</option>
                        <option value="plata">Plata</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Tipo de Trabajo</label>
                    <select class="form-select" id="filtroTipoTrabajo">
                        <option value="">Todos</option>
                        <option value="reparacion">Reparación</option>
                        <option value="ajuste">Ajuste</option>
                        <option value="grabado">Grabado</option>
                        <option value="diseño">Diseño</option>
                        <option value="limpieza">Limpieza</option>
                        <option value="engaste">Engaste</option>
                        <option value="repuesto">Repuesto</option>
                        <option value="fabricacion">Fabricación</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-secondary w-100" onclick="limpiarFiltros()">
                        <i class="bi bi-x-circle"></i> Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Trabajos -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-list-ul"></i> Listado de Trabajos</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Código</th>
                            <th>Cliente</th>
                            <th>Teléfono</th>
                            <th>Pieza</th>
                            <th>Tipo Trabajo</th>
                            <th>Material</th>
                            <th>Precio</th>
                            <th>Saldo</th>
                            <th>Fecha Entrega</th>
                            <th>Empleado</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaTrabajos">
                        <tr>
                            <td colspan="12" class="text-center text-muted py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                                <p class="mt-2">Cargando trabajos...</p>
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
let trabajosData = [];
let trabajosFiltrados = [];

async function cargarTrabajos() {
    try {
        mostrarCargando();
        
        const res = await fetch('/api/taller/listar.php?por_pagina=500');
        const data = await res.json();
        
        ocultarCargando();
        
        if (!data.success) {
            mostrarError(data.message || 'Error al cargar trabajos');
            return;
        }
        
        trabajosData = data.data || [];
        trabajosFiltrados = trabajosData;
        
        actualizarEstadisticas();
        aplicarFiltros();
        
    } catch (error) {
        ocultarCargando();
        console.error('Error:', error);
        mostrarError('Error al cargar trabajos');
    }
}

function actualizarEstadisticas() {
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);
    
    const stats = {
        recibidos: 0,
        en_proceso: 0,
        completados: 0,
        atrasados: 0
    };
    
    trabajosData.forEach(t => {
        if (t.estado === 'recibido') stats.recibidos++;
        if (t.estado === 'en_proceso') stats.en_proceso++;
        if (t.estado === 'completado') stats.completados++;
        
        // Atrasados: no entregados y fecha prometida pasada
        if (t.estado !== 'entregado' && t.estado !== 'cancelado') {
            const fechaPromesa = new Date(t.fecha_entrega_prometida);
            if (fechaPromesa < hoy) {
                stats.atrasados++;
            }
        }
    });
    
    document.getElementById('totalRecibidos').textContent = stats.recibidos;
    document.getElementById('totalEnProceso').textContent = stats.en_proceso;
    document.getElementById('totalCompletados').textContent = stats.completados;
    document.getElementById('totalAtrasados').textContent = stats.atrasados;
}

function aplicarFiltros() {
    const busqueda = document.getElementById('busqueda').value.toLowerCase();
    const estado = document.getElementById('filtroEstado').value;
    const material = document.getElementById('filtroMaterial').value;
    const tipoTrabajo = document.getElementById('filtroTipoTrabajo').value;
    
    trabajosFiltrados = trabajosData.filter(t => {
        // Búsqueda
        if (busqueda) {
            const coincide = 
                t.codigo.toLowerCase().includes(busqueda) ||
                t.cliente_nombre.toLowerCase().includes(busqueda) ||
                t.cliente_telefono.includes(busqueda) ||
                t.descripcion_pieza.toLowerCase().includes(busqueda);
            
            if (!coincide) return false;
        }
        
        // Estado
        if (estado && t.estado !== estado) return false;
        
        // Material
        if (material && t.material !== material) return false;
        
        // Tipo trabajo
        if (tipoTrabajo && t.tipo_trabajo !== tipoTrabajo) return false;
        
        return true;
    });
    
    renderizarTabla();
}

function renderizarTabla() {
    const tbody = document.getElementById('tablaTrabajos');
    
    if (trabajosFiltrados.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="12" class="text-center text-muted py-4">
                    <i class="bi bi-inbox fs-1"></i>
                    <p class="mt-2">No hay trabajos para mostrar</p>
                </td>
            </tr>
        `;
        return;
    }
    
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);
    
    let html = '';
    
    trabajosFiltrados.forEach(t => {
        const estadoBadge = {
            'recibido': 'bg-warning text-dark',
            'en_proceso': 'bg-primary',
            'completado': 'bg-success',
            'entregado': 'bg-info',
            'cancelado': 'bg-secondary'
        };
        
        const estadoTexto = {
            'recibido': 'Recibido',
            'en_proceso': 'En Proceso',
            'completado': 'Completado',
            'entregado': 'Entregado',
            'cancelado': 'Cancelado'
        };
        
        // Verificar atraso
        const fechaPromesa = new Date(t.fecha_entrega_prometida);
        const atrasado = (t.estado !== 'entregado' && t.estado !== 'cancelado' && fechaPromesa < hoy);
        const claseFechaAtraso = atrasado ? 'text-danger fw-bold' : '';
        
        const saldo = parseFloat(t.saldo) || 0;
        const claseSaldo = saldo > 0 ? 'text-danger' : 'text-success';
        
        html += `
            <tr>
                <td>
                    <strong>${escaparHTML(t.codigo)}</strong>
                    ${atrasado ? '<br><small class="text-danger"><i class="bi bi-exclamation-triangle"></i> Atrasado</small>' : ''}
                </td>
                <td>${escaparHTML(t.cliente_nombre)}</td>
                <td><small>${escaparHTML(t.cliente_telefono)}</small></td>
                <td><small>${escaparHTML(t.descripcion_pieza).substring(0, 30)}...</small></td>
                <td><small>${escaparHTML(t.tipo_trabajo)}</small></td>
                <td>
                    <span class="badge ${t.material === 'oro' ? 'bg-warning text-dark' : t.material === 'plata' ? 'bg-secondary' : 'bg-info'}">
                        ${escaparHTML(t.material)}
                    </span>
                </td>
                <td>${formatearMoneda(t.precio_total)}</td>
                <td class="${claseSaldo}">${formatearMoneda(saldo)}</td>
                <td class="${claseFechaAtraso}">${formatearFecha(t.fecha_entrega_prometida)}</td>
                <td><small>${escaparHTML(t.empleado_actual_nombre || '-')}</small></td>
                <td>
                    <span class="badge ${estadoBadge[t.estado]}">
                        ${estadoTexto[t.estado]}
                    </span>
                </td>
                <td class="text-center">
                    <div class="btn-group btn-group-sm">
                        <a href="ver.php?id=${t.id}" class="btn btn-outline-primary" title="Ver detalles">
                            <i class="bi bi-eye"></i>
                        </a>
                        ${t.estado !== 'entregado' && t.estado !== 'cancelado' ? `
                        <a href="editar.php?id=${t.id}" class="btn btn-outline-warning" title="Editar">
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
    document.getElementById('filtroEstado').value = '';
    document.getElementById('filtroMaterial').value = '';
    document.getElementById('filtroTipoTrabajo').value = '';
    aplicarFiltros();
}

// Event listeners
document.getElementById('busqueda').addEventListener('input', aplicarFiltros);
document.getElementById('filtroEstado').addEventListener('change', aplicarFiltros);
document.getElementById('filtroMaterial').addEventListener('change', aplicarFiltros);
document.getElementById('filtroTipoTrabajo').addEventListener('change', aplicarFiltros);

document.addEventListener('DOMContentLoaded', cargarTrabajos);
</script>