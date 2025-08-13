<?php

require_once __DIR__ . '/../config/Database.php';

class AwardeesModel {
    private $conn;
    private $table = 'awardees';
    
    // Propiedades del adjudicatario
    public $id;
    public $first_name;
    public $middle_name;
    public $last_name;
    public $second_last_name;
    public $id_number;
    public $phone;
    public $email;
    public $address;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Obtener todos los adjudicatarios
     * @param int $page
     * @param int $limit
     * @param string $search
     * @return array
     */
    public function getAll($page = 1, $limit = 10, $search = '') {
        try {
            $offset = ($page - 1) * $limit;
            $searchParam = "%$search%";
            
            $query = "SELECT * FROM " . $this->table . " 
                      WHERE (first_name LIKE :search 
                         OR last_name LIKE :search 
                         OR id_number LIKE :search 
                         OR email LIKE :search)
                      ORDER BY first_name ASC, last_name ASC 
                      LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':search', $searchParam);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            error_log("Error al obtener adjudicatarios: " . $exception->getMessage());
            return [];
        }
    }

    /**
     * Contar el total de adjudicatarios
     * @param string $search
     * @return int
     */
    public function countAll($search = '') {
        try {
            $searchParam = "%$search%";
            
            $query = "SELECT COUNT(id) as total FROM " . $this->table . " 
                      WHERE (first_name LIKE :search 
                         OR last_name LIKE :search 
                         OR id_number LIKE :search 
                         OR email LIKE :search)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':search', $searchParam);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['total'];
        } catch(PDOException $exception) {
            error_log("Error al contar adjudicatarios: " . $exception->getMessage());
            return 0;
        }
    }

    /**
     * Obtener un adjudicatario por ID
     * @param int $id
     * @return array|false
     */
    public function getById($id) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            error_log("Error al obtener adjudicatario: " . $exception->getMessage());
            return false;
        }
    }

    /**
     * Obtener un adjudicatario por número de identificación
     * @param string $id_number
     * @return array|false
     */
    public function getByIdNumber($id_number) {
        try {
            $query = "SELECT * FROM " . $this->table . " WHERE id_number = :id_number";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_number', $id_number);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            error_log("Error al obtener adjudicatario por número de identificación: " . $exception->getMessage());
            return false;
        }
    }

    /**
     * Crear un nuevo adjudicatario
     * @param array $data
     * @return array
     */
    public function create($data) {
        try {
            // Verificar si ya existe un adjudicatario con ese número de identificación
            $checkQuery = "SELECT id FROM " . $this->table . " WHERE id_number = :id_number";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':id_number', $data['id_number']);
            $checkStmt->execute();
            
            if ($checkStmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'Ya existe un adjudicatario con ese número de identificación'
                ];
            }
            
            $query = "INSERT INTO " . $this->table . " 
                      (first_name, middle_name, last_name, second_last_name, id_number, phone, email, address) 
                      VALUES (:first_name, :middle_name, :last_name, :second_last_name, :id_number, :phone, :email, :address)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':first_name', $data['first_name']);
            $stmt->bindParam(':middle_name', $data['middle_name']);
            $stmt->bindParam(':last_name', $data['last_name']);
            $stmt->bindParam(':second_last_name', $data['second_last_name']);
            $stmt->bindParam(':id_number', $data['id_number']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':address', $data['address']);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Adjudicatario creado exitosamente',
                    'id' => $this->conn->lastInsertId()
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al crear el adjudicatario'
                ];
            }
        } catch(PDOException $exception) {
            error_log("Error al crear adjudicatario: " . $exception->getMessage());
            return [
                'success' => false,
                'message' => 'Error en la base de datos: ' . $exception->getMessage()
            ];
        }
    }

    /**
     * Actualizar un adjudicatario
     * @param int $id
     * @param array $data
     * @return array
     */
    public function update($id, $data) {
        try {
            // Verificar si ya existe otro adjudicatario con ese número de identificación
            $checkQuery = "SELECT id FROM " . $this->table . " WHERE id_number = :id_number AND id != :id";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':id_number', $data['id_number']);
            $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $checkStmt->execute();
            
            if ($checkStmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'Ya existe otro adjudicatario con ese número de identificación'
                ];
            }
            
            $query = "UPDATE " . $this->table . " 
                      SET first_name = :first_name, 
                          middle_name = :middle_name, 
                          last_name = :last_name, 
                          second_last_name = :second_last_name, 
                          id_number = :id_number, 
                          phone = :phone, 
                          email = :email, 
                          address = :address 
                      WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':first_name', $data['first_name']);
            $stmt->bindParam(':middle_name', $data['middle_name']);
            $stmt->bindParam(':last_name', $data['last_name']);
            $stmt->bindParam(':second_last_name', $data['second_last_name']);
            $stmt->bindParam(':id_number', $data['id_number']);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':address', $data['address']);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                if ($stmt->rowCount() > 0) {
                    return [
                        'success' => true,
                        'message' => 'Adjudicatario actualizado exitosamente'
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'No se encontró el adjudicatario o no hubo cambios'
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al actualizar el adjudicatario'
                ];
            }
        } catch(PDOException $exception) {
            error_log("Error al actualizar adjudicatario: " . $exception->getMessage());
            return [
                'success' => false,
                'message' => 'Error en la base de datos: ' . $exception->getMessage()
            ];
        }
    }

    /**
     * Eliminar un adjudicatario
     * @param int $id
     * @return array
     */
    public function delete($id) {
        try {
            // Primero verificar si el adjudicatario existe
            $checkQuery = "SELECT first_name, last_name FROM " . $this->table . " WHERE id = :id";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $checkStmt->execute();
            
            $awardee = $checkStmt->fetch(PDO::FETCH_ASSOC);
            if (!$awardee) {
                return [
                    'success' => false,
                    'message' => 'El adjudicatario no existe'
                ];
            }
            
            $query = "DELETE FROM " . $this->table . " WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Adjudicatario eliminado exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al eliminar el adjudicatario'
                ];
            }
        } catch(PDOException $exception) {
            error_log("Error al eliminar adjudicatario: " . $exception->getMessage());
            return [
                'success' => false,
                'message' => 'Error en la base de datos: ' . $exception->getMessage()
            ];
        }
    }

    /**
     * Obtener lista simple de adjudicatarios para selects
     * @return array
     */
    public function getForSelect() {
        try {
            $query = "SELECT id, CONCAT(first_name, ' ', last_name) as full_name, id_number 
                      FROM " . $this->table . " 
                      ORDER BY first_name ASC, last_name ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            error_log("Error al obtener adjudicatarios para select: " . $exception->getMessage());
            return [];
        }
    }

    /**
     * Obtener el nombre completo de un adjudicatario
     * @param array $awardee
     * @return string
     */
    public function getFullName($awardee) {
        $name_parts = array_filter([
            $awardee['first_name'],
            $awardee['middle_name'],
            $awardee['last_name'],
            $awardee['second_last_name']
        ]);
        
        return implode(' ', $name_parts);
    }
}

?>