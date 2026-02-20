<?php
/**
 * VER.PHP - Ver detalles del cliente
 * Copiar a: modules/clientes/ver.php
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();

$cliente_id = $_GET['id'] ?? null;
if (!$cliente_id) {
    header('Location: lista.php');
    exit;
}

require_once '../../includes/header.php';
require_once '../../includes/navbar.php';
?>

<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-1"><i class="bi bi-eye"></i> Detalles del Cliente</h2>
            <p class="text-muted mb-0">Información completa</p>
        </div>
        <div>
            <?php if (in_array($_SESSION['usuario_rol'], ['administrador', 'dueño', 'vendedor'])): ?>
            <a href="editar.php?id=<?php echo $cliente_id; ?>" class="btn btn-warning me-2">
                <i class="bi bi-pencil"></i> Editar
            </a>
            <?php endif; ?>
            <a href="lista.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>
    <hr class="border-warning border-2 opacity-75 mb-4">
    <div id="contenedorCliente"></div>
</div>

<?php require_once '../../includes/footer.php'; ?>

<script src="../../assets/js/vendors/sweetalert2/sweetalert2.all.min.js"></script>
<script src="../../assets/js/common.js"></script>
<script src="../../assets/js/api-client.js"></script>

<script>
const clienteId = <?php echo $cliente_id; ?>;

async function cargarCliente() {
    try {
        mostrarCargando();
        const resultadoLista = await api.listarClientes();
        ocultarCargando();
        
        if (resultadoLista.success) {
            // Extraer array de clientes
            const clientes = resultadoLista.data.clientes || resultadoLista.data || [];
            const cliente = clientes.find(c => c.id == clienteId);
            
            if (cliente) {
                mostrarDatos(cliente);
            } else {
                mostrarError('Cliente no encontrado');
                setTimeout(() => window.location.href = 'lista.php', 2000);
            }
        }
    } catch (error) {
        ocultarCargando();
        mostrarError('Error: ' + error.message);
    }
}

function mostrarDatos(cliente) {
    const badgeEstado = cliente.activo == 1 
        ? '<span class="badge bg-success fs-6">Activo</span>'
        : '<span class="badge bg-secondary fs-6">Inactivo</span>';
    
    const badgeTipo = cliente.tipo_cliente === 'mayorista'
        ? '<span class="badge bg-warning text-dark fs-6">Mayorista</span>'
        : '<span class="badge bg-info fs-6">Público</span>';
    
    const html = `
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-info-circle"></i> Información General</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Nombre:</strong></div>
                            <div class="col-md-9"><h4 class="mb-0 text-primary">${escaparHTML(cliente.nombre)}</h4></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>NIT:</strong></div>
                            <div class="col-md-9">${escaparHTML(cliente.nit || 'C/F')}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Teléfono:</strong></div>
                            <div class="col-md-9">${cliente.telefono ? `<a href="tel:${cliente.telefono}"><i class="bi bi-telephone"></i> ${escaparHTML(cliente.telefono)}</a>` : '-'}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Email:</strong></div>
                            <div class="col-md-9">${cliente.email ? `<a href="mailto:${cliente.email}"><i class="bi bi-envelope"></i> ${escaparHTML(cliente.email)}</a>` : '-'}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Dirección:</strong></div>
                            <div class="col-md-9">${escaparHTML(cliente.direccion || '-')}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Tipo:</strong></div>
                            <div class="col-md-9">${badgeTipo}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Mercadería:</strong></div>
                            <div class="col-md-9">${escaparHTML(cliente.tipo_mercaderias || '-')}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Límite Crédito:</strong></div>
                            <div class="col-md-9">${cliente.limite_credito ? formatearMoneda(cliente.limite_credito) : 'Sin límite'}</div>
                        </div>
                        ${cliente.plazo_credito_dias ? `
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Plazo:</strong></div>
                            <div class="col-md-9">${cliente.plazo_credito_dias} días</div>
                        </div>
                        ` : ''}
                        <div class="row">
                            <div class="col-md-3"><strong>Estado:</strong></div>
                            <div class="col-md-9">${badgeEstado}</div>
                        </div>
                    </div>
                </div>
                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="bi bi-calendar"></i> Registro</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-md-3"><strong>Creado:</strong></div>
                            <div class="col-md-9"><small class="text-muted">${formatearFechaHora(cliente.fecha_creacion)}</small></div>
                        </div>
                        ${cliente.fecha_actualizacion ? `
                        <div class="row">
                            <div class="col-md-3"><strong>Actualizado:</strong></div>
                            <div class="col-md-9"><small class="text-muted">${formatearFechaHora(cliente.fecha_actualizacion)}</small></div>
                        </div>
                        ` : ''}
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('contenedorCliente').innerHTML = html;
}

document.addEventListener('DOMContentLoaded', cargarCliente);
</script>