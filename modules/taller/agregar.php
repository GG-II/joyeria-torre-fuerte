<?php
/**
 * ================================================
 * MÓDULO TALLER - AGREGAR TRABAJO
 * ================================================
 * 
 * TODO FASE 5: Conectar con APIs
 * GET /api/empleados/lista.php?rol=orfebre - Cargar orfebres
 * POST /api/taller/crear.php - Crear trabajo
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();
requiere_rol(['administrador', 'dueño', 'vendedor', 'cajero']);

$titulo_pagina = 'Nuevo Trabajo de Taller';
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="container-fluid main-content">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>dashboard.php"><i class="bi bi-house"></i> Dashboard</a></li>
            <li class="breadcrumb-item"><a href="lista.php"><i class="bi bi-tools"></i> Taller</a></li>
            <li class="breadcrumb-item active">Nuevo Trabajo</li>
        </ol>
    </nav>

    <div class="page-header mb-4">
        <h1 class="mb-2"><i class="bi bi-plus-circle"></i> Nuevo Trabajo de Taller</h1>
        <p class="text-muted mb-0">Registre un nuevo trabajo de orfebrería</p>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header" style="background-color: #1e3a8a; color: white;">
                    <i class="bi bi-pencil-square"></i> Información del Trabajo
                </div>
                <div class="card-body">
                    <form id="formTrabajo" method="POST">
                        <h5 class="mb-3 text-primary"><i class="bi bi-person"></i> Datos del Cliente</h5>

                        <div class="row g-3 mb-4">
                            <div class="col-md-8">
                                <label for="cliente_nombre" class="form-label"><i class="bi bi-person-badge"></i> Nombre del Cliente *</label>
                                <input type="text" class="form-control" id="cliente_nombre" name="cliente_nombre" placeholder="Nombre completo" required>
                                <input type="hidden" id="cliente_id" name="cliente_id">
                            </div>
                            <div class="col-md-4">
                                <label for="cliente_telefono" class="form-label"><i class="bi bi-phone"></i> Teléfono *</label>
                                <input type="tel" class="form-control" id="cliente_telefono" name="cliente_telefono" placeholder="5512-3456" required>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h5 class="mb-3 text-primary"><i class="bi bi-gem"></i> Información de la Pieza</h5>

                        <div class="mb-3">
                            <label for="descripcion_pieza" class="form-label"><i class="bi bi-info-circle"></i> Descripción de la Pieza *</label>
                            <textarea class="form-control" id="descripcion_pieza" name="descripcion_pieza" rows="2" placeholder="Ej: Anillo de compromiso oro 18K con diamante central" required></textarea>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label for="material" class="form-label"><i class="bi bi-circle"></i> Material *</label>
                                <select class="form-select" id="material" name="material" required>
                                    <option value="">Seleccione...</option>
                                    <option value="oro">🟡 Oro</option>
                                    <option value="plata">⚪ Plata</option>
                                    <option value="otro">⚫ Otro</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="peso_gramos" class="form-label"><i class="bi bi-weight"></i> Peso (gramos)</label>
                                <input type="number" class="form-control" id="peso_gramos" name="peso_gramos" min="0" step="0.001" placeholder="0.000">
                            </div>
                            <div class="col-md-4">
                                <label for="largo_cm" class="form-label"><i class="bi bi-rulers"></i> Largo (cm)</label>
                                <input type="number" class="form-control" id="largo_cm" name="largo_cm" min="0" step="0.01" placeholder="0.00">
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="estilo" class="form-label"><i class="bi bi-palette"></i> Estilo</label>
                                <input type="text" class="form-control" id="estilo" name="estilo" placeholder="Clásico, Moderno...">
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="con_piedra" name="con_piedra">
                                    <label class="form-check-label" for="con_piedra"><i class="bi bi-gem"></i> La pieza tiene piedras/diamantes</label>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h5 class="mb-3 text-primary"><i class="bi bi-tools"></i> Trabajo a Realizar</h5>

                        <div class="mb-3">
                            <label for="tipo_trabajo" class="form-label"><i class="bi bi-hammer"></i> Tipo de Trabajo *</label>
                            <select class="form-select" id="tipo_trabajo" name="tipo_trabajo" required>
                                <option value="">Seleccione...</option>
                                <option value="reparacion">Reparación</option>
                                <option value="ajuste">Ajuste de tamaño</option>
                                <option value="grabado">Grabado</option>
                                <option value="diseño">Diseño personalizado</option>
                                <option value="limpieza">Limpieza y pulido</option>
                                <option value="engaste">Engaste de piedras</option>
                                <option value="repuesto">Repuesto de partes</option>
                                <option value="fabricacion">Fabricación completa</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="descripcion_trabajo" class="form-label"><i class="bi bi-file-text"></i> Descripción del Trabajo *</label>
                            <textarea class="form-control" id="descripcion_trabajo" name="descripcion_trabajo" rows="3" placeholder="Describa detalladamente el trabajo a realizar..." required></textarea>
                        </div>

                        <hr class="my-4">

                        <h5 class="mb-3 text-primary"><i class="bi bi-calendar-check"></i> Precios y Entrega</h5>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label for="precio_total" class="form-label"><i class="bi bi-cash"></i> Precio Total (Q) *</label>
                                <input type="number" class="form-control" id="precio_total" name="precio_total" min="0" step="0.01" placeholder="0.00" required>
                            </div>
                            <div class="col-md-4">
                                <label for="anticipo" class="form-label"><i class="bi bi-currency-dollar"></i> Anticipo (Q)</label>
                                <input type="number" class="form-control" id="anticipo" name="anticipo" min="0" step="0.01" value="0.00">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label"><i class="bi bi-calculator"></i> Saldo</label>
                                <input type="text" class="form-control" id="saldo" readonly value="Q 0.00">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="fecha_entrega_prometida" class="form-label"><i class="bi bi-calendar-event"></i> Fecha de Entrega Prometida *</label>
                                <input type="date" class="form-control" id="fecha_entrega_prometida" name="fecha_entrega_prometida" required>
                            </div>
                            <div class="col-md-6">
                                <label for="empleado_actual_id" class="form-label"><i class="bi bi-person-workspace"></i> Asignar a Orfebre *</label>
                                <select class="form-select" id="empleado_actual_id" name="empleado_actual_id" required>
                                    <option value="">Seleccione...</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="observaciones" class="form-label"><i class="bi bi-chat-left-text"></i> Observaciones (opcional)</label>
                            <textarea class="form-control" id="observaciones" name="observaciones" rows="2" placeholder="Notas adicionales..."></textarea>
                        </div>

                        <div class="d-flex flex-column flex-sm-row justify-content-end gap-2">
                            <a href="lista.php" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Cancelar</a>
                            <button type="submit" class="btn btn-primary" id="btnGuardar"><i class="bi bi-save"></i> Guardar Trabajo</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3 shadow-sm">
                <div class="card-header" style="background-color: #1e3a8a; color: white;">
                    <i class="bi bi-lightbulb"></i> Guía Rápida
                </div>
                <div class="card-body">
                    <h6 class="fw-bold">Datos Obligatorios:</h6>
                    <ul class="small mb-3">
                        <li>Nombre y teléfono del cliente</li>
                        <li>Descripción de la pieza</li>
                        <li>Material</li>
                        <li>Tipo de trabajo</li>
                        <li>Descripción del trabajo</li>
                        <li>Precio total</li>
                        <li>Fecha de entrega</li>
                        <li>Orfebre asignado</li>
                    </ul>

                    <h6 class="fw-bold">Tipos de Trabajo:</h6>
                    <ul class="small mb-3">
                        <li><strong>Reparación:</strong> Arreglar daños</li>
                        <li><strong>Ajuste:</strong> Cambiar tamaño</li>
                        <li><strong>Grabado:</strong> Personalización</li>
                        <li><strong>Engaste:</strong> Montar piedras</li>
                        <li><strong>Fabricación:</strong> Crear desde cero</li>
                    </ul>

                    <h6 class="fw-bold">Anticipo:</h6>
                    <p class="small mb-0">Se recomienda solicitar al menos 50% de anticipo para trabajos mayores a Q 500.</p>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header" style="background-color: #1e3a8a; color: white;">
                    <i class="bi bi-eye"></i> Vista Previa
                </div>
                <div class="card-body">
                    <div class="mb-3 pb-3 border-bottom">
                        <small class="text-muted d-block">Código (generado automáticamente):</small>
                        <h5 id="preview-codigo" class="mb-0">T-<?php echo date('Y'); ?>-XXX</h5>
                    </div>
                    <div class="mb-3 pb-3 border-bottom">
                        <small class="text-muted d-block">Saldo Pendiente:</small>
                        <h4 id="preview-saldo" class="mb-0 text-danger">Q 0.00</h4>
                    </div>
                    <div>
                        <small class="text-muted d-block">Estado inicial:</small>
                        <span class="badge bg-warning">Recibido</span>
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
.form-control, .form-select { border: 1px solid #d1d5db; border-radius: 6px; padding: 0.625rem 0.75rem; }
.form-control:focus, .form-select:focus { border-color: #1e3a8a; box-shadow: 0 0 0 0.2rem rgba(30, 58, 138, 0.15); }
textarea.form-control { resize: vertical; }
.form-check-input { width: 1.2em; height: 1.2em; margin-top: 0.15em; }
.form-check-input:checked { background-color: #1e3a8a; border-color: #1e3a8a; }
h5.text-primary { color: #1e3a8a !important; font-weight: 600; }
hr { opacity: 0.1; }
.card-body h6 { color: #1a1a1a; font-size: 0.95rem; margin-bottom: 0.75rem; }
.card-body ul { padding-left: 20px; }
.card-body ul li { margin-bottom: 0.35rem; }
.border-bottom { border-bottom: 1px solid #e5e7eb !important; }
@media (max-width: 575.98px) {
    .main-content { padding: 15px 10px; }
    .page-header h1 { font-size: 1.5rem; }
    .card-body { padding: 15px; }
    h5 { font-size: 1.1rem; }
    .btn { width: 100%; }
}
@media (min-width: 576px) and (max-width: 767.98px) { .main-content { padding: 18px 15px; } }
@media (min-width: 992px) { .main-content { padding: 25px 30px; } }
@media (max-width: 767.98px) { .btn, .form-control, .form-select, textarea { min-height: 44px; } .form-check-input { width: 1.35em; height: 1.35em; } }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    cargarOrfebres();
    establecerFechaMinima();
});

function cargarOrfebres() {
    /* TODO FASE 5: Descomentar
    fetch('<?php echo BASE_URL; ?>api/empleados/lista.php?rol=orfebre')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('empleado_actual_id');
                data.data.forEach(emp => {
                    const option = document.createElement('option');
                    option.value = emp.id;
                    option.textContent = emp.nombre;
                    select.appendChild(option);
                });
            }
        });
    */
    
    const orfebres = ['Roberto Orfebre', 'Juan Artesano'];
    const select = document.getElementById('empleado_actual_id');
    orfebres.forEach((nombre, i) => {
        const option = document.createElement('option');
        option.value = i + 3;
        option.textContent = nombre;
        select.appendChild(option);
    });
}

function calcularSaldo() {
    const precio = parseFloat(document.getElementById('precio_total').value) || 0;
    const anticipo = parseFloat(document.getElementById('anticipo').value) || 0;
    const saldo = precio - anticipo;
    
    document.getElementById('saldo').value = 'Q ' + saldo.toFixed(2);
    document.getElementById('preview-saldo').textContent = 'Q ' + saldo.toFixed(2);
}

document.getElementById('precio_total').addEventListener('input', calcularSaldo);
document.getElementById('anticipo').addEventListener('input', calcularSaldo);

document.getElementById('cliente_telefono').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 4) {
        value = value.substring(0, 4) + '-' + value.substring(4, 8);
    }
    e.target.value = value;
});

document.getElementById('formTrabajo').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const anticipo = parseFloat(document.getElementById('anticipo').value) || 0;
    const precio = parseFloat(document.getElementById('precio_total').value) || 0;
    
    if (anticipo > precio) {
        alert('El anticipo no puede ser mayor al precio total');
        return;
    }
    
    const formData = new FormData(this);
    const datos = {
        cliente_nombre: formData.get('cliente_nombre'),
        cliente_telefono: formData.get('cliente_telefono'),
        cliente_id: formData.get('cliente_id') || null,
        material: formData.get('material'),
        peso_gramos: parseFloat(formData.get('peso_gramos')) || null,
        largo_cm: parseFloat(formData.get('largo_cm')) || null,
        con_piedra: formData.get('con_piedra') ? 1 : 0,
        estilo: formData.get('estilo') || null,
        descripcion_pieza: formData.get('descripcion_pieza'),
        tipo_trabajo: formData.get('tipo_trabajo'),
        descripcion_trabajo: formData.get('descripcion_trabajo'),
        precio_total: precio,
        anticipo: anticipo,
        fecha_entrega_prometida: formData.get('fecha_entrega_prometida'),
        empleado_actual_id: formData.get('empleado_actual_id'),
        observaciones: formData.get('observaciones') || null
    };
    
    const btnGuardar = document.getElementById('btnGuardar');
    btnGuardar.disabled = true;
    btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
    
    /* TODO FASE 5: Descomentar
    fetch('<?php echo BASE_URL; ?>api/taller/crear.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(datos)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Trabajo registrado exitosamente');
            setTimeout(() => window.location.href = 'ver.php?id=' + data.trabajo_id, 1500);
        } else {
            alert(data.message);
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = '<i class="bi bi-save"></i> Guardar Trabajo';
        }
    });
    */
    
    console.log('Datos:', datos);
    setTimeout(() => {
        alert('MODO DESARROLLO: Trabajo listo.\n\n' + JSON.stringify(datos, null, 2));
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = '<i class="bi bi-save"></i> Guardar Trabajo';
    }, 1000);
});

function establecerFechaMinima() {
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    document.getElementById('fecha_entrega_prometida').min = tomorrow.toISOString().split('T')[0];
}
</script>

<?php include '../../includes/footer.php'; ?>