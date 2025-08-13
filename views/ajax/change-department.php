<?php
require_once __DIR__ . '/../../controllers/AuthController.php';

header('Content-Type: application/json');

$authController = new AuthController();

// Verificar que el usuario esté autenticado
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

if (!isset($input['department']) || empty($input['department'])) {
    echo json_encode(['success' => false, 'message' => 'Departamento no especificado']);
    exit;
}

try {
    // Procesar cambio de departamento usando el controlador
    $result = $authController->changeDepartment($input['department']);
    
    // Retornar resultado
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Error cambiando departamento: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
}

?>