<?php
// ================================================
// MÓDULO CLIENTES - AGREGAR (CORREGIDO)
// ================================================

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
    <nav aria-label="breadcrumb">
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
    <div class="page-header">
        <h1>
            <i class="bi bi-person-plus"></i>
            Nuevo Cliente
        </h1>
        <p class="text-muted">Complete los datos del nuevo cliente</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Formulario -->
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-pencil-square"></i>
                    Datos del Cliente
                </div>
                <div class="card-body">
                    <form id="formCliente" method="POST" action="">
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

                        <div class="row mb-3">
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
                                <small class="text-muted">Use "CF" para consumidor final</small>
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
                                       required>
                            </div>
                        </div>

                        <div class="row mb-3">
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

                        <div class="mb-3">
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

                        <div class="mb-3">
                            <label for="tipo_mercaderias" class="form-label">
                                <i class="bi bi-box-seam"></i> Tipo de Mercaderías *
                            </label>
                            <select class="form-select" id="tipo_mercaderias" name="tipo_mercaderias" required>
                                <option value="ambas" selected>Oro y Plata</option>
                                <option value="oro">Solo Oro</option>
                                <option value="plata">Solo Plata</option>
                            </select>
                            <small class="text-muted">Tipo de productos que normalmente compra</small>
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

                        <div class="row mb-3">
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
                                <small class="text-muted">Monto máximo que puede deber</small>
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
                                       value="15">
                                <small class="text-muted">Días de plazo para pago</small>
                            </div>
                        </div>

                        <div class="form-check mb-3">
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
                        <div class="d-flex justify-content-end gap-2">
                            <a href="lista.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
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
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <i class="bi bi-lightbulb"></i>
                    Ayuda
                </div>
                <div class="card-body">
                    <h6 class="fw-bold">Datos Requeridos</h6>
                    <ul class="mb-3">
                        <li>Nombre completo</li>
                        <li>Teléfono de contacto</li>
                        <li>Tipo de cliente</li>
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
            <div class="alert alert-warning">
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

<script>
// Validación del formulario
document.getElementById('formCliente').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Aquí se conectará con la API
    alert('Formulario listo para conectar con la API');
    
    const formData = new FormData(this);
    console.log('Datos del formulario:', Object.fromEntries(formData));
});

// Formatear teléfono automáticamente
document.getElementById('telefono').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 4) {
        value = value.substring(0, 4) + '-' + value.substring(4, 8);
    }
    e.target.value = value;
});

// Formatear NIT automáticamente
document.getElementById('nit').addEventListener('input', function(e) {
    let value = e.target.value.toUpperCase().replace(/[^0-9K-]/g, '');
    e.target.value = value;
});

// Deshabilitar plazo si límite es 0
document.getElementById('limite_credito').addEventListener('input', function(e) {
    const plazoInput = document.getElementById('plazo_credito_dias');
    if (parseFloat(e.target.value) <= 0) {
        plazoInput.value = '';
        plazoInput.disabled = true;
    } else {
        plazoInput.disabled = false;
        if (!plazoInput.value) {
            plazoInput.value = 15;
        }
    }
});
</script>

<?php
// Incluir footer
include '../../includes/footer.php';
?>