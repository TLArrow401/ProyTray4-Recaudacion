<?php

require_once __DIR__ . '/../models/ContractsModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class ContractsController {
    private $contractsModel;
    
    public function __construct() {
        $this->contractsModel = new ContractsModel();
    }

    /**
     * Mostrar lista de contratos con filtros y paginación
     * @param array $params
     * @return array
     */
    public function index($params = []) {
        // Verificar acceso
        AuthMiddleware::requireUserManagementAccess();
        
        // Obtener parámetros
        $page = isset($params['page']) ? (int)$params['page'] : 1;
        $limit = 10;
        $search = isset($params['search']) ? $params['search'] : '';
        $awardee_filter = isset($params['awardee_id']) ? $params['awardee_id'] : '';
        $status_filter = isset($params['status']) ? $params['status'] : '';
        
        try {
            $result = [];
            $result['contracts'] = $this->contractsModel->getAll($page, $limit, $search, $awardee_filter, $status_filter);
            $result['total_contracts'] = $this->contractsModel->countContracts($search, $awardee_filter, $status_filter);
            $result['total_pages'] = ceil($result['total_contracts'] / $limit);
            $result['current_page'] = $page;
            $result['awardees'] = $this->contractsModel->getAllAwardees();
            $result['page_title'] = 'Gestión de Contratos';
            $result['search'] = $search;
            $result['awardee_filter'] = $awardee_filter;
            $result['status_filter'] = $status_filter;
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Error en index: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al cargar los contratos'
            ];
        }
    }

    /**
     * Mostrar formulario de creación
     * @return array
     */
    public function create() {
        // Verificar acceso
        AuthMiddleware::requireUserManagementAccess();
        
        try {
            $result = [];
            $result['awardees'] = $this->contractsModel->getAllAwardees();
            $result['fiscal_years'] = $this->contractsModel->getAllFiscalYears();
            $result['zones'] = $this->contractsModel->getAllZones();
            $result['external_categories'] = $this->contractsModel->getAllExternalCategories();
            $result['internal_categories'] = $this->contractsModel->getAllInternalCategories();
            $result['page_title'] = 'Crear Nuevo Contrato';
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Error en create: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al cargar los datos para el formulario'
            ];
        }
    }

    /**
     * Guardar nuevo contrato
     * @param array $data
     * @return array
     */
    public function store($data) {
        // Verificar acceso
        AuthMiddleware::requireUserManagementAccess();
        
        // Validar datos
        $validation = $this->validateContractData($data);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'errors' => $validation['errors']
            ];
        }

        try {
            // Preparar datos del contrato
            $contractData = [
                'awardee_id' => $data['awardee_id'],
                'fiscal_year_id' => $data['fiscal_year_id'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'type' => $data['type'],
                'contract_mode' => $data['contract_mode']
            ];

            // Crear contrato y obtener su ID
            $contractId = $this->contractsModel->create($contractData);
            
            if ($contractId) {
                // Guardar categorías de negocio asociadas
                if (!empty($data['business_categories'])) {
                    $this->contractsModel->saveContractCategories($contractId, $data['business_categories']);
                }
                
                // Guardar locales asociados
                if (!empty($data['locations'])) {
                    $this->contractsModel->saveContractLocations($contractId, $data['locations']);
                }
                
                // Generar pagos automáticamente
                $this->contractsModel->generateContractPayments($contractId, $data);
                
                return [
                    'success' => true,
                    'message' => 'Contrato creado exitosamente',
                    'contract_id' => $contractId,
                    'redirect' => 'index.php'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al crear el contrato'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Error en store: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al guardar el contrato: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Mostrar contrato específico
     * @param int $id
     * @return array
     */
    public function show($id) {
        // Verificar acceso
        AuthMiddleware::requireUserManagementAccess();
        
        try {
            $contract = $this->contractsModel->getById($id);
            
            if (!$contract) {
                return [
                    'success' => false,
                    'message' => 'Contrato no encontrado'
                ];
            }
            
            $result = [];
            $result['contract'] = $contract;
            $result['contract_categories'] = $this->contractsModel->getContractCategories($id);
            $result['contract_locations'] = $this->contractsModel->getContractLocations($id);
            $result['contract_payments'] = $this->contractsModel->getContractPayments($id);
            $result['page_title'] = 'Detalles del Contrato #' . $id;
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Error en show: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al cargar el contrato'
            ];
        }
    }

    /**
     * Mostrar formulario de edición
     * @param int $id
     * @return array
     */
    public function edit($id) {
        // Verificar acceso
        AuthMiddleware::requireUserManagementAccess();
        
        try {
            $contract = $this->contractsModel->getById($id);
            
            if (!$contract) {
                return [
                    'success' => false,
                    'message' => 'Contrato no encontrado'
                ];
            }
            
            $result = [];
            $result['contract'] = $contract;
            $result['contract_categories'] = $this->contractsModel->getContractCategories($id);
            $result['contract_locations'] = $this->contractsModel->getContractLocations($id);
            $result['awardees'] = $this->contractsModel->getAllAwardees();
            $result['fiscal_years'] = $this->contractsModel->getAllFiscalYears();
            $result['zones'] = $this->contractsModel->getAllZones();
            $result['external_categories'] = $this->contractsModel->getAllExternalCategories();
            $result['internal_categories'] = $this->contractsModel->getAllInternalCategories();
            $result['page_title'] = 'Editar Contrato #' . $id;
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Error en edit: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al cargar el contrato para edición'
            ];
        }
    }

    /**
     * Actualizar contrato
     * @param int $id
     * @param array $data
     * @return array
     */
    public function update($id, $data) {
        // Verificar acceso
        AuthMiddleware::requireUserManagementAccess();
        
        // Validar que el contrato existe
        $contract = $this->contractsModel->getById($id);
        if (!$contract) {
            return [
                'success' => false,
                'message' => 'Contrato no encontrado'
            ];
        }
        
        // Validar datos
        $validation = $this->validateContractData($data);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'errors' => $validation['errors']
            ];
        }

        try {
            // Preparar datos del contrato
            $contractData = [
                'awardee_id' => $data['awardee_id'],
                'fiscal_year_id' => $data['fiscal_year_id'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'type' => $data['type'],
                'contract_mode' => $data['contract_mode']
            ];

            // Actualizar contrato
            $result = $this->contractsModel->update($id, $contractData);
            
            if ($result) {
                // Actualizar categorías de negocio asociadas
                $this->contractsModel->deleteContractCategories($id);
                if (!empty($data['business_categories'])) {
                    $this->contractsModel->saveContractCategories($id, $data['business_categories']);
                }
                
                // Actualizar locales asociados
                $this->contractsModel->deleteContractLocations($id);
                if (!empty($data['locations'])) {
                    $this->contractsModel->saveContractLocations($id, $data['locations']);
                }
                
                // Regenerar pagos si es necesario
                $this->contractsModel->regenerateContractPayments($id, $data);
                
                return [
                    'success' => true,
                    'message' => 'Contrato actualizado exitosamente',
                    'redirect' => 'index.php'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al actualizar el contrato'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Error en update: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al actualizar el contrato: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Eliminar contrato
     * @param int $id
     * @return array
     */
    public function destroy($id) {
        // Verificar acceso
        AuthMiddleware::requireUserManagementAccess();
        
        try {
            $contract = $this->contractsModel->getById($id);
            
            if (!$contract) {
                return [
                    'success' => false,
                    'message' => 'Contrato no encontrado'
                ];
            }
            
            $result = $this->contractsModel->delete($id);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Contrato eliminado exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al eliminar el contrato'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Error en destroy: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al eliminar el contrato: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener sectores por zona (AJAX)
     * @param int $zone_id
     * @return array
     */
    public function getSectorsByZone($zone_id) {
        try {
            $sectors = $this->contractsModel->getSectorsByZone($zone_id);
            return [
                'success' => true,
                'sectors' => $sectors
            ];
        } catch (Exception $e) {
            error_log("Error en getSectorsByZone: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al obtener los sectores'
            ];
        }
    }

    /**
     * Obtener locales por zona y sector (AJAX)
     * @param int $zone_id
     * @param int $sector_id
     * @return array
     */
    public function getStallsByZoneAndSector($zone_id, $sector_id = null) {
        try {
            $stalls = $this->contractsModel->getStallsByZoneAndSector($zone_id, $sector_id);
            return [
                'success' => true,
                'stalls' => $stalls
            ];
        } catch (Exception $e) {
            error_log("Error en getStallsByZoneAndSector: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al obtener los locales'
            ];
        }
    }

    /**
     * Obtener datos del año fiscal por ID (AJAX)
     * @param int $fiscal_year_id
     * @return array
     */
    public function getFiscalYearData($fiscal_year_id) {
        try {
            // Usamos el método privado del modelo ContractsModel
            $fiscal_year = $this->contractsModel->getFiscalYearById($fiscal_year_id);
            
            if (!$fiscal_year) {
                return [
                    'success' => false,
                    'message' => 'Año fiscal no encontrado'
                ];
            }
            
            return [
                'success' => true,
                'fiscal_year' => $fiscal_year
            ];
        } catch (Exception $e) {
            error_log("Error en getFiscalYearData: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al obtener los datos del año fiscal'
            ];
        }
    }

    /**
     * Validar datos del contrato
     * @param array $data
     * @return array
     */
    private function validateContractData($data) {
        $errors = [];
        
        // Validar adjudicatario
        if (empty($data['awardee_id'])) {
            $errors[] = 'El adjudicatario es obligatorio';
        }
        
        // Validar año fiscal
        if (empty($data['fiscal_year_id'])) {
            $errors[] = 'El año fiscal es obligatorio';
        }
        
        // Validar fechas
        if (empty($data['start_date'])) {
            $errors[] = 'La fecha de inicio es obligatoria';
        }
        
        if (empty($data['end_date'])) {
            $errors[] = 'La fecha de finalización es obligatoria';
        }
        
        if (!empty($data['start_date']) && !empty($data['end_date'])) {
            $start_date = new DateTime($data['start_date']);
            $end_date = new DateTime($data['end_date']);
            
            if ($start_date >= $end_date) {
                $errors[] = 'La fecha de inicio debe ser anterior a la fecha de finalización';
            }
        }
        
        // Validar tipo de contrato
        if (empty($data['type']) || !in_array($data['type'], ['simultaneous', 'advance'])) {
            $errors[] = 'Debe seleccionar un tipo de contrato válido';
        }
        
        // Validar modalidad de contrato
        if (empty($data['contract_mode']) || !in_array($data['contract_mode'], ['monthly', 'weekly'])) {
            $errors[] = 'Debe seleccionar una modalidad de contrato válida';
        }
        
        // Validar que tenga al menos una categoría o local
        if (empty($data['business_categories']) && empty($data['locations'])) {
            $errors[] = 'Debe seleccionar al menos una categoría de negocio o un local';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}

?>