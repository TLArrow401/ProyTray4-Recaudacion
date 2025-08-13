<?php

require_once __DIR__ . '/../config/Database.php';

class CashRegistersModel {
    private $conn;
    private $table = 'cash_registers';
    
    // Propiedades de la caja registradora
    public $id;
    public $user_id;
    public $name;
    public $status;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Obtener todas las cajas registradoras con paginación y búsqueda
     * @param int $page
     * @param int $limit
     * @param string $search
     * @return array
     */
    public function getAll($page = 1, $limit = 10, $search = '') {
        try {
            $offset = ($page - 1) * $limit;
            
            $query = "SELECT c.*, 
                             COALESCE(
                                 NULLIF(TRIM(CONCAT(COALESCE(s.first_name, ''), ' ', COALESCE(s.last_name, ''))), ''),
                                 u.username,
                                 CONCAT('Usuario #', u.id)
                             ) as user_name, 
                             u.email as user_email 
                      FROM " . $this->table . " c 
                      LEFT JOIN users u ON c.user_id = u.id
                      LEFT JOIN staff s ON u.staff_id = s.id";
            $params = [];
            
            if (!empty($search)) {
                $query .= " WHERE c.name LIKE :search 
                           OR CONCAT(COALESCE(s.first_name, ''), ' ', COALESCE(s.last_name, '')) LIKE :search 
                           OR u.username LIKE :search 
                           OR u.email LIKE :search 
                           OR c.status LIKE :search";
                $params['search'] = '%' . $search . '%';
            }
            
            $query .= " ORDER BY c.created_at DESC LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            
            // Bind parámetros de búsqueda si existen
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            
            // Bind parámetros de paginación
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en getAll: " . $e->getMessage());
            throw new Exception("Error al obtener las cajas registradoras");
        }
    }

    /**
     * Contar total de cajas registradoras
     * @param string $search
     * @return int
     */
    public function countItems($search = '') {
        try {
            $query = "SELECT COUNT(*) as total FROM " . $this->table . " c 
                      LEFT JOIN users u ON c.user_id = u.id
                      LEFT JOIN staff s ON u.staff_id = s.id";
            $params = [];
            
            if (!empty($search)) {
                $query .= " WHERE c.name LIKE :search 
                           OR CONCAT(COALESCE(s.first_name, ''), ' ', COALESCE(s.last_name, '')) LIKE :search 
                           OR u.username LIKE :search 
                           OR u.email LIKE :search 
                           OR c.status LIKE :search";
                $params['search'] = '%' . $search . '%';
            }
            
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return (int)$result['total'];
            
        } catch (PDOException $e) {
            error_log("Error en countItems: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtener caja registradora por ID
     * @param int $id
     * @return array|false
     */
    public function getById($id) {
        try {
            $query = "SELECT c.*, 
                             COALESCE(
                                 NULLIF(TRIM(CONCAT(COALESCE(s.first_name, ''), ' ', COALESCE(s.last_name, ''))), ''),
                                 u.username,
                                 CONCAT('Usuario #', u.id)
                             ) as user_name, 
                             u.email as user_email 
                      FROM " . $this->table . " c 
                      LEFT JOIN users u ON c.user_id = u.id
                      LEFT JOIN staff s ON u.staff_id = s.id 
                      WHERE c.id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en getById: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crear nueva caja registradora
     * @param array $data
     * @return array
     */
    public function create($data) {
        try {
            // Validar datos requeridos
            if (empty($data['name'])) {
                return [
                    'success' => false,
                    'message' => 'El nombre es requerido',
                    'errors' => ['name' => 'El nombre es requerido']
                ];
            }

            if (empty($data['user_id'])) {
                return [
                    'success' => false,
                    'message' => 'El usuario asignado es requerido',
                    'errors' => ['user_id' => 'El usuario asignado es requerido']
                ];
            }

            if (empty($data['status'])) {
                return [
                    'success' => false,
                    'message' => 'El estado es requerido',
                    'errors' => ['status' => 'El estado es requerido']
                ];
            }

            // Verificar que el usuario existe
            if (!$this->userExists($data['user_id'])) {
                return [
                    'success' => false,
                    'message' => 'El usuario seleccionado no existe',
                    'errors' => ['user_id' => 'El usuario seleccionado no existe']
                ];
            }

            // Verificar que el estado es válido
            if (!in_array($data['status'], ['active', 'inactive', 'maintenance'])) {
                return [
                    'success' => false,
                    'message' => 'Estado no válido',
                    'errors' => ['status' => 'Estado no válido']
                ];
            }

            // Verificar si el nombre ya existe
            if ($this->nameExists($data['name'])) {
                return [
                    'success' => false,
                    'message' => 'Ya existe una caja registradora con este nombre',
                    'errors' => ['name' => 'Ya existe una caja registradora con este nombre']
                ];
            }

            $query = "INSERT INTO " . $this->table . " 
                     (user_id, name, status) 
                     VALUES (:user_id, :name, :status)";
            
            $stmt = $this->conn->prepare($query);
            
            // Limpiar y preparar datos
            $user_id = (int)$data['user_id'];
            $name = trim($data['name']);
            $status = trim($data['status']);
            
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':status', $status);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Caja registradora creada exitosamente',
                    'id' => $this->conn->lastInsertId()
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al crear la caja registradora'
                ];
            }
            
        } catch (PDOException $e) {
            error_log("Error en create: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error de base de datos al crear la caja registradora'
            ];
        }
    }

    /**
     * Actualizar caja registradora
     * @param int $id
     * @param array $data
     * @return array
     */
    public function update($id, $data) {
        try {
            // Verificar si la caja registradora existe
            if (!$this->getById($id)) {
                return [
                    'success' => false,
                    'message' => 'Caja registradora no encontrada'
                ];
            }

            // Validar datos requeridos
            if (empty($data['name'])) {
                return [
                    'success' => false,
                    'message' => 'El nombre es requerido',
                    'errors' => ['name' => 'El nombre es requerido']
                ];
            }

            if (empty($data['user_id'])) {
                return [
                    'success' => false,
                    'message' => 'El usuario asignado es requerido',
                    'errors' => ['user_id' => 'El usuario asignado es requerido']
                ];
            }

            if (empty($data['status'])) {
                return [
                    'success' => false,
                    'message' => 'El estado es requerido',
                    'errors' => ['status' => 'El estado es requerido']
                ];
            }

            // Verificar que el usuario existe
            if (!$this->userExists($data['user_id'])) {
                return [
                    'success' => false,
                    'message' => 'El usuario seleccionado no existe',
                    'errors' => ['user_id' => 'El usuario seleccionado no existe']
                ];
            }

            // Verificar que el estado es válido
            if (!in_array($data['status'], ['active', 'inactive', 'maintenance'])) {
                return [
                    'success' => false,
                    'message' => 'Estado no válido',
                    'errors' => ['status' => 'Estado no válido']
                ];
            }

            // Verificar si el nombre ya existe (excluyendo el actual)
            if ($this->nameExists($data['name'], $id)) {
                return [
                    'success' => false,
                    'message' => 'Ya existe una caja registradora con este nombre',
                    'errors' => ['name' => 'Ya existe una caja registradora con este nombre']
                ];
            }

            $query = "UPDATE " . $this->table . " 
                     SET user_id = :user_id, name = :name, status = :status 
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            
            // Limpiar y preparar datos
            $user_id = (int)$data['user_id'];
            $name = trim($data['name']);
            $status = trim($data['status']);
            
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':status', $status);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Caja registradora actualizada exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al actualizar la caja registradora'
                ];
            }
            
        } catch (PDOException $e) {
            error_log("Error en update: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error de base de datos al actualizar la caja registradora'
            ];
        }
    }

    /**
     * Eliminar caja registradora
     * @param int $id
     * @return array
     */
    public function delete($id) {
        try {
            // Verificar si la caja registradora existe
            $item = $this->getById($id);
            if (!$item) {
                return [
                    'success' => false,
                    'message' => 'Caja registradora no encontrada'
                ];
            }

            $query = "DELETE FROM " . $this->table . " WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Caja registradora "' . $item['name'] . '" eliminada exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al eliminar la caja registradora'
                ];
            }
            
        } catch (PDOException $e) {
            error_log("Error en delete: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error de base de datos al eliminar la caja registradora'
            ];
        }
    }

    /**
     * Verificar si un nombre ya existe
     * @param string $name
     * @param int $excludeId
     * @return bool
     */
    private function nameExists($name, $excludeId = null) {
        try {
            $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE name = :name";
            
            if ($excludeId) {
                $query .= " AND id != :excludeId";
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $name);
            
            if ($excludeId) {
                $stmt->bindParam(':excludeId', $excludeId, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return (int)$result['count'] > 0;
            
        } catch (PDOException $e) {
            error_log("Error en nameExists: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar si un usuario existe
     * @param int $user_id
     * @return bool
     */
    private function userExists($user_id) {
        try {
            $query = "SELECT COUNT(*) as count FROM users WHERE id = :user_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return (int)$result['count'] > 0;
            
        } catch (PDOException $e) {
            error_log("Error en userExists: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener todos los usuarios disponibles para seleccionar
     * Solo usuarios del departamento de Cobranza que no tengan caja asignada
     * @param int|null $excludeCashRegisterId ID de caja a excluir para permitir edición
     * @return array
     */
    public function getAvailableUsers($excludeCashRegisterId = null) {
        try {
            $query = "SELECT DISTINCT u.id, 
                             COALESCE(
                                 NULLIF(TRIM(CONCAT(COALESCE(s.first_name, ''), ' ', COALESCE(s.last_name, ''))), ''),
                                 u.username,
                                 CONCAT('Usuario #', u.id)
                             ) as name, 
                             u.email 
                      FROM users u 
                      LEFT JOIN staff s ON u.staff_id = s.id 
                      LEFT JOIN departments d ON s.department_id = d.id 
                      LEFT JOIN user_departments ud ON u.id = ud.user_id 
                      LEFT JOIN departments d2 ON ud.department_id = d2.id 
                      WHERE u.status = 'active' 
                      AND (
                          -- Usuario pertenece a Cobranza por su staff
                          (d.name = 'Cobranza') 
                          OR 
                          -- Usuario tiene asignación adicional a Cobranza
                          (d2.name = 'Cobranza' AND ud.status = 'active')
                      )
                      AND u.id NOT IN (
                          -- Excluir usuarios que ya tienen caja asignada, excepto la que se está editando
                          SELECT user_id FROM " . $this->table . " 
                          WHERE user_id IS NOT NULL" .
                          ($excludeCashRegisterId ? " AND id != :excludeCashRegisterId" : "") . "
                      )
                      ORDER BY name ASC";
            
            $stmt = $this->conn->prepare($query);
            
            if ($excludeCashRegisterId) {
                $stmt->bindParam(':excludeCashRegisterId', $excludeCashRegisterId, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en getAvailableUsers: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener todas las cajas registradoras sin paginación (para listas desplegables)
     * @return array
     */
    public function getAllSimple() {
        try {
            $query = "SELECT c.id, c.name, c.status, c.user_id, 
                             COALESCE(
                                 NULLIF(TRIM(CONCAT(COALESCE(s.first_name, ''), ' ', COALESCE(s.last_name, ''))), ''),
                                 u.username,
                                 CONCAT('Usuario #', u.id)
                             ) as user_name 
                      FROM " . $this->table . " c 
                      LEFT JOIN users u ON c.user_id = u.id
                      LEFT JOIN staff s ON u.staff_id = s.id 
                      ORDER BY c.name ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en getAllSimple: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Buscar cajas registradoras por término de búsqueda
     * @param string $searchTerm
     * @param int $limit
     * @return array
     */
    public function search($searchTerm, $limit = 20) {
        try {
            $query = "SELECT c.*, 
                             COALESCE(
                                 NULLIF(TRIM(CONCAT(COALESCE(s.first_name, ''), ' ', COALESCE(s.last_name, ''))), ''),
                                 u.username,
                                 CONCAT('Usuario #', u.id)
                             ) as user_name 
                      FROM " . $this->table . " c 
                      LEFT JOIN users u ON c.user_id = u.id
                      LEFT JOIN staff s ON u.staff_id = s.id 
                      WHERE c.name LIKE :searchTerm 
                         OR CONCAT(COALESCE(s.first_name, ''), ' ', COALESCE(s.last_name, '')) LIKE :searchTerm 
                         OR u.username LIKE :searchTerm 
                         OR c.status LIKE :searchTerm
                      ORDER BY c.name ASC 
                      LIMIT :limit";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%');
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en search: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener estadísticas de cajas registradoras
     * @return array
     */
    public function getStats() {
        try {
            $query = "SELECT 
                        COUNT(*) as total_cash_registers,
                        COUNT(CASE WHEN status = 'active' THEN 1 END) as active_count,
                        COUNT(CASE WHEN status = 'inactive' THEN 1 END) as inactive_count,
                        COUNT(CASE WHEN status = 'maintenance' THEN 1 END) as maintenance_count
                      FROM " . $this->table;
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en getStats: " . $e->getMessage());
            return [
                'total_cash_registers' => 0,
                'active_count' => 0,
                'inactive_count' => 0,
                'maintenance_count' => 0
            ];
        }
    }

    /**
     * Obtener cajas registradoras por estado
     * @param string $status
     * @return array
     */
    public function getByStatus($status) {
        try {
            $query = "SELECT c.*, 
                             COALESCE(
                                 NULLIF(TRIM(CONCAT(COALESCE(s.first_name, ''), ' ', COALESCE(s.last_name, ''))), ''),
                                 u.username,
                                 CONCAT('Usuario #', u.id)
                             ) as user_name 
                      FROM " . $this->table . " c 
                      LEFT JOIN users u ON c.user_id = u.id
                      LEFT JOIN staff s ON u.staff_id = s.id 
                      WHERE c.status = :status 
                      ORDER BY c.name ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en getByStatus: " . $e->getMessage());
            return [];
        }
    }
}

?>