<?php
/**
 * ================================================
 * MÓDULO CLIENTES - EDITAR
 * ================================================
 * 
 * Vista para editar un cliente existente.
 * Carga los datos actuales y permite actualizarlos.
 * 
 * TODO FASE 5: Conectar con APIs
 * GET /api/clientes/ver.php?id={cliente_id} - Cargar datos
 * POST /api/clientes/actualizar.php - Guardar cambios
 * 
 * Campos editables:
 * - nombre, nit, telefono, email
 * - direccion, tipo_cliente, tipo_mercaderias
 * - limite_credito, plazo_credito_dias, activo
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

// Verificar autenticación y permisos
requiere_autenticacion();
requiere_rol(['administrador', 'dueño', 'vendedor']);

// Obtener ID del cliente
$cliente_id = $_GET['id'] ?? null;

if (!$cliente_id) {
    header('Location: lista.php');
    exit;
}

// TODO FASE 5: Cargar datos desde API
$cliente = null;

// Título de página
$titulo_pagina = 'Editar Cliente';

// Incluir header
include '../../includes/header.php';

// Incluir navbar
include '../../includes/navbar.php';
?>

<!-- Contenido Principal -->
<div class="container-fluid main-content">
    <!-- Estado de carga -->
    <div id="loadingState" class="text-center py-5">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
        <p class="mt-3 text-muted">Cargando datos del cliente...</p>
    </div>

    <!-- Contenido principal -->
    <div id="mainContent" style="display: none;">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="<?php echo BASE_URL; ?>dashboard.php">
                        <i class="bi bi-house"></i> Dashboard
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a href="lista.php">
                        <i class="bi bi-people"></i> Clientes
                    </a>
                </li>
                <li class="breadcrumb-item active">Editar Cliente</li>
            </ol>
        </nav>

        <!-- Encabezado -->
        <div class="page-header mb-4">
            <div class="row align-items-center g-3">
                <div class="col-md-6">
                    <h1 class="mb-2">
                        <i class="bi bi-pencil-square"></i>
                        Editar Cliente
                    </h1>
                    <p class="text-muted mb-0" id="clienteSubtitulo">Cliente #-</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="ver.php?id=<?php echo $cliente_id; ?>" class="btn btn-info">
                        <i class="bi bi-eye"></i>
                        <span class="d-none d-sm-inline">Ver Ficha</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-8">
                <!-- Formulario -->
                <div class="card shadow-sm">
                    <div class="card-header" style="background-color: #1e3a8a; color: white;">
                        <i class="bi bi-pencil-square"></i>
                        Datos del Cliente
                    </div>
                    <div class="card-body">
                        <form id="formCliente" method="POST">
                            <input type="hidden" name="id" value="<?php echo $cliente_id; ?>">

                            <!-- Información Básica -->
                            <h5 class="mb-3 text-primary">
                                <i class="bi bi-person-badge"></i>
                                Información Básica
                            </h5>

                            <div class="mb-3">
                                <label for="nombre" class="form-label">
                                    <i class="bi bi-person"></i> Nombre Completo *
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="nombre" 
                                       name="nombre" 
                                       required>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="nit" class="form-label">
                                        <i class="bi bi-card-text"></i> NIT
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="nit" 
                                           name="nit">
                                </div>
                                <div class="col-md-6">
                                    <label for="telefono" class="form-label">
                                        <i class="bi bi-phone"></i> Teléfono *
                                    </label>
                                    <input type="tel" 
                                           class="form-control" 
                                           id="telefono" 
                                           name="telefono" 
                                           maxlength="9"
                                           required>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="email" class="form-label">
                                        <i class="bi bi-envelope"></i> Email
                                    </label>
                                    <input type="email" 
                                           class="form-control" 
                                           id="email" 
                                           name="email">
                                </div>
                                <div class="col-md-6">
                                    <label for="tipo_cliente" class="form-label">
                                        <i class="bi bi-tag"></i> Tipo de Cliente *
                                    </label>
                                    <select class="form-select" id="tipo_cliente" name="tipo_cliente" required>
                                        <option value="publico">Público</option>
                                        <option value="mayorista">Mayorista</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="direccion" class="form-label">
                                    <i class="bi bi-geo-alt"></i> Dirección
                                </label>
                                <textarea class="form-control" 
                                          id="direccion" 
                                          name="direccion" 
                                          rows="2"></textarea>
                            </div>

                            <hr class="my-4">

                            <!-- Preferencias Comerciales -->
                            <h5 class="mb-3 text-primary">
                                <i class="bi bi-gem"></i>
                                Preferencias Comerciales
                            </h5>

                            <div class="mb-4">
                                <label for="tipo_mercaderias" class="form-label">
                                    <i class="bi bi-box-seam"></i> Tipo de Mercaderías *
                                </label>
                                <select class="form-select" id="tipo_mercaderias" name="tipo_mercaderias" required>
                                    <option value="ambas">Oro y Plata</option>
                                    <option value="oro">Solo Oro</option>
                                    <option value="plata">Solo Plata</option>
                                </select>
                            </div>

                            <hr class="my-4">

                            <!-- Información de Crédito -->
                            <h5 class="mb-3 text-primary">
                                <i class="bi bi-cash-coin"></i>
                                Información de Crédito
                            </h5>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="limite_credito" class="form-label">
                                        <i class="bi bi-currency-dollar"></i> Límite de Crédito (Q)
                                    </label>
                                    <input type="number" 
                                           class="form-control" 
                                           id="limite_credito" 
                                           name="limite_credito" 
                                           min="0" 
                                           step="0.01">
                                    <div class="form-text">
                                        Saldo actual: <strong id="saldoActual">Q 0.00</strong>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="plazo_credito_dias" class="form-label">
                                        <i class="bi bi-calendar-check"></i> Plazo de Crédito (días)
                                    </label>
                                    <input type="number" 
                                           class="form-control" 
                                           id="plazo_credito_dias" 
                                           name="plazo_credito_dias" 
                                           min="0">
                                </div>
                            </div>

                            <div class="form-check mb-4">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="activo" 
                                       name="activo">
                                <label class="form-check-label" for="activo">
                                    Cliente activo
                                </label>
                            </div>

                            <!-- Botones -->
                            <div class="d-flex flex-column flex-sm-row justify-content-between gap-2">
                                <a href="lista.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i>
                                    Volver al Listado
                                </a>
                                <div class="d-flex flex-column flex-sm-row gap-2">
                                    <a href="ver.php?id=<?php echo $cliente_id; ?>" class="btn btn-info">
                                        <i class="bi bi-eye"></i>
                                        Ver Ficha
                                    </a>
                                    <button type="submit" class="btn btn-primary" id="btnGuardar">
                                        <i class="bi bi-save"></i>
                                        Guardar Cambios
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Panel Lateral -->
            <div class="col-lg-4">
                <!-- Información del Cliente -->
                <div class="card mb-3 shadow-sm">
                    <div class="card-header" style="background-color: #1e3a8a; color: white;">
                        <i class="bi bi-info-circle"></i>
                        Información del Cliente
                    </div>
                    <div class="card-body" id="infoCliente">
                        <div class="text-center text-muted py-3">
                            <div class="spinner-border spinner-border-sm"></div>
                            <p class="mt-2 mb-0 small">Cargando...</p>
                        </div>
                    </div>
                </div>

                <!-- Advertencia -->
                <div class="alert alert-warning shadow-sm">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Atención:</strong>
                    <p class="mb-0 small">
                        Los cambios en el límite de crédito afectarán las futuras ventas a crédito del cliente.
                    </p>
                </div>

                <!-- Acciones Adicionales -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <i class="bi bi-gear"></i>
                        Acciones
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="ver.php?id=<?php echo $cliente_id; ?>" class="list-group-item list-group-item-action">
                            <i class="bi bi-file-earmark-text"></i>
                            Ver historial de compras
                        </a>
                        <a href="#" class="list-group-item list-group-item-action" onclick="gestionarCreditos(); return false;">
                            <i class="bi bi-credit-card"></i>
                            Gestionar créditos
                        </a>
                        <?php if (tiene_permiso('clientes', 'eliminar')): ?>
                        <a href="#" class="list-group-item list-group-item-action text-danger" onclick="eliminarCliente(); return false;">
                            <i class="bi bi-trash"></i>
                            Eliminar cliente
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Error al cargar -->
    <div id="errorState" style="display: none;" class="text-center py-5">
        <i class="bi bi-exclamation-triangle text-danger" style="font-size: 48px;"></i>
        <h4 class="mt-3">Error al cargar el cliente</h4>
        <p class="text-muted" id="errorMessage">No se pudo cargar la información del cliente.</p>
        <a href="lista.php" class="btn btn-primary mt-3">
            <i class="bi bi-arrow-left"></i>
            Volver al listado
        </a>
    </div>
</div>

<style>
/* ============================================
   ESTILOS ESPECÍFICOS EDITAR CLIENTE
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

.card-body {
    padding: 25px;
}

/* Formulario */
.form-label {
    font-weight: 500;
    margin-bottom: 0.5rem;
    color: #374151;
}

.form-control,
.form-select {
    border: 1px solid #d1d5db;
    border-radius: 6px;
    padding: 0.625rem 0.75rem;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-control:focus,
.form-select:focus {
    border-color: #1e3a8a;
    box-shadow: 0 0 0 0.2rem rgba(30, 58, 138, 0.15);
}

textarea.form-control {
    resize: vertical;
}

.form-text {
    font-size: 0.875rem;
    color: #6b7280;
    margin-top: 0.25rem;
}

.form-check-input {
    width: 1.2em;
    height: 1.2em;
    margin-top: 0.15em;
}

.form-check-input:checked {
    background-color: #1e3a8a;
    border-color: #1e3a8a;
}

/* Secciones */
h5.text-primary {
    color: #1e3a8a !important;
    font-weight: 600;
}

hr {
    opacity: 0.1;
}

/* Info panel */
.card-body > div:not(:last-child) {
    padding-bottom: 12px;
    margin-bottom: 12px;
    border-bottom: 1px solid #e5e7eb;
}

/* List group actions */
.list-group-item {
    transition: background-color 0.15s ease;
}

.list-group-item:hover {
    background-color: #f3f4f6;
}

/* Alertas */
.alert {
    border-radius: 8px;
    border-left: 4px solid;
}

.alert-warning {
    border-left-color: #eab308;
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
    
    .card-body {
        padding: 15px;
    }
    
    h5 {
        font-size: 1.1rem;
    }
    
    .btn {
        width: 100%;
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
    .form-select,
    textarea {
        min-height: 44px;
    }
    
    .form-check-input {
        width: 1.35em;
        height: 1.35em;
    }
}
</style>

<script>
/**
 * ================================================
 * JAVASCRIPT - EDITAR CLIENTE
 * ================================================
 */

// Cargar datos al iniciar
document.addEventListener('DOMContentLoaded', function() {
    cargarDatosCliente();
});

/**
 * Cargar datos del cliente
 * TODO FASE 5: Conectar con API
 */
function cargarDatosCliente() {
    const clienteId = <?php echo $cliente_id; ?>;
    
    // TODO FASE 5: Descomentar y conectar
    /*
    fetch(`<?php echo BASE_URL; ?>api/clientes/ver.php?id=${clienteId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                llenarFormulario(data.data);
                mostrarInfoCliente(data.data);
            } else {
                mostrarError(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarError('Error al cargar los datos del cliente');
        });
    */
    
    // Simular carga
    setTimeout(() => {
        mostrarError('MODO DESARROLLO: Esperando conexión con API');
    }, 1500);
}

/**
 * Llenar formulario con datos del cliente
 */
function llenarFormulario(cliente) {
    document.getElementById('loadingState').style.display = 'none';
    document.getElementById('mainContent').style.display = 'block';
    
    document.getElementById('clienteSubtitulo').textContent = 'Cliente #' + cliente.id;
    document.getElementById('nombre').value = cliente.nombre;
    document.getElementById('nit').value = cliente.nit || '';
    document.getElementById('telefono').value = cliente.telefono;
    document.getElementById('email').value = cliente.email || '';
    document.getElementById('direccion').value = cliente.direccion || '';
    document.getElementById('tipo_cliente').value = cliente.tipo_cliente;
    document.getElementById('tipo_mercaderias').value = cliente.tipo_mercaderias;
    document.getElementById('limite_credito').value = cliente.limite_credito;
    document.getElementById('plazo_credito_dias').value = cliente.plazo_credito_dias;
    document.getElementById('activo').checked = cliente.activo == 1;
    
    // Actualizar saldo actual
    document.getElementById('saldoActual').textContent = 'Q ' + formatearMoneda(cliente.saldo_creditos || 0);
    
    // Deshabilitar plazo si límite es 0
    if (parseFloat(cliente.limite_credito) <= 0) {
        document.getElementById('plazo_credito_dias').disabled = true;
    }
}

/**
 * Mostrar información del cliente en panel lateral
 */
function mostrarInfoCliente(cliente) {
    const container = document.getElementById('infoCliente');
    const creditoDisponible = parseFloat(cliente.limite_credito) - parseFloat(cliente.saldo_creditos || 0);
    
    container.innerHTML = `
        <div>
            <small class="text-muted d-block">Cliente desde:</small>
            <strong>${formatearFecha(cliente.fecha_creacion)}</strong>
        </div>
        <div>
            <small class="text-muted d-block">Saldo pendiente:</small>
            <span class="${cliente.saldo_creditos > 0 ? 'text-danger' : 'text-success'} fw-bold">
                Q ${formatearMoneda(cliente.saldo_creditos || 0)}
            </span>
        </div>
        <div>
            <small class="text-muted d-block">Crédito disponible:</small>
            <span class="text-success fw-bold">
                Q ${formatearMoneda(creditoDisponible)}
            </span>
        </div>
        <div>
            <small class="text-muted d-block">Estado:</small>
            ${cliente.activo == 1 ? 
                '<span class="badge bg-success">Activo</span>' : 
                '<span class="badge bg-secondary">Inactivo</span>'
            }
        </div>
    `;
}

/**
 * Mostrar error
 */
function mostrarError(mensaje) {
    document.getElementById('loadingState').style.display = 'none';
    document.getElementById('errorState').style.display = 'block';
    document.getElementById('errorMessage').textContent = mensaje;
}

/**
 * Validación y envío del formulario
 * TODO FASE 5: Conectar con API
 */
document.getElementById('formCliente').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validaciones
    const telefono = document.getElementById('telefono').value;
    if (telefono.replace(/\D/g, '').length !== 8) {
        mostrarAlerta('El teléfono debe tener 8 dígitos', 'warning');
        return;
    }
    
    const limiteCredito = parseFloat(document.getElementById('limite_credito').value) || 0;
    const plazoDias = parseInt(document.getElementById('plazo_credito_dias').value) || 0;
    
    if (limiteCredito > 0 && plazoDias <= 0) {
        mostrarAlerta('Debe especificar el plazo de crédito', 'warning');
        return;
    }
    
    // Preparar datos
    const formData = new FormData(this);
    const datos = {
        id: formData.get('id'),
        nombre: formData.get('nombre'),
        nit: formData.get('nit'),
        telefono: formData.get('telefono'),
        email: formData.get('email') || null,
        direccion: formData.get('direccion') || null,
        tipo_cliente: formData.get('tipo_cliente'),
        tipo_mercaderias: formData.get('tipo_mercaderias'),
        limite_credito: limiteCredito,
        plazo_credito_dias: plazoDias,
        activo: formData.get('activo') ? 1 : 0
    };
    
    // Deshabilitar botón
    const btnGuardar = document.getElementById('btnGuardar');
    btnGuardar.disabled = true;
    btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
    
    // TODO FASE 5: Descomentar y conectar
    /*
    fetch('<?php echo BASE_URL; ?>api/clientes/actualizar.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(datos)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarAlerta('Cliente actualizado exitosamente', 'success');
            setTimeout(() => {
                window.location.href = 'ver.php?id=' + datos.id;
            }, 1500);
        } else {
            mostrarAlerta(data.message, 'error');
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = '<i class="bi bi-save"></i> Guardar Cambios';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarAlerta('Error al actualizar el cliente', 'error');
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = '<i class="bi bi-save"></i> Guardar Cambios';
    });
    */
    
    // Modo desarrollo
    console.log('Datos actualizados:', datos);
    setTimeout(() => {
        alert('MODO DESARROLLO: Cambios listos para guardar.\n\nDatos:\n' + JSON.stringify(datos, null, 2));
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = '<i class="bi bi-save"></i> Guardar Cambios';
    }, 1000);
});

/**
 * Formatear teléfono
 */
document.getElementById('telefono').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 4) {
        value = value.substring(0, 4) + '-' + value.substring(4, 8);
    }
    e.target.value = value;
});

/**
 * Formatear NIT
 */
document.getElementById('nit').addEventListener('input', function(e) {
    let value = e.target.value.toUpperCase().replace(/[^0-9K-]/g, '');
    e.target.value = value;
});

/**
 * Habilitar/deshabilitar plazo según límite
 */
document.getElementById('limite_credito').addEventListener('input', function(e) {
    const plazoInput = document.getElementById('plazo_credito_dias');
    const limiteCredito = parseFloat(e.target.value) || 0;
    
    if (limiteCredito <= 0) {
        plazoInput.value = '';
        plazoInput.disabled = true;
    } else {
        plazoInput.disabled = false;
        if (!plazoInput.value) {
            plazoInput.value = 15;
        }
    }
});

/**
 * Acciones adicionales
 */
function gestionarCreditos() {
    alert('Funcionalidad de gestión de créditos - Pendiente de implementar');
}

function eliminarCliente() {
    if (confirm('¿Está seguro de eliminar este cliente?\n\nEsta acción no se puede deshacer.')) {
        alert('Funcionalidad de eliminar cliente - Pendiente de implementar en Fase 5');
    }
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

function mostrarAlerta(mensaje, tipo) {
    // TODO: Implementar sistema de notificaciones
    alert(mensaje);
}
</script>

<?php
// Incluir footer
include '../../includes/footer.php';
?>