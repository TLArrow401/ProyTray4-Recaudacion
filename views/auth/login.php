<?php 
// Incluir la configuración al inicio de cada vista
require_once __DIR__ . '/../../config/app.php';

// Iniciar sesión antes de cualquier output
session_start();
?>
<!doctype html>

<html
  lang="es"
  class="layout-wide customizer-hide"
  dir="ltr"
  data-skin="default"
  data-bs-theme="light"
  data-assets-path="<?php echo ASSETS_URL; ?>/"
  data-template="vertical-menu-template">
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="robots" content="noindex, nofollow" />
    <title>Login - <?php echo PROJECT_NAME; ?></title>

    <meta name="description" content="" />

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

    <link rel="stylesheet" href="<?php echo vendor('libs/node-waves/node-waves.css'); ?>" />

    <link rel="stylesheet" href="<?php echo vendor('libs/pickr/pickr-themes.css'); ?>" />

    <link rel="stylesheet" href="<?php echo vendor('css/core.css'); ?>" />
    <link rel="stylesheet" href="<?php echo css('demo.css'); ?>" />

    <!-- Vendors CSS -->

    <link rel="stylesheet" href="<?php echo vendor('libs/perfect-scrollbar/perfect-scrollbar.css'); ?>" />

    <!-- endbuild -->

    <!-- Vendor -->
    <link rel="stylesheet" href="<?php echo vendor('libs/@form-validation/form-validation.css'); ?>" />

    <!-- Page CSS -->
    <!-- Page -->
    <link rel="stylesheet" href="<?php echo vendor('css/pages/page-auth.css'); ?>" />

    <!-- Helpers -->
    <script src="<?php echo vendor('js/helpers.js'); ?>"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->

    <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js. -->
    <script src="<?php echo vendor('js/template-customizer.js'); ?>"></script>

    <!--? Config: Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file. -->
    <script src="<?php echo js('config.js'); ?>"></script>
  </head>

  <body>
    <!-- Content -->

    <div class="authentication-wrapper authentication-cover">
      <!-- Logo -->
      <a href="<?php echo url('index.php'); ?>" class="auth-cover-brand d-flex align-items-center gap-2">
        <span class="app-brand-logo demo">
            <img src="<?php echo img('logo.png'); ?>" width="32" height="32" alt="Logo" class="logo" />   
        </span>
        <span class="app-brand-text demo text-heading fw-semibold"><?php echo PROJECT_NAME; ?></span>
      </a>
      <!-- /Logo -->
      <div class="authentication-inner row m-0">
        <!-- /Left Section -->
        <div class="d-none d-lg-flex col-lg-7 col-xl-8 align-items-center justify-content-center p-12 pb-2">
          <img
            src="<?php echo img('illustrations/auth-login-illustration-light.png'); ?>"
            class="auth-cover-illustration w-100"
            alt="auth-illustration"
            data-app-light-img="illustrations/auth-login-illustration-light.png"
            data-app-dark-img="illustrations/auth-login-illustration-dark.png" />
          <img
            alt="mask"
            src="<?php echo img('illustrations/auth-basic-login-mask-light.png'); ?>"
            class="authentication-image d-none d-lg-block"
            data-app-light-img="illustrations/auth-basic-login-mask-light.png"
            data-app-dark-img="illustrations/auth-basic-login-mask-dark.png" />
        </div>
        <!-- /Left Section -->

        <!-- Login -->
        <div
          class="d-flex col-12 col-lg-5 col-xl-4 align-items-center authentication-bg position-relative py-sm-12 px-12 py-6">
          <div class="w-px-400 mx-auto pt-12 pt-lg-0">
            <h4 class="mb-1">Bienvenido a <?php echo PROJECT_NAME; ?>! 👋</h4>
            <p class="mb-5">Inicia sesión en tu cuenta</p>

            <?php 
            if (isset($_SESSION['login_error'])): ?>
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <h6 class="alert-heading mb-1">Error de autenticación</h6>
                    <span><?php echo htmlspecialchars($_SESSION['login_error']); ?></span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php 
            unset($_SESSION['login_error']);
            endif; 
            ?>

            <form id="formAuthentication" class="mb-5" action="../../controllers/process-login.php" method="POST">
              <div class="form-floating form-floating-outline mb-5 form-control-validation">
                <input
                  type="text"
                  class="form-control"
                  id="email"
                  name="username"
                  placeholder="Ingresa tu usuario"
                  value="<?php echo isset($_COOKIE['remember_username']) ? htmlspecialchars($_COOKIE['remember_username']) : ''; ?>"
                  autofocus />
                <label for="email">Usuario</label>
              </div>
              <div class="mb-5">
                <div class="form-password-toggle form-control-validation">
                  <div class="input-group input-group-merge">
                    <div class="form-floating form-floating-outline">
                      <input
                        type="password"
                        id="password"
                        class="form-control"
                        name="password"
                        placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                        aria-describedby="password" />
                      <label for="password">Contraseña</label>
                    </div>
                    <span class="input-group-text cursor-pointer"
                      ><i class="icon-base ri ri-eye-off-line icon-20px"></i
                    ></span>
                  </div>
                </div>
              </div>
              <div class="mb-5 d-flex justify-content-between mt-5">
                <div class="form-check mt-2">
                  <input class="form-check-input" type="checkbox" id="remember-me" name="remember_me" 
                         <?php echo isset($_COOKIE['remember_username']) ? 'checked' : ''; ?> />
                  <label class="form-check-label" for="remember-me"> Recordarme </label>
                </div>
                <a href="<?php echo url('views/auth/forgot-password.php'); ?>" class="float-end mb-1 mt-2">
                  <span>¿Olvidaste tu contraseña?</span>
                </a>
              </div>
              <button class="btn btn-primary d-grid w-100" type="submit">Iniciar Sesión</button>
            </form>

          </div>
        </div>
        <!-- /Login -->
      </div>
    </div>

    <!-- / Content -->

    <!-- Core JS -->

    <!-- build:js assets/vendor/js/theme.js  -->

    <script src="<?php echo vendor('libs/jquery/jquery.js'); ?>"></script>

    <script src="<?php echo vendor('libs/popper/popper.js'); ?>"></script>
    <script src="<?php echo vendor('js/bootstrap.js'); ?>"></script>
    <script src="<?php echo vendor('libs/node-waves/node-waves.js'); ?>"></script>

    <script src="<?php echo vendor('libs/pickr/pickr.js'); ?>"></script>

    <script src="<?php echo vendor('libs/perfect-scrollbar/perfect-scrollbar.js'); ?>"></script>

    <script src="<?php echo vendor('libs/hammer/hammer.js'); ?>"></script>

    <script src="<?php echo vendor('libs/i18n/i18n.js'); ?>"></script>

    <script src="<?php echo vendor('js/menu.js'); ?>"></script>

    <!-- endbuild -->

    <!-- Vendors JS -->
    <script src="<?php echo vendor('libs/@form-validation/popular.js'); ?>"></script>
    <script src="<?php echo vendor('libs/@form-validation/bootstrap5.js'); ?>"></script>
    <script src="<?php echo vendor('libs/@form-validation/auto-focus.js'); ?>"></script>

    <!-- Main JS -->

    <script src="<?php echo js('main.js'); ?>"></script>

    <!-- Page JS -->
    <script src="<?php echo js('pages-auth.js'); ?>"></script>
    
    <?php if (DEBUG_MODE): ?>
    <!-- Scripts de desarrollo -->
    <script>
        console.log('🚀 Modo desarrollo activado');
        console.log('📍 Base URL:', '<?php echo BASE_URL; ?>');
        console.log('🎨 Assets URL:', '<?php echo ASSETS_URL; ?>');
        console.log('📱 Proyecto:', '<?php echo PROJECT_NAME; ?> v<?php echo PROJECT_VERSION; ?>');
    </script>
    <?php endif; ?>
    
  </body>
</html>