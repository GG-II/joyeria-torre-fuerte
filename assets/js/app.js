/**
 * ================================================
 * JAVASCRIPT PERSONALIZADO - JOYERÍA TORRE FUERTE
 * Sistema de Gestión Integral
 * ================================================
 */

// ================================================
// 1. INICIALIZACIÓN AL CARGAR LA PÁGINA
// ================================================
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Sistema Joyería Torre Fuerte cargado correctamente');
    
    // Inicializar tooltips de Bootstrap
    initTooltips();
    
    // Auto-cerrar alertas después de 5 segundos
    autoCloseAlerts();
    
    // Confirmar acciones destructivas
    confirmDestructiveActions();
});

// ================================================
// 2. TOOLTIPS
// ================================================
function initTooltips() {
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
}

// ================================================
// 3. ALERTAS AUTO-CERRAR
// ================================================
function autoCloseAlerts() {
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000); // 5 segundos
    });
}

// ================================================
// 4. CONFIRMACIONES
// ================================================
function confirmDestructiveActions() {
    // Botones de eliminar
    const deleteButtons = document.querySelectorAll('[data-action="delete"], .btn-delete');
    
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            if (!confirm('¿Estás seguro de que deseas eliminar este elemento? Esta acción no se puede deshacer.')) {
                e.preventDefault();
                return false;
            }
        });
    });
    
    // Botones de anular
    const cancelButtons = document.querySelectorAll('[data-action="cancel"], .btn-cancel-action');
    
    cancelButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            if (!confirm('¿Estás seguro de que deseas anular esta operación?')) {
                e.preventDefault();
                return false;
            }
        });
    });
}

// ================================================
// 5. FORMATO DE MONEDA
// ================================================
function formatCurrency(amount) {
    return new Intl.NumberFormat('es-GT', {
        style: 'currency',
        currency: 'GTQ'
    }).format(amount);
}

// ================================================
// 6. FORMATO DE FECHA
// ================================================
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('es-GT', options);
}

// ================================================
// 7. MOSTRAR LOADING SPINNER
// ================================================
function showLoading(button) {
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<span class="loading-spinner me-2"></span> Procesando...';
    button.dataset.originalText = originalText;
}

function hideLoading(button) {
    button.disabled = false;
    button.innerHTML = button.dataset.originalText;
}

// ================================================
// 8. VALIDACIÓN DE FORMULARIOS
// ================================================
function validateForm(formId) {
    const form = document.getElementById(formId);
    
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return false;
    }
    
    return true;
}

// ================================================
// 9. BÚSQUEDA EN TABLAS (FILTRO CLIENTE)
// ================================================
function filterTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const filter = input.value.toUpperCase();
    const table = document.getElementById(tableId);
    const tr = table.getElementsByTagName('tr');
    
    for (let i = 1; i < tr.length; i++) {
        let found = false;
        const td = tr[i].getElementsByTagName('td');
        
        for (let j = 0; j < td.length; j++) {
            if (td[j]) {
                const txtValue = td[j].textContent || td[j].innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }
        }
        
        tr[i].style.display = found ? '' : 'none';
    }
}

// ================================================
// 10. COPIAR AL PORTAPAPELES
// ================================================
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        showNotification('Copiado al portapapeles', 'success');
    }).catch(function(err) {
        console.error('Error al copiar:', err);
    });
}

// ================================================
// 11. NOTIFICACIONES TOAST (OPCIONAL)
// ================================================
function showNotification(message, type = 'info') {
    // Implementar con Bootstrap Toast si es necesario
    console.log(`[${type.toUpperCase()}] ${message}`);
}

// ================================================
// 12. DEBOUNCE (para búsquedas en tiempo real)
// ================================================
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// ================================================
// 13. EXPORTAR FUNCIONES GLOBALES
// ================================================
window.formatCurrency = formatCurrency;
window.formatDate = formatDate;
window.showLoading = showLoading;
window.hideLoading = hideLoading;
window.validateForm = validateForm;
window.filterTable = filterTable;
window.copyToClipboard = copyToClipboard;
window.showNotification = showNotification;
window.debounce = debounce;
