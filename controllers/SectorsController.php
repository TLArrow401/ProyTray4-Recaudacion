<?php

require_once __DIR__ . '/../models/SectorsModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class SectorsController {
    private $sectorsModel;
    
    public function __construct() {
        $this->sectorsModel = new SectorsModel();
    }

    /**
     * Mostrar lista de sectores con paginación
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
            // Obtener sectores
            $result['sectors'] = $this->sectorsModel->getAll($page, $limit, $search);
            $result['total_items'] = $this->sectorsModel->countItems($search);
            $result['total_pages'] = ceil($result['total_items'] / $limit);
            $result['current_page'] = $page;
            $result['search'] = $search;
            $result['page_title'] = 'Gestión de Sectores de Mercado';
            $result['success'] = true;
            
        } catch (Exception $e) {
            $result = [
                'success' => false,
                'message' => 'Error al cargar los sectores: ' . $e->getMessage(),
                'messageType' => 'error'
            ];
        }
        
        return $result;
    }

    /**
     * Crear nuevo sector
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
            'zone_id' => $params['zone_id'] ?? '',
            'name' => $params['name'] ?? '',
            'description' => $params['description'] ?? '',
            'zones' => $this->sectorsModel->getAvailableZones()
        ];
        
        // Si no es POST, devolver formulario vacío
        if (!isset($params['_method']) || $params['_method'] !== 'POST') {
            return $result;
        }
        
        try {
            // Validaciones
            $errors = [];
            
            // Validar zona
            if (empty($params['zone_id'])) {
                $errors['zone_id'] = 'La zona es requerida';
            }
            
            // Validar nombre
            if (empty($params['name'])) {
                $errors['name'] = 'El nombre es requerido';
            } elseif (strlen($params['name']) > 100) {
                $errors['name'] = 'El nombre no puede tener más de 100 caracteres';
            }
            
            // Validar descripción
            if (!empty($params['description']) && strlen($params['description']) > 1000) {
                $errors['description'] = 'La descripción no puede tener más de 1000 caracteres';
            }
            
            if (!empty($errors)) {
                $result['success'] = false;
                $result['errors'] = $errors;
                $result['message'] = 'Por favor corrige los errores en el formulario';
                $result['messageType'] = 'danger';
                return $result;
            }
            
            // Crear sector
            $createResult = $this->sectorsModel->create($params);
            
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
                'message' => 'Error al crear el sector: ' . $e->getMessage(),
                'messageType' => 'danger',
                'errors' => [],
                'zone_id' => $params['zone_id'] ?? '',
                'name' => $params['name'] ?? '',
                'description' => $params['description'] ?? '',
                'zones' => $this->sectorsModel->getAvailableZones()
            ];
        }
        
        return $result;
    }

    /**
     * Mostrar detalles de un sector
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
                'message' => 'ID de sector no válido',
                'redirect' => 'index.php'
            ];
        }
        
        try {
            $sector = $this->sectorsModel->getById($id);
            
            if (!$sector) {
                return [
                    'success' => false,
                    'message' => 'Sector no encontrado',
                    'redirect' => 'index.php'
                ];
            }
            
            return [
                'success' => true,
                'sector' => $sector
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al cargar el sector: ' . $e->getMessage(),
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
                'message' => 'ID de sector no válido',
                'redirect' => 'index.php'
            ];
        }
        
        try {
            // Obtener sector actual
            $sector = $this->sectorsModel->getById($id);
            
            if (!$sector) {
                return [
                    'success' => false,
                    'message' => 'Sector no encontrado',
                    'redirect' => 'index.php'
                ];
            }
            
            $result = [
                'success' => true,
                'message' => '',
                'messageType' => '',
                'errors' => [],
                'zone_id' => $params['zone_id'] ?? $sector['zone_id'],
                'name' => $params['name'] ?? $sector['name'],
                'description' => $params['description'] ?? $sector['description'],
                'zones' => $this->sectorsModel->getAvailableZones()
            ];
            
            // Si no es POST, devolver formulario con datos actuales
            if (!isset($params['_method']) || $params['_method'] !== 'POST') {
                return $result;
            }
            
            // Validaciones
            $errors = [];
            
            // Validar zona
            if (empty($params['zone_id'])) {
                $errors['zone_id'] = 'La zona es requerida';
            }
            
            // Validar nombre
            if (empty($params['name'])) {
                $errors['name'] = 'El nombre es requerido';
            } elseif (strlen($params['name']) > 100) {
                $errors['name'] = 'El nombre no puede tener más de 100 caracteres';
            }
            
            // Validar descripción
            if (!empty($params['description']) && strlen($params['description']) > 1000) {
                $errors['description'] = 'La descripción no puede tener más de 1000 caracteres';
            }
            
            if (!empty($errors)) {
                $result['success'] = false;
                $result['errors'] = $errors;
                $result['message'] = 'Por favor corrige los errores en el formulario';
                $result['messageType'] = 'danger';
                return $result;
            }
            
            // Actualizar sector
            $updateResult = $this->sectorsModel->update($id, $params);
            
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
                'message' => 'Error al editar el sector: ' . $e->getMessage(),
                'messageType' => 'danger',
                'redirect' => 'index.php'
            ];
        }
        
        return $result;
    }

    /**
     * Eliminar sector
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
                'message' => 'ID de sector no válido'
            ];
        }
        
        try {
            $result = $this->sectorsModel->delete($id);
            return $result;
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar el sector: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener todos los sectores para API o listados
     * @return array
     */
    public function getAll() {
        try {
            $items = $this->sectorsModel->getAllSimple();
            return [
                'success' => true,
                'data' => $items
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener los sectores: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener sectores por zona
     * @param array $params
     * @return array
     */
    public function getByZone($params = []) {
        try {
            $zone_id = isset($params['zone_id']) ? (int)$params['zone_id'] : 0;
            
            if (!$zone_id) {
                return [
                    'success' => false,
                    'message' => 'ID de zona no válido'
                ];
            }
            
            $items = $this->sectorsModel->getByZone($zone_id);
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
     * Buscar sectores
     * @param array $params
     * @return array
     */
    public function search($params = []) {
        try {
            $searchTerm = isset($params['term']) ? trim($params['term']) : '';
            $limit = isset($params['limit']) ? (int)$params['limit'] : 20;
            
            $items = $this->sectorsModel->search($searchTerm, $limit);
            return [
                'success' => true,
                'data' => $items
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al buscar sectores: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener estadísticas de sectores
     * @return array
     */
    public function getStats() {
        try {
            $stats = $this->sectorsModel->getStats();
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