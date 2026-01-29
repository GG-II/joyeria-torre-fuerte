<?php
/**
 * ================================================
 * MÓDULO CONFIGURACIÓN - CONFIGURACIÓN DEL SISTEMA
 * ================================================
 * 
 * TODO FASE 5: Conectar con API
 * GET /api/configuracion/sistema.php - Cargar configuración actual
 * POST /api/configuracion/sistema.php - Guardar cambios
 * 
 * Tabla BD: configuracion_sistema
 * Campos: nombre_empresa, rfc, direccion, telefono, email, moneda, simbolo_moneda,
 *         iva, dias_credito_default, stock_minimo_alerta, dias_alerta_trabajo,
 *         formato_fecha, zona_horaria
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();
requiere_rol(['administrador', 'dueño']);

$titulo_pagina = 'Configuración del Sistema';
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="container-fluid main-content">
    <div class="page-header mb-4">
        <h1 class="mb-2"><i class="bi bi-gear"></i> Configuración del Sistema</h1>
        <p class="text-muted mb-0">Parámetros generales del sistema</p>
    </div>

    <div id="loadingState" class="text-center py-5">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
        <p class="mt-3 text-muted">Cargando configuración del sistema...</p>
    </div>

    <div id="mainContent" style="display: none;">
        <div class="row g-3">
            <div class="col-lg-8">
                <form id="formConfiguracion">
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header" style="background-color: #1e3a8a; color: white;">
                            <i class="bi bi-building"></i> Información de la Empresa
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Nombre de la Empresa *</label>
                                <input type="text" class="form-control" name="nombre_empresa" id="nombre_empresa" required>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">RFC/NIT</label>
                                    <input type="text" class="form-control" name="rfc" id="rfc">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Teléfono Principal</label>
                                    <input type="tel" class="form-control" name="telefono" id="telefono">
                                </div>
                            </div>
                            <div class="mb-3 mt-3">
                                <label class="form-label">Dirección</label>
                                <input type="text" class="form-control" name="direccion" id="direccion">
                            </div>
                            <div class="mb-0">
                                <label class="form-label">Email de Contacto</label>
                                <input type="email" class="form-control" name="email" id="email">
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-success text-white">
                            <i class="bi bi-currency-dollar"></i> Configuración Financiera
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Moneda</label>
                                    <select class="form-select" name="moneda" id="moneda">
                                        <option value="GTQ">Quetzal Guatemalteco (GTQ)</option>
                                        <option value="USD">Dólar Estadounidense (USD)</option>
                                        <option value="MXN">Peso Mexicano (MXN)</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Símbolo de Moneda</label>
                                    <input type="text" class="form-control" name="simbolo_moneda" id="simbolo_moneda" maxlength="3">
                                </div>
                            </div>
                            <div class="row g-3 mt-2">
                                <div class="col-md-6">
                                    <label class="form-label">IVA (%)</label>
                                    <input type="number" class="form-control" name="iva" id="iva" step="0.01" min="0" max="100">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Días de Crédito por Defecto</label>
                                    <input type="number" class="form-control" name="dias_credito_default" id="dias_credito_default" min="1">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-warning text-dark">
                            <i class="bi bi-box-seam"></i> Configuración de Inventario
                        </div>
                        <div class="card-body">
                            <label class="form-label">Stock Mínimo para Alerta</label>
                            <input type="number" class="form-control" name="stock_minimo_alerta" id="stock_minimo_alerta" min="0">
                            <small class="text-muted">Se mostrará alerta cuando un producto tenga menos de esta cantidad</small>
                        </div>
                    </div>

                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-info text-white">
                            <i class="bi bi-tools"></i> Configuración del Taller
                        </div>
                        <div class="card-body">
                            <label class="form-label">Días de Alerta para Trabajos</label>
                            <input type="number" class="form-control" name="dias_alerta_trabajo" id="dias_alerta_trabajo" min="1">
                            <small class="text-muted">Se mostrará alerta cuando falten menos de estos días para la fecha de entrega</small>
                        </div>
                    </div>

                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-secondary text-white">
                            <i class="bi bi-globe"></i> Configuración Regional
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Formato de Fecha</label>
                                    <select class="form-select" name="formato_fecha" id="formato_fecha">
                                        <option value="d/m/Y">DD/MM/YYYY</option>
                                        <option value="m/d/Y">MM/DD/YYYY</option>
                                        <option value="Y-m-d">YYYY-MM-DD</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Zona Horaria</label>
                                    <select class="form-select" name="zona_horaria" id="zona_horaria">
                                        <option value="America/Guatemala">Guatemala (GMT-6)</option>
                                        <option value="America/Mexico_City">Ciudad de México (GMT-6)</option>
                                        <option value="America/New_York">Nueva York (GMT-5)</option>
                                        <option value="America/Los_Angeles">Los Ángeles (GMT-8)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" onclick="cargarConfiguracion()">
                            <i class="bi bi-arrow-clockwise"></i> Restablecer
                        </button>
                        <button type="submit" class="btn btn-primary" id="btnGuardar">
                            <i class="bi bi-save"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>

            <div class="col-lg-4">
                <div class="card mb-3 shadow-sm">
                    <div class="card-header bg-dark text-white">
                        <i class="bi bi-info-circle"></i> Información del Sistema
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <small class="text-muted d-block">Versión:</small>
                            <strong>1.0.0</strong>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block">Base de Datos:</small>
                            <strong>MySQL 8.0</strong>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block">PHP:</small>
                            <strong>8.2</strong>
                        </div>
                        <div class="mb-0">
                            <small class="text-muted d-block">Última Actualización:</small>
                            <strong id="fechaActualizacion">-</strong>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header"><i class="bi bi-gear"></i> Mantenimiento</div>
                    <div class="list-group list-group-flush">
                        <button class="list-group-item list-group-item-action" onclick="respaldarBD()">
                            <i class="bi bi-database"></i> Respaldar Base de Datos
                        </button>
                        <button class="list-group-item list-group-item-action" onclick="limpiarCache()">
                            <i class="bi bi-arrow-clockwise"></i> Limpiar Caché
                        </button>
                        <button class="list-group-item list-group-item-action" onclick="verLogs()">
                            <i class="bi bi-file-earmark-text"></i> Ver Logs del Sistema
                        </button>
                        <button class="list-group-item list-group-item-action text-danger" onclick="modoMantenimiento()">
                            <i class="bi bi-exclamation-triangle"></i> Modo Mantenimiento
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.main-content { padding: 20px; min-height: calc(100vh - 120px); }
.page-header h1 { font-size: 1.75rem; font-weight: 600; color: #1a1a1a; }
.shadow-sm { box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08) !important; }
.card-body { padding: 25px; }
.form-label { font-weight: 500; margin-bottom: 0.5rem; color: #374151; }
.form-control, .form-select { border: 1px solid #d1d5db; border-radius: 6px; }
.form-control:focus, .form-select:focus { border-color: #1e3a8a; box-shadow: 0 0 0 0.2rem rgba(30, 58, 138, 0.15); }
.list-group-item { transition: background-color 0.15s ease; }
.list-group-item:hover { background-color: #f3f4f6; }
@media (max-width: 575.98px) {
    .main-content { padding: 15px 10px; }
    .page-header h1 { font-size: 1.5rem; }
    .card-body { padding: 15px; }
}
@media (min-width: 576px) and (max-width: 767.98px) { .main-content { padding: 18px 15px; } }
@media (min-width: 992px) { .main-content { padding: 25px 30px; } }
@media (max-width: 767.98px) { .btn, .form-control, .form-select { min-height: 44px; } }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    cargarConfiguracion();
});

function cargarConfiguracion() {
    /* TODO FASE 5: Descomentar
    fetch('<?php echo BASE_URL; ?>api/configuracion/sistema.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                llenarFormulario(data.data);
                document.getElementById('loadingState').style.display = 'none';
                document.getElementById('mainContent').style.display = 'block';
            }
        })
        .catch(error => console.error('Error:', error));
    */
    
    setTimeout(() => {
        document.getElementById('loadingState').style.display = 'none';
        document.getElementById('mainContent').style.display = 'block';
        mostrarMensajeDesarrollo();
    }, 1500);
}

function llenarFormulario(config) {
    document.getElementById('nombre_empresa').value = config.nombre_empresa || '';
    document.getElementById('rfc').value = config.rfc || '';
    document.getElementById('telefono').value = config.telefono || '';
    document.getElementById('direccion').value = config.direccion || '';
    document.getElementById('email').value = config.email || '';
    document.getElementById('moneda').value = config.moneda || 'GTQ';
    document.getElementById('simbolo_moneda').value = config.simbolo_moneda || 'Q';
    document.getElementById('iva').value = config.iva || 12;
    document.getElementById('dias_credito_default').value = config.dias_credito_default || 30;
    document.getElementById('stock_minimo_alerta').value = config.stock_minimo_alerta || 5;
    document.getElementById('dias_alerta_trabajo').value = config.dias_alerta_trabajo || 3;
    document.getElementById('formato_fecha').value = config.formato_fecha || 'd/m/Y';
    document.getElementById('zona_horaria').value = config.zona_horaria || 'America/Guatemala';
    
    if (config.ultima_actualizacion) {
        document.getElementById('fechaActualizacion').textContent = formatearFechaHora(config.ultima_actualizacion);
    }
}

document.getElementById('formConfiguracion').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const datos = Object.fromEntries(formData);
    
    const btnGuardar = document.getElementById('btnGuardar');
    btnGuardar.disabled = true;
    btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
    
    /* TODO FASE 5: Descomentar
    fetch('<?php echo BASE_URL; ?>api/configuracion/sistema.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(datos)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Configuración guardada exitosamente');
            cargarConfiguracion();
        } else {
            alert(data.message);
        }
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = '<i class="bi bi-save"></i> Guardar Cambios';
    });
    */
    
    setTimeout(() => {
        alert('MODO DESARROLLO: Configuración guardada.\n\n' + JSON.stringify(datos, null, 2));
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = '<i class="bi bi-save"></i> Guardar Cambios';
    }, 1000);
});

function mostrarMensajeDesarrollo() {
    document.getElementById('fechaActualizacion').textContent = 'MODO DESARROLLO';
}

function respaldarBD() { alert('MODO DESARROLLO: Respaldar base de datos - Pendiente implementar'); }
function limpiarCache() { alert('MODO DESARROLLO: Limpiar caché - Pendiente implementar'); }
function verLogs() { alert('MODO DESARROLLO: Ver logs del sistema - Pendiente implementar'); }
function modoMantenimiento() {
    if (confirm('¿Está seguro de activar el modo mantenimiento?\n\nEsto impedirá el acceso al sistema a todos los usuarios excepto administradores.')) {
        alert('MODO DESARROLLO: Modo mantenimiento - Pendiente implementar');
    }
}

function formatearFechaHora(fecha) {
    const d = new Date(fecha);
    return d.toLocaleDateString('es-GT', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}
</script>

<?php include '../../includes/footer.php'; ?>