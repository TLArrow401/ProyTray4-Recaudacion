<?php
require_once __DIR__ . '/../../controllers/AuthController.php';

$authController = new AuthController();

// Procesar logout
$result = $authController->logout();

// Redireccionar al login
header('Location: ' . $result['redirect']);
exit;
?>