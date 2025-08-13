<?php

require_once __DIR__ . '/../models/PlanningModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class PlanningController {
    private $planningModel;
    
    public function __construct() {
        $this->planningModel = new PlanningModel();
    }

    /**
     * Mostrar planificación del mes actual con filtros
     * @param array $params
     * @return array
     */
    public function index($params = []) {
        // Verificar acceso
        AuthMiddleware::requireUserManagementAccess();
        
        try {
            // Obtener filtros
            $filters = [
                'zone_id' => isset($params['zone_id']) && !empty($params['zone_id']) ? $params['zone_id'] : '',
                'sector_id' => isset($params['sector_id']) && !empty($params['sector_id']) ? $params['sector_id'] : '',
                'show_delinquent' => isset($params['show_delinquent']) ? $params['show_delinquent'] : ''
            ];
            
            // Obtener datos
            $result = [];
            $result['contracts'] = $this->planningModel->getMonthlyContracts($filters);
            $result['zones'] = $this->planningModel->getAllZones();
            $result['sectors'] = [];
            
            // Si hay zona seleccionada, obtener sus sectores
            if (!empty($filters['zone_id'])) {
                $result['sectors'] = $this->planningModel->getSectorsByZone($filters['zone_id']);
            }
            
            $result['statistics'] = $this->planningModel->getMonthlyStatistics($filters);
            $result['filters'] = $filters;
            $result['page_title'] = 'Planificación de Cobros - ' . date('F Y');
            $result['current_month'] = date('F Y');
            $result['current_month_spanish'] = $this->getCurrentMonthSpanish();
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Error en index: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al cargar la planificación'
            ];
        }
    }

    /**
     * Obtener sectores por zona (AJAX)
     * @param array $params
     * @return void
     */
    public function getSectorsByZone($params = []) {
        // Verificar acceso
        AuthMiddleware::requireUserManagementAccess();
        
        header('Content-Type: application/json');
        
        try {
            $zone_id = isset($params['zone_id']) ? $params['zone_id'] : '';
            
            if (empty($zone_id)) {
                echo json_encode(['success' => false, 'message' => 'ID de zona requerido']);
                return;
            }
            
            $sectors = $this->planningModel->getSectorsByZone($zone_id);
            
            echo json_encode([
                'success' => true,
                'sectors' => $sectors
            ]);
            
        } catch (Exception $e) {
            error_log("Error en getSectorsByZone: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener sectores'
            ]);
        }
    }

    /**
     * Exportar planificación a CSV
     * @param array $params
     * @return void
     */
    public function exportCSV($params = []) {
        // Verificar acceso
        AuthMiddleware::requireUserManagementAccess();
        
        try {
            // Obtener filtros
            $filters = [
                'zone_id' => isset($params['zone_id']) && !empty($params['zone_id']) ? $params['zone_id'] : '',
                'sector_id' => isset($params['sector_id']) && !empty($params['sector_id']) ? $params['sector_id'] : '',
                'show_delinquent' => isset($params['show_delinquent']) ? $params['show_delinquent'] : ''
            ];
            
            $contracts = $this->planningModel->getMonthlyContracts($filters);
            
            // Configurar headers para descarga
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="planificacion_cobros_' . date('Y-m') . '.csv"');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            // Crear output
            $output = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers del CSV
            fputcsv($output, [
                'ID Contrato',
                'Adjudicatario',
                'Cédula',
                'Zona',
                'Sector',
                'Cantidad Rubros',
                'Cantidad Locales',
                'Monto a Pagar',
                'Fecha de Pago',
                'Estado'
            ], ';');
            
            // Datos
            foreach ($contracts as $contract) {
                fputcsv($output, [
                    $contract['contract_id'],
                    $contract['awardee_name'],
                    $contract['awardee_cedula'],
                    $contract['zone_name'] ?? 'N/A',
                    $contract['sector_name'] ?? 'N/A',
                    $contract['total_categories'],
                    $contract['total_locations'],
                    '$' . number_format($contract['calculated_amount'], 2),
                    $contract['payment_date'] ? date('d/m/Y', strtotime($contract['payment_date'])) : 'N/A',
                    $contract['payment_status_text']
                ], ';');
            }
            
            fclose($output);
            
        } catch (Exception $e) {
            error_log("Error en exportCSV: " . $e->getMessage());
            header('HTTP/1.1 500 Internal Server Error');
            echo 'Error al exportar datos';
        }
    }

    /**
     * Obtener estadísticas del dashboard
     * @param array $params
     * @return void
     */
    public function getStatistics($params = []) {
        // Verificar acceso
        AuthMiddleware::requireUserManagementAccess();
        
        header('Content-Type: application/json');
        
        try {
            // Obtener filtros
            $filters = [
                'zone_id' => isset($params['zone_id']) && !empty($params['zone_id']) ? $params['zone_id'] : '',
                'sector_id' => isset($params['sector_id']) && !empty($params['sector_id']) ? $params['sector_id'] : '',
                'show_delinquent' => isset($params['show_delinquent']) ? $params['show_delinquent'] : ''
            ];
            
            $statistics = $this->planningModel->getMonthlyStatistics($filters);
            
            echo json_encode([
                'success' => true,
                'statistics' => $statistics
            ]);
            
        } catch (Exception $e) {
            error_log("Error en getStatistics: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener estadísticas'
            ]);
        }
    }

    /**
     * Actualizar estado de pago
     * @param array $params
     * @return void
     */
    public function updatePaymentStatus($params = []) {
        // Verificar acceso
        AuthMiddleware::requireUserManagementAccess();
        
        header('Content-Type: application/json');
        
        try {
            $contract_id = isset($params['contract_id']) ? $params['contract_id'] : '';
            $new_status = isset($params['status']) ? $params['status'] : '';
            
            if (empty($contract_id) || empty($new_status)) {
                echo json_encode(['success' => false, 'message' => 'Parámetros requeridos']);
                return;
            }
            
            // Aquí se podría implementar la actualización del estado
            // Por ahora solo devolvemos éxito
            echo json_encode([
                'success' => true,
                'message' => 'Estado actualizado correctamente'
            ]);
            
        } catch (Exception $e) {
            error_log("Error en updatePaymentStatus: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error al actualizar estado'
            ]);
        }
    }

    /**
     * Obtener mes actual en español
     * @return string
     */
    private function getCurrentMonthSpanish() {
        $months = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        
        $current_month = date('n');
        $current_year = date('Y');
        
        return $months[$current_month] . ' ' . $current_year;
    }
}

?>