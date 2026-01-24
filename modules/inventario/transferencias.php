<?php
// ================================================
// MÓDULO INVENTARIO - TRANSFERENCIAS
// ================================================

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

// Verificar autenticación y permisos
requiere_autenticacion();
requiere_rol(['administrador', 'dueño', 'gerente']);

// Título de página
$titulo_pagina = 'Transferencias de Inventario';

// Incluir header
include '../../includes/header.php';

// Incluir navbar
include '../../includes/navbar.php';

// Historial de transferencias (dummy)
$transferencias = [
    [
        'id' => 15,
        'fecha' => '2025-01-20 14:30:00',
        'producto_codigo' => 'AN-001',
        'producto_nombre' => 'Anillo de Oro 18K',
        'cantidad' => 2,
        'origen' => 'Los Arcos',
        'destino' => 'Chinaca Central',
        'usuario' => 'Carlos Admin',
        'estado' => 'completada'
    ],
    [
        'id' => 14,
        'fecha' => '2025-01-18 10:15:00',
        'producto_codigo' => 'CO-045',
        'producto_nombre' => 'Collar de Oro 14K',
        'cantidad' => 1,
        'origen' => 'Chinaca Central',
        'destino' => 'Los Arcos',
        'usuario' => 'María Gerente',
        'estado' => 'completada'
    ],
    [
        'id' => 13,
        'fecha' => '2025-01-15 16:45:00',
        'producto_codigo' => 'AR-102',
        'producto_nombre' => 'Aretes de Plata',
        'cantidad' => 3,
        'origen' => 'Los Arcos',
        'destino' => 'Chinaca Central',
        'usuario' => 'Carlos Admin',
        'estado' => 'completada'
    ]
];
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
                    <i class="bi bi-box-seam"></i> Inventario
                </a>
            </li>
            <li class="breadcrumb-item active">Transferencias</li>
        </ol>
    </nav>

    <!-- Encabezado -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1>
                    <i class="bi bi-arrow-left-right"></i>
                    Transferencias de Inventario
                </h1>
                <p class="text-muted">Movimientos de stock entre sucursales</p>
            </div>
            <div class="col-md-6 text-end">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaTransferencia">
                    <i class="bi bi-plus-circle"></i>
                    Nueva Transferencia
                </button>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Fecha Desde</label>
                    <input type="date" class="form-control" id="fechaDesde">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha Hasta</label>
                    <input type="date" class="form-control" id="fechaHasta">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sucursal Origen</label>
                    <select class="form-select" id="filterOrigen">
                        <option value="">Todas</option>
                        <option value="los_arcos">Los Arcos</option>
                        <option value="chinaca">Chinaca Central</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button class="btn btn-secondary w-100">
                        <i class="bi bi-funnel"></i>
                        Filtrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Transferencias -->
    <div class="card">
        <div class="card-header">
            <i class="bi bi-table"></i>
            Historial de Transferencias
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th width="80">#</th>
                        <th>Fecha</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th><i class="bi bi-arrow-right text-primary"></i> Origen</th>
                        <th><i class="bi bi-arrow-left text-success"></i> Destino</th>
                        <th>Usuario</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transferencias as $trans): ?>
                    <tr>
                        <td class="fw-bold"><?php echo $trans['id']; ?></td>
                        <td>
                            <small><?php echo date('d/m/Y H:i', strtotime($trans['fecha'])); ?></small>
                        </td>
                        <td>
                            <div class="fw-bold"><?php echo $trans['producto_nombre']; ?></div>
                            <small class="text-muted"><?php echo $trans['producto_codigo']; ?></small>
                        </td>
                        <td>
                            <span class="badge bg-info"><?php echo $trans['cantidad']; ?></span>
                        </td>
                        <td>
                            <span class="badge bg-primary">
                                <i class="bi bi-building"></i>
                                <?php echo $trans['origen']; ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-success">
                                <i class="bi bi-building"></i>
                                <?php echo $trans['destino']; ?>
                            </span>
                        </td>
                        <td>
                            <small class="text-muted"><?php echo $trans['usuario']; ?></small>
                        </td>
                        <td>
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i>
                                <?php echo ucfirst($trans['estado']); ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Ver detalles">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <small class="text-muted">
                Mostrando <?php echo count($transferencias); ?> transferencias
            </small>
        </div>
    </div>
</div>

<!-- Modal: Nueva Transferencia -->
<div class="modal fade" id="modalNuevaTransferencia" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-arrow-left-right"></i>
                    Nueva Transferencia
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formTransferencia">
                    <!-- Producto -->
                    <div class="mb-3">
                        <label for="producto_id" class="form-label">
                            <i class="bi bi-box-seam"></i> Producto *
                        </label>
                        <select class="form-select" id="producto_id" name="producto_id" required>
                            <option value="">Seleccione un producto...</option>
                            <option value="1">AN-001 - Anillo de Oro 18K con Diamante</option>
                            <option value="2">AR-102 - Aretes de Plata con Perla</option>
                            <option value="3">CO-045 - Collar de Oro 14K Cadena Fina</option>
                            <option value="4">PU-089 - Pulsera de Plata con Circonitas</option>
                        </select>
                    </div>

                    <!-- Stock Disponible (se mostrará dinámicamente) -->
                    <div class="alert alert-info" id="infoStock" style="display: none;">
                        <strong>Stock Disponible:</strong>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <i class="bi bi-building"></i> Los Arcos: <span id="stock-los-arcos" class="fw-bold">0</span>
                            </div>
                            <div class="col-md-6">
                                <i class="bi bi-building"></i> Chinaca Central: <span id="stock-chinaca" class="fw-bold">0</span>
                            </div>
                        </div>
                    </div>

                    <!-- Origen y Destino -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="sucursal_origen" class="form-label">
                                <i class="bi bi-arrow-right"></i> Sucursal Origen *
                            </label>
                            <select class="form-select" id="sucursal_origen" name="sucursal_origen" required>
                                <option value="">Seleccione...</option>
                                <option value="1">Los Arcos</option>
                                <option value="2">Chinaca Central</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="sucursal_destino" class="form-label">
                                <i class="bi bi-arrow-left"></i> Sucursal Destino *
                            </label>
                            <select class="form-select" id="sucursal_destino" name="sucursal_destino" required>
                                <option value="">Seleccione...</option>
                                <option value="1">Los Arcos</option>
                                <option value="2">Chinaca Central</option>
                            </select>
                        </div>
                    </div>

                    <!-- Cantidad -->
                    <div class="mb-3">
                        <label for="cantidad" class="form-label">
                            <i class="bi bi-123"></i> Cantidad a Transferir *
                        </label>
                        <input type="number" 
                               class="form-control" 
                               id="cantidad" 
                               name="cantidad" 
                               min="1" 
                               required
                               placeholder="Ingrese la cantidad">
                    </div>

                    <!-- Notas -->
                    <div class="mb-3">
                        <label for="notas" class="form-label">
                            <i class="bi bi-chat-left-text"></i> Notas (opcional)
                        </label>
                        <textarea class="form-control" 
                                  id="notas" 
                                  name="notas" 
                                  rows="2"
                                  placeholder="Motivo o comentarios sobre la transferencia"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i>
                    Cancelar
                </button>
                <button type="button" class="btn btn-primary" onclick="procesarTransferencia()">
                    <i class="bi bi-arrow-left-right"></i>
                    Realizar Transferencia
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Mostrar stock cuando se selecciona un producto
document.getElementById('producto_id').addEventListener('change', function() {
    const infoStock = document.getElementById('infoStock');
    
    if (this.value) {
        // Aquí se haría una llamada a la API para obtener el stock real
        // Por ahora mostramos datos dummy
        document.getElementById('stock-los-arcos').textContent = '3';
        document.getElementById('stock-chinaca').textContent = '2';
        infoStock.style.display = 'block';
    } else {
        infoStock.style.display = 'none';
    }
});

// Validar que origen y destino sean diferentes
document.getElementById('sucursal_destino').addEventListener('change', function() {
    const origen = document.getElementById('sucursal_origen').value;
    const destino = this.value;
    
    if (origen && destino && origen === destino) {
        alert('La sucursal de origen y destino deben ser diferentes');
        this.value = '';
    }
});

// Procesar transferencia
function procesarTransferencia() {
    const form = document.getElementById('formTransferencia');
    
    if (form.checkValidity()) {
        // Aquí se conectará con la API
        alert('Transferencia lista para procesar con la API');
        
        const formData = new FormData(form);
        console.log('Datos de transferencia:', Object.fromEntries(formData));
        
        // Cerrar modal
        bootstrap.Modal.getInstance(document.getElementById('modalNuevaTransferencia')).hide();
        
        // Resetear formulario
        form.reset();
    } else {
        form.reportValidity();
    }
}
</script>

<?php
// Incluir footer
include '../../includes/footer.php';
?>