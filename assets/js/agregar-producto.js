/**
 * Script simple para agregar producto
 * SIN módulos complicados
 */

// Inicializar al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Página cargada');
    cargarCategorias();
    
    // Event listeners para vista previa
    document.getElementById('stock_los_arcos').addEventListener('input', calcularStockTotal);
    document.getElementById('stock_chinaca').addEventListener('input', calcularStockTotal);
    document.getElementById('precio_mayorista').addEventListener('input', actualizarPrecioMayorista);
    
    // Formulario
    document.getElementById('formProducto').addEventListener('submit', enviarFormulario);
});

/**
 * Cargar categorías (SIMPLE)
 */
function cargarCategorias() {
    console.log('📁 Cargando categorías...');
    
    const categorias = [
        'Anillos', 'Aretes', 'Collares', 'Pulseras', 
        'Cadenas', 'Dijes', 'Relojes', 'Otros'
    ];
    
    const select = document.getElementById('categoria_id');
    if (!select) {
        console.error('❌ Select no encontrado');
        return;
    }
    
    categorias.forEach((cat, index) => {
        const option = document.createElement('option');
        option.value = index + 1;
        option.textContent = cat;
        select.appendChild(option);
    });
    
    console.log(`✅ ${categorias.length} categorías cargadas`);
}

/**
 * Calcular stock total
 */
function calcularStockTotal() {
    const losArcos = parseInt(document.getElementById('stock_los_arcos').value) || 0;
    const chinaca = parseInt(document.getElementById('stock_chinaca').value) || 0;
    const total = losArcos + chinaca;
    
    document.getElementById('preview-stock-total').textContent = total;
}

/**
 * Actualizar precio mayorista
 */
function actualizarPrecioMayorista() {
    const precio = parseFloat(document.getElementById('precio_mayorista').value) || 0;
    document.getElementById('preview-precio-mayorista').textContent = 'Q ' + precio.toFixed(2);
}

/**
 * Enviar formulario
 */
async function enviarFormulario(e) {
    e.preventDefault();
    
    console.log('📤 Enviando formulario...');
    
    const btnGuardar = document.getElementById('btnGuardar');
    const btnTexto = btnGuardar.innerHTML;
    
    try {
        // Obtener datos
        const formData = new FormData(e.target);
        
        // Validaciones
        const precioPublico = parseFloat(formData.get('precio_publico')) || 0;
        const precioMayorista = parseFloat(formData.get('precio_mayorista')) || 0;
        
        if (precioMayorista > 0 && precioMayorista >= precioPublico) {
            alert('El precio mayorista debe ser menor que el precio público');
            return;
        }
        
        const stockLosArcos = parseInt(formData.get('stock_los_arcos')) || 0;
        const stockChinaca = parseInt(formData.get('stock_chinaca')) || 0;
        const stockTotal = stockLosArcos + stockChinaca;
        
        if (stockTotal === 0) {
            if (!confirm('No ha ingresado stock inicial. ¿Desea continuar?')) {
                return;
            }
        }
        
        // Generar código según categoría
        const categoriaId = parseInt(formData.get('categoria_id'));
        const selectCategoria = document.getElementById('categoria_id');
        const categoriaNombre = selectCategoria.options[selectCategoria.selectedIndex]?.text || '';
        const prefijo = categoriaNombre.substring(0, 2).toUpperCase();
        
        // Buscar último código
        let codigoProducto = `${prefijo}-001`;
        try {
            const respProductos = await API.get('api/productos/listar.php', {
                categoria_id: categoriaId,
                por_pagina: 1,
                ordenar_por: 'id',
                orden: 'DESC'
            });
            
            if (respProductos.success && respProductos.data?.productos?.length > 0) {
                const ultimoCodigo = respProductos.data.productos[0].codigo;
                const match = ultimoCodigo.match(/-(\d+)$/);
                if (match) {
                    const siguiente = parseInt(match[1]) + 1;
                    codigoProducto = `${prefijo}-${siguiente.toString().padStart(3, '0')}`;
                }
            }
        } catch (err) {
            console.log('Usando código por defecto');
        }
        
        // Generar código de barras
        const codigoBarras = '750' + Math.floor(Math.random() * 10000000000).toString().padStart(10, '0');
        
        // Preparar datos
        const datos = {
            codigo: codigoProducto,
            codigo_barras: codigoBarras,
            categoria_id: categoriaId,
            nombre: formData.get('nombre'),
            descripcion: formData.get('descripcion') || null,
            peso_gramos: parseFloat(formData.get('peso_gramos')) || null,
            largo_cm: parseFloat(formData.get('largo_cm')) || null,
            estilo: formData.get('estilo') || null,
            es_por_peso: formData.get('es_por_peso') ? 1 : 0,
            stock_minimo: parseInt(formData.get('stock_minimo')) || 5,
            activo: formData.get('activo') ? 1 : 0,
            precio_publico: precioPublico,
            precio_mayorista: precioMayorista || null,
            stock_los_arcos: stockLosArcos,
            stock_chinaca: stockChinaca
        };
        
        console.log('📦 Datos a enviar:', datos);
        
        // Mostrar loading
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
        
        // Enviar a API
        const response = await API.post('api/productos/crear.php', datos);
        
        console.log('📥 Respuesta API:', response);
        
        if (response.success) {
            // Éxito
            if (typeof Swal !== 'undefined') {
                await Swal.fire({
                    icon: 'success',
                    title: '¡Producto Creado!',
                    html: `
                        <p><strong>${datos.nombre}</strong></p>
                        <p>Código: ${codigoProducto}</p>
                        <p>Código de barras: ${codigoBarras}</p>
                        <p>Stock: ${stockTotal} unidades</p>
                    `,
                    confirmButtonColor: '#10b981'
                });
            } else {
                alert(`¡Producto creado!\nCódigo: ${codigoProducto}\nCódigo de barras: ${codigoBarras}`);
            }
            
            window.location.href = 'lista.php';
        }
        
    } catch (error) {
        console.error('❌ Error:', error);
        alert('Error: ' + (error.message || 'No se pudo crear el producto'));
    } finally {
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = btnTexto;
    }
}

console.log('✅ Script cargado');
