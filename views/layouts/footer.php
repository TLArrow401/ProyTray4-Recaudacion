<?php 
// Incluir la configuración al inicio de cada vista
require_once __DIR__ . '/../../config/app.php';
?>
                    <!-- Footer -->
                    <footer class="content-footer footer bg-footer-theme">
                        <div class="container-xxl">
                            <div class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
                                <div class="mb-2 mb-md-0">
                                    &#169;
                                    <script>
                                    document.write(new Date().getFullYear());
                                    </script> 
                                    <?php echo PROJECT_NAME; ?>
                                </div>
                                <div class="d-none d-lg-inline-block">
                                    <a
                                        href="#"
                                        target="_blank"
                                        class="footer-link me-4"
                                    >Documentación</a>
                                </div>
                            </div>
                        </div>
                    </footer>
                    <!-- / Footer -->

                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
                <!-- / Layout page -->
            </div>
            <!-- Overlay -->
            <div class="layout-overlay layout-menu-toggle"></div>

            <!-- Drag Target Area To SlideIn Menu On Small Screens -->
            <div class="drag-target"></div>
        </div>
        <!-- / Layout wrapper -->

        <!-- Core JS -->

        <!-- build:js assets/vendor/js/theme.js  -->

        <script src="<?php echo vendor('libs/jquery/jquery.js'); ?>"></script>
        <script src="<?php echo vendor('libs/popper/popper.js'); ?>"></script>
        <script src="<?php echo vendor('js/bootstrap.js'); ?>"></script>
        <script src="<?php echo vendor('libs/node-waves/node-waves.js'); ?>"></script>
        <script src="<?php echo vendor('libs/pickr/pickr.js'); ?>"></script>
        <script src="<?php echo vendor('libs/perfect-scrollbar/perfect-scrollbar.js'); ?>"></script>
        <script src="<?php echo vendor('libs/hammer/hammer.js'); ?>"></script>
        <script src="<?php echo vendor('js/menu.js'); ?>"></script>
        <script src="<?php echo js('main.js'); ?>"></script>
    </body>
</html>
