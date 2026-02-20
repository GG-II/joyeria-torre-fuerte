<?php
/**
 * ================================================
 * MÓDULO SUCURSALES - EDITAR
 * ================================================
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/funciones.php';
require_once '../../includes/auth.php';

requiere_autenticacion();
requiere_rol(['administrador', 'dueño']);

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
            <h2 class="mb-1"><i class="bi bi-pencil"></i> Editar Sucursal</h2>
            <p class="text-muted mb-0">Modificar datos de la sucursal</p>
        </div>
        <a href="sucursales.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    <hr class="border-warning border-2 opacity-75 mb-4">

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Datos de la Sucursal</h5>
                </div>
                <div class="card-body">
                    <form id="formSucursal">
                        
                        <div class="mb-3">
                            <label for="nombre" class="form-label">
                                Nombre de la Sucursal <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="nombre" required>
                        </div>

                        <div class="mb-3">
                            <label for="direccion" class="form-label">
                                Dirección <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" id="direccion" rows="2" required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="telefono">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="responsable_id" class="form-label">Responsable</label>
                            <select class="form-select" id="responsable_id">
                                <option value="">Sin responsable asignado</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="activo">
                                <label class="form-check-label" for="activo">Sucursal Activa</label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="sucursales.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-warning btn-lg">
                                <i class="bi bi-check-circle"></i> Guardar Cambios
                            </button>
                        </div>

                    </form>
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
const sucursalId = <?php echo $sucursal_id; ?>;

async function cargarUsuarios() {
    try {
        const resultado = await api.listarUsuarios({ activo: 1 });
        
        if (resultado.success && resultado.data) {
            const select = document.getElementById('responsable_id');
            
            resultado.data.forEach(usuario => {
                const option = document.createElement('option');
                option.value = usuario.id;
                option.textContent = `${usuario.nombre} (${usuario.rol})`;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error al cargar usuarios:', error);
    }
}

async function cargarSucursal() {
    try {
        mostrarCargando();
        
        // Cargar usuarios primero
        await cargarUsuarios();
        
        // Usar listar y buscar por ID
        const resultadoLista = await api.listarSucursales();
        
        ocultarCargando();
        
        if (resultadoLista.success && resultadoLista.data) {
            const sucursal = resultadoLista.data.find(s => s.id == sucursalId);
            
            if (sucursal) {
                llenarFormulario(sucursal);
            } else {
                mostrarError('Sucursal no encontrada');
                setTimeout(() => window.location.href = 'sucursales.php', 2000);
            }
        } else {
            mostrarError('Error al cargar sucursal');
        }
    } catch (error) {
        ocultarCargando();
        mostrarError('Error: ' + error.message);
    }
}

function llenarFormulario(sucursal) {
    document.getElementById('nombre').value = sucursal.nombre || '';
    document.getElementById('direccion').value = sucursal.direccion || '';
    document.getElementById('telefono').value = sucursal.telefono || '';
    document.getElementById('email').value = sucursal.email || '';
    document.getElementById('responsable_id').value = sucursal.responsable_id || '';
    document.getElementById('activo').checked = sucursal.activo == 1;
}

document.getElementById('formSucursal').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const datos = {
        nombre: document.getElementById('nombre').value.trim(),
        direccion: document.getElementById('direccion').value.trim(),
        telefono: document.getElementById('telefono').value.trim() || null,
        email: document.getElementById('email').value.trim() || null,
        responsable_id: document.getElementById('responsable_id').value || null,
        activo: document.getElementById('activo').checked ? 1 : 0
    };
    
    if (!datos.nombre || !datos.direccion) {
        mostrarError('Complete los campos requeridos');
        return;
    }
    
    if (datos.email && !validarEmail(datos.email)) {
        mostrarError('Email no válido');
        return;
    }
    
    if (datos.telefono && !validarTelefono(datos.telefono)) {
        mostrarError('Teléfono debe tener 8 dígitos');
        return;
    }
    
    const confirmacion = await confirmarAccion('¿Guardar los cambios?');
    if (!confirmacion) return;
    
    try {
        mostrarCargando();
        const resultado = await api.editarSucursal(sucursalId, datos);
        ocultarCargando();
        
        if (resultado.success) {
            await mostrarExito('Sucursal actualizada exitosamente');
            window.location.href = 'sucursales.php';
        } else {
            mostrarError(resultado.message || 'Error al actualizar');
        }
    } catch (error) {
        ocultarCargando();
        mostrarError('Error: ' + error.message);
    }
});

document.getElementById('telefono').addEventListener('input', function(e) {
    let valor = e.target.value.replace(/\D/g, '');
    if (valor.length > 4) {
        valor = valor.substring(0, 4) + '-' + valor.substring(4, 8);
    }
    e.target.value = valor;
});

document.addEventListener('DOMContentLoaded', cargarSucursal);
</script>