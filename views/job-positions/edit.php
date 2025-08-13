<?php
// Vista de edición/creación de cargos

// Incluir el controlador
require_once __DIR__ . '/../../controllers/JobPositionsController.php';

$jobPositionsController = new JobPositionsController();

$id = $_GET['id'] ?? null;
$is_edit = !empty($id);
$job_position = null;
$page_title = 'Crear Nuevo Cargo';
$errors = [];
$form_data = ['name' => ''];

// Si estamos editando, obtener los datos del cargo
if ($is_edit) {
    $result = $jobPositionsController->edit($id);
    
    if (!$result['success']) {
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'message' => $result['message']
        ];
        header('Location: index.php');
        exit;
    }
    
    $job_position = $result['job_position'];
    $page_title = $result['page_title'];
    $form_data['name'] = $job_position['name'];
}

// Procesar envío del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data = [
        'name' => trim($_POST['name'] ?? '')
    ];
    
    if ($is_edit) {
        $result = $jobPositionsController->update($id, $form_data);
    } else {
        $result = $jobPositionsController->store($form_data);
    }
    
    if ($result['success']) {
        if (isset($result['redirect'])) {
            header('Location: ' . $result['redirect']);
            exit;
        }
    } else {
        $errors = $result['errors'] ?? [$result['message']];
    }
}

// Incluir header y layouts
require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Navegación de breadcrumb -->
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Cargos</a></li>
                        <?php if ($is_edit): ?>
                        <li class="breadcrumb-item"><a href="view.php?id=<?php echo $job_position['id']; ?>"><?php echo htmlspecialchars($job_position['name']); ?></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Editar</li>
                        <?php else: ?>
                        <li class="breadcrumb-item active" aria-current="page">Crear Nuevo</li>
                        <?php endif; ?>
                    </ol>
                </nav>
                
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="ri-briefcase-line mr-1"></i>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <a href="<?php echo $is_edit ? 'view.php?id=' . $job_position['id'] : 'index.php'; ?>" class="btn btn-secondary">
                            <i class="ri-arrow-left-line"></i> 
                            <?php echo $is_edit ? 'Volver a detalles' : 'Volver al listado'; ?>
                        </a>
                    </div>
                    
                    <div class="card-body">
                        <!-- Mostrar errores -->
                        <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <h6 class="alert-heading">
                                <i class="ri-error-warning-line"></i> Se encontraron errores:
                            </h6>
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>

                        <form method="POST" novalidate>
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">
                                            Nombre del Cargo <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control <?php echo !empty($errors) && empty($form_data['name']) ? 'is-invalid' : ''; ?>" 
                                               id="name" 
                                               name="name" 
                                               value="<?php echo htmlspecialchars($form_data['name']); ?>"
                                               placeholder="Ej: Director, Gerente, Analista..."
                                               maxlength="255"
                                               required>
                                        <div class="form-text">
                                            Ingrese el nombre del cargo. Solo se permiten letras, espacios, guiones y puntos.
                                        </div>
                                        <?php if (!empty($errors) && empty($form_data['name'])): ?>
                                        <div class="invalid-feedback">
                                            El nombre del cargo es obligatorio.
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                <i class="ri-information-line"></i> Información
                                            </h6>
                                            <p class="card-text small mb-2">
                                                <strong>Campos obligatorios:</strong> están marcados con <span class="text-danger">*</span>
                                            </p>
                                            <p class="card-text small mb-2">
                                                <strong>Longitud:</strong> Entre 2 y 255 caracteres
                                            </p>
                                            <p class="card-text small mb-0">
                                                <strong>Caracteres permitidos:</strong> Letras, espacios, guiones (-) y puntos (.)
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if ($is_edit && $job_position['staff_count'] > 0): ?>
                            <div class="alert alert-warning">
                                <h6 class="alert-heading">
                                    <i class="ri-alert-line"></i> Precaución
                                </h6>
                                <p class="mb-0">
                                    Este cargo tiene <strong><?php echo $job_position['staff_count']; ?></strong> persona<?php echo $job_position['staff_count'] != 1 ? 's' : ''; ?> asignada<?php echo $job_position['staff_count'] != 1 ? 's' : ''; ?>. 
                                    Al cambiar el nombre del cargo, se actualizará para todo el personal que lo tiene asignado.
                                </p>
                            </div>
                            <?php endif; ?>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="<?php echo $is_edit ? 'view.php?id=' . $job_position['id'] : 'index.php'; ?>" 
                                   class="btn btn-outline-secondary">
                                    <i class="ri-close-line"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-<?php echo $is_edit ? 'warning' : 'primary'; ?>">
                                    <i class="ri-<?php echo $is_edit ? 'save' : 'add'; ?>-line"></i> 
                                    <?php echo $is_edit ? 'Actualizar Cargo' : 'Crear Cargo'; ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Información adicional para edición -->
                <?php if ($is_edit): ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="ri-information-line"></i> Información del Cargo
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>ID del Cargo:</strong> <?php echo htmlspecialchars($job_position['id']); ?></p>
                                <p><strong>Personal Asignado:</strong> 
                                    <span class="badge bg-<?php echo $job_position['staff_count'] > 0 ? 'primary' : 'secondary'; ?>">
                                        <?php echo $job_position['staff_count']; ?> persona<?php echo $job_position['staff_count'] != 1 ? 's' : ''; ?>
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <?php if ($job_position['staff_count'] > 0): ?>
                                <p class="text-muted small">
                                    <i class="ri-team-line"></i> 
                                    <a href="view.php?id=<?php echo $job_position['id']; ?>">Ver personal asignado</a>
                                </p>
                                <?php else: ?>
                                <p class="text-muted small">
                                    <i class="ri-information-line"></i> 
                                    Este cargo puede ser eliminado ya que no tiene personal asignado.
                                </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

<script>
// Validación en tiempo real
document.getElementById('name').addEventListener('input', function() {
    const value = this.value.trim();
    const regex = /^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s\-\.]*$/;
    
    // Remover clases previas
    this.classList.remove('is-valid', 'is-invalid');
    
    if (value.length === 0) {
        this.classList.add('is-invalid');
        return;
    }
    
    if (value.length < 2) {
        this.classList.add('is-invalid');
        return;
    }
    
    if (!regex.test(value)) {
        this.classList.add('is-invalid');
        return;
    }
    
    this.classList.add('is-valid');
});

// Prevenir envío si hay errores
document.querySelector('form').addEventListener('submit', function(e) {
    const nameInput = document.getElementById('name');
    const name = nameInput.value.trim();
    
    if (name.length < 2) {
        e.preventDefault();
        nameInput.classList.add('is-invalid');
        nameInput.focus();
        return false;
    }
    
    const regex = /^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s\-\.]+$/;
    if (!regex.test(name)) {
        e.preventDefault();
        nameInput.classList.add('is-invalid');
        nameInput.focus();
        return false;
    }
});
</script>