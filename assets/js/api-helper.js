/**
 * ================================================
 * API HELPER - SISTEMA JOYERÍA TORRE FUERTE
 * ================================================
 * Helper centralizado para todas las llamadas AJAX a las APIs del backend
 * 
 * Características:
 * - Manejo centralizado de errores
 * - Formato consistente de respuestas
 * - Headers automáticos
 * - Timeouts configurables
 * - Logging opcional
 * 
 * Autor: Sistema Joyería Torre Fuerte
 * Versión: 1.0
 */

const API = {
    /**
     * Configuración global
     */
    config: {
        baseURL: window.BASE_URL || 'http://localhost/joyeria-torre-fuerte/',
        timeout: 30000, // 30 segundos
        debug: true // Cambiar a false en producción
    },

    /**
     * ================================================
     * MÉTODO PRINCIPAL: REQUEST
     * ================================================
     * Realiza una petición HTTP a la API
     * 
     * @param {string} endpoint - Ruta del endpoint (ej: 'api/productos/listar.php')
     * @param {object} options - Opciones de la petición
     * @returns {Promise} Promesa con la respuesta
     */
    request: async function(endpoint, options = {}) {
        const config = {
            method: options.method || 'GET',
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            credentials: 'same-origin' // Incluir cookies de sesión
        };

        // Agregar body para POST
        if (options.body && config.method === 'POST') {
            config.body = JSON.stringify(options.body);
        }

        const url = this.config.baseURL + endpoint;

        if (this.config.debug) {
            console.log(`🌐 API Request [${config.method}]: ${endpoint}`, options.body || '');
        }

        try {
            // Agregar timeout
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), this.config.timeout);
            config.signal = controller.signal;

            const response = await fetch(url, config);
            clearTimeout(timeoutId);

            // DEBUG: Ver respuesta cruda ANTES de parsear
            const responseText = await response.text();
            
            // Mostrar en consola SIEMPRE (no solo en debug)
            console.log('🔍 Raw Response:', responseText.substring(0, 500));
            console.log('🔍 Response status:', response.status);
            console.log('🔍 Response headers:', [...response.headers.entries()]);

            // Parsear respuesta JSON
            let data;
            try {
                data = JSON.parse(responseText);
            } catch (parseError) {
                console.error('❌ ERROR PARSEANDO JSON');
                console.error('❌ Respuesta COMPLETA:', responseText);
                throw new APIError('Respuesta inválida del servidor', 'INVALID_RESPONSE');
            }

            if (this.config.debug) {
                console.log(`✅ API Response [${config.method}]: ${endpoint}`, data);
            }

            // Verificar si la respuesta fue exitosa
            if (!data.success) {
                throw new APIError(data.message || 'Error desconocido', data.code || 'UNKNOWN_ERROR', data);
            }

            return data;

        } catch (error) {
            if (this.config.debug) {
                console.error(`❌ API Error [${config.method}]: ${endpoint}`, error);
            }

            // Manejar errores específicos
            if (error.name === 'AbortError') {
                throw new APIError('La solicitud ha tardado demasiado', 'TIMEOUT');
            }

            if (error instanceof APIError) {
                throw error;
            }

            throw new APIError(error.message || 'Error de conexión', 'NETWORK_ERROR');
        }
    },

    /**
     * ================================================
     * MÉTODOS HTTP
     * ================================================
     */

    /**
     * GET - Obtener datos
     */
    get: function(endpoint, params = {}) {
        // Construir query string
        const queryString = new URLSearchParams(params).toString();
        const fullEndpoint = queryString ? `${endpoint}?${queryString}` : endpoint;
        
        return this.request(fullEndpoint, { method: 'GET' });
    },

    /**
     * POST - Enviar datos
     */
    post: function(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'POST',
            body: data
        });
    },

    /**
     * ================================================
     * ENDPOINTS ESPECÍFICOS - PRODUCTOS
     * ================================================
     */
    productos: {
        /**
         * Listar productos con filtros
         */
        listar: function(filtros = {}) {
            return API.get('api/productos/listar.php', filtros);
        },

        /**
         * Buscar productos (autocompletado)
         */
        buscar: function(termino, limite = 10) {
            return API.get('api/productos/buscar.php', { termino, limite });
        },

        /**
         * Obtener un producto por ID
         */
        obtener: function(id) {
            return API.get('api/productos/buscar.php', { termino: id, limite: 1 });
        },

        /**
         * Crear producto nuevo
         */
        crear: function(datos) {
            return API.post('api/productos/crear.php', datos);
        },

        /**
         * Actualizar producto existente
         */
        actualizar: function(datos) {
            return API.post('api/productos/actualizar.php', datos);
        },

        /**
         * Eliminar producto (soft delete)
         */
        eliminar: function(id) {
            return API.post('api/productos/eliminar.php', { id });
        },

        /**
         * Productos con bajo stock
         */
        bajoStock: function(sucursal_id = null, limite = 50) {
            const params = { limite };
            if (sucursal_id) params.sucursal_id = sucursal_id;
            return API.get('api/productos/bajo_stock.php', params);
        }
    },

    /**
     * ================================================
     * ENDPOINTS ESPECÍFICOS - INVENTARIO
     * ================================================
     */
    inventario: {
        /**
         * Obtener inventario por sucursal
         */
        porSucursal: function(sucursal_id, filtros = {}) {
            return API.get('api/inventario/por_sucursal.php', {
                sucursal_id,
                ...filtros
            });
        },

        /**
         * Ajustar stock de un producto
         */
        ajustarStock: function(datos) {
            return API.post('api/inventario/ajustar_stock.php', datos);
        },

        /**
         * Transferir stock entre sucursales
         */
        transferir: function(datos) {
            return API.post('api/inventario/transferir.php', datos);
        }
    },

    /**
     * ================================================
     * ENDPOINTS ESPECÍFICOS - CATEGORÍAS
     * ================================================
     */
    categorias: {
        /**
         * Listar categorías
         */
        listar: function(filtros = {}) {
            return API.get('api/categorias/listar.php', filtros);
        },

        /**
         * Crear categoría
         */
        crear: function(datos) {
            return API.post('api/categorias/crear.php', datos);
        },

        /**
         * Editar categoría
         */
        editar: function(datos) {
            return API.post('api/categorias/editar.php', datos);
        },

        /**
         * Cambiar estado (activar/desactivar)
         */
        cambiarEstado: function(id, accion) {
            return API.post('api/categorias/cambiar_estado.php', { id, accion });
        }
    },

    /**
     * ================================================
     * UTILIDADES
     * ================================================
     */

    /**
     * Manejar errores de forma user-friendly
     */
    handleError: function(error, defaultMessage = 'Ha ocurrido un error') {
        let mensaje = defaultMessage;

        if (error instanceof APIError) {
            mensaje = error.message;
        } else if (error.message) {
            mensaje = error.message;
        }

        // Mostrar alerta
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: mensaje,
                confirmButtonColor: '#1e3a8a'
            });
        } else {
            alert(mensaje);
        }

        return mensaje;
    },

    /**
     * Mostrar mensaje de éxito
     */
    showSuccess: function(mensaje = 'Operación exitosa') {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: mensaje,
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            alert(mensaje);
        }
    },

    /**
     * Confirmar acción destructiva
     */
    confirm: async function(mensaje = '¿Está seguro?', titulo = 'Confirmación') {
        if (typeof Swal !== 'undefined') {
            const result = await Swal.fire({
                title: titulo,
                text: mensaje,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, confirmar',
                cancelButtonText: 'Cancelar'
            });
            return result.isConfirmed;
        } else {
            return confirm(mensaje);
        }
    }
};

/**
 * ================================================
 * CLASE DE ERROR PERSONALIZADA
 * ================================================
 */
class APIError extends Error {
    constructor(message, code = 'UNKNOWN_ERROR', data = null) {
        super(message);
        this.name = 'APIError';
        this.code = code;
        this.data = data;
    }
}

/**
 * ================================================
 * EXPORTAR API GLOBALMENTE
 * ================================================
 */
window.API = API;
window.APIError = APIError;

// Log de inicialización
if (API.config.debug) {
    console.log('✅ API Helper cargado correctamente');
    console.log('📡 Base URL:', API.config.baseURL);
}
