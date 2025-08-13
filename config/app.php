<?php

/**
 * Archivo de inicialización de la aplicación
 * Incluye todas las configuraciones necesarias
 */

// Incluir configuración principal
require_once __DIR__ . '/config.php';

/**
 * Variables globales para usar en las vistas
 */
$GLOBALS['app'] = [
    'name' => PROJECT_NAME,
    'version' => PROJECT_VERSION,
    'base_url' => BASE_URL,
    'assets_url' => ASSETS_URL,
    'css_url' => CSS_URL,
    'js_url' => JS_URL,
    'img_url' => IMG_URL,
    'dist_url' => DIST_URL,
    'vendor_url' => VENDOR_URL,
    'debug' => DEBUG_MODE
];

/**
 * Funciones simples para assets
 */
function css($file) {
    return CSS_URL . '/' . ltrim($file, '/');
}

function dist($file) {
    return DIST_URL . '/' . ltrim($file, '/');
}

function js($file) {
    return JS_URL . '/' . ltrim($file, '/');
}

function img($file) {
    return IMG_URL . '/' . ltrim($file, '/');
}

function vendor($file) {
    return VENDOR_URL . '/' . ltrim($file, '/');
}