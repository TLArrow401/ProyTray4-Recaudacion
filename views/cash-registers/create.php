<?php
// Verificar acceso primero - ANTES de cualquier output
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
AuthMiddleware::requireUserManagementAccess();

// Incluir el controlador
require_once __DIR__ . '/../../controllers/CashRegistersController.php';

$cashRegistersController = new CashRegistersController();

// Inicializar variables
$result = [
    'success' => true,
    'message' => '',
    'messageType' => '',
    'errors' => [],
    'user_id' => '',
    'name' => '',
    'status' => 'active',
    'users' => []
];

// Si es POST, procesar la creación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $params = $_POST;
    $params['_method'] = 'POST';
    $result = $cashRegistersController->create($params);
    
    // Si la creación fue exitosa y hay redirección, manejarla
    if ($result['success'] && isset($result['redirect'])) {
        // La sesión ya está iniciada por AuthMiddleware
        $_SESSION['message'] = $result['message'];
        $_SESSION['messageType'] = $result['messageType'];
        header('Location: ' . $result['redirect']);
        exit;
    }
} else {
    // Solo obtener datos para formulario vacío
    $result = $cashRegistersController->create();
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
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title">
                            <i class="ri ri-add-line mr-1"></i>
                            Crear Nueva Caja Registradora
                        </h5>
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="ri ri-arrow-left-line mr-1"></i>
                            Volver al listado
                        </a>
                    </div>
                    <form method="POST" action="create.php" novalidate>
                    <div class="card-body">
                        <!-- Mostrar mensajes -->
                        <?php if (!$result['success'] && !empty($result['message'])): ?>
                        <div class="alert alert-<?php echo $result['messageType'] ?? 'danger'; ?> alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($result['message']); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <?php endif; ?>

                        
                        <div class="row">
                            <div class="col-md-8">
                                <!-- Información básica -->
                                
                                    <div class="form-group mb-3">
                                        <label for="name" class="form-label">
                                            Nombre de la Caja <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control <?php echo isset($result['errors']['name']) ? 'is-invalid' : ''; ?>" 
                                               id="name" 
                                               name="name" 
                                               value="<?php echo htmlspecialchars($result['name']); ?>" 
                                               maxlength="100"
                                               placeholder="Ej: Caja 1 - Recepción"
                                               required>
                                        <?php if (isset($result['errors']['name'])): ?>
                                        <div class="invalid-feedback">
                                            <?php echo htmlspecialchars($result['errors']['name']); ?>
                                        </div>
                                        <?php endif; ?>
                                        <small class="form-text text-muted">
                                            Máximo 100 caracteres. Incluye información descriptiva como ubicación o función.
                                        </small>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="user_id" class="form-label">
                                            Usuario Asignado <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control <?php echo isset($result['errors']['user_id']) ? 'is-invalid' : ''; ?>" 
                                                id="user_id" 
                                                name="user_id" 
                                                required>
                                            <option value="">Seleccione un usuario</option>
                                            <?php foreach ($result['users'] as $user): ?>
                                            <option value="<?php echo $user['id']; ?>" 
                                                    <?php echo (string)$result['user_id'] === (string)$user['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($user['name'] ?? 'Usuario #' . $user['id']); ?>
                                                <?php if (!empty($user['email'])): ?>
                                                    - <?php echo htmlspecialchars($user['email']); ?>
                                                <?php endif; ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if (isset($result['errors']['user_id'])): ?>
                                        <div class="invalid-feedback">
                                            <?php echo htmlspecialchars($result['errors']['user_id']); ?>
                                        </div>
                                        <?php endif; ?>
                                        <small class="form-text text-muted">
                                            Seleccione el usuario responsable de esta caja registradora.
                                        </small>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="status" class="form-label">
                                            Estado Operativo <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control <?php echo isset($result['errors']['status']) ? 'is-invalid' : ''; ?>" 
                                                id="status" 
                                                name="status" 
                                                required>
                                            <option value="">Seleccione un estado</option>
                                            <option value="active" <?php echo $result['status'] === 'active' ? 'selected' : ''; ?>>
                                                🟢 Activa - En funcionamiento normal
                                            </option>
                                            <option value="inactive" <?php echo $result['status'] === 'inactive' ? 'selected' : ''; ?>>
                                                ⚫ Inactiva - Fuera de servicio
                                            </option>
                                            <option value="maintenance" <?php echo $result['status'] === 'maintenance' ? 'selected' : ''; ?>>
                                                🟡 Mantenimiento - En reparación o mantenimiento
                                            </option>
                                        </select>
                                        <?php if (isset($result['errors']['status'])): ?>
                                        <div class="invalid-feedback">
                                            <?php echo htmlspecialchars($result['errors']['status']); ?>
                                        </div>
                                        <?php endif; ?>
                                        <small class="form-text text-muted">
                                            Define el estado operativo actual de la caja registradora.
                                        </small>
                                    </div>
                                
                            </div>
                            <div class="col-md-4">
                                <!-- Panel de información -->
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="ri ri-information-line mr-1"></i>
                                            Información
                                        </h6>
                                        <ul class="list-unstyled mb-0">
                                            <li class="mb-2">
                                                <small><strong>Estados disponibles:</strong></small>
                                                <ul class="mt-1">
                                                    <li><small><span class="badge badge-success">Activa</span> - Caja operativa y disponible</small></li>
                                                    <li><small><span class="badge badge-secondary">Inactiva</span> - Caja fuera de servicio</small></li>
                                                    <li><small><span class="badge badge-warning">Mantenimiento</span> - Caja en reparación</small></li>
                                                </ul>
                                            </li>
                                            <li class="mb-2">
                                                <small><strong>Usuarios:</strong></small><br>
                                                <small class="text-muted">Solo usuarios activos aparecen en la lista</small>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Panel de consejos -->
                                <div class="card bg-primary text-white mt-3">
                                    <div class="card-body">
                                        <h6 class="card-title text-white">
                                            <i class="ri ri-lightbulb-line mr-1"></i>
                                            Consejos
                                        </h6>
                                        <ul class="list-unstyled mb-0">
                                            <li class="mb-2">
                                                <small>💡 Use nombres descriptivos que incluyan la ubicación</small>
                                            </li>
                                            <li class="mb-2">
                                                <small>👤 Asigne cada caja a un usuario responsable</small>
                                            </li>
                                            <li class="mb-2">
                                                <small>⚙️ Configure el estado inicial según la situación actual</small>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri ri-save-line mr-1"></i>
                                    Crear Caja Registradora
                                </button>
                                <a href="index.php" class="btn btn-outline-secondary ml-2">
                                    <i class="ri ri-close-line mr-1"></i>
                                    Cancelar
                                </a>
                            </div>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts adicionales para mejorar la UX -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-focus en el primer campo
    document.getElementById('name').focus();
    
    // Validación en tiempo real del nombre
    const nameInput = document.getElementById('name');
    const maxLength = 100;
    
    nameInput.addEventListener('input', function() {
        const currentLength = this.value.length;
        const remaining = maxLength - currentLength;
        
        // Cambiar color según proximidad al límite
        if (remaining < 10) {
            this.classList.add('border-warning');
            this.classList.remove('border-danger');
        } else if (remaining < 0) {
            this.classList.add('border-danger');
            this.classList.remove('border-warning');
        } else {
            this.classList.remove('border-warning', 'border-danger');
        }
    });
    
    // Mejorar la visualización del selector de estado
    const statusSelect = document.getElementById('status');
    statusSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        // Cambiar color del select según el estado seleccionado
        this.classList.remove('border-success', 'border-secondary', 'border-warning');
        
        if (this.value === 'active') {
            this.classList.add('border-success');
        } else if (this.value === 'inactive') {
            this.classList.add('border-secondary');
        } else if (this.value === 'maintenance') {
            this.classList.add('border-warning');
        }
    });
    
    // Trigger inicial para el selector de estado
    if (statusSelect.value) {
        statusSelect.dispatchEvent(new Event('change'));
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>