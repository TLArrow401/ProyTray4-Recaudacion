<?php

require_once __DIR__ . '/../models/InternalItemModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class InternalItemsController {
    private $internalItemModel;
    
    public function __construct() {
        $this->internalItemModel = new InternalItemModel();
    }

    /**
     * Mostrar lista de rubros internos con paginación
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
            // Obtener rubros internos
            $result['internal_items'] = $this->internalItemModel->getAll($page, $limit, $search);
            $result['total_items'] = $this->internalItemModel->countItems($search);
            $result['total_pages'] = ceil($result['total_items'] / $limit);
            $result['current_page'] = $page;
            $result['search'] = $search;
            $result['page_title'] = 'Gestión de Rubros Internos';
            $result['success'] = true;
            
        } catch (Exception $e) {
            $result = [
                'success' => false,
                'message' => 'Error al cargar los rubros internos: ' . $e->getMessage(),
                'messageType' => 'error'
            ];
        }
        
        return $result;
    }

    /**
     * Mostrar formulario de creación y procesar creación de rubro interno
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
            'name' => '',
            'payment_count' => ''
        ];
        
        // Si es POST, procesar creación
        if (isset($params['_method']) && $params['_method'] === 'POST') {
            $name = trim($params['name'] ?? '');
            $payment_count = $params['payment_count'] ?? '';
            
            // Validaciones
            if (empty($name)) {
                $result['errors']['name'] = 'El nombre es requerido';
            } elseif (strlen($name) > 100) {
                $result['errors']['name'] = 'El nombre no puede tener más de 100 caracteres';
            }
            
            if (empty($payment_count)) {
                $result['errors']['payment_count'] = 'El número de cobros es requerido';
            } elseif (!is_numeric($payment_count) || $payment_count < 0) {
                $result['errors']['payment_count'] = 'El número de cobros debe ser un valor numérico positivo';
            }
            
            // Verificar si el nombre ya existe
            if (empty($result['errors']) && $this->internalItemModel->existsByName($name)) {
                $result['errors']['name'] = 'Ya existe un rubro interno con ese nombre';
            }
            
            if (empty($result['errors'])) {
                try {
                    $data = [
                        'name' => $name,
                        'payment_count' => $payment_count
                    ];
                    
                    $created_id = $this->internalItemModel->create($data);
                    
                    if ($created_id) {
                        $result['success'] = true;
                        $result['message'] = 'Rubro interno creado exitosamente';
                        $result['messageType'] = 'success';
                        $result['redirect'] = 'index.php';
                    } else {
                        $result['success'] = false;
                        $result['message'] = 'Error al crear el rubro interno';
                        $result['messageType'] = 'error';
                    }
                } catch (Exception $e) {
                    $result['success'] = false;
                    $result['message'] = 'Error al crear el rubro interno: ' . $e->getMessage();
                    $result['messageType'] = 'error';
                }
            } else {
                $result['success'] = false;
                $result['message'] = 'Por favor corrige los errores indicados';
                $result['messageType'] = 'error';
                $result['name'] = $name;
                $result['payment_count'] = $payment_count;
            }
        }
        
        return $result;
    }

    /**
     * Mostrar detalles de un rubro interno
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
                'message' => 'ID de rubro interno no válido',
                'redirect' => 'index.php'
            ];
        }
        
        try {
            $internal_item = $this->internalItemModel->getById($id);
            
            if (!$internal_item) {
                return [
                    'success' => false,
                    'message' => 'Rubro interno no encontrado',
                    'redirect' => 'index.php'
                ];
            }
            
            return [
                'success' => true,
                'internal_item' => $internal_item
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al cargar el rubro interno: ' . $e->getMessage(),
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
                'message' => 'ID de rubro interno no válido',
                'redirect' => 'index.php'
            ];
        }
        
        try {
            $internal_item = $this->internalItemModel->getById($id);
            
            if (!$internal_item) {
                return [
                    'success' => false,
                    'message' => 'Rubro interno no encontrado',
                    'redirect' => 'index.php'
                ];
            }
            
            $result = [
                'success' => true,
                'message' => '',
                'messageType' => '',
                'errors' => [],
                'internal_item' => $internal_item,
                'name' => $internal_item['name'],
                'payment_count' => $internal_item['payment_count']
            ];
            
            // Si es POST, procesar actualización
            if (isset($params['_method']) && $params['_method'] === 'POST') {
                $name = trim($params['name'] ?? '');
                $payment_count = $params['payment_count'] ?? '';
                
                // Validaciones
                if (empty($name)) {
                    $result['errors']['name'] = 'El nombre es requerido';
                } elseif (strlen($name) > 100) {
                    $result['errors']['name'] = 'El nombre no puede tener más de 100 caracteres';
                }
                
                if (empty($payment_count)) {
                    $result['errors']['payment_count'] = 'El número de cobros es requerido';
                } elseif (!is_numeric($payment_count) || $payment_count < 0) {
                    $result['errors']['payment_count'] = 'El número de cobros debe ser un valor numérico positivo';
                }
                
                // Verificar si el nombre ya existe (excluyendo el actual)
                if (empty($result['errors']) && $this->internalItemModel->existsByNameExcluding($name, $id)) {
                    $result['errors']['name'] = 'Ya existe otro rubro interno con ese nombre';
                }
                
                if (empty($result['errors'])) {
                    try {
                        $data = [
                            'name' => $name,
                            'payment_count' => $payment_count
                        ];
                        
                        $updated = $this->internalItemModel->update($id, $data);
                        
                        if ($updated) {
                            $result['success'] = true;
                            $result['message'] = 'Rubro interno actualizado exitosamente';
                            $result['messageType'] = 'success';
                            $result['redirect'] = 'index.php';
                        } else {
                            $result['success'] = false;
                            $result['message'] = 'Error al actualizar el rubro interno';
                            $result['messageType'] = 'error';
                        }
                    } catch (Exception $e) {
                        $result['success'] = false;
                        $result['message'] = 'Error al actualizar el rubro interno: ' . $e->getMessage();
                        $result['messageType'] = 'error';
                    }
                } else {
                    $result['success'] = false;
                    $result['message'] = 'Por favor corrige los errores indicados';
                    $result['messageType'] = 'error';
                    $result['name'] = $name;
                    $result['payment_count'] = $payment_count;
                }
            }
            
            return $result;
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al cargar el rubro interno: ' . $e->getMessage(),
                'redirect' => 'index.php'
            ];
        }
    }

    /**
     * Eliminar un rubro interno
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
                'message' => 'ID de rubro interno no válido'
            ];
        }
        
        try {
            $internal_item = $this->internalItemModel->getById($id);
            
            if (!$internal_item) {
                return [
                    'success' => false,
                    'message' => 'Rubro interno no encontrado'
                ];
            }
            
            $deleted = $this->internalItemModel->delete($id);
            
            if ($deleted) {
                return [
                    'success' => true,
                    'message' => 'Rubro interno eliminado exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al eliminar el rubro interno'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar el rubro interno: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener todos los rubros internos para API o listados
     * @return array
     */
    public function getAll() {
        try {
            $items = $this->internalItemModel->getAllSimple();
            return [
                'success' => true,
                'data' => $items
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener los rubros internos: ' . $e->getMessage()
            ];
        }
    }
}

?>