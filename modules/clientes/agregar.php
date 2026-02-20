<?php
/**
 * ================================================
 * MÓDULO CLIENTES - AGREGAR
 * ================================================
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();
requiere_rol(['administrador', 'dueño', 'vendedor']);

require_once '../../includes/header.php';
require_once '../../includes/navbar.php';
?>

<div class="container-fluid px-4 py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-1"><i class="bi bi-plus-circle"></i> Nuevo Cliente</h2>
            <p class="text-muted mb-0">Registrar nuevo cliente</p>
        </div>
        <a href="lista.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    <hr class="border-warning border-2 opacity-75 mb-4">

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Datos del Cliente</h5>
                </div>
                <div class="card-body">
                    <form id="formCliente">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">
                                    Nombre Completo <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="nombre" 
                                       placeholder="Nombre del cliente" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="nit" class="form-label">NIT</label>
                                <input type="text" class="form-control" id="nit" 
                                       placeholder="12345678-9 o C/F">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="telefono" 
                                       placeholder="1234-5678">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" 
                                       placeholder="cliente@ejemplo.com">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="direccion" class="form-label">Dirección</label>
                            <textarea class="form-control" id="direccion" rows="2" 
                                      placeholder="Dirección completa"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="tipo_cliente" class="form-label">
                                    Tipo de Cliente <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="tipo_cliente" required>
                                    <option value="">Seleccione...</option>
                                    <option value="publico">Público</option>
                                    <option value="mayorista">Mayorista</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="tipo_mercaderias" class="form-label">
                                    Tipo de Mercadería <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="tipo_mercaderias" required>
                                    <option value="">Seleccione...</option>
                                    <option value="oro">Oro</option>
                                    <option value="plata">Plata</option>
                                    <option value="ambas">Ambas</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="limite_credito" class="form-label">Límite de Crédito (Q)</label>
                                <input type="number" class="form-control" id="limite_credito" 
                                       placeholder="0.00" step="0.01" min="0">
                                <small class="text-muted">Opcional, dejar vacío si no aplica</small>
                            </div>
                        </div>

                        <div class="row" id="seccionCredito" style="display: none;">
                            <div class="col-md-12 mb-3">
                                <label for="plazo_credito_dias" class="form-label">Plazo de Crédito (días)</label>
                                <input type="number" class="form-control" id="plazo_credito_dias" 
                                       placeholder="30" min="1">
                                <small class="text-muted">Días para pagar el crédito</small>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="activo" checked>
                                <label class="form-check-label" for="activo">Cliente Activo</label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="lista.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-check-circle"></i> Crear Cliente
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

<?php require_once '../../includes/footer.php'; ?>

<script src="../../assets/js/vendors/sweetalert2/sweetalert2.all.min.js"></script>
<script src="../../assets/js/common.js"></script>
<script src="../../assets/js/api-client.js"></script>

<script>
// Mostrar/ocultar plazo crédito según límite
document.getElementById('limite_credito').addEventListener('input', function(e) {
    const seccion = document.getElementById('seccionCredito');
    if (parseFloat(e.target.value) > 0) {
        seccion.style.display = 'block';
    } else {
        seccion.style.display = 'none';
        document.getElementById('plazo_credito_dias').value = '';
    }
});

// Formateo teléfono
document.getElementById('telefono').addEventListener('input', function(e) {
    let valor = e.target.value.replace(/\D/g, '');
    if (valor.length > 4) {
        valor = valor.substring(0, 4) + '-' + valor.substring(4, 8);
    }
    e.target.value = valor;
});

document.getElementById('formCliente').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const datos = {
        nombre: document.getElementById('nombre').value.trim(),
        nit: document.getElementById('nit').value.trim() || null,
        telefono: document.getElementById('telefono').value.trim() || null,
        email: document.getElementById('email').value.trim() || null,
        direccion: document.getElementById('direccion').value.trim() || null,
        tipo_cliente: document.getElementById('tipo_cliente').value,
        tipo_mercaderias: document.getElementById('tipo_mercaderias').value,
        limite_credito: document.getElementById('limite_credito').value || null,
        plazo_credito_dias: document.getElementById('plazo_credito_dias').value || null,
        activo: document.getElementById('activo').checked ? 1 : 0
    };
    
    if (!datos.nombre || !datos.tipo_cliente || !datos.tipo_mercaderias) {
        mostrarError('Complete los campos requeridos');
        return;
    }
    
    if (datos.email && !validarEmail(datos.email)) {
        mostrarError('Email no válido');
        return;
    }
    
    if (datos.telefono && !validarTelefono(datos.telefono)) {
        mostrarError('Teléfono debe tener 8 dígitos');
        return;
    }
    
    if (datos.nit && datos.nit.trim() !== '') {
        const nitUpper = datos.nit.toUpperCase().trim();
        
        // Permitir C/F o CF
        if (nitUpper !== 'C/F' && nitUpper !== 'CF') {
            // Validar formato solo si no es C/F
            if (!validarNIT(datos.nit)) {
                mostrarError('NIT no válido (formato: 12345678-9 o C/F)');
                return;
            }
        }
    }
    
    const confirmacion = await confirmarAccion('¿Crear este cliente?');
    if (!confirmacion) return;
    
    try {
        mostrarCargando();
        const resultado = await api.crearCliente(datos);
        ocultarCargando();
        
        if (resultado.success) {
            await mostrarExito('Cliente creado exitosamente');
            window.location.href = 'lista.php';
        } else {
            mostrarError(resultado.message || 'Error al crear');
        }
    } catch (error) {
        ocultarCargando();
        mostrarError('Error: ' + error.message);
    }
});
</script>