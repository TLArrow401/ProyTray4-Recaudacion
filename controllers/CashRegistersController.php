<?php

require_once __DIR__ . '/../models/CashRegistersModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class CashRegistersController {
    private $cashRegistersModel;
    
    public function __construct() {
        $this->cashRegistersModel = new CashRegistersModel();
    }

    /**
     * Mostrar lista de cajas registradoras con paginación
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
            // Obtener cajas registradoras
            $result['cash_registers'] = $this->cashRegistersModel->getAll($page, $limit, $search);
            $result['total_items'] = $this->cashRegistersModel->countItems($search);
            $result['total_pages'] = ceil($result['total_items'] / $limit);
            $result['current_page'] = $page;
            $result['search'] = $search;
            $result['page_title'] = 'Gestión de Cajas Registradoras';
            $result['success'] = true;
            
        } catch (Exception $e) {
            $result = [
                'success' => false,
                'message' => 'Error al cargar las cajas registradoras: ' . $e->getMessage(),
                'messageType' => 'error'
            ];
        }
        
        return $result;
    }

    /**
     * Crear nueva caja registradora
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
            'user_id' => $params['user_id'] ?? '',
            'name' => $params['name'] ?? '',
            'status' => $params['status'] ?? 'active',
            'users' => $this->cashRegistersModel->getAvailableUsers()
        ];
        
        // Si no es POST, devolver formulario vacío
        if (!isset($params['_method']) || $params['_method'] !== 'POST') {
            return $result;
        }
        
        try {
            // Validaciones
            $errors = [];
            
            // Validar usuario
            if (empty($params['user_id'])) {
                $errors['user_id'] = 'El usuario asignado es requerido';
            }
            
            // Validar nombre
            if (empty($params['name'])) {
                $errors['name'] = 'El nombre es requerido';
            } elseif (strlen($params['name']) > 100) {
                $errors['name'] = 'El nombre no puede tener más de 100 caracteres';
            }
            
            // Validar estado
            if (empty($params['status'])) {
                $errors['status'] = 'El estado es requerido';
            } elseif (!in_array($params['status'], ['active', 'inactive', 'maintenance'])) {
                $errors['status'] = 'Estado no válido';
            }
            
            if (!empty($errors)) {
                $result['success'] = false;
                $result['errors'] = $errors;
                $result['message'] = 'Por favor corrige los errores en el formulario';
                $result['messageType'] = 'danger';
                return $result;
            }
            
            // Crear caja registradora
            $createResult = $this->cashRegistersModel->create($params);
            
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
                'message' => 'Error al crear la caja registradora: ' . $e->getMessage(),
                'messageType' => 'danger',
                'errors' => [],
                'user_id' => $params['user_id'] ?? '',
                'name' => $params['name'] ?? '',
                'status' => $params['status'] ?? 'active',
                'users' => $this->cashRegistersModel->getAvailableUsers()
            ];
        }
        
        return $result;
    }

    /**
     * Mostrar detalles de una caja registradora
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
                'message' => 'ID de caja registradora no válido',
                'redirect' => 'index.php'
            ];
        }
        
        try {
            $cash_register = $this->cashRegistersModel->getById($id);
            
            if (!$cash_register) {
                return [
                    'success' => false,
                    'message' => 'Caja registradora no encontrada',
                    'redirect' => 'index.php'
                ];
            }
            
            return [
                'success' => true,
                'cash_register' => $cash_register
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al cargar la caja registradora: ' . $e->getMessage(),
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
                'message' => 'ID de caja registradora no válido',
                'redirect' => 'index.php'
            ];
        }
        
        try {
            // Obtener caja registradora actual
            $cash_register = $this->cashRegistersModel->getById($id);
            
            if (!$cash_register) {
                return [
                    'success' => false,
                    'message' => 'Caja registradora no encontrada',
                    'redirect' => 'index.php'
                ];
            }
            
            $result = [
                'success' => true,
                'message' => '',
                'messageType' => '',
                'errors' => [],
                'user_id' => $params['user_id'] ?? $cash_register['user_id'],
                'name' => $params['name'] ?? $cash_register['name'],
                'status' => $params['status'] ?? $cash_register['status'],
                'users' => $this->cashRegistersModel->getAvailableUsers($id)
            ];
            
            // Si no es POST, devolver formulario con datos actuales
            if (!isset($params['_method']) || $params['_method'] !== 'POST') {
                return $result;
            }
            
            // Validaciones
            $errors = [];
            
            // Validar usuario
            if (empty($params['user_id'])) {
                $errors['user_id'] = 'El usuario asignado es requerido';
            }
            
            // Validar nombre
            if (empty($params['name'])) {
                $errors['name'] = 'El nombre es requerido';
            } elseif (strlen($params['name']) > 100) {
                $errors['name'] = 'El nombre no puede tener más de 100 caracteres';
            }
            
            // Validar estado
            if (empty($params['status'])) {
                $errors['status'] = 'El estado es requerido';
            } elseif (!in_array($params['status'], ['active', 'inactive', 'maintenance'])) {
                $errors['status'] = 'Estado no válido';
            }
            
            if (!empty($errors)) {
                $result['success'] = false;
                $result['errors'] = $errors;
                $result['message'] = 'Por favor corrige los errores en el formulario';
                $result['messageType'] = 'danger';
                return $result;
            }
            
            // Actualizar caja registradora
            $updateResult = $this->cashRegistersModel->update($id, $params);
            
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
                'message' => 'Error al editar la caja registradora: ' . $e->getMessage(),
                'messageType' => 'danger',
                'redirect' => 'index.php'
            ];
        }
        
        return $result;
    }

    /**
     * Eliminar caja registradora
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
                'message' => 'ID de caja registradora no válido'
            ];
        }
        
        try {
            $result = $this->cashRegistersModel->delete($id);
            return $result;
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar la caja registradora: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener todas las cajas registradoras para API o listados
     * @return array
     */
    public function getAll() {
        try {
            $items = $this->cashRegistersModel->getAllSimple();
            return [
                'success' => true,
                'data' => $items
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener las cajas registradoras: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener cajas registradoras por estado
     * @param array $params
     * @return array
     */
    public function getByStatus($params = []) {
        try {
            $status = isset($params['status']) ? trim($params['status']) : '';
            
            if (!$status) {
                return [
                    'success' => false,
                    'message' => 'Estado no válido'
                ];
            }
            
            $items = $this->cashRegistersModel->getByStatus($status);
            return [
                'success' => true,
                'data' => $items
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener las cajas registradoras por estado: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Buscar cajas registradoras
     * @param array $params
     * @return array
     */
    public function search($params = []) {
        try {
            $searchTerm = isset($params['term']) ? trim($params['term']) : '';
            $limit = isset($params['limit']) ? (int)$params['limit'] : 20;
            
            $items = $this->cashRegistersModel->search($searchTerm, $limit);
            return [
                'success' => true,
                'data' => $items
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al buscar cajas registradoras: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener estadísticas de cajas registradoras
     * @return array
     */
    public function getStats() {
        try {
            $stats = $this->cashRegistersModel->getStats();
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