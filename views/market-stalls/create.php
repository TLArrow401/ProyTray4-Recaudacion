<?php
// Verificar acceso primero - ANTES de cualquier output
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
AuthMiddleware::requireUserManagementAccess();

// Incluir el controlador
require_once __DIR__ . '/../../controllers/MarketStallsController.php';

$marketStallsController = new MarketStallsController();

// Inicializar variables
$result = [
    'success' => true,
    'message' => '',
    'messageType' => '',
    'errors' => [],
    'sector_id' => '',
    'stall_number' => '',
    'location_description' => '',
    'zones' => [],
    'sectors' => []
];

// Si es POST, procesar la creación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $params = $_POST;
    $params['_method'] = 'POST';
    $result = $marketStallsController->create($params);
    
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
    $result = $marketStallsController->create();
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
                            Crear Nuevo Local de Mercado
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
                                        <label for="zone_id" class="form-label">
                                            Zona <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control" 
                                                id="zone_id" 
                                                name="zone_id">
                                            <option value="">Seleccione una zona</option>
                                            <?php foreach ($result['zones'] as $zone): ?>
                                            <option value="<?php echo $zone['id']; ?>">
                                                <?php echo htmlspecialchars($zone['name']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="form-text text-muted">
                                            Primero seleccione la zona para cargar los sectores disponibles
                                        </small>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="sector_id" class="form-label">
                                            Sector <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control <?php echo isset($result['errors']['sector_id']) ? 'is-invalid' : ''; ?>" 
                                                id="sector_id" 
                                                name="sector_id" 
                                                required>
                                            <option value="">Primero seleccione una zona</option>
                                        </select>
                                        <?php if (isset($result['errors']['sector_id'])): ?>
                                        <div class="invalid-feedback">
                                            <?php echo htmlspecialchars($result['errors']['sector_id']); ?>
                                        </div>
                                        <?php endif; ?>
                                        <small class="form-text text-muted">
                                            Seleccione el sector donde se ubicará el local
                                        </small>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="stall_number" class="form-label">
                                            Número del Local <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                                class="form-control <?php echo isset($result['errors']['stall_number']) ? 'is-invalid' : ''; ?>" 
                                                id="stall_number" 
                                                name="stall_number" 
                                                value="<?php echo htmlspecialchars($result['stall_number']); ?>"
                                                placeholder="Ej: L-001, Local 25, etc."
                                                maxlength="50"
                                                required>
                                        <?php if (isset($result['errors']['stall_number'])): ?>
                                        <div class="invalid-feedback">
                                            <?php echo htmlspecialchars($result['errors']['stall_number']); ?>
                                        </div>
                                        <?php endif; ?>
                                        <small class="form-text text-muted">
                                            Número o identificador único del local (máximo 50 caracteres)
                                        </small>
                                    </div>

                                    <div class="form-group">
                                        <label for="location_description" class="form-label">
                                            Descripción de Ubicación
                                        </label>
                                        <textarea class="form-control <?php echo isset($result['errors']['location_description']) ? 'is-invalid' : ''; ?>" 
                                                id="location_description" 
                                                name="location_description" 
                                                rows="4"
                                                placeholder="Descripción detallada de la ubicación del local (opcional)"
                                                maxlength="255"><?php echo htmlspecialchars($result['location_description']); ?></textarea>
                                        <?php if (isset($result['errors']['location_description'])): ?>
                                        <div class="invalid-feedback">
                                            <?php echo htmlspecialchars($result['errors']['location_description']); ?>
                                        </div>
                                        <?php endif; ?>
                                        <small class="form-text text-muted">
                                            Descripción detallada de la ubicación (opcional, máximo 255 caracteres)
                                        </small>
                                    </div>
                            </div>

                            <div class="col-md-4">
                                <!-- Información adicional -->
                            
                                <div class="text-muted">
                                    <p><strong>Campos obligatorios:</strong></p>
                                    <ul class="mb-0">
                                        <li>Zona</li>
                                        <li>Sector</li>
                                        <li>Número del local</li>
                                    </ul>
                                </div>

                                <hr>

                                <div class="text-muted">
                                    <p><strong>Campos opcionales:</strong></p>
                                    <ul class="mb-0">
                                        <li>Descripción de ubicación</li>
                                    </ul>
                                </div>

                                <hr>

                                <div class="text-muted">
                                    <p><strong>Validaciones:</strong></p>
                                    <ul class="mb-0">
                                        <li>El número debe ser único por sector</li>
                                        <li>Máximo 50 caracteres para el número</li>
                                        <li>Máximo 255 caracteres para la descripción</li>
                                        <li>El sector debe existir</li>
                                    </ul>
                                </div>

                                
                            </div>
                        </div>
                    
                    </div>
                    <div class="card-footer d-flex justify-content-end">
                       <!-- Acciones -->
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="ri ri-save-line mr-1"></i>
                            Crear Local
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
    const zoneIdInput = document.getElementById('zone_id');
    const sectorIdInput = document.getElementById('sector_id');
    const stallNumberInput = document.getElementById('stall_number');
    const locationDescriptionInput = document.getElementById('location_description');

    // Función para cargar sectores por zona
    function loadSectorsByZone(zoneId) {
        if (!zoneId) {
            sectorIdInput.innerHTML = '<option value="">Primero seleccione una zona</option>';
            sectorIdInput.disabled = true;
            return;
        }

        // Mostrar loading
        sectorIdInput.innerHTML = '<option value="">Cargando sectores...</option>';
        sectorIdInput.disabled = true;

        // Realizar petición AJAX
        fetch('get_sectors_by_zone.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'zone_id=' + encodeURIComponent(zoneId)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let options = '<option value="">Seleccione un sector</option>';
                data.data.forEach(sector => {
                    options += `<option value="${sector.id}">${sector.name}</option>`;
                });
                sectorIdInput.innerHTML = options;
                sectorIdInput.disabled = false;
            } else {
                sectorIdInput.innerHTML = '<option value="">Error al cargar sectores</option>';
                console.error('Error:', data.message);
            }
        })
        .catch(error => {
            sectorIdInput.innerHTML = '<option value="">Error al cargar sectores</option>';
            console.error('Error:', error);
        });
    }

    // Manejar cambio de zona
    zoneIdInput.addEventListener('change', function() {
        loadSectorsByZone(this.value);
        sectorIdInput.classList.remove('is-invalid');
    });

    // Validar sector en tiempo real
    sectorIdInput.addEventListener('change', function() {
        if (this.value) {
            this.classList.remove('is-invalid');
        }
    });

    // Validar número del local en tiempo real
    stallNumberInput.addEventListener('input', function() {
        const value = this.value.trim();
        const feedback = this.nextElementSibling;
        
        if (value.length > 50) {
            this.classList.add('is-invalid');
            if (feedback && feedback.classList.contains('invalid-feedback')) {
                feedback.textContent = 'El número del local no puede tener más de 50 caracteres';
            }
        } else {
            this.classList.remove('is-invalid');
        }
    });

    // Validar descripción en tiempo real
    locationDescriptionInput.addEventListener('input', function() {
        const value = this.value.trim();
        const feedback = this.nextElementSibling;
        
        if (value.length > 255) {
            this.classList.add('is-invalid');
            if (feedback && feedback.classList.contains('invalid-feedback')) {
                feedback.textContent = 'La descripción no puede tener más de 255 caracteres';
            }
        } else {
            this.classList.remove('is-invalid');
        }
    });

    // Validación al enviar el formulario
    form.addEventListener('submit', function(e) {
        let isValid = true;

        // Validar sector
        if (!sectorIdInput.value) {
            sectorIdInput.classList.add('is-invalid');
            isValid = false;
        }

        // Validar número del local
        const stallNumber = stallNumberInput.value.trim();
        if (!stallNumber) {
            stallNumberInput.classList.add('is-invalid');
            isValid = false;
        }

        // Validar descripción
        const locationDescription = locationDescriptionInput.value.trim();
        if (locationDescription.length > 255) {
            locationDescriptionInput.classList.add('is-invalid');
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