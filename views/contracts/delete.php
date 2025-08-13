<?php
// Script para eliminar contrato

// Incluir el controlador
require_once __DIR__ . '/../../controllers/ContractsController.php';

$contractsController = new ContractsController();

// Obtener ID del contrato
$contract_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$contract_id) {
    header('Location: index.php');
    exit;
}

// Verificar que la petición sea POST con método DELETE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method']) && $_POST['_method'] === 'DELETE') {
    
    // Procesar eliminación
    $result = $contractsController->destroy($contract_id);
    
    if ($result['success']) {
        // Redirigir con mensaje de éxito
        session_start();
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'message' => $result['message']
        ];
        header('Location: index.php');
        exit;
    } else {
        // Redirigir con mensaje de error
        session_start();
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'message' => $result['message']
        ];
        header('Location: index.php');
        exit;
    }
    
} else {
    // Si no es una petición válida, redirigir
    header('Location: index.php');
    exit;
}

?>