CONFIGURACIÓN DE URLs - PROYECTO SERAMER
==========================================

ARCHIVOS CREADOS:
-----------------
1. config/config.php - Configuración principal con constantes
2. config/app.php - Archivo de inicialización fácil de usar

CÓMO USAR:
----------

1. Al inicio de cada vista, incluir:
   <?php require_once '../../config/app.php'; ?>
   
2. Cambiar las rutas hardcodeadas por las funciones helper:

   ANTES:
   <link rel="stylesheet" href="../../assets/css/demo.css" />
   <script src="../../assets/js/main.js"></script>
   <img src="../../assets/img/logo.png" />
   
   DESPUÉS:
   <link rel="stylesheet" href="<?php echo css('demo.css'); ?>" />
   <script src="<?php echo js('main.js'); ?>"></script>
   <img src="<?php echo img('logo.png'); ?>" />

FUNCIONES DISPONIBLES:
---------------------
- css('archivo.css') - Para archivos CSS
- js('archivo.js') - Para archivos JavaScript  
- img('imagen.png') - Para imágenes
- vendor('libs/jquery/jquery.js') - Para librerías vendor
- asset('ruta/archivo') - Para cualquier asset
- url('pagina') - Para URLs del sitio
- base_url() - URL base del proyecto

CONSTANTES DISPONIBLES:
----------------------
- BASE_URL - URL base del proyecto
- ASSETS_URL - URL de la carpeta assets
- CSS_URL - URL de la carpeta CSS
- JS_URL - URL de la carpeta JavaScript
- IMG_URL - URL de la carpeta de imágenes
- VENDOR_URL - URL de la carpeta vendor
- PROJECT_NAME - Nombre del proyecto
- DEBUG_MODE - Modo debug (true/false)

CONFIGURACIÓN:
--------------
Editar config/config.php y cambiar:
- BASE_URL por la URL de tu proyecto
- PROJECT_NAME por el nombre de tu proyecto

EJEMPLO DE USO COMPLETO:
-----------------------
Ver archivo: views/auth/login_ejemplo.php

VENTAJAS:
---------
✓ URLs centralizadas en un solo archivo
✓ Fácil cambio entre desarrollo y producción  
✓ Código más limpio y mantenible
✓ Sin rutas relativas complicadas (../../)
✓ Detección automática de entorno opcional