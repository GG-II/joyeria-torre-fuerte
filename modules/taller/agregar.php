<?php
/**
 * ================================================
 * MÓDULO TALLER - AGREGAR TRABAJO
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
    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-1"><i class="bi bi-plus-circle"></i> Nuevo Trabajo de Taller</h2>
            <p class="text-muted mb-0">Registrar reparación o trabajo de orfebrería</p>
        </div>
        <a href="lista.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    <hr class="border-warning border-2 opacity-75 mb-4">

    <form id="formTrabajo">
        <div class="row">
            <!-- Columna Izquierda -->
            <div class="col-lg-8">
                
                <!-- Datos del Cliente -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-person"></i> Datos del Cliente</h5>
                    </div>
                    <div class="card-body">
                        <!-- Cliente seleccionado O botón para buscar -->
                        <div id="clienteSeleccionadoContainer" style="display: none;">
                            <div class="card border-success shadow-sm mb-3" id="clienteSeleccionadoCard">
                                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-person-check-fill"></i> <strong>Cliente Seleccionado</strong></span>
                                    <button type="button" class="btn btn-sm btn-light" onclick="limpiarCliente()">
                                        <i class="bi bi-x-lg"></i> Cambiar
                                    </button>
                                </div>
                                <div class="card-body bg-light" id="clienteSeleccionadoBody">
                                    <!-- Se llenará dinámicamente -->
                                </div>
                            </div>
                        </div>

                        <!-- Botón para abrir modal de búsqueda -->
                        <div id="botonBuscarContainer">
                            <button type="button" class="btn btn-primary btn-lg w-100 mb-2" data-bs-toggle="modal" data-bs-target="#modalBuscarCliente">
                                <i class="bi bi-search"></i> Buscar Cliente Existente
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-lg w-100" id="btnNuevoClienteInline">
                                <i class="bi bi-person-plus"></i> Crear Nuevo Cliente
                            </button>
                        </div>
                        
                        <!-- Campos ocultos -->
                        <input type="hidden" id="cliente_nombre" required>
                        <input type="hidden" id="cliente_telefono" required>
                        <input type="hidden" id="cliente_id">
                    </div>
                </div>

                <!-- Descripción de la Pieza -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="bi bi-gem"></i> Descripción de la Pieza</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="material" class="form-label">
                                    Material <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="material" required>
                                    <option value="">Seleccione...</option>
                                    <option value="oro">Oro</option>
                                    <option value="plata">Plata</option>
                                    <option value="otro">Otro</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="peso_gramos" class="form-label">Peso (gramos)</label>
                                <input type="number" class="form-control" id="peso_gramos" 
                                       step="0.001" min="0">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="largo_cm" class="form-label">Largo (cm)</label>
                                <input type="number" class="form-control" id="largo_cm" 
                                       step="0.01" min="0">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="estilo" class="form-label">Estilo</label>
                                <input type="text" class="form-control" id="estilo" 
                                       placeholder="Ej: Clásico, Moderno">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label d-block">¿Con Piedra?</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="con_piedra" 
                                           id="con_piedra_si" value="1">
                                    <label class="form-check-label" for="con_piedra_si">Sí</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="con_piedra" 
                                           id="con_piedra_no" value="0" checked>
                                    <label class="form-check-label" for="con_piedra_no">No</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="descripcion_pieza" class="form-label">
                                Descripción de la Pieza <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" id="descripcion_pieza" rows="2" 
                                      placeholder="Ej: Anillo de compromiso en oro 18K con diamante central..." required></textarea>
                        </div>
                    </div>
                </div>

                <!-- Descripción del Trabajo -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-tools"></i> Descripción del Trabajo</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="tipo_trabajo" class="form-label">
                                Tipo de Trabajo <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="tipo_trabajo" required>
                                <option value="">Seleccione...</option>
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

                        <div class="mb-3">
                            <label for="descripcion_trabajo" class="form-label">
                                Descripción del Trabajo a Realizar <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" id="descripcion_trabajo" rows="3" 
                                      placeholder="Describa detalladamente el trabajo a realizar..." required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea class="form-control" id="observaciones" rows="2" 
                                      placeholder="Observaciones adicionales, condiciones especiales, etc."></textarea>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Columna Derecha -->
            <div class="col-lg-4">
                
                <!-- Fechas y Asignación -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-calendar"></i> Fechas y Asignación</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="fecha_entrega_prometida" class="form-label">
                                Fecha de Entrega Prometida <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control" id="fecha_entrega_prometida" required>
                        </div>

                        <div class="mb-3">
                            <label for="empleado_recibe_id" class="form-label">
                                Empleado que Recibe <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="empleado_recibe_id" required>
                                <option value="">Seleccione...</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Precios -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="bi bi-cash"></i> Precios</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="precio_total" class="form-label">
                                Precio Total (Q) <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control form-control-lg" id="precio_total" 
                                   step="0.01" min="0" required>
                        </div>

                        <div class="mb-3">
                            <label for="anticipo" class="form-label">Anticipo (Q)</label>
                            <input type="number" class="form-control" id="anticipo" 
                                   step="0.01" min="0" value="0">
                        </div>

                        <div class="alert alert-info">
                            <strong>Saldo:</strong> <span id="saldoCalculado">Q 0.00</span>
                        </div>
                    </div>
                </div>

                <!-- Botón Guardar -->
                <button type="submit" class="btn btn-warning btn-lg w-100">
                    <i class="bi bi-check-circle"></i> Crear Trabajo
                </button>

            </div>
        </div>
    </form>

<!-- Modal de Búsqueda de Clientes -->
<div class="modal fade" id="modalBuscarCliente" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-search"></i> Buscar Cliente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control form-control-lg" id="buscar_cliente_modal" 
                           placeholder="Escriba nombre o teléfono..." autocomplete="off">
                </div>
                
                <div id="resultadosClientesModal" style="max-height: 400px; overflow-y: auto;">
                    <p class="text-center text-muted">Escriba al menos 2 caracteres para buscar</p>
                </div>
            </div>
        </div>
    </div>
</div>

</div>

<?php require_once '../../includes/footer.php'; ?>

<script src="../../assets/js/vendors/sweetalert2/sweetalert2.all.min.js"></script>
<script src="../../assets/js/common.js"></script>
<script src="../../assets/js/api-client.js"></script>

<script>
async function cargarEmpleados() {
    try {
        const res = await fetch('/joyeria-torre-fuerte/api/usuarios/listar.php?activo=1');
        const data = await res.json();
        
        if (!data.success) return;
        
        const empleados = data.data.usuarios || data.data || [];
        const select = document.getElementById('empleado_recibe_id');
        
        empleados.forEach(emp => {
            const option = document.createElement('option');
            option.value = emp.id;
            option.textContent = emp.nombre;
            select.appendChild(option);
        });
        
        // Seleccionar usuario actual por defecto
        const usuarioActual = <?php echo $_SESSION['usuario_id'] ?? 'null'; ?>;
        if (usuarioActual) {
            select.value = usuarioActual;
        }
        
    } catch (error) {
        console.error('Error al cargar empleados:', error);
    }
}

// Calcular saldo en tiempo real
function calcularSaldo() {
    const total = parseFloat(document.getElementById('precio_total').value) || 0;
    const anticipo = parseFloat(document.getElementById('anticipo').value) || 0;
    const saldo = total - anticipo;
    
    document.getElementById('saldoCalculado').textContent = formatearMoneda(saldo);
}

document.getElementById('precio_total').addEventListener('input', calcularSaldo);
document.getElementById('anticipo').addEventListener('input', calcularSaldo);

// Validar teléfono (8 dígitos)
document.getElementById('cliente_telefono').addEventListener('input', function() {
    this.value = this.value.replace(/[^0-9-]/g, '');
});

// ================================================
// BÚSQUEDA DE CLIENTES CON MODAL (SIN DROPDOWN)
// ================================================
let timeoutBusqueda;

// Buscar en el modal
document.getElementById('buscar_cliente_modal').addEventListener('input', function() {
    clearTimeout(timeoutBusqueda);
    const busqueda = this.value.trim();
    
    const container = document.getElementById('resultadosClientesModal');
    
    if (busqueda.length < 2) {
        container.innerHTML = '<p class="text-center text-muted">Escriba al menos 2 caracteres para buscar</p>';
        return;
    }
    
    container.innerHTML = '<p class="text-center"><div class="spinner-border text-primary"></div><br>Buscando...</p>';
    
    timeoutBusqueda = setTimeout(() => buscarClientesModal(busqueda), 300);
});

async function buscarClientesModal(busqueda) {
    try {
        const res = await fetch(`/joyeria-torre-fuerte/api/clientes/listar.php?buscar=${encodeURIComponent(busqueda)}&limite=15`);
        const data = await res.json();
        
        const container = document.getElementById('resultadosClientesModal');
        
        if (!data.success || !data.data.clientes || data.data.clientes.length === 0) {
            container.innerHTML = '<p class="text-center text-muted">No se encontraron clientes</p>';
            return;
        }
        
        const clientes = data.data.clientes;
        
        let html = '<div class="list-group">';
        clientes.forEach(c => {
            html += `
                <a href="#" class="list-group-item list-group-item-action" 
                   onclick="event.preventDefault(); seleccionarClienteDesdeModal(${c.id}, '${escaparHTML(c.nombre).replace(/'/g, "\\'")}', '${escaparHTML(c.telefono)}')">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1">${escaparHTML(c.nombre)}</h6>
                            <small class="text-muted"><i class="bi bi-telephone"></i> ${escaparHTML(c.telefono)}</small>
                        </div>
                        ${c.nit && c.nit !== 'C/F' ? `<span class="badge bg-secondary">NIT: ${escaparHTML(c.nit)}</span>` : ''}
                    </div>
                </a>
            `;
        });
        html += '</div>';
        
        container.innerHTML = html;
        
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('resultadosClientesModal').innerHTML = 
            '<p class="text-center text-danger">Error al buscar clientes</p>';
    }
}

window.seleccionarClienteDesdeModal = function(id, nombre, telefono) {
    // Actualizar campos ocultos
    document.getElementById('cliente_id').value = id;
    document.getElementById('cliente_nombre').value = nombre;
    document.getElementById('cliente_telefono').value = telefono;
    
    // Mostrar card
    mostrarClienteSeleccionado(nombre, telefono, true);
    
    // Cerrar modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalBuscarCliente'));
    modal.hide();
    
    // Limpiar búsqueda
    document.getElementById('buscar_cliente_modal').value = '';
};

function mostrarClienteSeleccionado(nombre, telefono, esExistente) {
    document.getElementById('clienteSeleccionadoBody').innerHTML = `
        <div class="d-flex align-items-center">
            <div class="${esExistente ? 'bg-success' : 'bg-primary'} text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                 style="width: 60px; height: 60px; flex-shrink: 0;">
                <i class="bi ${esExistente ? 'bi-person-fill' : 'bi-person-plus-fill'} fs-3"></i>
            </div>
            <div class="flex-grow-1">
                <h5 class="mb-1">${escaparHTML(nombre)}</h5>
                <p class="mb-1 text-muted">
                    <i class="bi bi-telephone-fill"></i> ${escaparHTML(telefono)}
                </p>
                <span class="badge ${esExistente ? 'bg-success' : 'bg-primary'}">
                    <i class="bi ${esExistente ? 'bi-database' : 'bi-star'}"></i> 
                    ${esExistente ? 'Cliente registrado' : 'Nuevo cliente'}
                </span>
            </div>
        </div>
    `;
    
    const card = document.getElementById('clienteSeleccionadoCard');
    card.className = `card border-${esExistente ? 'success' : 'primary'} shadow-sm mb-3`;
    card.querySelector('.card-header').className = `card-header bg-${esExistente ? 'success' : 'primary'} text-white d-flex justify-content-between align-items-center`;
    
    document.getElementById('clienteSeleccionadoContainer').style.display = 'block';
    document.getElementById('botonBuscarContainer').style.display = 'none';
}

window.limpiarCliente = function() {
    document.getElementById('cliente_id').value = '';
    document.getElementById('cliente_nombre').value = '';
    document.getElementById('cliente_telefono').value = '';
    document.getElementById('clienteSeleccionadoContainer').style.display = 'none';
    document.getElementById('botonBuscarContainer').style.display = 'block';
};

document.getElementById('btnNuevoClienteInline').addEventListener('click', async function() {
    const { value: formValues } = await Swal.fire({
        title: '<i class="bi bi-person-plus-fill"></i> Crear Nuevo Cliente',
        html: `
            <div class="text-start p-3">
                <div class="mb-3">
                    <label class="form-label fw-bold">Nombre Completo:</label>
                    <input id="swal-nombre" class="form-control form-control-lg" placeholder="Ej: María López García" autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Teléfono:</label>
                    <input id="swal-telefono" class="form-control form-control-lg" placeholder="Ej: 5534-5678" maxlength="9">
                </div>
                <div class="alert alert-info mb-0">
                    <small><i class="bi bi-info-circle"></i> Este cliente se guardará solo para este trabajo</small>
                </div>
            </div>
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-check-lg"></i> Crear Cliente',
        cancelButtonText: 'Cancelar',
        width: '550px',
        customClass: {
            confirmButton: 'btn btn-primary btn-lg px-4',
            cancelButton: 'btn btn-secondary px-4'
        },
        buttonsStyling: false,
        preConfirm: () => {
            const nombre = document.getElementById('swal-nombre').value.trim();
            const telefono = document.getElementById('swal-telefono').value.trim();
            
            if (!nombre || !telefono) {
                Swal.showValidationMessage('Complete todos los campos');
                return false;
            }
            
            if (telefono.length < 8) {
                Swal.showValidationMessage('El teléfono debe tener al menos 8 dígitos');
                return false;
            }
            
            return { nombre, telefono };
        }
    });
    
    if (formValues) {
        document.getElementById('cliente_nombre').value = formValues.nombre;
        document.getElementById('cliente_telefono').value = formValues.telefono;
        document.getElementById('cliente_id').value = '';
        
        mostrarClienteSeleccionado(formValues.nombre, formValues.telefono, false);
    }
});

// ================================================
// FIN BÚSQUEDA DE CLIENTES
// ================================================

// Establecer fecha mínima (mañana)
const mañana = new Date();
mañana.setDate(mañana.getDate() + 1);
document.getElementById('fecha_entrega_prometida').min = mañana.toISOString().split('T')[0];

// Submit del formulario
document.getElementById('formTrabajo').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const conPiedra = document.querySelector('input[name="con_piedra"]:checked').value;
    
    const datos = {
        cliente_nombre: document.getElementById('cliente_nombre').value.trim(),
        cliente_telefono: document.getElementById('cliente_telefono').value.trim(),
        material: document.getElementById('material').value,
        peso_gramos: document.getElementById('peso_gramos').value || null,
        largo_cm: document.getElementById('largo_cm').value || null,
        con_piedra: parseInt(conPiedra),
        estilo: document.getElementById('estilo').value.trim() || null,
        descripcion_pieza: document.getElementById('descripcion_pieza').value.trim(),
        tipo_trabajo: document.getElementById('tipo_trabajo').value,
        descripcion_trabajo: document.getElementById('descripcion_trabajo').value.trim(),
        precio_total: parseFloat(document.getElementById('precio_total').value),
        anticipo: parseFloat(document.getElementById('anticipo').value) || 0,
        fecha_entrega_prometida: document.getElementById('fecha_entrega_prometida').value,
        empleado_recibe_id: parseInt(document.getElementById('empleado_recibe_id').value),
        observaciones: document.getElementById('observaciones').value.trim() || null
    };
    
    // Validaciones
    if (!datos.cliente_nombre || !datos.cliente_telefono) {
        mostrarError('Debe seleccionar o crear un cliente');
        return;
    }
    
    if (!datos.material || !datos.descripcion_pieza) {
        mostrarError('Complete la descripción de la pieza');
        return;
    }
    
    if (!datos.tipo_trabajo || !datos.descripcion_trabajo) {
        mostrarError('Complete la descripción del trabajo');
        return;
    }
    
    if (!datos.precio_total || datos.precio_total <= 0) {
        mostrarError('Ingrese un precio total válido');
        return;
    }
    
    if (!datos.fecha_entrega_prometida || !datos.empleado_recibe_id) {
        mostrarError('Complete fecha de entrega y empleado que recibe');
        return;
    }
    
    const confirmacion = await confirmarAccion(
        '¿Crear este trabajo?',
        `Cliente: ${datos.cliente_nombre}<br>Total: ${formatearMoneda(datos.precio_total)}`
    );
    
    if (!confirmacion) return;
    
    try {
        mostrarCargando();
        
        const res = await fetch('/joyeria-torre-fuerte/api/taller/crear.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datos)
        });
        
        const resultado = await res.json();
        
        ocultarCargando();
        
        if (resultado.success) {
            await mostrarExito(`Trabajo creado: ${resultado.data.codigo}`);
            window.location.href = 'ver.php?id=' + resultado.data.id;
        } else {
            mostrarError(resultado.message || 'Error al crear trabajo');
        }
        
    } catch (error) {
        ocultarCargando();
        console.error('Error:', error);
        mostrarError('Error: ' + error.message);
    }
});

document.addEventListener('DOMContentLoaded', cargarEmpleados);
</script>