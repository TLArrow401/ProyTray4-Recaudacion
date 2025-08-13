<?php
require_once __DIR__ . '/../../controllers/ContractsController.php';

header('Content-Type: application/json');

$contractsController = new ContractsController();

// Verificar que el usuario esté autenticado
require_once __DIR__ . '/../../controllers/AuthController.php';
$authController = new AuthController();

if (!$authController->isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

// Verificar que la petición sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Obtener datos JSON del cuerpo de la petición
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['zone_id']) || empty($input['zone_id'])) {
    echo json_encode(['success' => false, 'message' => 'Zone ID no especificado']);
    exit;
}

try {
    // Obtener locales por zona y sector usando el controlador
    $result = $contractsController->getStallsByZoneAndSector(
        $input['zone_id'], 
        $input['sector_id'] ?? null
    );
    
    // Retornar resultado
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Error obteniendo locales por zona y sector: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
}

?>