<?php

require_once __DIR__ . '/../config/Database.php';

class FiscalYearModel {
    private $conn;
    private $table = 'fiscal_year';
    
    // Propiedades del año fiscal
    public $id;
    public $year;
    public $start_date;
    public $end_date;
    public $status;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Obtener todos los años fiscales con paginación y búsqueda
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
                $query .= " WHERE year LIKE :search OR status LIKE :search";
                $params['search'] = '%' . $search . '%';
            }
            
            $query .= " ORDER BY year DESC LIMIT :limit OFFSET :offset";
            
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
            throw new Exception("Error al obtener los años fiscales");
        }
    }

    /**
     * Contar total de años fiscales
     * @param string $search
     * @return int
     */
    public function countItems($search = '') {
        try {
            $query = "SELECT COUNT(*) as total FROM " . $this->table;
            $params = [];
            
            if (!empty($search)) {
                $query .= " WHERE year LIKE :search OR status LIKE :search";
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
     * Obtener año fiscal por ID
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
            throw new Exception("Error al obtener el año fiscal");
        }
    }

    /**
     * Crear nuevo año fiscal
     * @param array $data
     * @return bool
     */
    public function create($data) {
        try {
            $query = "INSERT INTO " . $this->table . " 
                      (year, start_date, end_date, status) 
                      VALUES (:year, :start_date, :end_date, :status)";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(':year', $data['year']);
            $stmt->bindParam(':start_date', $data['start_date']);
            $stmt->bindParam(':end_date', $data['end_date']);
            
            $status = $data['status'] ?? 'active';
            $stmt->bindParam(':status', $status);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Error en create: " . $e->getMessage());
            
            if ($e->getCode() == 23000) {
                throw new Exception("Ya existe un año fiscal con estos datos");
            }
            
            throw new Exception("Error al crear el año fiscal: " . $e->getMessage());
        }
    }

    /**
     * Actualizar año fiscal
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        try {
            $query = "UPDATE " . $this->table . " 
                      SET year = :year, 
                          start_date = :start_date, 
                          end_date = :end_date, 
                          status = :status 
                      WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':year', $data['year']);
            $stmt->bindParam(':start_date', $data['start_date']);
            $stmt->bindParam(':end_date', $data['end_date']);
            
            $status = $data['status'] ?? 'active';
            $stmt->bindParam(':status', $status);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Error en update: " . $e->getMessage());
            
            if ($e->getCode() == 23000) {
                throw new Exception("Ya existe un año fiscal con estos datos");
            }
            
            throw new Exception("Error al actualizar el año fiscal: " . $e->getMessage());
        }
    }

    /**
     * Eliminar año fiscal
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
            
            if ($e->getCode() == 23000) {
                throw new Exception("No se puede eliminar el año fiscal porque está siendo utilizado en otras tablas");
            }
            
            throw new Exception("Error al eliminar el año fiscal: " . $e->getMessage());
        }
    }

    /**
     * Verificar si existe un año fiscal por año
     * @param string $year
     * @param int $excludeId
     * @return bool
     */
    public function existsByYear($year, $excludeId = null) {
        try {
            $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE year = :year";
            $params = ['year' => $year];
            
            if ($excludeId) {
                $query .= " AND id != :excludeId";
                $params['excludeId'] = $excludeId;
            }
            
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return (int)$result['count'] > 0;
            
        } catch (PDOException $e) {
            error_log("Error en existsByYear: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar si el año fiscal tiene contratos asociados
     * @param int $id
     * @return bool
     */
    public function hasAssociatedContracts($id) {
        try {
            $query = "SELECT COUNT(*) as count FROM contracts WHERE fiscal_year_id = :fiscal_year_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':fiscal_year_id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return (int)$result['count'] > 0;
            
        } catch (PDOException $e) {
            error_log("Error en hasAssociatedContracts: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener años fiscales activos para selectores
     * @return array
     */
    public function getActiveYears() {
        try {
            $query = "SELECT id, year FROM " . $this->table . " 
                      WHERE status = 'active' 
                      ORDER BY year DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en getActiveYears: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener estadísticas del año fiscal
     * @param int $id
     * @return array
     */
    public function getStatistics($id) {
        try {
            $stats = [];
            
            // Contar contratos asociados
            $query = "SELECT COUNT(*) as total_contracts FROM contracts WHERE fiscal_year_id = :fiscal_year_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':fiscal_year_id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_contracts'] = (int)$result['total_contracts'];
            
            // Contar contratos activos
            $query = "SELECT COUNT(*) as active_contracts 
                      FROM contracts 
                      WHERE fiscal_year_id = :fiscal_year_id 
                      AND end_date >= CURDATE()";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':fiscal_year_id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['active_contracts'] = (int)$result['active_contracts'];
            
            // Calcular monto total de pagos
            $query = "SELECT IFNULL(SUM(cp.amount), 0) as total_payments 
                      FROM contract_payments cp
                      INNER JOIN contracts c ON cp.contract_id = c.id
                      WHERE c.fiscal_year_id = :fiscal_year_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':fiscal_year_id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_payments'] = (float)$result['total_payments'];
            
            return $stats;
            
        } catch (PDOException $e) {
            error_log("Error en getStatistics: " . $e->getMessage());
            return [
                'total_contracts' => 0,
                'active_contracts' => 0,
                'total_payments' => 0
            ];
        }
    }

    /**
     * Activar/Desactivar año fiscal
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function updateStatus($id, $status) {
        try {
            $query = "UPDATE " . $this->table . " SET status = :status WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':status', $status);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Error en updateStatus: " . $e->getMessage());
            throw new Exception("Error al actualizar el estado del año fiscal");
        }
    }
}

?>