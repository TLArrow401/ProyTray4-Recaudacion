<?php

require_once __DIR__ . '/../models/ExternalItemModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class ExternalItemsController {
    private $externalItemModel;
    
    public function __construct() {
        $this->externalItemModel = new ExternalItemModel();
    }

    /**
     * Mostrar lista de rubros externos con paginación
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
            // Obtener rubros externos
            $result['external_items'] = $this->externalItemModel->getAll($page, $limit, $search);
            $result['total_items'] = $this->externalItemModel->countItems($search);
            $result['total_pages'] = ceil($result['total_items'] / $limit);
            $result['current_page'] = $page;
            $result['search'] = $search;
            $result['page_title'] = 'Gestión de Rubros Externos';
            $result['success'] = true;
            
        } catch (Exception $e) {
            $result = [
                'success' => false,
                'message' => 'Error al cargar los rubros externos: ' . $e->getMessage(),
                'messageType' => 'error'
            ];
        }
        
        return $result;
    }

    /**
     * Crear nuevo rubro externo
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
            'name' => $params['name'] ?? '',
            'installation_type' => $params['installation_type'] ?? '',
            'payment_count' => $params['payment_count'] ?? ''
        ];
        
        // Si no es POST, devolver formulario vacío
        if (!isset($params['_method']) || $params['_method'] !== 'POST') {
            return $result;
        }
        
        try {
            // Validaciones
            $errors = [];
            
            // Validar nombre
            if (empty($params['name'])) {
                $errors['name'] = 'El nombre es requerido';
            } elseif (strlen($params['name']) > 100) {
                $errors['name'] = 'El nombre no puede tener más de 100 caracteres';
            }
            
            // Validar tipo de instalación
            if (!empty($params['installation_type']) && strlen($params['installation_type']) > 100) {
                $errors['installation_type'] = 'El tipo de instalación no puede tener más de 100 caracteres';
            }
            
            // Validar número de cobros
            if (!empty($params['payment_count'])) {
                if (!is_numeric($params['payment_count']) || (float)$params['payment_count'] < 0) {
                    $errors['payment_count'] = 'El número de cobros debe ser un valor positivo';
                }
            }
            
            if (!empty($errors)) {
                $result['success'] = false;
                $result['errors'] = $errors;
                $result['message'] = 'Por favor corrige los errores en el formulario';
                $result['messageType'] = 'danger';
                return $result;
            }
            
            // Crear rubro externo
            $createResult = $this->externalItemModel->create($params);
            
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
                'message' => 'Error al crear el rubro externo: ' . $e->getMessage(),
                'messageType' => 'danger',
                'errors' => [],
                'name' => $params['name'] ?? '',
                'installation_type' => $params['installation_type'] ?? '',
                'payment_count' => $params['payment_count'] ?? ''
            ];
        }
        
        return $result;
    }

    /**
     * Mostrar detalles de un rubro externo
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
                'message' => 'ID de rubro externo no válido',
                'redirect' => 'index.php'
            ];
        }
        
        try {
            $external_item = $this->externalItemModel->getById($id);
            
            if (!$external_item) {
                return [
                    'success' => false,
                    'message' => 'Rubro externo no encontrado',
                    'redirect' => 'index.php'
                ];
            }
            
            return [
                'success' => true,
                'external_item' => $external_item
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al cargar el rubro externo: ' . $e->getMessage(),
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
                'message' => 'ID de rubro externo no válido',
                'redirect' => 'index.php'
            ];
        }
        
        try {
            // Obtener rubro externo actual
            $external_item = $this->externalItemModel->getById($id);
            
            if (!$external_item) {
                return [
                    'success' => false,
                    'message' => 'Rubro externo no encontrado',
                    'redirect' => 'index.php'
                ];
            }
            
            $result = [
                'success' => true,
                'message' => '',
                'messageType' => '',
                'errors' => [],
                'name' => $params['name'] ?? $external_item['name'],
                'installation_type' => $params['installation_type'] ?? $external_item['installation_type'],
                'payment_count' => $params['payment_count'] ?? $external_item['payment_count']
            ];
            
            // Si no es POST, devolver formulario con datos actuales
            if (!isset($params['_method']) || $params['_method'] !== 'POST') {
                return $result;
            }
            
            // Validaciones
            $errors = [];
            
            // Validar nombre
            if (empty($params['name'])) {
                $errors['name'] = 'El nombre es requerido';
            } elseif (strlen($params['name']) > 100) {
                $errors['name'] = 'El nombre no puede tener más de 100 caracteres';
            }
            
            // Validar tipo de instalación
            if (!empty($params['installation_type']) && strlen($params['installation_type']) > 100) {
                $errors['installation_type'] = 'El tipo de instalación no puede tener más de 100 caracteres';
            }
            
            // Validar número de cobros
            if (!empty($params['payment_count'])) {
                if (!is_numeric($params['payment_count']) || (float)$params['payment_count'] < 0) {
                    $errors['payment_count'] = 'El número de cobros debe ser un valor positivo';
                }
            }
            
            if (!empty($errors)) {
                $result['success'] = false;
                $result['errors'] = $errors;
                $result['message'] = 'Por favor corrige los errores en el formulario';
                $result['messageType'] = 'danger';
                return $result;
            }
            
            // Actualizar rubro externo
            $updateResult = $this->externalItemModel->update($id, $params);
            
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
                'message' => 'Error al editar el rubro externo: ' . $e->getMessage(),
                'messageType' => 'danger',
                'redirect' => 'index.php'
            ];
        }
        
        return $result;
    }

    /**
     * Eliminar rubro externo
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
                'message' => 'ID de rubro externo no válido'
            ];
        }
        
        try {
            $result = $this->externalItemModel->delete($id);
            return $result;
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar el rubro externo: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener todos los rubros externos para API o listados
     * @return array
     */
    public function getAll() {
        try {
            $items = $this->externalItemModel->getAllSimple();
            return [
                'success' => true,
                'data' => $items
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener los rubros externos: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Buscar rubros externos
     * @param array $params
     * @return array
     */
    public function search($params = []) {
        try {
            $searchTerm = isset($params['term']) ? trim($params['term']) : '';
            $limit = isset($params['limit']) ? (int)$params['limit'] : 20;
            
            $items = $this->externalItemModel->search($searchTerm, $limit);
            return [
                'success' => true,
                'data' => $items
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al buscar rubros externos: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener estadísticas de rubros externos
     * @return array
     */
    public function getStats() {
        try {
            $stats = $this->externalItemModel->getStats();
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

    /**
     * Obtener tipos de instalación disponibles
     * @return array
     */
    public function getInstallationTypes() {
        try {
            $types = $this->externalItemModel->getInstallationTypes();
            return [
                'success' => true,
                'data' => $types
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener tipos de instalación: ' . $e->getMessage()
            ];
        }
    }
}

?>