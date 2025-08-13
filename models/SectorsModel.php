<?php

require_once __DIR__ . '/../config/Database.php';

class SectorsModel {
    private $conn;
    private $table = 'sectors';
    
    // Propiedades del sector
    public $id;
    public $zone_id;
    public $name;
    public $description;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Obtener todos los sectores con paginación y búsqueda
     * @param int $page
     * @param int $limit
     * @param string $search
     * @return array
     */
    public function getAll($page = 1, $limit = 10, $search = '') {
        try {
            $offset = ($page - 1) * $limit;
            
            $query = "SELECT s.*, z.name as zone_name FROM " . $this->table . " s 
                      LEFT JOIN zones z ON s.zone_id = z.id";
            $params = [];
            
            if (!empty($search)) {
                $query .= " WHERE s.name LIKE :search OR s.description LIKE :search OR z.name LIKE :search";
                $params['search'] = '%' . $search . '%';
            }
            
            $query .= " ORDER BY z.name ASC, s.name ASC LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            
            // Bind parámetros de búsqueda si existen
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            
            // Bind parámetros de paginación
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en getAll: " . $e->getMessage());
            throw new Exception("Error al obtener los sectores");
        }
    }

    /**
     * Contar total de sectores
     * @param string $search
     * @return int
     */
    public function countItems($search = '') {
        try {
            $query = "SELECT COUNT(*) as total FROM " . $this->table . " s 
                      LEFT JOIN zones z ON s.zone_id = z.id";
            $params = [];
            
            if (!empty($search)) {
                $query .= " WHERE s.name LIKE :search OR s.description LIKE :search OR z.name LIKE :search";
                $params['search'] = '%' . $search . '%';
            }
            
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return (int)$result['total'];
            
        } catch (PDOException $e) {
            error_log("Error en countItems: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtener sector por ID
     * @param int $id
     * @return array|false
     */
    public function getById($id) {
        try {
            $query = "SELECT s.*, z.name as zone_name FROM " . $this->table . " s 
                      LEFT JOIN zones z ON s.zone_id = z.id 
                      WHERE s.id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en getById: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crear nuevo sector
     * @param array $data
     * @return array
     */
    public function create($data) {
        try {
            // Validar datos requeridos
            if (empty($data['name'])) {
                return [
                    'success' => false,
                    'message' => 'El nombre es requerido',
                    'errors' => ['name' => 'El nombre es requerido']
                ];
            }

            if (empty($data['zone_id'])) {
                return [
                    'success' => false,
                    'message' => 'La zona es requerida',
                    'errors' => ['zone_id' => 'La zona es requerida']
                ];
            }

            // Verificar que la zona existe
            if (!$this->zoneExists($data['zone_id'])) {
                return [
                    'success' => false,
                    'message' => 'La zona seleccionada no existe',
                    'errors' => ['zone_id' => 'La zona seleccionada no existe']
                ];
            }

            // Verificar si el nombre ya existe en la misma zona
            if ($this->nameExistsInZone($data['name'], $data['zone_id'])) {
                return [
                    'success' => false,
                    'message' => 'Ya existe un sector con este nombre en la zona seleccionada',
                    'errors' => ['name' => 'Ya existe un sector con este nombre en la zona seleccionada']
                ];
            }

            $query = "INSERT INTO " . $this->table . " 
                     (zone_id, name, description) 
                     VALUES (:zone_id, :name, :description)";
            
            $stmt = $this->conn->prepare($query);
            
            // Limpiar y preparar datos
            $zone_id = (int)$data['zone_id'];
            $name = trim($data['name']);
            $description = !empty($data['description']) ? trim($data['description']) : null;
            
            $stmt->bindParam(':zone_id', $zone_id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Sector creado exitosamente',
                    'id' => $this->conn->lastInsertId()
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al crear el sector'
                ];
            }
            
        } catch (PDOException $e) {
            error_log("Error en create: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error de base de datos al crear el sector'
            ];
        }
    }

    /**
     * Actualizar sector
     * @param int $id
     * @param array $data
     * @return array
     */
    public function update($id, $data) {
        try {
            // Verificar si el sector existe
            if (!$this->getById($id)) {
                return [
                    'success' => false,
                    'message' => 'Sector no encontrado'
                ];
            }

            // Validar datos requeridos
            if (empty($data['name'])) {
                return [
                    'success' => false,
                    'message' => 'El nombre es requerido',
                    'errors' => ['name' => 'El nombre es requerido']
                ];
            }

            if (empty($data['zone_id'])) {
                return [
                    'success' => false,
                    'message' => 'La zona es requerida',
                    'errors' => ['zone_id' => 'La zona es requerida']
                ];
            }

            // Verificar que la zona existe
            if (!$this->zoneExists($data['zone_id'])) {
                return [
                    'success' => false,
                    'message' => 'La zona seleccionada no existe',
                    'errors' => ['zone_id' => 'La zona seleccionada no existe']
                ];
            }

            // Verificar si el nombre ya existe en la misma zona (excluyendo el actual)
            if ($this->nameExistsInZone($data['name'], $data['zone_id'], $id)) {
                return [
                    'success' => false,
                    'message' => 'Ya existe un sector con este nombre en la zona seleccionada',
                    'errors' => ['name' => 'Ya existe un sector con este nombre en la zona seleccionada']
                ];
            }

            $query = "UPDATE " . $this->table . " 
                     SET zone_id = :zone_id, name = :name, description = :description 
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            
            // Limpiar y preparar datos
            $zone_id = (int)$data['zone_id'];
            $name = trim($data['name']);
            $description = !empty($data['description']) ? trim($data['description']) : null;
            
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':zone_id', $zone_id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Sector actualizado exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al actualizar el sector'
                ];
            }
            
        } catch (PDOException $e) {
            error_log("Error en update: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error de base de datos al actualizar el sector'
            ];
        }
    }

    /**
     * Eliminar sector
     * @param int $id
     * @return array
     */
    public function delete($id) {
        try {
            // Verificar si el sector existe
            $item = $this->getById($id);
            if (!$item) {
                return [
                    'success' => false,
                    'message' => 'Sector no encontrado'
                ];
            }

            // Verificar si hay locales asociados
            if ($this->hasAssociatedStalls($id)) {
                return [
                    'success' => false,
                    'message' => 'No se puede eliminar el sector porque tiene locales asociados'
                ];
            }

            $query = "DELETE FROM " . $this->table . " WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Sector "' . $item['name'] . '" eliminado exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al eliminar el sector'
                ];
            }
            
        } catch (PDOException $e) {
            error_log("Error en delete: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error de base de datos al eliminar el sector'
            ];
        }
    }

    /**
     * Verificar si un nombre ya existe en una zona
     * @param string $name
     * @param int $zone_id
     * @param int $excludeId
     * @return bool
     */
    private function nameExistsInZone($name, $zone_id, $excludeId = null) {
        try {
            $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE name = :name AND zone_id = :zone_id";
            
            if ($excludeId) {
                $query .= " AND id != :excludeId";
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':zone_id', $zone_id, PDO::PARAM_INT);
            
            if ($excludeId) {
                $stmt->bindParam(':excludeId', $excludeId, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return (int)$result['count'] > 0;
            
        } catch (PDOException $e) {
            error_log("Error en nameExistsInZone: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar si una zona existe
     * @param int $zone_id
     * @return bool
     */
    private function zoneExists($zone_id) {
        try {
            $query = "SELECT COUNT(*) as count FROM zones WHERE id = :zone_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':zone_id', $zone_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return (int)$result['count'] > 0;
            
        } catch (PDOException $e) {
            error_log("Error en zoneExists: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar si el sector tiene locales asociados
     * @param int $id
     * @return bool
     */
    private function hasAssociatedStalls($id) {
        try {
            $query = "SELECT COUNT(*) as count FROM market_stalls WHERE sector_id = :sector_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':sector_id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return (int)$result['count'] > 0;
            
        } catch (PDOException $e) {
            error_log("Error en hasAssociatedStalls: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener todos los sectores sin paginación (para listas desplegables)
     * @return array
     */
    public function getAllSimple() {
        try {
            $query = "SELECT s.id, s.name, s.description, s.zone_id, z.name as zone_name 
                      FROM " . $this->table . " s 
                      LEFT JOIN zones z ON s.zone_id = z.id 
                      ORDER BY z.name ASC, s.name ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en getAllSimple: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener sectores por zona
     * @param int $zone_id
     * @return array
     */
    public function getByZone($zone_id) {
        try {
            $query = "SELECT * FROM " . $this->table . " 
                      WHERE zone_id = :zone_id 
                      ORDER BY name ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':zone_id', $zone_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en getByZone: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Buscar sectores por término de búsqueda
     * @param string $searchTerm
     * @param int $limit
     * @return array
     */
    public function search($searchTerm, $limit = 20) {
        try {
            $query = "SELECT s.*, z.name as zone_name FROM " . $this->table . " s 
                      LEFT JOIN zones z ON s.zone_id = z.id 
                      WHERE s.name LIKE :searchTerm OR s.description LIKE :searchTerm OR z.name LIKE :searchTerm
                      ORDER BY z.name ASC, s.name ASC 
                      LIMIT :limit";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%');
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en search: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener estadísticas de sectores
     * @return array
     */
    public function getStats() {
        try {
            $query = "SELECT 
                        COUNT(*) as total_sectors,
                        COUNT(DISTINCT zone_id) as total_zones_with_sectors,
                        (SELECT COUNT(*) FROM market_stalls WHERE sector_id IN (SELECT id FROM " . $this->table . ")) as total_stalls
                      FROM " . $this->table;
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en getStats: " . $e->getMessage());
            return [
                'total_sectors' => 0,
                'total_zones_with_sectors' => 0,
                'total_stalls' => 0
            ];
        }
    }

    /**
     * Obtener todas las zonas disponibles para seleccionar
     * @return array
     */
    public function getAvailableZones() {
        try {
            $query = "SELECT id, name FROM zones ORDER BY name ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en getAvailableZones: " . $e->getMessage());
            return [];
        }
    }
}

?>