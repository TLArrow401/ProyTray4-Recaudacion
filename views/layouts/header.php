<?php 
// Incluir la configuración al inicio de cada vista
require_once __DIR__ . '/../../config/app.php';
?>
<!doctype html>

<html
    lang="en"
    class="layout-navbar-fixed layout-compact layout-menu-fixed"
    dir="ltr"
    data-skin="default"
    data-bs-theme="light"
    data-assets-path="../../public/assets/"
    data-template="vertical-menu-template">
    <head>
        <meta charset="utf-8" />
        <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
        <meta name="robots" content="noindex, nofollow" />
        <title><?php echo PROJECT_NAME; ?></title>

        <meta name="description" content="" />

        <link rel="apple-touch-icon" sizes="57x57" href="<?php echo img('favicon/apple-icon-57x57.png'); ?>">
        <link rel="apple-touch-icon" sizes="60x60" href="<?php echo img('favicon/apple-icon-60x60.png'); ?>">
        <link rel="apple-touch-icon" sizes="72x72" href="<?php echo img('favicon/apple-icon-72x72.png'); ?>">
        <link rel="apple-touch-icon" sizes="76x76" href="<?php echo img('favicon/apple-icon-76x76.png'); ?>">
        <link rel="apple-touch-icon" sizes="114x114" href="<?php echo img('favicon/apple-icon-114x114.png'); ?>">
        <link rel="apple-touch-icon" sizes="120x120" href="<?php echo img('favicon/apple-icon-120x120.png'); ?>">
        <link rel="apple-touch-icon" sizes="144x144" href="<?php echo img('favicon/apple-icon-144x144.png'); ?>">
        <link rel="apple-touch-icon" sizes="152x152" href="<?php echo img('favicon/apple-icon-152x152.png'); ?>">
        <link rel="apple-touch-icon" sizes="180x180" href="<?php echo img('favicon/apple-icon-180x180.png'); ?>">
        <link rel="icon" type="image/png" sizes="192x192"  href="<?php echo img('favicon/android-icon-192x192.png'); ?>">
        <link rel="icon" type="image/png" sizes="32x32" href="<?php echo img('favicon/favicon-32x32.png'); ?>">
        <link rel="icon" type="image/png" sizes="96x96" href="<?php echo img('favicon/favicon-96x96.png'); ?>">
        <link rel="icon" type="image/png" sizes="16x16" href="<?php echo img('favicon/favicon-16x16.png'); ?>">
        <link rel="manifest" href="<?php echo img('favicon/manifest.json'); ?>">
        <meta name="msapplication-TileColor" content="#ffffff">
        <meta name="msapplication-TileImage" content="<?php echo img('favicon/ms-icon-144x144.png'); ?>">
        <meta name="theme-color" content="#ffffff">
        <!-- Favicon -->
        <link rel="icon" type="image/x-icon" href="<?php echo img('favicon/favicon.ico'); ?>" />

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&ampdisplay=swap"
        rel="stylesheet" />
        <link rel="stylesheet" href="<?php echo vendor('fonts/remixicon/remixicon.css'); ?>" />
        <!-- Core CSS -->
        <!-- build:css assets/vendor/css/theme.css -->
        <link rel="stylesheet" href="<?php echo vendor('css/core.css'); ?>" />
        <link rel="stylesheet" href="<?php echo css('demo.css'); ?>" />
        <link rel="stylesheet" href="<?php echo vendor('libs/node-waves/node-waves.css'); ?>" />
        <link rel="stylesheet" href="<?php echo vendor('libs/pickr/pickr-themes.css'); ?>" />
        <!-- Vendors CSS -->
        <link rel="stylesheet" href="<?php echo vendor('libs/perfect-scrollbar/perfect-scrollbar.css'); ?>" />
        <!-- endbuild -->
        <script src="<?php echo vendor('js/template-customizer.js'); ?>"></script>
        <!-- Helpers -->
        <script src="<?php echo vendor('js/helpers.js'); ?>"></script>
        <!--? Config: Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file. -->
        <script src="<?php echo js('config.js'); ?>"></script>
    </head>
    <body>
        <!-- Layout wrapper -->
        <div class="layout-wrapper layout-content-navbar">
            <div class="layout-container">