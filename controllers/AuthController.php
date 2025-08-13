<?php

require_once __DIR__ . '/../models/UserModel.php';

class AuthController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new UserModel();
    }

    /**
     * Procesar login del usuario
     * @param string $username
     * @param string $password
     * @return array
     */
    public function login($username, $password) {
        try {
            // Validar datos de entrada
            if (empty($username) || empty($password)) {
                return [
                    'success' => false,
                    'message' => 'Usuario y contraseña son requeridos'
                ];
            }

            // Intentar autenticar usuario
            $user_data = $this->userModel->authenticate($username, $password);
            
            if (!$user_data) {
                return [
                    'success' => false,
                    'message' => 'Credenciales inválidas'
                ];
            }

            // Verificar que el usuario esté activo
            if ($user_data['status'] !== 'active') {
                return [
                    'success' => false,
                    'message' => 'Usuario inactivo. Contacte al administrador.'
                ];
            }

            // Verificar si la sesión ya está iniciada, si no, iniciarla
            if (session_status() === PHP_SESSION_NONE) {
                if (session_status() === PHP_SESSION_NONE) {
                if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
            }
            }
            
            // Guardar datos del usuario en sesión
            $_SESSION['user_id'] = $user_data['id'];
            $_SESSION['username'] = $user_data['username'];
            $_SESSION['user_email'] = $user_data['email'];
            $_SESSION['user_full_name'] = trim(($user_data['first_name'] ?? '') . ' ' . ($user_data['last_name'] ?? ''));
            $_SESSION['staff_id'] = $user_data['staff_id'];
            
            // Información del departamento principal
            if (!empty($user_data['departments'])) {
                $_SESSION['primary_department_id'] = $user_data['departments'][0]['id'];
                $_SESSION['primary_department_name'] = $user_data['departments'][0]['name'];
                $_SESSION['selected_department'] = $user_data['departments'][0]['name'];
                
                // Guardar todos los departamentos del usuario
                $_SESSION['user_departments'] = $user_data['departments'];
            }

            // Información adicional del staff si existe
            if ($user_data['department_id']) {
                $_SESSION['department_id'] = $user_data['department_id'];
                $_SESSION['department_name'] = $user_data['department_name'];
                $_SESSION['shift_type'] = $user_data['shift_type'];
            }

            // Registrar auditoría de login
            $this->logAuditAction($user_data['id'], 'login', 'users', $user_data['id'], 
                                null, ['login_time' => date('Y-m-d H:i:s')]);

            return [
                'success' => true,
                'message' => 'Login exitoso',
                'user' => [
                    'id' => $user_data['id'],
                    'username' => $user_data['username'],
                    'full_name' => $_SESSION['user_full_name'],
                    'department' => $_SESSION['primary_department_name'] ?? 'Sin asignar',
                    'departments' => $user_data['departments']
                ],
                'redirect' => '../views/dashboard/dashboard.php'
            ];

        } catch (Exception $e) {
            error_log("Error en login: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno del servidor'
            ];
        }
    }

    /**
     * Procesar el formulario de login
     * Maneja validaciones, cookies y redirecciones
     */
    public function processLogin() {
        try {
            // Verificar que sea una petición POST
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: ../views/auth/login.php');
                exit;
            }

            // Obtener datos del formulario
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $remember_me = isset($_POST['remember_me']);

            // Procesar login
            $result = $this->login($username, $password);

            if ($result['success']) {
                // Si el login fue exitoso, manejar "recordarme"
                if ($remember_me) {
                    // Crear cookie que dure 30 días
                    setcookie('remember_username', $username, time() + (30 * 24 * 60 * 60), '/');
                } else {
                    // Eliminar cookie si existe
                    if (isset($_COOKIE['remember_username'])) {
                        setcookie('remember_username', '', time() - 3600, '/');
                    }
                }
                
                // Redireccionar al dashboard
                header('Location: ' . $result['redirect']);
                exit;
            } else {
                // Si falló, redireccionar al login con mensaje de error
                if (session_status() === PHP_SESSION_NONE) {
                    if (session_status() === PHP_SESSION_NONE) {
                if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
            }
                }
                $_SESSION['login_error'] = $result['message'];
                header('Location: ../views/auth/login.php');
                exit;
            }

        } catch (Exception $e) {
            error_log("Error en processLogin: " . $e->getMessage());
            if (session_status() === PHP_SESSION_NONE) {
                if (session_status() === PHP_SESSION_NONE) {
                if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
            }
            }
            $_SESSION['login_error'] = 'Error interno del servidor';
            header('Location: ../views/auth/login.php');
            exit;
        }
    }

    /**
     * Cerrar sesión del usuario
     * @return array
     */
    public function logout() {
        try {
            if (session_status() === PHP_SESSION_NONE) {
                if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
            }
            
            // Registrar auditoría de logout si hay usuario
            if (isset($_SESSION['user_id'])) {
                $this->logAuditAction($_SESSION['user_id'], 'logout', 'users', $_SESSION['user_id'], 
                                    null, ['logout_time' => date('Y-m-d H:i:s')]);
            }

            // Limpiar todas las variables de sesión
            $_SESSION = array();

            // Destruir la cookie de sesión si existe
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }

            // Destruir la sesión
            session_destroy();

            return [
                'success' => true,
                'message' => 'Sesión cerrada exitosamente',
                'redirect' => '../auth/login.php'
            ];

        } catch (Exception $e) {
            error_log("Error en logout: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error cerrando sesión'
            ];
        }
    }

    /**
     * Verificar si el usuario está autenticado
     * @return bool
     */
    public function isAuthenticated() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Obtener datos del usuario actual de la sesión
     * @return array|null
     */
    public function getCurrentUser() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!$this->isAuthenticated()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'email' => $_SESSION['user_email'],
            'full_name' => $_SESSION['user_full_name'],
            'staff_id' => $_SESSION['staff_id'] ?? null,
            'department_id' => $_SESSION['department_id'] ?? null,
            'department_name' => $_SESSION['department_name'] ?? null,
            'primary_department' => $_SESSION['primary_department_name'] ?? null,
            'selected_department' => $_SESSION['selected_department'] ?? null,
            'departments' => $_SESSION['user_departments'] ?? []
        ];
    }

    /**
     * Cambiar departamento activo del usuario
     * @param string $department_name
     * @return array
     */
    public function changeDepartment($department_name) {
        try {
            if (session_status() === PHP_SESSION_NONE) {
                if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
            }
            
            if (!$this->isAuthenticated()) {
                return [
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ];
            }

            // Verificar que el usuario tiene acceso al departamento
            if (!$this->userModel->hasAccessToDepartment($_SESSION['user_id'], $department_name)) {
                return [
                    'success' => false,
                    'message' => 'Sin acceso al departamento solicitado'
                ];
            }

            // Actualizar departamento seleccionado en sesión
            $_SESSION['selected_department'] = $department_name;

            // Registrar auditoría del cambio
            $this->logAuditAction($_SESSION['user_id'], 'change_department', 'user_session', 
                                $_SESSION['user_id'], null, 
                                ['new_department' => $department_name]);

            return [
                'success' => true,
                'message' => 'Departamento cambiado exitosamente',
                'department' => $department_name
            ];

        } catch (Exception $e) {
            error_log("Error cambiando departamento: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno del servidor'
            ];
        }
    }

    /**
     * Middleware para proteger páginas que requieren autenticación
     * @param string $redirect_url
     */
    public function requireAuth($redirect_url = '../auth/login.php') {
        if (!$this->isAuthenticated()) {
            header("Location: $redirect_url");
            exit;
        }
    }

    /**
     * Middleware para proteger páginas que requieren departamento específico
     * @param string $required_department
     * @param string $redirect_url
     */
    public function requireDepartment($required_department, $redirect_url = '../dashboard/dashboard.php') {
        $this->requireAuth();
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!$this->userModel->hasAccessToDepartment($_SESSION['user_id'], $required_department)) {
            header("Location: $redirect_url");
            exit;
        }
    }

    /**
     * Procesar solicitud de restablecimiento de contraseña
     * @param string $email
     * @return array
     */
    public function requestPasswordReset($email) {
        try {
            if (empty($email)) {
                return [
                    'success' => false,
                    'message' => 'Email es requerido'
                ];
            }

            $token = $this->userModel->generatePasswordResetToken($email);
            
            if (!$token) {
                return [
                    'success' => false,
                    'message' => 'Email no encontrado o usuario inactivo'
                ];
            }

            // Aquí enviarías el email con el token
            // Por ahora solo retornamos éxito
            return [
                'success' => true,
                'message' => 'Se ha enviado un enlace de restablecimiento a tu email',
                'token' => $token // Solo para desarrollo, no incluir en producción
            ];

        } catch (Exception $e) {
            error_log("Error en reset password: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error procesando solicitud'
            ];
        }
    }

    /**
     * Restablecer contraseña con token
     * @param string $token
     * @param string $new_password
     * @return array
     */
    public function resetPassword($token, $new_password) {
        try {
            if (empty($token) || empty($new_password)) {
                return [
                    'success' => false,
                    'message' => 'Token y nueva contraseña son requeridos'
                ];
            }

            if (strlen($new_password) < 6) {
                return [
                    'success' => false,
                    'message' => 'La contraseña debe tener al menos 6 caracteres'
                ];
            }

            $result = $this->userModel->resetPassword($token, $new_password);
            
            if (!$result) {
                return [
                    'success' => false,
                    'message' => 'Token inválido o expirado'
                ];
            }

            return [
                'success' => true,
                'message' => 'Contraseña restablecida exitosamente'
            ];

        } catch (Exception $e) {
            error_log("Error restableciendo contraseña: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error procesando solicitud'
            ];
        }
    }

    /**
     * Registrar acción en auditoría
     * @param int $user_id
     * @param string $action
     * @param string $table_affected
     * @param int $record_id
     * @param array $old_values
     * @param array $new_values
     */
    private function logAuditAction($user_id, $action, $table_affected, $record_id = null, $old_values = null, $new_values = null) {
        try {
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            
            // Aquí implementarías el logging a la tabla audit_log
            // Por simplicidad, solo lo registramos en el log de errores
            error_log("AUDIT: User $user_id performed $action on $table_affected (record: $record_id)");
            
        } catch (Exception $e) {
            error_log("Error registrando auditoría: " . $e->getMessage());
        }
    }

    /**
     * Validar fortaleza de contraseña
     * @param string $password
     * @return array
     */
    public function validatePassword($password) {
        $errors = [];
        
        if (strlen($password) < 6) {
            $errors[] = 'La contraseña debe tener al menos 6 caracteres';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'La contraseña debe contener al menos una letra mayúscula';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'La contraseña debe contener al menos un número';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}

?>