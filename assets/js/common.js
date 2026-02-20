/**
 * COMMON.JS - Funciones Utilitarias Comunes
 * Sistema Joyería Torre Fuerte
 * 
 * Este archivo contiene funciones reutilizables que se usan en TODAS las vistas.
 * Incluye: formateo, alertas, validaciones, manipulación de formularios y tablas.
 */

// ============================================================================
// 1. FUNCIONES DE FORMATEO
// ============================================================================

/**
 * Formatear monto a moneda guatemalteca (Quetzales)
 * @param {number} monto - Número a formatear
 * @returns {string} - Ej: "Q1,500.00"
 */
function formatearMoneda(monto) {
    if (monto === null || monto === undefined || isNaN(monto)) {
        return 'Q0.00';
    }
    
    return new Intl.NumberFormat('es-GT', {
        style: 'currency',
        currency: 'GTQ',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(monto);
}

/**
 * Formatear fecha a formato guatemalteco
 * @param {string|Date} fecha - Fecha a formatear
 * @returns {string} - Ej: "23/01/2026"
 */
function formatearFecha(fecha) {
    if (!fecha) return '';
    
    const date = typeof fecha === 'string' ? new Date(fecha) : fecha;
    
    if (isNaN(date.getTime())) return '';
    
    const dia = String(date.getDate()).padStart(2, '0');
    const mes = String(date.getMonth() + 1).padStart(2, '0');
    const anio = date.getFullYear();
    
    return `${dia}/${mes}/${anio}`;
}

/**
 * Formatear fecha y hora
 * @param {string|Date} fecha - Fecha a formatear
 * @returns {string} - Ej: "23/01/2026 14:30"
 */
function formatearFechaHora(fecha) {
    if (!fecha) return '';
    
    const date = typeof fecha === 'string' ? new Date(fecha) : fecha;
    
    if (isNaN(date.getTime())) return '';
    
    const dia = String(date.getDate()).padStart(2, '0');
    const mes = String(date.getMonth() + 1).padStart(2, '0');
    const anio = date.getFullYear();
    const hora = String(date.getHours()).padStart(2, '0');
    const minutos = String(date.getMinutes()).padStart(2, '0');
    
    return `${dia}/${mes}/${anio} ${hora}:${minutos}`;
}

/**
 * Formatear número con separador de miles
 * @param {number} numero - Número a formatear
 * @returns {string} - Ej: "1,500"
 */
function formatearNumero(numero) {
    if (numero === null || numero === undefined || isNaN(numero)) {
        return '0';
    }
    
    return new Intl.NumberFormat('es-GT').format(numero);
}

// ============================================================================
// 2. FUNCIONES DE ALERTAS (SweetAlert2)
// ============================================================================

/**
 * Mostrar alerta de éxito
 * @param {string} mensaje - Mensaje a mostrar
 * @param {string} titulo - Título opcional (default: "Éxito")
 */
function mostrarExito(mensaje, titulo = 'Éxito') {
    Swal.fire({
        icon: 'success',
        title: titulo,
        text: mensaje,
        confirmButtonText: 'OK',
        confirmButtonColor: '#10b981'
    });
}

/**
 * Mostrar alerta de error
 * @param {string} mensaje - Mensaje a mostrar
 * @param {string} titulo - Título opcional (default: "Error")
 */
function mostrarError(mensaje, titulo = 'Error') {
    Swal.fire({
        icon: 'error',
        title: titulo,
        text: mensaje,
        confirmButtonText: 'OK',
        confirmButtonColor: '#ef4444'
    });
}

/**
 * Mostrar alerta de advertencia
 * @param {string} mensaje - Mensaje a mostrar
 * @param {string} titulo - Título opcional (default: "Advertencia")
 */
function mostrarAdvertencia(mensaje, titulo = 'Advertencia') {
    Swal.fire({
        icon: 'warning',
        title: titulo,
        text: mensaje,
        confirmButtonText: 'OK',
        confirmButtonColor: '#f59e0b'
    });
}

/**
 * Mostrar alerta de información
 * @param {string} mensaje - Mensaje a mostrar
 * @param {string} titulo - Título opcional (default: "Información")
 */
function mostrarInfo(mensaje, titulo = 'Información') {
    Swal.fire({
        icon: 'info',
        title: titulo,
        text: mensaje,
        confirmButtonText: 'OK',
        confirmButtonColor: '#3b82f6'
    });
}

/**
 * Confirmar acción (¿Estás seguro?)
 * @param {string} mensaje - Mensaje de confirmación
 * @param {string} titulo - Título opcional (default: "¿Estás seguro?")
 * @returns {Promise<boolean>} - true si confirma, false si cancela
 */
async function confirmarAccion(mensaje, titulo = '¿Estás seguro?') {
    const result = await Swal.fire({
        icon: 'warning',
        title: titulo,
        text: mensaje,
        showCancelButton: true,
        confirmButtonText: 'Sí, confirmar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#3b82f6',
        cancelButtonColor: '#6b7280',
        reverseButtons: true
    });
    
    return result.isConfirmed;
}

// ============================================================================
// 3. FUNCIONES DE VALIDACIÓN
// ============================================================================

/**
 * Validar email
 * @param {string} email - Email a validar
 * @returns {boolean} - true si es válido
 */
function validarEmail(email) {
    if (!email) return false;
    
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

/**
 * Validar teléfono guatemalteco (8 dígitos)
 * @param {string} telefono - Teléfono a validar
 * @returns {boolean} - true si es válido
 */
function validarTelefono(telefono) {
    if (!telefono) return false;
    
    // Eliminar espacios, guiones y paréntesis
    const telefonoLimpio = telefono.replace(/[\s\-\(\)]/g, '');
    
    // Validar que tenga 8 dígitos
    const regex = /^\d{8}$/;
    return regex.test(telefonoLimpio);
}

/**
 * Validar NIT guatemalteco (formato: 12345678-9 o 123456789)
 * @param {string} nit - NIT a validar
 * @returns {boolean} - true si es válido
 */
function validarNIT(nit) {
    if (!nit) return false;
    
    // Eliminar guiones
    const nitLimpio = nit.replace(/-/g, '');
    
    // Validar que tenga entre 7 y 9 dígitos
    const regex = /^\d{7,9}$/;
    return regex.test(nitLimpio);
}

/**
 * Validar que un monto sea positivo
 * @param {number|string} monto - Monto a validar
 * @returns {boolean} - true si es válido
 */
function validarMontoPositivo(monto) {
    const numero = parseFloat(monto);
    return !isNaN(numero) && numero > 0;
}

/**
 * Validar que un campo no esté vacío
 * @param {string} valor - Valor a validar
 * @returns {boolean} - true si NO está vacío
 */
function validarCampoRequerido(valor) {
    if (valor === null || valor === undefined) return false;
    return String(valor).trim().length > 0;
}

// ============================================================================
// 4. FUNCIONES DE FORMULARIOS
// ============================================================================

/**
 * Limpiar todos los campos de un formulario
 * @param {string} formId - ID del formulario
 */
function limpiarFormulario(formId) {
    const form = document.getElementById(formId);
    if (form) {
        form.reset();
        
        // Limpiar también cualquier mensaje de error
        const errores = form.querySelectorAll('.text-danger, .invalid-feedback');
        errores.forEach(error => error.textContent = '');
        
        // Remover clases de validación
        const campos = form.querySelectorAll('.is-invalid, .is-valid');
        campos.forEach(campo => campo.classList.remove('is-invalid', 'is-valid'));
    }
}

/**
 * Deshabilitar formulario (mientras se procesa)
 * @param {string} formId - ID del formulario
 */
function deshabilitarFormulario(formId) {
    const form = document.getElementById(formId);
    if (form) {
        // Deshabilitar todos los inputs, selects, textareas y botones
        const elementos = form.querySelectorAll('input, select, textarea, button');
        elementos.forEach(elemento => {
            elemento.disabled = true;
        });
    }
}

/**
 * Habilitar formulario (después de procesar)
 * @param {string} formId - ID del formulario
 */
function habilitarFormulario(formId) {
    const form = document.getElementById(formId);
    if (form) {
        // Habilitar todos los inputs, selects, textareas y botones
        const elementos = form.querySelectorAll('input, select, textarea, button');
        elementos.forEach(elemento => {
            elemento.disabled = false;
        });
    }
}

/**
 * Obtener datos de un formulario como objeto
 * @param {string} formId - ID del formulario
 * @returns {object} - Objeto con los datos del formulario
 */
function obtenerDatosFormulario(formId) {
    const form = document.getElementById(formId);
    if (!form) return {};
    
    const formData = new FormData(form);
    const datos = {};
    
    for (const [key, value] of formData.entries()) {
        datos[key] = value;
    }
    
    return datos;
}

// ============================================================================
// 5. FUNCIONES DE TABLAS
// ============================================================================

/**
 * Limpiar el contenido de una tabla (tbody)
 * @param {string} tablaId - ID de la tabla
 */
function limpiarTabla(tablaId) {
    const tabla = document.getElementById(tablaId);
    if (tabla) {
        const tbody = tabla.querySelector('tbody');
        if (tbody) {
            tbody.innerHTML = '';
        }
    }
}

/**
 * Agregar una fila a una tabla
 * @param {string} tablaId - ID de la tabla
 * @param {string} htmlFila - HTML de la fila a agregar
 */
function agregarFilaTabla(tablaId, htmlFila) {
    const tabla = document.getElementById(tablaId);
    if (tabla) {
        const tbody = tabla.querySelector('tbody');
        if (tbody) {
            tbody.insertAdjacentHTML('beforeend', htmlFila);
        }
    }
}

/**
 * Mostrar mensaje cuando una tabla está vacía
 * @param {string} tablaId - ID de la tabla
 * @param {string} mensaje - Mensaje a mostrar (default: "No hay datos disponibles")
 * @param {number} columnas - Número de columnas de la tabla
 */
function mostrarMensajeVacio(tablaId, mensaje = 'No hay datos disponibles', columnas = 5) {
    const tabla = document.getElementById(tablaId);
    if (tabla) {
        const tbody = tabla.querySelector('tbody');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="${columnas}" class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        ${mensaje}
                    </td>
                </tr>
            `;
        }
    }
}

// ============================================================================
// 6. FUNCIONES DE LOADING/SPINNER
// ============================================================================

/**
 * Mostrar spinner de carga global
 */
function mostrarCargando() {
    Swal.fire({
        title: 'Cargando...',
        html: 'Por favor espera',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
}

/**
 * Ocultar spinner de carga global
 */
function ocultarCargando() {
    Swal.close();
}

// ============================================================================
// 7. FUNCIONES UTILITARIAS
// ============================================================================

/**
 * Redirigir a otra página
 * @param {string} url - URL de destino
 */
function redirigir(url) {
    window.location.href = url;
}

/**
 * Obtener parámetro de la URL
 * @param {string} nombre - Nombre del parámetro
 * @returns {string|null} - Valor del parámetro o null
 * 
 * Ejemplo: Si la URL es ver.php?id=5
 * obtenerParametroURL('id') retorna "5"
 */
function obtenerParametroURL(nombre) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(nombre);
}

/**
 * Copiar texto al portapapeles
 * @param {string} texto - Texto a copiar
 */
async function copiarAlPortapapeles(texto) {
    try {
        await navigator.clipboard.writeText(texto);
        mostrarExito('Texto copiado al portapapeles');
    } catch (error) {
        mostrarError('No se pudo copiar el texto');
    }
}

/**
 * Scroll suave al inicio de la página
 */
function scrollAlInicio() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

/**
 * Convertir string a número decimal seguro
 * @param {string|number} valor - Valor a convertir
 * @returns {number} - Número decimal o 0 si es inválido
 */
function convertirADecimal(valor) {
    const numero = parseFloat(valor);
    return isNaN(numero) ? 0 : numero;
}

/**
 * Escapar HTML para prevenir XSS
 * @param {string} texto - Texto a escapar
 * @returns {string} - Texto escapado
 */
function escaparHTML(texto) {
    const div = document.createElement('div');
    div.textContent = texto;
    return div.innerHTML;
}

// ============================================================================
// 8. FUNCIONES DE BADGES Y ESTADO
// ============================================================================

/**
 * Obtener badge de Bootstrap según el estado
 * @param {string} estado - Estado (activo, inactivo, pendiente, completado, etc.)
 * @returns {string} - Clase de Bootstrap para el badge
 */
function obtenerBadgeEstado(estado) {
    const estados = {
        'activo': 'bg-success',
        'inactivo': 'bg-secondary',
        'pendiente': 'bg-warning',
        'en_proceso': 'bg-primary',
        'completado': 'bg-success',
        'entregado': 'bg-info',
        'cancelado': 'bg-danger',
        'vencido': 'bg-danger',
        'liquidado': 'bg-success'
    };
    
    return estados[estado] || 'bg-secondary';
}

/**
 * Obtener texto legible del estado
 * @param {string} estado - Estado en snake_case
 * @returns {string} - Texto formateado
 */
function obtenerTextoEstado(estado) {
    const textos = {
        'activo': 'Activo',
        'inactivo': 'Inactivo',
        'pendiente': 'Pendiente',
        'en_proceso': 'En Proceso',
        'completado': 'Completado',
        'entregado': 'Entregado',
        'cancelado': 'Cancelado',
        'vencido': 'Vencido',
        'liquidado': 'Liquidado'
    };
    
    return textos[estado] || estado;
}

// ============================================================================
// INICIALIZACIÓN
// ============================================================================

console.log('✅ common.js cargado correctamente');