/**
 * ================================================
 * MÓDULO INVENTARIO - CREAR PRODUCTO
 * ================================================
 */

const Inventario = {
    crear: {
        /**
         * Inicializar módulo de creación
         */
        init: function() {
            console.log('✅ Módulo Crear Producto inicializado');
            
            // Cargar categorías
            Inventario.crear.cargarCategorias();
            
            // Event listeners para vista previa
            const stockLosArcos = document.getElementById('stock_los_arcos');
            const stockChinaca = document.getElementById('stock_chinaca');
            const precioMayorista = document.getElementById('precio_mayorista');
            
            if (stockLosArcos) stockLosArcos.addEventListener('input', Inventario.crear.calcularStockTotal);
            if (stockChinaca) stockChinaca.addEventListener('input', Inventario.crear.calcularStockTotal);
            if (precioMayorista) precioMayorista.addEventListener('input', Inventario.crear.actualizarPrecioMayorista);
            
            // Formulario
            const form = document.getElementById('formProducto');
            if (form) {
                form.addEventListener('submit', Inventario.crear.enviar);
            }
        },

        /**
         * Cargar categorías desde la API
         */
        cargarCategorias: async function() {
            console.log('📁 Cargando categorías...');
            const select = document.getElementById('categoria_id');
            
            if (!select) {
                console.error('❌ Select de categoría no encontrado');
                return;
            }
            
            try {
                const response = await API.get('api/categorias/listar.php', { activo: 1 });
                console.log('📁 Respuesta API categorías:', response);
                
                if (response.success && response.data && response.data.length > 0) {
                    select.innerHTML = '<option value="">Seleccione una categoría...</option>';
                    
                    response.data.forEach(categoria => {
                        const option = document.createElement('option');
                        option.value = categoria.id;
                        option.textContent = categoria.nombre;
                        select.appendChild(option);
                    });
                    
                    console.log(`✅ ${response.data.length} categorías cargadas desde API`);
                } else {
                    console.warn('⚠️ API no devolvió categorías, usando fallback');
                    Inventario.crear.cargarCategoriasDefault();
                }
            } catch (error) {
                console.error('❌ Error al cargar categorías desde API:', error);
                console.log('📁 Cargando categorías por defecto...');
                Inventario.crear.cargarCategoriasDefault();
            }
        },

        /**
         * Cargar categorías por defecto (fallback)
         */
        cargarCategoriasDefault: function() {
            console.log('📁 Cargando categorías por defecto...');
            
            const categorias = [
                { id: 1, nombre: 'Anillos' },
                { id: 2, nombre: 'Aretes' },
                { id: 3, nombre: 'Collares' },
                { id: 4, nombre: 'Pulseras' },
                { id: 5, nombre: 'Cadenas' },
                { id: 6, nombre: 'Dijes' },
                { id: 7, nombre: 'Relojes' },
                { id: 8, nombre: 'Otros' }
            ];
            
            const select = document.getElementById('categoria_id');
            if (!select) {
                console.error('❌ Select de categoría no encontrado');
                return;
            }
            
            select.innerHTML = '<option value="">Seleccione una categoría...</option>';
            
            categorias.forEach(cat => {
                const option = document.createElement('option');
                option.value = cat.id;
                option.textContent = cat.nombre;
                select.appendChild(option);
            });
            
            console.log(`✅ ${categorias.length} categorías por defecto cargadas`);
        },

        /**
         * Calcular stock total para vista previa
         */
        calcularStockTotal: function() {
            const losArcos = parseInt(document.getElementById('stock_los_arcos').value) || 0;
            const chinaca = parseInt(document.getElementById('stock_chinaca').value) || 0;
            const total = losArcos + chinaca;
            
            const previewElement = document.getElementById('preview-stock-total');
            if (previewElement) {
                previewElement.textContent = total;
            }
        },

        /**
         * Actualizar precio mayorista en vista previa
         */
        actualizarPrecioMayorista: function() {
            const precio = parseFloat(document.getElementById('precio_mayorista').value) || 0;
            const previewElement = document.getElementById('preview-precio-mayorista');
            if (previewElement) {
                previewElement.textContent = 'Q ' + precio.toFixed(2);
            }
        },

        /**
         * Enviar formulario
         */
        enviar: async function(e) {
            e.preventDefault();
            
            const btnGuardar = document.getElementById('btnGuardar');
            const btnTextoOriginal = btnGuardar ? btnGuardar.innerHTML : '';
            
            try {
                // Obtener datos del formulario
                const formData = new FormData(e.target);
                
                // Validaciones adicionales
                const precioPublico = parseFloat(formData.get('precio_publico')) || 0;
                const precioMayorista = parseFloat(formData.get('precio_mayorista')) || 0;
                
                if (precioMayorista > 0 && precioMayorista >= precioPublico) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Validación',
                            text: 'El precio mayorista debe ser menor que el precio público'
                        });
                    } else {
                        alert('El precio mayorista debe ser menor que el precio público');
                    }
                    return;
                }
                
                const stockLosArcos = parseInt(formData.get('stock_los_arcos')) || 0;
                const stockChinaca = parseInt(formData.get('stock_chinaca')) || 0;
                const stockTotal = stockLosArcos + stockChinaca;
                
                if (stockTotal === 0) {
                    const confirmar = confirm('No ha ingresado stock inicial. ¿Desea continuar?');
                    if (!confirmar) return;
                }
                
                // Generar código según categoría
                const categoriaId = parseInt(formData.get('categoria_id'));
                const selectCategoria = document.getElementById('categoria_id');
                const categoriaNombre = selectCategoria.options[selectCategoria.selectedIndex]?.text || '';
                const prefijo = categoriaNombre.substring(0, 2).toUpperCase();
                
                // Buscar último código de la categoría
                let codigoProducto = `${prefijo}-001`; // Por defecto
                try {
                    const responseProductos = await API.get('api/productos/listar.php', {
                        categoria_id: categoriaId,
                        por_pagina: 1,
                        ordenar_por: 'id',
                        orden: 'DESC'
                    });
                    
                    if (responseProductos.success && responseProductos.data && 
                        responseProductos.data.productos && responseProductos.data.productos.length > 0) {
                        const ultimoCodigo = responseProductos.data.productos[0].codigo;
                        const match = ultimoCodigo.match(/-(\d+)$/);
                        if (match) {
                            const siguienteNumero = parseInt(match[1]) + 1;
                            codigoProducto = `${prefijo}-${siguienteNumero.toString().padStart(3, '0')}`;
                        }
                    }
                } catch (err) {
                    console.log('Usando código por defecto:', codigoProducto);
                }
                
                // Generar código de barras
                const codigoBarras = await Inventario.crear.generarCodigoBarras();
                
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
                
                // Mostrar loading
                if (btnGuardar) {
                    btnGuardar.disabled = true;
                    btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
                }
                
                // Enviar a la API correcta
                const response = await API.post('api/productos/crear.php', datos);
                
                if (response.success) {
                    // Mostrar mensaje de éxito
                    if (typeof Swal !== 'undefined') {
                        await Swal.fire({
                            icon: 'success',
                            title: '¡Producto Creado!',
                            html: `
                                <div style="text-align: center;">
                                    <p><strong>${datos.nombre}</strong></p>
                                    <div class="mt-3">
                                        <p class="mb-1"><strong>Código:</strong> ${codigoProducto}</p>
                                        <p class="mb-1"><strong>Código de barras:</strong> ${codigoBarras}</p>
                                        <p class="mb-0"><strong>Stock total:</strong> ${stockTotal} unidades</p>
                                    </div>
                                </div>
                            `,
                            confirmButtonColor: '#10b981'
                        });
                    } else {
                        alert(`¡Producto creado exitosamente!\n\nCódigo: ${codigoProducto}\nCódigo de barras: ${codigoBarras}`);
                    }
                    
                    // Redirigir a la lista
                    window.location.href = 'lista.php';
                }
                
            } catch (error) {
                console.error('Error al crear producto:', error);
                
                // Mostrar error
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'No se pudo crear el producto'
                    });
                } else {
                    alert('Error: ' + (error.message || 'No se pudo crear el producto'));
                }
            } finally {
                // Restaurar botón
                if (btnGuardar) {
                    btnGuardar.disabled = false;
                    btnGuardar.innerHTML = btnTextoOriginal;
                }
            }
        },

        /**
         * Generar código de barras único
         */
        generarCodigoBarras: async function() {
            try {
                // Generar código EAN-13 (13 dígitos)
                // Formato: 750 (Guatemala) + 10 dígitos aleatorios
                let codigo;
                let intentos = 0;
                const maxIntentos = 10;
                
                while (intentos < maxIntentos) {
                    // Generar 10 dígitos aleatorios
                    const random = Math.floor(Math.random() * 10000000000).toString().padStart(10, '0');
                    codigo = '750' + random;
                    
                    // Verificar que no exista en la BD
                    const existe = await Inventario.crear.verificarCodigoBarras(codigo);
                    if (!existe) {
                        return codigo;
                    }
                    
                    intentos++;
                }
                
                // Si no se pudo generar uno único, usar timestamp
                return '750' + Date.now().toString().slice(-10);
                
            } catch (error) {
                console.error('Error al generar código de barras:', error);
                // Fallback a timestamp
                return '750' + Date.now().toString().slice(-10);
            }
        },

        /**
         * Verificar si un código de barras ya existe
         */
        verificarCodigoBarras: async function(codigo) {
            try {
                const response = await API.get('api/productos/listar.php', {
                    codigo_barras: codigo,
                    por_pagina: 1
                });
                
                return response.success && response.data && response.data.productos && 
                       response.data.productos.length > 0;
            } catch (error) {
                console.error('Error al verificar código de barras:', error);
                return false;
            }
        }
    }
};

// Exportar para uso global
window.Inventario = Inventario;

console.log('✅ Módulo Inventario completo cargado');
