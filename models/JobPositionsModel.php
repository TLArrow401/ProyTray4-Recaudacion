<?php

require_once __DIR__ . '/../config/Database.php';

class JobPositionsModel {
    private $conn;
    private $table = 'job_positions';
    
    // Propiedades del cargo
    public $id;
    public $name;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Obtener todos los cargos
     * @param int $page
     * @param int $limit
     * @param string $search
     * @return array
     */
    public function getAll($page = 1, $limit = 10, $search = '') {
        try {
            $offset = ($page - 1) * $limit;
            $searchParam = "%$search%";
            
            $query = "SELECT jp.*, 
                             COUNT(s.id) as staff_count 
                      FROM " . $this->table . " jp 
                      LEFT JOIN staff s ON jp.id = s.job_position_id AND s.status = 'active'
                      WHERE jp.name LIKE :search
                      GROUP BY jp.id, jp.name
                      ORDER BY jp.name ASC 
                      LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':search', $searchParam);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            error_log("Error al obtener cargos: " . $exception->getMessage());
            return [];
        }
    }

    /**
     * Contar el total de cargos
     * @param string $search
     * @return int
     */
    public function countAll($search = '') {
        try {
            $searchParam = "%$search%";
            
            $query = "SELECT COUNT(id) as total FROM " . $this->table . " WHERE name LIKE :search";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':search', $searchParam);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['total'];
        } catch(PDOException $exception) {
            error_log("Error al contar cargos: " . $exception->getMessage());
            return 0;
        }
    }

    /**
     * Obtener un cargo por ID
     * @param int $id
     * @return array|false
     */
    public function getById($id) {
        try {
            $query = "SELECT jp.*, 
                             COUNT(s.id) as staff_count 
                      FROM " . $this->table . " jp 
                      LEFT JOIN staff s ON jp.id = s.job_position_id AND s.status = 'active'
                      WHERE jp.id = :id
                      GROUP BY jp.id, jp.name";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            error_log("Error al obtener cargo: " . $exception->getMessage());
            return false;
        }
    }

    /**
     * Obtener el personal asignado a un cargo
     * @param int $job_position_id
     * @return array
     */
    public function getStaffByJobPosition($job_position_id) {
        try {
            $query = "SELECT s.id, s.first_name, s.last_name, s.id_number, 
                             d.name as department_name, s.hire_date, s.status
                      FROM staff s
                      INNER JOIN departments d ON s.department_id = d.id
                      WHERE s.job_position_id = :job_position_id
                      ORDER BY s.first_name, s.last_name";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':job_position_id', $job_position_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            error_log("Error al obtener personal del cargo: " . $exception->getMessage());
            return [];
        }
    }

    /**
     * Crear un nuevo cargo
     * @param string $name
     * @return array
     */
    public function create($name) {
        try {
            // Verificar si ya existe un cargo con ese nombre
            $checkQuery = "SELECT id FROM " . $this->table . " WHERE name = :name";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':name', $name);
            $checkStmt->execute();
            
            if ($checkStmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'Ya existe un cargo con ese nombre'
                ];
            }
            
            $query = "INSERT INTO " . $this->table . " (name) VALUES (:name)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $name);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Cargo creado exitosamente',
                    'id' => $this->conn->lastInsertId()
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al crear el cargo'
                ];
            }
        } catch(PDOException $exception) {
            error_log("Error al crear cargo: " . $exception->getMessage());
            return [
                'success' => false,
                'message' => 'Error en la base de datos: ' . $exception->getMessage()
            ];
        }
    }

    /**
     * Actualizar un cargo
     * @param int $id
     * @param string $name
     * @return array
     */
    public function update($id, $name) {
        try {
            // Verificar si ya existe otro cargo con ese nombre
            $checkQuery = "SELECT id FROM " . $this->table . " WHERE name = :name AND id != :id";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':name', $name);
            $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $checkStmt->execute();
            
            if ($checkStmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'Ya existe otro cargo con ese nombre'
                ];
            }
            
            $query = "UPDATE " . $this->table . " SET name = :name WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                if ($stmt->rowCount() > 0) {
                    return [
                        'success' => true,
                        'message' => 'Cargo actualizado exitosamente'
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'No se encontró el cargo o no hubo cambios'
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al actualizar el cargo'
                ];
            }
        } catch(PDOException $exception) {
            error_log("Error al actualizar cargo: " . $exception->getMessage());
            return [
                'success' => false,
                'message' => 'Error en la base de datos: ' . $exception->getMessage()
            ];
        }
    }

    /**
     * Verificar si un cargo tiene personal asignado
     * @param int $id
     * @return bool
     */
    public function hasStaffAssigned($id) {
        try {
            $query = "SELECT COUNT(id) as count FROM staff WHERE job_position_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['count'] > 0;
        } catch(PDOException $exception) {
            error_log("Error al verificar personal asignado: " . $exception->getMessage());
            return true; // Por seguridad, asumimos que sí tiene personal
        }
    }

    /**
     * Eliminar un cargo
     * @param int $id
     * @return array
     */
    public function delete($id) {
        try {
            // Primero verificar si el cargo existe
            $checkQuery = "SELECT name FROM " . $this->table . " WHERE id = :id";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $checkStmt->execute();
            
            $cargo = $checkStmt->fetch(PDO::FETCH_ASSOC);
            if (!$cargo) {
                return [
                    'success' => false,
                    'message' => 'El cargo no existe'
                ];
            }
            
            // Verificar si tiene personal asignado
            if ($this->hasStaffAssigned($id)) {
                return [
                    'success' => false,
                    'message' => 'No se puede eliminar el cargo "' . $cargo['name'] . '" porque tiene personal asignado'
                ];
            }
            
            $query = "DELETE FROM " . $this->table . " WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Cargo eliminado exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al eliminar el cargo'
                ];
            }
        } catch(PDOException $exception) {
            error_log("Error al eliminar cargo: " . $exception->getMessage());
            return [
                'success' => false,
                'message' => 'Error en la base de datos: ' . $exception->getMessage()
            ];
        }
    }

    /**
     * Obtener lista simple de cargos para selects
     * @return array
     */
    public function getForSelect() {
        try {
            $query = "SELECT id, name FROM " . $this->table . " ORDER BY name ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            error_log("Error al obtener cargos para select: " . $exception->getMessage());
            return [];
        }
    }
}

?>