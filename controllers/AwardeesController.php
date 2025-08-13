<?php

require_once __DIR__ . '/../models/AwardeesModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../config/app.php';

class AwardeesController {
    private $awardeesModel;
    
    public function __construct() {
        $this->awardeesModel = new AwardeesModel();
    }

    /**
     * Mostrar lista de adjudicatarios con filtros y paginación
     * @param array $params
     * @return array
     */
    public function index($params = []) {
        // Verificar acceso - solo RRHH puede gestionar adjudicatarios
        AuthMiddleware::requireUserManagementAccess();
        
        // Obtener parámetros
        $page = isset($params['page']) ? (int)$params['page'] : 1;
        $limit = 10;
        $search = isset($params['search']) ? trim($params['search']) : '';
        
        // Obtener datos
        $awardees = $this->awardeesModel->getAll($page, $limit, $search);
        $total = $this->awardeesModel->countAll($search);
        $totalPages = ceil($total / $limit);
        
        $result = [
            'awardees' => $awardees,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_records' => $total,
            'search' => $search,
            'page_title' => 'Gestión de Adjudicatarios',
            'has_search' => !empty($search)
        ];
        
        return $result;
    }

    /**
     * Mostrar detalles de un adjudicatario específico
     * @param int $id
     * @return array
     */
    public function view($id) {
        // Verificar acceso
        AuthMiddleware::requireUserManagementAccess();
        
        if (!$id || !is_numeric($id)) {
            return [
                'success' => false,
                'message' => 'ID de adjudicatario inválido'
            ];
        }
        
        $awardee = $this->awardeesModel->getById($id);
        
        if (!$awardee) {
            return [
                'success' => false,
                'message' => 'Adjudicatario no encontrado'
            ];
        }
        
        return [
            'success' => true,
            'awardee' => $awardee,
            'full_name' => $this->awardeesModel->getFullName($awardee),
            'page_title' => 'Detalles del Adjudicatario: ' . $this->awardeesModel->getFullName($awardee)
        ];
    }

    /**
     * Mostrar formulario para crear un nuevo adjudicatario
     * @return array
     */
    public function create() {
        // Verificar acceso
        AuthMiddleware::requireUserManagementAccess();
        
        return [
            'page_title' => 'Crear Nuevo Adjudicatario',
            'action' => 'create'
        ];
    }

    /**
     * Procesar la creación de un nuevo adjudicatario
     * @param array $data
     * @return array
     */
    public function store($data) {
        // Verificar acceso
        AuthMiddleware::requireUserManagementAccess();
        
        // Validar datos
        $validation = $this->validateAwardeeData($data);
        if (!$validation['success']) {
            return $validation;
        }
        
        // Limpiar y preparar datos
        $cleanData = $this->cleanAwardeeData($data);
        
        // Crear el adjudicatario
        $result = $this->awardeesModel->create($cleanData);
        
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
     * Mostrar formulario para editar un adjudicatario
     * @param int $id
     * @return array
     */
    public function edit($id) {
        // Verificar acceso
        AuthMiddleware::requireUserManagementAccess();
        
        if (!$id || !is_numeric($id)) {
            return [
                'success' => false,
                'message' => 'ID de adjudicatario inválido'
            ];
        }
        
        $awardee = $this->awardeesModel->getById($id);
        
        if (!$awardee) {
            return [
                'success' => false,
                'message' => 'Adjudicatario no encontrado'
            ];
        }
        
        return [
            'success' => true,
            'awardee' => $awardee,
            'full_name' => $this->awardeesModel->getFullName($awardee),
            'page_title' => 'Editar Adjudicatario: ' . $this->awardeesModel->getFullName($awardee),
            'action' => 'edit'
        ];
    }

    /**
     * Procesar la actualización de un adjudicatario
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
                'message' => 'ID de adjudicatario inválido'
            ];
        }
        
        // Validar datos
        $validation = $this->validateAwardeeData($data);
        if (!$validation['success']) {
            return $validation;
        }
        
        // Limpiar y preparar datos
        $cleanData = $this->cleanAwardeeData($data);
        
        // Actualizar el adjudicatario
        $result = $this->awardeesModel->update($id, $cleanData);
        
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
     * Eliminar un adjudicatario
     * @param int $id
     * @return array
     */
    public function delete($id) {
        // Verificar acceso
        AuthMiddleware::requireUserManagementAccess();
        
        if (!$id || !is_numeric($id)) {
            return [
                'success' => false,
                'message' => 'ID de adjudicatario inválido'
            ];
        }
        
        // Intentar eliminar
        $result = $this->awardeesModel->delete($id);
        
        // Configurar mensaje flash
        $_SESSION['flash_message'] = [
            'type' => $result['success'] ? 'success' : 'error',
            'message' => $result['message']
        ];
        
        return $result;
    }

    /**
     * Validar datos del adjudicatario
     * @param array $data
     * @return array
     */
    private function validateAwardeeData($data) {
        $errors = [];
        
        // Validar primer nombre (obligatorio)
        if (empty($data['first_name'])) {
            $errors[] = 'El primer nombre es obligatorio';
        } else {
            $firstName = trim($data['first_name']);
            if (strlen($firstName) < 2) {
                $errors[] = 'El primer nombre debe tener al menos 2 caracteres';
            }
            if (strlen($firstName) > 50) {
                $errors[] = 'El primer nombre no puede exceder 50 caracteres';
            }
            if (!preg_match('/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]+$/', $firstName)) {
                $errors[] = 'El primer nombre solo puede contener letras y espacios';
            }
        }
        
        // Validar segundo nombre (opcional)
        if (!empty($data['middle_name'])) {
            $middleName = trim($data['middle_name']);
            if (strlen($middleName) > 50) {
                $errors[] = 'El segundo nombre no puede exceder 50 caracteres';
            }
            if (!preg_match('/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]+$/', $middleName)) {
                $errors[] = 'El segundo nombre solo puede contener letras y espacios';
            }
        }
        
        // Validar primer apellido (obligatorio)
        if (empty($data['last_name'])) {
            $errors[] = 'El primer apellido es obligatorio';
        } else {
            $lastName = trim($data['last_name']);
            if (strlen($lastName) < 2) {
                $errors[] = 'El primer apellido debe tener al menos 2 caracteres';
            }
            if (strlen($lastName) > 50) {
                $errors[] = 'El primer apellido no puede exceder 50 caracteres';
            }
            if (!preg_match('/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]+$/', $lastName)) {
                $errors[] = 'El primer apellido solo puede contener letras y espacios';
            }
        }
        
        // Validar segundo apellido (opcional)
        if (!empty($data['second_last_name'])) {
            $secondLastName = trim($data['second_last_name']);
            if (strlen($secondLastName) > 50) {
                $errors[] = 'El segundo apellido no puede exceder 50 caracteres';
            }
            if (!preg_match('/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]+$/', $secondLastName)) {
                $errors[] = 'El segundo apellido solo puede contener letras y espacios';
            }
        }
        
        // Validar número de identificación (obligatorio)
        if (empty($data['id_number'])) {
            $errors[] = 'El número de identificación es obligatorio';
        } else {
            $idNumber = trim($data['id_number']);
            if (strlen($idNumber) < 7) {
                $errors[] = 'El número de identificación debe tener al menos 7 caracteres';
            }
            if (strlen($idNumber) > 20) {
                $errors[] = 'El número de identificación no puede exceder 20 caracteres';
            }
            if (!preg_match('/^[V]?[0-9\-]+$/', $idNumber)) {
                $errors[] = 'El número de identificación solo puede contener V (opcional), números y guiones';
            }
        }
        
        // Validar teléfono (opcional)
        if (!empty($data['phone'])) {
            $phone = trim($data['phone']);
            if (strlen($phone) > 20) {
                $errors[] = 'El número de teléfono no puede exceder 20 caracteres';
            }
            if (!preg_match('/^[0-9\+\-\s\(\)]+$/', $phone)) {
                $errors[] = 'El número de teléfono contiene caracteres no válidos';
            }
        }
        
        // Validar email (opcional)
        if (!empty($data['email'])) {
            $email = trim($data['email']);
            if (strlen($email) > 100) {
                $errors[] = 'El correo electrónico no puede exceder 100 caracteres';
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'El formato del correo electrónico no es válido';
            }
        }
        
        // Validar dirección (opcional)
        if (!empty($data['address'])) {
            $address = trim($data['address']);
            if (strlen($address) > 500) {
                $errors[] = 'La dirección no puede exceder 500 caracteres';
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
     * Limpiar y preparar datos del adjudicatario
     * @param array $data
     * @return array
     */
    private function cleanAwardeeData($data) {
        return [
            'first_name' => trim($data['first_name']),
            'middle_name' => !empty($data['middle_name']) ? trim($data['middle_name']) : null,
            'last_name' => trim($data['last_name']),
            'second_last_name' => !empty($data['second_last_name']) ? trim($data['second_last_name']) : null,
            'id_number' => trim($data['id_number']),
            'phone' => !empty($data['phone']) ? trim($data['phone']) : null,
            'email' => !empty($data['email']) ? trim($data['email']) : null,
            'address' => !empty($data['address']) ? trim($data['address']) : null
        ];
    }

    /**
     * Obtener adjudicatarios para uso en selects
     * @return array
     */
    public function getForSelect() {
        return $this->awardeesModel->getForSelect();
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
                
            case 'get_by_id_number':
                if (isset($params['id_number'])) {
                    $awardee = $this->awardeesModel->getByIdNumber($params['id_number']);
                    if ($awardee) {
                        return [
                            'success' => true, 
                            'awardee' => $awardee,
                            'full_name' => $this->awardeesModel->getFullName($awardee)
                        ];
                    }
                    return ['success' => false, 'message' => 'Adjudicatario no encontrado'];
                }
                return ['success' => false, 'message' => 'Número de identificación no proporcionado'];
                
            default:
                return ['success' => false, 'message' => 'Acción no válida'];
        }
    }
}

?>