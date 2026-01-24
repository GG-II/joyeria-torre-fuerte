<?php
// ================================================
// MÓDULO CLIENTES - EDITAR (CORREGIDO)
// ================================================

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

// Verificar autenticación y permisos
requiere_autenticacion();
requiere_rol(['administrador', 'dueño', 'vendedor']);

// Obtener ID del cliente (dummy)
$cliente_id = $_GET['id'] ?? 1;

// Datos dummy del cliente a editar (CAMPOS REALES DEL SCHEMA)
$cliente = [
    'id' => $cliente_id,
    'nombre' => 'María García López',
    'nit' => '12345678-9',
    'telefono' => '5512-3456',
    'email' => 'maria.garcia@email.com',
    'direccion' => 'Zona 10, Ciudad de Guatemala',
    'tipo_cliente' => 'publico',
    'tipo_mercaderias' => 'ambas',
    'limite_credito' => 5000.00,
    'plazo_credito_dias' => 30,
    'saldo_creditos' => 0.00,  // Viene de JOIN con creditos_clientes
    'activo' => 1,
    'fecha_creacion' => '2024-01-15'
];

// Título de página
$titulo_pagina = 'Editar Cliente';

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
            <li class="breadcrumb-item active">Editar Cliente</li>
        </ol>
    </nav>

    <!-- Encabezado -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1>
                    <i class="bi bi-pencil-square"></i>
                    Editar Cliente
                </h1>
                <p class="text-muted">Cliente #<?php echo $cliente['id']; ?></p>
            </div>
            <div class="col-md-6 text-end">
                <a href="ver.php?id=<?php echo $cliente['id']; ?>" class="btn btn-info">
                    <i class="bi bi-eye"></i>
                    Ver Ficha
                </a>
            </div>
        </div>
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
                        <input type="hidden" name="id" value="<?php echo $cliente['id']; ?>">

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
                                   value="<?php echo htmlspecialchars($cliente['nombre']); ?>"
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
                                       value="<?php echo htmlspecialchars($cliente['nit']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="telefono" class="form-label">
                                    <i class="bi bi-phone"></i> Teléfono *
                                </label>
                                <input type="tel" 
                                       class="form-control" 
                                       id="telefono" 
                                       name="telefono" 
                                       value="<?php echo htmlspecialchars($cliente['telefono']); ?>"
                                       required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label">
                                    <i class="bi bi-envelope"></i> Email
                                </label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       value="<?php echo htmlspecialchars($cliente['email']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="tipo_cliente" class="form-label">
                                    <i class="bi bi-tag"></i> Tipo de Cliente *
                                </label>
                                <select class="form-select" id="tipo_cliente" name="tipo_cliente" required>
                                    <option value="publico" <?php echo $cliente['tipo_cliente'] == 'publico' ? 'selected' : ''; ?>>Público</option>
                                    <option value="mayorista" <?php echo $cliente['tipo_cliente'] == 'mayorista' ? 'selected' : ''; ?>>Mayorista</option>
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
                                      rows="2"><?php echo htmlspecialchars($cliente['direccion']); ?></textarea>
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
                                <option value="ambas" <?php echo $cliente['tipo_mercaderias'] == 'ambas' ? 'selected' : ''; ?>>Oro y Plata</option>
                                <option value="oro" <?php echo $cliente['tipo_mercaderias'] == 'oro' ? 'selected' : ''; ?>>Solo Oro</option>
                                <option value="plata" <?php echo $cliente['tipo_mercaderias'] == 'plata' ? 'selected' : ''; ?>>Solo Plata</option>
                            </select>
                        </div>

                        <hr class="my-4">

                        <!-- Información de Crédito -->
                        <h5 class="mb-3 text-primary">
                            <i class="bi bi-cash-coin"></i>
                            Información de Crédito
                        </h5>

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
                                       value="<?php echo $cliente['limite_credito']; ?>">
                                <small class="text-muted">
                                    Saldo actual: Q <?php echo number_format($cliente['saldo_creditos'], 2); ?>
                                </small>
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
                                       value="<?php echo $cliente['plazo_credito_dias']; ?>">
                            </div>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="activo" 
                                   name="activo"
                                   <?php echo $cliente['activo'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="activo">
                                Cliente activo
                            </label>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-between">
                            <a href="lista.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i>
                                Volver al Listado
                            </a>
                            <div class="d-flex gap-2">
                                <a href="ver.php?id=<?php echo $cliente['id']; ?>" class="btn btn-info">
                                    <i class="bi bi-eye"></i>
                                    Ver Ficha
                                </a>
                                <button type="submit" class="btn btn-primary">
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
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-info-circle"></i>
                    Información del Cliente
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>Cliente desde:</strong><br>
                        <?php echo date('d/m/Y', strtotime($cliente['fecha_creacion'])); ?>
                    </p>
                    <p class="mb-2">
                        <strong>Saldo pendiente:</strong><br>
                        <span class="<?php echo $cliente['saldo_creditos'] > 0 ? 'text-danger' : 'text-success'; ?> fw-bold">
                            Q <?php echo number_format($cliente['saldo_creditos'], 2); ?>
                        </span>
                    </p>
                    <p class="mb-2">
                        <strong>Crédito disponible:</strong><br>
                        <span class="text-success fw-bold">
                            Q <?php echo number_format($cliente['limite_credito'] - $cliente['saldo_creditos'], 2); ?>
                        </span>
                    </p>
                    <p class="mb-0">
                        <strong>Estado:</strong><br>
                        <?php if ($cliente['activo']): ?>
                            <span class="badge bg-success">Activo</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inactivo</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <!-- Advertencia -->
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i>
                <strong>Atención:</strong>
                <p class="mb-0 small">
                    Los cambios en el límite de crédito afectarán las futuras ventas a crédito del cliente.
                </p>
            </div>

            <!-- Acciones Adicionales -->
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-gear"></i>
                    Acciones
                </div>
                <div class="list-group list-group-flush">
                    <a href="ver.php?id=<?php echo $cliente['id']; ?>" class="list-group-item list-group-item-action">
                        <i class="bi bi-file-earmark-text"></i>
                        Ver historial de compras
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <i class="bi bi-credit-card"></i>
                        Gestionar créditos
                    </a>
                    <a href="#" class="list-group-item list-group-item-action text-danger">
                        <i class="bi bi-trash"></i>
                        Eliminar cliente
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validación del formulario
document.getElementById('formCliente').addEventListener('submit', function(e) {
    e.preventDefault();
    
    alert('Formulario listo para conectar con la API de actualización');
    
    const formData = new FormData(this);
    console.log('Datos actualizados:', Object.fromEntries(formData));
});

// Deshabilitar plazo si límite es 0
document.getElementById('limite_credito').addEventListener('input', function(e) {
    const plazoInput = document.getElementById('plazo_credito_dias');
    if (parseFloat(e.target.value) <= 0) {
        plazoInput.value = '';
        plazoInput.disabled = true;
    } else {
        plazoInput.disabled = false;
    }
});
</script>

<?php
// Incluir footer
include '../../includes/footer.php';
?>