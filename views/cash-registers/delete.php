<?php
// Verificar acceso primero - ANTES de cualquier output
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
AuthMiddleware::requireUserManagementAccess();

// Incluir el controlador
require_once __DIR__ . '/../../controllers/CashRegistersController.php';

// Solo permitir métodos POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
    exit;
}

// Verificar que se envió el ID
if (!isset($_POST['id']) || empty($_POST['id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'ID de caja registradora no proporcionado'
    ]);
    exit;
}

$id = (int)$_POST['id'];

if ($id <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'ID de caja registradora no válido'
    ]);
    exit;
}

try {
    // Crear instancia del controlador y procesar eliminación
    $cashRegistersController = new CashRegistersController();
    $result = $cashRegistersController->delete(['id' => $id]);
    
    // Establecer código de respuesta HTTP apropiado
    if ($result['success']) {
        http_response_code(200);
    } else {
        http_response_code(400);
    }
    
    // Devolver respuesta JSON
    header('Content-Type: application/json');
    echo json_encode($result);
    
} catch (Exception $e) {
    // Log del error
    error_log("Error en delete.php: " . $e->getMessage());
    
    // Respuesta de error
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor'
    ]);
}

exit;
?>