<?php
/**
 * Dashboard principal - Requiere autenticación
 */

// Incluir configuración
require_once __DIR__ . '/../../config/app.php';

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // No autenticado - redirigir al login
    $loginUrl = url('views/auth/login.php');
    header("Location: $loginUrl");
    exit();
}

// Verificar si la sesión es válida (no expirada)
$session_timeout = 1800; // 30 minutos
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $session_timeout) {
    // Sesión expirada
    session_unset();
    session_destroy();
    $loginUrl = url('views/auth/login.php');
    header("Location: $loginUrl");
    exit();
}

// Actualizar timestamp de actividad
$_SESSION['last_activity'] = time();

// Obtener datos del usuario
$user = [
    'id' => $_SESSION['user_id'] ?? null,
    'email' => $_SESSION['user_email'] ?? null,
    'name' => $_SESSION['user_name'] ?? 'Usuario',
    'role' => $_SESSION['user_role'] ?? 'user'
];
?>
<?php include __DIR__ . '/../layouts/header.php'; ?>
<?php include __DIR__ . '/../layouts/navigation.php'; ?>
<?php include __DIR__ . '/../layouts/navigation-top.php'; ?>


            <!-- Content -->
            <div class="container-xxl flex-grow-1 container-p-y">
                <div class="row g-6">
                    <div class="col-md-12 col-xxl-8">
                        <div class="card">
                            <div class="d-flex align-items-end row">
                                <div class="col-md-6 order-2 order-md-1">
                                    <div class="card-body">
                                        <h4 class="card-title mb-4">Bienvenido <span class="fw-bold"><?php echo $current_user['full_name'] ?? 'Usuario'; ?></span> 🎉</h4>
                                        
                                    </div>
                                </div>
                                <div class="col-md-6 text-center text-md-end order-1 order-md-2">
                                    <div class="card-body pb-0 px-0 pt-2">
                                    <img src="<?php echo img('illustrations/illustration-john-light.png'); ?>" height="186" class="scaleX-n1-rtl" alt="View Profile" data-app-light-img="illustrations/illustration-john-light.png" data-app-dark-img="illustrations/illustration-john-dark.png" style="visibility: visible;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                
            </div>
            <!-- / Content -->

            



<?php include __DIR__ . '/../layouts/footer.php'; ?>
