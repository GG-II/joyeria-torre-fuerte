<?php
/**
 * ================================================
 * MI PERFIL
 * ================================================
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();

// Cargar datos del usuario desde la BD directamente
$usuario_id = $_SESSION['usuario_id'];
$stmt = $pdo->prepare("
    SELECT u.*, s.nombre as sucursal_nombre 
    FROM usuarios u 
    LEFT JOIN sucursales s ON u.sucursal_id = s.id 
    WHERE u.id = ?
");
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

$titulo_pagina = 'Mi Perfil';
require_once '../../includes/header.php';
require_once '../../includes/navbar.php';
?>

<div class="container-fluid px-4 py-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-1"><i class="bi bi-person-circle"></i> Mi Perfil</h2>
            <p class="text-muted mb-0">Información personal y configuración de cuenta</p>
        </div>
        <a href="../../dashboard.php" class="btn btn-outline-warning">
            <i class="bi bi-arrow-left"></i> Volver al Dashboard
        </a>
    </div>

    <hr class="border-warning border-2 opacity-75 mb-4">

    <div class="row g-4">

        <!-- Columna izquierda -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-body text-center py-4">
                    <div class="mb-3">
                        <div style="width:100px;height:100px;border-radius:50%;background:#1e3a8a;display:flex;align-items:center;justify-content:center;margin:0 auto;border:3px solid #D4AF37;">
                            <i class="bi bi-person-fill text-white" style="font-size:50px;"></i>
                        </div>
                    </div>
                    <h5 class="mb-1"><?php echo htmlspecialchars($usuario['nombre']); ?></h5>
                    <span class="badge bg-warning text-dark mb-3"><?php echo htmlspecialchars($usuario['rol']); ?></span>

                    <div class="text-start mt-3">
                        <div class="card bg-light border-0">
                            <div class="card-body">
                                <h6 class="text-primary mb-3"><i class="bi bi-info-circle"></i> Información de la Cuenta</h6>
                                <div class="mb-2">
                                    <small class="text-muted d-block">Sucursal</small>
                                    <span><?php echo htmlspecialchars($usuario['sucursal_nombre'] ?? '-'); ?></span>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted d-block">Miembro desde</small>
                                    <span><?php echo $usuario['fecha_creacion'] ? date('d/m/Y', strtotime($usuario['fecha_creacion'])) : '-'; ?></span>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted d-block">Último acceso</small>
                                    <span><?php echo $usuario['ultimo_acceso'] ? date('d/m/Y H:i', strtotime($usuario['ultimo_acceso'])) : '-'; ?></span>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Estado</small>
                                    <span class="badge bg-success">Activo</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna derecha -->
        <div class="col-lg-8">

            <!-- Editar información personal -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-pencil-square"></i> Editar Información Personal</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre Completo *</label>
                            <input type="text" class="form-control" id="inputNombre" 
                                   value="<?php echo htmlspecialchars($usuario['nombre']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" id="inputEmail" 
                                   value="<?php echo htmlspecialchars($usuario['email']); ?>">
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button type="button" class="btn btn-outline-secondary" onclick="cancelarEdicion()">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                        <button type="button" class="btn btn-warning" onclick="guardarPerfil()">
                            <i class="bi bi-floppy"></i> Guardar Cambios
                        </button>
                    </div>
                </div>
            </div>

            <!-- Cambiar contraseña -->
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-key"></i> Cambiar Contraseña</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Contraseña Actual *</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="inputPasswordActual" placeholder="Tu contraseña actual">
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePass('inputPasswordActual', 'icon1')">
                                    <i class="bi bi-eye" id="icon1"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nueva Contraseña *</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="inputPasswordNueva" placeholder="Mínimo 6 caracteres">
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePass('inputPasswordNueva', 'icon2')">
                                    <i class="bi bi-eye" id="icon2"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirmar Contraseña *</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="inputPasswordConfirm" placeholder="Repite la nueva contraseña">
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePass('inputPasswordConfirm', 'icon3')">
                                    <i class="bi bi-eye" id="icon3"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-3">
                        <button type="button" class="btn btn-warning" onclick="cambiarPassword()">
                            <i class="bi bi-shield-lock"></i> Cambiar Contraseña
                        </button>
                    </div>
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
const usuarioId = <?php echo $usuario['id']; ?>;
const nombreOriginal = '<?php echo addslashes($usuario['nombre']); ?>';
const emailOriginal = '<?php echo addslashes($usuario['email']); ?>';

function cancelarEdicion() {
    document.getElementById('inputNombre').value = nombreOriginal;
    document.getElementById('inputEmail').value = emailOriginal;
}

async function guardarPerfil() {
    const nombre = document.getElementById('inputNombre').value.trim();
    const email = document.getElementById('inputEmail').value.trim();

    if (!nombre || !email) {
        mostrarError('El nombre y email son requeridos');
        return;
    }

    try {
        mostrarCargando();

        const resultado = await api.editarUsuario(usuarioId, {
            nombre: nombre,
            email: email,
            rol: '<?php echo $usuario['rol']; ?>',
            sucursal_id: <?php echo $usuario['sucursal_id'] ?? 'null'; ?>,
            activo: 1
        });

        ocultarCargando();
        mostrarExito('Perfil actualizado correctamente');

    } catch (error) {
        ocultarCargando();
        mostrarError('Error: ' + error.message);
    }
}

async function cambiarPassword() {
    const actual = document.getElementById('inputPasswordActual').value;
    const nueva = document.getElementById('inputPasswordNueva').value;
    const confirmar = document.getElementById('inputPasswordConfirm').value;

    if (!actual || !nueva || !confirmar) {
        mostrarError('Todos los campos de contraseña son requeridos');
        return;
    }

    if (nueva !== confirmar) {
        mostrarError('Las contraseñas nuevas no coinciden');
        return;
    }

    if (nueva.length < 6) {
        mostrarError('La nueva contraseña debe tener al menos 6 caracteres');
        return;
    }

    try {
        mostrarCargando();

        const resultado = await api.cambiarPasswordUsuario(usuarioId, actual, nueva, confirmar);

        ocultarCargando();
        mostrarExito('Contraseña cambiada correctamente');
        document.getElementById('inputPasswordActual').value = '';
        document.getElementById('inputPasswordNueva').value = '';
        document.getElementById('inputPasswordConfirm').value = '';

    } catch (error) {
        ocultarCargando();
        mostrarError('Error: ' + error.message);
    }
}

function togglePass(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}

console.log('✅ Perfil cargado correctamente');
</script>