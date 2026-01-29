<?php
/**
 * ================================================
 * MÓDULO CLIENTES - AGREGAR
 * ================================================
 * 
 * Vista para agregar un nuevo cliente al sistema.
 * Incluye información básica, preferencias comerciales y configuración de crédito.
 * 
 * TODO FASE 5: Conectar con API
 * POST /api/clientes/crear.php
 * 
 * Campos de la tabla clientes:
 * - nombre, nit, telefono, email
 * - direccion, tipo_cliente (publico/mayorista)
 * - tipo_mercaderias (oro/plata/ambas)
 * - limite_credito, plazo_credito_dias
 * - activo (boolean)
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

// Verificar autenticación y permisos
requiere_autenticacion();
requiere_rol(['administrador', 'dueño', 'vendedor', 'cajero']);

// Título de página
$titulo_pagina = 'Nuevo Cliente';

// Incluir header
include '../../includes/header.php';

// Incluir navbar
include '../../includes/navbar.php';
?>

<!-- Contenido Principal -->
<div class="container-fluid main-content">
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
            <li class="breadcrumb-item active">Nuevo Cliente</li>
        </ol>
    </nav>

    <!-- Encabezado -->
    <div class="page-header mb-4">
        <h1 class="mb-2">
            <i class="bi bi-person-plus"></i>
            Nuevo Cliente
        </h1>
        <p class="text-muted mb-0">Complete los datos del nuevo cliente</p>
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
                                   placeholder="Ej: María García López"
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
                                       name="nit" 
                                       placeholder="12345678-9 o CF"
                                       value="CF">
                                <div class="form-text">Use "CF" para consumidor final</div>
                            </div>
                            <div class="col-md-6">
                                <label for="telefono" class="form-label">
                                    <i class="bi bi-phone"></i> Teléfono *
                                </label>
                                <input type="tel" 
                                       class="form-control" 
                                       id="telefono" 
                                       name="telefono" 
                                       placeholder="5512-3456"
                                       maxlength="9"
                                       required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label">
                                    <i class="bi bi-envelope"></i> Email (Opcional)
                                </label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       placeholder="cliente@ejemplo.com">
                            </div>
                            <div class="col-md-6">
                                <label for="tipo_cliente" class="form-label">
                                    <i class="bi bi-tag"></i> Tipo de Cliente *
                                </label>
                                <select class="form-select" id="tipo_cliente" name="tipo_cliente" required>
                                    <option value="publico" selected>Público</option>
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
                                      rows="2" 
                                      placeholder="Dirección completa del cliente"></textarea>
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
                                <option value="ambas" selected>Oro y Plata</option>
                                <option value="oro">Solo Oro</option>
                                <option value="plata">Solo Plata</option>
                            </select>
                            <div class="form-text">Tipo de productos que normalmente compra</div>
                        </div>

                        <hr class="my-4">

                        <!-- Información de Crédito -->
                        <h5 class="mb-3 text-primary">
                            <i class="bi bi-cash-coin"></i>
                            Información de Crédito
                        </h5>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            Configure el límite de crédito solo si el cliente comprará a crédito.
                        </div>

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
                                       step="0.01" 
                                       value="0.00"
                                       placeholder="0.00">
                                <div class="form-text">Monto máximo que puede deber</div>
                            </div>
                            <div class="col-md-6">
                                <label for="plazo_credito_dias" class="form-label">
                                    <i class="bi bi-calendar-check"></i> Plazo de Crédito (días)
                                </label>
                                <input type="number" 
                                       class="form-control" 
                                       id="plazo_credito_dias" 
                                       name="plazo_credito_dias" 
                                       min="0"
                                       placeholder="15, 30, 60, 90..."
                                       value="15"
                                       disabled>
                                <div class="form-text">Días de plazo para pago</div>
                            </div>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="activo" 
                                   name="activo" 
                                   checked>
                            <label class="form-check-label" for="activo">
                                Cliente activo
                            </label>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex flex-column flex-sm-row justify-content-end gap-2">
                            <a href="lista.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary" id="btnGuardar">
                                <i class="bi bi-save"></i>
                                Guardar Cliente
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Panel Lateral -->
        <div class="col-lg-4">
            <!-- Ayuda -->
            <div class="card mb-3 shadow-sm">
                <div class="card-header" style="background-color: #1e3a8a; color: white;">
                    <i class="bi bi-lightbulb"></i>
                    Ayuda
                </div>
                <div class="card-body">
                    <h6 class="fw-bold">Datos Requeridos</h6>
                    <ul class="mb-3">
                        <li>Nombre completo</li>
                        <li>Teléfono de contacto</li>
                        <li>Tipo de cliente</li>
                        <li>Tipo de mercaderías</li>
                    </ul>

                    <h6 class="fw-bold">Tipos de Cliente</h6>
                    <ul class="mb-3">
                        <li><strong>Público:</strong> Cliente final</li>
                        <li><strong>Mayorista:</strong> Compras al por mayor</li>
                    </ul>

                    <h6 class="fw-bold">Tipo de Mercaderías</h6>
                    <p class="small mb-3">
                        Indica qué productos suele comprar el cliente:
                        <br>• <strong>Oro:</strong> Solo joyas de oro
                        <br>• <strong>Plata:</strong> Solo joyas de plata
                        <br>• <strong>Ambas:</strong> Oro y plata
                    </p>

                    <h6 class="fw-bold">Crédito</h6>
                    <p class="small mb-0">
                        El límite de crédito define cuánto puede deber el cliente. 
                        Use Q 0.00 si no se otorga crédito.
                    </p>
                </div>
            </div>

            <!-- Recordatorio -->
            <div class="alert alert-warning shadow-sm">
                <i class="bi bi-exclamation-triangle"></i>
                <strong>Importante:</strong>
                <p class="mb-0 small">
                    Verifique que los datos sean correctos antes de guardar. 
                    El NIT será usado para facturación.
                </p>
            </div>
        </div>
    </div>
</div>

<style>
/* ============================================
   ESTILOS ESPECÍFICOS AGREGAR CLIENTE
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

.form-check-label {
    padding-left: 0.5rem;
}

/* Secciones */
h5.text-primary {
    color: #1e3a8a !important;
    font-weight: 600;
}

hr {
    opacity: 0.1;
}

/* Alertas */
.alert {
    border-radius: 8px;
    border-left: 4px solid;
}

.alert-info {
    border-left-color: #0ea5e9;
}

.alert-warning {
    border-left-color: #eab308;
}

/* Panel de ayuda */
.card-body h6 {
    color: #1a1a1a;
    font-size: 0.95rem;
    margin-bottom: 0.75rem;
}

.card-body ul {
    padding-left: 20px;
}

.card-body ul li {
    margin-bottom: 0.35rem;
}

/* Botones */
.btn {
    padding: 0.625rem 1.25rem;
    font-weight: 500;
    border-radius: 6px;
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
    
    .form-label {
        font-size: 0.9rem;
    }
    
    .btn {
        width: 100%;
        padding: 0.75rem;
    }
    
    /* Ayuda más compacta */
    .card-body h6 {
        font-size: 0.9rem;
    }
    
    .card-body .small {
        font-size: 0.8rem;
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

/* Animaciones */
.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
</style>

<script>
/**
 * ================================================
 * JAVASCRIPT - FORMULARIO CLIENTE
 * ================================================
 */

/**
 * Validación y envío del formulario
 * TODO FASE 5: Conectar con API
 */
document.getElementById('formCliente').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validaciones adicionales
    const telefono = document.getElementById('telefono').value;
    if (telefono.replace(/\D/g, '').length !== 8) {
        mostrarAlerta('El teléfono debe tener 8 dígitos', 'warning');
        document.getElementById('telefono').focus();
        return;
    }
    
    const limiteCredito = parseFloat(document.getElementById('limite_credito').value) || 0;
    const plazoDias = parseInt(document.getElementById('plazo_credito_dias').value) || 0;
    
    if (limiteCredito > 0 && plazoDias <= 0) {
        mostrarAlerta('Debe especificar el plazo de crédito en días', 'warning');
        document.getElementById('plazo_credito_dias').focus();
        return;
    }
    
    // Preparar datos
    const formData = new FormData(this);
    const datos = {
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
    fetch('<?php echo BASE_URL; ?>api/clientes/crear.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(datos)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarAlerta('Cliente registrado exitosamente', 'success');
            setTimeout(() => {
                window.location.href = 'ver.php?id=' + data.cliente_id;
            }, 1500);
        } else {
            mostrarAlerta(data.message, 'error');
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = '<i class="bi bi-save"></i> Guardar Cliente';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarAlerta('Error al guardar el cliente', 'error');
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = '<i class="bi bi-save"></i> Guardar Cliente';
    });
    */
    
    // Modo desarrollo
    console.log('Datos del cliente:', datos);
    setTimeout(() => {
        alert('MODO DESARROLLO: Cliente listo para guardar.\n\nDatos:\n' + JSON.stringify(datos, null, 2));
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = '<i class="bi bi-save"></i> Guardar Cliente';
    }, 1000);
});

/**
 * Formatear teléfono automáticamente (XXXX-XXXX)
 */
document.getElementById('telefono').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 4) {
        value = value.substring(0, 4) + '-' + value.substring(4, 8);
    }
    e.target.value = value;
});

/**
 * Formatear NIT automáticamente (solo números, K y guión)
 */
document.getElementById('nit').addEventListener('input', function(e) {
    let value = e.target.value.toUpperCase().replace(/[^0-9K-]/g, '');
    e.target.value = value;
});

/**
 * Habilitar/deshabilitar plazo según límite de crédito
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
 * Validar email en tiempo real
 */
document.getElementById('email').addEventListener('blur', function(e) {
    const email = e.target.value.trim();
    if (email && !validarEmail(email)) {
        mostrarAlerta('El formato del email no es válido', 'warning');
        e.target.focus();
    }
});

/**
 * Utilidades
 */
function validarEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function mostrarAlerta(mensaje, tipo) {
    // TODO: Implementar sistema de notificaciones
    alert(mensaje);
}

// Auto-mayúscula en nombre
document.getElementById('nombre').addEventListener('blur', function(e) {
    const palabras = e.target.value.toLowerCase().split(' ');
    const nombreCapitalizado = palabras.map(palabra => 
        palabra.charAt(0).toUpperCase() + palabra.slice(1)
    ).join(' ');
    e.target.value = nombreCapitalizado;
});
</script>

<?php
// Incluir footer
include '../../includes/footer.php';
?>