<?php

require_once __DIR__ . '/../models/PaymentMethodModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class PaymentMethodController {
    private $paymentMethodModel;
    
    public function __construct() {
        $this->paymentMethodModel = new PaymentMethodModel();
    }

    /**
     * Mostrar lista de métodos de pago con paginación
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
            // Obtener métodos de pago
            $result['payment_methods'] = $this->paymentMethodModel->getAll($page, $limit, $search);
            $result['total_items'] = $this->paymentMethodModel->countItems($search);
            $result['total_pages'] = ceil($result['total_items'] / $limit);
            $result['current_page'] = $page;
            $result['search'] = $search;
            $result['page_title'] = 'Gestión de Métodos de Pago';
            $result['success'] = true;
            
        } catch (Exception $e) {
            $result = [
                'success' => false,
                'message' => 'Error al cargar los métodos de pago: ' . $e->getMessage(),
                'messageType' => 'error'
            ];
        }
        
        return $result;
    }

    /**
     * Crear nuevo método de pago
     * @param array $params
     * @return array
     */
    public function create($params = []) {
        // Verificar acceso
        AuthMiddleware::requireUserManagementAccess();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Procesar el formulario
            $data = [
                'name' => $_POST['name'] ?? '',
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];
            
            try {
                $result = $this->paymentMethodModel->create($data);
                
                if ($result['success']) {
                    $result['messageType'] = 'success';
                    $result['redirect'] = 'index.php';
                } else {
                    $result['messageType'] = 'danger';
                    $result['form_data'] = $data;
                }
                
            } catch (Exception $e) {
                $result = [
                    'success' => false,
                    'message' => 'Error al crear el método de pago: ' . $e->getMessage(),
                    'messageType' => 'danger',
                    'form_data' => $data
                ];
            }
            
        } else {
            // Mostrar formulario
            $result = [
                'success' => true,
                'page_title' => 'Crear Método de Pago',
                'form_data' => [
                    'name' => '',
                    'is_active' => 1
                ]
            ];
        }
        
        return $result;
    }

    /**
     * Mostrar detalles de un método de pago
     * @param array $params
     * @return array
     */
    public function show($params = []) {
        // Verificar acceso
        AuthMiddleware::requireUserManagementAccess();
        
        $id = isset($params['id']) ? (int)$params['id'] : 0;
        
        if (!$id) {
            return [
                'success' => false,
                'message' => 'ID de método de pago no válido',
                'messageType' => 'danger',
                'redirect' => 'index.php'
            ];
        }
        
        try {
            $payment_method = $this->paymentMethodModel->getById($id);
            
            if (!$payment_method) {
                return [
                    'success' => false,
                    'message' => 'Método de pago no encontrado',
                    'messageType' => 'danger',
                    'redirect' => 'index.php'
                ];
            }
            
            return [
                'success' => true,
                'payment_method' => $payment_method,
                'page_title' => 'Detalles del Método de Pago'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al cargar el método de pago: ' . $e->getMessage(),
                'messageType' => 'danger',
                'redirect' => 'index.php'
            ];
        }
    }

    /**
     * Editar método de pago
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
                'message' => 'ID de método de pago no válido',
                'messageType' => 'danger',
                'redirect' => 'index.php'
            ];
        }
        
        try {
            $payment_method = $this->paymentMethodModel->getById($id);
            
            if (!$payment_method) {
                return [
                    'success' => false,
                    'message' => 'Método de pago no encontrado',
                    'messageType' => 'danger',
                    'redirect' => 'index.php'
                ];
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Procesar el formulario de edición
                $data = [
                    'name' => $_POST['name'] ?? '',
                    'is_active' => isset($_POST['is_active']) ? 1 : 0
                ];
                
                $result = $this->paymentMethodModel->update($id, $data);
                
                if ($result['success']) {
                    $result['messageType'] = 'success';
                    $result['redirect'] = 'index.php';
                } else {
                    $result['messageType'] = 'danger';
                    $result['payment_method'] = array_merge($payment_method, $data);
                    $result['page_title'] = 'Editar Método de Pago';
                }
                
            } else {
                // Mostrar formulario de edición
                $result = [
                    'success' => true,
                    'payment_method' => $payment_method,
                    'page_title' => 'Editar Método de Pago'
                ];
            }
            
        } catch (Exception $e) {
            $result = [
                'success' => false,
                'message' => 'Error al editar el método de pago: ' . $e->getMessage(),
                'messageType' => 'danger',
                'redirect' => 'index.php'
            ];
        }
        
        return $result;
    }

    /**
     * Eliminar método de pago
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
                'message' => 'ID de método de pago no válido'
            ];
        }
        
        try {
            $result = $this->paymentMethodModel->delete($id);
            return $result;
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar el método de pago: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Cambiar estado de un método de pago (AJAX)
     * @param array $params
     * @return array
     */
    public function changeStatus($params = []) {
        // Verificar acceso
        AuthMiddleware::requireUserManagementAccess();
        
        $id = isset($params['id']) ? (int)$params['id'] : 0;
        $status = isset($params['status']) ? (int)$params['status'] : 0;
        
        if (!$id) {
            return [
                'success' => false,
                'message' => 'ID de método de pago no válido'
            ];
        }
        
        try {
            $result = $this->paymentMethodModel->changeStatus($id, $status);
            return $result;
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al cambiar el estado: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener todos los métodos de pago para API o listados
     * @return array
     */
    public function getAll() {
        try {
            $items = $this->paymentMethodModel->getAllSimple();
            return [
                'success' => true,
                'data' => $items
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener los métodos de pago: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Buscar métodos de pago
     * @param array $params
     * @return array
     */
    public function search($params = []) {
        try {
            $searchTerm = isset($params['term']) ? trim($params['term']) : '';
            $limit = isset($params['limit']) ? (int)$params['limit'] : 20;
            
            $items = $this->paymentMethodModel->search($searchTerm, $limit);
            return [
                'success' => true,
                'data' => $items
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al buscar métodos de pago: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener estadísticas de métodos de pago
     * @return array
     */
    public function getStats() {
        try {
            $stats = $this->paymentMethodModel->getStats();
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
     * Obtener métodos de pago activos
     * @return array
     */
    public function getActivePaymentMethods() {
        try {
            $methods = $this->paymentMethodModel->getActivePaymentMethods();
            return [
                'success' => true,
                'data' => $methods
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener métodos de pago activos: ' . $e->getMessage()
            ];
        }
    }
}

?>