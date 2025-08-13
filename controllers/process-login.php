<?php
/**
 * Endpoint para procesar el login
 * Este archivo maneja las peticiones de autenticación
 */

require_once 'AuthController.php';

// Crear instancia del controlador de autenticación
$authController = new AuthController();

// Procesar el login
$authController->processLogin();