<?php

require_once __DIR__ . '/../models/ZonesModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class ZonesController {
    private $zonesModel;
    
    public function __construct() {
        $this->zonesModel = new ZonesModel();
    }

    /**
     * Mostrar lista de zonas con paginación
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
            // Obtener zonas
            $result['zones'] = $this->zonesModel->getAll($page, $limit, $search);
            $result['total_items'] = $this->zonesModel->countItems($search);
            $result['total_pages'] = ceil($result['total_items'] / $limit);
            $result['current_page'] = $page;
            $result['search'] = $search;
            $result['page_title'] = 'Gestión de Zonas de Mercado';
            $result['success'] = true;
            
        } catch (Exception $e) {
            $result = [
                'success' => false,
                'message' => 'Error al cargar las zonas: ' . $e->getMessage(),
                'messageType' => 'error'
            ];
        }
        
        return $result;
    }

    /**
     * Crear nueva zona
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
            'description' => $params['description'] ?? ''
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
            
            // Crear zona
            $createResult = $this->zonesModel->create($params);
            
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
                'message' => 'Error al crear la zona: ' . $e->getMessage(),
                'messageType' => 'danger',
                'errors' => [],
                'name' => $params['name'] ?? '',
                'description' => $params['description'] ?? ''
            ];
        }
        
        return $result;
    }

    /**
     * Mostrar detalles de una zona
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
                'message' => 'ID de zona no válido',
                'redirect' => 'index.php'
            ];
        }
        
        try {
            $zone = $this->zonesModel->getById($id);
            
            if (!$zone) {
                return [
                    'success' => false,
                    'message' => 'Zona no encontrada',
                    'redirect' => 'index.php'
                ];
            }
            
            return [
                'success' => true,
                'zone' => $zone
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al cargar la zona: ' . $e->getMessage(),
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
                'message' => 'ID de zona no válido',
                'redirect' => 'index.php'
            ];
        }
        
        try {
            // Obtener zona actual
            $zone = $this->zonesModel->getById($id);
            
            if (!$zone) {
                return [
                    'success' => false,
                    'message' => 'Zona no encontrada',
                    'redirect' => 'index.php'
                ];
            }
            
            $result = [
                'success' => true,
                'message' => '',
                'messageType' => '',
                'errors' => [],
                'name' => $params['name'] ?? $zone['name'],
                'description' => $params['description'] ?? $zone['description']
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
            
            // Actualizar zona
            $updateResult = $this->zonesModel->update($id, $params);
            
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
                'message' => 'Error al editar la zona: ' . $e->getMessage(),
                'messageType' => 'danger',
                'redirect' => 'index.php'
            ];
        }
        
        return $result;
    }

    /**
     * Eliminar zona
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
                'message' => 'ID de zona no válido'
            ];
        }
        
        try {
            $result = $this->zonesModel->delete($id);
            return $result;
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar la zona: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener todas las zonas para API o listados
     * @return array
     */
    public function getAll() {
        try {
            $items = $this->zonesModel->getAllSimple();
            return [
                'success' => true,
                'data' => $items
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener las zonas: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Buscar zonas
     * @param array $params
     * @return array
     */
    public function search($params = []) {
        try {
            $searchTerm = isset($params['term']) ? trim($params['term']) : '';
            $limit = isset($params['limit']) ? (int)$params['limit'] : 20;
            
            $items = $this->zonesModel->search($searchTerm, $limit);
            return [
                'success' => true,
                'data' => $items
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al buscar zonas: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener estadísticas de zonas
     * @return array
     */
    public function getStats() {
        try {
            $stats = $this->zonesModel->getStats();
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