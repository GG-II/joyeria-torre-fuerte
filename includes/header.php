<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Sistema de Gestión Integral - Joyería Torre Fuerte">
    <meta name="author" content="Joyería Torre Fuerte">
    
    <!-- Título de la página -->
    <title><?php echo isset($titulo_pagina) ? $titulo_pagina . ' - ' : ''; ?><?php echo SISTEMA_NOMBRE; ?></title>
    
    <!-- Bootstrap CSS Local -->
    <link href="<?php echo BASE_URL; ?>assets/css/bootstrap/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons Local -->
    <link href="<?php echo BASE_URL; ?>assets/css/bootstrap-icons/bootstrap-icons.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@600;700&display=swap" rel="stylesheet">
    
    <!-- CSS Personalizado -->
    <link href="<?php echo BASE_URL; ?>assets/css/custom.css" rel="stylesheet">
    
    <!-- Favicon (opcional) -->
    <?php if (file_exists(__DIR__ . '/../assets/img/favicon.ico')): ?>
    <link rel="icon" type="image/x-icon" href="<?php echo BASE_URL; ?>assets/img/favicon.ico">
    <?php endif; ?>
</head>
<body>
