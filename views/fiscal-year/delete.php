<?php
// Verificar acceso primero - ANTES de cualquier output
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
AuthMiddleware::requireUserManagementAccess();

// Incluir el controlador
require_once __DIR__ . '/../../controllers/FiscalYearController.php';

$fiscalYearController = new FiscalYearController();

// Configurar respuesta JSON
header('Content-Type: application/json');

// Verificar que la petición sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Obtener ID del parámetro
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID de año fiscal no proporcionado']);
    exit;
}

try {
    // Usar el controlador para eliminar el año fiscal
    $result = $fiscalYearController->destroy($id);
    
    // Retornar resultado como JSON
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("Error eliminando año fiscal: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Error interno del servidor'
    ]);
}

exit;
?>