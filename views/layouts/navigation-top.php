<?php 
// Incluir la configuración al inicio de cada vista
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

// Inicializar el controlador de autenticación
$authController = new AuthController();

// Verificar autenticación
$authController->requireAuth();

// Obtener datos del usuario actual
$current_user = $authController->getCurrentUser();
?>
<!-- Layout container -->
<div class="layout-page">
    <!-- Navbar -->
    <nav class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
        <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 d-xl-none">
            <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
                <i class="icon-base ri ri-menu-line icon-22px"></i>
            </a>
        </div>

        <div class="navbar-nav-right d-flex align-items-center justify-content-end" id="navbar-collapse">
            <div class="navbar-nav align-items-center">
                
            </div>

            <ul class="navbar-nav flex-row align-items-center ms-md-auto">
                <!-- User -->
                <li class="nav-item navbar-dropdown dropdown-user dropdown">
                        <a
                            class="nav-link dropdown-toggle hide-arrow p-0"
                            href="javascript:void(0);"
                            data-bs-toggle="dropdown">
                            <div class="avatar avatar-online">
                                <img src="<?php echo img('avatars/1.png'); ?>" alt="avatar" class="rounded-circle" />
                            </div>
                        </a>

                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="#">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="avatar avatar-online">
                                        <img
                                            src="<?php echo img('avatars/1.png'); ?>"
                                            alt="avatar"
                                            class="w-px-40 h-auto rounded-circle" />
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0"><?php echo $current_user['full_name'] ?? 'Usuario'; ?></h6>
                                        <small class="text-body-secondary"><?php echo $current_user['selected_department'] ?? $current_user['primary_department'] ?? 'Sin departamento'; ?></small>
                                    </div>
                                </div>
                            </a>
                        </li>
                        
                        <li class="dropdown-divider my-1"></li>
                        
                        <li>
                            <div class="d-grid px-4 pt-2 pb-1">
                                <a class="btn btn-danger d-flex" href="<?php echo url('views/auth/logout.php'); ?>">
                                    <small class="align-middle">Cerrar Sesión</small>
                                    <i class="icon-base ri ri-logout-box-r-line ms-2 icon-16px"></i>
                                </a>
                            </div>
                        </li>
                    </ul>
                </li>
                 <!--/ User -->
            </ul>
    </div>
</nav>
<!-- / Navbar -->

<!-- Content wrapper -->
<div class="content-wrapper">