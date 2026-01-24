<?php
// ================================================
// MÓDULO TALLER - EDITAR TRABAJO
// ================================================

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

// Verificar autenticación y permisos
requiere_autenticacion();
requiere_rol(['administrador', 'dueño', 'orfebre']);

// Obtener ID del trabajo (dummy)
$trabajo_id = $_GET['id'] ?? 1;

// Datos dummy del trabajo (CAMPOS REALES)
$trabajo = [
    'id' => $trabajo_id,
    'codigo' => 'T-2025-001',
    'cliente_nombre' => 'María García López',
    'cliente_telefono' => '5512-3456',
    'cliente_id' => 1,
    'material' => 'oro',
    'peso_gramos' => 5.500,
    'largo_cm' => null,
    'con_piedra' => 1,
    'estilo' => 'Clásico',
    'descripcion_pieza' => 'Anillo de compromiso oro 18K',
    'tipo_trabajo' => 'reparacion',
    'descripcion_trabajo' => 'Reparar soldadura rota en aro inferior',
    'precio_total' => 350.00,
    'anticipo' => 150.00,
    'saldo' => 200.00,
    'fecha_recepcion' => '2025-01-20 09:30:00',
    'fecha_entrega_prometida' => '2025-01-25',
    'fecha_entrega_real' => null,
    'empleado_recibe_id' => 1,
    'empleado_actual_id' => 3,
    'empleado_entrega_id' => null,
    'estado' => 'en_proceso',
    'observaciones' => 'Cliente pidió trato delicado con la piedra'
];

// Título de página
$titulo_pagina = 'Editar Trabajo';

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
                    <i class="bi bi-tools"></i> Taller
                </a>
            </li>
            <li class="breadcrumb-item active">Editar Trabajo</li>
        </ol>
    </nav>

    <!-- Encabezado -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1>
                    <i class="bi bi-pencil-square"></i>
                    Editar Trabajo
                </h1>
                <p class="text-muted">Código: <?php echo $trabajo['codigo']; ?></p>
            </div>
            <div class="col-md-6 text-end">
                <a href="ver.php?id=<?php echo $trabajo['id']; ?>" class="btn btn-info">
                    <i class="bi bi-eye"></i>
                    Ver Detalles
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
                    Información del Trabajo
                </div>
                <div class="card-body">
                    <form id="formTrabajo" method="POST" action="">
                        <input type="hidden" name="id" value="<?php echo $trabajo['id']; ?>">

                        <!-- Cliente (solo lectura) -->
                        <h5 class="mb-3 text-primary">
                            <i class="bi bi-person"></i>
                            Datos del Cliente
                        </h5>

                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label class="form-label">Nombre del Cliente</label>
                                <input type="text" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($trabajo['cliente_nombre']); ?>"
                                       readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Teléfono</label>
                                <input type="text" 
                                       class="form-control" 
                                       value="<?php echo $trabajo['cliente_telefono']; ?>"
                                       readonly>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Estado del Trabajo -->
                        <h5 class="mb-3 text-primary">
                            <i class="bi bi-clipboard-check"></i>
                            Estado del Trabajo
                        </h5>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="estado" class="form-label">
                                    <i class="bi bi-flag"></i> Estado *
                                </label>
                                <select class="form-select" id="estado" name="estado" required>
                                    <option value="recibido" <?php echo $trabajo['estado'] == 'recibido' ? 'selected' : ''; ?>>Recibido</option>
                                    <option value="en_proceso" <?php echo $trabajo['estado'] == 'en_proceso' ? 'selected' : ''; ?>>En Proceso</option>
                                    <option value="completado" <?php echo $trabajo['estado'] == 'completado' ? 'selected' : ''; ?>>Completado</option>
                                    <option value="entregado" <?php echo $trabajo['estado'] == 'entregado' ? 'selected' : ''; ?>>Entregado</option>
                                    <option value="cancelado" <?php echo $trabajo['estado'] == 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="empleado_actual_id" class="form-label">
                                    <i class="bi bi-person-workspace"></i> Orfebre Asignado *
                                </label>
                                <select class="form-select" id="empleado_actual_id" name="empleado_actual_id" required>
                                    <option value="3" <?php echo $trabajo['empleado_actual_id'] == 3 ? 'selected' : ''; ?>>Roberto Orfebre</option>
                                    <option value="4" <?php echo $trabajo['empleado_actual_id'] == 4 ? 'selected' : ''; ?>>Juan Artesano</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="fecha_entrega_prometida" class="form-label">
                                    <i class="bi bi-calendar-event"></i> Fecha Entrega Prometida *
                                </label>
                                <input type="date" 
                                       class="form-control" 
                                       id="fecha_entrega_prometida" 
                                       name="fecha_entrega_prometida" 
                                       value="<?php echo $trabajo['fecha_entrega_prometida']; ?>"
                                       required>
                            </div>
                            <div class="col-md-6" id="fechaEntregaRealContainer" style="display: none;">
                                <label for="fecha_entrega_real" class="form-label">
                                    <i class="bi bi-check-circle"></i> Fecha Entrega Real
                                </label>
                                <input type="datetime-local" 
                                       class="form-control" 
                                       id="fecha_entrega_real" 
                                       name="fecha_entrega_real">
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Descripción del Trabajo -->
                        <h5 class="mb-3 text-primary">
                            <i class="bi bi-file-text"></i>
                            Descripción del Trabajo
                        </h5>

                        <div class="mb-3">
                            <label for="descripcion_trabajo" class="form-label">
                                Trabajo a Realizar *
                            </label>
                            <textarea class="form-control" 
                                      id="descripcion_trabajo" 
                                      name="descripcion_trabajo" 
                                      rows="3"
                                      required><?php echo htmlspecialchars($trabajo['descripcion_trabajo']); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="observaciones" class="form-label">
                                <i class="bi bi-chat-left-text"></i> Observaciones
                            </label>
                            <textarea class="form-control" 
                                      id="observaciones" 
                                      name="observaciones" 
                                      rows="2"><?php echo htmlspecialchars($trabajo['observaciones']); ?></textarea>
                        </div>

                        <hr class="my-4">

                        <!-- Precios -->
                        <h5 class="mb-3 text-primary">
                            <i class="bi bi-cash"></i>
                            Información de Pago
                        </h5>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="precio_total" class="form-label">
                                    Precio Total (Q) *
                                </label>
                                <input type="number" 
                                       class="form-control" 
                                       id="precio_total" 
                                       name="precio_total" 
                                       min="0" 
                                       step="0.01"
                                       value="<?php echo $trabajo['precio_total']; ?>"
                                       required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Anticipo Recibido (Q)</label>
                                <input type="text" 
                                       class="form-control" 
                                       value="<?php echo number_format($trabajo['anticipo'], 2); ?>"
                                       readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Saldo Pendiente (Q)</label>
                                <input type="text" 
                                       class="form-control text-danger fw-bold" 
                                       id="saldo" 
                                       readonly
                                       value="<?php echo number_format($trabajo['saldo'], 2); ?>">
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-between">
                            <a href="lista.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i>
                                Volver al Listado
                            </a>
                            <div class="d-flex gap-2">
                                <a href="ver.php?id=<?php echo $trabajo['id']; ?>" class="btn btn-info">
                                    <i class="bi bi-eye"></i>
                                    Ver Detalles
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
            <!-- Información del Trabajo -->
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-info-circle"></i>
                    Información del Trabajo
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>Fecha de Recepción:</strong><br>
                        <?php echo date('d/m/Y H:i', strtotime($trabajo['fecha_recepcion'])); ?>
                    </p>
                    <p class="mb-2">
                        <strong>Pieza:</strong><br>
                        <?php echo $trabajo['descripcion_pieza']; ?>
                    </p>
                    <p class="mb-2">
                        <strong>Material:</strong><br>
                        <?php echo ucfirst($trabajo['material']); ?>
                        <?php if ($trabajo['peso_gramos']): ?>
                            (<?php echo $trabajo['peso_gramos']; ?>g)
                        <?php endif; ?>
                    </p>
                    <p class="mb-0">
                        <strong>Tipo de Trabajo:</strong><br>
                        <?php echo ucfirst($trabajo['tipo_trabajo']); ?>
                    </p>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-lightning"></i>
                    Acciones Rápidas
                </div>
                <div class="list-group list-group-flush">
                    <a href="ver.php?id=<?php echo $trabajo['id']; ?>" class="list-group-item list-group-item-action">
                        <i class="bi bi-clock-history"></i>
                        Ver historial de transferencias
                    </a>
                    <a href="transferir.php?id=<?php echo $trabajo['id']; ?>" class="list-group-item list-group-item-action">
                        <i class="bi bi-arrow-left-right"></i>
                        Transferir a otro orfebre
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <i class="bi bi-printer"></i>
                        Imprimir orden de trabajo
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Mostrar campo de fecha entrega real si estado es "entregado"
document.getElementById('estado').addEventListener('change', function() {
    const container = document.getElementById('fechaEntregaRealContainer');
    if (this.value === 'entregado') {
        container.style.display = 'block';
        document.getElementById('fecha_entrega_real').required = true;
    } else {
        container.style.display = 'none';
        document.getElementById('fecha_entrega_real').required = false;
    }
});

// Calcular saldo al cambiar precio
document.getElementById('precio_total').addEventListener('input', function() {
    const precio = parseFloat(this.value) || 0;
    const anticipo = <?php echo $trabajo['anticipo']; ?>;
    const saldo = precio - anticipo;
    document.getElementById('saldo').value = saldo.toFixed(2);
});

// Validación del formulario
document.getElementById('formTrabajo').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // API actualizará trabajos_taller
    alert('Formulario listo para conectar con la API de actualización');
    
    const formData = new FormData(this);
    console.log('Datos actualizados:', Object.fromEntries(formData));
});
</script>

<?php
// Incluir footer
include '../../includes/footer.php';
?>