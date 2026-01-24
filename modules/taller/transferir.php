<?php
// ================================================
// MÓDULO TALLER - TRANSFERIR TRABAJO
// ================================================

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

// Verificar autenticación y permisos
requiere_autenticacion();
requiere_rol(['administrador', 'dueño', 'gerente']);

// Obtener ID del trabajo
$trabajo_id = $_GET['id'] ?? null;

// Datos dummy del trabajo
$trabajo = null;
if ($trabajo_id) {
    $trabajo = [
        'id' => $trabajo_id,
        'codigo' => 'T-2025-001',
        'cliente_nombre' => 'María García López',
        'descripcion_pieza' => 'Anillo de compromiso oro 18K',
        'empleado_actual_id' => 3,
        'empleado_actual_nombre' => 'Roberto Orfebre',
        'estado' => 'en_proceso'
    ];
}

// Título de página
$titulo_pagina = 'Transferir Trabajo';

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
            <li class="breadcrumb-item active">Transferir Trabajo</li>
        </ol>
    </nav>

    <!-- Encabezado -->
    <div class="page-header">
        <h1>
            <i class="bi bi-arrow-left-right"></i>
            Transferir Trabajo a Otro Orfebre
        </h1>
        <p class="text-muted">Registro inmutable de transferencia entre empleados</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Información del Trabajo -->
            <?php if ($trabajo): ?>
            <div class="alert alert-info">
                <h5 class="alert-heading">
                    <i class="bi bi-info-circle"></i>
                    Trabajo a Transferir
                </h5>
                <p class="mb-0">
                    <strong>Código:</strong> <?php echo $trabajo['codigo']; ?><br>
                    <strong>Cliente:</strong> <?php echo $trabajo['cliente_nombre']; ?><br>
                    <strong>Pieza:</strong> <?php echo $trabajo['descripcion_pieza']; ?><br>
                    <strong>Orfebre actual:</strong> <?php echo $trabajo['empleado_actual_nombre']; ?>
                </p>
            </div>
            <?php endif; ?>

            <!-- Formulario de Transferencia -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-pencil-square"></i>
                    Datos de la Transferencia
                </div>
                <div class="card-body">
                    <form id="formTransferencia" method="POST" action="">
                        <?php if ($trabajo): ?>
                        <input type="hidden" name="trabajo_id" value="<?php echo $trabajo['id']; ?>">
                        <input type="hidden" name="empleado_origen_id" value="<?php echo $trabajo['empleado_actual_id']; ?>">
                        <?php else: ?>
                        <!-- Selector de trabajo si no viene en URL -->
                        <div class="mb-3">
                            <label for="trabajo_id" class="form-label">
                                <i class="bi bi-search"></i> Seleccionar Trabajo *
                            </label>
                            <select class="form-select" id="trabajo_id" name="trabajo_id" required>
                                <option value="">Busque por código o cliente...</option>
                                <option value="1">T-2025-001 - María García - Anillo oro</option>
                                <option value="2">T-2025-002 - José Martínez - Cadena plata</option>
                                <option value="5">T-2025-005 - Lucía Hernández - Collar oro</option>
                            </select>
                        </div>
                        <?php endif; ?>

                        <hr class="my-4">

                        <!-- Transferencia -->
                        <h5 class="mb-3 text-primary">
                            <i class="bi bi-arrow-left-right"></i>
                            Información de Transferencia
                        </h5>

                        <div class="mb-3">
                            <label for="empleado_destino_id" class="form-label">
                                <i class="bi bi-person-workspace"></i> Transferir a Orfebre *
                            </label>
                            <select class="form-select" id="empleado_destino_id" name="empleado_destino_id" required>
                                <option value="">Seleccione...</option>
                                <option value="3">Roberto Orfebre</option>
                                <option value="4">Juan Artesano</option>
                                <option value="5">Pedro Maestro</option>
                            </select>
                        </div>

                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>Importante:</strong> La transferencia se registrará en la tabla 
                            <code>transferencias_trabajo</code> que es INMUTABLE y sirve como auditoría completa.
                        </div>

                        <div class="mb-3">
                            <label for="nota" class="form-label">
                                <i class="bi bi-chat-left-text"></i> Nota / Motivo de la Transferencia
                            </label>
                            <textarea class="form-control" 
                                      id="nota" 
                                      name="nota" 
                                      rows="3"
                                      placeholder="Ej: Transferido por carga de trabajo, especialización requerida, etc."></textarea>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?php echo $trabajo ? 'ver.php?id=' . $trabajo['id'] : 'lista.php'; ?>" 
                               class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-arrow-left-right"></i>
                                Realizar Transferencia
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Información Técnica -->
            <div class="card mt-3">
                <div class="card-header bg-info text-white">
                    <i class="bi bi-info-circle"></i>
                    Cómo Funciona la Transferencia
                </div>
                <div class="card-body">
                    <h6 class="fw-bold">Proceso automático al transferir:</h6>
                    <ol class="mb-3">
                        <li>Se actualiza el campo <code>empleado_actual_id</code> en <code>trabajos_taller</code></li>
                        <li>Se crea un registro INMUTABLE en <code>transferencias_trabajo</code> con:
                            <ul>
                                <li><code>trabajo_id</code></li>
                                <li><code>empleado_origen_id</code></li>
                                <li><code>empleado_destino_id</code></li>
                                <li><code>fecha_transferencia</code> (automática)</li>
                                <li><code>estado_trabajo_momento</code> (estado actual del trabajo)</li>
                                <li><code>nota</code> (motivo)</li>
                                <li><code>usuario_registra_id</code> (quien hace la transferencia)</li>
                            </ul>
                        </li>
                    </ol>

                    <div class="alert alert-success mb-0">
                        <i class="bi bi-shield-check"></i>
                        <strong>Auditoría Completa:</strong> Cada transferencia queda registrada permanentemente,
                        permitiendo rastrear la trazabilidad completa del trabajo.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validar que origen y destino sean diferentes
document.getElementById('empleado_destino_id').addEventListener('change', function() {
    <?php if ($trabajo): ?>
    const origen = <?php echo $trabajo['empleado_actual_id']; ?>;
    const destino = parseInt(this.value);
    
    if (origen === destino) {
        alert('No puede transferir el trabajo al mismo orfebre que lo tiene actualmente');
        this.value = '';
    }
    <?php endif; ?>
});

// Validación del formulario
document.getElementById('formTransferencia').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // API ejecutará:
    // 1. UPDATE trabajos_taller SET empleado_actual_id = [destino] WHERE id = [trabajo_id]
    // 2. INSERT INTO transferencias_trabajo (trabajo_id, empleado_origen_id, empleado_destino_id, 
    //    estado_trabajo_momento, nota, usuario_registra_id, fecha_transferencia)
    //    VALUES (...)
    
    if (confirm('¿Confirma la transferencia de este trabajo? Esta acción quedará registrada permanentemente.')) {
        alert('Transferencia lista para procesar con la API');
        
        const formData = new FormData(this);
        console.log('Datos de transferencia:', Object.fromEntries(formData));
        
        // Redireccionar después de guardar
        // window.location.href = 'ver.php?id=' + formData.get('trabajo_id');
    }
});
</script>

<?php
// Incluir footer
include '../../includes/footer.php';
?>