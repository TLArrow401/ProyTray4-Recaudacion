<?php
/**
 * Archivo principal del proyecto Seramer
 * Maneja la redirección basada en el estado de la sesión
 */

// Incluir configuración de la aplicación
require_once __DIR__ . '/config/app.php';

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Función para verificar si el usuario está autenticado
 * @return bool
 */
function isUserAuthenticated() {
    // Verificar si existe la sesión y tiene los datos necesarios
    return isset($_SESSION['user_id']) && 
           isset($_SESSION['user_email']) && 
           !empty($_SESSION['user_id']);
}

/**
 * Función para verificar si la sesión es válida (no expirada)
 * @return bool
 */
function isSessionValid() {
    // Verificar si existe el timestamp de la sesión
    if (!isset($_SESSION['last_activity'])) {
        return false;
    }
    
    // Tiempo de expiración en segundos (30 minutos = 1800 segundos)
    $session_timeout = 1800;
    
    // Verificar si la sesión no ha expirado
    if ((time() - $_SESSION['last_activity']) > $session_timeout) {
        // Sesión expirada, destruir sesión
        session_unset();
        session_destroy();
        return false;
    }
    
    // Actualizar timestamp de actividad
    $_SESSION['last_activity'] = time();
    return true;
}

/**
 * Función para obtener información del usuario logueado
 * @return array|null
 */
function getCurrentUser() {
    if (isUserAuthenticated() && isSessionValid()) {
        return [
            'id' => $_SESSION['user_id'] ?? null,
            'email' => $_SESSION['user_email'] ?? null,
            'name' => $_SESSION['user_name'] ?? null,
            'role' => $_SESSION['user_role'] ?? 'user',
            'last_activity' => $_SESSION['last_activity'] ?? null
        ];
    }
    return null;
}

// Lógica principal de redirección
try {
    
    // Verificar si el usuario está autenticado y la sesión es válida
    if (isUserAuthenticated() && isSessionValid()) {
        
        // Usuario autenticado - redirigir al dashboard
        $dashboardUrl = url('views/dashboard/dashboard.php');
        
        if (DEBUG_MODE) {
            $user = getCurrentUser();
            error_log("Usuario autenticado: " . $user['email'] . " - Redirigiendo a dashboard");
        }
        
        header("Location: $dashboardUrl");
        exit();
        
    } else {
        
        // Usuario no autenticado o sesión expirada - redirigir al login
        $loginUrl = url('views/auth/login.php');
        
        if (DEBUG_MODE) {
            error_log("Usuario no autenticado - Redirigiendo a login");
        }
        
        // Limpiar cualquier sesión residual
        if (isset($_SESSION)) {
            session_unset();
            session_destroy();
        }
        
        header("Location: $loginUrl");
        exit();
        
    }
    
} catch (Exception $e) {
    
    // En caso de error, redirigir al login por seguridad
    if (DEBUG_MODE) {
        error_log("Error en index.php: " . $e->getMessage());
    }
    
    $loginUrl = url('views/auth/login.php');
    header("Location: $loginUrl");
    exit();
    
}

// Esta línea nunca debería ejecutarse, pero por seguridad
$loginUrl = url('views/auth/login.php');
header("Location: $loginUrl");
exit();
?>
