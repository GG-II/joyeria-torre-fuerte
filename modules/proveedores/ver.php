<?php
/**
 * ================================================
 * MÓDULO PROVEEDORES - VER DETALLES
 * ================================================
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();

// Obtener ID del proveedor
$proveedor_id = $_GET['id'] ?? null;

if (!$proveedor_id) {
    header('Location: lista.php');
    exit;
}

require_once '../../includes/header.php';
require_once '../../includes/navbar.php';
?>

<div class="container-fluid px-4 py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-1"><i class="bi bi-eye"></i> Detalles del Proveedor</h2>
            <p class="text-muted mb-0">Información completa del proveedor</p>
        </div>
        <div>
            <?php if (in_array($_SESSION['usuario_rol'], ['administrador', 'dueño'])): ?>
            <a href="editar.php?id=<?php echo $proveedor_id; ?>" class="btn btn-warning me-2">
                <i class="bi bi-pencil"></i> Editar
            </a>
            <?php endif; ?>
            <a href="lista.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <hr class="border-warning border-2 opacity-75 mb-4">

    <!-- Contenedor de datos -->
    <div id="contenedorProveedor">
        <!-- Se carga con JavaScript -->
    </div>

</div>

<?php require_once '../../includes/footer.php'; ?>

<script src="../../assets/js/vendors/sweetalert2/sweetalert2.all.min.js"></script>
<script src="../../assets/js/common.js"></script>
<script src="../../assets/js/api-client.js"></script>

<script>
const proveedorId = <?php echo $proveedor_id; ?>;

async function cargarProveedor() {
    try {
        mostrarCargando();
        
        const resultado = await api.listarProveedores();
        
        ocultarCargando();
        
        if (resultado.success) {
            const proveedor = resultado.data.find(p => p.id == proveedorId);
            
            if (!proveedor) {
                mostrarError('Proveedor no encontrado');
                setTimeout(() => window.location.href = 'lista.php', 2000);
                return;
            }
            
            mostrarDatos(proveedor);
        } else {
            mostrarError('Error al cargar proveedor');
        }
        
    } catch (error) {
        ocultarCargando();
        console.error('Error:', error);
        mostrarError('Error al cargar datos: ' + error.message);
    }
}

function mostrarDatos(proveedor) {
    const badgeEstado = proveedor.activo == 1 
        ? '<span class="badge bg-success fs-6">Activo</span>'
        : '<span class="badge bg-secondary fs-6">Inactivo</span>';
    
    const html = `
        <div class="row">
            <!-- Información Principal -->
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-info-circle"></i> Información General</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong class="text-muted">Nombre:</strong>
                            </div>
                            <div class="col-md-8">
                                <h5 class="mb-0">${escaparHTML(proveedor.nombre)}</h5>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong class="text-muted">Empresa:</strong>
                            </div>
                            <div class="col-md-8">
                                ${escaparHTML(proveedor.empresa || '-')}
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong class="text-muted">Contacto:</strong>
                            </div>
                            <div class="col-md-8">
                                ${escaparHTML(proveedor.contacto || '-')}
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong class="text-muted">Teléfono:</strong>
                            </div>
                            <div class="col-md-8">
                                <a href="tel:${proveedor.telefono}" class="text-decoration-none">
                                    <i class="bi bi-telephone"></i> ${escaparHTML(proveedor.telefono || '-')}
                                </a>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong class="text-muted">Email:</strong>
                            </div>
                            <div class="col-md-8">
                                ${proveedor.email 
                                    ? `<a href="mailto:${proveedor.email}" class="text-decoration-none">
                                         <i class="bi bi-envelope"></i> ${escaparHTML(proveedor.email)}
                                       </a>`
                                    : '-'}
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong class="text-muted">Dirección:</strong>
                            </div>
                            <div class="col-md-8">
                                ${escaparHTML(proveedor.direccion || '-')}
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <strong class="text-muted">Estado:</strong>
                            </div>
                            <div class="col-md-8">
                                ${badgeEstado}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notas -->
                ${proveedor.notas ? `
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-sticky"></i> Notas</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">${escaparHTML(proveedor.notas)}</p>
                    </div>
                </div>
                ` : ''}
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Productos Suministra -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="bi bi-box-seam"></i> Productos</h5>
                    </div>
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Suministra:</h6>
                        ${mostrarProductos(proveedor.productos_suministra)}
                    </div>
                </div>

                <!-- Fechas -->
                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="bi bi-calendar"></i> Registro</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-2">
                            <strong>Creado:</strong><br>
                            <small class="text-muted">
                                ${formatearFechaHora(proveedor.fecha_creacion)}
                            </small>
                        </p>
                        ${proveedor.fecha_actualizacion ? `
                        <p class="mb-0">
                            <strong>Actualizado:</strong><br>
                            <small class="text-muted">
                                ${formatearFechaHora(proveedor.fecha_actualizacion)}
                            </small>
                        </p>
                        ` : ''}
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('contenedorProveedor').innerHTML = html;
}

function mostrarProductos(productos) {
    if (!productos) return '<p class="text-muted mb-0">-</p>';
    
    const lista = productos.split(',').map(p => p.trim());
    
    return lista.map(producto => 
        `<span class="badge bg-light text-dark border me-1 mb-1">${escaparHTML(producto)}</span>`
    ).join('');
}

document.addEventListener('DOMContentLoaded', cargarProveedor);
</script>