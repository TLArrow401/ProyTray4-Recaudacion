<?php

/**
 * Archivo de configuración principal del proyecto
 * Contiene las constantes y configuraciones básicas
 */

// Configuración de la URL base del proyecto
define('BASE_URL', 'http://localhost/seramer21');

// Configuración de rutas de assets
define('ASSETS_URL', BASE_URL . '/public/assets');
define('CSS_URL', ASSETS_URL . '/css');
define('JS_URL', ASSETS_URL . '/js');
define('IMG_URL', ASSETS_URL . '/img');
define('VENDOR_URL', ASSETS_URL . '/vendor');
define('DIST_URL', BASE_URL . '/public/dist');

// Configuración de rutas del sistema
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');
define('VIEWS_PATH', ROOT_PATH . '/views');
define('CONTROLLERS_PATH', ROOT_PATH . '/controller');
define('MODELS_PATH', ROOT_PATH . '/models');
define('PUBLIC_PATH', ROOT_PATH . '/public');

// Configuración del proyecto
define('PROJECT_NAME', 'Seramer');
define('PROJECT_VERSION', '1.0.0');

// Configuración de desarrollo/producción
define('ENVIRONMENT', 'development'); // 'development' o 'production'
define('DEBUG_MODE', ENVIRONMENT === 'development');

/**
 * Función helper para generar URLs de assets
 * @param string $path Ruta relativa del asset
 * @param string $type Tipo de asset (css, js, img, vendor)
 * @return string URL completa del asset
 */
function asset($path, $type = '') {
    switch ($type) {
        case 'css':
            return CSS_URL . '/' . ltrim($path, '/');
        case 'js':
            return JS_URL . '/' . ltrim($path, '/');
        case 'img':
            return IMG_URL . '/' . ltrim($path, '/');
        case 'vendor':
            return VENDOR_URL . '/' . ltrim($path, '/');
        case 'dist':
            return DIST_URL . '/' . ltrim($path, '/');
        default:
            return ASSETS_URL . '/' . ltrim($path, '/');
    }
}

/**
 * Función helper para generar URLs del sitio
 * @param string $path Ruta relativa
 * @return string URL completa
 */
function url($path = '') {
    return BASE_URL . '/' . ltrim($path, '/');
}

/**
 * Función helper para obtener la URL base
 * @return string URL base del proyecto
 */
function base_url() {
    return BASE_URL;
}