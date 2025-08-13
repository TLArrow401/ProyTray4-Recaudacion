<?php

require_once __DIR__ . '/../config/Database.php';

class ContractsModel {
    private $conn;
    private $table = 'contracts';
    
    // Propiedades del contrato
    public $id;
    public $awardee_id;
    public $fiscal_year_id;
    public $start_date;
    public $end_date;
    public $type;
    public $contract_mode;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Obtener todos los contratos con paginación y filtros
     * @param int $page
     * @param int $limit
     * @param string $search
     * @param string $awardee_filter
     * @param string $status_filter
     * @return array
     */
    public function getAll($page = 1, $limit = 10, $search = '', $awardee_filter = '', $status_filter = '') {
        try {
            $offset = ($page - 1) * $limit;
            
            $query = "SELECT c.*, 
                             CONCAT(a.first_name, ' ', a.last_name) as awardee_name,
                             a.id_number as awardee_id_number,
                             fy.year as fiscal_year,
                             COUNT(DISTINCT ccbc.id) as categories_count,
                             COUNT(DISTINCT cl.id) as locations_count
                      FROM " . $this->table . " c 
                      LEFT JOIN awardees a ON c.awardee_id = a.id 
                      LEFT JOIN fiscal_year fy ON c.fiscal_year_id = fy.id
                      LEFT JOIN contract_business_categories ccbc ON c.id = ccbc.contract_id
                      LEFT JOIN contract_locations cl ON c.id = cl.contract_id";
            
            $params = [];
            $conditions = [];
            
            if (!empty($search)) {
                $conditions[] = "(a.first_name LIKE :search OR a.last_name LIKE :search OR a.id_number LIKE :search)";
                $params['search'] = '%' . $search . '%';
            }
            
            if (!empty($awardee_filter)) {
                $conditions[] = "c.awardee_id = :awardee_filter";
                $params['awardee_filter'] = $awardee_filter;
            }
            
            if (!empty($conditions)) {
                $query .= " WHERE " . implode(' AND ', $conditions);
            }
            
            $query .= " GROUP BY c.id ORDER BY c.id DESC LIMIT :limit OFFSET :offset";
            
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
            throw new Exception("Error al obtener los contratos");
        }
    }

    /**
     * Contar total de contratos
     * @param string $search
     * @param string $awardee_filter
     * @param string $status_filter
     * @return int
     */
    public function countContracts($search = '', $awardee_filter = '', $status_filter = '') {
        try {
            $query = "SELECT COUNT(DISTINCT c.id) as total FROM " . $this->table . " c 
                      LEFT JOIN awardees a ON c.awardee_id = a.id";
            
            $params = [];
            $conditions = [];
            
            if (!empty($search)) {
                $conditions[] = "(a.first_name LIKE :search OR a.last_name LIKE :search OR a.id_number LIKE :search)";
                $params['search'] = '%' . $search . '%';
            }
            
            if (!empty($awardee_filter)) {
                $conditions[] = "c.awardee_id = :awardee_filter";
                $params['awardee_filter'] = $awardee_filter;
            }
            
            if (!empty($conditions)) {
                $query .= " WHERE " . implode(' AND ', $conditions);
            }
            
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return (int)$result['total'];
            
        } catch (PDOException $e) {
            error_log("Error en countContracts: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtener contrato por ID
     * @param int $id
     * @return array|false
     */
    public function getById($id) {
        try {
            $query = "SELECT c.*, 
                             CONCAT(a.first_name, ' ', a.last_name) as awardee_name,
                             a.id_number as awardee_id_number,
                             a.phone as awardee_phone,
                             a.email as awardee_email,
                             fy.year as fiscal_year
                      FROM " . $this->table . " c 
                      LEFT JOIN awardees a ON c.awardee_id = a.id 
                      LEFT JOIN fiscal_year fy ON c.fiscal_year_id = fy.id
                      WHERE c.id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en getById: " . $e->getMessage());
            throw new Exception("Error al obtener el contrato");
        }
    }

    /**
     * Crear nuevo contrato
     * @param array $data
     * @return int|false
     */
    public function create($data) {
        try {
            $query = "INSERT INTO " . $this->table . " 
                      (awardee_id, fiscal_year_id, start_date, end_date, type, contract_mode) 
                      VALUES (:awardee_id, :fiscal_year_id, :start_date, :end_date, :type, :contract_mode)";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(':awardee_id', $data['awardee_id'], PDO::PARAM_INT);
            $stmt->bindParam(':fiscal_year_id', $data['fiscal_year_id'], PDO::PARAM_INT);
            $stmt->bindParam(':start_date', $data['start_date']);
            $stmt->bindParam(':end_date', $data['end_date']);
            $stmt->bindParam(':type', $data['type']);
            $stmt->bindParam(':contract_mode', $data['contract_mode']);
            
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            
            return false;
            
        } catch (PDOException $e) {
            error_log("Error en create: " . $e->getMessage());
            throw new Exception("Error al crear el contrato: " . $e->getMessage());
        }
    }

    /**
     * Actualizar contrato
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        try {
            $query = "UPDATE " . $this->table . " 
                      SET awardee_id = :awardee_id, 
                          fiscal_year_id = :fiscal_year_id,
                          start_date = :start_date, 
                          end_date = :end_date, 
                          type = :type, 
                          contract_mode = :contract_mode 
                      WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':awardee_id', $data['awardee_id'], PDO::PARAM_INT);
            $stmt->bindParam(':fiscal_year_id', $data['fiscal_year_id'], PDO::PARAM_INT);
            $stmt->bindParam(':start_date', $data['start_date']);
            $stmt->bindParam(':end_date', $data['end_date']);
            $stmt->bindParam(':type', $data['type']);
            $stmt->bindParam(':contract_mode', $data['contract_mode']);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Error en update: " . $e->getMessage());
            throw new Exception("Error al actualizar el contrato: " . $e->getMessage());
        }
    }

    /**
     * Eliminar contrato
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        try {
            // Eliminar registros relacionados primero
            $this->deleteContractCategories($id);
            $this->deleteContractLocations($id);
            $this->deleteContractPayments($id);
            
            // Eliminar el contrato
            $query = "DELETE FROM " . $this->table . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Error en delete: " . $e->getMessage());
            throw new Exception("Error al eliminar el contrato: " . $e->getMessage());
        }
    }

    /**
     * Obtener todos los adjudicatarios
     * @return array
     */
    public function getAllAwardees() {
        try {
            $query = "SELECT id, CONCAT(first_name, ' ', last_name) as name, id_number 
                      FROM awardees 
                      ORDER BY first_name, last_name";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en getAllAwardees: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener todos los años fiscales
     * @return array
     */
    public function getAllFiscalYears() {
        try {
            $query = "SELECT id, year FROM fiscal_year ORDER BY year DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en getAllFiscalYears: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener todas las zonas
     * @return array
     */
    public function getAllZones() {
        try {
            $query = "SELECT id, name FROM zones ORDER BY name";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en getAllZones: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener categorías externas
     * @return array
     */
    public function getAllExternalCategories() {
        try {
            $query = "SELECT id, name, installation_type, payment_count 
                      FROM external_business_categories 
                      ORDER BY name";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en getAllExternalCategories: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener categorías internas
     * @return array
     */
    public function getAllInternalCategories() {
        try {
            $query = "SELECT id, name, payment_count, 'interna' as installation_type
                      FROM internal_business_categories 
                      ORDER BY name";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en getAllInternalCategories: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener sectores por zona
     * @param int $zone_id
     * @return array
     */
    public function getSectorsByZone($zone_id) {
        try {
            $query = "SELECT id, name FROM sectors WHERE zone_id = :zone_id ORDER BY name";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':zone_id', $zone_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en getSectorsByZone: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener locales por zona y sector
     * @param int $zone_id
     * @param int $sector_id
     * @return array
     */
    public function getStallsByZoneAndSector($zone_id, $sector_id = null) {
        try {
            $query = "SELECT ms.id, ms.stall_number, ms.location_description as description, s.name as sector_name
                      FROM market_stalls ms
                      LEFT JOIN sectors s ON ms.sector_id = s.id
                      WHERE s.zone_id = :zone_id";
            
            $params = ['zone_id' => $zone_id];
            
            if ($sector_id) {
                $query .= " AND ms.sector_id = :sector_id";
                $params['sector_id'] = $sector_id;
            }
            
            $query .= " ORDER BY ms.stall_number";
            
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en getStallsByZoneAndSector: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Guardar categorías del contrato
     * @param int $contract_id
     * @param array $categories
     * @return bool
     */
    public function saveContractCategories($contract_id, $categories) {
        try {
            foreach ($categories as $category) {
                $query = "INSERT INTO contract_business_categories 
                          (contract_id, external_category_id, internal_category_id, type) 
                          VALUES (:contract_id, :external_category_id, :internal_category_id, :type)";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':contract_id', $contract_id, PDO::PARAM_INT);
                
                if ($category['type'] === 'external') {
                    $stmt->bindParam(':external_category_id', $category['category_id'], PDO::PARAM_INT);
                    $stmt->bindValue(':internal_category_id', null, PDO::PARAM_NULL);
                } else {
                    $stmt->bindValue(':external_category_id', null, PDO::PARAM_NULL);
                    $stmt->bindParam(':internal_category_id', $category['category_id'], PDO::PARAM_INT);
                }
                
                $stmt->bindParam(':type', $category['type']);
                $stmt->execute();
            }
            
            return true;
            
        } catch (PDOException $e) {
            error_log("Error en saveContractCategories: " . $e->getMessage());
            throw new Exception("Error al guardar las categorías del contrato");
        }
    }

    /**
     * Guardar locales del contrato
     * @param int $contract_id
     * @param array $locations
     * @return bool
     */
    public function saveContractLocations($contract_id, $locations) {
        try {
            foreach ($locations as $stall_id) {
                $query = "INSERT INTO contract_locations (contract_id, stall_id) 
                          VALUES (:contract_id, :stall_id)";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':contract_id', $contract_id, PDO::PARAM_INT);
                $stmt->bindParam(':stall_id', $stall_id, PDO::PARAM_INT);
                $stmt->execute();
                
                // Nota: campo status no existe en market_stalls
                // $this->updateStallStatus($stall_id, 'occupied');
            }
            
            return true;
            
        } catch (PDOException $e) {
            error_log("Error en saveContractLocations: " . $e->getMessage());
            throw new Exception("Error al guardar los locales del contrato");
        }
    }

    /**
     * Obtener categorías del contrato
     * @param int $contract_id
     * @return array
     */
    public function getContractCategories($contract_id) {
        try {
            $query = "SELECT ccbc.*, 
                             CASE 
                                 WHEN ccbc.type = 'external' THEN ebc.name
                                 ELSE ibc.name
                             END as category_name,
                             CASE 
                                 WHEN ccbc.type = 'external' THEN ebc.payment_count
                                 ELSE ibc.payment_count
                             END as payment_count,
                             CASE 
                                 WHEN ccbc.type = 'external' THEN ebc.installation_type
                                 ELSE 'interna'
                             END as installation_type
                      FROM contract_business_categories ccbc
                      LEFT JOIN external_business_categories ebc ON ccbc.external_category_id = ebc.id
                      LEFT JOIN internal_business_categories ibc ON ccbc.internal_category_id = ibc.id
                      WHERE ccbc.contract_id = :contract_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':contract_id', $contract_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en getContractCategories: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener locales del contrato
     * @param int $contract_id
     * @return array
     */
    public function getContractLocations($contract_id) {
        try {
            $query = "SELECT cl.*, ms.stall_number, ms.location_description as description, 
                             s.name as sector_name, z.name as zone_name
                      FROM contract_locations cl
                      JOIN market_stalls ms ON cl.stall_id = ms.id
                      LEFT JOIN sectors s ON ms.sector_id = s.id
                      LEFT JOIN zones z ON s.zone_id = z.id
                      WHERE cl.contract_id = :contract_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':contract_id', $contract_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en getContractLocations: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener pagos del contrato
     * @param int $contract_id
     * @return array
     */
    public function getContractPayments($contract_id) {
        try {
            // Primero obtener el factor multiplicador (suma de payment_count de las categorías)
            $factor_multiplicador = $this->getContractMultiplierFactor($contract_id);
            
            $query = "SELECT cp.*, er.bs_value as euro_rate, CONCAT(er.month, ' ', er.year) as rate_date
                      FROM contract_payments cp
                      LEFT JOIN euro_rates er ON cp.euro_rate_id = er.id
                      WHERE cp.contract_id = :contract_id
                      ORDER BY cp.payment_date";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':contract_id', $contract_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Agregar el factor multiplicador a cada pago
            foreach ($payments as &$payment) {
                $payment['multiplier_factor'] = $factor_multiplicador;
            }
            
            return $payments;
            
        } catch (PDOException $e) {
            error_log("Error en getContractPayments: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener el valor de la tasa del euro por ID
     * @param int|null $euro_rate_id
     * @return float|null
     */
    private function getEuroRateValueById($euro_rate_id) {
        try {
            if (!$euro_rate_id) {
                return null;
            }
            
            $query = "SELECT bs_value FROM euro_rates WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $euro_rate_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['bs_value'] : null;
            
        } catch (Exception $e) {
            error_log("Error en getEuroRateValueById: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener el factor multiplicador del contrato (suma de payment_count de las categorías)
     * @param int $contract_id
     * @return float
     */
    public function getContractMultiplierFactor($contract_id) {
        try {
            $categories = $this->getContractCategories($contract_id);
            $total_payment_count = 0;
            
            foreach ($categories as $category) {
                $total_payment_count += ($category['payment_count'] ?? 0);
            }
            
            return $total_payment_count;
            
        } catch (Exception $e) {
            error_log("Error en getContractMultiplierFactor: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Generar pagos automáticamente para el contrato
     * @param int $contract_id
     * @param array $contract_data
     * @return bool
     */
    public function generateContractPayments($contract_id, $contract_data) {
        try {
            // Obtener datos del año fiscal
            $fiscal_year = $this->getFiscalYearById($contract_data['fiscal_year_id']);
            if (!$fiscal_year) {
                throw new Exception("Año fiscal no encontrado");
            }
            
            // Obtener el factor multiplicador (suma de payment_count de las categorías)
            $multiplier_factor = $this->getContractMultiplierFactor($contract_id);
            
            if ($multiplier_factor <= 0) {
                throw new Exception("Factor multiplicador inválido. Verifique las categorías del contrato.");
            }
            
            // Generar fechas de pago según la modalidad
            $payment_dates = $this->generatePaymentDates(
                $contract_data['start_date'], 
                $contract_data['end_date'], 
                $contract_data['contract_mode']
            );
            
            foreach ($payment_dates as $index => $payment_date) {
                // Buscar tasa del euro para la fecha del pago
                $euro_rate_id = $this->getEuroRateForDate($payment_date);
                $euro_rate_value = $this->getEuroRateValueById($euro_rate_id);
                
                // Calcular monto: factor_multiplicador × tasa_euro_del_mes
                $payment_amount = $multiplier_factor * ($euro_rate_value ?? 0);
                
                $query = "INSERT INTO contract_payments 
                          (contract_id, payment_reference, payment_date, amount, euro_rate_id, status) 
                          VALUES (:contract_id, :payment_reference, :payment_date, :amount, :euro_rate_id, 'pending')";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':contract_id', $contract_id, PDO::PARAM_INT);
                
                $payment_reference = 'PAY-' . $contract_id . '-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
                $stmt->bindParam(':payment_reference', $payment_reference);
                $stmt->bindParam(':payment_date', $payment_date);
                $stmt->bindParam(':amount', $payment_amount);
                $stmt->bindParam(':euro_rate_id', $euro_rate_id, PDO::PARAM_INT);
                
                $stmt->execute();
            }
            
            return true;
            
        } catch (PDOException $e) {
            error_log("Error en generateContractPayments: " . $e->getMessage());
            throw new Exception("Error al generar los pagos del contrato");
        }
    }

    /**
     * Regenerar pagos del contrato
     * @param int $contract_id
     * @param array $contract_data
     * @return bool
     */
    public function regenerateContractPayments($contract_id, $contract_data) {
        try {
            // Eliminar pagos existentes que estén pendientes
            $this->deleteContractPayments($contract_id, 'pending');
            
            // Generar nuevos pagos
            return $this->generateContractPayments($contract_id, $contract_data);
            
        } catch (Exception $e) {
            error_log("Error en regenerateContractPayments: " . $e->getMessage());
            throw new Exception("Error al regenerar los pagos del contrato");
        }
    }

    /**
     * Eliminar categorías del contrato
     * @param int $contract_id
     * @return bool
     */
    public function deleteContractCategories($contract_id) {
        try {
            $query = "DELETE FROM contract_business_categories WHERE contract_id = :contract_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':contract_id', $contract_id, PDO::PARAM_INT);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Error en deleteContractCategories: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar locales del contrato
     * @param int $contract_id
     * @return bool
     */
    public function deleteContractLocations($contract_id) {
        try {
            // Nota: campo status no existe en market_stalls
            // Primero obtener los locales para liberar su estado
            // $locations = $this->getContractLocations($contract_id);
            // foreach ($locations as $location) {
            //     $this->updateStallStatus($location['stall_id'], 'available');
            // }
            
            // Eliminar registros
            $query = "DELETE FROM contract_locations WHERE contract_id = :contract_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':contract_id', $contract_id, PDO::PARAM_INT);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Error en deleteContractLocations: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar pagos del contrato
     * @param int $contract_id
     * @param string $status
     * @return bool
     */
    public function deleteContractPayments($contract_id, $status = null) {
        try {
            $query = "DELETE FROM contract_payments WHERE contract_id = :contract_id";
            $params = ['contract_id' => $contract_id];
            
            if ($status) {
                $query .= " AND status = :status";
                $params['status'] = $status;
            }
            
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Error en deleteContractPayments: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar estado del local
     * @param int $stall_id
     * @param string $status
     * @return bool
     */
    private function updateStallStatus($stall_id, $status) {
        try {
            $query = "UPDATE market_stalls SET status = :status WHERE id = :stall_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':stall_id', $stall_id, PDO::PARAM_INT);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Error en updateStallStatus: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener año fiscal por ID
     * @param int $fiscal_year_id
     * @return array|false
     */
    public function getFiscalYearById($fiscal_year_id) {
        try {
            $query = "SELECT * FROM fiscal_year WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $fiscal_year_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en getFiscalYearById: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Calcular monto total del contrato basado en las categorías
     * @param int $contract_id
     * @return float
     */
    private function calculateContractTotalAmount($contract_id) {
        try {
            $categories = $this->getContractCategories($contract_id);
            $total_payment_count = 0;
            
            // Sumar todos los payment_count de las categorías
            foreach ($categories as $category) {
                $total_payment_count += ($category['payment_count'] ?? 0);
            }
            
            // Obtener la tasa del euro para calcular el monto
            $euro_rate = $this->getCurrentEuroRate();
            
            if (!$euro_rate) {
                throw new Exception("No se encontró tasa del euro para el cálculo");
            }
            
            // Calcular el monto total: suma de payment_count * tasa del euro
            $total = $total_payment_count * $euro_rate['bs_value'];
            
            return $total;
            
        } catch (Exception $e) {
            error_log("Error en calculateContractTotalAmount: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtener la tasa del euro actual (del mes vigente)
     * @return array|false
     */
    private function getCurrentEuroRate() {
        try {
            $current_date = new DateTime();
            $month_spanish = $this->getSpanishMonth($current_date->format('n'));
            $year = $current_date->format('Y');
            
            $query = "SELECT id, bs_value, month, year FROM euro_rates 
                      WHERE month = :month AND year = :year 
                      ORDER BY id DESC LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':month', $month_spanish);
            $stmt->bindParam(':year', $year);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Si no encuentra tasa del mes actual, busca la más reciente
            if (!$result) {
                $query = "SELECT id, bs_value, month, year FROM euro_rates 
                          ORDER BY year DESC, 
                          CASE month 
                              WHEN 'enero' THEN 1 WHEN 'febrero' THEN 2 WHEN 'marzo' THEN 3 
                              WHEN 'abril' THEN 4 WHEN 'mayo' THEN 5 WHEN 'junio' THEN 6 
                              WHEN 'julio' THEN 7 WHEN 'agosto' THEN 8 WHEN 'septiembre' THEN 9 
                              WHEN 'octubre' THEN 10 WHEN 'noviembre' THEN 11 WHEN 'diciembre' THEN 12 
                          END DESC 
                          LIMIT 1";
                
                $stmt = $this->conn->prepare($query);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Error en getCurrentEuroRate: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generar fechas de pago según la modalidad
     * @param string $start_date
     * @param string $end_date
     * @param string $contract_mode
     * @return array
     */
    private function generatePaymentDates($start_date, $end_date, $contract_mode) {
        $dates = [];
        $current = new DateTime($start_date);
        $end = new DateTime($end_date);
        
        $interval = $contract_mode === 'weekly' ? 'P7D' : 'P1M';
        $period = new DateInterval($interval);
        
        while ($current <= $end) {
            $dates[] = $current->format('Y-m-d');
            $current->add($period);
        }
        
        return $dates;
    }

    /**
     * Obtener tasa del euro para una fecha específica
     * @param string $date
     * @return int|null
     */
    private function getEuroRateForDate($date) {
        try {
            $payment_date = new DateTime($date);
            $month_spanish = $this->getSpanishMonth($payment_date->format('n'));
            $year = $payment_date->format('Y');
            
            $query = "SELECT id FROM euro_rates WHERE month = :month AND year = :year LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':month', $month_spanish);
            $stmt->bindParam(':year', $year);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['id'] : null;
            
        } catch (Exception $e) {
            error_log("Error en getEuroRateForDate: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Convertir número de mes a nombre en español
     * @param int $month_number
     * @return string
     */
    private function getSpanishMonth($month_number) {
        $months = [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
        ];
        
        return $months[$month_number] ?? 'enero';
    }
}

?>