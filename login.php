<?php
// ================================================
// PAGINA DE LOGIN
// ================================================

require_once 'config.php';
require_once 'includes/db.php';
require_once 'includes/funciones.php';
require_once 'includes/auth.php';

// Si ya esta autenticado, redirigir al dashboard
if (esta_autenticado()) {
    redirigir('dashboard.php');
}

// Procesar el formulario de login
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = limpiar_texto($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Por favor, completa todos los campos';
    } else {
        $usuario = intentar_login($email, $password);
        
        if ($usuario) {
            iniciar_sesion($usuario);
            mensaje_exito('Bienvenido, ' . $usuario['nombre']);
            redirigir('dashboard.php');
        } else {
            $error = 'Email o contrasena incorrectos';
        }
    }
}

$titulo_pagina = 'Iniciar Sesion';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $titulo_pagina; ?> - <?php echo SISTEMA_NOMBRE; ?></title>
    
    <link href="<?php echo BASE_URL; ?>assets/css/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>assets/css/bootstrap-icons/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@600;700&display=swap" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>assets/css/custom.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, var(--color-azul) 0%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-image: repeating-linear-gradient(45deg, transparent, transparent 35px, rgba(212, 175, 55, 0.03) 35px, rgba(212, 175, 55, 0.03) 70px);
            pointer-events: none;
        }
        
        .login-container {
            background: var(--color-blanco);
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
            border: 3px solid var(--color-dorado);
            position: relative;
            z-index: 1;
        }
        
        .login-header {
            background: var(--color-azul);
            color: var(--color-blanco);
            padding: 40px 30px 30px;
            text-align: center;
            position: relative;
            border-bottom: 4px solid var(--color-dorado);
        }
        
        .login-header::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: var(--color-plateado);
        }
        
        .logo-container {
            width: 100px;
            height: 100px;
            margin: 0 auto 20px;
            background: var(--color-blanco);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.4);
            border: 3px solid var(--color-dorado);
        }
        
        .logo-container img {
            width: 70px;
            height: 70px;
            object-fit: contain;
        }
        
        .logo-container i {
            font-size: 50px;
            color: var(--color-dorado);
        }
        
        .login-header h2 {
            margin: 0;
            font-size: 26px;
            font-weight: 700;
            font-family: 'Montserrat', sans-serif;
            letter-spacing: 1px;
        }
        
        .login-header p {
            margin: 8px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
            font-weight: 300;
        }
        
        .login-body {
            padding: 35px 30px;
            background: var(--color-blanco);
        }
        
        .btn-login {
            background: var(--color-dorado);
            border: 2px solid var(--color-dorado);
            border-radius: 6px;
            padding: 14px;
            font-size: 16px;
            font-weight: 600;
            color: var(--color-blanco);
            width: 100%;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .btn-login:hover {
            background: #c19b2e;
            border-color: #c19b2e;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(212, 175, 55, 0.3);
            color: var(--color-blanco);
        }

        .btn-ver-password {
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            border-left: none;
            cursor: pointer;
            padding: 0 14px;
            transition: background 0.2s;
        }

        .btn-ver-password:hover {
            background: #e5e7eb;
        }
        
        .login-footer {
            padding: 20px 30px;
            background: #f9fafb;
            text-align: center;
            font-size: 13px;
            color: var(--color-gris);
            border-top: 1px solid #e5e7eb;
        }
        
        .security-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: var(--color-gris);
        }
        
        .security-badge i {
            color: var(--color-dorado);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo-container">
                <?php if (file_exists('assets/img/logo-torre-fuerte.png')): ?>
                    <img src="assets/img/logo-torre-fuerte.png" alt="Logo Torre Fuerte">
                <?php else: ?>
                    <i class="bi bi-gem"></i>
                <?php endif; ?>
            </div>
            <h2><?php echo SISTEMA_NOMBRE; ?></h2>
            <p>Sistema de Gestion Integral</p>
        </div>
        
        <div class="login-body">
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php
            $mensaje_error = obtener_mensaje_error();
            if ($mensaje_error):
            ?>
                <div class="alert alert-warning" role="alert">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <?php echo $mensaje_error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="email" class="form-label">
                        <i class="bi bi-envelope me-1"></i> Correo Electronico
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-person"></i>
                        </span>
                        <input 
                            type="email" 
                            class="form-control" 
                            id="email" 
                            name="email" 
                            placeholder="usuario@ejemplo.com"
                            required
                            autofocus
                            value="<?php echo htmlspecialchars($email ?? ''); ?>"
                        >
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label">
                        <i class="bi bi-lock me-1"></i> Contrasena
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-key"></i>
                        </span>
                        <input 
                            type="password" 
                            class="form-control" 
                            id="password" 
                            name="password" 
                            placeholder="••••••••"
                            required
                        >
                        <button type="button" class="btn-ver-password" onclick="togglePassword()" title="Ver contrasena">
                            <i class="bi bi-eye" id="iconoPassword"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-login">
                    <i class="bi bi-box-arrow-in-right me-2"></i>
                    Iniciar Sesion
                </button>
            </form>
        </div>
        
        <div class="login-footer">
            <span class="security-badge">
                <i class="bi bi-shield-check"></i>
                Sistema protegido - Version <?php echo SISTEMA_VERSION; ?>
            </span>
        </div>
    </div>
    
    <script src="<?php echo BASE_URL; ?>assets/js/bootstrap/bootstrap.bundle.min.js"></script>
    
    <script>
    function togglePassword() {
        const input = document.getElementById('password');
        const icono = document.getElementById('iconoPassword');
        
        if (input.type === 'password') {
            input.type = 'text';
            icono.className = 'bi bi-eye-slash';
        } else {
            input.type = 'password';
            icono.className = 'bi bi-eye';
        }
    }
    </script>
</body>
</html>