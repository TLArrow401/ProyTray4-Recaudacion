<?php

require_once __DIR__ . '/../config/Database.php';

class EuroRatesModel {
    private $conn;
    private $table = 'euro_rates';
    
    // Propiedades de la tasa de euro
    public $id;
    public $bs_value;
    public $month;
    public $year;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Obtener todas las tasas de euro con paginación y búsqueda
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
                $query .= " WHERE month LIKE :search OR year LIKE :search OR bs_value LIKE :search";
                $params['search'] = '%' . $search . '%';
            }
            
            $query .= " ORDER BY year DESC, month DESC LIMIT :limit OFFSET :offset";
            
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
            throw new Exception("Error al obtener las tasas de euro");
        }
    }

    /**
     * Contar total de tasas de euro
     * @param string $search
     * @return int
     */
    public function countItems($search = '') {
        try {
            $query = "SELECT COUNT(*) as total FROM " . $this->table;
            $params = [];
            
            if (!empty($search)) {
                $query .= " WHERE month LIKE :search OR year LIKE :search OR bs_value LIKE :search";
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
     * Obtener tasa de euro por ID
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
     * Verificar si ya existe una tasa para un mes y año específicos
     * @param string $month
     * @param string $year
     * @param int $excludeId
     * @return bool
     */
    public function existsForMonthYear($month, $year, $excludeId = null) {
        try {
            $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE month = :month AND year = :year";
            $params = ['month' => $month, 'year' => $year];
            
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
            error_log("Error en existsForMonthYear: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crear nueva tasa de euro
     * @param array $data
     * @return array
     */
    public function create($data) {
        try {
            // Validar datos requeridos
            if (empty($data['bs_value'])) {
                return [
                    'success' => false,
                    'message' => 'El valor en bolívares es requerido',
                    'errors' => ['bs_value' => 'El valor en bolívares es requerido']
                ];
            }

            // Verificar si ya existe una tasa para ese mes y año
            if (!empty($data['month']) && !empty($data['year'])) {
                if ($this->existsForMonthYear($data['month'], $data['year'])) {
                    return [
                        'success' => false,
                        'message' => 'Ya existe una tasa de euro para este mes y año',
                        'errors' => ['month' => 'Ya existe una tasa para este período']
                    ];
                }
            }

            $query = "INSERT INTO " . $this->table . " 
                     (bs_value, month, year) 
                     VALUES (:bs_value, :month, :year)";
            
            $stmt = $this->conn->prepare($query);
            
            // Limpiar y preparar datos
            $bs_value = (float)$data['bs_value'];
            $month = !empty($data['month']) ? trim($data['month']) : null;
            $year = !empty($data['year']) ? trim($data['year']) : null;
            
            $stmt->bindParam(':bs_value', $bs_value);
            $stmt->bindParam(':month', $month);
            $stmt->bindParam(':year', $year);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Tasa de euro creada exitosamente',
                    'id' => $this->conn->lastInsertId()
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al crear la tasa de euro'
                ];
            }
            
        } catch (PDOException $e) {
            error_log("Error en create: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error de base de datos: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Actualizar tasa de euro
     * @param int $id
     * @param array $data
     * @return array
     */
    public function update($id, $data) {
        try {
            // Verificar si el registro existe
            $existing = $this->getById($id);
            if (!$existing) {
                return [
                    'success' => false,
                    'message' => 'La tasa de euro no existe'
                ];
            }

            // Validar datos requeridos
            if (empty($data['bs_value'])) {
                return [
                    'success' => false,
                    'message' => 'El valor en bolívares es requerido',
                    'errors' => ['bs_value' => 'El valor en bolívares es requerido']
                ];
            }

            // Verificar si ya existe una tasa para ese mes y año (excluyendo el actual)
            if (!empty($data['month']) && !empty($data['year'])) {
                if ($this->existsForMonthYear($data['month'], $data['year'], $id)) {
                    return [
                        'success' => false,
                        'message' => 'Ya existe otra tasa de euro para este mes y año',
                        'errors' => ['month' => 'Ya existe una tasa para este período']
                    ];
                }
            }

            $query = "UPDATE " . $this->table . " 
                     SET bs_value = :bs_value, month = :month, year = :year 
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            
            // Limpiar y preparar datos
            $bs_value = (float)$data['bs_value'];
            $month = !empty($data['month']) ? trim($data['month']) : null;
            $year = !empty($data['year']) ? trim($data['year']) : null;
            
            $stmt->bindParam(':bs_value', $bs_value);
            $stmt->bindParam(':month', $month);
            $stmt->bindParam(':year', $year);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                if ($stmt->rowCount() > 0) {
                    return [
                        'success' => true,
                        'message' => 'Tasa de euro actualizada exitosamente'
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'No se realizaron cambios en la tasa de euro'
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al actualizar la tasa de euro'
                ];
            }
            
        } catch (PDOException $e) {
            error_log("Error en update: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error de base de datos: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Eliminar tasa de euro
     * @param int $id
     * @return array
     */
    public function delete($id) {
        try {
            // Verificar si el registro existe
            $existing = $this->getById($id);
            if (!$existing) {
                return [
                    'success' => false,
                    'message' => 'La tasa de euro no existe'
                ];
            }

            $query = "DELETE FROM " . $this->table . " WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Tasa de euro eliminada exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al eliminar la tasa de euro'
                ];
            }
            
        } catch (PDOException $e) {
            error_log("Error en delete: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error de base de datos: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener tasas para uso en selects
     * @return array
     */
    public function getForSelect() {
        try {
            $query = "SELECT id, bs_value, month, year FROM " . $this->table . " ORDER BY year DESC, month DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en getForSelect: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener la tasa más reciente
     * @return array|false
     */
    public function getLatestRate() {
        try {
            $query = "SELECT * FROM " . $this->table . " ORDER BY year DESC, month DESC LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en getLatestRate: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Formatear nombre del período (mes/año)
     * @param array $rate
     * @return string
     */
    public function formatPeriod($rate) {
        if (empty($rate['month']) && empty($rate['year'])) {
            return 'Sin período especificado';
        }
        
        $period_parts = array_filter([
            $rate['month'],
            $rate['year']
        ]);
        
        return implode(' / ', $period_parts);
    }

    /**
     * Formatear valor en bolívares
     * @param float $value
     * @return string
     */
    public function formatBsValue($value) {
        return number_format($value, 2, ',', '.') . ' Bs.';
    }

    /**
     * Obtener lista de meses en español
     * @return array
     */
    public function getMonthsList() {
        return [
            'enero' => 'Enero',
            'febrero' => 'Febrero',
            'marzo' => 'Marzo',
            'abril' => 'Abril',
            'mayo' => 'Mayo',
            'junio' => 'Junio',
            'julio' => 'Julio',
            'agosto' => 'Agosto',
            'septiembre' => 'Septiembre',
            'octubre' => 'Octubre',
            'noviembre' => 'Noviembre',
            'diciembre' => 'Diciembre'
        ];
    }

    /**
     * Obtener lista de años disponibles
     * @return array
     */
    public function getYearsList() {
        $currentYear = (int)date('Y');
        $years = [];
        
        // Años desde 2020 hasta 5 años en el futuro
        for ($year = 2020; $year <= $currentYear + 5; $year++) {
            $years[] = (string)$year;
        }
        
        return array_reverse($years); // Más recientes primero
    }
}

?>