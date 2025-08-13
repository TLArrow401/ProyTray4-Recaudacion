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
$user_departments = $current_user['departments'] ?? [];
$current_department = $current_user['selected_department'] ?? '';
$department_menus = [];

// Obtener menús específicos del departamento si hay uno seleccionado
if (!empty($current_department)) {
    $userModel = new UserModel();
    $department_menus = $userModel->getMenusByDepartment($current_department);
}
?>
        <!-- Menu -->
        <aside id="layout-menu" class="layout-menu menu-vertical menu">
            <div class="app-brand demo">
                <a href="<?php echo url('views/dashboard/dashboard.php'); ?>" class="app-brand-link">
                    <span class="app-brand-logo demo">
                        <img src="<?php echo img('logo.png'); ?>" alt="<?php echo PROJECT_NAME; ?>" width="64" height="64">
                    </span>
                    <span class="app-brand-text demo menu-text fw-semibold ms-2"><?php echo PROJECT_NAME; ?></span>
                </a>

                <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                        <path
                        d="M8.47365 11.7183C8.11707 12.0749 8.11707 12.6531 8.47365 13.0097L12.071 16.607C12.4615 16.9975 12.4615 17.6305 12.071 18.021C11.6805 18.4115 11.0475 18.4115 10.657 18.021L5.83009 13.1941C5.37164 12.7356 5.37164 11.9924 5.83009 11.5339L10.657 6.707C11.0475 6.31653 11.6805 6.31653 12.071 6.707C12.4615 7.09747 12.4615 7.73053 12.071 8.121L8.47365 11.7183Z"
                        fill-opacity="0.9" />
                        <path
                        d="M14.3584 11.8336C14.0654 12.1266 14.0654 12.6014 14.3584 12.8944L18.071 16.607C18.4615 16.9975 18.4615 17.6305 18.071 18.021C17.6805 18.4115 17.0475 18.4115 16.657 18.021L11.6819 13.0459C11.3053 12.6693 11.3053 12.0587 11.6819 11.6821L16.657 6.707C17.0475 6.31653 17.6805 6.31653 18.071 6.707C18.4615 7.09747 18.4615 7.73053 18.071 8.121L14.3584 11.8336Z"
                        fill-opacity="0.4" />
                    </svg>
                </a>
            </div>
            <div class="menu-inner-shadow"></div>

            <ul class="menu-inner py-1">
                <!-- Dashboard -->
                <li class="menu-item <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
                    <a href="<?php echo url('views/dashboard/dashboard.php'); ?>" class="menu-link">
                        <i class="menu-icon icon-base ri ri-home-smile-line"></i>
                        <div data-i18n="Dashboard">Dashboard</div>
                    </a>
                </li>

                <?php if (!empty($user_departments) && count($user_departments) > 1): ?>
                <!-- Selector de Departamento -->
                <li class="menu-header small text-uppercase">
                    <span class="menu-header-text">Departamento</span>
                </li>
                <li class="menu-item">
                    <select class="form-select form-select-sm" id="department-selector" onchange="changeDepartment(this.value)">
                        <?php foreach ($user_departments as $dept): ?>
                            <option value="<?php echo htmlspecialchars($dept['name']); ?>" 
                                    <?php echo ($dept['name'] == $current_department) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dept['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </li>
                <?php endif; ?>

                <?php if (!empty($current_department)): ?>
                <!-- Título del departamento actual -->
                <li class="menu-header small text-uppercase">
                    <span class="menu-header-text"><?php echo htmlspecialchars($current_department); ?></span>
                </li>
                <?php endif; ?>

                <!-- Menús específicos del departamento -->
                <?php if (!empty($department_menus)): ?>
                    <?php foreach ($department_menus as $menu): ?>
                        <?php if (isset($menu['submenu'])): ?>
                            <!-- Menú con submenús -->
                            <li class="menu-item">
                                <a href="javascript:void(0);" class="menu-link menu-toggle">
                                    <i class="menu-icon icon-base <?php echo $menu['icon']; ?>"></i>
                                    <div data-i18n="<?php echo $menu['title']; ?>"><?php echo htmlspecialchars($menu['title']); ?></div>
                                </a>
                                <ul class="menu-sub">
                                    <?php foreach ($menu['submenu'] as $submenu): ?>
                                        <li class="menu-item">
                                            <a href="<?php echo url($submenu['url']); ?>" class="menu-link">
                                                <div data-i18n="<?php echo $submenu['title']; ?>"><?php echo htmlspecialchars($submenu['title']); ?></div>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        <?php else: ?>
                            <!-- Menú simple -->
                            <li class="menu-item">
                                <a href="<?php echo url($menu['url']); ?>" class="menu-link">
                                    <i class="menu-icon icon-base <?php echo $menu['icon']; ?>"></i>
                                    <div data-i18n="<?php echo $menu['title']; ?>"><?php echo htmlspecialchars($menu['title']); ?></div>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Módulos Generales -->
                <li class="menu-header small text-uppercase">
                    <span class="menu-header-text">Configuraciones</span>
                </li>

                <!-- Gestión de Usuarios (para RRHH, administradores y jefes de departamento) -->
                <?php 
                // Verificar si es RRHH o administrador (acceso completo)
                $is_rrhh_or_admin = ($current_department == 'Recursos Humanos' || (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'));
                
                // Verificar si es jefe de departamento
                require_once __DIR__ . '/../../models/UserModel.php';
                $userModel = new UserModel();
                $is_manager = false;
                if (isset($_SESSION['user_id'])) {
                    $is_manager = $userModel->isManager($_SESSION['user_id']);
                }
                
                // Mostrar menú si es RRHH, admin o jefe de departamento
                if ($is_rrhh_or_admin || $is_manager): 
                ?>
                <li class="menu-item 
                    <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">
                    <a href="<?php echo url('views/users/index.php'); ?>" class="menu-link">
                        <i class="menu-icon icon-base ri ri-user-settings-line"></i>
                        <div data-i18n="Usuarios">Usuarios</div>
                    </a>
                </li>
                
                <?php endif; ?>

               
            </ul>
        </aside>

        <script>
        function changeDepartment(departmentName) {
            // Enviar petición AJAX para cambiar departamento en sesión
            fetch('<?php echo url("ajax/change-department.php"); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    department: departmentName
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Recargar la página para mostrar nuevos menús
                    window.location.reload();
                } else {
                    console.error('Error cambiando departamento:', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        function showManagerMenuInfo() {
            alert('Como jefe de departamento, tienes acceso de solo lectura a la información de usuarios de tu departamento. Para realizar modificaciones, contacta al departamento de Recursos Humanos.');
        }
        </script>

        <div class="menu-mobile-toggler d-xl-none rounded-1">
            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large text-bg-secondary p-2 rounded-1">
                <i class="ri ri-menu-line icon-base"></i>
                <i class="ri ri-arrow-right-s-line icon-base"></i>
            </a>
        </div>
        <!-- / Menu -->