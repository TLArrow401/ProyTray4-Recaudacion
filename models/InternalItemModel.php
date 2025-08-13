<?php

require_once __DIR__ . '/../config/Database.php';

class InternalItemModel {
    private $conn;
    private $table = 'internal_business_categories';
    
    // Propiedades del rubro interno
    public $id;
    public $name;
    public $payment_count;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Obtener todos los rubros internos con paginación y búsqueda
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
            throw new Exception("Error al obtener los rubros internos");
        }
    }

    /**
     * Contar total de rubros internos
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
     * Obtener rubro interno por ID
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
     * Crear nuevo rubro interno
     * @param array $data
     * @return int|false
     */
    public function create($data) {
        try {
            $query = "INSERT INTO " . $this->table . " 
                      (name, payment_count) 
                      VALUES (:name, :payment_count)";
            
            $stmt = $this->conn->prepare($query);
            
            // Limpiar y validar datos
            $name = trim($data['name']);
            $payment_count = is_numeric($data['payment_count']) ? (float)$data['payment_count'] : 0;
            
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':payment_count', $payment_count);
            
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            
            return false;
            
        } catch (PDOException $e) {
            error_log("Error en create: " . $e->getMessage());
            throw new Exception("Error al crear el rubro interno");
        }
    }

    /**
     * Actualizar rubro interno existente
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        try {
            $query = "UPDATE " . $this->table . " 
                      SET name = :name, payment_count = :payment_count 
                      WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            
            // Limpiar y validar datos
            $name = trim($data['name']);
            $payment_count = is_numeric($data['payment_count']) ? (float)$data['payment_count'] : 0;
            
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':payment_count', $payment_count);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Error en update: " . $e->getMessage());
            throw new Exception("Error al actualizar el rubro interno");
        }
    }

    /**
     * Eliminar rubro interno
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Error en delete: " . $e->getMessage());
            throw new Exception("Error al eliminar el rubro interno");
        }
    }

    /**
     * Verificar si existe un rubro interno con el nombre especificado
     * @param string $name
     * @return bool
     */
    public function existsByName($name) {
        try {
            $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE name = :name";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return (int)$result['count'] > 0;
            
        } catch (PDOException $e) {
            error_log("Error en existsByName: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar si existe un rubro interno con el nombre especificado, excluyendo un ID
     * @param string $name
     * @param int $excludeId
     * @return bool
     */
    public function existsByNameExcluding($name, $excludeId) {
        try {
            $query = "SELECT COUNT(*) as count FROM " . $this->table . " 
                      WHERE name = :name AND id != :excludeId";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':excludeId', $excludeId, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return (int)$result['count'] > 0;
            
        } catch (PDOException $e) {
            error_log("Error en existsByNameExcluding: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener todos los rubros internos sin paginación (para listas desplegables)
     * @return array
     */
    public function getAllSimple() {
        try {
            $query = "SELECT id, name, payment_count FROM " . $this->table . " ORDER BY name ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en getAllSimple: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Buscar rubros internos por término de búsqueda
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
     * Obtener estadísticas de rubros internos
     * @return array
     */
    public function getStats() {
        try {
            $query = "SELECT 
                        COUNT(*) as total_items,
                        AVG(payment_count) as avg_payment_count,
                        MIN(payment_count) as min_payment_count,
                        MAX(payment_count) as max_payment_count,
                        SUM(payment_count) as total_payment_count
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
                'total_payment_count' => 0
            ];
        }
    }

    /**
     * Obtener rubros internos ordenados por número de cobros
     * @param string $order ASC o DESC
     * @param int $limit
     * @return array
     */
    public function getByPaymentCount($order = 'DESC', $limit = 10) {
        try {
            $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
            
            $query = "SELECT * FROM " . $this->table . " 
                      ORDER BY payment_count " . $order . ", name ASC 
                      LIMIT :limit";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en getByPaymentCount: " . $e->getMessage());
            return [];
        }
    }
}

?>