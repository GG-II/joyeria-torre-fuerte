/**
 * ================================================
 * SESSION TIMEOUT - JOYERÍA TORRE FUERTE
 * ================================================
 * Detecta inactividad del usuario, muestra un modal
 * de advertencia con cuenta regresiva y redirige al login.
 *
 * Configuración:
 *  - SESSION_TIMEOUT_SECONDS: debe coincidir con SESSION_TIMEOUT en config.php (3600)
 *  - ADVERTENCIA_SEGUNDOS: cuántos segundos antes del cierre mostrar el aviso
 */

(function () {
    'use strict';

    // ================================================
    // CONFIGURACIÓN (debe coincidir con config.php)
    // ================================================
    const SESSION_TIMEOUT_SEGUNDOS = 3600;  // 1 hora — igual a SESSION_TIMEOUT en config.php
    const ADVERTENCIA_SEGUNDOS     = 60;    // Mostrar aviso 60s antes de cerrar
    const LOGIN_URL                = '/joyeria-torre-fuerte/login';
    const LOGOUT_URL               = '/joyeria-torre-fuerte/logout';
    const CHECK_INTERVAL_MS        = 10000; // Verificar cada 10 segundos

    // ================================================
    // ESTADO INTERNO
    // ================================================
    let ultimaActividad    = Date.now();
    let modalMostrado      = false;
    let cuentaAtras        = null;
    let checkInterval      = null;
    let segundosRestantes  = ADVERTENCIA_SEGUNDOS;

    // ================================================
    // DETECTAR ACTIVIDAD DEL USUARIO
    // ================================================
    const eventosActividad = ['mousemove', 'mousedown', 'keypress', 'touchstart', 'scroll', 'click'];

    function registrarActividad() {
        if (!modalMostrado) {
            ultimaActividad = Date.now();
        }
    }

    eventosActividad.forEach(function (evento) {
        document.addEventListener(evento, registrarActividad, { passive: true });
    });

    // ================================================
    // CREAR EL MODAL EN EL DOM
    // ================================================
    function crearModal() {
        if (document.getElementById('sessionTimeoutModal')) return;

        const modal = document.createElement('div');
        modal.id = 'sessionTimeoutModal';
        modal.style.cssText = `
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            z-index: 99999;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(3px);
            justify-content: center;
            align-items: center;
        `;

        modal.innerHTML = `
            <div style="
                background: #fff;
                border-radius: 12px;
                padding: 2rem 2.5rem;
                max-width: 420px;
                width: 90%;
                text-align: center;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                border-top: 5px solid #D4AF37;
            ">
                <div style="font-size: 3rem; margin-bottom: 0.5rem;">⏰</div>
                <h4 style="color: #1a1a1a; margin-bottom: 0.5rem; font-weight: 700;">
                    Sesión por expirar
                </h4>
                <p style="color: #555; margin-bottom: 1rem; font-size: 0.95rem;">
                    Por seguridad, tu sesión se cerrará automáticamente por inactividad.
                </p>
                <div style="
                    font-size: 2.8rem;
                    font-weight: 800;
                    color: #1e3a8a;
                    margin-bottom: 1.5rem;
                    font-variant-numeric: tabular-nums;
                " id="sessionCountdown">60</div>
                <div style="display: flex; gap: 1rem; justify-content: center;">
                    <button
                        id="btnContinuarSesion"
                        style="
                            background: #D4AF37;
                            color: #1a1a1a;
                            border: none;
                            padding: 0.6rem 1.6rem;
                            border-radius: 8px;
                            font-weight: 600;
                            font-size: 0.95rem;
                            cursor: pointer;
                            transition: opacity 0.2s;
                        "
                        onmouseover="this.style.opacity='0.85'"
                        onmouseout="this.style.opacity='1'"
                    >
                        ✅ Continuar sesión
                    </button>
                    <button
                        id="btnCerrarSesion"
                        style="
                            background: #1e3a8a;
                            color: #fff;
                            border: none;
                            padding: 0.6rem 1.6rem;
                            border-radius: 8px;
                            font-weight: 600;
                            font-size: 0.95rem;
                            cursor: pointer;
                            transition: opacity 0.2s;
                        "
                        onmouseover="this.style.opacity='0.85'"
                        onmouseout="this.style.opacity='1'"
                    >
                        🚪 Cerrar sesión
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        // Botón continuar: resetear actividad y cerrar modal
        document.getElementById('btnContinuarSesion').addEventListener('click', function () {
            continuarSesion();
        });

        // Botón cerrar sesión manualmente
        document.getElementById('btnCerrarSesion').addEventListener('click', function () {
            cerrarSesion();
        });
    }

    // ================================================
    // MOSTRAR MODAL CON CUENTA REGRESIVA
    // ================================================
    function mostrarModal() {
        if (modalMostrado) return;
        modalMostrado = true;
        segundosRestantes = ADVERTENCIA_SEGUNDOS;

        const modal = document.getElementById('sessionTimeoutModal');
        const countdown = document.getElementById('sessionCountdown');

        modal.style.display = 'flex';
        countdown.textContent = segundosRestantes;

        cuentaAtras = setInterval(function () {
            segundosRestantes--;
            countdown.textContent = segundosRestantes;

            // Color de urgencia
            if (segundosRestantes <= 10) {
                countdown.style.color = '#dc2626';
            } else if (segundosRestantes <= 30) {
                countdown.style.color = '#f59e0b';
            }

            if (segundosRestantes <= 0) {
                cerrarSesion();
            }
        }, 1000);
    }

    // ================================================
    // OCULTAR MODAL Y REINICIAR
    // ================================================
    function continuarSesion() {
        modalMostrado = false;
        ultimaActividad = Date.now();

        if (cuentaAtras) {
            clearInterval(cuentaAtras);
            cuentaAtras = null;
        }

        const modal = document.getElementById('sessionTimeoutModal');
        if (modal) {
            modal.style.display = 'none';
            // Resetear color del countdown
            const countdown = document.getElementById('sessionCountdown');
            if (countdown) countdown.style.color = '#1e3a8a';
        }

        // Hacer ping al servidor para renovar la sesión PHP
        fetch('/joyeria-torre-fuerte/api/auth/ping.php', {
            method: 'POST',
            credentials: 'same-origin'
        }).catch(function () {
            // Si falla el ping, igual continuar localmente
        });
    }

    // ================================================
    // CERRAR SESIÓN Y REDIRIGIR
    // ================================================
    function cerrarSesion() {
        if (cuentaAtras) clearInterval(cuentaAtras);
        if (checkInterval) clearInterval(checkInterval);
        window.location.href = LOGOUT_URL + '?timeout=1';
    }

    // ================================================
    // LOOP PRINCIPAL: VERIFICAR INACTIVIDAD
    // ================================================
    function verificarInactividad() {
        const tiempoInactivo = (Date.now() - ultimaActividad) / 1000; // en segundos
        const tiempoParaCierre = SESSION_TIMEOUT_SEGUNDOS - tiempoInactivo;

        if (tiempoParaCierre <= 0) {
            // Ya expiró, redirigir directo
            cerrarSesion();
        } else if (tiempoParaCierre <= ADVERTENCIA_SEGUNDOS && !modalMostrado) {
            // Quedan menos de X segundos, mostrar aviso
            mostrarModal();
        }
    }

    // ================================================
    // INICIALIZAR
    // ================================================
    document.addEventListener('DOMContentLoaded', function () {
        crearModal();
        checkInterval = setInterval(verificarInactividad, CHECK_INTERVAL_MS);
    });

})();