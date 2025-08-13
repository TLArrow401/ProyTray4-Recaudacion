<?php

require_once __DIR__ . '/../config/Database.php';

class PlanningModel {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Obtener contratos para planificación del mes actual con filtros
     * @param array $filters
     * @return array
     */
    public function getMonthlyContracts($filters = []) {
        try {
            $current_month = date('n'); // Mes actual (1-12)
            $current_year = date('Y');
            $month_spanish = $this->getSpanishMonth($current_month);
            
            $query = "SELECT 
                        c.id as contract_id,
                        CONCAT(a.first_name, ' ', a.last_name) as awardee_name,
                        a.id_number as awardee_cedula,
                        c.start_date,
                        c.end_date,
                        c.type as contract_type,
                        cat_count.total_categories,
                        loc_count.total_locations,
                        z.name as zone_name,
                        z.id as zone_id,
                        s.name as sector_name,
                        s.id as sector_id,
                        cp.amount as monthly_amount,
                        cp.payment_date,
                        cp.status as payment_status,
                        cp.payment_reference,
                        er.bs_value as euro_rate,
                        er.month as euro_month,
                        er.year as euro_year,
                        cat_count.multiplier_factor
                      FROM contracts c
                      INNER JOIN awardees a ON c.awardee_id = a.id
                      
                      -- Subconsulta para contar categorías y calcular factor multiplicador
                      LEFT JOIN (
                          SELECT 
                              ccbc.contract_id,
                              COUNT(DISTINCT ccbc.id) as total_categories,
                              COALESCE(SUM(
                                  CASE 
                                      WHEN ccbc.type = 'external' THEN ebc.payment_count
                                      ELSE ibc.payment_count
                                  END
                              ), 0) as multiplier_factor
                          FROM contract_business_categories ccbc
                          LEFT JOIN external_business_categories ebc ON ccbc.external_category_id = ebc.id
                          LEFT JOIN internal_business_categories ibc ON ccbc.internal_category_id = ibc.id
                          GROUP BY ccbc.contract_id
                      ) cat_count ON c.id = cat_count.contract_id
                      
                      -- Subconsulta para contar locales y obtener zona/sector
                      LEFT JOIN (
                          SELECT 
                              cl.contract_id,
                              COUNT(DISTINCT cl.id) as total_locations,
                              MAX(z.id) as zone_id,
                              MAX(z.name) as zone_name,
                              MAX(s.id) as sector_id,
                              MAX(s.name) as sector_name
                          FROM contract_locations cl
                          LEFT JOIN market_stalls ms ON cl.stall_id = ms.id
                          LEFT JOIN sectors s ON ms.sector_id = s.id
                          LEFT JOIN zones z ON s.zone_id = z.id
                          GROUP BY cl.contract_id
                      ) loc_count ON c.id = loc_count.contract_id
                      
                      -- Obtener zona y sector de la subconsulta
                      LEFT JOIN zones z ON loc_count.zone_id = z.id
                      LEFT JOIN sectors s ON loc_count.sector_id = s.id
                      
                      -- Pagos del mes actual
                      LEFT JOIN contract_payments cp ON c.id = cp.contract_id 
                          AND MONTH(cp.payment_date) = :current_month 
                          AND YEAR(cp.payment_date) = :current_year
                          
                      -- Tasa del euro
                      LEFT JOIN euro_rates er ON cp.euro_rate_id = er.id";
            
            $params = [
                'current_month' => $current_month,
                'current_year' => $current_year
            ];
            
            $conditions = [];
            
            // Solo contratos activos del mes actual
            $conditions[] = "c.start_date <= LAST_DAY(CURDATE())";
            $conditions[] = "c.end_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')";
            
            // Filtro por zona
            if (!empty($filters['zone_id'])) {
                $conditions[] = "z.id = :zone_id";
                $params['zone_id'] = $filters['zone_id'];
            }
            
            // Filtro por sector
            if (!empty($filters['sector_id'])) {
                $conditions[] = "s.id = :sector_id";
                $params['sector_id'] = $filters['sector_id'];
            }
            
            // Filtro por morosos (pagos vencidos)
            if (!empty($filters['show_delinquent']) && $filters['show_delinquent'] == '1') {
                $conditions[] = "(cp.status = 'pending' AND cp.payment_date < CURDATE())";
            }
            
            if (!empty($conditions)) {
                $query .= " WHERE " . implode(' AND ', $conditions);
            }
            
            $query .= " ORDER BY 
                        CASE 
                            WHEN cp.status = 'pending' AND cp.payment_date < CURDATE() THEN 1
                            WHEN cp.status = 'pending' THEN 2
                            WHEN cp.status IS NULL THEN 3
                            ELSE 4
                        END,
                        a.first_name, a.last_name";
            
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Procesar resultados para calcular montos correctos
            foreach ($results as &$contract) {
                // Si no hay pago programado, obtener tasa del euro actual
                if (empty($contract['euro_rate']) && $contract['multiplier_factor'] > 0) {
                    $current_euro_rate = $this->getCurrentEuroRate();
                    $contract['euro_rate'] = $current_euro_rate['bs_value'] ?? 0;
                    $contract['euro_month'] = $current_euro_rate['month'] ?? '';
                    $contract['euro_year'] = $current_euro_rate['year'] ?? '';
                }
                
                // Calcular monto real basado en el factor multiplicador y tasa del euro
                if ($contract['multiplier_factor'] > 0 && $contract['euro_rate'] > 0) {
                    $contract['calculated_amount'] = $contract['multiplier_factor'] * $contract['euro_rate'];
                } else {
                    $contract['calculated_amount'] = $contract['monthly_amount'] ?? 0;
                }
                
                // Determinar estatus del pago
                $contract['payment_status_text'] = $this->getPaymentStatusText($contract['payment_status'], $contract['payment_date']);
                
                // Si no hay pago programado, indicar que debe programarse
                if (empty($contract['payment_status']) && $contract['multiplier_factor'] > 0) {
                    $contract['payment_status_text'] = 'Pago no programado';
                }
            }
            
            return $results;
            
        } catch (PDOException $e) {
            error_log("Error en getMonthlyContracts: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener todas las zonas para filtros
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
     * Obtener estadísticas del mes actual
     * @param array $filters
     * @return array
     */
    public function getMonthlyStatistics($filters = []) {
        try {
            $contracts = $this->getMonthlyContracts($filters);
            
            $stats = [
                'total_contracts' => count($contracts),
                'total_amount' => 0,
                'pending_payments' => 0,
                'paid_payments' => 0,
                'delinquent_payments' => 0,
                'total_categories' => 0,
                'total_locations' => 0
            ];
            
            foreach ($contracts as $contract) {
                $stats['total_amount'] += $contract['calculated_amount'];
                $stats['total_categories'] += $contract['total_categories'];
                $stats['total_locations'] += $contract['total_locations'];
                
                switch ($contract['payment_status']) {
                    case 'pending':
                        if (strtotime($contract['payment_date']) < strtotime(date('Y-m-d'))) {
                            $stats['delinquent_payments']++;
                        } else {
                            $stats['pending_payments']++;
                        }
                        break;
                    case 'paid':
                        $stats['paid_payments']++;
                        break;
                }
            }
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("Error en getMonthlyStatistics: " . $e->getMessage());
            return [];
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
     * Obtener texto del estado del pago
     * @param string $status
     * @param string $payment_date
     * @return string
     */
    private function getPaymentStatusText($status, $payment_date) {
        if (empty($status)) {
            return 'Sin pago programado';
        }
        
        switch ($status) {
            case 'paid':
                return 'Pagado';
            case 'pending':
                if (strtotime($payment_date) < strtotime(date('Y-m-d'))) {
                    return 'Moroso';
                }
                return 'Pendiente';
            case 'cancelled':
                return 'Cancelado';
            case 'refunded':
                return 'Reembolsado';
            default:
                return ucfirst($status);
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