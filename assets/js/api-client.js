/**
 * API-CLIENT.JS - Cliente de API REST
 * Sistema Joyería Torre Fuerte
 * 
 * Este archivo contiene TODAS las funciones para comunicarse con el backend.
 * Total: 74 endpoints organizados por módulo.
 * 
 * Uso: await api.nombreFuncion(parametros)
 * Ejemplo: const productos = await api.listarProductos({ activo: 1 });
 */

class APIClient {
    constructor() {
        this.baseURL = '/joyeria-torre-fuerte/api';
    }

    /**
     * Método base para hacer peticiones HTTP
     * @param {string} endpoint - Ruta del endpoint (ej: '/productos/listar.php')
     * @param {object} options - Opciones de fetch
     * @returns {Promise} - Promesa con la respuesta
     */
    async request(endpoint, options = {}) {
        const url = `${this.baseURL}${endpoint}`;
        
        const config = {
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        };

        try {
            const response = await fetch(url, config);
            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.message || data.error || 'Error en la petición');
            }

            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    // ============================================================================
    // 1. AUTENTICACIÓN (2 endpoints)
    // ============================================================================

    /**
     * Login de usuario
     * @param {string} email - Email del usuario
     * @param {string} password - Contraseña
     * @returns {Promise} - Datos del usuario y token
     */
    async login(email, password) {
        return this.request('/auth/login.php', {
            method: 'POST',
            body: JSON.stringify({ email, password })
        });
    }

    /**
     * Logout de usuario
     */
    async logout() {
        return this.request('/auth/logout.php', {
            method: 'POST'
        });
    }

    // ============================================================================
    // 2. PRODUCTOS (6 endpoints)
    // ============================================================================

    /**
     * Listar productos con filtros
     * @param {object} filtros - { activo, categoria_id, buscar, etc }
     */
    async listarProductos(filtros = {}) {
        const params = new URLSearchParams(filtros);
        return this.request(`/productos/listar.php?${params}`);
    }

    /**
     * Buscar productos por término
     * @param {string} termino - Texto a buscar
     */
    async buscarProductos(termino) {
        return this.request(`/productos/buscar.php?q=${encodeURIComponent(termino)}`);
    }

    /**
     * Crear producto nuevo
     * @param {object} datos - Datos del producto
     */
    async crearProducto(datos) {
        return this.request('/productos/crear.php', {
            method: 'POST',
            body: JSON.stringify(datos)
        });
    }

    /**
     * Actualizar producto existente
     * @param {number} id - ID del producto
     * @param {object} datos - Datos a actualizar
     */
    async actualizarProducto(id, datos) {
        return this.request('/productos/actualizar.php', {
            method: 'POST',
            body: JSON.stringify({ id, ...datos })
        });
    }

    /**
     * Eliminar producto (soft delete)
     * @param {number} id - ID del producto
     */
    async eliminarProducto(id) {
        return this.request('/productos/eliminar.php', {
            method: 'POST',
            body: JSON.stringify({ id })
        });
    }

    /**
     * Productos con stock bajo
     */
    async productosBajoStock() {
        return this.request('/productos/bajo_stock.php');
    }

    // ============================================================================
    // 3. INVENTARIO (3 endpoints)
    // ============================================================================

    /**
     * Obtener inventario por sucursal
     * @param {number} sucursal_id - ID de la sucursal
     */
    async inventarioPorSucursal(sucursal_id) {
        return this.request(`/inventario/por_sucursal.php?sucursal_id=${sucursal_id}`);
    }

    /**
     * Ajustar stock de producto
     * @param {object} datos - { producto_id, sucursal_id, cantidad, motivo }
     */
    async ajustarStock(datos) {
        return this.request('/inventario/ajustar_stock.php', {
            method: 'POST',
            body: JSON.stringify(datos)
        });
    }

    /**
     * Transferir productos entre sucursales
     * @param {object} datos - { sucursal_origen_id, sucursal_destino_id, productos, observaciones }
     */
    async transferirInventario(datos) {
        return this.request('/inventario/transferir.php', {
            method: 'POST',
            body: JSON.stringify(datos)
        });
    }

    // ============================================================================
    // 4. TALLER (8 endpoints)
    // ============================================================================

    /**
     * Listar trabajos del taller
     * @param {object} filtros - { estado, empleado_id, fecha_desde, fecha_hasta }
     */
    async listarTrabajos(filtros = {}) {
        const params = new URLSearchParams(filtros);
        return this.request(`/taller/listar.php?${params}`);
    }

    /**
     * Crear nuevo trabajo de taller
     * @param {object} datos - Datos del trabajo
     */
    async crearTrabajo(datos) {
        return this.request('/taller/crear.php', {
            method: 'POST',
            body: JSON.stringify(datos)
        });
    }

    /**
     * Transferir trabajo entre empleados
     * @param {object} datos - { trabajo_id, empleado_destino_id, observaciones }
     */
    async transferirTrabajo(datos) {
        return this.request('/taller/transferir.php', {
            method: 'POST',
            body: JSON.stringify(datos)
        });
    }

    /**
     * Entregar trabajo al cliente
     * @param {object} datos - { trabajo_id, forma_pago, monto_pagado, caja_id }
     */
    async entregarTrabajo(datos) {
        return this.request('/taller/entregar.php', {
            method: 'POST',
            body: JSON.stringify(datos)
        });
    }

    /**
     * Obtener detalle de un trabajo
     * @param {number} id - ID del trabajo
     */
    async detalleTrabajo(id) {
        return this.request(`/taller/detalle.php?id=${id}`);
    }

    /**
     * Cambiar estado de trabajo
     * @param {number} id - ID del trabajo
     * @param {string} estado - Nuevo estado
     */
    async cambiarEstadoTrabajo(id, estado) {
        return this.request('/taller/cambiar_estado.php', {
            method: 'POST',
            body: JSON.stringify({ id, estado })
        });
    }

    /**
     * Completar trabajo
     * @param {number} id - ID del trabajo
     */
    async completarTrabajo(id) {
        return this.request('/taller/completar.php', {
            method: 'POST',
            body: JSON.stringify({ id })
        });
    }

    /**
     * Cancelar trabajo
     * @param {number} id - ID del trabajo
     * @param {string} motivo - Motivo de cancelación
     */
    async cancelarTrabajo(id, motivo) {
        return this.request('/taller/cancelar.php', {
            method: 'POST',
            body: JSON.stringify({ id, motivo })
        });
    }

    // ============================================================================
    // 5. VENTAS (5 endpoints)
    // ============================================================================

    /**
     * Crear venta nueva
     * @param {object} datos - Datos completos de la venta
     */
    async crearVenta(datos) {
        return this.request('/ventas/crear.php', {
            method: 'POST',
            body: JSON.stringify(datos)
        });
    }

    /**
     * Listar ventas con filtros
     * @param {object} filtros - { fecha_desde, fecha_hasta, sucursal_id, vendedor_id }
     */
    async listarVentas(filtros = {}) {
        const params = new URLSearchParams(filtros);
        return this.request(`/ventas/listar.php?${params}`);
    }

    /**
     * Obtener detalle de una venta
     * @param {number} id - ID de la venta
     */
    async detalleVenta(id) {
        return this.request(`/ventas/detalle.php?id=${id}`);
    }

    /**
     * Anular venta
     * @param {number} id - ID de la venta
     * @param {string} motivo - Motivo de anulación
     */
    async anularVenta(id, motivo) {
        return this.request('/ventas/anular.php', {
            method: 'POST',
            body: JSON.stringify({ id, motivo })
        });
    }

    /**
     * Obtener ventas del día actual
     */
    async ventasDelDia() {
        return this.request('/ventas/ventas_del_dia.php');
    }

    // ============================================================================
    // 6. CAJA (3 endpoints)
    // ============================================================================

    /**
     * Abrir caja
     * @param {object} datos - { sucursal_id, monto_inicial }
     */
    async abrirCaja(datos) {
        return this.request('/caja/abrir_caja.php', {
            method: 'POST',
            body: JSON.stringify(datos)
        });
    }

    /**
     * Obtener caja actual abierta
     * @param {number} sucursal_id - ID de la sucursal
     */
    async cajaActual(sucursal_id) {
        return this.request(`/caja/caja_actual.php?sucursal_id=${sucursal_id}`);
    }

    /**
     * Cerrar caja
     * @param {object} datos - { caja_id, monto_final, observaciones }
     */
    async cerrarCaja(datos) {
        return this.request('/caja/cerrar_caja.php', {
            method: 'POST',
            body: JSON.stringify(datos)
        });
    }

    // ============================================================================
    // 7. CLIENTES (7 endpoints)
    // ============================================================================

    /**
     * Listar clientes
     * @param {object} filtros - { tipo_cliente, activo, buscar }
     */
    async listarClientes(filtros = {}) {
        const params = new URLSearchParams(filtros);
        return this.request(`/clientes/listar.php?${params}`);
    }

    /**
     * Buscar clientes por término
     * @param {string} termino - Texto a buscar
     */
    async buscarClientes(termino) {
        return this.request(`/clientes/buscar.php?q=${encodeURIComponent(termino)}`);
    }

    /**
     * Crear cliente nuevo
     * @param {object} datos - Datos del cliente
     */
    async crearCliente(datos) {
        const formData = new URLSearchParams();
        
        Object.keys(datos).forEach(key => {
            if (datos[key] !== null && datos[key] !== undefined) {
                formData.append(key, datos[key]);
            }
        });
        
        return this.request('/clientes/crear.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: formData.toString()
        });
    }

    /**
     * Actualizar cliente
     * @param {number} id - ID del cliente
     * @param {object} datos - Datos a actualizar
     */

    async actualizarCliente(id, datos) {
        // Crear FormData en lugar de JSON
        const formData = new URLSearchParams();
        formData.append('id', parseInt(id));
        
        // Agregar todos los campos
        Object.keys(datos).forEach(key => {
            if (datos[key] !== null && datos[key] !== undefined) {
                formData.append(key, datos[key]);
            }
        });
        
        return this.request('/clientes/actualizar.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: formData.toString()
        });
    }

    /**
     * Eliminar cliente
     * @param {number} id - ID del cliente
     */
    async eliminarCliente(id) {
        return this.request('/clientes/eliminar.php', {
            method: 'POST',
            body: JSON.stringify({ id })
        });
    }

    /**
     * Obtener créditos de un cliente
     * @param {number} cliente_id - ID del cliente
     */
    async creditosCliente(cliente_id) {
        return this.request(`/clientes/creditos.php?cliente_id=${cliente_id}`);
    }

    /**
     * Obtener historial de compras de un cliente
     * @param {number} cliente_id - ID del cliente
     */
    async historialComprasCliente(cliente_id) {
        return this.request(`/clientes/historial_compras.php?cliente_id=${cliente_id}`);
    }

    // ============================================================================
    // 8. USUARIOS (5 endpoints)
    // ============================================================================

    /**
     * Listar usuarios
     * @param {object} filtros - { rol, sucursal_id, activo }
     */
    async listarUsuarios(filtros = {}) {
        const params = new URLSearchParams(filtros);
        return this.request(`/usuarios/listar.php?${params}`);
    }

    /**
     * Crear usuario nuevo
     * @param {object} datos - Datos del usuario
     */
    async crearUsuario(datos) {
        return this.request('/usuarios/crear.php', {
            method: 'POST',
            body: JSON.stringify(datos)
        });
    }

    /**
     * Editar usuario
     * @param {number} id - ID del usuario
     * @param {object} datos - Datos a actualizar
     */
    async editarUsuario(id, datos) {
        return this.request('/usuarios/editar.php', {
            method: 'POST',
            body: JSON.stringify({ id, ...datos })
        });
    }

    /**
     * Cambiar estado de usuario (activar/desactivar)
     * @param {number} id - ID del usuario
     * @param {number} nuevoEstado - 1 o 0
     */
    async cambiarEstadoUsuario(id, nuevoEstado) {
        const accion = nuevoEstado == 1 ? 'activar' : 'desactivar';
        
        return this.request('/usuarios/cambiar_estado.php', {
            method: 'POST',
            body: JSON.stringify({ id: parseInt(id), accion })
        });
    }

    /**
     * Cambiar contraseña de usuario
     * @param {number} id - ID del usuario
     * @param {string} password_actual - Contraseña actual
     * @param {string} password_nueva - Nueva contraseña
     */
    async cambiarPasswordUsuario(id, password_actual, password_nueva) {
        return this.request('/usuarios/cambiar_password.php', {
            method: 'POST',
            body: JSON.stringify({ id, password_actual, password_nueva })
        });
    }

    // ============================================================================
    // 9. CATEGORÍAS (4 endpoints)
    // ============================================================================

    /**
     * Listar categorías
     * @param {object} filtros - { activo }
     */
    async listarCategorias(filtros = {}) {
        const params = new URLSearchParams(filtros);
        return this.request(`/categorias/listar.php?${params}`);
    }

    /**
     * Crear categoría nueva
     * @param {object} datos - Datos de la categoría
     */
    async crearCategoria(datos) {
        return this.request('/categorias/crear.php', {
            method: 'POST',
            body: JSON.stringify(datos)
        });
    }

    /**
     * Editar categoría
     * @param {number} id - ID de la categoría
     * @param {object} datos - Datos a actualizar
     */
    async editarCategoria(id, datos) {
        return this.request('/categorias/editar.php', {
            method: 'POST',
            body: JSON.stringify({ id, ...datos })
        });
    }

    /**
     * Cambiar estado de categoría
     * @param {number} id - ID de la categoría
     * @param {number} nuevoEstado - 1 o 0
     */
    async cambiarEstadoCategoria(id, nuevoEstado) {
        const accion = nuevoEstado == 1 ? 'activar' : 'desactivar';
        
        return this.request('/categorias/cambiar_estado.php', {
            method: 'POST',
            body: JSON.stringify({ id: parseInt(id), accion })
        });
    }

    // ============================================================================
    // 10. PROVEEDORES (5 endpoints)
    // ============================================================================

    /**
     * Listar proveedores
     * @param {object} filtros - { activo }
     */
    async listarProveedores(filtros = {}) {
        const params = new URLSearchParams(filtros);
        return this.request(`/proveedores/listar.php?${params}`);
    }

    /**
     * Buscar proveedores
     * @param {string} termino - Texto a buscar
     */
    async buscarProveedores(termino) {
        return this.request(`/proveedores/buscar.php?q=${encodeURIComponent(termino)}`);
    }

    /**
     * Crear proveedor nuevo
     * @param {object} datos - Datos del proveedor
     */
    async crearProveedor(datos) {
        return this.request('/proveedores/crear.php', {
            method: 'POST',
            body: JSON.stringify(datos)
        });
    }

    /**
     * Editar proveedor
     * @param {number} id - ID del proveedor
     * @param {object} datos - Datos a actualizar
     */
    async editarProveedor(id, datos) {
        return this.request('/proveedores/editar.php', {
            method: 'POST',
            body: JSON.stringify({ id, ...datos })
        });
    }

    /**
     * Cambiar estado de proveedor
     * @param {number} id - ID del proveedor
     * @param {number} nuevoEstado - 1 o 0
     */
    async cambiarEstadoProveedor(id, nuevoEstado) {
        const accion = nuevoEstado == 1 ? 'activar' : 'desactivar';
        
        return this.request('/proveedores/cambiar_estado.php', {
            method: 'POST',
            body: JSON.stringify({ id: parseInt(id), accion })
        });
    }

    // ============================================================================
    // 11. PRECIOS (4 endpoints)
    // ============================================================================

    /**
     * Listar precios
     * @param {object} filtros - { producto_id, tipo_precio, activo }
     */
    async listarPrecios(filtros = {}) {
        const params = new URLSearchParams(filtros);
        return this.request(`/precios/listar.php?${params}`);
    }

    /**
     * Crear precio nuevo
     * @param {object} datos - { producto_id, tipo_precio, precio }
     */
    async crearPrecio(datos) {
        return this.request('/precios/crear.php', {
            method: 'POST',
            body: JSON.stringify(datos)
        });
    }

    /**
     * Editar precio
     * @param {number} id - ID del precio
     * @param {object} datos - Datos a actualizar
     */
    async editarPrecio(id, datos) {
        return this.request('/precios/editar.php', {
            method: 'POST',
            body: JSON.stringify({ id, ...datos })
        });
    }

    /**
     * Cambiar estado de precio
     * @param {number} id - ID del precio
     * @param {number} nuevoEstado - 1 o 0
     */
    async cambiarEstadoPrecio(id, nuevoEstado) {
        const accion = nuevoEstado == 1 ? 'activar' : 'desactivar';
        
        return this.request('/precios/cambiar_estado.php', {
            method: 'POST',
            body: JSON.stringify({ id: parseInt(id), accion })
        });
    }

    // ============================================================================
    // 12. SUCURSALES (5 endpoints)
    // ============================================================================

    /**
     * Listar sucursales
     * @param {object} filtros - { activo }
     */
    async listarSucursales(filtros = {}) {
        const params = new URLSearchParams(filtros);
        return this.request(`/sucursales/listar.php?${params}`);
    }

    /**
     * Crear sucursal nueva
     * @param {object} datos - Datos de la sucursal
     */
    async crearSucursal(datos) {
        return this.request('/sucursales/crear.php', {
            method: 'POST',
            body: JSON.stringify(datos)
        });
    }

    /**
     * Editar sucursal
     * @param {number} id - ID de la sucursal
     * @param {object} datos - Datos a actualizar
     */
    async editarSucursal(id, datos) {
        return this.request('/sucursales/editar.php', {
            method: 'POST',
            body: JSON.stringify({ id, ...datos })
        });
    }

    /**
     * Obtener detalle de sucursal
     * @param {number} id - ID de la sucursal
     */
    async detalleSucursal(id) {
        return this.request(`/sucursales/detalle.php?id=${id}`);
    }

    /**
     * Cambiar estado de sucursal
     * @param {number} id - ID de la sucursal
     * @param {number} nuevoEstado - 1 o 0
     */
    async cambiarEstadoSucursal(id, nuevoEstado) {
        const accion = nuevoEstado == 1 ? 'activar' : 'desactivar';
        
        return this.request('/sucursales/cambiar_estado.php', {
            method: 'POST',
            body: JSON.stringify({ id: parseInt(id), accion })
        });
    }

    // ============================================================================
    // 13. MATERIA PRIMA (5 endpoints)
    // ============================================================================

    /**
     * Listar materias primas
     * @param {object} filtros - { tipo, activo }
     */
    async listarMateriasPrimas(filtros = {}) {
        const params = new URLSearchParams(filtros);
        return this.request(`/materia_prima/listar.php?${params}`);
    }

    /**
     * Crear materia prima nueva
     * @param {object} datos - Datos de la materia prima
     */
    async crearMateriaPrima(datos) {
        return this.request('/materia_prima/crear.php', {
            method: 'POST',
            body: JSON.stringify(datos)
        });
    }

    /**
     * Editar materia prima
     * @param {number} id - ID de la materia prima
     * @param {object} datos - Datos a actualizar
     */
    async editarMateriaPrima(id, datos) {
        return this.request('/materia_prima/editar.php', {
            method: 'POST',
            body: JSON.stringify({ id, ...datos })
        });
    }

    /**
     * Ajustar stock de materia prima
     * @param {object} datos - { materia_prima_id, cantidad, motivo }
     */
    async ajustarStockMateriaPrima(datos) {
        return this.request('/materia_prima/ajustar_stock.php', {
            method: 'POST',
            body: JSON.stringify(datos)
        });
    }

    /**
     * Cambiar estado de materia prima
     * @param {number} id - ID de la materia prima
     * @param {number} nuevoEstado - 1 o 0
     */
    async cambiarEstadoMateriaPrima(id, nuevoEstado) {
        const accion = nuevoEstado == 1 ? 'activar' : 'desactivar';
        
        return this.request('/materia_prima/cambiar_estado.php', {
            method: 'POST',
            body: JSON.stringify({ id: parseInt(id), accion })
        });
    }

    // ============================================================================
    // 14. FACTURAS (2 endpoints)
    // ============================================================================

    /**
     * Generar factura
     * @param {object} datos - { venta_id, tipo, nit, nombre }
     */
    async generarFactura(datos) {
        return this.request('/facturas/generar.php', {
            method: 'POST',
            body: JSON.stringify(datos)
        });
    }

    /**
     * Listar facturas
     * @param {object} filtros - { fecha_desde, fecha_hasta, tipo, estado }
     */
    async listarFacturas(filtros = {}) {
        const params = new URLSearchParams(filtros);
        return this.request(`/facturas/listar.php?${params}`);
    }

    // ============================================================================
    // 15. REPORTES (5 endpoints)
    // ============================================================================

    /**
     * Obtener datos para el dashboard
     */
    async reporteDashboard() {
        return this.request('/reportes/dashboard.php');
    }

    /**
     * Reporte de ventas
     * @param {object} filtros - { fecha_desde, fecha_hasta, sucursal_id, vendedor_id }
     */
    async reporteVentas(filtros = {}) {
        const params = new URLSearchParams(filtros);
        return this.request(`/reportes/ventas.php?${params}`);
    }

    /**
     * Reporte de productos más vendidos
     * @param {object} filtros - { fecha_desde, fecha_hasta, limite }
     */
    async reporteProductos(filtros = {}) {
        const params = new URLSearchParams(filtros);
        return this.request(`/reportes/productos.php?${params}`);
    }

    /**
     * Reporte de inventario
     * @param {object} filtros - { sucursal_id }
     */
    async reporteInventario(filtros = {}) {
        const params = new URLSearchParams(filtros);
        return this.request(`/reportes/inventario.php?${params}`);
    }

    /**
     * Reporte financiero
     * @param {object} filtros - { fecha_desde, fecha_hasta }
     */
    async reporteFinanciero(filtros = {}) {
        const params = new URLSearchParams(filtros);
        return this.request(`/reportes/financiero.php?${params}`);
    }

    // ============================================================================
    // 16. MOVIMIENTOS DE INVENTARIO (3 endpoints)
    // ============================================================================

    /**
     * Listar movimientos de inventario
     * @param {object} filtros - { producto_id, sucursal_id, tipo_movimiento, fecha_desde, fecha_hasta }
     */
    async listarMovimientosInventario(filtros = {}) {
        const params = new URLSearchParams(filtros);
        return this.request(`/movimientos_inventario/listar.php?${params}`);
    }

    /**
     * Estadísticas de movimientos de inventario
     * @param {object} filtros - { fecha_desde, fecha_hasta }
     */
    async estadisticasMovimientosInventario(filtros = {}) {
        const params = new URLSearchParams(filtros);
        return this.request(`/movimientos_inventario/estadisticas.php?${params}`);
    }

    /**
     * Resumen de productos con movimientos
     * @param {object} filtros - { fecha_desde, fecha_hasta }
     */
    async resumenProductosMovimientos(filtros = {}) {
        const params = new URLSearchParams(filtros);
        return this.request(`/movimientos_inventario/resumen_productos.php?${params}`);
    }

    // ============================================================================
    // 17. MOVIMIENTOS DE CAJA (2 endpoints)
    // ============================================================================

    /**
     * Listar movimientos de caja
     * @param {object} filtros - { caja_id, tipo_movimiento, fecha_desde, fecha_hasta }
     */
    async listarMovimientosCaja(filtros = {}) {
        const params = new URLSearchParams(filtros);
        return this.request(`/movimientos_caja/listar.php?${params}`);
    }

    /**
     * Estadísticas de movimientos de caja
     * @param {object} filtros - { caja_id, fecha_desde, fecha_hasta }
     */
    async estadisticasMovimientosCaja(filtros = {}) {
        const params = new URLSearchParams(filtros);
        return this.request(`/movimientos_caja/estadisticas.php?${params}`);
    }

    // ============================================================================
    // 18. ABONOS DE CRÉDITOS (2 endpoints)
    // ============================================================================

    /**
     * Listar abonos de créditos
     * @param {object} filtros - { credito_id, cliente_id, forma_pago, fecha_desde, fecha_hasta }
     */
    async listarAbonosCreditos(filtros = {}) {
        const params = new URLSearchParams(filtros);
        return this.request(`/abonos_creditos/listar.php?${params}`);
    }

    /**
     * Estadísticas de abonos de créditos
     * @param {object} filtros - { fecha_desde, fecha_hasta }
     */
    async estadisticasAbonosCreditos(filtros = {}) {
        const params = new URLSearchParams(filtros);
        return this.request(`/abonos_creditos/estadisticas.php?${params}`);
    }

    // ============================================================================
    // 19. TRANSFERENCIAS DE INVENTARIO (1 endpoint)
    // ============================================================================

    /**
     * Listar transferencias de inventario
     * @param {object} filtros - { sucursal_origen_id, sucursal_destino_id, estado, fecha_desde, fecha_hasta }
     */
    async listarTransferenciasInventario(filtros = {}) {
        const params = new URLSearchParams(filtros);
        return this.request(`/inventario/transferencias/listar.php?${params}`);
    }
}

// ============================================================================
// INSTANCIA GLOBAL
// ============================================================================

// Crear instancia global para usar en todo el sistema
const api = new APIClient();

// Log de confirmación
console.log('✅ api-client.js cargado correctamente');
console.log('📡 74 endpoints disponibles');
console.log('💡 Uso: await api.nombreFuncion(parametros)');