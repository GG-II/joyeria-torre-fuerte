<?php
// ================================================
// MÓDULO TALLER - AGREGAR TRABAJO
// ================================================

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

// Verificar autenticación y permisos
requiere_autenticacion();
requiere_rol(['administrador', 'dueño', 'vendedor', 'cajero']);

// Título de página
$titulo_pagina = 'Nuevo Trabajo de Taller';

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
            <li class="breadcrumb-item active">Nuevo Trabajo</li>
        </ol>
    </nav>

    <!-- Encabezado -->
    <div class="page-header">
        <h1>
            <i class="bi bi-plus-circle"></i>
            Nuevo Trabajo de Taller
        </h1>
        <p class="text-muted">Registre un nuevo trabajo de orfebrería</p>
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
                        <!-- Cliente -->
                        <h5 class="mb-3 text-primary">
                            <i class="bi bi-person"></i>
                            Datos del Cliente
                        </h5>

                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label for="cliente_nombre" class="form-label">
                                    <i class="bi bi-person-badge"></i> Nombre del Cliente *
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="cliente_nombre" 
                                       name="cliente_nombre" 
                                       placeholder="Nombre completo"
                                       required>
                                <input type="hidden" id="cliente_id" name="cliente_id">
                            </div>
                            <div class="col-md-4">
                                <label for="cliente_telefono" class="form-label">
                                    <i class="bi bi-phone"></i> Teléfono *
                                </label>
                                <input type="tel" 
                                       class="form-control" 
                                       id="cliente_telefono" 
                                       name="cliente_telefono" 
                                       placeholder="5512-3456"
                                       required>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Información de la Pieza -->
                        <h5 class="mb-3 text-primary">
                            <i class="bi bi-gem"></i>
                            Información de la Pieza
                        </h5>

                        <div class="mb-3">
                            <label for="descripcion_pieza" class="form-label">
                                <i class="bi bi-info-circle"></i> Descripción de la Pieza *
                            </label>
                            <textarea class="form-control" 
                                      id="descripcion_pieza" 
                                      name="descripcion_pieza" 
                                      rows="2"
                                      placeholder="Ej: Anillo de compromiso oro 18K con diamante central"
                                      required></textarea>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="material" class="form-label">
                                    <i class="bi bi-circle"></i> Material *
                                </label>
                                <select class="form-select" id="material" name="material" required>
                                    <option value="">Seleccione...</option>
                                    <option value="oro">🟡 Oro</option>
                                    <option value="plata">⚪ Plata</option>
                                    <option value="otro">⚫ Otro</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="peso_gramos" class="form-label">
                                    <i class="bi bi-weight"></i> Peso (gramos)
                                </label>
                                <input type="number" 
                                       class="form-control" 
                                       id="peso_gramos" 
                                       name="peso_gramos" 
                                       min="0" 
                                       step="0.001"
                                       placeholder="0.000">
                            </div>
                            <div class="col-md-4">
                                <label for="largo_cm" class="form-label">
                                    <i class="bi bi-rulers"></i> Largo (cm)
                                </label>
                                <input type="number" 
                                       class="form-control" 
                                       id="largo_cm" 
                                       name="largo_cm" 
                                       min="0" 
                                       step="0.01"
                                       placeholder="0.00">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="estilo" class="form-label">
                                    <i class="bi bi-palette"></i> Estilo
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="estilo" 
                                       name="estilo" 
                                       placeholder="Clásico, Moderno, Elegante...">
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="con_piedra" 
                                           name="con_piedra">
                                    <label class="form-check-label" for="con_piedra">
                                        <i class="bi bi-gem"></i> La pieza tiene piedras/diamantes
                                    </label>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Tipo de Trabajo -->
                        <h5 class="mb-3 text-primary">
                            <i class="bi bi-tools"></i>
                            Trabajo a Realizar
                        </h5>

                        <div class="mb-3">
                            <label for="tipo_trabajo" class="form-label">
                                <i class="bi bi-hammer"></i> Tipo de Trabajo *
                            </label>
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

                        <div class="mb-3">
                            <label for="descripcion_trabajo" class="form-label">
                                <i class="bi bi-file-text"></i> Descripción del Trabajo *
                            </label>
                            <textarea class="form-control" 
                                      id="descripcion_trabajo" 
                                      name="descripcion_trabajo" 
                                      rows="3"
                                      placeholder="Describa detalladamente el trabajo a realizar..."
                                      required></textarea>
                        </div>

                        <hr class="my-4">

                        <!-- Precios y Fechas -->
                        <h5 class="mb-3 text-primary">
                            <i class="bi bi-calendar-check"></i>
                            Precios y Entrega
                        </h5>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="precio_total" class="form-label">
                                    <i class="bi bi-cash"></i> Precio Total (Q) *
                                </label>
                                <input type="number" 
                                       class="form-control" 
                                       id="precio_total" 
                                       name="precio_total" 
                                       min="0" 
                                       step="0.01"
                                       placeholder="0.00"
                                       required>
                            </div>
                            <div class="col-md-4">
                                <label for="anticipo" class="form-label">
                                    <i class="bi bi-currency-dollar"></i> Anticipo (Q)
                                </label>
                                <input type="number" 
                                       class="form-control" 
                                       id="anticipo" 
                                       name="anticipo" 
                                       min="0" 
                                       step="0.01"
                                       value="0.00">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">
                                    <i class="bi bi-calculator"></i> Saldo
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="saldo" 
                                       readonly
                                       value="Q 0.00">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="fecha_entrega_prometida" class="form-label">
                                    <i class="bi bi-calendar-event"></i> Fecha de Entrega Prometida *
                                </label>
                                <input type="date" 
                                       class="form-control" 
                                       id="fecha_entrega_prometida" 
                                       name="fecha_entrega_prometida" 
                                       required>
                            </div>
                            <div class="col-md-6">
                                <label for="empleado_actual_id" class="form-label">
                                    <i class="bi bi-person-workspace"></i> Asignar a Orfebre *
                                </label>
                                <select class="form-select" id="empleado_actual_id" name="empleado_actual_id" required>
                                    <option value="">Seleccione...</option>
                                    <option value="3">Roberto Orfebre</option>
                                    <option value="4">Juan Artesano</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="observaciones" class="form-label">
                                <i class="bi bi-chat-left-text"></i> Observaciones (opcional)
                            </label>
                            <textarea class="form-control" 
                                      id="observaciones" 
                                      name="observaciones" 
                                      rows="2"
                                      placeholder="Notas adicionales sobre el trabajo..."></textarea>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="lista.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i>
                                Guardar Trabajo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Panel Lateral -->
        <div class="col-lg-4">
            <!-- Ayuda -->
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <i class="bi bi-lightbulb"></i>
                    Guía Rápida
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
                    <p class="small mb-0">
                        Se recomienda solicitar al menos 50% de anticipo para trabajos mayores a Q 500.
                    </p>
                </div>
            </div>

            <!-- Vista Previa -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-eye"></i>
                    Vista Previa
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted">Código (generado automáticamente):</small>
                        <h5 id="preview-codigo" class="mb-0">T-<?php echo date('Y'); ?>-XXX</h5>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Saldo Pendiente:</small>
                        <h4 id="preview-saldo" class="mb-0 text-danger">Q 0.00</h4>
                    </div>
                    <div>
                        <small class="text-muted">Estado inicial:</small>
                        <br><span class="badge bg-warning">Recibido</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Calcular saldo automáticamente
function calcularSaldo() {
    const precio = parseFloat(document.getElementById('precio_total').value) || 0;
    const anticipo = parseFloat(document.getElementById('anticipo').value) || 0;
    const saldo = precio - anticipo;
    
    document.getElementById('saldo').value = 'Q ' + saldo.toFixed(2);
    document.getElementById('preview-saldo').textContent = 'Q ' + saldo.toFixed(2);
}

document.getElementById('precio_total').addEventListener('input', calcularSaldo);
document.getElementById('anticipo').addEventListener('input', calcularSaldo);

// Formatear teléfono
document.getElementById('cliente_telefono').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 4) {
        value = value.substring(0, 4) + '-' + value.substring(4, 8);
    }
    e.target.value = value;
});

// Validación del formulario
document.getElementById('formTrabajo').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // API insertará en:
    // 1. trabajos_taller (datos principales)
    // campos: codigo, cliente_nombre, cliente_telefono, cliente_id, material,
    //         peso_gramos, largo_cm, con_piedra, estilo, descripcion_pieza,
    //         tipo_trabajo, descripcion_trabajo, precio_total, anticipo,
    //         fecha_entrega_prometida, empleado_recibe_id (usuario actual),
    //         empleado_actual_id, estado='recibido', observaciones
    
    alert('Formulario listo para conectar con la API');
    
    const formData = new FormData(this);
    console.log('Datos del trabajo:', Object.fromEntries(formData));
});

// Establecer fecha mínima (mañana)
const tomorrow = new Date();
tomorrow.setDate(tomorrow.getDate() + 1);
document.getElementById('fecha_entrega_prometida').min = tomorrow.toISOString().split('T')[0];
</script>

<?php
// Incluir footer
include '../../includes/footer.php';
?>