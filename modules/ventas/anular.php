<?php
/**
 * ================================================
 * MÓDULO VENTAS - ANULAR VENTA
 * ================================================
 * 
 * Vista para anular una venta completada.
 * Requiere motivo de anulación y devuelve productos al inventario.
 * 
 * TODO FASE 5: Conectar con API
 * GET /api/ventas/ver.php?id={venta_id} - Obtener datos
 * POST /api/ventas/anular.php - Procesar anulación
 * 
 * Proceso de anulación:
 * 1. UPDATE ventas SET estado='anulada', motivo_anulacion='...'
 * 2. UPDATE inventario (devolver cantidades)
 * 3. INSERT movimientos_inventario (tipo='ajuste')
 * 4. Si crédito: UPDATE creditos_clientes SET estado='cancelado'
 */

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

// TODO FASE 5: Cargar datos desde API
$venta = null;

// Título de página
$titulo_pagina = 'Anular Venta';

// Incluir header
include '../../includes/header.php';

// Incluir navbar
include '../../includes/navbar.php';
?>

<!-- Contenido Principal -->
<div class="container-fluid main-content">
    <!-- Estado de carga -->
    <div id="loadingState" class="text-center py-5">
        <div class="spinner-border text-danger" style="width: 3rem; height: 3rem;"></div>
        <p class="mt-3 text-muted">Cargando información de la venta...</p>
    </div>

    <!-- Contenido principal (se muestra cuando carguen los datos) -->
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
                        <i class="bi bi-cart-check"></i> Ventas
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a href="ver.php?id=<?php echo $venta_id; ?>" id="breadcrumbNumero">
                        -
                    </a>
                </li>
                <li class="breadcrumb-item active">Anular Venta</li>
            </ol>
        </nav>

        <!-- Encabezado -->
        <div class="page-header mb-4">
            <h1 class="text-danger mb-2">
                <i class="bi bi-exclamation-triangle"></i>
                Anular Venta
            </h1>
            <p class="text-muted mb-0">Esta acción NO puede ser revertida</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Advertencia -->
                <div class="alert alert-danger mb-4 shadow-sm">
                    <h4 class="alert-heading">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        ¡ATENCIÓN!
                    </h4>
                    <p class="mb-2">
                        Está a punto de anular una venta completada. Esta acción:
                    </p>
                    <ul class="mb-0">
                        <li>Cambiará el estado de la venta a "ANULADA"</li>
                        <li>Devolverá los productos al inventario</li>
                        <li>Registrará movimientos de inventario de tipo "ajuste"</li>
                        <li><strong>NO podrá ser revertida</strong></li>
                    </ul>
                </div>

                <!-- Información de la Venta -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-danger text-white">
                        <i class="bi bi-receipt"></i>
                        Información de la Venta a Anular
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <small class="text-muted d-block">Número de Venta:</small>
                                    <strong id="numeroVenta">-</strong>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Fecha:</small>
                                    <strong id="fechaVenta">-</strong>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <small class="text-muted d-block">Cliente:</small>
                                    <strong id="clienteVenta">-</strong>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Total:</small>
                                    <h4 class="mb-0 text-success" id="totalVenta">Q 0.00</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulario de Anulación -->
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <i class="bi bi-pencil-square"></i>
                        Motivo de la Anulación
                    </div>
                    <div class="card-body">
                        <form id="formAnular" method="POST">
                            <input type="hidden" name="venta_id" value="<?php echo $venta_id; ?>">
                            
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
                                <div class="form-text">
                                    Este motivo quedará registrado permanentemente en la base de datos
                                </div>
                            </div>

                            <div class="form-check mb-4">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="confirmo_anulacion" 
                                       required>
                                <label class="form-check-label" for="confirmo_anulacion">
                                    <strong>Confirmo que deseo anular esta venta y entiendo que esta acción no puede revertirse</strong>
                                </label>
                            </div>

                            <div class="alert alert-info mb-4">
                                <h6 class="fw-bold">
                                    <i class="bi bi-info-circle"></i>
                                    Proceso automático al anular:
                                </h6>
                                <ol class="mb-0 small">
                                    <li>Se actualizará el campo <code>estado='anulada'</code> en tabla <code>ventas</code></li>
                                    <li>Se guardará el <code>motivo_anulacion</code></li>
                                    <li>Se devolverán las cantidades al <code>inventario</code></li>
                                    <li>Se registrarán movimientos en <code>movimientos_inventario</code> tipo "ajuste"</li>
                                    <li>Si era venta a crédito, se actualizará el estado del crédito</li>
                                </ol>
                            </div>

                            <!-- Botones -->
                            <div class="d-flex flex-column flex-sm-row justify-content-between gap-2">
                                <a href="ver.php?id=<?php echo $venta_id; ?>" class="btn btn-secondary btn-lg">
                                    <i class="bi bi-arrow-left"></i>
                                    Cancelar
                                </a>
                                <button type="submit" class="btn btn-danger btn-lg" id="btnAnular">
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

    <!-- Error al cargar -->
    <div id="errorState" style="display: none;" class="text-center py-5">
        <i class="bi bi-exclamation-triangle text-danger" style="font-size: 48px;"></i>
        <h4 class="mt-3">Error al cargar la venta</h4>
        <p class="text-muted" id="errorMessage">No se pudo cargar la información de la venta.</p>
        <a href="lista.php" class="btn btn-primary mt-3">
            <i class="bi bi-arrow-left"></i>
            Volver al listado
        </a>
    </div>
</div>

<style>
/* ============================================
   ESTILOS ESPECÍFICOS ANULAR VENTA
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
}

/* Cards */
.shadow-sm {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08) !important;
}

.card-body {
    padding: 20px;
}

/* Alertas */
.alert {
    border-radius: 8px;
}

.alert ul {
    padding-left: 20px;
}

.alert code {
    background-color: rgba(0, 0, 0, 0.1);
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 0.9em;
}

/* Formulario */
.form-label {
    font-weight: 500;
    margin-bottom: 0.5rem;
}

textarea.form-control {
    resize: vertical;
    min-height: 100px;
}

.form-check-input {
    width: 1.2em;
    height: 1.2em;
    margin-top: 0.15em;
}

.form-check-label {
    padding-left: 0.5rem;
}

/* Botones */
.btn-lg {
    padding: 0.75rem 1.5rem;
    font-size: 1.1rem;
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
    
    .alert {
        font-size: 0.9rem;
    }
    
    .alert h4 {
        font-size: 1.1rem;
    }
    
    .btn-lg {
        padding: 0.65rem 1.25rem;
        font-size: 1rem;
        width: 100%;
    }
    
    /* Form text más pequeño */
    .form-text,
    .form-check-label {
        font-size: 0.875rem;
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
 * JAVASCRIPT - ANULACIÓN DE VENTA
 * ================================================
 */

// Cargar datos al iniciar
document.addEventListener('DOMContentLoaded', function() {
    cargarDatosVenta();
});

/**
 * Cargar datos de la venta
 * TODO FASE 5: Conectar con API
 */
function cargarDatosVenta() {
    const ventaId = <?php echo $venta_id; ?>;
    
    // TODO FASE 5: Descomentar y conectar
    /*
    fetch(`<?php echo BASE_URL; ?>api/ventas/ver.php?id=${ventaId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const venta = data.data.venta;
                
                // Verificar que puede anularse
                if (venta.estado !== 'completada') {
                    window.location.href = `ver.php?id=${ventaId}`;
                    return;
                }
                
                mostrarDatosVenta(venta);
            } else {
                mostrarError(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarError('Error al cargar los datos de la venta');
        });
    */
    
    // Simular carga
    setTimeout(() => {
        mostrarError('MODO DESARROLLO: Esperando conexión con API');
    }, 1500);
}

/**
 * Mostrar datos de la venta
 */
function mostrarDatosVenta(venta) {
    document.getElementById('loadingState').style.display = 'none';
    document.getElementById('mainContent').style.display = 'block';
    
    document.getElementById('breadcrumbNumero').textContent = venta.numero_venta;
    document.getElementById('numeroVenta').textContent = venta.numero_venta;
    document.getElementById('fechaVenta').textContent = formatearFecha(venta.fecha, venta.hora);
    document.getElementById('clienteVenta').textContent = venta.cliente_nombre || 'Consumidor Final';
    document.getElementById('totalVenta').textContent = 'Q ' + formatearMoneda(venta.total);
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
document.getElementById('formAnular').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validar checkbox
    if (!document.getElementById('confirmo_anulacion').checked) {
        mostrarAlerta('Debe confirmar la anulación', 'warning');
        return;
    }
    
    // Validar longitud del motivo
    const motivo = document.getElementById('motivo_anulacion').value.trim();
    if (motivo.length < 10) {
        mostrarAlerta('El motivo debe tener al menos 10 caracteres', 'warning');
        document.getElementById('motivo_anulacion').focus();
        return;
    }
    
    // Confirmación final
    if (!confirm('¿Está COMPLETAMENTE SEGURO de anular esta venta?\n\nEsta acción NO puede ser revertida.')) {
        return;
    }
    
    // Deshabilitar botón
    const btnAnular = document.getElementById('btnAnular');
    btnAnular.disabled = true;
    btnAnular.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Anulando...';
    
    // Preparar datos
    const formData = new FormData(this);
    const datos = {
        venta_id: formData.get('venta_id'),
        motivo_anulacion: formData.get('motivo_anulacion')
    };
    
    // TODO FASE 5: Descomentar y conectar
    /*
    fetch('<?php echo BASE_URL; ?>api/ventas/anular.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(datos)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarAlerta('Venta anulada exitosamente', 'success');
            setTimeout(() => {
                window.location.href = 'ver.php?id=' + datos.venta_id;
            }, 1500);
        } else {
            mostrarAlerta(data.message, 'error');
            btnAnular.disabled = false;
            btnAnular.innerHTML = '<i class="bi bi-x-circle"></i> Anular Venta';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarAlerta('Error al anular la venta', 'error');
        btnAnular.disabled = false;
        btnAnular.innerHTML = '<i class="bi bi-x-circle"></i> Anular Venta';
    });
    */
    
    // Modo desarrollo
    console.log('Datos de anulación:', datos);
    setTimeout(() => {
        alert('MODO DESARROLLO: Anulación preparada. Esperando API.\n\nDatos:\n' + JSON.stringify(datos, null, 2));
        btnAnular.disabled = false;
        btnAnular.innerHTML = '<i class="bi bi-x-circle"></i> Anular Venta';
    }, 1000);
});

/**
 * Utilidades
 */
function formatearMoneda(monto) {
    return parseFloat(monto).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function formatearFecha(fecha, hora) {
    const d = new Date(fecha + ' ' + hora);
    return d.toLocaleDateString('es-GT', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    }) + ' ' + d.toLocaleTimeString('es-GT', {
        hour: '2-digit',
        minute: '2-digit'
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