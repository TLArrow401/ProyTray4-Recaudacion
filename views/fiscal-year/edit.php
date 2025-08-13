<?php
// Verificar acceso primero - ANTES de cualquier output
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
AuthMiddleware::requireUserManagementAccess();

// Incluir el controlador
require_once __DIR__ . '/../../controllers/FiscalYearController.php';

$fiscalYearController = new FiscalYearController();

// Obtener ID del parámetro
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header('Location: index.php');
    exit;
}

// Inicializar variables
$result = [
    'success' => true,
    'message' => '',
    'messageType' => '',
    'errors' => [],
    'fiscal_year' => null
];

// Si es POST, procesar la actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $params = $_POST;
    $result = $fiscalYearController->update($id, $params);
    
    // Si la actualización fue exitosa y hay redirección, manejarla
    if ($result['success'] && isset($result['redirect'])) {
        // La sesión ya está iniciada por AuthMiddleware
        $_SESSION['message'] = $result['message'];
        $_SESSION['messageType'] = $result['messageType'];
        header('Location: ' . $result['redirect']);
        exit;
    }
    
    // Mantener datos del formulario en caso de error
    if (!$result['success']) {
        $result['fiscal_year'] = [
            'id' => $id,
            'year' => $_POST['year'] ?? '',
            'start_date' => $_POST['start_date'] ?? '',
            'end_date' => $_POST['end_date'] ?? '',
            'status' => $_POST['status'] ?? 'active'
        ];
    }
} else {
    // Obtener datos del año fiscal para mostrar en el formulario
    $result = $fiscalYearController->edit($id);
    
    // Si hay error al cargar los datos, redirigir
    if (!$result['success']) {
        $_SESSION['message'] = $result['message'];
        $_SESSION['messageType'] = 'error';
        header('Location: index.php');
        exit;
    }
}

$fiscal_year = $result['fiscal_year'];

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
                            Editar Año Fiscal: <?php echo htmlspecialchars($fiscal_year['year']); ?>
                        </h5>
                        <div>
                            <a href="view.php?id=<?php echo $id; ?>" class="btn btn-outline-info mr-2">
                                <i class="ri-eye-line mr-1"></i>
                                Ver detalles
                            </a>
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="ri-arrow-left-line mr-1"></i>
                                Volver al listado
                            </a>
                        </div>
                    </div>
                    <form method="POST" action="edit.php?id=<?php echo $id; ?>" novalidate>
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
                                                class="form-control <?php echo in_array('El año es obligatorio', $result['errors']) || in_array('El año debe ser un número de 4 dígitos', $result['errors']) || in_array('El año debe estar entre 2020 y 2050', $result['errors']) || in_array('Ya existe un año fiscal con este año', $result['errors']) ? 'is-invalid' : ''; ?>" 
                                                id="year" 
                                                name="year" 
                                                value="<?php echo htmlspecialchars($fiscal_year['year']); ?>"
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
                                                        class="form-control <?php echo in_array('La fecha de inicio es obligatoria', $result['errors']) || in_array('La fecha de inicio no es válida', $result['errors']) || in_array('La fecha de inicio debe ser anterior a la fecha de fin', $result['errors']) ? 'is-invalid' : ''; ?>" 
                                                        id="start_date" 
                                                        name="start_date" 
                                                        value="<?php echo htmlspecialchars($fiscal_year['start_date']); ?>"
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
                                                        class="form-control <?php echo in_array('La fecha de fin es obligatoria', $result['errors']) || in_array('La fecha de fin no es válida', $result['errors']) || in_array('La fecha de inicio debe ser anterior a la fecha de fin', $result['errors']) || in_array('El período debe ser de aproximadamente un año (360-370 días)', $result['errors']) ? 'is-invalid' : ''; ?>" 
                                                        id="end_date" 
                                                        name="end_date" 
                                                        value="<?php echo htmlspecialchars($fiscal_year['end_date']); ?>"
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
                                        <select class="form-select <?php echo in_array('El estado debe ser activo o inactivo', $result['errors']) ? 'is-invalid' : ''; ?>" 
                                                id="status" 
                                                name="status">
                                            <option value="active" <?php echo ($fiscal_year['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>
                                                Activo
                                            </option>
                                            <option value="inactive" <?php echo ($fiscal_year['status'] ?? 'active') === 'inactive' ? 'selected' : ''; ?>>
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
                                                <i class="ri-information-line"></i> Información del Año Fiscal
                                            </h6>
                                            
                                            <div class="text-muted">
                                                <p><strong>ID:</strong> <?php echo htmlspecialchars($fiscal_year['id']); ?></p>
                                                
                                                <?php
                                                // Calcular duración actual
                                                $start_date = new DateTime($fiscal_year['start_date']);
                                                $end_date = new DateTime($fiscal_year['end_date']);
                                                $diff = $start_date->diff($end_date);
                                                $days = $diff->days;
                                                ?>
                                                <p><strong>Duración actual:</strong> <?php echo number_format($days); ?> días</p>
                                                
                                                <p><strong>Estado actual:</strong> 
                                                    <span class="badge <?php echo ($fiscal_year['status'] ?? 'active') === 'active' ? 'bg-success' : 'bg-secondary'; ?>">
                                                        <?php echo ($fiscal_year['status'] ?? 'active') === 'active' ? 'Activo' : 'Inactivo'; ?>
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Advertencias -->
                                    <div class="card bg-warning bg-opacity-10 border-warning mt-3">
                                        <div class="card-body">
                                            <h6 class="card-title text-warning">
                                                <i class="ri-alert-line"></i> Advertencias
                                            </h6>
                                            <div class="text-muted small">
                                                <ul class="mb-0">
                                                    <li>Cambiar las fechas puede afectar contratos existentes</li>
                                                    <li>Desactivar este año fiscal ocultará los contratos asociados</li>
                                                    <li>Verificar que el período siga siendo válido (360-370 días)</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <a href="index.php" class="btn btn-outline-secondary">
                                        <i class="ri-close-line mr-1"></i>
                                        Cancelar
                                    </a>
                                    <a href="view.php?id=<?php echo $id; ?>" class="btn btn-outline-info ml-2">
                                        <i class="ri-eye-line mr-1"></i>
                                        Ver detalles
                                    </a>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-save-line mr-1"></i>
                                    Actualizar Año Fiscal
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
        feedback.innerHTML = `<i class="ri-information-line"></i> Nueva duración: ${diffDays} días`;
        
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
            feedback.innerHTML = `<i class="ri-alert-line"></i> Nueva duración: ${diffDays} días (recomendado: 360-370 días)`;
        }
    }
}

// Auto-completar año cuando se cambia la fecha de inicio
document.getElementById('start_date').addEventListener('change', function() {
    const startDate = this.value;
    if (startDate) {
        const year = new Date(startDate).getFullYear();
        if (confirm('¿Desea actualizar el año a ' + year + ' basado en la nueva fecha de inicio?')) {
            document.getElementById('year').value = year;
        }
    }
});

// Llamar validación inicial para mostrar duración actual
validateDates();
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>