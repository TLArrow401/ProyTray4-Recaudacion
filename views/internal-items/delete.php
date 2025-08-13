<?php
header('Content-Type: application/json');

// Incluir el controlador
require_once __DIR__ . '/../../controllers/InternalItemsController.php';

$internalItemsController = new InternalItemsController();

try {
    // Verificar que es una petición POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    // Obtener ID del parámetro
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if (!$id) {
        throw new Exception('ID de rubro interno no válido');
    }

    // Usar el controlador para eliminar
    $result = $internalItemsController->delete(['id' => $id]);

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