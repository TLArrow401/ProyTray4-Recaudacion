<?php
// Vista para editar tasa de euro

// Incluir el controlador
require_once __DIR__ . '/../../controllers/EuroRatesController.php';

$euroRatesController = new EuroRatesController();

// Obtener ID de la URL
$id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'ID de tasa de euro no proporcionado'
    ];
    header('Location: index.php');
    exit;
}

// Procesar parámetros de la petición
$params = [
    'id' => $id,
    '_method' => $_SERVER['REQUEST_METHOD'],
    'bs_value' => $_POST['bs_value'] ?? '',
    'month' => $_POST['month'] ?? '',
    'year' => $_POST['year'] ?? ''
];

// Usar el controlador para manejar la edición
$result = $euroRatesController->edit($params);

// Si hay error o redirección, manejarla
if (!$result['success'] && isset($result['redirect'])) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => $result['message']
    ];
    header('Location: ' . $result['redirect']);
    exit;
}

// Si hay redirección por éxito, ejecutarla
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
$euro_rate = $result['euro_rate'] ?? [];
$bs_value = $result['bs_value'] ?? '';
$month = $result['month'] ?? '';
$year = $result['year'] ?? '';
$months_list = $result['months_list'] ?? [];
$years_list = $result['years_list'] ?? [];
$page_title = $result['page_title'] ?? 'Editar Tasa de Euro';

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
                            <i class="ri-edit-line mr-1"></i>
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
                                    <div class="alert alert-warning" role="alert">
                                        <i class="ri-alert-line"></i>
                                        <strong>Atención:</strong>
                                        <ul class="mb-0 mt-2">
                                            <li>Si modifica el período (mes/año), asegúrese de que no exista otra tasa para ese mismo período</li>
                                            <li>Los cambios afectarán todos los cálculos que dependan de esta tasa</li>
                                            <li>Se recomienda verificar la información antes de guardar</li>
                                        </ul>
                                    </div>
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
                                    <i class="ri-save-line"></i> Actualizar Tasa de Euro
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Información del registro -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="ri-information-line"></i>
                            Información del Registro
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-muted">
                            <div class="col-md-4">
                                <small>
                                    <strong>ID de la Tasa:</strong> 
                                    #<?php echo htmlspecialchars($id); ?>
                                </small>
                            </div>
                            <div class="col-md-4">
                                <small>
                                    <strong>Valor Actual:</strong> 
                                    <?php 
                                    if (!empty($euro_rate['bs_value'])) {
                                        $euroRatesModel = new EuroRatesModel();
                                        echo htmlspecialchars($euroRatesModel->formatBsValue($euro_rate['bs_value']));
                                    } else {
                                        echo 'No disponible';
                                    }
                                    ?>
                                </small>
                            </div>
                            <div class="col-md-4">
                                <small>
                                    <strong>Período Actual:</strong> 
                                    <?php 
                                    if (!empty($euro_rate)) {
                                        $euroRatesModel = new EuroRatesModel();
                                        echo htmlspecialchars($euroRatesModel->formatPeriod($euro_rate));
                                    } else {
                                        echo 'No disponible';
                                    }
                                    ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Historial de cambios (opcional - podría implementarse en el futuro) -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="ri-history-line"></i>
                            Consejos para la Edición
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-warning">
                                    <i class="ri-error-warning-line"></i>
                                    Precauciones
                                </h6>
                                <ul class="text-muted small">
                                    <li>Verifique que el nuevo valor sea correcto</li>
                                    <li>Considere el impacto en cálculos existentes</li>
                                    <li>No cambie períodos históricos sin autorización</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-info">
                                    <i class="ri-lightbulb-line"></i>
                                    Recomendaciones
                                </h6>
                                <ul class="text-muted small">
                                    <li>Use tasas con períodos para mejor control</li>
                                    <li>Mantenga consistencia en el formato</li>
                                    <li>Documente cambios importantes</li>
                                </ul>
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
        console.log('Advertencia: Si especifica mes, debe especificar año y viceversa');
    }
    
    // Validar si el período ya existe (solo si ambos están llenos)
    if (month && year) {
        checkPeriodAvailability(month, year, <?php echo $id; ?>);
    }
}

function checkPeriodAvailability(month, year, excludeId) {
    // Esta función podría hacer una petición AJAX para verificar si el período ya existe
    // excluyendo el registro actual
    fetch('index.php?ajax=validate_period', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'month=' + encodeURIComponent(month) + '&year=' + encodeURIComponent(year) + '&exclude_id=' + excludeId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.exists) {
            console.log('Advertencia: Ya existe una tasa para este período');
            // Aquí podrías mostrar una advertencia visual
        }
    })
    .catch(error => {
        console.error('Error al validar período:', error);
    });
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

// Confirmar cambios significativos
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const originalValue = '<?php echo htmlspecialchars($euro_rate['bs_value'] ?? ''); ?>';
    
    form.addEventListener('submit', function(e) {
        const newValue = document.getElementById('bs_value').value;
        const change = Math.abs(parseFloat(newValue) - parseFloat(originalValue));
        const changePercent = (change / parseFloat(originalValue)) * 100;
        
        // Si el cambio es mayor al 10%, pedir confirmación
        if (changePercent > 10) {
            if (!confirm('El cambio en la tasa es significativo (' + changePercent.toFixed(1) + '%). ¿Está seguro de continuar?')) {
                e.preventDefault();
            }
        }
    });
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>