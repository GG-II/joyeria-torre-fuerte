    <!-- Footer -->
    <footer class="footer mt-auto">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p>
                        <i class="bi bi-shield-check me-2"></i>
                        &copy; <?php echo date('Y'); ?> <?php echo SISTEMA_NOMBRE; ?> - Todos los derechos reservados
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <p>
                        <i class="bi bi-code-square me-2"></i>
                        Versión <?php echo SISTEMA_VERSION; ?>
                        <?php if (isset($_SESSION['usuario_sucursal_nombre']) && $_SESSION['usuario_sucursal_nombre']): ?>
                        <span class="ms-3">
                            <i class="bi bi-geo-alt me-1"></i>
                            <?php echo $_SESSION['usuario_sucursal_nombre']; ?>
                        </span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS Local -->
    <script src="<?php echo BASE_URL; ?>assets/js/bootstrap/bootstrap.bundle.min.js"></script>
    
    <!-- JavaScript Personalizado -->
    <script src="<?php echo BASE_URL; ?>assets/js/app.js"></script>
    
    <!-- Scripts adicionales específicos de página -->
    <?php if (isset($scripts_adicionales)): ?>
        <?php echo $scripts_adicionales; ?>
    <?php endif; ?>
</body>
</html>
