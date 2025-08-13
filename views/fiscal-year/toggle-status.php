<?php
// Verificar acceso primero - ANTES de cualquier output
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
AuthMiddleware::requireUserManagementAccess();

// Incluir el modelo
require_once __DIR__ . '/../../models/FiscalYearModel.php';

$fiscalYearModel = new FiscalYearModel();

// Configurar respuesta JSON
header('Content-Type: application/json');

// Verificar que la petición sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Obtener parámetros
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$status = isset($_POST['status']) ? $_POST['status'] : '';

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID de año fiscal no proporcionado']);
    exit;
}

if (!in_array($status, ['active', 'inactive'])) {
    echo json_encode(['success' => false, 'message' => 'Estado no válido']);
    exit;
}

try {
    // Verificar que el año fiscal existe
    $fiscal_year = $fiscalYearModel->getById($id);
    
    if (!$fiscal_year) {
        echo json_encode(['success' => false, 'message' => 'Año fiscal no encontrado']);
        exit;
    }
    
    // Actualizar el estado
    $result = $fiscalYearModel->updateStatus($id, $status);
    
    if ($result) {
        $status_text = $status === 'active' ? 'activado' : 'desactivado';
        echo json_encode([
            'success' => true, 
            'message' => "Año fiscal {$status_text} exitosamente"
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Error al actualizar el estado del año fiscal'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Error cambiando estado del año fiscal: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Error interno del servidor'
    ]);
}

exit;
?>