<?php
/**
 * Vista para eliminar adjudicatario (Manejo AJAX)
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
    require_once __DIR__ . '/../../controllers/AwardeesController.php';
    
    $awardeesController = new AwardeesController();
    
    // Obtener ID del adjudicatario a eliminar
    $id = $_POST['id'] ?? null;
    
    if (!$id) {
        echo json_encode([
            'success' => false,
            'message' => 'ID de adjudicatario no proporcionado'
        ]);
        exit;
    }
    
    // Procesar eliminación
    $result = $awardeesController->delete($id);
    
    // Retornar resultado como JSON
    echo json_encode($result);
    
} catch (Exception $e) {
    // Log del error
    error_log("Error al eliminar adjudicatario: " . $e->getMessage());
    
    // Respuesta de error
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor'
    ]);
}
?>