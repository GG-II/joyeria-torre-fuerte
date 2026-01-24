<?php
// ================================================
// MÓDULO TALLER - LISTA
// ================================================

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

// Verificar autenticación y permisos
requiere_autenticacion();

// Título de página
$titulo_pagina = 'Trabajos de Taller';

// Incluir header
include '../../includes/header.php';

// Incluir navbar
include '../../includes/navbar.php';

// Datos dummy de trabajos (CAMPOS REALES DEL SCHEMA)
$trabajos = [
    [
        'id' => 1,
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
        'descripcion_trabajo' => 'Reparar soldadura rota en aro',
        'precio_total' => 350.00,
        'anticipo' => 150.00,
        'saldo' => 200.00,
        'fecha_recepcion' => '2025-01-20 09:30:00',
        'fecha_entrega_prometida' => '2025-01-25',
        'fecha_entrega_real' => null,
        'empleado_recibe_nombre' => 'Carlos Admin',
        'empleado_actual_nombre' => 'Roberto Orfebre',
        'empleado_entrega_id' => null,
        'estado' => 'en_proceso',
        'dias_restantes' => 2
    ],
    [
        'id' => 2,
        'codigo' => 'T-2025-002',
        'cliente_nombre' => 'José Martínez',
        'cliente_telefono' => '5598-7654',
        'cliente_id' => null,
        'material' => 'plata',
        'peso_gramos' => 3.200,
        'largo_cm' => 45.00,
        'con_piedra' => 0,
        'estilo' => 'Moderno',
        'descripcion_pieza' => 'Cadena de plata 925',
        'tipo_trabajo' => 'fabricacion',
        'descripcion_trabajo' => 'Fabricar cadena cubana 45cm',
        'precio_total' => 800.00,
        'anticipo' => 400.00,
        'saldo' => 400.00,
        'fecha_recepcion' => '2025-01-18 14:15:00',
        'fecha_entrega_prometida' => '2025-01-28',
        'fecha_entrega_real' => null,
        'empleado_recibe_nombre' => 'María Gerente',
        'empleado_actual_nombre' => 'Roberto Orfebre',
        'empleado_entrega_id' => null,
        'estado' => 'recibido',
        'dias_restantes' => 5
    ],
    [
        'id' => 3,
        'codigo' => 'T-2025-003',
        'cliente_nombre' => 'Ana Ramírez',
        'cliente_telefono' => '5534-5678',
        'cliente_id' => 3,
        'material' => 'oro',
        'peso_gramos' => 2.100,
        'largo_cm' => null,
        'con_piedra' => 1,
        'estilo' => 'Elegante',
        'descripcion_pieza' => 'Aretes de oro con diamantes',
        'tipo_trabajo' => 'engaste',
        'descripcion_trabajo' => 'Engastar 2 diamantes de 0.3 quilates',
        'precio_total' => 450.00,
        'anticipo' => 450.00,
        'saldo' => 0.00,
        'fecha_recepcion' => '2025-01-15 10:00:00',
        'fecha_entrega_prometida' => '2025-01-22',
        'fecha_entrega_real' => '2025-01-21 16:30:00',
        'empleado_recibe_nombre' => 'Carlos Admin',
        'empleado_actual_nombre' => 'Roberto Orfebre',
        'empleado_entrega_nombre' => 'María Gerente',
        'estado' => 'entregado',
        'dias_restantes' => 0
    ],
    [
        'id' => 4,
        'codigo' => 'T-2025-004',
        'cliente_nombre' => 'Carlos Pérez',
        'cliente_telefono' => '5501-2345',
        'cliente_id' => null,
        'material' => 'plata',
        'peso_gramos' => 8.500,
        'largo_cm' => null,
        'con_piedra' => 0,
        'estilo' => null,
        'descripcion_pieza' => 'Pulsera gruesa de plata',
        'tipo_trabajo' => 'limpieza',
        'descripcion_trabajo' => 'Limpieza profunda y pulido',
        'precio_total' => 80.00,
        'anticipo' => 0.00,
        'saldo' => 80.00,
        'fecha_recepcion' => '2025-01-22 11:20:00',
        'fecha_entrega_prometida' => '2025-01-23',
        'fecha_entrega_real' => '2025-01-23 09:45:00',
        'empleado_recibe_nombre' => 'Carlos Admin',
        'empleado_actual_nombre' => 'Roberto Orfebre',
        'empleado_entrega_nombre' => 'Carlos Admin',
        'estado' => 'completado',
        'dias_restantes' => 0
    ],
    [
        'id' => 5,
        'codigo' => 'T-2025-005',
        'cliente_nombre' => 'Lucía Hernández',
        'cliente_telefono' => '5567-8901',
        'cliente_id' => null,
        'material' => 'oro',
        'peso_gramos' => 12.300,
        'largo_cm' => 50.00,
        'con_piedra' => 0,
        'estilo' => 'Clásico',
        'descripcion_pieza' => 'Collar de oro 14K',
        'tipo_trabajo' => 'ajuste',
        'descripcion_trabajo' => 'Ajustar largo de 55cm a 50cm',
        'precio_total' => 200.00,
        'anticipo' => 100.00,
        'saldo' => 100.00,
        'fecha_recepcion' => '2025-01-10 15:45:00',
        'fecha_entrega_prometida' => '2025-01-20',
        'fecha_entrega_real' => null,
        'empleado_recibe_nombre' => 'María Gerente',
        'empleado_actual_nombre' => 'Roberto Orfebre',
        'empleado_entrega_id' => null,
        'estado' => 'en_proceso',
        'dias_restantes' => -3  // Atrasado
    ]
];
?>

<!-- Contenido Principal -->
<div class="container-fluid main-content">
    <!-- Encabezado de Página -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1>
                    <i class="bi bi-tools"></i>
                    Trabajos de Taller
                </h1>
                <p class="text-muted">Gestión de reparaciones y trabajos de orfebrería</p>
            </div>
            <div class="col-md-6 text-end">
                <?php if (tiene_permiso('taller', 'crear')): ?>
                <a href="agregar.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i>
                    Nuevo Trabajo
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Resumen de Trabajos -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card amarillo">
                <div class="stat-icon">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div class="stat-value">
                    <?php echo count(array_filter($trabajos, fn($t) => $t['estado'] == 'recibido')); ?>
                </div>
                <div class="stat-label">Recibidos</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card azul">
                <div class="stat-icon">
                    <i class="bi bi-gear"></i>
                </div>
                <div class="stat-value">
                    <?php echo count(array_filter($trabajos, fn($t) => $t['estado'] == 'en_proceso')); ?>
                </div>
                <div class="stat-label">En Proceso</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card verde">
                <div class="stat-icon">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-value">
                    <?php echo count(array_filter($trabajos, fn($t) => $t['estado'] == 'completado')); ?>
                </div>
                <div class="stat-label">Completados</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card rojo">
                <div class="stat-icon">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div class="stat-value">
                    <?php echo count(array_filter($trabajos, fn($t) => $t['dias_restantes'] < 0 && $t['estado'] != 'entregado')); ?>
                </div>
                <div class="stat-label">Atrasados</div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Buscar</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control" id="searchInput" 
                               placeholder="Código, cliente, teléfono...">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Estado</label>
                    <select class="form-select" id="filterEstado">
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
                    <select class="form-select" id="filterMaterial">
                        <option value="">Todos</option>
                        <option value="oro">Oro</option>
                        <option value="plata">Plata</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tipo Trabajo</label>
                    <select class="form-select" id="filterTipo">
                        <option value="">Todos</option>
                        <option value="reparacion">Reparación</option>
                        <option value="ajuste">Ajuste</option>
                        <option value="grabado">Grabado</option>
                        <option value="diseño">Diseño</option>
                        <option value="limpieza">Limpieza</option>
                        <option value="engaste">Engaste</option>
                        <option value="fabricacion">Fabricación</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button class="btn btn-secondary w-100" onclick="limpiarFiltros()">
                        <i class="bi bi-x-circle"></i>
                        Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Trabajos -->
    <div class="card">
        <div class="card-header">
            <i class="bi bi-table"></i>
            Listado de Trabajos (<?php echo count($trabajos); ?>)
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="tablaTaller">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Cliente</th>
                            <th>Pieza / Trabajo</th>
                            <th>Material</th>
                            <th>Entrega</th>
                            <th>Precio</th>
                            <th>Saldo</th>
                            <th>Orfebre</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($trabajos as $trabajo): ?>
                        <tr class="<?php echo $trabajo['dias_restantes'] < 0 && $trabajo['estado'] != 'entregado' ? 'table-danger' : ''; ?>">
                            <td class="fw-bold text-primary"><?php echo $trabajo['codigo']; ?></td>
                            <td>
                                <div class="fw-bold"><?php echo $trabajo['cliente_nombre']; ?></div>
                                <small class="text-muted">
                                    <i class="bi bi-phone"></i> <?php echo $trabajo['cliente_telefono']; ?>
                                </small>
                            </td>
                            <td>
                                <div class="fw-bold"><?php echo $trabajo['descripcion_pieza']; ?></div>
                                <small class="text-muted">
                                    <?php 
                                    $tipos_trabajo = [
                                        'reparacion' => 'Reparación',
                                        'ajuste' => 'Ajuste',
                                        'grabado' => 'Grabado',
                                        'diseño' => 'Diseño',
                                        'limpieza' => 'Limpieza',
                                        'engaste' => 'Engaste',
                                        'repuesto' => 'Repuesto',
                                        'fabricacion' => 'Fabricación'
                                    ];
                                    echo $tipos_trabajo[$trabajo['tipo_trabajo']];
                                    ?>
                                    <?php if ($trabajo['con_piedra']): ?>
                                        <span class="badge bg-warning text-dark">Con piedra</span>
                                    <?php endif; ?>
                                </small>
                            </td>
                            <td>
                                <?php
                                $material_icons = [
                                    'oro' => '🟡',
                                    'plata' => '⚪',
                                    'otro' => '⚫'
                                ];
                                ?>
                                <span><?php echo $material_icons[$trabajo['material']]; ?> <?php echo ucfirst($trabajo['material']); ?></span>
                                <br><small class="text-muted"><?php echo $trabajo['peso_gramos']; ?>g</small>
                            </td>
                            <td>
                                <div><?php echo date('d/m/Y', strtotime($trabajo['fecha_entrega_prometida'])); ?></div>
                                <?php if ($trabajo['dias_restantes'] < 0 && $trabajo['estado'] != 'entregado'): ?>
                                    <small class="text-danger fw-bold">
                                        <i class="bi bi-exclamation-triangle"></i>
                                        Atrasado <?php echo abs($trabajo['dias_restantes']); ?>d
                                    </small>
                                <?php elseif ($trabajo['dias_restantes'] > 0): ?>
                                    <small class="text-muted">
                                        En <?php echo $trabajo['dias_restantes']; ?> días
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td class="fw-bold text-success">
                                Q <?php echo number_format($trabajo['precio_total'], 2); ?>
                            </td>
                            <td>
                                <?php if ($trabajo['saldo'] > 0): ?>
                                    <span class="text-danger fw-bold">
                                        Q <?php echo number_format($trabajo['saldo'], 2); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-success">
                                        <i class="bi bi-check-circle"></i> Pagado
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small class="text-muted"><?php echo $trabajo['empleado_actual_nombre']; ?></small>
                            </td>
                            <td>
                                <?php
                                $badges_estado = [
                                    'recibido' => 'bg-warning',
                                    'en_proceso' => 'bg-info',
                                    'completado' => 'bg-success',
                                    'entregado' => 'bg-secondary',
                                    'cancelado' => 'bg-danger'
                                ];
                                $textos_estado = [
                                    'recibido' => 'Recibido',
                                    'en_proceso' => 'En Proceso',
                                    'completado' => 'Completado',
                                    'entregado' => 'Entregado',
                                    'cancelado' => 'Cancelado'
                                ];
                                ?>
                                <span class="badge <?php echo $badges_estado[$trabajo['estado']]; ?>">
                                    <?php echo $textos_estado[$trabajo['estado']]; ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <a href="ver.php?id=<?php echo $trabajo['id']; ?>" 
                                       class="btn btn-sm btn-info"
                                       data-bs-toggle="tooltip" 
                                       title="Ver detalles">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if (tiene_permiso('taller', 'editar') && $trabajo['estado'] != 'entregado'): ?>
                                    <a href="editar.php?id=<?php echo $trabajo['id']; ?>" 
                                       class="btn btn-sm btn-warning"
                                       data-bs-toggle="tooltip" 
                                       title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <small class="text-muted">
                Mostrando <?php echo count($trabajos); ?> trabajos
            </small>
        </div>
    </div>
</div>

<script>
function limpiarFiltros() {
    document.getElementById('searchInput').value = '';
    document.getElementById('filterEstado').value = '';
    document.getElementById('filterMaterial').value = '';
    document.getElementById('filterTipo').value = '';
}

// Búsqueda en tiempo real
document.getElementById('searchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#tablaTaller tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});
</script>

<?php
// Incluir footer
include '../../includes/footer.php';
?>