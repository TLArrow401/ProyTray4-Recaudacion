<?php
// Vista para editar adjudicatario

// Incluir el controlador
require_once __DIR__ . '/../../controllers/AwardeesController.php';

$awardeesController = new AwardeesController();

// Obtener ID del adjudicatario
$id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'ID de adjudicatario no proporcionado'
    ];
    header('Location: index.php');
    exit;
}

// Variables para mantener los datos del formulario
$formData = [];
$errors = [];
$result = null;
$editData = null;

// Procesar formulario si se envió
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario
    $formData = [
        'first_name' => $_POST['first_name'] ?? '',
        'middle_name' => $_POST['middle_name'] ?? '',
        'last_name' => $_POST['last_name'] ?? '',
        'second_last_name' => $_POST['second_last_name'] ?? '',
        'id_number' => $_POST['id_number'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'email' => $_POST['email'] ?? '',
        'address' => $_POST['address'] ?? ''
    ];
    
    // Procesar actualización
    $result = $awardeesController->update($id, $formData);
    
    if ($result['success']) {
        // Redirigir en caso de éxito
        if (isset($result['redirect'])) {
            header('Location: ' . $result['redirect']);
            exit;
        }
    } else {
        // Mostrar errores
        $errors = $result['errors'] ?? [$result['message']];
    }
} else {
    // Cargar datos existentes del adjudicatario
    $editData = $awardeesController->edit($id);
    
    if (!$editData['success']) {
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'message' => $editData['message']
        ];
        header('Location: index.php');
        exit;
    }
    
    // Llenar el formulario con los datos existentes
    $formData = $editData['awardee'];
}

$page_title = $editData['page_title'] ?? 'Editar Adjudicatario';

// Incluir header y layouts
require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title">
                            <i class="ri-user-settings-line mr-1"></i>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <div class="btn-group">
                            <a href="view.php?id=<?php echo $id; ?>" class="btn btn-outline-info">
                                <i class="ri-eye-line"></i> Ver Detalles
                            </a>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="ri-arrow-left-line"></i> Volver al Listado
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Mostrar errores -->
                        <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger" role="alert">
                            <h6><i class="ri-error-warning-line"></i> Se encontraron los siguientes errores:</h6>
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>

                        <form method="POST" class="needs-validation" novalidate>
                            <div class="row g-3">
                                <!-- Primer Nombre -->
                                <div class="col-md-6">
                                    <label for="first_name" class="form-label">
                                        Primer Nombre <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control <?php echo in_array('El primer nombre es obligatorio', $errors) || in_array('El primer nombre debe tener al menos 2 caracteres', $errors) || in_array('El primer nombre no puede exceder 50 caracteres', $errors) || in_array('El primer nombre solo puede contener letras y espacios', $errors) ? 'is-invalid' : ''; ?>" 
                                           id="first_name" 
                                           name="first_name" 
                                           value="<?php echo htmlspecialchars($formData['first_name'] ?? ''); ?>"
                                           maxlength="50"
                                           required>
                                    <div class="invalid-feedback">
                                        Por favor ingrese un primer nombre válido.
                                    </div>
                                </div>

                                <!-- Segundo Nombre -->
                                <div class="col-md-6">
                                    <label for="middle_name" class="form-label">Segundo Nombre</label>
                                    <input type="text" 
                                           class="form-control <?php echo in_array('El segundo nombre no puede exceder 50 caracteres', $errors) || in_array('El segundo nombre solo puede contener letras y espacios', $errors) ? 'is-invalid' : ''; ?>" 
                                           id="middle_name" 
                                           name="middle_name" 
                                           value="<?php echo htmlspecialchars($formData['middle_name'] ?? ''); ?>"
                                           maxlength="50">
                                    <div class="form-text">Campo opcional</div>
                                </div>

                                <!-- Primer Apellido -->
                                <div class="col-md-6">
                                    <label for="last_name" class="form-label">
                                        Primer Apellido <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control <?php echo in_array('El primer apellido es obligatorio', $errors) || in_array('El primer apellido debe tener al menos 2 caracteres', $errors) || in_array('El primer apellido no puede exceder 50 caracteres', $errors) || in_array('El primer apellido solo puede contener letras y espacios', $errors) ? 'is-invalid' : ''; ?>" 
                                           id="last_name" 
                                           name="last_name" 
                                           value="<?php echo htmlspecialchars($formData['last_name'] ?? ''); ?>"
                                           maxlength="50"
                                           required>
                                    <div class="invalid-feedback">
                                        Por favor ingrese un primer apellido válido.
                                    </div>
                                </div>

                                <!-- Segundo Apellido -->
                                <div class="col-md-6">
                                    <label for="second_last_name" class="form-label">Segundo Apellido</label>
                                    <input type="text" 
                                           class="form-control <?php echo in_array('El segundo apellido no puede exceder 50 caracteres', $errors) || in_array('El segundo apellido solo puede contener letras y espacios', $errors) ? 'is-invalid' : ''; ?>" 
                                           id="second_last_name" 
                                           name="second_last_name" 
                                           value="<?php echo htmlspecialchars($formData['second_last_name'] ?? ''); ?>"
                                           maxlength="50">
                                    <div class="form-text">Campo opcional</div>
                                </div>

                                <!-- Número de Identificación -->
                                <div class="col-md-6">
                                    <label for="id_number" class="form-label">
                                        Número de Identificación <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control <?php echo in_array('El número de identificación es obligatorio', $errors) || in_array('El número de identificación debe tener al menos 7 caracteres', $errors) || in_array('El número de identificación no puede exceder 20 caracteres', $errors) || in_array('El número de identificación solo puede contener V (opcional), números y guiones', $errors) || in_array('Ya existe otro adjudicatario con ese número de identificación', $errors) ? 'is-invalid' : ''; ?>" 
                                           id="id_number" 
                                           name="id_number" 
                                           value="<?php echo htmlspecialchars($formData['id_number'] ?? ''); ?>"
                                           maxlength="20"
                                           placeholder="Ej: V12345678 o 12345678-9"
                                           required>
                                    <div class="form-text">Cédula de identidad o pasaporte</div>
                                    <div class="invalid-feedback">
                                        Por favor ingrese un número de identificación válido.
                                    </div>
                                </div>

                                <!-- Teléfono -->
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Teléfono</label>
                                    <input type="tel" 
                                           class="form-control <?php echo in_array('El número de teléfono no puede exceder 20 caracteres', $errors) || in_array('El número de teléfono contiene caracteres no válidos', $errors) ? 'is-invalid' : ''; ?>" 
                                           id="phone" 
                                           name="phone" 
                                           value="<?php echo htmlspecialchars($formData['phone'] ?? ''); ?>"
                                           maxlength="20"
                                           placeholder="Ej: +1-234-567-8900">
                                    <div class="form-text">Campo opcional - Incluya código de país si aplica</div>
                                </div>

                                <!-- Email -->
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Correo Electrónico</label>
                                    <input type="email" 
                                           class="form-control <?php echo in_array('El correo electrónico no puede exceder 100 caracteres', $errors) || in_array('El formato del correo electrónico no es válido', $errors) ? 'is-invalid' : ''; ?>" 
                                           id="email" 
                                           name="email" 
                                           value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>"
                                           maxlength="100"
                                           placeholder="correo@ejemplo.com">
                                    <div class="form-text">Campo opcional</div>
                                </div>

                                <!-- Dirección -->
                                <div class="col-12">
                                    <label for="address" class="form-label">Dirección</label>
                                    <textarea class="form-control <?php echo in_array('La dirección no puede exceder 500 caracteres', $errors) ? 'is-invalid' : ''; ?>" 
                                              id="address" 
                                              name="address" 
                                              rows="3"
                                              maxlength="500"
                                              placeholder="Dirección completa del adjudicatario..."><?php echo htmlspecialchars($formData['address'] ?? ''); ?></textarea>
                                    <div class="form-text">Campo opcional - Máximo 500 caracteres</div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="d-flex justify-content-between">
                                <div>
                                    <a href="index.php" class="btn btn-secondary">
                                        <i class="ri-arrow-left-line"></i> Cancelar
                                    </a>
                                    <a href="view.php?id=<?php echo $id; ?>" class="btn btn-outline-info ms-2">
                                        <i class="ri-eye-line"></i> Ver Detalles
                                    </a>
                                </div>
                                <button type="submit" class="btn btn-warning">
                                    <i class="ri-save-line"></i> Actualizar Adjudicatario
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Información adicional -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="ri-information-line"></i>
                            Información del Registro
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-muted">
                            <div class="col-md-6">
                                <small>
                                    <strong>ID del Adjudicatario:</strong> 
                                    <?php echo htmlspecialchars($id); ?>
                                </small>
                            </div>
                            <div class="col-md-6">
                                <small>
                                    <strong>Último número de identificación registrado:</strong> 
                                    <?php echo htmlspecialchars($editData['awardee']['id_number'] ?? 'N/A'); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validación de formulario con Bootstrap
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();

// Formatear número de identificación mientras se escribe
document.getElementById('id_number').addEventListener('input', function(e) {
    // Permitir V al inicio, números y guiones (formatos: V12345678, 12345678-9)
    let value = e.target.value.replace(/[^0-9V\-]/g, '');
    e.target.value = value;
});

// Formatear teléfono mientras se escribe
document.getElementById('phone').addEventListener('input', function(e) {
    // Permitir solo números, espacios, guiones, paréntesis y el signo +
    let value = e.target.value.replace(/[^0-9\+\-\s\(\)]/g, '');
    e.target.value = value;
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>