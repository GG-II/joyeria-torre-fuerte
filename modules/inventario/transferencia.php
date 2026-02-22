<?php
/**
 * ================================================
 * MÓDULO INVENTARIO - TRANSFERIR PRODUCTOS
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
            <h2 class="mb-1"><i class="bi bi-arrow-left-right"></i> Transferir Productos</h2>
            <p class="text-muted mb-0">Mover inventario entre sucursales</p>
        </div>
        <a href="lista.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    <hr class="border-warning border-2 opacity-75 mb-4">

    <div class="row">
        <!-- Formulario de Transferencia -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-box-arrow-right"></i> Nueva Transferencia</h5>
                </div>
                <div class="card-body">
                    <form id="formTransferencia">
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="sucursal_origen" class="form-label">
                                    Sucursal Origen <span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-lg" id="sucursal_origen" required>
                                    <option value="">Seleccione...</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="sucursal_destino" class="form-label">
                                    Sucursal Destino <span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-lg" id="sucursal_destino" required>
                                    <option value="">Seleccione...</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="producto_id" class="form-label">
                                Producto <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="producto_id" required disabled>
                                <option value="">Seleccione primero la sucursal origen</option>
                            </select>
                            <small class="text-muted">Solo productos con stock disponible</small>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Stock Disponible</label>
                                <h3 id="stockDisponible" class="text-primary">-</h3>
                            </div>

                            <div class="col-md-4">
                                <label for="cantidad" class="form-label">
                                    Cantidad a Transferir <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control form-control-lg" 
                                       id="cantidad" min="1" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Quedará en Origen</label>
                                <h3 id="stockResultante" class="text-info">-</h3>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="motivo" class="form-label">
                                Motivo de la Transferencia <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" id="motivo" rows="2" 
                                      placeholder="Ej: Reposición de stock, solicitud de sucursal" required></textarea>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Nota:</strong> La transferencia registrará una salida en la sucursal origen 
                            y una entrada en la sucursal destino.
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle"></i> Realizar Transferencia
                        </button>

                    </form>
                </div>
            </div>
        </div>

        <!-- Transferencias Recientes -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Transferencias Recientes</h5>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    <div id="transferenciasRecientes">
                        <p class="text-center text-muted">Cargando...</p>
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
let sucursales = [];
let productosOrigen = [];
let stockActual = 0;

async function cargarDatos() {
    try {
        mostrarCargando();
        
        // Cargar sucursales
        const resSuc = await api.listarSucursales({ activo: 1 });
        if (resSuc.success) {
            sucursales = Array.isArray(resSuc.data) ? resSuc.data : [];
            
            const selectOrigen = document.getElementById('sucursal_origen');
            const selectDestino = document.getElementById('sucursal_destino');
            
            sucursales.forEach(suc => {
                const optionOrigen = document.createElement('option');
                optionOrigen.value = suc.id;
                optionOrigen.textContent = suc.nombre;
                selectOrigen.appendChild(optionOrigen);
                
                const optionDestino = document.createElement('option');
                optionDestino.value = suc.id;
                optionDestino.textContent = suc.nombre;
                selectDestino.appendChild(optionDestino);
            });
        }
        
        await cargarTransferenciasRecientes();
        
        ocultarCargando();
        
    } catch (error) {
        ocultarCargando();
        console.error('Error:', error);
        mostrarError('Error al cargar datos');
    }
}

async function cargarProductosOrigen(sucursalId) {
    try {
        mostrarCargando();
        
        const params = new URLSearchParams({
            sucursal_id: sucursalId,
            por_pagina: 500
        });
        
        const res = await fetch(`/api/inventario/listar.php?${params}`);
        const data = await res.json();
        
        ocultarCargando();
        
        if (!data.success || !data.data.inventario) {
            productosOrigen = [];
            return;
        }
        
        // Filtrar solo productos con stock > 0
        productosOrigen = data.data.inventario.filter(inv => (parseInt(inv.cantidad) || 0) > 0);
        
        const select = document.getElementById('producto_id');
        select.innerHTML = '<option value="">Seleccione un producto</option>';
        
        if (productosOrigen.length === 0) {
            select.innerHTML = '<option value="">No hay productos con stock</option>';
            select.disabled = true;
            return;
        }
        
        select.disabled = false;
        
        productosOrigen.forEach(prod => {
            const option = document.createElement('option');
            option.value = prod.producto_id;
            option.dataset.cantidad = prod.cantidad;
            option.textContent = `${prod.codigo} - ${prod.nombre} (Stock: ${prod.cantidad})`;
            select.appendChild(option);
        });
        
    } catch (error) {
        ocultarCargando();
        console.error('Error:', error);
    }
}

async function cargarTransferenciasRecientes() {
    const container = document.getElementById('transferenciasRecientes');
    
    try {
        const params = new URLSearchParams({
            tipo_movimiento: 'transferencia',
            limit: 10
        });
        
        const res = await fetch(`/api/movimientos_inventario/listar.php?${params}`);
        
        if (!res.ok) {
            throw new Error('Error HTTP: ' + res.status);
        }
        
        const data = await res.json();
        
        if (!data.success || !data.data || data.data.length === 0) {
            container.innerHTML = '<p class="text-center text-muted">No hay transferencias recientes</p>';
            return;
        }
        
        let html = '';
        data.data.forEach(trans => {
            const fecha = new Date(trans.fecha_hora);
            const fechaFormat = fecha.toLocaleDateString('es-GT', { 
                day: '2-digit', 
                month: 'short', 
                hour: '2-digit', 
                minute: '2-digit' 
            });
            
            html += `
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <small class="text-muted">${fechaFormat}</small>
                        <span class="badge bg-primary">${trans.cantidad}</span>
                    </div>
                    <strong class="d-block mb-1">${escaparHTML(trans.producto_nombre || 'Producto')}</strong>
                    <small class="text-muted d-block">
                        ${escaparHTML(trans.sucursal_nombre)}
                    </small>
                    ${trans.motivo ? `<small class="text-muted d-block mt-1"><i class="bi bi-chat-left-text"></i> ${escaparHTML(trans.motivo)}</small>` : ''}
                </div>
            `;
        });
        
        container.innerHTML = html;
        
    } catch (error) {
        console.error('Error al cargar transferencias:', error);
        container.innerHTML = '<p class="text-center text-muted">No hay transferencias disponibles</p>';
    }
}

// Cuando cambia sucursal origen, cargar productos
document.getElementById('sucursal_origen').addEventListener('change', function() {
    const sucId = this.value;
    
    // Limpiar selección
    document.getElementById('producto_id').value = '';
    document.getElementById('stockDisponible').textContent = '-';
    document.getElementById('stockResultante').textContent = '-';
    document.getElementById('cantidad').value = '';
    
    if (!sucId) {
        document.getElementById('producto_id').disabled = true;
        document.getElementById('producto_id').innerHTML = '<option value="">Seleccione primero la sucursal origen</option>';
        return;
    }
    
    cargarProductosOrigen(sucId);
});

// Validar que origen y destino sean diferentes
document.getElementById('sucursal_destino').addEventListener('change', function() {
    const origen = document.getElementById('sucursal_origen').value;
    const destino = this.value;
    
    if (origen && destino && origen === destino) {
        mostrarError('La sucursal destino debe ser diferente a la sucursal origen');
        this.value = '';
    }
});

// Actualizar stock disponible al seleccionar producto
document.getElementById('producto_id').addEventListener('change', function() {
    const option = this.options[this.selectedIndex];
    
    if (!option || !option.dataset.cantidad) {
        stockActual = 0;
        document.getElementById('stockDisponible').textContent = '-';
        document.getElementById('cantidad').max = 0;
        return;
    }
    
    stockActual = parseInt(option.dataset.cantidad) || 0;
    document.getElementById('stockDisponible').textContent = stockActual;
    document.getElementById('cantidad').max = stockActual;
    document.getElementById('cantidad').value = '';
    document.getElementById('stockResultante').textContent = '-';
});

// Calcular stock resultante
document.getElementById('cantidad').addEventListener('input', function() {
    const cantidad = parseInt(this.value) || 0;
    
    if (cantidad > stockActual) {
        this.value = stockActual;
        mostrarAdvertencia(`La cantidad máxima disponible es ${stockActual}`);
        return;
    }
    
    const resultante = stockActual - cantidad;
    document.getElementById('stockResultante').textContent = resultante;
});

// Realizar transferencia
document.getElementById('formTransferencia').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const sucursalOrigen = document.getElementById('sucursal_origen').value;
    const sucursalDestino = document.getElementById('sucursal_destino').value;
    const productoId = document.getElementById('producto_id').value;
    const cantidad = parseInt(document.getElementById('cantidad').value);
    const motivo = document.getElementById('motivo').value.trim();
    
    if (!sucursalOrigen || !sucursalDestino || !productoId || !cantidad || !motivo) {
        mostrarError('Complete todos los campos');
        return;
    }
    
    if (sucursalOrigen === sucursalDestino) {
        mostrarError('Las sucursales deben ser diferentes');
        return;
    }
    
    if (cantidad > stockActual) {
        mostrarError(`Stock insuficiente. Disponible: ${stockActual}`);
        return;
    }
    
    const confirmacion = await confirmarAccion(
        `¿Transferir ${cantidad} unidad(es)?`,
        `Se moverá desde ${sucursales.find(s => s.id == sucursalOrigen)?.nombre} hacia ${sucursales.find(s => s.id == sucursalDestino)?.nombre}`
    );
    
    if (!confirmacion) return;
    
    try {
        mostrarCargando();
        
        const formData = new URLSearchParams();
        formData.append('producto_id', productoId);
        formData.append('sucursal_origen', sucursalOrigen);
        formData.append('sucursal_destino', sucursalDestino);
        formData.append('cantidad', cantidad);
        formData.append('motivo', motivo);
        
        const res = await fetch('/api/inventario/transferir.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData.toString()
        });
        
        const resultado = await res.json();
        
        ocultarCargando();
        
        if (resultado.success) {
            await mostrarExito('Transferencia realizada exitosamente');
            
            // Limpiar formulario
            document.getElementById('formTransferencia').reset();
            document.getElementById('stockDisponible').textContent = '-';
            document.getElementById('stockResultante').textContent = '-';
            document.getElementById('producto_id').disabled = true;
            document.getElementById('producto_id').innerHTML = '<option value="">Seleccione primero la sucursal origen</option>';
            
            // Recargar transferencias recientes
            await cargarTransferenciasRecientes();
            
        } else {
            mostrarError(resultado.message || resultado.error || 'Error al realizar transferencia');
        }
        
    } catch (error) {
        ocultarCargando();
        console.error('Error:', error);
        mostrarError('Error: ' + error.message);
    }
});

document.addEventListener('DOMContentLoaded', cargarDatos);
</script>