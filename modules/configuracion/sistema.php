<?php
// ================================================
// MÓDULO CONFIGURACIÓN - CONFIGURACIÓN DEL SISTEMA
// ================================================

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();
requiere_rol(['administrador', 'dueño']);

$titulo_pagina = 'Configuración del Sistema';
include '../../includes/header.php';
include '../../includes/navbar.php';

// Configuraciones actuales (dummy)
$config = [
    'nombre_empresa' => 'Joyería Torre Fuerte',
    'rfc' => '123456789-0',
    'direccion' => 'Huehuetenango, Guatemala',
    'telefono' => '2234-5678',
    'email' => 'info@joyeriatf.com',
    'moneda' => 'GTQ',
    'simbolo_moneda' => 'Q',
    'iva' => 12,
    'dias_credito_default' => 30,
    'stock_minimo_alerta' => 5,
    'dias_alerta_trabajo' => 3,
    'formato_fecha' => 'd/m/Y',
    'zona_horaria' => 'America/Guatemala'
];
?>

<div class="container-fluid main-content">
    <div class="page-header">
        <h1><i class="bi bi-gear"></i> Configuración del Sistema</h1>
        <p class="text-muted">Parámetros generales del sistema</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form id="formConfiguracion">
                <!-- Información de la Empresa -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-building"></i>
                        Información de la Empresa
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre de la Empresa</label>
                            <input type="text" class="form-control" name="nombre_empresa" 
                                   value="<?php echo $config['nombre_empresa']; ?>">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">RFC/NIT</label>
                                <input type="text" class="form-control" name="rfc" 
                                       value="<?php echo $config['rfc']; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Teléfono Principal</label>
                                <input type="tel" class="form-control" name="telefono" 
                                       value="<?php echo $config['telefono']; ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dirección</label>
                            <input type="text" class="form-control" name="direccion" 
                                   value="<?php echo $config['direccion']; ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email de Contacto</label>
                            <input type="email" class="form-control" name="email" 
                                   value="<?php echo $config['email']; ?>">
                        </div>
                    </div>
                </div>

                <!-- Configuración Financiera -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <i class="bi bi-currency-dollar"></i>
                        Configuración Financiera
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Moneda</label>
                                <select class="form-select" name="moneda">
                                    <option value="GTQ" selected>Quetzal Guatemalteco (GTQ)</option>
                                    <option value="USD">Dólar Estadounidense (USD)</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Símbolo de Moneda</label>
                                <input type="text" class="form-control" name="simbolo_moneda" 
                                       value="<?php echo $config['simbolo_moneda']; ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">IVA (%)</label>
                                <input type="number" class="form-control" name="iva" 
                                       value="<?php echo $config['iva']; ?>" step="0.01">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Días de Crédito por Defecto</label>
                                <input type="number" class="form-control" name="dias_credito_default" 
                                       value="<?php echo $config['dias_credito_default']; ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Configuración de Inventario -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <i class="bi bi-box-seam"></i>
                        Configuración de Inventario
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Stock Mínimo para Alerta</label>
                            <input type="number" class="form-control" name="stock_minimo_alerta" 
                                   value="<?php echo $config['stock_minimo_alerta']; ?>">
                            <small class="text-muted">
                                Se mostrará alerta cuando un producto tenga menos de esta cantidad
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Configuración del Taller -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <i class="bi bi-tools"></i>
                        Configuración del Taller
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Días de Alerta para Trabajos</label>
                            <input type="number" class="form-control" name="dias_alerta_trabajo" 
                                   value="<?php echo $config['dias_alerta_trabajo']; ?>">
                            <small class="text-muted">
                                Se mostrará alerta cuando falten menos de estos días para la fecha de entrega
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Configuración Regional -->
                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">
                        <i class="bi bi-globe"></i>
                        Configuración Regional
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Formato de Fecha</label>
                                <select class="form-select" name="formato_fecha">
                                    <option value="d/m/Y" selected>DD/MM/YYYY</option>
                                    <option value="m/d/Y">MM/DD/YYYY</option>
                                    <option value="Y-m-d">YYYY-MM-DD</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Zona Horaria</label>
                                <select class="form-select" name="zona_horaria">
                                    <option value="America/Guatemala" selected>Guatemala (GMT-6)</option>
                                    <option value="America/Mexico_City">Ciudad de México (GMT-6)</option>
                                    <option value="America/New_York">Nueva York (GMT-5)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-secondary" onclick="window.location.reload()">
                        <i class="bi bi-arrow-clockwise"></i>
                        Restablecer
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i>
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>

        <!-- Panel Lateral -->
        <div class="col-lg-4">
            <!-- Información del Sistema -->
            <div class="card mb-3">
                <div class="card-header bg-dark text-white">
                    <i class="bi bi-info-circle"></i>
                    Información del Sistema
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
                        <strong>24/01/2025</strong>
                    </div>
                </div>
            </div>

            <!-- Acciones del Sistema -->
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-gear"></i>
                    Mantenimiento
                </div>
                <div class="list-group list-group-flush">
                    <button class="list-group-item list-group-item-action">
                        <i class="bi bi-database"></i>
                        Respaldar Base de Datos
                    </button>
                    <button class="list-group-item list-group-item-action">
                        <i class="bi bi-arrow-clockwise"></i>
                        Limpiar Caché
                    </button>
                    <button class="list-group-item list-group-item-action">
                        <i class="bi bi-file-earmark-text"></i>
                        Ver Logs del Sistema
                    </button>
                    <button class="list-group-item list-group-item-action text-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        Modo Mantenimiento
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('formConfiguracion').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    // API: UPDATE configuracion SET ... WHERE id = 1
    // O guardar en archivo config.php
    
    console.log('Configuración:', Object.fromEntries(formData));
    alert('Configuración guardada exitosamente');
});
</script>

<?php include '../../includes/footer.php'; ?>