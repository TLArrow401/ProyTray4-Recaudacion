<?php

require_once __DIR__ . '/../models/FiscalYearModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class FiscalYearController {
    private $fiscalYearModel;
    
    public function __construct() {
        $this->fiscalYearModel = new FiscalYearModel();
    }

    /**
     * Mostrar lista de años fiscales con paginación
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
            // Obtener años fiscales
            $result['fiscal_years'] = $this->fiscalYearModel->getAll($page, $limit, $search);
            $result['total_items'] = $this->fiscalYearModel->countItems($search);
            $result['total_pages'] = ceil($result['total_items'] / $limit);
            $result['current_page'] = $page;
            $result['search'] = $search;
            $result['page_title'] = 'Gestión de Años Fiscales';
            $result['success'] = true;
            
        } catch (Exception $e) {
            $result = [
                'success' => false,
                'message' => 'Error al cargar los años fiscales: ' . $e->getMessage(),
                'messageType' => 'error'
            ];
        }
        
        return $result;
    }

    /**
     * Mostrar formulario de creación / procesar creación
     * @param array $params
     * @return array
     */
    public function create($params = []) {
        // Verificar acceso
        AuthMiddleware::requireUserManagementAccess();
        
        // Si es POST, procesar la creación
        if (isset($params['_method']) && $params['_method'] === 'POST') {
            return $this->store($params);
        }
        
        return [
            'page_title' => 'Crear Nuevo Año Fiscal',
            'success' => true
        ];
    }

    /**
     * Guardar nuevo año fiscal
     * @param array $data
     * @return array
     */
    public function store($data) {
        // Verificar acceso
        AuthMiddleware::requireUserManagementAccess();
        
        // Validar datos
        $validation = $this->validateFiscalYear($data);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'errors' => $validation['errors']
            ];
        }

        try {
            $result = $this->fiscalYearModel->create($data);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Año fiscal creado exitosamente',
                    'messageType' => 'success',
                    'redirect' => 'index.php'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al crear el año fiscal',
                    'messageType' => 'error'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al guardar el año fiscal: ' . $e->getMessage(),
                'messageType' => 'error'
            ];
        }
    }

    /**
     * Mostrar año fiscal específico
     * @param int $id
     * @return array
     */
    public function show($id) {
        // Verificar acceso
        AuthMiddleware::requireUserManagementAccess();
        
        try {
            $fiscal_year = $this->fiscalYearModel->getById($id);
            
            if (!$fiscal_year) {
                return [
                    'success' => false,
                    'message' => 'Año fiscal no encontrado',
                    'messageType' => 'error'
                ];
            }
            
            return [
                'success' => true,
                'fiscal_year' => $fiscal_year,
                'page_title' => 'Detalles del Año Fiscal: ' . $fiscal_year['year']
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al cargar el año fiscal: ' . $e->getMessage(),
                'messageType' => 'error'
            ];
        }
    }

    /**
     * Mostrar formulario de edición / procesar actualización
     * @param array $params
     * @return array
     */
    public function edit($params) {
        // Verificar acceso
        AuthMiddleware::requireUserManagementAccess();
        
        $id = $params['id'] ?? 0;
        
        if (!$id) {
            return [
                'success' => false,
                'message' => 'ID de año fiscal no proporcionado',
                'messageType' => 'error',
                'redirect' => 'index.php'
            ];
        }
        
        // Si es POST, procesar la actualización
        if (isset($params['_method']) && $params['_method'] === 'POST') {
            return $this->update($id, $params);
        }
        
        try {
            $fiscal_year = $this->fiscalYearModel->getById($id);
            
            if (!$fiscal_year) {
                return [
                    'success' => false,
                    'message' => 'Año fiscal no encontrado',
                    'messageType' => 'error'
                ];
            }
            
            return [
                'success' => true,
                'fiscal_year' => $fiscal_year,
                'page_title' => 'Editar Año Fiscal: ' . $fiscal_year['year']
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al cargar el año fiscal: ' . $e->getMessage(),
                'messageType' => 'error'
            ];
        }
    }

    /**
     * Actualizar año fiscal
     * @param int $id
     * @param array $data
     * @return array
     */
    public function update($id, $data) {
        // Verificar acceso
        AuthMiddleware::requireUserManagementAccess();
        
        // Verificar que el año fiscal existe
        $fiscal_year = $this->fiscalYearModel->getById($id);
        if (!$fiscal_year) {
            return [
                'success' => false,
                'message' => 'Año fiscal no encontrado',
                'messageType' => 'error'
            ];
        }
        
        // Validar datos
        $validation = $this->validateFiscalYear($data, $id);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'errors' => $validation['errors']
            ];
        }

        try {
            $result = $this->fiscalYearModel->update($id, $data);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Año fiscal actualizado exitosamente',
                    'messageType' => 'success',
                    'redirect' => 'index.php'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al actualizar el año fiscal',
                    'messageType' => 'error'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar el año fiscal: ' . $e->getMessage(),
                'messageType' => 'error'
            ];
        }
    }

    /**
     * Eliminar año fiscal
     * @param int|array $params
     * @return array
     */
    public function destroy($params) {
        $id = is_array($params) ? ($params['id'] ?? 0) : $params;
        
        // Verificar acceso
        AuthMiddleware::requireUserManagementAccess();
        
        try {
            $fiscal_year = $this->fiscalYearModel->getById($id);
            
            if (!$fiscal_year) {
                return [
                    'success' => false,
                    'message' => 'Año fiscal no encontrado',
                    'messageType' => 'error'
                ];
            }
            
            // Verificar si tiene contratos asociados
            if ($this->fiscalYearModel->hasAssociatedContracts($id)) {
                return [
                    'success' => false,
                    'message' => 'No se puede eliminar el año fiscal porque tiene contratos asociados',
                    'messageType' => 'error'
                ];
            }
            
            $result = $this->fiscalYearModel->delete($id);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Año fiscal eliminado exitosamente',
                    'messageType' => 'success'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al eliminar el año fiscal',
                    'messageType' => 'error'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar el año fiscal: ' . $e->getMessage(),
                'messageType' => 'error'
            ];
        }
    }

    /**
     * Ver detalles del año fiscal (para compatibilidad con external-items)
     * @param array $params
     * @return array
     */
    public function view($params) {
        $id = $params['id'] ?? 0;
        return $this->show($id);
    }

    /**
     * Eliminar año fiscal (para compatibilidad con external-items)
     * @param array $params
     * @return array
     */
    public function delete($params) {
        return $this->destroy($params);
    }

    /**
     * Validar datos del año fiscal
     * @param array $data
     * @param int $excludeId
     * @return array
     */
    private function validateFiscalYear($data, $excludeId = null) {
        $errors = [];
        
        // Validar año
        if (empty($data['year'])) {
            $errors[] = 'El año es obligatorio';
        } elseif (!is_numeric($data['year']) || strlen($data['year']) !== 4) {
            $errors[] = 'El año debe ser un número de 4 dígitos';
        } elseif ($data['year'] < 2020 || $data['year'] > 2050) {
            $errors[] = 'El año debe estar entre 2020 y 2050';
        } else {
            // Verificar duplicados
            if ($this->fiscalYearModel->existsByYear($data['year'], $excludeId)) {
                $errors[] = 'Ya existe un año fiscal con este año';
            }
        }
        
        // Validar fecha de inicio
        if (empty($data['start_date'])) {
            $errors[] = 'La fecha de inicio es obligatoria';
        } elseif (!$this->isValidDate($data['start_date'])) {
            $errors[] = 'La fecha de inicio no es válida';
        }
        
        // Validar fecha de fin
        if (empty($data['end_date'])) {
            $errors[] = 'La fecha de fin es obligatoria';
        } elseif (!$this->isValidDate($data['end_date'])) {
            $errors[] = 'La fecha de fin no es válida';
        }
        
        // Validar que la fecha de inicio sea anterior a la de fin
        if (!empty($data['start_date']) && !empty($data['end_date'])) {
            $start_date = new DateTime($data['start_date']);
            $end_date = new DateTime($data['end_date']);
            
            if ($start_date >= $end_date) {
                $errors[] = 'La fecha de inicio debe ser anterior a la fecha de fin';
            }
            
            // Validar que el período sea de aproximadamente un año
            $diff = $start_date->diff($end_date);
            if ($diff->days < 360 || $diff->days > 370) {
                $errors[] = 'El período debe ser de aproximadamente un año (360-370 días)';
            }
        }
        
        // Validar estado
        if (isset($data['status']) && !in_array($data['status'], ['active', 'inactive'])) {
            $errors[] = 'El estado debe ser activo o inactivo';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Validar formato de fecha
     * @param string $date
     * @return bool
     */
    private function isValidDate($date) {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}

?>