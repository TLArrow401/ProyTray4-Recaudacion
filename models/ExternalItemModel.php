<?php

require_once __DIR__ . '/../config/Database.php';

class ExternalItemModel {
    private $conn;
    private $table = 'external_business_categories';
    
    // Propiedades del rubro externo
    public $id;
    public $name;
    public $installation_type;
    public $payment_count;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Obtener todos los rubros externos con paginación y búsqueda
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
                $query .= " WHERE name LIKE :search OR installation_type LIKE :search";
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
            throw new Exception("Error al obtener los rubros externos");
        }
    }

    /**
     * Contar total de rubros externos
     * @param string $search
     * @return int
     */
    public function countItems($search = '') {
        try {
            $query = "SELECT COUNT(*) as total FROM " . $this->table;
            $params = [];
            
            if (!empty($search)) {
                $query .= " WHERE name LIKE :search OR installation_type LIKE :search";
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
     * Obtener rubro externo por ID
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
     * Crear nuevo rubro externo
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
                    'message' => 'Ya existe un rubro externo con este nombre',
                    'errors' => ['name' => 'Ya existe un rubro externo con este nombre']
                ];
            }

            $query = "INSERT INTO " . $this->table . " 
                     (name, installation_type, payment_count) 
                     VALUES (:name, :installation_type, :payment_count)";
            
            $stmt = $this->conn->prepare($query);
            
            // Limpiar y preparar datos
            $name = trim($data['name']);
            $installation_type = !empty($data['installation_type']) ? trim($data['installation_type']) : null;
            $payment_count = !empty($data['payment_count']) ? (float)$data['payment_count'] : null;
            
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':installation_type', $installation_type);
            $stmt->bindParam(':payment_count', $payment_count);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Rubro externo creado exitosamente',
                    'id' => $this->conn->lastInsertId()
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al crear el rubro externo'
                ];
            }
            
        } catch (PDOException $e) {
            error_log("Error en create: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error de base de datos al crear el rubro externo'
            ];
        }
    }

    /**
     * Actualizar rubro externo
     * @param int $id
     * @param array $data
     * @return array
     */
    public function update($id, $data) {
        try {
            // Verificar si el rubro externo existe
            if (!$this->getById($id)) {
                return [
                    'success' => false,
                    'message' => 'Rubro externo no encontrado'
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
                    'message' => 'Ya existe un rubro externo con este nombre',
                    'errors' => ['name' => 'Ya existe un rubro externo con este nombre']
                ];
            }

            $query = "UPDATE " . $this->table . " 
                     SET name = :name, installation_type = :installation_type, payment_count = :payment_count 
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            
            // Limpiar y preparar datos
            $name = trim($data['name']);
            $installation_type = !empty($data['installation_type']) ? trim($data['installation_type']) : null;
            $payment_count = !empty($data['payment_count']) ? (float)$data['payment_count'] : null;
            
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':installation_type', $installation_type);
            $stmt->bindParam(':payment_count', $payment_count);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Rubro externo actualizado exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al actualizar el rubro externo'
                ];
            }
            
        } catch (PDOException $e) {
            error_log("Error en update: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error de base de datos al actualizar el rubro externo'
            ];
        }
    }

    /**
     * Eliminar rubro externo
     * @param int $id
     * @return array
     */
    public function delete($id) {
        try {
            // Verificar si el rubro externo existe
            $item = $this->getById($id);
            if (!$item) {
                return [
                    'success' => false,
                    'message' => 'Rubro externo no encontrado'
                ];
            }

            $query = "DELETE FROM " . $this->table . " WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Rubro externo "' . $item['name'] . '" eliminado exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al eliminar el rubro externo'
                ];
            }
            
        } catch (PDOException $e) {
            error_log("Error en delete: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error de base de datos al eliminar el rubro externo'
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
     * Obtener todos los rubros externos sin paginación (para listas desplegables)
     * @return array
     */
    public function getAllSimple() {
        try {
            $query = "SELECT id, name, installation_type, payment_count FROM " . $this->table . " ORDER BY name ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en getAllSimple: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Buscar rubros externos por término de búsqueda
     * @param string $searchTerm
     * @param int $limit
     * @return array
     */
    public function search($searchTerm, $limit = 20) {
        try {
            $query = "SELECT * FROM " . $this->table . " 
                      WHERE name LIKE :searchTerm OR installation_type LIKE :searchTerm
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
     * Obtener estadísticas de rubros externos
     * @return array
     */
    public function getStats() {
        try {
            $query = "SELECT 
                        COUNT(*) as total_items,
                        AVG(payment_count) as avg_payment_count,
                        MIN(payment_count) as min_payment_count,
                        MAX(payment_count) as max_payment_count,
                        SUM(payment_count) as total_payment_count,
                        COUNT(DISTINCT installation_type) as unique_installation_types
                      FROM " . $this->table;
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en getStats: " . $e->getMessage());
            return [
                'total_items' => 0,
                'avg_payment_count' => 0,
                'min_payment_count' => 0,
                'max_payment_count' => 0,
                'total_payment_count' => 0,
                'unique_installation_types' => 0
            ];
        }
    }

    /**
     * Obtener rubros externos por tipo de instalación
     * @param string $installationType
     * @return array
     */
    public function getByInstallationType($installationType) {
        try {
            $query = "SELECT * FROM " . $this->table . " 
                      WHERE installation_type = :installationType 
                      ORDER BY name ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':installationType', $installationType);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en getByInstallationType: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener tipos de instalación únicos
     * @return array
     */
    public function getInstallationTypes() {
        try {
            $query = "SELECT DISTINCT installation_type FROM " . $this->table . " 
                      WHERE installation_type IS NOT NULL 
                      ORDER BY installation_type ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
            
        } catch (PDOException $e) {
            error_log("Error en getInstallationTypes: " . $e->getMessage());
            return [];
        }
    }
}

?>