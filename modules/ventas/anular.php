<?php
// ================================================
// MÓDULO VENTAS - ANULAR VENTA
// ================================================

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

// Verificar autenticación y permisos
requiere_autenticacion();
requiere_rol(['administrador', 'dueño']);

// Obtener ID de la venta
$venta_id = $_GET['id'] ?? null;

if (!$venta_id) {
    header('Location: lista.php');
    exit;
}

// Datos dummy de la venta
$venta = [
    'id' => $venta_id,
    'numero_venta' => 'V-2025-0105',
    'fecha' => '2025-01-23',
    'hora' => '14:30:00',
    'cliente_nombre' => 'María García López',
    'total' => 3500.00,
    'estado' => 'completada',
    'usuario_nombre' => 'María Vendedor'
];

// Verificar que la venta puede ser anulada
if ($venta['estado'] != 'completada') {
    header('Location: ver.php?id=' . $venta_id);
    exit;
}

// Título de página
$titulo_pagina = 'Anular Venta';

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
                    <i class="bi bi-cart-check"></i> Ventas
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="ver.php?id=<?php echo $venta['id']; ?>">
                    <?php echo $venta['numero_venta']; ?>
                </a>
            </li>
            <li class="breadcrumb-item active">Anular Venta</li>
        </ol>
    </nav>

    <!-- Encabezado -->
    <div class="page-header">
        <h1 class="text-danger">
            <i class="bi bi-exclamation-triangle"></i>
            Anular Venta
        </h1>
        <p class="text-muted">Esta acción NO puede ser revertida</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Advertencia -->
            <div class="alert alert-danger">
                <h4 class="alert-heading">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    ¡ATENCIÓN!
                </h4>
                <p class="mb-0">
                    Está a punto de anular una venta completada. Esta acción:
                </p>
                <ul class="mb-0 mt-2">
                    <li>Cambiará el estado de la venta a "ANULADA"</li>
                    <li>Devolverá los productos al inventario</li>
                    <li>Registrará movimientos de inventario de tipo "ajuste"</li>
                    <li><strong>NO podrá ser revertida</strong></li>
                </ul>
            </div>

            <!-- Información de la Venta -->
            <div class="card mb-3">
                <div class="card-header bg-danger text-white">
                    <i class="bi bi-receipt"></i>
                    Información de la Venta a Anular
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Número de Venta:</strong><br>
                                <?php echo $venta['numero_venta']; ?>
                            </p>
                            <p class="mb-2">
                                <strong>Fecha:</strong><br>
                                <?php echo date('d/m/Y H:i', strtotime($venta['fecha'] . ' ' . $venta['hora'])); ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Cliente:</strong><br>
                                <?php echo $venta['cliente_nombre']; ?>
                            </p>
                            <p class="mb-2">
                                <strong>Total:</strong><br>
                                <span class="text-success fw-bold">
                                    Q <?php echo number_format($venta['total'], 2); ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulario de Anulación -->
            <div class="card">
                <div class="card-header bg-warning">
                    <i class="bi bi-pencil-square"></i>
                    Motivo de la Anulación
                </div>
                <div class="card-body">
                    <form id="formAnular" method="POST" action="">
                        <input type="hidden" name="venta_id" value="<?php echo $venta['id']; ?>">
                        
                        <div class="mb-3">
                            <label for="motivo_anulacion" class="form-label">
                                <i class="bi bi-chat-left-text"></i>
                                Describa el motivo de la anulación *
                            </label>
                            <textarea class="form-control" 
                                      id="motivo_anulacion" 
                                      name="motivo_anulacion" 
                                      rows="4"
                                      placeholder="Ej: Cliente devolvió producto por garantía, error en el cobro, etc."
                                      required></textarea>
                            <small class="text-muted">
                                Este motivo quedará registrado permanentemente en la base de datos
                            </small>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="confirmo_anulacion" 
                                   required>
                            <label class="form-check-label" for="confirmo_anulacion">
                                <strong>Confirmo que deseo anular esta venta y entiendo que esta acción no puede revertirse</strong>
                            </label>
                        </div>

                        <div class="alert alert-info">
                            <h6 class="fw-bold">
                                <i class="bi bi-info-circle"></i>
                                Proceso automático al anular:
                            </h6>
                            <ol class="mb-0">
                                <li>Se actualizará el campo <code>estado='anulada'</code> en tabla <code>ventas</code></li>
                                <li>Se guardará el <code>motivo_anulacion</code></li>
                                <li>Se devolverán las cantidades al <code>inventario</code></li>
                                <li>Se registrarán movimientos en <code>movimientos_inventario</code> tipo "ajuste"</li>
                                <li>Si era venta a crédito, se actualizará el estado del crédito</li>
                            </ol>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-between">
                            <a href="ver.php?id=<?php echo $venta['id']; ?>" class="btn btn-secondary btn-lg">
                                <i class="bi bi-arrow-left"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-danger btn-lg">
                                <i class="bi bi-x-circle"></i>
                                Anular Venta
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validación del formulario
document.getElementById('formAnular').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!document.getElementById('confirmo_anulacion').checked) {
        alert('Debe confirmar la anulación');
        return;
    }
    
    const motivo = document.getElementById('motivo_anulacion').value.trim();
    if (motivo.length < 10) {
        alert('El motivo debe tener al menos 10 caracteres');
        return;
    }
    
    // Confirmación final
    if (confirm('¿Está COMPLETAMENTE SEGURO de anular esta venta?\n\nEsta acción NO puede ser revertida.')) {
        // API ejecutará:
        // 1. UPDATE ventas SET estado='anulada', motivo_anulacion='...' WHERE id=...
        // 2. Para cada producto en detalle_ventas:
        //    - UPDATE inventario SET cantidad = cantidad + [cantidad_vendida]
        //    - INSERT INTO movimientos_inventario (tipo='ajuste', motivo='Anulación de venta...')
        // 3. Si tipo_venta='credito':
        //    - UPDATE creditos_clientes SET estado='cancelado'
        
        alert('Venta anulada exitosamente. Redirigiendo...');
        
        const formData = new FormData(this);
        console.log('Datos de anulación:', Object.fromEntries(formData));
        
        // window.location.href = 'ver.php?id=' + formData.get('venta_id');
    }
});
</script>

<?php
// Incluir footer
include '../../includes/footer.php';
?>