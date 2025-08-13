<?php
// Verificar acceso primero - ANTES de cualquier output
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
AuthMiddleware::requireUserManagementAccess();

// Incluir el controlador
require_once __DIR__ . '/../../controllers/InternalItemsController.php';

$internalItemsController = new InternalItemsController();

// Inicializar variables
$result = [
    'success' => true,
    'message' => '',
    'messageType' => '',
    'errors' => [],
    'name' => '',
    'payment_count' => ''
];

// Si es POST, procesar la creación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $params = $_POST;
    $params['_method'] = 'POST';
    $result = $internalItemsController->create($params);
    
    // Si la creación fue exitosa y hay redirección, manejarla
    if ($result['success'] && isset($result['redirect'])) {
        // La sesión ya está iniciada por AuthMiddleware
        $_SESSION['message'] = $result['message'];
        $_SESSION['messageType'] = $result['messageType'];
        header('Location: ' . $result['redirect']);
        exit;
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
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title">
                            <i class="ri ri-add-line mr-1"></i>
                            Crear Nuevo Rubro Interno
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
                                            Nombre del Rubro <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                                class="form-control <?php echo isset($result['errors']['name']) ? 'is-invalid' : ''; ?>" 
                                                id="name" 
                                                name="name" 
                                                value="<?php echo htmlspecialchars($result['name']); ?>"
                                                placeholder="Ingrese el nombre del rubro interno"
                                                maxlength="100"
                                                required>
                                        <?php if (isset($result['errors']['name'])): ?>
                                        <div class="invalid-feedback">
                                            <?php echo htmlspecialchars($result['errors']['name']); ?>
                                        </div>
                                        <?php endif; ?>
                                        <small class="form-text text-muted">
                                            Máximo 100 caracteres
                                        </small>
                                    </div>

                                    <div class="form-group">
                                        <label for="payment_count" class="form-label">
                                            Número de Cobros <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" 
                                                class="form-control <?php echo isset($result['errors']['payment_count']) ? 'is-invalid' : ''; ?>" 
                                                id="payment_count" 
                                                name="payment_count" 
                                                value="<?php echo htmlspecialchars($result['payment_count']); ?>"
                                                placeholder="0.00"
                                                step="0.01"
                                                min="0"
                                                required>
                                        <?php if (isset($result['errors']['payment_count'])): ?>
                                        <div class="invalid-feedback">
                                            <?php echo htmlspecialchars($result['errors']['payment_count']); ?>
                                        </div>
                                        <?php endif; ?>
                                        <small class="form-text text-muted">
                                            Ingrese el número de cobros requeridos para este rubro
                                        </small>
                                    </div>
                            </div>

                            <div class="col-md-4">
                                <!-- Información adicional -->
                            
                                <div class="text-muted">
                                    <p><strong>Campos obligatorios:</strong></p>
                                    <ul class="mb-0">
                                        <li>Nombre del rubro</li>
                                        <li>Número de cobros</li>
                                    </ul>
                                </div>

                                <hr>

                                <div class="text-muted">
                                    <p><strong>Validaciones:</strong></p>
                                    <ul class="mb-0">
                                        <li>El nombre debe ser único</li>
                                        <li>Máximo 100 caracteres para el nombre</li>
                                        <li>El número de cobros debe ser positivo</li>
                                    </ul>
                                </div>

                                
                            </div>
                        </div>
                    
                    </div>
                    <div class="card-footer d-flex justify-content-end">
                       <!-- Acciones -->
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="ri ri-save-line mr-1"></i>
                            Crear Rubro Interno
                        </button>
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="ri ri-close-line mr-1"></i>
                            Cancelar
                        </a>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validación en tiempo real
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const nameInput = document.getElementById('name');
    const paymentCountInput = document.getElementById('payment_count');

    // Validar nombre en tiempo real
    nameInput.addEventListener('input', function() {
        const value = this.value.trim();
        const feedback = this.nextElementSibling;
        
        if (value.length > 100) {
            this.classList.add('is-invalid');
            if (feedback && feedback.classList.contains('invalid-feedback')) {
                feedback.textContent = 'El nombre no puede tener más de 100 caracteres';
            }
        } else {
            this.classList.remove('is-invalid');
        }
    });

    // Validar número de cobros en tiempo real
    paymentCountInput.addEventListener('input', function() {
        const value = parseFloat(this.value);
        const feedback = this.nextElementSibling;
        
        if (isNaN(value) || value < 0) {
            this.classList.add('is-invalid');
            if (feedback && feedback.classList.contains('invalid-feedback')) {
                feedback.textContent = 'El número de cobros debe ser un valor positivo';
            }
        } else {
            this.classList.remove('is-invalid');
        }
    });

    // Validación al enviar el formulario
    form.addEventListener('submit', function(e) {
        let isValid = true;

        // Validar nombre
        const name = nameInput.value.trim();
        if (!name) {
            nameInput.classList.add('is-invalid');
            isValid = false;
        }

        // Validar número de cobros
        const paymentCount = parseFloat(paymentCountInput.value);
        if (isNaN(paymentCount) || paymentCount < 0) {
            paymentCountInput.classList.add('is-invalid');
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
            alert('Por favor corrige los errores en el formulario');
        }
    });
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>