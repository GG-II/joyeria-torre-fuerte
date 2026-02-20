<?php
/**
 * ================================================
 * MÓDULO USUARIOS - VER DETALLES
 * ================================================
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();

$usuario_id = $_GET['id'] ?? null;

if (!$usuario_id) {
    header('Location: usuarios.php');
    exit;
}

require_once '../../includes/header.php';
require_once '../../includes/navbar.php';
?>

<div class="container-fluid px-4 py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-1"><i class="bi bi-eye"></i> Detalles del Usuario</h2>
            <p class="text-muted mb-0">Información completa del usuario</p>
        </div>
        <div>
            <?php if (in_array($_SESSION['usuario_rol'], ['administrador', 'dueño'])): ?>
            <a href="editar-usuario.php?id=<?php echo $usuario_id; ?>" class="btn btn-warning me-2">
                <i class="bi bi-pencil"></i> Editar
            </a>
            <?php endif; ?>
            <a href="usuarios.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <hr class="border-warning border-2 opacity-75 mb-4">

    <div id="contenedorUsuario">
        <!-- Datos dinámicos -->
    </div>

</div>

<?php require_once '../../includes/footer.php'; ?>

<script src="../../assets/js/vendors/sweetalert2/sweetalert2.all.min.js"></script>
<script src="../../assets/js/common.js"></script>
<script src="../../assets/js/api-client.js"></script>

<script>
const usuarioId = <?php echo $usuario_id; ?>;

async function cargarUsuario() {
    try {
        mostrarCargando();
        
        const resultadoLista = await api.listarUsuarios();
        
        ocultarCargando();
        
        if (resultadoLista.success && resultadoLista.data) {
            const usuario = resultadoLista.data.find(u => u.id == usuarioId);
            
            if (usuario) {
                mostrarDatos(usuario);
            } else {
                mostrarError('Usuario no encontrado');
                setTimeout(() => window.location.href = 'usuarios.php', 2000);
            }
        }
        
    } catch (error) {
        ocultarCargando();
        console.error('Error:', error);
        mostrarError('Error: ' + error.message);
    }
}

function obtenerBadgeRol(rol) {
    const badges = {
        'administrador': '<span class="badge bg-danger fs-6">Administrador</span>',
        'dueño': '<span class="badge bg-warning text-dark fs-6">Dueño</span>',
        'vendedor': '<span class="badge bg-primary fs-6">Vendedor</span>',
        'cajero': '<span class="badge bg-success fs-6">Cajero</span>',
        'orfebre': '<span class="badge bg-info fs-6">Orfebre</span>',
        'publicidad': '<span class="badge bg-secondary fs-6">Publicidad</span>'
    };
    
    return badges[rol] || `<span class="badge bg-light text-dark fs-6">${escaparHTML(rol)}</span>`;
}

function mostrarDatos(usuario) {
    const badgeEstado = usuario.activo == 1 
        ? '<span class="badge bg-success fs-6">Activo</span>'
        : '<span class="badge bg-secondary fs-6">Inactivo</span>';
    
    const html = `
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-info-circle"></i> Información General</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <strong class="text-muted">Nombre:</strong>
                            </div>
                            <div class="col-md-9">
                                <h4 class="mb-0 text-primary">${escaparHTML(usuario.nombre)}</h4>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <strong class="text-muted">Email:</strong>
                            </div>
                            <div class="col-md-9">
                                <a href="mailto:${usuario.email}">
                                    <i class="bi bi-envelope"></i> ${escaparHTML(usuario.email)}
                                </a>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <strong class="text-muted">Rol:</strong>
                            </div>
                            <div class="col-md-9">
                                ${obtenerBadgeRol(usuario.rol)}
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <strong class="text-muted">Sucursal:</strong>
                            </div>
                            <div class="col-md-9">
                                ${usuario.sucursal_nombre 
                                    ? escaparHTML(usuario.sucursal_nombre)
                                    : '<em class="text-muted">Sin sucursal asignada</em>'}
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-3">
                                <strong class="text-muted">Estado:</strong>
                            </div>
                            <div class="col-md-9">
                                ${badgeEstado}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="bi bi-calendar"></i> Información de Registro</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-md-3">
                                <strong>Creado:</strong>
                            </div>
                            <div class="col-md-9">
                                <small class="text-muted">
                                    ${formatearFechaHora(usuario.fecha_creacion)}
                                </small>
                            </div>
                        </div>
                        
                        ${usuario.ultimo_acceso ? `
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Último Acceso:</strong>
                            </div>
                            <div class="col-md-9">
                                <small class="text-muted">
                                    ${formatearFechaHora(usuario.ultimo_acceso)}
                                </small>
                            </div>
                        </div>
                        ` : ''}
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('contenedorUsuario').innerHTML = html;
}

document.addEventListener('DOMContentLoaded', cargarUsuario);
</script>