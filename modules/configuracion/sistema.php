<?php
/**
 * ================================================
 * MÓDULO CONFIGURACIÓN - SISTEMA
 * ================================================
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();
requiere_rol(['administrador', 'dueño']);

require_once '../../includes/header.php';
require_once '../../includes/navbar.php';
?>

<div class="container-fluid px-4 py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-1"><i class="bi bi-gear"></i> Configuración del Sistema</h2>
            <p class="text-muted mb-0">Ajustes generales de la joyería</p>
        </div>
    </div>

    <hr class="border-warning border-2 opacity-75 mb-4">

    <div class="row">
        
        <!-- Columna Principal -->
        <div class="col-lg-8">
            
            <!-- Información del Negocio -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-building"></i> Información del Negocio</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre de la Joyería</label>
                        <p class="fs-4 mb-0">Joyería Torre Fuerte</p>
                        <small class="text-muted">Configurado en el sistema</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Ubicaciones</label>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-geo-alt-fill text-primary"></i> Los Arcos, Huehuetenango</li>
                            <li><i class="bi bi-geo-alt-fill text-primary"></i> Centro Comercial Chinaca Central</li>
                        </ul>
                        <small class="text-muted">Administrar en el módulo de Sucursales</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Moneda</label>
                        <p class="mb-0">Quetzal Guatemalteco (Q)</p>
                        <small class="text-muted">Moneda por defecto del sistema</small>
                    </div>
                </div>
            </div>

            <!-- Preferencias de Visualización -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-palette"></i> Preferencias de Visualización</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Colores Corporativos</label>
                        <div class="d-flex gap-3 align-items-center">
                            <div>
                                <div class="border rounded" style="width: 60px; height: 60px; background-color: #D4AF37;"></div>
                                <small class="d-block text-center mt-1">Dorado</small>
                            </div>
                            <div>
                                <div class="border rounded" style="width: 60px; height: 60px; background-color: #1e3a8a;"></div>
                                <small class="d-block text-center mt-1">Azul</small>
                            </div>
                            <div>
                                <div class="border rounded" style="width: 60px; height: 60px; background-color: #C0C0C0;"></div>
                                <small class="d-block text-center mt-1">Plateado</small>
                            </div>
                            <div>
                                <div class="border rounded" style="width: 60px; height: 60px; background-color: #1a1a1a;"></div>
                                <small class="d-block text-center mt-1">Negro</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Formato de Fecha</label>
                        <p class="mb-0"><?php echo date('d/m/Y H:i'); ?></p>
                        <small class="text-muted">Formato: DD/MM/AAAA HH:MM</small>
                    </div>

                    <div>
                        <label class="form-label fw-bold">Zona Horaria</label>
                        <p class="mb-0">América/Guatemala (GMT-6)</p>
                        <small class="text-muted">Hora actual: <?php echo date('H:i:s'); ?></small>
                    </div>
                </div>
            </div>

            <!-- Configuración de Inventario -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-box-seam"></i> Configuración de Inventario</h5>
                </div>
                <div class="card-body">
                    <form id="formInventario">
                        <div class="mb-3">
                            <label for="stock_minimo_default" class="form-label">
                                Stock Mínimo por Defecto
                            </label>
                            <input type="number" class="form-control" id="stock_minimo_default" 
                                   value="5" min="0">
                            <small class="text-muted">Se aplicará al crear nuevos productos</small>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="alertas_stock" checked>
                            <label class="form-check-label" for="alertas_stock">
                                <strong>Activar Alertas de Stock Bajo</strong>
                                <br><small class="text-muted">Mostrar notificaciones en el dashboard</small>
                            </label>
                        </div>

                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="auto_generar_codigo" checked>
                            <label class="form-check-label" for="auto_generar_codigo">
                                <strong>Auto-generar Códigos de Barras</strong>
                                <br><small class="text-muted">Generar automáticamente al crear producto</small>
                            </label>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Guardar Configuración
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Configuración de Taller -->
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-tools"></i> Configuración de Taller</h5>
                </div>
                <div class="card-body">
                    <form id="formTaller">
                        <div class="mb-3">
                            <label for="dias_entrega_default" class="form-label">
                                Días de Entrega por Defecto
                            </label>
                            <input type="number" class="form-control" id="dias_entrega_default" 
                                   value="7" min="1" max="30">
                            <small class="text-muted">Se sumará a la fecha actual al crear trabajo</small>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="alertas_atraso" checked>
                            <label class="form-check-label" for="alertas_atraso">
                                <strong>Alertas de Trabajos Atrasados</strong>
                                <br><small class="text-muted">Destacar en rojo los trabajos vencidos</small>
                            </label>
                        </div>

                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="permitir_entrega_deuda" checked>
                            <label class="form-check-label" for="permitir_entrega_deuda">
                                <strong>Permitir Entregar con Saldo Pendiente</strong>
                                <br><small class="text-muted">Marcar como advertencia pero permitir entrega</small>
                            </label>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-info">
                                <i class="bi bi-check-circle"></i> Guardar Configuración
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>

        <!-- Sidebar Derecho -->
        <div class="col-lg-4">
            
            <!-- Info del Sistema -->
            <div class="card shadow-sm mb-4 border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Información del Sistema</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Versión:</small>
                        <p class="mb-0 fw-bold">1.0.0</p>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Última Actualización:</small>
                        <p class="mb-0">Febrero 2026</p>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Base de Datos:</small>
                        <p class="mb-0">MySQL 8.0</p>
                    </div>
                    <div>
                        <small class="text-muted">Servidor:</small>
                        <p class="mb-0">Apache/PHP 8.x</p>
                    </div>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-lightning"></i> Acciones Rápidas</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="../configuracion/usuarios.php" class="btn btn-outline-primary">
                            <i class="bi bi-people"></i> Administrar Usuarios
                        </a>
                        <a href="../configuracion/sucursales.php" class="btn btn-outline-primary">
                            <i class="bi bi-building"></i> Administrar Sucursales
                        </a>
                        <a href="../categorias/lista.php" class="btn btn-outline-primary">
                            <i class="bi bi-tags"></i> Administrar Categorías
                        </a>
                    </div>
                </div>
            </div>

            <!-- Soporte -->
            <div class="card shadow-sm border-secondary">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-question-circle"></i> Soporte</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <i class="bi bi-envelope"></i> 
                        <strong>Email:</strong><br>
                        <a href="mailto:soporte@joyeriatf.com">soporte@joyeriatf.com</a>
                    </p>
                    <p class="mb-2">
                        <i class="bi bi-telephone"></i> 
                        <strong>Teléfono:</strong><br>
                        <a href="tel:+50212345678">+502 1234-5678</a>
                    </p>
                    <p class="mb-0">
                        <i class="bi bi-whatsapp"></i> 
                        <strong>WhatsApp:</strong><br>
                        <a href="https://wa.me/50212345678" target="_blank">Enviar mensaje</a>
                    </p>
                </div>
            </div>

        </div>

    </div>

</div>

<?php require_once '../../includes/footer.php'; ?>

<script src="../../assets/js/vendors/sweetalert2/sweetalert2.all.min.js"></script>
<script src="../../assets/js/common.js"></script>

<script>
// Guardar configuración de inventario
document.getElementById('formInventario').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const config = {
        stock_minimo_default: document.getElementById('stock_minimo_default').value,
        alertas_stock: document.getElementById('alertas_stock').checked,
        auto_generar_codigo: document.getElementById('auto_generar_codigo').checked
    };
    
    // Por ahora solo guardar en localStorage
    // En el futuro se puede crear un endpoint API
    localStorage.setItem('config_inventario', JSON.stringify(config));
    
    await mostrarExito('Configuración de inventario guardada');
});

// Guardar configuración de taller
document.getElementById('formTaller').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const config = {
        dias_entrega_default: document.getElementById('dias_entrega_default').value,
        alertas_atraso: document.getElementById('alertas_atraso').checked,
        permitir_entrega_deuda: document.getElementById('permitir_entrega_deuda').checked
    };
    
    // Por ahora solo guardar en localStorage
    localStorage.setItem('config_taller', JSON.stringify(config));
    
    await mostrarExito('Configuración de taller guardada');
});

// Cargar configuraciones guardadas
document.addEventListener('DOMContentLoaded', function() {
    // Inventario
    const configInventario = localStorage.getItem('config_inventario');
    if (configInventario) {
        const config = JSON.parse(configInventario);
        document.getElementById('stock_minimo_default').value = config.stock_minimo_default || 5;
        document.getElementById('alertas_stock').checked = config.alertas_stock !== false;
        document.getElementById('auto_generar_codigo').checked = config.auto_generar_codigo !== false;
    }
    
    // Taller
    const configTaller = localStorage.getItem('config_taller');
    if (configTaller) {
        const config = JSON.parse(configTaller);
        document.getElementById('dias_entrega_default').value = config.dias_entrega_default || 7;
        document.getElementById('alertas_atraso').checked = config.alertas_atraso !== false;
        document.getElementById('permitir_entrega_deuda').checked = config.permitir_entrega_deuda !== false;
    }
});
</script>