<?php
/**
 * ================================================
 * MÓDULO CATEGORÍAS - VER DETALLES
 * ================================================
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();

$categoria_id = $_GET['id'] ?? null;

if (!$categoria_id) {
    header('Location: lista.php');
    exit;
}

require_once '../../includes/header.php';
require_once '../../includes/navbar.php';
?>

<div class="container-fluid px-4 py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-1"><i class="bi bi-eye"></i> Detalles de la Categoría</h2>
            <p class="text-muted mb-0">Información completa de la categoría</p>
        </div>
        <div>
            <?php if (in_array($_SESSION['usuario_rol'], ['administrador', 'dueño'])): ?>
            <a href="editar.php?id=<?php echo $categoria_id; ?>" class="btn btn-warning me-2">
                <i class="bi bi-pencil"></i> Editar
            </a>
            <?php endif; ?>
            <a href="lista.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <hr class="border-warning border-2 opacity-75 mb-4">

    <div id="contenedorCategoria">
        <!-- Datos dinámicos -->
    </div>

</div>

<?php require_once '../../includes/footer.php'; ?>

<script src="../../assets/js/vendors/sweetalert2/sweetalert2.all.min.js"></script>
<script src="../../assets/js/common.js"></script>
<script src="../../assets/js/api-client.js"></script>

<script>
const categoriaId = <?php echo $categoria_id; ?>;

async function cargarCategoria() {
    try {
        mostrarCargando();
        
        const resultado = await api.listarCategorias();
        
        ocultarCargando();
        
        if (resultado.success) {
            const categoria = resultado.data.find(c => c.id == categoriaId);
            
            if (!categoria) {
                mostrarError('Categoría no encontrada');
                setTimeout(() => window.location.href = 'lista.php', 2000);
                return;
            }
            
            mostrarDatos(categoria);
        }
        
    } catch (error) {
        ocultarCargando();
        console.error('Error:', error);
        mostrarError('Error: ' + error.message);
    }
}

function mostrarDatos(categoria) {
    const badgeEstado = categoria.activo == 1 
        ? '<span class="badge bg-success fs-6">Activa</span>'
        : '<span class="badge bg-secondary fs-6">Inactiva</span>';
    
    const badgeTipo = categoria.tipo_clasificacion === 'tipo'
        ? '<span class="badge bg-primary fs-6">Tipo de Producto</span>'
        : '<span class="badge bg-warning text-dark fs-6">Material</span>';
    
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
                                <h4 class="mb-0 text-primary">${escaparHTML(categoria.nombre)}</h4>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <strong class="text-muted">Tipo:</strong>
                            </div>
                            <div class="col-md-9">
                                ${badgeTipo}
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <strong class="text-muted">Descripción:</strong>
                            </div>
                            <div class="col-md-9">
                                ${categoria.descripcion 
                                    ? escaparHTML(categoria.descripcion)
                                    : '<em class="text-muted">Sin descripción</em>'}
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
                                    ${formatearFechaHora(categoria.fecha_creacion)}
                                </small>
                            </div>
                        </div>
                        
                        ${categoria.fecha_actualizacion ? `
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Actualizada:</strong>
                            </div>
                            <div class="col-md-9">
                                <small class="text-muted">
                                    ${formatearFechaHora(categoria.fecha_actualizacion)}
                                </small>
                            </div>
                        </div>
                        ` : ''}
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('contenedorCategoria').innerHTML = html;
}

document.addEventListener('DOMContentLoaded', cargarCategoria);
</script>