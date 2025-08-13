<?php
/**
 * Vista para eliminar tasa de euro (Manejo AJAX)
 * Este archivo procesa las solicitudes de eliminación enviadas por AJAX
 */

// Configurar headers para respuesta JSON
header('Content-Type: application/json');

// Solo permitir método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
    exit;
}

try {
    // Incluir el controlador
    require_once __DIR__ . '/../../controllers/EuroRatesController.php';
    
    $euroRatesController = new EuroRatesController();
    
    // Obtener ID de la tasa de euro a eliminar
    $id = $_POST['id'] ?? null;
    
    if (!$id) {
        echo json_encode([
            'success' => false,
            'message' => 'ID de tasa de euro no proporcionado'
        ]);
        exit;
    }
    
    // Validar que el ID sea numérico
    if (!is_numeric($id)) {
        echo json_encode([
            'success' => false,
            'message' => 'ID de tasa de euro no válido'
        ]);
        exit;
    }
    
    // Procesar eliminación
    $result = $euroRatesController->delete(['id' => (int)$id]);
    
    // Configurar mensaje flash para la próxima página si fue exitoso
    if ($result['success']) {
        session_start();
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'message' => $result['message']
        ];
    }
    
    // Retornar resultado como JSON
    echo json_encode($result);
    
} catch (Exception $e) {
    // Log del error
    error_log("Error al eliminar tasa de euro: " . $e->getMessage());
    
    // Respuesta de error
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor al eliminar la tasa de euro'
    ]);
}
?>