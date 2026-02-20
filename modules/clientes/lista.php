<?php
/**
 * ================================================
 * MÓDULO CLIENTES - LISTA
 * ================================================
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();
requiere_rol(['administrador', 'dueño', 'vendedor', 'cajero']);

require_once '../../includes/header.php';
require_once '../../includes/navbar.php';
?>

<div class="container-fluid px-4 py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-1"><i class="bi bi-people"></i> Clientes</h2>
            <p class="text-muted mb-0">Gestión de clientes y cuentas por cobrar</p>
        </div>
        <?php if (in_array($_SESSION['usuario_rol'], ['administrador', 'dueño', 'vendedor'])): ?>
        <a href="agregar.php" class="btn btn-warning btn-lg">
            <i class="bi bi-plus-circle"></i> Nuevo Cliente
        </a>
        <?php endif; ?>
    </div>

    <hr class="border-warning border-2 opacity-75 mb-4">

    <!-- Filtros -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Buscar Cliente</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" id="inputBuscar" 
                               placeholder="Nombre, NIT, teléfono...">
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Tipo de Cliente</label>
                    <select class="form-select" id="selectTipo">
                        <option value="">Todos</option>
                        <option value="publico">Público</option>
                        <option value="mayorista">Mayorista</option>
                    </select>
                </div>

                <div class="col-md-2">
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
            <h5 class="mb-0"><i class="bi bi-table"></i> Listado de Clientes</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="tablaClientes">
                    <thead class="table-dark">
                        <tr>
                            <th width="20%">Nombre</th>
                            <th width="12%">NIT</th>
                            <th width="10%">Teléfono</th>
                            <th width="15%">Email</th>
                            <th width="8%">Tipo</th>
                            <th width="10%">Mercadería</th>
                            <th width="10%">Límite Crédito</th>
                            <th width="8%">Estado</th>
                            <th width="7%" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaClientesBody">
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
let clientesData = [];
let clientesFiltrados = [];

async function cargarClientes() {
    try {
        mostrarCargando();
        
        const estado = document.getElementById('selectEstado').value;
        const tipo = document.getElementById('selectTipo').value;
        const filtros = {};
        
        if (estado !== '') filtros.activo = estado;
        if (tipo) filtros.tipo_cliente = tipo;
        
        const resultado = await api.listarClientes(filtros);
        
        ocultarCargando();
        
        if (resultado.success) {
            // LA API DEVUELVE: { data: { clientes: [...], total, pagina } }
            const datos = resultado.data.clientes || resultado.data || [];
            clientesData = Array.isArray(datos) ? datos : [];
            clientesFiltrados = [...clientesData];
            
            aplicarFiltros();
        } else {
            clientesData = [];
            clientesFiltrados = [];
            mostrarMensajeVacio('tablaClientes', 'No hay clientes para mostrar', 9);
        }
        
    } catch (error) {
        ocultarCargando();
        console.error('Error:', error);
        mostrarError('Error: ' + error.message);
        mostrarMensajeVacio('tablaClientes', 'Error al cargar datos', 9);
    }
}
function aplicarFiltros() {
    const buscar = document.getElementById('inputBuscar').value.toLowerCase().trim();
    
    clientesFiltrados = clientesData.filter(cliente => {
        if (buscar) {
            const nombre = (cliente.nombre || '').toLowerCase();
            const nit = (cliente.nit || '').toLowerCase();
            const telefono = (cliente.telefono || '').toLowerCase();
            
            if (!nombre.includes(buscar) && !nit.includes(buscar) && !telefono.includes(buscar)) {
                return false;
            }
        }
        
        return true;
    });
    
    mostrarClientes();
}

function mostrarClientes() {
    const tbody = document.getElementById('tablaClientesBody');
    
    if (clientesFiltrados.length === 0) {
        mostrarMensajeVacio('tablaClientes', 'No hay clientes para mostrar', 9);
        return;
    }
    
    const rolUsuario = '<?php echo $_SESSION["usuario_rol"] ?? ""; ?>';
    const puedeEditar = ['administrador', 'dueño', 'vendedor'].includes(rolUsuario);
    
    let html = '';
    
    clientesFiltrados.forEach(cliente => {
        const badgeEstado = cliente.activo == 1 
            ? '<span class="badge bg-success">Activo</span>'
            : '<span class="badge bg-secondary">Inactivo</span>';
        
        const badgeTipo = cliente.tipo_cliente === 'mayorista'
            ? '<span class="badge bg-warning text-dark">Mayorista</span>'
            : '<span class="badge bg-info">Público</span>';
        
        const badgeMercaderia = obtenerBadgeMercaderia(cliente.tipo_mercaderias);
        
        const btnVer = `
            <a href="ver.php?id=${cliente.id}" 
               class="btn btn-sm btn-outline-info" 
               title="Ver">
                <i class="bi bi-eye"></i>
            </a>
        `;
        
        let botonesAdmin = '';
        if (puedeEditar) {
            const btnEditar = `
                <a href="editar.php?id=${cliente.id}" 
                   class="btn btn-sm btn-outline-warning" 
                   title="Editar">
                    <i class="bi bi-pencil"></i>
                </a>
            `;
            
            const btnEstado = cliente.activo == 1
                ? `<button class="btn btn-sm btn-outline-danger" 
                           onclick="cambiarEstado(${cliente.id}, 0)" 
                           title="Desactivar">
                       <i class="bi bi-x-circle"></i>
                   </button>`
                : `<button class="btn btn-sm btn-outline-success" 
                           onclick="cambiarEstado(${cliente.id}, 1)" 
                           title="Activar">
                       <i class="bi bi-check-circle"></i>
                   </button>`;
            
            botonesAdmin = btnEditar + ' ' + btnEstado;
        }
        
        html += `
            <tr>
                <td><strong>${escaparHTML(cliente.nombre || '')}</strong></td>
                <td>${escaparHTML(cliente.nit || 'C/F')}</td>
                <td>${escaparHTML(cliente.telefono || '-')}</td>
                <td><small>${escaparHTML(cliente.email || '-')}</small></td>
                <td>${badgeTipo}</td>
                <td>${badgeMercaderia}</td>
                <td>${cliente.limite_credito ? formatearMoneda(cliente.limite_credito) : '-'}</td>
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

function obtenerBadgeMercaderia(tipo) {
    const badges = {
        'oro': '<span class="badge bg-warning text-dark">Oro</span>',
        'plata': '<span class="badge bg-secondary">Plata</span>',
        'ambas': '<span class="badge bg-primary">Ambas</span>'
    };
    
    return badges[tipo] || '<span class="badge bg-light text-dark">-</span>';
}

async function cambiarEstado(id, nuevoEstado) {
    const accion = nuevoEstado == 1 ? 'activar' : 'desactivar';
    const confirmacion = await confirmarAccion(`¿Estás seguro de ${accion} este cliente?`);
    
    if (!confirmacion) return;
    
    try {
        mostrarCargando();
        
        const resultado = await api.cambiarEstadoCliente(id, nuevoEstado);
        
        ocultarCargando();
        
        if (resultado.success) {
            mostrarExito(`Cliente ${accion === 'activar' ? 'activado' : 'desactivado'} correctamente`);
            cargarClientes();
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
    document.getElementById('selectTipo').value = '';
    cargarClientes();
}

document.addEventListener('DOMContentLoaded', function() {
    cargarClientes();
    
    document.getElementById('inputBuscar').addEventListener('input', aplicarFiltros);
    document.getElementById('selectEstado').addEventListener('change', cargarClientes);
    document.getElementById('selectTipo').addEventListener('change', cargarClientes);
    document.getElementById('btnLimpiar').addEventListener('click', limpiarFiltros);
});

console.log('✅ Vista de Clientes cargada correctamente');
</script>