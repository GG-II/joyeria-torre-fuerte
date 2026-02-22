<?php
/**
 * ================================================
 * DASHBOARD PRINCIPAL
 * ================================================
 * Vista principal del sistema - Menú de navegación
 */

require_once 'config.php';
require_once 'includes/db.php';
require_once 'includes/funciones.php';
require_once 'includes/auth.php';

requiere_autenticacion();

$titulo_pagina = 'Dashboard';
include 'includes/header.php';
include 'includes/navbar.php';

// Obtener nombre del usuario
$nombre_usuario = explode(' ', usuario_actual_nombre())[0];
?>

<div class="dashboard-container">
    
    <!-- Header con Logo y Bienvenida -->
    <div class="dashboard-header">
        <div class="logo-section">
            <img src="<?php echo BASE_URL; ?>assets/img/logo-torre-fuerte.png" alt="Torre Fuerte" class="dashboard-logo">
        </div>
        <div class="welcome-section">
            <h1 class="welcome-title">Bienvenido, <span class="user-name"><?php echo htmlspecialchars($nombre_usuario); ?></span></h1>
            <div class="datetime-info">
                <i class="bi bi-clock"></i>
                <span id="currentTime">--:-- --</span>
                <span class="separator">•</span>
                <span id="currentDate">--</span>
            </div>
        </div>
    </div>

    <!-- Grid de Módulos -->
    <div class="modules-grid">
        
        <?php if (tiene_permiso('ventas', 'ver')): ?>
        <a href="<?php echo BASE_URL; ?>modules/ventas/nueva.php" class="module-card ventas">
            <div class="module-icon">
                <i class="bi bi-cart-check"></i>
            </div>
            <h3 class="module-title">Ventas</h3>
            <p class="module-description">Registrar nueva venta</p>
            <div class="module-arrow">
                <i class="bi bi-arrow-right"></i>
            </div>
        </a>
        <?php endif; ?>

        <?php if (tiene_permiso('productos', 'ver') || tiene_permiso('inventario', 'ver')): ?>
        <a href="<?php echo BASE_URL; ?>modules/inventario/lista.php" class="module-card inventario">
            <div class="module-icon">
                <i class="bi bi-box-seam"></i>
            </div>
            <h3 class="module-title">Inventario</h3>
            <p class="module-description">Gestionar productos</p>
            <div class="module-arrow">
                <i class="bi bi-arrow-right"></i>
            </div>
        </a>
        <?php endif; ?>

        <?php if (tiene_permiso('clientes', 'ver')): ?>
        <a href="<?php echo BASE_URL; ?>modules/clientes/lista.php" class="module-card clientes">
            <div class="module-icon">
                <i class="bi bi-people"></i>
            </div>
            <h3 class="module-title">Clientes</h3>
            <p class="module-description">Gestionar clientes</p>
            <div class="module-arrow">
                <i class="bi bi-arrow-right"></i>
            </div>
        </a>
        <?php endif; ?>

        <?php if (tiene_permiso('taller', 'ver')): ?>
        <a href="<?php echo BASE_URL; ?>modules/taller/lista.php" class="module-card taller">
            <div class="module-icon">
                <i class="bi bi-tools"></i>
            </div>
            <h3 class="module-title">Taller</h3>
            <p class="module-description">Trabajos y reparaciones</p>
            <div class="module-arrow">
                <i class="bi bi-arrow-right"></i>
            </div>
        </a>
        <?php endif; ?>

        <?php if (tiene_permiso('caja', 'ver')): ?>
        <a href="<?php echo BASE_URL; ?>modules/caja/lista.php" class="module-card caja">
            <div class="module-icon">
                <i class="bi bi-cash-coin"></i>
            </div>
            <h3 class="module-title">Caja</h3>
            <p class="module-description">Control de efectivo</p>
            <div class="module-arrow">
                <i class="bi bi-arrow-right"></i>
            </div>
        </a>
        <?php endif; ?>

        <?php if (tiene_permiso('proveedores', 'ver')): ?>
        <a href="<?php echo BASE_URL; ?>modules/proveedores/lista.php" class="module-card proveedores">
            <div class="module-icon">
                <i class="bi bi-truck"></i>
            </div>
            <h3 class="module-title">Proveedores</h3>
            <p class="module-description">Gestionar proveedores</p>
            <div class="module-arrow">
                <i class="bi bi-arrow-right"></i>
            </div>
        </a>
        <?php endif; ?>

<?php if (in_array(usuario_actual_rol(), ['administrador', 'dueño', 'publicidad'])): ?>
<a href="<?php echo BASE_URL; ?>modules/reportes/dashboard.php" class="module-card reportes">
    <div class="module-icon">
        <i class="bi bi-graph-up"></i>
    </div>
    <h3 class="module-title">Reportes</h3>
    <p class="module-description">Análisis y estadísticas</p>
    <div class="module-arrow">
        <i class="bi bi-arrow-right"></i>
    </div>
</a>
<?php endif; ?>

        <?php if (tiene_permiso('usuarios', 'ver') || tiene_permiso('sucursales', 'ver')): ?>
        <a href="<?php echo BASE_URL; ?>modules/configuracion/sistema.php" class="module-card configuracion">
            <div class="module-icon">
                <i class="bi bi-gear"></i>
            </div>
            <h3 class="module-title">Configuración</h3>
            <p class="module-description">Sistema y usuarios</p>
            <div class="module-arrow">
                <i class="bi bi-arrow-right"></i>
            </div>
        </a>
        <?php endif; ?>

    </div>

</div>

<style>
/* ============================================
   DASHBOARD MODERNO - ESTILOS
   ============================================ */

.dashboard-container {
    min-height: calc(100vh - 60px);
    padding: 40px 60px;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

/* Header */
.dashboard-header {
    text-align: center;
    margin-bottom: 60px;
    animation: fadeInDown 0.6s ease;
}

.logo-section {
    margin-bottom: 30px;
}

.dashboard-logo {
    height: 100px;
    width: auto;
    filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1));
    animation: float 3s ease-in-out infinite;
}

.welcome-section {
    background: white;
    padding: 30px 40px;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    display: inline-block;
    min-width: 500px;
}

.welcome-title {
    font-size: 2rem;
    font-weight: 600;
    color: #1a1a1a;
    margin: 0 0 15px 0;
}

.user-name {
    color: #d4af37;
    font-weight: 700;
}

.datetime-info {
    font-size: 1.1rem;
    color: #6b7280;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.datetime-info i {
    color: #1e3a8a;
    font-size: 1.2rem;
}

.separator {
    color: #d1d5db;
    font-weight: 300;
}

/* Grid de Módulos */
.modules-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 25px;
    max-width: 1400px;
    margin: 0 auto;
}

/* Tarjetas de Módulos */
.module-card {
    background: white;
    border-radius: 16px;
    padding: 35px 30px;
    text-decoration: none;
    color: #1a1a1a;
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 2px solid transparent;
    animation: fadeInUp 0.6s ease;
    animation-fill-mode: both;
}

.module-card:nth-child(1) { animation-delay: 0.1s; }
.module-card:nth-child(2) { animation-delay: 0.2s; }
.module-card:nth-child(3) { animation-delay: 0.3s; }
.module-card:nth-child(4) { animation-delay: 0.4s; }
.module-card:nth-child(5) { animation-delay: 0.5s; }
.module-card:nth-child(6) { animation-delay: 0.6s; }
.module-card:nth-child(7) { animation-delay: 0.7s; }
.module-card:nth-child(8) { animation-delay: 0.8s; }

.module-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #d4af37, #f4e4b7);
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.3s ease;
}

.module-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
    border-color: #d4af37;
}

.module-card:hover::before {
    transform: scaleX(1);
}

.module-icon {
    width: 70px;
    height: 70px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    margin-bottom: 20px;
    transition: all 0.3s ease;
}

.module-card:hover .module-icon {
    transform: scale(1.1) rotate(5deg);
}

.module-title {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0 0 10px 0;
    color: #1a1a1a;
}

.module-description {
    font-size: 0.95rem;
    color: #6b7280;
    margin: 0;
}

.module-arrow {
    position: absolute;
    bottom: 25px;
    right: 25px;
    font-size: 1.5rem;
    color: #d4af37;
    opacity: 0;
    transform: translateX(-10px);
    transition: all 0.3s ease;
}

.module-card:hover .module-arrow {
    opacity: 1;
    transform: translateX(0);
}

/* Colores por Módulo */
.module-card.ventas .module-icon {
    background: linear-gradient(135deg, #22c55e, #16a34a);
    color: white;
}

.module-card.inventario .module-icon {
    background: linear-gradient(135deg, #d4af37, #b8941f);
    color: white;
}

.module-card.clientes .module-icon {
    background: linear-gradient(135deg, #1e3a8a, #1e40af);
    color: white;
}

.module-card.taller .module-icon {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: white;
}

.module-card.caja .module-icon {
    background: linear-gradient(135deg, #8b5cf6, #7c3aed);
    color: white;
}

.module-card.proveedores .module-icon {
    background: linear-gradient(135deg, #0ea5e9, #0284c7);
    color: white;
}

.module-card.reportes .module-icon {
    background: linear-gradient(135deg, #ec4899, #db2777);
    color: white;
}

.module-card.configuracion .module-icon {
    background: linear-gradient(135deg, #6b7280, #4b5563);
    color: white;
}

/* Animaciones */
@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes float {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-10px);
    }
}

/* ============================================
   RESPONSIVE
   ============================================ */

@media (max-width: 1200px) {
    .modules-grid {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }
}

@media (max-width: 768px) {
    .dashboard-container {
        padding: 30px 20px;
    }
    
    .dashboard-header {
        margin-bottom: 40px;
    }
    
    .dashboard-logo {
        height: 80px;
    }
    
    .welcome-section {
        min-width: auto;
        width: 100%;
        padding: 25px 20px;
    }
    
    .welcome-title {
        font-size: 1.5rem;
    }
    
    .datetime-info {
        font-size: 0.95rem;
        flex-wrap: wrap;
    }
    
    .modules-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .module-card {
        padding: 25px 20px;
    }
    
    .module-icon {
        width: 60px;
        height: 60px;
        font-size: 28px;
    }
    
    .module-title {
        font-size: 1.25rem;
    }
}

@media (max-width: 480px) {
    .dashboard-container {
        padding: 20px 15px;
    }
    
    .dashboard-logo {
        height: 60px;
    }
    
    .welcome-title {
        font-size: 1.25rem;
    }
    
    .datetime-info {
        font-size: 0.85rem;
    }
}
</style>

<script>
// Reloj en tiempo real (hora de Guatemala)
function actualizarReloj() {
    const now = new Date();
    
    // Hora de Guatemala (GMT-6)
    const guatemalaTime = new Date(now.toLocaleString('en-US', { timeZone: 'America/Guatemala' }));
    
    // Formatear hora
    let horas = guatemalaTime.getHours();
    const minutos = guatemalaTime.getMinutes().toString().padStart(2, '0');
    const ampm = horas >= 12 ? 'PM' : 'AM';
    horas = horas % 12 || 12;
    
    const timeString = `${horas}:${minutos} ${ampm}`;
    
    // Formatear fecha
    const dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    const meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 
                   'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    
    const diaSemana = dias[guatemalaTime.getDay()];
    const dia = guatemalaTime.getDate();
    const mes = meses[guatemalaTime.getMonth()];
    const año = guatemalaTime.getFullYear();
    
    const dateString = `${diaSemana}, ${dia} de ${mes} de ${año}`;
    
    // Actualizar DOM
    document.getElementById('currentTime').textContent = timeString;
    document.getElementById('currentDate').textContent = dateString;
}

// Actualizar cada segundo
actualizarReloj();
setInterval(actualizarReloj, 1000);
</script>

<?php include 'includes/footer.php'; ?>
