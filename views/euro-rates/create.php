<?php
// Vista para crear tasa de euro

// Incluir el controlador
require_once __DIR__ . '/../../controllers/EuroRatesController.php';

$euroRatesController = new EuroRatesController();

// Procesar parámetros de la petición
$params = [
    '_method' => $_SERVER['REQUEST_METHOD'],
    'bs_value' => $_POST['bs_value'] ?? '',
    'month' => $_POST['month'] ?? '',
    'year' => $_POST['year'] ?? ''
];

// Usar el controlador para manejar la creación
$result = $euroRatesController->create($params);

// Si hay redirección, ejecutarla
if (isset($result['redirect']) && $result['success']) {
    $_SESSION['flash_message'] = [
        'type' => 'success',
        'message' => $result['message']
    ];
    header('Location: ' . $result['redirect']);
    exit;
}

// Extraer variables para la vista
$success = $result['success'];
$message = $result['message'] ?? '';
$messageType = $result['messageType'] ?? '';
$errors = $result['errors'] ?? [];
$bs_value = $result['bs_value'] ?? '';
$month = $result['month'] ?? '';
$year = $result['year'] ?? '';
$months_list = $result['months_list'] ?? [];
$years_list = $result['years_list'] ?? [];

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
                            <i class="ri-add-line mr-1"></i>
                            Nueva Tasa de Euro
                        </h5>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="ri-arrow-left-line"></i> Volver al Listado
                        </a>
                    </div>

                    <div class="card-body">
                        <!-- Mostrar mensajes -->
                        <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                            <?php if ($messageType === 'danger'): ?>
                                <i class="ri-error-warning-line"></i>
                            <?php else: ?>
                                <i class="ri-check-line"></i>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>

                        <form method="POST" class="needs-validation" novalidate>
                            <div class="row g-3">
                                <!-- Valor en Bolívares -->
                                <div class="col-md-6">
                                    <label for="bs_value" class="form-label">
                                        Valor en Bolívares <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="ri-money-euro-circle-line"></i>
                                        </span>
                                        <input type="number" 
                                               class="form-control <?php echo isset($errors['bs_value']) ? 'is-invalid' : ''; ?>" 
                                               id="bs_value" 
                                               name="bs_value" 
                                               value="<?php echo htmlspecialchars($bs_value); ?>"
                                               step="0.01"
                                               min="0.01"
                                               max="999999.99"
                                               placeholder="Ej: 36.50"
                                               required>
                                        <span class="input-group-text">Bs.</span>
                                        <?php if (isset($errors['bs_value'])): ?>
                                        <div class="invalid-feedback">
                                            <?php echo htmlspecialchars($errors['bs_value']); ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="form-text">
                                        Valor de 1 Euro en Bolívares (máximo 999,999.99)
                                    </div>
                                </div>

                                <!-- Mes -->
                                <div class="col-md-3">
                                    <label for="month" class="form-label">
                                        Mes
                                    </label>
                                    <select class="form-select <?php echo isset($errors['month']) ? 'is-invalid' : ''; ?>" 
                                            id="month" 
                                            name="month">
                                        <option value="">Seleccionar mes...</option>
                                        <?php foreach ($months_list as $value => $label): ?>
                                        <option value="<?php echo htmlspecialchars($value); ?>" 
                                                <?php echo $month === $value ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($label); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (isset($errors['month'])): ?>
                                    <div class="invalid-feedback">
                                        <?php echo htmlspecialchars($errors['month']); ?>
                                    </div>
                                    <?php endif; ?>
                                    <div class="form-text">
                                        Campo opcional - Deje vacío si no aplica a un mes específico
                                    </div>
                                </div>

                                <!-- Año -->
                                <div class="col-md-3">
                                    <label for="year" class="form-label">
                                        Año
                                    </label>
                                    <select class="form-select <?php echo isset($errors['year']) ? 'is-invalid' : ''; ?>" 
                                            id="year" 
                                            name="year">
                                        <option value="">Seleccionar año...</option>
                                        <?php foreach ($years_list as $yearOption): ?>
                                        <option value="<?php echo htmlspecialchars($yearOption); ?>" 
                                                <?php echo $year === $yearOption ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($yearOption); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (isset($errors['year'])): ?>
                                    <div class="invalid-feedback">
                                        <?php echo htmlspecialchars($errors['year']); ?>
                                    </div>
                                    <?php endif; ?>
                                    <div class="form-text">
                                        Campo opcional - Deje vacío si no aplica a un año específico
                                    </div>
                                </div>

                                <!-- Información adicional -->
                                <div class="col-12">
                                    <div class="alert alert-info" role="alert">
                                        <i class="ri-information-line"></i>
                                        <strong>Importante:</strong>
                                        <ul class="mb-0 mt-2">
                                            <li>El valor en bolívares es obligatorio</li>
                                            <li>Si especifica un mes, debe especificar también el año</li>
                                            <li>Si especifica un año, debe especificar también el mes</li>
                                            <li>No puede existir más de una tasa para el mismo período</li>
                                            <li>Puede crear tasas sin período específico si son tasas generales</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="d-flex justify-content-between">
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="ri-arrow-left-line"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-save-line"></i> Crear Tasa de Euro
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Card con información adicional -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="ri-lightbulb-line"></i>
                            Consejos para el Registro
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary">
                                    <i class="ri-calendar-check-line"></i>
                                    Tasas con Período
                                </h6>
                                <p class="text-muted small">
                                    Use tasas con mes y año específicos para registros históricos 
                                    oficiales o tasas que cambian mensualmente.
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-success">
                                    <i class="ri-global-line"></i>
                                    Tasas Generales
                                </h6>
                                <p class="text-muted small">
                                    Deje el mes y año vacíos para tasas generales o de referencia 
                                    que no están atadas a un período específico.
                                </p>
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

// Validación en tiempo real para consistencia mes/año
document.getElementById('month').addEventListener('change', validatePeriod);
document.getElementById('year').addEventListener('change', validatePeriod);

function validatePeriod() {
    const month = document.getElementById('month').value;
    const year = document.getElementById('year').value;
    
    // Si uno está lleno y el otro vacío, mostrar advertencia
    if ((month && !year) || (!month && year)) {
        // Aquí podrías agregar lógica adicional para mostrar advertencias en tiempo real
        console.log('Advertencia: Si especifica mes, debe especificar año y viceversa');
    }
    
    // Validar si el período ya existe (solo si ambos están llenos)
    if (month && year) {
        checkPeriodAvailability(month, year);
    }
}

function checkPeriodAvailability(month, year) {
    // Esta función podría hacer una petición AJAX para verificar si el período ya existe
    // Implementación opcional para validación en tiempo real
}

// Formatear valor mientras se escribe
document.getElementById('bs_value').addEventListener('input', function(e) {
    let value = e.target.value;
    
    // Permitir solo números y punto decimal
    value = value.replace(/[^0-9.]/g, '');
    
    // Asegurar solo un punto decimal
    const parts = value.split('.');
    if (parts.length > 2) {
        value = parts[0] + '.' + parts.slice(1).join('');
    }
    
    // Limitar decimales a 2 posiciones
    if (parts.length === 2 && parts[1].length > 2) {
        value = parts[0] + '.' + parts[1].substring(0, 2);
    }
    
    e.target.value = value;
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>