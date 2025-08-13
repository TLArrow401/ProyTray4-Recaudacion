<?php

require_once __DIR__ . '/../models/EuroRatesModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class EuroRatesController {
    private $euroRatesModel;
    
    public function __construct() {
        $this->euroRatesModel = new EuroRatesModel();
    }

    /**
     * Mostrar lista de tasas de euro con paginación
     * @param array $params
     * @return array
     */
    public function index($params = []) {
        // Verificar acceso
        AuthMiddleware::requireUserManagementAccess();
        
        // Obtener parámetros
        $page = isset($params['page']) ? (int)$params['page'] : 1;
        $limit = 10;
        $search = isset($params['search']) ? trim($params['search']) : '';
        
        $result = [];
        
        try {
            // Obtener tasas de euro
            $result['euro_rates'] = $this->euroRatesModel->getAll($page, $limit, $search);
            $result['total_items'] = $this->euroRatesModel->countItems($search);
            $result['total_pages'] = ceil($result['total_items'] / $limit);
            $result['current_page'] = $page;
            $result['search'] = $search;
            $result['page_title'] = 'Gestión de Tasas de Euro';
            $result['success'] = true;
            
        } catch (Exception $e) {
            $result = [
                'success' => false,
                'message' => 'Error al cargar las tasas de euro: ' . $e->getMessage(),
                'messageType' => 'error'
            ];
        }
        
        return $result;
    }

    /**
     * Crear nueva tasa de euro
     * @param array $params
     * @return array
     */
    public function create($params = []) {
        // Verificar acceso
        AuthMiddleware::requireUserManagementAccess();
        
        $result = [
            'success' => true,
            'message' => '',
            'messageType' => '',
            'errors' => [],
            'bs_value' => $params['bs_value'] ?? '',
            'month' => $params['month'] ?? '',
            'year' => $params['year'] ?? '',
            'months_list' => $this->euroRatesModel->getMonthsList(),
            'years_list' => $this->euroRatesModel->getYearsList()
        ];
        
        // Si no es POST, devolver formulario vacío
        if (!isset($params['_method']) || $params['_method'] !== 'POST') {
            return $result;
        }
        
        try {
            // Validaciones
            $errors = [];
            
            // Validar valor en bolívares
            if (empty($params['bs_value'])) {
                $errors['bs_value'] = 'El valor en bolívares es requerido';
            } elseif (!is_numeric($params['bs_value']) || (float)$params['bs_value'] <= 0) {
                $errors['bs_value'] = 'El valor en bolívares debe ser un número positivo';
            } elseif ((float)$params['bs_value'] > 999999.99) {
                $errors['bs_value'] = 'El valor en bolívares es demasiado alto';
            }
            
            // Validar mes (opcional pero si se proporciona debe ser válido)
            if (!empty($params['month'])) {
                $validMonths = array_keys($this->euroRatesModel->getMonthsList());
                if (!in_array(strtolower($params['month']), $validMonths)) {
                    $errors['month'] = 'El mes seleccionado no es válido';
                }
            }
            
            // Validar año (opcional pero si se proporciona debe ser válido)
            if (!empty($params['year'])) {
                $validYears = $this->euroRatesModel->getYearsList();
                if (!in_array($params['year'], $validYears)) {
                    $errors['year'] = 'El año seleccionado no es válido';
                }
            }
            
            // Si se proporciona mes, debe proporcionarse año y viceversa
            if (!empty($params['month']) && empty($params['year'])) {
                $errors['year'] = 'Si especifica un mes, debe especificar también el año';
            }
            if (!empty($params['year']) && empty($params['month'])) {
                $errors['month'] = 'Si especifica un año, debe especificar también el mes';
            }
            
            if (!empty($errors)) {
                $result['success'] = false;
                $result['errors'] = $errors;
                $result['message'] = 'Por favor corrige los errores en el formulario';
                $result['messageType'] = 'danger';
                return $result;
            }
            
            // Crear tasa de euro
            $createResult = $this->euroRatesModel->create($params);
            
            if ($createResult['success']) {
                $result['success'] = true;
                $result['message'] = $createResult['message'];
                $result['messageType'] = 'success';
                $result['redirect'] = 'index.php';
            } else {
                $result['success'] = false;
                $result['message'] = $createResult['message'];
                $result['messageType'] = 'danger';
                $result['errors'] = $createResult['errors'] ?? [];
            }
            
        } catch (Exception $e) {
            $result = [
                'success' => false,
                'message' => 'Error al crear la tasa de euro: ' . $e->getMessage(),
                'messageType' => 'danger',
                'errors' => [],
                'bs_value' => $params['bs_value'] ?? '',
                'month' => $params['month'] ?? '',
                'year' => $params['year'] ?? '',
                'months_list' => $this->euroRatesModel->getMonthsList(),
                'years_list' => $this->euroRatesModel->getYearsList()
            ];
        }
        
        return $result;
    }

    /**
     * Mostrar detalles de una tasa de euro
     * @param array $params
     * @return array
     */
    public function view($params = []) {
        // Verificar acceso
        AuthMiddleware::requireUserManagementAccess();
        
        $id = isset($params['id']) ? (int)$params['id'] : 0;
        
        if (!$id) {
            return [
                'success' => false,
                'message' => 'ID de tasa de euro no válido',
                'redirect' => 'index.php'
            ];
        }
        
        try {
            $euroRate = $this->euroRatesModel->getById($id);
            
            if (!$euroRate) {
                return [
                    'success' => false,
                    'message' => 'Tasa de euro no encontrada',
                    'redirect' => 'index.php'
                ];
            }
            
            return [
                'success' => true,
                'euro_rate' => $euroRate,
                'formatted_period' => $this->euroRatesModel->formatPeriod($euroRate),
                'formatted_value' => $this->euroRatesModel->formatBsValue($euroRate['bs_value']),
                'page_title' => 'Detalles de Tasa de Euro'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al cargar la tasa de euro: ' . $e->getMessage(),
                'redirect' => 'index.php'
            ];
        }
    }

    /**
     * Editar tasa de euro
     * @param array $params
     * @return array
     */
    public function edit($params = []) {
        // Verificar acceso
        AuthMiddleware::requireUserManagementAccess();
        
        $id = isset($params['id']) ? (int)$params['id'] : 0;
        
        if (!$id) {
            return [
                'success' => false,
                'message' => 'ID de tasa de euro no válido',
                'redirect' => 'index.php'
            ];
        }
        
        try {
            $euroRate = $this->euroRatesModel->getById($id);
            
            if (!$euroRate) {
                return [
                    'success' => false,
                    'message' => 'Tasa de euro no encontrada',
                    'redirect' => 'index.php'
                ];
            }
            
            $result = [
                'success' => true,
                'message' => '',
                'messageType' => '',
                'errors' => [],
                'euro_rate' => $euroRate,
                'bs_value' => $params['bs_value'] ?? $euroRate['bs_value'],
                'month' => $params['month'] ?? $euroRate['month'],
                'year' => $params['year'] ?? $euroRate['year'],
                'months_list' => $this->euroRatesModel->getMonthsList(),
                'years_list' => $this->euroRatesModel->getYearsList(),
                'page_title' => 'Editar Tasa de Euro'
            ];
            
            // Si no es POST, devolver formulario con datos actuales
            if (!isset($params['_method']) || $params['_method'] !== 'POST') {
                return $result;
            }
            
            // Validaciones para actualización
            $errors = [];
            
            // Validar valor en bolívares
            if (empty($params['bs_value'])) {
                $errors['bs_value'] = 'El valor en bolívares es requerido';
            } elseif (!is_numeric($params['bs_value']) || (float)$params['bs_value'] <= 0) {
                $errors['bs_value'] = 'El valor en bolívares debe ser un número positivo';
            } elseif ((float)$params['bs_value'] > 999999.99) {
                $errors['bs_value'] = 'El valor en bolívares es demasiado alto';
            }
            
            // Validar mes (opcional pero si se proporciona debe ser válido)
            if (!empty($params['month'])) {
                $validMonths = array_keys($this->euroRatesModel->getMonthsList());
                if (!in_array(strtolower($params['month']), $validMonths)) {
                    $errors['month'] = 'El mes seleccionado no es válido';
                }
            }
            
            // Validar año (opcional pero si se proporciona debe ser válido)
            if (!empty($params['year'])) {
                $validYears = $this->euroRatesModel->getYearsList();
                if (!in_array($params['year'], $validYears)) {
                    $errors['year'] = 'El año seleccionado no es válido';
                }
            }
            
            // Si se proporciona mes, debe proporcionarse año y viceversa
            if (!empty($params['month']) && empty($params['year'])) {
                $errors['year'] = 'Si especifica un mes, debe especificar también el año';
            }
            if (!empty($params['year']) && empty($params['month'])) {
                $errors['month'] = 'Si especifica un año, debe especificar también el mes';
            }
            
            if (!empty($errors)) {
                $result['success'] = false;
                $result['errors'] = $errors;
                $result['message'] = 'Por favor corrige los errores en el formulario';
                $result['messageType'] = 'danger';
                return $result;
            }
            
            // Actualizar tasa de euro
            $updateResult = $this->euroRatesModel->update($id, $params);
            
            if ($updateResult['success']) {
                $result['success'] = true;
                $result['message'] = $updateResult['message'];
                $result['messageType'] = 'success';
                $result['redirect'] = 'index.php';
            } else {
                $result['success'] = false;
                $result['message'] = $updateResult['message'];
                $result['messageType'] = 'danger';
                $result['errors'] = $updateResult['errors'] ?? [];
            }
            
        } catch (Exception $e) {
            $result = [
                'success' => false,
                'message' => 'Error al actualizar la tasa de euro: ' . $e->getMessage(),
                'messageType' => 'danger',
                'errors' => [],
                'bs_value' => $params['bs_value'] ?? '',
                'month' => $params['month'] ?? '',
                'year' => $params['year'] ?? '',
                'months_list' => $this->euroRatesModel->getMonthsList(),
                'years_list' => $this->euroRatesModel->getYearsList()
            ];
        }
        
        return $result;
    }

    /**
     * Eliminar tasa de euro
     * @param array $params
     * @return array
     */
    public function delete($params = []) {
        // Verificar acceso
        AuthMiddleware::requireUserManagementAccess();
        
        $id = isset($params['id']) ? (int)$params['id'] : 0;
        
        if (!$id) {
            return [
                'success' => false,
                'message' => 'ID de tasa de euro no válido'
            ];
        }
        
        try {
            $result = $this->euroRatesModel->delete($id);
            return $result;
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar la tasa de euro: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener tasas de euro para uso en selects
     * @return array
     */
    public function getForSelect() {
        try {
            return $this->euroRatesModel->getForSelect();
        } catch (Exception $e) {
            error_log("Error al obtener tasas para select: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener la tasa más reciente
     * @return array|false
     */
    public function getLatestRate() {
        try {
            return $this->euroRatesModel->getLatestRate();
        } catch (Exception $e) {
            error_log("Error al obtener tasa más reciente: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Manejar solicitudes AJAX
     * @param string $action
     * @param array $params
     * @return array
     */
    public function handleAjax($action, $params = []) {
        switch ($action) {
            case 'delete':
                return $this->delete($params);
                
            case 'get_latest_rate':
                $rate = $this->getLatestRate();
                if ($rate) {
                    return [
                        'success' => true,
                        'rate' => $rate,
                        'formatted_value' => $this->euroRatesModel->formatBsValue($rate['bs_value']),
                        'formatted_period' => $this->euroRatesModel->formatPeriod($rate)
                    ];
                }
                return ['success' => false, 'message' => 'No se encontró ninguna tasa'];
                
            case 'validate_period':
                if (isset($params['month']) && isset($params['year'])) {
                    $excludeId = isset($params['exclude_id']) ? (int)$params['exclude_id'] : null;
                    $exists = $this->euroRatesModel->existsForMonthYear($params['month'], $params['year'], $excludeId);
                    return [
                        'success' => true,
                        'exists' => $exists,
                        'message' => $exists ? 'Ya existe una tasa para este período' : 'Período disponible'
                    ];
                }
                return ['success' => false, 'message' => 'Datos insuficientes'];
                
            default:
                return ['success' => false, 'message' => 'Acción no válida'];
        }
    }
}

?>