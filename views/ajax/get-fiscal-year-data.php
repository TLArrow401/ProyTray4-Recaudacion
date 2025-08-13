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

if (!isset($input['fiscal_year_id']) || empty($input['fiscal_year_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID del año fiscal no especificado']);
    exit;
}

try {
    // Obtener datos del año fiscal usando el controlador
    $result = $contractsController->getFiscalYearData($input['fiscal_year_id']);
    
    // Retornar resultado
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Error obteniendo datos del año fiscal: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
}

?>