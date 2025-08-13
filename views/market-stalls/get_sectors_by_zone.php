<?php
header('Content-Type: application/json');

// Verificar acceso
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
AuthMiddleware::requireUserManagementAccess();

// Incluir el controlador
require_once __DIR__ . '/../../controllers/MarketStallsController.php';

$marketStallsController = new MarketStallsController();

try {
    // Verificar que es una petición POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    // Obtener zone_id del parámetro
    $zone_id = isset($_POST['zone_id']) ? (int)$_POST['zone_id'] : 0;

    if (!$zone_id) {
        throw new Exception('ID de zona no válido');
    }

    // Usar el controlador para obtener sectores
    $result = $marketStallsController->getSectorsByZone(['zone_id' => $zone_id]);

    // Responder con JSON
    echo json_encode($result);

} catch (Exception $e) {
    // Responder con error
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

exit;
?>