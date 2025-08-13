<?php

require_once __DIR__ . '/../config/Database.php';

class UserModel {
    private $conn;
    private $table = 'users';
    
    // Propiedades del usuario
    public $id;
    public $staff_id;
    public $username;
    public $password_hash;
    public $email;
    public $last_login;
    public $password_reset_token;
    public $password_reset_expires;
    public $status;
    public $created_at;
    public $updated_at;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Autenticar usuario por username y password
     * @param string $username
     * @param string $password
     * @return array|false
     */
    public function authenticate($username, $password) {
        try {
            $query = "SELECT u.*, s.first_name, s.last_name, s.department_id, 
                             d.name as department_name, d.shift_type 
                      FROM " . $this->table . " u 
                      LEFT JOIN staff s ON u.staff_id = s.id 
                      LEFT JOIN departments d ON s.department_id = d.id 
                      WHERE u.username = :username AND u.status = 'active'";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Actualizar último login
                $this->updateLastLogin($user['id']);
                
                // Obtener departamentos del usuario
                $user['departments'] = $this->getUserDepartments($user['id']);
                
                return $user;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Error en autenticación: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener departamentos asignados a un usuario
     * @param int $user_id
     * @return array
     */
    public function getUserDepartments($user_id) {
        try {
            $query = "SELECT d.*, ud.status as assignment_status 
                      FROM departments d 
                      INNER JOIN user_departments ud ON d.id = ud.department_id 
                      WHERE ud.user_id = :user_id AND ud.status = 'active'
                      ORDER BY d.name";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error obteniendo departamentos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Crear nuevo usuario
     * @param array $data
     * @return bool|int
     */
    public function create($data) {
        try {
            $query = "INSERT INTO " . $this->table . " 
                      (staff_id, username, password_hash, email, status) 
                      VALUES (:staff_id, :username, :password_hash, :email, :status)";
            
            $stmt = $this->conn->prepare($query);
            
            // Hash de la contraseña
            $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
            
            $stmt->bindParam(':staff_id', $data['staff_id']);
            $stmt->bindParam(':username', $data['username']);
            $stmt->bindParam(':password_hash', $password_hash);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':status', $data['status']);
            
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Error creando usuario: " . $e->getMessage());
            return false;
        }
    }



    /**
     * Eliminar usuario (soft delete - cambiar status)
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        try {
            $query = "UPDATE " . $this->table . " SET status = 'inactive' WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error eliminando usuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener usuario por ID
     * @param int $id
     * @return array|false
     */
    public function getById($id) {
        try {
            $query = "SELECT u.*, s.first_name, s.last_name, s.id_number, 
                             d.name as department_name, d.id as department_id
                      FROM " . $this->table . " u 
                      LEFT JOIN staff s ON u.staff_id = s.id 
                      LEFT JOIN departments d ON s.department_id = d.id 
                      WHERE u.id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                $user['departments'] = $this->getUserDepartments($user['id']);
            }
            
            return $user;
        } catch (PDOException $e) {
            error_log("Error obteniendo usuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener usuario por username
     * @param string $username
     * @return array|false
     */
    public function getByUsername($username) {
        try {
            $query = "SELECT u.*, s.first_name, s.last_name, s.id_number, 
                             d.name as department_name, d.id as department_id
                      FROM " . $this->table . " u 
                      LEFT JOIN staff s ON u.staff_id = s.id 
                      LEFT JOIN departments d ON s.department_id = d.id 
                      WHERE u.username = :username";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error obteniendo usuario por username: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener todos los usuarios con paginación
     * @param int $page
     * @param int $limit
     * @param string $department_filter
     * @return array
     */
        public function getAll($page = 1, $limit = 10, $department_filter = '') {
        try {
            $offset = ($page - 1) * $limit;

            $where_clause = "WHERE 1=1";
            $params = [];

            if (!empty($department_filter)) {
                $where_clause .= " AND d.name = :department";
                $params[':department'] = $department_filter;
            }

            $query = "SELECT u.*, s.first_name as staff_first_name, s.last_name as staff_last_name, s.id_number,
                             d.name as department_name, d.id as department_id, jp.name as staff_job_position
                      FROM " . $this->table . " u
                      LEFT JOIN staff s ON u.staff_id = s.id
                      LEFT JOIN departments d ON s.department_id = d.id
                      LEFT JOIN job_positions jp ON s.job_position_id = jp.id
                      $where_clause
                      ORDER BY u.created_at DESC
                      LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindParam($key, $value);
            }
            
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error obteniendo usuarios: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Asignar departamento a usuario
     * @param int $user_id
     * @param int $department_id
     * @return bool
     */
    public function assignDepartment($user_id, $department_id) {
        try {
            // Verificar si ya existe la asignación
            $check_query = "SELECT id FROM user_departments 
                           WHERE user_id = :user_id AND department_id = :department_id";
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->bindParam(':user_id', $user_id);
            $check_stmt->bindParam(':department_id', $department_id);
            $check_stmt->execute();
            
            if ($check_stmt->fetch()) {
                // Si existe, actualizar status
                $query = "UPDATE user_departments SET status = 'active' 
                         WHERE user_id = :user_id AND department_id = :department_id";
            } else {
                // Si no existe, crear nueva asignación
                $query = "INSERT INTO user_departments (user_id, department_id, status) 
                         VALUES (:user_id, :department_id, 'active')";
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':department_id', $department_id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error asignando departamento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remover departamento de usuario
     * @param int $user_id
     * @param int $department_id
     * @return bool
     */
    public function removeDepartment($user_id, $department_id) {
        try {
            $query = "UPDATE user_departments SET status = 'inactive' 
                     WHERE user_id = :user_id AND department_id = :department_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':department_id', $department_id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error removiendo departamento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar si usuario tiene acceso a un departamento específico
     * @param int $user_id
     * @param string $department_name
     * @return bool
     */
    public function hasAccessToDepartment($user_id, $department_name) {
        try {
            $query = "SELECT ud.id FROM user_departments ud
                      INNER JOIN departments d ON ud.department_id = d.id
                      WHERE ud.user_id = :user_id 
                      AND d.name = :department_name 
                      AND ud.status = 'active'";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':department_name', $department_name);
            $stmt->execute();
            
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            error_log("Error verificando acceso: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar último login
     * @param int $user_id
     * @return bool
     */
    private function updateLastLogin($user_id) {
        try {
            $query = "UPDATE " . $this->table . " SET last_login = NOW() WHERE id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error actualizando último login: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generar token para reset de contraseña
     * @param string $email
     * @return string|false
     */
    public function generatePasswordResetToken($email) {
        try {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $query = "UPDATE " . $this->table . " 
                     SET password_reset_token = :token, password_reset_expires = :expires 
                     WHERE email = :email AND status = 'active'";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(':expires', $expires);
            $stmt->bindParam(':email', $email);
            
            if ($stmt->execute() && $stmt->rowCount() > 0) {
                return $token;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Error generando token: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Resetear contraseña usando token
     * @param string $token
     * @param string $new_password
     * @return bool
     */
    public function resetPassword($token, $new_password) {
        try {
            // Verificar token válido y no expirado
            $query = "SELECT id FROM " . $this->table . " 
                     WHERE password_reset_token = :token 
                     AND password_reset_expires > NOW() 
                     AND status = 'active'";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':token', $token);
            $stmt->execute();
            
            $user = $stmt->fetch();
            
            if (!$user) {
                return false;
            }
            
            // Actualizar contraseña y limpiar token
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            
            $update_query = "UPDATE " . $this->table . " 
                            SET password_hash = :password_hash, 
                                password_reset_token = NULL, 
                                password_reset_expires = NULL 
                            WHERE id = :user_id";
            
            $update_stmt = $this->conn->prepare($update_query);
            $update_stmt->bindParam(':password_hash', $password_hash);
            $update_stmt->bindParam(':user_id', $user['id']);
            
            return $update_stmt->execute();
        } catch (PDOException $e) {
            error_log("Error reseteando contraseña: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener menús por departamento
     * @param string $department_name
     * @return array
     */
    public function getMenusByDepartment($department_name) {
        $menus = [
            'Recursos Humanos' => [
                
                [
                    'title' => 'Cargos',
                    'icon' => 'ri-briefcase-line',
                    'url' => 'views/job-positions/index.php'
                ]
            ],
            'Liquidacion' => [
                [
                    'title' => 'Adjudicatarios',
                    'icon' => 'ri-user-3-line',
                    'url' => 'views/awardees/index.php'
                ],
                [
                    'title' => 'Gestiones',
                    'icon' => 'ri-money-cny-circle-line',
                    'submenu' => [
                        ['title' => 'Rubros Internos', 'url' => 'views/internal-items/index.php'],
                        ['title' => 'Rubros Externos', 'url' => 'views/external-items/index.php'],
                        ['title' => 'Zonas', 'url' => 'views/market-zones/index.php'],
                        ['title' => 'Sectores', 'url' => 'views/sectors/index.php'],
                        ['title' => 'Locales', 'url' => 'views/market-stalls/index.php'],
                        ['title' => 'Tasas de Euro', 'url' => 'views/euro-rates/index.php'],
                        ['title' => 'Año Fiscal', 'url' => 'views/fiscal-year/index.php'],
                    ]
                ],
                [
                    'title' => 'Contratos',
                    'icon' => 'ri-file-list-3-line',
                    'url' => 'views/contracts/index.php'
                ]
            ],
            'Fiscalizacion' => [
               
                [
                    'title' => 'Planificacion',
                    'icon' => 'ri-calendar-line',
                    'url' => 'views/planning/index.php'
                ],
                
            ],
            'Cobranza' => [
                [
                    'title' => 'Planificacion',
                    'icon' => 'ri-calendar-line',
                    'url' => 'views/planning/index.php'
                ],
                [
                    'title' => 'Gestiones',
                    'icon' => 'ri-user-3-line',
                    'submenu' => [
                        ['title' => 'Metodos de pago', 'url' => 'views/payment-method/index.php'],
                        ['title' => 'Cajas', 'url' => 'views/cash-registers/index.php'],
                    ]
                ],
            ]
        ];

        return isset($menus[$department_name]) ? $menus[$department_name] : [];
    }

    /**
     * Contar total de usuarios
     * @param string $department_filter
     * @return int
     */
    public function countUsers($department_filter = '') {
        try {
            $where_clause = "WHERE 1=1";
            $params = [];
            
            if (!empty($department_filter)) {
                $where_clause .= " AND d.name = :department";
                $params[':department'] = $department_filter;
            }
            
            $query = "SELECT COUNT(*) as total 
                     FROM " . $this->table . " u 
                     LEFT JOIN staff s ON u.staff_id = s.id 
                     LEFT JOIN departments d ON s.department_id = d.id 
                     $where_clause";
            
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindParam($key, $value);
            }
            
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return (int) $result['total'];
        } catch (PDOException $e) {
            error_log("Error contando usuarios: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Verificar si un usuario es jefe de departamento
     * @param int $user_id
     * @return array|false - Retorna información del departamento si es jefe, false si no
     */
    public function isManager($user_id) {
        try {
            $query = "SELECT d.*, s.first_name, s.last_name 
                      FROM departments d
                      INNER JOIN staff s ON d.manager_id = s.id
                      INNER JOIN users u ON s.id = u.staff_id
                      WHERE u.id = :user_id AND u.status = 'active'";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error verificando si es jefe: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener usuarios del departamento de un jefe
     * @param int $manager_user_id
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getUsersByManagerDepartment($manager_user_id, $page = 1, $limit = 10) {
        try {
            // Primero verificar que es jefe y obtener su departamento
            $manager_info = $this->isManager($manager_user_id);
            if (!$manager_info) {
                return [];
            }

            $offset = ($page - 1) * $limit;
            
                        $query = "SELECT u.*, s.first_name as staff_first_name, s.last_name as staff_last_name, s.id_number,
                             d.name as department_name, d.id as department_id, jp.name as staff_job_position
                      FROM " . $this->table . " u
                      LEFT JOIN staff s ON u.staff_id = s.id
                      LEFT JOIN departments d ON s.department_id = d.id
                      LEFT JOIN job_positions jp ON s.job_position_id = jp.id
                      WHERE d.id = :department_id
                      ORDER BY u.created_at DESC
                      LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':department_id', $manager_info['id']);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error obteniendo usuarios del departamento: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Contar usuarios del departamento de un jefe
     * @param int $manager_user_id
     * @return int
     */
    public function countUsersByManagerDepartment($manager_user_id) {
        try {
            // Primero verificar que es jefe y obtener su departamento
            $manager_info = $this->isManager($manager_user_id);
            if (!$manager_info) {
                return 0;
            }
            
            $query = "SELECT COUNT(*) as total 
                     FROM " . $this->table . " u 
                     LEFT JOIN staff s ON u.staff_id = s.id 
                     LEFT JOIN departments d ON s.department_id = d.id 
                     WHERE d.id = :department_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':department_id', $manager_info['id']);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return (int) $result['total'];
        } catch (PDOException $e) {
            error_log("Error contando usuarios del departamento: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtener usuario por email
     * @param string $email
     * @return array|false
     */
    public function getByEmail($email) {
        try {
            $query = "SELECT u.*, s.first_name, s.last_name, s.id_number, 
                             d.name as department_name, d.id as department_id
                      FROM " . $this->table . " u 
                      LEFT JOIN staff s ON u.staff_id = s.id 
                      LEFT JOIN departments d ON s.department_id = d.id 
                      WHERE u.email = :email";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error obteniendo usuario por email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar usuario
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        try {
            $set_clauses = [];
            $params = [':id' => $id];
            
            if (isset($data['username'])) {
                $set_clauses[] = "username = :username";
                $params[':username'] = $data['username'];
            }
            
            if (isset($data['email'])) {
                $set_clauses[] = "email = :email";
                $params[':email'] = $data['email'];
            }
            
            if (isset($data['status'])) {
                $set_clauses[] = "status = :status";
                $params[':status'] = $data['status'];
            }
            
            if (isset($data['password'])) {
                $set_clauses[] = "password_hash = :password_hash";
                $params[':password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            
            $set_clauses[] = "updated_at = NOW()";
            
            $query = "UPDATE " . $this->table . " SET " . implode(', ', $set_clauses) . " WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindParam($key, $value);
            }
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error actualizando usuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener personal sin usuario asignado por departamento
     * @param int $department_id
     * @return array
     */
    public function getStaffWithoutUserByDepartment($department_id) {
        try {
            $query = "SELECT s.*, d.name as department_name, jp.name as job_position_name,
                             ad.name as academic_degree_name, asp.name as academic_specialization_name
                      FROM staff s
                      LEFT JOIN departments d ON s.department_id = d.id
                      LEFT JOIN job_positions jp ON s.job_position_id = jp.id
                      LEFT JOIN academic_degrees ad ON s.academic_degree_id = ad.id
                      LEFT JOIN academic_specializations asp ON s.academic_specialization_id = asp.id
                      LEFT JOIN users u ON s.id = u.staff_id
                      WHERE s.department_id = :department_id 
                      AND s.status = 'active'
                      AND u.staff_id IS NULL
                      ORDER BY s.first_name, s.last_name";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':department_id', $department_id);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error obteniendo personal sin usuario: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener todo el personal sin usuario asignado (para RRHH)
     * @return array
     */
    public function getAllStaffWithoutUser() {
        try {
            $query = "SELECT s.*, d.name as department_name, jp.name as job_position_name,
                             ad.name as academic_degree_name, asp.name as academic_specialization_name
                      FROM staff s
                      LEFT JOIN departments d ON s.department_id = d.id
                      LEFT JOIN job_positions jp ON s.job_position_id = jp.id
                      LEFT JOIN academic_degrees ad ON s.academic_degree_id = ad.id
                      LEFT JOIN academic_specializations asp ON s.academic_specialization_id = asp.id
                      LEFT JOIN users u ON s.id = u.staff_id
                      WHERE s.status = 'active'
                      AND u.staff_id IS NULL
                      ORDER BY d.name, s.first_name, s.last_name";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error obteniendo todo el personal sin usuario: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Crear usuario para un staff específico
     * @param int $staff_id
     * @param string $username
     * @param string $password
     * @param string $email
     * @return array
     */
    public function createUserForStaff($staff_id, $username, $password, $email) {
        try {
            // Verificar que el staff existe y no tiene usuario
            $staff_query = "SELECT s.*, u.staff_id as existing_user 
                           FROM staff s 
                           LEFT JOIN users u ON s.id = u.staff_id 
                           WHERE s.id = :staff_id AND s.status = 'active'";
            
            $stmt = $this->conn->prepare($staff_query);
            $stmt->bindParam(':staff_id', $staff_id);
            $stmt->execute();
            $staff = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$staff) {
                return ['success' => false, 'message' => 'Personal no encontrado o inactivo'];
            }
            
            if ($staff['existing_user']) {
                return ['success' => false, 'message' => 'Este personal ya tiene un usuario asignado'];
            }
            
            // Verificar que el username no exista
            $username_check = "SELECT id FROM users WHERE username = :username";
            $stmt = $this->conn->prepare($username_check);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'El nombre de usuario ya existe'];
            }
            
            // Verificar que el email no exista
            $email_check = "SELECT id FROM users WHERE email = :email";
            $stmt = $this->conn->prepare($email_check);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'El email ya está en uso'];
            }
            
            // Crear el usuario
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $insert_query = "INSERT INTO users (staff_id, username, password_hash, email, status, created_at) 
                            VALUES (:staff_id, :username, :password_hash, :email, 'active', NOW())";
            
            $stmt = $this->conn->prepare($insert_query);
            $stmt->bindParam(':staff_id', $staff_id);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password_hash', $password_hash);
            $stmt->bindParam(':email', $email);
            
            if ($stmt->execute()) {
                $user_id = $this->conn->lastInsertId();
                
                // Asignar automáticamente el departamento del staff al usuario
                $dept_query = "INSERT INTO user_departments (user_id, department_id, status, created_at) 
                              VALUES (:user_id, :department_id, 'active', NOW())";
                
                $stmt = $this->conn->prepare($dept_query);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':department_id', $staff['department_id']);
                $stmt->execute();
                
                return [
                    'success' => true, 
                    'message' => 'Usuario creado exitosamente',
                    'user_id' => $user_id
                ];
            } else {
                return ['success' => false, 'message' => 'Error al crear el usuario'];
            }
            
        } catch (PDOException $e) {
            error_log("Error creando usuario para staff: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno del servidor'];
        }
    }

    /**
     * Desactivar usuario (no eliminar)
     * @param int $user_id
     * @return bool
     */
    public function deactivateUser($user_id) {
        try {
            $query = "UPDATE users SET status = 'inactive', updated_at = NOW() WHERE id = :user_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error desactivando usuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Reactivar usuario
     * @param int $user_id
     * @return bool
     */
    public function reactivateUser($user_id) {
        try {
            $query = "UPDATE users SET status = 'active', updated_at = NOW() WHERE id = :user_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error reactivando usuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener información completa del usuario con datos del personal
     * @param int $user_id
     * @return array|false
     */
    public function getUserWithStaffDetails($user_id) {
        try {
            // Primero verificar que el usuario existe
            $user_check = "SELECT * FROM users WHERE id = :user_id";
            $stmt = $this->conn->prepare($user_check);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            $basic_user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$basic_user) {
                error_log("getUserWithStaffDetails: Usuario $user_id no existe");
                return false; // Usuario no existe
            }
            
            // Si el usuario no tiene staff_id, retornar solo datos del usuario
            if (!$basic_user['staff_id']) {
                error_log("getUserWithStaffDetails: Usuario $user_id sin staff_id, retornando datos básicos");
                try {
                    $basic_user['departments'] = $this->getUserDepartments($basic_user['id']);
                    $basic_user['activity_log'] = $this->getUserActivityLog($basic_user['id']);
                } catch (Exception $e) {
                    error_log("getUserWithStaffDetails: Error en getUserDepartments/getUserActivityLog: " . $e->getMessage());
                    // Continuar sin estos datos adicionales
                }
                return $basic_user;
            }
            
            // Si tiene staff_id, intentar obtener datos completos con consulta completa
            $query = "SELECT u.*, s.first_name, s.last_name, s.id_number, s.department_id,
                             d.name as department_name, jp.name as job_position_name,
                             ad.name as academic_degree_name, asp.name as academic_specialization_name
                      FROM users u
                      LEFT JOIN staff s ON u.staff_id = s.id
                      LEFT JOIN departments d ON s.department_id = d.id
                      LEFT JOIN job_positions jp ON s.job_position_id = jp.id
                      LEFT JOIN academic_degrees ad ON s.academic_degree_id = ad.id
                      LEFT JOIN academic_specializations asp ON s.academic_specialization_id = asp.id
                      WHERE u.id = :user_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Si la consulta completa falla, usar datos básicos
            if (!$user) {
                error_log("getUserWithStaffDetails: Consulta completa falló para usuario $user_id con staff_id " . $basic_user['staff_id']);
                try {
                    $basic_user['departments'] = $this->getUserDepartments($basic_user['id']);
                    $basic_user['activity_log'] = $this->getUserActivityLog($basic_user['id']);
                } catch (Exception $e) {
                    error_log("getUserWithStaffDetails: Error en métodos adicionales: " . $e->getMessage());
                }
                return $basic_user;
            }
            
            // Si todo está bien, agregar datos adicionales
            error_log("getUserWithStaffDetails: Éxito para usuario $user_id");
            try {
                $user['departments'] = $this->getUserDepartments($user['id']);
                $user['activity_log'] = $this->getUserActivityLog($user['id']);
            } catch (Exception $e) {
                error_log("getUserWithStaffDetails: Error en métodos adicionales (caso exitoso): " . $e->getMessage());
                // Continuar sin estos datos adicionales
            }
            
            return $user;
        } catch (PDOException $e) {
            error_log("getUserWithStaffDetails: Error PDO para usuario $user_id: " . $e->getMessage());
            return false;
        }
    }



    /**
     * Obtener historial de actividad del usuario
     * @param int $user_id
     * @param int $limit
     * @return array
     */
    public function getUserActivityLog($user_id, $limit = 50) {
        try {
            $query = "SELECT * FROM audit_log 
                      WHERE user_id = :user_id 
                      ORDER BY created_at DESC 
                      LIMIT :limit";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error obteniendo historial de actividad: " . $e->getMessage());
            return [];
        }
    }
}

?>
