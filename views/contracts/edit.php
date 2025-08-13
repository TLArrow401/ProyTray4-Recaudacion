<?php
// Vista para editar contrato

// Incluir el controlador
require_once __DIR__ . '/../../controllers/ContractsController.php';

$contractsController = new ContractsController();

// Obtener ID del contrato
$contract_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$contract_id) {
    header('Location: index.php');
    exit;
}

// Variables para mantener los datos del formulario
$formData = [];
$errors = [];
$result = null;

// Procesar formulario si se envió
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario
    $formData = [
        'awardee_id' => $_POST['awardee_id'] ?? '',
        'fiscal_year_id' => $_POST['fiscal_year_id'] ?? '',
        'start_date' => $_POST['start_date'] ?? '',
        'end_date' => $_POST['end_date'] ?? '',
        'type' => $_POST['type'] ?? '',
        'contract_mode' => $_POST['contract_mode'] ?? '',
        'business_categories' => json_decode($_POST['business_categories'] ?? '[]', true),
        'locations' => json_decode($_POST['locations'] ?? '[]', true)
    ];
    
    // Procesar actualización
    $result = $contractsController->update($contract_id, $formData);
    
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
}

// Obtener datos para la vista
$editData = $contractsController->edit($contract_id);
if (isset($editData['success']) && !$editData['success']) {
    $error_message = $editData['message'];
    header('Location: index.php');
    exit;
} else {
    $contract = $editData['contract'] ?? [];
    $contract_categories = $editData['contract_categories'] ?? [];
    $contract_locations = $editData['contract_locations'] ?? [];
    $awardees = $editData['awardees'] ?? [];
    $fiscal_years = $editData['fiscal_years'] ?? [];
    $zones = $editData['zones'] ?? [];
    $external_categories = $editData['external_categories'] ?? [];
    $internal_categories = $editData['internal_categories'] ?? [];
    $page_title = $editData['page_title'] ?? 'Editar Contrato';
    
    // Usar datos existentes si no se envió formulario
    if (empty($formData)) {
        $formData = [
            'awardee_id' => $contract['awardee_id'],
            'fiscal_year_id' => $contract['fiscal_year_id'],
            'start_date' => $contract['start_date'],
            'end_date' => $contract['end_date'],
            'type' => $contract['type'],
            'contract_mode' => $contract['contract_mode']
        ];
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
                            <i class="ri-edit-line mr-1"></i>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <div>
                            <a href="show.php?id=<?php echo $contract_id; ?>" class="btn btn-info me-2">
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

                        <form method="POST" class="needs-validation" novalidate id="contractForm">
                            <div class="row g-3">
                                <!-- Información básica del contrato -->
                                <div class="col-12">
                                    <h6 class="border-bottom pb-2 mb-3">
                                        <i class="ri-information-line"></i> Información Básica del Contrato
                                    </h6>
                                </div>

                                <!-- Adjudicatario -->
                                <div class="col-md-6">
                                    <label for="awardee_id" class="form-label">
                                        Adjudicatario <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="awardee_id" name="awardee_id" required>
                                        <option value="">Seleccionar adjudicatario...</option>
                                        <?php foreach ($awardees as $awardee): ?>
                                        <option value="<?php echo $awardee['id']; ?>" 
                                                <?php echo $formData['awardee_id'] == $awardee['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($awardee['name'] . ' (' . $awardee['id_number'] . ')'); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Año Fiscal -->
                                <div class="col-md-6">
                                    <label for="fiscal_year_id" class="form-label">
                                        Año Fiscal <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="fiscal_year_id" name="fiscal_year_id" required onchange="loadFiscalYearDates()">
                                        <option value="">Seleccionar año fiscal...</option>
                                        <?php foreach ($fiscal_years as $fiscal_year): ?>
                                        <option value="<?php echo $fiscal_year['id']; ?>" 
                                                <?php echo $formData['fiscal_year_id'] == $fiscal_year['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($fiscal_year['year']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Fecha de Inicio -->
                                <div class="col-md-6">
                                    <label for="start_date" class="form-label">
                                        Fecha de Inicio <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" 
                                           class="form-control" 
                                           id="start_date" 
                                           name="start_date" 
                                           value="<?php echo htmlspecialchars($formData['start_date']); ?>" 
                                           required>
                                </div>

                                <!-- Fecha de Finalización -->
                                <div class="col-md-6">
                                    <label for="end_date" class="form-label">
                                        Fecha de Finalización <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" 
                                           class="form-control" 
                                           id="end_date" 
                                           name="end_date" 
                                           value="<?php echo htmlspecialchars($formData['end_date']); ?>" 
                                           required>
                                </div>

                                <!-- Tipo de Contrato -->
                                <div class="col-md-6">
                                    <label for="type" class="form-label">
                                        Tipo de Contrato <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="type" name="type" required>
                                        <option value="">Seleccionar tipo...</option>
                                        <option value="simultaneous" <?php echo $formData['type'] === 'simultaneous' ? 'selected' : ''; ?>>
                                            Simultáneo
                                        </option>
                                        <option value="advance" <?php echo $formData['type'] === 'advance' ? 'selected' : ''; ?>>
                                            Adelantado
                                        </option>
                                    </select>
                                </div>

                                <!-- Modalidad de Contrato -->
                                <div class="col-md-6">
                                    <label for="contract_mode" class="form-label">
                                        Modalidad de Pago <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="contract_mode" name="contract_mode" required>
                                        <option value="">Seleccionar modalidad...</option>
                                        <option value="monthly" <?php echo $formData['contract_mode'] === 'monthly' ? 'selected' : ''; ?>>
                                            Mensual
                                        </option>
                                        <option value="weekly" <?php echo $formData['contract_mode'] === 'weekly' ? 'selected' : ''; ?>>
                                            Semanal
                                        </option>
                                    </select>
                                </div>

                                <!-- Categorías de Negocio -->
                                <div class="col-12">
                                    <h6 class="border-bottom pb-2 mb-3 mt-4">
                                        <i class="ri-price-tag-3-line"></i> Categorías de Negocio (Rubros)
                                    </h6>
                                </div>

                                <div class="col-12">
                                    <div class="row">
                                        <!-- Selector de Categorías Externas -->
                                        <div class="col-md-6">
                                            <label for="external_category_selector" class="form-label">
                                                Categorías Externas
                                            </label>
                                            <div class="input-group">
                                                <select class="form-select" id="external_category_selector">
                                                    <option value="">Seleccionar categoría externa...</option>
                                                    <?php foreach ($external_categories as $category): ?>
                                                    <option value="<?php echo $category['id']; ?>" 
                                                            data-name="<?php echo htmlspecialchars($category['name']); ?>"
                                                            data-type="external"
                                                            data-payment-count="<?php echo $category['payment_count']; ?>"
                                                            data-installation-type="<?php echo htmlspecialchars($category['installation_type']); ?>">
                                                        <?php echo htmlspecialchars($category['name'] . ' (' . $category['installation_type'] . ')'); ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button type="button" class="btn btn-outline-primary" onclick="addCategory('external')">
                                                    <i class="ri-add-line"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Selector de Categorías Internas -->
                                        <div class="col-md-6">
                                            <label for="internal_category_selector" class="form-label">
                                                Categorías Internas
                                            </label>
                                            <div class="input-group">
                                                <select class="form-select" id="internal_category_selector">
                                                    <option value="">Seleccionar categoría interna...</option>
                                                    <?php foreach ($internal_categories as $category): ?>
                                                    <option value="<?php echo $category['id']; ?>" 
                                                            data-name="<?php echo htmlspecialchars($category['name']); ?>"
                                                            data-type="internal"
                                                            data-payment-count="<?php echo $category['payment_count']; ?>"
                                                            data-installation-type="<?php echo htmlspecialchars($category['installation_type']); ?>">
                                                        <?php echo htmlspecialchars($category['name'] . ' (' . $category['installation_type'] . ')'); ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button type="button" class="btn btn-outline-primary" onclick="addCategory('internal')">
                                                    <i class="ri-add-line"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Cards de Categorías Seleccionadas -->
                                <div class="col-12">
                                    <div id="categoriesContainer" class="row g-3">
                                        <!-- Las categorías se agregarán aquí dinámicamente -->
                                    </div>
                                </div>

                                <!-- Locales -->
                                <div class="col-12">
                                    <h6 class="border-bottom pb-2 mb-3 mt-4">
                                        <i class="ri-store-2-line"></i> Locales del Contrato
                                    </h6>
                                </div>

                                <!-- Selector de Zona -->
                                <div class="col-md-4">
                                    <label for="zone_selector" class="form-label">
                                        Zona
                                    </label>
                                    <select class="form-select" id="zone_selector" onchange="loadSectors()">
                                        <option value="">Seleccionar zona...</option>
                                        <?php foreach ($zones as $zone): ?>
                                        <option value="<?php echo $zone['id']; ?>">
                                            <?php echo htmlspecialchars($zone['name']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Selector de Sector -->
                                <div class="col-md-4">
                                    <label for="sector_selector" class="form-label">
                                        Sector
                                    </label>
                                    <select class="form-select" id="sector_selector" onchange="loadStalls()" disabled>
                                        <option value="">Primero seleccione una zona</option>
                                    </select>
                                </div>

                                <!-- Selector de Local -->
                                <div class="col-md-4">
                                    <label for="stall_selector" class="form-label">
                                        Local
                                    </label>
                                    <div class="input-group">
                                        <select class="form-select" id="stall_selector" disabled>
                                            <option value="">Primero seleccione zona y sector</option>
                                        </select>
                                        <button type="button" class="btn btn-outline-primary" onclick="addStall()" disabled id="addStallBtn">
                                            <i class="ri-add-line"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Cards de Locales Seleccionados -->
                                <div class="col-12">
                                    <div id="stallsContainer" class="row g-3">
                                        <!-- Los locales se agregarán aquí dinámicamente -->
                                    </div>
                                </div>

                                <!-- Campos ocultos para enviar datos -->
                                <input type="hidden" name="business_categories" id="business_categories_input">
                                <input type="hidden" name="locations" id="locations_input">

                                <!-- Botones de acción -->
                                <div class="col-12">
                                    <div class="d-flex justify-content-end gap-2 mt-4">
                                        <a href="show.php?id=<?php echo $contract_id; ?>" class="btn btn-secondary">
                                            <i class="ri-close-line"></i> Cancelar
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ri-save-line"></i> Actualizar Contrato
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let selectedCategories = [];
let selectedStalls = [];

// Cargar fechas del año fiscal
async function loadFiscalYearDates() {
    const fiscalYearId = document.getElementById('fiscal_year_id').value;
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    if (!fiscalYearId) {
        // Limpiar fechas si no hay año fiscal seleccionado
        startDateInput.value = '';
        endDateInput.value = '';
        return;
    }
    
    try {
        const response = await fetch('../ajax/get-fiscal-year-data.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ fiscal_year_id: fiscalYearId })
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Establecer fecha de inicio como fecha actual
            const today = new Date();
            const todayString = today.toISOString().split('T')[0];
            startDateInput.value = todayString;
            
            // Establecer fecha final del año fiscal
            endDateInput.value = result.fiscal_year.end_date;
        } else {
            console.error('Error al cargar fechas del año fiscal:', result.message);
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Cargar sectores por zona
async function loadSectors() {
    const zoneId = document.getElementById('zone_selector').value;
    const sectorSelect = document.getElementById('sector_selector');
    const stallSelect = document.getElementById('stall_selector');
    const addStallBtn = document.getElementById('addStallBtn');
    
    // Resetear selectores dependientes
    sectorSelect.innerHTML = '<option value="">Cargando sectores...</option>';
    sectorSelect.disabled = true;
    stallSelect.innerHTML = '<option value="">Primero seleccione zona y sector</option>';
    stallSelect.disabled = true;
    addStallBtn.disabled = true;
    
    if (!zoneId) {
        sectorSelect.innerHTML = '<option value="">Primero seleccione una zona</option>';
        return;
    }
    
    try {
        const response = await fetch('../ajax/get-sectors-by-zone.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ zone_id: zoneId })
        });
        
        const result = await response.json();
        
        if (result.success) {
            sectorSelect.innerHTML = '<option value="">Seleccionar sector...</option>';
            result.sectors.forEach(sector => {
                sectorSelect.innerHTML += `<option value="${sector.id}">${sector.name}</option>`;
            });
            sectorSelect.disabled = false;
        } else {
            sectorSelect.innerHTML = '<option value="">Error al cargar sectores</option>';
        }
    } catch (error) {
        console.error('Error:', error);
        sectorSelect.innerHTML = '<option value="">Error al cargar sectores</option>';
    }
}

// Cargar locales por zona y sector
async function loadStalls() {
    const zoneId = document.getElementById('zone_selector').value;
    const sectorId = document.getElementById('sector_selector').value;
    const stallSelect = document.getElementById('stall_selector');
    const addStallBtn = document.getElementById('addStallBtn');
    
    stallSelect.innerHTML = '<option value="">Cargando locales...</option>';
    stallSelect.disabled = true;
    addStallBtn.disabled = true;
    
    if (!zoneId || !sectorId) {
        stallSelect.innerHTML = '<option value="">Primero seleccione zona y sector</option>';
        return;
    }
    
    try {
        const response = await fetch('../ajax/get-stalls-by-zone-sector.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ zone_id: zoneId, sector_id: sectorId })
        });
        
        const result = await response.json();
        
        if (result.success) {
            stallSelect.innerHTML = '<option value="">Seleccionar local...</option>';
            result.stalls.forEach(stall => {
                // No mostrar locales ya seleccionados
                if (!selectedStalls.find(s => s.id == stall.id)) {
                    stallSelect.innerHTML += `<option value="${stall.id}" data-number="${stall.stall_number}" data-description="${stall.description}" data-sector="${stall.sector_name}">${stall.stall_number} - ${stall.description}</option>`;
                }
            });
            stallSelect.disabled = false;
            addStallBtn.disabled = false;
        } else {
            stallSelect.innerHTML = '<option value="">Error al cargar locales</option>';
        }
    } catch (error) {
        console.error('Error:', error);
        stallSelect.innerHTML = '<option value="">Error al cargar locales</option>';
    }
}

// Las demás funciones son iguales que en create.php
function addCategory(type) {
    const selector = document.getElementById(`${type}_category_selector`);
    const selectedOption = selector.options[selector.selectedIndex];
    
    if (!selectedOption.value) {
        alert('Por favor seleccione una categoría');
        return;
    }
    
    const categoryId = selectedOption.value;
    const categoryData = {
        category_id: categoryId,
        type: type,
        name: selectedOption.dataset.name,
        payment_count: selectedOption.dataset.paymentCount,
        installation_type: selectedOption.dataset.installationType
    };
    
    // Verificar si ya está agregada
    if (selectedCategories.find(c => c.category_id == categoryId && c.type == type)) {
        alert('Esta categoría ya ha sido agregada');
        return;
    }
    
    selectedCategories.push(categoryData);
    renderCategories();
    updateCategoriesInput();
    
    // Resetear selector
    selector.selectedIndex = 0;
}

function addStall() {
    const stallSelect = document.getElementById('stall_selector');
    const selectedOption = stallSelect.options[stallSelect.selectedIndex];
    
    if (!selectedOption.value) {
        alert('Por favor seleccione un local');
        return;
    }
    
    const stallData = {
        id: selectedOption.value,
        stall_number: selectedOption.dataset.number,
        description: selectedOption.dataset.description,
        sector_name: selectedOption.dataset.sector
    };
    
    selectedStalls.push(stallData);
    renderStalls();
    updateLocationsInput();
    
    // Quitar del selector
    selectedOption.remove();
    
    // Resetear selector
    stallSelect.selectedIndex = 0;
}

function renderCategories() {
    const container = document.getElementById('categoriesContainer');
    container.innerHTML = '';
    
    selectedCategories.forEach((category, index) => {
        const card = document.createElement('div');
        card.className = 'col-md-6';
        card.innerHTML = `
            <div class="card border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="card-title">
                                <span class="badge ${category.type === 'external' ? 'bg-warning' : 'bg-info'} me-2">
                                    ${category.type === 'external' ? 'Externa' : 'Interna'}
                                </span>
                                ${category.name}
                            </h6>
                            <p class="card-text">
                                <small class="text-muted">
                                    Tipo: ${category.installation_type}<br>
                                    Pagos: ${category.payment_count} vez(es)
                                </small>
                            </p>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeCategory(${index})">
                            <i class="ri-close-line"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        container.appendChild(card);
    });
}

function renderStalls() {
    const container = document.getElementById('stallsContainer');
    container.innerHTML = '';
    
    selectedStalls.forEach((stall, index) => {
        const card = document.createElement('div');
        card.className = 'col-md-6';
        card.innerHTML = `
            <div class="card border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="card-title">
                                <span class="badge bg-success me-2">Local</span>
                                ${stall.stall_number}
                            </h6>
                            <p class="card-text">
                                <small class="text-muted">
                                    ${stall.description}<br>
                                    Sector: ${stall.sector_name}
                                </small>
                            </p>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeStall(${index})">
                            <i class="ri-close-line"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        container.appendChild(card);
    });
}

function removeCategory(index) {
    selectedCategories.splice(index, 1);
    renderCategories();
    updateCategoriesInput();
}

function removeStall(index) {
    selectedStalls.splice(index, 1);
    renderStalls();
    updateLocationsInput();
    
    // Recargar locales si hay zona y sector seleccionados
    const zoneId = document.getElementById('zone_selector').value;
    const sectorId = document.getElementById('sector_selector').value;
    if (zoneId && sectorId) {
        loadStalls();
    }
}

function updateCategoriesInput() {
    document.getElementById('business_categories_input').value = JSON.stringify(selectedCategories);
}

function updateLocationsInput() {
    const stallIds = selectedStalls.map(stall => stall.id);
    document.getElementById('locations_input').value = JSON.stringify(stallIds);
}

// Validación del formulario
document.getElementById('contractForm').addEventListener('submit', function(e) {
    updateCategoriesInput();
    updateLocationsInput();
    
    if (selectedCategories.length === 0 && selectedStalls.length === 0) {
        e.preventDefault();
        alert('Debe agregar al menos una categoría de negocio o un local al contrato');
        return false;
    }
});

// Cargar datos existentes
document.addEventListener('DOMContentLoaded', function() {
    // Cargar categorías existentes
    <?php if (!empty($contract_categories)): ?>
    selectedCategories = <?php echo json_encode(array_map(function($cat) {
        return [
            'category_id' => $cat['type'] === 'external' ? $cat['external_category_id'] : $cat['internal_category_id'],
            'type' => $cat['type'],
            'name' => $cat['category_name'],
            'payment_count' => $cat['payment_count'],
            'installation_type' => $cat['installation_type']
        ];
    }, $contract_categories)); ?>;
    renderCategories();
    updateCategoriesInput();
    <?php endif; ?>
    
    // Cargar locales existentes
    <?php if (!empty($contract_locations)): ?>
    selectedStalls = <?php echo json_encode(array_map(function($loc) {
        return [
            'id' => $loc['stall_id'],
            'stall_number' => $loc['stall_number'],
            'description' => $loc['description'],
            'sector_name' => $loc['sector_name']
        ];
    }, $contract_locations)); ?>;
    renderStalls();
    updateLocationsInput();
    <?php endif; ?>
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>