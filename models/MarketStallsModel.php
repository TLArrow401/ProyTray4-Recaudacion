<?php

require_once __DIR__ . '/../config/Database.php';

class MarketStallsModel {
    private $conn;
    private $table = 'market_stalls';
    
    // Propiedades del local
    public $id;
    public $sector_id;
    public $stall_number;
    public $location_description;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Obtener todos los locales con paginación y búsqueda
     * @param int $page
     * @param int $limit
     * @param string $search
     * @return array
     */
    public function getAll($page = 1, $limit = 10, $search = '') {
        try {
            $offset = ($page - 1) * $limit;
            
            $query = "SELECT ms.*, s.name as sector_name, z.name as zone_name 
                      FROM " . $this->table . " ms 
                      LEFT JOIN sectors s ON ms.sector_id = s.id 
                      LEFT JOIN zones z ON s.zone_id = z.id";
            $params = [];
            
            if (!empty($search)) {
                $query .= " WHERE ms.stall_number LIKE :search 
                           OR ms.location_description LIKE :search 
                           OR s.name LIKE :search 
                           OR z.name LIKE :search";
                $params['search'] = '%' . $search . '%';
            }
            
            $query .= " ORDER BY z.name ASC, s.name ASC, ms.stall_number ASC LIMIT :limit OFFSET :offset";
            
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
            throw new Exception("Error al obtener los locales");
        }
    }

    /**
     * Contar total de locales
     * @param string $search
     * @return int
     */
    public function countItems($search = '') {
        try {
            $query = "SELECT COUNT(*) as total FROM " . $this->table . " ms 
                      LEFT JOIN sectors s ON ms.sector_id = s.id 
                      LEFT JOIN zones z ON s.zone_id = z.id";
            $params = [];
            
            if (!empty($search)) {
                $query .= " WHERE ms.stall_number LIKE :search 
                           OR ms.location_description LIKE :search 
                           OR s.name LIKE :search 
                           OR z.name LIKE :search";
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
     * Obtener local por ID
     * @param int $id
     * @return array|false
     */
    public function getById($id) {
        try {
            $query = "SELECT ms.*, s.name as sector_name, s.zone_id, z.name as zone_name 
                      FROM " . $this->table . " ms 
                      LEFT JOIN sectors s ON ms.sector_id = s.id 
                      LEFT JOIN zones z ON s.zone_id = z.id 
                      WHERE ms.id = :id";
            
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
     * Crear nuevo local
     * @param array $data
     * @return array
     */
    public function create($data) {
        try {
            // Validar datos requeridos
            if (empty($data['stall_number'])) {
                return [
                    'success' => false,
                    'message' => 'El número del local es requerido',
                    'errors' => ['stall_number' => 'El número del local es requerido']
                ];
            }

            if (empty($data['sector_id'])) {
                return [
                    'success' => false,
                    'message' => 'El sector es requerido',
                    'errors' => ['sector_id' => 'El sector es requerido']
                ];
            }

            // Verificar que el sector existe
            if (!$this->sectorExists($data['sector_id'])) {
                return [
                    'success' => false,
                    'message' => 'El sector seleccionado no existe',
                    'errors' => ['sector_id' => 'El sector seleccionado no existe']
                ];
            }

            // Verificar si el número del local ya existe en el mismo sector
            if ($this->stallNumberExistsInSector($data['stall_number'], $data['sector_id'])) {
                return [
                    'success' => false,
                    'message' => 'Ya existe un local con este número en el sector seleccionado',
                    'errors' => ['stall_number' => 'Ya existe un local con este número en el sector seleccionado']
                ];
            }

            $query = "INSERT INTO " . $this->table . " 
                     (sector_id, stall_number, location_description) 
                     VALUES (:sector_id, :stall_number, :location_description)";
            
            $stmt = $this->conn->prepare($query);
            
            // Limpiar y preparar datos
            $sector_id = (int)$data['sector_id'];
            $stall_number = trim($data['stall_number']);
            $location_description = !empty($data['location_description']) ? trim($data['location_description']) : null;
            
            $stmt->bindParam(':sector_id', $sector_id, PDO::PARAM_INT);
            $stmt->bindParam(':stall_number', $stall_number);
            $stmt->bindParam(':location_description', $location_description);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Local creado exitosamente',
                    'id' => $this->conn->lastInsertId()
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al crear el local'
                ];
            }
            
        } catch (PDOException $e) {
            error_log("Error en create: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error de base de datos al crear el local'
            ];
        }
    }

    /**
     * Actualizar local
     * @param int $id
     * @param array $data
     * @return array
     */
    public function update($id, $data) {
        try {
            // Verificar si el local existe
            if (!$this->getById($id)) {
                return [
                    'success' => false,
                    'message' => 'Local no encontrado'
                ];
            }

            // Validar datos requeridos
            if (empty($data['stall_number'])) {
                return [
                    'success' => false,
                    'message' => 'El número del local es requerido',
                    'errors' => ['stall_number' => 'El número del local es requerido']
                ];
            }

            if (empty($data['sector_id'])) {
                return [
                    'success' => false,
                    'message' => 'El sector es requerido',
                    'errors' => ['sector_id' => 'El sector es requerido']
                ];
            }

            // Verificar que el sector existe
            if (!$this->sectorExists($data['sector_id'])) {
                return [
                    'success' => false,
                    'message' => 'El sector seleccionado no existe',
                    'errors' => ['sector_id' => 'El sector seleccionado no existe']
                ];
            }

            // Verificar si el número del local ya existe en el mismo sector (excluyendo el actual)
            if ($this->stallNumberExistsInSector($data['stall_number'], $data['sector_id'], $id)) {
                return [
                    'success' => false,
                    'message' => 'Ya existe un local con este número en el sector seleccionado',
                    'errors' => ['stall_number' => 'Ya existe un local con este número en el sector seleccionado']
                ];
            }

            $query = "UPDATE " . $this->table . " 
                     SET sector_id = :sector_id, stall_number = :stall_number, location_description = :location_description 
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            
            // Limpiar y preparar datos
            $sector_id = (int)$data['sector_id'];
            $stall_number = trim($data['stall_number']);
            $location_description = !empty($data['location_description']) ? trim($data['location_description']) : null;
            
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':sector_id', $sector_id, PDO::PARAM_INT);
            $stmt->bindParam(':stall_number', $stall_number);
            $stmt->bindParam(':location_description', $location_description);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Local actualizado exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al actualizar el local'
                ];
            }
            
        } catch (PDOException $e) {
            error_log("Error en update: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error de base de datos al actualizar el local'
            ];
        }
    }

    /**
     * Eliminar local
     * @param int $id
     * @return array
     */
    public function delete($id) {
        try {
            // Verificar si el local existe
            $item = $this->getById($id);
            if (!$item) {
                return [
                    'success' => false,
                    'message' => 'Local no encontrado'
                ];
            }

            $query = "DELETE FROM " . $this->table . " WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Local "' . $item['stall_number'] . '" eliminado exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al eliminar el local'
                ];
            }
            
        } catch (PDOException $e) {
            error_log("Error en delete: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error de base de datos al eliminar el local'
            ];
        }
    }

    /**
     * Verificar si un número de local ya existe en un sector
     * @param string $stall_number
     * @param int $sector_id
     * @param int $excludeId
     * @return bool
     */
    private function stallNumberExistsInSector($stall_number, $sector_id, $excludeId = null) {
        try {
            $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE stall_number = :stall_number AND sector_id = :sector_id";
            
            if ($excludeId) {
                $query .= " AND id != :excludeId";
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':stall_number', $stall_number);
            $stmt->bindParam(':sector_id', $sector_id, PDO::PARAM_INT);
            
            if ($excludeId) {
                $stmt->bindParam(':excludeId', $excludeId, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return (int)$result['count'] > 0;
            
        } catch (PDOException $e) {
            error_log("Error en stallNumberExistsInSector: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar si un sector existe
     * @param int $sector_id
     * @return bool
     */
    private function sectorExists($sector_id) {
        try {
            $query = "SELECT COUNT(*) as count FROM sectors WHERE id = :sector_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':sector_id', $sector_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return (int)$result['count'] > 0;
            
        } catch (PDOException $e) {
            error_log("Error en sectorExists: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener todos los locales sin paginación (para listas desplegables)
     * @return array
     */
    public function getAllSimple() {
        try {
            $query = "SELECT ms.id, ms.stall_number, ms.location_description, ms.sector_id, 
                             s.name as sector_name, z.name as zone_name 
                      FROM " . $this->table . " ms 
                      LEFT JOIN sectors s ON ms.sector_id = s.id 
                      LEFT JOIN zones z ON s.zone_id = z.id 
                      ORDER BY z.name ASC, s.name ASC, ms.stall_number ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en getAllSimple: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener locales por sector
     * @param int $sector_id
     * @return array
     */
    public function getBySector($sector_id) {
        try {
            $query = "SELECT * FROM " . $this->table . " 
                      WHERE sector_id = :sector_id 
                      ORDER BY stall_number ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':sector_id', $sector_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en getBySector: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Buscar locales por término de búsqueda
     * @param string $searchTerm
     * @param int $limit
     * @return array
     */
    public function search($searchTerm, $limit = 20) {
        try {
            $query = "SELECT ms.*, s.name as sector_name, z.name as zone_name 
                      FROM " . $this->table . " ms 
                      LEFT JOIN sectors s ON ms.sector_id = s.id 
                      LEFT JOIN zones z ON s.zone_id = z.id 
                      WHERE ms.stall_number LIKE :searchTerm 
                         OR ms.location_description LIKE :searchTerm 
                         OR s.name LIKE :searchTerm 
                         OR z.name LIKE :searchTerm
                      ORDER BY z.name ASC, s.name ASC, ms.stall_number ASC 
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
     * Obtener estadísticas de locales
     * @return array
     */
    public function getStats() {
        try {
            $query = "SELECT 
                        COUNT(*) as total_stalls,
                        COUNT(DISTINCT sector_id) as total_sectors_with_stalls,
                        COUNT(DISTINCT s.zone_id) as total_zones_with_stalls
                      FROM " . $this->table . " ms
                      LEFT JOIN sectors s ON ms.sector_id = s.id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en getStats: " . $e->getMessage());
            return [
                'total_stalls' => 0,
                'total_sectors_with_stalls' => 0,
                'total_zones_with_stalls' => 0
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

    /**
     * Obtener sectores por zona para filtrado dinámico
     * @param int $zone_id
     * @return array
     */
    public function getSectorsByZone($zone_id) {
        try {
            $query = "SELECT id, name FROM sectors WHERE zone_id = :zone_id ORDER BY name ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':zone_id', $zone_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en getSectorsByZone: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener todos los sectores disponibles para seleccionar
     * @return array
     */
    public function getAvailableSectors() {
        try {
            $query = "SELECT s.id, s.name, s.zone_id, z.name as zone_name 
                      FROM sectors s 
                      LEFT JOIN zones z ON s.zone_id = z.id 
                      ORDER BY z.name ASC, s.name ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en getAvailableSectors: " . $e->getMessage());
            return [];
        }
    }
}

?>