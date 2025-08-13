<?php
// Procesar eliminación de cargos

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir el controlador
require_once __DIR__ . '/../../controllers/JobPositionsController.php';

$jobPositionsController = new JobPositionsController();

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Obtener el ID del cargo a eliminar
$id = $_POST['id'] ?? null;

if (!$id) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'ID de cargo no válido'
    ];
    header('Location: index.php');
    exit;
}

// Procesar la eliminación
$result = $jobPositionsController->delete($id);

// Los mensajes flash ya se configuran en el controlador
// Redirigir al listado
header('Location: index.php');
exit;
?>