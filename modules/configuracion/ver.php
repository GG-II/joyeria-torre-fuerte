<?php
/**
 * ================================================
 * MÓDULO SUCURSALES - VER DETALLES
 * ================================================
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();

$sucursal_id = $_GET['id'] ?? null;

if (!$sucursal_id) {
    header('Location: sucursales.php');
    exit;
}

require_once '../../includes/header.php';
require_once '../../includes/navbar.php';
?>

<div class="container-fluid px-4 py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-1"><i class="bi bi-eye"></i> Detalles de la Sucursal</h2>
            <p class="text-muted mb-0">Información completa de la sucursal</p>
        </div>
        <div>
            <?php if (in_array($_SESSION['usuario_rol'], ['administrador', 'dueño'])): ?>
            <a href="editar.php?id=<?php echo $sucursal_id; ?>" class="btn btn-warning me-2">
                <i class="bi bi-pencil"></i> Editar
            </a>
            <?php endif; ?>
            <a href="sucursales.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <hr class="border-warning border-2 opacity-75 mb-4">

    <div id="contenedorSucursal">
        <!-- Datos dinámicos -->
    </div>

</div>

<?php require_once '../../includes/footer.php'; ?>

<script src="../../assets/js/vendors/sweetalert2/sweetalert2.all.min.js"></script>
<script src="../../assets/js/common.js"></script>
<script src="../../assets/js/api-client.js"></script>

<script>
const sucursalId = <?php echo $sucursal_id; ?>;

async function cargarSucursal() {
    try {
        mostrarCargando();
        
        // Usar listar y buscar por ID
        const resultadoLista = await api.listarSucursales();
        
        ocultarCargando();
        
        if (resultadoLista.success && resultadoLista.data) {
            const sucursal = resultadoLista.data.find(s => s.id == sucursalId);
            
            if (sucursal) {
                mostrarDatos(sucursal);
            } else {
                mostrarError('Sucursal no encontrada');
                setTimeout(() => window.location.href = 'sucursales.php', 2000);
            }
        } else {
            mostrarError('Error al cargar sucursal');
        }
        
    } catch (error) {
        ocultarCargando();
        console.error('Error:', error);
        mostrarError('Error: ' + error.message);
    }
}

function mostrarDatos(sucursal) {
    const badgeEstado = sucursal.activo == 1 
        ? '<span class="badge bg-success fs-6">Activa</span>'
        : '<span class="badge bg-secondary fs-6">Inactiva</span>';
    
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
                                <h4 class="mb-0 text-primary">${escaparHTML(sucursal.nombre)}</h4>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <strong class="text-muted">Dirección:</strong>
                            </div>
                            <div class="col-md-9">
                                ${escaparHTML(sucursal.direccion || '-')}
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <strong class="text-muted">Teléfono:</strong>
                            </div>
                            <div class="col-md-9">
                                ${sucursal.telefono 
                                    ? `<a href="tel:${sucursal.telefono}">
                                         <i class="bi bi-telephone"></i> ${escaparHTML(sucursal.telefono)}
                                       </a>`
                                    : '-'}
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <strong class="text-muted">Email:</strong>
                            </div>
                            <div class="col-md-9">
                                ${sucursal.email 
                                    ? `<a href="mailto:${sucursal.email}">
                                         <i class="bi bi-envelope"></i> ${escaparHTML(sucursal.email)}
                                       </a>`
                                    : '-'}
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <strong class="text-muted">Responsable:</strong>
                            </div>
                            <div class="col-md-9">
                                ${sucursal.responsable_nombre 
                                    ? escaparHTML(sucursal.responsable_nombre)
                                    : '<em class="text-muted">Sin responsable asignado</em>'}
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

                <!-- Fechas -->
                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="bi bi-calendar"></i> Información de Registro</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-md-3">
                                <strong>Creada:</strong>
                            </div>
                            <div class="col-md-9">
                                <small class="text-muted">
                                    ${formatearFechaHora(sucursal.fecha_creacion)}
                                </small>
                            </div>
                        </div>
                        
                        ${sucursal.fecha_actualizacion ? `
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Actualizada:</strong>
                            </div>
                            <div class="col-md-9">
                                <small class="text-muted">
                                    ${formatearFechaHora(sucursal.fecha_actualizacion)}
                                </small>
                            </div>
                        </div>
                        ` : ''}
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('contenedorSucursal').innerHTML = html;
}

document.addEventListener('DOMContentLoaded', cargarSucursal);
</script>