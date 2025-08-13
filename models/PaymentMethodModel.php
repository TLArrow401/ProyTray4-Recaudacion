<?php

require_once __DIR__ . '/../config/Database.php';

class PaymentMethodModel {
    private $conn;
    private $table = 'payment_methods';
    
    // Propiedades del método de pago
    public $id;
    public $name;
    public $is_active;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Obtener todos los métodos de pago con paginación y búsqueda
     * @param int $page
     * @param int $limit
     * @param string $search
     * @return array
     */
    public function getAll($page = 1, $limit = 10, $search = '') {
        try {
            $offset = ($page - 1) * $limit;
            
            $query = "SELECT * FROM " . $this->table;
            $params = [];
            
            if (!empty($search)) {
                $query .= " WHERE name LIKE :search";
                $params['search'] = '%' . $search . '%';
            }
            
            $query .= " ORDER BY name ASC LIMIT :limit OFFSET :offset";
            
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
            throw new Exception("Error al obtener los métodos de pago");
        }
    }

    /**
     * Contar total de métodos de pago
     * @param string $search
     * @return int
     */
    public function countItems($search = '') {
        try {
            $query = "SELECT COUNT(*) as total FROM " . $this->table;
            $params = [];
            
            if (!empty($search)) {
                $query .= " WHERE name LIKE :search";
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
     * Obtener método de pago por ID
     * @param int $id
     * @return array|false
     */
    public function getById($id) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
            
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
     * Crear nuevo método de pago
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

            // Verificar si el nombre ya existe
            if ($this->nameExists($data['name'])) {
                return [
                    'success' => false,
                    'message' => 'Ya existe un método de pago con este nombre',
                    'errors' => ['name' => 'Ya existe un método de pago con este nombre']
                ];
            }

            $query = "INSERT INTO " . $this->table . " 
                     (name, is_active) 
                     VALUES (:name, :is_active)";
            
            $stmt = $this->conn->prepare($query);
            
            // Limpiar y preparar datos
            $name = trim($data['name']);
            $is_active = isset($data['is_active']) ? (int)$data['is_active'] : 1;
            
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':is_active', $is_active, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Método de pago creado exitosamente',
                    'id' => $this->conn->lastInsertId()
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al crear el método de pago'
                ];
            }
            
        } catch (PDOException $e) {
            error_log("Error en create: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error de base de datos al crear el método de pago'
            ];
        }
    }

    /**
     * Actualizar método de pago
     * @param int $id
     * @param array $data
     * @return array
     */
    public function update($id, $data) {
        try {
            // Verificar si el método de pago existe
            if (!$this->getById($id)) {
                return [
                    'success' => false,
                    'message' => 'Método de pago no encontrado'
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

            // Verificar si el nombre ya existe (excluyendo el actual)
            if ($this->nameExists($data['name'], $id)) {
                return [
                    'success' => false,
                    'message' => 'Ya existe un método de pago con este nombre',
                    'errors' => ['name' => 'Ya existe un método de pago con este nombre']
                ];
            }

            $query = "UPDATE " . $this->table . " 
                     SET name = :name, is_active = :is_active 
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            
            // Limpiar y preparar datos
            $name = trim($data['name']);
            $is_active = isset($data['is_active']) ? (int)$data['is_active'] : 1;
            
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':is_active', $is_active, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Método de pago actualizado exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al actualizar el método de pago'
                ];
            }
            
        } catch (PDOException $e) {
            error_log("Error en update: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error de base de datos al actualizar el método de pago'
            ];
        }
    }

    /**
     * Eliminar método de pago
     * @param int $id
     * @return array
     */
    public function delete($id) {
        try {
            // Verificar si el método de pago existe
            $item = $this->getById($id);
            if (!$item) {
                return [
                    'success' => false,
                    'message' => 'Método de pago no encontrado'
                ];
            }

            $query = "DELETE FROM " . $this->table . " WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Método de pago "' . $item['name'] . '" eliminado exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al eliminar el método de pago'
                ];
            }
            
        } catch (PDOException $e) {
            error_log("Error en delete: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error de base de datos al eliminar el método de pago'
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
     * Obtener todos los métodos de pago sin paginación (para listas desplegables)
     * @param bool $activeOnly
     * @return array
     */
    public function getAllSimple($activeOnly = true) {
        try {
            $query = "SELECT id, name, is_active FROM " . $this->table;
            
            if ($activeOnly) {
                $query .= " WHERE is_active = 1";
            }
            
            $query .= " ORDER BY name ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en getAllSimple: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Buscar métodos de pago por término de búsqueda
     * @param string $searchTerm
     * @param int $limit
     * @return array
     */
    public function search($searchTerm, $limit = 20) {
        try {
            $query = "SELECT * FROM " . $this->table . " 
                      WHERE name LIKE :searchTerm
                      ORDER BY name ASC 
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
     * Obtener estadísticas de métodos de pago
     * @return array
     */
    public function getStats() {
        try {
            $query = "SELECT 
                        COUNT(*) as total_methods,
                        SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_methods,
                        SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive_methods
                      FROM " . $this->table;
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en getStats: " . $e->getMessage());
            return [
                'total_methods' => 0,
                'active_methods' => 0,
                'inactive_methods' => 0
            ];
        }
    }

    /**
     * Obtener métodos de pago activos
     * @return array
     */
    public function getActivePaymentMethods() {
        try {
            $query = "SELECT * FROM " . $this->table . " 
                      WHERE is_active = 1 
                      ORDER BY name ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en getActivePaymentMethods: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Cambiar estado de un método de pago
     * @param int $id
     * @param int $status
     * @return array
     */
    public function changeStatus($id, $status) {
        try {
            // Verificar si el método de pago existe
            $item = $this->getById($id);
            if (!$item) {
                return [
                    'success' => false,
                    'message' => 'Método de pago no encontrado'
                ];
            }

            $query = "UPDATE " . $this->table . " SET is_active = :status WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':status', $status, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $statusText = $status ? 'activado' : 'desactivado';
                return [
                    'success' => true,
                    'message' => 'Método de pago "' . $item['name'] . '" ' . $statusText . ' exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al cambiar el estado del método de pago'
                ];
            }
            
        } catch (PDOException $e) {
            error_log("Error en changeStatus: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error de base de datos al cambiar el estado'
            ];
        }
    }
}

?>