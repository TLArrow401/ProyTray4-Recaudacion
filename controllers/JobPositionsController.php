<?php

require_once __DIR__ . '/../models/JobPositionsModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../config/app.php';

class JobPositionsController {
    private $jobPositionsModel;
    
    public function __construct() {
        $this->jobPositionsModel = new JobPositionsModel();
    }

    /**
     * Mostrar lista de cargos con filtros y paginación
     * @param array $params
     * @return array
     */
    public function index($params = []) {
        // Verificar acceso - solo RRHH puede gestionar cargos
        AuthMiddleware::requireUserManagementAccess();
        
        // Obtener parámetros
        $page = isset($params['page']) ? (int)$params['page'] : 1;
        $limit = 10;
        $search = isset($params['search']) ? trim($params['search']) : '';
        
        // Obtener datos
        $jobPositions = $this->jobPositionsModel->getAll($page, $limit, $search);
        $total = $this->jobPositionsModel->countAll($search);
        $totalPages = ceil($total / $limit);
        
        $result = [
            'job_positions' => $jobPositions,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_records' => $total,
            'search' => $search,
            'page_title' => 'Gestión de Cargos',
            'has_search' => !empty($search)
        ];
        
        return $result;
    }

    /**
     * Mostrar detalles de un cargo específico
     * @param int $id
     * @return array
     */
    public function view($id) {
        // Verificar acceso
        AuthMiddleware::requireUserManagementAccess();
        
        if (!$id || !is_numeric($id)) {
            return [
                'success' => false,
                'message' => 'ID de cargo inválido'
            ];
        }
        
        $jobPosition = $this->jobPositionsModel->getById($id);
        
        if (!$jobPosition) {
            return [
                'success' => false,
                'message' => 'Cargo no encontrado'
            ];
        }
        
        // Obtener el personal asignado a este cargo
        $staff = $this->jobPositionsModel->getStaffByJobPosition($id);
        
        return [
            'success' => true,
            'job_position' => $jobPosition,
            'staff' => $staff,
            'page_title' => 'Detalles del Cargo: ' . $jobPosition['name']
        ];
    }

    /**
     * Mostrar formulario para crear un nuevo cargo
     * @return array
     */
    public function create() {
        // Verificar acceso
        AuthMiddleware::requireUserManagementAccess();
        
        return [
            'page_title' => 'Crear Nuevo Cargo',
            'action' => 'create'
        ];
    }

    /**
     * Procesar la creación de un nuevo cargo
     * @param array $data
     * @return array
     */
    public function store($data) {
        // Verificar acceso
        AuthMiddleware::requireUserManagementAccess();
        
        // Validar datos
        $validation = $this->validateJobPositionData($data);
        if (!$validation['success']) {
            return $validation;
        }
        
        $name = trim($data['name']);
        
        // Crear el cargo
        $result = $this->jobPositionsModel->create($name);
        
        if ($result['success']) {
            // Redirigir al listado con mensaje de éxito
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'message' => $result['message']
            ];
            
            return [
                'success' => true,
                'redirect' => 'index.php'
            ];
        } else {
            return $result;
        }
    }

    /**
     * Mostrar formulario para editar un cargo
     * @param int $id
     * @return array
     */
    public function edit($id) {
        // Verificar acceso
        AuthMiddleware::requireUserManagementAccess();
        
        if (!$id || !is_numeric($id)) {
            return [
                'success' => false,
                'message' => 'ID de cargo inválido'
            ];
        }
        
        $jobPosition = $this->jobPositionsModel->getById($id);
        
        if (!$jobPosition) {
            return [
                'success' => false,
                'message' => 'Cargo no encontrado'
            ];
        }
        
        return [
            'success' => true,
            'job_position' => $jobPosition,
            'page_title' => 'Editar Cargo: ' . $jobPosition['name'],
            'action' => 'edit'
        ];
    }

    /**
     * Procesar la actualización de un cargo
     * @param int $id
     * @param array $data
     * @return array
     */
    public function update($id, $data) {
        // Verificar acceso
        AuthMiddleware::requireUserManagementAccess();
        
        if (!$id || !is_numeric($id)) {
            return [
                'success' => false,
                'message' => 'ID de cargo inválido'
            ];
        }
        
        // Validar datos
        $validation = $this->validateJobPositionData($data);
        if (!$validation['success']) {
            return $validation;
        }
        
        $name = trim($data['name']);
        
        // Actualizar el cargo
        $result = $this->jobPositionsModel->update($id, $name);
        
        if ($result['success']) {
            // Redirigir al listado con mensaje de éxito
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'message' => $result['message']
            ];
            
            return [
                'success' => true,
                'redirect' => 'index.php'
            ];
        } else {
            return $result;
        }
    }

    /**
     * Eliminar un cargo
     * @param int $id
     * @return array
     */
    public function delete($id) {
        // Verificar acceso
        AuthMiddleware::requireUserManagementAccess();
        
        if (!$id || !is_numeric($id)) {
            return [
                'success' => false,
                'message' => 'ID de cargo inválido'
            ];
        }
        
        // Intentar eliminar
        $result = $this->jobPositionsModel->delete($id);
        
        // Configurar mensaje flash
        $_SESSION['flash_message'] = [
            'type' => $result['success'] ? 'success' : 'error',
            'message' => $result['message']
        ];
        
        return $result;
    }

    /**
     * Validar datos del cargo
     * @param array $data
     * @return array
     */
    private function validateJobPositionData($data) {
        $errors = [];
        
        // Validar nombre
        if (empty($data['name'])) {
            $errors[] = 'El nombre del cargo es obligatorio';
        } else {
            $name = trim($data['name']);
            if (strlen($name) < 2) {
                $errors[] = 'El nombre del cargo debe tener al menos 2 caracteres';
            }
            if (strlen($name) > 255) {
                $errors[] = 'El nombre del cargo no puede exceder 255 caracteres';
            }
            // Validar caracteres permitidos
            if (!preg_match('/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s\-\.]+$/', $name)) {
                $errors[] = 'El nombre del cargo solo puede contener letras, espacios, guiones y puntos';
            }
        }
        
        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $errors
            ];
        }
        
        return ['success' => true];
    }

    /**
     * Obtener cargos para uso en selects
     * @return array
     */
    public function getForSelect() {
        return $this->jobPositionsModel->getForSelect();
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
                if (isset($params['id'])) {
                    return $this->delete($params['id']);
                }
                return ['success' => false, 'message' => 'ID no proporcionado'];
                
            case 'get_staff':
                if (isset($params['id'])) {
                    $staff = $this->jobPositionsModel->getStaffByJobPosition($params['id']);
                    return ['success' => true, 'staff' => $staff];
                }
                return ['success' => false, 'message' => 'ID no proporcionado'];
                
            default:
                return ['success' => false, 'message' => 'Acción no válida'];
        }
    }
}

?>