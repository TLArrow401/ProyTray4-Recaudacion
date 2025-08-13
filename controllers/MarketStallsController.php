<?php

require_once __DIR__ . '/../models/MarketStallsModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class MarketStallsController {
    private $marketStallsModel;
    
    public function __construct() {
        $this->marketStallsModel = new MarketStallsModel();
    }

    /**
     * Mostrar lista de locales con paginación
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
            // Obtener locales
            $result['market_stalls'] = $this->marketStallsModel->getAll($page, $limit, $search);
            $result['total_items'] = $this->marketStallsModel->countItems($search);
            $result['total_pages'] = ceil($result['total_items'] / $limit);
            $result['current_page'] = $page;
            $result['search'] = $search;
            $result['page_title'] = 'Gestión de Locales de Mercado';
            $result['success'] = true;
            
        } catch (Exception $e) {
            $result = [
                'success' => false,
                'message' => 'Error al cargar los locales: ' . $e->getMessage(),
                'messageType' => 'error'
            ];
        }
        
        return $result;
    }

    /**
     * Crear nuevo local
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
            'sector_id' => $params['sector_id'] ?? '',
            'stall_number' => $params['stall_number'] ?? '',
            'location_description' => $params['location_description'] ?? '',
            'zones' => $this->marketStallsModel->getAvailableZones(),
            'sectors' => $this->marketStallsModel->getAvailableSectors()
        ];
        
        // Si no es POST, devolver formulario vacío
        if (!isset($params['_method']) || $params['_method'] !== 'POST') {
            return $result;
        }
        
        try {
            // Validaciones
            $errors = [];
            
            // Validar sector
            if (empty($params['sector_id'])) {
                $errors['sector_id'] = 'El sector es requerido';
            }
            
            // Validar número del local
            if (empty($params['stall_number'])) {
                $errors['stall_number'] = 'El número del local es requerido';
            } elseif (strlen($params['stall_number']) > 50) {
                $errors['stall_number'] = 'El número del local no puede tener más de 50 caracteres';
            }
            
            // Validar descripción de ubicación
            if (!empty($params['location_description']) && strlen($params['location_description']) > 255) {
                $errors['location_description'] = 'La descripción de ubicación no puede tener más de 255 caracteres';
            }
            
            if (!empty($errors)) {
                $result['success'] = false;
                $result['errors'] = $errors;
                $result['message'] = 'Por favor corrige los errores en el formulario';
                $result['messageType'] = 'danger';
                return $result;
            }
            
            // Crear local
            $createResult = $this->marketStallsModel->create($params);
            
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
                'message' => 'Error al crear el local: ' . $e->getMessage(),
                'messageType' => 'danger',
                'errors' => [],
                'sector_id' => $params['sector_id'] ?? '',
                'stall_number' => $params['stall_number'] ?? '',
                'location_description' => $params['location_description'] ?? '',
                'zones' => $this->marketStallsModel->getAvailableZones(),
                'sectors' => $this->marketStallsModel->getAvailableSectors()
            ];
        }
        
        return $result;
    }

    /**
     * Mostrar detalles de un local
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
                'message' => 'ID de local no válido',
                'redirect' => 'index.php'
            ];
        }
        
        try {
            $market_stall = $this->marketStallsModel->getById($id);
            
            if (!$market_stall) {
                return [
                    'success' => false,
                    'message' => 'Local no encontrado',
                    'redirect' => 'index.php'
                ];
            }
            
            return [
                'success' => true,
                'market_stall' => $market_stall
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al cargar el local: ' . $e->getMessage(),
                'redirect' => 'index.php'
            ];
        }
    }

    /**
     * Mostrar formulario de edición y procesar actualización
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
                'message' => 'ID de local no válido',
                'redirect' => 'index.php'
            ];
        }
        
        try {
            // Obtener local actual
            $market_stall = $this->marketStallsModel->getById($id);
            
            if (!$market_stall) {
                return [
                    'success' => false,
                    'message' => 'Local no encontrado',
                    'redirect' => 'index.php'
                ];
            }
            
            $result = [
                'success' => true,
                'message' => '',
                'messageType' => '',
                'errors' => [],
                'sector_id' => $params['sector_id'] ?? $market_stall['sector_id'],
                'stall_number' => $params['stall_number'] ?? $market_stall['stall_number'],
                'location_description' => $params['location_description'] ?? $market_stall['location_description'],
                'zones' => $this->marketStallsModel->getAvailableZones(),
                'sectors' => $this->marketStallsModel->getAvailableSectors(),
                'current_zone_id' => $market_stall['zone_id'] ?? null
            ];
            
            // Si no es POST, devolver formulario con datos actuales
            if (!isset($params['_method']) || $params['_method'] !== 'POST') {
                return $result;
            }
            
            // Validaciones
            $errors = [];
            
            // Validar sector
            if (empty($params['sector_id'])) {
                $errors['sector_id'] = 'El sector es requerido';
            }
            
            // Validar número del local
            if (empty($params['stall_number'])) {
                $errors['stall_number'] = 'El número del local es requerido';
            } elseif (strlen($params['stall_number']) > 50) {
                $errors['stall_number'] = 'El número del local no puede tener más de 50 caracteres';
            }
            
            // Validar descripción de ubicación
            if (!empty($params['location_description']) && strlen($params['location_description']) > 255) {
                $errors['location_description'] = 'La descripción de ubicación no puede tener más de 255 caracteres';
            }
            
            if (!empty($errors)) {
                $result['success'] = false;
                $result['errors'] = $errors;
                $result['message'] = 'Por favor corrige los errores en el formulario';
                $result['messageType'] = 'danger';
                return $result;
            }
            
            // Actualizar local
            $updateResult = $this->marketStallsModel->update($id, $params);
            
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
                'message' => 'Error al editar el local: ' . $e->getMessage(),
                'messageType' => 'danger',
                'redirect' => 'index.php'
            ];
        }
        
        return $result;
    }

    /**
     * Eliminar local
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
                'message' => 'ID de local no válido'
            ];
        }
        
        try {
            $result = $this->marketStallsModel->delete($id);
            return $result;
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar el local: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener todos los locales para API o listados
     * @return array
     */
    public function getAll() {
        try {
            $items = $this->marketStallsModel->getAllSimple();
            return [
                'success' => true,
                'data' => $items
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener los locales: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener locales por sector
     * @param array $params
     * @return array
     */
    public function getBySector($params = []) {
        try {
            $sector_id = isset($params['sector_id']) ? (int)$params['sector_id'] : 0;
            
            if (!$sector_id) {
                return [
                    'success' => false,
                    'message' => 'ID de sector no válido'
                ];
            }
            
            $items = $this->marketStallsModel->getBySector($sector_id);
            return [
                'success' => true,
                'data' => $items
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener los locales por sector: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener sectores por zona (para AJAX)
     * @param array $params
     * @return array
     */
    public function getSectorsByZone($params = []) {
        try {
            $zone_id = isset($params['zone_id']) ? (int)$params['zone_id'] : 0;
            
            if (!$zone_id) {
                return [
                    'success' => false,
                    'message' => 'ID de zona no válido'
                ];
            }
            
            $items = $this->marketStallsModel->getSectorsByZone($zone_id);
            return [
                'success' => true,
                'data' => $items
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener los sectores por zona: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Buscar locales
     * @param array $params
     * @return array
     */
    public function search($params = []) {
        try {
            $searchTerm = isset($params['term']) ? trim($params['term']) : '';
            $limit = isset($params['limit']) ? (int)$params['limit'] : 20;
            
            $items = $this->marketStallsModel->search($searchTerm, $limit);
            return [
                'success' => true,
                'data' => $items
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al buscar locales: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener estadísticas de locales
     * @return array
     */
    public function getStats() {
        try {
            $stats = $this->marketStallsModel->getStats();
            return [
                'success' => true,
                'data' => $stats
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ];
        }
    }
}

?>