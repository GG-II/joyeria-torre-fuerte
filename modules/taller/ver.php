<?php
// ================================================
// MÓDULO TALLER - VER DETALLES DEL TRABAJO
// ================================================

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

// Verificar autenticación
requiere_autenticacion();

// Obtener ID del trabajo
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
    'descripcion_pieza' => 'Anillo de compromiso oro 18K con diamante central',
    'tipo_trabajo' => 'reparacion',
    'descripcion_trabajo' => 'Reparar soldadura rota en aro inferior. Cliente solicita revisar engaste del diamante.',
    'precio_total' => 350.00,
    'anticipo' => 150.00,
    'saldo' => 200.00,
    'fecha_recepcion' => '2025-01-20 09:30:00',
    'fecha_entrega_prometida' => '2025-01-25',
    'fecha_entrega_real' => null,
    'empleado_recibe_id' => 1,
    'empleado_recibe_nombre' => 'Carlos Admin',
    'empleado_actual_id' => 3,
    'empleado_actual_nombre' => 'Roberto Orfebre',
    'empleado_entrega_id' => null,
    'estado' => 'en_proceso',
    'observaciones' => 'Cliente pidió trato delicado con la piedra central',
    'fecha_creacion' => '2025-01-20 09:30:00'
];

// Historial de transferencias (tabla transferencias_trabajo)
$transferencias = [
    [
        'id' => 5,
        'empleado_origen_nombre' => 'Carlos Admin',
        'empleado_destino_nombre' => 'Roberto Orfebre',
        'fecha_transferencia' => '2025-01-20 09:35:00',
        'estado_trabajo_momento' => 'recibido',
        'nota' => 'Trabajo asignado inicialmente',
        'usuario_registra_nombre' => 'Carlos Admin'
    ]
];

// Título de página
$titulo_pagina = 'Detalles del Trabajo';

// Incluir header
include '../../includes/header.php';

// Incluir navbar
include '../../includes/navbar.php';

// Calcular días restantes
$dias_restantes = floor((strtotime($trabajo['fecha_entrega_prometida']) - time()) / 86400);
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
            <li class="breadcrumb-item active"><?php echo $trabajo['codigo']; ?></li>
        </ol>
    </nav>

    <!-- Encabezado del Trabajo -->
    <div class="card mb-4" style="border-left: 5px solid var(--color-dorado);">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-primary text-white me-3" style="width: 80px; height: 80px; font-size: 32px;">
                            <i class="bi bi-tools"></i>
                        </div>
                        <div>
                            <h2 class="mb-1"><?php echo $trabajo['codigo']; ?></h2>
                            <div class="d-flex gap-3 text-muted">
                                <span>
                                    <i class="bi bi-person"></i>
                                    <?php echo $trabajo['cliente_nombre']; ?>
                                </span>
                                <span>
                                    <i class="bi bi-phone"></i>
                                    <?php echo $trabajo['cliente_telefono']; ?>
                                </span>
                                <span>
                                    <?php
                                    $badges_estado = [
                                        'recibido' => 'bg-warning',
                                        'en_proceso' => 'bg-info',
                                        'completado' => 'bg-success',
                                        'entregado' => 'bg-secondary',
                                        'cancelado' => 'bg-danger'
                                    ];
                                    ?>
                                    <span class="badge <?php echo $badges_estado[$trabajo['estado']]; ?>">
                                        <?php echo ucfirst($trabajo['estado']); ?>
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <?php if (tiene_permiso('taller', 'editar') && $trabajo['estado'] != 'entregado'): ?>
                    <a href="editar.php?id=<?php echo $trabajo['id']; ?>" class="btn btn-warning">
                        <i class="bi bi-pencil"></i>
                        Editar
                    </a>
                    <?php endif; ?>
                    <a href="lista.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i>
                        Volver
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Información del Trabajo -->
        <div class="col-lg-4">
            <!-- Datos de la Pieza -->
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-gem"></i>
                    Información de la Pieza
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Descripción</small>
                        <strong><?php echo $trabajo['descripcion_pieza']; ?></strong>
                    </div>
                    <hr>
                    <div class="mb-2">
                        <small class="text-muted d-block">Material</small>
                        <strong><?php echo ucfirst($trabajo['material']); ?></strong>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block">Peso</small>
                        <strong><?php echo $trabajo['peso_gramos']; ?> gramos</strong>
                    </div>
                    <?php if ($trabajo['largo_cm']): ?>
                    <div class="mb-2">
                        <small class="text-muted d-block">Largo</small>
                        <strong><?php echo $trabajo['largo_cm']; ?> cm</strong>
                    </div>
                    <?php endif; ?>
                    <?php if ($trabajo['estilo']): ?>
                    <div class="mb-2">
                        <small class="text-muted d-block">Estilo</small>
                        <strong><?php echo $trabajo['estilo']; ?></strong>
                    </div>
                    <?php endif; ?>
                    <div class="mb-0">
                        <small class="text-muted d-block">Piedras</small>
                        <?php if ($trabajo['con_piedra']): ?>
                            <span class="badge bg-warning text-dark">Con piedras</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Sin piedras</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Información de Pago -->
            <div class="card mb-3">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-cash-coin"></i>
                    Información de Pago
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Precio Total</small>
                        <h4 class="mb-0 text-success">
                            Q <?php echo number_format($trabajo['precio_total'], 2); ?>
                        </h4>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Anticipo Recibido</small>
                        <h5 class="mb-0 text-primary">
                            Q <?php echo number_format($trabajo['anticipo'], 2); ?>
                        </h5>
                    </div>
                    <div>
                        <small class="text-muted d-block">Saldo Pendiente</small>
                        <h4 class="mb-0 <?php echo $trabajo['saldo'] > 0 ? 'text-danger' : 'text-success'; ?>">
                            Q <?php echo number_format($trabajo['saldo'], 2); ?>
                        </h4>
                    </div>
                </div>
            </div>

            <!-- Fechas -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <i class="bi bi-calendar-event"></i>
                    Fechas Importantes
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Fecha de Recepción</small>
                        <strong><?php echo date('d/m/Y H:i', strtotime($trabajo['fecha_recepcion'])); ?></strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Entrega Prometida</small>
                        <strong><?php echo date('d/m/Y', strtotime($trabajo['fecha_entrega_prometida'])); ?></strong>
                        <br>
                        <?php if ($dias_restantes < 0 && !$trabajo['fecha_entrega_real']): ?>
                            <span class="badge bg-danger">Atrasado <?php echo abs($dias_restantes); ?> días</span>
                        <?php elseif ($dias_restantes >= 0 && !$trabajo['fecha_entrega_real']): ?>
                            <span class="badge bg-info">Faltan <?php echo $dias_restantes; ?> días</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($trabajo['fecha_entrega_real']): ?>
                    <div>
                        <small class="text-muted d-block">Entrega Real</small>
                        <strong><?php echo date('d/m/Y H:i', strtotime($trabajo['fecha_entrega_real'])); ?></strong>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Detalles del Trabajo -->
        <div class="col-lg-8">
            <!-- Descripción del Trabajo -->
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-file-text"></i>
                    Descripción del Trabajo
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="fw-bold">Tipo de Trabajo:</h6>
                        <p class="mb-0">
                            <span class="badge bg-primary"><?php echo ucfirst(str_replace('_', ' ', $trabajo['tipo_trabajo'])); ?></span>
                        </p>
                    </div>
                    <div class="mb-3">
                        <h6 class="fw-bold">Detalles:</h6>
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($trabajo['descripcion_trabajo'])); ?></p>
                    </div>
                    <?php if ($trabajo['observaciones']): ?>
                    <div>
                        <h6 class="fw-bold">Observaciones:</h6>
                        <p class="mb-0 text-muted"><?php echo nl2br(htmlspecialchars($trabajo['observaciones'])); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Personal Asignado -->
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-people"></i>
                    Personal Asignado
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <small class="text-muted d-block">Recibió el trabajo:</small>
                            <strong><?php echo $trabajo['empleado_recibe_nombre']; ?></strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Orfebre actual:</small>
                            <strong><?php echo $trabajo['empleado_actual_nombre']; ?></strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Entregó:</small>
                            <strong><?php echo $trabajo['empleado_entrega_id'] ? 'Nombre Empleado' : 'Pendiente'; ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Historial de Transferencias (tabla transferencias_trabajo) -->
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-clock-history"></i>
                    Historial de Transferencias (INMUTABLE)
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>De</th>
                                <th>Para</th>
                                <th>Estado</th>
                                <th>Nota</th>
                                <th>Registrado por</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transferencias as $trans): ?>
                            <tr>
                                <td>
                                    <small><?php echo date('d/m/Y H:i', strtotime($trans['fecha_transferencia'])); ?></small>
                                </td>
                                <td><?php echo $trans['empleado_origen_nombre']; ?></td>
                                <td><?php echo $trans['empleado_destino_nombre']; ?></td>
                                <td>
                                    <span class="badge bg-info"><?php echo ucfirst($trans['estado_trabajo_momento']); ?></span>
                                </td>
                                <td>
                                    <small class="text-muted"><?php echo $trans['nota']; ?></small>
                                </td>
                                <td>
                                    <small class="text-muted"><?php echo $trans['usuario_registra_nombre']; ?></small>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i>
                        Este historial es inmutable y sirve como auditoría completa del trabajo
                    </small>
                </div>
            </div>

            <!-- Acciones -->
            <?php if ($trabajo['estado'] != 'entregado'): ?>
            <div class="card mt-3">
                <div class="card-header">
                    <i class="bi bi-gear"></i>
                    Acciones Disponibles
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <a href="transferir.php?id=<?php echo $trabajo['id']; ?>" class="btn btn-warning w-100">
                                <i class="bi bi-arrow-left-right"></i>
                                Transferir a Orfebre
                            </a>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-info w-100">
                                <i class="bi bi-printer"></i>
                                Imprimir Orden
                            </button>
                        </div>
                        <div class="col-md-4">
                            <a href="editar.php?id=<?php echo $trabajo['id']; ?>" class="btn btn-primary w-100">
                                <i class="bi bi-pencil"></i>
                                Editar Trabajo
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Incluir footer
include '../../includes/footer.php';
?>