<?php
// Verificar acceso primero - ANTES de cualquier output
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
AuthMiddleware::requireUserManagementAccess();

// Incluir el controlador
require_once __DIR__ . '/../../controllers/FiscalYearController.php';

$fiscalYearController = new FiscalYearController();

// Inicializar variables
$result = [
    'success' => true,
    'message' => '',
    'messageType' => '',
    'errors' => [],
    'year' => '',
    'start_date' => '',
    'end_date' => '',
    'status' => 'active'
];

// Si es POST, procesar la creación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $params = $_POST;
    $result = $fiscalYearController->store($params);
    
    // Si la creación fue exitosa y hay redirección, manejarla
    if ($result['success'] && isset($result['redirect'])) {
        // La sesión ya está iniciada por AuthMiddleware
        $_SESSION['message'] = $result['message'];
        $_SESSION['messageType'] = $result['messageType'];
        header('Location: ' . $result['redirect']);
        exit;
    }
    
    // Mantener datos del formulario en caso de error
    if (!$result['success']) {
        $result['year'] = $_POST['year'] ?? '';
        $result['start_date'] = $_POST['start_date'] ?? '';
        $result['end_date'] = $_POST['end_date'] ?? '';
        $result['status'] = $_POST['status'] ?? 'active';
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
                            <i class="ri-add-line mr-1"></i>
                            Crear Nuevo Año Fiscal
                        </h5>
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="ri-arrow-left-line mr-1"></i>
                            Volver al listado
                        </a>
                    </div>
                    <form method="POST" action="create.php" novalidate>
                    <div class="card-body">
                        <!-- Mostrar mensajes -->
                        <?php if (!$result['success'] && !empty($result['message'])): ?>
                        <div class="alert alert-<?php echo $result['messageType'] ?? 'danger'; ?> alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($result['message']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php endif; ?>

                        <!-- Mostrar errores de validación -->
                        <?php if (!empty($result['errors'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <h6><i class="ri-error-warning-line"></i> Se encontraron los siguientes errores:</h6>
                            <ul class="mb-0">
                                <?php foreach ($result['errors'] as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-8">
                                <!-- Información básica del año fiscal -->
                                
                                <div class="form-group mb-3">
                                    <label for="year" class="form-label">
                                        Año <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                            class="form-control <?php echo (is_array($result['errors']) && (in_array('El año es obligatorio', $result['errors']) || in_array('El año debe ser un número de 4 dígitos', $result['errors']) || in_array('El año debe estar entre 2020 y 2050', $result['errors']) || in_array('Ya existe un año fiscal con este año', $result['errors']))) ? 'is-invalid' : ''; ?>" 
                                            id="year" 
                                            name="year" 
                                            value="<?php echo htmlspecialchars($result['year']); ?>"
                                            placeholder="2024"
                                            min="2020"
                                            max="2050"
                                            required>
                                    <small class="form-text text-muted">
                                        Año del período fiscal (debe ser único entre 2020-2050)
                                    </small>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="start_date" class="form-label">
                                                Fecha de Inicio <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" 
                                                    class="form-control <?php echo (is_array($result['errors']) && (in_array('La fecha de inicio es obligatoria', $result['errors']) || in_array('La fecha de inicio no es válida', $result['errors']) || in_array('La fecha de inicio debe ser anterior a la fecha de fin', $result['errors']))) ? 'is-invalid' : ''; ?>" 
                                                    id="start_date" 
                                                    name="start_date" 
                                                    value="<?php echo htmlspecialchars($result['start_date']); ?>"
                                                    required>
                                            <small class="form-text text-muted">
                                                Fecha de inicio del año fiscal
                                            </small>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="end_date" class="form-label">
                                                Fecha de Fin <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" 
                                                    class="form-control <?php echo (is_array($result['errors']) && (in_array('La fecha de fin es obligatoria', $result['errors']) || in_array('La fecha de fin no es válida', $result['errors']) || in_array('La fecha de inicio debe ser anterior a la fecha de fin', $result['errors']) || in_array('El período debe ser de aproximadamente un año (360-370 días)', $result['errors']))) ? 'is-invalid' : ''; ?>" 
                                                    id="end_date" 
                                                    name="end_date" 
                                                    value="<?php echo htmlspecialchars($result['end_date']); ?>"
                                                    required>
                                            <small class="form-text text-muted">
                                                Fecha de finalización del año fiscal
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="status" class="form-label">
                                        Estado
                                    </label>
                                    <select class="form-select <?php echo (is_array($result['errors']) && in_array('El estado debe ser activo o inactivo', $result['errors'])) ? 'is-invalid' : ''; ?>" 
                                            id="status" 
                                            name="status">
                                        <option value="active" <?php echo $result['status'] === 'active' ? 'selected' : ''; ?>>
                                            Activo
                                        </option>
                                        <option value="inactive" <?php echo $result['status'] === 'inactive' ? 'selected' : ''; ?>>
                                            Inactivo
                                        </option>
                                    </select>
                                    <small class="form-text text-muted">
                                        Solo los años fiscales activos aparecerán en los selectores de otros módulos
                                    </small>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <!-- Información adicional -->
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="ri-information-line"></i> Información Importante
                                        </h6>
                                        
                                        <div class="text-muted">
                                            <p><strong>Campos obligatorios:</strong></p>
                                            <ul class="mb-3">
                                                <li>Año</li>
                                                <li>Fecha de inicio</li>
                                                <li>Fecha de fin</li>
                                            </ul>
                                            
                                            <p><strong>Validaciones:</strong></p>
                                            <ul class="mb-3">
                                                <li>El año debe ser único</li>
                                                <li>La fecha de inicio debe ser anterior a la de fin</li>
                                                <li>El período debe durar aproximadamente un año (360-370 días)</li>
                                            </ul>
                                            
                                            <p><strong>Nota:</strong></p>
                                            <p class="small">
                                                Un año fiscal define el período contable para los contratos.
                                                No podrá eliminarse si tiene contratos asociados.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Sugerencias automáticas -->
                                <div class="card bg-info bg-opacity-10 border-info mt-3">
                                    <div class="card-body">
                                        <h6 class="card-title text-info">
                                            <i class="ri-lightbulb-line"></i> Sugerencias Automáticas
                                        </h6>
                                        <div class="text-muted small">
                                            <button type="button" class="btn btn-sm btn-outline-info" onclick="fillCurrentYear()">
                                                Llenar con año actual
                                            </button>
                                            <br><small>Completará automáticamente con el año actual y fechas estándar</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="ri-close-line mr-1"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line mr-1"></i>
                                Crear Año Fiscal
                            </button>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Función para llenar automáticamente con el año actual
function fillCurrentYear() {
    const currentYear = new Date().getFullYear();
    const nextYear = currentYear + 1;
    
    document.getElementById('year').value = nextYear;
    document.getElementById('start_date').value = nextYear + '-01-01';
    document.getElementById('end_date').value = nextYear + '-12-31';
    
    // Mostrar feedback visual
    const yearInput = document.getElementById('year');
    yearInput.classList.add('border-info');
    setTimeout(() => {
        yearInput.classList.remove('border-info');
    }, 2000);
}

// Validación en tiempo real de fechas
document.getElementById('start_date').addEventListener('change', validateDates);
document.getElementById('end_date').addEventListener('change', validateDates);

function validateDates() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    
    if (startDate && endDate) {
        const start = new Date(startDate);
        const end = new Date(endDate);
        const diffTime = Math.abs(end - start);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        // Mostrar duración calculada
        const feedback = document.createElement('small');
        feedback.className = 'text-info';
        feedback.innerHTML = `<i class="ri-information-line"></i> Duración: ${diffDays} días`;
        
        // Remover feedback anterior
        const existingFeedback = document.querySelector('.duration-feedback');
        if (existingFeedback) {
            existingFeedback.remove();
        }
        
        // Agregar nuevo feedback
        feedback.classList.add('duration-feedback');
        document.getElementById('end_date').parentNode.appendChild(feedback);
        
        // Colorear según validación
        if (diffDays < 360 || diffDays > 370) {
            feedback.className = 'text-warning duration-feedback';
            feedback.innerHTML = `<i class="ri-alert-line"></i> Duración: ${diffDays} días (recomendado: 360-370 días)`;
        }
    }
}

// Auto-completar año cuando se cambia la fecha de inicio
document.getElementById('start_date').addEventListener('change', function() {
    const startDate = this.value;
    if (startDate && !document.getElementById('year').value) {
        const year = new Date(startDate).getFullYear();
        document.getElementById('year').value = year;
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>